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
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph;
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

        self::assertSame([], $evolution->callBuildForecastData([], [], [], [], [], []));
    }

    public function testBuildForecastDataReturnsEmptyWhenComparing(): void
    {
        $evolution = $this->createEvolution(['show_forecast' => 1], true);

        self::assertSame([], $evolution->callBuildForecastData([], [], [], [], [], []));
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
     * @dataProvider getDownwardForecastColumnTestData
     * @param string|false $columnUnit
     */
    public function testColumnAllowsDownwardForecastUsesMetricMonotonicity(string $columnName, $columnUnit, bool $expected): void
    {
        $evolution = $this->createEvolution([], false);

        $method = new ReflectionMethod(Evolution::class, 'columnAllowsDownwardForecast');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $columnName, $columnUnit));
    }

    /**
     * @return iterable<string, array{string, string|false, bool}>
     */
    public function getDownwardForecastColumnTestData(): iterable
    {
        yield 'percent unit' => ['custom_metric', '%', true];
        yield 'rate metric' => ['bounce_rate', false, true];
        yield 'percentage metric' => ['conversion_percentage', false, true];
        yield 'average metric' => ['avg_time_on_site', false, true];
        yield 'per metric containing nb prefix' => ['nb_actions_per_visit', false, true];
        yield 'plain nb metric' => ['nb_visits', false, false];
        yield 'sum daily nb metric' => ['sum_daily_nb_users', false, false];
        yield 'exit nb metric' => ['exit_nb_visits', false, false];
        yield 'sum daily exit nb metric' => ['sum_daily_exit_nb_uniq_visitors', false, false];
        yield 'lower is better count metric' => ['bounce_count', false, false];
        yield 'unknown metric defaults to monotonic' => ['custom_numeric_metric', false, false];
        yield 'revenue currency metric stays monotonic' => ['revenue', false, false];
        yield 'duration sum metric stays monotonic' => ['sum_time_spent', false, false];
    }

    /**
     * @dataProvider getDownwardForecastSemanticTypeTestData
     */
    public function testColumnAllowsDownwardForecastUsesSemanticTypeForCustomMetrics(
        string $columnName,
        ?string $stubSemanticType,
        bool $expected
    ): void {
        $semanticTypes = $stubSemanticType !== null ? [$columnName => $stubSemanticType] : [];
        $evolution = $this->createEvolution([], false, $semanticTypes);

        $method = new ReflectionMethod(Evolution::class, 'columnAllowsDownwardForecast');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $columnName, false));
    }

    /**
     * @return iterable<string, array{string, ?string, bool}>
     */
    public function getDownwardForecastSemanticTypeTestData(): iterable
    {
        yield 'percent semantic type without name pattern' => ['engagement_score', Dimension::TYPE_PERCENT, true];
        yield 'float semantic type without name pattern' => ['session_quality', Dimension::TYPE_FLOAT, true];
        yield 'number semantic type without name pattern stays monotonic' => ['custom_count', Dimension::TYPE_NUMBER, false];
        yield 'money semantic type stays monotonic' => ['custom_revenue', Dimension::TYPE_MONEY, false];
        yield 'duration semantic type without avg_ prefix stays monotonic' => ['custom_dwell', Dimension::TYPE_DURATION_S, false];
        yield 'byte semantic type without avg_ prefix stays monotonic' => ['custom_bandwidth', Dimension::TYPE_BYTE, false];
        yield 'no semantic type defaults to monotonic' => ['custom_metric_no_signal', null, false];
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

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-11', $site, '2026-04-11 12:00:00'),
        ];

        $forecast = $evolution->callBuildForecastData(
            ['Visits' => [80.0, 20.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Visits' => false],
            ['Visits' => [true, true]],
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

        self::assertSame([[null, null, null, null, 48.0]], $evolution->precomputeForecast($map));
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
        bool $allowsDownward,
        int $expected
    ): void {
        $evolution = $this->createEvolution([], false);

        $method = new ReflectionMethod(Evolution::class, 'getForecastPrecisionForColumn');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        self::assertSame($expected, $method->invoke($evolution, $columnName, $columnUnit, $allowsDownward));
    }

    /**
     * @return iterable<string, array{string, string|false, bool, int}>
     */
    public function getForecastPrecisionTestData(): iterable
    {
        yield 'plain nb metric' => ['nb_visits', false, false, 0];
        yield 'embedded nb metric' => ['exit_nb_visits', false, false, 0];
        yield 'count suffix metric' => ['bounce_count', false, false, 0];
        yield 'actions per visit is ratio' => ['nb_actions_per_visit', false, true, 2];
        yield 'percent unit' => ['custom_metric', '%', true, 2];
        yield 'duration name fallback' => ['sum_visit_length_returning', false, false, 2];
        yield 'unknown metric fallback' => ['custom_numeric_metric', false, false, 2];
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
        $graph = $this->getMockBuilder(JqplotGraph::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isComparing'])
            ->getMockForAbstractClass();
        $graph->method('isComparing')->willReturn($isComparing);

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
             * @param array<string, mixed> $allSeriesData
             * @param array<int, DataTable> $dataTables
             * @param array<int, string> $dataStates
             * @param array<string, string|false> $seriesUnits
             * @param array<string, array<int, bool>> $allSeriesDataAvailability
             * @param array<string, bool> $allSeriesAllowsDownwardForecast
             * @return array<int, array<int, float|null>>
             */
            public function callBuildForecastData(
                array $allSeriesData,
                array $dataTables,
                array $dataStates,
                array $seriesUnits,
                array $allSeriesDataAvailability,
                array $allSeriesAllowsDownwardForecast
            ): array {
                return $this->buildForecastData(
                    $allSeriesData,
                    $dataTables,
                    $dataStates,
                    $seriesUnits,
                    $allSeriesDataAvailability,
                    $allSeriesAllowsDownwardForecast
                );
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
