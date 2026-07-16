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
        $hitsWithTime = (int) $row['nb_hits_with_time_spent'];

        $this->assertGreaterThan(0, $sum);
        $this->assertGreaterThanOrEqual(count($legacyDays) + count($accurateDays), $hitsWithTime, 'Each day contributes at least one time-carrying observation');

        $avg = $sum / $hitsWithTime;
        $this->assertGreaterThanOrEqual(10, $avg, 'Avg per contribution stays plausible for a ~20s pageview gap');
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

    private function readSumTimeSpent(string $period, string $date): int
    {
        $rows = $this->readPageUrlRows($period, $date);
        $sum = 0;
        foreach ($rows as $row) {
            $sum += (int) $row['sum_time_spent'];
        }
        return $sum;
    }

    private function readPageUrlRows(string $period, string $date): array
    {
        $report = ActionsAPI::getInstance()->getPageUrls($this->idSite, $period, $date, false, false, false, -1, false, 'flat');
        $out = [];
        foreach ($report->getRows() as $row) {
            $label = $row->getMetadata('url') ?: $row->getColumn('label');
            if (!$label) {
                continue;
            }
            $out[$label] = [
                'sum_time_spent'          => (int) $row->getColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_SPENT),
                'nb_hits_with_time_spent' => (int) $row->getColumn(PiwikMetrics::INDEX_PAGE_NB_HITS_WITH_TIME_SPENT),
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
