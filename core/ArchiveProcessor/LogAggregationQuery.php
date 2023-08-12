<?php
/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Columns\Dimension;
use Piwik\Columns\MetricsList;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\LogAggregator;
use Piwik\Metrics;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugin\LogTablesProvider;
use Piwik\RankingQuery;
use Piwik\Segment\SegmentExpression;
use Piwik\Tracker\GoalManager;

/**
 * TODO
 *
 * TODO: member docs
 */
class LogAggregationQuery
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string[]
     */
    private $from;

    /**
     * @var LogAggregator
     */
    private $logAggregator;

    /**
     * ['selectAs' => ['sql' => 'select sql', 'bind' => [...]]]
     *
     * @var array
     */
    private $selects = [];

    /**
     * @var string[]
     */
    private $dimensions = [];

    /**
     * @var string[]
     */
    private $metrics = [];

    /**
     * ['sql' => '...', 'bind' => [...]]
     *
     * @var array[]
     */
    private $whereConditions = [];

    /**
     * @var string[]
     */
    private $groupBy = [];

    /**
     * @var string[]
     */
    private $orderBySelect = [];

    /**
     * @var string[]
     */
    private $orderByDirection = [];

    /**
     * TODO
     *
     * @var callable
     */
    private $rowProcessor;

    /**
     * @var bool|number
     */
    private $rankingQueryLimit = false;

    /**
     * @var array
     */
    private $rankingQueryAggregations = [];

    public function __construct(string $table, LogAggregator $logAggregator)
    {
        $this->table = $table;
        $this->from = [$this->table];
        $this->logAggregator = $logAggregator;
        $this->rowProcessor = function ($cursor) {
            try {
                while ($row = $cursor->fetch()) {
                    yield $row;
                }
            } finally {
                $cursor->closeCursor();
            }
        };
    }

    /**
     * TODO
     * @param string|array $from
     */
    public function addFromSql($from): LogAggregationQuery
    {
        $this->from[] = $from;
        return $this;
    }

    /**
     * TODO
     *
     * TODO: reverse order of parameters? either here or in addMetric
     */
    public function addDimension(Dimension $dimension, string $selectAs = null): LogAggregationQuery
    {
        $defaultSelectAs = str_replace(',', '_', $dimension->getId());
        $selectAs = $selectAs ?: $defaultSelectAs;

        $join = $dimension->getDbColumnJoin();
        if (!empty($join)) {
            $tableAlias = str_replace('.', '_', $dimension->getId()) . '_' . $join->getTable();

            // TODO: just use sprintf, it'll be cleaner
            // TODO: $dimension->getDbTableName() may not match what's in $from
            $joinOn = $tableAlias . '.' . $join->getColumn() . ' = ' . $dimension->getDbTableName() . '.' . $dimension->getColumnName();

            $joinDiscriminator = $dimension->getDbDiscriminator();
            if (!empty($joinDiscriminator)) {
                $joinOn .= ' AND ' . $tableAlias . '.' . $joinDiscriminator->getColumn() . ' = ' .  $joinDiscriminator->getValue();
            }

            $joinTable = [
                'table' => $join->getTable(),
                'tableAlias' => $tableAlias,
                'joinOn' => $joinOn,
            ];
            $this->addFromSql($joinTable);

            $sql = $tableAlias . '.' . $join->getTargetColumn();
            $groupBy = $dimension->getDbTableName() . '.' . $dimension->getColumnName();
        } else {
            $sql = $this->getDimensionSelectSql($dimension);
            $groupBy = $selectAs;
        }

        $this->dimensions[] = $selectAs;
        $this->selects[$selectAs] = ['sql' => $sql, 'bind' => []];
        $this->groupBy[] = $groupBy;
        return $this;
    }

    /**
     * TODO
     *
     * @param string $name
     * @param string $sql
     * @param array $bind
     * @return $this
     */
    public function addDimensionSql(string $name, string $sql, array $bind = []): LogAggregationQuery
    {
        $this->dimensions[] = $name;
        $this->selects[$name] = ['sql' => $sql, 'bind' => $bind];
        $this->groupBy[] = $name;
        return $this;
    }

    /**
     * TODO
     */
    public function addMetric(ArchivedMetric $metric, string $selectAs = null): LogAggregationQuery
    {
        return $this->addMetricSql($selectAs ?: $metric->getName(), $metric->getQuery());
    }

    /**
     * TODO
     * @param string $name
     * @param string $sql
     * @param array $bind
     * @return $this
     */
    public function addMetricSql(string $name, string $sql, array $bind = []): LogAggregationQuery
    {
        $this->metrics[] = $name;
        $this->selects[$name] = ['sql' => $sql, 'bind' => $bind];
        return $this;
    }

    /**
     * TODO
     */
    public function addHistogram(string $name, Dimension $dimension, string $countMetricName, array $ranges): LogAggregationQuery
    {
        $dimensionTable = $dimension->getDbTableName();
        if (!empty($dimensionTable) && !$this->isTableInFrom($dimensionTable)) {
            $this->addFromSql($dimensionTable);
        }

        $sql = $this->getDimensionSelectSql($dimension);
        return $this->addHistogramSql($name, $sql, $countMetricName, $ranges);
    }

    /**
     * TODO
     *
     * @param string $name
     * @param string $dimensionSql
     * @param array $ranges
     * @return LogAggregationQuery
     */
    public function addHistogramSql(string $name, string $dimensionSql, string $countMetricName, array $ranges): LogAggregationQuery
    {
        foreach ($ranges as $index => $gap) {
            $gap = array_map('floatval', $gap);

            $selectAs = $name . '_' . $index;

            if (count($gap) == 2) {
                [$lowerBound, $upperBound] = $gap;

                $this->selects[$selectAs] = [
                    'sql' => "sum(case when $dimensionSql between $lowerBound and $upperBound then 1 else 0 end)",
                    'bind' => [],
                ];
            } else {
                [$lowerBound] = $gap;

                $this->selects[$selectAs] = [
                    'sql' => "sum(case when $dimensionSql > $lowerBound then 1 else 0 end)",
                    'bind' => [],
                ];
            }
        }

        // make sure selected values are transformed into rows that can be turned into a DataTable
        $currentRowProcessor = $this->rowProcessor;
        $this->rowProcessor = function ($cursor) use ($currentRowProcessor, $name, $countMetricName, $ranges) {
            foreach ($currentRowProcessor($cursor) as $row) {
                $histogram = [];
                foreach ($ranges as $index => $gap) {
                    $selectAs = $name . '_' . $index;
                    if (!isset($row[$selectAs])) {
                        continue;
                    }

                    if (count($gap) == 2) {
                        $label = implode('-', $gap);
                    } else {
                        $lowerBound = $gap[0];
                        $label = ($lowerBound + 1) . urlencode('+');
                    }

                    $histogram[] = [
                        'label' => $label,
                        $countMetricName => $row[$selectAs],
                    ];

                    unset($row[$selectAs]);
                }

                $row[$name] = $histogram;

                yield $row;
            }
        };

        return $this;
    }

    /**
     * TODO
     *
     * @param string $sqlCondition
     * @param array $bind
     * @return LogAggregationQuery
     */
    public function addWhere(string $sqlCondition, array $bind = []): LogAggregationQuery
    {
        $this->whereConditions[] = ['sql' => $sqlCondition, 'bind' => $bind];
        return $this;
    }

    /**
     * TODO
     */
    public function addConversionMetrics(): LogAggregationQuery
    {
        if ($this->table !== 'log_conversion') {
            throw new \Exception('Default conversion metrics can only be added to queries on log_conversion.');
        }

        $this->addMetricSql(Metrics::INDEX_GOAL_NB_CONVERSIONS, 'count(*)');
        $this->addMetricSql(Metrics::INDEX_GOAL_NB_VISITS_CONVERTED, 'count(distinct log_conversion.idvisit)');
        $this->addMetricSql(Metrics::INDEX_GOAL_REVENUE, LogAggregator::getSqlRevenue('SUM(log_conversion.revenue)'));
        $this->addMetricSql(Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SUBTOTAL, LogAggregator::getSqlRevenue('SUM(log_conversion.revenue_subtotal)'));
        $this->addMetricSql(Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_TAX, LogAggregator::getSqlRevenue('SUM(log_conversion.revenue_tax)'));
        $this->addMetricSql(Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_SHIPPING, LogAggregator::getSqlRevenue('SUM(log_conversion.revenue_shipping)'));
        $this->addMetricSql(Metrics::INDEX_GOAL_ECOMMERCE_REVENUE_DISCOUNT, LogAggregator::getSqlRevenue('SUM(log_conversion.revenue_discount)'));
        $this->addMetricSql(Metrics::INDEX_GOAL_ECOMMERCE_ITEMS, 'SUM(log_conversion.items)');
        return $this;
    }

    /**
     * TODO
     */
    public function addEcommerceItemMetrics(): LogAggregationQuery
    {
        if ($this->table !== 'log_conversion_item') {
            throw new \Exception('Default ecommerce item metrics can only be added to queries on log_conversion_item.');
        }

        $this->addMetricSql(Metrics::INDEX_ECOMMERCE_ITEM_REVENUE, LogAggregator::getSqlRevenue('SUM(log_conversion_item.quantity * log_conversion_item.price)'));
        $this->addMetricSql(Metrics::INDEX_ECOMMERCE_ITEM_QUANTITY, LogAggregator::getSqlRevenue('SUM(log_conversion_item.quantity)'));
        $this->addMetricSql(Metrics::INDEX_ECOMMERCE_ITEM_PRICE, LogAggregator::getSqlRevenue('SUM(log_conversion_item.price)'));
        $this->addMetricSql(Metrics::INDEX_ECOMMERCE_ORDERS, 'COUNT(distinct log_conversion_item.idorder)');
        $this->addMetricSql(Metrics::INDEX_NB_VISITS, 'COUNT(distinct log_conversion_item.idvisit)');
        return $this;
    }

    /**
     * TODO
     */
    public function addActionMetrics(): LogAggregationQuery
    {
        if ($this->table !== 'log_link_visit_action') {
            throw new \Exception('Default action metrics can only be added to queries on log_action.');
        }

        $this->addMetricSql(Metrics::INDEX_NB_VISITS, 'count(distinct log_link_visit_action.idvisit)');
        $this->addMetricSql(Metrics::INDEX_NB_UNIQ_VISITORS, 'count(distinct log_link_visit_action.idvisitor)');
        $this->addMetricSql(Metrics::INDEX_NB_ACTIONS, 'count(*)');
        return $this;
    }

    /**
     * TODO
     * TODO: use this in addHistogram
     *
     * @param callable $processor
     * @return $this
     */
    public function addRowTransform(callable $processor): LogAggregationQuery
    {
        $currentRowProcessor = $this->rowProcessor;
        $this->rowProcessor = function ($cursor) use ($currentRowProcessor, $processor) {
            foreach ($currentRowProcessor($cursor) as $row) {
                $transformed = $processor($row);
                if (!isset($transformed)) {
                    continue;
                }

                yield $transformed;
            }
        };

        return $this;
    }

    /**
     * TODO
     * @return string[]
     */
    public function getMetricFields(): array
    {
        return $this->metrics;
    }

    /**
     * TODO
     * @return $this
     */
    public function useRankingQuery($rankingQueryLimit, array $metricAggregations = [])
    {
        $this->rankingQueryLimit = $rankingQueryLimit;
        $this->rankingQueryAggregations = $metricAggregations;
        return $this;
    }

    /**
     * TODO
     * @return string[]
     */
    public function getDimensionFields(): array
    {
        return $this->dimensions;
    }

    /**
     * TODO
     * @param string $table
     * @return bool
     */
    public function isTableInFrom(string $table): bool
    {
        // TODO: small optimization, just make the key in $this->from the deduced table alias
        foreach ($this->from as $tableInfo) {
            if ($tableInfo === $table) {
                return true;
            }

            if (!is_array($tableInfo)) {
                continue;
            }

            if (isset($tableInfo['tableAlias'])
                && $tableInfo['tableAlias'] == $table
            ) {
                return true;
            }

            if (!isset($tableInfo['tableAlias'])
                && $tableInfo['table'] == $table
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * TODO
     *
     * @return \Traversable
     */
    public function execute(): \Traversable
    {
        $query = $this->buildQuery();

        $cursor = $this->logAggregator->getDb()->query($query['sql'], $query['bind']);

        $currentRowProcessor = $this->rowProcessor;
        return $currentRowProcessor($cursor);
    }

    /**
     * TODO
     *
     * @return array
     */
    public function buildQuery(): array
    {
        $bind = [];

        $selects = [];
        foreach ($this->selects as $selectAs => $selectInfo) {
            $selects[] = $selectInfo['sql'] . ' AS `' . $selectAs . '`';
            $bind = array_merge($bind, $selectInfo['bind']);
        }

        $from = $this->getFromWithAutoJoinedTables();

        $where = [];

        $datetimeField = $this->getDatetimeFieldForTable();
        $where[] = $this->logAggregator->getWhereStatement($this->table, $datetimeField);

        foreach ($this->whereConditions as $whereInfo) {
            $where[] = $whereInfo['sql'];
            $bind = array_merge($bind, $whereInfo['bind']);
        }

        $groupBy = implode(', ', $this->groupBy);

        $orderBy = [];
        foreach ($this->orderBySelect as $i => $orderBySelect) {
            $orderBy[] = $orderBySelect . ' ' . $this->orderByDirection[$i];
        }
        $orderBy = implode(', ', $orderBy);

        $query = $this->logAggregator->generateQuery(
            implode(",\n ", $selects),
            $from,
            implode(' AND ', $where),
            $groupBy,
            $orderBy
        );

        // TODO: timeLimitInMs support

        if ($this->rankingQueryLimit) {
            $rankingQuery = new RankingQuery($this->rankingQueryLimit);
            foreach ($this->dimensions as $dimensionSelectAs) {
                if (!array_key_exists($dimensionSelectAs, $this->rankingQueryAggregations)) {
                    $rankingQuery->addLabelColumn($dimensionSelectAs);
                }
            }

            foreach ($this->rankingQueryAggregations as $metric => $aggregation) {
                $rankingQuery->addColumn($metric, $aggregation);
            }

            $query['sql'] = $rankingQuery->generateRankingQuery($query['sql']);
        }

        // TODO: bind only works up til where statement. adding placeholders after bind won't work.
        $query['bind'] = array_merge($bind, $query['bind']);

        return $query;
    }

    /**
     * TODO
     *
     * @param string $select
     * @param string $direction
     * @return $this
     */
    public function orderBy(string $select, string $direction = 'desc')
    {
        $this->orderBySelect[] = $select;
        $this->orderByDirection[] = $direction;
        return $this;
    }

    private function getDimensionSelectSql(Dimension $dimension): string
    {
        $sql = $dimension->getSqlFilter();
        if (empty($sql)) {
            $sql = $dimension->getSqlSegment();
        }
        if (empty($sql)) {
            $sql = $this->table . '.' . $dimension->getColumnName();
        }
        return $sql;
    }

    private function getDatetimeFieldForTable(): string
    {
        $logTableProvider = StaticContainer::get(LogTablesProvider::class);
        $logTableInfo = $logTableProvider->getLogTable($this->table);
        if (empty($logTableInfo)
            || empty($logTableInfo->getDateTimeColumn())
        ) {
            throw new \Exception('LogAggregationQuery can only be used with tables that provide a datetime column and a \Piwik\Tracker\LogTable class to define this metadata.');
        }
        return $logTableInfo->getDateTimeColumn();
    }

    private function getFromWithAutoJoinedTables()
    {
        $partsToCheckForAutoJoinTables = array_merge($this->whereConditions, $this->selects);

        $from = $this->from;

        foreach ($partsToCheckForAutoJoinTables as $sqlPart) {
            $sql = $sqlPart['sql'];
            $joinOnColumns = SegmentExpression::parseColumnsFromSqlExpr($sql);
            foreach ($joinOnColumns as $column) {
                SegmentExpression::checkFieldIsAvailable($column, $from, null);
            }
        }

        return $from;
    }
}
