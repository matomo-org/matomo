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
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;

// TODO: unit test

// TODO: if comparing days w/ non-days, in html table & elsewhere, we must display nb_visits instead of nb_uniq_visitors

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

    /**
     * @var array
     */
    private $columnMappings;

    public function __construct(DataTable $table, $request)
    {
        parent::__construct($table);
        $this->request = $request;

        $generalConfig = Config::getInstance()->General;
        $this->segmentCompareLimit = (int) $generalConfig['data_comparison_segment_limit'];
        $this->checkComparisonLimit($this->segmentCompareLimit, 'data_comparison_segment_limit');

        $this->periodCompareLimit = (int) $generalConfig['data_comparison_period_limit'];
        $this->checkComparisonLimit($this->periodCompareLimit, 'data_comparison_period_limit');

        $this->columnMappings = $this->getColumnMappings();
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
        if (count($segments) > $this->segmentCompareLimit) {
            throw new \Exception("The maximum number of segments that can be compared simultaneously is {$this->segmentCompareLimit}.");
        }

        $dates = Common::getRequestVar('compareDates', $default = [], $type = 'array', $this->request);
        $dates = array_values($dates);

        $periods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array', $this->request);
        $periods = array_values($periods);

        if (count($dates) !== count($periods)) {
            throw new \InvalidArgumentException("compareDates query parameter length must match comparePeriods query parameter length.");
        }

        if (count($dates) > $this->periodCompareLimit) {
            throw new \Exception("The maximum number of periods that can be compared simultaneously is {$this->periodCompareLimit}.");
        }

        if (empty($segments)
            && empty($periods)
        ) {
            throw new \Exception("compare=1 set, but no segments or periods to compare.");
        }

        $this->availableSegments = self::getAvailableSegments();

        $comparisonTotals = [];

        $reportsToCompare = $this->getReportsToCompare($segments, $dates, $periods);
        foreach ($reportsToCompare as $modifiedParams) {
            $metadata = $this->getMetadataFromModifiedParams($modifiedParams);

            $compareTable = $this->requestReport($table, $method, $modifiedParams);
            $this->compareTables($metadata, $table, $compareTable);

            $totals = $compareTable->getMetadata('totals');
            if (!empty($totals)) {
                $totals = $this->replaceIndexesInTotals($totals);
                $comparisonTotals[] = array_merge($metadata, [
                    'totals' => $totals,
                ]);
            }

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

        if (!empty($comparisonTotals)) {
            $table->setMetadata('comparisonTotals', $comparisonTotals);
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

        $params = array_merge(
            [
                'filter_limit' => -1,
                'filter_offset' => 0,
                'filter_sort_column' => '',
                'filter_truncate' => -1,
                'compare' => 0,
                'totals' => 1,
                'disable_queued_filters' => 1,
                'format_metrics' => 0,
            ],
            $paramsToModify
        );

        if (!isset($params['idSite'])) {
            $params['idSite'] = $table->getMetadata('site')->getId();
        }
        if (!isset($params['period'])) {
            $params['period'] = $period->getLabel();
        }
        if (!isset($params['date'])) {
            $params['date'] = $period->getDateStart()->toString();
        }

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

            $columnMappings = $this->columnMappings;
            $comparisonTable->filter(DataTable\Filter\ReplaceColumnNames::class, [$columnMappings]);

            $formatter->formatMetrics($comparisonTable);

            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->formatComparisonTables($subtable);
            }
        }
    }

    private function compareRow($metadata, DataTable\Row $row, DataTable\Row $compareRow = null)
    {
        $comparisonDataTable = $row->getMetadata(DataTable\Row::COMPARISONS_METADATA_NAME);
        if (empty($comparisonDataTable)) {
            $comparisonDataTable = new DataTable();
            $row->setMetadata(DataTable\Row::COMPARISONS_METADATA_NAME, $comparisonDataTable);
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

        // add segment metadatas
        if ($row->getMetadata('segment')) {
            $newSegment = $row->getMetadata('segment');
            if ($newRow->getMetadata('compareSegment')) {
                $newSegment = Segment::combine($newRow->getMetadata('compareSegment'), SegmentExpression::AND_DELIMITER, $newSegment);
            }
            $newRow->setMetadata('segment', $newSegment);
        }

        // calculate changes (including processed metric changes)
        foreach ($newRow->getColumns() as $name => $value) {
            $valueToCompare = $row->getColumn($name) ?: 0;
            $change = DataTable\Filter\CalculateEvolutionFilter::calculate($value, $valueToCompare, $precision = 1, $appendPercent = false);

            if ($change >= 0) {
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
            $this->compareTables($metadata, $subtable, $compareRow->getSubtable());
        }
    }

    private function compareTables($metadata, DataTable $table, DataTable $compareTable = null)
    {
        // if there are no rows in the table because the metrics are 0, add one so we can still set comparison values
        if ($table->getRowsCount() == 0) {
            $table->addRow(new DataTable\Row());
        }

        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');

            $compareRow = null;
            if ($compareTable instanceof Simple) {
                $compareRow = $compareTable->getFirstRow();
            } else if ($compareTable instanceof DataTable) {
                $compareRow = $compareTable->getRowFromLabel($label) ?: null;
            }

            $this->compareRow($metadata, $row, $compareRow);
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
            $prettyPeriod = Period\Factory::build($metadata['comparePeriod'], $metadata['compareDate'])->getLocalizedLongString();
            $metadata['comparePeriodPretty'] = ucfirst($prettyPeriod);
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

    private function getMetadataFromModifiedParams($modifiedParams)
    {
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
        return $metadata;
    }

    private function replaceIndexesInTotals($totals)
    {
        foreach ($totals as $index => $value) {
            if (isset($this->columnMappings[$index])) {
                $name = $this->columnMappings[$index];
                $totals[$name] = $totals[$index];
                unset($totals[$index]);
            }
        }
        return $totals;
    }
}