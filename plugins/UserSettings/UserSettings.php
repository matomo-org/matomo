<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_UserSettings
 */

/**
 * @package Piwik_UserSettings
 */
class Piwik_UserSettings extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'Visitors Settings',
			'description' => 'Reports various User Settings: Browser, Browser Family, Operating System, Plugins, Resolution, Global Settings.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}

	// source: http://en.wikipedia.org/wiki/List_of_web_browsers
	static public $browserType = array(
		"ie"	=> array("IE"),
		"gecko" => array("NS", "PX", "FF", "FB", "CA", "GA", "KM", "MO", "SM"),
		"khtml" => array("SF", "KO", "OW", "CH"),
		"opera" => array("OP")
	);

	static public $browserType_display = array(
		'ie' => 'Internet Explorer',
		'gecko' => 'Gecko (Mozilla, Netscape)',
		'khtml' => 'Khtml (Konqueror, Safari)',
		'opera' => 'Opera'
	);

	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenu',
		);
		return $hooks;
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'UserSettings', 'getResolution', Piwik_Translate('UserSettings_WidgetResolutions'));
		Piwik_AddWidget( 'UserSettings', 'getBrowser', Piwik_Translate('UserSettings_WidgetBrowsers'));
		Piwik_AddWidget( 'UserSettings', 'getPlugin', Piwik_Translate('UserSettings_WidgetPlugins'));
		Piwik_AddWidget( 'UserSettings', 'getWideScreen', Piwik_Translate('UserSettings_WidgetWidescreen'));
		Piwik_AddWidget( 'UserSettings', 'getBrowserType', Piwik_Translate('UserSettings_WidgetBrowserFamilies'));
		Piwik_AddWidget( 'UserSettings', 'getOS', Piwik_Translate('UserSettings_WidgetOperatingSystems'));
		Piwik_AddWidget( 'UserSettings', 'getConfiguration', Piwik_Translate('UserSettings_WidgetGlobalVisitors'));
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'UserSettings_SubmenuSettings', array('module' => 'UserSettings'));
	}
	
	function archiveDay( $notification )
	{
		require_once "UserSettings/functions.php";
		
		$archiveProcessing = $notification->getNotificationObject();
		$this->archiveProcessing = $archiveProcessing;
			
		$recordName = 'UserSettings_configuration';
		$labelSQL = "CONCAT(config_os, ';', config_browser_name, ';', config_resolution)";
		$interestByConfiguration = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableConfiguration = $archiveProcessing->getDataTableFromArray($interestByConfiguration);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableConfiguration->getSerialized());
		
		$recordName = 'UserSettings_os';
		$labelSQL = "config_os";
		$interestByOs = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableOs = $archiveProcessing->getDataTableFromArray($interestByOs);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableOs->getSerialized());
		
		$recordName = 'UserSettings_browser';
		$labelSQL = "CONCAT(config_browser_name, ';', config_browser_version)";
		$interestByBrowser = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableBrowser = $archiveProcessing->getDataTableFromArray($interestByBrowser);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableBrowser->getSerialized());
		
		$recordName = 'UserSettings_browserType';
		$tableBrowserType = $this->getTableBrowserByType($tableBrowser);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableBrowserType->getSerialized());
		
		$recordName = 'UserSettings_resolution';
		$labelSQL = "config_resolution";
		$interestByResolution = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$tableResolution = $archiveProcessing->getDataTableFromArray($interestByResolution);
		$filter = new Piwik_DataTable_Filter_ColumnCallbackDeleteRow($tableResolution, 'label', 'Piwik_UserSettings_keepStrlenGreater');
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableResolution->getSerialized());
		
		$recordName = 'UserSettings_wideScreen';
		$tableWideScreen = $this->getTableWideScreen($tableResolution);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableWideScreen->getSerialized());
		
		$recordName = 'UserSettings_plugin';
		$tablePlugin = $this->getDataTablePlugin();
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tablePlugin->getSerialized());
	}
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'UserSettings_configuration',
				'UserSettings_os',
				'UserSettings_browser',
				'UserSettings_browserType',
				'UserSettings_resolution',
				'UserSettings_wideScreen',
				'UserSettings_plugin',
		);
		
		$archiveProcessing->archiveDataTable($dataTableToSum);
	}
	
	protected function getTableWideScreen($tableResolution)
	{
		$nameToRow = array();
		foreach($tableResolution->getRows() as $row)
		{
			$resolution = $row->getColumn('label');
			$name = Piwik_getScreenTypeFromResolution($resolution);
			if(!isset($nameToRow[$name]))
			{
				$nameToRow[$name] = new Piwik_DataTable_Row;
				$nameToRow[$name]->addColumn('label', $name);
			}
			
			$nameToRow[$name]->sumRow( $row );
		}
		$tableWideScreen = new Piwik_DataTable;
		$tableWideScreen->addRowsFromArray($nameToRow);
		
		return $tableWideScreen;
	}
	
	protected function getTableBrowserByType($tableBrowser)
	{		
		$nameToRow = array();
		foreach($tableBrowser->getRows() as $row)
		{
			$browserLabel = $row->getColumn('label');
			$familyNameToUse = Piwik_getBrowserFamily($browserLabel);
			if(!isset($nameToRow[$familyNameToUse]))
			{
				$nameToRow[$familyNameToUse] = new Piwik_DataTable_Row;
				$nameToRow[$familyNameToUse]->addColumn('label',$familyNameToUse);
			}
			$nameToRow[$familyNameToUse]->sumRow( $row );
		}
		
		$tableBrowserType = new Piwik_DataTable;
		$tableBrowserType->addRowsFromArray($nameToRow);
		return $tableBrowserType;
	}
	
	protected function getDataTablePlugin()
	{
		$toSelect = "sum(case config_pdf when 1 then 1 else 0 end) as pdf, 
							sum(case config_flash when 1 then 1 else 0 end) as flash, 
				 			sum(case config_java when 1 then 1 else 0 end) as java, 
							sum(case config_director when 1 then 1 else 0 end) as director,
				 			sum(case config_quicktime when 1 then 1 else 0 end) as quicktime, 
							sum(case config_realplayer when 1 then 1 else 0 end) as realplayer,
							sum(case config_windowsmedia when 1 then 1 else 0 end) as windowsmedia,
							sum(case config_cookie when 1 then 1 else 0 end) as cookie	";
		return $this->archiveProcessing->getSimpleDataTableFromSelect($toSelect, Piwik_Archive::INDEX_NB_VISITS);
	}
}
