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
     * @param LocationProvider $provider
     * @param LocationProvider $backupProvider
     * @param Cache $locationCache
     */
    public function __construct(LocationProvider $provider = null, LocationProvider $backupProvider = null, Cache $locationCache = null)
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