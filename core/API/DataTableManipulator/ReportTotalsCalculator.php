<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Plugins\API\API;

/**
 * This class is responsible for setting the metadata property 'totals' on each dataTable if the report
 * has a dimension. 'Totals' means it tries to calculate the total report value for each metric. For each
 * the total number of visits, actions, ... for a given report / dataTable.
 */
class ReportTotalsCalculator extends DataTableManipulator
{
    /**
     * Cached report metadata array.
     * @var array
     */
    private static $reportMetadata = array();

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
        } catch(\Exception $e) {
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
        $report = $this->findCurrentReport();

        if (!empty($report) && empty($report['dimension'])) {
            // we currently do not calculate the total value for reports having no dimension
            return $dataTable;
        }

        // Array [readableMetric] => [summed value]
        $totalValues = array();

        $firstLevelTable    = $this->makeSureToWorkOnFirstLevelDataTable($dataTable);
        $metricsToCalculate = Metrics::getMetricIdsToProcessReportTotal();

        foreach ($metricsToCalculate as $metricId) {
            if (!$this->hasDataTableMetric($firstLevelTable, $metricId)) {
                continue;
            }

            foreach ($firstLevelTable->getRows() as $row) {
                $totalValues = $this->sumColumnValueToTotal($row, $metricId, $totalValues);
            }
        }

        $dataTable->setMetadata('totals', $totalValues);

        return $dataTable;
    }

    private function hasDataTableMetric(DataTable $dataTable, $metricId)
    {
        $firstRow = $dataTable->getFirstRow();

        if (empty($firstRow)) {
            return false;
        }

        if (false === $this->getColumn($firstRow, $metricId)) {
            return false;
        }

        return true;
    }

    /**
     * Returns column from a given row.
     * Will work with 2 types of datatable
     * - raw datatables coming from the archive DB, which columns are int indexed
     * - datatables processed resulting of API calls, which columns have human readable english names
     *
     * @param Row|array $row
     * @param int $columnIdRaw see consts in Metrics::
     * @return mixed  Value of column, false if not found
     */
    private function getColumn($row, $columnIdRaw)
    {
        $columnIdReadable = Metrics::getReadableColumnName($columnIdRaw);

        if ($row instanceof Row) {
            $raw = $row->getColumn($columnIdRaw);
            if ($raw !== false) {
                return $raw;
            }

            return $row->getColumn($columnIdReadable);
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
            $module = $firstLevelReport['module'];
            $action = $firstLevelReport['action'];
        }

        $request = $this->request;

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

    private function sumColumnValueToTotal(Row $row, $metricId, $totalValues)
    {
        $value = $this->getColumn($row, $metricId);

        if (false === $value) {

            return $totalValues;
        }

        $metricName = Metrics::getReadableColumnName($metricId);

        if (array_key_exists($metricName, $totalValues)) {
            $totalValues[$metricName] += $value;
        } else {
            $totalValues[$metricName] = $value;
        }

        return $totalValues;
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

    private function getReportMetadata()
    {
        if (!empty(static::$reportMetadata)) {
            return static::$reportMetadata;
        }

        static::$reportMetadata = API::getInstance()->getReportMetadata();

        return static::$reportMetadata;
    }

    private function findCurrentReport()
    {
        foreach ($this->getReportMetadata() as $report) {
            if ($this->apiMethod == $report['action']
                && $this->apiModule == $report['module']) {

                return $report;
            }
        }
    }

    private function findFirstLevelReport()
    {
        foreach ($this->getReportMetadata() as $report) {
            if (!empty($report['actionToLoadSubTables'])
                && $this->apiMethod == $report['actionToLoadSubTables']
                && $this->apiModule == $report['module']
            ) {

                return $report;
            }
        }
    }
}
