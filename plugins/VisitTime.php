<?php
	
class Piwik_Plugin_VisitTime extends Piwik_Plugin
{	
	public function __construct()
	{
		parent::__construct();
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'VisitTime',
			'description' => 'Visit Local & Server Time',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
	}
	
	function uninstall()
	{
	}
	
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archiveMonth',
		);
		return $hooks;
	}
	
	
	function archiveMonth( $notification )
	{
		$this->archiveProcessing = $notification->getNotificationObject();
		
		$dataTableToSum = array( 
				'VisitTime_localTime',
				'VisitTime_serverTime',
		);
		
		$this->archiveProcessing->archiveDataTable($dataTableToSum);
	}
	public function archiveDay( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();

		$recordName = 'VisitTime_localTime';
		$labelSQL = "HOUR(visitor_localtime)";
		$tableLocalTime = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableLocalTime->getSerialized());
//		echo $tableLocalTime;
		
		$recordName = 'VisitTime_serverTime';
		$labelSQL = "HOUR(visit_first_action_time)";
		$tableServerTime = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableServerTime->getSerialized());
//		echo $tableServerTime;
	}
}