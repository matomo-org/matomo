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
use Piwik\Log\LoggerInterface;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSubPeriodFetcher;

/**
 * @group CoreVisualizations
 * @group Evolution
 * @group JqplotDataGenerator
 */
class ForecastSubPeriodFetcherTest extends TestCase
{
    public function testCollectReturnsEmptyForEmptyDataTables(): void
    {
        $fetcher = $this->createFetcher();

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect([], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectReturnsEmptyForEmptySeriesColumns(): void
    {
        $fetcher = $this->createFetcher();

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect([$this->createDayDataTable('2026-04-10')], $this->createSeriesState([], [], []), 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectReturnsEmptyWhenApiMethodIsMissingOrMalformed(): void
    {
        $fetcher = $this->createFetcher();
        $dataTables = [$this->createDayDataTable('2026-04-10')];

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect($dataTables, $this->createSeriesState(['Visits' => 'nb_visits'], [], []), '', 1, '')
        );
        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect($dataTables, $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'NoDotMethod', 1, '')
        );
    }

    public function testCollectReturnsEmptyForInvalidIdSite(): void
    {
        $fetcher = $this->createFetcher();
        $dataTables = [$this->createDayDataTable('2026-04-10')];

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect($dataTables, $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 0, '')
        );
    }

    public function testCollectReturnsEmptyForUnsupportedPeriodLabel(): void
    {
        // A range-period displayed graph: ForecastBuilder only seasonal-decomposes for
        // day/week/month/year, so the fetcher should bail out without firing the API call.
        $fetcher = $this->createFetcher();
        $rangeTable = new DataTable();
        $rangeTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('range', '2026-04-01,2026-04-10'));

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect([$rangeTable], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectIssuesGapDailyRequestAndMergesWithDisplayedRangeForShortDayDisplay(): void
    {
        // Short display: the analog window (70 days back from the day before the in-progress
        // target) starts before the displayed range, so the fetcher fires a gap-only inner
        // request for the pre-display history and merges it with the per-day values already
        // present in $dataTables. The in-progress target is skipped via its INCOMPLETE
        // archive-state metadata so its partial value cannot leak into a later same-DoW
        // tick's analog walk via the running daily map.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = [$apiMethod, $params];
            return $this->createSampleResultMap([
                '2026-04-01' => ['nb_visits' => 70.0],
                '2026-04-02' => ['nb_visits' => 75.0],
            ]);
        });

        $dataTables = [
            $this->createDayDataTableWithRow('2026-04-05', ['nb_visits' => 110.0], ArchiveState::COMPLETE),
            $this->createDayDataTableWithRow('2026-04-06', ['nb_visits' => 120.0], ArchiveState::COMPLETE),
            $this->createDayDataTableWithRow('2026-04-07', ['nb_visits' => 130.0], ArchiveState::COMPLETE),
            $this->createDayDataTableWithRow('2026-04-08', ['nb_visits' => 140.0], ArchiveState::COMPLETE),
            $this->createDayDataTableWithRow('2026-04-09', ['nb_visits' => 150.0], ArchiveState::COMPLETE),
            $this->createDayDataTableWithRow('2026-04-10', ['nb_visits' => 22.0], ArchiveState::INCOMPLETE),
        ];

        $result = $fetcher->collect(
            $dataTables,
            $this->createSeriesState(
                ['Visits' => 'nb_visits'],
                [],
                ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'VisitsSummary.get',
            42,
            'pageUrl==foo'
        );

        // Fetch covers only the pre-display gap [analogStart, displayStart - 1], not the
        // displayed range itself. analogStart = endDate (one day before last displayed) - 70 days.
        self::assertCount(1, $captured);
        self::assertSame('VisitsSummary.get', $captured[0][0]);
        self::assertSame('day', $captured[0][1]['period']);
        self::assertSame('2026-01-29,2026-04-04', $captured[0][1]['date']);
        self::assertSame('pageUrl==foo', $captured[0][1]['segment']);

        // Result merges gap fetch (2026-04-01, 2026-04-02) with the complete displayed days
        // (2026-04-05 through 2026-04-09). The INCOMPLETE target 2026-04-10 is absent —
        // matching what the API path would have returned for the same range.
        self::assertSame(
            [
                'daily' => [
                    'Visits' => [
                        '2026-04-01' => 70.0,
                        '2026-04-02' => 75.0,
                        '2026-04-05' => 110.0,
                        '2026-04-06' => 120.0,
                        '2026-04-07' => 130.0,
                        '2026-04-08' => 140.0,
                        '2026-04-09' => 150.0,
                    ],
                ],
                'monthly' => [],
            ],
            $result
        );
    }

    public function testCollectSkipsDailyFetchWhenDayDisplayCoversAnalogWindow(): void
    {
        // Long display: when the displayed range spans (or exceeds) the day-analog window
        // (70 days before the day before the in-progress target), the displayed per-day
        // values alone supply the analog walk. No inner request fires.
        $fetcher = $this->createFetcher();

        // 76-day display from 2026-01-25 to 2026-04-10. analogWindowStart = 2026-04-09 - 70
        // days = 2026-01-29; firstDisplayedDate = 2026-01-25 < analogWindowStart, so skip.
        $dataTables = $this->buildDailyDisplayRange('2026-01-25', 76, [75]);

        $result = $fetcher->collect(
            $dataTables,
            $this->createSeriesState(
                ['Visits' => 'nb_visits'],
                [],
                ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'VisitsSummary.get',
            42,
            ''
        );

        // No inner request fired (createFetcher's default stub calls self::fail() if invoked).
        self::assertSame([], $result['monthly']);
        // Daily map carries the 75 complete days; the INCOMPLETE trailing target is absent.
        self::assertArrayHasKey('Visits', $result['daily']);
        self::assertCount(75, $result['daily']['Visits']);
        self::assertArrayHasKey('2026-01-25', $result['daily']['Visits']);
        self::assertArrayHasKey('2026-04-09', $result['daily']['Visits']);
        self::assertArrayNotHasKey('2026-04-10', $result['daily']['Visits']);
    }

    public function testCollectSkipsDailyFetchAtExactAnalogWindowBoundary(): void
    {
        // Exact boundary: firstDisplayedDate == analogWindowStart. The substitution branch
        // takes a `<=` comparison, so this case must skip the fetch (not fire it).
        $fetcher = $this->createFetcher();

        // 72-day display from 2026-01-29 to 2026-04-10. analogWindowStart = 2026-01-29 ==
        // firstDisplayedDate (boundary case).
        $dataTables = $this->buildDailyDisplayRange('2026-01-29', 72, [71]);

        $result = $fetcher->collect(
            $dataTables,
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            42,
            ''
        );

        self::assertCount(71, $result['daily']['Visits']);
        self::assertArrayHasKey('2026-01-29', $result['daily']['Visits']);
        self::assertArrayNotHasKey('2026-04-10', $result['daily']['Visits']);
    }

    public function testCollectDropsIncompleteDisplayedDaysFromDailyMapEvenMidRange(): void
    {
        // Mid-range incomplete tick (e.g. an archive invalidated for a single past day):
        // the partial archived value for that date must not enter the daily sample map,
        // because a later same-DoW analog walk would treat it as a real prior observation.
        $fetcher = $this->createFetcher();

        // 72-day display from 2026-01-29 to 2026-04-10; two INCOMPLETE entries (i=30, i=71).
        $dataTables = $this->buildDailyDisplayRange('2026-01-29', 72, [30, 71]);

        $result = $fetcher->collect(
            $dataTables,
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            42,
            ''
        );

        // 2026-01-29 + 30 days = 2026-02-28; 2026-01-29 + 71 days = 2026-04-10.
        self::assertArrayNotHasKey('2026-02-28', $result['daily']['Visits']);
        self::assertArrayNotHasKey('2026-04-10', $result['daily']['Visits']);
        self::assertCount(70, $result['daily']['Visits']);
    }

    public function testCollectFansOutDailyAndMonthlyForWeekPeriod(): void
    {
        // Week-period forecasts pull only a daily window (no monthly fan-out) sized to the
        // in-progress week (≤7 days) + WEEK_ANALOG_CHUNK × 7 = 21 days of same-DoW history
        // = 30 days with slack. The pre-resize implementation fetched 70 days regardless.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = [$params['period'], $params['date']];
            return $this->createSampleResultMap([]);
        });

        $weekTable = new DataTable();
        $weekTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('week', '2026-04-06'));

        $fetcher->collect([$weekTable], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '');

        self::assertCount(1, $captured);
        self::assertSame('day', $captured[0][0]);
        // endDate = week end (2026-04-12) - 1 day = 2026-04-11; startDate = endDate - 30 days.
        self::assertSame('2026-03-12,2026-04-11', $captured[0][1]);
    }

    public function testCollectFansOutDailyAndMonthlyForMonthPeriod(): void
    {
        // Month-period forecasts pull a daily window (MONTH_DAILY_WINDOW_DAYS = 60: 31-day
        // month + MONTH_ANALOG_CHUNK × 7 = 28 days history + 1 day slack) plus a
        // MONTH_MONTHLY_WINDOW_YEARS = 4-year monthly window for the same-MoY trend.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = [$params['period'], $params['date']];
            return $this->createSampleResultMap([]);
        });

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $fetcher->collect([$monthTable], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '');

        self::assertCount(2, $captured);
        self::assertSame('day', $captured[0][0]);
        // endDate = month end (2026-04-30) - 1 day = 2026-04-29; startDate = endDate - 60 days.
        self::assertSame('2026-02-28,2026-04-29', $captured[0][1]);
        self::assertSame('month', $captured[1][0]);
        // Monthly window: 4 years back from endDate. Date::subYear truncates to Jan 1, so a
        // year-aligned start is the documented behaviour; do not over-rotate that into a
        // calendar-day-aligned expectation.
        self::assertSame('2022-01-01,2026-04-29', $captured[1][1]);
    }

    public function testCollectFansOutDailyAndMonthlyForYearPeriod(): void
    {
        // Year-period forecasts pull YEAR_DAILY_WINDOW_DAYS = 60 daily (for the in-progress
        // month recursion) and YEAR_MONTHLY_WINDOW_YEARS = 8 monthly (for the same-MoY trend
        // across remaining months). The pre-resize implementation fetched 70 + 9 years.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = [$params['period'], $params['date']];
            return $this->createSampleResultMap([]);
        });

        $yearTable = new DataTable();
        $yearTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('year', '2026-01-01'));

        $fetcher->collect([$yearTable], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '');

        self::assertCount(2, $captured);
        self::assertSame('day', $captured[0][0]);
        // endDate = year end (2026-12-31) - 1 day = 2026-12-30; startDate = endDate - 60 days.
        self::assertSame('2026-10-31,2026-12-30', $captured[0][1]);
        self::assertSame('month', $captured[1][0]);
        // 8-year monthly window for year-target forecasts. Date::subYear truncates to Jan 1.
        self::assertSame('2018-01-01,2026-12-30', $captured[1][1]);
    }

    public function testCollectLogsAndReturnsEmptyWhenInnerRequestThrows(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with(self::stringContains('forecast sub-period fetch failed'), self::callback(static function (array $context): bool {
                return ($context['apiMethod'] ?? null) === 'VisitsSummary.get'
                    && ($context['idSite'] ?? null) === 99
                    && ($context['period'] ?? null) === 'day'
                    && false !== strpos($context['message'] ?? '', 'archive boom');
            }));

        $fetcher = $this->createFetcher(static function (): void {
            throw new \RuntimeException('archive boom');
        }, $logger);

        $result = $fetcher->collect(
            [$this->createDayDataTable('2026-04-10')],
            $this->createSeriesState(['Visits' => 'nb_visits'], [], []),
            'VisitsSummary.get',
            99,
            ''
        );

        self::assertSame(['daily' => [], 'monthly' => []], $result);
    }

    public function testCollectReturnsEmptyWhenInnerRequestReturnsNonMapResult(): void
    {
        // Single in-progress displayed day → no usable display map (the INCOMPLETE target is
        // filtered out before extraction). With the inner API stub returning a non-Map result,
        // both contribution channels are empty and the fetcher should produce an empty payload.
        $fetcher = $this->createFetcher(static function () {
            return new DataTable();
        });

        $result = $fetcher->collect(
            [$this->createDayDataTableWithRow('2026-04-10', ['nb_visits' => 1.0], ArchiveState::INCOMPLETE)],
            $this->createSeriesState(['Visits' => 'nb_visits'], [], []),
            'VisitsSummary.get',
            1,
            ''
        );

        self::assertSame(['daily' => [], 'monthly' => []], $result);
    }

    public function testExtractSamplesLooksUpRawColumnAndStoresUnderSeriesLabel(): void
    {
        // Sub-period API result rows are keyed by the raw archive column name (after
        // ReplaceColumnNames), but ForecastBuilder consumes samples keyed by the series label
        // it sees in $allSeriesData. Exercise the lookup with a translated label so a
        // regression that swaps the two keys would silently produce empty samples again.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['nb_visits' => 80.0],
            '2026-04-11' => ['nb_visits' => 100.0],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            $this->createSeriesState(
                ['Visits' => 'nb_visits'],
                [],
                ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'day'
        );

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 100.0]],
            $samples
        );
    }

    public function testExtractSamplesFillsZeroForRowlessSubTableOnMonotonicUpSeries(): void
    {
        // A sub-period table with no rows is a legitimately-empty archive, not a fetch error.
        // For MONOTONICITY_UP count series that means a true zero, so the extractor backfills
        // 0.0 to keep the analog walk's calendar dense.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['nb_visits' => 80.0],
            '2026-04-11' => [],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            $this->createSeriesState(
                ['Visits' => 'nb_visits'],
                [],
                ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'day'
        );

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 0.0]],
            $samples
        );
    }

    public function testExtractSamplesSkipsRowlessSubTableForMonotonicDownAndFreeSeries(): void
    {
        // Synthetic 0 backfill is defensible only for MONOTONICITY_UP (no traffic = zero
        // count). For MONOTONICITY_DOWN (running mins) a "min of nothing" is not 0, and a
        // 0% rate inferred from no traffic is not a real ratio observation for
        // MONOTONICITY_FREE. The extractor must leave those dates absent from the sample
        // map so the same-DoW analog walk skips them instead of treating a synthetic
        // zero as an analog -- which would otherwise pull the DOWN prior to/below
        // current and trip silent forecast suppression via shouldRenderForecastValue().
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['min_event_value' => 12.0, 'bounce_rate' => 35.0, 'nb_visits' => 80.0],
            '2026-04-11' => [],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            $this->createSeriesState(
                [
                    'Min event value' => 'min_event_value',
                    'Bounce rate'     => 'bounce_rate',
                    'Visits'          => 'nb_visits',
                ],
                [],
                [
                    'Min event value' => ForecastMetricClassifier::MONOTONICITY_DOWN,
                    'Bounce rate'     => ForecastMetricClassifier::MONOTONICITY_FREE,
                    'Visits'          => ForecastMetricClassifier::MONOTONICITY_UP,
                ]
            ),
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

    public function testExtractSamplesDefaultsMissingMonotonicityToUp(): void
    {
        // Defensive: if a caller fails to thread the monotonicity map through, the
        // rowless backfill should retain the legacy "always emit 0" behaviour rather
        // than silently dropping every series. Pin that fallback so a regression that
        // forgets the parameter does not produce mysteriously empty sample maps on
        // every no-traffic day.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['nb_visits' => 80.0],
            '2026-04-11' => [],
        ]);

        $samples = $fetcher->extractSamples($map, $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'day');

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 0.0]],
            $samples
        );
    }

    public function testExtractSamplesUsesPerSeriesRowMatcherForMultiRowSeries(): void
    {
        // Multi-row evolution graphs (selectable_rows on a per-row report, e.g. plotting
        // France and Germany on a Country.getCountry evolution) plot one series per selected
        // row. The displayed-series path pulls each series' values from $childTable
        // ->getRowFromLabel($rowMatcher). The sub-period sample fetch must do the same -- if
        // it falls back to getFirstRow() then both series' historical samples are taken from
        // whichever country happens to sort first in the sub-period archive, collapsing both
        // forecasts onto identical, wrong history.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            // Sort order is "Germany first, France second" -- representative of a
            // top-by-visits sub-period archive where the user's selected rows are not the top.
            '2026-04-10' => [
                ['label' => 'Germany', 'nb_visits' => 500.0],
                ['label' => 'France',  'nb_visits' => 80.0],
            ],
            '2026-04-11' => [
                ['label' => 'Germany', 'nb_visits' => 510.0],
                ['label' => 'France',  'nb_visits' => 90.0],
            ],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            $this->createSeriesState(
                ['France' => 'nb_visits', 'Germany' => 'nb_visits'],
                ['France' => 'France', 'Germany' => 'Germany'],
                [
                    'France'  => ForecastMetricClassifier::MONOTONICITY_UP,
                    'Germany' => ForecastMetricClassifier::MONOTONICITY_UP,
                ]
            ),
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

    public function testExtractSamplesBackfillsZeroForMonotonicUpSeriesWhenRowMatcherIsMissing(): void
    {
        // A specific selected row may be absent from an individual sub-period archive (e.g.
        // France had no visits that day, so the daily Country.getCountry archive has no
        // France row). For MONOTONICITY_UP count series this is a real zero observation, so
        // the extractor backfills 0.0; for DOWN/FREE the date stays absent.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => [
                ['label' => 'Germany', 'nb_visits' => 500.0],
                ['label' => 'France',  'nb_visits' => 80.0],
            ],
            // 2026-04-11: only Germany, no France row at all.
            '2026-04-11' => [
                ['label' => 'Germany', 'nb_visits' => 510.0],
            ],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            $this->createSeriesState(
                ['France' => 'nb_visits', 'Germany' => 'nb_visits'],
                ['France' => 'France', 'Germany' => 'Germany'],
                [
                    'France'  => ForecastMetricClassifier::MONOTONICITY_UP,
                    'Germany' => ForecastMetricClassifier::MONOTONICITY_UP,
                ]
            ),
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

    /**
     * Build the slice of {@see ForecastSeriesState} the fetcher reads (columns, rows,
     * monotonicity). The state's remaining slots (data, availability, precision) are
     * builder-only and stay empty here.
     *
     * @param array<string, string> $columns
     * @param array<string, mixed> $rows
     * @param array<string, string> $monotonicity
     */
    private function createSeriesState(
        array $columns,
        array $rows = [],
        array $monotonicity = []
    ): ForecastSeriesState {
        return new ForecastSeriesState([], [], $monotonicity, [], $columns, $rows);
    }

    private function createFetcher(?callable $apiRequestProcessor = null, ?LoggerInterface $logger = null): ForecastSubPeriodFetcher
    {
        return new ForecastSubPeriodFetcher(
            $apiRequestProcessor ?? static function () {
                self::fail('Inner API request should not have fired for this case.');
            },
            $logger ?? $this->createMock(LoggerInterface::class)
        );
    }

    private function createDayDataTable(string $date): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
        return $dataTable;
    }

    /**
     * Day table with a single row of column values and an explicit archive state. Used by
     * the day-period substitution tests where the displayed range carries real per-day
     * archive values and the in-progress target is flagged INCOMPLETE so the substitution
     * path correctly drops it from the daily sample map.
     *
     * @param array<string, float> $columns
     */
    private function createDayDataTableWithRow(string $date, array $columns, string $archiveState): DataTable
    {
        $dataTable = $this->createDayDataTable($date);
        $dataTable->setMetadata(DataTable::ARCHIVE_STATE_METADATA_NAME, $archiveState);
        $dataTable->addRowFromArray([DataTable\Row::COLUMNS => $columns]);
        return $dataTable;
    }

    /**
     * Build a contiguous run of day tables starting at $startDate (Y-m-d), spanning $length
     * days. Index positions in $incompleteIndexes are flagged ArchiveState::INCOMPLETE; the
     * remainder are ArchiveState::COMPLETE. Row values are deterministic and unique per
     * index so the merged result can be asserted by key without value-equality plumbing.
     *
     * @param array<int, int> $incompleteIndexes
     * @return array<int, DataTable>
     */
    private function buildDailyDisplayRange(string $startDate, int $length, array $incompleteIndexes = []): array
    {
        $incomplete = array_flip($incompleteIndexes);
        $cursor = Factory::build('day', $startDate)->getDateStart();
        $tables = [];
        for ($i = 0; $i < $length; ++$i) {
            $date = $cursor->addDay($i)->toString('Y-m-d');
            $state = isset($incomplete[$i]) ? ArchiveState::INCOMPLETE : ArchiveState::COMPLETE;
            $tables[] = $this->createDayDataTableWithRow($date, ['nb_visits' => 100.0 + $i], $state);
        }
        return $tables;
    }

    /**
     * @param array<string, mixed> $rowsByDate Map of date (Y-m-d) → either a column map for
     *        the single-row case (`['nb_visits' => 80.0]`) or a list of column maps for the
     *        multi-row case (`[['label' => 'France', 'nb_visits' => 80.0], ['label' => ...]]`).
     *        An empty array creates a rowless sub-table.
     */
    private function createSampleResultMap(array $rowsByDate): DataTable\Map
    {
        $map = new DataTable\Map();
        foreach ($rowsByDate as $date => $rows) {
            $subTable = new DataTable();
            $subTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
            if ($rows !== []) {
                // Disambiguate single-row column map from list-of-rows by checking whether
                // the first value is itself an array.
                $firstValue = reset($rows);
                $isListOfRows = is_array($firstValue);
                $rowSet = $isListOfRows ? $rows : [$rows];
                foreach ($rowSet as $rowColumns) {
                    $subTable->addRowFromArray([DataTable\Row::COLUMNS => $rowColumns]);
                }
            }
            $map->addTable($subTable, $date);
        }
        return $map;
    }
}
