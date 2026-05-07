<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\tests\Unit\JqplotDataGenerator;

use PHPUnit\Framework\TestCase;
use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Evolution;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution as JqplotEvolutionGraph;
use Piwik\ViewDataTable\RequestConfig;
use Piwik\Site;
use ReflectionMethod;

/**
 * @group CoreVisualizations
 * @group Evolution
 * @group JqplotDataGenerator
 */
class EvolutionTest extends TestCase
{
    public function testBuildForecastDataReturnsEmptyWhenForecastDisabled(): void
    {
        $evolution = $this->createEvolution(['show_forecast' => 0], false);

        self::assertSame([], $evolution->callBuildForecastData(new ForecastSeriesState([], [], [], []), [], [], []));
    }

    public function testBuildForecastDataReturnsEmptyWhenComparing(): void
    {
        $evolution = $this->createEvolution(['show_forecast' => 1], true);

        self::assertSame([], $evolution->callBuildForecastData(new ForecastSeriesState([], [], [], []), [], [], []));
    }

    /**
     * @dataProvider getColumnValueRuleTestData
     * @param mixed $value
     */
    public function testHasColumnValueRule($value, bool $expected): void
    {
        $evolution = $this->createEvolution([], false);

        $method = new ReflectionMethod(Evolution::class, 'hasColumnValue');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $value));
    }

    /**
     * @return iterable<string, array{mixed, bool}>
     */
    public function getColumnValueRuleTestData(): iterable
    {
        yield 'false is missing' => [false, false];
        yield 'null is missing' => [null, false];
        yield 'empty string is missing' => ['', false];
        yield 'numeric zero counts as data' => [0, true];
        yield 'float zero counts as data' => [0.0, true];
        yield 'string zero counts as data' => ['0', true];
        yield 'positive integer counts as data' => [42, true];
        yield 'positive float counts as data' => [12.5, true];
        yield 'negative number counts as data' => [-3, true];
        yield 'non-empty string counts as data' => ['1.5', true];
    }

    /**
     * @dataProvider getColumnMonotonicityTestData
     * @param string|false $columnUnit
     */
    public function testGetColumnMonotonicityFromName(string $columnName, $columnUnit, string $expected): void
    {
        $evolution = $this->createEvolution([], false);

        $method = new ReflectionMethod(Evolution::class, 'getColumnMonotonicity');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $columnName, $columnUnit));
    }

    /**
     * @return iterable<string, array{string, string|false, string}>
     */
    public function getColumnMonotonicityTestData(): iterable
    {
        yield 'percent unit' => ['custom_metric', '%', Evolution::MONOTONICITY_FREE];
        yield 'rate metric' => ['bounce_rate', false, Evolution::MONOTONICITY_FREE];
        yield 'percentage metric' => ['conversion_percentage', false, Evolution::MONOTONICITY_FREE];
        yield 'average metric' => ['avg_time_on_site', false, Evolution::MONOTONICITY_FREE];
        yield 'per metric containing nb prefix' => ['nb_actions_per_visit', false, Evolution::MONOTONICITY_FREE];
        yield 'plain nb metric' => ['nb_visits', false, Evolution::MONOTONICITY_UP];
        yield 'sum daily nb metric' => ['sum_daily_nb_users', false, Evolution::MONOTONICITY_UP];
        yield 'exit nb metric' => ['exit_nb_visits', false, Evolution::MONOTONICITY_UP];
        yield 'sum daily exit nb metric' => ['sum_daily_exit_nb_uniq_visitors', false, Evolution::MONOTONICITY_UP];
        yield 'lower is better count metric' => ['bounce_count', false, Evolution::MONOTONICITY_UP];
        yield 'unknown metric defaults to monotonic up' => ['custom_numeric_metric', false, Evolution::MONOTONICITY_UP];
        yield 'revenue currency metric stays monotonic up' => ['revenue', false, Evolution::MONOTONICITY_UP];
        yield 'duration sum metric stays monotonic up' => ['sum_time_spent', false, Evolution::MONOTONICITY_UP];
        yield 'min_ prefix is monotonic down' => ['min_bandwidth', false, Evolution::MONOTONICITY_DOWN];
        yield 'min_ prefix on event value is monotonic down' => ['min_event_value', false, Evolution::MONOTONICITY_DOWN];
        yield 'max_ prefix stays monotonic up by default' => ['max_actions', false, Evolution::MONOTONICITY_UP];
    }

    /**
     * @dataProvider getColumnMonotonicitySemanticTypeTestData
     */
    public function testGetColumnMonotonicityUsesSemanticTypeForCustomMetrics(
        string $columnName,
        ?string $stubSemanticType,
        string $expected
    ): void {
        $semanticTypes = $stubSemanticType !== null ? [$columnName => $stubSemanticType] : [];
        $evolution = $this->createEvolution([], false, $semanticTypes);

        $method = new ReflectionMethod(Evolution::class, 'getColumnMonotonicity');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $columnName, false));
    }

    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public function getColumnMonotonicitySemanticTypeTestData(): iterable
    {
        yield 'percent semantic type without name pattern' => ['engagement_score', Dimension::TYPE_PERCENT, Evolution::MONOTONICITY_FREE];
        yield 'float semantic type without name pattern' => ['session_quality', Dimension::TYPE_FLOAT, Evolution::MONOTONICITY_FREE];
        yield 'number semantic type without name pattern stays monotonic up' => ['custom_count', Dimension::TYPE_NUMBER, Evolution::MONOTONICITY_UP];
        yield 'money semantic type stays monotonic up' => ['custom_revenue', Dimension::TYPE_MONEY, Evolution::MONOTONICITY_UP];
        yield 'duration semantic type without avg_ prefix stays monotonic up' => ['custom_dwell', Dimension::TYPE_DURATION_S, Evolution::MONOTONICITY_UP];
        yield 'byte semantic type without avg_ prefix stays monotonic up' => ['custom_bandwidth', Dimension::TYPE_BYTE, Evolution::MONOTONICITY_UP];
        yield 'no semantic type defaults to monotonic up' => ['custom_metric_no_signal', null, Evolution::MONOTONICITY_UP];
        yield 'min_ prefix wins even with TYPE_NUMBER semantic' => ['min_custom', Dimension::TYPE_NUMBER, Evolution::MONOTONICITY_DOWN];
        yield 'min_ prefix yields to percent semantic type' => ['min_rate_custom', Dimension::TYPE_PERCENT, Evolution::MONOTONICITY_FREE];
    }

    public function testPrecomputeForecastReturnsEmptyWhenComparing(): void
    {
        $evolution = $this->createEvolution(
            ['show_forecast' => 1, 'columns_to_display' => ['nb_visits'], 'rows_to_display' => [false]],
            true
        );

        self::assertSame([], $evolution->precomputeForecast(new DataTable\Map()));
    }

    public function testPrecomputeForecastReturnsEmptyForEmptyMap(): void
    {
        $evolution = $this->createEvolution(
            ['show_forecast' => 1, 'columns_to_display' => ['nb_visits'], 'rows_to_display' => [false]],
            false
        );

        self::assertSame([], $evolution->precomputeForecast(new DataTable\Map()));
    }

    public function testPrecomputeForecastReturnsEmptyWhenNoIncompletePeriod(): void
    {
        $evolution = $this->createEvolution(
            ['show_forecast' => 1, 'columns_to_display' => ['nb_visits'], 'rows_to_display' => [false]],
            false
        );

        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable($this->createDataTableForDay('2026-04-10', $site), '2026-04-10');
        $map->addTable($this->createDataTableForDay('2026-04-11', $site), '2026-04-11');

        self::assertSame([], $evolution->precomputeForecast($map));
    }

    public function testBuildForecastDataDelegatesToBuilderWhenEnabledAndNotComparing(): void
    {
        $evolution = $this->createEvolution(['show_forecast' => 1], false);
        $site = $this->createSiteMock();

        // Two same-weekday priors so the prior-only path produces a renderable value. Apr 4
        // and Apr 11 are both Saturdays.
        $dataTables = [
            $this->createDataTableForDay('2026-04-04', $site),
            $this->createDataTableForDay('2026-04-11', $site, '2026-04-11 12:00:00'),
        ];

        $seriesState = new ForecastSeriesState(
            ['Visits' => [80.0, 20.0]],
            ['Visits' => [true, true]],
            ['Visits' => Evolution::MONOTONICITY_UP],
            []
        );

        $forecast = $evolution->callBuildForecastData(
            $seriesState,
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Visits' => false]
        );

        self::assertCount(1, $forecast);
        self::assertNull($forecast[0][0]);
        self::assertIsFloat($forecast[0][1]);
        self::assertGreaterThan(20.0, $forecast[0][1]);
    }

    public function testPrecomputeForecastRoundsCountMetricToInteger(): void
    {
        $evolution = $this->createEvolution(
            [
                'show_forecast' => 1,
                'columns_to_display' => ['nb_visits'],
                'rows_to_display' => [false],
                'translations' => ['nb_visits' => 'Visits'],
            ],
            false
        );
        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable($this->createDataTableForDay('2026-04-10', $site, null, ['nb_visits' => 80.0]), '2026-04-10');
        $map->addTable($this->createDataTableForDay('2026-04-11', $site, null, ['nb_visits' => 100.0]), '2026-04-11');
        $map->addTable($this->createDataTableForDay('2026-04-12', $site, null, ['nb_visits' => 140.0]), '2026-04-12');
        $map->addTable($this->createDataTableForDay('2026-04-13', $site, null, ['nb_visits' => 60.0]), '2026-04-13');
        $map->addTable($this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00', ['nb_visits' => 20.0], ArchiveState::INCOMPLETE), '2026-04-17');

        self::assertSame([[null, null, null, null, 80.0]], $evolution->precomputeForecast($map));
    }

    public function testPrecomputeForecastRoundsNonCountMetricToTwoDecimals(): void
    {
        $evolution = $this->createEvolution(
            [
                'show_forecast' => 1,
                'columns_to_display' => ['nb_actions_per_visit'],
                'rows_to_display' => [false],
                'translations' => ['nb_actions_per_visit' => 'Actions per visit'],
            ],
            false
        );
        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable($this->createDataTableForDay('2026-04-10', $site, null, ['nb_actions_per_visit' => 12.345]), '2026-04-10');
        $map->addTable($this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00', ['nb_actions_per_visit' => 90.0], ArchiveState::INCOMPLETE), '2026-04-17');

        self::assertSame([[null, 12.35]], $evolution->precomputeForecast($map));
    }

    /**
     * @dataProvider getForecastPrecisionTestData
     * @param string|false $columnUnit
     */
    public function testForecastPrecisionUsesMetricSemanticsAndNameFallbacks(
        string $columnName,
        $columnUnit,
        string $monotonicity,
        int $expected
    ): void {
        $evolution = $this->createEvolution([], false);

        $method = new ReflectionMethod(Evolution::class, 'getForecastPrecisionForColumn');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $columnName, $columnUnit, $monotonicity));
    }

    /**
     * @return iterable<string, array{string, string|false, string, int}>
     */
    public function getForecastPrecisionTestData(): iterable
    {
        yield 'plain nb metric' => ['nb_visits', false, Evolution::MONOTONICITY_UP, 0];
        yield 'embedded nb metric' => ['exit_nb_visits', false, Evolution::MONOTONICITY_UP, 0];
        yield 'count suffix metric' => ['bounce_count', false, Evolution::MONOTONICITY_UP, 0];
        yield 'actions per visit is ratio' => ['nb_actions_per_visit', false, Evolution::MONOTONICITY_FREE, 2];
        yield 'percent unit' => ['custom_metric', '%', Evolution::MONOTONICITY_FREE, 2];
        yield 'duration name fallback' => ['sum_visit_length_returning', false, Evolution::MONOTONICITY_UP, 2];
        yield 'unknown metric fallback' => ['custom_numeric_metric', false, Evolution::MONOTONICITY_UP, 2];
    }

    public function testExtractSubPeriodSamplesLooksUpRawColumnAndStoresUnderSeriesLabel(): void
    {
        // Sub-period API result rows are keyed by the raw archive column name (after
        // ReplaceColumnNames), but ForecastBuilder consumes samples keyed by the series label
        // it sees in $allSeriesData. Exercise the lookup with a translated label so a regression
        // that swaps the two keys would silently produce empty samples again.
        $evolution = $this->createEvolution([], false);
        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable($this->createDataTableForDay('2026-04-10', $site, null, ['nb_visits' => 80.0]), '2026-04-10');
        $map->addTable($this->createDataTableForDay('2026-04-11', $site, null, ['nb_visits' => 100.0]), '2026-04-11');

        $method = new ReflectionMethod(Evolution::class, 'extractSubPeriodSamples');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $samples = $method->invoke(
            $evolution,
            $map,
            ['Visits' => 'nb_visits'],
            [],
            ['Visits' => Evolution::MONOTONICITY_UP],
            'day'
        );

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 100.0]],
            $samples
        );
    }

    public function testExtractSubPeriodSamplesFillsZeroForRowlessSubTableOnMonotonicUpSeries(): void
    {
        // A sub-period table with no rows is a legitimately-empty archive, not a fetch error.
        // For MONOTONICITY_UP count series that means a true zero, so the extractor backfills
        // 0.0 to keep the analog walk's calendar dense.
        $evolution = $this->createEvolution([], false);
        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable($this->createDataTableForDay('2026-04-10', $site, null, ['nb_visits' => 80.0]), '2026-04-10');
        $map->addTable($this->createDataTableForDay('2026-04-11', $site, null, []), '2026-04-11');

        $method = new ReflectionMethod(Evolution::class, 'extractSubPeriodSamples');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $samples = $method->invoke(
            $evolution,
            $map,
            ['Visits' => 'nb_visits'],
            [],
            ['Visits' => Evolution::MONOTONICITY_UP],
            'day'
        );

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 0.0]],
            $samples
        );
    }

    public function testExtractSubPeriodSamplesSkipsRowlessSubTableForMonotonicDownAndFreeSeries(): void
    {
        // Synthetic 0 backfill is defensible only for MONOTONICITY_UP (no traffic = zero
        // count). For MONOTONICITY_DOWN (running mins) a "min of nothing" is not 0, and a
        // 0% rate inferred from no traffic is not a real ratio observation for
        // MONOTONICITY_FREE. The extractor must leave those dates absent from the sample
        // map so the same-DoW analog walk skips them instead of treating a synthetic
        // zero as an analog -- which would otherwise pull the DOWN prior to/below
        // current and trip silent forecast suppression via shouldRenderForecastValue().
        $evolution = $this->createEvolution([], false);
        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable(
            $this->createDataTableForDay('2026-04-10', $site, null, [
                'min_event_value' => 12.0,
                'bounce_rate' => 35.0,
                'nb_visits' => 80.0,
            ]),
            '2026-04-10'
        );
        // Rowless sub-table (a real no-traffic day in the analog window).
        $map->addTable($this->createDataTableForDay('2026-04-11', $site, null, []), '2026-04-11');

        $method = new ReflectionMethod(Evolution::class, 'extractSubPeriodSamples');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $samples = $method->invoke(
            $evolution,
            $map,
            [
                'Min event value' => 'min_event_value',
                'Bounce rate'     => 'bounce_rate',
                'Visits'          => 'nb_visits',
            ],
            [],
            [
                'Min event value' => Evolution::MONOTONICITY_DOWN,
                'Bounce rate'     => Evolution::MONOTONICITY_FREE,
                'Visits'          => Evolution::MONOTONICITY_UP,
            ],
            'day'
        );

        self::assertSame(
            [
                'Min event value' => ['2026-04-10' => 12.0],
                'Bounce rate'     => ['2026-04-10' => 35.0],
                'Visits'          => ['2026-04-10' => 80.0, '2026-04-11' => 0.0],
            ],
            $samples
        );
    }

    public function testExtractSubPeriodSamplesDefaultsMissingMonotonicityToUp(): void
    {
        // Defensive: if a caller fails to thread the monotonicity map through, the
        // rowless backfill should retain the legacy "always emit 0" behaviour rather
        // than silently dropping every series. Pin that fallback so a regression that
        // forgets the parameter does not produce mysteriously empty sample maps on
        // every no-traffic day.
        $evolution = $this->createEvolution([], false);
        $site = $this->createSiteMock();
        $map = new DataTable\Map();
        $map->addTable($this->createDataTableForDay('2026-04-10', $site, null, ['nb_visits' => 80.0]), '2026-04-10');
        $map->addTable($this->createDataTableForDay('2026-04-11', $site, null, []), '2026-04-11');

        $method = new ReflectionMethod(Evolution::class, 'extractSubPeriodSamples');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $samples = $method->invoke($evolution, $map, ['Visits' => 'nb_visits'], [], [], 'day');

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 0.0]],
            $samples
        );
    }

    public function testExtractSubPeriodSamplesUsesPerSeriesRowMatcherForMultiRowSeries(): void
    {
        // Multi-row evolution graphs (selectable_rows on a per-row report, e.g. plotting
        // France and Germany on a Country.getCountry evolution) plot one series per selected
        // row. The displayed-series path pulls each series' values from $childTable
        // ->getRowFromLabel($rowMatcher). The sub-period sample fetch must do the same -- if
        // it falls back to getFirstRow() then both series' historical samples are taken from
        // whichever country happens to sort first in the sub-period archive, collapsing both
        // forecasts onto identical, wrong history.
        $evolution = $this->createEvolution([], false);
        $site = $this->createSiteMock();

        $createDayWithRows = static function (string $date, array $rowsByLabel) use ($site): DataTable {
            $dataTable = new DataTable();
            $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
            $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $site);
            foreach ($rowsByLabel as $label => $columns) {
                $dataTable->addRowFromArray([
                    DataTable\Row::COLUMNS => array_merge(['label' => $label], $columns),
                ]);
            }
            return $dataTable;
        };

        $map = new DataTable\Map();
        // Sort order is "Germany first, France second" -- representative of a top-by-visits
        // sub-period archive where the user's selected rows are not the top row.
        $map->addTable($createDayWithRows('2026-04-10', [
            'Germany' => ['nb_visits' => 500.0],
            'France'  => ['nb_visits' => 80.0],
        ]), '2026-04-10');
        $map->addTable($createDayWithRows('2026-04-11', [
            'Germany' => ['nb_visits' => 510.0],
            'France'  => ['nb_visits' => 90.0],
        ]), '2026-04-11');

        $method = new ReflectionMethod(Evolution::class, 'extractSubPeriodSamples');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $samples = $method->invoke(
            $evolution,
            $map,
            ['France' => 'nb_visits', 'Germany' => 'nb_visits'],
            ['France' => 'France', 'Germany' => 'Germany'],
            ['France' => Evolution::MONOTONICITY_UP, 'Germany' => Evolution::MONOTONICITY_UP],
            'day'
        );

        self::assertSame(
            [
                'France'  => ['2026-04-10' => 80.0, '2026-04-11' => 90.0],
                'Germany' => ['2026-04-10' => 500.0, '2026-04-11' => 510.0],
            ],
            $samples
        );
    }

    public function testExtractSubPeriodSamplesBackfillsZeroForMonotonicUpSeriesWhenRowMatcherIsMissing(): void
    {
        // A specific selected row may be absent from an individual sub-period archive (e.g.
        // France had no visits that day, so the daily Country.getCountry archive has no
        // France row). For MONOTONICITY_UP count series this is a real zero observation, so
        // the extractor backfills 0.0; for DOWN/FREE the date stays absent.
        $evolution = $this->createEvolution([], false);
        $site = $this->createSiteMock();

        $createDayWithRows = static function (string $date, array $rowsByLabel) use ($site): DataTable {
            $dataTable = new DataTable();
            $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
            $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $site);
            foreach ($rowsByLabel as $label => $columns) {
                $dataTable->addRowFromArray([
                    DataTable\Row::COLUMNS => array_merge(['label' => $label], $columns),
                ]);
            }
            return $dataTable;
        };

        $map = new DataTable\Map();
        $map->addTable($createDayWithRows('2026-04-10', [
            'Germany' => ['nb_visits' => 500.0],
            'France'  => ['nb_visits' => 80.0],
        ]), '2026-04-10');
        // 2026-04-11: only Germany, no France row at all.
        $map->addTable($createDayWithRows('2026-04-11', [
            'Germany' => ['nb_visits' => 510.0],
        ]), '2026-04-11');

        $method = new ReflectionMethod(Evolution::class, 'extractSubPeriodSamples');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $samples = $method->invoke(
            $evolution,
            $map,
            ['France' => 'nb_visits', 'Germany' => 'nb_visits'],
            ['France' => 'France', 'Germany' => 'Germany'],
            ['France' => Evolution::MONOTONICITY_UP, 'Germany' => Evolution::MONOTONICITY_UP],
            'day'
        );

        self::assertSame(
            [
                'France'  => ['2026-04-10' => 80.0, '2026-04-11' => 0.0],
                'Germany' => ['2026-04-10' => 500.0, '2026-04-11' => 510.0],
            ],
            $samples
        );
    }

    public function testForecastPrecisionForMonotonicDownIntegerMetricRoundsToZeroDecimals(): void
    {
        $evolution = $this->createEvolution([], false, ['min_event_value' => Dimension::TYPE_NUMBER]);
        $method = new ReflectionMethod(Evolution::class, 'getForecastPrecisionForColumn');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame(0, $method->invoke($evolution, 'min_event_value', false, Evolution::MONOTONICITY_DOWN));
    }

    /**
     * @param array<string, mixed> $properties
     * @param array<string, string> $semanticTypes
     */
    private function createEvolution(
        array $properties,
        bool $isComparing,
        array $semanticTypes = []
    ): Evolution {
        $graph = $this->getMockBuilder(JqplotEvolutionGraph::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isComparing', 'getForecastSeriesState', 'setForecastSeriesState'])
            ->getMock();
        $graph->method('isComparing')->willReturn($isComparing);
        $graph->method('getForecastSeriesState')->willReturn(null);
        // Bypassing the parent constructor leaves requestConfig unset; supply a stub so the
        // sub-period sample fetch can early-return on the empty apiMethodToRequestDataTable
        // instead of crashing on the property access.
        $graph->requestConfig = new RequestConfig();

        return new class ($properties, 'evolution', $graph, $semanticTypes) extends Evolution {
            /** @var array<string, string> */
            private $stubSemanticTypes;

            /**
             * @param array<string, mixed> $properties
             * @param array<string, string> $semanticTypes
             */
            public function __construct(array $properties, string $type, $graph, array $semanticTypes)
            {
                parent::__construct($properties, $type, $graph);
                $this->stubSemanticTypes = $semanticTypes;
            }

            protected function getMetricSemanticTypes(): array
            {
                return $this->stubSemanticTypes;
            }

            protected function getUnitsForColumnsToDisplay()
            {
                return array_fill_keys($this->properties['columns_to_display'] ?? [], false);
            }

            /**
             * @param array<int, DataTable> $dataTables
             * @param array<int, string> $dataStates
             * @param array<string, string|false> $seriesUnits
             * @return array<int, array<int, float|null>>
             */
            public function callBuildForecastData(
                ForecastSeriesState $seriesState,
                array $dataTables,
                array $dataStates,
                array $seriesUnits
            ): array {
                return $this->buildForecastData($seriesState, $dataTables, $dataStates, $seriesUnits);
            }
        };
    }

    private function createSiteMock(): Site
    {
        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTimezone'])
            ->getMock();

        $site->method('getTimezone')->willReturn('UTC');

        return $site;
    }

    /**
     * @param array<string, float|int> $columns
     */
    private function createDataTableForDay(
        string $date,
        Site $site,
        ?string $archivedDate = null,
        array $columns = [],
        ?string $archiveState = null
    ): DataTable {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $site);

        if ($archivedDate !== null) {
            $dataTable->setMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME, $archivedDate);
        }

        if ($archiveState !== null) {
            $dataTable->setMetadata(DataTable::ARCHIVE_STATE_METADATA_NAME, $archiveState);
        }

        if ($columns !== []) {
            $dataTable->addRowFromArray([
                DataTable\Row::COLUMNS => $columns,
            ]);
        }

        return $dataTable;
    }
}
