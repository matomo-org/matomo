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

        self::assertSame([], $evolution->callBuildForecastData([], [], [], [], []));
    }

    public function testBuildForecastDataReturnsEmptyWhenComparing(): void
    {
        $evolution = $this->createEvolution(['show_forecast' => 1], true);

        self::assertSame([], $evolution->callBuildForecastData([], [], [], [], []));
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
            ['Visits' => [true, true]]
        );

        self::assertCount(1, $forecast);
        self::assertNull($forecast[0][0]);
        self::assertIsFloat($forecast[0][1]);
        self::assertGreaterThan(20.0, $forecast[0][1]);
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function createEvolution(array $properties, bool $isComparing): Evolution
    {
        $graph = $this->getMockBuilder(JqplotGraph::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isComparing'])
            ->getMockForAbstractClass();
        $graph->method('isComparing')->willReturn($isComparing);

        return new class ($properties, 'evolution', $graph) extends Evolution {
            /**
             * @param array<string, mixed> $allSeriesData
             * @param array<int, DataTable> $dataTables
             * @param array<int, string> $dataStates
             * @param array<string, string|false> $seriesUnits
             * @param array<string, array<int, bool>> $allSeriesDataAvailability
             * @return array<int, array<int, float|null>>
             */
            public function callBuildForecastData(
                array $allSeriesData,
                array $dataTables,
                array $dataStates,
                array $seriesUnits,
                array $allSeriesDataAvailability
            ): array {
                return $this->buildForecastData(
                    $allSeriesData,
                    $dataTables,
                    $dataStates,
                    $seriesUnits,
                    $allSeriesDataAvailability
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

    private function createDataTableForDay(string $date, Site $site, ?string $archivedDate = null): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $site);

        if ($archivedDate !== null) {
            $dataTable->setMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME, $archivedDate);
        }

        return $dataTable;
    }
}
