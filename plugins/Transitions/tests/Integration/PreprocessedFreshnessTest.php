<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\tests\Integration;

use Piwik\Archive;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\EventDispatcher;
use Piwik\Log\LoggerInterface;
use Piwik\Metrics;
use Piwik\Period\Day;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\ArchivingMode;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsLoader;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsReader;
use Piwik\Segment;
use Piwik\Tests\Fixtures\SomeVisitsManyPageviewsWithTransitions;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Refreshing the stored records of today and yesterday in a request, and building them without cron archiving.
 *
 * "Today" is faked as {@link TODAY}, the fixture's later day.
 *
 * @group Transitions
 * @group PreprocessedFreshnessTest
 * @group Plugins
 */
class PreprocessedFreshnessTest extends IntegrationTestCase
{
    /**
     * @var SomeVisitsManyPageviewsWithTransitions
     */
    public static $fixture;

    private const PAGE_URL = 'http://example.org/page/one.html';
    private const OTHER_PAGE_URL = 'http://example.org/sub/dir/page2.html';
    private const DAY = '2010-03-06';
    private const TODAY = '2010-03-14';
    private const SEGMENT = 'referrerType==direct';

    private int $visitors = 0;

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        // tracking in the past needs a token
        $fixture->createSuperUser = true;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::clearAccess(true);
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    /**
     * @dataProvider getArchivingModes
     */
    public function testOutdatedRecordsOfTodayAreRebuiltForTheRequestedPageOnly(bool $withBrowserArchiving): void
    {
        $this->setBrowserArchiving($withBrowserArchiving);
        $idPage = $this->selectPage(self::PAGE_URL);
        $otherIdPage = $this->selectPage(self::OTHER_PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $pageviews = $this->request('day', self::TODAY)['pageMetrics']['pageviews'];

        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);

        // within the refresh time
        $this->setNow(self::TODAY . ' 12:10:00');
        $report = $this->request('day', self::TODAY);
        self::assertSame($pageviews, $report['pageMetrics']['pageviews']);
        self::assertSame(self::TODAY . ' 12:00:00', $report['preprocessedAt']);
        self::assertSame(0, $this->countPartialArchives(self::TODAY));

        $this->setNow(self::TODAY . ' 12:20:00');
        $report = $this->request('day', self::TODAY);
        self::assertSame($pageviews + 2, $report['pageMetrics']['pageviews']);
        self::assertSame(1, $report['preprocessed']);
        self::assertSame(self::TODAY . ' 12:20:00', $report['preprocessedAt']);

        // one partial archive with the requested page's records only
        self::assertSame(1, $this->countPartialArchives(self::TODAY));
        $names = $this->getPartialArchiveRecordNames(self::TODAY);
        self::assertContains(Records::getRecordName($idPage, Records::PAGEVIEWS), $names);
        self::assertEmpty(preg_grep('/^Transitions_' . $otherIdPage . '_/', $names));

        // served from the rebuilt records from now on
        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
        self::assertSame(1, $this->countPartialArchives(self::TODAY));
    }

    /**
     * @return array<string, array{bool}>
     */
    public function getArchivingModes(): array
    {
        return ['with cron archiving' => [false], 'without cron archiving' => [true]];
    }

    public function testNothingIsRebuiltWhenTheRefreshIsTurnedOff(): void
    {
        Config::setSetting('Transitions', 'preprocessed_refresh_after', 0);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $report = $this->request('day', self::TODAY);

        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 18:00:00');

        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
        self::assertSame(0, $this->countPartialArchives(self::TODAY));
    }

    public function testTheRefreshTimeCanBeSetPerWebsite(): void
    {
        $service = StaticContainer::get(Service::class);
        self::assertSame(900, $service->getRefreshAfter(1));

        Config::setSetting('Transitions', 'preprocessed_refresh_after', '60');
        Config::setSetting('Transitions_1', 'preprocessed_refresh_after', 0);
        self::assertSame(0, $service->getRefreshAfter(1));
        self::assertSame(60, $service->getRefreshAfter(2));

        Config::setSetting('Transitions_1', 'preprocessed_refresh_after', '-1');
        $this->expectExceptionMessage('Invalid value for [Transitions_1] preprocessed_refresh_after: expected an integer >= 0.');
        $service->getRefreshAfter(1);
    }

    public function testYesterdayIsRebuiltUntilItIsArchivedAgainAfterItEnded(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 23:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $pageviews = $this->request('day', self::TODAY)['pageMetrics']['pageviews'];
        $this->trackPageviews(self::TODAY . ' 23:30:00', 1);

        $this->setNow('2010-03-15 01:00:00');
        $report = $this->request('day', self::TODAY);

        self::assertSame($pageviews + 1, $report['pageMetrics']['pageviews']);
        self::assertSame('2010-03-15 01:00:00', $report['preprocessedAt']);

        // the rebuilt records were built after the day ended, so they are kept
        $this->trackPageviews(self::TODAY . ' 23:40:00', 1);
        $this->setNow('2010-03-15 03:00:00');
        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
        self::assertSame(1, $this->countPartialArchives(self::TODAY));
    }

    public function testPeriodsArchivedAfterTheyEndedAreNeverRebuilt(): void
    {
        Config::setSetting('Transitions', 'preprocessed_refresh_after', 1);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'week', self::DAY);
        $day = $this->request('day', self::DAY);
        $week = $this->request('week', self::DAY);

        $this->trackPageviews(self::DAY . ' 18:00:00', 2);
        $this->setNow(self::TODAY . ' 18:00:00');

        self::assertSame($this->toArray($day), $this->toArray($this->request('day', self::DAY)));
        self::assertSame($this->toArray($week), $this->toArray($this->request('week', self::DAY)));
        self::assertSame(0, $this->countPartialArchives(self::DAY));
    }

    public function testAWeekWithTodayIsSummedFromItsDaysWithTodayRebuilt(): void
    {
        // visits on every day of the week, so each day has an archive
        foreach (['2010-03-08', '2010-03-09', '2010-03-10', '2010-03-11', '2010-03-12', '2010-03-13'] as $day) {
            $this->trackPageviews($day . ' 10:00:00', 1, self::OTHER_PAGE_URL);
        }
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'week', self::TODAY);
        $this->archive($idPage, 'month', self::TODAY);
        $week = $this->request('week', self::TODAY);
        $month = $this->request('month', self::TODAY);

        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        $refreshedWeek = $this->request('week', self::TODAY);
        self::assertSame($week['pageMetrics']['pageviews'] + 2, $refreshedWeek['pageMetrics']['pageviews']);
        // the days before today were archived at noon
        self::assertSame(self::TODAY . ' 12:00:00', $refreshedWeek['preprocessedAt']);

        self::assertSame($month['pageMetrics']['pageviews'] + 2, $this->request('month', self::TODAY)['pageMetrics']['pageviews']);
        self::assertSame(1, $this->countPartialArchives(self::TODAY));
        self::assertSame(0, $this->countPartialArchives('2010-03-08', 2));
    }

    public function testARequestServesTheStoredRecordsWhileAnotherOneBuildsThem(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $report = $this->request('day', self::TODAY);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        $this->setLoader(200);
        $lock = new Lock(StaticContainer::get(LockBackend::class), 'Transitions.buildPreprocessedDay.');
        self::assertTrue($lock->acquireLock('1.' . $idPage . '.' . self::TODAY . '.'));

        try {
            self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
            self::assertSame(0, $this->countPartialArchives(self::TODAY));
        } finally {
            $lock->unlock();
        }

        self::assertSame($report['pageMetrics']['pageviews'] + 2, $this->request('day', self::TODAY)['pageMetrics']['pageviews']);
    }

    public function testARemovedPageIsNotRebuilt(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $report = $this->request('day', self::TODAY);

        $service = StaticContainer::get(Service::class);
        foreach ($service->getPages(1) as $page) {
            $service->removePage(1, $page['idpage']);
        }
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 18:00:00');

        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
        self::assertSame(0, $this->countPartialArchives(self::TODAY));
    }

    public function testAPartialArchiveIsDeletedOnceTheDayIsArchivedAgain(): void
    {
        $this->setBrowserArchiving(false);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');
        $refreshed = $this->request('day', self::TODAY);
        self::assertSame(1, $this->countPartialArchives(self::TODAY));

        // as core:archive archives today again, which replaces the older archives
        $this->setNow(self::TODAY . ' 12:40:00');
        $this->archive($idPage, 'day', self::TODAY);

        self::assertSame(0, $this->countPartialArchives(self::TODAY));
        self::assertSame(0, (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_blob_2010_03') . ' WHERE idsite = 1 AND period = 1 AND date1 = ? AND ts_archived = ?',
            [self::TODAY, self::TODAY . ' 12:20:00']
        ));
        $report = $this->request('day', self::TODAY);
        self::assertSame($refreshed['pageMetrics'], $report['pageMetrics']);
        self::assertSame(self::TODAY . ' 12:40:00', $report['preprocessedAt']);
    }

    public function testWithoutCronASingleRequestedDayIsBuilt(): void
    {
        $raw = $this->request('day', self::DAY);
        $idPage = $this->selectPage(self::PAGE_URL);

        $built = $this->request('day', self::DAY);

        self::assertSame(1, $built['preprocessed']);
        unset($built['preprocessed'], $built['preprocessedAt']);
        self::assertSame($this->toArray($raw), $this->toArray($built));
        self::assertSame(1, $this->countPartialArchives(self::DAY));
        self::assertSame(1.0, $this->getStoredNumeric($idPage, Records::DAYS, self::DAY));

        // read from then on
        $this->request('day', self::DAY);
        self::assertSame(1, $this->countPartialArchives(self::DAY));
    }

    public function testWithCronArchivingNoDayIsBuiltAlsoWithBrowserArchivingEnabled(): void
    {
        $raw = $this->request('day', self::DAY);
        $this->selectPage(self::PAGE_URL);
        ArchivingMode::rememberCronArchiving(1);

        self::assertSame($this->toArray($raw), $this->toArray($this->request('day', self::DAY)));
        self::assertSame(0, $this->countPartialArchives(self::DAY));
    }

    public function testWithCronNoDayIsBuilt(): void
    {
        $this->setBrowserArchiving(false);
        $raw = $this->request('day', self::DAY);
        $this->selectPage(self::PAGE_URL);

        self::assertSame($this->toArray($raw), $this->toArray($this->request('day', self::DAY)));
        self::assertSame(0, $this->countPartialArchives(self::DAY));
    }

    public function testWithoutCronAWeekIsSummedFromItsStoredDays(): void
    {
        // visits on each day of the week, so archiving doesn't skip any
        $days = ['2010-03-08', '2010-03-09', '2010-03-10', '2010-03-11', '2010-03-12', '2010-03-13', self::TODAY];
        foreach ($days as $day) {
            $this->trackPageviews($day . ' 10:00:00', 1, self::OTHER_PAGE_URL);
        }
        $raw = $this->request('week', self::TODAY);
        $this->selectPage(self::PAGE_URL);

        // one day not built yet
        foreach (array_slice($days, 0, 6) as $day) {
            $this->buildDay($day);
        }
        $archiveRows = $this->countArchiveRows();
        self::assertSame($this->toArray($raw), $this->toArray($this->request('week', self::TODAY)));
        self::assertSame($archiveRows, $this->countArchiveRows());

        $this->buildDay(self::TODAY);
        $week = $this->request('week', self::TODAY);
        self::assertSame(1, $week['preprocessed']);
        unset($week['preprocessed'], $week['preprocessedAt']);
        self::assertSame($this->toArray($raw), $this->toArray($week));
    }

    public function testAWeekWithTodayAndDaysWithoutVisitsIsSummedFromItsDays(): void
    {
        // the days before today have no visits, so archiving skips them
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'week', self::TODAY);
        $week = $this->request('week', self::TODAY);

        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        foreach (['week' => self::TODAY, 'range' => '2010-03-08,' . self::TODAY] as $period => $date) {
            $report = $this->request($period, $date);
            self::assertSame($week['pageMetrics']['pageviews'] + 2, $report['pageMetrics']['pageviews'], $period);
            self::assertSame(self::TODAY . ' 12:20:00', $report['preprocessedAt'], $period);
        }
        self::assertSame(1, $this->countPartialArchives(self::TODAY));
    }

    public function testThePreProcessedSegmentOfTodayIsRebuilt(): void
    {
        $this->addPreProcessedSegment();
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->trackPageviews(self::TODAY . ' 09:00:00', 1);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY, self::SEGMENT);
        $report = $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT);

        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        $refreshed = $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT);
        self::assertSame($report['pageMetrics']['pageviews'] + 2, $refreshed['pageMetrics']['pageviews']);
        self::assertSame(self::TODAY . ' 12:20:00', $refreshed['preprocessedAt']);
    }

    public function testNoSegmentIsBuiltWhenSegmentsArentArchivedForTransitions(): void
    {
        $this->addPreProcessedSegment();
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->trackPageviews(self::TODAY . ' 09:00:00', 1);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY, self::SEGMENT);
        $report = $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT);

        Config::getInstance()->General['disable_archiving_segment_for_plugins'] = 'Transitions';
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT)));
        self::assertSame(0, $this->countPartialArchives(self::TODAY));
    }

    public function testAFailedBuildIsNotRetriedRightAway(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $report = $this->request('day', self::TODAY);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        $fail = true;
        $builds = 0;
        EventDispatcher::getInstance()->addObserver('ArchiveProcessor.shouldAggregateFromRawData', function () use (&$fail, &$builds) {
            $builds++;
            if ($fail) {
                throw new \Exception('archiving failed');
            }
        });

        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
        $fail = false;
        self::assertSame($this->toArray($report), $this->toArray($this->request('day', self::TODAY)));
        self::assertSame(1, $builds);
        self::assertTrue(Cache::getLazyCache()->contains($this->getAttemptCacheId($idPage, self::TODAY)));

        // once the failure is forgotten
        Cache::getLazyCache()->delete($this->getAttemptCacheId($idPage, self::TODAY));
        self::assertSame($report['pageMetrics']['pageviews'] + 2, $this->request('day', self::TODAY)['pageMetrics']['pageviews']);
        self::assertFalse(Cache::getLazyCache()->contains($this->getAttemptCacheId($idPage, self::TODAY)));
    }

    public function testWithoutCronADayWithoutVisitsIsBuiltOnce(): void
    {
        $this->addPreProcessedSegment();
        $this->selectPage(self::PAGE_URL);

        // the website, and so the segment, has no visits that day
        foreach ([['2010-03-09', ''], ['2010-03-09', self::SEGMENT]] as [$date, $segment]) {
            for ($i = 0; $i < 2; $i++) {
                try {
                    $this->request('day', $date, self::PAGE_URL, $segment);
                    self::fail('NoDataForAction expected');
                } catch (\Exception $e) {
                    self::assertSame('NoDataForAction', $e->getMessage());
                }
            }
        }

        // one per segment, not one per request
        self::assertSame(2, $this->countPartialArchives('2010-03-09'));
    }

    public function testWithoutCronTooManyPeriodsAreAnsweredFromTheLogs(): void
    {
        $days = ['2010-03-08', '2010-03-09', '2010-03-10', '2010-03-11', '2010-03-12', '2010-03-13', self::TODAY];
        foreach ($days as $day) {
            $this->trackPageviews($day . ' 10:00:00', 1, self::OTHER_PAGE_URL);
        }
        $raw = $this->request('week', self::TODAY);
        $this->selectPage(self::PAGE_URL);
        foreach ($days as $day) {
            $this->buildDay($day);
        }
        self::assertSame(1, $this->request('week', self::TODAY)['preprocessed']);

        // at most 6 periods are summed
        $this->setLoader(200, 6);
        self::assertSame($this->toArray($raw), $this->toArray($this->request('week', self::TODAY)));
    }

    public function testWithoutCronTheSumOfManyDaysIsTruncatedToTheStoredRows(): void
    {
        // a visit from the page to another page each day, the later days to the lower labels
        $days = [];
        for ($index = 0; $index < 7; $index++) {
            $days[] = Date::factory('2010-03-08')->addDay($index)->toString();
            $tracker = Fixture::getTracker(1, $days[$index] . ' 10:00:00', $defaultInit = true);
            $tracker->setIp('10.2.0.' . $index);
            $tracker->setNewVisitorId();
            $tracker->setUrl(self::OTHER_PAGE_URL);
            Fixture::checkResponse($tracker->doTrackPageView('other'));
            $tracker->setForceVisitDateTime($days[$index] . ' 10:01:00');
            $tracker->setUrl('http://example.org/day/' . (6 - $index) . '.html');
            Fixture::checkResponse($tracker->doTrackPageView('day ' . $index));
        }
        $idPage = $this->selectPage(self::OTHER_PAGE_URL);
        foreach ($days as $day) {
            $this->buildDay($day, self::OTHER_PAGE_URL);
        }

        Config::getInstance()->General['datatable_archiving_maximum_rows_transitions'] = 2;
        $periods = array_map(fn(string $day) => new Day(Date::factory($day)), $days);
        $records = StaticContainer::get(StoredRecordsReader::class)->readSum(1, $periods, new Segment('', [1]), $idPage, [Records::FOLLOWING_PAGES]);

        self::assertNotNull($records);
        $followingPages = $records->getTable(Records::FOLLOWING_PAGES);
        self::assertSame(2, $followingPages->getRowsCountWithoutSummaryRow());
        self::assertSame(5, (int) $followingPages->getSummaryRow()->getColumn(Metrics::INDEX_NB_ACTIONS));
        // of equal counts, the lowest labels are kept, whatever order they were summed in
        $labels = array_map(fn($row) => $row->getColumn('label'), array_values($followingPages->getRowsWithoutSummaryRow()));
        self::assertSame(['example.org/day/0.html', 'example.org/day/1.html'], $labels);
    }

    public function testWithoutCronDaysAreNotSummedBeyondThePeriodLimit(): void
    {
        $days = ['2010-03-08', '2010-03-09', '2010-03-10', '2010-03-11', '2010-03-12', '2010-03-13', self::TODAY];
        foreach ($days as $day) {
            $this->trackPageviews($day . ' 10:00:00', 1, self::OTHER_PAGE_URL);
        }
        $this->selectPage(self::PAGE_URL);
        foreach ($days as $day) {
            $this->buildDay($day);
        }
        self::assertSame(1, $this->request('week', self::TODAY)['preprocessed']);

        Config::setSetting('Transitions', 'max_period_allowed', 'day');

        $this->expectExceptionMessage('PeriodNotAllowed');
        $this->request('week', self::TODAY);
    }

    /**
     * Requests a day as the report does without cron archiving, which builds the page's records.
     */
    private function buildDay(string $date, string $pageUrl = self::PAGE_URL): void
    {
        try {
            $this->request('day', $date, $pageUrl);
        } catch (\Exception $e) {
            // the page had no pageviews that day
            self::assertSame('NoDataForAction', $e->getMessage());
        }
    }

    private function setBrowserArchiving(bool $enabled): void
    {
        Config::getInstance()->General['enable_browser_archiving_triggering'] = (int) $enabled;
        Config::getInstance()->General['browser_archiving_disabled_enforce'] = (int) !$enabled;
    }

    private function setNow(string $dateTime): void
    {
        Date::$now = strtotime($dateTime . ' UTC');
    }

    private function trackPageviews(string $dateTime, int $count, string $url = self::PAGE_URL): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->visitors++;
            $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
            $tracker->setIp('10.1.0.' . $this->visitors);
            $tracker->setNewVisitorId();
            $tracker->setUrl($url);
            Fixture::checkResponse($tracker->doTrackPageView('title'));
        }
    }

    private function selectPage(string $url): int
    {
        return API::getInstance()->addPreprocessedPage(1, $url)['idpage'];
    }

    /**
     * Archives the period as browser archiving does, with the page's records and the visits.
     */
    private function archive(int $idPage, string $period, string $date, string $segment = ''): void
    {
        $general = Config::getInstance()->General;
        $this->setBrowserArchiving(true);
        Cache::getTransientCache()->flushAll();

        try {
            Archive::build(1, $period, $date, $segment)->getNumeric(['nb_visits', Records::getRebuildReportName($idPage)]);
        } finally {
            Config::getInstance()->General = $general;
            Cache::getTransientCache()->flushAll();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function request(string $period, string $date, string $pageUrl = self::PAGE_URL, string $segment = '', int $limit = 0): array
    {
        Cache::getTransientCache()->flushAll();

        return API::getInstance()->getTransitionsForAction($pageUrl, 'url', 1, $period, $date, $segment, $limit);
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private function toArray(array $report): array
    {
        foreach ($report as $key => $value) {
            if ($value instanceof \Piwik\DataTable) {
                $rows = [];
                foreach ($value->getRows() as $row) {
                    $rows[] = [$row->getColumn('label'), $row->getColumn('referrals')];
                }
                $report[$key] = $rows;
            }
        }

        return $report;
    }

    private function countPartialArchives(string $date1, int $periodId = 1): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_numeric_2010_03')
            . " WHERE idsite = 1 AND period = ? AND date1 = ? AND name LIKE 'done%.Transitions' AND value = ?",
            [$periodId, $date1, ArchiveWriter::DONE_PARTIAL]
        );
    }

    private function setLoader(int $maxLockWaitMilliseconds, int $maxSummedPeriods = StoredRecordsLoader::MAX_SUMMED_PERIODS): void
    {
        StaticContainer::getContainer()->set(StoredRecordsLoader::class, new StoredRecordsLoader(
            StaticContainer::get(StoredRecordsReader::class),
            StaticContainer::get(Service::class),
            StaticContainer::get(LockBackend::class),
            StaticContainer::get(LoggerInterface::class),
            $maxLockWaitMilliseconds,
            $maxSummedPeriods
        ));
    }

    private function addPreProcessedSegment(): void
    {
        // a segment can only be pre-processed with cron archiving
        $general = Config::getInstance()->General;
        $this->setBrowserArchiving(false);
        try {
            SegmentEditorAPI::getInstance()->add('direct', self::SEGMENT, 1, true);
        } finally {
            Config::getInstance()->General = $general;
        }
        Cache::getTransientCache()->flushAll();
        Cache::getEagerCache()->flushAll();
    }

    private function getAttemptCacheId(int $idPage, string $date, string $segment = ''): string
    {
        $hash = $segment === '' ? '' : (new Segment($segment, [1]))->getHash();

        return 'Transitions_buildPreprocessedDay_' . md5('1.' . $idPage . '.' . $date . '.' . $hash);
    }

    /**
     * @return list<string>
     */
    private function getPartialArchiveRecordNames(string $date1): array
    {
        $names = [];
        foreach (['archive_numeric_2010_03', 'archive_blob_2010_03'] as $table) {
            $rows = Db::fetchAll(
                'SELECT DISTINCT d.name FROM ' . Common::prefixTable($table) . ' d JOIN ' . Common::prefixTable('archive_numeric_2010_03') . ' f'
                . " ON f.idarchive = d.idarchive AND f.name LIKE 'done%.Transitions' AND f.value = ?"
                . ' WHERE d.idsite = 1 AND d.period = 1 AND d.date1 = ?',
                [ArchiveWriter::DONE_PARTIAL, $date1]
            );
            foreach ($rows as $row) {
                $names[] = (string) $row['name'];
            }
        }

        return $names;
    }

    private function getStoredNumeric(int $idPage, string $part, string $date1): ?float
    {
        $value = Db::fetchOne(
            'SELECT value FROM ' . Common::prefixTable('archive_numeric_2010_03') . ' WHERE idsite = 1 AND period = 1 AND date1 = ? AND name = ?'
            . ' ORDER BY ts_archived DESC',
            [$date1, Records::getRecordName($idPage, $part)]
        );

        return $value === false ? null : (float) $value;
    }

    private function countArchiveRows(): int
    {
        $count = 0;
        foreach (Db::fetchAll("SHOW TABLES LIKE '" . Common::prefixTable('archive_') . "%'") as $row) {
            $count += (int) Db::fetchOne('SELECT COUNT(*) FROM ' . reset($row));
        }

        return $count;
    }
}

PreprocessedFreshnessTest::$fixture = new SomeVisitsManyPageviewsWithTransitions();
