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


