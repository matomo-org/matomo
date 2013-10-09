<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package UserSettings
 */
namespace Piwik\Plugins\UserSettings;

use Piwik\ArchiveProcessor;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\WidgetsList;

/**
 *
 * @package UserSettings
 */
class UserSettings extends \Piwik\Plugin
{
    /**
     * Mapping between the browser family shortcode and the displayed name
     *
     * @type array
     */
    static public $browserType_display = array(
        'ie'     => 'Trident (IE)',
        'gecko'  => 'Gecko (Firefox)',
        'khtml'  => 'KHTML (Konqueror)',
        'webkit' => 'WebKit (Safari, Chrome)',
        'opera'  => 'Presto (Opera)',
    );

    /**
     * Defines API reports.
     * Also used to define Widgets.
     *
     * @type array
     *
     * Category, Report Name, API Module, API action, Translated column name,
     * $segment, $sqlSegment, $acceptedValues, $sqlFilter
     */
    protected $reportMetadata = array(
        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetResolutions',
              'UserSettings',
              'getResolution',
              'UserSettings_ColumnResolution',
              'resolution',
              'log_visit.config_resolution',
              '1280x1024, 800x600, etc.',
              null,),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetBrowsers',
              'UserSettings',
              'getBrowser',
              'UserSettings_ColumnBrowser',
              'browserCode',
              'log_visit.config_browser_name',
              'FF, IE, CH, SF, OP, etc.',
              null,),

        // browser version
        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetBrowserVersion',
              'UserSettings',
              'getBrowserVersion',
              'UserSettings_ColumnBrowserVersion',
              'browserVersion',
              'log_visit.config_browser_version',
              '1.0, 8.0, etc.',
              null,),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetBrowserFamilies',
              'UserSettings',
              'getBrowserType',
              'UserSettings_ColumnBrowserFamily',
              null,
              null,
              null,
              null,),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetPlugins',
              'UserSettings',
              'getPlugin',
              'General_Plugin',
              null,
              null,
              null,
              null,),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetWidescreen',
              'UserSettings',
              'getWideScreen',
              'UserSettings_ColumnTypeOfScreen',
              null,
              null,
              null,
              null,),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetOperatingSystems',
              'UserSettings',
              'getOS',
              'UserSettings_ColumnOperatingSystem',
              'operatingSystemCode',
              'log_visit.config_os',
              'WXP, WI7, MAC, LIN, AND, IPD, etc.',
              null,),

        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetGlobalVisitors',
              'UserSettings',
              'getConfiguration',
              'UserSettings_ColumnConfiguration',
              null,
              null,
              null,
              null),

        // operating system family
        array('UserSettings_VisitorSettings',
              'UserSettings_WidgetOperatingSystemFamily',
              'UserSettings',
              'getOSFamily',
              'UserSettings_OperatingSystemFamily',
              null,
              null,
              null,
              null),

        // device type
        array('UserSettings_VisitorSettings',
              'UserSettings_MobileVsDesktop',
              'UserSettings',
              'getMobileVsDesktop',
              'UserSettings_MobileVsDesktop',
              null,
              null,
              null,
              null),

        // Browser language
        array('UserSettings_VisitorSettings',
              'UserSettings_BrowserLanguage',
              'UserSettings',
              'getLanguage',
              'General_Language',
              null,
              null,
              null,
              null),
    );

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
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
        return $hooks;
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['UserSettings.getResolution'] = $this->getDisplayPropertiesForGetResolution();
        $properties['UserSettings.getConfiguration'] = $this->getDisplayPropertiesForGetConfiguration();
        $properties['UserSettings.getOS'] = $this->getDisplayPropertiesForGetOS();
        $properties['UserSettings.getOSFamily'] = $this->getDisplayPropertiesForGetOSFamily();
        $properties['UserSettings.getBrowserVersion'] = $this->getDisplayPropertiesForGetBrowserVersion();
        $properties['UserSettings.getBrowser'] = $this->getDisplayPropertiesForGetBrowser();
        $properties['UserSettings.getBrowserType'] = $this->getDisplayPropertiesForGetBrowserType();
        $properties['UserSettings.getWideScreen'] = $this->getDisplayPropertiesForGetWideScreen();
        $properties['UserSettings.getMobileVsDesktop'] = $this->getDisplayPropertiesForGetMobileVsDesktop();
        $properties['UserSettings.getPlugin'] = $this->getDisplayPropertiesForGetPlugin();
        $properties['UserSettings.getLanguage'] = $this->getDisplayPropertiesForGetLanguage();
    }

    private function getDisplayPropertiesForGetResolution()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations' => array('label' => Piwik::translate('UserSettings_ColumnResolution'))
                                                                           ));
    }

    private function getDisplayPropertiesForGetConfiguration()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'filter_limit' => 3,
                                                                                'translations' => array('label' => Piwik::translate('UserSettings_ColumnConfiguration'))
                                                                           ));
    }

    private function getDisplayPropertiesForGetOS()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations'    => array('label' => Piwik::translate('UserSettings_ColumnOperatingSystem')),
                                                                                'title'           => Piwik::translate('UserSettings_OperatingSystems'),
                                                                                'related_reports' => $this->getOsRelatedReports()
                                                                           ));
    }

    private function getDisplayPropertiesForGetOSFamily()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations'    => array('label' => Piwik::translate('UserSettings_OperatingSystemFamily')),
                                                                                'title'           => Piwik::translate('UserSettings_OperatingSystemFamily'),
                                                                                'related_reports' => $this->getOsRelatedReports()
                                                                           ));
    }

    private function getDisplayPropertiesForGetBrowserVersion()
    {
        $result = array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                   'translations'    => array('label' => Piwik::translate('UserSettings_ColumnBrowserVersion')),
                                                                                   'title'           => Piwik::translate('UserSettings_ColumnBrowserVersion'),
                                                                                   'related_reports' => $this->getBrowserRelatedReports()
                                                                              ));
        $result['visualization_properties']['graph']['max_graph_elements'] = 7;
        return $result;
    }

    private function getDisplayPropertiesForGetBrowser()
    {
        $result = array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                   'translations'    => array('label' => Piwik::translate('UserSettings_ColumnBrowser')),
                                                                                   'title'           => Piwik::translate('UserSettings_Browsers'),
                                                                                   'related_reports' => $this->getBrowserRelatedReports()
                                                                              ));
        $result['visualization_properties']['graph']['max_graph_elements'] = 7;
        return $result;
    }

    private function getDisplayPropertiesForGetBrowserType()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations'            => array('label' => Piwik::translate('UserSettings_ColumnBrowserFamily')),
                                                                                'show_offset_information' => false,
                                                                                'show_pagination_control' => false,
                                                                                'show_limit_control'      => false,
                                                                                'default_view_type'       => 'graphPie',
                                                                           ));
    }

    private function getDisplayPropertiesForGetWideScreen()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations'            => array('label' => Piwik::translate('UserSettings_ColumnTypeOfScreen')),
                                                                                'show_offset_information' => false,
                                                                                'show_pagination_control' => false,
                                                                                'show_limit_control'      => false,
                                                                                'title'                   => Piwik::translate('UserSettings_ColumnTypeOfScreen'),
                                                                                'related_reports'         => $this->getWideScreenDeviceTypeRelatedReports()
                                                                           ));
    }

    private function getDisplayPropertiesForGetMobileVsDesktop()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations'    => array('label' => Piwik::translate('UserSettings_MobileVsDesktop')),
                                                                                'title'           => Piwik::translate('UserSettings_MobileVsDesktop'),
                                                                                'related_reports' => $this->getWideScreenDeviceTypeRelatedReports()
                                                                           ));
    }

    private function getDisplayPropertiesForGetPlugin()
    {
        return array_merge($this->getBasicUserSettingsDisplayProperties(), array(
                                                                                'translations'            => array(
                                                                                    'label'                => Piwik::translate('General_Plugin'),
                                                                                    'nb_visits_percentage' =>
                                                                                        str_replace(' ', '&nbsp;', Piwik::translate('General_ColumnPercentageVisits'))
                                                                                ),
                                                                                'show_offset_information' => false,
                                                                                'show_pagination_control' => false,
                                                                                'show_limit_control'      => false,
                                                                                'show_all_views_icons'    => false,
                                                                                'show_table_all_columns'  => false,
                                                                                'columns_to_display'      => array('label', 'nb_visits_percentage', 'nb_visits'),
                                                                                'filter_sort_column'      => 'nb_visits_percentage',
                                                                                'filter_sort_order'       => 'desc',
                                                                                'filter_limit'            => 10,
                                                                                'show_footer_message'     => Piwik::translate('UserSettings_PluginDetectionDoesNotWorkInIE'),
                                                                           ));
    }

    private function getDisplayPropertiesForGetLanguage()
    {
        return array(
            'translations'                => array('label' => Piwik::translate('General_Language')),
            'filter_sort_column'          => 'nb_visits',
            'filter_sort_order'           => 'desc',
            'show_search'                 => false,
            'columns_to_display'          => array('label', 'nb_visits'),
            'show_exclude_low_population' => false,
        );
    }

    private function getWideScreenDeviceTypeRelatedReports()
    {
        return array(
            'UserSettings.getMobileVsDesktop' => Piwik::translate('UserSettings_MobileVsDesktop'),
            'UserSettings.getWideScreen'      => Piwik::translate('UserSettings_ColumnTypeOfScreen')
        );
    }

    private function getBrowserRelatedReports()
    {
        return array(
            'UserSettings.getBrowser'        => Piwik::translate('UserSettings_Browsers'),
            'UserSettings.getBrowserVersion' => Piwik::translate('UserSettings_ColumnBrowserVersion')
        );
    }

    private function getOsRelatedReports()
    {
        return array(
            'UserSettings.getOSFamily' => Piwik::translate('UserSettings_OperatingSystemFamily'),
            'UserSettings.getOS'       => Piwik::translate('UserSettings_OperatingSystems')
        );
    }

    private function getBasicUserSettingsDisplayProperties()
    {
        return array(
            'show_search'                 => false,
            'show_exclude_low_population' => false,
            'filter_limit'                => 5,
            'visualization_properties'    => array(
                'graph' => array(
                    'max_graph_elements' => 5
                )
            )
        );
    }

    /**
     * Registers reports metadata
     *
     * @param array $reports
     */
    public function getReportMetadata(&$reports)
    {
        $i = 0;
        foreach ($this->reportMetadata as $report) {
            list($category, $name, $apiModule, $apiAction, $columnName) = $report;
            if ($category == false) continue;

            $report = array(
                'category'  => Piwik::translate($category),
                'name'      => Piwik::translate($name),
                'module'    => $apiModule,
                'action'    => $apiAction,
                'dimension' => Piwik::translate($columnName),
                'order'     => $i++
            );

            $translation = $name . 'Documentation';
            $translated = Piwik::translate($translation, '<br />');
            if ($translated != $translation) {
                $report['documentation'] = $translated;
            }

            // getPlugin returns only a subset of metrics
            if ($apiAction == 'getPlugin') {
                $report['metrics'] = array(
                    'nb_visits',
                    'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits')
                );
                // There is no processedMetrics for this report
                $report['processedMetrics'] = array();
                // Always has same number of rows, 1 per plugin
                $report['constantRowsCount'] = true;
            }
            $reports[] = $report;
        }
    }

    /**
     * Get segments meta data
     */
    public function getSegmentsMetadata(&$segments)
    {
        foreach ($this->reportMetadata as $report) {
            @list($category, $name, $apiModule, $apiAction, $columnName, $segment, $sqlSegment, $acceptedValues, $sqlFilter) = $report;
            if (empty($segment)) continue;
            $segments[] = array(
                'type'           => 'dimension',
                'category'       => Piwik::translate('General_Visit'),
                'name'           => $columnName,
                'segment'        => $segment,
                'acceptedValues' => $acceptedValues,
                'sqlSegment'     => $sqlSegment,
                'sqlFilter'      => isset($sqlFilter) ? $sqlFilter : false,
            );
        }
    }

    /**
     * Adds the various User Settings widgets
     */
    function addWidgets()
    {
        // in this case, Widgets have same names as API reports
        foreach ($this->reportMetadata as $report) {
            list($category, $name, $controllerName, $controllerAction) = $report;
            if ($category == false) continue;
            WidgetsList::add($category, $name, $controllerName, $controllerAction);
        }
    }

    /**
     * Adds the User Settings menu
     */
    function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', 'General_Settings', array('module' => 'UserSettings', 'action' => 'index'));
    }

    /**
     * Daily archive of User Settings report. Processes reports for Visits by Resolution,
     * by Browser, Browser family, etc. Some reports are built from the logs, some reports
     * are superset of an existing report (eg. Browser family is built from the Browser report)
     */
    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }
}