<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API;

use Piwik\API\DataTableManipulator\Flattener;
use Piwik\API\DataTableManipulator\LabelFilter;
use Piwik\API\DataTableManipulator\ReportTotalsCalculator;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Filter\PivotByDimension;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;

/**
 * Processes DataTables that should be served through Piwik's APIs. This processing handles
 * special query parameters and computes processed metrics. It does not included rendering to
 * output formates (eg, 'xml').
 */
class DataTablePostProcessor
{
    const PROCESSED_METRICS_FORMATTED_FLAG = 'processed_metrics_formatted';
    const PROCESSED_METRICS_COMPUTED_FLAG = 'processed_metrics_computed';

    /**
     * Apply post-processing logic to a DataTable of a report for an API request.
     *
     * @param DataTableInterface $dataTable The data table to process.
     * @param Report|null $report The Report metadata class for the DataTable's report, or null if
     *                            there is none.
     * @param string[] $request The API request that
     * @param bool $applyFormatting Whether to format processed metrics or not.
     * @return DataTableInterface A new data table.
     */
    public function process(DataTableInterface $dataTable, $report, $request, $applyFormatting = true)
    {
        $label = self::getLabelFromRequest($request);

        $dataTable = $this->applyPivotByFilter($dataTable, $report, $request);
        $dataTable = $this->applyFlattener($dataTable, $report, $request);
        $dataTable = $this->applyTotalsCalculator($dataTable, $report, $request);
        $dataTable = $this->applyGenericFilters($label, $dataTable, $report, $request);

        $dataTable->filter(array($this, 'computeProcessedMetrics'), array($report));

        // we automatically safe decode all datatable labels (against xss)
        $dataTable->queueFilter('SafeDecodeLabel');

        $dataTable = $this->applyQueuedFilters($dataTable, $request);
        $dataTable = $this->applyRequestedColumnDeletion($dataTable, $request);
        $dataTable = $this->applyLabelFilter($label, $dataTable, $report, $request);
        $dataTable = $this->applyProcessedMetricsFormatting($dataTable, $report, $applyFormatting);

        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @param Report|null $report
     * @param $request
     * @return DataTableInterface
     */
    private function applyPivotByFilter(DataTableInterface $dataTable, $report, $request)
    {
        $pivotBy = Common::getRequestVar('pivotBy', false, 'string', $request);
        if (!empty($pivotBy)) {
            $reportId = $report->getModule() . '.' . $report->getAction();
            $pivotByColumn = Common::getRequestVar('pivotByColumn', false, 'string', $request);
            $pivotByColumnLimit = Common::getRequestVar('pivotByColumnLimit', false, 'int', $request);

            $dataTable->filter('PivotByDimension', array($reportId, $pivotBy, $pivotByColumn, $pivotByColumnLimit,
                PivotByDimension::isSegmentFetchingEnabledInConfig()));
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @param Report|null $report
     * @param $request
     * @return DataTable|DataTableInterface|DataTable\Map
     */
    private function applyFlattener($dataTable, $report, $request)
    {
        if (Common::getRequestVar('flat', '0', 'string', $request) == '1') {
            $flattener = new Flattener($report->getModule(), $report->getAction(), $request);
            if (Common::getRequestVar('include_aggregate_rows', '0', 'string', $request) == '1') {
                $flattener->includeAggregateRows();
            }
            $dataTable = $flattener->flatten($dataTable);
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @param Report|null $report
     * @param $request
     * @return DataTableInterface
     */
    private function applyTotalsCalculator($dataTable, $report, $request)
    {
        if (1 == Common::getRequestVar('totals', '1', 'integer', $request)) {
            $reportTotalsCalculator = new ReportTotalsCalculator($report->getModule(), $report->getAction(), $request);
            $dataTable     = $reportTotalsCalculator->calculate($dataTable);
        }
        return $dataTable;
    }

    /**
     * @param string $label
     * @param DataTableInterface $dataTable
     * @param Report|null $report
     * @param $request
     * @return DataTableInterface
     */
    private function applyGenericFilters($label, $dataTable, $report, $request)
    {
        // if the flag disable_generic_filters is defined we skip the generic filters
        if (0 == Common::getRequestVar('disable_generic_filters', '0', 'string', $request)) {
            $self = $this;

            $genericFilter = new DataTableGenericFilter($request, $report);

            if ($genericFilter->areProcessedMetricsNeededFor($report)) {
                $dataTable->filter(function (DataTable $table) use ($self, $report) {
                    $self->computeProcessedMetrics($table, $report);
                });
            }

            if (!empty($label)) {
                $genericFilter->disableFilters(array('Limit', 'Truncate'));
            }

            $genericFilter->filter($dataTable);
        }

        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @param $request
     * @return DataTableInterface
     */
    private function applyQueuedFilters($dataTable, $request)
    {
        // if the flag disable_queued_filters is defined we skip the filters that were queued
        if (Common::getRequestVar('disable_queued_filters', 0, 'int', $request) == 0) {
            $dataTable->applyQueuedFilters();
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @param $request
     * @return DataTableInterface
     */
    private function applyRequestedColumnDeletion($dataTable, $request)
    {
        // use the ColumnDelete filter if hideColumns/showColumns is provided (must be done
        // after queued filters are run so processed metrics can be removed, too)
        $hideColumns = Common::getRequestVar('hideColumns', '', 'string', $request);
        $showColumns = Common::getRequestVar('showColumns', '', 'string', $request);
        if (empty($showColumns)) {
            // if 'columns' is used, we remove all temporary metrics by showing only the columns specified in
            // 'columns'
            $showColumns = Common::getRequestVar('columns', '', 'string', $request);
        }

        if (!empty($hideColumns)
            || !empty($showColumns)
        ) {
            $dataTable->filter('ColumnDelete', array($hideColumns, $showColumns));
        }

        return $dataTable;
    }

    /**
     * @param string $label
     * @param DataTableInterface $dataTable
     * @param Report $report
     * @return DataTableInterface
     */
    private function applyLabelFilter($label, $dataTable, $report, $request)
    {
        // apply label filter: only return rows matching the label parameter (more than one if more than one label)
        if (!empty($label)) {
            $addLabelIndex = Common::getRequestVar('labelFilterAddLabelIndex', 0, 'int', $request) == 1;

            $filter = new LabelFilter($report->getModule(), $report->getAction(), $request);
            $dataTable = $filter->filter($label, $dataTable, $addLabelIndex);
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @param Report $report
     * @return DataTableInterface
     */
    private function applyProcessedMetricsFormatting($dataTable, $report, $applyFormatting)
    {
        if ($applyFormatting) {
            $dataTable->filter(array($this, 'formatProcessedMetrics'), array($report));
        } else {
            $dataTable->queueFilter(array($this, 'formatProcessedMetrics'), array($report)); // TODO: queuing does not always work.
        }

        return $dataTable;
    }

    /**
     * Returns the value for the label query parameter which can be either a string
     * (ie, label=...) or array (ie, label[]=...).
     *
     * @param array $request
     * @return array
     */
    public static function getLabelFromRequest($request)
    {
        $label = Common::getRequestVar('label', array(), 'array', $request);
        if (empty($label)) {
            $label = Common::getRequestVar('label', '', 'string', $request);
            if (!empty($label)) {
                $label = array($label);
            }
        }

        $label = self::unsanitizeLabelParameter($label);
        return $label;
    }

    public static function unsanitizeLabelParameter($label)
    {
        // this is needed because Proxy uses Common::getRequestVar which in turn
        // uses Common::sanitizeInputValue. This causes the > that separates recursive labels
        // to become &gt; and we need to undo that here.
        $label = Common::unsanitizeInputValues($label);
        return $label;
    }

    private function computeProcessedMetrics(DataTable $dataTable, $report)
    {
        if ($dataTable->getMetadata(self::PROCESSED_METRICS_COMPUTED_FLAG)) {
            return;
        }

        $dataTable->setMetadata(self::PROCESSED_METRICS_COMPUTED_FLAG, true);

        $processedMetrics = $this->getProcessedMetricsFor($dataTable, $report);
        if (empty($processedMetrics)) {
            return;
        }

        foreach ($processedMetrics as $name => $processedMetric) {
            if (!$processedMetric->beforeCompute($this, $dataTable)) {
                continue;
            }

            foreach ($dataTable->getRows() as $row) {
                if ($row->getColumn($name) === false) { // do not compute the metric if it has been computed already
                    $row->addColumn($name, $processedMetric->compute($row));

                    $subtable = $row->getSubtable();
                    if (!empty($subtable)) {
                        $this->computeProcessedMetrics($subtable, $report);
                    }
                }
            }
        }
    }

    /**
     * public for use as callback.
     */
    public function formatProcessedMetrics(DataTable $dataTable, $report)
    {
        if ($dataTable->getMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG)) {
            return;
        }

        $dataTable->setMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG, true);

        $processedMetrics = $this->getProcessedMetricsFor($dataTable, $report);
        if (empty($processedMetrics)) {
            return;
        }

        foreach ($dataTable->getRows() as $row) {
            foreach ($processedMetrics as $name => $processedMetric) {
                $columnValue = $row->getColumn($name);
                if ($columnValue !== false) {
                    $row->setColumn($name, $processedMetric->format($columnValue));
                }

                $subtable = $row->getSubtable();
                if (!empty($subtable)) {
                    $this->formatProcessedMetrics($subtable, $report);
                }
            }
        }
    }

    /**
     * @param DataTable $dataTable
     * @param Report $report
     * @return ProcessedMetric[]
     */
    private function getProcessedMetricsFor(DataTable $dataTable, $report)
    {
        $processedMetrics = $dataTable->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: array();

        if (!empty($report)) {
           $processedMetrics = array_merge($processedMetrics, $report->processedMetrics ?: array());
        }

        $result = array();
        foreach ($processedMetrics as $metric) {
            if (!($metric instanceof ProcessedMetric)) {
                continue;
            }

            $result[$metric->getName()] = $metric;
        }
        return $result;
    }
}