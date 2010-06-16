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
			'description' =>  Piwik_Translate('VisitTime_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
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
			'Goals.getAvailableGoalSegments' => 'addGoalSegments',
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
		Piwik_AddMenu('General_Visitors', 'VisitTime_SubmenuTimes', array('module' => 'VisitTime', 'action' => 'index'));
	}

	function addGoalSegments( $notification )
	{
		$segments =& $notification->getNotificationObject();
		$segments[Piwik_Translate('VisitTime_ColumnServerTime')] = array(
        		array(
        			'name' => Piwik_Translate('VisitTime_ColumnServerTime'),
        			'module' => 'VisitTime',
        			'action' => 'getVisitInformationPerServerTime',
        		),
        	);
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
		
		$labelSQL = "HOUR(visit_last_action_time)";
		$this->interestByServerTime = $archiveProcessing->getArrayInterestForLabel($labelSQL);
		$this->interestByServerTime = $this->convertServerTimeToLocalTimezone($this->interestByServerTime, $archiveProcessing);
	}
	
	protected function convertServerTimeToLocalTimezone($interestByServerTime, $archiveProcessing)
	{
		$date = Piwik_Date::factory($archiveProcessing->getStartDatetimeUTC())->toString();
		$timezone = $archiveProcessing->site->getTimezone();
		$visitsByHourTz = array();
		foreach($interestByServerTime as $hour => $stats)
		{
			$datetime = $date . ' '.$hour.':00:00';
			$hourInTz = (int)Piwik_Date::factory($datetime, $timezone)->toString('H');
			$visitsByHourTz[$hourInTz] = $stats;
		}
		return $visitsByHourTz;
	}
	
	protected function archiveDayAggregateGoals($archiveProcessing)
	{
		$query = $archiveProcessing->queryConversionsBySingleSegment("HOUR(server_time)");
		while($row = $query->fetch())
		{
			$goalByServerTime[$row['label']][$row['idgoal']] = $archiveProcessing->getGoalRowFromQueryRow($row);
		}
		$goalByServerTime = $this->convertServerTimeToLocalTimezone($goalByServerTime, $archiveProcessing);
		foreach($goalByServerTime as $hour => $goals)
		{
			$this->interestByServerTime[$hour][Piwik_Archive::INDEX_GOALS] = $goals;
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

