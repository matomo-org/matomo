<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;

use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

class VisitFrequency extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations'
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'nb_visits_returning'  => 'VisitFrequency_ColumnReturningVisits',
            'nb_actions_returning' => 'VisitFrequency_ColumnActionsByReturningVisits',
            'avg_time_on_site_returning' => 'VisitFrequency_ColumnAverageVisitDurationForReturningVisitors',
            'bounce_rate_returning'      => 'VisitFrequency_ColumnBounceRateForReturningVisits',
            'nb_actions_per_visit_returning' => 'VisitFrequency_ColumnAvgActionsPerReturningVisit',
            'nb_uniq_visitors_returning'     => 'VisitFrequency_ColumnUniqueReturningVisitors'
        );

        $translations = array_merge($translations, $metrics);
    }

}
