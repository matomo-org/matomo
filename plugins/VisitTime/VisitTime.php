<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * 
 * @package Piwik_VisitTime
 */
	
/**
 * 
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime extends Piwik_Plugin
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
		$archiveProcessing = $notification->getNotificationObject();

		$dataTableToSum = array( 
				'VisitTime_localTime',
				'VisitTime_serverTime',
		);

		$archiveProcessing->archiveDataTable($dataTableToSum);
	}
	public function archiveDay( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();

		$this->archiveProcessing = $archiveProcessing;
		
		$recordName = 'VisitTime_localTime';
		$labelSQL = "HOUR(visitor_localtime)";
		$tableLocalTime = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$this->makeSureAllHoursAreSet($tableLocalTime);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableLocalTime->getSerialized());
//		echo $tableLocalTime;
		
		$recordName = 'VisitTime_serverTime';
		$labelSQL = "HOUR(visit_first_action_time)";
		$tableServerTime = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$this->makeSureAllHoursAreSet($tableServerTime);
		$record = new Piwik_ArchiveProcessing_Record_Blob_Array($recordName, $tableServerTime->getSerialized());
//		echo $tableServerTime;
	}
	
	private function makeSureAllHoursAreSet($table)
	{
		for($i=0;$i<=23;$i++)
		{
			if($table->getRowFromLabel($i) === false)
			{
				$row = $this->archiveProcessing->getNewInterestRowLabeled($i);
				$table->addRow( $row );
			}
		}
	}
}


Piwik_AddWidget( 'VisitTime', 'getVisitInformationPerLocalTime', 'Visits by local time');
Piwik_AddWidget( 'VisitTime', 'getVisitInformationPerServerTime', 'Visits by server time');

Piwik_AddMenu('General', 'Time', array('module' => 'VisitTime'));

