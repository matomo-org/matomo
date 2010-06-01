<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitFrequency
 */

/**
 *
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'VisitFrequency',
			'description' => Piwik_Translate('VisitFrequency_PluginDescription'),
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
		);
		return $hooks;
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'General_Visitors', 'VisitFrequency_WidgetOverview', 'VisitFrequency', 'getSparklines');
		Piwik_AddWidget( 'General_Visitors', 'VisitFrequency_WidgetGraphReturning', 'VisitFrequency', 'getEvolutionGraph', array('columns' => array('nb_visits_returning')));
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'VisitFrequency_SubmenuFrequency', array('module' => 'VisitFrequency', 'action' => 'index'));
	}
	
	function archivePeriod( $notification )
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$numericToSum = array( 
				'nb_visits_returning',
				'nb_actions_returning',
				'sum_visit_length_returning',
				'bounce_count_returning',
				'nb_visits_converted_returning',
		);
		$archiveProcessing->archiveNumericValuesSum($numericToSum);
		$archiveProcessing->archiveNumericValuesMax('max_actions_returning');
	}
	
	function archiveDay($notification)
	{
		/* @var $archiveProcessing Piwik_ArchiveProcessing */
		$archiveProcessing = $notification->getNotificationObject();
		
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors_returning,
							count(*) as nb_visits_returning, 
							sum(visit_total_actions) as nb_actions_returning,
							max(visit_total_actions) as max_actions_returning, 
							sum(visit_total_time) as sum_visit_length_returning,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count_returning,
							sum(case visit_goal_converted when 1 then 1 else 0 end) as nb_visits_converted_returning
				 	FROM ".$archiveProcessing->logTable."
				 	WHERE visit_last_action_time >= ?
						AND visit_last_action_time <= ?
				 		AND idsite = ?
				 		AND visitor_returning = 1";
		$row = $archiveProcessing->db->fetchRow($query, array( $archiveProcessing->getStartDatetimeUTC(), $archiveProcessing->getEndDatetimeUTC(), $archiveProcessing->idsite ) );
		
		if($row === false || $row === null)
		{
			$row['nb_visits_returning'] = 0;
			$row['nb_actions_returning'] = 0;
			$row['max_actions_returning'] = 0;
			$row['sum_visit_length_returning'] = 0;
			$row['bounce_count_returning'] = 0;
			$row['nb_visits_converted_returning'] = 0;
		}
		
		foreach($row as $name => $value)
		{
			$archiveProcessing->insertNumericRecord($name, $value);
		}
	}
}

