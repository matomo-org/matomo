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
 * {@see ForecastMetricClassifier::MONOTONICITY_*}):
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
 * - MONOTONICITY_MAX — running-max series (max_actions, max_event_value): the mirror of the
 *   running-min case. The period value is the max over its sub-periods (never their sum), so
 *   the seasonal decomposition combines sub-periods with max(); the "forecast >= current" gate
 *   matches the additive case because a running max can only rise within the period.
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
     * Width of the historical-range envelope expressed in (robust) standard deviations of the
     * past samples — see {@see MAD_TO_SIGMA} for how the spread is estimated. Three sigmas covers
     * ~99.7% of normally-distributed history, so a forecast landing outside this band almost
     * certainly reflects an extrapolation artefact rather than a credible final value.
     */
    private const BOUNDED_RANGE_SIGMAS = 3.0;

    /**
     * Minimum half-width of the historical-range envelope expressed as a fraction of the sample
     * median. Without this floor a perfectly stable history (spread ≈ 0) would collapse the
     * envelope onto the centre and forbid any deviation, including the legitimate case where the
     * partial period is genuinely trending up or down.
     */
    private const BOUNDED_RANGE_MIN_RELATIVE_SPREAD = 0.05;

    /**
     * Scales the median absolute deviation onto the standard-deviation scale: for normally
     * distributed samples, sigma ≈ 1.4826 * MAD. The envelope spread is built from the MAD
     * rather than the raw standard deviation so a single recent spike — the case the clamp
     * exists to tame — cannot inflate the band and contain its own runaway extrapolation.
     */
    private const MAD_TO_SIGMA = 1.4826;

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
     * Number of most-recent same-period samples whose median defines the "current level" used to
     * detect a traffic level shift in {@see stripPreLevelShiftSamples()}. Matches
     * MIN_SAMPLES_FOR_BOUNDED_RANGE so the same window that must exist for the envelope clamp also
     * anchors the shift detector; four points is enough for a median to shrug off a single noisy
     * recent day without being so wide it reaches back across the very shift it is meant to detect.
     */
    private const RECENT_LEVEL_WINDOW = 4;

    /**
     * Fold-change threshold marking a same-period sample as belonging to a pre-shift traffic
     * regime rather than the current one, used by {@see stripPreLevelShiftSamples()}. A sample
     * more than this factor above or below the current level (so outside
     * [level / RATIO, level * RATIO]) reads as a discrete step change — a marketing, tracking or
     * seasonality regime the current period no longer belongs to — not the gradual drift the
     * damped linear trend is meant to model. 2.0 leaves a genuine trend (which does not double or
     * halve within a ten-week same-DoW window) untouched while catching the multi-fold steps that
     * otherwise drag the trend fit far below the current level.
     */
    private const LEVEL_SHIFT_RATIO = 2.0;

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
     * @param ForecastSeriesState $seriesState Per-series state collected upstream — data,
     *        availability, intra-period monotonicity, and forecast precision. Missing
     *        monotonicity entries fall back to FREE for percent-unit series and UP otherwise;
     *        missing precision entries preserve the historical 4-decimal default.
     * @param array<DataTable> $dataTables
     * @param array<int, string> $dataStates
     * @param array<string, string|false> $seriesUnits
     * @param array<string, array<string, float>> $allSeriesDailySamples Per-series map of
     *        Y-m-d → final daily value, covering enough history to populate same-DoW analog
     *        slots for the highest-tick week/month target. Required for MONOTONICITY_UP
     *        week/month forecasts; without it the builder falls back to prior-only same-period
     *        projection on the period-level series.
     * @param array<string, array<string, float>> $allSeriesMonthlySamples Per-series map of
     *        YYYY-MM → final monthly value, used by MONOTONICITY_UP year forecasts to project
     *        remaining months from same-month-of-year analogs.
     * @param string|null $earliestDataDate Earliest 'Y-m-d' the site/segment can hold data
     *        (site creation date, raised to an auto-archived segment's re-archive start). Analog
     *        samples dated before it are dropped from the day-period prior so a wide displayed
     *        range cannot resurrect pre-creation history the sub-period fetch already floors away
     *        — without it the day forecast flips on/off with the "rows to display" width. Null
     *        disables the floor (no resolvable creation date).
     * @return array<int, array<int, float|null>>
     */
    public function build(
        ForecastSeriesState $seriesState,
        array $dataTables,
        array $dataStates,
        array $seriesUnits,
        array $allSeriesDailySamples = [],
        array $allSeriesMonthlySamples = [],
        ?string $earliestDataDate = null
    ): array {
        $allSeriesData = $seriesState->getAllSeriesData();
        $allSeriesDataAvailability = $seriesState->getAllSeriesDataAvailability();
        $allSeriesMonotonicity = $seriesState->getAllSeriesMonotonicity();
        $allSeriesForecastPrecision = $seriesState->getAllSeriesForecastPrecision();

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
                ?? ($isPercentSeries ? ForecastMetricClassifier::MONOTONICITY_FREE : ForecastMetricClassifier::MONOTONICITY_UP);
        }

        // Process MONOTONICITY_UP series first so the cross-series gate (below) can read each
        // tick's count-series forecast before deciding whether dependent ratios/averages should
        // render. Output order is restored by indexing $forecastData on the original series
        // index and ksort'ing at the end.
        $processingOrder = array_keys($seriesDataList);
        usort($processingOrder, function ($a, $b) use ($resolvedMonotonicity) {
            $aRank = ForecastMetricClassifier::MONOTONICITY_UP === $resolvedMonotonicity[$a] ? 0 : 1;
            $bRank = ForecastMetricClassifier::MONOTONICITY_UP === $resolvedMonotonicity[$b] ? 0 : 1;
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
            $isUpSeries = ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity;
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
                    $runningDailySamples,
                    $earliestDataDate
                );

                $tickWindow = new ForecastSampleWindow($runningDailySamples, $runningMonthlySamples);

                $forecastValue = $this->buildForecastValue(
                    $currentValue,
                    $pastValues,
                    $previousForecastValue,
                    $monotonicity,
                    $dataTable,
                    $site,
                    $tickWindow
                );

                if ($forecastValue === null) {
                    $seriesForecasts[] = null;
                    $previousForecastValue = null;
                    continue;
                }

                // An additive count (UP) and a running max (MAX) share the same lower bound: the
                // final-period value is at least what has already been observed this period, so a
                // forecast below the current partial is impossible. Floor it at current so the
                // point renders flat instead of being suppressed by the >= gate below. This is
                // the mirror of the min_* clamp further down, but applied BEFORE the gate, because
                // for UP/MAX "below current" is a legitimate outcome to render at current, whereas
                // for min_* "above current" is an impossible value to suppress.
                //
                // For MAX the floored case means "the max will not grow further". For UP it is the
                // elapsed-blind prior-only fallback (day targets, or week/month without sub-period
                // samples) returning a static historical prior the partial has already overtaken;
                // this only happens late in the period, where current is most of the final value,
                // so flooring renders a guaranteed lower bound near the realised total rather than
                // dropping the in-progress point entirely. The seasonal decomposition path is
                // already >= current by construction and is unaffected.
                if (
                    ForecastMetricClassifier::MONOTONICITY_MAX === $monotonicity
                    || ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity
                ) {
                    $forecastValue = max($forecastValue, $currentValue);
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
                if (ForecastMetricClassifier::MONOTONICITY_DOWN === $monotonicity) {
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
                    foreach ($tickWindow->getDayProjections() as $anchor => $value) {
                        $runningDailySamples[$anchor] = (float) $value;
                    }
                }
                if ($feedMonthlyProjections) {
                    foreach ($tickWindow->getMonthProjections() as $anchor => $value) {
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
     * Single forecast-value entry point for all monotonicities. When sub-period samples are
     * available and the target is week/month/year, runs the seasonal-decomposition path with
     * a reducer chosen by monotonicity:
     *
     * - {@see ForecastMetricClassifier::MONOTONICITY_UP}: SUM completed sub-period totals +
     *   in-progress partial-floor + remaining analog projections (additive-count semantics).
     * - {@see ForecastMetricClassifier::MONOTONICITY_FREE}: AVG of completed daily rates +
     *   analog projections for in-progress and remaining sub-periods (rate-approximation
     *   semantics; unweighted-vs-traffic-weighted introduces a bounded relative error in
     *   exchange for stability of the forecast across changes to the displayed range).
     * - {@see ForecastMetricClassifier::MONOTONICITY_DOWN}: MIN over completed sub-period
     *   running mins + analog projections (min-as-min semantics).
     *
     * Falls back to a prior-only same-period projection on $pastValues (with envelope clamp
     * for UP and MAX — both run the damped linear-trend prior, so both carry the
     * trend-extrapolation runaway the clamp guards against) when sub-period samples are absent.
     * Final fallback is $previousForecastValue from the prior tick in this series.
     *
     * @param array<int, float> $pastValues
     */
    private function buildForecastValue(
        float $currentValue,
        array $pastValues,
        ?float $previousForecastValue,
        string $monotonicity,
        DataTable $dataTable,
        Site $site,
        ForecastSampleWindow $window
    ): ?float {
        $periodLabel = $this->getPeriodLabel($dataTable);
        $period = $this->getPeriod($dataTable);

        $seasonal = $this->buildSeasonalForecastValue(
            $dataTable,
            $periodLabel,
            $period,
            $currentValue,
            $monotonicity,
            $site,
            $window
        );
        if ($seasonal !== null) {
            return $seasonal;
        }

        if ([] !== $pastValues) {
            $prior = $this->computeHistoricalPrior($pastValues);
            if (
                (
                    ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity
                    || ForecastMetricClassifier::MONOTONICITY_MAX === $monotonicity
                )
                && count($pastValues) >= self::MIN_SAMPLES_FOR_BOUNDED_RANGE
            ) {
                $prior = $this->clampForecastToHistoricalRange($prior, $pastValues);
            }
            // Day-target prior-only path: record the day's forecast under its own anchor so a
            // later same-DoW day in this series picks it up via recentSameDoWValues instead of
            // walking back to a partial/zero entry the sample fetch left for this day.
            if ('day' === $periodLabel) {
                $window->projectDay($period->getDateStart()->toString('Y-m-d'), $prior);
            }
            return $prior;
        }

        if ($previousForecastValue !== null) {
            if ('day' === $periodLabel) {
                $window->projectDay($period->getDateStart()->toString('Y-m-d'), $previousForecastValue);
            }
            return $previousForecastValue;
        }

        return null;
    }

    /**
     * Run the seasonal-decomposition path for whichever monotonicity applies, when the caller
     * has supplied the sub-period samples it needs. Returns null when the path does not apply
     * (day target, no sub-period samples, unsupported period), letting the caller fall back
     * to the prior-only path on the displayed-range $pastValues.
     */
    private function buildSeasonalForecastValue(
        DataTable $dataTable,
        string $periodLabel,
        Period $period,
        float $currentValue,
        string $monotonicity,
        Site $site,
        ForecastSampleWindow $window
    ): ?float {
        switch ($periodLabel) {
            case 'week':
                if ([] === $window->getDailySamples()) {
                    return null;
                }
                return $this->forecastWeekSeasonal(
                    $dataTable,
                    $period,
                    $currentValue,
                    $monotonicity,
                    $site,
                    $window
                );
            case 'month':
                if ([] === $window->getDailySamples()) {
                    return null;
                }
                return $this->forecastMonthSeasonal(
                    $dataTable,
                    $period,
                    $currentValue,
                    $monotonicity,
                    $site,
                    $window
                );
            case 'year':
                if ([] === $window->getMonthlySamples()) {
                    return null;
                }
                return $this->forecastYearSeasonal(
                    $dataTable,
                    $period,
                    $currentValue,
                    $monotonicity,
                    $site,
                    $window
                );
            default:
                return null;
        }
    }

    /**
     * Week forecast via daily decomposition. Completed days contribute their archived values;
     * the in-progress day and remaining days are projected from same-DoW analog samples. The
     * combining reducer is chosen by monotonicity: SUM for UP, AVG for FREE, MIN for DOWN.
     */
    private function forecastWeekSeasonal(
        DataTable $dataTable,
        Period $weekPeriod,
        float $currentValue,
        string $monotonicity,
        Site $site,
        ForecastSampleWindow $window
    ): ?float {
        $weekStart = $weekPeriod->getDateStart();
        $siteTz = $site->getTimezone();

        $dayAnchors = [];
        for ($i = 0; $i < 7; ++$i) {
            $dayAnchors[$i] = $weekStart->addDay($i)->toString('Y-m-d');
        }

        $todayIdx = $this->resolveSubPeriodTodayIndex($dataTable, $dayAnchors, $siteTz);

        switch ($monotonicity) {
            case ForecastMetricClassifier::MONOTONICITY_FREE:
                return $this->decomposeAndAverageRate($dayAnchors, $todayIdx, self::WEEK_ANALOG_CHUNK, $window);
            case ForecastMetricClassifier::MONOTONICITY_DOWN:
                return $this->decomposeAndMinimize($dayAnchors, $todayIdx, self::WEEK_ANALOG_CHUNK, $window);
            case ForecastMetricClassifier::MONOTONICITY_MAX:
                return $this->decomposeAndMaximize($dayAnchors, $todayIdx, self::WEEK_ANALOG_CHUNK, $window);
            case ForecastMetricClassifier::MONOTONICITY_UP:
            default:
                return $this->decomposeAndForecast(
                    $dayAnchors,
                    $todayIdx,
                    $currentValue,
                    self::WEEK_ANALOG_CHUNK,
                    1.0,
                    $window
                );
        }
    }

    /**
     * Month forecast via daily decomposition. The UP path applies a month-of-year scale to
     * the analog daily counts so a Feb forecast does not borrow Aug-level traffic from the
     * rolling day window; FREE/DOWN paths skip the MoY scaling because rates/mins do not
     * scale proportionally with traffic volume (a 0.8 traffic ratio does not imply
     * bounce_rate × 0.8).
     */
    private function forecastMonthSeasonal(
        DataTable $dataTable,
        Period $monthPeriod,
        float $currentValue,
        string $monotonicity,
        Site $site,
        ForecastSampleWindow $window
    ): ?float {
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

        switch ($monotonicity) {
            case ForecastMetricClassifier::MONOTONICITY_FREE:
                return $this->decomposeAndAverageRate($dayAnchors, $todayIdx, self::MONTH_ANALOG_CHUNK, $window);
            case ForecastMetricClassifier::MONOTONICITY_DOWN:
                return $this->decomposeAndMinimize($dayAnchors, $todayIdx, self::MONTH_ANALOG_CHUNK, $window);
            case ForecastMetricClassifier::MONOTONICITY_MAX:
                // No month-of-year scaling: like MIN/AVG, a running max does not scale
                // proportionally with monthly traffic volume (a 0.8 traffic ratio does not
                // imply max_actions * 0.8).
                return $this->decomposeAndMaximize($dayAnchors, $todayIdx, self::MONTH_ANALOG_CHUNK, $window);
            case ForecastMetricClassifier::MONOTONICITY_UP:
            default:
                $monthAnchor = $monthStart->toString('Y-m');
                $monthOfYearScale = $this->computeMonthOfYearScale(
                    $monthAnchor,
                    self::MONTH_ANALOG_CHUNK,
                    $window
                );
                return $this->decomposeAndForecast(
                    $dayAnchors,
                    $todayIdx,
                    $currentValue,
                    self::MONTH_ANALOG_CHUNK,
                    $monthOfYearScale,
                    $window
                );
        }
    }

    /**
     * Year forecast via monthly decomposition. Same per-monotonicity reducer dispatch as the
     * week and month paths: SUM for UP, AVG for FREE, MIN for DOWN. Completed months come
     * from the monthly sample map; the current month is estimated by recursing into the
     * month seasonal path with the same monotonicity when daily samples are available;
     * remaining months are projected from same-month-of-year monthly analogs.
     */
    private function forecastYearSeasonal(
        DataTable $dataTable,
        Period $yearPeriod,
        float $currentValue,
        string $monotonicity,
        Site $site,
        ForecastSampleWindow $window
    ): ?float {
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

        if (ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity) {
            return $this->forecastYearSeasonalUp($dataTable, $monthAnchors, $todayMonthIdx, $currentValue, $site, $window);
        }

        return $this->forecastYearSeasonalAggregate(
            $dataTable,
            $monthAnchors,
            $todayMonthIdx,
            $monotonicity,
            $site,
            $window
        );
    }

    /**
     * UP-flavoured year decomposition: SUM completed monthly counts + current-month partial
     * floor + remaining same-MoY analog projections. Extracted from the year-level dispatch
     * so the dispatch reads cleanly.
     *
     * @param array<int, string> $monthAnchors
     */
    private function forecastYearSeasonalUp(
        DataTable $dataTable,
        array $monthAnchors,
        int $todayMonthIdx,
        float $currentValue,
        Site $site,
        ForecastSampleWindow $window
    ): float {
        $dailySamples = $window->getDailySamples();
        $monthlySamples = $window->getMonthlySamples();

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
                ForecastMetricClassifier::MONOTONICITY_UP,
                $site,
                $window
            );
        }
        if ($currentMonthEstimate === null) {
            $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchors[$todayMonthIdx], self::YEAR_ANALOG_CHUNK);
            $currentMonthEstimate = !empty($samples)
                ? $this->computeHistoricalPrior($samples)
                : $currentMonthPartial;
        }
        $currentMonthEstimate = max($currentMonthEstimate, $currentMonthPartial);
        $window->projectMonth($monthAnchors[$todayMonthIdx], $currentMonthEstimate);

        $remainingExpected = 0.0;
        for ($i = $todayMonthIdx + 1; $i < 12; ++$i) {
            $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchors[$i], self::YEAR_ANALOG_CHUNK);
            if (empty($samples)) {
                continue;
            }
            $projected = $this->computeHistoricalPrior($samples);
            $remainingExpected += $projected;
            $window->projectMonth($monthAnchors[$i], $projected);
        }

        return $completedReal + $currentMonthEstimate + $remainingExpected;
    }

    /**
     * FREE/DOWN-flavoured year decomposition: build per-month value list (archived monthly
     * value for completed months, recursive month-seasonal forecast for the current month,
     * same-MoY analog mean for remaining months) and combine with the monotonicity's reducer
     * (AVG for FREE, MIN for DOWN). No partial-floor: monthly rates and monthly mins do not
     * combine with the year's running value the way monthly counts do.
     *
     * @param array<int, string> $monthAnchors
     */
    private function forecastYearSeasonalAggregate(
        DataTable $dataTable,
        array $monthAnchors,
        int $todayMonthIdx,
        string $monotonicity,
        Site $site,
        ForecastSampleWindow $window
    ): ?float {
        $dailySamples = $window->getDailySamples();
        $monthlySamples = $window->getMonthlySamples();

        $monthlyValues = [];
        for ($i = 0; $i < $todayMonthIdx; ++$i) {
            if (isset($monthlySamples[$monthAnchors[$i]])) {
                $monthlyValues[] = (float) $monthlySamples[$monthAnchors[$i]];
            }
        }

        $currentMonthAnchorStr = $monthAnchors[$todayMonthIdx] . '-01';
        $currentMonthEstimate = null;
        if ([] !== $dailySamples) {
            // Recurse with the same monotonicity so the month-level decomposition uses AVG
            // or MIN as appropriate. currentValue is unused by those reducers, so 0.0 is fine.
            $currentMonthEstimate = $this->forecastMonthSeasonal(
                $dataTable,
                new Month(Date::factory($currentMonthAnchorStr)),
                0.0,
                $monotonicity,
                $site,
                $window
            );
        }
        if ($currentMonthEstimate === null) {
            $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchors[$todayMonthIdx], self::YEAR_ANALOG_CHUNK);
            if (!empty($samples)) {
                $currentMonthEstimate = $this->reduceMonthlySamples($samples, $monotonicity);
            }
        }
        if ($currentMonthEstimate !== null) {
            $monthlyValues[] = $currentMonthEstimate;
            $window->projectMonth($monthAnchors[$todayMonthIdx], $currentMonthEstimate);
        }

        for ($i = $todayMonthIdx + 1; $i < 12; ++$i) {
            $samples = $this->recentSameMoYValues($monthlySamples, $monthAnchors[$i], self::YEAR_ANALOG_CHUNK);
            if (empty($samples)) {
                continue;
            }
            $projected = $this->reduceMonthlySamples($samples, $monotonicity);
            $monthlyValues[] = $projected;
            $window->projectMonth($monthAnchors[$i], $projected);
        }

        if (empty($monthlyValues)) {
            return null;
        }

        return $this->reduceMonthlySamples($monthlyValues, $monotonicity);
    }

    /**
     * Combine same-month-of-year analog samples (and the per-month estimates derived from them)
     * for the year-aggregate path, by the reducer the monotonicity implies: MIN for running
     * mins, MAX for running maxes, and the unweighted mean for the FREE rate/average case.
     * MONOTONICITY_UP never reaches this helper -- additive year forecasts take the dedicated
     * {@see self::forecastYearSeasonalUp()} sum path instead.
     *
     * @param array<int, float> $samples Non-empty.
     */
    private function reduceMonthlySamples(array $samples, string $monotonicity): float
    {
        switch ($monotonicity) {
            case ForecastMetricClassifier::MONOTONICITY_DOWN:
                return min($samples);
            case ForecastMetricClassifier::MONOTONICITY_MAX:
                return max($samples);
            default:
                return array_sum($samples) / count($samples);
        }
    }

    /**
     * Shared completed/in-progress/remaining decomposition for the UP (additive count) week and
     * month paths. "Today" is the sub-period containing the current site-local instant.
     * Sub-periods before today contribute their real archived values from $dailySamples.
     * Today's contribution is the larger of (the partial floor implied by currentValue minus
     * completed real) and the same-DoW analog prior — never an elapsed-time multiplication.
     * Remaining sub-periods are projected from same-DoW analogs reduced by the day-level reducer.
     *
     * Captures today's contribution and the remaining-day projections onto $window so the
     * caller can feed them forward into the running daily samples map. Completed-day values
     * are not written back because they are already present in $window's daily samples; only
     * the values the decomposition produced for this tick need to be added.
     *
     * @param array<int, string> $dayAnchors
     */
    private function decomposeAndForecast(
        array $dayAnchors,
        int $todayIdx,
        float $currentValue,
        int $analogChunk,
        float $analogScale,
        ForecastSampleWindow $window
    ): float {
        $dailySamples = $window->getDailySamples();

        $completedReal = 0.0;
        for ($i = 0; $i < $todayIdx; ++$i) {
            $completedReal += $dailySamples[$dayAnchors[$i]] ?? 0.0;
        }

        $todayAnchor = $dayAnchors[$todayIdx];
        $todayPartial = max(0.0, $currentValue - $completedReal);

        $todayPrior = $this->projectDailyValueViaAnalog(
            $todayAnchor,
            $dailySamples,
            $analogChunk,
            $analogScale,
            null
        );
        $todayContribution = $todayPrior !== null ? max($todayPartial, $todayPrior) : $todayPartial;
        $window->projectDay($todayAnchor, $todayContribution);

        $remainingExpected = 0.0;
        $count = count($dayAnchors);
        for ($i = $todayIdx + 1; $i < $count; ++$i) {
            $projected = $this->projectDailyValueViaAnalog(
                $dayAnchors[$i],
                $dailySamples,
                $analogChunk,
                $analogScale,
                $window
            );
            if ($projected === null) {
                continue;
            }
            $remainingExpected += $projected;
        }

        return $completedReal + $todayContribution + $remainingExpected;
    }

    /**
     * Average-of-daily-rates decomposition for the FREE (rate, ratio, average) week and month
     * paths. Same shape as {@see self::decomposeAndForecast()} but the in-progress day is treated
     * as just another analog-projected day (no partial-floor max — the partial rate is not a
     * lower bound for the final rate the way a partial count is), and remaining days are
     * combined by unweighted mean instead of sum.
     *
     * The mean-of-daily-rates differs from the true traffic-weighted period rate by a bounded
     * relative error (Simpson's-paradox magnitude that depends on how traffic is distributed
     * across days) — accepted as the price for keeping the forecast stable across changes to
     * the displayed range. $analogScale is always 1.0 here: scaling daily *rates* by a
     * monthly-traffic ratio is conceptually wrong (a 0.8 month-of-year traffic ratio does not
     * imply bounce_rate × 0.8).
     *
     * @param array<int, string> $dayAnchors
     */
    private function decomposeAndAverageRate(
        array $dayAnchors,
        int $todayIdx,
        int $analogChunk,
        ForecastSampleWindow $window
    ): ?float {
        $dailySamples = $window->getDailySamples();
        $dailyValues = $this->collectCompletedAndProjectedDailyValues(
            $dayAnchors,
            $todayIdx,
            $dailySamples,
            $analogChunk,
            $window
        );
        if (empty($dailyValues)) {
            return null;
        }

        return array_sum($dailyValues) / count($dailyValues);
    }

    /**
     * Min-of-daily-mins decomposition for the DOWN (running-min) week and month paths. Same
     * collection shape as {@see self::decomposeAndAverageRate()} (in-progress day is analog-
     * projected, no partial-floor), but the combining operator is `min` because a running min
     * over a period is the min of the per-sub-period running mins by construction.
     *
     * @param array<int, string> $dayAnchors
     */
    private function decomposeAndMinimize(
        array $dayAnchors,
        int $todayIdx,
        int $analogChunk,
        ForecastSampleWindow $window
    ): ?float {
        $dailySamples = $window->getDailySamples();
        $dailyValues = $this->collectCompletedAndProjectedDailyValues(
            $dayAnchors,
            $todayIdx,
            $dailySamples,
            $analogChunk,
            $window
        );
        if (empty($dailyValues)) {
            return null;
        }

        return min($dailyValues);
    }

    /**
     * Max-of-daily-maxes decomposition for the MAX (running-max) week and month paths. Mirror
     * of {@see self::decomposeAndMinimize()}: a running max over a period is the max of the
     * per-sub-period running maxes by construction, so the combining operator is `max`. The
     * in-progress day is analog-projected (no partial-floor); the completed days contribute
     * their archived daily maxes.
     *
     * @param array<int, string> $dayAnchors
     */
    private function decomposeAndMaximize(
        array $dayAnchors,
        int $todayIdx,
        int $analogChunk,
        ForecastSampleWindow $window
    ): ?float {
        $dailySamples = $window->getDailySamples();
        $dailyValues = $this->collectCompletedAndProjectedDailyValues(
            $dayAnchors,
            $todayIdx,
            $dailySamples,
            $analogChunk,
            $window
        );
        if (empty($dailyValues)) {
            return null;
        }

        return max($dailyValues);
    }

    /**
     * Build the per-day value list the rate-/min-decomposition reducers consume: archived
     * daily values for completed days, same-DoW analog projections for the in-progress day
     * and remaining days. Records the analog projections on $window so later same-DoW ticks
     * in the same series pick them up.
     *
     * @param array<int, string> $dayAnchors
     * @param array<string, float> $dailySamples
     * @return array<int, float>
     */
    private function collectCompletedAndProjectedDailyValues(
        array $dayAnchors,
        int $todayIdx,
        array $dailySamples,
        int $analogChunk,
        ForecastSampleWindow $window
    ): array {
        $values = [];

        for ($i = 0; $i < $todayIdx; ++$i) {
            if (isset($dailySamples[$dayAnchors[$i]])) {
                $values[] = (float) $dailySamples[$dayAnchors[$i]];
            }
        }

        $count = count($dayAnchors);
        for ($i = $todayIdx; $i < $count; ++$i) {
            $projected = $this->projectDailyValueViaAnalog(
                $dayAnchors[$i],
                $dailySamples,
                $analogChunk,
                1.0,
                $window
            );
            if ($projected !== null) {
                $values[] = $projected;
            }
        }

        return $values;
    }

    /**
     * Project a single sub-period day's value from same-DoW analog samples. Returns null when
     * no analogs exist in the supplied $dailySamples. When $window is non-null the projection
     * is also recorded into the window's day-projection accumulator so later same-DoW ticks
     * in this series pick it up via the running daily map.
     *
     * @param array<string, float> $dailySamples
     */
    private function projectDailyValueViaAnalog(
        string $anchor,
        array $dailySamples,
        int $analogChunk,
        float $analogScale,
        ?ForecastSampleWindow $window
    ): ?float {
        $samples = $this->recentSameDoWValues($dailySamples, $anchor, $analogChunk);
        if (empty($samples)) {
            return null;
        }
        if ($analogScale !== 1.0) {
            $samples = array_map(static function ($v) use ($analogScale) {
                return $v * $analogScale;
            }, $samples);
        }
        $value = $this->dayLevelAnalogPrior($samples);
        if ($window !== null) {
            $window->projectDay($anchor, $value);
        }
        return $value;
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
     */
    private function computeMonthOfYearScale(
        string $monthAnchor,
        int $analogChunk,
        ForecastSampleWindow $window
    ): float {
        $monthlySamples = $window->getMonthlySamples();
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

        $dailySamples = $window->getDailySamples();
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
     *        {@see ForecastMetricClassifier::MONOTONICITY_*} constants. Drives whether leading zeros are
     *        stripped: only MONOTONICITY_UP treats them as "tracking had not started yet".
     *        For FREE/DOWN a leading 0 is kept as a legitimate observation.
     * @param array<string, float> $dailySamples Optional daily sample map (Y-m-d → value)
     *        covering enough history to populate the day-period prior. When supplied on a day
     *        target, the prior is built from same-DoW analogs walked back through this map
     *        instead of from the displayed range alone — short displays (4-7 day charts)
     *        otherwise carry at most one same-DoW history tick.
     * @param string|null $earliestDataDate Earliest 'Y-m-d' the site/segment can hold data.
     *        Same-period analog samples dated before it are dropped so the day prior does not
     *        depend on how far back the displayed range happens to reach: the sub-period fetch
     *        already floors its own window here, but the displayed-range map and the legacy
     *        dataTableList walk are not fetched through that floor, so a wide "rows to display"
     *        would otherwise pull in pre-creation history and flip the forecast on. Null skips
     *        the floor.
     * @return array<int, float>
     */
    private function getHistoricalSamplesForSeries(
        array $seriesData,
        array $dataTableList,
        array $dataStates,
        int $currentTickIndex,
        DataTable $currentDataTable,
        array $seriesDataAvailability = [],
        string $monotonicity = ForecastMetricClassifier::MONOTONICITY_UP,
        array $dailySamples = [],
        ?string $earliestDataDate = null
    ): array {
        $allSamples = [];
        $alignedSamples = [];
        $periodLabel = $this->getPeriodLabel($currentDataTable);

        if ('day' === $periodLabel && [] !== $dailySamples) {
            if (null !== $earliestDataDate) {
                foreach (array_keys($dailySamples) as $sampleDate) {
                    if (strcmp((string) $sampleDate, $earliestDataDate) < 0) {
                        unset($dailySamples[$sampleDate]);
                    }
                }
            }
            $todayAnchor = $this->getPeriod($currentDataTable)->getDateStart()->toString('Y-m-d');
            $samples = $this->recentSameDoWValues(
                $dailySamples,
                $todayAnchor,
                self::DAY_PRIOR_TARGET_SAMPLES
            );

            if (ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity) {
                return $this->stripPreLevelShiftSamples($this->removeLeadingZeroSamples($samples));
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

            // Floor the walk at the earliest date the site/segment can hold data. The displayed
            // range is not fetched through the sub-period fetcher's creation-date clamp, so
            // without this a wide "rows to display" pulls pre-creation ticks into the prior and
            // makes the forecast depend on the displayed width. Compared on the period start so a
            // period straddling the creation date is kept.
            if (
                null !== $earliestDataDate
                && strcmp($this->getPeriod($dataTable)->getDateStart()->toString('Y-m-d'), $earliestDataDate) < 0
            ) {
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
        if (ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity) {
            return $this->removeLeadingZeroSamples($samples);
        }

        return $samples;
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
     * Clamp a trend extrapolation to a robust historical-range envelope. Both the centre and the
     * spread are robust statistics of the past samples, so a single recent spike — the case the
     * clamp exists to tame — can neither shift the band onto itself nor inflate it wide enough to
     * contain its own runaway extrapolation:
     *
     *  - Centre is the sample *median*, not the value being clamped. The extrapolation is the
     *    noisy term we want to bound, so an envelope built around it (the previous behaviour)
     *    always contained it and never fired.
     *  - Spread is the median absolute deviation scaled onto the sigma scale, not the raw
     *    standard deviation. A lone outlier barely moves the MAD but blows up the std, which
     *    would widen a std-based band past the outlier-driven prior and defeat the clamp.
     *
     * A genuine sustained trend still produces enough MAD spread — plus the relative-spread
     * floor — to pass through untouched. Used by the prior-only fallback.
     *
     * @param array<int, float> $pastValues Historical samples used to size and centre the envelope.
     */
    private function clampForecastToHistoricalRange(float $forecastValue, array $pastValues): float
    {
        if ([] === $pastValues) {
            return $forecastValue;
        }

        $center = $this->median($pastValues);

        $absoluteDeviations = [];
        foreach ($pastValues as $sample) {
            $absoluteDeviations[] = abs($sample - $center);
        }
        $spread = $this->median($absoluteDeviations) * self::MAD_TO_SIGMA;

        $minSpread = abs($center) * self::BOUNDED_RANGE_MIN_RELATIVE_SPREAD;
        $halfWidth = max($spread, $minSpread) * self::BOUNDED_RANGE_SIGMAS;

        $lower = max(0.0, $center - $halfWidth);
        $upper = $center + $halfWidth;

        return max($lower, min($upper, $forecastValue));
    }

    /**
     * Median of an unordered sample list. Even counts return the mean of the two central values.
     *
     * @param array<int, float> $values Non-empty sample list.
     */
    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $mid = intdiv($count, 2);

        if ($count % 2 === 1) {
            return (float) $values[$mid];
        }

        return ((float) $values[$mid - 1] + (float) $values[$mid]) / 2.0;
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

    /**
     * Strip the leading (oldest) run of same-period samples that sit on the far side of a traffic
     * level shift from the current level, so the damped linear-trend prior fits the current
     * regime instead of reading the pre-shift level as a steep ongoing trend.
     *
     * Without this, a site whose traffic stepped (e.g. a 3.5x drop) partway through the same-DoW
     * window collapses: the fit runs a line from the old high samples down through the recent low
     * ones and projects well below the current level, which the envelope clamp then only floors at
     * a low bound. The current level is the median of the most recent {@see RECENT_LEVEL_WINDOW}
     * samples; any unbroken run of oldest samples more than {@see LEVEL_SHIFT_RATIO} away from it
     * (either direction) is dropped. A gradual trend never crosses that ratio within the window so
     * it survives untouched — only a discrete multi-fold step is trimmed. Mirrors
     * {@see removeLeadingZeroSamples()}, which strips a different kind of unrepresentative leading
     * run (pre-tracking zeros).
     *
     * Skipped below RECENT_LEVEL_WINDOW samples (too few to tell a shift from noise) and when the
     * recent level is <= 0 (the trailing-no-data path handles a genuinely empty recent window).
     * Never trims into the recent-level window itself, so at least those samples always remain.
     *
     * @param array<int, float> $samples Oldest-first.
     * @return array<int, float>
     */
    private function stripPreLevelShiftSamples(array $samples): array
    {
        $count = count($samples);
        if ($count < self::RECENT_LEVEL_WINDOW) {
            return $samples;
        }

        $recentLevel = $this->median(array_slice($samples, -self::RECENT_LEVEL_WINDOW));
        if ($recentLevel <= 0.0) {
            return $samples;
        }

        $lower = $recentLevel / self::LEVEL_SHIFT_RATIO;
        $upper = $recentLevel * self::LEVEL_SHIFT_RATIO;
        $maxTrim = $count - self::RECENT_LEVEL_WINDOW;

        $trim = 0;
        while ($trim < $maxTrim && ($samples[$trim] < $lower || $samples[$trim] > $upper)) {
            ++$trim;
        }

        return array_slice($samples, $trim);
    }

    private function shouldRenderForecastValue(
        float $forecastValue,
        float $currentDisplayValue,
        string $monotonicity
    ): bool {
        switch ($monotonicity) {
            case ForecastMetricClassifier::MONOTONICITY_FREE:
                return true;
            case ForecastMetricClassifier::MONOTONICITY_DOWN:
                return $forecastValue <= $currentDisplayValue;
            case ForecastMetricClassifier::MONOTONICITY_MAX:
            case ForecastMetricClassifier::MONOTONICITY_UP:
            default:
                // A running max can only rise within the period, so the final value cannot fall
                // below the current partial max -- same gate as the additive UP case.
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
