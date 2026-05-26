<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\JqplotDataGenerator;

use Piwik\API\Request as ApiRequest;
use Piwik\Archive\ArchiveState;
use Piwik\Archive\DataTableFactory;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Period;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\CoreVisualizations\Metrics\Formatter\Numeric;

/**
 * Fetches the additional historical data {@see ForecastBuilder} consumes for seasonal
 * decomposition: a 70-day daily window plus, for year-level forecasts, a multi-year monthly
 * window. Each request is an inner API call against the same module/method/idSite/segment
 * the displayed series came from, narrowed to a finer period granularity.
 *
 * Errors in the inner fetch (unsupported API method, archive errors, missing parameters)
 * fall back to empty maps so the builder can still emit a prior-only same-period projection
 * from the period-level series alone. Sustained failures are logged so a dip in forecast
 * quality is investigable instead of silent.
 *
 * The API request and logger are constructor-injected so tests can substitute deterministic
 * fixtures without touching the Matomo bootstrap.
 */
class ForecastSubPeriodFetcher
{
    /**
     * Day-target analog window expressed in days. {@see ForecastBuilder::recentSameDoWValues()}
     * walks back at most {@see ForecastBuilder::DAY_PRIOR_TARGET_SAMPLES} same-DoW samples in
     * 7-day strides, so the deepest history a day-target forecast can consume is K × 7 days
     * before the incomplete tick. The fetcher uses this both as the window-size for the
     * inner request and as the threshold above which the displayed range alone can supply
     * the analog window (so no inner request is needed).
     */
    private const DAY_ANALOG_WINDOW_DAYS = 70;

    /**
     * Week-target daily window. The week seasonal decomposition needs same-DoW analog samples
     * for each remaining day in the in-progress week, with {@see ForecastBuilder::WEEK_ANALOG_CHUNK}
     * = 3 samples per slot stepped back 7 days each: that is 21 days of history before the
     * in-progress week's start, plus the in-progress week itself (≤7 days) = 28 days minimum.
     * 30 keeps 2 days of slack for the week-starts-on-Saturday edge and avoids cutting any
     * future {@see ForecastBuilder::WEEK_ANALOG_CHUNK} bump up to 4 too tight.
     */
    private const WEEK_DAILY_WINDOW_DAYS = 30;

    /**
     * Month-target daily window. Same shape as the week case but with
     * {@see ForecastBuilder::MONTH_ANALOG_CHUNK} = 4 samples per slot = 28 days of history
     * before the in-progress month's start, plus a 31-day month = 59 days minimum. 60 covers
     * that exactly and gives one day of slack for the boundary case.
     */
    private const MONTH_DAILY_WINDOW_DAYS = 60;

    /**
     * Month-target monthly window. {@see ForecastBuilder::computeMonthOfYearScale()} only
     * needs the same-MoY trend numerator, and {@see ForecastBuilder::computeHistoricalPrior()}
     * already produces a trend fit from 2 samples (least-squares on 2 points = the line
     * through both, plus damping). 1 year collapses the same-MoY history to a single value
     * with no slope to fit; 2 is the smallest window that gives the prior a usable slope.
     * Larger windows do not feed additional samples into the same-MoY walk.
     */
    private const MONTH_MONTHLY_WINDOW_YEARS = 2;

    /**
     * Year-target daily window. {@see ForecastBuilder::forecastYearSeasonal()} recurses into
     * {@see ForecastBuilder::forecastMonthSeasonal()} for the in-progress month, so the
     * daily history needed is the same as the month target's: 28 days of history + 31-day
     * month = 59 minimum, sized to 60.
     */
    private const YEAR_DAILY_WINDOW_DAYS = 60;

    /**
     * Year-target monthly window. {@see ForecastBuilder::forecastYearSeasonal()} walks
     * {@see ForecastBuilder::YEAR_ANALOG_CHUNK} = 8 same-MoY samples back through the
     * monthly map, so the minimum is 8 years. Going wider lengthens the inner archive
     * lookup without feeding additional samples to the analog walk.
     */
    private const YEAR_MONTHLY_WINDOW_YEARS = 8;

    /** @var callable(string, array<string, mixed>): mixed */
    private $apiRequestProcessor;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param callable(string, array<string, mixed>): mixed|null $apiRequestProcessor
     *        Inner-request driver. Receives the API method name and a parameter array, returns
     *        whatever the API method returns (expected to be a {@see DataTable\Map} for
     *        usable responses; anything else is treated as an empty sample set). Null uses
     *        {@see ApiRequest::processRequest()}.
     */
    public function __construct(?callable $apiRequestProcessor = null, ?LoggerInterface $logger = null)
    {
        $this->apiRequestProcessor = $apiRequestProcessor ?? static function (string $apiMethod, array $params) {
            return ApiRequest::processRequest($apiMethod, $params);
        };
        $this->logger = $logger ?? StaticContainer::get(LoggerInterface::class);
    }

    /**
     * Fetch sub-period samples for the displayed evolution series. Returns empty maps when
     * the displayed period type does not need them (e.g. non-day/week/month/year) or when
     * the inner request cannot be issued (no API method, no idSite, etc.).
     *
     * @param array<DataTable> $dataTables Per-tick tables of the displayed series, ordered by
     *        date. Used to read the period type and the end date of the displayed range.
     * @param ForecastSeriesState $seriesState Per-series metadata (columns, rows, monotonicity)
     *        threaded through to {@see self::extractSamples()}.
     * @param string $apiMethod API method spec to fan out to (`Module.action`).
     * @param int $idSite Site id the displayed series belongs to.
     * @param string $segment Segment expression to pin onto the inner request.
     * @return array{daily: array<string, array<string, float>>, monthly: array<string, array<string, float>>}
     */
    public function collect(
        array $dataTables,
        ForecastSeriesState $seriesState,
        string $apiMethod,
        int $idSite,
        string $segment
    ): array {
        $empty = ['daily' => [], 'monthly' => []];

        if (empty($dataTables) || [] === $seriesState->getAllSeriesColumns()) {
            return $empty;
        }

        if (empty($apiMethod) || strpos($apiMethod, '.') === false) {
            return $empty;
        }

        if ($idSite <= 0) {
            return $empty;
        }

        $firstTable = reset($dataTables);
        $period = $firstTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        if (!$period instanceof Period) {
            return $empty;
        }
        $periodLabel = $period->getLabel();

        $lastTable = end($dataTables);
        $lastPeriod = $lastTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
        if (!$lastPeriod instanceof Period) {
            return $empty;
        }
        $endDate = $lastPeriod->getDateEnd()->subDay(1)->toString('Y-m-d');

        try {
            if ('day' === $periodLabel) {
                // Day-period forecasts cannot decompose into sub-periods, so the prior-only
                // path is the only forecast surface. The analog walk in ForecastBuilder
                // collects up to DAY_ANALOG_WINDOW_DAYS / 7 same-DoW samples from the day
                // before the target back through DAY_ANALOG_WINDOW_DAYS days. The displayed
                // $dataTables already carry the same archived per-day values for the
                // displayed range, so the inner request only needs to fill the *gap* between
                // the analog window start and the displayed range start. When the displayed
                // range itself already spans the analog window (e.g. evolution_day_last_n >= 70),
                // no inner request fires at all.
                $firstDisplayedDate = $period->getDateStart()->toString('Y-m-d');
                $displayDailyMap = $this->extractDisplayedDailyMap($dataTables, $seriesState);

                $analogWindowStart = Date::factory($endDate)
                    ->subDay(self::DAY_ANALOG_WINDOW_DAYS)
                    ->toString('Y-m-d');

                if (strcmp($firstDisplayedDate, $analogWindowStart) <= 0) {
                    // Displayed range covers (or exceeds) the analog window. Skip the fetch.
                    return ['daily' => $displayDailyMap, 'monthly' => []];
                }

                $gapEndDate = Date::factory($firstDisplayedDate)->subDay(1)->toString('Y-m-d');
                $gapDailyMap = $this->fetchSeries(
                    $apiMethod,
                    $idSite,
                    $segment,
                    'day',
                    $analogWindowStart,
                    $gapEndDate,
                    $seriesState
                );

                return [
                    'daily'   => $this->mergeDailyMaps($gapDailyMap, $displayDailyMap),
                    'monthly' => [],
                ];
            }
            if ('week' === $periodLabel || 'month' === $periodLabel) {
                // Daily window sized to what the seasonal-decomposition path actually consumes:
                // WEEK_DAILY_WINDOW_DAYS for week (in-progress week + same-DoW × 3 history),
                // MONTH_DAILY_WINDOW_DAYS for month (in-progress month + same-DoW × 4 history).
                $dailyWindow = 'week' === $periodLabel
                    ? self::WEEK_DAILY_WINDOW_DAYS
                    : self::MONTH_DAILY_WINDOW_DAYS;
                $startDate = (Date::factory($endDate))->subDay($dailyWindow)->toString('Y-m-d');

                $dailyMap = $this->fetchSeries(
                    $apiMethod,
                    $idSite,
                    $segment,
                    'day',
                    $startDate,
                    $endDate,
                    $seriesState
                );

                // Monthly fan-out on the month target is consumed only by the UP-flavoured
                // {@see ForecastBuilder::computeMonthOfYearScale()}; FREE/DOWN month forecasts
                // skip the MoY scaling because scaling rates/mins by a traffic ratio is
                // conceptually wrong. Skip the inner request when no UP series is on this
                // graph -- the data would have no consumer.
                $monthlyMap = [];
                if ('month' === $periodLabel && $this->seriesStateHasUpSeries($seriesState)) {
                    $monthlyMap = $this->fetchSeries(
                        $apiMethod,
                        $idSite,
                        $segment,
                        'month',
                        $this->yearsBack($endDate, self::MONTH_MONTHLY_WINDOW_YEARS),
                        $endDate,
                        $seriesState
                    );
                }

                return [
                    'daily'   => $dailyMap,
                    'monthly' => $monthlyMap,
                ];
            }
            if ('year' === $periodLabel) {
                $dailyStart = (Date::factory($endDate))->subDay(self::YEAR_DAILY_WINDOW_DAYS)->toString('Y-m-d');
                return [
                    'daily'   => $this->fetchSeries($apiMethod, $idSite, $segment, 'day', $dailyStart, $endDate, $seriesState),
                    'monthly' => $this->fetchSeries($apiMethod, $idSite, $segment, 'month', $this->yearsBack($endDate, self::YEAR_MONTHLY_WINDOW_YEARS), $endDate, $seriesState),
                ];
            }
        } catch (\Throwable $e) {
            // Defensive: any error in the parallel fetch falls back to the prior-only path.
            // The seasonal-decomposition advantage is lost on this render, but the forecast
            // still renders something defensible from the displayed series alone. Log so a
            // sustained dip in forecast quality is investigable instead of silent.
            $this->logger->info(
                'Evolution forecast sub-period fetch failed for {apiMethod} (idSite={idSite}, period={period}): {message}',
                [
                    'apiMethod' => $apiMethod,
                    'idSite'    => $idSite,
                    'period'    => $periodLabel,
                    'message'   => $e->getMessage(),
                    'exception' => $e,
                ]
            );
        }

        return $empty;
    }

    /**
     * Issue a single sub-period API request and shape the result into a series-keyed map of
     * date → value. Date keys are 'Y-m-d' for day targets and 'Y-m' for month targets,
     * matching what {@see ForecastBuilder}'s analog walks expect. The returned map keys by
     * series label so the builder's per-series lookup hits a populated entry, while the API
     * row lookup uses the raw archive column name (after ReplaceColumnNames).
     *
     * @return array<string, array<string, float>>
     */
    private function fetchSeries(
        string $apiMethod,
        int $idSite,
        string $segment,
        string $subPeriod,
        string $startDate,
        string $endDate,
        ForecastSeriesState $seriesState
    ): array {
        // Use processRequest() rather than `new ApiRequest([...])` so the inner fetch picks
        // up its `compare=0` / `format=original` / `serialize=0` defaults and stays aligned
        // with the recent core convention for inner API calls. Inheritance from $_GET + $_POST
        // is intentional: this code path runs inside an already-authorized evolution-graph
        // render, so outer-URL scoping params (idGoal for Goals reports, idDimension for
        // CustomDimensions, and similar plugin-specific selectors) must reach the inner
        // request for the historical samples to come from the same series the user is
        // looking at. The `isComparing` guard upstream prevents comparison-mode leakage;
        // the explicit overrides below pin everything else the forecast needs in fixed form.
        $result = ($this->apiRequestProcessor)($apiMethod, [
            'idSite'                  => $idSite,
            'period'                  => $subPeriod,
            'date'                    => $startDate . ',' . $endDate,
            'segment'                 => $segment,
            'filter_limit'            => -1,
            'disable_generic_filters' => 1,
            // Pin raw, number-valued metrics regardless of the outer URL's format_metrics.
            // With format_metrics=1 the inner request would run the *base* string formatter
            // (e.g. bandwidth -> "3 G", a string unusable for the decomposition arithmetic)
            // and stamp the PROCESSED_METRICS_FORMATTED_FLAG, which would then suppress the
            // Numeric pass below. format_metrics=0 keeps every column a raw numeric value
            // and leaves the flag unset so the Numeric pass is free to run.
            'format_metrics'          => 0,
        ]);

        if (!$result instanceof DataTable\Map) {
            return [];
        }

        // Bring the inner samples onto the same scale the outer evolution chart plots in. The
        // outer is rendered by {@see \Piwik\Plugins\CoreVisualizations\Visualizations\Graph},
        // which formats with the {@see Numeric} formatter (format-as-number, not as string):
        // {@see \Piwik\Plugin\ProcessedMetric} columns are rewritten in place -- bandwidth
        // bytes divided by 1024^3 onto the chart's fixed 'G' axis, percent quotients scaled
        // to whole percent, etc. The inner request above is pinned to raw numeric units, so
        // without this pass the forecast would sum analog bytes-per-day against an outer
        // current value already in gigabytes, the ~10^9 blowup seen on bandwidth forecasts.
        // Running the identical Numeric pass here mirrors that transformation row-for-row so
        // the seasonal decomposition stays in one unit system.
        $report = $this->resolveReportForFormatting($apiMethod);
        $formatter = new Numeric();
        foreach ($result->getDataTables() as $subTable) {
            $formatter->formatMetrics($subTable, $report);
        }

        return $this->extractSamples($result, $seriesState, $subPeriod);
    }

    /**
     * Resolve the {@see \Piwik\Plugin\Report} the formatter consults to discover which
     * columns carry a {@see \Piwik\Plugin\ProcessedMetric} (and therefore need format()
     * applied). API methods without a registered report (custom dimensions, plugin-defined
     * report-less endpoints) yield null; {@see Numeric::formatMetrics()} then only acts on
     * processed metrics carried in the table's own EXTRA_PROCESSED_METRICS_METADATA_NAME,
     * which is the same surface the outer formatter sees.
     */
    private function resolveReportForFormatting(string $apiMethod): ?\Piwik\Plugin\Report
    {
        $parts = explode('.', $apiMethod, 2);
        if (count($parts) !== 2) {
            return null;
        }

        return ReportsProvider::factory($parts[0], $parts[1]);
    }

    /**
     * Shape a sub-period DataTable\Map into a series-keyed map of date → value. The result of
     * the inner API request has already been through ReplaceColumnNames so its row columns are
     * the raw archive column names; we look up by raw name and store under the series label so
     * ForecastBuilder's per-series lookup hits the right entry.
     *
     * Each series carries its own row matcher in $seriesState. Multi-row evolution graphs
     * (selectable_rows on a non-summary report) plot one series per selected row, so the
     * historical sample for each series must come from that series' own row in the sub-period
     * archive -- not from {@see DataTable::getFirstRow()}, which would silently pin every
     * series to whichever row sorts first in that sub-table (typically the top-ranked row).
     * `false` matchers fall through to getFirstRow() to keep the single-row default behaviour.
     *
     * Missing monotonicity entries fall back to MONOTONICITY_UP so the legacy backfill
     * behaviour survives for callers that have not propagated the classifier output yet.
     *
     * @return array<string, array<string, float>>
     */
    public function extractSamples(
        DataTable\Map $result,
        ForecastSeriesState $seriesState,
        string $subPeriod
    ): array {
        return $this->extractSamplesFromTables($result->getDataTables(), $seriesState, $subPeriod);
    }

    /**
     * Same extraction as {@see self::extractSamples()} but reads from a plain array of
     * sub-tables. Lets the day-target path in {@see self::collect()} reuse the same
     * column-name + row-matcher walk against the already-loaded displayed `$dataTables`,
     * so the inner API request can be skipped when the displayed range alone covers the
     * analog window.
     *
     * @param array<DataTable> $subTables
     * @return array<string, array<string, float>>
     */
    private function extractSamplesFromTables(
        array $subTables,
        ForecastSeriesState $seriesState,
        string $subPeriod
    ): array {
        $samples = [];
        $rowKey = $subPeriod === 'month' ? 'Y-m' : 'Y-m-d';
        $seriesColumns = $seriesState->getAllSeriesColumns();
        $seriesRows = $seriesState->getAllSeriesRows();
        $seriesMonotonicity = $seriesState->getAllSeriesMonotonicity();

        foreach ($subTables as $subTable) {
            if (!$subTable instanceof DataTable) {
                continue;
            }
            $tablePeriod = $subTable->getMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX);
            if (!$tablePeriod instanceof Period) {
                continue;
            }
            $dateKey = $tablePeriod->getDateStart()->toString($rowKey);

            foreach ($seriesColumns as $seriesLabel => $columnName) {
                $rowMatcher = $seriesRows[$seriesLabel] ?? false;
                $row = (false === $rowMatcher)
                    ? $subTable->getFirstRow()
                    : $subTable->getRowFromLabel($rowMatcher);

                if (empty($row)) {
                    // No matching row on this date. Only MONOTONICITY_UP count series can
                    // defensibly read that as a real 0 (no observation = zero count), so they
                    // get the backfill to keep the analog calendar dense. MONOTONICITY_DOWN
                    // (running mins) and MONOTONICITY_FREE (rates/averages) have no
                    // "no observation → 0" mapping: a min of nothing is not 0, and a 0% rate
                    // inferred from no traffic is not a real ratio observation. Leaving the
                    // date absent lets recentSameDoWValues() skip it instead of treating a
                    // synthetic zero as a same-DoW analog, which would pull the prior below
                    // current and trip shouldRenderForecastValue() into silent suppression.
                    // The column-missing-on-existing-row branch below is deliberately
                    // different: a row that exists but lacks the requested column means the
                    // metric isn't reported here, which is not the same as zero -- and that
                    // branch already skips for every monotonicity.
                    $monotonicity = $seriesMonotonicity[$seriesLabel] ?? ForecastMetricClassifier::MONOTONICITY_UP;
                    if (ForecastMetricClassifier::MONOTONICITY_UP === $monotonicity) {
                        $samples[$seriesLabel][$dateKey] = 0.0;
                    }
                    continue;
                }

                $value = $row->getColumn($columnName);
                if ($value === false || $value === null) {
                    continue;
                }
                $samples[$seriesLabel][$dateKey] = (float) $value;
            }
        }

        return $samples;
    }

    private function yearsBack(string $endDate, int $years): string
    {
        return Date::factory($endDate)->subYear($years)->toString('Y-m-d');
    }

    /**
     * True when any series on the chart is classified MONOTONICITY_UP. Used to decide whether
     * the monthly fan-out on month target needs to fire: month-level MoY scaling only applies
     * to count metrics, so a graph of nothing but ratios/averages/mins gets no value from the
     * monthly archive lookup. Falls back to "assume an UP series is present" when the series
     * state is partially or completely unclassified, matching ForecastBuilder's defensive
     * default monotonicity for unclassified non-percent series.
     */
    private function seriesStateHasUpSeries(ForecastSeriesState $seriesState): bool
    {
        $monotonicities = $seriesState->getAllSeriesMonotonicity();
        $columns = $seriesState->getAllSeriesColumns();
        // Defensive fallback: unclassified or partially-classified state could imply UP-like
        // metrics, so do not skip the monthly fetch in that case.
        if (count($monotonicities) !== count($columns) || [] === $monotonicities) {
            return true;
        }
        return in_array(ForecastMetricClassifier::MONOTONICITY_UP, $monotonicities, true);
    }

    /**
     * Build a daily sample map from the already-loaded displayed `$dataTables`, skipping
     * tables flagged ArchiveState::INCOMPLETE. The skip is what keeps the substitution
     * equivalent to the API fetch: the API path naturally omits incomplete days from its
     * result (missing/in-progress archive → no entry), and matching that ensures a partial
     * value on an in-progress tick cannot leak into a later same-DoW tick's analog walk
     * via the running daily map.
     *
     * @param array<DataTable> $dataTables
     * @return array<string, array<string, float>>
     */
    private function extractDisplayedDailyMap(array $dataTables, ForecastSeriesState $seriesState): array
    {
        $completeTables = [];
        foreach ($dataTables as $key => $table) {
            if (!$table instanceof DataTable) {
                continue;
            }
            if (ArchiveState::INCOMPLETE === $table->getMetadata(DataTable::ARCHIVE_STATE_METADATA_NAME)) {
                continue;
            }
            $completeTables[$key] = $table;
        }
        return $this->extractSamplesFromTables($completeTables, $seriesState, 'day');
    }

    /**
     * Per-series union of two daily sample maps. Display values win on the (unexpected)
     * overlap with the gap fetch — both should reference the same archive rows for the
     * same dates, but treating the display values as authoritative keeps the result
     * consistent with what the chart is rendering on the same screen.
     *
     * @param array<string, array<string, float>> $gap
     * @param array<string, array<string, float>> $display
     * @return array<string, array<string, float>>
     */
    private function mergeDailyMaps(array $gap, array $display): array
    {
        $merged = [];
        $seriesLabels = array_unique(array_merge(array_keys($gap), array_keys($display)));
        foreach ($seriesLabels as $seriesLabel) {
            $merged[$seriesLabel] = array_replace(
                $gap[$seriesLabel] ?? [],
                $display[$seriesLabel] ?? []
            );
        }
        return $merged;
    }
}
