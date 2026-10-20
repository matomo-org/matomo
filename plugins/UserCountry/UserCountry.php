<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry;

use Piwik\Columns\Dimension;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
use Piwik\Segment\SegmentExpression;

class UserCountry extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'Tracker.setTrackerCacheGeneral'         => 'setTrackerCacheGeneral',
            'Insights.addReportToOverview'           => 'addReportToInsightsOverview',
            'Metrics.getDefaultMetricSemanticTypes'  => 'addMetricSemanticTypes',
        );
    }

    /**
     * @param array<string, string> $types
     */
    public function addMetricSemanticTypes(array &$types): void
    {
        $types[Archiver::DISTINCT_COUNTRIES_METRIC] = Dimension::TYPE_NUMBER;
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'General_InfoFor';
        $translations[] = 'General_NotInstalled';
        $translations[] = 'General_Installed';
        $translations[] = 'General_Broken';
        $translations[] = 'UserCountry_CurrentLocationIntro';
        $translations[] = 'General_Refresh';
        $translations[] = 'UserCountry_CannotLocalizeLocalIP';
        $translations[] = 'UserCountry_NoProviders';
        $translations[] = 'General_Disabled';
        $translations[] = 'UserCountry_GeolocationPageDesc';
        $translations[] = 'UserCountry_LocationProvider';
        $translations[] = 'UserCountry_Geolocation';
        $translations[] = 'UserCountry_DistinctCountries';
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
    }

    /**
     * Returns a list of country codes for a given continent code.
     *
     * @param string $continent The continent code.
     * @param string|null $sqlField The segment's SQL field, passed by the segment and unused.
     * @param string|null $matchType The segment operator, see {@link SegmentExpression}.
     * @return array
     */
    public static function getCountriesForContinent($continent, $sqlField = null, $matchType = null)
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
                     'bind' => '-', // HACK: SegmentExpression requires a $bind, even if there's nothing to bind
                     'negate' => in_array($matchType, [SegmentExpression::MATCH_NOT_EQUAL, SegmentExpression::MATCH_DOES_NOT_CONTAIN], true));
    }

    /**
     * Returns true if a GeoIP provider is installed & working, false if otherwise.
     *
     * @return bool
     */
    public function isGeoIPWorking()
    {
        $provider = LocationProvider::getCurrentProvider();
        return $provider instanceof GeoIp2
        && $provider->isAvailable() === true
        && $provider->isWorking() === true;
    }

    public static function isGeoLocationAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_geolocation_admin'];
    }
}
