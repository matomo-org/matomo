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
        bool $allowsDownward,
        bool $expected
    ): void {
        $result = $this->invokePrivateMethod(
            new ForecastBuilder(),
            'shouldRenderForecastValue',
            [$forecastValue, $currentValue, $allowsDownward]
        );

        self::assertSame($expected, $result);
    }

    /**
     * @return iterable<string, array{float, float, bool, bool}>
     */
    public function getForecastVisibilityTestData(): iterable
    {
        yield 'monotonic, higher than current' => [12.5, 10.0, false, true];
        yield 'monotonic, equal to current' => [10.0, 10.0, false, true];
        yield 'monotonic, below current' => [9.99, 10.0, false, false];
        yield 'non-monotonic, higher than current' => [12.5, 10.0, true, true];
        yield 'non-monotonic, equal to current' => [10.0, 10.0, true, true];
        yield 'non-monotonic, below current' => [9.99, 10.0, true, true];
    }

    public function testBuildUsesPriorForecastAsFirstNoDataBaseline(): void
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

        // Apr 17 (Fri partial, archived noon, partial=20): same-DOW filter keeps only Apr 10
        // Fri = 80, so prior = 80. Linear = 20 / 0.5 = 40. Base prior weight at 1 sample/day is
        // 0.2; the early-period bias adds (1 - 0.5) * 0.4 = 0.2 → effective weight 0.4.
        // Forecast = 0.6 * 40 + 0.4 * 80 = 56.
        // Apr 18 (Sat partial, archived 00:00, partial=0): same-DOW filter keeps Apr 11 Sat = 100,
        // so prior = 100. With currentValue ≤ 0 and a previous forecast in hand the base falls
        // back to the carried 56. At elapsedRatio = 0 the early-period bias contributes its full
        // 0.4 → effective weight 0.6. Forecast = 0.4 * 56 + 0.6 * 100 = 82.4.
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

        self::assertSame([[null, null, null, null, 55.9996, 82.3998]], $forecastData);
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

        // Same Apr 17 forecast as testBuildUsesPriorForecastAsFirstNoDataBaseline (= 56) but
        // round()'d to integer precision via the per-series forecast precision flag.
        self::assertSame([[null, null, null, null, 56.0]], $forecastData);
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
            ['Actions per visit' => true],
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
            ['Avg time on page' => true]
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
            ['Bounce count' => false]
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
        // Mar 27 Fri = 80 and Apr 10 Fri = 100 — slope 20, intercept 60, prior 60 + 20*2.5 = 110.
        // Linear = 20 / 0.5 = 40. Two-sample weight is 0.4 + (1 - 0.5)*0.4 = 0.6.
        // Forecast = 0.4 * 40 + 0.6 * 110 = 82.
        self::assertSame([[null, null, null, 81.9997]], $forecastData);
    }

    public function testBuildReusesForecastAsSyntheticDataForLaterNoDataDaysAndRecalculatesWhenDataReturns(): void
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

        // Apr 17 Fri (partial 20, archived noon): same-DOW prior [80] → 56 (see baseline test).
        // Apr 18 Sat (no data, archived 00:00): prior [100], carries Apr 17's 56 forward, full
        // early-period bias (elapsed = 0) → forecast 0.4 * 56 + 0.6 * 100 = 82.4.
        // Apr 19 Sun (no data, archived 00:00): prior [140], carries 82.4 forward → 0.4 * 82.4 +
        // 0.6 * 140 = 116.96.
        // Apr 20 Mon (partial 30, archived noon): prior [60], linear = 60. Both base and prior
        // collapse to 60 so the forecast is 60 regardless of weight; it is rendered because the
        // monotonic gate (forecast ≥ currentValue 30) is satisfied.
        self::assertSame([[null, null, null, null, 55.9996, 82.3998, 116.9599, 59.9996]], $forecastData);
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

        self::assertSame([[null, null, null, 0.0]], $forecastData);
    }

    public function testBuildEarlyPeriodBiasShiftsWeightToPriorWhenLittleElapsed(): void
    {
        $site = $this->createSiteMock();

        // Three stable Friday priors of 100 (slope 0, prior 100). May 1 partial 30 archived at
        // hour 1 → elapsedRatio ≈ 0.04 → MIN_FORECAST_RATIO floor of 0.05 → linear = 600. With
        // three samples (one shy of MIN_SAMPLES_FOR_BOUNDED_RANGE) the clamp is skipped, so the
        // only thing pulling 600 down to a defensible value is the early-period bias.
        // Effective weight = min(0.95, 0.6 + (1 - 0.05) * 0.4) = 0.95.
        // Forecast = 0.05 * 600 + 0.95 * 100 = 125. Before the bias the same setup produced
        // 0.4 * 600 + 0.6 * 100 = 300, which is implausible for a metric that has been flat
        // across all sampled periods.
        $dataTables = [
            $this->createDataTableForDay('2026-04-10', $site),
            $this->createDataTableForDay('2026-04-17', $site),
            $this->createDataTableForDay('2026-04-24', $site),
            $this->createDataTableForDay('2026-05-01', $site, '2026-05-01 01:00:00'),
        ];

        $forecastData = (new ForecastBuilder())->build(
            ['Visits' => [100.0, 100.0, 100.0, 30.0]],
            $dataTables,
            [
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::COMPLETE,
                ArchiveState::INCOMPLETE,
            ],
            ['Visits' => false]
        );

        self::assertSame([[null, null, null, 125.0]], $forecastData);
    }

    public function testBuildPrefersCalendarAlignedMonthlySamplesWhenAvailable(): void
    {
        $site = $this->createSiteMock();

        // Two prior years of alternating March/June with very different magnitudes; the partial
        // is March 2025 (in the past relative to the test environment so getElapsedRatio is
        // pinned by the archive timestamp, not Date::now()). Calendar-month alignment selects
        // the two Marches [100, 110] (slope 10, intercept 90, prior = 90 + 10*2.5 = 115). The
        // fallback path would mix the four samples [100, 500, 110, 550] and yield a prior near
        // 500. Mid-month archive makes elapsedRatio ≈ 0.47, linear ≈ 128, weight ≈ 0.71, so the
        // aligned forecast lands ~118 — near the March pattern, not the contaminated four-
        // sample regression.
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
        self::assertLessThan(135.0, $forecast);
    }

    public function testBuildFallsBackToAllSamplesWhenAlignedSamplesBelowThreshold(): void
    {
        $site = $this->createSiteMock();

        // Only March 2024 is calendar-aligned with March 2025; the other priors are January and
        // February 2025. With a single aligned sample the builder must fall back to the full
        // recency window [100, 200, 300] (slope 100, prior 350), not collapse onto the lone
        // aligned value. The fallback prior pulls the forecast well above 200, while an
        // aligned-only path with prior = 100 would have produced a forecast near 110. Pinning
        // the high outcome guards against the alignment filter degrading to "exclude when not
        // aligned", which would silently discard two thirds of the available history.
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
        self::assertLessThan(310.0, $forecast);
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
