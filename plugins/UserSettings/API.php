<?php

require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";
		
class Piwik_UserSettings_API extends Piwik_Apiable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	public function getResolution( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_resolution');
		return $dataTable;
	}
	
	public function getConfiguration( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_configuration');
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($dataTable, 'label', 'Piwik_getConfigurationLabel');
			
		return $dataTable;
	}
	public function getOS( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_os');
	
		
		$filter = new Piwik_DataTable_Filter_ColumnCallbackAddDetail($dataTable, 'label', 'shortLabel', 'Piwik_getOSShortLabel');
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($dataTable, 'label', 'Piwik_getOSLabel');
		return $dataTable;
	}
	
	
	public function getBrowser( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_browser');
		
		
		$filter = new Piwik_DataTable_Filter_ColumnCallbackAddDetail($dataTable, 'label', 'logo', 'Piwik_getBrowsersLogo');
		$filter = new Piwik_DataTable_Filter_ColumnCallbackAddDetail($dataTable, 'label', 'shortLabel', 'Piwik_getBrowserShortLabel');
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($dataTable, 'label', 'Piwik_getBrowserLabel');
		
		return $dataTable;
	}
	
	public function getBrowserType( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_browserType');
		

		$filter = new Piwik_DataTable_Filter_ColumnCallbackAddDetail($dataTable, 'label', 'shortLabel', 'ucfirst');
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($dataTable, 'label', 'Piwik_getBrowserTypeLabel');
		
		return $dataTable;
	}
	
	public function getWideScreen( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_wideScreen');	
		
		
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($dataTable, 'label', 'ucfirst');
	
		return $dataTable;
	}
	public function getPlugin( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_plugin');
		
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($dataTable, 'label', 'ucfirst');
		
		return $dataTable;
	}
	
}
	
function Piwik_getOSLabel($oldLabel)
{
	if(isset($GLOBALS['Piwik_Oslist_IdToLabel'][$oldLabel]))
	{
		return $GLOBALS['Piwik_Oslist_IdToLabel'][$oldLabel];
	}
	return 'UNK';
}
function Piwik_getOSShortLabel($oldLabel)
{
	if(isset($GLOBALS['Piwik_Oslist_IdToShortLabel'][$oldLabel]))
	{
		return $GLOBALS['Piwik_Oslist_IdToShortLabel'][$oldLabel];
	}
	return 'UNK';
}
function Piwik_getBrowserTypeLabel($oldLabel)
{
	if(isset(Piwik_UserSettings::$browserType_display[$oldLabel]))
	{
		return Piwik_UserSettings::$browserType_display[$oldLabel];
	}
	return 'Unknown';
}


function Piwik_getConfigurationLabel($str)
{
	$values = explode(";", $str);
	
	$os = Piwik_getOSLabel($values[0]);
	$browser = $GLOBALS['Piwik_BrowserList_IdToLabel'][$values[1]];
	$resolution = $values[2];
	
	return $os . " / " . $browser . " / " . $resolution;
}
function Piwik_getBrowserLabel($oldLabel)
{
	$name = Piwik_getBrowserId($oldLabel);
	$version = Piwik_getBrowserVersion($oldLabel);
	if(isset($GLOBALS['Piwik_BrowserList_IdToLabel'][$name]))
	{
		return $GLOBALS['Piwik_BrowserList_IdToLabel'][$name] . " ". $version;
	}
	return 'UNK';
}

function Piwik_getBrowserShortLabel($oldLabel)
{
	$name = Piwik_getBrowserId($oldLabel);
	$version = Piwik_getBrowserVersion($oldLabel);
	if(isset($GLOBALS['Piwik_BrowserList_IdToShortLabel'][$name]))
	{
		return $GLOBALS['Piwik_BrowserList_IdToShortLabel'][$name] . " ". $version;
	}
	return 'UNK';
}

function Piwik_getBrowserId($str)
{
	return substr($str, 0, strpos($str, ';'));
}

function Piwik_getBrowserVersion($str)
{
	return substr($str, strpos($str, ';') + 1);
}

function Piwik_getBrowsersLogo($label)
{
	$id = Piwik_getBrowserId($label);
	return "/plugins/UserSettings/images/browsers/". $id . ".gif";
}
?>
