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
use Piwik\Date;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Evolution;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
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

        self::assertSame([], $evolution->callBuildForecastData());
    }

    public function testBuildForecastDataReturnsEmptyWhenComparing(): void
    {
        $evolution = $this->createEvolution(['show_forecast' => 1], true);

        self::assertSame([], $evolution->callBuildForecastData());
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
        yield 'numeric int 0 counts as data' => [0, true];
        yield 'numeric float 0 counts as data' => [0.0, true];
        yield 'string "0" counts as data' => ['0', true];
        yield 'positive int' => [1, true];
        yield 'positive float' => [1.5, true];
        yield 'boolean false is missing' => [false, false];
        yield 'null is missing' => [null, false];
        yield 'empty string is missing' => ['', false];
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
     * @dataProvider getIsIncompleteTickTestData
     * @param array{date: string, archiveState?: string}|null $tableSpec
     */
    public function testIsIncompleteTick(?array $tableSpec, string $siteToday, bool $expected): void
    {
        if (null === $tableSpec) {
            $childTable = new DataTable();
        } else {
            $childTable = $this->createDataTableForDay(
                $tableSpec['date'],
                $this->createSiteMock(),
                null,
                [],
                $tableSpec['archiveState'] ?? null
            );
        }

        self::assertSame(
            $expected,
            Evolution::isIncompleteTick($childTable, strtotime($siteToday))
        );
    }

    /**
     * @return iterable<string, array{?array{date: string, archiveState?: string}, string, bool}>
     */
    public function getIsIncompleteTickTestData(): iterable
    {
        // siteToday far past the period; if the helper looked only at the period it would
        // say "complete", so a true result proves the INCOMPLETE archive_state metadata wins.
        yield 'archive_state INCOMPLETE wins over far-past period' => [
            ['date' => '2026-04-10', 'archiveState' => ArchiveState::INCOMPLETE],
            '2099-01-01 00:00:00 UTC',
            true,
        ];

        // siteToday equals the period's start; the day ends 23:59:59 later, so the period
        // covers siteToday and the helper must return true even without metadata.
        yield 'period covers siteToday without metadata' => [
            ['date' => '2026-05-21'],
            '2026-05-21 00:00:00 UTC',
            true,
        ];

        // siteToday is one second after the period ended, so the helper must classify the
        // tick as complete despite the absent archive_state metadata.
        yield 'past period without metadata is complete' => [
            ['date' => '2026-05-20'],
            '2026-05-21 00:00:00 UTC',
            false,
        ];

        // Defensive case: a bare child table with neither archive_state nor period metadata.
        // The helper must not call getDateEnd() on a non-object.
        yield 'bare datatable without period metadata is complete' => [
            null,
            '2026-05-21 00:00:00 UTC',
            false,
        ];
    }

    /**
     * @dataProvider getComputeDataStatesTimezoneTestData
     * @param array<int, string> $periodDates
     * @param array<int, string> $expectedStates
     */
    public function testComputeDataStatesHonoursSiteTimezone(
        string $pinnedNow,
        string $siteTimezone,
        array $periodDates,
        array $expectedStates
    ): void {
        // Pin "now" so the test stays deterministic regardless of when it runs. Without
        // archive_state metadata each row's state is derived from the period boundary
        // measured in the site's timezone, which is the surface this test exercises (the
        // case Goals/Ecommerce evolutions hit on days without events).
        $originalNow = Date::$now;
        Date::$now = strtotime($pinnedNow);

        try {
            $site = $this->createSiteMock($siteTimezone);
            $evolution = $this->createEvolution([], false);

            $childTables = [];
            foreach ($periodDates as $date) {
                $childTables[$date] = $this->createDataTableForDay($date, $site);
            }

            self::assertSame($expectedStates, $evolution->computeDataStates($childTables));
        } finally {
            Date::$now = $originalNow;
        }
    }

    /**
     * @return iterable<string, array{string, string, array<int, string>, array<int, string>}>
     */
    public function getComputeDataStatesTimezoneTestData(): iterable
    {
        yield 'UTC site marks current day INCOMPLETE without metadata' => [
            '2026-05-21 12:00:00 UTC',
            'UTC',
            ['2026-05-19', '2026-05-20', '2026-05-21'],
            [ArchiveState::COMPLETE, ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
        ];

        // 00:30 UTC on 2026-05-22 — Pacific/Auckland is on NZST (UTC+12) in May, so its
        // local time is already 12:30 on 2026-05-22. The 2026-05-22 period is the site's
        // "today" even though UTC has barely rolled over.
        yield 'UTC+12 (Auckland NZST) treats site-local today as INCOMPLETE' => [
            '2026-05-22 00:30:00 UTC',
            'Pacific/Auckland',
            ['2026-05-20', '2026-05-21', '2026-05-22'],
            [ArchiveState::COMPLETE, ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
        ];

        // Same UTC instant as the UTC+12 case, but Pacific/Pago_Pago sits at UTC-11 (no
        // DST) so its local time is still 13:30 on 2026-05-21. The 2026-05-21 period is
        // the site's "today" even though UTC has already moved on to 2026-05-22.
        yield 'UTC-11 (Pago_Pago) treats site-local today as INCOMPLETE despite UTC rollover' => [
            '2026-05-22 00:30:00 UTC',
            'Pacific/Pago_Pago',
            ['2026-05-19', '2026-05-20', '2026-05-21'],
            [ArchiveState::COMPLETE, ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
        ];
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

            protected function getForecastClassifier(): ForecastMetricClassifier
            {
                return new ForecastMetricClassifier($this->stubSemanticTypes);
            }

            protected function getUnitsForColumnsToDisplay()
            {
                return array_fill_keys($this->properties['columns_to_display'] ?? [], false);
            }

            /**
             * @return array<int, array<int, float|null>>
             */
            public function callBuildForecastData(): array
            {
                return $this->buildForecastData();
            }
        };
    }

    private function createSiteMock(string $timezone = 'UTC'): Site
    {
        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTimezone'])
            ->getMock();

        $site->method('getTimezone')->willReturn($timezone);

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
