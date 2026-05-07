<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\API\Request as ApiRequest;
use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Period\Factory;
use Piwik\Plugins\API\Filter\DataComparisonFilter;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution as JqplotEvolutionGraph;
use Piwik\Site;
use Piwik\Url;

/**
 * Generates JQPlot JSON data/config for evolution graphs.
 */
class Evolution extends JqplotDataGenerator
{
    public const MONOTONICITY_UP = 'up';
    public const MONOTONICITY_DOWN = 'down';
    public const MONOTONICITY_FREE = 'free';

    /**
     * Narrow the parent's untyped `$graph` to the evolution visualization, since
     * `JqplotDataGenerator\Evolution` is only ever constructed by
     * {@see JqplotEvolutionGraph::makeDataGenerator()}. Lets later code call
     * forecast-specific accessors without redundant `instanceof` checks.
     *
     * @var JqplotEvolutionGraph
     */
    protected $graph;

    protected function getUnitsForColumnsToDisplay()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $units = [];
        foreach ($this->properties['columns_to_display'] as $columnName) {
            $derivedUnit = Metrics::getUnit($columnName, $idSite);
            $units[$columnName] = empty($derivedUnit) ? false : $derivedUnit;
        }
        return $units;
    }

    /**
     * @param DataTable|DataTable\Map $dataTable
     * @param Chart $visualization
     */
    protected function initChartObjectData($dataTable, $visualization)
    {
        // if the loaded datatable is a simple DataTable, it is most likely a plugin plotting some custom data
        // we don't expect plugin developers to return a well defined Set

        if ($dataTable instanceof DataTable) {
            parent::initChartObjectData($dataTable, $visualization);
            return;
        }

        $dataTables = $dataTable->getDataTables();

        // determine x labels based on both the displayed date range and the compared periods
        /** @var Period[][] $xLabels */
        $xLabels = [
            [], // placeholder for first series
        ];

        $this->addComparisonXLabels($xLabels, reset($dataTables));
        $this->addSelectedSeriesXLabels($xLabels, $dataTables);

        $units = $this->getUnitsForColumnsToDisplay();

        // if rows to display are not specified, default to all rows (TODO: perhaps this should be done elsewhere?)
        $rowsToDisplay = $this->properties['rows_to_display']
            ? : array_unique($dataTable->getColumn('label'))
                ? : [false] // make sure that a series is plotted even if there is no data
        ;

        $columnsToDisplay = array_values($this->properties['columns_to_display']);

        [$seriesMetadata, $seriesUnits, $seriesLabels, $seriesToXAxis] =
            $this->getSeriesMetadata($rowsToDisplay, $columnsToDisplay, $units, $dataTables);

        if ($this->isComparing) {
            // Comparing graphs never render a forecast, so we only need the chart-rendering
            // data, not the forecast-state machinery the seriesState wrapper carries.
            $allSeriesData = $this->collectComparisonSeriesData(
                $rowsToDisplay,
                $columnsToDisplay,
                $seriesLabels,
                $dataTable
            );
            $seriesState = new ForecastSeriesState([], [], [], []);
        } else {
            // Reuse the per-series state precomputed in
            // JqplotGraph\Evolution::afterAllFiltersAreApplied() when forecast is on, instead
            // of running the same row × column collection loop again.
            $seriesState = $this->graph->getForecastSeriesState()
                ?? $this->collectForecastSeriesState($rowsToDisplay, $columnsToDisplay, $units, $dataTable);

            $allSeriesData = $seriesState->getAllSeriesData();
        }

        $visualization->properties = $this->properties;

        $units = null;
        if ($visualization->properties['request_parameters_to_modify']['format_metrics'] === 0) {
            $units = $seriesUnits;
        }
        $visualization->setAxisYValues($allSeriesData, $seriesMetadata, $units);
        $visualization->setAxisYUnits($seriesUnits);

        $xLabelStrs = [];
        $xAxisTicks = [];
        foreach ($xLabels as $index => $seriesXLabels) {
            $xLabelStrs[$index] = array_map(function (Period $p) {
                return $p->getLocalizedLongString();
            }, $seriesXLabels);
            $xAxisTicks[$index] = array_map(function (Period $p) {
                return $p->getLocalizedShortString();
            }, $seriesXLabels);
        }

        $visualization->setAxisXLabelsMultiple($xLabelStrs, $seriesToXAxis, $xAxisTicks);

        if ($this->isLinkEnabled()) {
            $idSite = Common::getRequestVar('idSite', null, 'int');
            $periodLabel = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLabel();

            $axisXOnClick = array();
            foreach ($dataTable->getDataTables() as $metadataDataTable) {
                $dateInUrl = $metadataDataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart();
                $parameters = array(
                    'idSite'  => $idSite,
                    'period'  => $periodLabel,
                    'date'    => $dateInUrl->toString(),
                    'segment' => \Piwik\API\Request::getRawSegmentFromRequest(),
                );
                $link = Url::getQueryStringFromParameters($parameters);
                $axisXOnClick[] = $link;
            }
            $visualization->setAxisXOnClick($axisXOnClick);
        }

        $dataStates = $this->setDataStates($visualization, $dataTables);
        $visualization->setForecastData($this->buildForecastData(
            $seriesState,
            $dataTables,
            $dataStates,
            $seriesUnits
        ));
    }

    /**
     * Compute forecast values via {@see ForecastBuilder} when forecast is enabled
     * and the graph is not in compare mode. Both gates short-circuit to an empty
     * payload — the builder would yield [] anyway, but checking here avoids
     * unnecessary work on the cold path.
     *
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @return array<int, array<int, float|null>>
     */
    protected function buildForecastData(
        ForecastSeriesState $seriesState,
        array $dataTables,
        array $dataStates,
        array $seriesUnits
    ): array {
        if (empty($this->properties['show_forecast']) || $this->isComparing) {
            return [];
        }

        $subPeriodSamples = $this->collectSubPeriodSamples(
            $dataTables,
            $seriesState->getAllSeriesColumns(),
            $seriesState->getAllSeriesRows(),
            $seriesState->getAllSeriesMonotonicity()
        );

        return (new ForecastBuilder())->build(
            $seriesState->getAllSeriesData(),
            $dataTables,
            $dataStates,
            $seriesUnits,
            $seriesState->getAllSeriesDataAvailability(),
            $seriesState->getAllSeriesMonotonicity(),
            $seriesState->getAllSeriesForecastPrecision(),
            $subPeriodSamples['daily'],
            $subPeriodSamples['monthly']
        );
    }

    /**
     * Resolve the metric semantic-type map. Wraps the static lookup so tests can substitute
     * a fixed map without seeding the global transient cache that backs
     * {@see Metrics::getDefaultMetricSemanticTypes()}.
     *
     * @return array<string, string>
     *
     * @internal
     */
    protected function getMetricSemanticTypes(): array
    {
        return Metrics::getDefaultMetricSemanticTypes();
    }

    /**
     * Classify a column into one of three intra-period directions:
     *
     * - MONOTONICITY_UP: counts/sums/totals that can only grow within the period
     *   ("forecast >= current" gate applies).
     * - MONOTONICITY_DOWN: running mins that can only fall within the period
     *   ("forecast <= current" gate applies).
     * - MONOTONICITY_FREE: ratios, rates, percentages, averages whose value can move in either
     *   direction within the period (no gate).
     *
     * Driven by column unit, semantic type, and a small name-convention layer. The convention
     * layer cannot disambiguate metrics whose names look like counts but are actually ratios
     * (ctr, position, web-vitals percentiles); those need a dedicated plugin signal.
     *
     * @param string|false $columnUnit
     * @return self::MONOTONICITY_*
     */
    private function getColumnMonotonicity(string $columnName, $columnUnit): string
    {
        if ($columnUnit === '%') {
            return self::MONOTONICITY_FREE;
        }

        // TYPE_PERCENT and TYPE_FLOAT are non-monotonic by construction (a percentage's or a
        // ratio's value can move in either direction within a partial period). Plugins extend
        // the semantic-type map via the Metrics.getDefaultMetricSemanticTypes event, so a
        // custom metric declared as TYPE_PERCENT or TYPE_FLOAT classifies correctly without
        // needing a magic name.
        $semanticType = $this->getMetricSemanticTypes()[$columnName] ?? null;
        if ($semanticType === Dimension::TYPE_PERCENT || $semanticType === Dimension::TYPE_FLOAT) {
            return self::MONOTONICITY_FREE;
        }

        // Name-pattern fallback for metrics whose semantic type is the ambiguous TYPE_NUMBER
        // but whose name reveals ratio shape (e.g. nb_actions_per_visit is TYPE_NUMBER yet
        // genuinely non-monotonic). The avg_ prefix also disambiguates TYPE_DURATION_*/TYPE_BYTE
        // averages from their additive sum_ siblings.
        if ($this->hasRatioShapedColumnName($columnName)) {
            return self::MONOTONICITY_FREE;
        }

        // min_* metrics carry a structural invariant: more samples within the period can only
        // pull the running min down or leave it unchanged. The default monotonic-up gate would
        // render upward-projecting forecasts on a metric that cannot rise, so flip to a
        // monotonic-down gate instead.
        if (strpos($columnName, 'min_') === 0) {
            return self::MONOTONICITY_DOWN;
        }

        // Default unknown metrics to monotonic-up count behaviour. The "forecast >= current"
        // gate then suppresses obviously-wrong forecasts on metrics whose semantics we cannot
        // classify, which is safer than emitting a downward forecast on a metric that turns
        // out to be additive (visits, conversions, revenue, …).
        return self::MONOTONICITY_UP;
    }

    /**
     * True when a column name carries one of the ratio/average/rate name patterns Matomo uses
     * for non-monotonic metrics. Shared between the monotonicity classifier and the forecast
     * precision picker so the two cannot drift on the same set of name fragments.
     */
    private function hasRatioShapedColumnName(string $columnName): bool
    {
        return strpos($columnName, '_rate') !== false
            || strpos($columnName, '_percentage') !== false
            || strpos($columnName, 'avg_') === 0
            || strpos($columnName, '_per_') !== false;
    }

    /**
     * Derive conservative raw forecast payload precision for a metric.
     *
     * Integer/count-like metrics should not emit fractional forecast values. Ratios, averages,
     * durations, money, bytes, floats, and unknown numeric metrics keep up to two decimals.
     *
     * Both MONOTONICITY_UP and MONOTONICITY_DOWN are treated as "monotonic" for precision —
     * a min_* count metric should round to integers the same way an additive nb_* count does.
     * Only MONOTONICITY_FREE (ratios/averages/percentages) keeps the two-decimal default for
     * TYPE_NUMBER metrics, which is the original allowsDownward = true behaviour.
     *
     * @param string|false $columnUnit
     * @param self::MONOTONICITY_* $monotonicity
     */
    private function getForecastPrecisionForColumn(string $columnName, $columnUnit, string $monotonicity): int
    {
        if ($columnUnit !== false) {
            return 2;
        }

        $semanticType = $this->getMetricSemanticTypes()[$columnName] ?? null;

        if (
            in_array($semanticType, [
                Dimension::TYPE_BYTE,
                Dimension::TYPE_DURATION_MS,
                Dimension::TYPE_DURATION_S,
                Dimension::TYPE_FLOAT,
                Dimension::TYPE_MONEY,
                Dimension::TYPE_PERCENT,
            ], true)
        ) {
            return 2;
        }

        // Word-boundary check: an underscore-delimited "time"/"length" segment in the column
        // name signals a duration- or length-shaped metric (sum_time_spent, time_per_action,
        // nb_visit_length, length_score). Anchored substring matches avoid false positives on
        // unrelated names that happen to contain the literal letters (lifetime_*, wavelength).
        if (
            $this->hasRatioShapedColumnName($columnName)
            || strpos($columnName, '_time') !== false
            || strpos($columnName, 'time_') === 0
            || strpos($columnName, '_length') !== false
            || strpos($columnName, 'length_') === 0
        ) {
            return 2;
        }

        if ($semanticType === Dimension::TYPE_NUMBER && $monotonicity !== self::MONOTONICITY_FREE) {
            return 0;
        }

        if (
            strpos($columnName, 'nb_') === 0
            || strpos($columnName, '_nb_') !== false
            || strpos($columnName, '_count') !== false
            || in_array($columnName, ['hits', 'items', 'quantity', 'orders', 'goals'], true)
        ) {
            return 0;
        }

        return 2;
    }

    private function getSeriesData($rowLabel, $columnName, DataTable\Map $dataTable, &$seriesDataAvailability)
    {
        $seriesData = array();
        $seriesDataAvailability = array();
        foreach ($dataTable->getDataTables() as $childTable) {
            // get the row for this label (use the first if $rowLabel is false)
            if ($rowLabel === false) {
                $row = $childTable->getFirstRow();
            } else {
                $row = $childTable->getRowFromLabel($rowLabel);
            }

            // get series data point. defaults to 0 if no row or no column value.
            if ($row === false) {
                $seriesData[] = 0;
                $seriesDataAvailability[] = false;
            } else {
                $value = $row->getColumn($columnName);
                // Preserve the legacy `?: 0` coercion for the rendered series data so '0' /
                // 0.0 values continue to flow through as plain int 0 (downstream consumers
                // doing `=== 0` rely on it). The hasColumnValue check below tracks the
                // separate "real 0 vs missing" distinction the forecast builder needs.
                $seriesData[] = $value ?: 0;
                $seriesDataAvailability[] = $this->hasColumnValue($value);
            }
        }
        return $seriesData;
    }

    /**
     * Single source of truth for whether a column value should count as "this tick has data".
     * Numeric 0 (and "0") counts as data; only false, null, and '' are treated as missing.
     */
    private function hasColumnValue($value): bool
    {
        return $value !== false && $value !== null && $value !== '';
    }

    /**
     * Derive the series label from the row label and the column name.
     * If the row label is set, both the label and the column name are displayed.
     * @param string $rowLabel
     * @param string $columnName
     * @return string
     */
    private function getSeriesLabel($rowLabel, $columnName)
    {
        $metricLabel = @$this->properties['translations'][$columnName];

        if ($rowLabel !== false) {
            // eg. "Yahoo! (Visits)"
            $label = "$rowLabel ($metricLabel)";
        } else {
            // eg. "Visits"
            $label = $metricLabel;
        }

        return $label;
    }

    private function isLinkEnabled()
    {
        static $linkEnabled;
        if (!isset($linkEnabled)) {
            // 1) Custom Date Range always have link disabled, otherwise
            // the graph data set is way too big and fails to display
            // 2) disableLink parameter is set in the Widgetize "embed" code
            $linkEnabled = !Common::getRequestVar('disableLink', 0, 'int')
                && Common::getRequestVar('period', 'day') != 'range';
        }
        return $linkEnabled;
    }

    /**
     * Each period comparison shows data over different data points than the main series (eg, 2014-02-03,1014-02-06 compared w/ 2015-03-04,2015-03-15).
     * Though we only display the selected period's x labels, we need to both have the labels for all these data points for tooltips and to stretch
     * out the selected period x axis, in case it is shorter than one of the compared periods (as in the example above).
     */
    private function addComparisonXLabels(array &$xLabels, DataTable $table)
    {
        $comparePeriods = $table->getMetadata('comparePeriods') ?: [];
        $compareDates = $table->getMetadata('compareDates') ?: [];

        // get rid of selected period
        array_shift($comparePeriods);
        array_shift($compareDates);

        foreach (array_values($comparePeriods) as $index => $period) {
            $date = $compareDates[$index];

            $range = Factory::build($period, $date);
            foreach ($range->getSubperiods() as $subperiod) {
                $xLabels[$index + 1][] = $subperiod;
            }
        }
    }

    /**
     * @param array $xLabels
     * @param DataTable[] $dataTables
     * @throws \Exception
     */
    protected function addSelectedSeriesXLabels(array &$xLabels, array $dataTables)
    {
        $xTicksCount = count($dataTables);
        foreach ($xLabels as $labelSeries) {
            $xTicksCount = max(count($labelSeries), $xTicksCount);
        }

        /** @var Date $startDate */
        $startDate = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getDateStart();
        $periodType = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX)->getLabel();

        for ($i = 0; $i < $xTicksCount; ++$i) {
            $period = Factory::build($periodType, $startDate->addPeriod($i, $periodType));
            $xLabels[0][] = $period;
        }
    }

    /**
     * Run the row × column collection loop and produce the per-series state. Shared by
     * initChartObjectData() (render path) and precomputeForecast() (toggle-visibility path)
     * so the two paths produce identical state. Comparing graphs go through
     * {@see self::collectComparisonSeriesData()} instead — they only need $allSeriesData and
     * never consume the forecast-state fields.
     *
     * @param array<int, mixed> $rowsToDisplay
     * @param array<int, string> $columnsToDisplay
     * @param array<string, string|false> $units
     */
    private function collectForecastSeriesState(
        array $rowsToDisplay,
        array $columnsToDisplay,
        array $units,
        DataTable\Map $dataTable
    ): ForecastSeriesState {
        // The render path always needs $allSeriesData to seed the chart's y-axis values,
        // but the forecast precision/downward-forecast classifiers are forecast-only signals
        // that touch the metric semantic-type registry and run a cluster of string searches per
        // column. Skip them on the show_forecast=0 hot path so dashboards full of evolution
        // graphs do not pay for a feature they are not rendering. precomputeForecast() always
        // sets show_forecast=1, so the toggle-visibility path keeps the full classifier work.
        $forecastEnabled = !empty($this->properties['show_forecast']);

        $allSeriesData = [];
        $allSeriesDataAvailability = [];
        $allSeriesMonotonicity = [];
        $allSeriesForecastPrecision = [];
        $allSeriesColumns = [];
        $allSeriesRows = [];

        foreach ($rowsToDisplay as $rowIdentifier) {
            $rowLabel = $this->resolveRowLabel($rowIdentifier);

            foreach ($columnsToDisplay as $columnName) {
                $columnUnit = $units[$columnName] ?? false;
                $columnMonotonicity = $forecastEnabled
                    ? $this->getColumnMonotonicity($columnName, $columnUnit)
                    : Evolution::MONOTONICITY_UP;

                $this->setNonComparisonSeriesData(
                    $allSeriesData,
                    $allSeriesDataAvailability,
                    $allSeriesMonotonicity,
                    $allSeriesForecastPrecision,
                    $allSeriesColumns,
                    $allSeriesRows,
                    $rowLabel,
                    $columnName,
                    $columnMonotonicity,
                    $columnUnit,
                    $dataTable,
                    $forecastEnabled
                );
            }
        }

        return new ForecastSeriesState(
            $allSeriesData,
            $allSeriesDataAvailability,
            $allSeriesMonotonicity,
            $allSeriesForecastPrecision,
            $allSeriesColumns,
            $allSeriesRows
        );
    }

    /**
     * Comparing-only twin of {@see self::collectForecastSeriesState()}: walks the same
     * row × column grid but only populates the chart-rendering data, since comparing graphs
     * never reach the forecast builder.
     *
     * @param array<int, mixed> $rowsToDisplay
     * @param array<int, string> $columnsToDisplay
     * @param array<int, string> $seriesLabels
     * @return array<string, array<int, float|int>>
     */
    private function collectComparisonSeriesData(
        array $rowsToDisplay,
        array $columnsToDisplay,
        array $seriesLabels,
        DataTable\Map $dataTable
    ): array {
        $allSeriesData = [];

        foreach ($rowsToDisplay as $rowIdentifier) {
            $rowLabel = $this->resolveRowLabel($rowIdentifier);

            foreach ($columnsToDisplay as $columnName) {
                $this->setComparisonSeriesData(
                    $allSeriesData,
                    $seriesLabels,
                    $rowLabel,
                    $columnName,
                    $dataTable
                );
            }
        }

        return $allSeriesData;
    }

    /**
     * Apply the `selectable_rows` matcher → label translation that both collection paths
     * share, keeping the loop body identical between comparing and non-comparing.
     *
     * @param mixed $rowIdentifier
     * @return mixed
     */
    private function resolveRowLabel($rowIdentifier)
    {
        if (!empty($this->properties['selectable_rows'])) {
            foreach ($this->properties['selectable_rows'] as $row) {
                if ($rowIdentifier === $row['matcher']) {
                    return $row['label'];
                }
            }
        }
        return $rowIdentifier;
    }

    /**
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, string> $allSeriesMonotonicity
     * @param array<string, int> $allSeriesForecastPrecision
     * @param array<string, string> $allSeriesColumns
     * @param array<string, mixed> $allSeriesRows Per-series row label/matcher (the same value
     *        the displayed-series path passes to {@see DataTable::getRowFromLabel()}). `false`
     *        when the series uses {@see DataTable::getFirstRow()} (single-row reports).
     * @param mixed $rowLabel
     */
    private function setNonComparisonSeriesData(
        array &$allSeriesData,
        array &$allSeriesDataAvailability,
        array &$allSeriesMonotonicity,
        array &$allSeriesForecastPrecision,
        array &$allSeriesColumns,
        array &$allSeriesRows,
        $rowLabel,
        $columnName,
        string $columnMonotonicity,
        $columnUnit,
        DataTable\Map $dataTable,
        bool $forecastEnabled
    ): void {
        $seriesLabel = $this->getSeriesLabel($rowLabel, $columnName);

        $seriesData = $this->getSeriesData($rowLabel, $columnName, $dataTable, $seriesDataAvailability);
        $allSeriesData[$seriesLabel] = $seriesData;

        if (!$forecastEnabled) {
            return;
        }

        $allSeriesDataAvailability[$seriesLabel] = $seriesDataAvailability;
        $allSeriesMonotonicity[$seriesLabel] = $columnMonotonicity;
        $allSeriesForecastPrecision[$seriesLabel] = $this->getForecastPrecisionForColumn(
            $columnName,
            $columnUnit,
            $columnMonotonicity
        );
        $allSeriesColumns[$seriesLabel] = $columnName;
        $allSeriesRows[$seriesLabel] = $rowLabel;
    }

    /**
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<int, string> $seriesLabels
     */
    private function setComparisonSeriesData(
        array &$allSeriesData,
        array $seriesLabels,
        $rowLabel,
        $columnName,
        DataTable\Map $dataTable
    ): void {
        foreach ($dataTable->getDataTables() as $label => $childTable) {
            // get the row for this label (use the first if $rowLabel is false)
            if ($rowLabel === false) {
                $row = $childTable->getFirstRow();
            } else {
                $row = $childTable->getRowFromLabel($rowLabel);
            }

            if (
                empty($row)
                || empty($row->getComparisons())
            ) {
                foreach ($seriesLabels as $seriesIndex => $seriesLabelPrefix) {
                    $wholeSeriesLabel = $this->getComparisonSeriesLabelFromCompareSeries($seriesLabelPrefix, $columnName, $rowLabel);
                    $allSeriesData[$wholeSeriesLabel][] = 0;
                }

                continue;
            }

            /** @var DataTable $comparisonTable */
            $comparisonTable = $row->getComparisons();
            foreach ($comparisonTable->getRows() as $compareRow) {
                $seriesLabel = $this->getComparisonSeriesLabel($compareRow, $columnName, $rowLabel);
                $allSeriesData[$seriesLabel][] = $compareRow->getColumn($columnName);
            }

            $totalsRow = $comparisonTable->getTotalsRow();
            if ($totalsRow) {
                $seriesLabel = $this->getComparisonSeriesLabel($totalsRow, $columnName, $rowLabel);
                $allSeriesData[$seriesLabel][] = $totalsRow->getColumn($columnName);
            }
        }
    }

    private function getSeriesMetadata(array $rowsToDisplay, array $columnsToDisplay, array $units, array $dataTables)
    {
        $seriesMetadata = null; // maps series labels to any metadata of the series
        $seriesUnits = array(); // maps series labels to unit labels
        $seriesToXAxis = []; // maps series index to x-axis index (groups of metrics for a single comparison will use the same x-axis)

        $table = reset($dataTables);
        $seriesLabels = $table->getMetadata('comparisonSeries') ?: [];
        foreach ($rowsToDisplay as $rowIndex => $rowLabel) {
            foreach ($columnsToDisplay as $columnIndex => $columnName) {
                if ($this->isComparing) {
                    foreach ($seriesLabels as $seriesIndex => $seriesLabel) {
                        $wholeSeriesLabel = $this->getComparisonSeriesLabelFromCompareSeries($seriesLabel, $columnName, $rowLabel);

                        $allSeriesData[$wholeSeriesLabel] = [];

                        $metricIndex = $rowIndex * count($columnsToDisplay) + $columnIndex;
                        $seriesMetadata[$wholeSeriesLabel] = [
                            'metricIndex' => $metricIndex,
                            'seriesIndex' => $seriesIndex,
                        ];

                        $seriesUnits[$wholeSeriesLabel] = $units[$columnName];

                        [$periodIndex, $segmentIndex] = DataComparisonFilter::getIndividualComparisonRowIndices($table, $seriesIndex);
                        $seriesToXAxis[] = $periodIndex;
                    }
                } else {
                    $seriesLabel = $this->getSeriesLabel($rowLabel, $columnName);
                    $seriesUnits[$seriesLabel] = $units[$columnName];
                }
            }
        }

        return [$seriesMetadata, $seriesUnits, $seriesLabels, $seriesToXAxis];
    }

    /**
     * @param array<DataTable> $dataTables
     */
    private function setDataStates(Chart $visualization, array $dataTables): array
    {
        $dataStates = $this->computeDataStates($dataTables);
        $visualization->setDataStates($dataStates);

        return $dataStates;
    }

    /**
     * Pure data-state computation. Returns the per-tick archive state for the given
     * per-period DataTables, ordered by the original DataTable\Map keys.
     *
     * @param array<DataTable> $dataTables
     * @return array<int, string>
     */
    public function computeDataStates(array $dataTables): array
    {
        if (0 === count($dataTables)) {
            return [];
        }

        $dataTableDates = array_keys($dataTables);
        $mostRecentDate = end($dataTableDates);

        /** @var Site $site */
        $site = $dataTables[$mostRecentDate]->getMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX);

        $dataStates = [];
        $siteToday = Date::factoryInTimezone('today', $site->getTimezone())->getTimestamp();
        $previousState = ArchiveState::COMPLETE;

        foreach ($dataTableDates as $dataTableDate) {
            $childTable = $dataTables[$dataTableDate];
            $state = $childTable->getMetadata(DataTable::ARCHIVE_STATE_METADATA_NAME);

            if (false === $state) {
                // Missing archive state information should only occur if no
                // usable archive was found in the database. Treat a missing archive
                // (for example if there are legitimately zero visits to a site)
                // as complete unless it follows an incomplete archive.
                $state = ArchiveState::INCOMPLETE === $previousState
                    ? ArchiveState::INCOMPLETE
                    : ArchiveState::COMPLETE;
            }

            if (self::isIncompleteTick($childTable, $siteToday)) {
                $state = ArchiveState::INCOMPLETE;
            }

            $dataStates[$dataTableDate] = $state;
            $previousState = $state;
        }

        return array_values($dataStates);
    }

    /**
     * Decides whether a single child table from an evolution Map represents an
     * incomplete tick. Two signals can mark a tick incomplete: an explicit
     * INCOMPLETE archive_state metadata flag (set by the archiver when ts_archived
     * falls before the period end), or the tick's period running on/past the
     * site's "today". The siteToday rule exists because the archiver may not
     * write numeric records for periods with no data (low-volume Goals/Ecommerce
     * reports hit this), in which case the metadata is absent even though the
     * period is, by definition, still in progress.
     *
     * Called from {@see computeDataStates()} as the override that forces the
     * per-tick state to INCOMPLETE, and from
     * {@see \Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution::hasAnyIncompleteTick()}
     * to gate the forecast toggle visibility on whether any tick is incomplete.
     * Both consumers must agree on the rule, so the comparison lives in one place.
     */
    public static function isIncompleteTick(DataTable $childTable, int $siteToday): bool
    {
        if (ArchiveState::INCOMPLETE === $childTable->getMetadata(DataTable::ARCHIVE_STATE_METADATA_NAME)) {
            return true;
        }

        $period = $childTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);

        return $period instanceof Period && $siteToday <= $period->getDateEnd()->getTimestamp();
    }

    /**
     * Compute forecast values for the given DataTable\Map without rendering a chart.
     * Used by the visualization in afterAllFiltersAreApplied() to gate the forecast
     * toggle action on whether the algorithm actually yields any renderable values.
     *
     * The collected per-series state is stashed on the visualization so the later
     * initChartObjectData() call can reuse it instead of running the same loop again.
     *
     * @return array<int, array<int, float|null>>
     */
    public function precomputeForecast(DataTable\Map $dataTable): array
    {
        if ($this->isComparing) {
            return [];
        }

        $dataTables = $dataTable->getDataTables();
        if ([] === $dataTables) {
            return [];
        }

        // Cheap gate: without at least one incomplete tick the builder cannot
        // produce a forecast value, so skip the per-series construction below.
        // This runs on every evolution graph render to size the toggle action,
        // so the early exit matters for dashboards full of historical-only graphs.
        $dataStates = $this->computeDataStates($dataTables);
        if (!in_array(ArchiveState::INCOMPLETE, $dataStates, true)) {
            return [];
        }

        $units = $this->getUnitsForColumnsToDisplay();

        $rowsToDisplay = $this->properties['rows_to_display']
            ?: array_unique($dataTable->getColumn('label'))
                ?: [false];

        $columnsToDisplay = array_values($this->properties['columns_to_display']);

        [, $seriesUnits] = $this->getSeriesMetadata($rowsToDisplay, $columnsToDisplay, $units, $dataTables);

        $seriesState = $this->collectForecastSeriesState($rowsToDisplay, $columnsToDisplay, $units, $dataTable);

        $this->graph->setForecastSeriesState($seriesState);

        return $this->buildForecastData($seriesState, $dataTables, $dataStates, $seriesUnits);
    }

    /**
     * Fetch sub-period samples needed by ForecastBuilder's seasonal-decomposition path.
     *
     * For week/month targets returns a daily sample map per series, covering the displayed
     * date range plus enough historical days to populate the same-DoW analog slots. For year
     * targets returns a monthly sample map covering enough years for same-MoY analogs.
     *
     * The fetch issues an additional inner API request with the same module/method/idSite/segment
     * as the displayed data but at a finer period granularity. Failure (unsupported API method,
     * archive errors, missing parameters) returns empty maps so the builder falls back to the
     * prior-only same-period projection on the period-level series.
     *
     * @param array<DataTable> $dataTables
     * @param array<string, string> $seriesColumns Map of series label → raw archive column name.
     *        The map keys are how ForecastBuilder consumes the returned sample maps; the values
     *        are how the sub-period API result rows are keyed once ReplaceColumnNames has run.
     * @param array<string, mixed> $seriesRows Map of series label → row label/matcher (the
     *        value the displayed-series path passes to {@see DataTable::getRowFromLabel()}).
     *        `false` selects the sub-table's first row, matching the single-row default.
     *        Threaded through to {@see self::extractSubPeriodSamples()} so multi-row evolution
     *        graphs (selectable_rows) pull each series' historical samples from its own row.
     * @param array<string, string> $seriesMonotonicity Map of series label → MONOTONICITY_* tag.
     *        Forwarded to {@see self::extractSubPeriodSamples()} so the rowless-sub-table
     *        backfill stays UP-only and synthetic zeros do not pollute DOWN/FREE analog walks.
     * @return array{daily: array<string, array<string, float>>, monthly: array<string, array<string, float>>}
     */
    private function collectSubPeriodSamples(
        array $dataTables,
        array $seriesColumns,
        array $seriesRows,
        array $seriesMonotonicity
    ): array {
        $empty = ['daily' => [], 'monthly' => []];

        if (empty($dataTables) || empty($seriesColumns)) {
            return $empty;
        }

        $firstTable = reset($dataTables);
        $period = $firstTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        if (!$period instanceof Period) {
            return $empty;
        }
        $periodLabel = $period->getLabel();

        $apiMethod = $this->graph->requestConfig->apiMethodToRequestDataTable;
        if (empty($apiMethod) || strpos($apiMethod, '.') === false) {
            return $empty;
        }

        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if ($idSite <= 0) {
            return $empty;
        }
        // getRawSegmentFromRequest() returns false when no segment is set; coerce at the boundary
        // since fetchSubPeriodSeries() takes a non-nullable string parameter.
        $segment = (string) ApiRequest::getRawSegmentFromRequest();

        $lastTable = end($dataTables);
        $lastPeriod = $lastTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        if (!$lastPeriod instanceof Period) {
            return $empty;
        }
        $endDate = $lastPeriod->getDateEnd()->subDay(1)->toString('Y-m-d');

        try {
            if ('day' === $periodLabel) {
                // Day-period forecasts cannot decompose into sub-periods, so the prior-only
                // path is the only forecast surface. A 4-7 day display carries at most one
                // same-DoW history tick in $seriesData; pull a 70-day window so the same-DoW
                // walk in ForecastBuilder collects ~10 analogs and the trend fit + envelope
                // clamp engage on short displays.
                $startDate = (Date::factory($endDate))->subDay(70)->toString('Y-m-d');
                return [
                    'daily' => $this->fetchSubPeriodSeries($apiMethod, $idSite, $segment, 'day', $startDate, $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                    'monthly' => [],
                ];
            }
            if ('week' === $periodLabel || 'month' === $periodLabel) {
                // Pull enough days to populate same-DoW analog slots for the largest analog
                // chunk the builder might consume. ForecastBuilder uses chunk = 3 (week) or
                // 4 (month); the worst case is the first projected day of a 31-day month
                // pulling its chunk-th analog (31 + 4 × 7 = 59 days). 70 days gives headroom
                // for any future chunk increase up to 5.
                $startDate = (Date::factory($endDate))->subDay(70)->toString('Y-m-d');
                return [
                    'daily' => $this->fetchSubPeriodSeries($apiMethod, $idSite, $segment, 'day', $startDate, $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                    'monthly' => 'month' === $periodLabel
                        ? $this->fetchSubPeriodSeries($apiMethod, $idSite, $segment, 'month', $this->yearsBack($endDate, 4), $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity)
                        : [],
                ];
            }
            if ('year' === $periodLabel) {
                return [
                    'daily' => $this->fetchSubPeriodSeries($apiMethod, $idSite, $segment, 'day', (Date::factory($endDate))->subDay(70)->toString('Y-m-d'), $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                    'monthly' => $this->fetchSubPeriodSeries($apiMethod, $idSite, $segment, 'month', $this->yearsBack($endDate, 9), $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                ];
            }
        } catch (\Throwable $e) {
            // Defensive: any error in the parallel fetch falls back to the prior-only path.
            // The seasonal-decomposition advantage is lost on this render, but the forecast
            // still renders something defensible from the displayed series alone. Log so a
            // sustained dip in forecast quality is investigable instead of silent.
            StaticContainer::get(LoggerInterface::class)->info(
                'Evolution forecast sub-period fetch failed for {apiMethod} (idSite={idSite}, period={period}): {message}',
                [
                    'apiMethod' => $apiMethod,
                    'idSite' => $idSite,
                    'period' => $periodLabel,
                    'message' => $e->getMessage(),
                    'exception' => $e,
                ]
            );
        }

        return $empty;
    }

    /**
     * Issue a single sub-period API request and shape the result into a series-keyed map of
     * date → value. Date keys are 'Y-m-d' for day targets and 'Y-m' for month targets, matching
     * what ForecastBuilder's analog walks expect. The returned map keys by series label so the
     * builder's per-series lookup ($allSeriesDailySamples[$seriesLabel]) hits a populated entry,
     * while the API row lookup uses the raw archive column name (after ReplaceColumnNames).
     *
     * @param array<string, string> $seriesColumns Map of series label → raw archive column name.
     * @param array<string, mixed> $seriesRows Map of series label → row label/matcher,
     *        threaded through to {@see self::extractSubPeriodSamples()} so the per-series row
     *        lookup hits the same row the displayed series is reading.
     * @param array<string, string> $seriesMonotonicity Map of series label → MONOTONICITY_* tag,
     *        threaded through to {@see self::extractSubPeriodSamples()} so the rowless-table
     *        backfill can be gated on direction.
     * @return array<string, array<string, float>>
     */
    private function fetchSubPeriodSeries(
        string $apiMethod,
        int $idSite,
        string $segment,
        string $subPeriod,
        string $startDate,
        string $endDate,
        array $seriesColumns,
        array $seriesRows,
        array $seriesMonotonicity
    ): array {
        // Use processRequest() rather than `new ApiRequest([...])` so the inner fetch picks up
        // its `compare=0` / `format=original` / `serialize=0` defaults and stays aligned with the
        // recent core convention for inner API calls. Inheritance from $_GET + $_POST is
        // intentional: this code path runs inside an already-authorized evolution-graph render,
        // so outer-URL scoping params (idGoal for Goals reports, idDimension for CustomDimensions,
        // and similar plugin-specific selectors) must reach the inner request for the historical
        // samples to come from the same series the user is looking at. The `isComparing` guard
        // upstream prevents comparison-mode leakage; the explicit overrides below pin everything
        // else the forecast needs in fixed form.
        $result = ApiRequest::processRequest($apiMethod, [
            'idSite'                  => $idSite,
            'period'                  => $subPeriod,
            'date'                    => $startDate . ',' . $endDate,
            'segment'                 => $segment,
            'filter_limit'            => -1,
            'disable_generic_filters' => 1,
        ]);
        if (!$result instanceof DataTable\Map) {
            return [];
        }

        return $this->extractSubPeriodSamples($result, $seriesColumns, $seriesRows, $seriesMonotonicity, $subPeriod);
    }

    /**
     * Shape a sub-period DataTable\Map into a series-keyed map of date → value. The result of
     * the inner API request has already been through ReplaceColumnNames so its row columns are
     * the raw archive column names; we look up by raw name and store under the series label so
     * ForecastBuilder's per-series lookup hits the right entry.
     *
     * Each series carries its own row matcher in $seriesRows. Multi-row evolution graphs
     * (selectable_rows on a non-summary report) plot one series per selected row, so the
     * historical sample for each series must come from that series' own row in the sub-period
     * archive -- not from {@see DataTable::getFirstRow()}, which would silently pin every
     * series to whichever row sorts first in that sub-table (typically the top-ranked row).
     * `false` matchers fall through to getFirstRow() to keep the single-row default behaviour.
     *
     * @param array<string, string> $seriesColumns Map of series label → raw archive column name.
     * @param array<string, mixed> $seriesRows Map of series label → row label/matcher (the same
     *        value the displayed-series path passes to {@see DataTable::getRowFromLabel()}).
     *        `false` selects the sub-table's first row.
     * @param array<string, string> $seriesMonotonicity Map of series label → MONOTONICITY_* tag.
     *        Missing entries fall back to MONOTONICITY_UP so the legacy backfill behaviour
     *        survives for callers that have not propagated the classifier output yet.
     * @return array<string, array<string, float>>
     */
    private function extractSubPeriodSamples(
        DataTable\Map $result,
        array $seriesColumns,
        array $seriesRows,
        array $seriesMonotonicity,
        string $subPeriod
    ): array {
        $samples = [];
        $rowKey = $subPeriod === 'month' ? 'Y-m' : 'Y-m-d';

        foreach ($result->getDataTables() as $subTable) {
            if (!$subTable instanceof DataTable) {
                continue;
            }
            $tablePeriod = $subTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
            if (!$tablePeriod instanceof Period) {
                continue;
            }
            $dateKey = $tablePeriod->getDateStart()->toString($rowKey);

            foreach ($seriesColumns as $seriesLabel => $columnName) {
                $rowMatcher = $seriesRows[$seriesLabel] ?? false;
                $row = (false === $rowMatcher)
                    ? $subTable->getFirstRow()
                    : $subTable->getRowFromLabel($rowMatcher);

                if (empty($row)) {
                    // No matching row on this date. Only MONOTONICITY_UP count series can
                    // defensibly read that as a real 0 (no observation = zero count), so they
                    // get the backfill to keep the analog calendar dense. MONOTONICITY_DOWN
                    // (running mins) and MONOTONICITY_FREE (rates/averages) have no
                    // "no observation → 0" mapping: a min of nothing is not 0, and a 0% rate
                    // inferred from no traffic is not a real ratio observation. Leaving the
                    // date absent lets recentSameDoWValues() skip it instead of treating a
                    // synthetic zero as a same-DoW analog, which would pull the prior below
                    // current and trip shouldRenderForecastValue() into silent suppression.
                    // The column-missing-on-existing-row branch below is deliberately
                    // different: a row that exists but lacks the requested column means the
                    // metric isn't reported here, which is not the same as zero -- and that
                    // branch already skips for every monotonicity.
                    $monotonicity = $seriesMonotonicity[$seriesLabel] ?? Evolution::MONOTONICITY_UP;
                    if (Evolution::MONOTONICITY_UP === $monotonicity) {
                        $samples[$seriesLabel][$dateKey] = 0.0;
                    }
                    continue;
                }

                $value = $row->getColumn($columnName);
                if ($value === false || $value === null) {
                    continue;
                }
                $samples[$seriesLabel][$dateKey] = (float) $value;
            }
        }

        return $samples;
    }

    private function yearsBack(string $endDate, int $years): string
    {
        return Date::factory($endDate)->subYear($years)->toString('Y-m-d');
    }
}
