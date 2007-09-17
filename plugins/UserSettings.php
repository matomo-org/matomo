<?php

class Piwik_UserSettings extends Piwik_Plugin
{	
	// source: http://en.wikipedia.org/wiki/List_of_web_browsers
	static public $browserType = array(
		"ie"	=> array("IE"),
		"gecko" => array("NS", "PX", "FF", "FB", "CA", "CH", "GA", "KM", "MO", "SM"),
		"khtml" => array("SF", "KO", "OW"),
		"opera" => array("OP")
	);

	static public $browserType_display = array(
		'ie' => 'Internet Explorer',
		'gecko' => 'Gecko (Mozilla, Netscape)',
		'khtml' => 'Khtml (Konqueror, Safari)',
		'opera' => 'Opera'
	);
	
	public function __construct()
	{
		parent::__construct();
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'UserSettings',
			'description' => 'UserSettings',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function uninstall()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archiveMonth',
		);
		return $hooks;
	}
	protected function getTableWideScreen($tableResolution)
	{
		foreach($tableResolution->getRows() as $row)
		{
			$resolution = $row->getColumn('label');
			
			$width = intval(substr($resolution, 0, strpos($resolution, 'x')));
			$height= intval(substr($resolution, strpos($resolution, 'x') + 1));
			$ratio = Piwik::secureDiv($width, $height);
			
			if($ratio < 1.4)
			{
				$name = 'normal';
			}
			else if($ratio < 2)
			{
				$name = 'wide';
			}
			else
			{
				$name = 'dual';
			}
			
			if(!isset($nameToRow[$name]))
			{
				$nameToRow[$name] = new Piwik_DataTable_Row;
				$nameToRow[$name]->addColumn('label', $name);
			}
			
			$nameToRow[$name]->sumRow( $row );
		}
		$tableWideScreen = new Piwik_DataTable;
		$tableWideScreen->loadFromArray($nameToRow);
		
		return $tableWideScreen;
	}
	
	protected function getTableBrowserByType($tableBrowser)
	{		
		$nameToRow = array();
		
		foreach($tableBrowser->getRows() as $row)
		{
			$browserLabel = $row->getColumn('label');
			
			$familyNameToUse = 'unknown';
				
			foreach(self::$browserType as $familyName => $aBrowsers)
			{			
				if(in_array(substr($browserLabel, 0, 2), $aBrowsers))
				{
					$familyNameToUse = $familyName;
					break;				
				}
			}	
			
			if(!isset($nameToRow[$familyNameToUse]))
			{
				$nameToRow[$familyNameToUse] = new Piwik_DataTable_Row;
				$nameToRow[$familyNameToUse]->addColumn('label',$familyNameToUse);
			}
			
			$nameToRow[$familyNameToUse]->sumRow( $row );
		}
		
		$tableBrowserType = new Piwik_DataTable;
		$tableBrowserType->loadFromArray($nameToRow);
				
		return $tableBrowserType;
	}
	
	function archiveDay( $notification )
	{
		$this->archiveProcessing = $notification->getNotificationObject();
		
		$recordName = 'UserSettings_configuration';
		$labelSQL = "CONCAT(config_os, ';', config_browser_name, ';', config_resolution)";
		$tableConfiguration = $this->archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableConfiguration->getSerialized());
		
		$recordName = 'UserSettings_os';
		$labelSQL = "config_os";
		$tableOs = $this->archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableOs->getSerialized());
		
		$recordName = 'UserSettings_browser';
		$labelSQL = "CONCAT(config_browser_name, ';', config_browser_version)";
		$tableBrowser = $this->archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableBrowser->getSerialized());
		
		$recordName = 'UserSettings_browserType';
		$tableBrowserType = $this->getTableBrowserByType($tableBrowser);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableBrowserType->getSerialized());
		
		$recordName = 'UserSettings_resolution';
		$labelSQL = "config_resolution";
		$tableResolution = $this->archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$filter = new Piwik_DataTable_Filter_ColumnCallback($tableResolution, 'label', 'Piwik_UserSettings_keepStrlenGreater');
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableResolution->getSerialized());
		
		$recordName = 'UserSettings_wideScreen';
		$tableWideScreen = $this->getTableWideScreen($tableResolution);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableWideScreen->getSerialized());
		
		$recordName = 'UserSettings_plugin';
		$tablePlugin = $this->getDataTablePlugin();
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tablePlugin->getSerialized());
		
//		echo $tableResolution;
//		echo $tableWideScreen;
//		echo $tablePlugin;
//		Piwik::printMemoryUsage("End of ".get_class($this)." "); 
	}
	
	function archiveMonth( $notification )
	{
		$this->archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'UserSettings_configuration',
				'UserSettings_os',
				'UserSettings_browser',
				'UserSettings_browserType',
				'UserSettings_resolution',
				'UserSettings_wideScreen',
				'UserSettings_plugin',
		);
		
		$this->archiveProcessing->archiveDataTable($dataTableToSum);
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

function Piwik_UserSettings_keepStrlenGreater($value)
{
	return strlen($value) > 5;
}


