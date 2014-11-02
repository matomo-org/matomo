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
class FormatProcessedMetrics extends BaseFilter
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
     * Executes the filter. See {@link ComputeProcessedMetrics}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $processedMetrics = $this->report->processedMetrics;
        if (empty($processedMetrics)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            foreach ($processedMetrics as $processedMetric) {
                if (!($processedMetric instanceof ProcessedMetric)) {
                    continue;
                }

                $name = $processedMetric->getName();
                $columnValue = $row->getColumn($name);
                if ($columnValue !== false) {
                    $row->setColumn($name, $processedMetric->format($columnValue));
                }
            }
        }
    }
}