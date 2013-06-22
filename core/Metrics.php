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

}