<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\Filter;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Simple;
use Piwik\Metrics\Formatter;
use Piwik\Plugins\AbTesting\DataTable\Filter\BaseFilter;

class DataComparisonFilter extends BaseFilter
{
    /**
     * @var array
     */
    private $request;

    public function __construct(DataTable $table, $request)
    {
        parent::__construct($table);
        $this->request = $request;
    }

    /**
     * @param DataTable $table
     * @throws \Exception
     */
    public function filter($table)
    {
        $method = Common::getRequestVar('method', $default = null, $type = 'string', $this->request);
        if ($method == 'Live') {
            throw new \Exception("Data comparison is not enabled for the Live API.");
        }

        // TODO: multiple sites / periods. will need to change requests appropriately, based on table metadata.

        // TODO: soft limit or segments/date\speriods to compare
        $segments = Common::getRequestVar('compareSegments', $default = [], $type = 'array', $this->request);
        if (empty($segments)) {
            $segments = [''];
        }

        $dates = Common::getRequestVar('compareDates', $default = [], $type = 'array', $this->request);
        $dates = array_values($dates);
        if (empty($dates)) {
            $dates = [''];
        }

        $periods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array', $this->request);
        $periods = array_values($periods);
        if (empty($periods)) {
            $periods = [''];
        }

        if (count($dates) !== count($periods)) {
            throw new \InvalidArgumentException("compareDates query parameter length must match comparePeriods query parameter length.");
        }

        $reportsToCompare = $this->getReportsToCompare($segments, $dates, $periods);
        foreach ($reportsToCompare as $modifiedParams) {
            $compareTable = $this->requestReport($method, $modifiedParams);
            $this->compareTables($modifiedParams, $table, $compareTable);

            Common::destroy($compareTable);
            unset($compareTable);
        }

        // format comparison table metrics
        $this->formatComparisonTables($table);

        // add comparison parameters as metadata
        if (!empty($segments)) {
            $table->setMetadata('compareSegments', $segments);
        }

        if (!empty($dates)) {
            $table->setMetadata('compareDates', $dates);
        }

        if (!empty($periods)) {
            $table->setMetadata('comparePeriods', $periods);
        }
    }

    private function getReportsToCompare($segments, $dates, $periods)
    {
        $permutations = [];
        foreach ($segments as $segment) {
            foreach ($dates as $index => $date) {
                $period = $periods[$index];

                $params = [];

                if (!empty($segment)) {
                    $params['segment'] = $segment;
                }

                if (!empty($period)
                    && !empty($date)
                ) {
                    $params['date'] = $date;
                    $params['period'] = $period;
                }

                $permutations[] = $params;
            }
        }
        return $permutations;
    }

    /**
     * @param $paramsToModify
     * @return DataTable
     */
    private function requestReport($method, $paramsToModify)
    {
        $params = array_merge([
            'filter_limit' => -1,
            'filter_offset' => 0,
            'filter_sort_column' => '',
            'filter_truncate' => -1,
            'compare' => 0,
            'totals' => 0,
        ], $paramsToModify);

        return Request::processRequest($method, $params);
    }

    private function formatComparisonTables(DataTable $table)
    {
        $formatter = new Formatter();
        foreach ($table->getRows() as $row) {
            $comparisonTable = $row->getMetadata(DataTable\Row::COMPARISONS_METADATA_NAME);
            $comparisonTable->filter(DataTable\Filter\ReplaceColumnNames::class);
            $formatter->formatMetrics($comparisonTable);

            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->formatComparisonTables($subtable);
            }
        }
    }

    private function compareRow($modifiedParams, DataTable\Row $row, DataTable\Row $compareRow = null)
    {
        $comparisonDataTable = $row->getMetadata(DataTable\Row::COMPARISONS_METADATA_NAME);
        if (empty($comparisonDataTable)) {
            $comparisonDataTable = new DataTable();
            $row->setMetadata(DataTable\Row::COMPARISONS_METADATA_NAME, $comparisonDataTable);
        }

        $metadata = [];
        if (!empty($modifiedParams['segment'])) {
            $metadata['compareSegment'] = $modifiedParams['segment'];
        }
        if (!empty($modifiedParams['period'])) {
            $metadata['comparePeriod'] = $modifiedParams['period'];
        }
        if (!empty($modifiedParams['date'])) {
            $metadata['compareDate'] = $modifiedParams['date'];
        }

        if ($compareRow) {
            $columns = $compareRow->getColumns();
        } else {
            $rowColumns = array_keys($row->getColumns());
            $columns = array_fill_keys($rowColumns, 0);
        }

        $newRow = new DataTable\Row([
            DataTable\Row::COLUMNS => $columns,
            DataTable\Row::METADATA => $metadata,
        ]);

        // calculate changes
        foreach ($row->getColumns() as $name => $value) {
            $valueToCompare = $row->getColumn($name) ?: 0;
            $change = DataTable\Filter\CalculateEvolutionFilter::calculate($value, $valueToCompare, $precision = 1);
            $newRow->addColumn($name . '_change', $change);
        }

        $comparisonDataTable->addRow($newRow);

        // recurse on subtable if there
        $subtable = $row->getSubtable();
        if ($subtable
            && $compareRow
        ) {
            $this->compareTables($modifiedParams, $subtable, $compareRow->getSubtable());
        }
    }

    private function compareTables($modifiedParams, DataTable $table, DataTable $compareTable = null)
    {
        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');

            $compareRow = null;
            if ($compareTable instanceof Simple) {
                $compareRow = $compareTable->getFirstRow();
            } else if ($compareTable instanceof DataTable) {
                $compareRow = $compareTable->getRowFromLabel($label) ?: null;
            }

            $this->compareRow($modifiedParams, $row, $compareRow);
        }
    }
}