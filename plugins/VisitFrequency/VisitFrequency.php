<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitFrequency
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\WidgetsList;

/**
 *
 * @package VisitFrequency
 */
class VisitFrequency extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'WidgetsList.addWidgets'  => 'addWidgets',
            'Menu.Reporting.addItems' => 'addMenu',
            'API.getReportMetadata'   => 'getReportMetadata',
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'         => Piwik::translate('General_Visitors'),
            'name'             => Piwik::translate('VisitFrequency_ColumnReturningVisits'),
            'module'           => 'VisitFrequency',
            'action'           => 'get',
            'metrics'          => array(
                'nb_visits_returning'            => Piwik::translate('VisitFrequency_ColumnReturningVisits'),
                'nb_actions_returning'           => Piwik::translate('VisitFrequency_ColumnActionsByReturningVisits'),
                'avg_time_on_site_returning'     => Piwik::translate('VisitFrequency_ColumnAverageVisitDurationForReturningVisitors'),
                'bounce_rate_returning'          => Piwik::translate('VisitFrequency_ColumnBounceRateForReturningVisits'),
                'nb_actions_per_visit_returning' => Piwik::translate('VisitFrequency_ColumnAvgActionsPerReturningVisit'),
                'nb_uniq_visitors_returning'     => Piwik::translate('VisitFrequency_ColumnUniqueReturningVisitors'),
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
        WidgetsList::add('General_Visitors', 'VisitFrequency_WidgetOverview', 'VisitFrequency', 'getSparklines');
        WidgetsList::add('General_Visitors', 'VisitFrequency_WidgetGraphReturning', 'VisitFrequency', 'getEvolutionGraph', array('columns' => array('nb_visits_returning')));
    }

    function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', 'VisitFrequency_SubmenuFrequency', array('module' => 'VisitFrequency', 'action' => 'index'));
    }
}
