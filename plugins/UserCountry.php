<?php
	
class Piwik_Plugin_UserCountry extends Piwik_Plugin
{	
	public function __construct()
	{
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'UserCountry',
			'description' => 'UserCountry',
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
			'ArchiveProcessing_Day.compute' => 'archiveDay'
		);
		return $hooks;
	}
	
	function archiveDay($notification)
	{
		$this->ArchiveProcessing = $notification->getNotificationObject();
		
		$recordName = 'UserCountry_country';
		$labelSQL = "location_country";
		$tableCountry = $this->ArchiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableCountry->getSerialized());
//		echo $tableCountry;
		
		$recordName = 'UserCountry_continent';
		$labelSQL = "location_continent";
		$tableContinent = $this->ArchiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_Archive_Processing_Record_Blob_Array($recordName, $tableContinent->getSerialized());
//		echo $tableContinent;
	}
}