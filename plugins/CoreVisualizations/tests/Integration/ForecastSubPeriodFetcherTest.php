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
use Piwik\DataTable;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugin;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSeriesState;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastSubPeriodFetcher;
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
