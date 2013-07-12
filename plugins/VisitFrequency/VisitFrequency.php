<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        $info = array(
            'description'     => Piwik_Translate('VisitFrequency_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
        return $info;
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'WidgetsList.add'                  => 'addWidgets',
            'Menu.add'                         => 'addMenu',
            'API.getReportMetadata'            => 'getReportMetadata',
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'         => Piwik_Translate('General_Visitors'),
            'name'             => Piwik_Translate('VisitFrequency_ColumnReturningVisits'),
            'module'           => 'VisitFrequency',
            'action'           => 'get',
            'metrics'          => array(
                'nb_visits_returning'            => Piwik_Translate('VisitFrequency_ColumnReturningVisits'),
                'nb_actions_returning'           => Piwik_Translate('VisitFrequency_ColumnActionsByReturningVisits'),
                'avg_time_on_site_returning'     => Piwik_Translate('VisitFrequency_ColumnAverageVisitDurationForReturningVisitors'),
                'bounce_rate_returning'          => Piwik_Translate('VisitFrequency_ColumnBounceRateForReturningVisits'),
                'nb_actions_per_visit_returning' => Piwik_Translate('VisitFrequency_ColumnAvgActionsPerReturningVisit'),
                'nb_uniq_visitors_returning'     => Piwik_Translate('VisitFrequency_ColumnUniqueReturningVisitors'),
// Not displayed
//    			'nb_visits_converted_returning',
//    			'sum_visit_length_returning',
//    			'max_actions_returning',
//    			'bounce_count_returning',
            ),
            'processedMetrics' => false,
            'order'            => 40
        );
    }

    function addWidgets()
    {
        Piwik_AddWidget('General_Visitors', 'VisitFrequency_WidgetOverview', 'VisitFrequency', 'getSparklines');
        Piwik_AddWidget('General_Visitors', 'VisitFrequency_WidgetGraphReturning', 'VisitFrequency', 'getEvolutionGraph', array('columns' => array('nb_visits_returning')));
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'VisitFrequency_SubmenuFrequency', array('module' => 'VisitFrequency', 'action' => 'index'));
    }
}
