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
use Piwik\DataTable\Row;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Metrics\Formatter;
use Piwik\Period\Factory;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSubPeriodFetcher;
use Piwik\Site;

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
            ['daily' => [], 'monthly' => [], 'earliestDataDate' => null],
            $fetcher->collect([], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectReturnsEmptyForEmptySeriesColumns(): void
    {
        $fetcher = $this->createFetcher();

        self::assertSame(
            ['daily' => [], 'monthly' => [], 'earliestDataDate' => null],
            $fetcher->collect([$this->createDayDataTable('2026-04-10')], $this->createSeriesState([], [], []), 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectReturnsEmptyWhenApiMethodIsMissingOrMalformed(): void
    {
        $fetcher = $this->createFetcher();
        $dataTables = [$this->createDayDataTable('2026-04-10')];

        self::assertSame(
            ['daily' => [], 'monthly' => [], 'earliestDataDate' => null],
            $fetcher->collect($dataTables, $this->createSeriesState(['Visits' => 'nb_visits'], [], []), '', 1, '')
        );
        self::assertSame(
            ['daily' => [], 'monthly' => [], 'earliestDataDate' => null],
            $fetcher->collect($dataTables, $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'NoDotMethod', 1, '')
        );
    }

    public function testCollectReturnsEmptyForInvalidIdSite(): void
    {
        $fetcher = $this->createFetcher();
        $dataTables = [$this->createDayDataTable('2026-04-10')];

        self::assertSame(
            ['daily' => [], 'monthly' => [], 'earliestDataDate' => null],
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
            ['daily' => [], 'monthly' => [], 'earliestDataDate' => null],
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
                'earliestDataDate' => null,
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
        // Monthly window: 2 years back from endDate. Date::subYear truncates to Jan 1.
        self::assertSame('2024-01-01,2026-04-29', $captured[1][1]);
    }

    public function testCollectSkipsMonthlyFetchOnMonthTargetWhenNoUpSeries(): void
    {
        // Monthly fan-out on month target is consumed only by computeMonthOfYearScale (UP-only).
        // When all series are explicitly classified FREE/DOWN, the monthly archive lookup has
        // no consumer and should be skipped. Daily fetch still fires for FREE/DOWN seasonal
        // decomposition (avg-of-daily-rates, min-of-daily-mins).
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = $params['period'];
            return $this->createSampleResultMap([]);
        });

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Bounce Rate' => 'bounce_rate', 'Min BW' => 'min_bandwidth'],
                [],
                [
                    'Bounce Rate' => ForecastMetricClassifier::MONOTONICITY_FREE,
                    'Min BW'      => ForecastMetricClassifier::MONOTONICITY_DOWN,
                ]
            ),
            'VisitsSummary.get',
            1,
            ''
        );

        self::assertSame(['day'], $captured);
    }

    public function testCollectFansOutDailyAndMonthlyForMonthPeriodWhenAtLeastOneSeriesIsUp(): void
    {
        // Mixed graph: at least one UP series → monthly fan-out fires so its MoY scale can
        // engage. Other monotonicities along for the ride share the daily fetch.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = $params['period'];
            return $this->createSampleResultMap([]);
        });

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Visits' => 'nb_visits', 'Bounce Rate' => 'bounce_rate'],
                [],
                [
                    'Visits'      => ForecastMetricClassifier::MONOTONICITY_UP,
                    'Bounce Rate' => ForecastMetricClassifier::MONOTONICITY_FREE,
                ]
            ),
            'VisitsSummary.get',
            1,
            ''
        );

        self::assertSame(['day', 'month'], $captured);
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

    public function testCollectClampsWindowEndToLastCompleteDayForInProgressYear(): void
    {
        // For a mid-year "year" target the last displayed period is the in-progress year, whose
        // calendar end (2026-12-31) is in the future. Left unclamped, the daily window would sit
        // months ahead of "now" (see testCollectFansOutDailyAndMonthlyForYearPeriod, which asserts
        // the unclamped 2026-10-31,2026-12-30). The window must instead anchor to the last complete
        // day in the site's timezone so it samples real recent history. Pin "today" to 2026-07-10
        // (UTC site) so the last complete day is 2026-07-09.
        $originalNow = Date::$now;
        Date::$now = strtotime('2026-07-10 12:00:00 UTC');

        try {
            $captured = [];
            $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
                $captured[] = [$params['period'], $params['date']];
                return $this->createSampleResultMap([]);
            });

            $yearTable = new DataTable();
            $yearTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('year', '2026-01-01'));
            $yearTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $this->createSiteMock('UTC'));

            $fetcher->collect([$yearTable], $this->createSeriesState(['Visits' => 'nb_visits'], [], []), 'VisitsSummary.get', 1, '');

            self::assertCount(2, $captured);
            self::assertSame('day', $captured[0][0]);
            // endDate clamped to 2026-07-09 (today - 1 day); daily start = endDate - 60 days.
            self::assertSame('2026-05-10,2026-07-09', $captured[0][1]);
            self::assertSame('month', $captured[1][0]);
            // 8-year monthly window from the clamped end; Date::subYear truncates to Jan 1.
            self::assertSame('2018-01-01,2026-07-09', $captured[1][1]);
        } finally {
            Date::$now = $originalNow;
        }
    }

    public function testCollectClampsGapDayFetchStartToEarliestDataDate(): void
    {
        // Day target, short display: the gap fetch would naturally reach analogWindowStart
        // (2026-01-29). A site/segment that only has data from 2026-02-15 makes everything
        // before that a guaranteed-empty archive lookup, so the gap request must start at the
        // clamped floor instead -- the displayed range still supplies its own days unchanged.
        $captured = [];
        $fetcher = $this->createFetcher(
            function (string $apiMethod, array $params) use (&$captured) {
                $captured[] = $params['date'];
                return $this->createSampleResultMap(['2026-02-15' => ['nb_visits' => 5.0]]);
            },
            null,
            static function (int $idSite, string $segment): ?string {
                return '2026-02-15';
            }
        );

        // 2026-04-05 .. 2026-04-10, last day INCOMPLETE. endDate = 2026-04-09;
        // analogWindowStart = 2026-01-29; firstDisplayedDate = 2026-04-05; gapEndDate = 2026-04-04.
        $dataTables = $this->buildDailyDisplayRange('2026-04-05', 6, [5]);

        $result = $fetcher->collect(
            $dataTables,
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            42,
            ''
        );

        self::assertSame(['2026-02-15,2026-04-04'], $captured);
        self::assertArrayHasKey('2026-02-15', $result['daily']['Visits']);
    }

    public function testCollectSkipsGapDayFetchWhenEarliestDataDateIsAfterTheGap(): void
    {
        // The site/segment came into existence inside the displayed range (after the gap end),
        // so the entire pre-display gap predates any possible data. No inner request fires; the
        // displayed range alone feeds the analog walk. createFetcher's default stub fails if the
        // inner request is issued.
        $fetcher = $this->createFetcher(
            null,
            null,
            static function (int $idSite, string $segment): ?string {
                return '2026-04-05'; // gapEndDate is 2026-04-04, so the whole gap is excluded
            }
        );

        $dataTables = $this->buildDailyDisplayRange('2026-04-05', 6, [5]);

        $result = $fetcher->collect(
            $dataTables,
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            42,
            ''
        );

        self::assertSame([], $result['monthly']);
        // Only the complete displayed days survive (the INCOMPLETE 2026-04-10 target is absent).
        self::assertSame(
            ['2026-04-05', '2026-04-06', '2026-04-07', '2026-04-08', '2026-04-09'],
            array_keys($result['daily']['Visits'])
        );
    }

    public function testCollectClampsDailyAndMonthlyWindowsToEarliestDataDateForMonthPeriod(): void
    {
        // Month target on a young site/segment (data from 2026-03-10). The daily window would
        // naturally start at 2026-02-28 and the monthly window two years back at 2024-01-01;
        // both are raised to the data floor so neither inner request asks for pre-existence
        // sub-periods.
        $captured = [];
        $fetcher = $this->createFetcher(
            function (string $apiMethod, array $params) use (&$captured) {
                $captured[] = [$params['period'], $params['date']];
                return $this->createSampleResultMap([]);
            },
            null,
            static function (int $idSite, string $segment): ?string {
                return '2026-03-10';
            }
        );

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            1,
            ''
        );

        self::assertSame(
            [
                ['day', '2026-03-10,2026-04-29'],
                ['month', '2026-03-10,2026-04-29'],
            ],
            $captured
        );
    }

    public function testCollectSkipsAllFetchesWhenEarliestDataDateIsAfterThePeriod(): void
    {
        // The site/segment did not exist until after the displayed period ends, so every
        // fan-out window is empty. Both the daily and monthly inner requests are skipped;
        // the default stub fails if either fires.
        $fetcher = $this->createFetcher(
            null,
            null,
            static function (int $idSite, string $segment): ?string {
                return '2026-12-01'; // after the 2026-04-29 endDate
            }
        );

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $result = $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            1,
            ''
        );

        self::assertSame(['daily' => [], 'monthly' => [], 'earliestDataDate' => '2026-12-01'], $result);
    }

    public function testCollectPassesIdSiteAndSegmentToEarliestDataDateResolver(): void
    {
        // The clamp floor is site- and segment-specific, so the resolver must receive both the
        // idSite and the raw segment expression the displayed series was requested with.
        $seen = [];
        $fetcher = $this->createFetcher(
            function () {
                return $this->createSampleResultMap([]);
            },
            null,
            static function (int $idSite, string $segment) use (&$seen): ?string {
                $seen[] = [$idSite, $segment];
                return null;
            }
        );

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(['Visits' => 'nb_visits'], [], ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]),
            'VisitsSummary.get',
            77,
            'countryCode==de'
        );

        self::assertSame([[77, 'countryCode==de']], $seen);
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

        self::assertSame(['daily' => [], 'monthly' => [], 'earliestDataDate' => null], $result);
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

        self::assertSame(['daily' => [], 'monthly' => [], 'earliestDataDate' => null], $result);
    }

    public function testApiGetFanOutBailsToSingleApiGetCallForUnmappedColumn(): void
    {
        // The fan-out only splits when every plotted column maps to exactly one owning Module.get.
        // An unmapped column (0 owners) -- a typo'd, removed, or not-yet-registered metric -- must
        // regress the whole graph to a single API.get fetch rather than fanning out a partial set,
        // and must log a debug line so the silent slow-path regression stays investigable.
        $metadata = [
            ['module' => 'VisitsSummary', 'action' => 'get', 'metrics' => ['nb_visits' => 'Visits']],
        ];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('debug')
            ->with(
                self::stringContains('fan-out disabled'),
                self::callback(static function (array $context): bool {
                    return 'made_up_metric' === ($context['column'] ?? null)
                        && 0 === ($context['ownerCount'] ?? null);
                })
            );

        $captured = [];
        $fetcher = $this->createApiGetFetcher(
            $metadata,
            function (string $apiMethod) use (&$captured) {
                $captured[] = $apiMethod;
                return $this->createSampleResultMap(['2026-04-01' => ['nb_visits' => 70.0]]);
            },
            $logger
        );

        $fetcher->collect(
            $this->buildDailyDisplayRange('2026-04-05', 6, [5]),
            $this->createSeriesState(
                ['Visits' => 'nb_visits', 'Made up' => 'made_up_metric'],
                [],
                [
                    'Visits'  => ForecastMetricClassifier::MONOTONICITY_UP,
                    'Made up' => ForecastMetricClassifier::MONOTONICITY_UP,
                ]
            ),
            'API.get',
            1,
            ''
        );

        // A single API.get fetch fired (the fallback group), never a per-module Module.get fan-out.
        self::assertNotEmpty($captured);
        self::assertSame(['API.get'], array_values(array_unique($captured)));
    }

    public function testApiGetFanOutBailsToSingleApiGetCallForMultiOwnerColumn(): void
    {
        // A column exposed by more than one Module.get report -- e.g. a new plugin registering a
        // metric name that collides with an existing one -- cannot be resolved to a single owner
        // without reproducing API.get's merge precedence, so the fan-out bails to a single API.get
        // fetch and logs the collision. This is the concerning silent-regression case: a common
        // graph flips onto the slow path with no other signal.
        $metadata = [
            ['module' => 'VisitsSummary', 'action' => 'get', 'metrics' => ['nb_visits' => 'Visits']],
            ['module' => 'Actions', 'action' => 'get', 'metrics' => ['nb_visits' => 'Visits (collision)']],
        ];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('debug')
            ->with(
                self::stringContains('fan-out disabled'),
                self::callback(static function (array $context): bool {
                    return 'nb_visits' === ($context['column'] ?? null)
                        && 2 === ($context['ownerCount'] ?? null);
                })
            );

        $captured = [];
        $fetcher = $this->createApiGetFetcher(
            $metadata,
            function (string $apiMethod) use (&$captured) {
                $captured[] = $apiMethod;
                return $this->createSampleResultMap(['2026-04-01' => ['nb_visits' => 70.0]]);
            },
            $logger
        );

        $fetcher->collect(
            $this->buildDailyDisplayRange('2026-04-05', 6, [5]),
            $this->createSeriesState(
                ['Visits' => 'nb_visits'],
                [],
                ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'API.get',
            1,
            ''
        );

        self::assertNotEmpty($captured);
        self::assertSame(['API.get'], array_values(array_unique($captured)));
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

    public function testCollectAppliesNumericFormatterToInnerSubPeriodSamples(): void
    {
        // Reproduce the bandwidth-shape units mismatch that hides behind any ProcessedMetric
        // whose format() is non-identity: the outer evolution chart runs the Numeric
        // formatter over every ProcessedMetric column (raw bytes -> gigabytes, raw quotients
        // -> percent points), but the inner sub-period fetch goes through processRequest()
        // with format_metrics=0 and would otherwise return the same column in raw archive
        // units. The fetcher mirrors the outer formatter on each inner sub-table so the
        // seasonal decomposition stays in a single unit system; without it the inner samples
        // ride into the builder ~10^9 too large and the rendered forecast lands as a
        // bytes-domain integer wearing the chart's 'G' suffix.
        $bandwidthMetric = $this->createBandwidthLikeProcessedMetric();
        $rawBytes = 100000000000; // ~100 GB-per-day in raw bytes (= 93.13 G after /1024^3)

        $fetcher = $this->createFetcher(function () use ($bandwidthMetric, $rawBytes) {
            $map = new DataTable\Map();
            foreach (['2026-02-28', '2026-04-29'] as $date) {
                $sub = new DataTable();
                $sub->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
                // Attach the ProcessedMetric via the table's own metadata so the formatter
                // discovers it without needing a real Report registered for the apiMethod.
                // In production Bandwidth.get's processedMetrics flow into API.get via the
                // *.get-merge in {@see \Piwik\Plugins\API\Reports\Get}, and the formatter
                // finds OverallBandwidth through the report's getProcessedMetricsById().
                $sub->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, [$bandwidthMetric]);
                $sub->addRowFromArray([Row::COLUMNS => ['nb_total_overall_bandwidth' => $rawBytes]]);
                $map->addTable($sub, $date);
            }
            return $map;
        });

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $result = $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Bytes' => 'nb_total_overall_bandwidth'],
                [],
                ['Bytes' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'VisitsSummary.get',
            1,
            ''
        );

        // 100,000,000,000 bytes / 1024^3 ≈ 93.13 with Numeric's precision=2 fixed 'G' unit.
        self::assertSame(
            ['Bytes' => ['2026-02-28' => 93.13, '2026-04-29' => 93.13]],
            $result['daily']
        );
    }

    /**
     * Anonymous {@see ProcessedMetric} that mirrors the bandwidth-overall metric's format
     * surface ({@see \Piwik\Metrics\Formatter::getPrettySizeFromBytes()}) without pulling in
     * a Bandwidth plugin dependency. Numeric pins the size unit at 'G', so the test asserts
     * the exact gigabyte value the production formatter would produce for the same bytes.
     */
    private function createBandwidthLikeProcessedMetric(): ProcessedMetric
    {
        return new class () extends ProcessedMetric {
            public function getName()
            {
                return 'nb_total_overall_bandwidth';
            }

            public function getTranslatedName()
            {
                return 'Bytes transferred overall';
            }

            public function compute(Row $row)
            {
                return $row->getColumn($this->getName());
            }

            public function getDependentMetrics()
            {
                return [$this->getName()];
            }

            public function format($value, Formatter $formatter)
            {
                return $formatter->getPrettySizeFromBytes($value, null, 2);
            }
        };
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

    private function createFetcher(
        ?callable $apiRequestProcessor = null,
        ?LoggerInterface $logger = null,
        ?callable $earliestDataDateResolver = null
    ): ForecastSubPeriodFetcher {
        return new ForecastSubPeriodFetcher(
            $apiRequestProcessor ?? static function () {
                self::fail('Inner API request should not have fired for this case.');
            },
            $logger ?? $this->createMock(LoggerInterface::class),
            // Default to no floor so window-sizing assertions exercise the unclamped windows
            // and unit tests never touch the site/segment lookups the real resolver performs.
            $earliestDataDateResolver ?? static function (): ?string {
                return null;
            }
        );
    }

    /**
     * Fetcher whose report-metadata catalog is stubbed so the API.get column->module resolution
     * (and its bail-to-fallback paths) can be exercised without a live report catalog. Overrides
     * the {@see ForecastSubPeriodFetcher::fetchReportMetadataCatalog()} seam.
     *
     * @param array<int, array<string, mixed>> $reportMetadata
     */
    private function createApiGetFetcher(
        array $reportMetadata,
        callable $apiRequestProcessor,
        LoggerInterface $logger
    ): ForecastSubPeriodFetcher {
        return new class ($reportMetadata, $apiRequestProcessor, $logger) extends ForecastSubPeriodFetcher {
            /** @var array<int, array<string, mixed>> */
            private $reportMetadata;

            public function __construct(array $reportMetadata, callable $apiRequestProcessor, LoggerInterface $logger)
            {
                parent::__construct($apiRequestProcessor, $logger, static function (): ?string {
                    return null;
                });
                $this->reportMetadata = $reportMetadata;
            }

            protected function fetchReportMetadataCatalog(int $idSite, string $period, string $date): array
            {
                return $this->reportMetadata;
            }
        };
    }

    private function createDayDataTable(string $date): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
        return $dataTable;
    }

    private function createSiteMock(string $timezone): Site
    {
        $site = $this->getMockBuilder(Site::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTimezone'])
            ->getMock();

        $site->method('getTimezone')->willReturn($timezone);

        return $site;
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
