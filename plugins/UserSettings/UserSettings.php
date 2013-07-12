<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

/**
 *
 * @package Piwik_UserSettings
 */
class Piwik_UserSettings extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('UserSettings_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

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
              'UserSettings_ColumnPlugin',
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
     * returns list of hooks
     *
     * @return array
     */
    function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'            => 'archiveDay',
            'ArchiveProcessing_Period.compute'         => 'archivePeriod',
            'WidgetsList.add'                          => 'addWidgets',
            'Menu.add'                                 => 'addMenu',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'API.getSegmentsMetadata'                  => 'getSegmentsMetadata',
            'ViewDataTable.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
        return $hooks;
    }
    
    public function getReportDisplayProperties(&$properties, $apiAction)
    {
        $basicUserSettingsProperties = array('show_search'                 => false,
                                             'show_exclude_low_population' => false,
                                             'filter_limit'                => 5,
                                             'graph_limit'                 => 5);
        
        $osRelatedReports = array(
            'UserSettings.getOSFamily' => Piwik_Translate('UserSettings_OperatingSystemFamily'),
            'UserSettings.getOS'       => Piwik_Translate('UserSettings_OperatingSystems')
        );
        
        $browserRelatedReports = array(
            'UserSettings.getBrowser' => Piwik_Translate('UserSettings_Browsers'),
            'UserSettings.getBrowserVersion' => Piwik_Translate('UserSettings_ColumnBrowserVersion')
        );
        
        $wideScreenDeviceTypeRelatedReports = array(
            'UserSettings.getMobileVsDesktop' => Piwik_Translate('UserSettings_MobileVsDesktop'),
            'UserSettings.getWideScreen' => Piwik_Translate('UserSettings_ColumnTypeOfScreen')
        );
        
        $reportViewProperties = array(
            'UserSettings.getResolution' => array_merge($basicUserSettingsProperties, array(
                'translations' => array('label' => Piwik_Translate('UserSettings_ColumnResolution'))
            )),
            
            'UserSettings.getConfiguration' => array_merge($basicUserSettingsProperties, array(
                'filter_limit' => 3,
                'translations' => array('label' => Piwik_Translate('UserSettings_ColumnConfiguration'))
            )),
            
            'UserSettings.getOS' => array_merge($basicUserSettingsProperties, array(
                'translations'   => array('label' => Piwik_Translate('UserSettings_ColumnOperatingSystem')),
                'title'          => Piwik_Translate('UserSettings_OperatingSystems'),
                'relatedReports' => $osRelatedReports
            )),
            
            'UserSettings.getOSFamily' => array_merge($basicUserSettingsProperties, array(
                'translations'   => array('label' => Piwik_Translate('UserSettings_OperatingSystemFamily')),
                'title'          => Piwik_Translate('UserSettings_OperatingSystemFamily'),
                'relatedReports' => $osRelatedReports
            )),
            
            'UserSettings.getBrowserVersion' => array_merge($basicUserSettingsProperties, array(
                'translations'   => array('label' => Piwik_Translate('UserSettings_ColumnBrowserVersion')),
                'graph_limit'    => 7,
                'title'          => Piwik_Translate('UserSettings_ColumnBrowserVersion'),
                'relatedReports' => $browserRelatedReports
            )),
            
            'UserSettings.getBrowser' => array_merge($basicUserSettingsProperties, array(
                'translations'   => array('label' => Piwik_Translate('UserSettings_ColumnBrowser')),
                'graph_limit'    => 7,
                'title'          => Piwik_Translate('UserSettings_Browsers'),
                'relatedReports' => $browserRelatedReports
            )),
            
            'UserSettings.getBrowserType' => array_merge($basicUserSettingsProperties, array(
                'translations'            => array('label' => Piwik_Translate('UserSettings_ColumnBrowserFamily')),
                'show_offset_information' => false,
                'show_pagination_control' => false,
                'default_view_type'       => 'graphPie',
            )),
            
            'UserSettings.getWideScreen'  => array_merge($basicUserSettingsProperties, array(
                'translations'            => array('label' => Piwik_Translate('UserSettings_ColumnTypeOfScreen')),
                'show_offset_information' => false,
                'show_pagination_control' => false,
                'title'                   => Piwik_Translate('UserSettings_ColumnTypeOfScreen'),
                'relatedReports'          => $wideScreenDeviceTypeRelatedReports
            )),
            
            'UserSettings.getMobileVsDesktop' => array_merge($basicUserSettingsProperties, array(
                'translations'            => array('label' => Piwik_Translate('UserSettings_MobileVsDesktop')),
                'title'                   => Piwik_Translate('UserSettings_MobileVsDesktop'),
                'relatedReports'          => $wideScreenDeviceTypeRelatedReports
            )),
            
            'UserSettings.getPlugin' => array_merge($basicUserSettingsProperties, array(
                'translations'             => array(
                    'label'                => Piwik_Translate('UserSettings_ColumnPlugin'),
                    'nb_visits_percentage' =>
                        str_replace(' ', '&nbsp;', Piwik_Translate('General_ColumnPercentageVisits'))
                ),
                'show_offset_information'  => false,
                'show_pagination_control'  => false,
                'show_all_views_icons'     => false,
                'show_table_all_columns'   => false,
                'columns_to_display'       => array('label', 'nb_visits_percentage', 'nb_visits'),
                'filter_sort_column'       => 'nb_visits_percentage',
                'filter_sort_order'        => 'desc',
                'filter_limit'             => 10,
                'show_footer_message'      => Piwik_Translate('UserSettings_PluginDetectionDoesNotWorkInIE'),
            )),
            
            'UserSettings.getLanguage' => array(
                'translations'  => array('label' => Piwik_Translate('General_Language')),
                'filter_sort_column'          => 'nb_visits',
                'filter_sort_order'           => 'desc',
                'show_search'                 => false,
                'filter_limit'                => false,
                'columns_to_display'          => array('label', 'nb_visits'),
                'show_exclude_low_population' => false,
            ),
        );
        
        if (isset($reportViewProperties[$apiAction])) {
            $properties = $reportViewProperties[$apiAction];
        }
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
                'category'  => Piwik_Translate($category),
                'name'      => Piwik_Translate($name),
                'module'    => $apiModule,
                'action'    => $apiAction,
                'dimension' => Piwik_Translate($columnName),
                'order'     => $i++
            );

            $translation = $name . 'Documentation';
            $translated = Piwik_Translate($translation, '<br />');
            if ($translated != $translation) {
                $report['documentation'] = $translated;
            }

            // getPlugin returns only a subset of metrics
            if ($apiAction == 'getPlugin') {
                $report['metrics'] = array(
                    'nb_visits',
                    'nb_visits_percentage' => Piwik_Translate('General_ColumnPercentageVisits')
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
                'category'       => Piwik_Translate('General_Visit'),
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
            Piwik_AddWidget($category, $name, $controllerName, $controllerAction);
        }
    }

    /**
     * Adds the User Settings menu
     */
    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'UserSettings_SubmenuSettings', array('module' => 'UserSettings', 'action' => 'index'));
    }

    /**
     * Daily archive of User Settings report. Processes reports for Visits by Resolution,
     * by Browser, Browser family, etc. Some reports are built from the logs, some reports
     * are superset of an existing report (eg. Browser family is built from the Browser report)
     */
    public function archiveDay(Piwik_ArchiveProcessor_Day $archiveProcessor)
    {
        $archiving = new Piwik_UserSettings_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    /**
     * Period archiving: simply sums up daily archives
     */
    public function archivePeriod(Piwik_ArchiveProcessor_Period $archiveProcessor)
    {
        $archiving = new Piwik_UserSettings_Archiver($archiveProcessor);
        if($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }
}
