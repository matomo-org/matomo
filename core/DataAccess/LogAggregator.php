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
 * This class queries the Visitor logs tables (visits, actions, conversions, ecommerce)
 * and returns aggregate data.
 */
class Piwik_DataAccess_LogAggregator
{
    const LOG_VISIT_TABLE = 'log_visit';

    const LOG_ACTIONS_TABLE = 'log_link_visit_action';

    const LOG_CONVERSION_TABLE = "log_conversion";

    const REVENUE_SUBTOTAL_FIELD = 'revenue_subtotal';

    const REVENUE_TAX_FIELD = 'revenue_tax';

    const REVENUE_SHIPPING_FIELD = 'revenue_shipping';

    const REVENUE_DISCOUNT_FIELD = 'revenue_discount';

    const TOTAL_REVENUE_FIELD = 'revenue';

    const ITEMS_COUNT_FIELD = "items";

    const CONVERSION_DATETIME_FIELD = "server_time";

    const ACTION_DATETIME_FIELD = "server_time";

    const VISIT_DATETIME_FIELD = 'visit_last_action_time';

    const IDGOAL_FIELD = 'idgoal';

    const FIELDS_SEPARATOR = ", \n\t\t\t";

    /** @var \Piwik_Date */
    protected $dateStart;

    /** @var \Piwik_Date */
    protected $dateEnd;

    /** @var \Piwik_Site */
    protected $site;

    /** @var \Piwik_Segment */
    protected $segment;

    public function __construct(Piwik_Date $dateStart, Piwik_Date $dateEnd, Piwik_Site $site, Piwik_Segment $segment)
    {
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
        $this->segment = $segment;
        $this->site = $site;
    }

    public function generateQuery($select, $from, $where, $groupBy, $orderBy)
    {
        $bind = $this->getBindDatetimeSite();
        $query = $this->segment->getSelectQuery($select, $from, $where, $bind, $orderBy, $groupBy);
        return $query;
    }

    protected function getVisitsMetricFields()
    {
        return array(
            Piwik_Metrics::INDEX_NB_UNIQ_VISITORS    => "count(distinct " . self::LOG_VISIT_TABLE . ".idvisitor)",
            Piwik_Metrics::INDEX_NB_VISITS           => "count(*)",
            Piwik_Metrics::INDEX_NB_ACTIONS          => "sum(" . self::LOG_VISIT_TABLE . ".visit_total_actions)",
            Piwik_Metrics::INDEX_MAX_ACTIONS         => "max(" . self::LOG_VISIT_TABLE . ".visit_total_actions)",
            Piwik_Metrics::INDEX_SUM_VISIT_LENGTH    => "sum(" . self::LOG_VISIT_TABLE . ".visit_total_time)",
            Piwik_Metrics::INDEX_BOUNCE_COUNT        => "sum(case " . self::LOG_VISIT_TABLE . ".visit_total_actions when 1 then 1 when 0 then 1 else 0 end)",
            Piwik_Metrics::INDEX_NB_VISITS_CONVERTED => "sum(case " . self::LOG_VISIT_TABLE . ".visit_goal_converted when 1 then 1 else 0 end)",
        );
    }

    static public function getConversionsMetricFields()
    {
        return array(
            Piwik_Metrics::INDEX_GOAL_NB_CONVERSIONS             => "count(*)",
            Piwik_Metrics::INDEX_GOAL_NB_VISITS_CONVERTED        => "count(distinct " . self::LOG_CONVERSION_TABLE . ".idvisit)",
            Piwik_Metrics::INDEX_GOAL_REVENUE                    => self::getSqlConversionRevenueSum(self::TOTAL_REVENUE_FIELD),
            Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL => self::getSqlConversionRevenueSum(self::REVENUE_SUBTOTAL_FIELD),
            Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_TAX      => self::getSqlConversionRevenueSum(self::REVENUE_TAX_FIELD),
            Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING => self::getSqlConversionRevenueSum(self::REVENUE_SHIPPING_FIELD),
            Piwik_Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT => self::getSqlConversionRevenueSum(self::REVENUE_DISCOUNT_FIELD),
            Piwik_Metrics::INDEX_GOAL_ECOMMERCE_ITEMS            => "SUM(" . self::LOG_CONVERSION_TABLE . "." . self::ITEMS_COUNT_FIELD . ")",
        );
    }

    static private function getSqlConversionRevenueSum($field)
    {
        return self::getSqlRevenue('SUM(' . self::LOG_CONVERSION_TABLE . '.' . $field . ')');
    }

    static public function getSqlRevenue($field)
    {
        return "ROUND(" . $field . "," . Piwik_Tracker_GoalManager::REVENUE_PRECISION . ")";
    }

    /**
     * Query visits logs by dimension, and return the aggregate data.
     *
     * @param array|string $dimensions     Can be a string, eg. "referer_name", will be aliased as 'label' in the returned rows
     *                                      Can also be an array of strings, when the dimension spans multiple fields,
     *                                      eg. array("referer_name", "referer_keyword")
     * @param bool|string $where Additional condition for WHERE clause
     * @param array $additionalSelects Additional SELECT clause
     * @param bool|array $metrics   Set this if you want to limit the columns that are returned.
     *                                      The possible values in the array are Piwik_Metrics::INDEX_*.
     * @param bool|Piwik_RankingQuery $rankingQuery
     *                                      A pre-configured ranking query instance that is used to limit the result.
     *                                      If set, the return value is the array returned by Piwik_RankingQuery::execute().
     *
     * @return mixed
     */
    public function queryVisitsByDimension(array $dimensions = array(), $where = false, array $additionalSelects = array(), $metrics = false, $rankingQuery = false)
    {
        $tableName = self::LOG_VISIT_TABLE;
        $availableMetrics = $this->getVisitsMetricFields();

        $select = $this->getSelectStatement($dimensions, $tableName, $additionalSelects, $availableMetrics, $metrics);
        $from = array($tableName);
        $where = $this->getWhereStatement($tableName, self::VISIT_DATETIME_FIELD, $where);
        $groupBy = $this->getGroupByStatement($dimensions, $tableName);
        $orderBy = false;

        if ($rankingQuery) {
            $orderBy = '`' . Piwik_Metrics::INDEX_NB_VISITS . '` DESC';
        }
        $query = $this->generateQuery($select, $from, $where, $groupBy, $orderBy);

        if ($rankingQuery) {
            unset($availableMetrics[Piwik_Metrics::INDEX_MAX_ACTIONS]);
            $sumColumns = array_keys($availableMetrics);
            if ($metrics) {
                $sumColumns = array_intersect($sumColumns, $metrics);
            }
            $rankingQuery->addColumn($sumColumns, 'sum');
            if ($this->isMetricRequested(Piwik_Metrics::INDEX_MAX_ACTIONS, $metrics)) {
                $rankingQuery->addColumn(Piwik_Metrics::INDEX_MAX_ACTIONS, 'max');
            }
            return $rankingQuery->execute($query['sql'], $query['bind']);
        }
        return $this->getDb()->query($query['sql'], $query['bind']);
    }

    protected function getSelectsMetrics($metricsAvailable, $metricsRequested = false)
    {
        $selects = array();
        foreach ($metricsAvailable as $metricId => $statement) {
            if ($this->isMetricRequested($metricId, $metricsRequested)) {
                $aliasAs = $this->getSelectAliasAs($metricId);
                $selects[] = $statement . $aliasAs;
            }
        }
        return $selects;
    }

    protected function getSelectStatement($dimensions, $tableName, $additionalSelects, array $availableMetrics, $requestedMetrics = false)
    {
        $dimensionsToSelect = $this->getDimensionsToSelect($dimensions, $additionalSelects);
        $selects = array_merge(
            $this->getSelectDimensions($dimensionsToSelect, $tableName),
            $this->getSelectsMetrics($availableMetrics, $requestedMetrics),
            !empty($additionalSelects) ? $additionalSelects : array()
        );
        $select = implode(self::FIELDS_SEPARATOR, $selects);
        return $select;
    }

    /**
     * Will return the subset of $dimensions that are not found in $additionalSelects
     *
     * @param $dimensions
     * @param array $additionalSelects
     * @return array
     */
    protected function getDimensionsToSelect($dimensions, $additionalSelects)
    {
        if (empty($additionalSelects)) {
            return $dimensions;
        }
        $dimensionsToSelect = array();
        foreach ($dimensions as $selectAs => $dimension) {
            $asAlias = $this->getSelectAliasAs($dimension);
            foreach ($additionalSelects as $additionalSelect) {
                if (strpos($additionalSelect, $asAlias) === false) {
                    $dimensionsToSelect[$selectAs] = $dimension;
                }
            }
        }
        $dimensionsToSelect = array_unique($dimensionsToSelect);
        return $dimensionsToSelect;
    }

    /**
     * Returns the dimensions array, where
     * (1) the table name is prepended to the field
     * (2) the "AS `label` " is appended to the field
     *
     * @param $dimensions
     * @param $tableName
     * @param bool $appendSelectAs
     * @return mixed
     */
    protected function getSelectDimensions($dimensions, $tableName, $appendSelectAs = true)
    {
        foreach ($dimensions as $selectAs => &$field) {
            $selectAsString = $field;
            if (!is_numeric($selectAs)) {
                $selectAsString = $selectAs;
            } else {
                // if function, do not alias or prefix
                if ($this->isFieldFunctionOrComplexExpression($field)) {
                    $selectAsString = $appendSelectAs = false;
                }
            }
            $isKnownField = !in_array($field, array('referrer_data'));
            if ($selectAsString == $field
                && $isKnownField
            ) {
                $field = "$tableName.$field";
            }
            if ($appendSelectAs && $selectAsString) {
                $field = $field . $this->getSelectAliasAs($selectAsString);
            }
        }
        return $dimensions;
    }

    protected function isFieldFunctionOrComplexExpression($field)
    {
        return strpos($field, "(") !== false
            || strpos($field, "CASE") !== false;
    }

    protected function getSelectAliasAs($metricId)
    {
        return " AS `" . $metricId . "`";
    }

    protected function isMetricRequested($metricId, $metricsRequested)
    {
        return $metricsRequested === false
            || in_array($metricId, $metricsRequested);
    }

    protected function getWhereStatement($tableName, $datetimeField, $extraWhere = false)
    {
        $where = "$tableName.$datetimeField >= ?
				AND $tableName.$datetimeField <= ?
				AND $tableName.idsite = ?";
        if (!empty($extraWhere)) {
            $extraWhere = sprintf($extraWhere, $tableName, $tableName);
            $where .= ' AND ' . $extraWhere;
        }
        return $where;
    }

    protected function getGroupByStatement($dimensions, $tableName)
    {
        $dimensions = $this->getSelectDimensions($dimensions, $tableName, $appendSelectAs = false);
        $groupBy = implode(", ", $dimensions);
        return $groupBy;
    }

    protected function getBindDatetimeSite()
    {
        return array($this->dateStart->getDateStartUTC(), $this->dateEnd->getDateEndUTC(), $this->site->getId());
    }

    /**
     * Returns the ecommerce items
     *
     * @param string $field
     * @return string
     */
    public function queryEcommerceItems($field)
    {
        $query = "SELECT
						name as label,
						" . self::getSqlRevenue('SUM(quantity * price)') . " as `" . Piwik_Metrics::INDEX_ECOMMERCE_ITEM_REVENUE . "`,
						" . self::getSqlRevenue('SUM(quantity)') . " as `" . Piwik_Metrics::INDEX_ECOMMERCE_ITEM_QUANTITY . "`,
						" . self::getSqlRevenue('SUM(price)') . " as `" . Piwik_Metrics::INDEX_ECOMMERCE_ITEM_PRICE . "`,
						count(distinct idorder) as `" . Piwik_Metrics::INDEX_ECOMMERCE_ORDERS . "`,
						count(idvisit) as `" . Piwik_Metrics::INDEX_NB_VISITS . "`,
						case idorder when '0' then " . Piwik_Tracker_GoalManager::IDGOAL_CART . " else " . Piwik_Tracker_GoalManager::IDGOAL_ORDER . " end as ecommerceType
			 	FROM " . Piwik_Common::prefixTable('log_conversion_item') . "
			 		LEFT JOIN " . Piwik_Common::prefixTable('log_action') . "
			 		ON $field = idaction
			 	WHERE server_time >= ?
						AND server_time <= ?
			 			AND idsite = ?
			 			AND deleted = 0
			 	GROUP BY ecommerceType, $field
				ORDER BY null";
        // Segment not supported yet
        // $query = $this->query($select, $from, $where, $groupBy, $orderBy);

        $bind = $this->getBindDatetimeSite();
        $query = $this->getDb()->query($query, $bind);
        return $query;
    }

    /**
     * Queries the Actions table log_link_visit_action and returns the aggregate data.
     *
     * @param array|string $dimensions      the dimensionRecord(s) you're interested in
     * @param string $where      where clause
     * @param array|bool $additionalSelects additional select clause
     * @param bool|array $metrics    Set this if you want to limit the columns that are returned.
     *                                  The possible values in the array are Piwik_Metrics::INDEX_*.
     * @param Piwik_RankingQuery $rankingQuery     pre-configured ranking query instance
     * @param bool|string $joinLogActionOnColumn  column from log_link_visit_action that
     *                                              log_action should be joined on.
     *                                                can be an array to join multiple times.
     * @return mixed
     */
    public function queryActionsByDimension($dimensions, $where = '', $additionalSelects = array(), $metrics = false, $rankingQuery = null, $joinLogActionOnColumn = false)
    {
        $tableName = self::LOG_ACTIONS_TABLE;
        $availableMetrics = $this->getActionsMetricFields();

        $select = $this->getSelectStatement($dimensions, $tableName, $additionalSelects, $availableMetrics, $metrics);
        $from = array($tableName);
        $where = $this->getWhereStatement($tableName, self::ACTION_DATETIME_FIELD, $where);
        $groupBy = $this->getGroupByStatement($dimensions, $tableName);
        $orderBy = false;

        if ($joinLogActionOnColumn !== false) {
            $multiJoin = is_array($joinLogActionOnColumn);
            if (!$multiJoin) {
                $joinLogActionOnColumn = array($joinLogActionOnColumn);
            }

            foreach ($joinLogActionOnColumn as $i => $joinColumn) {
                $tableAlias = 'log_action' . ($multiJoin ? $i + 1 : '');
                if (strpos($joinColumn, ' ') === false) {
                    $joinOn = $tableAlias . '.idaction = ' . $tableName . '.' . $joinColumn;
                } else {
                    // more complex join column like IF(...)
                    $joinOn = $tableAlias . '.idaction = ' . $joinColumn;
                }
                $from[] = array(
                    'table'      => 'log_action',
                    'tableAlias' => $tableAlias,
                    'joinOn'     => $joinOn
                );
            }
        }

        if ($rankingQuery) {
            $orderBy = '`' . Piwik_Metrics::INDEX_NB_ACTIONS . '` DESC';
        }

        $query = $this->generateQuery($select, $from, $where, $groupBy, $orderBy);

        if ($rankingQuery !== null) {
            $sumColumns = array_keys($availableMetrics);
            if ($metrics) {
                $sumColumns = array_intersect($sumColumns, $metrics);
            }
            $rankingQuery->addColumn($sumColumns, 'sum');
            return $rankingQuery->execute($query['sql'], $query['bind']);
        }

        return $this->getDb()->query($query['sql'], $query['bind']);
    }

    protected function getActionsMetricFields()
    {
        return $availableMetrics = array(
            Piwik_Metrics::INDEX_NB_VISITS        => "count(distinct " . self::LOG_ACTIONS_TABLE . ".idvisit)",
            Piwik_Metrics::INDEX_NB_UNIQ_VISITORS => "count(distinct " . self::LOG_ACTIONS_TABLE . ".idvisitor)",
            Piwik_Metrics::INDEX_NB_ACTIONS       => "count(*)",
        );
    }

    /**
     * Queries the log_conversion table and return aggregate data
     *
     * @param string|array $dimensions
     * @param bool|string $where
     * @param array $additionalSelects
     * @return PDOStatement
     */
    public function queryConversionsByDimension($dimensions = array(), $where = false, $additionalSelects = array())
    {
        $dimensions = array_merge(array(self::IDGOAL_FIELD), $dimensions);
        $availableMetrics = $this->getConversionsMetricFields();
        $tableName = self::LOG_CONVERSION_TABLE;

        $select = $this->getSelectStatement($dimensions, $tableName, $additionalSelects, $availableMetrics);

        $from = array($tableName);
        $where = $this->getWhereStatement($tableName, self::CONVERSION_DATETIME_FIELD, $where);
        $groupBy = $this->getGroupByStatement($dimensions, $tableName);
        $orderBy = false;
        $query = $this->generateQuery($select, $from, $where, $groupBy, $orderBy);
        return $this->getDb()->query($query['sql'], $query['bind']);
    }

    /**
     * Creates and returns an array of SQL SELECT expressions that will summarize
     * the data in a column of a specified table, over a set of ranges.
     *
     * The SELECT expressions will count the number of column values that are
     * within each range.
     *
     * @param $metadata
     * @return array  An array of SQL SELECT expressions.
     */
    public static function getSelectsFromRangedColumn($metadata)
    {
        @list($column, $ranges, $table, $selectColumnPrefix, $i_am_your_nightmare_DELETE_ME) = $metadata;

        $selects = array();
        $extraCondition = '';
        if ($i_am_your_nightmare_DELETE_ME) {
            // extra condition for the SQL SELECT that makes sure only returning visits are counted
            // when creating the 'days since last visit' report
            $extraCondition = 'and log_visit.visitor_returning = 1';
            $extraSelect = "sum(case when log_visit.visitor_returning = 0 then 1 else 0 end) "
                . " as `" . $selectColumnPrefix . 'General_NewVisits' . "`";
            $selects[] = $extraSelect;
        }
        foreach ($ranges as $gap) {
            if (count($gap) == 2) {
                $lowerBound = $gap[0];
                $upperBound = $gap[1];

                $selectAs = "$selectColumnPrefix$lowerBound-$upperBound";

                $selects[] = "sum(case when $table.$column between $lowerBound and $upperBound $extraCondition" .
                    " then 1 else 0 end) as `$selectAs`";
            } else {
                $lowerBound = $gap[0];

                $selectAs = $selectColumnPrefix . ($lowerBound + 1) . urlencode('+');

                $selects[] = "sum(case when $table.$column > $lowerBound $extraCondition then 1 else 0 end) as `$selectAs`";
            }
        }

        return $selects;
    }

    /**
     * Clean up the row data and return values.
     * $lookForThisPrefix can be used to make sure only SOME of the data in $row is used.
     *
     * The array will have one column $columnName
     *
     * @param $row
     * @param $columnName
     * @param bool $lookForThisPrefix A string that identifies which elements of $row to use
     *                                 in the result. Every key of $row that starts with this
     *                                 value is used.
     * @return array
     */
    static public function makeArrayOneColumn($row, $columnName, $lookForThisPrefix = false)
    {
        $cleanRow = array();
        foreach ($row as $label => $count) {
            if (empty($lookForThisPrefix)
                || strpos($label, $lookForThisPrefix) === 0
            ) {
                $cleanLabel = substr($label, strlen($lookForThisPrefix));
                $cleanRow[$cleanLabel] = array($columnName => $count);
            }
        }
        return $cleanRow;
    }

    public function getDb()
    {
        return Zend_Registry::get('db');
    }
}