<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitsSummary
 */

/**
 *
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'name' => 'VisitsSummary',
			'description' => Piwik_Translate('VisitsSummary_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
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
		Piwik_AddWidget( 'VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetLastVisits', 'VisitsSummary', 'getEvolutionGraph', array('columns' => array('nb_visits')));
		Piwik_AddWidget( 'VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetVisits', 'VisitsSummary', 'getSparklines');
		Piwik_AddWidget( 'VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetOverviewGraph', 'VisitsSummary', 'index');
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'VisitsSummary_SubmenuOverview', array('module' => 'VisitsSummary'), true);
	}
}


