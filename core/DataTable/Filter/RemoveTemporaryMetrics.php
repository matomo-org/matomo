<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Plugin\Metric;
use Piwik\Plugin\Report;

/**
 * TODO
 */
class RemoveTemporaryMetrics extends BaseFilter
{
    /**
     * TODO
     */
    private $report;

    /**
     * Constructor.
     *TODO modify
     * @param DataTable $table The table that will be filtered.
     */
    public function __construct($table, Report $report)
    {
        parent::__construct($table);

        $this->report = $report;
    }

    /**
     * Executes the filter. See {@link RemoveTemporaryMetrics}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $temporaryMetrics = $this->report->temporaryMetrics;
        if (empty($temporaryMetrics)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            foreach ($temporaryMetrics as $temporaryMetric) {
                if (!($temporaryMetric instanceof Metric)) {
                    continue;
                }

                $row->deleteColumn($temporaryMetric->getName());
            }
        }
    }
}