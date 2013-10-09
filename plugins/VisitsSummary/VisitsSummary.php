<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitsSummary
 */
namespace Piwik\Plugins\VisitsSummary;

use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\WidgetsList;

/**
 * Note: This plugin does not hook on Daily and Period Archiving like other Plugins because it reports the
 * very core metrics (visits, actions, visit duration, etc.) which are processed in the Core
 * Day class directly.
 * These metrics can be used by other Plugins so they need to be processed up front.
 *
 * @package VisitsSummary
 */
class VisitsSummary extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'API.getReportMetadata'   => 'getReportMetadata',
            'WidgetsList.addWidgets'  => 'addWidgets',
            'Menu.Reporting.addItems' => 'addMenu',
        );
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'         => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'             => Piwik::translate('VisitsSummary_VisitsSummary'),
            'module'           => 'VisitsSummary',
            'action'           => 'get',
            'metrics'          => array(
                'nb_uniq_visitors',
                'nb_visits',
                'nb_actions',
                'nb_actions_per_visit',
                'bounce_rate',
                'avg_time_on_site' => Piwik::translate('General_VisitDuration'),
                'max_actions'      => Piwik::translate('General_ColumnMaxActions'),
// Used to process metrics, not displayed/used directly
//								'sum_visit_length',
//								'nb_visits_converted',
            ),
            'processedMetrics' => false,
            'order'            => 1
        );
    }

    function addWidgets()
    {
        WidgetsList::add('VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetLastVisits', 'VisitsSummary', 'getEvolutionGraph', array('columns' => array('nb_visits')));
        WidgetsList::add('VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetVisits', 'VisitsSummary', 'getSparklines');
        WidgetsList::add('VisitsSummary_VisitsSummary', 'VisitsSummary_WidgetOverviewGraph', 'VisitsSummary', 'index');
    }

    function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', '', array('module' => 'VisitsSummary', 'action' => 'index'), true, 10);
        MenuMain::getInstance()->add('General_Visitors', 'General_Overview', array('module' => 'VisitsSummary', 'action' => 'index'), true, 1);
    }
}


