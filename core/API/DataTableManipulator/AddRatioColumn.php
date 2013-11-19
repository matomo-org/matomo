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
use Piwik\API\Request;
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
    protected $roundPrecision = 2;
    private $totalValues = array();

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
        $metricsToCalculate = Metrics::getMetricIdsToProcessRatio();

        $parentTable = $this->getFirstLevelDataTable();

        if ($parentTable instanceof DataTable\Map) {
            // TODO
        }

        foreach ($parentTable->getRows() as $row) {
            foreach ($metricsToCalculate as $metricId) {
                $this->addColumnValueToTotal($row, $metricId);
            }
        }

        foreach ($dataTable->getRows() as $row) {
            foreach ($metricsToCalculate as $metricId) {
                $this->addRatioColumnIfNeeded($row, $metricId);
            }
        }

        return $dataTable;
    }

    protected function getFirstLevelDataTable()
    {
        $reportMetadata = API::getInstance()->getReportMetadata();

        $firstLevelReport = array();
        foreach ($reportMetadata as $report) {
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

    private function addColumnValueToTotal(Row $row, $columnIdRaw)
    {
        $value = $this->getColumn($row, $columnIdRaw);

        if (false === $value) {

            return;
        }

        if (array_key_exists($columnIdRaw, $this->totalValues)) {
            $this->totalValues[$columnIdRaw] += $value;
        } else {
            $this->totalValues[$columnIdRaw] = $value;
        }
    }

    private function addRatioColumnIfNeeded(Row $row, $columnIdRaw)
    {
        if (!array_key_exists($columnIdRaw, $this->totalValues)) {
            return;
        }

        $value = $this->getColumn($row, $columnIdRaw);

        if (false === $value) {
            return;
        }

        $mappingIdToName  = Metrics::$mappingFromIdToName;
        $columnIdReadable = $mappingIdToName[$columnIdRaw];
        $relativeValue    = $this->getPercentage($value, $this->totalValues[$columnIdRaw]);

        $row->addColumn($columnIdReadable . '_ratio_report', $relativeValue);
    }

    private function getPercentage($value, $totalValue)
    {
        $percentage = Piwik::getPercentageSafe($value, $totalValue, $this->roundPrecision);

        return $percentage . '%';
    }

    /**
     * Returns column from a given row.
     * Will work with 2 types of datatable
     * - raw datatables coming from the archive DB, which columns are int indexed
     * - datatables processed resulting of API calls, which columns have human readable english names
     *
     * @param Row|array $row
     * @param int $columnIdRaw see consts in Archive::
     * @param bool|array $mappingIdToName
     * @return mixed  Value of column, false if not found
     */
    protected function getColumn($row, $columnIdRaw, $mappingIdToName = false)
    {
        if (empty($mappingIdToName)) {
            $mappingIdToName = Metrics::$mappingFromIdToName;
        }

        $columnIdReadable = $mappingIdToName[$columnIdRaw];

        if ($row instanceof Row) {
            $raw = $row->getColumn($columnIdRaw);
            if ($raw !== false) {
                return $raw;
            }
            return $row->getColumn($columnIdReadable);
        }

        return false;
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

        $parametersToRemove = array('flat', '');

        foreach ($parametersToRemove as $param) {
            if (array_key_exists($param, $request)) {
                unset($request[$param]);
            }
        }
    }
}
