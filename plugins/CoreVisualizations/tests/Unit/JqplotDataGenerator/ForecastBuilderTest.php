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
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastBuilder;
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
        yield 'up, higher than current' => [12.5, 10.0, Evolution::MONOTONICITY_UP, true];
        yield 'up, equal to current' => [10.0, 10.0, Evolution::MONOTONICITY_UP, true];
        yield 'up, below current' => [9.99, 10.0, Evolution::MONOTONICITY_UP, false];
        yield 'free, higher than current' => [12.5, 10.0, Evolution::MONOTONICITY_FREE, true];
        yield 'free, equal to current' => [10.0, 10.0, Evolution::MONOTONICITY_FREE, true];
        yield 'free, below current' => [9.99, 10.0, Evolution::MONOTONICITY_FREE, true];
        yield 'down, higher than current' => [12.5, 10.0, Evolution::MONOTONICITY_DOWN, false];
        yield 'down, equal to current' => [10.0, 10.0, Evolution::MONOTONICITY_DOWN, true];
        yield 'down, below current' => [9.99, 10.0, Evolution::MONOTONICITY_DOWN, true];
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
        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
            ['Actions per visit' => [12.345, 90.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Actions per visit' => false],
            [],
            ['Actions per visit' => Evolution::MONOTONICITY_FREE],
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
        $forecastData = (new ForecastBuilder())->build(
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
        $forecastData = (new ForecastBuilder())->build(
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
        $forecastData = (new ForecastBuilder())->build(
            ['Avg time on page' => [12.0, 90.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Avg time on page' => 's'],
            [],
            ['Avg time on page' => Evolution::MONOTONICITY_FREE]
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

        $forecastData = (new ForecastBuilder())->build(
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

    public function testBuildCountSeriesSuppressesForecastBelowCurrentEvenWhenLowerIsBetter(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-01', $site),
            $this->createDataTableForDay('2026-04-08', $site),
            $this->createDataTableForDay('2026-04-15', $site, '2026-04-15 22:48:00'),
        ];

        // bounce_count is lower-is-better for trend display, but it is still an additive count
        // within the incomplete period. A forecast below the current archived count is not a
        // possible final value and must be suppressed.
        $forecastData = (new ForecastBuilder())->build(
            ['Bounce count' => [50.0, 50.0, 90.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Bounce count' => false],
            [],
            ['Bounce count' => Evolution::MONOTONICITY_UP]
        );

        self::assertSame([[null, null, null]], $forecastData);
    }

    public function testBuildDoesNotUseSyntheticZeroAsPercentForecastSeed(): void
    {
        $site = $this->createSiteMock();

        $dataTables = [
            $this->createDataTableForDay('2026-04-05', $site),
            $this->createDataTableForDay('2026-04-12', $site, '2026-04-12 00:00:00'),
            $this->createDataTableForDay('2026-04-19', $site, '2026-04-19 00:00:00'),
        ];

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
            ['Min event value' => [30.0]],
            $dataTables,
            [ArchiveState::INCOMPLETE],
            ['Min event value' => false],
            [],
            ['Min event value' => Evolution::MONOTONICITY_DOWN],
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $dataTables = [
            $this->createDataTableForDay('2026-04-01', $site),
            $this->createDataTableForDay('2026-04-08', $site),
            $this->createDataTableForDay('2026-04-15', $site, '2026-04-15 22:48:00'),
            $this->createDataTableForDay('2026-04-16', $site, '2026-04-16 00:00:00'),
        ];

        // Apr 15 Wed (partial 90): same-DoW priors Apr 1 Wed = 50, Apr 8 Wed = 50, prior = 50.
        // The UP gate (forecast >= currentValue) fails: 50 < 90, so the forecast is suppressed
        // and previousForecastValue resets to null. Apr 16 Thu has no same-DoW priors (Apr 1
        // and Apr 8 are both Wed) so the prior-only path also yields no forecast — without a
        // previousForecastValue to carry, the result is null. Pins the no-carry-forward
        // semantic across a suppression gap.
        $forecastData = (new ForecastBuilder())->build(
            ['Visits' => [50.0, 50.0, 90.0, 0.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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
            ['Min event value' => Evolution::MONOTONICITY_DOWN],
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

        $forecastData = (new ForecastBuilder())->build(
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
            ['Min event value' => Evolution::MONOTONICITY_DOWN],
            ['Min event value' => 0]
        );

        self::assertSame([[null, null, null, null]], $forecastData);
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

        $forecastData = (new ForecastBuilder())->build(
            ['Min bandwidth' => [4.0, 10.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min bandwidth' => false],
            [],
            ['Min bandwidth' => Evolution::MONOTONICITY_DOWN],
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

        $forecastData = (new ForecastBuilder())->build(
            ['Min event value' => [0.0, 30.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Min event value' => false],
            [],
            ['Min event value' => Evolution::MONOTONICITY_DOWN],
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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

        $forecastData = (new ForecastBuilder())->build(
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
