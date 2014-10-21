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
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\UserCountry\LocationFetcher;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\IP;
use Piwik\Tracker\Visitor;
use Piwik\Tracker\Request;

abstract class Base extends VisitDimension
{
    /**
     * @var LocationFetcher
     */
    private $locationFetcher;

    protected function getUrlOverrideValueIfAllowed($urlParamToOverride, Request $request)
    {
        if (!$request->isAuthenticated()) {
            return false;
        }

        $value = Common::getRequestVar($urlParamToOverride, false, 'string', $request->getParams());
        if (!empty($value)) {
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
        $location = $this->getLocationFetcher()->getLocation(
            $userInfo,
            empty($GLOBALS['PIWIK_TRACKER_LOCAL_TRACKING'])
        );

        if (!isset($location[$locationKey])) {
            return false;
        }

        return $location[$locationKey];
    }

    protected function getLocationFetcher()
    {
        if ($this->locationFetcher === null) {
            $this->locationFetcher = new LocationFetcher();
        }

        return $this->locationFetcher;
    }

    protected function getUserInfo(Request $request, Visitor $visitor)
    {
        $ipAddress = $this->getIpAddress($visitor->getVisitorColumn('location_ip'), $request);
        $language  = $visitor->getVisitorColumn('location_browser_lang');

        $userInfo  = array('lang' => $language, 'ip' => $ipAddress);

        return $userInfo;
    }

    private function getIpAddress($anonymizedIp, \Piwik\Tracker\Request $request)
    {
        $privacyConfig = new PrivacyManagerConfig();

        $ip = $request->getIp();

        if ($privacyConfig->useAnonymizedIpForVisitEnrichment) {
            $ip = $anonymizedIp;
        }

        $ipAddress = IP::N2P($ip);

        return $ipAddress;
    }
}
