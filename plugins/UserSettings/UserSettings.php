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

    /*
     * Mapping between the browser family shortcode and the displayed name
     */
    static public $browserType_display = array(
        'ie'     => 'Trident (IE)',
        'gecko'  => 'Gecko (Firefox)',
        'khtml'  => 'KHTML (Konqueror)',
        'webkit' => 'WebKit (Safari, Chrome)',
        'opera'  => 'Presto (Opera)',
    );

    /*
     * Defines API reports.
     * Also used to define Widgets.
     *
     * @array Category, Report Name, API Module, API action, Translated column name,
     * 			$segment, $sqlSegment, $acceptedValues, $sqlFilter
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

    /*
     * List of hooks
     */
    function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessing_Day.compute'    => 'archiveDay',
            'ArchiveProcessing_Period.compute' => 'archivePeriod',
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenu',
            'API.getReportMetadata'            => 'getReportMetadata',
            'API.getSegmentsMetadata'          => 'getSegmentsMetadata',
        );
        return $hooks;
    }

    /*
     * Registers reports metadata
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getReportMetadata($notification)
    {
        $reports = & $notification->getNotificationObject();

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
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getSegmentsMetadata($notification)
    {
        $segments =& $notification->getNotificationObject();
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
     *
     * @param Piwik_Event_Notification $notification  notification object
     * @return void
     */
    function archiveDay($notification)
    {
        require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';
        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];
        $columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;

        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $this->archiveProcessing = $archiveProcessing;

        $recordName = 'UserSettings_configuration';
        $labelSQL = "CONCAT(log_visit.config_os, ';', log_visit.config_browser_name, ';', log_visit.config_resolution)";
        $interestByConfiguration = $archiveProcessing->getArrayInterestForLabel($labelSQL);

        $tableConfiguration = $archiveProcessing->getDataTableFromArray($interestByConfiguration);
        $archiveProcessing->insertBlobRecord($recordName, $tableConfiguration->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
        destroy($tableConfiguration);

        $recordName = 'UserSettings_os';
        $labelSQL = "log_visit.config_os";
        $interestByOs = $archiveProcessing->getArrayInterestForLabel($labelSQL);
        $tableOs = $archiveProcessing->getDataTableFromArray($interestByOs);
        $archiveProcessing->insertBlobRecord($recordName, $tableOs->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
        destroy($tableOs);

        $recordName = 'UserSettings_browser';
        $labelSQL = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
        $interestByBrowser = $archiveProcessing->getArrayInterestForLabel($labelSQL);
        $tableBrowser = $archiveProcessing->getDataTableFromArray($interestByBrowser);
        $archiveProcessing->insertBlobRecord($recordName, $tableBrowser->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));

        $recordName = 'UserSettings_browserType';
        $tableBrowserType = $this->getTableBrowserByType($tableBrowser);
        $archiveProcessing->insertBlobRecord($recordName, $tableBrowserType->getSerialized());
        destroy($tableBrowser);
        destroy($tableBrowserType);

        $recordName = 'UserSettings_resolution';
        $labelSQL = "log_visit.config_resolution";
        $interestByResolution = $archiveProcessing->getArrayInterestForLabel($labelSQL);
        $tableResolution = $archiveProcessing->getDataTableFromArray($interestByResolution);
        $tableResolution->filter('ColumnCallbackDeleteRow', array('label', 'Piwik_UserSettings_keepStrlenGreater'));
        $archiveProcessing->insertBlobRecord($recordName, $tableResolution->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));

        $recordName = 'UserSettings_wideScreen';
        $tableWideScreen = $this->getTableWideScreen($tableResolution);
        $archiveProcessing->insertBlobRecord($recordName, $tableWideScreen->getSerialized());
        destroy($tableResolution);
        destroy($tableWideScreen);

        $recordName = 'UserSettings_plugin';
        $tablePlugin = $this->getDataTablePlugin();
        $archiveProcessing->insertBlobRecord($recordName, $tablePlugin->getSerialized());
        destroy($tablePlugin);

        $recordName = 'UserSettings_language';
        $tableLanguage = $this->getDataTableLanguages();
        $archiveProcessing->insertBlobRecord($recordName, $tableLanguage->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
    }

    /**
     * Period archiving: simply sums up daily archives
     *
     * @param Piwik_Event_Notification $notification  notification object
     * @return void
     */
    function archivePeriod($notification)
    {
        $archiveProcessing = $notification->getNotificationObject();

        if (!$archiveProcessing->shouldProcessReportsForPlugin($this->getPluginName())) return;

        $maximumRowsInDataTable = Piwik_Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];

        $dataTableToSum = array(
            'UserSettings_configuration',
            'UserSettings_os',
            'UserSettings_browser',
            'UserSettings_browserType',
            'UserSettings_resolution',
            'UserSettings_wideScreen',
            'UserSettings_plugin',
            'UserSettings_language',
        );

        $archiveProcessing->archiveDataTable($dataTableToSum, null, $maximumRowsInDataTable);
    }

    /**
     * Returns the report Visits by Screen type given the Resolution table
     *
     * @param Piwik_DataTable $tableResolution
     * @return Piwik_DataTable
     */
    protected function getTableWideScreen(Piwik_DataTable $tableResolution)
    {
        $nameToRow = array();
        foreach ($tableResolution->getRows() as $row) {
            $resolution = $row->getColumn('label');
            $name = Piwik_getScreenTypeFromResolution($resolution);
            if (!isset($nameToRow[$name])) {
                $nameToRow[$name] = new Piwik_DataTable_Row();
                $nameToRow[$name]->addColumn('label', $name);
            }

            $nameToRow[$name]->sumRow($row);
        }
        $tableWideScreen = new Piwik_DataTable();
        $tableWideScreen->addRowsFromArray($nameToRow);

        return $tableWideScreen;
    }

    /**
     * Returns the report Visits by Browser family given the Browser report
     *
     * @param Piwik_DataTable $tableBrowser
     * @return Piwik_DataTable
     */
    protected function getTableBrowserByType(Piwik_DataTable $tableBrowser)
    {
        $nameToRow = array();
        foreach ($tableBrowser->getRows() as $row) {
            $browserLabel = $row->getColumn('label');
            $familyNameToUse = Piwik_getBrowserFamily($browserLabel);
            if (!isset($nameToRow[$familyNameToUse])) {
                $nameToRow[$familyNameToUse] = new Piwik_DataTable_Row();
                $nameToRow[$familyNameToUse]->addColumn('label', $familyNameToUse);
            }
            $nameToRow[$familyNameToUse]->sumRow($row);
        }

        $tableBrowserType = new Piwik_DataTable();
        $tableBrowserType->addRowsFromArray($nameToRow);
        return $tableBrowserType;
    }

    /**
     * Returns SQL that processes stats for Plugins
     *
     * @return Piwik_DataTable_Simple
     */
    protected function getDataTablePlugin()
    {
        $toSelect = "sum(case log_visit.config_pdf when 1 then 1 else 0 end) as pdf,
				sum(case log_visit.config_flash when 1 then 1 else 0 end) as flash,
				sum(case log_visit.config_java when 1 then 1 else 0 end) as java,
				sum(case log_visit.config_director when 1 then 1 else 0 end) as director,
				sum(case log_visit.config_quicktime when 1 then 1 else 0 end) as quicktime,
				sum(case log_visit.config_realplayer when 1 then 1 else 0 end) as realplayer,
				sum(case log_visit.config_windowsmedia when 1 then 1 else 0 end) as windowsmedia,
				sum(case log_visit.config_gears when 1 then 1 else 0 end) as gears,
				sum(case log_visit.config_silverlight when 1 then 1 else 0 end) as silverlight,
				sum(case log_visit.config_cookie when 1 then 1 else 0 end) as cookie	";
        return $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, Piwik_Archive::INDEX_NB_VISITS);
    }

    protected function getDataTableLanguages()
    {
        $labelSQL = "log_visit.location_browser_lang";
        $interestByLanguage = $this->archiveProcessing->getArrayInterestForLabel($labelSQL);

        $languageCodes = array_keys(Piwik_Common::getLanguagesList());

        foreach ($interestByLanguage as $lang => $count) {
            // get clean language code
            $code = Piwik_Common::extractLanguageCodeFromBrowserLanguage($lang, $languageCodes);
            if ($code != $lang) {
                if (!array_key_exists($code, $interestByLanguage)) {
                    $interestByLanguage[$code] = array();
                }
                // Add the values to the primary language
                foreach ($count as $key => $value) {
                    if (array_key_exists($key, $interestByLanguage[$code])) {
                        $interestByLanguage[$code][$key] += $value;
                    } else {
                        $interestByLanguage[$code][$key] = $value;
                    }
                }
                unset($interestByLanguage[$lang]);
            }
        }
        $tableLanguage = $this->archiveProcessing->getDataTableFromArray($interestByLanguage);
        return $tableLanguage;
    }
}
