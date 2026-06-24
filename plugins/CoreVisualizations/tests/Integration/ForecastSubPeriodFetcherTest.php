<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\tests\Integration;

use Piwik\Archive\DataTableFactory;
use Piwik\Config;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugin;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSubPeriodFetcher;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreVisualizations
 * @group ForecastSubPeriodFetcher
 * @group Bandwidth
 * @group Plugins
 */
class ForecastSubPeriodFetcherTest extends IntegrationTestCase
{
    private const TRACKING_DATE = '2026-04-15';

    public function setUp(): void
    {
        parent::setUp();
        FakeAccess::$superUser = true;

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2026-01-01 00:00:00');
        }

        Plugin\Manager::getInstance()->loadPlugin('Bandwidth');
        try {
            Plugin\Manager::getInstance()->activatePlugin('Bandwidth');
        } catch (\Exception $e) {
            // Already active.
        }
    }

    public function testInnerSubPeriodSamplesArriveInChartUnits(): void
    {
        // Track a handful of pageviews with bandwidth payloads on a known date so the
        // archived nb_total_overall_bandwidth column lands at ~3 GB worth of bytes. The
        // forecast precompute reads sub-period samples for that column through this
        // fetcher; without the units alignment they would arrive in raw archive bytes
        // (~3 * 10^9) and the seasonal decomposition would sum them against an outer
        // current value already in gigabytes -- the ~10^9 blowup users see on bandwidth
        // metric forecasts.
        $tracker = Fixture::getTracker(1, self::TRACKING_DATE . ' 00:01:01', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $perPageviewBytes = 1073741824; // exactly 1 GB raw bytes per pageview
        foreach (['/a', '/b', '/c'] as $url) {
            $tracker->setUrl('http://example.org' . $url);
            $tracker->setDebugStringAppend('bw_bytes=' . $perPageviewBytes);
            $tracker->doTrackPageView($url);
        }

        // Trigger archiving for the day so the inner API.get request has a row to return.
        \Piwik\API\Request::processRequest('API.get', [
            'idSite' => 1,
            'period' => 'day',
            'date'   => self::TRACKING_DATE,
            'format' => 'original',
            'serialize' => '0',
        ]);

        $monthTable = new DataTable();
        $monthTable->setMetadata(
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
            PeriodFactory::build('month', self::TRACKING_DATE)
        );

        $fetcher = new ForecastSubPeriodFetcher();
        $result = $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Bytes' => 'nb_total_overall_bandwidth'],
                [],
                ['Bytes' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'API.get',
            1,
            ''
        );

        // 3 pageviews x 1 GB each = 3 GB total raw bytes for the tracked day. The chart's
        // Numeric formatter pins the byte size unit at 'G' with precision 2, so the same
        // formatter pass on the inner sub-table must return the value as 3.00 (gigabytes),
        // not 3221225472 (raw bytes). The test passes the assertion only if the units fix
        // is active on the fetcher; without it the sample lands at the raw byte count.
        $trackedDay = self::TRACKING_DATE;
        self::assertArrayHasKey('Bytes', $result['daily']);
        self::assertArrayHasKey($trackedDay, $result['daily']['Bytes'], 'Inner daily map is missing the tracked day');

        $sample = $result['daily']['Bytes'][$trackedDay];
        self::assertLessThan(
            100.0,
            $sample,
            sprintf(
                'Inner sub-period sample arrived in raw bytes (%s) -- the Numeric formatter pass on the'
                . ' inner fetch result is not running, so the forecast will land ~10^9 too large.',
                var_export($sample, true)
            )
        );
        // 3 pageviews x 1 GB / 1024^3 = 3.0, rounded to 3.0 at precision 2.
        self::assertEqualsWithDelta(3.0, $sample, 0.01);
    }

    public function testInnerSubPeriodPercentMetricArrivesInChartUnits(): void
    {
        // conversion_rate is a percentage ProcessedMetric. The displayed chart scales it to
        // whole percent (e.g. 25.0 for "25%") through Goals.getMetrics, whose report declares
        // the ConversionRate object. The inner sub-period fetch resolves Goals.get, whose
        // report lists conversion_rate only as a string, so before the fix the sample arrived
        // as the raw 0..1 quotient (0.25) and the forecast rendered ~100x too small. The fix
        // seeds the delegated metric object so the inner Numeric pass scales identically.
        Plugin\Manager::getInstance()->loadPlugin('Goals');
        try {
            Plugin\Manager::getInstance()->activatePlugin('Goals');
        } catch (\Exception $e) {
            // Already active.
        }

        \Piwik\Plugins\Goals\API::getInstance()->addGoal(
            1,
            'Thank You',
            'url',
            'thankyou',
            'contains'
        );

        // Four distinct visits, one of which converts the goal: nb_visits = 4,
        // nb_visits_converted = 1, so conversion_rate = 25% (raw quotient 0.25, whole 25.0).
        for ($visit = 0; $visit < 4; $visit++) {
            $tracker = Fixture::getTracker(1, self::TRACKING_DATE . ' 0' . $visit . ':10:00', true, true);
            $tracker->setTokenAuth(Fixture::getTokenAuth());
            $tracker->setVisitorId(substr(md5('visitor' . $visit), 0, 16));
            $tracker->setUrl('http://example.org/page' . $visit);
            $tracker->doTrackPageView('page' . $visit);
            if ($visit === 0) {
                $tracker->setUrl('http://example.org/thankyou');
                $tracker->doTrackPageView('thankyou');
            }
        }

        \Piwik\API\Request::processRequest('Goals.get', [
            'idSite'    => 1,
            'period'    => 'day',
            'date'      => self::TRACKING_DATE,
            'format'    => 'original',
            'serialize' => '0',
        ]);

        $monthTable = new DataTable();
        $monthTable->setMetadata(
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
            PeriodFactory::build('month', self::TRACKING_DATE)
        );

        $fetcher = new ForecastSubPeriodFetcher();
        $result = $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Conversion Rate' => 'conversion_rate'],
                [],
                ['Conversion Rate' => ForecastMetricClassifier::MONOTONICITY_FREE]
            ),
            'Goals.get',
            1,
            ''
        );

        self::assertArrayHasKey('Conversion Rate', $result['daily']);
        self::assertArrayHasKey(
            self::TRACKING_DATE,
            $result['daily']['Conversion Rate'],
            'Inner daily map is missing the tracked day'
        );

        $sample = $result['daily']['Conversion Rate'][self::TRACKING_DATE];
        self::assertGreaterThan(
            1.5,
            $sample,
            sprintf(
                'Inner conversion_rate sample arrived as a raw 0..1 quotient (%s) -- the Numeric'
                . ' formatter pass did not scale the percentage to whole percent, so the forecast'
                . ' would render ~100x too small.',
                var_export($sample, true)
            )
        );
        // 1 converted visit / 4 visits = 25%, whole-percent 25.0 (matching the displayed chart).
        self::assertEqualsWithDelta(25.0, $sample, 0.5);
    }

    public function testInnerSubPeriodMaxMetricReturnsDailyMaxNotSum(): void
    {
        // max_actions is the maximum action count in any single visit within the period -- an
        // extremum, not an additive total. Five pageviews from one visitor make a single visit
        // with five actions, so the day's archived max_actions = 5 (regardless of how many
        // other one-pageview visits also occur). The inner sub-period fetch must return that
        // daily max unchanged, not anything resembling a sum (the pre-MONOTONICITY_MAX
        // decomposition summed ~30 daily maxes and inflated the forecast an order of
        // magnitude; this regression test guards the fetched sample at the source).
        $tracker = Fixture::getTracker(1, self::TRACKING_DATE . ' 00:01:01', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        foreach (['/a', '/b', '/c', '/d', '/e'] as $url) {
            $tracker->setUrl('http://example.org' . $url);
            $tracker->doTrackPageView($url);
        }

        \Piwik\API\Request::processRequest('API.get', [
            'idSite' => 1,
            'period' => 'day',
            'date'   => self::TRACKING_DATE,
            'format' => 'original',
            'serialize' => '0',
        ]);

        $monthTable = new DataTable();
        $monthTable->setMetadata(
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
            PeriodFactory::build('month', self::TRACKING_DATE)
        );

        $fetcher = new ForecastSubPeriodFetcher();
        $result = $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Max actions' => 'max_actions'],
                [],
                ['Max actions' => ForecastMetricClassifier::MONOTONICITY_MAX]
            ),
            'API.get',
            1,
            ''
        );

        self::assertArrayHasKey('Max actions', $result['daily']);
        self::assertArrayHasKey(
            self::TRACKING_DATE,
            $result['daily']['Max actions'],
            'Inner daily map is missing the tracked day for max_actions'
        );

        $sample = $result['daily']['Max actions'][self::TRACKING_DATE];
        // 5 actions in one visit. Any value substantially larger would mean the fetcher (or
        // some upstream pass) is summing/scaling the column instead of returning the raw
        // archived max per day -- the original max_actions blowup shape.
        self::assertLessThan(
            10.0,
            $sample,
            sprintf(
                'Inner max_actions sample is larger than the per-day archived max (%s) -- the'
                . ' fetcher is producing a non-max-shaped value for an extremum metric, which'
                . ' would feed the seasonal decomposition into the bytes-domain-style blowup'
                . ' MONOTONICITY_MAX exists to prevent.',
                var_export($sample, true)
            )
        );
        self::assertSame(5.0, $sample);
    }

    public function testInnerFetchWindowsAreClampedToSiteCreationDate(): void
    {
        // Site 1's ts_created is 2025-12-31 -- Fixture::createWebsite stores the day before the
        // date it is passed (2026-01-01 in setUp). A month-period forecast's monthly fan-out
        // would naturally reach MONTH_MONTHLY_WINDOW_YEARS back to 2024-01-01 -- two full years
        // before the site existed. Every one of those pre-existence month sub-periods is a
        // guaranteed-empty archive lookup that hits the archiver's skip path (recomputed, never
        // persisted) on every render, or triggers on-demand archiving under browser archiving.
        // With the real resolver wired in (only the inner request is stubbed, to capture the
        // dates), the fetcher must clamp the window start to the site creation date.
        $captured = [];
        $fetcher = new ForecastSubPeriodFetcher(function (string $apiMethod, array $params) use (&$captured) {
            $captured[$params['period']] = $params['date'];
            return new DataTable\Map();
        });

        $monthTable = new DataTable();
        $monthTable->setMetadata(
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
            PeriodFactory::build('month', '2026-04-01')
        );

        $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Visits' => 'nb_visits'],
                [],
                ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
            ),
            'VisitsSummary.get',
            1,
            ''
        );

        // Monthly window clamped from its natural 2024-01-01 start up to the 2025-12-31 site
        // creation date. The daily window (natural start 2026-02-28) already falls after
        // creation, so it is left untouched -- proving the clamp raises only what predates the site.
        self::assertSame('2025-12-31,2026-04-29', $captured['month'] ?? null);
        self::assertSame('2026-02-28,2026-04-29', $captured['day'] ?? null);
    }

    public function testApiGetFanOutSplitsAndMergesSamplesAcrossOwningModules(): void
    {
        // API.get is the cross-plugin merge; the fan-out resolves each plotted column to its
        // owning Module.get so the sub-period reads are archive-backed (no per-sub-period
        // report-metadata catalog rebuild -- the dominant render cost API.get otherwise incurs).
        // nb_visits is a VisitsSummary.get metric and nb_pageviews an Actions.get metric, so a
        // graph plotting both must fan out into one request per module. This proves both groups
        // are fetched, formatted with their own report, and merged back into one per-series map.
        $tracker = Fixture::getTracker(1, self::TRACKING_DATE . ' 00:01:01', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        foreach (['/a', '/b', '/c'] as $url) {
            $tracker->setUrl('http://example.org' . $url);
            $tracker->doTrackPageView($url);
        }

        // Archive the day so both modules' sub-period reads have a row to return.
        \Piwik\API\Request::processRequest('API.get', [
            'idSite'    => 1,
            'period'    => 'day',
            'date'      => self::TRACKING_DATE,
            'format'    => 'original',
            'serialize' => '0',
        ]);

        $monthTable = new DataTable();
        $monthTable->setMetadata(
            DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
            PeriodFactory::build('month', self::TRACKING_DATE)
        );

        $fetcher = new ForecastSubPeriodFetcher();
        $result = $fetcher->collect(
            [$monthTable],
            $this->createSeriesState(
                ['Visits' => 'nb_visits', 'Pageviews' => 'nb_pageviews'],
                [],
                [
                    'Visits'    => ForecastMetricClassifier::MONOTONICITY_UP,
                    'Pageviews' => ForecastMetricClassifier::MONOTONICITY_UP,
                ]
            ),
            'API.get',
            1,
            ''
        );

        // Both series resolved to their owning module's report and merged into one daily map.
        self::assertArrayHasKey('Visits', $result['daily']);
        self::assertArrayHasKey('Pageviews', $result['daily']);
        // 3 pageviews in a single visit: nb_visits = 1 (VisitsSummary.get), nb_pageviews = 3
        // (Actions.get). Correct per-series values prove the right column came from the right
        // module's fetch -- not a cross-wired or summed merge.
        self::assertSame(1.0, $result['daily']['Visits'][self::TRACKING_DATE] ?? null);
        self::assertSame(3.0, $result['daily']['Pageviews'][self::TRACKING_DATE] ?? null);
    }

    public function testInnerFetchWindowsAreClampedToSegmentCreationDate(): void
    {
        // With process_new_segments_from = segment_creation_time, core only persists an
        // auto-archived segment's archives from its creation date forward; earlier periods come
        // back empty via the archiver's segment skip path. So for a segment created 2026-02-15
        // (later than the 2025-12-31 site creation), the resolver must raise the window floor to
        // the segment date -- the real findSegmentForHash + getReArchiveSegmentStartDate path,
        // not just the site floor. The monthly fan-out for a 2026-04 month forecast then starts
        // at the segment date instead of reaching two years back to 2024-01-01.
        $originalNow = Date::$now;
        $originalProcessFrom = Config::getInstance()->General['process_new_segments_from'] ?? null;
        $originalBrowserTrigger = Config::getInstance()->General['enable_browser_archiving_triggering'] ?? null;
        try {
            Config::getInstance()->General['process_new_segments_from'] = SegmentArchiving::CREATION_TIME;
            // Creating an auto-archived (pre-processed) segment requires browser-triggered
            // archiving to be off, otherwise SegmentEditor\API::add() rejects auto_archive=true.
            Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;

            // ts_created is stamped from Date::now(); fake it so the segment's creation date is
            // deterministic (09:00 keeps it clear of any midnight/timezone boundary).
            Date::$now = strtotime('2026-02-15 09:00:00');
            SegmentEditorApi::getInstance()->add(
                'Firefox visitors',
                'browserCode==FF',
                1,
                true, // autoArchive -- required for findSegmentForHash to match
                true  // enabledAllUsers
            );
            Date::$now = $originalNow;

            $captured = [];
            $fetcher = new ForecastSubPeriodFetcher(function (string $apiMethod, array $params) use (&$captured) {
                $captured[$params['period']] = $params['date'];
                return new DataTable\Map();
            });

            $monthTable = new DataTable();
            $monthTable->setMetadata(
                DataTableFactory::TABLE_METADATA_PERIOD_INDEX,
                PeriodFactory::build('month', '2026-04-01')
            );

            $fetcher->collect(
                [$monthTable],
                $this->createSeriesState(
                    ['Visits' => 'nb_visits'],
                    [],
                    ['Visits' => ForecastMetricClassifier::MONOTONICITY_UP]
                ),
                'VisitsSummary.get',
                1,
                'browserCode==FF'
            );

            // Monthly window clamped to the 2026-02-15 segment creation date (above the site floor).
            self::assertSame('2026-02-15,2026-04-29', $captured['month'] ?? null);
            // Daily window natural start 2026-02-28 already falls after the segment date, untouched.
            self::assertSame('2026-02-28,2026-04-29', $captured['day'] ?? null);
        } finally {
            Date::$now = $originalNow;
            Config::getInstance()->General['process_new_segments_from'] = $originalProcessFrom;
            Config::getInstance()->General['enable_browser_archiving_triggering'] = $originalBrowserTrigger;
        }
    }

    public function testForecastClassifierMapsMaxActionsToMaxWithDefaultSemanticTypes(): void
    {
        // The classifier reads the real Metrics::getDefaultMetricSemanticTypes() map when no
        // explicit override is supplied. This catches the case where a plugin registers
        // max_actions (or a sibling max_*) with a semantic type that would route the name
        // through MONOTONICITY_FREE before the max_ prefix check fires (TYPE_PERCENT,
        // TYPE_FLOAT). Pinning the classification end-to-end means the runtime monotonicity
        // and the unit-tested reducer are exercised against the same map the production
        // evolution graph sees.
        $classifier = new ForecastMetricClassifier();
        self::assertSame(
            ForecastMetricClassifier::MONOTONICITY_MAX,
            $classifier->getColumnMonotonicity('max_actions', false)
        );
    }

    /**
     * @param array<string, string> $columns
     * @param array<string, mixed> $rows
     * @param array<string, string> $monotonicity
     */
    private function createSeriesState(array $columns, array $rows, array $monotonicity): ForecastSeriesState
    {
        return new ForecastSeriesState([], [], $monotonicity, [], $columns, $rows);
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
