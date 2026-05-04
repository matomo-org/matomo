<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period;
use Piwik\Site;

/**
 * Computes per-tick forecast values for incomplete-period data points on evolution-style series.
 *
 * Two algorithms are applied based on each series' monotonicity:
 *
 * - Monotonic count series (visits, conversions, page views): the period's value can only grow
 *   from the current partial accumulation, so the forecast blends a linear elapsed-ratio
 *   extrapolation with the historical same-period average and is suppressed if it falls below
 *   the current display value (counts cannot shrink within a period).
 * - Non-monotonic series (ratios, rates, percentages, averages): the forecast is the historical
 *   same-period average only, and is allowed to fall below the current display value because the
 *   period's value can move in either direction during the remaining time.
 *
 * The builder is stateless and reusable. Period bounds and the archive timestamp are read from
 * DataTable metadata, so any caller producing comparable DataTable maps can reuse it.
 */
class ForecastBuilder
{
    private const MIN_FORECAST_RATIO = 0.05;

    /**
     * Damping factor applied to the linear-trend projection of the historical prior.
     * 1.0 = full least-squares extrapolation (most responsive to trends, most prone to
     * overshoot on noisy ratios). 0.0 = no projection (flat mean). 0.5 keeps the regression
     * line's fit on the historical samples but only takes a half-step in the slope direction
     * for the next-period forecast — a trade-off between catching growth on count series and
     * not amplifying noise on volatile averages.
     */
    private const TREND_DAMPING = 0.5;

    /**
     * Additional weight added to the historical prior when little of the current period has
     * elapsed. The linear extrapolation is currentValue / elapsedRatio, so at 5% elapsed the
     * partial value carries 20× leverage on whatever short-window noise produced it; the prior
     * needs to anchor the forecast more strongly until enough of the period has accumulated.
     * Linearly interpolated between elapsed=1 (no extra bias) and elapsed=0 (full bias).
     */
    private const EARLY_PERIOD_PRIOR_BIAS = 0.4;

    /**
     * Hard ceiling on the prior weight. Even at zero elapsed the partial value retains at least
     * a small contribution so a recent step-change in traffic can pull the forecast off a
     * mismatched prior — fully ignoring the partial would freeze the forecast at the prior's
     * projection, which is wrong when the period is genuinely diverging from history.
     */
    private const MAX_PRIOR_WEIGHT = 0.95;

    /**
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, bool> $allSeriesAllowsDownwardForecast Per-series flag; when true the
     *        series is treated as able to decrease by the end of the period and forecasts are
     *        produced from the historical prior only without the "forecast >= current"
     *        suppression. When the flag is missing for a series, the builder falls back to
     *        "percent unit implies non-monotonic".
     * @param array<string, int> $allSeriesForecastPrecision Per-series decimal precision for raw
     *        forecast payload values. Missing entries preserve the historical 4-decimal default.
     * @return array<int, array<int, float|null>>
     */
    public function build(
        array $allSeriesData,
        array $dataTables,
        array $dataStates,
        array $seriesUnits,
        array $allSeriesDataAvailability = [],
        array $allSeriesAllowsDownwardForecast = [],
        array $allSeriesForecastPrecision = []
    ): array {
        if ([] === $allSeriesData || [] === $dataTables || [] === $dataStates) {
            return [];
        }

        /** @var Site|null $site */
        $site = reset($dataTables)->getMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX);
        if (empty($site)) {
            return [];
        }

        $dataTableList = array_values($dataTables);
        $seriesDataList = array_values($allSeriesData);
        $seriesUnitsList = array_values($seriesUnits);
        $seriesDataAvailabilityList = array_values($allSeriesDataAvailability);
        $seriesAllowsDownwardList = array_values($allSeriesAllowsDownwardForecast);
        $seriesForecastPrecisionList = array_values($allSeriesForecastPrecision);

        $forecastData = [];

        foreach ($seriesDataList as $seriesIndex => $seriesData) {
            $seriesForecasts = [];
            // Reset on every non-rendered tick so a suppressed or skipped forecast does not
            // bridge into later zero-data ticks; later ticks must restart from historical priors.
            $previousForecastValue = null;
            $isPercentSeries = ($seriesUnitsList[$seriesIndex] ?? false) === '%';
            $seriesDataAvailability = $seriesDataAvailabilityList[$seriesIndex] ?? [];
            $allowsDownward = $seriesAllowsDownwardList[$seriesIndex] ?? $isPercentSeries;
            $forecastPrecision = $seriesForecastPrecisionList[$seriesIndex] ?? 4;

            foreach ($seriesData as $tickIndex => $currentValueRaw) {
                $state = $dataStates[$tickIndex] ?? ArchiveState::COMPLETE;

                if (ArchiveState::INCOMPLETE !== $state) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                $currentValue = (float) $currentValueRaw;
                $dataTable = $dataTableList[$tickIndex] ?? null;

                if (empty($dataTable)) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                $pastValues = $this->getHistoricalSamplesForSeries(
                    $seriesData,
                    $dataTableList,
                    $dataStates,
                    $tickIndex,
                    $dataTable,
                    $seriesDataAvailability
                );

                if ($allowsDownward) {
                    $forecastValue = $this->buildNonMonotonicForecastValue($pastValues, $previousForecastValue);

                    if ($forecastValue === null) {
                        $seriesForecasts[] = null;
                        $previousForecastValue = null;
                        continue;
                    }

                    $roundedForecast = round($forecastValue, $forecastPrecision);
                    $seriesForecasts[] = $roundedForecast;
                    $previousForecastValue = $roundedForecast;
                    continue;
                }

                $forecastValue = $this->buildMonotonicForecastValue(
                    $currentValue,
                    $pastValues,
                    $previousForecastValue,
                    $dataTable,
                    $site
                );

                if (!$this->shouldRenderForecastValue($forecastValue, $currentValue, $allowsDownward)) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                $roundedForecast = round($forecastValue, $forecastPrecision);
                $seriesForecasts[] = $roundedForecast;
                $previousForecastValue = $roundedForecast;
            }

            $forecastData[] = $seriesForecasts;
        }

        return $forecastData;
    }

    /**
     * Forecast for monotonic count series: linear elapsed-ratio extrapolation blended with the
     * historical same-period prior. Carries the previous forecast forward when the current tick
     * has no positive value so a synthetic 0 does not collapse the linear seed to zero. When the
     * current tick is the first incomplete tick (no previous forecast) and historical priors
     * exist, the blend would otherwise dilute the prior with a meaningless zero, so it falls back
     * to the prior directly. The prior itself is trend-aware via computeHistoricalPrior.
     *
     * @param array<int, float> $pastValues
     */
    private function buildMonotonicForecastValue(
        float $currentValue,
        array $pastValues,
        ?float $previousForecastValue,
        DataTable $dataTable,
        Site $site
    ): float {
        $elapsedRatio = $this->getElapsedRatio($dataTable, $site);
        $ratio = max($elapsedRatio, self::MIN_FORECAST_RATIO);
        $linearForecast = $currentValue / $ratio;

        $baseForecast = $linearForecast;
        $priorForecast = $linearForecast;

        if ([] !== $pastValues) {
            $priorForecast = $this->computeHistoricalPrior($pastValues);
        }

        $weight = $this->getPriorForecastWeight(
            count($pastValues),
            $this->getPeriodLabel($dataTable),
            $elapsedRatio
        );

        if ($currentValue <= 0 && $previousForecastValue !== null) {
            $baseForecast = $previousForecastValue;
        } elseif ($currentValue <= 0 && [] !== $pastValues) {
            $baseForecast = $priorForecast;
        }

        $forecastValue = $this->blendForecastValue($baseForecast, $priorForecast, $weight);

        if ($baseForecast >= 0 && $priorForecast >= 0) {
            $forecastValue = max(0, $forecastValue);
        }

        return $forecastValue;
    }

    /**
     * Forecast for non-monotonic series (ratios, averages, latency-style). Returns the historical
     * same-period prior (trend-aware via computeHistoricalPrior), falling back to the previous
     * forecast if there is no prior. Returns null when neither signal is available because no
     * defensible value can be produced.
     *
     * @param array<int, float> $pastValues
     */
    private function buildNonMonotonicForecastValue(array $pastValues, ?float $previousForecastValue): ?float
    {
        if ([] !== $pastValues) {
            return $this->computeHistoricalPrior($pastValues);
        }

        if ($previousForecastValue !== null) {
            return $previousForecastValue;
        }

        return null;
    }

    /**
     * Same-period historical prior used by both forecast paths. With fewer than two samples
     * there is no slope to fit and the only signal is the single value (or a flat mean).
     * With two or more samples we apply a least-squares linear-trend extrapolation projected
     * one step forward, then dampen the projection by TREND_DAMPING so noisy ratios do not
     * runaway-extrapolate from a spurious slope. Catching sustained growth or decline that a
     * flat mean would systematically lag is the win; the damping is what keeps that win from
     * becoming a loss on volatile averages. The result is clamped to >= 0 because every metric
     * the builder serves (counts, percentages, durations) is non-negative; a negative trend
     * extrapolation past zero is never a defensible forecast.
     *
     * @param array<int, float> $pastValues Same-period historical samples in temporal order
     *        (oldest first), already filtered and stripped of leading zeros by the caller.
     */
    private function computeHistoricalPrior(array $pastValues): float
    {
        $sampleCount = count($pastValues);
        if ($sampleCount < 2) {
            return max(0.0, (float) $pastValues[0]);
        }

        $sumX = $sampleCount * ($sampleCount + 1) / 2;
        $sumY = array_sum($pastValues);
        $sumXX = 0.0;
        $sumXY = 0.0;
        for ($i = 0; $i < $sampleCount; ++$i) {
            $x = $i + 1;
            $sumXX += $x * $x;
            $sumXY += $x * $pastValues[$i];
        }

        $denominator = $sampleCount * $sumXX - $sumX * $sumX;
        if ($denominator <= 0.0) {
            return max(0.0, $sumY / $sampleCount);
        }

        $slope = ($sampleCount * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $sampleCount;

        // Equivalent to projecting from the regressed value at x=sampleCount and taking a
        // fractional step in the slope direction: y(n) + damping * slope.
        return max(0.0, $intercept + $slope * ($sampleCount + self::TREND_DAMPING));
    }

    /**
     * @param array<int, float|int> $seriesData
     * @param array<int, DataTable> $dataTableList
     * @param array<int, string> $dataStates
     * @param array<int, bool> $seriesDataAvailability
     * @return array<int, float>
     */
    private function getHistoricalSamplesForSeries(
        array $seriesData,
        array $dataTableList,
        array $dataStates,
        int $currentTickIndex,
        DataTable $currentDataTable,
        array $seriesDataAvailability = []
    ): array {
        $samples = [];
        $periodLabel = $this->getPeriodLabel($currentDataTable);
        $currentWeekDay = (int) $this->getPeriodStartDayOfWeek($currentDataTable);

        for ($tickIndex = 0; $tickIndex < $currentTickIndex; ++$tickIndex) {
            if (($dataStates[$tickIndex] ?? null) !== ArchiveState::COMPLETE) {
                continue;
            }

            if (!isset($seriesData[$tickIndex])) {
                continue;
            }

            if (($seriesDataAvailability[$tickIndex] ?? true) === false) {
                continue;
            }

            $value = (float) $seriesData[$tickIndex];
            $dataTable = $dataTableList[$tickIndex] ?? null;

            if (empty($dataTable)) {
                continue;
            }

            if ('day' === $periodLabel) {
                if ((int) $this->getPeriodStartDayOfWeek($dataTable) !== $currentWeekDay) {
                    continue;
                }
            }

            $samples[] = $value;
        }

        return $this->removeLeadingZeroSamples($samples);
    }

    /**
     * @param array<int, float> $samples
     * @return array<int, float>
     */
    private function removeLeadingZeroSamples(array $samples): array
    {
        while ([] !== $samples && 0.0 === (float) reset($samples)) {
            array_shift($samples);
        }

        return array_values($samples);
    }

    private function shouldRenderForecastValue(
        float $forecastValue,
        float $currentDisplayValue,
        bool $allowsDownward
    ): bool {
        if ($allowsDownward) {
            return true;
        }

        return $forecastValue >= $currentDisplayValue;
    }

    private function blendForecastValue(float $baseForecast, float $priorForecast, float $weight): float
    {
        return ((1 - $weight) * $baseForecast) + ($weight * $priorForecast);
    }

    /**
     * Blend weight assigned to the historical prior. The base weight grows with the number of
     * available samples (more history = more confidence in the prior); the elapsed-ratio
     * adjustment shifts further weight onto the prior early in a period and yields it back to
     * the partial value as completion approaches. The combined weight is capped to retain at
     * least a small contribution from the partial-period extrapolation, so a recent traffic
     * step-change can still pull the forecast off a mismatched prior projection.
     */
    private function getPriorForecastWeight(int $sampleCount, string $periodLabel, float $elapsedRatio): float
    {
        if ($sampleCount <= 0) {
            return 0.0;
        }

        $baseWeight = ('day' === $periodLabel)
            ? min(0.7, $sampleCount / 5)
            : min(0.5, $sampleCount / 4);

        $clampedElapsed = max(0.0, min(1.0, $elapsedRatio));
        $earlyPeriodAdjustment = (1.0 - $clampedElapsed) * self::EARLY_PERIOD_PRIOR_BIAS;

        return min(self::MAX_PRIOR_WEIGHT, $baseWeight + $earlyPeriodAdjustment);
    }

    private function getElapsedRatio(DataTable $dataTable, Site $site): float
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        $siteTz = $site->getTimezone();

        // date1/date2 are site-local wall-clock; setTimezone($siteTz)->getTimestamp() returns
        // the real UTC instant of that wall-clock moment.
        $startTs = $period->getDateTimeStart()->setTimezone($siteTz)->getTimestamp();
        $endTs = $period->getDateTimeEnd()->setTimezone($siteTz)->getTimestamp();

        // ts_archived is stored as UTC, so getTimestampUTC() keeps it real UTC; cap by current
        // real UTC so the ratio reflects actual elapsed time in the site's day.
        $elapsedTs = Date::now()->getTimestamp();
        $archivedDateStr = $dataTable->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME);
        if (!empty($archivedDateStr)) {
            $archivedTs = Date::factory($archivedDateStr)->getTimestampUTC();
            if ($archivedTs < $elapsedTs) {
                $elapsedTs = $archivedTs;
            }
        }

        $elapsedTs = min($elapsedTs, $endTs);

        if ($elapsedTs <= $startTs || $endTs <= $startTs) {
            return 0.0;
        }

        return min(1.0, max(0.0, ($elapsedTs - $startTs) / ($endTs - $startTs)));
    }

    private function getPeriodLabel(DataTable $dataTable): string
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        return $period->getLabel();
    }

    private function getPeriodStartDayOfWeek(DataTable $dataTable): string
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        return $period->getDateStart()->toString('N');
    }
}
