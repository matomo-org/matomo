<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Date;
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

        // Reuse the per-series state precomputed in JqplotGraph\Evolution::afterAllFiltersAreApplied()
        // when forecast is on, instead of running the same row × column collection loop again. Only
        // populated for the non-comparing path; the comparing branch still computes from scratch.
        $precomputedSeriesState = (!$this->isComparing && $this->graph instanceof JqplotEvolutionGraph)
            ? $this->graph->getForecastSeriesState()
            : null;

        if ($precomputedSeriesState !== null) {
            $allSeriesData = $precomputedSeriesState['allSeriesData'];
            $allSeriesDataAvailability = $precomputedSeriesState['allSeriesDataAvailability'];
            $allSeriesAllowsDownwardForecast = $precomputedSeriesState['allSeriesAllowsDownwardForecast'];
            $allSeriesForecastPrecision = $precomputedSeriesState['allSeriesForecastPrecision'];
        } else {
            // The render path always needs $allSeriesData to seed the chart's y-axis values, but
            // the forecast precision/downward-forecast classifiers are forecast-only signals that
            // touch the metric semantic-type registry and run a cluster of string searches per
            // column. Skip them on the show_forecast=0 hot path so dashboards full of evolution
            // graphs do not pay for a feature they are not rendering. precomputeForecast() always
            // sets show_forecast=1, so the toggle-visibility path keeps the full classifier work.
            // Comparing graphs are also treated as the forecast-off path: buildForecastData() and
            // precomputeForecast() both return [] when isComparing, so the classifier output would
            // be discarded anyway.
            $forecastEnabled = !empty($this->properties['show_forecast']) && !$this->isComparing;

            // collect series data to show. each row-to-display/column-to-display permutation creates a series.
            $allSeriesData = [];
            $allSeriesDataAvailability = [];
            $allSeriesAllowsDownwardForecast = [];
            $allSeriesForecastPrecision = [];
            foreach ($rowsToDisplay as $rowIdentifier) {
                $rowLabel = $rowIdentifier;

                if (!empty($this->properties['selectable_rows'])) {
                    foreach ($this->properties['selectable_rows'] as $row) {
                        if ($rowIdentifier === $row['matcher']) {
                            $rowLabel = $row['label'];
                        }
                    }
                }

                foreach ($columnsToDisplay as $columnName) {
                    if (!$this->isComparing) {
                        // The comparing branch never renders a forecast (buildForecastData()
                        // short-circuits when comparing), so the classifier output would be
                        // discarded. Skip the lookup for the only path that uses it.
                        $columnAllowsDownwardForecast = $forecastEnabled
                            ? $this->columnAllowsDownwardForecast($columnName, $units[$columnName] ?? false)
                            : false;

                        $this->setNonComparisonSeriesData(
                            $allSeriesData,
                            $allSeriesDataAvailability,
                            $allSeriesAllowsDownwardForecast,
                            $allSeriesForecastPrecision,
                            $rowLabel,
                            $columnName,
                            $columnAllowsDownwardForecast,
                            $units[$columnName] ?? false,
                            $dataTable,
                            $forecastEnabled
                        );
                    } else {
                        $this->setComparisonSeriesData($allSeriesData, $seriesLabels, $rowLabel, $columnName, $dataTable);
                    }
                }
            }
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
            $allSeriesData,
            $dataTables,
            $dataStates,
            $seriesUnits,
            $allSeriesDataAvailability,
            $allSeriesAllowsDownwardForecast,
            $allSeriesForecastPrecision
        ));
    }

    /**
     * Compute forecast values via {@see ForecastBuilder} when forecast is enabled
     * and the graph is not in compare mode. Both gates short-circuit to an empty
     * payload — the builder would yield [] anyway, but checking here avoids
     * unnecessary work on the cold path.
     *
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, bool> $allSeriesAllowsDownwardForecast
     * @param array<string, int> $allSeriesForecastPrecision
     * @return array<int, array<int, float|null>>
     */
    protected function buildForecastData(
        array $allSeriesData,
        array $dataTables,
        array $dataStates,
        array $seriesUnits,
        array $allSeriesDataAvailability = [],
        array $allSeriesAllowsDownwardForecast = [],
        array $allSeriesForecastPrecision = []
    ): array {
        if (empty($this->properties['show_forecast']) || $this->isComparing) {
            return [];
        }

        return (new ForecastBuilder())->build(
            $allSeriesData,
            $dataTables,
            $dataStates,
            $seriesUnits,
            $allSeriesDataAvailability,
            $allSeriesAllowsDownwardForecast,
            $allSeriesForecastPrecision
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
     * Whether forecasts for a given column may legitimately fall below the current partial value.
     *
     * Counts and additive totals only grow within an incomplete period, so their forecast is
     * gated by the "forecast >= current" rule. Ratios, rates, percentages, and averages can move
     * either way during the period and need the gate lifted, otherwise valid downward trends are
     * silently suppressed.
     *
     * @param string|false $columnUnit
     */
    private function columnAllowsDownwardForecast(string $columnName, $columnUnit): bool
    {
        if ($columnUnit === '%') {
            return true;
        }

        // TYPE_PERCENT and TYPE_FLOAT are non-monotonic by construction (a percentage's or a
        // ratio's value can move in either direction within a partial period). Plugins extend
        // the semantic-type map via the Metrics.getDefaultMetricSemanticTypes event, so a
        // custom metric declared as TYPE_PERCENT or TYPE_FLOAT classifies correctly without
        // needing a magic name.
        $semanticType = $this->getMetricSemanticTypes()[$columnName] ?? null;
        if ($semanticType === Dimension::TYPE_PERCENT || $semanticType === Dimension::TYPE_FLOAT) {
            return true;
        }

        // Name-pattern fallback for metrics whose semantic type is the ambiguous TYPE_NUMBER
        // but whose name reveals ratio shape (e.g. nb_actions_per_visit is TYPE_NUMBER yet
        // genuinely non-monotonic). The avg_ prefix also disambiguates TYPE_DURATION_*/TYPE_BYTE
        // averages from their additive sum_ siblings.
        if ($this->hasRatioShapedColumnName($columnName)) {
            return true;
        }

        // Default unknown metrics to monotonic count behaviour. The "forecast >= current" gate
        // then suppresses obviously-wrong forecasts on metrics whose semantics we cannot
        // classify, which is safer than emitting a downward forecast on a metric that turns
        // out to be additive (visits, conversions, revenue, …).
        return false;
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
     * @param string|false $columnUnit
     */
    private function getForecastPrecisionForColumn(string $columnName, $columnUnit, bool $allowsDownwardForecast): int
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

        if ($semanticType === Dimension::TYPE_NUMBER && !$allowsDownwardForecast) {
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

    private function setNonComparisonSeriesData(
        array &$allSeriesData,
        array &$allSeriesDataAvailability,
        array &$allSeriesAllowsDownwardForecast,
        array &$allSeriesForecastPrecision,
        $rowLabel,
        $columnName,
        bool $columnAllowsDownwardForecast,
        $columnUnit,
        DataTable\Map $dataTable,
        bool $forecastEnabled
    ) {
        $seriesLabel = $this->getSeriesLabel($rowLabel, $columnName);

        $seriesData = $this->getSeriesData($rowLabel, $columnName, $dataTable, $seriesDataAvailability);
        $allSeriesData[$seriesLabel] = $seriesData;

        if (!$forecastEnabled) {
            return;
        }

        $allSeriesDataAvailability[$seriesLabel] = $seriesDataAvailability;
        $allSeriesAllowsDownwardForecast[$seriesLabel] = $columnAllowsDownwardForecast;
        $allSeriesForecastPrecision[$seriesLabel] = $this->getForecastPrecisionForColumn(
            $columnName,
            $columnUnit,
            $columnAllowsDownwardForecast
        );
    }

    private function setComparisonSeriesData(array &$allSeriesData, array $seriesLabels, $rowLabel, $columnName, DataTable\Map $dataTable)
    {
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

        $allSeriesData = [];
        $allSeriesDataAvailability = [];
        $allSeriesAllowsDownwardForecast = [];
        $allSeriesForecastPrecision = [];
        foreach ($rowsToDisplay as $rowIdentifier) {
            $rowLabel = $rowIdentifier;

            if (!empty($this->properties['selectable_rows'])) {
                foreach ($this->properties['selectable_rows'] as $row) {
                    if ($rowIdentifier === $row['matcher']) {
                        $rowLabel = $row['label'];
                    }
                }
            }

            foreach ($columnsToDisplay as $columnName) {
                $columnAllowsDownwardForecast = $this->columnAllowsDownwardForecast(
                    $columnName,
                    $units[$columnName] ?? false
                );

                $this->setNonComparisonSeriesData(
                    $allSeriesData,
                    $allSeriesDataAvailability,
                    $allSeriesAllowsDownwardForecast,
                    $allSeriesForecastPrecision,
                    $rowLabel,
                    $columnName,
                    $columnAllowsDownwardForecast,
                    $units[$columnName] ?? false,
                    $dataTable,
                    true
                );
            }
        }

        if ($this->graph instanceof JqplotEvolutionGraph) {
            $this->graph->setForecastSeriesState([
                'allSeriesData' => $allSeriesData,
                'allSeriesDataAvailability' => $allSeriesDataAvailability,
                'allSeriesAllowsDownwardForecast' => $allSeriesAllowsDownwardForecast,
                'allSeriesForecastPrecision' => $allSeriesForecastPrecision,
            ]);
        }

        return $this->buildForecastData(
            $allSeriesData,
            $dataTables,
            $dataStates,
            $seriesUnits,
            $allSeriesDataAvailability,
            $allSeriesAllowsDownwardForecast,
            $allSeriesForecastPrecision
        );
    }
}
