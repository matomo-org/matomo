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

        self::assertSame([[null, null, null, null, 47.9996, 58.3997]], $forecastData);
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

        self::assertSame([[null, null, null, 67.9997]], $forecastData);
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

        self::assertSame([[null, null, null, null, 47.9996, 58.3997, 74.7198, 59.9994]], $forecastData);
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
