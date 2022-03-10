<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitFrequency;


class VisitFrequency extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
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
            'nb_uniq_visitors_returning'     => 'VisitFrequency_ColumnUniqueReturningVisitors',
            'nb_users_returning' => 'VisitFrequency_ColumnReturningUsers',

            'nb_visits_new'  => 'VisitFrequency_ColumnNewVisits',
            'nb_actions_new' => 'VisitFrequency_ColumnActionsByNewVisits',
            'avg_time_on_site_new' => 'VisitFrequency_ColumnAverageVisitDurationForNewVisitors',
            'bounce_rate_new'      => 'VisitFrequency_ColumnBounceRateForNewVisits',
            'nb_actions_per_visit_new' => 'VisitFrequency_ColumnAvgActionsPerNewVisit',
            'nb_uniq_visitors_new'     => 'VisitFrequency_ColumnUniqueNewVisitors',
            'nb_users_new' => 'VisitFrequency_ColumnNewUsers'
        );

        $translations = array_merge($translations, $metrics);
    }

}
