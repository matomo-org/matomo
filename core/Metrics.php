<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Cache as PiwikCache;
use Piwik\Columns\Dimension;
use Piwik\Tracker\GoalManager;

require_once PIWIK_INCLUDE_PATH . "/core/Piwik.php";

/**
 * This class contains metadata regarding core metrics and contains several
 * related helper functions.
 *
 * Of note are the `INDEX_...` constants. In the database, metric column names
 * in {@link DataTable} rows are stored as integers to save space. The integer
 * values used are determined by these constants.
 *
 * @api
 */
class Metrics
{
    /*
     * When saving DataTables in the DB, we replace all columns name with these IDs. This saves many bytes,
     * eg. INDEX_NB_UNIQ_VISITORS is an integer: 4 bytes, but 'nb_uniq_visitors' is 16 bytes at least
     */
    public const INDEX_NB_UNIQ_VISITORS = 1;
    public const INDEX_NB_VISITS = 2;
    public const INDEX_NB_ACTIONS = 3;
    public const INDEX_MAX_ACTIONS = 4;
    public const INDEX_SUM_VISIT_LENGTH = 5;
    public const INDEX_BOUNCE_COUNT = 6;
    public const INDEX_NB_VISITS_CONVERTED = 7;
    public const INDEX_NB_CONVERSIONS = 8;
    public const INDEX_REVENUE = 9;
    public const INDEX_GOALS = 10;
    public const INDEX_SUM_DAILY_NB_UNIQ_VISITORS = 11;

    // Specific to the Actions reports
    public const INDEX_PAGE_NB_HITS = 12;
    public const INDEX_PAGE_SUM_TIME_SPENT = 13;
    public const INDEX_PAGE_EXIT_NB_UNIQ_VISITORS = 14;
    public const INDEX_PAGE_EXIT_NB_VISITS = 15;
    public const INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS = 16;
    public const INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS = 17;
    public const INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS = 18;
    public const INDEX_PAGE_ENTRY_NB_VISITS = 19;
    public const INDEX_PAGE_ENTRY_NB_ACTIONS = 20;
    public const INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH = 21;
    public const INDEX_PAGE_ENTRY_BOUNCE_COUNT = 22;

    // Ecommerce Items reports
    public const INDEX_ECOMMERCE_ITEM_REVENUE = 23;
    public const INDEX_ECOMMERCE_ITEM_QUANTITY = 24;
    public const INDEX_ECOMMERCE_ITEM_PRICE = 25;
    public const INDEX_ECOMMERCE_ORDERS = 26;
    public const INDEX_ECOMMERCE_ITEM_PRICE_VIEWED = 27;

    // Site Search
    public const INDEX_SITE_SEARCH_HAS_NO_RESULT = 28;
    public const INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS = 29;

    // Performance Analytics
    public const INDEX_PAGE_SUM_TIME_GENERATION = 30;
    public const INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION = 31;
    public const INDEX_PAGE_MIN_TIME_GENERATION = 32;
    public const INDEX_PAGE_MAX_TIME_GENERATION = 33;

    // Events
    public const INDEX_EVENT_NB_HITS = 34;
    public const INDEX_EVENT_SUM_EVENT_VALUE = 35;
    public const INDEX_EVENT_MIN_EVENT_VALUE = 36;
    public const INDEX_EVENT_MAX_EVENT_VALUE = 37;
    public const INDEX_EVENT_NB_HITS_WITH_VALUE = 38;

    // Number of unique User IDs
    public const INDEX_NB_USERS = 39;
    public const INDEX_SUM_DAILY_NB_USERS = 40;

    // Contents
    public const INDEX_CONTENT_NB_IMPRESSIONS = 41;
    public const INDEX_CONTENT_NB_INTERACTIONS = 42;

    // Unique visitors fingerprints (useful to process unique visitors across websites)
    public const INDEX_NB_UNIQ_FINGERPRINTS = 43;

    // Total number of hits
    public const INDEX_NB_HITS = 44;

    // Goal reports
    public const INDEX_GOAL_NB_CONVERSIONS = 1;
    public const INDEX_GOAL_REVENUE = 2;
    public const INDEX_GOAL_NB_VISITS_CONVERTED = 3;
    public const INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL = 4;
    public const INDEX_GOAL_ECOMMERCE_REVENUE_TAX = 5;
    public const INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING = 6;
    public const INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT = 7;
    public const INDEX_GOAL_ECOMMERCE_ITEMS = 8;
    public const INDEX_GOAL_NB_PAGES_UNIQ_BEFORE = 9;
    public const INDEX_GOAL_NB_CONVERSIONS_ATTRIB = 10;
    public const INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE = 11;
    public const INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ = 12;
    public const INDEX_GOAL_NB_CONVERSIONS_ENTRY_RATE = 13;
    public const INDEX_GOAL_REVENUE_PER_ENTRY = 14;
    public const INDEX_GOAL_REVENUE_ATTRIB = 15;
    public const INDEX_GOAL_NB_CONVERSIONS_ENTRY = 16;
    public const INDEX_GOAL_REVENUE_ENTRY = 17;

    public static $mappingFromIdToName = array(
        Metrics::INDEX_NB_UNIQ_VISITORS                      => 'nb_uniq_visitors',
        Metrics::INDEX_NB_UNIQ_FINGERPRINTS                  => 'nb_uniq_fingerprints',
        Metrics::INDEX_NB_VISITS                             => 'nb_visits',
        Metrics::INDEX_NB_ACTIONS                            => 'nb_actions',
        Metrics::INDEX_NB_USERS                              => 'nb_users',
        Metrics::INDEX_MAX_ACTIONS                           => 'max_actions',
        Metrics::INDEX_SUM_VISIT_LENGTH                      => 'sum_visit_length',
        Metrics::INDEX_BOUNCE_COUNT                          => 'bounce_count',
        Metrics::INDEX_NB_VISITS_CONVERTED                   => 'nb_visits_converted',
        Metrics::INDEX_NB_CONVERSIONS                        => 'nb_conversions',
        Metrics::INDEX_REVENUE                               => 'revenue',
        Metrics::INDEX_GOALS                                 => 'goals',
        Metrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS            => 'sum_daily_nb_uniq_visitors',
        Metrics::INDEX_SUM_DAILY_NB_USERS                    => 'sum_daily_nb_users',
        Metrics::INDEX_NB_HITS                               => 'nb_hits',

        // Actions metrics
        Metrics::INDEX_PAGE_NB_HITS                          => 'nb_hits',
        Metrics::INDEX_PAGE_SUM_TIME_SPENT                   => 'sum_time_spent',
        Metrics::INDEX_PAGE_SUM_TIME_GENERATION              => 'sum_time_generation',
        Metrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION     => 'nb_hits_with_time_generation',
        Metrics::INDEX_PAGE_MIN_TIME_GENERATION              => 'min_time_generation',
        Metrics::INDEX_PAGE_MAX_TIME_GENERATION              => 'max_time_generation',

        Metrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS            => 'exit_nb_uniq_visitors',
        Metrics::INDEX_PAGE_EXIT_NB_VISITS                   => 'exit_nb_visits',
        Metrics::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS  => 'sum_daily_exit_nb_uniq_visitors',

        Metrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS           => 'entry_nb_uniq_visitors',
        Metrics::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS => 'sum_daily_entry_nb_uniq_visitors',
        Metrics::INDEX_PAGE_ENTRY_NB_VISITS                  => 'entry_nb_visits',
        Metrics::INDEX_PAGE_ENTRY_NB_ACTIONS                 => 'entry_nb_actions',
        Metrics::INDEX_PAGE_ENTRY_SUM_VISIT_LENGTH           => 'entry_sum_visit_length',
        Metrics::INDEX_PAGE_ENTRY_BOUNCE_COUNT               => 'entry_bounce_count',
        Metrics::INDEX_PAGE_IS_FOLLOWING_SITE_SEARCH_NB_HITS => 'nb_hits_following_search',

        // Items reports metrics
        Metrics::INDEX_ECOMMERCE_ITEM_REVENUE                => 'revenue',
        Metrics::INDEX_ECOMMERCE_ITEM_QUANTITY               => 'quantity',
        Metrics::INDEX_ECOMMERCE_ITEM_PRICE                  => 'price',
        Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED           => 'price_viewed',
        Metrics::INDEX_ECOMMERCE_ORDERS                      => 'orders',

        // Events
        Metrics::INDEX_EVENT_NB_HITS                         => 'nb_events',
        Metrics::INDEX_EVENT_SUM_EVENT_VALUE                 => 'sum_event_value',
        Metrics::INDEX_EVENT_MIN_EVENT_VALUE                 => 'min_event_value',
        Metrics::INDEX_EVENT_MAX_EVENT_VALUE                 => 'max_event_value',
        Metrics::INDEX_EVENT_NB_HITS_WITH_VALUE              => 'nb_events_with_value',

        // Contents
        Metrics::INDEX_CONTENT_NB_IMPRESSIONS                => 'nb_impressions',
        Metrics::INDEX_CONTENT_NB_INTERACTIONS               => 'nb_interactions'
    );

    public static $mappingFromIdToNameGoal = array(
        Metrics::INDEX_GOAL_NB_CONVERSIONS             => 'nb_conversions',
        Metrics::INDEX_GOAL_NB_VISITS_CONVERTED        => 'nb_visits_converted',
        Metrics::INDEX_GOAL_REVENUE                    => 'revenue',
        Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL => 'revenue_subtotal',
        Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_TAX      => 'revenue_tax',
        Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING => 'revenue_shipping',
        Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT => 'revenue_discount',
        Metrics::INDEX_GOAL_ECOMMERCE_ITEMS            => 'items',
        Metrics::INDEX_GOAL_NB_PAGES_UNIQ_BEFORE       => 'nb_conv_pages_before',
        Metrics::INDEX_GOAL_NB_CONVERSIONS_ATTRIB      => 'nb_conversions_attrib',
        Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE   => 'nb_conversions_page_rate',
        Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ   => 'nb_conversions_page_uniq',
        Metrics::INDEX_GOAL_NB_CONVERSIONS_ENTRY_RATE  => 'nb_conversions_entry_rate',
        Metrics::INDEX_GOAL_REVENUE_PER_ENTRY          => 'revenue_per_entry',
        Metrics::INDEX_GOAL_REVENUE_ATTRIB             => 'revenue_attrib',
        Metrics::INDEX_GOAL_NB_CONVERSIONS_ENTRY       => 'nb_conversions_entry',
        Metrics::INDEX_GOAL_REVENUE_ENTRY              => 'revenue_entry',
    );

    protected static $metricsAggregatedFromLogs = array(
        Metrics::INDEX_NB_UNIQ_VISITORS,
        Metrics::INDEX_NB_VISITS,
        Metrics::INDEX_NB_ACTIONS,
        Metrics::INDEX_NB_USERS,
        Metrics::INDEX_MAX_ACTIONS,
        Metrics::INDEX_SUM_VISIT_LENGTH,
        Metrics::INDEX_BOUNCE_COUNT,
        Metrics::INDEX_NB_VISITS_CONVERTED,
    );

    public static function getMappingFromIdToName()
    {
        $cache = PiwikCache::getTransientCache();
        $cacheKey = CacheId::siteAware(CacheId::pluginAware('Metrics.mappingFromIdToName'));

        $value = $cache->fetch($cacheKey);
        if (empty($value)) {
            $value = self::$mappingFromIdToName;

            /**
             * Use this event if your plugin uses custom metric integer IDs to associate those IDs with the
             * actual metric names (eg, 2 => nb_visits). This allows matomo to automate the replacing
             * of IDs => metric names for your new metrics.
             *
             * **Example**
             *
             *     public function addMetricIdToNameMapping(&$mapping)
             *     {
             *         $mapping[Archiver::INDEX_MY_NEW_METRIC] = $mapping['MyPlugin_myNewMetric'];
             *     }
             *
             * @ignore
             */
            Piwik::postEvent('Metrics.addMetricIdToNameMapping', [&$value]);

            $cache->save($cacheKey, $value);
        }
        return $value;
    }

    public static function getVisitsMetricNames()
    {
        $names = array();

        foreach (self::$metricsAggregatedFromLogs as $metricId) {
            $names[$metricId] = self::$mappingFromIdToName[$metricId];
        }

        return $names;
    }

    public static function getMappingFromNameToId()
    {
        static $nameToId = null;
        if ($nameToId === null) {
            $nameToId = array_flip(self::$mappingFromIdToName);
        }
        return $nameToId;
    }

    public static function getMappingFromNameToIdGoal()
    {
        static $nameToId = null;
        if ($nameToId === null) {
            $nameToId = array_flip(self::$mappingFromIdToNameGoal);
        }
        return $nameToId;
    }

    /**
     * Is a lower value for a given column better?
     * @param $column
     * @return bool
     *
     * @ignore
     */
    public static function isLowerValueBetter($column)
    {
        $isLowerBetter = null;

        /**
         * Use this event to define if a lower value of a metric is better.
         *
         * @param string $isLowerBetter should be set to a boolean indicating if lower is better
         * @param string $column name of the column to determine
         *
         * **Example**
         *
         * public function checkIsLowerMetricValueBetter(&$isLowerBetter, $metric)
         * {
         *     if ($metric === 'position') {
         *         $isLowerBetter = true;
         *     }
         * }
         */
        Piwik::postEvent('Metrics.isLowerValueBetter', [&$isLowerBetter, $column]);

        if (!is_null($isLowerBetter)) {
            return true;
        }

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
    public static function getUnit($column, $idSite)
    {
        $nameToUnit = array(
            '_rate'   => '%',
            'revenue' => Site::getCurrencySymbolFor($idSite),
            '_time_'  => 's'
        );

        $unit = null;

        /**
         * Use this event to define units for custom metrics used in evolution graphs and row evolution only.
         *
         * @param string $unit should hold the unit (e.g. %, €, s or empty string)
         * @param string $column name of the column to determine
         * @param string $idSite id of the current site
         */
        Piwik::postEvent('Metrics.getEvolutionUnit', [&$unit, $column, $idSite]);

        if (!empty($unit)) {
            return $unit;
        }

        foreach ($nameToUnit as $pattern => $type) {
            if (strpos($column, $pattern) !== false) {
                return $type;
            }
        }

        return '';
    }

    public static function getDefaultMetricSemanticTypes(): array
    {
        $cacheId = CacheId::pluginAware('DefaultMetricSemanticTypes');
        $cache = PiwikCache::getTransientCache();

        $types = $cache->fetch($cacheId);
        if (empty($types)) {
            $types = [
                'nb_visits'                     => Dimension::TYPE_NUMBER,
                'nb_uniq_visitors'              => Dimension::TYPE_NUMBER,
                'nb_actions'                    => Dimension::TYPE_NUMBER,
                'nb_users'                      => Dimension::TYPE_NUMBER,
                'avg_time_on_page'              => Dimension::TYPE_DURATION_S,
                'sum_time_spent'                => Dimension::TYPE_DURATION_S,
                'sum_visit_length'              => Dimension::TYPE_DURATION_S,
                'bounce_count'                  => Dimension::TYPE_NUMBER,
                'bounce_count_returning'        => Dimension::TYPE_NUMBER,
                'max_actions'                   => Dimension::TYPE_NUMBER,
                'max_actions_returning'         => Dimension::TYPE_NUMBER,
                'nb_visits_converted_returning' => Dimension::TYPE_NUMBER,
                'sum_visit_length_returning'    => Dimension::TYPE_NUMBER,
                'nb_visits_converted'           => Dimension::TYPE_NUMBER,
                'nb_conversions'                => Dimension::TYPE_NUMBER,
                'revenue'                       => Dimension::TYPE_MONEY,
                'nb_hits'                       => Dimension::TYPE_NUMBER,
                'entry_nb_visits'               => Dimension::TYPE_NUMBER,
                'entry_nb_uniq_visitors'        => Dimension::TYPE_NUMBER,
                'exit_nb_visits'                => Dimension::TYPE_NUMBER,
                'exit_nb_uniq_visitors'         => Dimension::TYPE_NUMBER,
                'entry_bounce_count'            => Dimension::TYPE_NUMBER,
                'exit_bounce_count'             => Dimension::TYPE_NUMBER,
                'exit_rate'                     => Dimension::TYPE_PERCENT,
                'sum_daily_nb_uniq_visitors'    => Dimension::TYPE_NUMBER,
                'sum_daily_nb_users'            => Dimension::TYPE_NUMBER,
                'sum_daily_entry_nb_uniq_visitors' => Dimension::TYPE_NUMBER,
                'sum_daily_exit_nb_uniq_visitors' => Dimension::TYPE_NUMBER,
                'entry_nb_actions'              => Dimension::TYPE_NUMBER,
                'entry_sum_visit_length'        => Dimension::TYPE_NUMBER,
                'nb_actions_per_visit'          => Dimension::TYPE_NUMBER,
                'avg_time_on_site'              => Dimension::TYPE_DURATION_S,
                'bounce_rate'                   => Dimension::TYPE_PERCENT,
                'conversion_rate'               => Dimension::TYPE_PERCENT,
            ];

            /**
             * Use this event to notify Matomo of the semantic types of metrics your plugin adds.
             *
             * A metric's semantic type is metadata used primarily in integrations with Matomo
             * and third party services/applications. It provides information that can be used
             * to determine how to display or use the information.
             *
             * It is recommended for your plugin to provide this information so users of third
             * party services that connect with Matomo can make full use of the data your plugin
             * tracks.
             *
             * See {@link Metrics} for the list of available semantic types.
             *
             * @param string $types The array mapping of metric_name => metric semantic type
             */
            Piwik::postEvent('Metrics.getDefaultMetricSemanticTypes', [&$types]);

            $cache->save($cacheId, $types);
        }

        return $types;
    }

    public static function getDefaultMetricTranslations()
    {
        $cacheId = CacheId::pluginAware('DefaultMetricTranslations');
        $cache   = PiwikCache::getTransientCache();

        if ($cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        $translations = array(
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
            'revenue'                       => 'General_ColumnRevenue',
            'nb_hits'                       => 'General_ColumnPageviews',
            'entry_nb_visits'               => 'General_ColumnEntrances',
            'entry_nb_uniq_visitors'        => 'General_ColumnUniqueEntrances',
            'exit_nb_visits'                => 'General_ColumnExits',
            'exit_nb_uniq_visitors'         => 'General_ColumnUniqueExits',
            'entry_bounce_count'            => 'General_ColumnBounces',
            'exit_bounce_count'             => 'General_ColumnBounces',
            'exit_rate'                     => 'General_ColumnExitRate',
        );

        $dailySum = ' (' . Piwik::translate('General_DailySum') . ')';
        $afterEntry = ' ' . Piwik::translate('General_AfterEntry');

        $translations['sum_daily_nb_uniq_visitors'] = Piwik::translate('General_ColumnNbUniqVisitors') . $dailySum;
        $translations['sum_daily_nb_users'] = Piwik::translate('General_ColumnNbUsers') . $dailySum;
        $translations['sum_daily_entry_nb_uniq_visitors'] = Piwik::translate('General_ColumnUniqueEntrances') . $dailySum;
        $translations['sum_daily_exit_nb_uniq_visitors'] = Piwik::translate('General_ColumnUniqueExits') . $dailySum;
        $translations['entry_nb_actions'] = Piwik::translate('General_ColumnNbActions') . $afterEntry;
        $translations['entry_sum_visit_length'] = Piwik::translate('General_ColumnSumVisitLength') . $afterEntry;

        $translations = array_merge(self::getDefaultMetrics(), self::getDefaultProcessedMetrics(), $translations);

        /**
         * Use this event to register translations for metrics processed by your plugin.
         *
         * @param string $translations The array mapping of column_name => Plugin_TranslationForColumn
         */
        Piwik::postEvent('Metrics.getDefaultMetricTranslations', array(&$translations));

        $translations = array_map(array('\\Piwik\\Piwik', 'translate'), $translations);

        $cache->save($cacheId, $translations);

        return $translations;
    }

    public static function getDefaultMetrics()
    {
        $cacheId = CacheId::languageAware('DefaultMetrics');
        $cache   = PiwikCache::getTransientCache();

        if ($cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        $translations = array(
            'nb_visits'        => 'General_ColumnNbVisits',
            'nb_uniq_visitors' => 'General_ColumnNbUniqVisitors',
            'nb_actions'       => 'General_ColumnNbActions',
            'nb_users'         => 'General_ColumnNbUsers',
        );
        $translations = array_map(array('\\Piwik\\Piwik', 'translate'), $translations);

        $cache->save($cacheId, $translations);

        return $translations;
    }

    public static function getDefaultProcessedMetrics()
    {
        $cacheId = CacheId::languageAware('DefaultProcessedMetrics');
        $cache   = PiwikCache::getTransientCache();

        if ($cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        $translations = array(
            // Processed in AddColumnsProcessedMetrics
            'nb_actions_per_visit' => 'General_ColumnActionsPerVisit',
            'avg_time_on_site'     => 'General_ColumnAvgTimeOnSite',
            'bounce_rate'          => 'General_ColumnBounceRate',
            'conversion_rate'      => 'General_ColumnConversionRate',
        );
        $translations = array_map(array('\\Piwik\\Piwik', 'translate'), $translations);

        $cache->save($cacheId, $translations);

        return $translations;
    }

    public static function getReadableColumnName($columnIdRaw)
    {
        $mappingIdToName = self::$mappingFromIdToName;

        if (array_key_exists($columnIdRaw, $mappingIdToName)) {
            return $mappingIdToName[$columnIdRaw];
        }

        return $columnIdRaw;
    }

    public static function getMetricIdsToProcessReportTotal()
    {
        return array(
            self::INDEX_NB_VISITS,
            self::INDEX_NB_UNIQ_VISITORS,
            self::INDEX_NB_ACTIONS,
            self::INDEX_NB_HITS,
            self::INDEX_PAGE_NB_HITS,
            self::INDEX_NB_VISITS_CONVERTED,
            self::INDEX_NB_CONVERSIONS,
            self::INDEX_BOUNCE_COUNT,
            self::INDEX_PAGE_ENTRY_BOUNCE_COUNT,
            self::INDEX_PAGE_ENTRY_NB_VISITS,
            self::INDEX_PAGE_ENTRY_NB_ACTIONS,
            self::INDEX_PAGE_EXIT_NB_VISITS,
            self::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
            self::INDEX_REVENUE
        );
    }

    public static function getDefaultMetricsDocumentation()
    {
        $cacheId = CacheId::pluginAware('DefaultMetricsDocumentation');
        $cache   = PiwikCache::getTransientCache();

        if ($cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        $translations = array(
            'nb_visits'            => 'General_ColumnNbVisitsDocumentation',
            'nb_uniq_visitors'     => 'General_ColumnNbUniqVisitorsDocumentation',
            'nb_actions'           => 'General_ColumnNbActionsDocumentation',
            'nb_users'             => 'General_ColumnNbUsersDocumentation',
            'nb_actions_per_visit' => 'General_ColumnActionsPerVisitDocumentation',
            'avg_time_on_site'     => 'General_ColumnAvgTimeOnSiteDocumentation',
            'bounce_rate'          => 'General_ColumnBounceRateDocumentation',
            'conversion_rate'      => 'General_ColumnConversionRateDocumentation',
            'avg_time_on_page'     => 'General_ColumnAverageTimeOnPageDocumentation',
            'nb_hits'              => 'General_ColumnPageviewsDocumentation',
            'exit_rate'            => 'General_ColumnExitRateDocumentation'
        );

        /**
         * Use this event to register translations for metrics documentation processed by your plugin.
         *
         * @param string[] $translations The array mapping of column_name => Plugin_TranslationForColumnDocumentation
         */
        Piwik::postEvent('Metrics.getDefaultMetricDocumentationTranslations', array(&$translations));

        $translations = array_map(array('\\Piwik\\Piwik', 'translate'), $translations);

        $cache->save($cacheId, $translations);

        return $translations;
    }

    public static function getPercentVisitColumn()
    {
        $percentVisitsLabel = str_replace(' ', '&nbsp;', Piwik::translate('General_ColumnPercentageVisits'));
        return $percentVisitsLabel;
    }

    /**
     * This is a utility method used when building records through log aggregation.
     *
     * In records with per-goal conversion metrics the metrics are stored within DataTable Rows
     * as a column with an array a value. The array is indexed by the goal ID and the column name
     * is set to `Metrics::INDEX_GOALS`, for example:
     *
     * ```
     * $columns = [
     *     Metrics::INDEX_GOALS = [
     *         $idGoal => [
     *             // ... conversion metrics ...
     *         ],
     *     ],
     * ];
     * $row = new Row([DataTable::COLUMNS => $columns]);
     * ```
     *
     * This methods returns an array like `$columns` above based on a goal ID and a row of
     * metric values for the goal. The result can be added directly to a DataTable record via `sumRowWithLabel()`.
     *
     * The goal metrics returned will differ based on whether the goal is user defined or an ecommerce goal.
     *
     * @param int $idGoal
     * @param array $goalsMetrics
     * @return array
     */
    public static function makeGoalColumnsRow(int $idGoal, array $goalsMetrics): array
    {
        if ($idGoal > GoalManager::IDGOAL_ORDER) { // user defined goal
            $columns = [
                Metrics::INDEX_GOAL_NB_CONVERSIONS,
                Metrics::INDEX_GOAL_NB_VISITS_CONVERTED,
                Metrics::INDEX_GOAL_REVENUE,
            ];
        } elseif ($idGoal == GoalManager::IDGOAL_ORDER) { // ecommerce order
            $columns = [
                Metrics::INDEX_GOAL_NB_CONVERSIONS,
                Metrics::INDEX_GOAL_NB_VISITS_CONVERTED,
                Metrics::INDEX_GOAL_REVENUE,
                Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL,
                Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_TAX,
                Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING,
                Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT,
                Metrics::INDEX_GOAL_ECOMMERCE_ITEMS,
            ];
        } else { // idGoal == GoalManager::IDGOAL_CART (abandoned cart)
            $columns = [
                Metrics::INDEX_GOAL_NB_CONVERSIONS,
                Metrics::INDEX_GOAL_NB_VISITS_CONVERTED,
                Metrics::INDEX_GOAL_REVENUE,
                Metrics::INDEX_GOAL_ECOMMERCE_ITEMS,
            ];
        }

        $values = [];
        foreach ($columns as $column) {
            $values[$column] = (float)($goalsMetrics[$column] ?? 0);
        }
        return $values;
    }
}
