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
use Piwik\Plugin\Report;

/**
 * This class is responsible for setting the metadata property 'totals' on each dataTable if the report
 * has a dimension. 'Totals' means it tries to calculate the total report value for each metric. For each
 * the total number of visits, actions, ... for a given report / dataTable.
 */
class ReportTotalsCalculator extends DataTableManipulator
{
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

        if (!empty($report) && !$report->getDimension() && !$this->isReportAllMetricsReport($report)) {
            // we currently do not calculate the total value for reports having no dimension
            return $dataTable;
        }

        // Array [readableMetric] => [summed value]
        $totalValues = array();

        $firstLevelTable    = $this->makeSureToWorkOnFirstLevelDataTable($dataTable);
        $metricsToCalculate = Metrics::getMetricIdsToProcessReportTotal();

        $realMetricNames = array();
        foreach ($metricsToCalculate as $metricId) {
            $metricName = Metrics::getReadableColumnName($metricId);
            $realMetricName = $this->hasDataTableMetric($firstLevelTable, $metricId, $metricName);
            if (!empty($realMetricName)) {
                $realMetricNames[$metricName] = $realMetricName;
            }
        }

        foreach ($firstLevelTable->getRows() as $row) {
            $columns = $row->getColumns();
            foreach ($realMetricNames as $metricName => $realMetricName) {
                $totalValues = $this->sumColumnValueToTotal($columns, $metricName, $realMetricName, $totalValues);
            }
        }

        $dataTable->setMetadata('totals', $totalValues);

        return $dataTable;
    }

    private function hasDataTableMetric(DataTable $dataTable, $metricId, $readableColumnName)
    {
        $firstRow = $dataTable->getFirstRow();

        if (empty($firstRow)) {
            return false;
        }

        $columnAlternatives = array(
            $metricId,
            $readableColumnName,
            // TODO: this and below is a hack to get report totals to work correctly w/ MultiSites.getAll. can be corrected
            //       when all metrics are described by Metadata classes & internal naming quirks are handled by core system.
            'Goal_' . $readableColumnName,
            'Actions_' . $readableColumnName
        );

        foreach ($columnAlternatives as $column) {
            if ($firstRow->getColumn($column) !== false) {
                return $column;
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

    private function sumColumnValueToTotal($columns, $metricName, $realMetricId, $totalValues)
    {
        $value = false;
        if (array_key_exists($realMetricId, $columns)) {
            $value = $columns[$realMetricId];
        }

        if (false === $value) {

            return $totalValues;
        }

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

    private function findCurrentReport()
    {
        return Report::factory($this->apiModule, $this->apiMethod);
    }

    private function findFirstLevelReport()
    {
        foreach (Report::getAllReports() as $report) {
            $actionToLoadSubtables = $report->getActionToLoadSubTables();
            if ($actionToLoadSubtables == $this->apiMethod
                && $this->apiModule == $report->getModule()
            ) {
                return $report;
            }
        }
        return null;
    }

    private function isReportAllMetricsReport(Report $report)
    {
        return $report->getModule() == 'API' && $report->getAction() == 'get';
    }
}
