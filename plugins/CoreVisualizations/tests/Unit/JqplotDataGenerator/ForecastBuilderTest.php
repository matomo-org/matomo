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
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastBuilder;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Site;
use ReflectionClass;

/**
 * @group CoreVisualizations
 * @group ForecastBuilder
 * @group JqplotDataGenerator
 */
class ForecastBuilderTest extends TestCase
{
    /**
     * @dataProvider getLeadingZeroSamplesTestData
     * @param array<int, float> $samples
     * @param array<int, float> $expected
     */
    public function testRemoveLeadingZeroSamples(array $samples, array $expected): void
    {
        $result = $this->invokePrivateMethod(
            new ForecastBuilder(),
            'removeLeadingZeroSamples',
            [$samples]
        );

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{array<int, float>, array<int, float>}>
     */
    public function getLeadingZeroSamplesTestData(): iterable
    {
        yield 'empty' => [
            [],
            [],
        ];

        yield 'all zeros' => [
            [0.0, 0.0, 0.0],
            [],
        ];

        yield 'leading zeros only are removed' => [
            [0.0, 0.0, 5.0, 0.0, 2.0],
            [5.0, 0.0, 2.0],
        ];

        yield 'first value non zero' => [
            [3.0, 0.0, 1.0],
            [3.0, 0.0, 1.0],
        ];
    }

    /**
     * @dataProvider getForecastVisibilityTestData
     */
    public function testShouldRenderForecastValue(
        float $forecastValue,
        float $currentValue,
        string $monotonicity,
        bool $expected
    ): void {
        $result = $this->invokePrivateMethod(
            new ForecastBuilder(),
            'shouldRenderForecastValue',
            [$forecastValue, $currentValue, $monotonicity]
        );

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{float, float, string, bool}>
     */
    public function getForecastVisibilityTestData(): iterable
    {
        yield 'up, higher than current' => [12.5, 10.0, ForecastMetricClassifier::MONOTONICITY_UP, true];
        yield 'up, equal to current' => [10.0, 10.0, ForecastMetricClassifier::MONOTONICITY_UP, true];
        yield 'up, below current' => [9.99, 10.0, ForecastMetricClassifier::MONOTONICITY_UP, false];
        yield 'free, higher than current' => [12.5, 10.0, ForecastMetricClassifier::MONOTONICITY_FREE, true];
        yield 'free, equal to current' => [10.0, 10.0, ForecastMetricClassifier::MONOTONICITY_FREE, true];
        yield 'free, below current' => [9.99, 10.0, ForecastMetricClassifier::MONOTONICITY_FREE, true];
        yield 'down, higher than current' => [12.5, 10.0, ForecastMetricClassifier::MONOTONICITY_DOWN, false];
        yield 'down, equal to current' => [10.0, 10.0, ForecastMetricClassifier::MONOTONICITY_DOWN, true];
        yield 'down, below current' => [9.99, 10.0, ForecastMetricClassifier::MONOTONICITY_DOWN, true];
    }

    public function testResolveSubPeriodTodayIndexLooksUpAnchorInSiteLocalDate(): void
    {
        // 2026-04-19 13:00 UTC is 2026-04-20 01:00 NZST (Pacific/Auckland is UTC+12 in
        // April). The site-local anchor for the reference instant is 2026-04-20 even
        // though the UTC instant still falls on the 19th; the calendar-aligned lookup
        // must reflect the site's local date, not the process timezone.
        $site = $this->createSiteMock('Pacific/Auckland');
        $dataTable = $this->createDataTableForWeek('2026-04-19', $site, '2026-04-19 13:00:00');

        $dayAnchors = [
            '2026-04-19', '2026-04-20', '2026-04-21', '2026-04-22',
            '2026-04-23', '2026-04-24', '2026-04-25',
        ];

        $index = $this->invokePrivateMethod(
            new ForecastBuilder(),
            'resolveSubPeriodTodayIndex',
            [$dataTable, $dayAnchors, 'Pacific/Auckland']
        );

        self::assertSame(1, $index);
    }

    public function testResolveSubPeriodTodayIndexHandlesDstFallBackDayWithoutSlipping(): void
    {
        // 2026-11-02 07:30 UTC is 2026-11-01 23:30 PST. November 1 is the
        // America/Los_Angeles fall-back day -- 25 wall-clock hours long, so a naive
        // (referenceTs - weekStartTs)/86400 implementation lands on index 1 (Nov 2).
        // The calendar-aligned 'Y-m-d' lookup against the anchor list must return 0,
        // pinning the property the inline comment on resolveSubPeriodTodayIndex calls
        // out as the reason for not using seconds arithmetic. Date::$now is pinned to
        // a far-future instant so the archived metadata stays the minimum reference.
        $originalNow = Date::$now;
        Date::$now = strtotime('2027-01-01 00:00:00 UTC');

        try {
            $site = $this->createSiteMock('America/Los_Angeles');
            $dataTable = $this->createDataTableForWeek('2026-11-01', $site, '2026-11-02 07:30:00');

            $dayAnchors = [
                '2026-11-01', '2026-11-02', '2026-11-03', '2026-11-04',
                '2026-11-05', '2026-11-06', '2026-11-07',
            ];

            $index = $this->invokePrivateMethod(
                new ForecastBuilder(),
                'resolveSubPeriodTodayIndex',
                [$dataTable, $dayAnchors, 'America/Los_Angeles']
            );

            self::assertSame(0, $index);
        } finally {
            Date::$now = $originalNow;
        }
    }

    public function testBuildUsesSameDoWPriorForDayTargetIncompleteTick(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-11', $site),
            $this->createDataTableForDay('2026-04-12', $site),
            $this->createDataTableForDay('2026-04-13', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
            $this->createDataTableForDay('2026-04-18', $site, '2026-04-18 00:00:00'),
        ];

        // Apr 17 (Fri partial, partial=20): same-DOW filter keeps only Apr 10 Fri = 80, so the
        // prior-only forecast is 80. Apr 18 (Sat partial, partial=0): same-DOW filter keeps
        // Apr 11 Sat = 100, so the prior-only forecast is 100. The seasonal-decomposition path
        // does not apply on day targets (no useful sub-period); the displayed partial is no
        // longer multiplied by an elapsed-time fraction.
        $forecastData = $this->buildForecast(
            ['Visits' => [80.0, 100.0, 140.0, 60.0, 20.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, null, 80.0, 100.0]], $forecastData);
    }

    public function testBuildAppliesSuppliedZeroDecimalForecastPrecision(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-11', $site),
            $this->createDataTableForDay('2026-04-12', $site),
            $this->createDataTableForDay('2026-04-13', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [80.0, 100.0, 140.0, 60.0, 20.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            [],
            ['Visits' => 0]
        );

        // Same Apr 17 forecast as testBuildUsesSameDoWPriorForDayTargetIncompleteTick (= 80)
        // but round()'d to integer precision via the per-series forecast precision flag.
        self::assertSame([[null, null, null, null, 80.0]], $forecastData);
    }

    public function testBuildAppliesSuppliedTwoDecimalForecastPrecision(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Actions per visit' => [12.345, 90.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Actions per visit' => false],
            [],
            ['Actions per visit' => ForecastMetricClassifier::MONOTONICITY_FREE],
            ['Actions per visit' => 2]
        );

        self::assertSame([[null, 12.35]], $forecastData);
    }

    public function testBuildPercentSeriesUsesHistoricalPriorWithoutLinearExtrapolation(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        // Percent series: linear elapsed-ratio extrapolation does not apply, so the forecast is
        // the same-weekday historical prior. Without a flag the builder falls back to
        // "percent unit implies non-monotonic", which is what we exercise here.
        $forecastData = $this->buildForecast(
            ['Bounce rate' => [80.0, 20.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce rate' => '%']
        );

        self::assertSame([[null, 80.0]], $forecastData);
    }

    public function testBuildPercentSeriesAllowsForecastBelowCurrentValue(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        // Prior bounce rate (20%) is below the current partial value (80%). For a count series
        // this would be suppressed; for a percent (non-monotonic) series the downward forecast
        // is a meaningful "trending back to the historical average" signal and must be rendered.
        $forecastData = $this->buildForecast(
            ['Bounce rate' => [20.0, 80.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce rate' => '%']
        );

        self::assertSame([[null, 20.0]], $forecastData);
    }

    public function testBuildAverageSeriesAllowsDownwardForecastViaFlag(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        // avg_time_on_page does not carry a percent unit, but its final period value can move
        // below the current partial average. The data generator marks it as allowing a downward
        // forecast via the per-series flag, and the builder must honour that without falling back
        // to count-style suppression.
        $forecastData = $this->buildForecast(
            ['Avg time on page' => [12.0, 90.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Avg time on page' => 's'],
            [],
            ['Avg time on page' => ForecastMetricClassifier::MONOTONICITY_FREE]
        );

        self::assertSame([[null, 12.0]], $forecastData);
    }

    public function testBuildNonMonotonicFallsBackToPreviousForecastWhenNoPriorAvailable(): void
    {
        $site = $this->createSiteMock();

        // Two consecutive incomplete days with no complete history at all: the first incomplete
        // tick has no prior to draw from and must yield null; the second can carry the first
        // forecast forward only once it is set, otherwise both stay null.
        $dataTables = [
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
            $this->createDataTableForDay('2026-04-18', $site, '2026-04-18 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Bounce rate' => [10.0, 30.0]],
            $dataTables,
            [
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce rate' => '%']
        );

        self::assertSame([[null, null]], $forecastData);
    }

    public function testBuildCountSeriesFloorsForecastToCurrentWhenPriorIsBelowCurrent(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-01', $site),
            $this->createDataTableForDay('2026-04-08', $site),
            $this->createDataTableForDay('2026-04-15', $site, '2026-04-15 22:48:00'),
        ];

        // bounce_count is lower-is-better for trend display, but it is still an additive count
        // within the incomplete period, so the final value cannot fall below the current archived
        // count (90). The same-DoW prior is 50 — below current, and not a possible final value.
        // Rather than suppress the in-progress point (which only ever happens late in the period,
        // where current is most of the final value), the builder floors the forecast to current
        // and renders it flat, mirroring the running-max path. The guaranteed lower bound is more
        // useful than a trailing gap.
        $forecastData = $this->buildForecast(
            ['Bounce count' => [50.0, 50.0, 90.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce count' => false],
            [],
            ['Bounce count' => ForecastMetricClassifier::MONOTONICITY_UP]
        );

        self::assertSame([[null, null, 90.0]], $forecastData);
    }

    public function testBuildDoesNotUseSyntheticZeroAsPercentForecastSeed(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-05', $site),
            $this->createDataTableForDay('2026-04-12', $site, '2026-04-12 00:00:00'),
            $this->createDataTableForDay('2026-04-19', $site, '2026-04-19 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Bounce rate' => [69.0, 0.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce rate' => '%'],
            ['Bounce rate' => [true, false, false]]
        );

        self::assertSame([[null, 69.0, 69.0]], $forecastData);
    }

    public function testBuildDoesNotUseUnavailableHistoricalSamples(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-03-27', $site),
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [80.0, 0.0, 100.0, 20.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            ['Visits' => [true, false, true, true]]
        );

        // Apr 3 Fri is flagged unavailable so its 0 sample is dropped. Same-DOW filter keeps
        // Mar 27 Fri = 80 and Apr 10 Fri = 100 — slope 20, intercept 60, damped projection at
        // x = 2 + 0.5 = 2.5 → 110. The prior-only forecast renders 110 directly (the seasonal
        // path does not apply on day targets, the displayed partial is not extrapolated).
        self::assertSame([[null, null, null, 110.0]], $forecastData);
    }

    public function testBuildIncompleteDayTicksRenderTheirOwnSameDoWPrior(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-11', $site),
            $this->createDataTableForDay('2026-04-12', $site),
            $this->createDataTableForDay('2026-04-13', $site),
            $this->createDataTableForDay('2026-04-17', $site, '2026-04-17 12:00:00'),
            $this->createDataTableForDay('2026-04-18', $site, '2026-04-18 00:00:00'),
            $this->createDataTableForDay('2026-04-19', $site, '2026-04-19 00:00:00'),
            $this->createDataTableForDay('2026-04-20', $site, '2026-04-20 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [80.0, 100.0, 140.0, 60.0, 20.0, 0.0, 0.0, 30.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        // Each incomplete tick renders its own same-DoW prior. There is no carry-forward needed
        // and no displayed-partial extrapolation: Apr 17 Fri → Apr 10 Fri = 80, Apr 18 Sat →
        // Apr 11 Sat = 100, Apr 19 Sun → Apr 12 Sun = 140, Apr 20 Mon → Apr 13 Mon = 60. Every
        // forecast satisfies its UP gate because the partial value is below the prior.
        self::assertSame([[null, null, null, null, 80.0, 100.0, 140.0, 60.0]], $forecastData);
    }

    public function testBuildDayTargetUsesDailySamplesPriorWhenSupplied(): void
    {
        $site = $this->createSiteMock();

        // Apr 30 (Thu) → May 3 (Sun) display, partial Sun. Without daily samples the displayed
        // range carries zero same-DoW Sundays, so the existing path has no prior. With four
        // prior Sundays at a flat 50 in $dailySamples, the new day-target fast path collects
        // them as the prior, the trend fit reduces to mean = 50, and the envelope clamp engages
        // at four samples (no movement on a flat history).
        $dataTables = [
            $this->createDataTableForDay('2026-04-30', $site),
            $this->createDataTableForDay('2026-05-01', $site),
            $this->createDataTableForDay('2026-05-02', $site),
            $this->createDataTableForDay('2026-05-03', $site, '2026-05-03 12:00:00'),
        ];

        $dailySamples = [
            '2026-04-05' => 50.0,
            '2026-04-12' => 50.0,
            '2026-04-19' => 50.0,
            '2026-04-26' => 50.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [10.0, 20.0, 30.0, 5.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[null, null, null, 50.0]], $forecastData);
    }

    public function testBuildDayTargetTracksRecentLevelAcrossTrafficLevelShift(): void
    {
        $site = $this->createSiteMock();

        // Sunday May 31 forecast. The same-DoW Sunday history spans a 3.5x traffic level shift:
        // the four oldest Sundays sit at 220 (pre-shift regime), the six recent ones at 62. The
        // damped linear-trend prior over the raw window reads the old high samples as a steep
        // ongoing decline and projects far below the current level (the envelope clamp then only
        // floors it near ~26) -- the reported collapse. stripPreLevelShiftSamples drops the four
        // pre-shift Sundays so the prior fits the current regime and tracks 62.
        $dailySamples = [
            '2026-03-22' => 220.0, '2026-03-29' => 220.0, '2026-04-05' => 220.0, '2026-04-12' => 220.0,
            '2026-04-19' => 62.0,  '2026-04-26' => 62.0,  '2026-05-03' => 62.0,  '2026-05-10' => 62.0,
            '2026-05-17' => 62.0,  '2026-05-24' => 62.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [5.0]],
            [$this->createDataTableForDay('2026-05-31', $site, '2026-05-31 12:00:00')],
            [ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[62.0]], $forecastData);
    }

    public function testBuildDayTargetKeepsGradualTrendAcrossLevelShiftStrip(): void
    {
        $site = $this->createSiteMock();

        // Guard against over-trimming: a gradual same-DoW up-trend (40..76, no sample more than
        // 2x from the recent level) must not be treated as a level shift, so the damped trend is
        // preserved and the forecast continues the climb (36 + 4 * (10 + 0.5) = 78) rather than
        // being flattened onto the recent level.
        $dailySamples = [
            '2026-03-22' => 40.0, '2026-03-29' => 44.0, '2026-04-05' => 48.0, '2026-04-12' => 52.0,
            '2026-04-19' => 56.0, '2026-04-26' => 60.0, '2026-05-03' => 64.0, '2026-05-10' => 68.0,
            '2026-05-17' => 72.0, '2026-05-24' => 76.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [5.0]],
            [$this->createDataTableForDay('2026-05-31', $site, '2026-05-31 12:00:00')],
            [ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[78.0]], $forecastData);
    }

    public function testBuildDayTargetWithSingleSameDoWSampleInDailySamplesReturnsThatSample(): void
    {
        $site = $this->createSiteMock();

        // Only one prior same-DoW (Sunday) entry in $dailySamples. The same-DoW walk collects
        // [75]; computeHistoricalPrior with one sample returns it directly because the trend
        // fit needs at least two points. The envelope clamp also stays off below
        // MIN_SAMPLES_FOR_BOUNDED_RANGE = 4.
        $dataTables = [
            $this->createDataTableForDay('2026-05-03', $site, '2026-05-03 12:00:00'),
        ];

        $dailySamples = [
            '2026-04-26' => 75.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [10.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[75.0]], $forecastData);
    }

    public function testBuildDayTargetDropsSameDoWSamplesBeforeEarliestDataDate(): void
    {
        $site = $this->createSiteMock();

        // Sunday 2026-06-07 forecast on a site created 2026-06-02. Every same-DoW Sunday in the
        // supplied window (05-31, 05-24, 05-17, 05-10) predates creation, so all four are dropped
        // by the earliest-data floor and the day prior has no samples left -> suppressed. Without
        // the floor these pre-creation Sundays would produce a prior, which is exactly the leak
        // that made the forecast flip on once the displayed "rows to display" reached back far
        // enough to include them.
        $dailySamples = [
            '2026-05-10' => 200.0,
            '2026-05-17' => 200.0,
            '2026-05-24' => 200.0,
            '2026-05-31' => 200.0,
        ];

        $arguments = [
            ['Visits' => [5.0]],
            [$this->createDataTableForDay('2026-06-07', $site, '2026-06-07 12:00:00')],
            [ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples],
        ];

        // Without the floor the pre-creation Sundays carry the prior (flat 200).
        self::assertSame([[200.0]], $this->buildForecast(...$arguments));

        // With the floor they are all dropped, leaving no same-DoW history -> no forecast.
        self::assertSame([[null]], $this->buildForecast(...array_merge($arguments, [[], '2026-06-02'])));
    }

    public function testBuildDayTargetWithDailySamplesStripsLeadingZeroForMonotonicUp(): void
    {
        $site = $this->createSiteMock();

        // Three prior Thursdays in $dailySamples; the oldest is 0 (tracking hadn't started yet).
        // The MONOTONICITY_UP path strips the leading zero, leaving [50, 50] → slope 0,
        // intercept 50, projection 50. Without the strip, [0, 50, 50] would project ~71 from
        // the upward slope.
        $dataTables = [
            $this->createDataTableForDay('2026-04-30', $site, '2026-04-30 12:00:00'),
        ];

        $dailySamples = [
            '2026-04-09' => 0.0,
            '2026-04-16' => 50.0,
            '2026-04-23' => 50.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [5.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[50.0]], $forecastData);
    }

    public function testBuildDayTargetWithDailySamplesKeepsLeadingZeroForMonotonicDown(): void
    {
        $site = $this->createSiteMock();

        // Single same-DoW sample of 0 in $dailySamples — a legitimate min observation, not a
        // tracking-not-started zero. The MONOTONICITY_DOWN path keeps leading zeros, so the
        // prior renders 0; the DOWN gate (0 <= current 30) lets the forecast through.
        $dataTables = [
            $this->createDataTableForDay('2026-04-30', $site, '2026-04-30 12:00:00'),
        ];

        $dailySamples = [
            '2026-04-23' => 0.0,
        ];

        $forecastData = $this->buildForecast(
            ['Min event value' => [30.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE],
            ['Min event value' => false],
            [],
            ['Min event value' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min event value' => 0],
            ['Min event value' => $dailySamples]
        );

        self::assertSame([[0.0]], $forecastData);
    }

    public function testBuildDayTargetSharesDailySamplesAcrossMultipleIncompleteTicks(): void
    {
        $site = $this->createSiteMock();

        // Two consecutive incomplete days (Thu Apr 30, Fri May 1) both forecast from the same
        // $dailySamples map. Each tick's walk filters the shared map by its own DoW: Thu picks
        // the Thursday entries (flat 100), Fri picks the Fridays (flat 200). Pins the
        // "fetch-once, reuse-per-tick" contract on the day-target prior path.
        $dataTables = [
            $this->createDataTableForDay('2026-04-30', $site, '2026-04-30 12:00:00'),
            $this->createDataTableForDay('2026-05-01', $site, '2026-05-01 12:00:00'),
        ];

        $dailySamples = [
            '2026-04-09' => 100.0, '2026-04-10' => 200.0,
            '2026-04-16' => 100.0, '2026-04-17' => 200.0,
            '2026-04-23' => 100.0, '2026-04-24' => 200.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [5.0, 10.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE, ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[100.0, 200.0]], $forecastData);
    }

    public function testBuildDayTargetFeedsForwardSameDoWForecastToNextWeekIncompleteTick(): void
    {
        $site = $this->createSiteMock();

        // Two same-DoW incomplete ticks one week apart (Tue Apr 28 and Tue May 5). The first is
        // the "today" tick with a small partial of 100; the second is a future Tuesday with no
        // data archived. The fetched daily-sample map carries the partial under the today
        // anchor (since the parallel sub-period fetch covers the displayed range, including
        // incomplete dates) and zero for the future date — the same shape the production
        // caller produces.
        //
        // Without feed-forward, the second Tuesday's recentSameDoWValues walk picks up the
        // partial 100 at Apr 28 alongside the real 1000s further back; the trend fit lands far
        // below 1000 because the most recent point looks like a hard collapse. The fix folds
        // the first tick's forecast (1000) back into the running daily sample map so the
        // second tick's walk sees a flat history of 1000s and forecasts 1000 again.
        $dataTables = [
            $this->createDataTableForDay('2026-04-28', $site, '2026-04-28 12:00:00'),
            $this->createDataTableForDay('2026-05-05', $site, '2026-05-05 00:00:00'),
        ];

        $dailySamples = [
            '2026-03-31' => 1000.0, // Tue, four weeks back, real complete
            '2026-04-07' => 1000.0, // Tue
            '2026-04-14' => 1000.0, // Tue
            '2026-04-21' => 1000.0, // Tue
            '2026-04-28' => 100.0,  // Tue, today, partial as it would arrive from a sub-period fetch
            '2026-05-05' => 0.0,    // Tue, future tick, empty archive — extractSubPeriodSamples writes 0
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [100.0, 0.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE, ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertNotNull($forecastData[0][0]);
        self::assertNotNull($forecastData[0][1]);
        self::assertSame(1000.0, $forecastData[0][0]);
        // Without the feed-forward fix, the second Tuesday's same-DoW walk picks up Apr 28's
        // partial (100) and the trend fit collapses; the fix keeps the second forecast aligned
        // with the four 1000-sample history.
        self::assertSame(1000.0, $forecastData[0][1]);
    }

    public function testBuildDayTargetFeedsForwardForecastForNonMonotonicSeries(): void
    {
        $site = $this->createSiteMock();

        // Same-DoW feed-forward also has to apply to MONOTONICITY_FREE series (ratios,
        // percentages, averages — bounce_rate is the canonical case the user reported). For a
        // ratio metric the partial value at "today" is doubly problematic: early-hours samples
        // skew the rate (a low-traffic morning that bounces hard reads near 0% engagement, an
        // early session of one engaged visitor reads at the other extreme). Without
        // feed-forward the second Tuesday's recentSameDoWValues walk pulls today's partial
        // alongside the prior Tuesdays, the least-squares fit picks up a strong slope from the
        // outlier and the forecast collapses. With feed-forward today's value in the running
        // map becomes today's forecast, so the second tick's walk sees a flat history.
        //
        // Setup: prior Tuesdays at a stable 35.0 (bounce rate %), today's partial at 5.0
        // (the early-hours skew), next Tuesday's slot at 0.0 (future, fetch returns 0).
        $dataTables = [
            $this->createDataTableForDay('2026-04-28', $site, '2026-04-28 02:00:00'),
            $this->createDataTableForDay('2026-05-05', $site, '2026-05-05 00:00:00'),
        ];

        $dailySamples = [
            '2026-03-31' => 35.0,
            '2026-04-07' => 35.0,
            '2026-04-14' => 35.0,
            '2026-04-21' => 35.0,
            '2026-04-28' => 5.0,
            '2026-05-05' => 0.0,
        ];

        $forecastData = $this->buildForecast(
            ['Bounce rate' => [5.0, 0.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE, ArchiveState::INCOMPLETE],
            ['Bounce rate' => '%'],
            [],
            ['Bounce rate' => ForecastMetricClassifier::MONOTONICITY_FREE],
            [],
            ['Bounce rate' => $dailySamples]
        );

        // Tick 1: walk from Apr 28 picks up [35, 35, 35, 35]. computeHistoricalPrior on a flat
        // history reduces to the mean = 35.
        self::assertSame(35.0, $forecastData[0][0]);
        // Tick 2: with feed-forward, $runningDailySamples['2026-04-28'] = 35. Walk from May 5
        // picks up [35, 35, 35, 35] → 35. Without the fix the walk would see [35, 35, 35, 5]
        // (with today's partial at the most-recent slot) and the trend fit would tilt the
        // projection well below 35.
        self::assertSame(35.0, $forecastData[0][1]);
    }

    public function testBuildDayTargetFeedsForwardForecastForMonotonicDownSeries(): void
    {
        $site = $this->createSiteMock();

        // MONOTONICITY_DOWN running-min series go through the same non-monotonic forecast path
        // as FREE ratios, so the same feed-forward applies. Setup: prior Tuesdays at a flat
        // 50, today's partial at 200 (an early-hours min that has not yet had time to fall),
        // next Tuesday's partial at 100 (mid-day partial high enough that a 50 forecast still
        // satisfies the DOWN render gate of forecast ≤ current).
        //
        // Without feed-forward, next Tuesday's analog walk picks up [50, 50, 50, 200] and the
        // damped least-squares projection lands well above the historical 50; the DOWN gate
        // (forecast ≤ current = 100) then suppresses the rendered forecast. With feed-forward
        // today's value in the running map becomes 50 (today's forecast), the walk sees a flat
        // history at 50 and the rendered forecast is 50.
        $dataTables = [
            $this->createDataTableForDay('2026-04-28', $site, '2026-04-28 02:00:00'),
            $this->createDataTableForDay('2026-05-05', $site, '2026-05-05 09:00:00'),
        ];

        $dailySamples = [
            '2026-03-31' => 50.0,
            '2026-04-07' => 50.0,
            '2026-04-14' => 50.0,
            '2026-04-21' => 50.0,
            '2026-04-28' => 200.0,
            '2026-05-05' => 100.0,
        ];

        $forecastData = $this->buildForecast(
            ['Min bandwidth' => [200.0, 100.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE, ArchiveState::INCOMPLETE],
            ['Min bandwidth' => false],
            [],
            ['Min bandwidth' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            [],
            ['Min bandwidth' => $dailySamples]
        );

        // Tick 1: prior [50, 50, 50, 50] → forecast 50. DOWN gate 50 ≤ 200 passes; the clamp
        // is a no-op. Today's projection in $runningDailySamples becomes 50.
        self::assertSame(50.0, $forecastData[0][0]);
        // Tick 2 with feed-forward: walk sees [50, 50, 50, 50] → forecast 50. Render gate
        // 50 ≤ 100 passes, the rendered forecast is 50. Without feed-forward the walk would
        // see [50, 50, 50, 200] and the trend-aware prior projects above 100, so the DOWN
        // gate would suppress the forecast (null in the unfixed output).
        self::assertSame(50.0, $forecastData[0][1]);
    }

    public function testBuildWeekTargetFeedsForwardDayProjectionsAcrossConsecutiveIncompleteWeeks(): void
    {
        $site = $this->createSiteMock();

        // Two consecutive incomplete weeks (week of Apr 27 with today = Wed Apr 29; week of
        // May 4 fully in the future). The first week's seasonal decomposition projects per-day
        // values for Wed/Thu/Fri/Sat/Sun. The second week's decomposition needs same-DoW
        // analogs for each of its seven days; each walk steps back through the running daily
        // map, so without feed-forward those walks would land on the zeros the parallel fetch
        // produced for the future-dated week-1 days (Thu/Fri/Sat/Sun) and pull the trend down
        // hard. With feed-forward the walk picks up week 1's projections instead.
        //
        // To make the difference observable, week 1's same-DoW projection differs from the
        // historical mean: priors are 100/day flat, but Apr 29 (Wed today) carries a partial
        // floor of 500 that is bigger than the 100 prior, so today's contribution is 500. Week
        // 1 then projects Wed=500 (partial floor), Thu/Fri/Sat/Sun=100 (analog). Week 2's Wed
        // walks back to Apr 29 = 500 (with fix) vs the partial-or-missing case (without fix);
        // the rest of week 2's days walk to flat-100 history.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-29 12:00:00'),
            $this->createDataTableForWeek('2026-05-04', $site, '2026-05-04 00:00:00'),
        ];

        // Eight prior weeks of flat-100/day so every same-DoW analog walk reduces to a mean of
        // 100 with no slope, isolating the fix's effect.
        $dailySamples = $this->buildFlatDailySamples('2026-03-02', 8, 100.0);
        // Week 1's Mon-Tue real archived completed days.
        $dailySamples['2026-04-27'] = 100.0;
        $dailySamples['2026-04-28'] = 100.0;
        // Week 1's Wed (today) carries the partial value the parallel fetch returns.
        $dailySamples['2026-04-29'] = 500.0;
        // Week 1's Thu..Sun are future-dated, so the fetch returns 0. Week 2's Mon..Sun are
        // also fetched and zeroed out for the same reason.
        foreach (['2026-04-30', '2026-05-01', '2026-05-02', '2026-05-03'] as $futureWeek1Day) {
            $dailySamples[$futureWeek1Day] = 0.0;
        }
        foreach (['2026-05-04', '2026-05-05', '2026-05-06', '2026-05-07', '2026-05-08', '2026-05-09', '2026-05-10'] as $futureWeek2Day) {
            $dailySamples[$futureWeek2Day] = 0.0;
        }

        $forecastData = $this->buildForecast(
            // Week 1 currentValue: Mon+Tue real (200) + Wed partial (500) = 700.
            // Week 2 currentValue: 0 (all future).
            ['Visits' => [700.0, 0.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE, ArchiveState::INCOMPLETE],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        // Week 1 forecast: Mon (100) + Tue (100) + Wed today contribution (max of partial 500
        // and prior 100 = 500) + Thu/Fri/Sat/Sun analog priors (100 each) = 200 + 500 + 400 =
        // 1100.
        self::assertSame(1100.0, $forecastData[0][0]);

        // Week 2 forecast with the feed-forward fix: each day's same-DoW walk picks up week
        // 1's projection.
        //   Mon walk → Apr 27 = 100 (real), and 100s further back → 100.
        //   Tue walk → Apr 28 = 100 (real), and 100s further back → 100.
        //   Wed walk → Apr 29 = 500 (week 1 projection), then 100s → analog mean grows; with
        //     the chunk-3 walk it lands on (100 + 100 + 500) / 3 = 233.33.
        //   Thu walk → Apr 30 = 100 (week 1 projection from analog), then 100s → 100.
        //   Fri walk → May 1 = 100, then 100s → 100.
        //   Sat walk → May 2 = 100, then 100s → 100.
        //   Sun walk → May 3 = 100, then 100s → 100.
        // Week 2 = 100 + 100 + 233.3333 + 100 + 100 + 100 + 100 = 833.3333.
        self::assertNotNull($forecastData[0][1]);
        self::assertEqualsWithDelta(833.3333, $forecastData[0][1], 0.01);

        // Pin the property the user reported: with feed-forward the second incomplete week's
        // forecast does not collapse below the flat-100/day baseline (700). Without the fix
        // Wed's walk would see Apr 29 = 500 alongside two zeros (week 1 projections were never
        // folded), the rest of the days would see two zeros each, every day's analog mean
        // would collapse toward 0 and the weekly forecast would land far below 700.
        self::assertGreaterThan(700.0, $forecastData[0][1]);
    }

    public function testBuildMaxSeriesCombinesSubPeriodsWithMaxNotSum(): void
    {
        $site = $this->createSiteMock();

        // Running-max metric (max_actions) on an incomplete week: today = Wed Apr 29, so
        // Mon/Tue are completed and Wed-Sun are analog-projected. The period value of a max
        // metric is the max over its days, never their sum. With eight prior weeks of flat-10
        // same-DoW history every analog projection is 10, and Tue's archived max of 15 is the
        // single high day. A correct MAX decomposition returns max(10, 15, 10, 10, 10, 10, 10)
        // = 15; the pre-fix MONOTONICITY_UP path would SUM the seven days (~75), an order of
        // magnitude too large and the exact shape of the reported max_actions blowup.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-29 12:00:00'),
        ];

        $dailySamples = $this->buildFlatDailySamples('2026-03-02', 8, 10.0);
        $dailySamples['2026-04-27'] = 10.0; // Mon completed
        $dailySamples['2026-04-28'] = 15.0; // Tue completed -- the period max
        // Wed (today) and Thu-Sun are future-dated for the fetch; analog walk supplies 10 each.
        foreach (['2026-04-29', '2026-04-30', '2026-05-01', '2026-05-02', '2026-05-03'] as $day) {
            $dailySamples[$day] = 0.0;
        }

        $forecastData = $this->buildForecast(
            ['Max actions' => [15.0]], // displayed week-to-date max
            $dataTables,
            [ArchiveState::INCOMPLETE],
            ['Max actions' => false],
            [],
            ['Max actions' => ForecastMetricClassifier::MONOTONICITY_MAX],
            [],
            ['Max actions' => $dailySamples]
        );

        self::assertSame(15.0, $forecastData[0][0]);
    }

    public function testBuildSuppressesForecastWhenTwoMostRecentPriorTicksHaveNoData(): void
    {
        $site = $this->createSiteMock();

        // Sustained zero-traffic stretch: weeks of 200 visits, then two consecutive zero-visit
        // weeks, then the incomplete forecast tick. The same-period analog walk would happily
        // reach back over the two zero weeks and project the forecast at the pre-outage level
        // (a "ghost spike" with no observable basis). The trailing-zero gate suppresses any
        // forecast on the incomplete tick because the two complete prior ticks both read as
        // empty.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-06', $site),
            $this->createDataTableForWeek('2026-04-13', $site),
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site),
            $this->createDataTableForWeek('2026-05-04', $site, '2026-05-04 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [200.0, 200.0, 0.0, 0.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, null, null]], $forecastData);
    }

    public function testBuildRendersForecastWhenOnlySingleTrailingZeroTick(): void
    {
        $site = $this->createSiteMock();

        // Single zero-traffic prior tick is too short a pattern to be confident the recent
        // past was empty: the suppression gate requires two consecutive empty ticks. Pin the
        // boundary so a one-week outage blip does not silently kill an otherwise renderable
        // forecast.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-13', $site),
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site),
            $this->createDataTableForWeek('2026-05-04', $site, '2026-05-04 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [200.0, 200.0, 0.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertNotNull($forecastData[0][3]);
    }

    public function testBuildSuppressionTreatsUnavailableTicksAsNoData(): void
    {
        $site = $this->createSiteMock();

        // Bounce rate during a two-week traffic outage: nb_visits is 0, so the bounce_rate
        // column is absent and seriesDataAvailability is false on the two outage weeks. The
        // suppression gate treats availability=false the same as a zero observation, so the
        // forecast is suppressed. Without this branch the bounce_rate prior would draw from
        // the (filtered) historic 50% values and snap the forecast back to a meaningless
        // ratio on a tick where there will be no visits to ratio against.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-06', $site),
            $this->createDataTableForWeek('2026-04-13', $site),
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site),
            $this->createDataTableForWeek('2026-05-04', $site, '2026-05-04 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Bounce rate' => [50.0, 50.0, 0.0, 0.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce rate' => '%'],
            ['Bounce rate' => [true, true, false, false, true]]
        );

        self::assertSame([[null, null, null, null, null]], $forecastData);
    }

    public function testBuildCrossSeriesGateSuppressesRatioWhenCountForecastIsZero(): void
    {
        $site = $this->createSiteMock();

        // Cross-series gate: when the count series (Visits, MONOTONICITY_UP) is suppressed by
        // the trailing-zero rule, dependent ratios (Bounce rate, MONOTONICITY_FREE) on the
        // same tick must also be suppressed even if their own history would otherwise produce
        // a renderable value. This mirrors the user's reported scenario: visits drop to zero,
        // visits forecast is suppressed, but bounce_rate's prior history excludes the
        // unavailable rows and still projects ~50% — a meaningless ratio over zero visits.
        // The gate prevents that.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-06', $site),
            $this->createDataTableForWeek('2026-04-13', $site),
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site),
            $this->createDataTableForWeek('2026-05-04', $site, '2026-05-04 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            [
                'Visits' => [200.0, 200.0, 0.0, 0.0, 0.0],
                // Bounce rate availability=false on the zero-visit weeks so its own history
                // would still pull a 50% prior — only the cross-series gate suppresses it.
                'Bounce rate' => [50.0, 50.0, 0.0, 0.0, 0.0],
            ],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false, 'Bounce rate' => '%'],
            [
                'Visits' => [true, true, true, true, true],
                'Bounce rate' => [true, true, false, false, true],
            ]
        );

        self::assertSame([null, null, null, null, null], $forecastData[0]);
        self::assertSame([null, null, null, null, null], $forecastData[1]);
    }

    public function testBuildCrossSeriesGateLeavesRatioRenderableWhenCountForecastIsPositive(): void
    {
        $site = $this->createSiteMock();

        // Counterpart to the gate-fires test: when the count series renders a positive
        // forecast, the dependent ratio is not gated and renders its own prior. Pins that the
        // cross-series gate is conditional, not blanket.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-13', $site),
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            [
                'Visits' => [200.0, 220.0, 100.0],
                'Bounce rate' => [50.0, 50.0, 30.0],
            ],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false, 'Bounce rate' => '%']
        );

        // Visits forecast renders a value > 0 (count series with two healthy priors), so the
        // gate does not fire and bounce_rate's same-period prior renders its own value.
        self::assertNotNull($forecastData[0][2]);
        self::assertGreaterThan(0.0, $forecastData[0][2]);
        self::assertNotNull($forecastData[1][2]);
    }

    public function testBuildMonotonicReturnsTrendAwarePriorWhenIncompleteTickHasNoData(): void
    {
        $site = $this->createSiteMock();

        // The "today" tick exists in the date range but has no archived data yet — currentValue
        // is 0 and there is no earlier incomplete tick to carry a forecast forward from. The
        // blend would otherwise dilute the historical prior with a meaningless zero; the builder
        // falls back to computeHistoricalPrior. With three same-weekday priors [80, 100, 60] the
        // least-squares fit gives slope = -10, intercept = 100. The damped projection at
        // x = 3 + TREND_DAMPING (0.5) is 100 + (-10) * 3.5 = 65. Apr 3/10/17/24 are all Fridays
        // so the daily weekday filter keeps every prior tick in the sample.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [80.0, 100.0, 60.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, 65.0]], $forecastData);
    }

    public function testBuildAppliesDampedLinearTrendOnMultiSamplePriors(): void
    {
        $site = $this->createSiteMock();

        // Five consecutive Fridays so the daily weekday filter keeps all four priors. Priors
        // [100, 120, 140, 160] form a clean linear trend: slope = 20, intercept = 80, regressed
        // value at x=4 = 160. The damped projection adds TREND_DAMPING * slope to that anchor:
        // 160 + 0.5 * 20 = 170. A flat-mean predictor would have returned 130, so the test pins
        // the trend-aware behaviour on a realistic four-week history.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site),
            $this->createDataTableForDay('2026-05-01', $site, '2026-05-01 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [100.0, 120.0, 140.0, 160.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, null, 170.0]], $forecastData);
    }

    public function testBuildPriorReducesToMeanWhenTrendIsFlat(): void
    {
        $site = $this->createSiteMock();

        // With identical priors the least-squares slope is zero, so the damped projection
        // reduces exactly to the historical mean. This guards against accidental drift the
        // trend formulation might introduce on stable series.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site),
            $this->createDataTableForDay('2026-05-01', $site, '2026-05-01 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [50.0, 50.0, 50.0, 50.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, null, 50.0]], $forecastData);
    }

    public function testBuildClampsNegativeTrendExtrapolationToZero(): void
    {
        $site = $this->createSiteMock();

        // A steeply collapsing prior series produces a damped extrapolation below zero
        // (slope = -32.5, intercept = 122.5, damped projection at x=4.5 = -23.75). Counts and
        // percentages are non-negative by construction, so the forecast clamps to 0 rather
        // than rendering a meaningless negative value on the chart.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site),
            $this->createDataTableForDay('2026-05-01', $site, '2026-05-01 00:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [100.0, 50.0, 10.0, 5.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, null, 0.0]], $forecastData);
    }

    public function testBuildDoesNotCarryForwardAcrossSuppressedForecastGap(): void
    {
        $site = $this->createSiteMock();

        // A running-min (DOWN) series drives the gap. The additive-count below-current case no
        // longer suppresses — it floors to current and renders flat — so the DOWN above-current
        // gate is now the path that produces a genuine suppression with a computed-but-dropped
        // value, which is what this test needs to exercise the no-carry-forward reset.
        $dataTables = [
            $this->createDataTableForDay('2026-04-07', $site),
            $this->createDataTableForDay('2026-04-14', $site),
            $this->createDataTableForDay('2026-04-21', $site, '2026-04-21 02:00:00'),
            $this->createDataTableForDay('2026-04-22', $site, '2026-04-22 02:00:00'),
        ];

        // Apr 21 Tue (partial min 10): same-DoW priors Apr 7 Tue = 100, Apr 14 Tue = 100,
        // prior = 100. The DOWN gate (forecast <= currentValue) fails: 100 > 10, so the forecast
        // is suppressed and previousForecastValue resets to null. Apr 22 Wed has no same-DoW
        // priors (Apr 7 and Apr 14 are both Tue) so the prior-only path yields no forecast of its
        // own — without a previousForecastValue to carry, the result is null. Were the reset not
        // applied, Apr 22 would carry Apr 21's computed-but-suppressed 100. Pins the
        // no-carry-forward semantic across a suppression gap.
        $forecastData = $this->buildForecast(
            ['Min bandwidth' => [100.0, 100.0, 10.0, 10.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min bandwidth' => false],
            [],
            ['Min bandwidth' => ForecastMetricClassifier::MONOTONICITY_DOWN]
        );

        self::assertSame([[null, null, null, null]], $forecastData);
    }

    public function testBuildPrefersCalendarAlignedMonthlySamplesWhenAvailable(): void
    {
        $site = $this->createSiteMock();

        // Two prior years of alternating March/June with very different magnitudes; the partial
        // is March 2025. The seasonal path falls back to the prior-only branch here because no
        // daily/monthly sample maps are supplied, and that branch's calendar-month alignment
        // selects the two Marches [100, 110] (slope 10, intercept 90, damped projection at
        // x = 2.5 → 115). The fallback would otherwise mix the four samples [100, 500, 110,
        // 550] and yield a prior near 500.
        $dataTables = [
            $this->createDataTableForMonth('2023-03-01', $site),
            $this->createDataTableForMonth('2023-06-01', $site),
            $this->createDataTableForMonth('2024-03-01', $site),
            $this->createDataTableForMonth('2024-06-01', $site),
            $this->createDataTableForMonth('2025-03-01', $site, '2025-03-15 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [100.0, 500.0, 110.0, 550.0, 60.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        $forecast = $forecastData[0][4];
        self::assertNotNull($forecast);
        self::assertGreaterThan(105.0, $forecast);
        self::assertLessThan(125.0, $forecast);
    }

    public function testBuildFallsBackToAllSamplesWhenAlignedSamplesBelowThreshold(): void
    {
        $site = $this->createSiteMock();

        // Only March 2024 is calendar-aligned with March 2025; the other priors are January and
        // February 2025. With a single aligned sample the builder must fall back to the full
        // recency window [100, 200, 300] (slope 100, intercept 0, damped projection at x = 3.5
        // → 350), not collapse onto the lone aligned value. The fallback prior pulls the
        // forecast well above 200, while an aligned-only path with prior = 100 would have
        // produced a forecast near 110. Pinning the high outcome guards against the alignment
        // filter degrading to "exclude when not aligned", which would silently discard two
        // thirds of the available history.
        $dataTables = [
            $this->createDataTableForMonth('2024-03-01', $site),
            $this->createDataTableForMonth('2025-01-01', $site),
            $this->createDataTableForMonth('2025-02-01', $site),
            $this->createDataTableForMonth('2025-03-01', $site, '2025-03-15 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [100.0, 200.0, 300.0, 60.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        $forecast = $forecastData[0][3];
        self::assertNotNull($forecast);
        self::assertGreaterThan(200.0, $forecast);
        self::assertLessThanOrEqual(360.0, $forecast);
    }

    public function testBuildPrefersCalendarAlignedWeeklySamplesWhenAvailable(): void
    {
        $site = $this->createSiteMock();

        // Two prior years' worth of "the same ISO week" (week 18) plus a noisy adjacent-week
        // sample. ISO-week alignment selects [200, 220] (slope 20, intercept 180, prior
        // 180+20*2.5 = 230). The unaligned fallback over the three samples [200, 9000, 220]
        // would be dominated by the outlier and produce a wildly different prior.
        $dataTables = [
            $this->createDataTableForWeek('2024-04-29', $site), // ISO 2024-W18
            $this->createDataTableForWeek('2025-04-21', $site), // ISO 2025-W17 (mismatch)
            $this->createDataTableForWeek('2025-04-28', $site), // ISO 2025-W18
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 12:00:00'), // ISO 2026-W18
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [200.0, 9000.0, 220.0, 80.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        $forecast = $forecastData[0][3];
        self::assertNotNull($forecast);
        // The W18 alignment keeps the forecast near the historical W18 trajectory; without
        // alignment the W17 outlier of 9000 would push the prior into the thousands.
        self::assertGreaterThan(180.0, $forecast);
        self::assertLessThan(280.0, $forecast);
    }

    public function testBuildMonotonicDownSeriesAllowsForecastBelowCurrentValue(): void
    {
        $site = $this->createSiteMock();

        // Three same-weekday priors of 5 form a flat history. The current partial min is 12,
        // which is above every historical final-period min: a min metric routed through the
        // monotonic-down path must render the prior (5), proving the gate flip from
        // "forecast >= current" to "forecast <= current" let the legitimate downward forecast
        // through. The legacy boolean gate would have suppressed this entirely.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Min event value' => [5.0, 5.0, 5.0, 12.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min event value' => false],
            [],
            ['Min event value' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min event value' => 0]
        );

        self::assertSame([[null, null, null, 5.0]], $forecastData);
    }

    public function testBuildMonotonicDownSeriesClampsForecastAboveCurrentValueToCurrent(): void
    {
        $site = $this->createSiteMock();

        // Three same-weekday priors of 50 form a flat history. The current partial min is 8 —
        // an early outlier the period has already locked in. The historical prior (50) cannot
        // describe a defensible final value, since the running min cannot rise above 8. The
        // gate fails (50 > 8) and the forecast is suppressed; without the gate flip the
        // monotonic-up path would have rendered ~50 as a wrong-direction forecast.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Min event value' => [50.0, 50.0, 50.0, 8.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min event value' => false],
            [],
            ['Min event value' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min event value' => 0]
        );

        self::assertSame([[null, null, null, null]], $forecastData);
    }

    public function testBuildMaxSeriesFloorsForecastBelowCurrentUpToCurrent(): void
    {
        $site = $this->createSiteMock();

        // The reported scenario: historical same-period maxes sit flat at 11 while the
        // in-progress period has already observed a higher max of 15. A running max cannot
        // fall back below what has already been seen, so the forecast must render flat at 15.
        // Before the max-floor the prior (11) failed the "forecast >= current" gate (11 < 15)
        // and the point was suppressed -- the forecast went missing from the chart entirely,
        // which is what the bug report described. The floor mirrors the min_* clamp but
        // upward: "below current" is a no-further-growth outcome to render at current, not an
        // impossible value to drop.
        $dataTables = [
            $this->createDataTableForDay('2026-04-03', $site),
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Max actions' => [11.0, 11.0, 11.0, 15.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Max actions' => false],
            [],
            ['Max actions' => ForecastMetricClassifier::MONOTONICITY_MAX],
            ['Max actions' => 0]
        );

        self::assertSame([[null, null, null, 15.0]], $forecastData);
    }

    public function testBuildMaxSeriesClampsRunawayPriorToHistoricalRange(): void
    {
        $site = $this->createSiteMock();

        // A running-max series reaches the same damped linear-trend prior-only fallback as an
        // additive count, so a spike in the same-DoW history extrapolates into a runaway just as
        // it would for MONOTONICITY_UP. max_actions is a user-selectable metric on the Visits
        // Overview evolution graph, so this path is one click away on a common page. The envelope
        // clamp must therefore engage for MAX exactly as it does for UP: four prior Sundays at
        // [100, 105, 95, 400] fit a steep trend whose damped projection is 353, which the
        // historical-range envelope (median 102.5, MAD-sized) reins to its upper bound 124.739,
        // rounded to 125. Without the MAX arm on the clamp gate the chart renders the unclamped
        // 353 instead.
        $dataTables = [
            $this->createDataTableForDay('2026-04-30', $site),
            $this->createDataTableForDay('2026-05-01', $site),
            $this->createDataTableForDay('2026-05-02', $site),
            $this->createDataTableForDay('2026-05-03', $site, '2026-05-03 12:00:00'),
        ];

        $dailySamples = [
            '2026-04-05' => 100.0,
            '2026-04-12' => 105.0,
            '2026-04-19' => 95.0,
            '2026-04-26' => 400.0,
        ];

        $forecastData = $this->buildForecast(
            ['Max actions' => [10.0, 20.0, 30.0, 15.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Max actions' => false],
            [],
            ['Max actions' => ForecastMetricClassifier::MONOTONICITY_MAX],
            ['Max actions' => 0],
            ['Max actions' => $dailySamples]
        );

        self::assertSame([[null, null, null, 125.0]], $forecastData);
    }

    public function testBuildMonotonicDownSeriesSkipsLinearExtrapolationOfPartialValue(): void
    {
        $site = $this->createSiteMock();

        // The current partial min (10) is above the only same-weekday prior (4). The
        // monotonic-up path would compute linear = 10 / 0.5 = 20 and blend it with the prior,
        // landing far above 10 — wrong-direction for a min. The monotonic-down path skips
        // linear extrapolation and renders the prior (4) directly, satisfying the
        // forecast <= current gate.
        $dataTables = [
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Min bandwidth' => [4.0, 10.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min bandwidth' => false],
            [],
            ['Min bandwidth' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min bandwidth' => 2]
        );

        self::assertSame([[null, 4.0]], $forecastData);
    }

    public function testBuildMonotonicDownSeriesKeepsLeadingZeroFromHistory(): void
    {
        $site = $this->createSiteMock();

        // The single same-weekday historical sample is a legitimate min of 0 (e.g. a
        // min_event_value where one observation in the period was 0). The legacy strip
        // would discard it, leaving the prior empty and forcing the builder to suppress
        // the forecast. With the strip scoped to MONOTONICITY_UP, the prior is 0 and the
        // monotonic-down gate (forecast <= current 30) is satisfied, so the forecast
        // renders the historically defensible value.
        $dataTables = [
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Min event value' => [0.0, 30.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min event value' => false],
            [],
            ['Min event value' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min event value' => 0]
        );

        self::assertSame([[null, 0.0]], $forecastData);
    }

    public function testBuildPercentSeriesKeepsLeadingZeroFromHistory(): void
    {
        $site = $this->createSiteMock();

        // The single same-weekday historical sample is a legitimate 0% (e.g. a bounce_rate
        // of 0% on a low-traffic day with a single bounceless visit). For a non-monotonic
        // series the legacy strip would discard it and the builder would have no prior to
        // draw from, suppressing the forecast on a metric where 0 is a perfectly valid
        // value. The strip is now skipped on FREE so the prior is 0 and the forecast
        // renders 0 — anchored to the only same-period observation we have.
        $dataTables = [
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site, '2026-04-24 12:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Bounce rate' => [0.0, 50.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce rate' => '%']
        );

        self::assertSame([[null, 0.0]], $forecastData);
    }

    public function testBuildWeekSeasonalDecompositionUsesCompletedDaysAndDoWAnalogs(): void
    {
        $site = $this->createSiteMock();

        // Target week 2026-04-27..2026-05-03 (Mon-Sun). Archived Thu 2026-04-30 23:00 → today
        // index 3 (Thursday). Mon-Wed of the target week are fed via the daily sample map as
        // "completed real". The same-DoW analog set comes from prior weeks' days, all stable
        // values keyed off DoW so the prior trend reduces to a flat mean.
        //
        // Completed real (Mon-Wed): 100 + 110 + 90 = 300.
        // Today (Thu) prior: mean of [120, 120, 120] = 120 (≥ partial floor 50, so 120).
        // Remaining (Fri/Sat/Sun) priors: 80, 60, 70.
        // Forecast = 300 + 120 + 80 + 60 + 70 = 630.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 23:00:00'),
        ];

        $dailySamples = [
            // 3 prior weeks of stable per-DoW data so the day-level prior collapses to mean.
            '2026-04-06' => 100.0, '2026-04-07' => 110.0, '2026-04-08' => 90.0,
            '2026-04-09' => 120.0, '2026-04-10' => 80.0,  '2026-04-11' => 60.0,  '2026-04-12' => 70.0,
            '2026-04-13' => 100.0, '2026-04-14' => 110.0, '2026-04-15' => 90.0,
            '2026-04-16' => 120.0, '2026-04-17' => 80.0,  '2026-04-18' => 60.0,  '2026-04-19' => 70.0,
            '2026-04-20' => 100.0, '2026-04-21' => 110.0, '2026-04-22' => 90.0,
            '2026-04-23' => 120.0, '2026-04-24' => 80.0,  '2026-04-25' => 60.0,  '2026-04-26' => 70.0,
            // Target week's Mon-Wed are real archived values that contribute to "completed".
            '2026-04-27' => 100.0, '2026-04-28' => 110.0, '2026-04-29' => 90.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [630.0, 350.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[null, 630.0]], $forecastData);
    }

    public function testBuildWeekSeasonalForecastFloorsAtCurrentPartialNotElapsedExtrapolation(): void
    {
        $site = $this->createSiteMock();

        // Same setup as above but the partial today (Thu) is huge (200) — well above the same-
        // DoW prior of 120. The today contribution is therefore the partial floor (200), not an
        // elapsed-time multiplication of it; the displayed-data extrapolation pathway is gone.
        // Forecast = 300 (Mon-Wed real) + max(partial 200 - completed 300, 120) = the partial
        // formulation is `max(snapshotValue - completed_real, prior)`. Snapshot total = 600
        // (300 completed + 200 today + remainder_irrelevant). max(600 - 300, 120) = 300. Then
        // remaining priors 80 + 60 + 70 = 210. Total = 300 + 300 + 210 = 810.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 23:00:00'),
        ];

        $dailySamples = [
            '2026-04-06' => 100.0, '2026-04-07' => 110.0, '2026-04-08' => 90.0,
            '2026-04-09' => 120.0, '2026-04-10' => 80.0,  '2026-04-11' => 60.0,  '2026-04-12' => 70.0,
            '2026-04-13' => 100.0, '2026-04-14' => 110.0, '2026-04-15' => 90.0,
            '2026-04-16' => 120.0, '2026-04-17' => 80.0,  '2026-04-18' => 60.0,  '2026-04-19' => 70.0,
            '2026-04-20' => 100.0, '2026-04-21' => 110.0, '2026-04-22' => 90.0,
            '2026-04-23' => 120.0, '2026-04-24' => 80.0,  '2026-04-25' => 60.0,  '2026-04-26' => 70.0,
            '2026-04-27' => 100.0, '2026-04-28' => 110.0, '2026-04-29' => 90.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [630.0, 600.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[null, 810.0]], $forecastData);
    }

    public function testBuildMonthSeasonalDecompositionUsesCompletedDaysAndDoWAnalogs(): void
    {
        $site = $this->createSiteMock();

        // Target month 2026-04 (30 days). Apr 1, 2026 is a Wednesday. Archive at 2026-04-04
        // 12:00:00 → today is index 3 (Saturday). Apr 1-3 (Wed/Thu/Fri) are completed real;
        // Apr 4 (Sat) is today; Apr 5-30 are remaining priors. Eight prior weeks of identical
        // per-DoW values feed the same-DoW analog walk so each remaining-day prior collapses
        // to its DoW's value (Mon=100, Tue=110, Wed=90, Thu=120, Fri=80, Sat=60, Sun=70).
        // Empty $monthlySamples short-circuits the MoY scale to 1.0.
        //
        // Completed real (Apr 1-3): 90 + 120 + 80 = 290.
        // Today (Apr 4 Sat) prior: 60. Partial = max(0, 350-290) = 60. Today contribution = 60.
        // Remaining (Apr 5-30, 26 days):
        //   Sun×4 + Mon×4 + Tue×4 + Wed×4 + Thu×4 + Fri×3 + Sat×3
        //   = 4*70 + 4*100 + 4*110 + 4*90 + 4*120 + 3*80 + 3*60 = 2380.
        // Forecast = 290 + 60 + 2380 = 2730.
        $dataTables = [
            $this->createDataTableForMonth('2026-03-01', $site),
            $this->createDataTableForMonth('2026-04-01', $site, '2026-04-04 12:00:00'),
        ];

        $dailySamples = $this->buildUniformDoWDailySamples('2026-02-02', 8);
        $dailySamples['2026-04-01'] = 90.0;  // Wed
        $dailySamples['2026-04-02'] = 120.0; // Thu
        $dailySamples['2026-04-03'] = 80.0;  // Fri

        $forecastData = $this->buildForecast(
            ['Visits' => [2730.0, 350.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples]
        );

        self::assertSame([[null, 2730.0]], $forecastData);
    }

    public function testBuildMonthSeasonalUpAppliesNonTrivialMonthOfYearScale(): void
    {
        $site = $this->createSiteMock();

        // Companion to testBuildMonthSeasonalDecompositionUsesCompletedDaysAndDoWAnalogs, which
        // leaves $monthlySamples empty so computeMonthOfYearScale() short-circuits to 1.0. Here
        // we feed it a non-empty same-month-of-year (April) history so the scale is genuinely
        // != 1.0 and multiplies the analog daily projections in the UP month decomposition.
        //
        // Target month 2026-04 (30 days). Apr 1 2026 is a Wednesday; archive at 2026-04-04
        // 12:00:00 -> today is index 3 (Apr 4). Apr 1-3 are completed real; Apr 4 is today;
        // Apr 5-30 (26 days) are remaining analog-projected days.
        //
        // Daily samples are a flat 1000.0 across Jan 1 - Apr 30 2026 (120 days), so:
        //   - every completed real day (Apr 1-3) contributes 1000 -> completedReal = 3000
        //   - every same-DoW analog mean is 1000 (n=4 < MIN_SAMPLES_FOR_DAY_LEVEL_TREND -> mean)
        //
        // Month-of-year scale (computeMonthOfYearScale, analogChunk = MONTH_ANALOG_CHUNK = 4):
        //   - recentSameMoYValues('2026-04', 4) -> [2022-04, 2023-04, 2024-04, 2025-04], all 60875
        //   - numer = computeHistoricalPrior([60875 x 4]) = 60875 (flat fit, slope 0)
        //   - denom = (sum/count of daily samples) * 30.4375 = 1000 * 30.4375 = 30437.5
        //   - scale = 60875 / 30437.5 = 2.0 (exact)
        //
        // decomposeAndForecast with scale 2.0:
        //   - completedReal (Apr 1-3, unscaled)           = 3 * 1000            = 3000
        //   - today (Apr 4): partial = max(0, 3000 - 3000) = 0; prior = 1000 * 2 = 2000
        //     -> contribution = max(0, 2000)                                    = 2000
        //   - remaining (Apr 5-30, 26 days): each 1000 * 2 = 2000 -> 26 * 2000  = 52000
        //   Forecast = 3000 + 2000 + 52000 = 57000.
        //
        // With the scale suppressed to 1.0 (empty $monthlySamples) the same setup yields
        // 3000 + 1000 + 26000 = 30000, so 57000 confirms the scale is both non-trivial and
        // applied to the rendered forecast.
        $dataTables = [
            $this->createDataTableForMonth('2026-03-01', $site),
            $this->createDataTableForMonth('2026-04-01', $site, '2026-04-04 12:00:00'),
        ];

        $dailySamples = $this->buildUniformDailySamples('2026-01-01', 120, 1000.0);

        $monthlySamples = [
            '2022-04' => 60875.0,
            '2023-04' => 60875.0,
            '2024-04' => 60875.0,
            '2025-04' => 60875.0,
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [30000.0, 3000.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP],
            [],
            ['Visits' => $dailySamples],
            ['Visits' => $monthlySamples]
        );

        self::assertSame([[null, 57000.0]], $forecastData);
    }

    public function testBuildYearSeasonalDecompositionUsesCompletedMonthsAndMoYAnalogs(): void
    {
        $site = $this->createSiteMock();

        // Target year 2026 (12 months). Archive at 2026-04-04 12:00:00 → todayMonthIdx = 3
        // (April). Months 0..2 (Jan/Feb/Mar) are completed real from $monthlySamples; month 3
        // (April) is forecast via the recursive forecastMonthSeasonal path; months 4..11 are
        // projected from same-MoY analog walks across $monthlySamples.
        //
        // Setup:
        //   - Every prior-year month (2022..2025, all 12 each) = 1000 in $monthlySamples.
        //   - 2026 Jan/Feb/Mar = 1000 each in $monthlySamples (the 'completed real' bucket).
        //   - April daily samples reproduce a per-month total of 1000 via the month
        //     decomposition (same shape as the month test above, scaled to 1000-total).
        //
        // The recursion makes April's currentMonthEstimate = 1000, and each remaining month's
        // same-MoY analog walk yields prior = 1000. Year forecast = 3*1000 (completed) + 1000
        // (April) + 8*1000 (remaining May..Dec) = 12000.
        $dataTables = [
            $this->createDataTableForPeriod('year', '2025-01-01', $site),
            $this->createDataTableForPeriod('year', '2026-01-01', $site, '2026-04-04 12:00:00'),
        ];

        // Per-DoW scale where one full week (Mon-Sun) sums to 1000/30 days × 7 days ≈ 233.33;
        // tune by-the-day so the April per-DoW analog mean × remaining-day pattern + completed
        // + today reproduces 1000. Mon=100/3, Tue=110/3, Wed=90/3, Thu=120/3, Fri=80/3, Sat=60/3,
        // Sun=70/3 yields the same 2730/3 = 910 forecast for April; we use the simpler 2730 ÷
        // April's 30-day total proportion directly here. Use the same numerical values as the
        // month test but scale all daily values by 1000/2730 so month forecast → 1000.
        $scale = 1000.0 / 2730.0;
        $dailySamples = $this->buildUniformDoWDailySamples('2026-02-02', 8, $scale);
        $dailySamples['2026-04-01'] = 90.0 * $scale;  // Wed
        $dailySamples['2026-04-02'] = 120.0 * $scale; // Thu
        $dailySamples['2026-04-03'] = 80.0 * $scale;  // Fri

        $monthlySamples = [];
        for ($year = 2022; $year <= 2025; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $monthlySamples[sprintf('%04d-%02d', $year, $month)] = 1000.0;
            }
        }
        $monthlySamples['2026-01'] = 1000.0;
        $monthlySamples['2026-02'] = 1000.0;
        $monthlySamples['2026-03'] = 1000.0;

        $forecastData = $this->buildForecast(
            // Year-level currentValue: 3 completed months at 1000 each + April partial
            // (currentMonthPartial = 290*scale ≈ 106.23). currentValue = 3000 + 106.23 ≈ 3106.23.
            ['Visits' => [12000.0, 3000.0 + 290.0 * $scale]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false],
            [],
            [],
            [],
            ['Visits' => $dailySamples],
            ['Visits' => $monthlySamples]
        );

        // Allow a small delta because the daily-sample scale × per-DoW arithmetic does not
        // round to exactly 1000 for April; the seasonal path engages and the forecast lands
        // close to the 12000 fully-uniform projection.
        $forecast = $forecastData[0][1];
        self::assertNotNull($forecast);
        self::assertEqualsWithDelta(12000.0, $forecast, 25.0);
    }

    public function testBuildSeasonalPathIsSkippedWhenNoDailySamplesProvided(): void
    {
        $site = $this->createSiteMock();

        // Without daily samples the caller cannot decompose the week, and the builder falls
        // back to the prior-only path on the period-level series. Two prior weekly totals at
        // 500 produce a flat trend prior of 500 (the calendar-aligned ISO-week filter degrades
        // to the all-samples set since there is only one same-week observation in two prior
        // years). The current partial of 100 fails the calendar-aligned filter for the W18
        // alignment, so the test pins the qualitative "fallback-to-prior" outcome rather than a
        // specific number.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-13', $site),
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 23:00:00'),
        ];

        $forecastData = $this->buildForecast(
            ['Visits' => [500.0, 500.0, 100.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        $forecast = $forecastData[0][2];
        self::assertNotNull($forecast);
        self::assertEqualsWithDelta(500.0, $forecast, 1.0);
    }

    public function testBuildWeekSeasonalFreeUsesAverageOfDailyRates(): void
    {
        $site = $this->createSiteMock();

        // Target week 2026-04-27..05-03 (Mon-Sun). Archive at 2026-04-30 23:00 → todayIdx=3 (Thu).
        // Mon-Wed completed days come from $dailySamples as archived daily rates. Thu-Sun are
        // analog-projected from prior weeks (3 same-DoW analogs each). All sample values are
        // identical to keep the day-level prior at the plain mean (n<5 → mean fallback) and
        // to make the expected average trivial to compute.
        //
        // Day values used: Mon=50, Tue=52, Wed=48, Thu=51, Fri=49, Sat=53, Sun=47.
        // Expected: (50+52+48+51+49+53+47) / 7 = 350 / 7 = 50.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 23:00:00'),
        ];

        $rates = [50.0, 52.0, 48.0, 51.0, 49.0, 53.0, 47.0]; // Mon..Sun
        $dailySamples = [];
        $cursor = \Piwik\Date::factory('2026-04-06');
        for ($w = 0; $w < 3; ++$w) {
            for ($d = 0; $d < 7; ++$d) {
                $dailySamples[$cursor->toString('Y-m-d')] = $rates[$d];
                $cursor = $cursor->addDay(1);
            }
        }
        // Completed Mon-Wed of the target week need to be present in the daily map too.
        $dailySamples['2026-04-27'] = $rates[0];
        $dailySamples['2026-04-28'] = $rates[1];
        $dailySamples['2026-04-29'] = $rates[2];

        $forecastData = $this->buildForecast(
            ['Bounce Rate' => [50.0, 49.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Bounce Rate' => false],
            [],
            ['Bounce Rate' => ForecastMetricClassifier::MONOTONICITY_FREE],
            ['Bounce Rate' => 2],
            ['Bounce Rate' => $dailySamples]
        );

        self::assertSame([[null, 50.0]], $forecastData);
    }

    public function testBuildWeekSeasonalDownUsesMinOfDailyMins(): void
    {
        $site = $this->createSiteMock();

        // Same setup as the FREE case but with MIN reducer. Day values: Mon=50, Tue=52, Wed=48,
        // Thu=51, Fri=49, Sat=53, Sun=47. min(50,52,48,51,49,53,47) = 47 (Sun analog).
        // Belt-and-braces clamp: forecast = min(47, currentValue=200) → 47.
        $dataTables = [
            $this->createDataTableForWeek('2026-04-20', $site),
            $this->createDataTableForWeek('2026-04-27', $site, '2026-04-30 23:00:00'),
        ];

        $rates = [50.0, 52.0, 48.0, 51.0, 49.0, 53.0, 47.0];
        $dailySamples = [];
        $cursor = \Piwik\Date::factory('2026-04-06');
        for ($w = 0; $w < 3; ++$w) {
            for ($d = 0; $d < 7; ++$d) {
                $dailySamples[$cursor->toString('Y-m-d')] = $rates[$d];
                $cursor = $cursor->addDay(1);
            }
        }
        $dailySamples['2026-04-27'] = $rates[0];
        $dailySamples['2026-04-28'] = $rates[1];
        $dailySamples['2026-04-29'] = $rates[2];

        $forecastData = $this->buildForecast(
            ['Min BW' => [50.0, 200.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Min BW' => false],
            [],
            ['Min BW' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min BW' => 0],
            ['Min BW' => $dailySamples]
        );

        self::assertSame([[null, 47.0]], $forecastData);
    }

    public function testBuildMonthSeasonalFreeSkipsMonthOfYearScaling(): void
    {
        $site = $this->createSiteMock();

        // Target month 2026-04 (30 days). Apr 1 = Wed. Archive at 2026-04-04 12:00 → todayIdx=3
        // (Sat). Apr 1-3 completed (Wed/Thu/Fri); Apr 4-30 analog-projected. With a uniform daily
        // rate of 60 across the entire fetched window, FREE's avg-of-daily-rates = 60 regardless
        // of any MoY-scale value. The presence of a non-trivial monthlySamples set would have
        // perturbed the UP path via the MoY denominator; FREE ignores it, which is what we pin.
        $dataTables = [
            $this->createDataTableForMonth('2026-03', $site),
            $this->createDataTableForMonth('2026-04', $site, '2026-04-04 12:00:00'),
        ];

        $dailySamples = $this->buildFlatDailySamples('2026-02-02', 13, 60.0);

        // Monthly samples that would have scaled the UP path drastically. FREE should ignore.
        $monthlySamples = [
            '2025-01' => 1000.0, '2025-02' => 2000.0, '2025-03' => 3000.0, '2025-04' => 4000.0,
            '2024-01' => 1000.0, '2024-02' => 2000.0, '2024-03' => 3000.0, '2024-04' => 4000.0,
        ];

        $forecastData = $this->buildForecast(
            ['Bounce Rate' => [55.0, 60.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Bounce Rate' => false],
            [],
            ['Bounce Rate' => ForecastMetricClassifier::MONOTONICITY_FREE],
            ['Bounce Rate' => 2],
            ['Bounce Rate' => $dailySamples],
            ['Bounce Rate' => $monthlySamples]
        );

        self::assertSame([[null, 60.0]], $forecastData);
    }

    public function testBuildYearSeasonalFreeUsesAverageOfMonthlyRates(): void
    {
        $site = $this->createSiteMock();

        // Target year 2026, archive at 2026-04-04 12:00 → todayMonthIdx = 3 (April). Per-month
        // monthly rates in $monthlySamples are constant 50 across prior years (2022-2025, all 12
        // months) and 2026 Jan/Feb/Mar. April daily rates are also 50, so the recursive month
        // decomposition for April returns 50 (avg of daily rates). Each remaining month's
        // same-MoY analog walk produces 50. avg of all 12 monthly values = 50.
        $dataTables = [
            $this->createDataTableForPeriod('year', '2025-01-01', $site),
            $this->createDataTableForPeriod('year', '2026-01-01', $site, '2026-04-04 12:00:00'),
        ];

        $dailySamples = $this->buildFlatDailySamples('2026-02-02', 8, 50.0);
        // Completed days of April that the recursion needs (Apr 1-3) before todayIdx = 3.
        $dailySamples['2026-04-01'] = 50.0;
        $dailySamples['2026-04-02'] = 50.0;
        $dailySamples['2026-04-03'] = 50.0;

        $monthlySamples = [];
        for ($year = 2022; $year <= 2025; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $monthlySamples[sprintf('%04d-%02d', $year, $month)] = 50.0;
            }
        }
        $monthlySamples['2026-01'] = 50.0;
        $monthlySamples['2026-02'] = 50.0;
        $monthlySamples['2026-03'] = 50.0;

        $forecastData = $this->buildForecast(
            ['Bounce Rate' => [50.0, 50.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Bounce Rate' => false],
            [],
            ['Bounce Rate' => ForecastMetricClassifier::MONOTONICITY_FREE],
            ['Bounce Rate' => 2],
            ['Bounce Rate' => $dailySamples],
            ['Bounce Rate' => $monthlySamples]
        );

        self::assertSame([[null, 50.0]], $forecastData);
    }

    public function testBuildYearSeasonalDownUsesMinOfMonthlyMins(): void
    {
        $site = $this->createSiteMock();

        // Year 2026, archive Apr 4. todayMonthIdx = 3 (April). Same shape as the FREE case but
        // 2026 March's monthly min is 30 while every other monthly value (prior years + 2026
        // Jan/Feb + April daily rates + same-MoY analogs for May-Dec) is 100. The min reducer
        // surfaces 30 across all 12 months. Belt-and-braces clamp at the call site holds the
        // rendered forecast at min(30, currentValue=200) → 30.
        $dataTables = [
            $this->createDataTableForPeriod('year', '2025-01-01', $site),
            $this->createDataTableForPeriod('year', '2026-01-01', $site, '2026-04-04 12:00:00'),
        ];

        $dailySamples = $this->buildFlatDailySamples('2026-02-02', 8, 100.0);
        $dailySamples['2026-04-01'] = 100.0;
        $dailySamples['2026-04-02'] = 100.0;
        $dailySamples['2026-04-03'] = 100.0;

        $monthlySamples = [];
        for ($year = 2022; $year <= 2025; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $monthlySamples[sprintf('%04d-%02d', $year, $month)] = 100.0;
            }
        }
        $monthlySamples['2026-01'] = 100.0;
        $monthlySamples['2026-02'] = 100.0;
        $monthlySamples['2026-03'] = 30.0;

        $forecastData = $this->buildForecast(
            ['Min BW' => [100.0, 200.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Min BW' => false],
            [],
            ['Min BW' => ForecastMetricClassifier::MONOTONICITY_DOWN],
            ['Min BW' => 0],
            ['Min BW' => $dailySamples],
            ['Min BW' => $monthlySamples]
        );

        self::assertSame([[null, 30.0]], $forecastData);
    }

    public function testBuildYearSeasonalFreeFallsBackToSameMoYAnalogWhenNoDailySamples(): void
    {
        $site = $this->createSiteMock();

        // Same setup as the FREE-year test but no daily samples supplied. The April recursion
        // into forecastMonthSeasonal returns null because the month seasonal requires daily
        // samples; the year-level FREE path falls back to the same-MoY analog mean over past
        // Aprils for the current-month value. All same-MoY analogs are 50 here, so the
        // fallback path produces a per-month value of 50 for April, and avg = 50 overall.
        $dataTables = [
            $this->createDataTableForPeriod('year', '2025-01-01', $site),
            $this->createDataTableForPeriod('year', '2026-01-01', $site, '2026-04-04 12:00:00'),
        ];

        $monthlySamples = [];
        for ($year = 2022; $year <= 2025; ++$year) {
            for ($month = 1; $month <= 12; ++$month) {
                $monthlySamples[sprintf('%04d-%02d', $year, $month)] = 50.0;
            }
        }
        $monthlySamples['2026-01'] = 50.0;
        $monthlySamples['2026-02'] = 50.0;
        $monthlySamples['2026-03'] = 50.0;

        $forecastData = $this->buildForecast(
            ['Bounce Rate' => [50.0, 50.0]],
            $dataTables,
            [ArchiveState::COMPLETE, ArchiveState::INCOMPLETE],
            ['Bounce Rate' => false],
            [],
            ['Bounce Rate' => ForecastMetricClassifier::MONOTONICITY_FREE],
            ['Bounce Rate' => 2],
            [], // no daily samples — exercises the same-MoY fallback path
            ['Bounce Rate' => $monthlySamples]
        );

        self::assertSame([[null, 50.0]], $forecastData);
    }

    /**
     * Build a daily sample map of $weeks consecutive weeks starting at $firstMonday where every
     * day carries the same flat $value. Useful for isolating analog-walk feedback effects from
     * day-of-week variance.
     *
     * @return array<string, float>
     */
    private function buildFlatDailySamples(string $firstMonday, int $weeks, float $value): array
    {
        $samples = [];
        $cursor = \Piwik\Date::factory($firstMonday);
        for ($w = 0; $w < $weeks; ++$w) {
            for ($d = 0; $d < 7; ++$d) {
                $samples[$cursor->toString('Y-m-d')] = $value;
                $cursor = $cursor->addDay(1);
            }
        }
        return $samples;
    }

    /**
     * Build a daily sample map of $weeks consecutive weeks starting at $firstMonday with a
     * uniform per-DoW pattern (Mon=100, Tue=110, Wed=90, Thu=120, Fri=80, Sat=60, Sun=70).
     * Optionally scale every value by $scale.
     *
     * @return array<string, float>
     */
    private function buildUniformDoWDailySamples(string $firstMonday, int $weeks, float $scale = 1.0): array
    {
        $pattern = [100.0, 110.0, 90.0, 120.0, 80.0, 60.0, 70.0]; // Mon..Sun
        $samples = [];
        $cursor = \Piwik\Date::factory($firstMonday);
        for ($w = 0; $w < $weeks; ++$w) {
            for ($d = 0; $d < 7; ++$d) {
                $samples[$cursor->toString('Y-m-d')] = $pattern[$d] * $scale;
                $cursor = $cursor->addDay(1);
            }
        }
        return $samples;
    }

    /**
     * Flat daily sample map: $days consecutive 'Y-m-d' keys from $start, every value $value.
     * Used by the month-of-year scale test so the MoY denominator
     * ((sum/count) * 30.4375) reduces to a single clean number and the per-day analog means
     * are all identical, isolating the scale factor as the only non-trivial term.
     *
     * @return array<string, float>
     */
    private function buildUniformDailySamples(string $start, int $days, float $value): array
    {
        $samples = [];
        $cursor = Date::factory($start);
        for ($i = 0; $i < $days; ++$i) {
            $samples[$cursor->toString('Y-m-d')] = $value;
            $cursor = $cursor->addDay(1);
        }
        return $samples;
    }

    /**
     * @dataProvider getClampToHistoricalRangeTestData
     * @param array<int, float> $pastValues
     */
    public function testClampForecastToHistoricalRange(
        float $forecastValue,
        array $pastValues,
        float $expected
    ): void {
        $result = $this->invokePrivateMethod(
            new ForecastBuilder(),
            'clampForecastToHistoricalRange',
            [$forecastValue, $pastValues]
        );

        self::assertEqualsWithDelta($expected, $result, 1e-9);
    }

    /**
     * @return iterable<string, array{float, array<int, float>, float}>
     */
    public function getClampToHistoricalRangeTestData(): iterable
    {
        // A single recent spike pulls the trend fit far above the typical level. The envelope is
        // centred on the median (102.5) and sized from the MAD (5.0 -> 7.413 sigma), so the band
        // is [80.261, 124.739] and the runaway prior is held at the upper bound. The previous
        // self-centred form left the prior untouched.
        yield 'spike-driven runaway is reined to the upper bound' => [
            353.0,
            [100.0, 105.0, 95.0, 400.0],
            124.739,
        ];

        // A genuine sustained trend produces enough MAD spread to keep the band wide
        // ([70.522, 159.478] around median 115), so the in-range prior passes untouched.
        yield 'genuine trend passes untouched' => [
            135.0,
            [100.0, 110.0, 120.0, 130.0],
            135.0,
        ];

        // Perfectly stable history has zero MAD; the relative-spread floor (5% of the median)
        // keeps the band at +-15 rather than collapsing it onto the centre.
        yield 'relative-spread floor keeps a stable history from collapsing the band' => [
            200.0,
            [100.0, 100.0, 100.0, 100.0],
            115.0,
        ];

        // No samples: nothing to bound against, value returned unchanged.
        yield 'empty samples returns the value unchanged' => [
            42.0,
            [],
            42.0,
        ];
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

    private function createDataTableForDay(string $date, Site $site, ?string $archivedDate = null): DataTable
    {
        return $this->createDataTableForPeriod('day', $date, $site, $archivedDate);
    }

    private function createDataTableForWeek(string $date, Site $site, ?string $archivedDate = null): DataTable
    {
        return $this->createDataTableForPeriod('week', $date, $site, $archivedDate);
    }

    private function createDataTableForMonth(string $date, Site $site, ?string $archivedDate = null): DataTable
    {
        return $this->createDataTableForPeriod('month', $date, $site, $archivedDate);
    }

    private function createDataTableForPeriod(
        string $periodLabel,
        string $date,
        Site $site,
        ?string $archivedDate = null
    ): DataTable {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build($periodLabel, $date));
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX, $site);

        if ($archivedDate !== null) {
            $dataTable->setMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME, $archivedDate);
        }

        return $dataTable;
    }

    /**
     * Construct the per-series state from the same positional shape the tests historically
     * passed to {@see ForecastBuilder::build()}, then forward to it. Lets tests stay readable
     * (positional args, no inline {@see ForecastSeriesState} construction at every call site)
     * while the production signature carries the state object instead of four parallel maps.
     *
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, string> $allSeriesMonotonicity
     * @param array<string, int> $allSeriesForecastPrecision
     * @param array<string, array<string, float>> $allSeriesDailySamples
     * @param array<string, array<string, float>> $allSeriesMonthlySamples
     * @return array<int, array<int, float|null>>
     */
    private function buildForecast(
        array $allSeriesData,
        array $dataTables,
        array $dataStates,
        array $seriesUnits,
        array $allSeriesDataAvailability = [],
        array $allSeriesMonotonicity = [],
        array $allSeriesForecastPrecision = [],
        array $allSeriesDailySamples = [],
        array $allSeriesMonthlySamples = [],
        ?string $earliestDataDate = null
    ): array {
        $seriesState = new ForecastSeriesState(
            $allSeriesData,
            $allSeriesDataAvailability,
            $allSeriesMonotonicity,
            $allSeriesForecastPrecision
        );

        return (new ForecastBuilder())->build(
            $seriesState,
            $dataTables,
            $dataStates,
            $seriesUnits,
            $allSeriesDailySamples,
            $allSeriesMonthlySamples,
            $earliestDataDate
        );
    }

    /**
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function invokePrivateMethod(ForecastBuilder $builder, string $methodName, array $arguments)
    {
        $reflection = new ReflectionClass(ForecastBuilder::class);
        $method = $reflection->getMethod($methodName);

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        return $method->invokeArgs($builder, $arguments);
    }
}
