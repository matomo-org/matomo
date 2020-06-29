<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
     * The sub-namespace name in a plugin where ProcessedMetrics are stored.
     */
    const COMPONENT_SUBNAMESPACE = 'Columns\\Metrics';

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
    abstract public function getDependentMetrics();

    /**
     * Returns the array of metrics that are necessary for computing this metric, but should not
     * be displayed to the user unless explicitly requested. These metrics are intermediate
     * metrics that are not really valuable to the user. On a request, if showColumns or hideColumns
     * is not used, they will be removed automatically.
     *
     * @return string[]
     */
    public function getTemporaryMetrics()
    {
        return array();
    }

    /**
     * Executed before computing all processed metrics for a report. Implementers can return `false`
     * to skip computing this metric.
     *
     * @param Report $report
     * @param DataTable $table
     * @return bool Return `true` to compute the metric for the table, `false` to skip computing
     *              this metric.
     */
    public function beforeCompute($report, DataTable $table)
    {
        return true;
    }

    /**
     * @param Row $row
     * @ignore
     */
    public function beforeComputeSubtable(Row $row)
    {
        // empty
    }

    /**
     * @param Row $row
     * @ignore
     */
    public function afterComputeSubtable(Row $row)
    {
        // empty
    }
}
