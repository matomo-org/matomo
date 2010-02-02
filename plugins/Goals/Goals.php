<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 * TODO Goals plugin
 * - clean API especially int methods
 */

/**
 *
 * @package Piwik_Goals
 */
class Piwik_Goals extends Piwik_Plugin
{	
	const ROUNDING_PRECISION = 2;
	
	public function getInformation()
	{
		$info = array(
			'name' => 'Goals',
			'description' => Piwik_Translate('Goals_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
			'TrackerPlugin' => true, // this plugin must be loaded during the stats logging
		);
		
		return $info;
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'Common.fetchWebsiteAttributes' => 'fetchGoalsFromDb',
			'ArchiveProcessing_Day.compute' => 'archiveDay',
			'ArchiveProcessing_Period.compute' => 'archivePeriod',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenus',
		);
		return $hooks;
	}

	function fetchGoalsFromDb($notification)
	{
		$idsite = $notification->getNotificationInfo();
		
		// add the 'goal' entry in the website array
		$array =& $notification->getNotificationObject();
		$array['goals'] = Piwik_Goals_API::getGoals($idsite);
	}
	
	function addWidgets()
	{
	}
	
	function addMenus()
	{
		$goals = Piwik_Tracker_GoalManager::getGoalDefinitions(Piwik_Common::getRequestVar('idSite'));
		if(count($goals)==0)
		{
			Piwik_AddMenu('Goals', 'Add a new Goal', array('module' => 'Goals', 'action' => 'addNewGoal'));
		}
		else
		{
			Piwik_AddMenu('Goals', 'Overview', array('module' => 'Goals'));
			foreach($goals as $goal) 
			{
				Piwik_AddMenu('Goals', str_replace('%', '%%', $goal['name']), array('module' => 'Goals', 'action' => 'goalReport', 'idGoal' => $goal['idgoal']));
			}
		}
	}
	
	/**
	 * @param string $recordName 'nb_conversions'
	 * @param int $idGoal idGoal to return the metrics for, or false to return overall 
	 * @param int $visitorReturning 0 for new visitors, 1 for returning visitors, false for all
	 * @return unknown
	 */
	static public function getRecordName($recordName, $idGoal = false, $visitorReturning = false)
	{
		$idGoalStr = $returningStr = '';
		if($idGoal !== false)
		{
			$idGoalStr = $idGoal . "_";
		}
		if($visitorReturning !== false)
		{
			$returningStr = 'visitor_returning_' . $visitorReturning . '_';
		}
		return 'Goal_' . $returningStr . $idGoalStr . $recordName;
	}
	
	function archivePeriod($notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$metricsToSum = array( 'nb_conversions', 'revenue');
		$goalIdsToSum = Piwik_Tracker_GoalManager::getGoalIds($archiveProcessing->idsite);
		
		$fieldsToSum = array();
		foreach($metricsToSum as $metricName)
		{
			foreach($goalIdsToSum as $goalId)
			{
				$fieldsToSum[] = self::getRecordName($metricName, $goalId);
				$fieldsToSum[] = self::getRecordName($metricName, $goalId, 0);
				$fieldsToSum[] = self::getRecordName($metricName, $goalId, 1);
			}
			$fieldsToSum[] = self::getRecordName($metricName);
		}
		$records = $archiveProcessing->archiveNumericValuesSum($fieldsToSum);
		
		// also recording conversion_rate for each goal
		foreach($goalIdsToSum as $goalId)
		{
			$nb_conversions = $records[self::getRecordName('nb_conversions', $goalId)]->value;
			$conversion_rate = $this->getConversionRate($nb_conversions, $archiveProcessing);
			$archiveProcessing->insertNumericRecord(self::getRecordName('conversion_rate', $goalId), $conversion_rate);
		}
		
		// global conversion rate
		$nb_conversions = $records[self::getRecordName('nb_conversions')]->value;
		$conversion_rate = $this->getConversionRate($nb_conversions, $archiveProcessing);
		$archiveProcessing->insertNumericRecord(self::getRecordName('conversion_rate'), $conversion_rate);
	}
	
	function archiveDay( $notification )
	{
		/**
		 * @var Piwik_ArchiveProcessing_Day 
		 */
		$archiveProcessing = $notification->getNotificationObject();
		
		// by processing visitor_returning segment, we can also simply sum and get stats for all goals.
		$query = $archiveProcessing->queryConversionsBySegment('visitor_returning');

		$nb_conversions = $revenue = 0;
		$goals = $goalsByVisitorReturning = array();
		while($row = $query->fetch() )
		{
			$goalsByVisitorReturning[$row['idgoal']][$row['visitor_returning']] = $archiveProcessing->getGoalRowFromQueryRow($row);
			
			if(!isset($goals[$row['idgoal']])) $goals[$row['idgoal']] = $archiveProcessing->getNewGoalRow();
			$archiveProcessing->updateGoalStats($row, $goals[$row['idgoal']]);

			$revenue += $row['revenue'];
			$nb_conversions += $row['nb_conversions'];
		}
		
		// Stats by goal, for all visitors
		foreach($goals as $idgoal => $values)
		{
			foreach($values as $metricId => $value)
			{
				$metricName = Piwik_Archive::$mappingFromIdToNameGoal[$metricId];
				$recordName = self::getRecordName($metricName, $idgoal);
				$archiveProcessing->insertNumericRecord($recordName, $value);
			}
			$conversion_rate = $this->getConversionRate($values[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS], $archiveProcessing);
			$recordName = self::getRecordName('conversion_rate', $idgoal);
			$archiveProcessing->insertNumericRecord($recordName, $conversion_rate);
		}
		
		// Stats by goal, for visitor returning / non returning
		foreach($goalsByVisitorReturning as $idgoal => $values)
		{
			foreach($values as $visitor_returning => $goalValues)
			{
				foreach($goalValues as $metricId => $value)
				{
					$metricName = Piwik_Archive::$mappingFromIdToNameGoal[$metricId];
					$recordName = self::getRecordName($metricName, $idgoal, $visitor_returning);
					$archiveProcessing->insertNumericRecord($recordName, $value);
//					echo $record . "<br>";
				}
			}
		}
	
		// Stats for all goals
		$totalAllGoals = array(
			self::getRecordName('conversion_rate')	=> $this->getConversionRate($archiveProcessing->getNumberOfVisitsConverted(), $archiveProcessing),
			self::getRecordName('nb_conversions')	=> $nb_conversions,
			self::getRecordName('revenue') 			=> $revenue,
		);
		foreach($totalAllGoals as $recordName => $value)
		{
			$archiveProcessing->insertNumericRecord($recordName, $value);
		}
	}
	
	function getConversionRate($count, $archiveProcessing)
	{
		return round(100 * $count / $archiveProcessing->getNumberOfVisits(), self::ROUNDING_PRECISION);
	}
}
