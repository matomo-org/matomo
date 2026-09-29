<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\tests\Integration\RecordBuilders;

use Piwik\Common;
use Piwik\Config;
use Piwik\CronArchive;
use Piwik\Db;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugins\Actions\API as ActionsAPI;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache;

/**
 * @group Actions
 * @group PageViewTime
 * @group Plugins
 * @group ActionReportsAccurate
 */
class ActionReportsAccurateArchiveTest extends IntegrationTestCase
{
    /** @var int */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser(true);
        $this->idSite = Fixture::createWebsite('2026-01-01 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;
        Config::getInstance()->General['enable_segments_cache'] = 1;

        Cache::deleteTrackerCache();
    }

    public function tearDown(): void
    {
        $this->setAccurateFlag(true);

        parent::tearDown();
    }

    public function testWeeklyArchiveThatSpansTheKillSwitchFlipSumsBothLegacyAndAccurateDays(): void
    {
        $weekStart = '2026-06-01';
        $legacyDays   = ['2026-06-01', '2026-06-02', '2026-06-03', '2026-06-04'];
        $accurateDays = ['2026-06-05', '2026-06-06', '2026-06-07'];

        $this->setAccurateFlag(false);
        foreach ($legacyDays as $day) {
            $this->trackTimedPageviewPair($day, 'https://example.org/landing');
        }

        $this->setAccurateFlag(true);
        foreach ($accurateDays as $day) {
            $this->trackTimedPageviewPair($day, 'https://example.org/landing');
        }

        (new CronArchive())->main();

        $weeklySumTimeSpent = $this->readSumTimeSpent('week', $weekStart);

        $this->assertGreaterThan(
            0,
            $weeklySumTimeSpent,
            'Weekly archive must retain time_spent from legacy days after the mid-week flip'
        );
        $this->assertGreaterThanOrEqual(
            100,
            $weeklySumTimeSpent,
            'Weekly sum_time_spent should reflect ALL 7 days, not only the 3 accurate-path days'
        );
    }

    public function testInvalidatingAPreUpgradeDayReArchivesUsingLegacySumTimeSpent(): void
    {
        $day = '2026-06-10';

        $this->setAccurateFlag(false);
        $this->trackTimedPageviewPair($day, 'https://example.org/stable');

        (new CronArchive())->main();

        $sumBefore = $this->readSumTimeSpent('day', $day);
        $this->assertGreaterThan(0, $sumBefore, 'Legacy tracked day must produce non-zero sum_time_spent');

        $this->setAccurateFlag(true);
        CoreAdminHomeAPI::getInstance()->invalidateArchivedReports($this->idSite, $day, 'day');

        (new CronArchive())->main();

        $sumAfter = $this->readSumTimeSpent('day', $day);

        $this->assertSame(
            $sumBefore,
            $sumAfter,
            'Re-archiving an empty-log_page_view_time day must fall back to the legacy metric,'
            . ' not collapse historical sum_time_spent to zero.'
        );

        $rowCount = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_page_view_time')
            . ' WHERE idsite = ? AND server_time BETWEEN ? AND ?',
            [$this->idSite, $day . ' 00:00:00', $day . ' 23:59:59']
        );
        $this->assertSame(0, $rowCount, 'log_page_view_time must be empty for the pre-upgrade day');
    }

    public function testMidDayKillSwitchFlipKeepsMorningPageviewsInDayArchive(): void
    {
        $day = '2026-06-15';
        $morningUrl   = 'https://example.org/morning';
        $afternoonUrl = 'https://example.org/afternoon';

        $this->setAccurateFlag(false);
        $this->trackTimedPageviewPair($day . ' 08:00:00', $day . ' 08:00:30', $morningUrl, $morningUrl);

        $this->setAccurateFlag(true);
        $this->trackTimedPageviewPair($day . ' 14:00:00', $day . ' 14:00:45', $afternoonUrl, $afternoonUrl);

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);

        $this->assertNotEmpty($rows[$morningUrl] ?? null, 'Morning pageview URL must appear in day archive');
        $this->assertNotEmpty($rows[$afternoonUrl] ?? null, 'Afternoon pageview URL must appear in day archive');
        $this->assertGreaterThan(
            0,
            (int) $rows[$morningUrl]['sum_time_spent'],
            'Morning URL (tracked before the writer was enabled) must still contribute time via the legacy path'
        );
        $this->assertGreaterThan(
            0,
            (int) $rows[$afternoonUrl]['sum_time_spent'],
            'Afternoon URL (writer enabled) must contribute time via the accurate path'
        );
    }

    public function testTransitionWeekAverageStaysBoundedByPerRowContributions(): void
    {
        $weekStart = '2026-07-06';
        $url = 'https://example.org/transition';

        $legacyDays   = ['2026-07-06', '2026-07-07', '2026-07-08'];
        $accurateDays = ['2026-07-09', '2026-07-10'];

        $this->setAccurateFlag(false);
        foreach ($legacyDays as $day) {
            $this->trackTimedPageviewPair($day, $url);
        }

        $this->setAccurateFlag(true);
        foreach ($accurateDays as $day) {
            $this->trackTimedPageviewPair($day, $url);
        }

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('week', $weekStart);
        $this->assertNotEmpty($rows[$url] ?? null, 'Transition-week URL must appear in weekly archive');

        $row = $rows[$url];
        $sum = (int) $row['sum_time_spent'];
        $nbHits = (int) $row['nb_hits'];

        $this->assertGreaterThan(0, $sum);
        $this->assertGreaterThanOrEqual(count($legacyDays) + count($accurateDays), $nbHits, 'Each day contributes at least one pageview');

        $avg = $sum / $nbHits;
        $this->assertGreaterThanOrEqual(10, $avg, 'Avg per pageview stays plausible for a ~20s pageview gap');
        $this->assertLessThanOrEqual(60, $avg, 'Avg must not inflate when legacy/accurate days are mixed');
    }

    public function testRedirectPageviewInSameSecondDoesNotInflateFirstPageBackfill(): void
    {
        $day = '2026-06-20';
        $baseTime = $day . ' 12:00:00';

        $this->setAccurateFlag(true);

        $tracker = Fixture::getTracker($this->idSite, $baseTime, true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());

        $tracker->setUrl('https://example.org/page-a');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Page A'));

        $tracker->setForceVisitDateTime($baseTime);
        $tracker->setUrl('https://example.org/page-b');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackPageView('Page B'));

        $tracker->setForceVisitDateTime($day . ' 12:05:00');
        $tracker->setUrl('https://example.org/page-c');
        $tracker->setPageviewId('cccccc');
        Fixture::checkResponse($tracker->doTrackPageView('Page C'));

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);
        $pageA = $rows['https://example.org/page-a'] ?? null;
        $this->assertNotEmpty($pageA, 'Page A must appear in the archive');

        $sumA = (int) $pageA['sum_time_spent'];
        $this->assertLessThan(
            60,
            $sumA,
            'Page A (immediately-redirected pageview) must NOT be credited with the whole 5-minute visit duration —'
            . ' the visit_last_action_time backfill must only apply to the visit\'s TRUE last pageview'
        );
    }

    public function testSegmentedArchiveWithSegmentsCacheDisabledDoesNotInflateTimeSpent(): void
    {
        $day = '2026-06-25';

        $this->setAccurateFlag(true);

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());

        $tracker->setUrl('https://example.org/seg-a');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Seg A'));

        $tracker->setForceVisitDateTime($day . ' 12:00:30');
        $tracker->setUrl('https://example.org/seg-b');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackPageView('Seg B'));

        $tracker->setForceVisitDateTime($day . ' 12:01:30');
        $tracker->setUrl('https://example.org/seg-c');
        $tracker->setPageviewId('cccccc');
        Fixture::checkResponse($tracker->doTrackPageView('Seg C'));

        // Without the segment temp table (enable_segments_cache = 0) the segment pulls
        // log_link_visit_action directly into the accurate query; if that join is per-visit
        // instead of per-pageview, every time_spent row is multiplied by the number of
        // matching action rows in the visit.
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 0;
        Config::getInstance()->General['enable_segments_cache'] = 0;

        $rows = $this->readPageUrlRows('day', $day, 'pageUrl=@seg-');

        $this->assertSame(30, (int) ($rows['https://example.org/seg-a']['sum_time_spent'] ?? -1));
        $this->assertSame(60, (int) ($rows['https://example.org/seg-b']['sum_time_spent'] ?? -1));
    }

    public function testRepeatedUrlClosedByPvIdLessHitKeepsBothInstances(): void
    {
        $day = '2026-06-28';

        $this->setAccurateFlag(true);

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());

        $tracker->setUrl('https://example.org/repeat');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Repeat'));

        $tracker->setForceVisitDateTime($day . ' 12:00:10');
        $tracker->setUrl('https://example.org/other');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackPageView('Other'));

        $tracker->setForceVisitDateTime($day . ' 12:00:40');
        $tracker->setUrl('https://example.org/repeat');
        $tracker->setPageviewId('cccccc');
        Fixture::checkResponse($tracker->doTrackPageView('Repeat'));

        // A pv_id-less follow-up hit (older tracker, server-side SDK, log import) closes the
        // most recent row, which is the second /repeat pageview.
        $tracker->setForceVisitDateTime($day . ' 12:01:10');
        $tracker->setPageviewId('');
        Fixture::checkResponse($tracker->doTrackAction('https://example.org/file.zip', 'download'));

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);

        // Both /repeat instances carry their own accurate time (10s + 30s), so the shared
        // per-(visit, action) anti-join key does not lose the second one.
        $this->assertSame(40, (int) ($rows['https://example.org/repeat']['sum_time_spent'] ?? -1));
        $this->assertSame(30, (int) ($rows['https://example.org/other']['sum_time_spent'] ?? -1));
    }

    public function testPvIdLessHitAfterAPvIdCarryingHitKeepsBothIntervals(): void
    {
        $day = '2026-06-24';

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl('https://example.org/mixed');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Mixed'));

        // First download carries pv_id, second does not: the page must end up with the whole
        // 25s, not just the first 10s.
        $tracker->setForceVisitDateTime($day . ' 12:00:10');
        Fixture::checkResponse($tracker->doTrackAction('https://example.org/one.zip', 'download'));

        $tracker->setForceVisitDateTime($day . ' 12:00:25');
        $tracker->setPageviewId('');
        Fixture::checkResponse($tracker->doTrackAction('https://example.org/two.zip', 'download'));

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);
        $this->assertSame(25, (int) ($rows['https://example.org/mixed']['sum_time_spent'] ?? -1));
    }

    public function testPvIdLessHitDoesNotFreezeTheRowAgainstLaterHeartbeats(): void
    {
        $day = '2026-06-25';

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl('https://example.org/frozen');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Frozen'));

        $tracker->setForceVisitDateTime($day . ' 12:00:10');
        $tracker->setPageviewId('');
        Fixture::checkResponse($tracker->doTrackAction('https://example.org/x.zip', 'download'));

        // A later pv_id-carrying heartbeat must still grow the row past the pv_id-less bump.
        $tracker->setForceVisitDateTime($day . ' 12:00:40');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doPing());

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);
        $this->assertSame(40, (int) ($rows['https://example.org/frozen']['sum_time_spent'] ?? -1));
    }

    private function readSumTimeSpent(string $period, string $date): int
    {
        $rows = $this->readPageUrlRows($period, $date);
        $sum = 0;
        foreach ($rows as $row) {
            $sum += (int) $row['sum_time_spent'];
        }
        return $sum;
    }

    /**
     * Two tabs on the same visit, the second revisiting a page the first already left.
     *
     * The second view's row is never closed, so its interval reaches the report only through
     * the legacy metric. Matching the anti-join on the action alone would let the first view's
     * closed row suppress it, and the seconds would be counted by neither path.
     */
    public function testSecondViewOfAPageKeepsItsLegacyTimeWhileTheFirstViewIsAccurate(): void
    {
        $day = '2026-06-28';

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());

        // Tab 1 reads /repeat for 10s, then moves to /other.
        $tracker->setUrl('https://example.org/repeat');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Repeat'));

        $tracker->setForceVisitDateTime($day . ' 12:00:10');
        $tracker->setUrl('https://example.org/other');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackPageView('Other'));

        // Tab 2 opens /repeat again. This closes /other's row, not the first /repeat row.
        $tracker->setForceVisitDateTime($day . ' 12:00:20');
        $tracker->setUrl('https://example.org/repeat');
        $tracker->setPageviewId('cccccc');
        Fixture::checkResponse($tracker->doTrackPageView('Repeat'));

        // Tab 1 downloads a file, carrying tab 1's pv_id, so /other's row grows instead of the
        // second /repeat row. Legacy credits /repeat, because tab 2 moved the visit exit url.
        $tracker->setForceVisitDateTime($day . ' 12:00:30');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackAction('https://example.org/file.zip', 'download'));

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);

        // 10s measured for the first view, 10s from legacy for the second.
        $this->assertSame(20, (int) ($rows['https://example.org/repeat']['sum_time_spent'] ?? -1));
        $this->assertSame(20, (int) ($rows['https://example.org/other']['sum_time_spent'] ?? -1));
    }

    /**
     * A page held longer than visit_standard_length, so its measured span is capped.
     *
     * The hit that closed the row then sits beyond the capped span. Its legacy value still
     * covers the same interval, so counting it on top of the capped one would report the page
     * twice over.
     */
    public function testAPageHeldPastTheCapIsNotCountedTwice(): void
    {
        $day = '2026-06-29';

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl('https://example.org/long');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Long'));

        // An event moves visit_last_action_time, so the legacy value credited later is measured
        // from here rather than from the pageview.
        $tracker->setForceVisitDateTime($day . ' 12:00:10');
        Fixture::checkResponse($tracker->doTrackEvent('Video', 'play'));

        // Still the same visit: the window is measured from the last action, not the pageview.
        $tracker->setForceVisitDateTime($day . ' 12:30:05');
        $tracker->setUrl('https://example.org/next');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackPageView('Next'));

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);

        // Capped at visit_standard_length, not 1800 + the 1795s legacy value on top.
        $this->assertSame(1800, (int) ($rows['https://example.org/long']['sum_time_spent'] ?? -1));
    }

    /**
     * As above, but the last action before leaving comes after the capped span ends.
     *
     * The legacy value then starts where the capped span stops, so the two do not overlap,
     * yet both still describe the one view.
     */
    public function testAPageHeldPastTheCapIsNotCountedTwiceWhenTheLastActionFollowsTheCap(): void
    {
        $day = '2026-06-30';

        $tracker = Fixture::getTracker($this->idSite, $day . ' 12:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl('https://example.org/long');
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Long'));

        // Two events keep the visit alive past the cap; the second one lands after it.
        $tracker->setForceVisitDateTime($day . ' 12:20:00');
        Fixture::checkResponse($tracker->doTrackEvent('Video', 'play'));
        $tracker->setForceVisitDateTime($day . ' 12:45:00');
        Fixture::checkResponse($tracker->doTrackEvent('Video', 'pause'));

        $tracker->setForceVisitDateTime($day . ' 12:50:00');
        $tracker->setUrl('https://example.org/next');
        $tracker->setPageviewId('bbbbbb');
        Fixture::checkResponse($tracker->doTrackPageView('Next'));

        (new CronArchive())->main();

        $rows = $this->readPageUrlRows('day', $day);

        // Capped at visit_standard_length, not 1800 + the 300s legacy value on top.
        $this->assertSame(1800, (int) ($rows['https://example.org/long']['sum_time_spent'] ?? -1));
    }

    private function readPageUrlRows(string $period, string $date, $segment = false): array
    {
        $report = ActionsAPI::getInstance()->getPageUrls($this->idSite, $period, $date, $segment, false, false, -1, false, 'flat');
        $out = [];
        foreach ($report->getRows() as $row) {
            $label = $row->getMetadata('url') ?: $row->getColumn('label');
            if (!$label) {
                continue;
            }
            $out[$label] = [
                'sum_time_spent'          => (int) $row->getColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_SPENT),
                'nb_hits'                 => (int) $row->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS),
            ];
        }
        return $out;
    }

    private function setAccurateFlag(bool $on): void
    {
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['record_accurate_page_view_time'] = $on ? 1 : 0;
        $config->Tracker = $tracker;
        Cache::deleteTrackerCache();
    }

    private function trackTimedPageviewPair(string $firstAt, ?string $secondAtOrUrl = null, ?string $urlOrNull = null, ?string $secondUrl = null): void
    {
        if ($urlOrNull === null) {
            $day = $firstAt;
            $firstAt  = $day . ' 12:00:00';
            $secondAt = $day . ' 12:00:20';
            $url = $secondAtOrUrl;
            $urlSecond = $url;
        } else {
            $secondAt = $secondAtOrUrl;
            $url = $urlOrNull;
            $urlSecond = $secondUrl ?? $url;
        }

        $tracker = Fixture::getTracker($this->idSite, $firstAt, true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl($url);
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Stable'));

        $tracker->setForceVisitDateTime($secondAt);
        $tracker->setPageviewId('bbbbbb');
        $tracker->setUrl($urlSecond);
        Fixture::checkResponse($tracker->doTrackPageView('Stable'));
    }
}
