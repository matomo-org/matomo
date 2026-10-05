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
use Piwik\Tracker;
use Piwik\Tracker\Action;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Integration tests for accurate per-pageview time-spent capture.
 *
 * Covers:
 *  - PV with pv_id inserts a row with time_spent = 0
 *  - Follow-up event with the same pv_id updates time_spent
 *  - Heartbeat (ping=1) with the same pv_id updates time_spent
 *  - A second PV with a *different* pv_id does NOT collapse the first row (per-tab attribution)
 *  - PV without pv_id is recorded with NULL idpageview
 *  - Non-PV without pv_id closes the most recent pageview row
 *  - Non-ASCII pv_id is treated as absent (idpageview is an ascii-only column)
 *  - time_spent is capped at visit_standard_length
 *  - Kill-switch (record_accurate_page_view_time = 0) disables all writes
 *  - Kill-switch can be applied per site via a [Tracker_N] config section
 *  - Inside a tracker transaction (bulk / queued tracking) the same rows are written
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
        $config->Tracker_1 = [];

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

    public function testEventWithoutPvIdClosesTheMostRecentPageView()
    {
        // Simulate a tracker that doesn't emit pv_id (older SDKs / server-side libs). setPageviewId('')
        // suppresses the auto-generated pv_id while leaving everything else intact. An event's own
        // idaction_url is a TYPE_EVENT row, so it cannot be matched by idaction; the writer instead
        // closes the most recent row, which is the page the legacy path credits too. Legacy cannot
        // fill this in: events are excluded from the legacy time query entirely.
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
        $this->assertSame(15, (int) $rows[0]['time_spent'], 'A pv_id-less event closes the most recent pageview row');
    }

    public function testNonAsciiPvIdIsHashedInsteadOfFailingTheWrite()
    {
        // idpageview is CHAR(6) CHARACTER SET ascii while log_link_visit_action.idpageview is
        // utf8mb4: a crafted pv_id like 'ééé' (6 bytes of valid UTF-8) passes the llva insert
        // but would make the pvt INSERT fail under strict SQL mode. Keying it by a hash keeps
        // the row, and keeps the id usable, so a later hit still finds this page view.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('ééé');
        $tracker->setUrl('https://example.org/landing');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 15));
        Fixture::checkResponse($tracker->doPing());

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(1, $rows);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{6}$/', (string) $rows[0]['idpageview']);
        $this->assertSame(15, (int) $rows[0]['time_spent']);
    }

    public function testPunctuatedPvIdIsKeptRatherThanTreatedAsAbsent()
    {
        // A site setting its own id through setPageViewId() can use characters the JS tracker
        // never generates. The ascii column stores them, so the writer must attribute by id
        // instead of falling back to closing whichever row happens to be most recent.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('p_001');
        $tracker->setUrl('https://example.org/landing');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 10));
        $tracker->setPageviewId('p_002');
        $tracker->setUrl('https://example.org/second');
        Fixture::checkResponse($tracker->doTrackPageView('Second'));

        // Carries the first page's id, so it must close that row and not the newer one.
        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 40));
        $tracker->setPageviewId('p_001');
        Fixture::checkResponse($tracker->doTrackEvent('Video', 'play'));

        $rows = $this->fetchRowsByPvId();
        $this->assertSame(['p_001', 'p_002'], array_keys($rows));
        $this->assertSame(40, (int) $rows['p_001']['time_spent']);
        $this->assertSame(0, (int) $rows['p_002']['time_spent']);
    }

    public function testOverlongPvIdsThatShareAPrefixStillSelectTheirOwnRow()
    {
        // Both ids share their first six characters. Truncating before checking would store
        // one id for both page views, and the later hit would then grow the wrong row.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('pageview-1');
        $tracker->setUrl('https://example.org/landing');
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 10));
        $tracker->setPageviewId('pageview-2');
        $tracker->setUrl('https://example.org/second');
        Fixture::checkResponse($tracker->doTrackPageView('Second'));

        // Back on the first page, which is no longer the most recent row.
        $tracker->setForceVisitDateTime($this->offset($this->baseTime, 40));
        $tracker->setPageviewId('pageview-1');
        Fixture::checkResponse($tracker->doTrackEvent('Video', 'play'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertCount(2, $rows);
        $this->assertNotSame($rows[0]['idpageview'], $rows[1]['idpageview']);
        $this->assertNotNull($rows[0]['idpageview']);

        // The event reached the first page view, not the newer one.
        $this->assertSame(40, (int) $rows[0]['time_spent']);
        $this->assertSame(0, (int) $rows[1]['time_spent']);
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

    public function testKillSwitchCanBeDisabledPerSiteViaTrackerSection()
    {
        // A [Tracker_N] section overrides [Tracker] for site N only; the writer reads the
        // kill-switch with the request's idSite so operators can disable the accurate metric
        // for a single problematic site.
        $config = Config::getInstance();
        $config->Tracker_1 = ['record_accurate_page_view_time' => 0];
        Cache::deleteTrackerCache();

        $matomoTracker = $this->getTracker($this->baseTime);
        $matomoTracker->setPageviewId('killit');
        $matomoTracker->setUrl('https://example.org/off');
        Fixture::checkResponse($matomoTracker->doTrackPageView('Off'));

        $rows = $this->fetchPageViewTimeRows();
        $this->assertSame([], $rows, 'Per-site kill-switch must prevent writes for that site');
    }

    public function testWritesTheSameRowsInsideATrackerTransaction()
    {
        // Bulk and queued tracking run the writer inside a transaction, where it locks the visit
        // row first. That must not change what it writes.
        $tracker = $this->getTracker($this->baseTime);
        $tracker->setPageviewId('aaaaaa');
        $tracker->setUrl('https://example.org/page-a');
        Fixture::checkResponse($tracker->doTrackPageView('Page A'));
        $idVisit = (int) Db::fetchOne('SELECT idvisit FROM ' . Common::prefixTable('log_visit'));

        $pageView = $this->createMock(Action::class);
        $pageView->method('getActionType')->willReturn(Action::TYPE_PAGE_URL);
        $pageView->method('getIdLinkVisitAction')->willReturn(999);
        $pageView->method('getIdActionUrl')->willReturn(1);
        $pageView->method('getIdActionName')->willReturn(2);

        $db = Tracker::getDatabase();
        $transactionId = $db->beginTransaction();
        try {
            $writer = new PageViewTimeWriter();
            $writer->write($pageView, new VisitProperties(['idvisit' => $idVisit]), $this->makeRequest('bbbbbb', 20));
            $writer->write(null, new VisitProperties(['idvisit' => $idVisit]), $this->makeRequest('bbbbbb', 50));
            $db->commit($transactionId);
        } finally {
            Tracker::disconnectCachedDbConnection();
        }

        $rows = $this->fetchRowsByPvId();
        $this->assertCount(2, $rows);
        $this->assertSame(20, (int) $rows['aaaaaa']['time_spent'], 'The new pageview closes the previous one');
        $this->assertSame(30, (int) $rows['bbbbbb']['time_spent'], 'The ping grows the new pageview');
    }

    private function makeRequest(string $pvId, int $secondsAfterBase): Request
    {
        $request = new Request(['idsite' => 1, 'pv_id' => $pvId]);
        $request->setCurrentTimestamp(strtotime($this->baseTime) + $secondsAfterBase);
        $request->setMetadata('CoreHome', 'isNewVisit', false);
        return $request;
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
