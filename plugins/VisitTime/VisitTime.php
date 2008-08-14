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
	public function getInformation()
	{
		$info = array(
			'name' => 'Visits Time',
			'description' => 'Reports the Local and Server time. Server time information can be useful to schedule a maintenance on the Website.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function postLoad()
	{
		Piwik_AddWidget( 'VisitTime', 'getVisitInformationPerLocalTime', Piwik_Translate('VisitTime_WidgetLocalTime'));
		Piwik_AddWidget( 'VisitTime', 'getVisitInformationPerServerTime', Piwik_Translate('VisitTime_WidgetServerTime'));

		Piwik_AddMenu('Visitors', Piwik_Translate('VisitTime_SubmenuTimes'), array('module' => 'VisitTime'));
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
		);
		return $hooks;
	}

	function archivePeriod( $notification )
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
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableLocalTime->getSerialized());
		
		$recordName = 'VisitTime_serverTime';
		$labelSQL = "HOUR(visit_first_action_time)";
		$tableServerTime = $archiveProcessing->getDataTableInterestForLabel($labelSQL);
		$this->makeSureAllHoursAreSet($tableServerTime);
		$record = new Piwik_ArchiveProcessing_Record_BlobArray($recordName, $tableServerTime->getSerialized());
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

