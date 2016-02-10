<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\ArchiveProcessor;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
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
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Tracker.setTrackerCacheGeneral'         => 'setTrackerCacheGeneral',
            'Insights.addReportToOverview'           => 'addReportToInsightsOverview',
            'Live.getAllVisitorDetails'              => 'extendVisitorDetails'
        );
    }

    public function extendVisitorDetails(&$visitor, $details)
    {
        $instance = new Visitor($details);

        $visitor['continent']     = $instance->getContinent();
        $visitor['continentCode'] = $instance->getContinentCode();
        $visitor['country']       = $instance->getCountryName();
        $visitor['countryCode']   = $instance->getCountryCode();
        $visitor['countryFlag']   = $instance->getCountryFlag();
        $visitor['region']        = $instance->getRegionName();
        $visitor['regionCode']    = $instance->getRegionCode();
        $visitor['city']          = $instance->getCityName();
        $visitor['location']      = $instance->getPrettyLocation();
        $visitor['latitude']      = $instance->getLatitude();
        $visitor['longitude']     = $instance->getLongitude();
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
    }

    public static function isGeoLocationAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_geolocation_admin'];
    }

}
