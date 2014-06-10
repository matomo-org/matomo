<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\Piwik;

/**
 *
 */
class VisitFrequency extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
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
}
