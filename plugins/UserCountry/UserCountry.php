<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Config;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Url;

/**
 * @see plugins/UserCountry/GeoIPAutoUpdater.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/GeoIPAutoUpdater.php';

/**
 *
 */
class UserCountry extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'Goals.getReportsWithGoalMetrics'        => 'getReportsWithGoalMetrics',
            'API.getSegmentDimensionMetadata'        => 'getSegmentsMetadata',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Tracker.newVisitorInformation'          => 'enrichVisitWithLocation',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Tracker.setTrackerCacheGeneral'         => 'setTrackerCacheGeneral',
            'Insights.addReportToOverview'           => 'addReportToInsightsOverview'
        );
        return $hooks;
    }

    public function addReportToInsightsOverview(&$reports)
    {
        $reports['UserCountry_getCountry'] = array();
    }

    public function setTrackerCacheGeneral(&$cache)
    {
        $cache['currentLocationProviderId'] = LocationProvider::getCurrentProviderId();
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/UserCountry/stylesheets/userCountry.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserCountry/javascripts/userCountry.js";
    }

    public function enrichVisitWithLocation(&$visitorInfo, \Piwik\Tracker\Request $request)
    {
        require_once PIWIK_INCLUDE_PATH . "/plugins/UserCountry/LocationProvider.php";

        $privacyConfig = new PrivacyManagerConfig();

        $ipAddress = IP::N2P($privacyConfig->useAnonymizedIpForVisitEnrichment ? $visitorInfo['location_ip'] : $request->getIp());
        $userInfo = array(
            'lang' => $visitorInfo['location_browser_lang'],
            'ip' => $ipAddress
        );

        $id = Common::getCurrentLocationProviderId();
        $provider = LocationProvider::getProviderById($id);
        if ($provider === false) {
            $id = DefaultProvider::ID;
            $provider = LocationProvider::getProviderById($id);
            Common::printDebug("GEO: no current location provider sent, falling back to default '$id' one.");
        }

        $location = $provider->getLocation($userInfo);

        // if we can't find a location, use default provider
        if ($location === false) {
            $defaultId = DefaultProvider::ID;
            $provider = LocationProvider::getProviderById($defaultId);
            $location = $provider->getLocation($userInfo);
            Common::printDebug("GEO: couldn't find a location with Geo Module '$id', using Default '$defaultId' provider as fallback...");
            $id = $defaultId;
        }
        Common::printDebug("GEO: Found IP $ipAddress location (provider '" . $id . "'): " . var_export($location, true));

        if (empty($location['country_code'])) { // sanity check
            $location['country_code'] = \Piwik\Tracker\Visit::UNKNOWN_CODE;
        }

        // add optional location components
        $this->updateVisitInfoWithLocation($visitorInfo, $location);
    }

    /**
     * Sets visitor info array with location info.
     *
     * @param array $visitorInfo
     * @param array $location See LocationProvider::getLocation for more info.
     */
    private function updateVisitInfoWithLocation(&$visitorInfo, $location)
    {
        static $logVisitToLowerLocationMapping = array(
            'location_country' => LocationProvider::COUNTRY_CODE_KEY,
        );

        static $logVisitToLocationMapping = array(
            'location_region'    => LocationProvider::REGION_CODE_KEY,
            'location_city'      => LocationProvider::CITY_NAME_KEY,
            'location_latitude'  => LocationProvider::LATITUDE_KEY,
            'location_longitude' => LocationProvider::LONGITUDE_KEY,
        );

        foreach ($logVisitToLowerLocationMapping as $column => $locationKey) {
            if (!empty($location[$locationKey])) {
                $visitorInfo[$column] = strtolower($location[$locationKey]);
            }
        }

        foreach ($logVisitToLocationMapping as $column => $locationKey) {
            if (!empty($location[$locationKey])) {
                $visitorInfo[$column] = $location[$locationKey];
            }
        }

        // if the location has provider/organization info, set it
        if (!empty($location[LocationProvider::ISP_KEY])) {
            $providerValue = $location[LocationProvider::ISP_KEY];

            // if the org is set and not the same as the isp, add it to the provider value
            if (!empty($location[LocationProvider::ORG_KEY])
                && $location[LocationProvider::ORG_KEY] != $providerValue
            ) {
                $providerValue .= ' - ' . $location[LocationProvider::ORG_KEY];
            }
        } else if (!empty($location[LocationProvider::ORG_KEY])) {
            $providerValue = $location[LocationProvider::ORG_KEY];
        }

        if (isset($providerValue)
            && Manager::getInstance()->isPluginInstalled('Provider')) {
            $visitorInfo['location_provider'] = $providerValue;
        }
    }

    public function getSegmentsMetadata(&$segments)
    {
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('UserCountry_Country'),
            'segment'        => 'countryCode',
            'sqlSegment'     => 'log_visit.location_country',
            'acceptedValues' => 'de, us, fr, in, es, etc.',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('UserCountry_Continent'),
            'segment'        => 'continentCode',
            'sqlSegment'     => 'log_visit.location_country',
            'acceptedValues' => 'eur, asi, amc, amn, ams, afr, ant, oce',
            'sqlFilter'      => __NAMESPACE__ . '\UserCountry::getCountriesForContinent',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('UserCountry_Region'),
            'segment'        => 'regionCode',
            'sqlSegment'     => 'log_visit.location_region',
            'acceptedValues' => '01 02, OR, P8, etc.<br/>eg. region=A1;country=fr',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('UserCountry_City'),
            'segment'        => 'city',
            'sqlSegment'     => 'log_visit.location_city',
            'acceptedValues' => 'Sydney, Sao Paolo, Rome, etc.',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('UserCountry_Latitude'),
            'segment'        => 'latitude',
            'sqlSegment'     => 'log_visit.location_latitude',
            'acceptedValues' => '-33.578, 40.830, etc.<br/>You can select visitors within a lat/long range using &segment=lat&gt;X;lat&lt;Y;long&gt;M;long&lt;N.',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik::translate('UserCountry_Longitude'),
            'segment'        => 'longitude',
            'sqlSegment'     => 'log_visit.location_longitude',
            'acceptedValues' => '-70.664, 14.326, etc.',
        );
    }

    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions = array_merge($dimensions, array(
                                                    array('category' => Piwik::translate('General_Visit'),
                                                          'name'     => Piwik::translate('UserCountry_Country'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getCountry',
                                                    ),
                                                    array('category' => Piwik::translate('General_Visit'),
                                                          'name'     => Piwik::translate('UserCountry_Continent'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getContinent',
                                                    ),
                                                    array('category' => Piwik::translate('General_Visit'),
                                                          'name'     => Piwik::translate('UserCountry_Region'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getRegion'),
                                                    array('category' => Piwik::translate('General_Visit'),
                                                          'name'     => Piwik::translate('UserCountry_City'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getCity'),
                                               ));
    }

    /**
     * Returns a list of country codes for a given continent code.
     *
     * @param string $continent The continent code.
     * @return array
     */
    public static function getCountriesForContinent($continent)
    {
        $result = array();
        $continent = strtolower($continent);
        foreach (Common::getCountriesList() as $countryCode => $continentCode) {
            if ($continent == $continentCode) {
                $result[] = $countryCode;
            }
        }
        return array('SQL'  => "'" . implode("', '", $result) . "', ?",
                     'bind' => '-'); // HACK: SegmentExpression requires a $bind, even if there's nothing to bind
    }

    /**
     * Returns true if a GeoIP provider is installed & working, false if otherwise.
     *
     * @return bool
     */
    public function isGeoIPWorking()
    {
        $provider = LocationProvider::getCurrentProvider();
        return $provider instanceof GeoIp
        && $provider->isAvailable() === true
        && $provider->isWorking() === true;
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "UserCountry_FatalErrorDuringDownload";
        $translationKeys[] = "UserCountry_SetupAutomaticUpdatesOfGeoIP";
        $translationKeys[] = "General_Done";
    }

    public static function isGeoLocationAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_geolocation_admin'];
    }

}
