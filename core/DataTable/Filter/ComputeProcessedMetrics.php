<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Exception;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * TODO
 */
class ComputeProcessedMetrics extends BaseFilter
{
    /**
     * TODO
     *
     * @var Report
     */
    private $report;

    /**
     * Constructor.
     *
     * @param DataTable $table The table that will be filtered.
     * @param Report $report The report metadata.
     */
    public function __construct($table, Report $report)
    {
        parent::__construct($table);

        $this->report = $report;
    }

    /**
     * Executes the filter. See {@link ComputeProcessedMetrics}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $processedMetrics = $this->report->processedMetrics;
        if (!is_array($processedMetrics)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            /** @var ProcessedMetric $processedMetric */ // TODO: should remove this and if below eventually.
            foreach ($processedMetrics as $processedMetric) {
                if ($processedMetric instanceof ProcessedMetric) {
                    $row->addColumn($processedMetric->getName(), $processedMetric->compute($row));
                }
            }
        }
    }
}