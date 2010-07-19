<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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
			'description' => Piwik_Translate('UserSettings_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	/*
	 * Mapping between the browser family shortcode and the displayed name
	 */
	static public $browserType_display = array(
		'ie'     => 'Trident (IE)',
		'gecko'  => 'Gecko (Firefox)',
		'khtml'  => 'KHTML (Konqueror)',
		'webkit' => 'WebKit (Safari)',
		'opera'  => 'Presto (Opera)',
	);

	/*
	 * Defines API reports. 
	 * Also used to define Widgets.
	 * 
	 * @array Category, Report Name, API Module, API action, Translated column name
	 */
	protected $reportMetadata = array(
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetResolutions', 'UserSettings', 'getResolution', 'UserSettings_ColumnResolution' ),
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetBrowsers', 'UserSettings', 'getBrowser', 'UserSettings_ColumnBrowser'),
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetPlugins', 'UserSettings', 'getPlugin', 'UserSettings_ColumnPlugin'),
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetWidescreen', 'UserSettings', 'getWideScreen', 'UserSettings_ColumnTypeOfScreen'),
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetBrowserFamilies', 'UserSettings', 'getBrowserType', 'UserSettings_ColumnBrowserFamily'),
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetOperatingSystems', 'UserSettings', 'getOS', 'UserSettings_ColumnOperatingSystem'),
		array( 'UserSettings_VisitorSettings', 'UserSettings_WidgetGlobalVisitors', 'UserSettings', 'getConfiguration', 'UserSettings_ColumnConfiguration'),
	);
	
	/*
	 * List of hooks 
	 */
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenu',
			'API.getReportMetadata' => 'getReportMetadata',
		);
		return $hooks;
	}

	/*
	 * Registers reports metadata
	 */
	public function getReportMetadata($notification) 
	{
		$reports = &$notification->getNotificationObject();
		foreach($this->reportMetadata as $report)
		{
			list( $category, $name, $apiModule, $apiAction, $columnName ) = $report;
    		$report = array(
    			'category' => Piwik_Translate($category),
    			'name' => Piwik_Translate($name),
    			'module' => $apiModule,
    			'action' => $apiAction,
    			'dimension' => Piwik_Translate($columnName),
    		);
    		
    		// getPlugin returns only a subset of metrics
    		if($apiAction == 'getPlugin')
    		{
    			$report['metrics'] = array(
    				'nb_visits',
    				'nb_visits_percentage' => Piwik_Translate('General_ColumnPercentageVisits')
    			);
    		}
    		$reports[] = $report;
		}
	}
	
	/**
	 * Adds the various User Settings widgets
	 */
	function addWidgets()
	{
		// in this case, Widgets have same names as API reports 
		foreach($this->reportMetadata as $report)
		{
			list( $category, $name, $controllerName, $controllerAction ) = extract($report);
			Piwik_AddWidget( $category, $name, $controllerName, $controllerAction );
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
	 * @param $notification
	 * @return void
	 */
	function archiveDay( $notification )
	{
		require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';
		$maximumRowsInDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_standard;
		$columnToSortByBeforeTruncation = Piwik_Archive::INDEX_NB_VISITS;
		
		$archiveProcessing = $notification->getNotificationObject();
		$this->archiveProcessing = $archiveProcessing;
			
		$recordName = 'UserSettings_configuration';
		$labelSQL = "CONCAT(config_os, ';', config_browser_name, ';', config_resolution)";
		$interestByConfiguration = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableConfiguration = $archiveProcessing->getDataTableFromArray($interestByConfiguration);
		$archiveProcessing->insertBlobRecord($recordName, $tableConfiguration->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
		destroy($tableConfiguration);
		
		$recordName = 'UserSettings_os';
		$labelSQL = "config_os";
		$interestByOs = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableOs = $archiveProcessing->getDataTableFromArray($interestByOs);
		$archiveProcessing->insertBlobRecord($recordName, $tableOs->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
		destroy($tableOs);
		
		$recordName = 'UserSettings_browser';
		$labelSQL = "CONCAT(config_browser_name, ';', config_browser_version)";
		$interestByBrowser = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableBrowser = $archiveProcessing->getDataTableFromArray($interestByBrowser);
		$archiveProcessing->insertBlobRecord($recordName, $tableBrowser->getSerialized($maximumRowsInDataTable, null, $columnToSortByBeforeTruncation));
		
		$recordName = 'UserSettings_browserType';
		$tableBrowserType = $this->getTableBrowserByType($tableBrowser);
		$archiveProcessing->insertBlobRecord($recordName, $tableBrowserType->getSerialized());
		destroy($tableBrowser);
		destroy($tableBrowserType);
		
		$recordName = 'UserSettings_resolution';
		$labelSQL = "config_resolution";
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
	}
	
	/**
	 * Period archiving: simply sums up daily archives
	 * @param $notification
	 * @return void
	 */
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		$maximumRowsInDataTable = Zend_Registry::get('config')->General->datatable_archiving_maximum_rows_standard;
		
		$dataTableToSum = array( 
				'UserSettings_configuration',
				'UserSettings_os',
				'UserSettings_browser',
				'UserSettings_browserType',
				'UserSettings_resolution',
				'UserSettings_wideScreen',
				'UserSettings_plugin',
		);
		
		$archiveProcessing->archiveDataTable($dataTableToSum, null, $maximumRowsInDataTable);
	}
	
	/**
	 * Returns the report Visits by Screen type given the Resolution table
	 * 
	 * @param $tableResolution 
	 * @return Piwik_DataTable
	 */
	protected function getTableWideScreen(Piwik_DataTable $tableResolution)
	{
		$nameToRow = array();
		foreach($tableResolution->getRows() as $row)
		{
			$resolution = $row->getColumn('label');
			$name = Piwik_getScreenTypeFromResolution($resolution);
			if(!isset($nameToRow[$name]))
			{
				$nameToRow[$name] = new Piwik_DataTable_Row();
				$nameToRow[$name]->addColumn('label', $name);
			}
			
			$nameToRow[$name]->sumRow( $row );
		}
		$tableWideScreen = new Piwik_DataTable();
		$tableWideScreen->addRowsFromArray($nameToRow);
		
		return $tableWideScreen;
	}
	
	/**
	 * Returns the report Visits by Browser family given the Browser report
	 * 
	 * @param $tableBrowser 
	 * @return Piwik_DataTable
	 */
	protected function getTableBrowserByType(Piwik_DataTable $tableBrowser)
	{		
		$nameToRow = array();
		foreach($tableBrowser->getRows() as $row)
		{
			$browserLabel = $row->getColumn('label');
			$familyNameToUse = Piwik_getBrowserFamily($browserLabel);
			if(!isset($nameToRow[$familyNameToUse]))
			{
				$nameToRow[$familyNameToUse] = new Piwik_DataTable_Row();
				$nameToRow[$familyNameToUse]->addColumn('label',$familyNameToUse);
			}
			$nameToRow[$familyNameToUse]->sumRow( $row );
		}
		
		$tableBrowserType = new Piwik_DataTable();
		$tableBrowserType->addRowsFromArray($nameToRow);
		return $tableBrowserType;
	}
	
	/**
	 * Returns SQL that processes stats for Plugins
	 * @return unknown_type
	 */
	protected function getDataTablePlugin()
	{
		$toSelect = "sum(case config_pdf when 1 then 1 else 0 end) as pdf, 
							sum(case config_flash when 1 then 1 else 0 end) as flash, 
							sum(case config_java when 1 then 1 else 0 end) as java, 
							sum(case config_director when 1 then 1 else 0 end) as director,
							sum(case config_quicktime when 1 then 1 else 0 end) as quicktime,
							sum(case config_realplayer when 1 then 1 else 0 end) as realplayer,
							sum(case config_windowsmedia when 1 then 1 else 0 end) as windowsmedia,
							sum(case config_gears when 1 then 1 else 0 end) as gears,
							sum(case config_silverlight when 1 then 1 else 0 end) as silverlight,
							sum(case config_cookie when 1 then 1 else 0 end) as cookie	";
		return $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, Piwik_Archive::INDEX_NB_VISITS);
	}
}
