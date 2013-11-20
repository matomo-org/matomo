<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\API\DataTableManipulator;

use Piwik\API\DataTableManipulator;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\DataTable\Filter;
use Piwik\Period\Range;
use Piwik\Piwik;
use Piwik\Metrics;
use Piwik\Plugins\API\API;

/**
 * This class is responsible for adding ratio columns.
 *
 * @package Piwik
 * @subpackage Piwik_API
 */
class AddRatioColumn extends DataTableManipulator
{
    protected $roundPrecision = 1;

    /**
     * Array [readableMetric] => [summed value]
     * @var array
     */
    private $totalValues = array();
    private static $reportMetadata = array();

    /**
     * @param  DataTable $table
     * @return \Piwik\DataTable|\Piwik\DataTable\Map
     */
    public function addColumns($table)
    {
        return $this->manipulate($table);
    }

    /**
     * Adds ratio metrics if possible.
     *
     * @param  DataTable $dataTable
     * @return DataTable
     */
    protected function manipulateDataTable($dataTable)
    {
        $report = $this->getCurrentReport();

        if (!empty($report) && empty($report['dimension'])) {
            return $dataTable;
        }

        $metricsToCalculate = Metrics::getMetricIdsToProcessRatio();
        $parentTable        = $this->getFirstLevelDataTable($dataTable);

        foreach ($metricsToCalculate as $metricId) {
            if (!$this->hasDataTableMetric($parentTable, $metricId)) {
                continue;
            }

            foreach ($parentTable->getRows() as $row) {
                $this->addColumnValueToTotal($row, $metricId);
            }
        }

        foreach ($this->totalValues as $metricId => $totalValue) {
            if (!$this->hasDataTableMetric($dataTable, $metricId)) {
                continue;
            }

            foreach ($dataTable->getRows() as $row) {
                $this->addRatioColumnIfPossible($row, $metricId, $totalValue);
            }
        }

        return $dataTable;
    }

    protected function hasDataTableMetric(DataTable $dataTable, $metricId)
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

    protected function getColumn($row, $columnIdRaw)
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

    protected function getCurrentReport()
    {
        foreach ($this->getReportMetadata() as $report) {
            if (!empty($report['actionToLoadSubTables'])
                && $this->apiMethod == $report['action']
                && $this->apiModule == $report['module']) {

                return $report;
            }
        }

    }

    protected function getFirstLevelDataTable($table)
    {
        if (!array_key_exists('idSubtable', $this->request)) {
            return $table;
        }

        $firstLevelReport = array();
        foreach ($this->getReportMetadata() as $report) {
            if (!empty($report['actionToLoadSubTables'])
                && $this->apiMethod == $report['actionToLoadSubTables']
                && $this->apiModule == $report['module']) {

                $firstLevelReport = $report;
                break;
            }
        }

        if (empty($firstLevelReport)) {
            // it is not a subtable report
            $module = $this->apiModule;
            $action = $this->apiMethod;
        } else {
            $module = $firstLevelReport['module'];
            $action = $firstLevelReport['action'];
        }

        $request = $this->request;

        return $this->callApiAndReturnDataTable($module, $action, $request);
    }

    private function addColumnValueToTotal(Row $row, $metricId)
    {
        $value = $this->getColumn($row, $metricId);

        if (false === $value) {

            return;
        }

        if (array_key_exists($metricId, $this->totalValues)) {
            $this->totalValues[$metricId] += $value;
        } else {
            $this->totalValues[$metricId] = $value;
        }
    }

    private function addRatioColumnIfPossible(Row $row, $metricId, $totalValue)
    {
        $value = $this->getColumn($row, $metricId);

        if (false === $value) {
            return;
        }

        $relativeValue = $this->getPercentage($value, $totalValue);
        $metricName    = Metrics::getReadableColumnName($metricId);
        $ratioMetric   = Metrics::makeReportRatioMetricName($metricName);

        $row->addColumn($ratioMetric, $relativeValue);
    }

    private function getPercentage($value, $totalValue)
    {
        $percentage = Piwik::getPercentageSafe($value, $totalValue, $this->roundPrecision);

        return $percentage . '%';
    }

    /**
     * Make sure to get all rows of the first level table.
     *
     * @param array $request
     */
    protected function manipulateSubtableRequest(&$request)
    {
        $request['ratio']         = 0;
        $request['expanded']      = 0;
        $request['filter_limit']  = -1;
        $request['filter_offset'] = 0;

        if (Range::parseDateRange($request['date'])) {
            $request['period'] = 'range';
        }

        $parametersToRemove = array('flat', 'idSubtable');

        foreach ($parametersToRemove as $param) {
            if (array_key_exists($param, $request)) {
                unset($request[$param]);
            }
        }
    }

    private function getReportMetadata()
    {
        if (!empty(static::$reportMetadata)) {
            return static::$reportMetadata;
        }

        static::$reportMetadata = API::getInstance()->getReportMetadata();

        return static::$reportMetadata;
    }
}
