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
use Piwik\Metrics\Formatter;
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
     * @var null|Report
     */
    private $report;

    /**
     * @var string[]
     */
    private $request;

    /**
     * @var string
     */
    private $apiModule;

    /**
     * @var string
     */
    private $apiMethod;

    /**
     * Constructor.
     */
    public function __construct($apiModule, $apiMethod, $request)
    {
        $this->apiModule = $apiModule;
        $this->apiMethod = $apiMethod;
        $this->request = $request;

        $this->report = Report::factory($apiModule, $apiMethod);
    }

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
    public function process(DataTableInterface $dataTable, $applyFormatting = true)
    {
        $dataTable = $this->applyPivotByFilter($dataTable);
        $dataTable = $this->applyFlattener($dataTable);
        $dataTable = $this->applyTotalsCalculator($dataTable);
        $dataTable = $this->applyGenericFilters($dataTable);

        $this->applyComputeProcessedMetrics($dataTable);

        // we automatically safe decode all datatable labels (against xss)
        $dataTable->queueFilter('SafeDecodeLabel');

        $dataTable = $this->applyQueuedFilters($dataTable);
        $dataTable = $this->applyRequestedColumnDeletion($dataTable);
        $dataTable = $this->applyLabelFilter($dataTable);

        if ($applyFormatting) {
            $dataTable = $this->applyProcessedMetricsFormatting($dataTable);
        }

        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyPivotByFilter(DataTableInterface $dataTable)
    {
        $pivotBy = Common::getRequestVar('pivotBy', false, 'string', $this->request);
        if (!empty($pivotBy)) {
            $this->applyComputeProcessedMetrics($dataTable);

            $reportId = $this->apiModule . '.' . $this->apiMethod;
            $pivotByColumn = Common::getRequestVar('pivotByColumn', false, 'string', $this->request);
            $pivotByColumnLimit = Common::getRequestVar('pivotByColumnLimit', false, 'int', $this->request);

            $dataTable->filter('PivotByDimension', array($reportId, $pivotBy, $pivotByColumn, $pivotByColumnLimit,
                PivotByDimension::isSegmentFetchingEnabledInConfig()));
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTable|DataTableInterface|DataTable\Map
     */
    public function applyFlattener($dataTable)
    {
        if (Common::getRequestVar('flat', '0', 'string', $this->request) == '1') {
            $flattener = new Flattener($this->apiModule, $this->apiMethod, $this->request);
            if (Common::getRequestVar('include_aggregate_rows', '0', 'string', $this->request) == '1') {
                $flattener->includeAggregateRows();
            }
            $dataTable = $flattener->flatten($dataTable);
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyTotalsCalculator($dataTable)
    {
        if (1 == Common::getRequestVar('totals', '1', 'integer', $this->request)) {
            $reportTotalsCalculator = new ReportTotalsCalculator($this->apiModule, $this->apiMethod, $this->request);
            $dataTable     = $reportTotalsCalculator->calculate($dataTable);
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyGenericFilters($dataTable)
    {
        // if the flag disable_generic_filters is defined we skip the generic filters
        if (0 == Common::getRequestVar('disable_generic_filters', '0', 'string', $this->request)) {
            $this->applyProcessedMetricsGenericFilters($dataTable);

            $genericFilter = new DataTableGenericFilter($this->request);

            $self = $this;
            $report = $this->report;
            $dataTable->filter(function (DataTable $table) use ($genericFilter, $report, $self) {
                $processedMetrics = Report::getProcessedMetricsFor($table, $report);
                if ($genericFilter->areProcessedMetricsNeededFor($processedMetrics)) {
                    $self->computeProcessedMetrics($table);
                }
            });

            $label = self::getLabelFromRequest($this->request);
            if (!empty($label)) {
                $genericFilter->disableFilters(array('Limit', 'Truncate'));
            }

            $genericFilter->filter($dataTable);
        }

        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyProcessedMetricsGenericFilters($dataTable)
    {
        $addNormalProcessedMetrics = Common::getRequestVar(
            'filter_add_columns_when_show_all_columns', false, 'integer', $this->request);
        if ($addNormalProcessedMetrics !== false) {
            $dataTable->filter('AddColumnsProcessedMetrics', array($addNormalProcessedMetrics));
        }

        $addGoalProcessedMetrics = Common::getRequestVar(
            'filter_update_columns_when_show_all_goals', false, 'integer', $this->request);
        if ($addGoalProcessedMetrics !== false) {
            $idGoal = Common::getRequestVar(
                'idGoal', DataTable\Filter\AddColumnsProcessedMetricsGoal::GOALS_OVERVIEW, 'string', $this->request);

            $dataTable->filter('AddColumnsProcessedMetricsGoal', array($ignore = true, $idGoal));
        }

        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyQueuedFilters($dataTable)
    {
        // if the flag disable_queued_filters is defined we skip the filters that were queued
        if (Common::getRequestVar('disable_queued_filters', 0, 'int', $this->request) == 0) {
            $dataTable->applyQueuedFilters();
        }
        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyRequestedColumnDeletion($dataTable)
    {
        // use the ColumnDelete filter if hideColumns/showColumns is provided (must be done
        // after queued filters are run so processed metrics can be removed, too)
        $hideColumns = Common::getRequestVar('hideColumns', '', 'string', $this->request);
        $showColumns = Common::getRequestVar('showColumns', '', 'string', $this->request);
        if (!empty($hideColumns)
            || !empty($showColumns)
        ) {
            $dataTable->filter('ColumnDelete', array($hideColumns, $showColumns));
        }

        return $dataTable;
    }

    /**
     * @param DataTableInterface $dataTable
     * @return DataTableInterface
     */
    public function applyLabelFilter($dataTable)
    {
        $label = self::getLabelFromRequest($this->request);

        // apply label filter: only return rows matching the label parameter (more than one if more than one label)
        if (!empty($label)) {
            $addLabelIndex = Common::getRequestVar('labelFilterAddLabelIndex', 0, 'int', $this->request) == 1;

            $filter = new LabelFilter($this->apiModule, $this->apiMethod, $this->request);
            $dataTable = $filter->filter($label, $dataTable, $addLabelIndex);
        }
        return $dataTable;
    }

    /**
     * TODO: remove applyFormatting parameter
     *
     * @param DataTableInterface $dataTable
     * @param Formatter|null $formatter
     * @return DataTableInterface
     */
    public function applyProcessedMetricsFormatting($dataTable, Formatter $formatter = null)
    {
        if ($formatter === null)
        {
            $formatter = new Formatter();
        }

        $dataTable->filter(array($this, 'formatProcessedMetrics'), array($formatter));
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

    public function computeProcessedMetrics(DataTable $dataTable)
    {
        if ($dataTable->getMetadata(self::PROCESSED_METRICS_COMPUTED_FLAG)) {
            return;
        }

        $processedMetrics = Report::getProcessedMetricsFor($dataTable, $this->report);
        if (empty($processedMetrics)) {
            return;
        }

        $dataTable->setMetadata(self::PROCESSED_METRICS_COMPUTED_FLAG, true);

        foreach ($processedMetrics as $name => $processedMetric) {
            if (!$processedMetric->beforeCompute($this->report, $dataTable)) {
                continue;
            }

            foreach ($dataTable->getRows() as $row) {
                if ($row->getColumn($name) === false) { // do not compute the metric if it has been computed already
                    $row->addColumn($name, $processedMetric->compute($row));

                    $subtable = $row->getSubtable();
                    if (!empty($subtable)) {
                        $this->computeProcessedMetrics($subtable);
                    }
                }
            }
        }
    }

    /**
     * public for use as callback.
     */
    public function formatProcessedMetrics(DataTable $dataTable, Formatter $formatter)
    {
        if ($dataTable->getMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG)) {
            return;
        }

        $processedMetrics = Report::getProcessedMetricsFor($dataTable, $this->report);
        if (empty($processedMetrics)) {
            return;
        }

        $dataTable->setMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG, true);

        foreach ($processedMetrics as $name => $processedMetric) {
            if (!$processedMetric->beforeFormat($this->report, $dataTable)) {
                continue;
            }

            foreach ($dataTable->getRows() as $row) {
                $columnValue = $row->getColumn($name);
                if ($columnValue !== false) {
                    $row->setColumn($name, $processedMetric->format($columnValue, $formatter));
                }

                $subtable = $row->getSubtable();
                if (!empty($subtable)) {
                    $this->formatProcessedMetrics($subtable, $formatter);
                }
            }
        }
    }

    public function applyComputeProcessedMetrics(DataTableInterface $dataTable)
    {
        $dataTable->filter(array($this, 'computeProcessedMetrics'));
    }
}