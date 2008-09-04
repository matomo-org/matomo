<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitsSummary
 */
	
/**
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'name' => 'Visits Summary',
			'description' => 'Reports the general Analytics numbers: visits, unique visitors, number of actions, Bounce Rate, etc.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		return $info;
	}
	
	function getListHooksRegistered()
	{
		return array(
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenu',
		);
	}
	
	function addWidgets()
	{
		Piwik_AddWidget( 'VisitsSummary', 'getLastVisitsGraph', Piwik_Translate('VisitsSummary_WidgetLastVisits'));
		Piwik_AddWidget( 'VisitsSummary', 'getSparklines', Piwik_Translate('VisitsSummary_WidgetVisits'));
		Piwik_AddWidget( 'VisitsSummary', 'getLastUniqueVisitorsGraph', Piwik_Translate('VisitsSummary_WidgetLastVisitors'));
		Piwik_AddWidget( 'VisitsSummary', 'index', Piwik_Translate('VisitsSummary_WidgetOverviewGraph'));
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'VisitsSummary_SubmenuOverview', array('module' => 'VisitsSummary'), true);
	}
}


function Piwik_getPrettyTimeFromSeconds($numberOfSeconds)
{
	$numberOfSeconds = (double)$numberOfSeconds;
	$days = floor($numberOfSeconds / 86400);
	
	$minusDays = $numberOfSeconds - $days * 86400;
	$hours = floor($minusDays / 3600);
	
	$minusDaysAndHours = $minusDays - $hours * 3600;
	$minutes = floor($minusDaysAndHours / 60 );
	
	$minusDaysAndHoursAndMinutes = $minusDaysAndHours - $minutes * 60;
	$secondsMod = $minusDaysAndHoursAndMinutes; // should be same as $numberOfSeconds % 60 
	
	if($days > 0)
	{
		return sprintf("%d days %d hours", $days, $hours);
	}
	elseif($hours > 0)
	{
		return sprintf("%d hours %d min", $hours, $minutes);
	}
	else
	{
		return sprintf("%d min %d s", $minutes, $numberOfSeconds);		
	}
}