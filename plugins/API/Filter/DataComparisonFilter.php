<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\Filter;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Http\BadRequestException;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Period\Factory;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugins\API\Filter\DataComparisonFilter\ComparisonRowGenerator;
use Piwik\Segment;
use Piwik\Site;

/**
 * Handles the API portion of the data comparison feature.
 *
 * If the `compareSegments`/`comparePeriods`/`compareDates` parameters are supplied this class will fetch
 * the data to compare with and store this data next to each row in the root report.
 *
 * Additionally, `..._change` columns will be added that show the percentage change for a column. This is only
 * done when comparing periods, since segments are subsets of visits, so it doesn't make sense to consider
 * differences between them as "changes".
 *
 * ### Comparing multiple periods
 *
 * It is possible to compare multiple periods with other multiple periods. For example, it
 * is possible to compare period=day, date=2018-02-03,2018-02-13 with period=day, date=2018-03-01,2018-03-15.
 * When done, this filter will compare the first period in the first set w/ the first period in the second set,
 * etc. So in the previous example, 2018-02-03 will be compared with 2018-03-01, 2018-02-04 with 2018-03-02, etc.
 *
 * ### Metadata
 *
 * This filter adds the following metadata to DataTables:
 *
 * - 'compareSegments': The list of segments being compared. The first entry will always be the value of the `segment` query param.
 * - 'comparePeriods': The list of labels of periods being compared. The first entry will always be the value of the
 *                     `period` query param.
 * - 'compareDates': The list of dates being compared. The first entry will always be the value of the `date` query param.
 * - 'comparisonSeries': Prettified labels for every comparison series in order.
 *
 * This filter adds the following metadata to rows in the comparison DataTables:
 *
 * - 'compareSegment': The segment of the data for the comparison row.
 * - 'compareSegmentPretty': The prettified label for the segment.
 * - 'comparePeriod': The period label for the data in the comparison row. This does not have to match a value in the
 *                    `comparePeriods` query parameter if comparing multiple periods.
 * - 'compareDate': The date for the period in the data in the comparison row. This does not have to match a value in the
 *                    `compareDates` query parameter if comparing multiple periods.
 * - 'comparePeriodPretty': The prettified label for the period.
 * - 'compareSeriesPretty': Prettified label for the comparison data represented by the row. This will match an entry
 *                          in the DataTable's `comparisonSeries` metadata.
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

    /**
     * @var ComparisonRowGenerator
     */
    private $comparisonRowGenerator;

    /**
     * @var array
     */
    private $columnMappings;

    /**
     * @var bool
     */
    private $invertCompareChangeCompute;

    public function __construct($request, ?Report $report = null)
    {
        $this->request = new \Piwik\Request($request);

        $generalConfig = Config::getInstance()->General;
        $this->segmentCompareLimit = (int) $generalConfig['data_comparison_segment_limit'];
        $this->checkComparisonLimit($this->segmentCompareLimit, 'data_comparison_segment_limit');

        $this->periodCompareLimit = (int) $generalConfig['data_comparison_period_limit'];
        $this->checkComparisonLimit($this->periodCompareLimit, 'data_comparison_period_limit');

        $this->segmentName = $this->getSegmentNameFromReport($report);

        $this->compareSegments = self::getCompareSegments();
        if (count($this->compareSegments) > $this->segmentCompareLimit + 1) {
            throw new BadRequestException(Piwik::translate('General_MaximumNumberOfSegmentsComparedIs', [$this->segmentCompareLimit]));
        }

        $this->compareDates = self::getCompareDates($request);
        $this->comparePeriods = self::getComparePeriods($request);

        if (count($this->compareDates) !== count($this->comparePeriods)) {
            throw new BadRequestException(Piwik::translate('General_CompareDatesParamMustMatchComparePeriods', ['compareDates', 'comparePeriods']));
        }

        if (count($this->compareDates) > $this->periodCompareLimit + 1) {
            throw new BadRequestException(Piwik::translate('General_MaximumNumberOfPeriodsComparedIs', [$this->periodCompareLimit]));
        }

        if (
            count($this->compareSegments) == 1
            && count($this->comparePeriods) == 1
        ) {
            return;
        }

        $this->checkMultiplePeriodCompare();

        // map segments/periods to their indexes in the query parameter arrays for comparisonIdSubtable matching
        $this->compareSegmentIndices = array_flip($this->compareSegments);
        foreach ($this->comparePeriods as $index => $period) {
            $date = $this->compareDates[$index];
            $this->comparePeriodIndices[$period][$date] = $index;
        }

        $this->invertCompareChangeCompute = $this->request->getIntegerParameter('invert_compare_change_compute', 0) === 1;
        if ($this->invertCompareChangeCompute && count($this->comparePeriods) != 2) {
            throw new \Exception("invert_compare_change_compute=1 can only be used when comparing two periods.");
        }

        $this->columnMappings = $this->getColumnMappings();
        $this->comparisonRowGenerator = new ComparisonRowGenerator($this->segmentName, $this->isRequestMultiplePeriod(), $this->columnMappings);
    }

    public static function isCompareParamsPresent($request = null)
    {
        return !empty(Common::getRequestVar('compareSegments', [], $type = 'array', $request))
            || !empty(Common::getRequestVar('comparePeriods', [], $type = 'array', $request))
            || !empty(Common::getRequestVar('compareDates', [], $type = 'array', $request));
    }

    /**
     * @param DataTable\DataTableInterface $table
     */
    public function compare(DataTable\DataTableInterface $table)
    {
        if (
            empty($this->compareSegments)
            && empty($this->comparePeriods)
        ) {
            return;
        }

        $method = $this->request->getStringParameter('method');
        if ($method === 'Live') {
            throw new \Exception("Data comparison is not enabled for the Live API.");
        }

        // optimization, if empty, single table, don't need to make extra queries
        if ($table->getRowsCount() == 0) {
            return;
        }

        $comparisonSeries = [];

        // fetch data first
        $reportsToCompare = self::getReportsToCompare($this->compareSegments, $this->comparePeriods, $this->compareDates);
        foreach ($reportsToCompare as $index => $modifiedParams) {
            $compareMetadata = $this->getMetadataFromModifiedParams($modifiedParams);
            $comparisonSeries[] = $compareMetadata['compareSeriesPretty'];

            $compareTable = $this->requestReport($method, $modifiedParams);
            $this->comparisonRowGenerator->compareTables($compareMetadata, $table, empty($compareTable) ? null : $compareTable);
        }

        // calculate changes (including processed metric changes)
        // NOTE: it doesn't make to sense to calculate these values for segments, since segments are subsets of all visits, where periods are
        //       time periods (so things can change from one to another).
        if (count($this->comparePeriods) > 1) {
            $this->compareChangePercents($table);
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

                if (
                    !empty($period)
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
                'label' => '',
                'flat' => $this->request->getIntegerParameter('flat', 0),
                'filter_add_columns_when_show_all_columns' => $this->request->getStringParameter('filter_add_columns_when_show_all_columns', ''),
                'filter_update_columns_when_show_all_goals' => $this->request->getStringParameter('filter_update_columns_when_show_all_goals', ''),
                'filter_show_goal_columns_process_goals' => $this->request->getStringParameter('filter_show_goal_columns_process_goals', ''),
                'idGoal' => $this->request->getStringParameter('idGoal', ''),
            ],
            $paramsToModify
        );

        $params['keep_totals_row'] = $this->request->getIntegerParameter('keep_totals_row', 0);
        $params['keep_totals_row_label'] = $this->request->getStringParameter('keep_totals_row_label', '');

        if (!isset($params['idSite'])) {
            $params['idSite'] = $this->request->getStringParameter('idSite');
        }
        if (!isset($params['period'])) {
            $params['period'] = $this->request->getStringParameter('period');
        }
        if (!isset($params['date'])) {
            $params['date'] = $this->request->getStringParameter('date');
        }

        $idSubtable = $this->request->getIntegerParameter('idSubtable', 0);
        if ($idSubtable > 0) {
            $comparisonIdSubtables = $this->request->getJsonParameter('comparisonIdSubtables', false);
            if (empty($comparisonIdSubtables)) {
                throw new \Exception("Comparing segments/periods with subtables only works when the comparison idSubtables are supplied as well.");
            }

            $segmentIndex = empty($paramsToModify['segment']) ? 0 : $this->compareSegmentIndices[$paramsToModify['segment']];
            $periodIndex = empty($paramsToModify['period']) ? 0 : $this->comparePeriodIndices[$paramsToModify['period']][$paramsToModify['date']];
            $seriesIndex = self::getComparisonSeriesIndex(null, $periodIndex, $segmentIndex, count($this->compareSegments));

            if (!isset($comparisonIdSubtables[$seriesIndex])) {
                throw new \Exception("Invalid comparisonIdSubtables parameter: no idSubtable found for segment $segmentIndex and period $periodIndex");
            }

            $comparisonIdSubtable = $comparisonIdSubtables[$seriesIndex];
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
            $rows = $table->getRows();

            $totalRow = $table->getTotalsRow();
            if ($totalRow) {
                $rows[] = $totalRow;
            }

            foreach ($rows as $row) {
                /** @var DataTable $comparisonTable */
                $comparisonTable = $row->getComparisons();
                if (!empty($comparisonTable)) { // sanity check
                    $columnMappings = $this->columnMappings;
                    $comparisonTable->filter(DataTable\Filter\ReplaceColumnNames::class, [$columnMappings]);
                }

                $subtable = $row->getSubtable();
                if ($subtable) {
                    $this->formatComparisonTables($subtable);
                }
            }
        });
    }

    private function checkComparisonLimit($n, $configName)
    {
        if ($n <= 1) {
            throw new \Exception("The [General] $configName INI config option must be greater than 1.");
        }
    }

    private function getMetadataFromModifiedParams($modifiedParams)
    {
        $metadata = [];

        $period = isset($modifiedParams['period']) ? $modifiedParams['period'] : reset($this->comparePeriods);
        $date = isset($modifiedParams['date']) ? $modifiedParams['date'] : reset($this->compareDates);
        $segment = isset($modifiedParams['segment']) ? $modifiedParams['segment'] : reset($this->compareSegments);

        $metadata['compareSegment'] = $segment;

        $idSite = $modifiedParams['idSite'] ?? $this->request->getStringParameter('idSite');

        $segmentObj = new Segment($segment, [$idSite]);
        $metadata['compareSegmentPretty'] = $segmentObj->getStoredSegmentName($idSite);

        $metadata['comparePeriod'] = $period;
        $metadata['compareDate'] = $date;

        $prettyPeriod = Factory::build($period, $date)->getLocalizedLongString();
        $metadata['comparePeriodPretty'] = ucfirst($prettyPeriod);

        $metadata['compareSeriesPretty'] = self::getComparisonSeriesLabelSuffixFromParts(
            $metadata['comparePeriodPretty'],
            $metadata['compareSegmentPretty']
        );

        return $metadata;
    }

    private static function getComparisonSeriesLabelSuffixFromParts($periodPretty, $segmentPretty)
    {
        $comparisonLabels = [
            $periodPretty,
            $segmentPretty,
        ];
        $comparisonLabels = array_filter($comparisonLabels);

        return '(' . implode(') (', $comparisonLabels) . ')';
    }

    private function getSegmentNameFromReport(?Report $report = null)
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
            $period = $this->request->getStringParameter('period');
            $date = $this->request->getStringParameter('date');

            $this->isRequestMultiplePeriod = Period::isMultiplePeriod($date, $period);
        }
        return $this->isRequestMultiplePeriod;
    }

    private function compareChangePercents(DataTableInterface $result)
    {
        $segmentCount = count($this->compareSegments);

        $result->filter(function (DataTable $table) use ($segmentCount) {
            $rows = $table->getRows();

            $totalRow = $table->getTotalsRow();
            if ($totalRow) {
                $rows[] = $totalRow;
            }

            foreach ($rows as $row) {
                $comparisons = $row->getComparisons();
                if (empty($comparisons)) {
                    continue;
                }

                /** @var DataTable\Row[] $rows */
                $rows = array_values($comparisons->getRows());
                foreach ($rows as $index => $compareRow) {
                    [$periodIndex, $segmentIndex] = self::getIndividualComparisonRowIndices($table, $index, $segmentCount);

                    if (!$this->invertCompareChangeCompute && $index < $segmentCount) {
                        continue; // do not calculate for first period
                    } elseif ($this->invertCompareChangeCompute && $periodIndex != 0) {
                        continue; // when inverting change calculation, only calculate for first period rows
                    }

                    if (!$this->invertCompareChangeCompute) {
                        $otherPeriodRowIndex = $segmentIndex;
                        $otherPeriodRow = $comparisons[$otherPeriodRowIndex];
                    } else {
                        $otherPeriodIndex = 1;
                        $otherPeriodRowIndex = self::getComparisonSeriesIndex($table, $otherPeriodIndex, $segmentIndex, $segmentCount);
                        $otherPeriodRow = $comparisons[$otherPeriodRowIndex];
                    }

                    foreach ($compareRow->getColumns() as $name => $value) {
                        [$changeTo, $trendTo] = $this->computeChangePercent($otherPeriodRow, $compareRow, $name);
                        $compareRow->addColumn($name . '_change', $changeTo);
                        if ($this->shouldIncludeTrendValues()) {
                            $compareRow->addColumn($name . '_trend', $trendTo);
                        }

                        [$changeFrom, $trendFrom] = $this->computeChangePercent($compareRow, $otherPeriodRow, $name);
                        $compareRow->addColumn($name . '_change_from', $changeFrom);
                        if ($this->shouldIncludeTrendValues()) {
                            $compareRow->addColumn($name . '_trend_from', $trendFrom);
                        }
                    }
                }
            }
        });
    }

    private function computeChangePercent(DataTable\Row $fromRow, DataTable\Row $toRow, $columnName)
    {
        $value = $toRow ? $toRow->getColumn($columnName) : 0;
        $value = $value ?: 0;

        $valueToCompare = $fromRow ? $fromRow->getColumn($columnName) : 0;
        $valueToCompare = $valueToCompare ?: 0;

        $change = DataTable\Filter\CalculateEvolutionFilter::calculate($value, $valueToCompare, $precision = 1, true, true);
        $trend = $value - $valueToCompare < 0 ? -1 : ($value - $valueToCompare > 0 ? 1 : 0);

        return [$change, $trend];
    }

    /**
     * Returns the period and segment indices for a given comparison index.
     *
     * @param DataTable|null $table
     * @param $comparisonRowIndex
     * @param null $segmentCount
     * @return array
     */
    public static function getIndividualComparisonRowIndices($table, $comparisonRowIndex, $segmentCount = null)
    {
        $segmentCount = $segmentCount ?: count($table->getMetadata('compareSegments'));
        $segmentIndex = $comparisonRowIndex % $segmentCount;
        $periodIndex = floor($comparisonRowIndex / $segmentCount);
        return [$periodIndex, $segmentIndex];
    }

    /**
     * Returns the series index for a comparison based on the period and segment indices.
     *
     * @param DataTable|null $table
     * @param int $periodIndex
     * @param int $segmentIndex
     * @param int|null $segmentCount
     * @return int
     */
    public static function getComparisonSeriesIndex($table, $periodIndex, $segmentIndex, $segmentCount = null)
    {
        $segmentCount = $segmentCount ?: count($table->getMetadata('compareSegments'));
        return $periodIndex * $segmentCount + $segmentIndex;
    }

    private static function getCompareSegments($request = null)
    {
        $segments = Common::getRequestVar('compareSegments', $default = [], $type = 'array', $request);
        array_unshift($segments, Common::getRequestVar('segment', '', 'string', $request));
        $segments = Common::unsanitizeInputValues($segments);
        return $segments;
    }

    private static function getComparePeriods($request = null)
    {
        $periods = Common::getRequestVar('comparePeriods', $default = [], $type = 'array', $request);
        array_unshift($periods, Common::getRequestVar('period', '', 'string', $request));
        return array_values($periods);
    }

    private static function getCompareDates($request = null)
    {
        $dates = Common::getRequestVar('compareDates', $default = [], $type = 'array', $request);
        array_unshift($dates, Common::getRequestVar('date', '', 'string', $request));
        return array_values($dates);
    }

    /**
     * Returns whether to include trend values for all evolution columns or not
     * This is requested only for sparklines
     *
     * @see \Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines::render()
     *
     * @return bool
     * @throws \Exception
     */
    private function shouldIncludeTrendValues(): bool
    {
        return $this->request->getBoolParameter('include_trends', false);
    }

    /**
     * Returns the pretty series label for a specific comparison based on the currently set comparison query parameters.
     *
     * @param int $labelSeriesIndex The index of the comparison. Comparison series order is determined by {@see self::getReportsToCompare()}.
     */
    public static function getPrettyComparisonLabelFromSeriesIndex($labelSeriesIndex)
    {
        $compareSegments = self::getCompareSegments();
        $comparePeriods = self::getComparePeriods();
        $compareDates = self::getCompareDates();

        [$periodIndex, $segmentIndex] = self::getIndividualComparisonRowIndices(null, $labelSeriesIndex, count($compareSegments));

        $idSite = \Piwik\Request::fromRequest()->getStringParameter('idSite');
        $segmentObj = new Segment($compareSegments[$segmentIndex], [$idSite]);
        $prettySegment = $segmentObj->getStoredSegmentName($idSite);

        $prettyPeriod = Factory::build($comparePeriods[$periodIndex], $compareDates[$periodIndex])->getLocalizedLongString();
        $prettyPeriod = ucfirst($prettyPeriod);

        return self::getComparisonSeriesLabelSuffixFromParts($prettyPeriod, $prettySegment);
    }

    private function getColumnMappings()
    {
        $allMappings = Metrics::getMappingFromIdToName();

        $mappings = [];
        foreach ($allMappings as $index => $name) {
            $mappings[$index] = $name;
            $mappings[$index . '_change'] = $name . '_change';
            $mappings[$index . '_change_from'] = $name . '_change_from';
            $mappings[$index . '_trend'] = $name . '_trend';
            $mappings[$index . '_trend_from'] = $name . '_trend_from';
        }
        return $mappings;
    }
}
