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
use Piwik\Archive\DataTableFactory;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Period;

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
     * @param array<string, string> $seriesColumns Map of series label → raw archive column
     *        name. Keys are how {@see ForecastBuilder} consumes the returned sample maps;
     *        values are how the sub-period API result rows are keyed once
     *        ReplaceColumnNames has run.
     * @param array<string, mixed> $seriesRows Map of series label → row label/matcher (the
     *        value the displayed-series path passes to {@see DataTable::getRowFromLabel()}).
     *        `false` selects the sub-table's first row, matching the single-row default.
     *        Threaded through to {@see self::extractSamples()} so multi-row evolution graphs
     *        (selectable_rows) pull each series' historical samples from its own row.
     * @param array<string, string> $seriesMonotonicity Map of series label → MONOTONICITY_*
     *        tag from {@see ForecastMetricClassifier}. Gates the rowless-sub-table backfill
     *        so synthetic zeros never pollute DOWN/FREE analog walks.
     * @param string $apiMethod API method spec to fan out to (`Module.action`).
     * @param int $idSite Site id the displayed series belongs to.
     * @param string $segment Segment expression to pin onto the inner request.
     * @return array{daily: array<string, array<string, float>>, monthly: array<string, array<string, float>>}
     */
    public function collect(
        array $dataTables,
        array $seriesColumns,
        array $seriesRows,
        array $seriesMonotonicity,
        string $apiMethod,
        int $idSite,
        string $segment
    ): array {
        $empty = ['daily' => [], 'monthly' => []];

        if (empty($dataTables) || empty($seriesColumns)) {
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
                // path is the only forecast surface. A 4-7 day display carries at most one
                // same-DoW history tick in $seriesData; pull a 70-day window so the same-DoW
                // walk in ForecastBuilder collects ~10 analogs and the trend fit + envelope
                // clamp engage on short displays.
                $startDate = (Date::factory($endDate))->subDay(70)->toString('Y-m-d');
                return [
                    'daily'   => $this->fetchSeries($apiMethod, $idSite, $segment, 'day', $startDate, $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                    'monthly' => [],
                ];
            }
            if ('week' === $periodLabel || 'month' === $periodLabel) {
                // Pull enough days to populate same-DoW analog slots for the largest analog
                // chunk the builder might consume. ForecastBuilder uses chunk = 3 (week) or
                // 4 (month); the worst case is the first projected day of a 31-day month
                // pulling its chunk-th analog (31 + 4 × 7 = 59 days). 70 days gives headroom
                // for any future chunk increase up to 5.
                $startDate = (Date::factory($endDate))->subDay(70)->toString('Y-m-d');
                return [
                    'daily'   => $this->fetchSeries($apiMethod, $idSite, $segment, 'day', $startDate, $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                    'monthly' => 'month' === $periodLabel
                        ? $this->fetchSeries($apiMethod, $idSite, $segment, 'month', $this->yearsBack($endDate, 4), $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity)
                        : [],
                ];
            }
            if ('year' === $periodLabel) {
                return [
                    'daily'   => $this->fetchSeries($apiMethod, $idSite, $segment, 'day', (Date::factory($endDate))->subDay(70)->toString('Y-m-d'), $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
                    'monthly' => $this->fetchSeries($apiMethod, $idSite, $segment, 'month', $this->yearsBack($endDate, 9), $endDate, $seriesColumns, $seriesRows, $seriesMonotonicity),
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
     * @param array<string, string> $seriesColumns Map of series label → raw archive column name.
     * @param array<string, mixed> $seriesRows Map of series label → row label/matcher.
     * @param array<string, string> $seriesMonotonicity Map of series label → MONOTONICITY_* tag.
     * @return array<string, array<string, float>>
     */
    private function fetchSeries(
        string $apiMethod,
        int $idSite,
        string $segment,
        string $subPeriod,
        string $startDate,
        string $endDate,
        array $seriesColumns,
        array $seriesRows,
        array $seriesMonotonicity
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
        ]);

        if (!$result instanceof DataTable\Map) {
            return [];
        }

        return $this->extractSamples($result, $seriesColumns, $seriesRows, $seriesMonotonicity, $subPeriod);
    }

    /**
     * Shape a sub-period DataTable\Map into a series-keyed map of date → value. The result of
     * the inner API request has already been through ReplaceColumnNames so its row columns are
     * the raw archive column names; we look up by raw name and store under the series label so
     * ForecastBuilder's per-series lookup hits the right entry.
     *
     * Each series carries its own row matcher in $seriesRows. Multi-row evolution graphs
     * (selectable_rows on a non-summary report) plot one series per selected row, so the
     * historical sample for each series must come from that series' own row in the sub-period
     * archive -- not from {@see DataTable::getFirstRow()}, which would silently pin every
     * series to whichever row sorts first in that sub-table (typically the top-ranked row).
     * `false` matchers fall through to getFirstRow() to keep the single-row default behaviour.
     *
     * @param array<string, string> $seriesColumns Map of series label → raw archive column name.
     * @param array<string, mixed> $seriesRows Map of series label → row label/matcher (the same
     *        value the displayed-series path passes to {@see DataTable::getRowFromLabel()}).
     *        `false` selects the sub-table's first row.
     * @param array<string, string> $seriesMonotonicity Map of series label → MONOTONICITY_* tag.
     *        Missing entries fall back to MONOTONICITY_UP so the legacy backfill behaviour
     *        survives for callers that have not propagated the classifier output yet.
     * @return array<string, array<string, float>>
     */
    public function extractSamples(
        DataTable\Map $result,
        array $seriesColumns,
        array $seriesRows,
        array $seriesMonotonicity,
        string $subPeriod
    ): array {
        $samples = [];
        $rowKey = $subPeriod === 'month' ? 'Y-m' : 'Y-m-d';

        foreach ($result->getDataTables() as $subTable) {
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
}
