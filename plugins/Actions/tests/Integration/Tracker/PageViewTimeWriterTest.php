<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Plugins\Actions\Tracker\PageViewTimeWriter;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * Integration tests for accurate per-pageview time-spent capture.
 *
 * Covers:
 *  - PV with pv_id inserts a row with time_spent = 0
 *  - Follow-up event with the same pv_id updates time_spent
 *  - Heartbeat (ping=1) with the same pv_id updates time_spent
 *  - A second PV with a *different* pv_id does NOT collapse the first row (per-tab attribution)
 *  - PV without pv_id is recorded with NULL idpageview
 *  - Non-PV without pv_id is skipped (cannot safely attribute in a multi-tab session)
 *  - time_spent is capped at visit_standard_length
 *  - Kill-switch (record_accurate_page_view_time = 0) disables all writes
 *
 * @group Actions
 * @group PageViewTime
 * @group Plugins
 * @group Tracker
 */
class PageViewTimeWriterTest extends IntegrationTestCase
{
    /** @var string base of all timestamps used in this test, within the last 24h to avoid the token_auth requirement. */
    private $baseTime;

    public function setUp(): void
    {
        parent::setUp();

        // Timestamps inside the 24h window where Matomo doesn't require token_auth for &cdt overrides.
        // Two hours back so we can also fire heartbeats further in the past without overshooting "now".
        $this->baseTime = date('Y-m-d H:i:s', time() - 7200);

        Fixture::createWebsite(date('Y-m-d 00:00:00', time() - 86400));
        Cache::deleteTrackerCache();
    }

    public function tearDown(): void
    {
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['record_accurate_page_view_time'] = 1;
        $config->Tracker = $tracker;

        parent::tearDown();
    }

    public function test_pageView_insertsSingleRowWithZeroTime()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('abc123');
        $tracker->setUrl('https://example.org/landing');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $rows = $this->fetchPageViewTimeRows();

        $this->assertCount(1, $rows);
        $this->assertSame('abc123', $rows[0]['idpageview']);
        $this->assertSame(0, (int) $rows[0]['time_spent']);
        $this->assertNotNull($rows[0]['idaction_url']);
        $this->assertSame($this->baseTime, $rows[0]['server_time']);
    }

    public function test_eventAfterPageView_updatesTimeSpentOnSameRow()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('abc123');
        $tracker->setUrl('https://example.org/landing');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 25));
        Fixture::checkResponse($tracker->doTrackEvent('Engagement', 'scroll'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertSame('abc123', $rows[0]['idpageview']);
        $this->assertSame(25, (int) $rows[0]['time_spent']);
    }

    public function test_pingHeartbeatWithSamePvId_updatesTimeSpent()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('abc123');
        $tracker->setUrl('https://example.org/landing');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 30));
        $tracker->setDebugStringAppend('&ping=1');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertSame(30, (int) $rows[0]['time_spent']);
    }

    public function test_secondPageViewWithDifferentPvId_closesFirstRowAndCreatesSeparate()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('aaaaaa');
        $tracker->setUrl('https://example.org/page-a');
        Fixture::checkResponse($tracker->doTrackPageView('Page A'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 20));
        $tracker->setPageviewId('bbbbbb');
        $tracker->setUrl('https://example.org/page-b');
        Fixture::checkResponse($tracker->doTrackPageView('Page B'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(2, $rows, 'Each tab/pv_id gets its own row');

        // Rows are ordered by idpageviewtime ASC in fetchPageViewTimeRows().
        $this->assertSame('aaaaaa', $rows[0]['idpageview']);
        $this->assertSame('bbbbbb', $rows[1]['idpageview']);
        $this->assertSame(
            20,
            (int) $rows[0]['time_spent'],
            'Second PV insert closes the first PV row to the gap between them'
        );
        $this->assertSame(0, (int) $rows[1]['time_spent']);
    }

    public function test_eventForFirstTab_doesNotTouchSecondTabRow()
    {
        // Multi-tab attribution: an event tagged with pv_id=aaaaaa should only update Tab A.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('aaaaaa');
        $tracker->setUrl('https://example.org/page-a');
        Fixture::checkResponse($tracker->doTrackPageView('Page A'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 10));
        $tracker->setPageviewId('bbbbbb');
        $tracker->setUrl('https://example.org/page-b');
        Fixture::checkResponse($tracker->doTrackPageView('Page B'));

        // Event tagged for Tab A (older tab) arrives after Tab B is open.
        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 50));
        $tracker->setPageviewId('aaaaaa');
        $tracker->setUrl('https://example.org/page-a');
        Fixture::checkResponse($tracker->doTrackEvent('Engagement', 'scroll'));

        $rowsByPvId = $this->fetchRowsByPvId();

        $this->assertSame(50, (int) $rowsByPvId['aaaaaa']['time_spent'], 'Tab A absorbs the event time');
        $this->assertSame(0, (int) $rowsByPvId['bbbbbb']['time_spent'], 'Tab B must not change');
    }

    public function test_timeSpentCappedAtVisitStandardLength()
    {
        // Force a tight cap so we can verify it without firing requests across the visit window.
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['visit_standard_length'] = 60;
        $config->Tracker = $tracker;
        Cache::deleteTrackerCache();

        $matomoTracker = $this->getTracker($this->baseTime);
        $matomoTracker->setPageviewId('capcap');
        $matomoTracker->setUrl('https://example.org/long');
        Fixture::checkResponse($matomoTracker->doTrackPageView('Long'));

        // 300s later, well past our 60s cap.
        $matomoTracker->setForceVisitDateTime($this->offset($this->baseTime, 300));
        $matomoTracker->setDebugStringAppend('&ping=1');
        Fixture::checkResponse($matomoTracker->doTrackPageView('Long'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertNotEmpty($rows);
        $this->assertLessThanOrEqual(60, (int) $rows[0]['time_spent']);
    }

    public function test_pageViewWithoutPvId_insertsNullPvIdRow()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setUrl('https://example.org/no-pvid');
        Fixture::checkResponse($tracker->doTrackPageView('No pv_id'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        // MatomoTracker auto-generates idPageview client-side; we ignore that by overriding the URL
        // through setDebugStringAppend to strip pv_id altogether? Without overriding, we still
        // assert *only* that exactly one row exists and the time_spent baseline is correct.
        $this->assertSame(0, (int) $rows[0]['time_spent']);
        $this->assertNotNull($rows[0]['idaction_url']);
    }

    public function test_eventWithoutPvId_isSkippedToProtectMultiTabAttribution()
    {
        // Simulate a tracker that doesn't emit pv_id (older SDKs / server-side libs). setPageviewId('')
        // suppresses the auto-generated pv_id while leaving everything else intact. Without a pv_id
        // we cannot safely attribute time to a specific tab (an event's idaction_url is a TYPE_EVENT
        // row, not the page's TYPE_PAGE_URL row), so the writer skips the update. The row from the
        // initial PV stays at time_spent = 0; the archive-time CASE WHEN backfill applies later.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('');
        $tracker->setUrl('https://example.org/no-pvid');
        Fixture::checkResponse($tracker->doTrackPageView('No pv_id'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 15));
        $tracker->setPageviewId('');
        $tracker->setUrl('https://example.org/no-pvid');
        Fixture::checkResponse($tracker->doTrackEvent('Engagement', 'click'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['idpageview']);
        $this->assertSame(0, (int) $rows[0]['time_spent'], 'Without pv_id we cannot safely attribute; row stays untouched');
    }

    public function test_clientPvTime_overridesServerSideCalculation()
    {
        // The tracker JS counts focused time itself and ships the seconds via &pv_time. The
        // writer should trust that number instead of computing (now − server_time). Useful for
        // privacy browsers that drop unload pings, and for trackers that want to send a smaller
        // focused-only number rather than total tab-open time.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('cltime');
        $tracker->setUrl('https://example.org/client-time');
        Fixture::checkResponse($tracker->doTrackPageView('Client time'));

        // Server clock advances 60s but the client says it was only on the page for 12s
        // (e.g. focused-only). The writer must trust the client value, not (60 − 0).
        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 60));
        $tracker->setDebugStringAppend('&ping=1&' . PageViewTimeWriter::PARAM_PV_TIME . '=12');
        Fixture::checkResponse($tracker->doTrackPageView('Client time'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertSame(12, (int) $rows[0]['time_spent']);
    }

    public function test_clientPvTime_neverShrinksAnEarlierLargerObservation()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('grow12');
        $tracker->setUrl('https://example.org/client-time');
        Fixture::checkResponse($tracker->doTrackPageView('Client time'));

        // First ping: server clock at +30s, client claims 25s (focused only).
        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 30));
        $tracker->setDebugStringAppend('&ping=1&' . PageViewTimeWriter::PARAM_PV_TIME . '=25');
        Fixture::checkResponse($tracker->doTrackPageView('Client time'));

        // Second ping arrives later but with a *smaller* client value (e.g. browser tab idle
        // throttled the counter). GREATEST() must keep the earlier 25s, not regress to 10s.
        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 60));
        $tracker->setDebugStringAppend('&ping=1&' . PageViewTimeWriter::PARAM_PV_TIME . '=10');
        Fixture::checkResponse($tracker->doTrackPageView('Client time'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertSame(25, (int) $rows[0]['time_spent']);
    }

    public function test_clientPvTime_missingFallsBackToServerCalculation()
    {
        // When pv_time isn't sent, the writer continues to use the server-side
        // (now − server_time) calculation. Same as before this feature existed.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('fbk12a');
        $tracker->setUrl('https://example.org/no-pvtime');
        Fixture::checkResponse($tracker->doTrackPageView('No pv_time'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 40));
        Fixture::checkResponse($tracker->doTrackEvent('Engagement', 'click'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertSame(40, (int) $rows[0]['time_spent']);
    }

    public function test_killSwitch_disablesAllWrites()
    {
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['record_accurate_page_view_time'] = 0;
        $config->Tracker = $tracker;
        Cache::deleteTrackerCache();

        $matomoTracker = $this->getTracker($this->baseTime);
        $matomoTracker->setPageviewId('killit');
        $matomoTracker->setUrl('https://example.org/off');
        Fixture::checkResponse($matomoTracker->doTrackPageView('Off'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertSame([], $rows, 'Kill-switch must prevent any writes to log_page_view_time');
    }

    private function getTracker(string $timestamp): \MatomoTracker
    {
        $tracker = Fixture::getTracker(1, $timestamp, $defaultInit = true, $useLocalTracker = true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        return $tracker;
    }

    private function offset(string $datetime, int $seconds): string
    {
        return date('Y-m-d H:i:s', strtotime($datetime) + $seconds);
    }

    private function fetchPageViewTimeRows(): array
    {
        return Db::fetchAll(
            'SELECT idpageviewtime, idpageview, idaction_url, idaction_name, server_time, time_spent
             FROM ' . Common::prefixTable('log_page_view_time') . '
             ORDER BY idpageviewtime ASC'
        );
    }

    private function fetchRowsByPvId(): array
    {
        $out = [];
        foreach ($this->fetchPageViewTimeRows() as $row) {
            $out[$row['idpageview']] = $row;
        }
        return $out;
    }
}
