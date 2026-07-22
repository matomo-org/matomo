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

    public function testPageViewInsertsSingleRowWithZeroTime()
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

    public function testEventAfterPageViewUpdatesTimeSpentOnSameRow()
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

    public function testPingHeartbeatWithSamePvIdUpdatesTimeSpent()
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

    public function testSecondPageViewWithDifferentPvIdClosesFirstRowAndCreatesSeparate()
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

    public function testEventForFirstTabDoesNotTouchSecondTabRow()
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

    public function testTimeSpentCappedAtVisitStandardLength()
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

    public function testPageViewWithoutExplicitPvIdStillInsertsRow()
    {
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setUrl('https://example.org/no-pvid');
        Fixture::checkResponse($tracker->doTrackPageView('No pv_id'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        // MatomoTracker auto-generates a pv_id client-side when none is set explicitly, so we only
        // assert that exactly one row exists and the time_spent baseline is correct.
        $this->assertSame(0, (int) $rows[0]['time_spent']);
        $this->assertNotNull($rows[0]['idaction_url']);
    }

    public function testEventWithoutPvIdIsSkippedToProtectMultiTabAttribution()
    {
        // Simulate a tracker that doesn't emit pv_id (older SDKs / server-side libs). setPageviewId('')
        // suppresses the auto-generated pv_id while leaving everything else intact. Without a pv_id
        // we cannot safely attribute time to a specific tab (an event's idaction_url is a TYPE_EVENT
        // row, not the page's TYPE_PAGE_URL row), so the writer skips the update. The row from the
        // initial PV stays at time_spent = 0; the legacy archive path still credits this pageview
        // via `time_spent_ref_action` on the following action.
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

    public function testSiteSearchAfterPageviewInsertsSeparateRowAndClosesPreviousPage()
    {
        // A site-search hit shares pv_id with the parent page (the JS tracker does not rotate
        // pv_id between them) but produces its own log_link_visit_action row. The writer
        // records it like a page-view so the idlink_va-keyed anti-join on the archive side has
        // an exact per-action match and cannot double-count.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('shared');
        $tracker->setUrl('https://example.org/results');
        Fixture::checkResponse($tracker->doTrackPageView('Results page'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 12));
        Fixture::checkResponse($tracker->doTrackSiteSearch('shoes', 'catalog', 3));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(2, $rows, 'Site search gets its own row alongside the page-view');

        // Rows are ordered by idpageviewtime ASC.
        $this->assertSame('shared', $rows[0]['idpageview']);
        $this->assertSame('shared', $rows[1]['idpageview']);
        $this->assertSame(12, (int) $rows[0]['time_spent'], 'Search hit closes the parent page');
        $this->assertSame(0, (int) $rows[1]['time_spent']);
    }

    public function testKillSwitchDisablesAllWrites()
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
            'SELECT idpageviewtime, idpageview, idlink_va, idaction_url, idaction_name, server_time, time_spent
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
