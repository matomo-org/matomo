<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\API\DataTablePostProcessor;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Metrics\Formatter;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;

/**
 * This class is responsible for setting the metadata property 'totals' on each dataTable if the report
 * has a dimension. 'Totals' means it tries to calculate the total report value for each metric. For each
 * the total number of visits, actions, ... for a given report / dataTable.
 */
class ReportTotalsCalculator extends DataTableManipulator
{
    /**
     * @var Report
     */
    private $report;

    /**
     * @param string|bool $apiModule
     * @param string|bool $apiMethod
     * @param array $request
     * @param Report|null $report
     */
    public function __construct($apiModule = false, $apiMethod = false, $request = array(), $report = null)
    {
        parent::__construct($apiModule, $apiMethod, $request);
        $this->report = $report;
    }

    /**
     * @param  DataTable $table
     * @return \Piwik\DataTable|\Piwik\DataTable\Map
     */
    public function calculate($table)
    {
        // apiModule and/or apiMethod is empty for instance in case when flat=1 is called. Basically whenever a
        // datamanipulator calls the API and wants the dataTable in return, see callApiAndReturnDataTable().
        // it is also not set for some settings API request etc.
        if (empty($this->apiModule) || empty($this->apiMethod)) {
            return $table;
        }

        try {
            return $this->manipulate($table);
        } catch (\Exception $e) {
            // eg. requests with idSubtable may trigger this exception
            // (where idSubtable was removed in
            // ?module=API&method=Events.getNameFromCategoryId&idSubtable=1&secondaryDimension=eventName&format=XML&idSite=1&period=day&date=yesterday&flat=0
            return $table;
        }
    }

    /**
     * Computes the report totals and stores them as 'totals'/'totalsUnformatted' metadata on the table.
     *
     * @param  DataTable $dataTable
     * @return DataTable
     */
    protected function manipulateDataTable($dataTable)
    {
        if (!empty($this->report) && !$this->report->getDimension() && !$this->isAllMetricsReport()) {
            // we currently do not calculate the total value for reports having no dimension
            return $dataTable;
        }

        if (1 != Common::getRequestVar('totals', 1, 'integer', $this->request)) {
            return $dataTable;
        }

        $firstLevelTable = $this->makeSureToWorkOnFirstLevelDataTable($dataTable);

        if (
            !$firstLevelTable->getRowsCount()
            || $dataTable->getTotalsRow()
            || $dataTable->getMetadata('totals')
        ) {
            return $dataTable;
        }

        $totalRowUnformatted = null;
        $totalRow = $this->makeTotalsRow($firstLevelTable, $totalRowUnformatted);

        if (isset($totalRow)) {
            $totals = $totalRow->getColumns();
            unset($totals['label']);
            $dataTable->setMetadata('totals', $totals);
            if (isset($totalRowUnformatted)) {
                unset($totalRowUnformatted['label']);
                $dataTable->setMetadata('totalsUnformatted', $totalRowUnformatted);
            }

            if (1 === Common::getRequestVar('keep_totals_row', 0, 'integer', $this->request)) {
                $totalLabel = Common::getRequestVar('keep_totals_row_label', Piwik::translate('General_Totals'), 'string', $this->request);

                $totalRow->deleteMetadata(false);
                $totalRow->setColumn('label', $totalLabel);
                $dataTable->setTotalsRow($totalRow);
            }
        }

        return $dataTable;
    }

    /**
     * Replaces the totals row of a table that was reduced to the rows matching the table search of
     * the request, so it totals those rows instead of every row of the report.
     *
     * Has to be called while the table still contains every matching row, ie. before the rows are
     * limited to the requested page of results. The report totals are left untouched in the
     * 'totals' and 'totalsUnformatted' metadata, so both values can be shown next to each other.
     *
     * @param DataTable $dataTable A table that only contains the rows matching the request.
     */
    public function calculateFilteredTotals(DataTable $dataTable): void
    {
        if (
            empty($this->apiModule)
            || empty($this->apiMethod)
            || !$this->isTableSearchActive()
            || !$dataTable->getRowsCount()
            || !$dataTable->getTotalsRow()
        ) {
            return;
        }

        if (1 === Common::getRequestVar('compare', 0, 'integer', $this->request)) {
            // the compared reports are requested without the table search, so a filtered total could
            // only be compared against an unfiltered one
            return;
        }

        try {
            $unformatted = null;
            $totalRow = $this->makeTotalsRow($dataTable, $unformatted, $rowsWerePostProcessed = true);
        } catch (\Exception $e) {
            // the report totals are kept when the filtered totals cannot be computed
            return;
        }

        if (!isset($totalRow)) {
            return;
        }

        $totalRow->deleteMetadata(false);
        $totalRow->setColumn('label', Piwik::translate('General_FilteredTotal'));

        $dataTable->setTotalsRow($totalRow);
        $dataTable->setMetadata(DataTable::TOTALS_ROW_IS_FILTERED_METADATA_NAME, true);
    }

    /**
     * Sums every row of the given table into a single row, computes its processed metrics and
     * formats it.
     *
     * @param array|null $totalRowUnformatted Set to the total column values before they are formatted.
     * @param bool $rowsWerePostProcessed Whether the rows already went through the part of the post
     *                                    processing that computes and formats processed metrics.
     */
    private function makeTotalsRow(
        DataTable $table,
        ?array &$totalRowUnformatted,
        bool $rowsWerePostProcessed = false
    ): ?DataTable\Row {
        // keeping queued filters would not only add various metadata but also break the totals calculator for some reports
        // eg when needed metadata is missing to get site information (multisites.getall) etc
        $clone = $table->getEmptyClone($keepFilters = false);
        foreach ($table->getQueuedFilters() as $queuedFilter) {
            if (is_array($queuedFilter) && 'ReplaceColumnNames' === $queuedFilter['className']) {
                $clone->queueFilter($queuedFilter['className'], $queuedFilter['parameters']);
            }
        }

        if ($rowsWerePostProcessed) {
            // getEmptyClone() copies the metadata of the source table, which then states that the
            // processed metrics were already computed and formatted. The totals row still needs both.
            $clone->deleteMetadata(DataTablePostProcessor::PROCESSED_METRICS_COMPUTED_FLAG);
            $clone->deleteMetadata(Formatter::PROCESSED_METRICS_FORMATTED_FLAG);
        }

        $tableMeta = $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);

        /** @var DataTable\Row|null $totalRow */
        $totalRow = null;
        foreach ($table->getRows() as $row) {
            if (!isset($totalRow)) {
                $columns = $row->getColumns();
                $columns['label'] = DataTable::LABEL_TOTALS_ROW;
                $totalRow = new DataTable\Row(array(DataTable\Row::COLUMNS => $columns));
            } else {
                $totalRow->sumRow($row, $copyMetadata = false, $tableMeta);
            }
        }

        if (!isset($totalRow)) {
            return null;
        }

        if ($rowsWerePostProcessed) {
            // processed metrics are derived from the summed metrics and must never be summed themselves.
            // they are already on the rows when a generic filter needed them, eg when sorting by one.
            foreach (Report::getProcessedMetricsForTable($table, $this->report) as $processedMetricName => $processedMetric) {
                $totalRow->deleteColumn($processedMetricName);
            }
        }

        $clone->addRow($totalRow);

        if (
            $this->report
            && $this->report->getProcessedMetrics()
            && array_keys($this->report->getProcessedMetrics()) === array('nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate', 'conversion_rate')
        ) {
            // hack for AllColumns table or default processed metrics
            $clone->filter('AddColumnsProcessedMetrics', array($deleteRowsWithNoVisit = false));
        }

        $processor = new DataTablePostProcessor($this->apiModule, $this->apiMethod, $this->request);
        $processor->applyComputeProcessedMetrics($clone);
        $clone = $processor->applyQueuedFilters($clone);

        $totalRowUnformatted = null;
        foreach ($clone->getRows() as $row) {
            /** * @var DataTable\Row $row */
            if ($row->getColumn('label') === DataTable::LABEL_TOTALS_ROW) {
                $totalRowUnformatted = $row->getColumns();
                break;
            }
        }
        $clone = $processor->applyMetricsFormatting($clone);

        $totalRow = null;
        foreach ($clone->getRows() as $row) {
            /** * @var DataTable\Row $row */
            if ($row->getColumn('label') === DataTable::LABEL_TOTALS_ROW) {
                $totalRow = $row;
                break;
            }
        }

        if (!isset($totalRow) && $clone->getRowsCount() === 1) {
            // if for some reason the processor renamed the totals row,
            $totalRow = $clone->getFirstRow();
        }

        return $totalRow;
    }

    /**
     * Returns whether the request searches the table for a pattern, ie. whether the rows the request
     * results in are a subset of the rows of the report.
     */
    private function isTableSearchActive(): bool
    {
        $patterns = array(
            Common::getRequestVar('filter_pattern', '', 'string', $this->request),
            Common::getRequestVar('filter_pattern_recursive', '', 'string', $this->request),
        );

        foreach ($patterns as $pattern) {
            if ('' !== $pattern) {
                return true;
            }
        }

        return false;
    }

    private function makeSureToWorkOnFirstLevelDataTable($table)
    {
        if (!array_key_exists('idSubtable', $this->request)) {
            return $table;
        }

        $firstLevelReport = $this->findFirstLevelReport();

        if (empty($firstLevelReport)) {
            // it is not a subtable report
            $module = $this->apiModule;
            $action = $this->apiMethod;
        } else {
            $module = $firstLevelReport->getModule();
            $action = $firstLevelReport->getAction();
        }

        $request = $this->request;
        unset($request['idSubtable']); // to make sure we work on first level table

        /** @var \Piwik\Period|false $period */
        $period = $table->getMetadata('period');

        if (!empty($period)) {
            // we want a dataTable, not a dataTable\map
            if (Period::isMultiplePeriod($request['date'], $request['period']) || 'range' == $period->getLabel()) {
                $request['date']   = $period->getRangeString();
                $request['period'] = 'range';
            } else {
                $request['date']   = $period->getDateStart()->toString();
                $request['period'] = $period->getLabel();
            }
        }

        $table = $this->callApiAndReturnDataTable($module, $action, $request);

        if ($table instanceof DataTable\Map) {
            $table = $table->mergeChildren();
        }

        return $table;
    }

    /**
     * Make sure to get all rows of the first level table.
     *
     * @param array $request
     * @return array
     */
    protected function manipulateSubtableRequest($request)
    {
        $request['totals']        = 0;
        $request['expanded']      = 0;
        $request['filter_limit']  = -1;
        $request['filter_offset'] = 0;
        $request['filter_sort_column'] = '';

        $parametersToRemove = array('flat');

        if (!array_key_exists('idSubtable', $this->request)) {
            $parametersToRemove[] = 'idSubtable';
        }

        foreach ($parametersToRemove as $param) {
            if (array_key_exists($param, $request)) {
                unset($request[$param]);
            }
        }
        return $request;
    }

    private function findFirstLevelReport()
    {
        $reports = new ReportsProvider();
        foreach ($reports->getAllReports() as $report) {
            $actionToLoadSubtables = $report->getActionToLoadSubTables();
            if (
                $actionToLoadSubtables == $this->apiMethod
                && $this->apiModule == $report->getModule()
            ) {
                return $report;
            }
        }
        return null;
    }

    private function isAllMetricsReport()
    {
        return $this->report->getModule() == 'API' && $this->report->getAction() == 'get';
    }
}
