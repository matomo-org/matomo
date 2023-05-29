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
     * TODO
     *
     * @var callable
     */
    private $rowProcessor;

    public function __construct(string $table, LogAggregator $logAggregator)
    {
        $this->table = $table;
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
     */
    public function addDimension(Dimension $dimension, string $selectAs = null): LogAggregationQuery
    {
        $sql = $this->getDimensionSelectSql($dimension);
        return $this->addDimensionSql($selectAs ?: $dimension->getId(), $sql);
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
        foreach ($ranges as $gap) {
            $gap = array_map('floatval', $gap);

            $selectAs = $name . '_' . implode('-', $gap);

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
                foreach ($ranges as $gap) {
                    $label = implode('-', $gap);

                    $selectAs = $name . '_' . $label;
                    if (!isset($row[$selectAs])) {
                        continue;
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

        $metricList = MetricsList::get();
        $this->addMetric($metricList->getMetric('nb_conversions'), Metrics::INDEX_GOAL_NB_CONVERSIONS);
        $this->addMetricSql(Metrics::INDEX_NB_CONVERSIONS, 'count(distinct log_conversion.idvisit)');
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
     * @return string[]
     */
    public function getMetricFields(): array
    {
        return $this->metrics;
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

        // TODO: support multiple from
        // TODO: support order by as well

        $from = [$this->table];

        $where = [];
        foreach ($this->whereConditions as $whereInfo) {
            $where[] = $whereInfo['sql'];
            $bind = array_merge($bind, $whereInfo['bind']);
        }

        $datetimeField = $this->getDatetimeFieldForTable();
        $where[] = $this->logAggregator->getWhereStatement($this->table, $datetimeField, $where);

        $groupBy = implode(', ', $this->dimensions);

        $query = $this->logAggregator->generateQuery(
            implode(', ', $selects),
            $from,
            implode(' AND ', $where),
            $groupBy,
            $orderBy = false
        );

        // TODO: bind only works up til where statement. adding placeholders after bind won't work.
        $query['bind'] = array_merge($bind, $query['bind']);

        return $query;
    }

    private function getDimensionSelectSql(Dimension $dimension): string
    {
        $sql = $dimension->getSqlFilter();
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
}
