<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\API\DataTablePostProcessor;
use Piwik\Common;
use Piwik\DataTable;
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
     * Constructor
     *
     * @param bool $apiModule
     * @param bool $apiMethod
     * @param array $request
     * @param Report $report
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
     * Adds ratio metrics if possible.
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

        if (!$firstLevelTable->getRowsCount()
            || $dataTable->getTotalsRow()
            || $dataTable->getMetadata('totals')
        ) {
            return $dataTable;
        }

        // keeping queued filters would not only add various metadata but also break the totals calculator for some reports
        // eg when needed metadata is missing to get site information (multisites.getall) etc
        $clone = $firstLevelTable->getEmptyClone($keepFilters = false);
        foreach ($firstLevelTable->getQueuedFilters() as $queuedFilter) {
            if (is_array($queuedFilter) && 'ReplaceColumnNames' === $queuedFilter['className']) {
                $clone->queueFilter($queuedFilter['className'], $queuedFilter['parameters']);
            }
        }
        $tableMeta = $firstLevelTable->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);

        /** @var DataTable\Row $totalRow */
        $totalRow = null;
        foreach ($firstLevelTable->getRows() as $row) {
            if (!isset($totalRow)) {
                $columns = $row->getColumns();
                $columns['label'] = DataTable::LABEL_TOTALS_ROW;
                $totalRow = new DataTable\Row(array(DataTable\Row::COLUMNS => $columns));
            } else {
                $totalRow->sumRow($row, $copyMetadata = false, $tableMeta);
            }
        }
        $clone->addRow($totalRow);

        if ($this->report
            && $this->report->getProcessedMetrics()
            && array_keys($this->report->getProcessedMetrics()) === array('nb_actions_per_visit', 'avg_time_on_site', 'bounce_rate', 'conversion_rate')) {
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

        if (isset($totalRow)) {
            $totals = $row->getColumns();
            unset($totals['label']);
            $dataTable->setMetadata('totals', $totals);
            if (isset($totalRowUnformatted)) {
                unset($totalRowUnformatted['label']);
                $dataTable->setMetadata('totalsUnformatted', $totalRowUnformatted);
            }

            if (1 === Common::getRequestVar('keep_totals_row', 0, 'integer', $this->request)) {
                $totalLabel = Common::getRequestVar('keep_totals_row_label', Piwik::translate('General_Totals'), 'string', $this->request);

                $row->deleteMetadata(false);
                $row->setColumn('label', $totalLabel);
                $dataTable->setTotalsRow($row);
            }
        }

        return $dataTable;
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

        /** @var \Piwik\Period $period */
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
            if ($actionToLoadSubtables == $this->apiMethod
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
