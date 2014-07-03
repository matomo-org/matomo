<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Common;
use Piwik\Log;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\IP;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Visit;
use Piwik\Tracker\Request;

abstract class Base extends VisitDimension
{
    private static $cachedLocations = array();

    protected function getUrlOverrideValueIfAllowed($urlParamToOverride, Request $request)
    {
        if (!$request->isAuthenticated()) {
            return false;
        }

        $value = Common::getRequestVar($urlParamToOverride, false, 'string', $request->getParams());
        if (!empty($value)) {
            $message = 'getUrlOverrideValueIfAllowed for parameter ' . $urlParamToOverride . ': ' . $value;

            Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);
            return $value;
        }

        return false;
    }

    public function getRequiredVisitFields()
    {
        return array('location_ip', 'location_browser_lang');
    }

    protected function getLocationDetail($userInfo, $locationKey)
    {
        $location = $this->getCachedLocation($userInfo);

        if (!empty($location[$locationKey])) {

            $message = 'Location Detail: ' . $locationKey . ' ' . $location[$locationKey];
            Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);

            return $location[$locationKey];
        }

        return false;
    }

    protected function getUserInfo(Request $request, Visitor $visitor)
    {
        $ipAddress = $this->getIpAddress($visitor->getVisitorColumn('location_ip'), $request);
        $language  = $visitor->getVisitorColumn('location_browser_lang');

        $message = 'language is ' . $language;
        Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);

        $userInfo  = array('lang' => $language, 'ip' => $ipAddress);

        return $userInfo;
    }

    protected function getCachedLocation($userInfo)
    {
        require_once PIWIK_INCLUDE_PATH . "/plugins/UserCountry/LocationProvider.php";

        $key = md5(implode(',', $userInfo));

        $message = 'cache key for location info is ' . $key;
        Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);

        if (array_key_exists($key, self::$cachedLocations)) {
            return self::$cachedLocations[$key];
        }

        $provider = $this->getProvider();
        $location = $this->getLocation($provider, $userInfo);

        $message = 'first location test is ' . print_r($location, true);
        Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);

        if (empty($location)) {
            $providerId = $provider->getId();
            Common::printDebug("GEO: couldn't find a location with Geo Module '$providerId'");

            if (!$this->isDefaultProvider($provider)) {
                Common::printDebug("Using default provider as fallback...");
                $provider = $this->getDefaultProvider();
                $location = $this->getLocation($provider, $userInfo);

                $message = 'default location test is ' . print_r($location, true);
                Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);
            }
        }

        if (empty($location)) {
            $location = array();
        }

        if (empty($location['country_code'])) { // sanity check
            $location['country_code'] = Visit::UNKNOWN_CODE;
        }

        self::$cachedLocations[$key] = $location;

        return $location;
    }

    private function getIpAddress($anonymizedIp, \Piwik\Tracker\Request $request)
    {
        $privacyConfig = new PrivacyManagerConfig();

        $ip = $request->getIp();

        $message = 'Get ip address: ' . $ip;
        Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);
        if ($privacyConfig->useAnonymizedIpForVisitEnrichment) {
            $message = 'useAnonymizedIpForVisitEnrichment';
            Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);
            $ip = $anonymizedIp;
        }

        $message = 'Get actual ip address: ' . $ip;
        Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);
        $ipAddress = IP::N2P($ip);

        $message = 'Get actual ip address rev: ' . $ipAddress;
        Log::getInstance()->customLogToFileForDebuggingIfYouStillSeeThisHereRemoveIt($message);

        return $ipAddress;
    }

    /**
     * @param \Piwik\Plugins\UserCountry\LocationProvider $provider
     * @param array $userInfo
     * @return array|null
     */
    private function getLocation($provider, $userInfo)
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

    private function getDefaultProvider()
    {
        $id       = DefaultProvider::ID;
        $provider = LocationProvider::getProviderById($id);

        return $provider;
    }

    private function isDefaultProvider($provider)
    {
        return !empty($provider) && DefaultProvider::ID == $provider->getId();
    }

    private function getProvider()
    {
        $id       = Common::getCurrentLocationProviderId();
        $provider = LocationProvider::getProviderById($id);

        if ($provider === false) {
            $provider = $this->getDefaultProvider();
            Common::printDebug("GEO: no current location provider sent, falling back to default '$id' one.");
        }

        return $provider;
    }

}