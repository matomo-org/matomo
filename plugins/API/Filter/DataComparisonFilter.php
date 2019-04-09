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
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Simple;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugins\AbTesting\DataTable\Filter\BaseFilter;

// TODO: unit test

/**
 * TODO
 */
class DataComparisonFilter extends BaseFilter
{
    /**
     * @var array
     */
    private $request;

    /**
     * @var int
     */
    private $segmentCompareLimit;

    /**
     * @var int
     */
    private $periodCompareLimit;

    /**
     * @var array[]
     */
    private $availableSegments;

    public function __construct(DataTable $table, $request)
    {
        parent::__construct($table);
        $this->request = $request;

        $generalConfig = Config::getInstance()->General;
        $this->segmentCompareLimit = (int) $generalConfig['data_comparison_segment_limit'];
        $this->checkComparisonLimit($this->segmentCompareLimit, 'data_comparison_segment_limit');

        $this->periodCompareLimit = (int) $generalConfig['data_comparison_period_limit'];
        $this->checkComparisonLimit($this->periodCompareLimit, 'data_comparison_period_limit');
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

        $segments = Common::getRequestVar('compareSegments', $default = [], $type = 'array', $this->request);
        if (empty($segments)) {
            $segments = [''];
        }
        if (count($segments) > $this->segmentCompareLimit) {
            throw new \Exception("The maximum number of segments that can be compared simultaneously is {$this->segmentCompareLimit}.");
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

        if (count($dates) > $this->periodCompareLimit) {
            throw new \Exception("The maximum number of periods that can be compared simultaneously is {$this->periodCompareLimit}.");
        }

        $this->availableSegments = self::getAvailableSegments();

        $reportsToCompare = $this->getReportsToCompare($segments, $dates, $periods);
        foreach ($reportsToCompare as $modifiedParams) {
            $compareTable = $this->requestReport($table, $method, $modifiedParams);
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

        // add base compare against segment and date
        array_unshift($segments, isset($this->request['segment']) ? $this->request['segment'] : '');
        array_unshift($dates, isset($this->request['date']) ? $this->request['date'] : '');
        array_unshift($periods, isset($this->request['period']) ? $this->request['period'] : '');

        // NOTE: the order of these loops determines the order of the rows in the comparison table. ie,
        // if we loop over dates then segments, then we'll see comparison rows change segments before changing
        // rows. this is because this loop determines in what order we fetch report data.
        foreach ($dates as $index => $date) {
            foreach ($segments as $segment) {
                $period = $periods[$index];

                $params = [];
                $params['segment'] = $segment;

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
    private function requestReport(DataTable $table, $method, $paramsToModify)
    {
        /** @var Period $period */
        $period = $table->getMetadata('period');

        $params = array_merge([
            'filter_limit' => -1,
            'filter_offset' => 0,
            'filter_sort_column' => '',
            'filter_truncate' => -1,
            'compare' => 0,
            'totals' => 0,
            'disable_queued_filters' => 1,
            'format_metrics' => 0,
            'idSite' => $table->getMetadata('site')->getId(),
            'period' => $period->getLabel(),
            'date' => $period->getDateStart()->toString(),
        ], $paramsToModify);

        return Request::processRequest($method, $params);
    }

    private function formatComparisonTables(DataTable $table)
    {
        $formatter = new Formatter();
        foreach ($table->getRows() as $row) {
            /** @var DataTable $comparisonTable */
            $comparisonTable = $row->getMetadata(DataTable\Row::COMPARISONS_METADATA_NAME);
            if (empty($comparisonTable)
                || $comparisonTable->getRowsCount() === 0
            ) { // sanity check
                continue;
            }

            $columnMappings = $this->getColumnMappings();
            $comparisonTable->filter(DataTable\Filter\ReplaceColumnNames::class, [$columnMappings]);

            foreach ($comparisonTable->getRows() as $rowie) {
                foreach ($rowie->getColumns() as $column => $value) {
                    if (is_numeric($column)) {
                        throw new \Exception("found wrong column: " . print_r($rowie->getColumns(), true) . ' - '
                            . print_r($columnMappings, true) . ' - ' . print_r($row, true));
                    }
                }
            }

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
        if (isset($modifiedParams['segment'])) {
            $metadata['compareSegment'] = $modifiedParams['segment'];
        }
        if (!empty($modifiedParams['period'])) {
            $metadata['comparePeriod'] = $modifiedParams['period'];
        }
        if (!empty($modifiedParams['date'])) {
            $metadata['compareDate'] = $modifiedParams['date'];
        }

        $this->addPrettifiedMetadata($metadata);

        $columns = [];
        if ($compareRow) {
            foreach ($compareRow as $name => $value) {
                if (!is_numeric($value)
                    || $name == 'label'
                ) {
                    continue;
                }

                $columns[$name] = $value;
            }
        } else {
            foreach ($row as $name => $value) {
                if (!is_numeric($value)
                    || $name == 'label'
                ) {
                    continue;
                }

                $columns[$name] = 0;
            }
        }

        $newRow = new DataTable\Row([
            DataTable\Row::COLUMNS => $columns,
            DataTable\Row::METADATA => $metadata,
        ]);

        // calculate changes (including processed metric changes)
        foreach ($newRow->getColumns() as $name => $value) {
            $valueToCompare = $row->getColumn($name) ?: 0;
            $change = DataTable\Filter\CalculateEvolutionFilter::calculate($value, $valueToCompare, $precision = 1, $appendPercent = false);

            if ($change > 0) {
                $change = '+' . $change;
            }
            $change .= '%';

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

    private function getColumnMappings()
    {
        $allMappings = Metrics::getMappingFromIdToName(); // TODO: cache this

        $mappings = [];
        foreach ($allMappings as $index => $name) {
            $mappings[$index] = $name;
            $mappings[$index . '_change'] = $name . '_change';
        }
        return $mappings;
    }

    private function checkComparisonLimit($n, $configName)
    {
        if ($n <= 1) {
            throw new \Exception("The [General] $configName INI config option must be greater than 1.");
        }
    }

    private function addPrettifiedMetadata(array &$metadata)
    {
        if (isset($metadata['compareSegment'])) {
            $storedSegment = $this->findSegment($metadata['compareSegment']);
            $metadata['compareSegmentPretty'] = $storedSegment ? $storedSegment['name'] : $metadata['compareSegment'];
        }
        if (!empty($metadata['comparePeriod'])
            && !empty($metadata['compareDate'])
        ) {
            $metadata['comparePeriodPretty'] = Period\Factory::build($metadata['comparePeriod'], $metadata['compareDate'])->getLocalizedLongString();
        }
    }

    public static function getAvailableSegments() // TODO: should this be cached in transient cache?
    {
        $segments = Request::processRequest('SegmentEditor.getAll', $override = [], $default = []);
        usort($segments, function ($lhs, $rhs) {
            return strcmp($lhs['name'], $rhs['name']);
        });
        return $segments;
    }

    private function findSegment($segment)
    {
        $segment = trim($segment);
        if (empty($segment)) {
            return ['name' => Piwik::translate('SegmentEditor_DefaultAllVisits')];
        }
        foreach ($this->availableSegments as $storedSegment) {
            if ($storedSegment['definition'] == $segment
                || $storedSegment['definition'] == urldecode($segment)
                || $storedSegment['definition'] == urlencode($segment)
            ) {
                return $storedSegment;
            }
        }
        return null;
    }
}