<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

/**
 *
 */
class UserCountry extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Tracker.setTrackerCacheGeneral'         => 'setTrackerCacheGeneral',
            'Insights.addReportToOverview'           => 'addReportToInsightsOverview',
        );
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
        $jsFiles[] = "plugins/UserCountry/angularjs/location-provider-selection/location-provider-selection.controller.js";
        $jsFiles[] = "plugins/UserCountry/angularjs/location-provider-selection/location-provider-selection.directive.js";
        $jsFiles[] = "plugins/UserCountry/angularjs/location-provider-updater/location-provider-updater.controller.js";
        $jsFiles[] = "plugins/UserCountry/angularjs/location-provider-updater/location-provider-updater.directive.js";
    }

    /**
     * Returns a list of country codes for a given continent code.
     *
     * @param string $continent The continent code.
     * @return array
     */
    public static function getCountriesForContinent($continent)
    {
        /** @var RegionDataProvider $regionDataProvider */
        $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');

        $result = array();
        $continent = strtolower($continent);
        foreach ($regionDataProvider->getCountryList() as $countryCode => $continentCode) {
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
        $translationKeys[] = "General_Save";
        $translationKeys[] = "General_Continue";
    }

    public static function isGeoLocationAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_geolocation_admin'];
    }

}
