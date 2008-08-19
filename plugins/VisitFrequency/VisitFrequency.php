<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
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
			'name' => 'Visits Frequency',
			'description' => 'Reports various statistics about the Returning Visitor versus the First time visitor.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function postLoad()
	{
		Piwik_AddWidget( 'VisitFrequency', 'getSparklines', Piwik_Translate('VisitFrequency_WidgetOverview'));
		Piwik_AddWidget( 'VisitFrequency', 'getLastVisitsReturningGraph', Piwik_Translate('VisitFrequency_WidgetGraphReturning'));
		Piwik_AddMenu('General_Visitors', 'VisitFrequency_SubmenuFrequency', array('module' => 'VisitFrequency'));
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
		
		$numericToSum = array( 
				'nb_visits_returning',
				'nb_actions_returning',
				'sum_visit_length_returning',
				'bounce_count_returning',
		);
		$archiveProcessing->archiveNumericValuesSum($numericToSum);
		$archiveProcessing->archiveNumericValuesMax('max_actions_returning');
	}
	
	function archiveDay($notification)
	{
		$archiveProcessing = $notification->getNotificationObject();
		
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors_returning,
							count(*) as nb_visits_returning, 
							sum(visit_total_actions) as nb_actions_returning,
							max(visit_total_actions) as max_actions_returning, 
							sum(visit_total_time) as sum_visit_length_returning,							
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count_returning
				 	FROM ".$archiveProcessing->logTable."
				 	WHERE visit_server_date = ?
				 		AND idsite = ?
				 		AND visitor_returning = 1
				 	GROUP BY visitor_returning";
		$row = $archiveProcessing->db->fetchRow($query, array( $archiveProcessing->strDateStart, $archiveProcessing->idsite ) );
		
		if($row==false)
		{
			$row['nb_visits_returning'] = 0;
			$row['nb_actions_returning'] = 0;
			$row['max_actions_returning'] = 0;
			$row['sum_visit_length_returning'] = 0;
			$row['bounce_count_returning'] = 0;
		}
		
		foreach($row as $name => $value)
		{
			$record = new Piwik_ArchiveProcessing_Record_Numeric($name, $value);
		}
	}
}

