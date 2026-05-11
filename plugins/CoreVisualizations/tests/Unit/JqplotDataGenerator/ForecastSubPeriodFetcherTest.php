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
use Piwik\Archive\DataTableFactory;
use Piwik\DataTable;
use Piwik\Log\LoggerInterface;
use Piwik\Period\Factory;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSubPeriodFetcher;

/**
 * @group CoreVisualizations
 * @group Evolution
 * @group JqplotDataGenerator
 */
class ForecastSubPeriodFetcherTest extends TestCase
{
    public function testCollectReturnsEmptyForEmptyDataTables(): void
    {
        $fetcher = $this->createFetcher();

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect([], ['Visits' => 'nb_visits'], [], [], 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectReturnsEmptyForEmptySeriesColumns(): void
    {
        $fetcher = $this->createFetcher();

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect([$this->createDayDataTable('2026-04-10')], [], [], [], 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectReturnsEmptyWhenApiMethodIsMissingOrMalformed(): void
    {
        $fetcher = $this->createFetcher();
        $dataTables = [$this->createDayDataTable('2026-04-10')];

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect($dataTables, ['Visits' => 'nb_visits'], [], [], '', 1, '')
        );
        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect($dataTables, ['Visits' => 'nb_visits'], [], [], 'NoDotMethod', 1, '')
        );
    }

    public function testCollectReturnsEmptyForInvalidIdSite(): void
    {
        $fetcher = $this->createFetcher();
        $dataTables = [$this->createDayDataTable('2026-04-10')];

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect($dataTables, ['Visits' => 'nb_visits'], [], [], 'VisitsSummary.get', 0, '')
        );
    }

    public function testCollectReturnsEmptyForUnsupportedPeriodLabel(): void
    {
        // A range-period displayed graph: ForecastBuilder only seasonal-decomposes for
        // day/week/month/year, so the fetcher should bail out without firing the API call.
        $fetcher = $this->createFetcher();
        $rangeTable = new DataTable();
        $rangeTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('range', '2026-04-01,2026-04-10'));

        self::assertSame(
            ['daily' => [], 'monthly' => []],
            $fetcher->collect([$rangeTable], ['Visits' => 'nb_visits'], [], [], 'VisitsSummary.get', 1, '')
        );
    }

    public function testCollectIssuesDailyInnerRequestForDayPeriod(): void
    {
        // Day-period forecasts pull a 70-day daily window from $endDate. Capture the params
        // passed to the API stub so we can pin both the period type and the date range.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = [$apiMethod, $params];
            return $this->createSampleResultMap(['2026-04-09' => ['nb_visits' => 80.0]]);
        });

        $result = $fetcher->collect(
            [$this->createDayDataTable('2026-04-09'), $this->createDayDataTable('2026-04-10')],
            ['Visits' => 'nb_visits'],
            [],
            ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP],
            'VisitsSummary.get',
            42,
            'pageUrl==foo'
        );

        self::assertCount(1, $captured);
        self::assertSame('VisitsSummary.get', $captured[0][0]);
        self::assertSame(42, $captured[0][1]['idSite']);
        self::assertSame('day', $captured[0][1]['period']);
        self::assertSame('2026-01-29,2026-04-09', $captured[0][1]['date']);
        self::assertSame('pageUrl==foo', $captured[0][1]['segment']);

        self::assertSame(
            ['daily' => ['Visits' => ['2026-04-09' => 80.0]], 'monthly' => []],
            $result
        );
    }

    public function testCollectFansOutDailyAndMonthlyForMonthPeriod(): void
    {
        // Month-period forecasts pull both a daily window (for same-DoW analogs) and a
        // 4-year monthly window (for same-MoY analogs). Capture and check both calls.
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = $params['period'];
            return $this->createSampleResultMap([]);
        });

        $monthTable = new DataTable();
        $monthTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('month', '2026-04-01'));

        $fetcher->collect([$monthTable], ['Visits' => 'nb_visits'], [], [], 'VisitsSummary.get', 1, '');

        self::assertSame(['day', 'month'], $captured);
    }

    public function testCollectFansOutDailyAndMonthlyForYearPeriod(): void
    {
        $captured = [];
        $fetcher = $this->createFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[] = [$params['period'], $params['date']];
            return $this->createSampleResultMap([]);
        });

        $yearTable = new DataTable();
        $yearTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('year', '2026-01-01'));

        $fetcher->collect([$yearTable], ['Visits' => 'nb_visits'], [], [], 'VisitsSummary.get', 1, '');

        self::assertCount(2, $captured);
        self::assertSame('day', $captured[0][0]);
        self::assertSame('month', $captured[1][0]);
        // 9-year monthly window for year-target forecasts.
        self::assertSame('2017-01-01,2026-12-30', $captured[1][1]);
    }

    public function testCollectLogsAndReturnsEmptyWhenInnerRequestThrows(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with(self::stringContains('forecast sub-period fetch failed'), self::callback(static function (array $context): bool {
                return ($context['apiMethod'] ?? null) === 'VisitsSummary.get'
                    && ($context['idSite'] ?? null) === 99
                    && ($context['period'] ?? null) === 'day'
                    && false !== strpos($context['message'] ?? '', 'archive boom');
            }));

        $fetcher = $this->createFetcher(static function (): void {
            throw new \RuntimeException('archive boom');
        }, $logger);

        $result = $fetcher->collect(
            [$this->createDayDataTable('2026-04-10')],
            ['Visits' => 'nb_visits'],
            [],
            [],
            'VisitsSummary.get',
            99,
            ''
        );

        self::assertSame(['daily' => [], 'monthly' => []], $result);
    }

    public function testCollectReturnsEmptyWhenInnerRequestReturnsNonMapResult(): void
    {
        $fetcher = $this->createFetcher(static function () {
            return new DataTable();
        });

        $result = $fetcher->collect(
            [$this->createDayDataTable('2026-04-10')],
            ['Visits' => 'nb_visits'],
            [],
            [],
            'VisitsSummary.get',
            1,
            ''
        );

        self::assertSame(['daily' => [], 'monthly' => []], $result);
    }

    public function testExtractSamplesLooksUpRawColumnAndStoresUnderSeriesLabel(): void
    {
        // Sub-period API result rows are keyed by the raw archive column name (after
        // ReplaceColumnNames), but ForecastBuilder consumes samples keyed by the series label
        // it sees in $allSeriesData. Exercise the lookup with a translated label so a
        // regression that swaps the two keys would silently produce empty samples again.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['nb_visits' => 80.0],
            '2026-04-11' => ['nb_visits' => 100.0],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            ['Visits' => 'nb_visits'],
            [],
            ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP],
            'day'
        );

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 100.0]],
            $samples
        );
    }

    public function testExtractSamplesFillsZeroForRowlessSubTableOnMonotonicUpSeries(): void
    {
        // A sub-period table with no rows is a legitimately-empty archive, not a fetch error.
        // For MONOTONICITY_UP count series that means a true zero, so the extractor backfills
        // 0.0 to keep the analog walk's calendar dense.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['nb_visits' => 80.0],
            '2026-04-11' => [],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            ['Visits' => 'nb_visits'],
            [],
            ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP],
            'day'
        );

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 0.0]],
            $samples
        );
    }

    public function testExtractSamplesSkipsRowlessSubTableForMonotonicDownAndFreeSeries(): void
    {
        // Synthetic 0 backfill is defensible only for MONOTONICITY_UP (no traffic = zero
        // count). For MONOTONICITY_DOWN (running mins) a "min of nothing" is not 0, and a
        // 0% rate inferred from no traffic is not a real ratio observation for
        // MONOTONICITY_FREE. The extractor must leave those dates absent from the sample
        // map so the same-DoW analog walk skips them instead of treating a synthetic
        // zero as an analog -- which would otherwise pull the DOWN prior to/below
        // current and trip silent forecast suppression via shouldRenderForecastValue().
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['min_event_value' => 12.0, 'bounce_rate' => 35.0, 'nb_visits' => 80.0],
            '2026-04-11' => [],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            [
                'Min event value' => 'min_event_value',
                'Bounce rate'     => 'bounce_rate',
                'Visits'          => 'nb_visits',
            ],
            [],
            [
                'Min event value' => ForecastMetricClassifier::MONOTONICITY_DOWN,
                'Bounce rate'     => ForecastMetricClassifier::MONOTONICITY_FREE,
                'Visits'          => ForecastMetricClassifier::MONOTONICITY_UP,
            ],
            'day'
        );

        self::assertSame(
            [
                'Min event value' => ['2026-04-10' => 12.0],
                'Bounce rate'     => ['2026-04-10' => 35.0],
                'Visits'          => ['2026-04-10' => 80.0, '2026-04-11' => 0.0],
            ],
            $samples
        );
    }

    public function testExtractSamplesDefaultsMissingMonotonicityToUp(): void
    {
        // Defensive: if a caller fails to thread the monotonicity map through, the
        // rowless backfill should retain the legacy "always emit 0" behaviour rather
        // than silently dropping every series. Pin that fallback so a regression that
        // forgets the parameter does not produce mysteriously empty sample maps on
        // every no-traffic day.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => ['nb_visits' => 80.0],
            '2026-04-11' => [],
        ]);

        $samples = $fetcher->extractSamples($map, ['Visits' => 'nb_visits'], [], [], 'day');

        self::assertSame(
            ['Visits' => ['2026-04-10' => 80.0, '2026-04-11' => 0.0]],
            $samples
        );
    }

    public function testExtractSamplesUsesPerSeriesRowMatcherForMultiRowSeries(): void
    {
        // Multi-row evolution graphs (selectable_rows on a per-row report, e.g. plotting
        // France and Germany on a Country.getCountry evolution) plot one series per selected
        // row. The displayed-series path pulls each series' values from $childTable
        // ->getRowFromLabel($rowMatcher). The sub-period sample fetch must do the same -- if
        // it falls back to getFirstRow() then both series' historical samples are taken from
        // whichever country happens to sort first in the sub-period archive, collapsing both
        // forecasts onto identical, wrong history.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            // Sort order is "Germany first, France second" -- representative of a
            // top-by-visits sub-period archive where the user's selected rows are not the top.
            '2026-04-10' => [
                ['label' => 'Germany', 'nb_visits' => 500.0],
                ['label' => 'France',  'nb_visits' => 80.0],
            ],
            '2026-04-11' => [
                ['label' => 'Germany', 'nb_visits' => 510.0],
                ['label' => 'France',  'nb_visits' => 90.0],
            ],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            ['France' => 'nb_visits', 'Germany' => 'nb_visits'],
            ['France' => 'France', 'Germany' => 'Germany'],
            [
                'France'  => ForecastMetricClassifier::MONOTONICITY_UP,
                'Germany' => ForecastMetricClassifier::MONOTONICITY_UP,
            ],
            'day'
        );

        self::assertSame(
            [
                'France'  => ['2026-04-10' => 80.0, '2026-04-11' => 90.0],
                'Germany' => ['2026-04-10' => 500.0, '2026-04-11' => 510.0],
            ],
            $samples
        );
    }

    public function testExtractSamplesBackfillsZeroForMonotonicUpSeriesWhenRowMatcherIsMissing(): void
    {
        // A specific selected row may be absent from an individual sub-period archive (e.g.
        // France had no visits that day, so the daily Country.getCountry archive has no
        // France row). For MONOTONICITY_UP count series this is a real zero observation, so
        // the extractor backfills 0.0; for DOWN/FREE the date stays absent.
        $fetcher = $this->createFetcher();
        $map = $this->createSampleResultMap([
            '2026-04-10' => [
                ['label' => 'Germany', 'nb_visits' => 500.0],
                ['label' => 'France',  'nb_visits' => 80.0],
            ],
            // 2026-04-11: only Germany, no France row at all.
            '2026-04-11' => [
                ['label' => 'Germany', 'nb_visits' => 510.0],
            ],
        ]);

        $samples = $fetcher->extractSamples(
            $map,
            ['France' => 'nb_visits', 'Germany' => 'nb_visits'],
            ['France' => 'France', 'Germany' => 'Germany'],
            [
                'France'  => ForecastMetricClassifier::MONOTONICITY_UP,
                'Germany' => ForecastMetricClassifier::MONOTONICITY_UP,
            ],
            'day'
        );

        self::assertSame(
            [
                'France'  => ['2026-04-10' => 80.0, '2026-04-11' => 0.0],
                'Germany' => ['2026-04-10' => 500.0, '2026-04-11' => 510.0],
            ],
            $samples
        );
    }

    private function createFetcher(?callable $apiRequestProcessor = null, ?LoggerInterface $logger = null): ForecastSubPeriodFetcher
    {
        return new ForecastSubPeriodFetcher(
            $apiRequestProcessor ?? static function () {
                self::fail('Inner API request should not have fired for this case.');
            },
            $logger ?? $this->createMock(LoggerInterface::class)
        );
    }

    private function createDayDataTable(string $date): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
        return $dataTable;
    }

    /**
     * @param array<string, mixed> $rowsByDate Map of date (Y-m-d) → either a column map for
     *        the single-row case (`['nb_visits' => 80.0]`) or a list of column maps for the
     *        multi-row case (`[['label' => 'France', 'nb_visits' => 80.0], ['label' => ...]]`).
     *        An empty array creates a rowless sub-table.
     */
    private function createSampleResultMap(array $rowsByDate): DataTable\Map
    {
        $map = new DataTable\Map();
        foreach ($rowsByDate as $date => $rows) {
            $subTable = new DataTable();
            $subTable->setMetadata(DataTableFactory::TABLE_METADATA_PERIOD_INDEX, Factory::build('day', $date));
            if ($rows !== []) {
                // Disambiguate single-row column map from list-of-rows by checking whether
                // the first value is itself an array.
                $firstValue = reset($rows);
                $isListOfRows = is_array($firstValue);
                $rowSet = $isListOfRows ? $rows : [$rows];
                foreach ($rowSet as $rowColumns) {
                    $subTable->addRowFromArray([DataTable\Row::COLUMNS => $rowColumns]);
                }
            }
            $map->addTable($subTable, $date);
        }
        return $map;
    }
}
