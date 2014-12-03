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
     * @var string
     */
    protected $currentLocationProviderId;

    /**
     * @var callable|string
     */
    protected $getProviderByIdCallback;

    /**
     * @param null|string $currentLocationProviderId
     * @param null $getProviderByIdCallback
     */
    public function __construct($currentLocationProviderId = null, $getProviderByIdCallback = null)
    {
        if ($currentLocationProviderId === null) {
            $currentLocationProviderId = Common::getCurrentLocationProviderId();
        }

        $this->currentLocationProviderId = $currentLocationProviderId;

        if (!is_callable($getProviderByIdCallback)) {
            $getProviderByIdCallback = '\Piwik\Plugins\UserCountry\LocationProvider::getProviderById';
        }

        $this->getProviderByIdCallback = $getProviderByIdCallback;
    }

    public function getLocation($userInfo, $useClassCache = true, $defaultProviderId = null)
    {
        if ($defaultProviderId === null) {
            $defaultProviderId = DefaultProvider::ID;
        }

        $userInfoKey = md5(implode(',', $userInfo));

        if (array_key_exists($userInfoKey, self::$cachedLocations) && $useClassCache) {
            return self::$cachedLocations[$userInfoKey];
        }

        $provider = $this->getProvider();
        $location = $this->getLocationObject($provider, $userInfo);

        if (empty($location)) {
            $providerId = $provider->getId();
            Common::printDebug("GEO: couldn't find a location with Geo Module '$providerId'");

            if (empty($provider) && $defaultProviderId == $provider->getId()) {
                Common::printDebug("Using default provider as fallback...");
                $provider = call_user_func($this->getProviderByIdCallback, $defaultProviderId);
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

    public function getProvider($defaultProviderId = null)
    {
        if ($defaultProviderId === null) {
            $defaultProviderId = DefaultProvider::ID;
        }

        $id = $this->currentLocationProviderId;
        $provider = call_user_func($this->getProviderByIdCallback, $id);

        if ($provider === false) {
            $provider = call_user_func($this->getProviderByIdCallback, $defaultProviderId);
            Common::printDebug("GEO: no current location provider sent, falling back to default '$id' one.");
        }

        return $provider;
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
