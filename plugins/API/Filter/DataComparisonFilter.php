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
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Simple;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Period;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugin\Report;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;
use Piwik\Site;

// TODO: unit test
// TODO: if comparing days w/ non-days, in html table & elsewhere, we must display nb_visits instead of nb_uniq_visitors

/**
 * TODO
 */
class DataComparisonFilter
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

    /**
     * @var string
     */
    private $segmentName;

    /**
     * @var string[]
     */
    private $compareSegments;

    /**
     * @var string[]
     */
    private $compareDates;

    /**
     * @var string[]
     */
    private $comparePeriods;

    /**
     * @var int[]
     */
    private $compareSegmentIndices;

    /**
     * @var int[]
     */
    private $comparePeriodIndices;

    /**
     * @var bool
     */
    private $isRequestMultiplePeriod;

    public function __construct($request, Report $report = null)
    {
        $this->request = $request;

        $generalConfig = Config::getInstance()->General;
        $this->segmentCompareLimit = (int) $generalConfig['data_comparison_segment_limit'];
        $this->checkComparisonLimit($this->segmentCompareLimit, 'data_comparison_segment_limit');

        $this->periodCompareLimit = (int) $generalConfig['data_comparison_period_limit'];
        $this->checkComparisonLimit($this->periodCompareLimit, 'data_comparison_period_limit');

        $this->segmentName = $this->getSegmentNameFromReport($report);

        $this->compareSegments = Common::getRequestVar('compareSegments', $default = [], $type = 'array', $this->request);
        $this->compareSegments = Common::unsanitizeInputValues($this->compareSegments);
        if (count($this->compareSegments) > $this->segmentCompareLimit) {
            throw new \Exception("The maximum number of segments that can be compared simultaneously is {$this->segmentCompareLimit}.");
        }

        $this->compareDates = Common::getRequestVar('compareDates', $default = [], $type = 'array', $this->request);
        $this->compareDates = array_values($this->compareDates);

        $this->comparePeriods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array', $this->request);
        $this->comparePeriods = array_values($this->comparePeriods);

        if (count($this->compareDates) !== count($this->comparePeriods)) {
            throw new \InvalidArgumentException("compareDates query parameter length must match comparePeriods query parameter length.");
        }

        if (count($this->compareDates) > $this->periodCompareLimit) {
            throw new \Exception("The maximum number of periods that can be compared simultaneously is {$this->periodCompareLimit}.");
        }

        if (empty($this->compareSegments)
            && empty($this->comparePeriods)
        ) {
            throw new \Exception("compare=1 set, but no segments or periods to compare.");
        }

        $this->checkMultiplePeriodCompare();

        // add base compare against segment and date
        array_unshift($this->compareSegments, isset($this->request['segment']) ? $this->request['segment'] : '');
        array_unshift($this->compareDates, ''); // for date/period, we use the metadata in the table to avoid requesting multiple periods
        array_unshift($this->comparePeriods, '');

        // map segments/periods to their indexes in the query parameter arrays for comparisonIdSubtable matching
        $this->compareSegmentIndices = array_flip($this->compareSegments);
        foreach ($this->comparePeriods as $index => $period) {
            $date = $this->compareDates[$index];
            $this->comparePeriodIndices[$period][$date] = $index;
        }
    }

    /**
     * @param DataTable\DataTableInterface $table
     */
    public function compare(DataTable\DataTableInterface $table)
    {
        $method = Common::getRequestVar('method', $default = null, $type = 'string', $this->request);
        if ($method == 'Live') {
            throw new \Exception("Data comparison is not enabled for the Live API.");
        }

        // optimization, if empty, single table, don't need to make extra queries
        if ($table->getRowsCount() == 0) {
            return;
        }

        $this->columnMappings = $this->getColumnMappings();

        $this->availableSegments = self::getAvailableSegments();

        $comparisonSeries = [];

        // fetch data first
        $reportsToCompare = self::getReportsToCompare($this->compareSegments, $this->comparePeriods, $this->compareDates);
        foreach ($reportsToCompare as $index => $modifiedParams) {
            $compareMetadata = $this->getMetadataFromModifiedParams($modifiedParams);
            $comparisonSeries[] = $compareMetadata['compareSeriesPretty'];

            $compareTable = $this->requestReport($method, $modifiedParams);
            $this->compareTables($compareMetadata, $table, $compareTable); // TODO: set comparison totals here + handle correctly
        }

        // format comparison table metrics
        $this->formatComparisonTables($table);

        // add comparison parameters as metadata
        $table->filter(function (DataTable $singleTable) use ($comparisonSeries) {
            if (isset($this->compareSegments)) {
                $singleTable->setMetadata('compareSegments', $this->compareSegments);
            }

            if (isset($this->comparePeriods)) {
                $singleTable->setMetadata('comparePeriods', $this->comparePeriods);
            }

            if (isset($this->compareDates)) {
                $singleTable->setMetadata('compareDates', $this->compareDates);
            }

            $singleTable->setMetadata('comparisonSeries', $comparisonSeries);
        });
    }

    public static function getReportsToCompare($compareSegments, $comparePeriods, $compareDates)
    {
        $permutations = [];

        // NOTE: the order of these loops determines the order of the rows in the comparison table. ie,
        // if we loop over dates then segments, then we'll see comparison rows change segments before changing
        // periods. this is because this loop determines in what order we fetch report data.
        foreach ($compareDates as $index => $date) {
            foreach ($compareSegments as $segment) {
                $period = $comparePeriods[$index];

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
    private function requestReport($method, $paramsToModify)
    {
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
            $params['idSite'] = Common::getRequestVar('idSite', null, 'string', $this->request);
        }
        if (!isset($params['period'])) {
            $params['period'] = Common::getRequestVar('period', null, 'string', $this->request);
        }
        if (!isset($params['date'])) {
            $params['date'] = Common::getRequestVar('date', null, 'string', $this->request);
        }

        $idSubtable = Common::getRequestVar('idSubtable', 0, 'int', $this->request);
        if ($idSubtable > 0) {
            $comparisonIdSubtables = Common::getRequestVar('comparisonIdSubtables', $default = false, 'json', $this->request);
            if (empty($comparisonIdSubtables)) {
                throw new \Exception("Comparing segments/periods with subtables only works when the comparison idSubtables are supplied as well.");
            }

            $segmentIndex = empty($paramsToModify['segment']) ? 0 : $this->compareSegmentIndices[$paramsToModify['segment']];
            $periodIndex = empty($paramsToModify['period']) ? 0 : $this->comparePeriodIndices[$paramsToModify['period']][$paramsToModify['date']];

            if (!isset($comparisonIdSubtables[$segmentIndex][$periodIndex])) {
                throw new \Exception("Invalid comparisonIdSubtables parameter: no idSubtable found for segment $segmentIndex and period $periodIndex");
            }

            $comparisonIdSubtable = $comparisonIdSubtables[$segmentIndex][$periodIndex];
            if ($comparisonIdSubtable === -1) { // no subtable in comparison row
                $table = new DataTable();
                $table->setMetadata('site', new Site($params['idSite']));
                $table->setMetadata('period', Period\Factory::build($params['period'], $params['date']));
                return $table;
            }

            $params['idSubtable'] = $comparisonIdSubtable;
        }

        return Request::processRequest($method, $params);
    }

    private function formatComparisonTables(DataTableInterface $tableOrMap)
    {
        $tableOrMap->filter(function (DataTable $table) {
            $formatter = new Formatter();
            foreach ($table->getRows() as $row) {
                /** @var DataTable $comparisonTable */
                $comparisonTable = $row->getComparisons();
                if (!empty($comparisonTable)
                    && $comparisonTable->getRowsCount() > 0
                ) { // sanity check
                    $columnMappings = $this->columnMappings;
                    $comparisonTable->filter(DataTable\Filter\ReplaceColumnNames::class, [$columnMappings]);

                    $formatter->formatMetrics($comparisonTable);
                }

                $subtable = $row->getSubtable();
                if ($subtable) {
                    $this->formatComparisonTables($subtable);
                }
            }
        });
    }

    private function compareRow($compareMetadata, DataTable\Row $row, DataTable\Row $compareRow = null, DataTable $rootTable = null)
    {
        $comparisonDataTable = $row->getComparisons();
        if (empty($comparisonDataTable)) {
            $comparisonDataTable = new DataTable();
            $row->setComparisons($comparisonDataTable);
        }

        $this->addIndividualChildPrettifiedMetadata($compareMetadata, $rootTable);

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
            DataTable\Row::METADATA => $compareMetadata,
        ]);

        // set subtable
        $newRow->setMetadata('idsubdatatable_in_db', -1);
        if ($compareRow) {
            $subtableId = $compareRow->getMetadata('idsubdatatable_in_db') ?: $compareRow->getIdSubDataTable();
            if ($subtableId) {
                $newRow->setMetadata('idsubdatatable_in_db', $subtableId);
            }
        }

        // add segment metadatas
        if ($row->getMetadata('segment')) {
            $newSegment = $row->getMetadata('segment');
            if ($newRow->getMetadata('compareSegment')) {
                $newSegment = Segment::combine($newRow->getMetadata('compareSegment'), SegmentExpression::AND_DELIMITER, $newSegment);
            }
            $newRow->setMetadata('segment', $newSegment);
        } else if ($this->segmentName
            && $row->getMetadata('segmentValue') !== false
        ) {
            $segmentValue = $row->getMetadata('segmentValue');
            $newRow->setMetadata('segment', sprintf('%s==%s', $this->segmentName, urlencode($segmentValue)));
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
            $this->compareTable($compareMetadata, $subtable, $rootTable, $compareRow->getSubtable());
        }
    }

    private function compareTables($compareMetadata, DataTableInterface $tables, DataTableInterface $compareTables = null)
    {
        if ($tables instanceof DataTable) {
            $this->compareTable($compareMetadata, $tables, $compareTables, $compareTables);
        } else if ($tables instanceof DataTable\Map) {
            $childTablesArray = array_values($tables->getDataTables());
            $compareTablesArray = isset($compareTables) ? array_values($compareTables->getDataTables()) : [];

            $isDatePeriod = $tables->getKeyName() == 'date';

            foreach ($childTablesArray as $index => $childTable) {
                $compareChildTable = isset($compareTablesArray[$index]) ? $compareTablesArray[$index] : null;
                $this->compareTables($compareMetadata, $childTable, $compareChildTable);
            }

            // in case one of the compared periods has more periods than the main one, we want to fill the result with empty datatables
            // so the comparison data is still present. this allows us to see that data in an evolution report.
            if ($isDatePeriod) {
                $lastTable = end($childTablesArray);

                /** @var Period $lastPeriod */
                $lastPeriod = $lastTable->getMetadata('period');
                $periodType = $lastPeriod->getLabel();

                for ($i = count($childTablesArray); $i < count($compareTablesArray); ++$i) {
                    $periodChangeCount = $i - count($childTablesArray) + 1;
                    $newPeriod = Period\Factory::build($periodType, $lastPeriod->getDateStart()->addPeriod($periodChangeCount, $periodType));

                    // create an empty table for the main request
                    $newTable = new DataTable();
                    $newTable->setAllTableMetadata($lastTable->getAllTableMetadata());
                    $newTable->setMetadata('period', $newPeriod);

                    if ($newPeriod->getLabel() === 'week' || $newPeriod->getLabel() === 'range') {
                        $periodLabel = $newPeriod->getRangeString();
                    } else {
                        $periodLabel = $newPeriod->getPrettyString();
                    }

                    $tables->addTable($newTable, $periodLabel);

                    // compare with the empty table
                    $compareTable = $compareTablesArray[$i];
                    $this->compareTables($compareMetadata, $newTable, $compareTable);
                }
            }
        } else {
            throw new \Exception("Unexpected DataTable type: " . get_class($tables));
        }
    }

    private function compareTable($compareMetadata, DataTable $table, DataTable $rootCompareTable = null, DataTable $compareTable = null)
    {
        // if there are no rows in the table because the metrics are 0, add one so we can still set comparison values
        if ($table->getRowsCount() == 0) {
            $table->addRow(new DataTable\Row());
        }

        if (!$compareTable) {
            return;
        }

        foreach ($table->getRows() as $row) {
            $label = $row->getColumn('label');

            $compareRow = null;
            if ($compareTable instanceof Simple) {
                $compareRow = $compareTable->getFirstRow() ?: null;
            } else if ($compareTable instanceof DataTable) {
                $compareRow = $compareTable->getRowFromLabel($label) ?: null;
            }

            $this->compareRow($compareMetadata, $row, $compareRow, $rootCompareTable);
        }

        $totals = $compareTable->getMetadata('totals');
        if (!empty($totals)) {
            $totals = $this->replaceIndexesInTotals($totals);
            $comparisonTotalsEntry = array_merge($compareMetadata, [
                'totals' => $totals,
            ]);

            $allTotalsTables = $compareTable->getMetadata('comparisonTotals');
            $allTotalsTables[] = $comparisonTotalsEntry;
            $compareTable->setMetadata('comparisonTotals', $allTotalsTables);
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

    private function addIndividualChildPrettifiedMetadata(array &$metadata, DataTable $parentTable = null)
    {
        if ($parentTable) {
            /** @var Period $period */
            $period = $parentTable->getMetadata('period');

            $prettyPeriod = $period->getLocalizedLongString();
            $metadata['comparePeriodPretty'] = ucfirst($prettyPeriod);

            $metadata['comparePeriod'] = $period->getLabel();
            $metadata['compareDate'] = $period->getDateStart()->toString();
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

            $storedSegment = $this->findSegment($metadata['compareSegment']);
            $metadata['compareSegmentPretty'] = $storedSegment ? $storedSegment['name'] : $metadata['compareSegment'];
        }
        if (!empty($modifiedParams['period'])) {
            $metadata['comparePeriod'] = $modifiedParams['period'];
            $metadata['compareDate'] = $modifiedParams['date'];
        }

        // set compareSeriesPretty
        $segmentPretty = isset($metadata['compareSegmentPretty']) ? $metadata['compareSegmentPretty'] : '';

        $period = isset($metadata['comparePeriod']) ? $metadata['comparePeriod'] : Common::getRequestVar('period', null, 'string', $this->request);
        $date = isset($metadata['compareDate']) ? $metadata['compareDate'] : Common::getRequestVar('date', null, 'string', $this->request);

        $periodPretty = Factory::build($period, $date)->getLocalizedLongString();
        $periodPretty = ucfirst($periodPretty);

        $metadata['compareSeriesPretty'] = $this->getComparisonSeriesLabelSuffixFromParts($periodPretty, $segmentPretty);

        // TODO: we should document this metadata
        return $metadata;
    }

    private function getComparisonSeriesLabelSuffixFromParts($periodPretty, $segmentPretty)
    {
        $comparisonLabels = [
            $periodPretty,
            $segmentPretty,
        ];
        $comparisonLabels = array_filter($comparisonLabels);

        return '(' . implode(') (', $comparisonLabels) . ')';
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

    private function getSegmentNameFromReport(Report $report = null)
    {
        if (empty($report)) {
            return null;
        }

        $dimension = $report->getDimension();
        if (empty($dimension)) {
            return null;
        }

        $segments = $dimension->getSegments();
        if (empty($segments)) {
            return null;
        }

        /** @var \Piwik\Plugin\Segment $segment */
        $segment     = reset($segments);
        $segmentName = $segment->getSegment();
        return $segmentName;
    }

    private function checkMultiplePeriodCompare()
    {
        if ($this->isRequestMultiplePeriod()) {
            foreach ($this->comparePeriods as $index => $period) {
                if (!Period::isMultiplePeriod($this->compareDates[$index], $period)) {
                    throw new \Exception("Cannot compare: original request is multiple period and cannot be compared with single periods.");
                }
            }
        } else {
            foreach ($this->comparePeriods as $index => $period) {
                if (Period::isMultiplePeriod($this->compareDates[$index], $period)) {
                    throw new \Exception("Cannot compare: original request is single period and cannot be compared with multiple periods.");
                }
            }
        }
    }

    private function isRequestMultiplePeriod()
    {
        if ($this->isRequestMultiplePeriod === null) {
            $period = Common::getRequestVar('period', $default = null, 'string', $this->request);
            $date = Common::getRequestVar('date', $default = null, 'string', $this->request);

            $this->isRequestMultiplePeriod = Period::isMultiplePeriod($date, $period);
        }
        return $this->isRequestMultiplePeriod;
    }
}