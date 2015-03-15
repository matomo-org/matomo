<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Cache\Cache;
use Piwik\Cache\Transient;
use Piwik\Common;
use Piwik\DataAccess\RawLogDao;
use Piwik\Network\IPUtils;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Tracker\Visit;

require_once PIWIK_INCLUDE_PATH . "/plugins/UserCountry/LocationProvider.php";

/**
 * Service that determines a visitor's location using visitor information.
 *
 * Individual locations are provided by a LocationProvider instance. By default,
 * the configured LocationProvider (as determined by
 * `Common::getCurrentLocationProviderId()` is used.
 *
 * If the configured location provider cannot provide a location for the visitor,
 * the default location provider (`DefaultProvider`) is used.
 *
 * A cache is used internally to speed up location retrieval. By default, an
 * in-memory cache is used, but another type of cache can be supplied during
 * construction.
 *
 * This service can be used from within the tracker.
 */
class VisitorGeolocator
{
    /**
     * @var string[]
     */
    public static $logVisitFieldsToUpdate = array(
        'location_country'   => LocationProvider::COUNTRY_CODE_KEY,
        'location_region'    => LocationProvider::REGION_CODE_KEY,
        'location_city'      => LocationProvider::CITY_NAME_KEY,
        'location_latitude'  => LocationProvider::LATITUDE_KEY,
        'location_longitude' => LocationProvider::LONGITUDE_KEY
    );

    /**
     * @var Cache
     */
    protected static $defaultLocationCache = null;

    /**
     * @var LocationProvider
     */
    private $provider;

    /**
     * @var LocationProvider
     */
    private $backupProvider;

    /**
     * @var Cache
     */
    private $locationCache;

    /**
     * @var RawLogDao
     */
    protected $dao;

    public function __construct(LocationProvider $provider = null, LocationProvider $backupProvider = null, Cache $locationCache = null,
                                RawLogDao $dao = null)
    {
        if ($provider === null) {
            // note: Common::getCurrentLocationProviderId() uses the tracker cache, which is why it's used here instead
            // of accessing the option table
            $provider = LocationProvider::getProviderById(Common::getCurrentLocationProviderId());

            if (empty($provider)) {
                Common::printDebug("GEO: no current location provider sent, falling back to default '" . DefaultProvider::ID . "' one.");

                $provider = $this->getDefaultProvider();
            }
        }
        $this->provider = $provider;

        $this->backupProvider = $backupProvider ?: $this->getDefaultProvider();
        $this->locationCache = $locationCache ?: self::getDefaultLocationCache();
        $this->dao = $dao ?: new RawLogDao();
    }

    public function getLocation($userInfo, $useClassCache = true)
    {
        $userInfoKey = md5(implode(',', $userInfo));
        if ($useClassCache
            && $this->locationCache->contains($userInfoKey)
        ) {
            return $this->locationCache->fetch($userInfoKey);
        }

        $location = $this->getLocationObject($this->provider, $userInfo);

        if (empty($location)) {
            $providerId = $this->provider->getId();
            Common::printDebug("GEO: couldn't find a location with Geo Module '$providerId'");

            if ($providerId != $this->backupProvider->getId()) {
                Common::printDebug("Using default provider as fallback...");

                $location = $this->getLocationObject($this->backupProvider, $userInfo);
            }
        }

        $location = $location ?: array();
        if (empty($location['country_code'])) {
            $location['country_code'] = Visit::UNKNOWN_CODE;
        }

        $this->locationCache->save($userInfoKey, $location);

        return $location;
    }

    /**
     * @param LocationProvider $provider
     * @param array $userInfo
     * @return array|false
     */
    private function getLocationObject(LocationProvider $provider, $userInfo)
    {
        $location   = $provider->getLocation($userInfo);
        $providerId = $provider->getId();
        $ipAddress  = $userInfo['ip'];

        if ($location === false) {
            return false;
        }

        Common::printDebug("GEO: Found IP $ipAddress location (provider '" . $providerId . "'): " . var_export($location, true));

        return $location;
    }

    /**
     * Geolcates an existing visit and then updates it if it's current attributes are different than
     * what was geolocated. Also updates all conversions of a visit.
     *
     * **This method should NOT be used from within the tracker.**
     */
    public function attributeExistingVisit($visit, $useClassCache = true)
    {
        $ip = IPUtils::binaryToStringIP($visit['location_ip']);
        $location = $this->getLocation(array('ip' => $ip), $useClassCache);

        $valuesToUpdate = $this->getVisitFieldsToUpdate($visit, $location);

        if (!empty($valuesToUpdate)) {
            $idVisit = $visit['idvisit'];

            $this->dao->updateVisits($valuesToUpdate, $idVisit);
            $this->dao->updateConversions($valuesToUpdate, $idVisit);
        }

        return $valuesToUpdate;
    }

    /**
     * Returns location log values that are different than the values currently in a log row.
     *
     * @param array $row The visit row.
     * @param array $location The location information.
     * @return array The location properties to update.
     */
    private function getVisitFieldsToUpdate(array $row, $location)
    {
        if (isset($location[LocationProvider::COUNTRY_CODE_KEY])) {
            $location[LocationProvider::COUNTRY_CODE_KEY] = strtolower($location[LocationProvider::COUNTRY_CODE_KEY]);
        }

        $valuesToUpdate = array();
        foreach (self::$logVisitFieldsToUpdate as $column => $locationKey) {
            if (empty($location[$locationKey])) {
                continue;
            }

            $locationPropertyValue = $location[$locationKey];
            $existingPropertyValue = $row[$column];

            if ($locationPropertyValue != $existingPropertyValue) {
                $valuesToUpdate[$column] = $locationPropertyValue;
            }
        }
        return $valuesToUpdate;
    }

    /**
     * @return LocationProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return LocationProvider
     */
    public function getBackupProvider()
    {
        return $this->backupProvider;
    }

    private function getDefaultProvider()
    {
        return LocationProvider::getProviderById(DefaultProvider::ID);
    }

    public static function getDefaultLocationCache()
    {
        if (self::$defaultLocationCache === null) {
            self::$defaultLocationCache = new Transient();
        }
        return self::$defaultLocationCache;
    }
}