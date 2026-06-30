<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals;

use Exception;
use Piwik\API\Request;
use Piwik\Archive;
use Piwik\CacheId;
use Piwik\Cache as PiwikCache;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DbHelper;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\API\DataTable\MergeDataTables;
use Piwik\Plugins\CoreHome\Columns\Metrics\ConversionRate;
use Piwik\Plugins\Goals\Columns\Metrics\AverageOrderRevenue;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\Goals\Columns\Metrics\GoalConversionRate;
use Piwik\Plugins\Goals\Reports\GetMetrics;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Tracker\GoalManager;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyAPI;
use Piwik\Validators\Regex;
use Piwik\Validators\WhitelistedValue;

/**
 * Goals API lets you Manage existing goals, via "updateGoal" and "deleteGoal", create new Goals via "addGoal",
 * or list existing Goals for one or several websites via "getGoals"
 *
 * If you are <a href='https://matomo.org/docs/ecommerce-analytics/' target='_blank'>tracking Ecommerce orders and products</a> on your site, the functions "getItemsSku", "getItemsName" and "getItemsCategory"
 * will return the list of products purchased on your site, either grouped by Product SKU, Product Name or Product Category. For each name, SKU or category, the following
 * metrics are returned: Total revenue, Total quantity, average price, average quantity, number of orders (or abandoned carts) containing this product, number of visits on the Product page,
 * Conversion rate.
 *
 * By default, these functions return the 'Products purchased'. These functions also accept an optional parameter &abandonedCarts=1.
 * If the parameter is set, it will instead return the metrics for products that were left in an abandoned cart therefore not purchased.
 *
 * The API also lets you request overall Goal metrics via the method "get": Conversions, Visits with at least one conversion, Conversion rate and Revenue.
 * If you wish to request specific metrics about Ecommerce goals, you can set the parameter &idGoal=ecommerceAbandonedCart to get metrics about abandoned carts (including Lost revenue, and number of items left in the cart)
 * or &idGoal=ecommerceOrder to get metrics about Ecommerce orders (number of orders, visits with an order, subtotal, tax, shipping, discount, revenue, items ordered)
 *
 * See also the documentation about <a href='https://matomo.org/docs/tracking-goals-web-analytics/' rel='noreferrer' target='_blank'>Tracking Goals</a> in Matomo.
 *
 * @method static \Piwik\Plugins\Goals\API getInstance()
 *
 * @phpstan-import-type GoalStoredRecord from Model
 * @phpstan-type GoalMatchAttribute 'url'|'title'|'file'|'external_website'|'manually'|'visit_duration'|'visit_total_actions'|'visit_total_pageviews'|'event_action'|'event_category'|'event_name'
 * @phpstan-type GoalPatternType ''|'regex'|'contains'|'exact'|'greater_than'
 * @phpstan-type GoalRecord array{
 *     idgoal: int|string,
 *     idsite?: int|string,
 *     name: string,
 *     description: string,
 *     match_attribute: GoalMatchAttribute|string,
 *     pattern?: string,
 *     pattern_type?: GoalPatternType|string,
 *     case_sensitive?: int|string,
 *     allow_multiple?: int|string,
 *     revenue: float|int|string,
 *     event_value_as_revenue: int|string,
 *     deleted?: int|string
 * }
 */
class API extends \Piwik\Plugin\API
{
    public const AVG_PRICE_VIEWED = 'avg_price_viewed';

    /**
     * Return a single goal.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @param int $idGoal The numeric ID of the goal to query.
     * @return array|null Goal attributes, or `null` if the goal does not exist.
     * @phpstan-return GoalRecord|null
     */
    public function getGoal(int $idSite, int $idGoal): ?array
    {
        Piwik::checkUserHasViewAccess($idSite);

        $goal = $this->getModel()->getActiveGoal($idSite, $idGoal);

        if (!empty($goal)) {
            return $this->formatGoal($goal);
        }

        return null;
    }

    /**
     * Returns all goals for one or more websites.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param bool $orderByName Whether to sort the returned goals alphabetically by name.
     * @return array Goal attributes, indexed by goal ID for single-site requests
     *               and returned as a numeric list for multi-site requests.
     * @phpstan-return array<int, GoalRecord>
     */
    public function getGoals($idSite, bool $orderByName = false): array
    {
        if (is_array($idSite)) {
            $idSite = array_map('intval', $idSite);
            $idSite = implode(',', $idSite);
        }

        $cacheId = self::getCacheId($idSite);
        $cache = $this->getGoalsInfoStaticCache();
        if (!$cache->contains($cacheId)) {
            // note: the reason this is secure is because the above cache is a static cache and cleared after each request
            // if we were to use a different cache that persists the result, this would not be secure because when a
            // result is in the cache, it would just return the result
            $idSite = Site::getIdSitesFromIdSitesString($idSite, false, true);

            if (empty($idSite)) {
                return [];
            }

            Piwik::checkUserHasViewAccess($idSite);

            $goals = $this->getModel()->getActiveGoals($idSite);
            $cleanedGoals = [];
            $indexByIdGoal = 1 === count($idSite);

            foreach ($goals as &$goal) {
                if ($indexByIdGoal) {
                    $cleanedGoals[$goal['idgoal']] = $this->formatGoal($goal);
                } else {
                    $cleanedGoals[] = $this->formatGoal($goal);
                }
            }

            $cache->save($cacheId, $cleanedGoals);
        }

        /** @var array<int, GoalRecord> $goals */
        $goals = $cache->fetch($cacheId);

        if ($orderByName) {
            uasort($goals, function ($a, $b) {
                if ($a['name'] == $b['name']) {
                    return $a['idgoal'] > $b['idgoal'] ? -1 : 1;
                }

                return strcasecmp($a['name'], $b['name']);
            });
        }

        return $goals;
    }

    /**
     * @phpstan-param GoalStoredRecord $goal
     * @phpstan-return GoalRecord
     */
    private function formatGoal(array $goal): array
    {
        $goal['name'] = Common::unsanitizeInputValue($goal['name']);
        $goal['description'] = Common::unsanitizeInputValue($goal['description']);
        $goal['pattern_type'] = Common::unsanitizeInputValue($goal['pattern_type']);
        $goal['pattern'] = Common::unsanitizeInputValue($goal['pattern']);

        if ($goal['match_attribute'] == 'manually') {
            unset($goal['pattern']);
            unset($goal['pattern_type']);
            unset($goal['case_sensitive']);
        }

        return $goal;
    }

    /**
     * Creates a goal for a website.
     *
     * @param int $idSite The numeric ID of the website to configure the goal for.
     * @param string $name Goal name.
     * @param string $matchAttribute Attribute used to match conversions.
     * @phpstan-param GoalMatchAttribute $matchAttribute
     * @param string $pattern Match pattern. Use a URL, title, filename, external website, or event value for string
     *                        match attributes; use a numeric threshold for visit duration, actions, or pageview
     *                        match attributes; this value is ignored for `manually`.
     * @param string $patternType Matching operator. Numeric match attributes only accept `greater_than`; string match
     *                            attributes accept `exact`, `contains`, or `regex`; use an empty string for `manually`.
     * @phpstan-param GoalPatternType $patternType
     * @param bool $caseSensitive Whether string matching should be case-sensitive.
     * @param bool|float $revenue Default revenue to assign to conversions. Pass `false` or `0` to disable a fixed
     *                            revenue value.
     * @param bool $allowMultipleConversionsPerVisit Whether multiple conversions may be recorded within one visit.
     * @param string $description Optional goal description shown in the Goals management UI.
     * @param bool $useEventValueAsRevenue Whether to use the tracked event value as goal revenue. This is only valid
     *                                     for event-based goals.
     * @return int ID of the new goal.
     */
    public function addGoal(
        int $idSite,
        string $name,
        $matchAttribute,
        $pattern,
        $patternType,
        $caseSensitive = false,
        $revenue = false,
        $allowMultipleConversionsPerVisit = false,
        string $description = '',
        $useEventValueAsRevenue = false
    ) {
        Piwik::checkUserHasWriteAccess($idSite);

        $patternType = Common::unsanitizeInputValue($patternType);

        $this->checkPatternIsValid($patternType, $pattern, $matchAttribute);
        $pattern = $this->checkPattern($pattern, $matchAttribute);
        $patternType = $this->checkPatternType($patternType, $matchAttribute);

        $revenue = Common::forceDotAsSeparatorForDecimalPoint((float)$revenue);

        $goal = array(
            'name' => $name,
            'description' => $description,
            'match_attribute' => $matchAttribute,
            'pattern' => $pattern,
            'pattern_type' => $patternType,
            'case_sensitive' => (int)$caseSensitive,
            'allow_multiple' => (int)$allowMultipleConversionsPerVisit,
            'revenue' => $revenue,
            'deleted' => 0,
            'event_value_as_revenue' => (int)$useEventValueAsRevenue,
        );

        $idGoal = $this->getModel()->createGoalForSite($idSite, $goal);

        $this->getGoalsInfoStaticCache()->delete(self::getCacheId($idSite));

        Cache::regenerateCacheWebsiteAttributes($idSite);
        return $idGoal;
    }

    private function getModel(): Model
    {
        return new Model();
    }

    /**
     * Updates an existing goal without reprocessing already recorded conversions.
     *
     * @param int $idSite The numeric ID of the website the goal belongs to.
     * @param int $idGoal Goal ID to update.
     * @param string $name Goal name.
     * @param string $matchAttribute Attribute used to match conversions.
     * @phpstan-param GoalMatchAttribute $matchAttribute
     * @param string $pattern Match pattern. Use a URL, title, filename, external website, or event value for string
     *                        match attributes; use a numeric threshold for visit duration, actions, or pageview
     *                        match attributes; this value is ignored for `manually`.
     * @param string $patternType Matching operator. Numeric match attributes only accept `greater_than`; string match
     *                            attributes accept `exact`, `contains`, or `regex`; use an empty string for `manually`.
     * @phpstan-param GoalPatternType $patternType
     * @param bool $caseSensitive Whether string matching should be case-sensitive.
     * @param bool|float $revenue Default revenue to assign to conversions. Pass `false` or `0` to disable a fixed
     *                            revenue value.
     * @param bool $allowMultipleConversionsPerVisit Whether multiple conversions may be recorded within one visit.
     * @param string $description Optional goal description shown in the Goals management UI.
     * @param bool $useEventValueAsRevenue Whether to use the tracked event value as goal revenue. This is only valid
     *                                     for event-based goals.
     */
    public function updateGoal(
        int $idSite,
        $idGoal,
        string $name,
        $matchAttribute,
        $pattern,
        $patternType,
        $caseSensitive = false,
        $revenue = false,
        $allowMultipleConversionsPerVisit = false,
        string $description = '',
        $useEventValueAsRevenue = false
    ): void {
        Piwik::checkUserHasWriteAccess($idSite);

        $patternType = Common::unsanitizeInputValue($patternType);

        $patternType = $this->checkPatternType($patternType, $matchAttribute);
        $pattern = $this->checkPattern($pattern, $matchAttribute);
        $this->checkPatternIsValid($patternType, $pattern, $matchAttribute);

        $revenue = Common::forceDotAsSeparatorForDecimalPoint((float)$revenue);

        $goal = array(
            'name' => $name,
            'description' => $description,
            'match_attribute' => $matchAttribute,
            'pattern' => $pattern,
            'pattern_type' => $patternType,
            'case_sensitive' => (int)$caseSensitive,
            'allow_multiple' => (int)$allowMultipleConversionsPerVisit,
            'revenue' => $revenue,
            'event_value_as_revenue' => (int)$useEventValueAsRevenue,
        );

        $this->checkEventValueAsRevenue($goal);

        $this->getModel()->updateGoal($idSite, $idGoal, $goal);

        $this->getGoalsInfoStaticCache()->delete(self::getCacheId($idSite));

        Cache::regenerateCacheWebsiteAttributes($idSite);
    }

    /**
     * @param array{
     *       name: string,
     *       description: string,
     *       match_attribute: GoalMatchAttribute|string,
     *       pattern: string,
     *       pattern_type: GoalPatternType|string,
     *       case_sensitive: int|string,
     *       allow_multiple: int|string,
     *       revenue: float|int|string,
     *       event_value_as_revenue: int|string,
     *       deleted?: int|string
     *   } $goal
     */
    private function checkEventValueAsRevenue(array $goal): void
    {
        if ($goal['event_value_as_revenue'] && !GoalManager::isEventMatchingGoal($goal)) {
            throw new \Exception("'useEventValueAsRevenue' can only be 1 if the goal matches an event attribute.");
        }
    }

    /**
     * @param string $patternType
     * @param string $pattern
     * @param string $matchAttribute
     */
    private function checkPatternIsValid($patternType, $pattern, $matchAttribute): void
    {
        if (
            $patternType == 'exact'
            && substr($pattern, 0, 4) != 'http'
            && substr($matchAttribute, 0, 6) != 'event_'
            && $matchAttribute != 'title'
        ) {
            throw new Exception(Piwik::translate('Goals_ExceptionInvalidMatchingString', array("http:// or https://", "http://www.yourwebsite.com/newsletter/subscribed.html")));
        }

        if ($patternType == 'regex') {
            $validator = new Regex();
            $validator->validate(GoalManager::formatRegex($pattern));
        }
    }

    /**
     * @param string|null $patternType
     * @param string $matchAttribute
     * @phpstan-return GoalPatternType
     */
    private function checkPatternType($patternType, $matchAttribute): string
    {
        if (empty($patternType)) {
            return '';
        }

        $patternType = strtolower($patternType);

        if (in_array($matchAttribute, GoalManager::$NUMERIC_MATCH_ATTRIBUTES)) {
            $validValues = ['greater_than'];
        } else {
            $validValues = ['exact', 'contains', 'regex'];
        }

        $validator = new WhitelistedValue($validValues);
        $validator->validate($patternType);

        return $patternType;
    }

    /**
     * @param string $pattern
     * @phpstan-param GoalMatchAttribute $matchAttribute
     */
    private function checkPattern($pattern, $matchAttribute): string
    {
        if (
            in_array($matchAttribute, GoalManager::$NUMERIC_MATCH_ATTRIBUTES)
            && !is_numeric($pattern)
        ) {
            throw new \Exception("Invalid pattern for match attribute '$matchAttribute'. (got '$pattern', expected numeric value).");
        }

        return $pattern;
    }

    /**
     * Soft deletes a given Goal.
     * Stats data in the archives will still be recorded, but not displayed.
     *
     * @param int $idSite The numeric ID of the website to query.
     * @param int $idGoal The numeric ID of the goal to delete.
     *
     * @return void
     */
    public function deleteGoal(int $idSite, $idGoal)
    {
        Piwik::checkUserHasWriteAccess($idSite);

        $this->getModel()->deleteGoal($idSite, $idGoal);
        $this->getModel()->deleteGoalConversions($idSite, $idGoal);

        $this->getGoalsInfoStaticCache()->delete(self::getCacheId($idSite));

        Cache::regenerateCacheWebsiteAttributes($idSite);
    }

    /**
     * Returns a datatable of Items SKU/name or categories and their metrics
     * If $abandonedCarts set to 1, will return items abandoned in carts. If set to 0, will return items ordered
     *
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param bool $abandonedCarts
     * @param string|null|false $segment
     * @return DataTable|DataTable\Map
     */
    protected function getItems(string $recordName, $idSite, string $period, string $date, $abandonedCarts, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $recordNameFinal = $recordName;
        if ($abandonedCarts) {
            $recordNameFinal = Archiver::getItemRecordNameAbandonedCart($recordName);
        }

        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($recordNameFinal);
        // ensure to use real column names from here on, as using the index can cause trouble when sorting by columns that don't exist in every row.
        $dataTable->filter('ReplaceColumnNames');

        // Before Matomo 4.0.0 ecommerce views were tracked in custom variables
        // So if Matomo was installed before try to fetch the views from custom variables and enrich the report
        if (version_compare(DbHelper::getInstallVersion(), '4.0.0-b2', '<')) {
            $this->enrichItemsTableWithViewMetrics($dataTable, $recordName, $idSite, $period, $date, $segment);
        }

        // use average ecommerce view price if no cart price is available
        $dataTable->filter(function (DataTable $table) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                if (!$row->getColumn('avg_price') && !$row->getColumn('price')) {
                    $row->renameColumn(self::AVG_PRICE_VIEWED, 'avg_price');
                }
                $row->deleteColumn(self::AVG_PRICE_VIEWED);
            }
        });

        $reportToNotDefinedString = array(
            'Goals_ItemsSku' => Piwik::translate('General_NotDefined', Piwik::translate('Goals_ProductSKU')), // Note: this should never happen
            'Goals_ItemsName' => Piwik::translate('General_NotDefined', Piwik::translate('Goals_ProductName')),
            'Goals_ItemsCategory' => Piwik::translate('General_NotDefined', Piwik::translate('Goals_ProductCategory')),
        );
        $notDefinedStringPretty = $reportToNotDefinedString[$recordName];
        $this->renameNotDefinedRow($dataTable, $notDefinedStringPretty);

        if ($abandonedCarts) {
            $ordersColumn = 'abandoned_carts';
            $dataTable->renameColumn('orders', $ordersColumn);
        }

        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->queueFilter('ColumnDelete', array('price'));

        return $dataTable;
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param string $notDefinedStringPretty
     */
    protected function renameNotDefinedRow($dataTable, $notDefinedStringPretty): void
    {
        if ($dataTable instanceof DataTable\Map) {
            foreach ($dataTable->getDataTables() as $table) {
                $this->renameNotDefinedRow($table, $notDefinedStringPretty);
            }
            return;
        }

        $rowNotDefined = $dataTable->getRowFromLabel('Value not defined');
        if ($rowNotDefined) {
            $rowNotDefined->setColumn('label', $notDefinedStringPretty);
        }
    }

    /**
     * @param DataTable $dataTable
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param int|null $idSubtable
     */
    protected function enrichItemsDataTableWithItemsViewMetrics($dataTable, $idSite, string $period, string $date, $segment, $idSubtable): void
    {
        if (!Manager::getInstance()->isPluginActivated('CustomVariables') || in_array('nb_visits', $dataTable->getColumns())) {
            // skip if CustomVariables plugin is not available or table already contains visits
            return;
        }

        /** @var DataTable $ecommerceViews */
        $ecommerceViews = \Piwik\Plugins\CustomVariables\API::getInstance()->getCustomVariablesValuesFromNameId($idSite, $period, $date, $idSubtable, $segment, $_leavePriceViewedColumn = true);

        // For Product names and SKU reports, and for Category report
        // Use the Price (tracked on page views)
        // ONLY when the price sold in conversions is not found (ie. product viewed but not sold)
        foreach ($ecommerceViews->getRows() as $rowView) {
            // If there is not already a 'sum price' for this product
            $rowFound = $dataTable->getRowFromLabel($rowView->getColumn('label'));
            $price = $rowFound
                ? $rowFound->getColumn(Metrics::INDEX_ECOMMERCE_ITEM_PRICE)
                : false;
            if (empty($price)) {
                // If a price was tracked on the product page
                if ($rowView->getColumn(Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED)) {
                    $rowView->renameColumn(Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED, self::AVG_PRICE_VIEWED);
                }
            }
            $rowView->deleteColumn(Metrics::INDEX_ECOMMERCE_ITEM_PRICE_VIEWED);
        }

        $ecommerceViews->filter('ReplaceColumnNames');

        $dataTable->addDataTable($ecommerceViews);
    }

    /**
     * Returns ecommerce product metrics grouped by product SKU.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param bool $abandonedCarts Whether to return abandoned-cart product metrics instead of purchased products.
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Ecommerce product metrics grouped by SKU.
     */
    public function getItemsSku($idSite, string $period, string $date, $abandonedCarts = false, $segment = false)
    {
        $dataTable = $this->getItems('Goals_ItemsSku', $idSite, $period, $date, $abandonedCarts, $segment);
        $dataTable->filter('AddSegmentByLabel', ['productSku']);
        return $dataTable;
    }

    /**
     * Returns ecommerce product metrics grouped by product name.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param bool $abandonedCarts Whether to return abandoned-cart product metrics instead of purchased products.
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Ecommerce product metrics grouped by name.
     */
    public function getItemsName($idSite, string $period, string $date, $abandonedCarts = false, $segment = false)
    {
        $dataTable = $this->getItems('Goals_ItemsName', $idSite, $period, $date, $abandonedCarts, $segment);
        $dataTable->filter('AddSegmentByLabel', ['productName']);
        return $dataTable;
    }

    /**
     * Returns ecommerce product metrics grouped by product category.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param bool $abandonedCarts Whether to return abandoned-cart product metrics instead of purchased products.
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Ecommerce product metrics grouped by category.
     */
    public function getItemsCategory($idSite, string $period, string $date, $abandonedCarts = false, $segment = false)
    {
        $dataTable = $this->getItems('Goals_ItemsCategory', $idSite, $period, $date, $abandonedCarts, $segment);
        $dataTable->filter('AddSegmentByLabel', ['productCategory']);
        return $dataTable;
    }

    /**
     * Helper function that checks for special string goal IDs and converts them to
     * their integer equivalents.
     *
     * Checks for the following values:
     * Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER
     * Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART
     *
     * @param int|string $idGoal The goal id as an integer or a special string.
     * @return int The numeric goal id.
     */
    protected static function convertSpecialGoalIds($idGoal)
    {
        if ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER) {
            return GoalManager::IDGOAL_ORDER;
        } elseif ($idGoal == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
            return GoalManager::IDGOAL_CART;
        } else {
            return $idGoal;
        }
    }

    /**
     * Returns goal and ecommerce metrics, including new and returning visitor variants.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string|false $idGoal Goal ID, `ecommerceOrder`, `ecommerceAbandonedCart`, or `false` for all goals.
     * @param string|string[] $columns Optional metric name or list of metric names to return, for example
     *                                 `nb_conversions`, `nb_visits_converted`, `conversion_rate`, `revenue`, or
     *                                 ecommerce-specific metrics such as `items` or `avg_order_revenue`.
     * @param bool $showAllGoalSpecificMetrics Whether to include per-goal metric columns when no specific goal is
     *                                         selected.
     * @param bool $compare Whether to prepare the table for a comparison report by deferring metric formatting.
     * @return DataTable|DataTable\Map Goal metrics with additional columns for all visits, new visits, and returning
     *                                 visits.
     */
    public function get($idSite, string $period, string $date, $segment = false, $idGoal = false, $columns = [], $showAllGoalSpecificMetrics = false, $compare = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        /** @var DataTable|DataTable\Map $table */
        $table = null;

        $segments = array(
            '' => false,
            '_new_visit' => VisitFrequencyAPI::NEW_VISITOR_SEGMENT,
            '_returning_visit' => VisitFrequencyAPI::RETURNING_VISITOR_SEGMENT,
        );

        foreach ($segments as $appendToMetricName => $predefinedSegment) {
            $startingArchiveDependent = \Piwik\Plugin\Archiver::$ARCHIVE_DEPENDENT;
            try {
                if (!empty($predefinedSegment)) {
                    // we are disabling the archiving of these segments as the archiver archives them already using
                    // archiveProcessDependend logic. Otherwise we would eg archive reports that we don't need:
                    // userid=5;visitorType%3D%3Dnew;visitorType%3D%3Dreturning%2CvisitorType%3D%3DreturningCustomer
                    // userid=5;visitorType%3D%3Dreturning%2CvisitorType%3D%3DreturningCustomer;visitorType%3D%3Dnew;
                    // it would also archive dependends for these segments that we already combined here and then combine
                    // segments again when archiving dependends
                    \Piwik\Plugin\Archiver::$ARCHIVE_DEPENDENT = false;
                }
                $segmentToUse = $this->appendSegment($segment, $predefinedSegment);

                /** @var DataTable|DataTable\Map $tableSegmented */
                $tableSegmented = Request::processRequest('Goals.getMetrics', array(
                    'segment' => $segmentToUse,
                    'idSite' => $idSite,
                    'period' => $period,
                    'date' => $date,
                    'idGoal' => $idGoal,
                    'columns' => $columns,
                    'showAllGoalSpecificMetrics' => $showAllGoalSpecificMetrics,
                    'format_metrics' => !empty($compare) ? 0 : Common::getRequestVar('format_metrics', 'bc'),
                ), $default = []);
            } finally {
                \Piwik\Plugin\Archiver::$ARCHIVE_DEPENDENT = $startingArchiveDependent;
            }
            $tableSegmented->filter(
                'Piwik\Plugins\Goals\DataTable\Filter\AppendNameToColumnNames',
                array($appendToMetricName)
            );

            if (!isset($table)) {
                $table = $tableSegmented;
            } else {
                $merger = new MergeDataTables();
                $merger->mergeDataTables($table, $tableSegmented);
            }
        }

        // if we are comparing, this will be queried with format_metrics=0, but we will eventually need to format the metrics.
        // unfortunately, we can't do that since the processed metric information is in the GetMetrics report. in this case,
        // we queue the filter so it will eventually be formatted. however, if the caller explicitly opted out of metric
        // formatting via format_metrics=0 (e.g. the evolution chart, which then plots the raw numbers itself), we must not
        // format the comparison rows either — formatting them turns revenue into a currency string and the chart cannot plot
        // it as a number.
        $formatMetricsRequest = \Piwik\Request::fromRequest()->getStringParameter('format_metrics', 'bc');
        if (!empty($compare) && $formatMetricsRequest !== '0') {
            $getMetricsReport = ReportsProvider::factory('Goals', 'getMetrics');
            $table->queueFilter(function (DataTable $t) use ($getMetricsReport) {
                $t->setMetadata(Metrics\Formatter::PROCESSED_METRICS_FORMATTED_FLAG, false);

                $formatter = new Metrics\Formatter();
                $formatter->formatMetrics($t, $getMetricsReport, $metricsToFormat = null, $formatAll = true);
            });
        }

        return $table;
    }

    /**
     * Similar to {@link get()} but does not return any metrics for new and returning visitors. It won't apply
     * any segment by default. This method is deprecated from the API as it is only there to make the implementation of
     * the actual {@link get()} method easy.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string|false $idGoal Goal ID, `ecommerceOrder`, `ecommerceAbandonedCart`, or `false` for all goals.
     * @param string|string[] $columns Optional metric name or list of metric names to return.
     * @param bool $showAllGoalSpecificMetrics Whether to show all goal-specific metrics when no goal is set.
     *
     * @return DataTable|DataTable\Map
     *
     * @deprecated
     * @internal
     */
    public function getMetrics($idSite, string $period, string $date, $segment = false, $idGoal = false, $columns = [], $showAllGoalSpecificMetrics = false)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);

        $showAllGoalSpecificMetrics = $showAllGoalSpecificMetrics && $idGoal === false;

        // Mapping string idGoal to internal ID
        $idGoal = self::convertSpecialGoalIds($idGoal);
        $isEcommerceGoal = $idGoal === GoalManager::IDGOAL_ORDER || $idGoal === GoalManager::IDGOAL_CART;

        $allMetrics = Goals::getGoalColumns($idGoal);

        if ($showAllGoalSpecificMetrics) {
            foreach ($this->getGoals($idSite) as $aGoal) {
                foreach (Goals::getGoalColumns($aGoal['idgoal']) as $goalColumn) {
                    $allMetrics[] = Goals::makeGoalColumn($aGoal['idgoal'], $goalColumn);
                }
            }
            $allMetrics[] = 'nb_visits';
        }

        $columnsToShow = Piwik::getArrayFromApiParameter($columns);
        $requestedColumns = $columnsToShow;

        $shouldAddAverageOrderRevenue = (in_array('avg_order_revenue', $requestedColumns) || empty($requestedColumns)) && $isEcommerceGoal;

        if ($shouldAddAverageOrderRevenue && !empty($requestedColumns)) {
            $avgOrder = new AverageOrderRevenue();
            $metricsToAdd = $avgOrder->getDependentMetrics();

            $requestedColumns = array_unique(array_merge($requestedColumns, $metricsToAdd));
        }

        if ($showAllGoalSpecificMetrics && !empty($requestedColumns)) {
            foreach ($requestedColumns as $requestedColumn) {
                if (strpos($requestedColumn, '_conversion_rate') !== false) {
                    $columnIdGoal = Goals::getGoalIdFromGoalColumn($requestedColumn);
                    if ($columnIdGoal) {
                        $goalConversionRate = new GoalConversionRate($idSite, $columnIdGoal);
                        $metricsToAdd = $goalConversionRate->getDependentMetrics();
                        $requestedColumns = array_unique(array_merge($requestedColumns, $metricsToAdd));
                    }
                }
            }
        }

        /** @var GetMetrics $report */
        $report = ReportsProvider::factory('Goals', 'getMetrics');
        $columnsToGet = $report->getMetricsRequiredForReport($allMetrics, $requestedColumns);

        $inDbMetricNames = array_map(function ($name) use ($idGoal) {
            $name = str_replace('goal_', '', $name);
            return $name == 'nb_visits' ? $name : Archiver::getRecordName($name, $idGoal);
        }, $columnsToGet);
        $dataTable = $archive->getDataTableFromNumeric($inDbMetricNames);

        if (count($columnsToGet) > 0) {
            $newNameMapping = array_combine($inDbMetricNames, $columnsToGet);
        } else {
            $newNameMapping = array();
        }
        $dataTable->filter('ReplaceColumnNames', array($newNameMapping));

        // TODO: this should be in Goals/Get.php but it depends on idGoal parameter which isn't always in _GET (ie,
        //       it's not in ProcessedReport.php). more refactoring must be done to report class before this can be
        //       corrected.
        if ($shouldAddAverageOrderRevenue) {
            $dataTable->filter(function (DataTable $table) {
                $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
                if (empty($extraProcessedMetrics)) {
                    $extraProcessedMetrics = array();
                }
                $extraProcessedMetrics[] = new AverageOrderRevenue();
                $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
            });
        }
        if ($showAllGoalSpecificMetrics) {
            $dataTable->filter(function (DataTable $table) use ($idSite, &$allMetrics, $requestedColumns) {
                $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);
                if (empty($extraProcessedMetrics)) {
                    $extraProcessedMetrics = array();
                }
                foreach ($this->getGoals($idSite) as $aGoal) {
                    $metric = new GoalConversionRate($idSite, $aGoal['idgoal']);
                    if (!empty($requestedColumns) && !in_array($metric->getName(), $requestedColumns)) {
                        continue;
                    }
                    $extraProcessedMetrics[] = $metric;
                    $allMetrics[] = $metric->getName();
                }
                $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
            });
        }

        // remove temporary metrics that were not explicitly requested
        if (empty($columnsToShow)) {
            $columnsToShow = $allMetrics;
            $columnsToShow[] = 'conversion_rate';
            if ($isEcommerceGoal) {
                $columnsToShow[] = 'avg_order_revenue';
            }
        }

        $dataTable->queueFilter('ColumnDelete', array($columnsToRemove = array(), $columnsToShow));

        return $dataTable;
    }

    /**
     * @param string|null|false $segment
     * @param string|null|false $segmentToAppend
     * @return string
     */
    protected function appendSegment($segment, $segmentToAppend)
    {
        return Segment::combine($segment, SegmentExpression::AND_DELIMITER, $segmentToAppend);
    }

    /**
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param string|string[] $toFetch
     * @return DataTable|DataTable\Map
     */
    protected function getNumeric($idSite, string $period, string $date, $segment, $toFetch)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTableFromNumeric($toFetch);
        return $dataTable;
    }

    /**
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param int|string|false $idGoal
     * @return DataTable|DataTable\Map
     * @ignore
     */
    public function getConversions($idSite, string $period, string $date, $segment = false, $idGoal = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, Archiver::getRecordName('nb_conversions', $idGoal));
    }

    /**
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param int|string|false $idGoal
     * @return DataTable|DataTable\Map
     * @ignore
     */
    public function getNbVisitsConverted($idSite, string $period, string $date, $segment = false, $idGoal = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, Archiver::getRecordName('nb_visits_converted', $idGoal));
    }

    /**
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param int|string|false $idGoal
     * @return DataTable|DataTable\Map
     * @ignore
     */
    public function getConversionRate($idSite, string $period, string $date, $segment = false, $idGoal = false)
    {
        $table = $this->get($idSite, $period, $date, $segment, $idGoal, 'conversion_rate');
        $table->filter(function (DataTable $dataTable) {
            $dataTable->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [new ConversionRate()]);
        });
        return $table;
    }

    /**
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param int|string|false $idGoal
     * @return DataTable|DataTable\Map
     * @ignore
     */
    public function getRevenue($idSite, string $period, string $date, $segment = false, $idGoal = false)
    {
        return $this->getNumeric($idSite, $period, $date, $segment, Archiver::getRecordName('revenue', $idGoal));
    }

    /**
     * Loads a goal-specific archived table and resolves special ecommerce goal identifiers before querying.
     *
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @param int|string|false $idGoal Goal ID, `ecommerceOrder`, `ecommerceAbandonedCart`, or `false` for all goals.
     * @return DataTable|DataTable\Map
     */
    protected function getGoalSpecificDataTable(string $recordName, $idSite, string $period, string $date, $segment, $idGoal)
    {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, $segment);

        // check for the special goal ids
        $realGoalId = !$idGoal ? false : self::convertSpecialGoalIds($idGoal);

        // get the data table
        $dataTable = $archive->getDataTable(Archiver::getRecordName($recordName, $realGoalId), $idSubtable = null);
        $dataTable->queueFilter('ReplaceColumnNames');

        return $dataTable;
    }

    /**
     * Returns conversions grouped by the number of days between first visit and conversion.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string|false $idGoal Goal ID, `ecommerceOrder`, `ecommerceAbandonedCart`, or `false` for all goals.
     * @return DataTable|DataTable\Map Conversion counts grouped by days until conversion.
     */
    public function getDaysToConversion($idSite, string $period, string $date, $segment = false, $idGoal = false)
    {
        $dataTable = $this->getGoalSpecificDataTable(
            Archiver::DAYS_UNTIL_CONV_RECORD_NAME,
            $idSite,
            $period,
            $date,
            $segment,
            $idGoal
        );

        $dataTable->queueFilter('Sort', array('label', 'asc', true, false));
        $dataTable->queueFilter(
            'BeautifyRangeLabels',
            array(Piwik::translate('Intl_OneDay'), Piwik::translate('Intl_NDays'))
        );

        return $dataTable;
    }

    /**
     * Returns conversions grouped by the visit count before conversion.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|string|false $idGoal Goal ID, `ecommerceOrder`, `ecommerceAbandonedCart`, or `false` for all goals.
     * @return DataTable|DataTable\Map Conversion counts grouped by visits until conversion.
     */
    public function getVisitsUntilConversion($idSite, string $period, string $date, $segment = false, $idGoal = false)
    {
        $dataTable = $this->getGoalSpecificDataTable(
            Archiver::VISITS_UNTIL_RECORD_NAME,
            $idSite,
            $period,
            $date,
            $segment,
            $idGoal
        );

        $dataTable->queueFilter('Sort', array('label', 'asc', true, false));
        $dataTable->queueFilter(
            'BeautifyRangeLabels',
            array(Piwik::translate('General_OneVisit'), Piwik::translate('General_NVisits'))
        );

        return $dataTable;
    }

    /**
     * Enriches ecommerce item reports with product-view metrics from the Custom Variables plugin for pre-4.0 data.
     *
     * @param DataTable|DataTable\Map $dataTable
     * @param string $recordName Goals item archive name used to resolve the matching custom variable key.
     * @param int|string|int[] $idSite
     * @param 'day'|'week'|'month'|'year'|'range' $period
     * @param string|null|false $segment
     * @return void
     */
    protected function enrichItemsTableWithViewMetrics($dataTable, string $recordName, $idSite, string $period, string $date, $segment)
    {
        if (!Manager::getInstance()->isPluginActivated('CustomVariables')) {
            return;
        }

        // Enrich the datatable with Product/Categories views, and conversion rates
        $customVariables = \Piwik\Plugins\CustomVariables\API::getInstance()->getCustomVariables(
            $idSite,
            $period,
            $date,
            $segment,
            $expanded = false,
            $_leavePiwikCoreVariables = true
        );
        $mapping = array(
            'Goals_ItemsSku'      => '_pks',
            'Goals_ItemsName'     => '_pkn',
            'Goals_ItemsCategory' => '_pkc',
        );
        $customVarNameToLookFor = $mapping[$recordName];

        // Handle case where date=last30&period=day
        if ($customVariables instanceof DataTable\Map) {
            $customVariableDatatables = $customVariables->getDataTables();
            /** @var DataTable\Map $dataTable */
            $dataTables = $dataTable->getDataTables();
            foreach ($customVariableDatatables as $key => $customVariableTableForDate) {
                /** @var DataTable $dataTableForDate */
                $dataTableForDate = $dataTables[$key] ?? new DataTable();

                // we do not enter the IF
                // if case idSite=1,3 AND period=day&date=datefrom,dateto,
                if (
                    $customVariableTableForDate instanceof DataTable
                    && $customVariableTableForDate->getMetadata(Archive\DataTableFactory::TABLE_METADATA_PERIOD_INDEX)
                ) {
                    $dateRewrite = $customVariableTableForDate->getMetadata(Archive\DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart()->toString();
                    $row = $customVariableTableForDate->getRowFromLabel($customVarNameToLookFor);
                    if ($row) {
                        $idSubtable = $row->getIdSubDataTable();
                        $this->enrichItemsDataTableWithItemsViewMetrics($dataTableForDate, $idSite, $period, $dateRewrite, $segment, $idSubtable);
                    }
                    $dataTable->addTable($dataTableForDate, $key);
                }
            }
        } elseif ($customVariables instanceof DataTable) {
            $row = $customVariables->getRowFromLabel($customVarNameToLookFor);
            if ($row) {
                $idSubtable = $row->getIdSubDataTable();
                /** @var DataTable $dataTable */
                $this->enrichItemsDataTableWithItemsViewMetrics($dataTable, $idSite, $period, $date, $segment, $idSubtable);
            }
        }
    }

    /**
     * @param int|string $idSite
     */
    private function getCacheId($idSite): string
    {
        return CacheId::pluginAware('Goals.getGoals.' . $idSite);
    }

    private function getGoalsInfoStaticCache(): \Matomo\Cache\Transient
    {
        return PiwikCache::getTransientCache();
    }
}
