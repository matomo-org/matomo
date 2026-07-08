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
 * Integration tests for the mid-upgrade transition and invalidation paths of the accurate
 * Time-on-Page archiver.
 *
 * Two adversarial scenarios that the standard writer tests don't cover:
 *  - Weekly archive that spans days tracked before and after the kill-switch flip.
 *  - Invalidation and re-archiving of a day tracked before the kill-switch flip.
 *
 * In both cases the invariant is: sum_time_spent MUST come from `log_link_visit_action.
 * time_spent_ref_action` on days where `log_page_view_time` has no rows for the site,
 * so historical numbers stay stable when the accurate path is enabled retroactively.
 *
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

        // Deterministic: block browser-triggered archiving so we control when archives are built.
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;

        Cache::deleteTrackerCache();
    }

    public function tearDown(): void
    {
        $this->setAccurateFlag(true);

        parent::tearDown();
    }

    /**
     * Mid-week upgrade: legacy write path for the first 4 days of the ISO week, accurate write
     * path for the last 3. The archiver dispatch is per-day, so:
     *   - Day 1-4 daily archives must use the legacy `time_spent_ref_action` query (no
     *     log_page_view_time rows).
     *   - Day 5-7 daily archives must use the accurate query.
     *   - The weekly archive is a sum of the seven daily archives.
     *
     * The assertion the customer cares about: weekly sum_time_spent is strictly greater than
     * either half alone, i.e. both halves contribute and neither is silently dropped when the
     * flag is flipped mid-week.
     */
    public function testWeeklyArchiveThatSpansTheKillSwitchFlipSumsBothLegacyAndAccurateDays(): void
    {
        // ISO week starting Monday 2026-06-01. Days 1-4 tracked legacy, 5-7 tracked accurate.
        $weekStart = '2026-06-01';
        $legacyDays   = ['2026-06-01', '2026-06-02', '2026-06-03', '2026-06-04'];
        $accurateDays = ['2026-06-05', '2026-06-06', '2026-06-07'];

        // Days 1-4: kill switch off → writer skips log_page_view_time.
        $this->setAccurateFlag(false);
        foreach ($legacyDays as $day) {
            $this->trackTimedPageviewPair($day, 'https://example.org/landing');
        }

        // Days 5-7: kill switch on → writer populates log_page_view_time.
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
        // Each of the 7 tracked days contributes a ~20s gap between its two pageviews, so we
        // expect around 7 * 20 = 140s. We accept a wide band (>= 100s) to leave headroom for
        // second-boundary rounding in the test-scaffold clock.
        $this->assertGreaterThanOrEqual(
            100,
            $weeklySumTimeSpent,
            'Weekly sum_time_spent should reflect ALL 7 days, not only the 3 accurate-path days'
        );
    }

    /**
     * Invalidation of a pre-upgrade day: the day was originally archived with the flag off (so
     * its numbers came from `time_spent_ref_action`). After the flag flips to on and the day is
     * invalidated, re-archiving MUST NOT silently drop the numbers to zero just because
     * log_page_view_time has no rows for that day.
     */
    public function testInvalidatingAPreUpgradeDayReArchivesUsingLegacySumTimeSpent(): void
    {
        $day = '2026-06-10';

        // Track with the accurate writer off → only log_link_visit_action gets time_spent_ref_action.
        $this->setAccurateFlag(false);
        $this->trackTimedPageviewPair($day, 'https://example.org/stable');

        (new CronArchive())->main();

        $sumBefore = $this->readSumTimeSpent('day', $day);
        $this->assertGreaterThan(0, $sumBefore, 'Legacy tracked day must produce non-zero sum_time_spent');

        // Flip the flag on and invalidate the historical day. `log_page_view_time` is still empty
        // for the day because it wasn't populated during tracking.
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

        // Cross-check that log_page_view_time is genuinely empty for the day — protects against
        // a false pass if a future change starts populating it retroactively.
        $rowCount = (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('log_page_view_time')
            . ' WHERE idsite = ? AND server_time BETWEEN ? AND ?',
            [$this->idSite, $day . ' 00:00:00', $day . ' 23:59:59']
        );
        $this->assertSame(0, $rowCount, 'log_page_view_time must be empty for the pre-upgrade day');
    }

    /**
     * Reads the total `sum_time_spent` (INDEX_PAGE_SUM_TIME_SPENT = 13) across every row of the
     * `Actions.getPageUrls` report for the given period. Archive records surface metrics keyed
     * by integer index, not by the human string name — string keys like `sum_time_spent` only
     * exist after the ProcessedMetrics layer wraps the row, which the API does not do here.
     */
    private function readSumTimeSpent(string $period, string $date): int
    {
        $report = ActionsAPI::getInstance()->getPageUrls($this->idSite, $period, $date);
        $sum = 0;
        foreach ($report->getRows() as $row) {
            $sum += (int) $row->getColumn(PiwikMetrics::INDEX_PAGE_SUM_TIME_SPENT);
        }
        return $sum;
    }

    private function setAccurateFlag(bool $on): void
    {
        $config = Config::getInstance();
        $tracker = $config->Tracker;
        $tracker['record_accurate_page_view_time'] = $on ? 1 : 0;
        $config->Tracker = $tracker;
        Cache::deleteTrackerCache();
    }

    /**
     * Fires two pageviews 20 seconds apart against the same URL in the same visit. This is the
     * minimal shape that makes both the accurate writer (closePreviousPageView UPDATE) and the
     * legacy path (time_spent_ref_action of the second row) produce a non-zero sum_time_spent
     * for the URL — so the two paths are directly comparable.
     */
    private function trackTimedPageviewPair(string $day, string $url): void
    {
        $firstAt  = $day . ' 12:00:00';
        $secondAt = $day . ' 12:00:20';

        $tracker = Fixture::getTracker($this->idSite, $firstAt, true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl($url);
        $tracker->setPageviewId('aaaaaa');
        Fixture::checkResponse($tracker->doTrackPageView('Stable'));

        $tracker->setForceVisitDateTime($secondAt);
        $tracker->setPageviewId('bbbbbb');
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('Stable'));
    }
}
