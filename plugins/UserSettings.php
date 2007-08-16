<?php
	
class Piwik_Plugin_UserSettings extends Piwik_Plugin
{	
	// source: http://en.wikipedia.org/wiki/List_of_web_browsers
	protected $browserType = array(
		"ie"	=> array("IE"),
		"gecko" => array("NS", "PX", "FF", "FB", "CA", "CH", "GA", "KM", "MO", "SM"),
		"khtml" => array("SF", "KO", "OW"),
		"opera" => array("OP")
	);

	protected $browserType_display = array(
		'ie' => 'Internet Explorer',
		'gecko' => 'Gecko (Mozilla, Netscape)',
		'khtml' => 'Khtml (Konqueror, Safari)',
		'opera' => 'Opera'
	);
	
	public function __construct()
	{
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
			'ArchiveProcessing_Day.compute' => 'compute'
		);
		return $hooks;
	}
	
	protected function getTableBrowserByType($tableBrowser)
	{		
		$nameToRow = array();
		
		foreach($tableBrowser->getRows() as $row)
		{
			$browserLabel = $row->getColumn('label');
			
			$familyNameToUse = 'unknown';
				
			foreach($this->browserType as $familyName => $aBrowsers)
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
		
		echo $tableBrowserType;
		
		return $tableBrowserType;
	}
	
	function compute( $notification )
	{
		$this->ArchiveProcessing = $notification->getNotificationObject();
		
		$recordName = 'UserSettings_configuration';
		$labelSQL = "CONCAT(config_os, ';', config_browser_name, ';', config_resolution)";
		$tableConfiguration = $this->ArchiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableConfiguration->getSerialized());
		
		$recordName = 'UserSettings_os';
		$labelSQL = "config_os";
		$tableOs = $this->ArchiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableOs->getSerialized());
		
		$recordName = 'UserSettings_browser';
		$labelSQL = "CONCAT(config_browser_name, ';', config_browser_version)";
		$tableBrowser = $this->ArchiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableBrowser->getSerialized());
		
		$recordName = 'UserSettings_browserType';
		$tableBrowserType = $this->getTableBrowserByType($tableBrowser);
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableBrowserType->getSerialized());
		
		$recordName = 'UserSettings_resolution';
		$labelSQL = "config_resolution";
		$tableResolution = $this->ArchiveProcessing->getDataTableInterestForLabel($labelSQL);
		$filter = new Piwik_DataTable_Filter_ColumnCallback($tableResolution, 'label', 'Piwik_Plugin_UserSettings_keepStrlenGreater');
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableBrowser->getSerialized());
		
		$recordName = 'UserSettings_plugin';
		$tablePlugin = $this->getDataTablePlugin();
		echo $tablePlugin;
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tablePlugin->getSerialized());
		
	}
	
	protected function getDataTablePlugin()
	{
		$query = "SELECT  	sum(case config_pdf when 1 then 1 else 0 end) as pdf, 
							sum(case config_flash when 1 then 1 else 0 end) as flash, 
				 			sum(case config_java when 1 then 1 else 0 end) as java, 
							sum(case config_director when 1 then 1 else 0 end) as director,
				 			sum(case config_quicktime when 1 then 1 else 0 end) as quicktime, 
							sum(case config_realplayer when 1 then 1 else 0 end) as realplayer,
							sum(case config_windowsmedia when 1 then 1 else 0 end) as windowsmedia,
							sum(case config_cookie when 1 then 1 else 0 end) as cookie		
			 	FROM ".$this->ArchiveProcessing->logTable."
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				";
		$data = $this->ArchiveProcessing->db->fetchRow($query, array( $this->ArchiveProcessing->strDateStart, $this->ArchiveProcessing->idsite ));

		foreach($data as $plugin => &$visitors)
		{
			$visitors = array('nb_visitors' => $visitors);
		}
		$table = new Piwik_DataTable;
		$table->loadFromArrayLabelIsKey($data);
		return $table;		
	}
}

function Piwik_Plugin_UserSettings_keepStrlenGreater($value)
{
	return strlen($value) > 5;
}

?>
