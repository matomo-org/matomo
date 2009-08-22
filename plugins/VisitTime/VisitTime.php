<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
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
		Piwik_AddWidget( 'VisitTime_SubmenuTimes', 'VisitTime_WidgetLocalTime', 'VisitTime', 'getVisitInformationPerLocalTime');
		Piwik_AddWidget( 'VisitTime_SubmenuTimes', 'VisitTime_WidgetServerTime', 'VisitTime', 'getVisitInformationPerServerTime');
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'VisitTime_SubmenuTimes', array('module' => 'VisitTime'));
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
		$this->archiveDayAggregateVisits($archiveProcessing);
		$this->archiveDayAggregateGoals($archiveProcessing);
		$this->archiveDayRecordInDatabase($archiveProcessing);
	}
	
	protected function archiveDayAggregateVisits($archiveProcessing)
	{
		$labelSQL = "HOUR(visitor_localtime)";
		$this->interestByLocalTime = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		
		$labelSQL = "HOUR(visit_first_action_time)";
		$this->interestByServerTime = $archiveProcessing->getArrayInterestForLabel($labelSQL);
	}
	
	protected function archiveDayAggregateGoals($archiveProcessing)
	{
		$query = $archiveProcessing->queryConversionsBySingleSegment("HOUR(server_time)");
		while($row = $query->fetch())
		{
			$this->interestByServerTime[$row['label']][Piwik_Archive::INDEX_GOALS][$row['idgoal']] = $archiveProcessing->getGoalRowFromQueryRow($row);
		}
		$archiveProcessing->enrichConversionsByLabelArray($this->interestByServerTime);
	}
	
	protected function archiveDayRecordInDatabase($archiveProcessing)
	{
		$tableLocalTime = $archiveProcessing->getDataTableFromArray($this->interestByLocalTime);
		$this->makeSureAllHoursAreSet($tableLocalTime, $archiveProcessing);
		$archiveProcessing->insertBlobRecord('VisitTime_localTime', $tableLocalTime->getSerialized());
		destroy($tableLocalTime);
		
		$tableServerTime = $archiveProcessing->getDataTableFromArray($this->interestByServerTime);
		$this->makeSureAllHoursAreSet($tableServerTime, $archiveProcessing);
		$archiveProcessing->insertBlobRecord('VisitTime_serverTime', $tableServerTime->getSerialized());
		destroy($tableServerTime);
	}

	private function makeSureAllHoursAreSet($table, $archiveProcessing)
	{
		for($i=0; $i<=23; $i++)
		{
			if($table->getRowFromLabel($i) === false)
			{
				$row = $archiveProcessing->getNewInterestRowLabeled($i);
				$table->addRow( $row );
			}
		}
	}
}

