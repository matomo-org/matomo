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
use Piwik\Period\Month;
use Piwik\Site;

/**
 * Computes per-tick forecast values for incomplete-period data points on evolution-style series.
 *
 * Three algorithms are applied based on each series' intra-period direction (see
 * {@see Evolution::MONOTONICITY_*}):
 *
 * - MONOTONICITY_UP — additive count series (visits, conversions, page views): forecasted by
 *   reconstructing the period from sub-period analog samples. Completed sub-periods contribute
 *   their real archived values; the in-progress and future sub-periods contribute calendar-aligned
 *   analog samples (same day-of-week for week/month, same month-of-year for year). The displayed
 *   in-progress total is used only as a floor — never as a multiplier — so the forecast does not
 *   drift with the displayed graph range. Falls back to a prior-only same-period projection when
 *   the caller did not supply the sub-period samples needed for decomposition.
 * - MONOTONICITY_DOWN — running-min series (min_bandwidth, min_event_value): the period's value
 *   can only equal or fall below the current partial value as more samples arrive, so the
 *   forecast is the historical same-period prior, suppressed (and finally clamped) if it would
 *   project above the current value.
 * - MONOTONICITY_FREE — ratio/rate/percentage/average series: the forecast is the historical
 *   same-period prior with no directional gate, because the period's value can move either way
 *   during the remaining time.
 *
 * The builder is stateless and reusable. Period bounds and the archive timestamp are read from
 * DataTable metadata, so any caller producing comparable DataTable maps can reuse it.
 */
class ForecastBuilder
{
    /**
     * Damping factor applied to the linear-trend projection of historical priors. 1.0 = full
     * least-squares extrapolation (most responsive to trends, most prone to overshoot on noisy
     * ratios). 0.0 = no projection (flat mean). 0.5 keeps the regression line's fit on the
     * historical samples but only takes a half-step in the slope direction for the next-period
     * forecast — a trade-off between catching growth on count series and not amplifying noise
     * on volatile averages.
     */
    private const TREND_DAMPING = 0.5;

    /**
     * Minimum number of calendar-aligned historical samples (same week-of-year, same calendar
     * month) required before preferring them over the full sample set. With only one aligned
     * sample there is no slope to fit and the recency-only window is more informative than a
     * single calendar-matched data point.
     */
    private const MIN_ALIGNED_SAMPLES_TO_PREFER = 2;

    /**
     * Minimum number of historical samples required before clamping the blended forecast to a
     * historical-range envelope. With fewer samples the empirical standard deviation is too
     * noisy to define a meaningful upper/lower bound, so the clamp is skipped.
     */
    private const MIN_SAMPLES_FOR_BOUNDED_RANGE = 4;

    /**
     * Below this count the day-level analog reducer falls back to a plain mean instead of a
     * trend fit. With only a handful of weekly-strided samples the least-squares slope swings
     * wildly because the time base is short and neighbouring same-DoW values drift around a
     * stationary mean rather than a persistent trend; the envelope clamp is also not active,
     * so a noisy slope cannot be contained. The plain mean is the more informative reducer
     * until enough samples are present to constrain the slope.
     */
    private const MIN_SAMPLES_FOR_DAY_LEVEL_TREND = 5;

    /**
     * Width of the historical-range envelope expressed in standard deviations of the past
     * samples. Three sigmas covers ~99.7% of normally-distributed history, so a forecast landing
     * outside this band almost certainly reflects an extrapolation artefact rather than a
     * credible final value.
     */
    private const BOUNDED_RANGE_SIGMAS = 3.0;

    /**
     * Minimum half-width of the historical-range envelope expressed as a fraction of the sample
     * mean. Without this floor a perfectly stable history (sigma ≈ 0) would collapse the
     * envelope onto the prior and forbid any deviation, including the legitimate case where the
     * partial period is genuinely trending up or down.
     */
    private const BOUNDED_RANGE_MIN_RELATIVE_SPREAD = 0.05;

    /**
     * Number of same-DoW samples to draw for the day-period historical prior when the caller
     * supplies a daily sample map. The day path never enters the seasonal-decomposition branch
     * (a day has no useful sub-period to decompose), so the prior-only path is the only
     * forecast surface and a 70-day fetched window yields at most ten same-DoW samples. Above
     * MIN_SAMPLES_FOR_BOUNDED_RANGE so the envelope clamp engages, and enough data points for
     * the trend fit to resist single-day noise on short displays.
     */
    private const DAY_PRIOR_TARGET_SAMPLES = 10;

    /**
     * Default number of same-DoW analog samples per remaining day slot when forecasting a week.
     * Smaller chunks lose accuracy from a single noisy analog; larger chunks pull in older
     * traffic that drifts away from current site level.
     */
    private const WEEK_ANALOG_CHUNK = 3;

    /**
     * Default number of same-DoW analog samples per remaining day slot when forecasting a
     * month. Slightly larger than the week default because monthly forecasts span more remaining
     * days, so additional analogs bring incremental stability without introducing meaningful
     * drift.
     */
    private const MONTH_ANALOG_CHUNK = 4;

    /**
     * Default number of same-month-of-year analog samples when forecasting a year. With one
     * sample per analog year, eight is enough to drive the trend fit's slope while remaining
     * within typical multi-year archive depth.
     */
    private const YEAR_ANALOG_CHUNK = 8;

    /**
     * Number of consecutive complete prior ticks immediately preceding the forecast tick that
     * must read as "no data" (zero value or missing column) before the forecast is suppressed
     * as a no-recent-traffic case. Set to 2 so a legitimate single-zero observation (e.g. a
     * 0% bounce_rate on a low-traffic day with one bounceless visit, or a min_* metric whose
     * single archived sample was 0) does not trigger suppression — those are valid priors.
     * Two consecutive empty ticks is the shortest pattern that distinguishes a sustained
     * outage from a one-tick blip.
     */
    private const MIN_RECENT_NO_DATA_TICKS_FOR_SUPPRESSION = 2;

    /**
     * @param array<string, array<int, float|int>> $allSeriesData
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @param array<string, array<int, bool>> $allSeriesDataAvailability
     * @param array<string, string> $allSeriesMonotonicity Per-series intra-period direction tag,
     *        one of the {@see Evolution::MONOTONICITY_*} constants. Missing entries fall back to
     *        FREE for percent-unit series and UP otherwise.
     * @param array<string, int> $allSeriesForecastPrecision Per-series decimal precision for raw
     *        forecast payload values. Missing entries preserve the historical 4-decimal default.
     * @param array<string, array<string, float>> $allSeriesDailySamples Per-series map of
     *        Y-m-d → final daily value, covering enough history to populate same-DoW analog
     *        slots for the highest-tick week/month target. Required for MONOTONICITY_UP
     *        week/month forecasts; without it the builder falls back to prior-only same-period
     *        projection on the period-level series.
     * @param array<string, array<string, float>> $allSeriesMonthlySamples Per-series map of
     *        YYYY-MM → final monthly value, used by MONOTONICITY_UP year forecasts to project
     *        remaining months from same-month-of-year analogs.
     * @return array<int, array<int, float|null>>
     */
    public function build(
        array $allSeriesData,
        array $dataTables,
        array $dataStates,
        array $seriesUnits,
        array $allSeriesDataAvailability = [],
        array $allSeriesMonotonicity = [],
        array $allSeriesForecastPrecision = [],
        array $allSeriesDailySamples = [],
        array $allSeriesMonthlySamples = []
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
        $seriesNames = array_keys($allSeriesData);
        $seriesDataList = array_values($allSeriesData);
        $seriesUnitsList = array_values($seriesUnits);
        $seriesDataAvailabilityList = array_values($allSeriesDataAvailability);
        $seriesMonotonicityList = array_values($allSeriesMonotonicity);
        $seriesForecastPrecisionList = array_values($allSeriesForecastPrecision);

        $resolvedMonotonicity = [];
        foreach ($seriesDataList as $seriesIndex => $unused) {
            $isPercentSeries = ($seriesUnitsList[$seriesIndex] ?? false) === '%';
            $resolvedMonotonicity[$seriesIndex] = $seriesMonotonicityList[$seriesIndex]
                ?? ($isPercentSeries ? Evolution::MONOTONICITY_FREE : Evolution::MONOTONICITY_UP);
        }

        // Process MONOTONICITY_UP series first so the cross-series gate (below) can read each
        // tick's count-series forecast before deciding whether dependent ratios/averages should
        // render. Output order is restored by indexing $forecastData on the original series
        // index and ksort'ing at the end.
        $processingOrder = array_keys($seriesDataList);
        usort($processingOrder, function ($a, $b) use ($resolvedMonotonicity) {
            $aRank = Evolution::MONOTONICITY_UP === $resolvedMonotonicity[$a] ? 0 : 1;
            $bRank = Evolution::MONOTONICITY_UP === $resolvedMonotonicity[$b] ? 0 : 1;
            if ($aRank === $bRank) {
                return $a <=> $b;
            }
            return $aRank <=> $bRank;
        });

        // Per-tick "any UP series produced a renderable forecast > 0" map. Built up as the
        // first-pass UP series finish processing, consumed by the second-pass FREE/DOWN series
        // to suppress dependent ratios/averages on ticks where no count series carries data.
        $upSeriesNonZeroByTick = [];
        $hasAnyUpSeries = false;

        $forecastData = [];

        foreach ($processingOrder as $seriesIndex) {
            $seriesData = $seriesDataList[$seriesIndex];
            $seriesName = $seriesNames[$seriesIndex] ?? null;
            $seriesForecasts = [];
            // Reset on every non-rendered tick so a suppressed or skipped forecast does not
            // bridge into later zero-data ticks; later ticks must restart from historical priors.
            $previousForecastValue = null;
            $seriesDataAvailability = $seriesDataAvailabilityList[$seriesIndex] ?? [];
            $monotonicity = $resolvedMonotonicity[$seriesIndex];
            $forecastPrecision = $seriesForecastPrecisionList[$seriesIndex] ?? 4;
            $isUpSeries = Evolution::MONOTONICITY_UP === $monotonicity;
            if ($isUpSeries) {
                $hasAnyUpSeries = true;
            }
            // Running sample maps grow as forecasts are produced for earlier incomplete ticks
            // in this series, so subsequent ticks pick up those projections in their analog
            // walks instead of regressing to the partial/empty data left for forecast ticks in
            // the original sample fetch. Without this feedback the second of two same-DoW
            // forecast days (e.g. a Tuesday this week and a Tuesday next week) sees this
            // Tuesday's partial value as a historical analog and pulls the trend hard down.
            //
            // Only feed projections forward when the caller originally supplied the matching
            // sample map. With an empty caller-supplied map the seasonal/day-target paths do
            // not engage and the prior comes from the legacy dataTableList walk; folding
            // projections into a previously-empty map would flip the path for later ticks and
            // hide the legacy behaviour the dataTableList walk is meant to provide.
            $runningDailySamples = ($seriesName !== null && isset($allSeriesDailySamples[$seriesName]))
                ? $allSeriesDailySamples[$seriesName]
                : [];
            $runningMonthlySamples = ($seriesName !== null && isset($allSeriesMonthlySamples[$seriesName]))
                ? $allSeriesMonthlySamples[$seriesName]
                : [];
            $feedDailyProjections = [] !== $runningDailySamples;
            $feedMonthlyProjections = [] !== $runningMonthlySamples;

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

                // Trailing-no-data gate: if the most recent complete prior tick(s) read as
                // empty (zero value or column unavailable), the same-period analog walks will
                // happily reach back over the no-data stretch and "snap" the forecast to the
                // pre-outage level — a ghost spike on the chart with no observable basis.
                // Suppress instead. Applies to every series in the build pass, regardless of
                // monotonicity, so a no-traffic stretch hides counts, ratios and averages
                // uniformly.
                if (
                    $this->hasRecentNoDataPattern(
                        $seriesData,
                        $dataStates,
                        $seriesDataAvailability,
                        $tickIndex,
                        self::MIN_RECENT_NO_DATA_TICKS_FOR_SUPPRESSION
                    )
                ) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                // Cross-series gate: ratios and averages (FREE/DOWN) are only meaningful when
                // the underlying count exists. After the UP-first pass has finished, suppress
                // any dependent series at ticks where no UP series rendered a non-zero
                // forecast. Skipped when no UP series were present in the build call.
                if (!$isUpSeries && $hasAnyUpSeries && empty($upSeriesNonZeroByTick[$tickIndex])) {
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
                    $seriesDataAvailability,
                    $monotonicity,
                    $runningDailySamples
                );

                $tickDayProjections = [];
                $tickMonthProjections = [];

                if ($isUpSeries) {
                    $forecastValue = $this->buildMonotonicForecastValue(
                        $currentValue,
                        $pastValues,
                        $previousForecastValue,
                        $dataTable,
                        $site,
                        $runningDailySamples,
                        $runningMonthlySamples,
                        $tickDayProjections,
                        $tickMonthProjections
                    );
                } else {
                    $forecastValue = $this->buildNonMonotonicForecastValue(
                        $pastValues,
                        $previousForecastValue,
                        $dataTable,
                        $tickDayProjections
                    );
                }

                if ($forecastValue === null) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                if (!$this->shouldRenderForecastValue($forecastValue, $currentValue, $monotonicity)) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                // Belt-and-braces clamp: a min_* metric's final-period value can never exceed
                // the current partial min, so even if the gate let the prior through (e.g. it
                // equalled current within rounding) we hold the rendered forecast at or below
                // current. Cheap insurance against an ever-rising-min visual in the chart.
                if (Evolution::MONOTONICITY_DOWN === $monotonicity) {
                    $forecastValue = min($forecastValue, $currentValue);
                }

                $roundedForecast = round($forecastValue, $forecastPrecision);
                $seriesForecasts[] = $roundedForecast;
                $previousForecastValue = $roundedForecast;

                if ($isUpSeries && $forecastValue > 0.0) {
                    $upSeriesNonZeroByTick[$tickIndex] = true;
                }

                // Feed this tick's projections forward as historical analogs for later
                // incomplete ticks in the same series. Use raw (un-rounded) values so the
                // feedback channel keeps the precision of the underlying computation; the
                // rounding step above is for display only.
                if ($feedDailyProjections) {
                    foreach ($tickDayProjections as $anchor => $value) {
                        $runningDailySamples[$anchor] = (float) $value;
                    }
                }
                if ($feedMonthlyProjections) {
                    foreach ($tickMonthProjections as $anchor => $value) {
                        $runningMonthlySamples[$anchor] = (float) $value;
                    }
                }
            }

            $forecastData[$seriesIndex] = $seriesForecasts;
        }

        ksort($forecastData);

        return array_values($forecastData);
    }

    /**
     * True when the $requiredCount complete prior ticks immediately preceding $currentTickIndex
     * all read as "no data" — either zero value or column-availability false. Skips earlier
     * incomplete ticks (forecast slots) so an unbroken run of forecast ticks does not interrupt
     * the walk back into real history. Returns false if the walk runs out of complete history
     * before $requiredCount ticks have been examined; suppression is a "we are confident the
     * recent past was empty" decision, and partial evidence does not justify it.
     *
     * @param array<int, float|int> $seriesData
     * @param array<int, string> $dataStates
     * @param array<int, bool> $seriesDataAvailability
     */
    private function hasRecentNoDataPattern(
        array $seriesData,
        array $dataStates,
        array $seriesDataAvailability,
        int $currentTickIndex,
        int $requiredCount
    ): bool {
        if ($requiredCount <= 0) {
            return false;
        }

        $examined = 0;
        for ($i = $currentTickIndex - 1; $i >= 0 && $examined < $requiredCount; --$i) {
            $state = $dataStates[$i] ?? ArchiveState::COMPLETE;
            if (ArchiveState::COMPLETE !== $state) {
                continue;
            }

            $available = $seriesDataAvailability[$i] ?? true;
            $value = (float) ($seriesData[$i] ?? 0);
            if ($available && $value > 0.0) {
                return false;
            }
            ++$examined;
        }

        return $examined >= $requiredCount;
    }

    /**
     * Forecast for monotonic count series via sub-period decomposition. When the caller has
     * supplied the daily (and, for year targets, monthly) sample maps, the period's final value
     * is reconstructed from completed sub-periods (real archived values) plus same-DoW (or
     * same-MoY) analog projections for the in-progress and remaining sub-periods. The displayed
     * partial value is used only as a floor — it is never multiplied up by an elapsed-time
     * fraction — so the forecast does not change with the displayed graph range.
     *
     * Falls back to a prior-only same-period projection on $pastValues when sub-period samples
     * are not available, so the builder degrades gracefully on legacy callers and on day-period
     * targets (where there is no useful sub-period structure).
     *
     * The $dayProjections and $monthProjections out-params capture sub-period values produced
     * during this tick's decomposition (today's contribution + remaining-day analogs for week/
     * month, per-month projections for year, plus the day-target prior keyed under the day's
     * own anchor). The caller merges them into the running sample maps so that later incomplete
     * ticks in the same series see this tick's projections as analogs instead of the partial
     * (or zero) values that prefilled their slots in the original sample fetch.
     *
     * @param array<int, float> $pastValues
     * @param array<string, float> $dailySamples
     * @param array<string, float> $monthlySamples
     * @param array<string, float> $dayProjections
     * @param array<string, float> $monthProjections
     */
    private function buildMonotonicForecastValue(
        float $currentValue,
        array $pastValues,
        ?float $previousForecastValue,
        DataTable $dataTable,
        Site $site,
        array $dailySamples,
        array $monthlySamples,
        array &$dayProjections,
        array &$monthProjections
    ): ?float {
        $periodLabel = $this->getPeriodLabel($dataTable);
        $period = $this->getPeriod($dataTable);

        $seasonal = $this->buildSeasonalForecastValue(
            $dataTable,
            $periodLabel,
            $period,
            $currentValue,
            $dailySamples,
            $monthlySamples,
            $site,
            $dayProjections,
            $monthProjections
        );
        if ($seasonal !== null) {
            return $seasonal;
        }

        if ([] !== $pastValues) {
            $prior = $this->computeHistoricalPrior($pastValues);
            if (count($pastValues) >= self::MIN_SAMPLES_FOR_BOUNDED_RANGE) {
                $prior = $this->clampForecastToHistoricalRange($prior, $pastValues, $prior);
            }
            // Day-target prior-only path: record the day's forecast under its own anchor so a
            // later same-DoW day in this series picks it up via recentSameDoWValues instead of
            // walking back to a partial/zero entry the sample fetch left for this day.
            if ('day' === $periodLabel) {
                $dayProjections[$period->getDateStart()->toString('Y-m-d')] = $prior;
            }
            return $prior;
        }

        if ($previousForecastValue !== null) {
            if ('day' === $periodLabel) {
                $dayProjections[$period->getDateStart()->toString('Y-m-d')] = $previousForecastValue;
            }
            return $previousForecastValue;
        }

        return null;
    }

    /**
     * Run the seasonal-decomposition path when the caller has supplied the sub-period samples
     * needed for it. Returns null when the path does not apply (day target, no daily samples,
     * unsupported period), letting the caller fall back to the prior-only path.
     *
     * @param array<string, float> $dailySamples
     * @param array<string, float> $monthlySamples
     * @param array<string, float> $dayProjections
     * @param array<string, float> $monthProjections
     */
    private function buildSeasonalForecastValue(
        DataTable $dataTable,
        string $periodLabel,
        Period $period,
        float $currentValue,
        array $dailySamples,
        array $monthlySamples,
        Site $site,
        array &$dayProjections,
        array &$monthProjections
    ): ?float {
        switch ($periodLabel) {
            case 'week':
                if ([] === $dailySamples) {
                    return null;
                }
                return $this->forecastWeekSeasonal(
                    $dataTable,
                    $period,
                    $currentValue,
                    $dailySamples,
                    $site,
                    $dayProjections
                );
            case 'month':
                if ([] === $dailySamples) {
                    return null;
                }
                return $this->forecastMonthSeasonal(
                    $dataTable,
                    $period,
                    $currentValue,
                    $dailySamples,
                    $monthlySamples,
                    $site,
                    $dayProjections
                );
            case 'year':
                if ([] === $monthlySamples) {
                    return null;
                }
                return $this->forecastYearSeasonal(
                    $dataTable,
                    $period,
                    $currentValue,
                    $dailySamples,
                    $monthlySamples,
                    $site,
                    $dayProjections,
                    $monthProjections
                );
            default:
                return null;
        }
    }

    /**
     * Week forecast via daily decomposition. Completed days contribute their archived values;
     * the in-progress day and remaining days are projected from same-DoW analog samples.
     *
     * @param array<string, float> $dailySamples
     * @param array<string, float> $dayProjections
     */
    private function forecastWeekSeasonal(
        DataTable $dataTable,
        Period $weekPeriod,
        float $currentValue,
        array $dailySamples,
        Site $site,
        array &$dayProjections
    ): float {
        $weekStart = $weekPeriod->getDateStart();
        $siteTz = $site->getTimezone();

        $dayAnchors = [];
        for ($i = 0; $i < 7; ++$i) {
            $dayAnchors[$i] = $weekStart->addDay($i)->toString('Y-m-d');
        }

        $todayIdx = $this->resolveSubPeriodTodayIndex($dataTable, $dayAnchors, $siteTz);

        return $this->decomposeAndForecast(
            $dayAnchors,
            $todayIdx,
            $currentValue,
            $dailySamples,
            self::WEEK_ANALOG_CHUNK,
            1.0,
            $dayProjections
        );
    }

    /**
     * Month forecast via daily decomposition with month-of-year scaling. Same shape as the week
     * path but the calendar-day count varies (28-31). The same-DoW analog mean is scaled by a
     * month-of-year factor so a Feb forecast does not borrow Aug-level traffic from the rolling
     * day window.
     *
     * @param array<string, float> $dailySamples
     * @param array<string, float> $monthlySamples
     * @param array<string, float> $dayProjections
     */
    private function forecastMonthSeasonal(
        DataTable $dataTable,
        Period $monthPeriod,
        float $currentValue,
        array $dailySamples,
        array $monthlySamples,
        Site $site,
        array &$dayProjections
    ): float {
        $monthStart = $monthPeriod->getDateStart();
        $siteTz = $site->getTimezone();

        // 't' = days in the month containing $monthStart. Cheaper and DST-safe vs differencing
        // strtotime() of the boundaries, which a non-UTC process can off-by-one across a DST gap.
        $dayCount = (int) $monthStart->toString('t');

        $dayAnchors = [];
        for ($i = 0; $i < $dayCount; ++$i) {
            $dayAnchors[$i] = $monthStart->addDay($i)->toString('Y-m-d');
        }

        $todayIdx = $this->resolveSubPeriodTodayIndex($dataTable, $dayAnchors, $siteTz);

        $monthAnchor = $monthStart->toString('Y-m');
        $monthOfYearScale = $this->computeMonthOfYearScale(
            $monthAnchor,
            $monthlySamples,
            self::MONTH_ANALOG_CHUNK,
            $dailySamples
        );

        return $this->decomposeAndForecast(
            $dayAnchors,
            $todayIdx,
            $currentValue,
            $dailySamples,
            self::MONTH_ANALOG_CHUNK,
            $monthOfYearScale,
            $dayProjections
        );
    }

    /**
     * Year forecast via monthly decomposition. Completed months come from the monthly sample
     * map; the current month is estimated by recursing into the month seasonal path when daily
     * samples are available, and remaining months are projected from same-month-of-year
     * monthly analogs.
     *
     * @param array<string, float> $dailySamples
     * @param array<string, float> $monthlySamples
     * @param array<string, float> $dayProjections
     * @param array<string, float> $monthProjections
     */
    private function forecastYearSeasonal(
        DataTable $dataTable,
        Period $yearPeriod,
        float $currentValue,
        array $dailySamples,
        array $monthlySamples,
        Site $site,
        array &$dayProjections,
        array &$monthProjections
    ): float {
        $yearStart = $yearPeriod->getDateStart();
        $siteTz = $site->getTimezone();

        $monthAnchors = [];
        for ($i = 0; $i < 12; ++$i) {
            $monthAnchors[$i] = $yearStart->addMonth($i)->toString('Y-m');
        }

        $referenceTs = $this->resolveReferenceTimestamp($dataTable);
        // Calendar-aligned anchor lookup using the same {@see Date::adjustForTimezone()} primitive
        // {@see resolveSubPeriodTodayIndex()} relies on, so the day/month/year branches all derive
        // the in-progress sub-period from the same site-local 'Y-m' anchor surface. A
        // reference instant outside the displayed year (rare; only possible for an archive whose
        // ts_archived predates the year start) is treated as Dec, matching the previous behaviour.
        $referenceMonthAnchor = Date::factory(Date::adjustForTimezone($referenceTs, $siteTz))
            ->toString('Y-m');
        $idx = array_search($referenceMonthAnchor, $monthAnchors, true);
        $todayMonthIdx = (false === $idx) ? 11 : (int) $idx;

        $completedReal = 0.0;
        for ($i = 0; $i < $todayMonthIdx; ++$i) {
            $completedReal += $monthlySamples[$monthAnchors[$i]] ?? 0.0;
        }

        $currentMonthPartial = max(0.0, $currentValue - $completedReal);
        $currentMonthAnchorStr = $monthAnchors[$todayMonthIdx] . '-01';

        $currentMonthEstimate = null;
        if ([] !== $dailySamples) {
            $currentMonthEstimate = $this->forecastMonthSeasonal(
                $dataTable,
                new Month(Date::factory($currentMonthAnchorStr)),
                $currentMonthPartial,
                $dailySamples,
                $monthlySamples,
                $site,
                $dayProjections
            );
        }
        if ($currentMonthEstimate === null) {
            $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchors[$todayMonthIdx], self::YEAR_ANALOG_CHUNK);
            $currentMonthEstimate = !empty($samples)
                ? $this->computeHistoricalPrior($samples)
                : $currentMonthPartial;
        }
        $currentMonthEstimate = max($currentMonthEstimate, $currentMonthPartial);
        $monthProjections[$monthAnchors[$todayMonthIdx]] = $currentMonthEstimate;

        $remainingExpected = 0.0;
        for ($i = $todayMonthIdx + 1; $i < 12; ++$i) {
            $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchors[$i], self::YEAR_ANALOG_CHUNK);
            if (empty($samples)) {
                continue;
            }
            $projected = $this->computeHistoricalPrior($samples);
            $remainingExpected += $projected;
            $monthProjections[$monthAnchors[$i]] = $projected;
        }

        return $completedReal + $currentMonthEstimate + $remainingExpected;
    }

    /**
     * Shared completed/in-progress/remaining decomposition for the week and month paths.
     * "Today" is the sub-period containing the current site-local instant. Sub-periods before
     * today contribute their real archived values from $dailySamples. Today's contribution is
     * the larger of (the partial floor implied by currentValue minus completed real) and the
     * same-DoW analog prior — never an elapsed-time multiplication. Remaining sub-periods are
     * projected from same-DoW analogs reduced by the day-level reducer.
     *
     * Captures today's contribution and the remaining-day projections into $dayProjections so
     * the caller can feed them forward into the running daily samples map. Completed-day values
     * are not written back because they are already present in $dailySamples; only the values
     * the decomposition produced for this tick need to be added.
     *
     * @param array<int, string> $dayAnchors
     * @param array<string, float> $dailySamples
     * @param array<string, float> $dayProjections
     */
    private function decomposeAndForecast(
        array $dayAnchors,
        int $todayIdx,
        float $currentValue,
        array $dailySamples,
        int $analogChunk,
        float $analogScale,
        array &$dayProjections
    ): float {
        $completedReal = 0.0;
        for ($i = 0; $i < $todayIdx; ++$i) {
            $completedReal += $dailySamples[$dayAnchors[$i]] ?? 0.0;
        }

        $todayAnchor = $dayAnchors[$todayIdx];
        $todayPartial = max(0.0, $currentValue - $completedReal);

        $todayPriorSamples = $this->recentSameDoWValues($dailySamples, $todayAnchor, $analogChunk);
        $todayPriorScaled = ($analogScale === 1.0 || empty($todayPriorSamples))
            ? $todayPriorSamples
            : array_map(static function ($v) use ($analogScale) {
                return $v * $analogScale;
            }, $todayPriorSamples);

        if (!empty($todayPriorScaled)) {
            $todayPrior = $this->dayLevelAnalogPrior($todayPriorScaled);
            $todayContribution = max($todayPartial, $todayPrior);
        } else {
            $todayContribution = $todayPartial;
        }
        $dayProjections[$todayAnchor] = $todayContribution;

        $remainingExpected = 0.0;
        $count = count($dayAnchors);
        for ($i = $todayIdx + 1; $i < $count; ++$i) {
            $anchor = $dayAnchors[$i];
            $samples = $this->recentSameDoWValues($dailySamples, $anchor, $analogChunk);
            if (empty($samples)) {
                continue;
            }
            if ($analogScale !== 1.0) {
                $samples = array_map(static function ($v) use ($analogScale) {
                    return $v * $analogScale;
                }, $samples);
            }
            $projected = $this->dayLevelAnalogPrior($samples);
            $remainingExpected += $projected;
            $dayProjections[$anchor] = $projected;
        }

        return $completedReal + $todayContribution + $remainingExpected;
    }

    /**
     * Day-level analog reducer. Plain mean below MIN_SAMPLES_FOR_DAY_LEVEL_TREND (the slope of
     * a 2-3 sample fit on weekly-strided same-DoW values is dominated by noise and the envelope
     * clamp is not active to contain it); damped least-squares trend at and above the threshold.
     *
     * @param array<int, float> $samples
     */
    private function dayLevelAnalogPrior(array $samples): float
    {
        $n = count($samples);
        if ($n === 0) {
            return 0.0;
        }
        if ($n < self::MIN_SAMPLES_FOR_DAY_LEVEL_TREND) {
            return max(0.0, array_sum($samples) / $n);
        }
        return $this->computeHistoricalPrior($samples);
    }

    /**
     * Walk back 7 days at a time from the day before $targetAnchor, collecting up to $K samples
     * that exist in $dailySamples. Returned oldest-first so the trend fit lines up with
     * chronological order.
     *
     * @param array<string, float> $dailySamples
     * @return array<int, float>
     */
    private function recentSameDoWValues(array $dailySamples, string $targetAnchor, int $K): array
    {
        if (empty($dailySamples)) {
            return [];
        }
        // Drive the stride from Matomo's Date class so the cursor sequence stays calendar-aligned
        // regardless of the process timezone. The samples map is keyed by site-local anchors, so a
        // process-TZ stride could drift across midnight and skip or duplicate a key.
        $samples = [];
        $maxLookbackYears = max(1, $K);
        $cursor = Date::factory($targetAnchor)->subDay(7);
        $stop = Date::factory($targetAnchor)->subYear($maxLookbackYears);
        while (count($samples) < $K && $cursor->isLater($stop)) {
            $key = $cursor->toString('Y-m-d');
            if (isset($dailySamples[$key])) {
                $samples[] = (float) $dailySamples[$key];
            }
            $cursor = $cursor->subDay(7);
        }
        return array_reverse($samples);
    }

    /**
     * Walk back same-month-of-year entries from $monthlySamples. Keys are 'YYYY-MM'.
     *
     * @param array<string, float> $monthlySamples
     * @return array<int, float>
     */
    private function recentSameMoYValues(array $monthlySamples, string $targetMonthAnchor, int $K): array
    {
        if (empty($monthlySamples)) {
            return [];
        }
        $samples = [];
        $parts = explode('-', $targetMonthAnchor);
        if (count($parts) < 2) {
            return [];
        }
        $year = (int) $parts[0];
        $month = (int) $parts[1];
        $minYear = $year - max(1, $K) - 1;
        while (count($samples) < $K && $year > $minYear) {
            --$year;
            $key = sprintf('%04d-%02d', $year, $month);
            if (isset($monthlySamples[$key])) {
                $samples[] = (float) $monthlySamples[$key];
            }
        }
        return array_reverse($samples);
    }

    /**
     * Same-MoY level relative to the rolling-monthly baseline implied by the daily sample
     * window. Returns 1.0 when either side is missing or degenerate so the caller falls back
     * gracefully to the unscaled day-level mean.
     *
     * The denominator uses the daily sample sum (not the monthly index) because that is the
     * same surface the same-DoW analog reducer draws from -- so the ratio cancels the "average
     * month covered by day samples" out and leaves only the MoY effect.
     *
     * @param array<string, float> $monthlySamples
     * @param array<string, float> $dailySamples
     */
    private function computeMonthOfYearScale(
        string $monthAnchor,
        array $monthlySamples,
        int $analogChunk,
        array $dailySamples
    ): float {
        if (empty($monthlySamples)) {
            return 1.0;
        }
        $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchor, $analogChunk);
        if (empty($samples)) {
            return 1.0;
        }
        $numer = $this->computeHistoricalPrior($samples);
        if ($numer <= 0.0) {
            return 1.0;
        }

        if (empty($dailySamples)) {
            $monthValues = array_values($monthlySamples);
            $tail = array_slice($monthValues, -min(count($monthValues), 12));
            if (empty($tail)) {
                return 1.0;
            }
            $denom = array_sum($tail) / count($tail);
        } else {
            $sum = array_sum($dailySamples);
            if ($sum <= 0.0) {
                return 1.0;
            }
            // 30.4375 = average days per month over a 4-year cycle.
            $denom = ($sum / count($dailySamples)) * 30.4375;
        }
        if ($denom <= 0.0) {
            return 1.0;
        }
        return $numer / $denom;
    }

    /**
     * Return the index of the sub-period (within $dayAnchors) that contains the reference instant
     * in the site's timezone. Used to decide which sub-periods are "completed" (real) vs
     * "in-progress" / "future" (analog-projected).
     *
     * Calendar-aligned lookup against the anchor list rather than seconds arithmetic so a DST
     * transition mid-period (where one wall-clock day is 23h or 25h) cannot shift the index.
     *
     * @param array<int, string> $dayAnchors Site-local 'Y-m-d' strings for each sub-period, in
     *        chronological order. Must be non-empty.
     */
    private function resolveSubPeriodTodayIndex(DataTable $dataTable, array $dayAnchors, string $siteTz): int
    {
        $referenceTs = $this->resolveReferenceTimestamp($dataTable);
        // {@see Date::setTimezone()} reinterprets the wall-clock as belonging to a different
        // timezone rather than projecting a UTC instant into that timezone's wall-clock, so the
        // round-trip lands a calendar day off for far-offset sites. {@see Date::adjustForTimezone()}
        // shifts the UTC seconds so that the same wall-clock formatting in UTC reads as the
        // site-local wall-clock -- the same primitive
        // {@see \Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\Evolution::computeDataStates()}
        // uses to derive "today in site TZ" -- so the anchor lookup matches a site-local 'Y-m-d'.
        $referenceAnchor = Date::factory(Date::adjustForTimezone($referenceTs, $siteTz))->toString('Y-m-d');

        $idx = array_search($referenceAnchor, $dayAnchors, true);
        if (false !== $idx) {
            return (int) $idx;
        }

        if ($referenceAnchor < $dayAnchors[0]) {
            return 0;
        }
        return count($dayAnchors) - 1;
    }

    /**
     * Reference instant for "as of when is this incomplete tick being forecast". Defaults to
     * Date::now() so live charts always reflect the current state, but yields to a smaller
     * ARCHIVED_DATE_METADATA_NAME when present so historical archive runs (and tests) get a
     * deterministic forecast pinned to the moment the archive was produced.
     */
    private function resolveReferenceTimestamp(DataTable $dataTable): int
    {
        $referenceTs = Date::now()->getTimestampUTC();
        $archivedDateStr = $dataTable->getMetadata(DataTable::ARCHIVED_DATE_METADATA_NAME);
        if (!empty($archivedDateStr)) {
            $archivedTs = Date::factory($archivedDateStr)->getTimestampUTC();
            if ($archivedTs < $referenceTs) {
                $referenceTs = $archivedTs;
            }
        }
        return $referenceTs;
    }

    /**
     * Forecast for non-monotonic series (ratios, averages, latency-style). Returns the historical
     * same-period prior (trend-aware via computeHistoricalPrior), falling back to the previous
     * forecast if there is no prior. Returns null when neither signal is available because no
     * defensible value can be produced.
     *
     * Day-target ticks record the forecast under their own anchor in $dayProjections so a later
     * same-DoW tick in this series picks up this tick's forecast (not the partial value the
     * sub-period fetch left at this anchor) when its analog walk steps back through the running
     * daily map. Without that feedback channel the second of two same-DoW forecast days on a
     * ratio series like bounce_rate sees this tick's partial (early-hours, low-traffic) value
     * as a historical analog and the trend fit collapses.
     *
     * @param array<int, float> $pastValues
     * @param array<string, float> $dayProjections
     */
    private function buildNonMonotonicForecastValue(
        array $pastValues,
        ?float $previousForecastValue,
        DataTable $dataTable,
        array &$dayProjections
    ): ?float {
        $periodLabel = $this->getPeriodLabel($dataTable);

        if ([] !== $pastValues) {
            $forecast = $this->computeHistoricalPrior($pastValues);
            if ('day' === $periodLabel) {
                $dayProjections[$this->getPeriod($dataTable)->getDateStart()->toString('Y-m-d')] = $forecast;
            }
            return $forecast;
        }

        if ($previousForecastValue !== null) {
            if ('day' === $periodLabel) {
                $dayProjections[$this->getPeriod($dataTable)->getDateStart()->toString('Y-m-d')] = $previousForecastValue;
            }
            return $previousForecastValue;
        }

        return null;
    }

    /**
     * Same-period historical prior. With fewer than two samples the only signal is the single
     * value (or a flat mean). With two or more samples we apply a least-squares linear-trend
     * extrapolation projected one step forward, then dampen the projection by TREND_DAMPING so
     * noisy ratios do not runaway-extrapolate from a spurious slope. Catching sustained growth
     * or decline that a flat mean would systematically lag is the win; the damping is what keeps
     * that win from becoming a loss on volatile averages. The result is clamped to >= 0 because
     * every metric the builder serves (counts, percentages, durations) is non-negative; a
     * negative trend extrapolation past zero is never a defensible forecast.
     *
     * @param array<int, float> $pastValues Same-period historical samples in temporal order
     *        (oldest first), already filtered by availability. Leading zeros have been stripped
     *        only for MONOTONICITY_UP series, where they likely mark "tracking had not started
     *        yet"; for FREE/DOWN series a leading 0 is a legitimate observation (a real 0% rate,
     *        an actual running min of 0) and is retained.
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
     * @param string $monotonicity Per-series intra-period direction tag, one of the
     *        {@see Evolution::MONOTONICITY_*} constants. Drives whether leading zeros are
     *        stripped: only MONOTONICITY_UP treats them as "tracking had not started yet".
     *        For FREE/DOWN a leading 0 is kept as a legitimate observation.
     * @param array<string, float> $dailySamples Optional daily sample map (Y-m-d → value)
     *        covering enough history to populate the day-period prior. When supplied on a day
     *        target, the prior is built from same-DoW analogs walked back through this map
     *        instead of from the displayed range alone — short displays (4-7 day charts)
     *        otherwise carry at most one same-DoW history tick.
     * @return array<int, float>
     */
    private function getHistoricalSamplesForSeries(
        array $seriesData,
        array $dataTableList,
        array $dataStates,
        int $currentTickIndex,
        DataTable $currentDataTable,
        array $seriesDataAvailability = [],
        string $monotonicity = Evolution::MONOTONICITY_UP,
        array $dailySamples = []
    ): array {
        $allSamples = [];
        $alignedSamples = [];
        $periodLabel = $this->getPeriodLabel($currentDataTable);

        if ('day' === $periodLabel && [] !== $dailySamples) {
            $todayAnchor = $this->getPeriod($currentDataTable)->getDateStart()->toString('Y-m-d');
            $samples = $this->recentSameDoWValues(
                $dailySamples,
                $todayAnchor,
                self::DAY_PRIOR_TARGET_SAMPLES
            );

            if (Evolution::MONOTONICITY_UP === $monotonicity) {
                return $this->removeLeadingZeroSamples($samples);
            }

            return array_values($samples);
        }

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

            $isAligned = $this->isSamplePeriodCalendarAligned($currentDataTable, $dataTable, $periodLabel);

            if ('day' === $periodLabel) {
                // Daily series have strong day-of-week effects; mixing weekdays into a Saturday
                // forecast (or vice versa) is worse than working with a single same-DOW sample,
                // so the strict filter overrides the aligned/all fallback used for week/month.
                if (!$isAligned) {
                    continue;
                }
                $allSamples[] = $value;
                continue;
            }

            $allSamples[] = $value;

            if ($isAligned) {
                $alignedSamples[] = $value;
            }
        }

        $samples = (
            'day' !== $periodLabel
            && count($alignedSamples) >= self::MIN_ALIGNED_SAMPLES_TO_PREFER
        ) ? $alignedSamples : $allSamples;

        // Leading-zero stripping is only sound for additive counts where a leading 0 most
        // likely marks "tracking had not started yet". For MONOTONICITY_DOWN (running mins)
        // and MONOTONICITY_FREE (rates/averages) a leading 0 is a legitimate observation
        // (e.g. a real running min of 0, a 0% rate on a low-traffic day) and dropping it
        // would inflate the prior — for DOWN it tends to fail the forecast <= current gate
        // and silently suppress an otherwise-renderable forecast.
        if (Evolution::MONOTONICITY_UP === $monotonicity) {
            return $this->removeLeadingZeroSamples($samples);
        }

        return array_values($samples);
    }

    /**
     * True when the candidate sample is "calendar-aligned" to the current period: same
     * day-of-week for daily, same ISO week-of-year for weekly, same calendar month for monthly.
     * Year periods have no useful alignment (every prior tick is the same kind of period), so
     * the check returns true and lets the recency-only sample set carry the forecast.
     */
    private function isSamplePeriodCalendarAligned(
        DataTable $current,
        DataTable $candidate,
        string $periodLabel
    ): bool {
        switch ($periodLabel) {
            case 'day':
                return $this->getPeriodStartDayOfWeek($current) === $this->getPeriodStartDayOfWeek($candidate);
            case 'week':
                return $this->getPeriodStartIsoWeek($current) === $this->getPeriodStartIsoWeek($candidate);
            case 'month':
                return $this->getPeriodStartCalendarMonth($current) === $this->getPeriodStartCalendarMonth($candidate);
            default:
                return true;
        }
    }

    /**
     * Clamp a forecast value to a historical-range envelope around the prior projection. The
     * envelope is k * sigma wide (with a relative-spread floor so a perfectly stable history
     * does not collapse the band onto the prior). Used by the prior-only fallback to bound a
     * trend extrapolation that strays well outside the empirical history.
     *
     * @param array<int, float> $pastValues Historical samples used to size the envelope.
     */
    private function clampForecastToHistoricalRange(
        float $forecastValue,
        array $pastValues,
        float $priorForecast
    ): float {
        $sampleCount = count($pastValues);
        if ($sampleCount === 0) {
            return $forecastValue;
        }

        $mean = array_sum($pastValues) / $sampleCount;
        $variance = 0.0;
        foreach ($pastValues as $sample) {
            $variance += ($sample - $mean) ** 2;
        }
        $stdDev = sqrt($variance / $sampleCount);

        $minSpread = abs($mean) * self::BOUNDED_RANGE_MIN_RELATIVE_SPREAD;
        $halfWidth = max($stdDev, $minSpread) * self::BOUNDED_RANGE_SIGMAS;

        $lower = max(0.0, $priorForecast - $halfWidth);
        $upper = $priorForecast + $halfWidth;

        return max($lower, min($upper, $forecastValue));
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
        string $monotonicity
    ): bool {
        switch ($monotonicity) {
            case Evolution::MONOTONICITY_FREE:
                return true;
            case Evolution::MONOTONICITY_DOWN:
                return $forecastValue <= $currentDisplayValue;
            case Evolution::MONOTONICITY_UP:
            default:
                return $forecastValue >= $currentDisplayValue;
        }
    }

    private function getPeriod(DataTable $dataTable): Period
    {
        /** @var Period $period */
        $period = $dataTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        return $period;
    }

    private function getPeriodLabel(DataTable $dataTable): string
    {
        return $this->getPeriod($dataTable)->getLabel();
    }

    private function getPeriodStartDayOfWeek(DataTable $dataTable): string
    {
        return $this->getPeriod($dataTable)->getDateStart()->toString('N');
    }

    private function getPeriodStartIsoWeek(DataTable $dataTable): string
    {
        return $this->getPeriod($dataTable)->getDateStart()->toString('W');
    }

    private function getPeriodStartCalendarMonth(DataTable $dataTable): string
    {
        return $this->getPeriod($dataTable)->getDateStart()->toString('m');
    }
}
