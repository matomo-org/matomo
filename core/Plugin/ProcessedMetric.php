<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * Base type for processed metrics. A processed metric is a metric that is computed using
 * one or more other metrics.
 *
 * @api
 */
abstract class ProcessedMetric extends Metric
{
    /**
     * The sub-namespace name in a plugin where Report components are stored.
     */
    const COMPONENT_SUBNAMESPACE = 'Metrics';

    /**
     * Computes the metric using the values in a {@link Piwik\DataTable\Row}.
     *
     * The computed value should be numerical and not formatted in any way. For example, for
     * a percent value, `0.14` should be returned instead of `"14%"`.
     *
     * @return mixed
     */
    abstract public function compute(Row $row);

    /**
     * Returns the array of metrics that are necessary for computing this metric. The elements
     * of the array are metric names.
     *
     * @return string[]
     */
    abstract public function getDependenctMetrics();

    /**
     * Executed before computing all processed metrics for a report. Implementers can return `false`
     * to skip computing this metric.
     *
     * @param Report $report
     * @param DataTable $table
     * @return bool Return `true` to compute the metric for the table, `false` to skip computing
     *              this metric.
     */
    public function beforeCompute(Report $report, DataTable $table)
    {
        return true;
    }
}