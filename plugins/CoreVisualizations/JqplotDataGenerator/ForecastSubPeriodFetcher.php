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
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Period;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\CoreVisualizations\Metrics\Formatter\Numeric;
use Piwik\Segment;
use Piwik\Site;

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

    /** @var callable(int, string): (string|null) */
    private $earliestDataDateResolver;

    /**
     * @param callable(string, array<string, mixed>): mixed|null $apiRequestProcessor
     *        Inner-request driver. Receives the API method name and a parameter array, returns
     *        whatever the API method returns (expected to be a {@see DataTable\Map} for
     *        usable responses; anything else is treated as an empty sample set). Null uses
     *        {@see ApiRequest::processRequest()}.
     * @param callable(int, string): (string|null)|null $earliestDataDateResolver
     *        Resolves the earliest 'Y-m-d' date the displayed series can have archivable data
     *        for, given the idSite and raw segment expression. The fan-out windows are clamped
     *        to this floor so the fetcher never requests sub-periods that predate the site (or
     *        an auto-archived segment) -- periods that cannot hold data but would otherwise
     *        cost a skip-path recompute or an on-demand archive on every render. Returns null
     *        for "no floor". Null uses {@see self::resolveEarliestDataDate()}.
     */
    public function __construct(
        ?callable $apiRequestProcessor = null,
        ?LoggerInterface $logger = null,
        ?callable $earliestDataDateResolver = null
    ) {
        $this->apiRequestProcessor = $apiRequestProcessor ?? static function (string $apiMethod, array $params) {
            return ApiRequest::processRequest($apiMethod, $params);
        };
        $this->logger = $logger ?? StaticContainer::get(LoggerInterface::class);
        $this->earliestDataDateResolver = $earliestDataDateResolver ?? function (int $idSite, string $segment): ?string {
            return $this->resolveEarliestDataDate($idSite, $segment);
        };
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
     * @return array{daily: array<string, array<string, float>>, monthly: array<string, array<string, float>>, earliestDataDate: string|null}
     */
    public function collect(
        array $dataTables,
        ForecastSeriesState $seriesState,
        string $apiMethod,
        int $idSite,
        string $segment
    ): array {
        $empty = ['daily' => [], 'monthly' => [], 'earliestDataDate' => null];

        if (empty($dataTables) || [] === $seriesState->getAllSeriesColumns()) {
            return $empty;
        }

        if (empty($apiMethod) || strpos($apiMethod, '.') === false) {
            return $empty;
        }

        if ($idSite <= 0) {
            return $empty;
        }

        // Earliest date the site/segment can hold data. Both the inner fetch windows below and
        // the caller's ForecastBuilder floor the analog history at this date, so resolve it once
        // and thread it through every return path (including the display-only day branches that
        // skip the inner request).
        $earliestDataDate = ($this->earliestDataDateResolver)($idSite, $segment);

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

        // getDateEnd() is the calendar end of the (possibly in-progress) last displayed period,
        // so for a mid-period month/year target it lands in the future. Anchoring the historical
        // sample windows there would push the daily window entirely into the future -- every
        // sub-period a guaranteed-empty archive read that feeds no samples into the seasonal
        // decomposition. Clamp to the last complete day in the site's timezone; the in-progress
        // period's own partial value already comes from the displayed $dataTables, not this fetch.
        $site = $lastTable->getMetadata(DataTableFactory::TABLE_METADATA_SITE_INDEX);
        if ($site instanceof Site) {
            $endDate = $this->clampEndDate($endDate, $site);
        }

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
                    return ['daily' => $displayDailyMap, 'monthly' => [], 'earliestDataDate' => $earliestDataDate];
                }

                $gapEndDate = Date::factory($firstDisplayedDate)->subDay(1)->toString('Y-m-d');

                // Clamp the gap window to the earliest date the site (or an auto-archived
                // segment) can have data. A gap start that predates the site's existence asks
                // for sub-periods that cannot hold data: each would hit the archiver's skip
                // path (recomputed, never persisted) or, under browser archiving, trigger an
                // on-demand archive -- on every render. When the whole gap predates the data,
                // the displayed range alone supplies the analog walk, so drop the inner request.
                $gapStartDate = $this->clampStartDate(
                    $analogWindowStart,
                    $earliestDataDate
                );
                if (strcmp($gapStartDate, $gapEndDate) > 0) {
                    return ['daily' => $displayDailyMap, 'monthly' => [], 'earliestDataDate' => $earliestDataDate];
                }

                $gapDailyMap = $this->fetchSeries(
                    $apiMethod,
                    $idSite,
                    $segment,
                    'day',
                    $gapStartDate,
                    $gapEndDate,
                    $seriesState
                );

                return [
                    'daily'   => $this->mergeDailyMaps($gapDailyMap, $displayDailyMap),
                    'monthly' => [],
                    'earliestDataDate' => $earliestDataDate,
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

                $dailyMap = $this->fetchClampedSeries(
                    $apiMethod,
                    $idSite,
                    $segment,
                    'day',
                    $startDate,
                    $endDate,
                    $seriesState,
                    $earliestDataDate
                );

                // Monthly fan-out on the month target is consumed only by the UP-flavoured
                // {@see ForecastBuilder::computeMonthOfYearScale()}; FREE/DOWN month forecasts
                // skip the MoY scaling because scaling rates/mins by a traffic ratio is
                // conceptually wrong. Skip the inner request when no UP series is on this
                // graph -- the data would have no consumer.
                $monthlyMap = [];
                if ('month' === $periodLabel && $this->seriesStateHasUpSeries($seriesState)) {
                    $monthlyMap = $this->fetchClampedSeries(
                        $apiMethod,
                        $idSite,
                        $segment,
                        'month',
                        $this->yearsBack($endDate, self::MONTH_MONTHLY_WINDOW_YEARS),
                        $endDate,
                        $seriesState,
                        $earliestDataDate
                    );
                }

                return [
                    'daily'   => $dailyMap,
                    'monthly' => $monthlyMap,
                    'earliestDataDate' => $earliestDataDate,
                ];
            }
            if ('year' === $periodLabel) {
                $dailyStart = (Date::factory($endDate))->subDay(self::YEAR_DAILY_WINDOW_DAYS)->toString('Y-m-d');
                return [
                    'daily'   => $this->fetchClampedSeries($apiMethod, $idSite, $segment, 'day', $dailyStart, $endDate, $seriesState, $earliestDataDate),
                    'monthly' => $this->fetchClampedSeries($apiMethod, $idSite, $segment, 'month', $this->yearsBack($endDate, self::YEAR_MONTHLY_WINDOW_YEARS), $endDate, $seriesState, $earliestDataDate),
                    'earliestDataDate' => $earliestDataDate,
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

        // Fetch failed or the period type needs no sub-period samples: still surface the
        // resolved floor so the caller's prior-only path stays width-independent.
        return ['daily' => [], 'monthly' => [], 'earliestDataDate' => $earliestDataDate];
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
        // Scope the inner request to the columns the chart actually plots. The displayed
        // evolution graph already does this (Controller::getLastUnitGraphAcrossPlugins sets
        // custom_parameters['columns'] = columns_to_display); the fan-out did not, so when the
        // graph's API method is the cross-plugin merge API.get, every sub-period rebuilt the
        // full union of all contributing plugins' metrics (VisitsSummary, Actions, Referrers,
        // Goals, ...). The forecast only consumes the plotted columns, so querying the rest is
        // pure waste. An empty set is sent as columns='' which API.get reads as "all metrics",
        // preserving the legacy unscoped behaviour for callers without a populated series state.
        $plottedColumns = array_values(array_unique(array_filter(
            $seriesState->getAllSeriesColumns(),
            static function ($column): bool {
                return '' !== $column;
            }
        )));

        // Resolve the cross-plugin merge API.get into one fetch group per owning Module.get report.
        // API.get is a PHP-side merge, not an archive method, so the framework rebuilds its
        // multi-sub-period result one sub-period at a time -- re-running the full report-metadata
        // catalog build (getReportMetadata -> configureReportMetadata across every report of every
        // plugin) on each, the dominant forecast-render cost. Concrete archive-backed `.get`
        // methods return the whole sub-period Map from one batched archive read with no catalog
        // build, so each group costs one catalog-free read instead of one full API.get rebuild per
        // sub-period. Graphs whose metrics span several plugins (the Visits Overview set is the
        // common case) fan out into one request per module; their per-series samples are merged
        // below. Non-API.get callers and unresolvable column sets fall back to a single group on
        // the original method (see resolveModuleColumnGroups).
        $groups = $this->resolveModuleColumnGroups($apiMethod, $idSite, $plottedColumns);

        $samples = [];
        foreach ($groups as $group) {
            $map = $this->requestSubPeriodMap(
                $group['method'],
                $idSite,
                $segment,
                $subPeriod,
                $startDate,
                $endDate,
                $group['columns']
            );
            if (null === $map) {
                continue;
            }

            $this->applyChartUnitFormatting($map, $group['method']);

            // Restrict to this group's own series before merging: the group fetched only its
            // module's columns, so a sibling series' column is absent from these tables and
            // extractSamples would emit a synthetic MONOTONICITY_UP zero for it on any empty
            // sub-period. Merging that zero could clobber the real value the sibling's own group
            // produced (and the merge order is not significant once each series is sourced solely
            // from its owning group).
            $groupSamples = $this->restrictSamplesToColumns(
                $this->extractSamples($map, $seriesState, $subPeriod),
                $seriesState,
                $group['columns']
            );

            $samples = $this->mergeSampleMaps($samples, $groupSamples);
        }

        return $samples;
    }

    /**
     * Issue one sub-period request for $method scoped to $columns and return the raw
     * {@see DataTable\Map}, or null when the API method did not return a Map (unsupported method,
     * error response). The pinned framework result-shape params keep the inner rows aligned with
     * what the chart plots; see the inline notes.
     *
     * @param array<int, string> $columns
     */
    private function requestSubPeriodMap(
        string $method,
        int $idSite,
        string $segment,
        string $subPeriod,
        string $startDate,
        string $endDate,
        array $columns
    ): ?DataTable\Map {
        // processRequest() picks up the core convention's compare=0 / format=original /
        // serialize=0 defaults. $_GET + $_POST inheritance is asymmetric on purpose: scope
        // params (idGoal, idDimension, future plugin selectors) inherit so the inner sample
        // hits the same series the chart plots -- the set is open-ended and an allowlist
        // would have to predict every plugin. Framework result-shape mutators are a closed
        // set, pinned below: inheriting them would silently shift which rows the inner
        // samples come from. isComparing upstream guards against comparison leakage.
        $result = ($this->apiRequestProcessor)($method, [
            'idSite'                  => $idSite,
            'period'                  => $subPeriod,
            'date'                    => $startDate . ',' . $endDate,
            'segment'                 => $segment,
            'columns'                 => implode(',', $columns),
            'filter_limit'            => -1,
            'disable_generic_filters' => 1,
            // format_metrics=1 replaces numeric values with display strings (bandwidth -> "3 G")
            // and stamps PROCESSED_METRICS_FORMATTED_FLAG so the Numeric pass below cannot
            // re-run. Pin raw so the decomposition has numbers to work with.
            'format_metrics'          => 0,
            // Framework result-shape pins -- inheriting these would mismatch the chart's rows:
            'flat'                     => 0,    // flattens subtables; row matcher hits wrong labels
            'expanded'                 => 0,    // same risk as flat=1
            'idSubtable'               => '',   // would point at an unrelated subtable
            'pivotBy'                  => '',   // transposes rows around a dimension
            'filter_offset'            => 0,    // skips rows; shifts getFirstRow()
            'filter_sort_column'       => '',   // outer sort reorders the inner first row
            'filter_sort_order'        => '',
            'filter_pattern'           => '',   // outer UI search shrinks the inner result
            'filter_pattern_recursive' => '',
            'filter_column'            => '',
            'keep_summary_row'         => 0,    // belt-and-braces; disable_generic_filters=1 already gates this
        ]);

        return $result instanceof DataTable\Map ? $result : null;
    }

    /**
     * Bring the inner samples onto the same scale the outer evolution chart plots in. The
     * outer is rendered by {@see \Piwik\Plugins\CoreVisualizations\Visualizations\Graph},
     * which formats with the {@see Numeric} formatter (format-as-number, not as string):
     * {@see \Piwik\Plugin\ProcessedMetric} columns are rewritten in place -- bandwidth
     * bytes divided by 1024^3 onto the chart's fixed 'G' axis, percent quotients scaled
     * to whole percent, etc. The inner request is pinned to raw numeric units, so without this
     * pass the forecast would sum analog bytes-per-day against an outer current value already in
     * gigabytes, the ~10^9 blowup seen on bandwidth forecasts. Running the identical Numeric pass
     * here mirrors that transformation row-for-row so the seasonal decomposition stays in one unit
     * system. $method is the report the group was fetched from, so per-module groups format with
     * their own report's processed-metric definitions.
     */
    private function applyChartUnitFormatting(DataTable\Map $result, string $method): void
    {
        $report = $this->resolveReportForFormatting($method);
        // Some `Module.get` reports (Goals.get is the canonical case) list a percent/ratio
        // ProcessedMetric only by its string name in $processedMetrics, which
        // Report::getProcessedMetricsById() drops -- the metric *object* that knows how to
        // scale the raw quotient to whole percent lives on the sibling `Module.getMetrics`
        // report the outer request delegates to. Without it, formatMetrics() cannot recognise
        // e.g. conversion_rate and leaves the inner sample as the raw 0..1 quotient while the
        // chart shows whole percent -- a forecast ~100x too small. Seed those metric objects
        // onto each sub-table's metadata (the same surface AddColumnsProcessedMetrics uses) so
        // the Numeric pass recognises and scales them exactly as the displayed chart does.
        $extraMetrics = $this->resolveDelegatedProcessedMetrics($method);
        $formatter = new Numeric();
        foreach ($result->getDataTables() as $subTable) {
            if ([] !== $extraMetrics) {
                $existing = $subTable->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];
                $subTable->setMetadata(
                    DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME,
                    array_merge($extraMetrics, $existing)
                );
            }
            $formatter->formatMetrics($subTable, $report);
        }
    }

    /**
     * Fetch a sub-period series after clamping its window start to the earliest date the
     * site/segment can have data. Returns an empty sample map -- without issuing the inner
     * request -- when the whole window predates that floor, since every sub-period in it would
     * be a guaranteed-empty archive lookup.
     *
     * @return array<string, array<string, float>>
     */
    private function fetchClampedSeries(
        string $apiMethod,
        int $idSite,
        string $segment,
        string $subPeriod,
        string $startDate,
        string $endDate,
        ForecastSeriesState $seriesState,
        ?string $earliestDataDate
    ): array {
        $clampedStart = $this->clampStartDate($startDate, $earliestDataDate);
        if (strcmp($clampedStart, $endDate) > 0) {
            // The entire window falls before the site/segment came into existence. The prior-only
            // path in ForecastBuilder takes over from the displayed period-level series alone.
            return [];
        }

        return $this->fetchSeries($apiMethod, $idSite, $segment, $subPeriod, $clampedStart, $endDate, $seriesState);
    }

    /**
     * Raise $startDate to $earliestDataDate when the latter is later. Both are 'Y-m-d' strings,
     * which compare lexicographically in chronological order, so strcmp() is a date comparison
     * here. A null floor (resolver opted out) leaves the start untouched.
     */
    private function clampStartDate(string $startDate, ?string $earliestDataDate): string
    {
        if (null !== $earliestDataDate && strcmp($earliestDataDate, $startDate) > 0) {
            return $earliestDataDate;
        }

        return $startDate;
    }

    /**
     * Lower $endDate to the last complete day in the site's timezone when the displayed period
     * runs past it (a mid-period month/year target whose calendar end is in the future). Mirrors
     * {@see self::clampStartDate()}: both operands are 'Y-m-d' strings comparing lexicographically
     * in chronological order, so strcmp() is a date comparison here.
     */
    private function clampEndDate(string $endDate, Site $site): string
    {
        $lastCompleteDay = Date::factoryInTimezone('today', $site->getTimezone())
            ->subDay(1)
            ->toString('Y-m-d');

        return strcmp($endDate, $lastCompleteDay) > 0 ? $lastCompleteDay : $endDate;
    }

    /**
     * Earliest 'Y-m-d' date the displayed series can hold archivable data for. The hard floor is
     * the site creation date -- no traffic can predate the site. When the request carries an
     * auto-archived segment, the floor is raised to the segment's re-archive start date, because
     * core only persists that segment's archives from that date forward (earlier periods return
     * empty via the archiver's segment skip path anyway, so clamping them out costs no real
     * samples and removes the synthetic-zero backfill that would otherwise drag the prior down).
     *
     * On-demand (non auto-archived) segments are deliberately left at the site floor: core
     * computes and persists their historic archives on request, so that history is genuine data
     * the forecast must keep. {@see SegmentArchiving::findSegmentForHash()} returns null for
     * them, which is exactly the gate.
     *
     * Any failure (site removed mid-request, unparseable segment) falls back to null -- no clamp
     * -- so a resolver hiccup can never silently truncate a legitimate window.
     */
    private function resolveEarliestDataDate(int $idSite, string $segment): ?string
    {
        try {
            $earliest = Date::factory(Site::getCreationDateFor($idSite));
        } catch (\Throwable $e) {
            return null;
        }

        if ('' !== $segment) {
            $segmentStart = $this->resolveSegmentStartDate($segment, $idSite);
            if (null !== $segmentStart && $segmentStart->isLater($earliest)) {
                $earliest = $segmentStart;
            }
        }

        return $earliest->toString('Y-m-d');
    }

    /**
     * Re-archive start date for an auto-archived segment matching $segment on $idSite, or null
     * when the segment is on-demand (so historic data is real and must not be clamped away) or
     * cannot be resolved.
     */
    private function resolveSegmentStartDate(string $segment, int $idSite): ?Date
    {
        try {
            $segmentObj = new Segment($segment, [$idSite]);

            /** @var SegmentArchiving $segmentArchiving */
            $segmentArchiving = StaticContainer::get(SegmentArchiving::class);
            $segmentInfo = $segmentArchiving->findSegmentForHash($segmentObj->getHash(), $idSite);
            if (null === $segmentInfo) {
                return null;
            }

            return $segmentArchiving->getReArchiveSegmentStartDate($segmentInfo);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Split the plotted columns into one fetch group per owning `Module.get` report, so the
     * sub-period fan-out can issue catalog-free archive-backed requests instead of the
     * per-sub-period API.get rebuild (see {@see self::fetchSeries()} for why that rebuild
     * dominates the render). Each group is `['method' => 'Module.get', 'columns' => [...]]`.
     *
     * Returns a single group carrying the original $apiMethod when:
     *  - the method is not the cross-plugin merge API.get (concrete reports already avoid the
     *    rebuild, so there is nothing to split);
     *  - there are no plotted columns to scope by;
     *  - any column resolves to other than exactly one module (an unmapped metric, or one shared
     *    by several `.get` reports), where reproducing API.get's own merge precedence is not worth
     *    the risk -- the column-scoped API.get path stays correct, just slower.
     *
     * The column -> module mapping is read from the report metadata exactly as
     * {@see \Piwik\Plugins\API\API::get()} reads it, keyed on the outer request's period/date so it
     * reuses the catalog the displayed graph already built (a transient-cache hit). Any failure
     * falls back to the single API.get group, matching this class's defensive degradation.
     *
     * @param array<int, string> $plottedColumns
     * @return array<int, array{method: string, columns: array<int, string>}>
     */
    private function resolveModuleColumnGroups(string $apiMethod, int $idSite, array $plottedColumns): array
    {
        $fallback = [['method' => $apiMethod, 'columns' => $plottedColumns]];

        if ('API.get' !== $apiMethod || [] === $plottedColumns) {
            return $fallback;
        }

        try {
            $period = Common::getRequestVar('period', 'day', 'string');
            $date   = Common::getRequestVar('date', 'today', 'string');
            $meta   = $this->fetchReportMetadataCatalog($idSite, $period, $date);
        } catch (\Throwable $e) {
            return $fallback;
        }

        // Build column -> set-of-owning-modules from every plugin's `.get` report, mirroring the
        // scan API.get itself does (action 'get', no parameters, not the API module, has metrics).
        $modulesByColumn = [];
        foreach ($meta as $reportMeta) {
            if (
                ($reportMeta['action'] ?? null) !== 'get'
                || isset($reportMeta['parameters'])
                || ($reportMeta['module'] ?? 'API') === 'API'
                || empty($reportMeta['metrics'])
            ) {
                continue;
            }
            $module  = $reportMeta['module'];
            $metrics = array_merge($reportMeta['metrics'], $reportMeta['processedMetrics'] ?? []);
            foreach ($metrics as $column => $translation) {
                $modulesByColumn[$column][$module] = true;
            }
        }

        // Group the plotted columns by their single owning module. Bail to the API.get fallback if
        // any column is unmapped or owned by more than one module, since a concrete per-module
        // fan-out cannot reproduce API.get's merge for it without guessing precedence.
        $columnsByModule = [];
        foreach ($plottedColumns as $column) {
            $owners = $modulesByColumn[$column] ?? [];
            if (count($owners) !== 1) {
                // Unmapped (0 owners) or shared (>1) column: the whole graph regresses to a single
                // API.get fetch. This is correct but slow, and it would otherwise flip silently if a
                // plugin later registered a colliding metric name -- log the offending column and its
                // owner count so the regression is investigable rather than invisible.
                $this->logger->debug(
                    'Evolution forecast module fan-out disabled: column {column} maps to {ownerCount} '
                    . 'modules (idSite={idSite}), falling back to API.get',
                    [
                        'column'     => $column,
                        'ownerCount' => count($owners),
                        'idSite'     => $idSite,
                    ]
                );
                return $fallback;
            }
            $columnsByModule[(string) key($owners)][] = $column;
        }

        $groups = [];
        foreach ($columnsByModule as $module => $columns) {
            $groups[] = ['method' => $module . '.get', 'columns' => $columns];
        }

        return $groups;
    }

    /**
     * Report-metadata catalog used to resolve each plotted column to its owning `Module.get`
     * report, read exactly as {@see \Piwik\Plugins\API\API::get()} reads it.
     *
     * This is a soft optimization: it reuses getReportMetadata's transient cache only when the
     * outer request already built the catalog under the same (idSite, period, date) key -- the
     * common evolution-graph path does. A caller reaching here without that priming (e.g. an
     * unusual widget path) pays one full catalog build itself, which is still the cost the fan-out
     * then avoids on every sub-period. Kept as an overridable seam so the column->module
     * resolution and its fallbacks can be unit-tested without a live report catalog.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function fetchReportMetadataCatalog(int $idSite, string $period, string $date): array
    {
        return \Piwik\Plugins\API\API::getInstance()->getReportMetadata($idSite, $period, $date);
    }

    /**
     * Per-series union of two sample maps (seriesLabel -> dateKey -> value), used to combine the
     * per-module group fetches. Each plotted column is owned by exactly one module
     * ({@see self::resolveModuleColumnGroups()} bails otherwise) and each group's samples are
     * restricted to its own series before merging, so a given series is contributed by a single
     * group and the unions never collide; array_replace is order-insensitive here as a result.
     *
     * @param array<string, array<string, float>> $base
     * @param array<string, array<string, float>> $add
     * @return array<string, array<string, float>>
     */
    private function mergeSampleMaps(array $base, array $add): array
    {
        foreach ($add as $seriesLabel => $dateValues) {
            $base[$seriesLabel] = array_replace($base[$seriesLabel] ?? [], $dateValues);
        }

        return $base;
    }

    /**
     * Keep only the series whose plotted column belongs to $columns. A per-module group fetches
     * just its module's columns, so a sibling series' column is absent from the group's tables;
     * {@see self::extractSamplesFromTables()} would still emit a synthetic MONOTONICITY_UP zero for
     * that sibling on any empty sub-period, and merging the zero could clobber the real value the
     * sibling's own group produced. Dropping out-of-group series here keeps every series sourced
     * solely from its owning module. A single-group (fallback / concrete-method) fetch passes the
     * full plotted-column set, so this is a no-op there.
     *
     * @param array<string, array<string, float>> $samples
     * @param array<int, string> $columns
     * @return array<string, array<string, float>>
     */
    private function restrictSamplesToColumns(array $samples, ForecastSeriesState $seriesState, array $columns): array
    {
        $seriesColumns = $seriesState->getAllSeriesColumns();
        $allowed       = array_fill_keys($columns, true);

        $restricted = [];
        foreach ($samples as $seriesLabel => $dateValues) {
            $column = $seriesColumns[$seriesLabel] ?? null;
            if (null !== $column && isset($allowed[$column])) {
                $restricted[$seriesLabel] = $dateValues;
            }
        }

        return $restricted;
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
     * Object-declared ProcessedMetrics from the `Module.getMetrics` report a `Module.get`
     * report delegates its metric computation to. These carry the format() logic (e.g. the
     * percent-quotient scaling) that the `Module.get` report exposes only as a string name and
     * therefore hides from {@see \Piwik\Plugin\Report::getProcessedMetricsById()}. Returns an
     * empty array for non-`get` methods or modules without a `getMetrics` sibling, so reports
     * that already declare their metrics as objects (e.g. Bandwidth) are unaffected.
     *
     * @return array<string, \Piwik\Plugin\ProcessedMetric>
     */
    private function resolveDelegatedProcessedMetrics(string $apiMethod): array
    {
        $parts = explode('.', $apiMethod, 2);
        if (count($parts) !== 2 || 'get' !== $parts[1]) {
            return [];
        }

        $metricsReport = ReportsProvider::factory($parts[0], 'getMetrics');
        if (null === $metricsReport) {
            return [];
        }

        return $metricsReport->getProcessedMetricsById();
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
     * @param array<DataTable|DataTable\Map> $subTables
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
     * @param array<DataTable|DataTable\Map> $dataTables
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
