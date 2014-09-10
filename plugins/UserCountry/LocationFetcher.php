<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Common;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Tracker\Visit;

require_once PIWIK_INCLUDE_PATH . "/plugins/UserCountry/LocationProvider.php";

class LocationFetcher
{
    /**
     * @var array
     */
    protected static $cachedLocations = array();

    /**
     * @var LocationFetcherProvider
     */
    protected $locationFetcherProvider;

    /**
     * @param LocationFetcherProvider|null $locationFetcherProvider
     */
    public function __construct(LocationFetcherProvider $locationFetcherProvider = null)
    {
        if ($locationFetcherProvider === null) {
            $locationFetcherProvider = new LocationFetcherProvider();
        }

        $this->locationFetcherProvider = $locationFetcherProvider;
    }

    /**
     * @param array $userInfo
     * @param string $key
     * @param bool $useClassCache
     * @return bool
     */
    public function getLocationDetail($userInfo, $key, $useClassCache = true)
    {
        $location = $this->getLocation($userInfo, $useClassCache);

        if (!isset($location[$key])) {
            return false;
        }

        return $location[$key];
    }

    public function getLocation($userInfo, $useClassCache = true)
    {
        $userInfoKey = md5(implode(',', $userInfo));

        if (array_key_exists($userInfoKey, self::$cachedLocations) && $useClassCache) {
            return self::$cachedLocations[$userInfoKey];
        }

        $provider = $this->locationFetcherProvider->get();
        $location = $this->getLocationObject($provider, $userInfo);

        if (empty($location)) {
            $providerId = $provider->getId();
            Common::printDebug("GEO: couldn't find a location with Geo Module '$providerId'");

            if (!$this->locationFetcherProvider->isDefaultProvider($provider)) {
                Common::printDebug("Using default provider as fallback...");
                $provider = $this->locationFetcherProvider->getDefaultProvider();
                $location = $this->getLocationObject($provider, $userInfo);
            }
        }

        if (empty($location)) {
            $location = array();
        }

        if (empty($location['country_code'])) {
            $location['country_code'] = Visit::UNKNOWN_CODE;
        }

        self::$cachedLocations[$userInfoKey] = $location;

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
} 
