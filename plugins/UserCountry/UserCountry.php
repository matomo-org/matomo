<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 * @see plugins/UserCountry/GeoIPAutoUpdater.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/GeoIPAutoUpdater.php';

/**
 *
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('UserCountry_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
            'TrackerPlugin'   => true,
        );
        return $info;
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenu',
            'AdminMenu.add'                    => 'addAdminMenu',
            'Goals.getReportsWithGoalMetrics'  => 'getReportsWithGoalMetrics',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
            'AssetManager.getCssFiles'         => 'getCssFiles',
            'AssetManager.getJsFiles'          => 'getJsFiles',
            'Tracker.getVisitorLocation'       => 'getVisitorLocation',
            'TaskScheduler.getScheduledTasks'  => 'getScheduledTasks',
        );
        return $hooks;
    }

    public function getScheduledTasks(&$tasks)
    {
        // add the auto updater task
        $tasks[] = Piwik_UserCountry_GeoIPAutoUpdater::makeScheduledTask();
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/UserCountry/stylesheets/userCountry.css";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserCountry/javascripts/userCountry.js";
    }

    public function getVisitorLocation(&$location, $visitorInfo)
    {
        require_once PIWIK_INCLUDE_PATH . "/plugins/UserCountry/LocationProvider.php";

        $id = Piwik_Common::getCurrentLocationProviderId();
        $provider = Piwik_UserCountry_LocationProvider::getProviderById($id);
        if ($provider === false) {
            $id = Piwik_UserCountry_LocationProvider_Default::ID;
            $provider = Piwik_UserCountry_LocationProvider::getProviderById($id);
            printDebug("GEO: no current location provider sent, falling back to default '$id' one.");
        }

        $location = $provider->getLocation($visitorInfo);

        // if we can't find a location, use default provider
        if ($location === false) {
            $defaultId = Piwik_UserCountry_LocationProvider_Default::ID;
            $provider = Piwik_UserCountry_LocationProvider::getProviderById($defaultId);
            $location = $provider->getLocation($visitorInfo);
            printDebug("GEO: couldn't find a location with Geo Module '$id', using Default '$defaultId' provider as fallback...");
            $id = $defaultId;
        }
        printDebug("GEO: Found IP location (provider '" . $id . "'): " . var_export($location, true));
    }

    function addWidgets()
    {
        $widgetContinentLabel = Piwik_Translate('UserCountry_WidgetLocation')
            . ' (' . Piwik_Translate('UserCountry_Continent') . ')';
        $widgetCountryLabel = Piwik_Translate('UserCountry_WidgetLocation')
            . ' (' . Piwik_Translate('UserCountry_Country') . ')';
        $widgetRegionLabel = Piwik_Translate('UserCountry_WidgetLocation')
            . ' (' . Piwik_Translate('UserCountry_Region') . ')';
        $widgetCityLabel = Piwik_Translate('UserCountry_WidgetLocation')
            . ' (' . Piwik_Translate('UserCountry_City') . ')';

        Piwik_AddWidget('General_Visitors', $widgetContinentLabel, 'UserCountry', 'getContinent');
        Piwik_AddWidget('General_Visitors', $widgetCountryLabel, 'UserCountry', 'getCountry');
        Piwik_AddWidget('General_Visitors', $widgetRegionLabel, 'UserCountry', 'getRegion');
        Piwik_AddWidget('General_Visitors', $widgetCityLabel, 'UserCountry', 'getCity');
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'UserCountry_SubmenuLocations', array('module' => 'UserCountry', 'action' => 'index'));
    }

    /**
     * Event handler. Adds menu items to the Admin menu.
     */
    function addAdminMenu()
    {
        Piwik_AddAdminSubMenu('General_Settings', 'UserCountry_Geolocation',
            array('module' => 'UserCountry', 'action' => 'adminIndex'),
            Piwik::isUserIsSuperUser(),
            $order = 8);
    }

    public function getSegmentsMetadata(&$segments)
    {
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('UserCountry_Country'),
            'segment'        => 'countryCode',
            'sqlSegment'     => 'log_visit.location_country',
            'acceptedValues' => 'de, us, fr, in, es, etc.',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('UserCountry_Continent'),
            'segment'        => 'continentCode',
            'sqlSegment'     => 'log_visit.location_country',
            'acceptedValues' => 'eur, asi, amc, amn, ams, afr, ant, oce',
            'sqlFilter'      => array('Piwik_UserCountry', 'getCountriesForContinent'),
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('UserCountry_Region'),
            'segment'        => 'regionCode',
            'sqlSegment'     => 'log_visit.location_region',
            'acceptedValues' => '01 02, OR, P8, etc.<br/>eg. region=A1;country=fr',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('UserCountry_City'),
            'segment'        => 'city',
            'sqlSegment'     => 'log_visit.location_city',
            'acceptedValues' => 'Sydney, Sao Paolo, Rome, etc.',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('UserCountry_Latitude'),
            'segment'        => 'latitude',
            'sqlSegment'     => 'log_visit.location_latitude',
            'acceptedValues' => '-33.578, 40.830, etc.<br/>You can select visitors within a lat/long range using &segment=lat&gt;X;lat&lt;Y;long&gt;M;long&lt;N.',
        );
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'Visit Location',
            'name'           => Piwik_Translate('UserCountry_Longitude'),
            'segment'        => 'longitude',
            'sqlSegment'     => 'log_visit.location_longitude',
            'acceptedValues' => '-70.664, 14.326, etc.',
        );
    }

    public function getReportMetadata(&$reports)
    {
        $metrics = array(
            'nb_visits'        => Piwik_Translate('General_ColumnNbVisits'),
            'nb_uniq_visitors' => Piwik_Translate('General_ColumnNbUniqVisitors'),
            'nb_actions'       => Piwik_Translate('General_ColumnNbActions'),
        );

        $reports[] = array(
            'category'  => Piwik_Translate('General_Visitors'),
            'name'      => Piwik_Translate('UserCountry_Country'),
            'module'    => 'UserCountry',
            'action'    => 'getCountry',
            'dimension' => Piwik_Translate('UserCountry_Country'),
            'metrics'   => $metrics,
            'order'     => 5,
        );

        $reports[] = array(
            'category'  => Piwik_Translate('General_Visitors'),
            'name'      => Piwik_Translate('UserCountry_Continent'),
            'module'    => 'UserCountry',
            'action'    => 'getContinent',
            'dimension' => Piwik_Translate('UserCountry_Continent'),
            'metrics'   => $metrics,
            'order'     => 6,
        );

        $reports[] = array(
            'category'  => Piwik_Translate('General_Visitors'),
            'name'      => Piwik_Translate('UserCountry_Region'),
            'module'    => 'UserCountry',
            'action'    => 'getRegion',
            'dimension' => Piwik_Translate('UserCountry_Region'),
            'metrics'   => $metrics,
            'order'     => 7,
        );

        $reports[] = array(
            'category'  => Piwik_Translate('General_Visitors'),
            'name'      => Piwik_Translate('UserCountry_City'),
            'module'    => 'UserCountry',
            'action'    => 'getCity',
            'dimension' => Piwik_Translate('UserCountry_City'),
            'metrics'   => $metrics,
            'order'     => 8,
        );
    }

    public function getReportsWithGoalMetrics(&$dimensions)
    {
        $dimensions = array_merge($dimensions, array(
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('UserCountry_Country'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getCountry',
                                                    ),
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('UserCountry_Continent'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getContinent',
                                                    ),
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('UserCountry_Region'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getRegion'),
                                                    array('category' => Piwik_Translate('General_Visit'),
                                                          'name'     => Piwik_Translate('UserCountry_City'),
                                                          'module'   => 'UserCountry',
                                                          'action'   => 'getCity'),
                                               ));
    }

    public function archivePeriod(Piwik_ArchiveProcessor_Period $archiveProcessor)
    {
        $archiving = new Piwik_UserCountry_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay(Piwik_ArchiveProcessor_Day $archiveProcessor)
    {
        $archiving = new Piwik_UserCountry_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
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
        foreach (Piwik_Common::getCountriesList() as $countryCode => $continentCode) {
            if ($continent == $continentCode) {
                $result[] = $countryCode;
            }
        }
        return array('SQL'  => "'" . implode("', '", $result) . "', ?",
                     'bind' => '-'); // HACK: SegmentExpression requires a $bind, even if there's nothing to bind
    }
}
