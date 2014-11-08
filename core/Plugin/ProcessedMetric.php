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
 * TODO
 */
abstract class ProcessedMetric extends Metric
{
    /**
     * The sub-namespace name in a plugin where Report components are stored.
     */
    const COMPONENT_SUBNAMESPACE = 'Metrics';

    /**
     * TODO
     */
    abstract public function compute(Row $row);

    /**
     * TODO
     */
    abstract public function getDependenctMetrics();

    /**
     * TODO
     */
    public function beforeCompute(Report $report, DataTable $table)
    {
        return true;
    }
}