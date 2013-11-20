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
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Metrics;
use Piwik\Plugins\API\API;

/**
 * This class is responsible for adding ratio columns.
 *
 * @package Piwik
 * @subpackage Piwik_API
 */
class Totals extends DataTableManipulator
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
    public function generate($table)
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

        $this->totalValues = array();

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

        $dataTable->setMetadata('totals', $this->totalValues);

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

        /** @var \Piwik\Period\Range $period */
        $period = $table->getMetadata('period');

        if (!empty($period)) {
            if (Period::isMultiplePeriod($request['date'], $request['period']) || 'range' == $period->getLabel()) {
                $request['date']   = $period->getRangeString();
                $request['period'] = 'range';
            } else {
                $request['date']   = $period->toString();
                $request['period'] = $period->getLabel();
            }
        }

        return $this->callApiAndReturnDataTable($module, $action, $request);
    }

    private function addColumnValueToTotal(Row $row, $metricId)
    {
        $value = $this->getColumn($row, $metricId);

        if (false === $value) {

            return;
        }

        $metricName = Metrics::getReadableColumnName($metricId);

        if (array_key_exists($metricName, $this->totalValues)) {
            $this->totalValues[$metricName] += $value;
        } else {
            $this->totalValues[$metricName] = $value;
        }
    }

    /**
     * Make sure to get all rows of the first level table.
     *
     * @param array $request
     */
    protected function manipulateSubtableRequest(&$request)
    {
        $request['totals']        = 0;
        $request['expanded']      = 0;
        $request['filter_limit']  = -1;
        $request['filter_offset'] = 0;

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
