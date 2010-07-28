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
 * Note: This plugin does not hook on Daily and Period Archiving like other Plugins because it reports the 
 * very core metrics (visits, actions, visit duration, etc.) which are processed in the Core
 * Piwik_ArchiveProcessing_Day class directly. 
 * These metrics can be used by other Plugins so they need to be processed up front.
 * 
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
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
			'AssetManager.getJsFiles' => 'getJsFiles',
			'API.getReportMetadata' => 'getReportMetadata',
			'WidgetsList.add' => 'addWidgets',
			'Menu.add' => 'addMenu',
		);
	}
	
	public function getReportMetadata($notification) 
	{
		$reports = &$notification->getNotificationObject();
		$reports[] = array(
			'category' => Piwik_Translate('VisitsSummary_VisitsSummary'),
			'name' => Piwik_Translate('VisitsSummary_VisitsSummary'),
			'module' => 'VisitsSummary',
			'action' => 'get',
			'metrics' => array(
								'nb_uniq_visitors', 
								'nb_visits',
								'nb_actions', 
								'nb_actions_per_visit',
								'bounce_rate',
								'avg_time_on_site' => Piwik_Translate('General_VisitDuration'),
								'max_actions' => Piwik_Translate('General_ColumnMaxActions'),
// Used to process metrics, not displayed/used directly
//								'sum_visit_length',
//								'nb_visits_converted',
			),
			'processedMetrics' => false,
		);
	}
	
	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = "plugins/CoreHome/templates/sparkline.js";
	}	
	
	function addWidgets()
	{
		Piwik_AddWidget( 'VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetLastVisits', 'VisitsSummary', 'getEvolutionGraph', array('columns' => array('nb_visits')));
		Piwik_AddWidget( 'VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetVisits', 'VisitsSummary', 'getSparklines');
		Piwik_AddWidget( 'VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetOverviewGraph', 'VisitsSummary', 'index');
	}
	
	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', '', array('module' => 'VisitsSummary', 'action' => 'index'), true, 10);
		Piwik_AddMenu('General_Visitors', 'VisitsSummary_SubmenuOverview', array('module' => 'VisitsSummary', 'action' => 'index'), true, 1);
	}
}


