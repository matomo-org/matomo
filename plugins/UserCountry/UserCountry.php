<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package UserCountry
 */
namespace Piwik\Plugins\UserCountry;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\IP;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\Plugins\UserCountry\LocationProvider\DefaultProvider;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

use Piwik\Url;
use Piwik\WidgetsList;

/**
 * @see plugins/UserCountry/GeoIPAutoUpdater.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/GeoIPAutoUpdater.php';

/**
 *
 * @package UserCountry
 */
class UserCountry extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'Menu.Reporting.addItems'                  => 'addMenu',
            'Menu.Admin.addItems'                      => 'addAdminMenu',
            'Goals.getReportsWithGoalMetrics'          => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'AssetManager.getStylesheetFiles'          => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles'          => 'getJsFiles',
            'Tracker.newVisitorInformation'            => 'getVisitorLocation',
            'TaskScheduler.getScheduledTasks'          => 'getScheduledTasks',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
            'Translate.getClientSideTranslationKeys'   => 'getClientSideTranslationKeys',
            'Tracker.setTrackerCacheGeneral'           => 'setTrackerCacheGeneral'
        );
        return $hooks;
    }

    public function setTrackerCacheGeneral(&$cache)
    {
        $cache['currentLocationProviderId'] = LocationProvider::getCurrentProviderId();
    }

    public function getScheduledTasks(&$tasks)
    {
        // add the auto updater task
        $tasks[] = GeoIPAutoUpdater::makeScheduledTask();
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/UserCountry/stylesheets/userCountry.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserCountry/javascripts/userCountry.js";
    }

    public function getVisitorLocation(&$visitorInfo, $extraInfo)
    {
        require_once PIWIK_INCLUDE_PATH . "/plugins/UserCountry/LocationProvider.php";

        $userInfo = array(
            'lang' => $visitorInfo['location_browser_lang'],
            'ip'   => IP::N2P($visitorInfo['location_ip'])
        );

        $location = array();

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
        Common::printDebug("GEO: Found IP location (provider '" . $id . "'): " . var_export($location, true));

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

        if (isset($providerValue)) {
            $visitorInfo['location_provider'] = $providerValue;
        }
    }

    public function addWidgets()
    {
        $widgetContinentLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_Continent') . ')';
        $widgetCountryLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_Country') . ')';
        $widgetRegionLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_Region') . ')';
        $widgetCityLabel = Piwik::translate('UserCountry_WidgetLocation')
            . ' (' . Piwik::translate('UserCountry_City') . ')';

        WidgetsList::add('General_Visitors', $widgetContinentLabel, 'UserCountry', 'getContinent');
        WidgetsList::add('General_Visitors', $widgetCountryLabel, 'UserCountry', 'getCountry');
        WidgetsList::add('General_Visitors', $widgetRegionLabel, 'UserCountry', 'getRegion');
        WidgetsList::add('General_Visitors', $widgetCityLabel, 'UserCountry', 'getCity');
    }

    public function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry', 'action' => 'index'));
    }

    /**
     * Event handler. Adds menu items to the MenuAdmin menu.
     */
    public function addAdminMenu()
    {
        MenuAdmin::getInstance()->add('General_Settings', 'UserCountry_Geolocation',
            array('module' => 'UserCountry', 'action' => 'adminIndex'),
            Piwik::isUserIsSuperUser(),
            $order = 8);
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

    public function getReportMetadata(&$reports)
    {
        $metrics = array(
            'nb_visits'        => Piwik::translate('General_ColumnNbVisits'),
            'nb_uniq_visitors' => Piwik::translate('General_ColumnNbUniqVisitors'),
            'nb_actions'       => Piwik::translate('General_ColumnNbActions'),
        );

        $reports[] = array(
            'category'  => Piwik::translate('General_Visitors'),
            'name'      => Piwik::translate('UserCountry_Country'),
            'module'    => 'UserCountry',
            'action'    => 'getCountry',
            'dimension' => Piwik::translate('UserCountry_Country'),
            'metrics'   => $metrics,
            'order'     => 5,
        );

        $reports[] = array(
            'category'  => Piwik::translate('General_Visitors'),
            'name'      => Piwik::translate('UserCountry_Continent'),
            'module'    => 'UserCountry',
            'action'    => 'getContinent',
            'dimension' => Piwik::translate('UserCountry_Continent'),
            'metrics'   => $metrics,
            'order'     => 6,
        );

        $reports[] = array(
            'category'  => Piwik::translate('General_Visitors'),
            'name'      => Piwik::translate('UserCountry_Region'),
            'module'    => 'UserCountry',
            'action'    => 'getRegion',
            'dimension' => Piwik::translate('UserCountry_Region'),
            'metrics'   => $metrics,
            'order'     => 7,
        );

        $reports[] = array(
            'category'  => Piwik::translate('General_Visitors'),
            'name'      => Piwik::translate('UserCountry_City'),
            'module'    => 'UserCountry',
            'action'    => 'getCity',
            'dimension' => Piwik::translate('UserCountry_City'),
            'metrics'   => $metrics,
            'order'     => 8,
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

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
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

    public function getReportDisplayProperties(&$properties)
    {
        $properties['UserCountry.getCountry'] = $this->getDisplayPropertiesForGetCountry();
        $properties['UserCountry.getContinent'] = $this->getDisplayPropertiesForGetContinent();
        $properties['UserCountry.getRegion'] = $this->getDisplayPropertiesForGetRegion();
        $properties['UserCountry.getCity'] = $this->getDisplayPropertiesForGetCity();
    }

    private function getDisplayPropertiesForGetCountry()
    {
        $result = array(
            'show_exclude_low_population' => false,
            'show_goals'                  => true,
            'filter_limit'                => 5,
            'translations'                => array('label' => Piwik::translate('UserCountry_Country')),
            'documentation'               => Piwik::translate('UserCountry_getCountryDocumentation')
        );

        if (LocationProvider::getCurrentProviderId() == DefaultProvider::ID) {
            // if we're using the default location provider, add a note explaining how it works
            $footerMessage = Piwik::translate("General_Note") . ': '
                . Piwik::translate('UserCountry_DefaultLocationProviderExplanation',
                    array('<a target="_blank" href="http://piwik.org/docs/geo-locate/">', '</a>'));

            $result['show_footer_message'] = $footerMessage;
        }

        return $result;
    }

    private function getDisplayPropertiesForGetContinent()
    {
        return array(
            'show_exclude_low_population' => false,
            'show_goals'                  => true,
            'show_search'                 => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_limit_control'          => false,
            'translations'                => array('label' => Piwik::translate('UserCountry_Continent')),
            'documentation'               => Piwik::translate('UserCountry_getContinentDocumentation')
        );
    }

    private function getDisplayPropertiesForGetRegion()
    {
        $result = array(
            'show_exclude_low_population' => false,
            'show_goals'                  => true,
            'filter_limit'                => 5,
            'translations'                => array('label' => Piwik::translate('UserCountry_Region')),
            'documentation'               => Piwik::translate('UserCountry_getRegionDocumentation') . '<br/>'
                . $this->getGeoIPReportDocSuffix()
        );
        $this->checkIfNoDataForGeoIpReport($result);
        return $result;
    }

    private function getDisplayPropertiesForGetCity()
    {
        $result = array(
            'show_exclude_low_population' => false,
            'show_goals'                  => true,
            'filter_limit'                => 5,
            'translations'                => array('label' => Piwik::translate('UserCountry_City')),
            'documentation'               => Piwik::translate('UserCountry_getCityDocumentation') . '<br/>'
                . $this->getGeoIPReportDocSuffix()
        );
        $this->checkIfNoDataForGeoIpReport($result);
        return $result;
    }

    private function getGeoIPReportDocSuffix()
    {
        return Piwik::translate('UserCountry_GeoIPDocumentationSuffix',
            array('<a target="_blank" href="http://www.maxmind.com/?rId=piwik">',
                  '</a>',
                  '<a target="_blank" href="http://www.maxmind.com/en/city_accuracy?rId=piwik">',
                  '</a>')
        );
    }

    /**
     * Checks if a datatable for a view is empty and if so, displays a message in the footer
     * telling users to configure GeoIP.
     */
    private function checkIfNoDataForGeoIpReport(&$properties)
    {
        $self = $this;
        $properties['filters'][] = function ($dataTable, $view) use ($self) {
            // if there's only one row whose label is 'Unknown', display a message saying there's no data
            if ($dataTable->getRowsCount() == 1
                && $dataTable->getFirstRow()->getColumn('label') == Piwik::translate('General_Unknown')
            ) {
                $footerMessage = Piwik::translate('UserCountry_NoDataForGeoIPReport1');

                // if GeoIP is working, don't display this part of the message
                if (!$self->isGeoIPWorking()) {
                    $params = array('module' => 'UserCountry', 'action' => 'adminIndex');
                    $footerMessage .= ' ' . Piwik::translate('UserCountry_NoDataForGeoIPReport2',
                            array('<a target="_blank" href="' . Url::getCurrentQueryStringWithParametersModified($params) . '">',
                                  '</a>',
                                  '<a target="_blank" href="http://dev.maxmind.com/geoip/geolite?rId=piwik">',
                                  '</a>'));
                } else {
                    $footerMessage .= ' ' . Piwik::translate('UserCountry_ToGeolocateOldVisits',
                            array('<a target="_blank" href="http://piwik.org/faq/how-to/#faq_167">', '</a>'));
                }

                $view->show_footer_message = $footerMessage;
            }
        };
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
    }
}
