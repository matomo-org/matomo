<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * This class holds the various mappings we use to internally store and manipulate metrics.
 */
class Piwik_Metrics
{
    /**
     * When saving DataTables in the DB, we replace all columns name with these IDs. This saves many bytes,
     * eg. INDEX_NB_UNIQ_VISITORS is an integer: 4 bytes, but 'nb_uniq_visitors' is 16 bytes at least
     */
    const INDEX_NB_UNIQ_VISITORS = 1;
    const INDEX_NB_VISITS = 2;
    const INDEX_NB_ACTIONS = 3;
    const INDEX_MAX_ACTIONS = 4;
    const INDEX_SUM_VISIT_LENGTH = 5;
    const INDEX_BOUNCE_COUNT = 6;
    const INDEX_NB_VISITS_CONVERTED = 7;
    const INDEX_NB_CONVERSIONS = 8;
    const INDEX_REVENUE = 9;
    const INDEX_GOALS = 10;
    const INDEX_SUM_DAILY_NB_UNIQ_VISITORS = 11;

    // Specific to the Actions reports
    const INDEX_PAGE_NB_HITS = 12;
    const INDEX_PAGE_SUM_TIME_SPENT = 13;

    const INDEX_PAGE_EXIT_NB_UNIQ_VISITORS = 14;
    const INDEX_PAGE_EXIT_NB_VISITS = 15;
    const INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS = 16;

    const INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS = 17;
    const INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS = 18;
    const INDEX_PAGE_ENTRY_NB_VISITS = 19;
    const INDEX_PAGE_ENTRY_NB_ACTIONS = 20;
    const INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH = 21;
    const INDEX_PAGE_ENTRY_BOUNCE_COUNT = 22;

    // Ecommerce Items reports
    const INDEX_ECOMMERCE_ITEM_REVENUE = 23;
    const INDEX_ECOMMERCE_ITEM_QUANTITY = 24;
    const INDEX_ECOMMERCE_ITEM_PRICE = 25;
    const INDEX_ECOMMERCE_ORDERS = 26;
    const INDEX_ECOMMERCE_ITEM_PRICE_VIEWED = 27;

    // Site Search
    const INDEX_SITE_SEARCH_HAS_NO_RESULT = 28;
    const INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS = 29;

    // Performance Analytics
    const INDEX_PAGE_SUM_TIME_GENERATION = 30;
    const INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION = 31;
    const INDEX_PAGE_MIN_TIME_GENERATION = 32;
    const INDEX_PAGE_MAX_TIME_GENERATION = 33;

    // Goal reports
    const INDEX_GOAL_NB_CONVERSIONS = 1;
    const INDEX_GOAL_REVENUE = 2;
    const INDEX_GOAL_NB_VISITS_CONVERTED = 3;

    const INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL = 4;
    const INDEX_GOAL_ECOMMERCE_REVENUE_TAX = 5;
    const INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING = 6;
    const INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT = 7;
    const INDEX_GOAL_ECOMMERCE_ITEMS = 8;

    public static $mappingFromIdToName = array(
        Piwik_Metrics::INDEX_NB_UNIQ_VISITORS                      => 'nb_uniq_visitors',
        Piwik_Metrics::INDEX_NB_VISITS                             => 'nb_visits',
        Piwik_Metrics::INDEX_NB_ACTIONS                            => 'nb_actions',
        Piwik_Metrics::INDEX_MAX_ACTIONS                           => 'max_actions',
        Piwik_Metrics::INDEX_SUM_VISIT_LENGTH                      => 'sum_visit_length',
        Piwik_Metrics::INDEX_BOUNCE_COUNT                          => 'bounce_count',
        Piwik_Metrics::INDEX_NB_VISITS_CONVERTED                   => 'nb_visits_converted',
        Piwik_Metrics::INDEX_NB_CONVERSIONS                        => 'nb_conversions',
        Piwik_Metrics::INDEX_REVENUE                               => 'revenue',
        Piwik_Metrics::INDEX_GOALS                                 => 'goals',
        Piwik_Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS            => 'sum_daily_nb_uniq_visitors',

        // Actions metrics
        Piwik_Metrics::INDEX_PAGE_NB_HITS                          => 'nb_hits',
        Piwik_Metrics::INDEX_PAGE_SUM_TIME_SPENT                   => 'sum_time_spent',
        Piwik_Metrics::INDEX_PAGE_SUM_TIME_GENERATION              => 'sum_time_generation',
        Piwik_Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION     => 'nb_hits_with_time_generation',
        Piwik_Metrics::INDEX_PAGE_MIN_TIME_GENERATION              => 'min_time_generation',
        Piwik_Metrics::INDEX_PAGE_MAX_TIME_GENERATION              => 'max_time_generation',

        Piwik_Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS            => 'exit_nb_uniq_visitors',
        Piwik_Metrics::INDEX_PAGE_EXIT_NB_VISITS                   => 'exit_nb_visits',
        Piwik_Metrics::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS  => 'sum_daily_exit_nb_uniq_visitors',

        Piwik_Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS           => 'entry_nb_uniq_visitors',
        Piwik_Metrics::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS => 'sum_daily_entry_nb_uniq_visitors',
        Piwik_Metrics::INDEX_PAGE_ENTRY_NB_VISITS                  => 'entry_nb_visits',
        Piwik_Metrics::INDEX_PAGE_ENTRY_NB_ACTIONS                 => 'entry_nb_actions',
        Piwik_Metrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH           => 'entry_sum_visit_length',
        Piwik_Metrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT               => 'entry_bounce_count',
        Piwik_Metrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS => 'nb_hits_following_search',

        // Items reports metrics
        Piwik_Metrics::INDEX_ECOMMERCE_ITEM_REVENUE                => 'revenue',
        Piwik_Metrics::INDEX_ECOMMERCE_ITEM_QUANTITY               => 'quantity',
        Piwik_Metrics::INDEX_ECOMMERCE_ITEM_PRICE                  => 'price',
        Piwik_Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED           => 'price_viewed',
        Piwik_Metrics::INDEX_ECOMMERCE_ORDERS                      => 'orders',
    );

    public static $mappingFromIdToNameGoal = array(
        Piwik_Metrics::INDEX_GOAL_NB_CONVERSIONS             => 'nb_conversions',
        Piwik_Metrics::INDEX_GOAL_NB_VISITS_CONVERTED        => 'nb_visits_converted',
        Piwik_Metrics::INDEX_GOAL_REVENUE                    => 'revenue',
        Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL => 'revenue_subtotal',
        Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_TAX      => 'revenue_tax',
        Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING => 'revenue_shipping',
        Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT => 'revenue_discount',
        Piwik_Metrics::INDEX_GOAL_ECOMMERCE_ITEMS            => 'items',
    );

    protected static $metricsAggregatedFromLogs = array(
        Piwik_Metrics::INDEX_NB_UNIQ_VISITORS,
        Piwik_Metrics::INDEX_NB_VISITS,
        Piwik_Metrics::INDEX_NB_ACTIONS,
        Piwik_Metrics::INDEX_MAX_ACTIONS,
        Piwik_Metrics::INDEX_SUM_VISIT_LENGTH,
        Piwik_Metrics::INDEX_BOUNCE_COUNT,
        Piwik_Metrics::INDEX_NB_VISITS_CONVERTED,
    );

    public static function getVisitsMetricNames()
    {
        $names = array();
        foreach(self::$metricsAggregatedFromLogs as $metricId) {
            $names[$metricId] = self::$mappingFromIdToName[$metricId];
        }
        return $names;
    }

    /* Used in DataTable Sort filter */
    public static function getMappingFromIdToName()
    {
        $idToName = array_flip(self::$mappingFromIdToName);
        return $idToName;
    }
    public static $mappingFromNameToId = array(
        'nb_uniq_visitors'           => Piwik_Metrics::INDEX_NB_UNIQ_VISITORS,
        'nb_visits'                  => Piwik_Metrics::INDEX_NB_VISITS,
        'nb_actions'                 => Piwik_Metrics::INDEX_NB_ACTIONS,
        'max_actions'                => Piwik_Metrics::INDEX_MAX_ACTIONS,
        'sum_visit_length'           => Piwik_Metrics::INDEX_SUM_VISIT_LENGTH,
        'bounce_count'               => Piwik_Metrics::INDEX_BOUNCE_COUNT,
        'nb_visits_converted'        => Piwik_Metrics::INDEX_NB_VISITS_CONVERTED,
        'nb_conversions'             => Piwik_Metrics::INDEX_NB_CONVERSIONS,
        'revenue'                    => Piwik_Metrics::INDEX_REVENUE,
        'goals'                      => Piwik_Metrics::INDEX_GOALS,
        'sum_daily_nb_uniq_visitors' => Piwik_Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
    );


    /**
     * Is a lower value for a given column better?
     * @param $column
     * @return bool
     *
     * @ignore
     */
    static public function isLowerValueBetter($column)
    {
        $lowerIsBetterPatterns = array(
            'bounce', 'exit'
        );

        foreach ($lowerIsBetterPatterns as $pattern) {
            if (strpos($column, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Derive the unit name from a column name
     * @param $column
     * @param $idSite
     * @return string
     * @ignore
     */
    static public function getUnit($column, $idSite)
    {
        $nameToUnit = array(
            '_rate'   => '%',
            'revenue' => Piwik::getCurrency($idSite),
            '_time_'  => 's'
        );

        foreach ($nameToUnit as $pattern => $type) {
            if (strpos($column, $pattern) !== false) {
                return $type;
            }
        }

        return '';
    }

    public static function getDefaultMetricTranslations()
    {
        $trans = array(
            'label'                         => 'General_ColumnLabel',
            'date'                          => 'General_Date',
            'avg_time_on_page'              => 'General_ColumnAverageTimeOnPage',
            'sum_time_spent'                => 'General_ColumnSumVisitLength',
            'sum_visit_length'              => 'General_ColumnSumVisitLength',
            'bounce_count'                  => 'General_ColumnBounces',
            'bounce_count_returning'        => 'VisitFrequency_ColumnBounceCountForReturningVisits',
            'max_actions'                   => 'General_ColumnMaxActions',
            'max_actions_returning'         => 'VisitFrequency_ColumnMaxActionsInReturningVisit',
            'nb_visits_converted_returning' => 'VisitFrequency_ColumnNbReturningVisitsConverted',
            'sum_visit_length_returning'    => 'VisitFrequency_ColumnSumVisitLengthReturning',
            'nb_visits_converted'           => 'General_ColumnVisitsWithConversions',
            'nb_conversions'                => 'Goals_ColumnConversions',
            'revenue'                       => 'Goals_ColumnRevenue',
            'nb_hits'                       => 'General_ColumnPageviews',
            'entry_nb_visits'               => 'General_ColumnEntrances',
            'entry_nb_uniq_visitors'        => 'General_ColumnUniqueEntrances',
            'exit_nb_visits'                => 'General_ColumnExits',
            'exit_nb_uniq_visitors'         => 'General_ColumnUniqueExits',
            'entry_bounce_count'            => 'General_ColumnBounces',
            'exit_bounce_count'             => 'General_ColumnBounces',
            'exit_rate'                     => 'General_ColumnExitRate'
        );

        $trans = array_map('Piwik_Translate', $trans);

        $dailySum = ' (' . Piwik_Translate('General_DailySum') . ')';
        $afterEntry = ' ' . Piwik_Translate('General_AfterEntry');

        $trans['sum_daily_nb_uniq_visitors'] = Piwik_Translate('General_ColumnNbUniqVisitors') . $dailySum;
        $trans['sum_daily_entry_nb_uniq_visitors'] = Piwik_Translate('General_ColumnUniqueEntrances') . $dailySum;
        $trans['sum_daily_exit_nb_uniq_visitors'] = Piwik_Translate('General_ColumnUniqueExits') . $dailySum;
        $trans['entry_nb_actions'] = Piwik_Translate('General_ColumnNbActions') . $afterEntry;
        $trans['entry_sum_visit_length'] = Piwik_Translate('General_ColumnSumVisitLength') . $afterEntry;

        $trans = array_merge(self::getDefaultMetrics(), self::getDefaultProcessedMetrics(), $trans);

        return $trans;
    }

    static public function getDefaultMetrics()
    {
        $translations = array(
            'nb_visits'        => 'General_ColumnNbVisits',
            'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
            'nb_actions'       => 'General_ColumnNbActions',
        );
        $translations = array_map('Piwik_Translate', $translations);
        return $translations;
    }

    public function getDefaultMetricsDocumentation()
    {
        $documentation = array(
            'nb_visits'            => 'General_ColumnNbVisitsDocumentation',
            'nb_uniq_visitors'     => 'General_ColumnNbUniqVisitorsDocumentation',
            'nb_actions'           => 'General_ColumnNbActionsDocumentation',
            'nb_actions_per_visit' => 'General_ColumnActionsPerVisitDocumentation',
            'avg_time_on_site'     => 'General_ColumnAvgTimeOnSiteDocumentation',
            'bounce_rate'          => 'General_ColumnBounceRateDocumentation',
            'conversion_rate'      => 'General_ColumnConversionRateDocumentation',
            'avg_time_on_page'     => 'General_ColumnAverageTimeOnPageDocumentation',
            'nb_hits'              => 'General_ColumnPageviewsDocumentation',
            'exit_rate'            => 'General_ColumnExitRateDocumentation'
        );
        return array_map('Piwik_Translate', $documentation);
    }
    public function getDefaultProcessedMetrics()
    {
        $translations = array(
            // Processed in AddColumnsProcessedMetrics
            'nb_actions_per_visit' => 'General_ColumnActionsPerVisit',
            'avg_time_on_site'     => 'General_ColumnAvgTimeOnSite',
            'bounce_rate'          => 'General_ColumnBounceRate',
            'conversion_rate'      => 'General_ColumnConversionRate',
        );
        return array_map('Piwik_Translate', $translations);
    }


}