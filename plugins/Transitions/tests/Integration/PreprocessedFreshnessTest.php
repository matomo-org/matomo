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
use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Loader as ArchiveLoader;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Db;
use Piwik\EventDispatcher;
use Piwik\Log\LoggerInterface;
use Piwik\Metrics;
use Piwik\Option;
use Piwik\Period\Day;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Period\Month;
use Piwik\Period\Week;
use Piwik\Plugins\Live\Exception\MaxExecutionTimeExceededException;
use Piwik\Plugins\PrivacyManager\API as PrivacyManagerAPI;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\Model;
use Piwik\Plugins\Transitions\PreprocessedPages\ArchivingMode;
use Piwik\Plugins\Transitions\PreprocessedPages\PartialArchives;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsLoader;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsReader;
use Piwik\Segment;
use Piwik\Site;
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

    /**
     * Partial archives older than an archive for all plugins are kept, like other plugins' ones, but no longer read.
     */
    public function testAPartialArchiveIsNotServedOnceTheDayIsArchivedAgain(): void
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

        self::assertSame(1, $this->countPartialArchives(self::TODAY));
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

    public function testRecordsOfAnInvalidatedArchiveAreNotServed(): void
    {
        $this->setBrowserArchiving(false);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);
        $day = $this->request('day', self::DAY);
        $month = $this->request('month', self::DAY);
        self::assertSame(1, $day['preprocessed']);
        self::assertSame(1, $month['preprocessed']);

        // the stored values of the day and the month no longer match the logs, which only an invalidation tells
        Db::query(
            'UPDATE ' . Common::prefixTable('archive_numeric_2010_03') . ' SET value = value + 1000 WHERE idsite = 1 AND period IN (1, 3) AND date1 IN (?, ?) AND name = ?',
            [self::DAY, '2010-03-01', Records::getRecordName($idPage, Records::PAGEVIEWS)]
        );
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], [Date::factory(self::DAY)], 'day');

        // a past day isn't built in a request with cron archiving, so the logs answer it
        $raw = $this->request('day', self::DAY);
        self::assertArrayNotHasKey('preprocessed', $raw);
        unset($day['preprocessed'], $day['preprocessedAt']);
        self::assertSame($this->toArray($day), $this->toArray($raw));

        // the month isn't used either, and the day is one of its subperiods
        self::assertArrayNotHasKey('preprocessed', $this->request('month', self::DAY));

        // once the day is archived again, the month is summed from its subperiods
        $this->archive($idPage, 'day', self::DAY);
        $summed = $this->request('month', self::DAY);
        self::assertSame(1, $summed['preprocessed']);
        self::assertSame($month['pageMetrics'], $summed['pageMetrics']);
    }

    public function testTodayInvalidatedByCronArchivingIsServedWithItsLongerPeriods(): void
    {
        $this->setBrowserArchiving(false);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'year', self::TODAY);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');
        $this->request('day', self::TODAY);
        self::assertSame(1, $this->countPartialArchives(self::TODAY));

        // core:archive invalidates today, and its week, month and year, before it archives them again
        $this->setNow(self::TODAY . ' 12:25:00');
        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], [Date::factory(self::TODAY)], 'day');
        self::assertSame(1, $this->countPartialArchives(self::TODAY));

        // meanwhile, the partial archive of today is served, which core doesn't invalidate, also within its longer
        // periods beyond the period limit
        Config::setSetting('Transitions', 'max_period_allowed', 'day');
        $this->setNow(self::TODAY . ' 12:30:00');
        $day = $this->request('day', self::TODAY);
        self::assertSame(1, $day['preprocessed']);
        self::assertSame(self::TODAY . ' 12:20:00', $day['preprocessedAt']);
        self::assertSame(1, $this->countPartialArchives(self::TODAY));

        $this->setNow(self::TODAY . ' 12:35:00');
        foreach (['day', 'week', 'month', 'year'] as $period) {
            self::assertSame(1, $this->request($period, self::TODAY)['preprocessed'], $period);
        }
        self::assertSame($this->toArray($day), $this->toArray($this->request('day', self::TODAY)));
        self::assertSame(1, $this->countPartialArchives(self::TODAY));
    }

    /**
     * Core doesn't invalidate partial archives, so the day's records built within a request are served until the day
     * is archived again.
     */
    public function testWithoutCronAnInvalidatedDayIsServedFromItsPartialArchiveUntilItIsArchivedAgain(): void
    {
        // the day's archive for all plugins, built before the page was selected
        $this->archiveAllPlugins(self::DAY);
        $this->selectPage(self::PAGE_URL);
        $this->setNow('2010-03-20 09:00:00');
        $built = $this->request('day', self::DAY);
        self::assertSame(1, $built['preprocessed']);
        self::assertSame(1, $this->countPartialArchives(self::DAY));

        StaticContainer::get(ArchiveInvalidator::class)->markArchivesAsInvalidated([1], [Date::factory(self::DAY)], 'day');

        $this->setNow('2010-03-20 10:00:00');
        self::assertSame($this->toArray($built), $this->toArray($this->request('day', self::DAY)));

        $this->archiveAllPlugins(self::DAY);
        $this->setNow('2010-03-20 11:00:00');
        $archivedAgain = $this->request('day', self::DAY);
        self::assertSame(1, $archivedAgain['preprocessed']);
        self::assertSame('2010-03-20 10:00:00', $archivedAgain['preprocessedAt']);
        unset($built['preprocessedAt'], $archivedAgain['preprocessedAt']);
        self::assertSame($this->toArray($built), $this->toArray($archivedAgain));
    }

    /**
     * Core doesn't invalidate partial archives: a data subject's deleted visits are served from the page's partial
     * archive until the day is archived again, and are gone afterwards.
     */
    public function testErasedVisitsAreServedFromAPartialArchiveUntilTheDayIsArchivedAgain(): void
    {
        $this->trackPageviews(self::DAY . ' 09:00:00', 2);
        $this->archiveAllPlugins(self::DAY);
        $this->selectPage(self::PAGE_URL);
        $built = $this->request('day', self::DAY);
        self::assertSame(1, $built['preprocessed']);

        // until core:archive applies the erasure, the archives of the day are served, as for every report
        $this->setBrowserArchiving(false);
        $this->deleteVisitOfPage(self::DAY);
        self::assertSame(1, $this->countPartialArchives(self::DAY));
        self::assertSame($this->toArray($built), $this->toArray($this->request('day', self::DAY)));

        // also once core:archive applied the erasure, which it skips for an archive just built
        Date::$now = time() + 3600;
        $this->applyRememberedInvalidationsAsCronArchiving();
        self::assertSame(1, $this->countPartialArchives(self::DAY));
        self::assertSame($this->toArray($built), $this->toArray($this->request('day', self::DAY)));

        // once the day is archived again, the newer archive replaces the partial archive
        $this->archiveAllPlugins(self::DAY);
        $this->setBrowserArchiving(false);
        $report = $this->request('day', self::DAY);
        self::assertSame(1, $report['preprocessed']);
        self::assertLessThan($built['pageMetrics']['pageviews'], $report['pageMetrics']['pageviews']);
        self::assertSame($this->getRawPageMetrics(self::DAY), $this->toArray($report)['pageMetrics']);
    }

    /**
     * @dataProvider getArchivingModes
     */
    public function testAnErasureCoreDropsAsTheLogsOfTheDayWereDeletedMeanwhileKeepsThePartialArchive(bool $withBrowserArchiving): void
    {
        $this->trackPageviews(self::DAY . ' 09:00:00', 2);
        $this->selectPage(self::PAGE_URL);
        $built = $this->request('day', self::DAY);
        self::assertSame(1, $built['preprocessed']);

        $this->deleteVisitOfPage(self::DAY);
        // the logs of the day are deleted before the erasure is applied, which core then skips for its archives too
        $this->setNow('2010-03-20 00:30:00');
        PrivacyManager::savePurgeDataSettings(['delete_logs_enable' => 1, 'delete_logs_older_than' => 10]);
        $this->setBrowserArchiving($withBrowserArchiving);
        if (!$withBrowserArchiving) {
            $this->applyRememberedInvalidationsAsCronArchiving();
        }

        self::assertSame($this->toArray($built), $this->toArray($this->request('day', self::DAY)));
        self::assertSame(1, $this->countPartialArchives(self::DAY));
    }

    public function testWithoutCronARequestApplyingAnErasureKeepsServingTheRecordsOfOtherPages(): void
    {
        $this->trackPageviews(self::DAY . ' 09:00:00', 2);
        // archived before the pages were selected, so their records are in partial archives only
        $this->archiveAllPlugins(self::DAY);
        $this->selectPage(self::PAGE_URL);
        $otherIdPage = $this->selectPage(self::OTHER_PAGE_URL);
        self::assertSame(1, $this->request('day', self::DAY, self::OTHER_PAGE_URL)['preprocessed']);

        // a request for the first page applies the erasure
        $this->deleteVisitOfPage(self::DAY);
        self::assertSame(1, $this->request('day', self::DAY)['preprocessed']);
        self::assertArrayNotHasKey(self::DAY, StaticContainer::get(ArchiveInvalidator::class)->getRememberedArchivedReportsThatShouldBeInvalidated(1));

        // the other page's records are kept and served, as core doesn't invalidate partial archives
        self::assertContains(Records::getRecordName($otherIdPage, Records::PAGEVIEWS), $this->getPartialArchiveRecordNames(self::DAY));
        self::assertSame(1, $this->request('day', self::DAY, self::OTHER_PAGE_URL)['preprocessed']);

        // also once the logs are deleted
        $this->setNow('2010-03-20 00:30:00');
        PrivacyManager::savePurgeDataSettings(['delete_logs_enable' => 1, 'delete_logs_older_than' => 10]);
        self::assertSame(1, $this->request('day', self::DAY, self::OTHER_PAGE_URL)['preprocessed']);
    }

    /**
     * @dataProvider getSegmentArchiveKinds
     */
    public function testErasedVisitsOfASegmentAreGoneOnceTheSegmentsDayIsArchivedAgain(bool $isPartial): void
    {
        $this->addPreProcessedSegment();
        $this->trackPageviews(self::DAY . ' 09:00:00', 2);
        $idPage = $this->selectPage(self::PAGE_URL);
        if (!$isPartial) {
            // the segment's archive for all plugins, as core:archive builds it
            $this->setNow(self::TODAY . ' 12:00:00');
            $this->archive($idPage, 'day', self::DAY, self::SEGMENT);
            Date::$now = null;
        }
        $built = $this->request('day', self::DAY, self::PAGE_URL, self::SEGMENT);
        self::assertSame(1, $built['preprocessed']);
        self::assertSame($isPartial ? 1 : 0, $this->countPartialArchives(self::DAY));

        $this->deleteVisitOfPage(self::DAY);
        // another report archives the day for all visits, which applies the erasure and forgets the date
        $this->archiveAllPlugins(self::DAY);
        self::assertArrayNotHasKey(self::DAY, StaticContainer::get(ArchiveInvalidator::class)->getRememberedArchivedReportsThatShouldBeInvalidated(1));

        if ($isPartial) {
            // core doesn't invalidate the segment's partial archive, which is served until the day is archived again
            self::assertSame($this->toArray($built), $this->toArray($this->request('day', self::DAY, self::PAGE_URL, self::SEGMENT)));
            $this->archivePluginAsCoreArchive('day', self::DAY, self::SEGMENT);
        }

        $rebuilt = $this->request('day', self::DAY, self::PAGE_URL, self::SEGMENT);
        self::assertSame(1, $rebuilt['preprocessed']);
        self::assertLessThan($built['pageMetrics']['pageviews'], $rebuilt['pageMetrics']['pageviews']);
        self::assertSame($this->getRawPageMetrics(self::DAY, self::SEGMENT), $this->toArray($rebuilt)['pageMetrics']);
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public function getSegmentArchiveKinds(): iterable
    {
        yield 'partial archive built in a request' => [true];
        yield 'archive for all plugins' => [false];
    }

    public function testErasedVisitsAreGoneFromAPartialArchiveOfASegmentOnceItIsRefreshed(): void
    {
        $this->addPreProcessedSegment();
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY, self::SEGMENT);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');
        $refreshed = $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT);
        self::assertSame(self::TODAY . ' 12:20:00', $refreshed['preprocessedAt']);
        self::assertSame(1, $this->countPartialArchives(self::TODAY));

        // core doesn't invalidate the partial archive, which is served until it is refreshed
        $this->deleteVisitOfPage(self::TODAY);
        $this->setNow(self::TODAY . ' 12:21:00');
        self::assertSame($this->toArray($refreshed), $this->toArray($this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT)));
        self::assertSame(1, $this->countPartialArchives(self::TODAY));

        $this->setNow(self::TODAY . ' 12:36:00');
        $rebuilt = $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT);
        self::assertSame(1, $rebuilt['preprocessed']);
        self::assertLessThan($refreshed['pageMetrics']['pageviews'], $rebuilt['pageMetrics']['pageviews']);
        self::assertSame($this->getRawPageMetrics(self::TODAY, self::SEGMENT), $this->toArray($rebuilt)['pageMetrics']);
    }

    /**
     * The partial archives a page's rebuild writes for a week and a month are not invalidated by core: after a data
     * subject's deletion they are served until the periods are archived again, which replaces them.
     */
    public function testErasedVisitsAreServedFromARebuiltPagesPartialArchivesUntilThePeriodsAreArchivedAgain(): void
    {
        $this->trackPageviews(self::DAY . ' 09:00:00', 2);
        $this->archiveAllPlugins(self::DAY);
        $idPage = $this->selectPage(self::PAGE_URL);
        foreach (['day', 'week', 'month'] as $period) {
            $this->rebuildAsCoreArchive($idPage, $period, self::DAY);
        }
        $this->setBrowserArchiving(false);
        $built = [];
        foreach ([Week::PERIOD_ID => 'week', Month::PERIOD_ID => 'month'] as $periodId => $period) {
            self::assertSame(1, $this->countPartialArchives($this->getPeriodStart($period), $periodId), $period);
            $built[$period] = $this->request($period, self::DAY);
            self::assertSame(1, $built[$period]['preprocessed'], $period);
        }

        $this->deleteVisitOfPage(self::DAY);
        Date::$now = time() + 3600;
        $this->applyRememberedInvalidationsAsCronArchiving();
        foreach (['week', 'month'] as $period) {
            self::assertSame($this->toArray($built[$period]), $this->toArray($this->request($period, self::DAY)), $period);
        }

        // as core:archive archives the invalidated periods again
        foreach (['day', 'week', 'month'] as $period) {
            $this->archiveAllPlugins(self::DAY, $period);
        }
        $this->setBrowserArchiving(false);
        foreach (['week', 'month'] as $period) {
            $report = $this->request($period, self::DAY);
            self::assertSame(1, $report['preprocessed'], $period);
            self::assertLessThan($built[$period]['pageMetrics']['pageviews'], $report['pageMetrics']['pageviews'], $period);
        }
    }

    private function getPeriodStart(string $period): string
    {
        return PeriodFactory::build($period, self::DAY)->getDateStart()->toString();
    }

    public function testABuildDeletesTheOlderRecordsOfThePageOnly(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $otherIdPage = $this->selectPage(self::OTHER_PAGE_URL);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 1);
        $this->trackPageviews(self::TODAY . ' 13:00:00', 1, self::OTHER_PAGE_URL);

        $this->setNow(self::TODAY . ' 12:20:00');
        $this->request('day', self::TODAY);
        $this->request('day', self::TODAY, self::OTHER_PAGE_URL);
        self::assertSame(2, $this->countPartialArchives(self::TODAY));

        $this->trackPageviews(self::TODAY . ' 13:00:00', 1);
        $this->setNow(self::TODAY . ' 12:40:00');
        $report = $this->request('day', self::TODAY);
        self::assertSame(self::TODAY . ' 12:40:00', $report['preprocessedAt']);

        self::assertSame(2, $this->countPartialArchives(self::TODAY));
        $names = $this->getPartialArchiveRecordNames(self::TODAY);
        self::assertContains(Records::getRecordName($otherIdPage, Records::PAGEVIEWS), $names);
    }

    public function testDeletingAVisitIsReflectedInADayBuiltWithoutCron(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $built = $this->request('day', self::DAY);
        self::assertSame(1, $built['preprocessed']);

        $idVisit = Db::fetchOne(
            'SELECT idvisit FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE idsite = 1 AND server_time >= ? AND server_time < ?'
            . ' AND idaction_url = (SELECT idaction FROM ' . Common::prefixTable('log_action') . ' WHERE name = ? AND type = 1) LIMIT 1',
            [self::DAY . ' 00:00:00', self::DAY . ' 23:59:59', 'example.org/page/one.html']
        );
        PrivacyManagerAPI::getInstance()->deleteDataSubjects([['idsite' => 1, 'idvisit' => $idVisit]]);

        // core doesn't invalidate the partial archive, which is served until the day is archived again
        self::assertSame($this->toArray($built), $this->toArray($this->request('day', self::DAY)));
        $this->archiveAllPlugins(self::DAY);

        $rebuilt = $this->request('day', self::DAY);
        self::assertSame(1, $rebuilt['preprocessed']);
        self::assertLessThan($built['pageMetrics']['pageviews'], $rebuilt['pageMetrics']['pageviews']);
        unset($rebuilt['preprocessed'], $rebuilt['preprocessedAt']);
        // a limit above the stored rows asks the logs
        $raw = $this->request('day', self::DAY, self::PAGE_URL, '', Records::getMaxRowsServed(1) + 1);
        self::assertSame($this->toArray($raw)['pageMetrics'], $this->toArray($rebuilt)['pageMetrics']);
        self::assertArrayNotHasKey(self::DAY, StaticContainer::get(ArchiveInvalidator::class)->getRememberedArchivedReportsThatShouldBeInvalidated(1));
    }

    public function testADayBuildThatTimesOutIsAnsweredFromTheLogsAndNotTriedAgainAtOnce(): void
    {
        Config::getInstance()->General['live_query_max_execution_time'] = 7.5;
        $raw = $this->request('day', self::DAY);
        $this->selectPage(self::PAGE_URL);
        $model = new class extends Model {
            /**
             * @var list<float>
             */
            public array $timeLimits = [];

            public function queryInternalReferrersOfPages(LogAggregator $logAggregator, array $idActions, int $limit, string $othersLabel, float $timeLimit = -1): array
            {
                $this->timeLimits[] = $timeLimit;
                throw new MaxExecutionTimeExceededException('The query took too long.');
            }
        };
        StaticContainer::getContainer()->set(Model::class, $model);

        self::assertSame($this->toArray($raw), $this->toArray($this->request('day', self::DAY)));
        self::assertSame([7.5], $model->timeLimits);
        self::assertSame(0, $this->countPartialArchives(self::DAY));

        // the next request doesn't try again
        $this->request('day', self::DAY);
        self::assertSame([7.5], $model->timeLimits);
    }

    /**
     * @dataProvider getBuildLockTtls
     */
    public function testTheBuildLockOutlastsTheTimeLimitedQueriesAndIsExtendedBetweenTheBuildSteps(float $timeLimit, int $ttl): void
    {
        Config::getInstance()->General['live_query_max_execution_time'] = $timeLimit;
        $this->selectPage(self::PAGE_URL);
        $backend = new class (StaticContainer::get(LockBackend::class)) implements LockBackend {
            /**
             * @var array{acquired: list<int>, extended: list<int>}
             */
            public array $ttls = ['acquired' => [], 'extended' => []];

            public function __construct(private readonly LockBackend $backend)
            {
            }

            public function getKeysMatchingPattern($pattern)
            {
                return $this->backend->getKeysMatchingPattern($pattern);
            }

            public function setIfNotExists($lockKey, $lockValue, $ttlInSeconds)
            {
                $this->ttls['acquired'][] = $ttlInSeconds;

                return $this->backend->setIfNotExists($lockKey, $lockValue, $ttlInSeconds);
            }

            public function get($lockKey)
            {
                return $this->backend->get($lockKey);
            }

            public function deleteIfKeyHasValue($lockKey, $lockValue)
            {
                return $this->backend->deleteIfKeyHasValue($lockKey, $lockValue);
            }

            public function expireIfKeyHasValue($lockKey, $lockValue, $ttlInSeconds)
            {
                $this->ttls['extended'][] = $ttlInSeconds;

                return $this->backend->expireIfKeyHasValue($lockKey, $lockValue, $ttlInSeconds);
            }
        };
        $this->setLoader(10000, StoredRecordsLoader::MAX_SUMMED_PERIODS, $backend);

        self::assertSame(1, $this->request('day', self::DAY)['preprocessed']);
        self::assertSame(['acquired' => [$ttl], 'extended' => [$ttl, $ttl]], $backend->ttls);
    }

    /**
     * @return array<string, array{float, int}>
     */
    public function getBuildLockTtls(): array
    {
        return [
            'no time limit' => [-1.0, 300],
            'a short time limit' => [7.5, 300],
            'a long time limit' => [200.0, 660],
        ];
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

    public function testASegmentNoLongerArchivedForTransitionsIsAnsweredFromTheLogs(): void
    {
        $this->addPreProcessedSegment();
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->trackPageviews(self::TODAY . ' 09:00:00', 1);
        $this->setNow(self::TODAY . ' 12:00:00');
        $this->archive($idPage, 'day', self::TODAY, self::SEGMENT);
        self::assertSame(1, $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT)['preprocessed']);

        Config::getInstance()->General['disable_archiving_segment_for_plugins'] = 'Transitions';
        $this->trackPageviews(self::TODAY . ' 13:00:00', 2);
        $this->setNow(self::TODAY . ' 12:20:00');

        $report = $this->request('day', self::TODAY, self::PAGE_URL, self::SEGMENT);
        self::assertArrayNotHasKey('preprocessed', $report);
        self::assertSame($this->getRawPageMetrics(self::TODAY, self::SEGMENT), $this->toArray($report)['pageMetrics']);
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

    /**
     * Archives the day for all plugins as browser archiving does, for example for another report.
     */
    private function archiveAllPlugins(string $date, string $period = 'day'): void
    {
        $general = Config::getInstance()->General;
        $this->setBrowserArchiving(true);
        Cache::getTransientCache()->flushAll();

        try {
            Archive::build(1, $period, $date)->getNumeric('nb_visits');
        } finally {
            Config::getInstance()->General = $general;
            Cache::getTransientCache()->flushAll();
        }
    }

    /**
     * @return array<string, mixed> the page metrics of the logs: a limit above the stored rows asks them
     */
    private function getRawPageMetrics(string $date, string $segment = ''): array
    {
        return $this->toArray($this->request('day', $date, self::PAGE_URL, $segment, Records::getMaxRowsServed(1) + 1))['pageMetrics'];
    }

    /**
     * Deletes a direct entry's visit to the page on the day, as a data subject's; it is in {@link SEGMENT}.
     */
    private function deleteVisitOfPage(string $date): void
    {
        $idVisit = Db::fetchOne(
            'SELECT a.idvisit FROM ' . Common::prefixTable('log_link_visit_action') . ' a JOIN ' . Common::prefixTable('log_visit') . ' v ON v.idvisit = a.idvisit'
            . ' WHERE a.idsite = 1 AND a.server_time >= ? AND a.server_time <= ? AND v.referer_type = ?'
            . ' AND a.idaction_url = (SELECT idaction FROM ' . Common::prefixTable('log_action') . ' WHERE name = ? AND type = 1)'
            . ' ORDER BY a.idvisit DESC LIMIT 1',
            [$date . ' 00:00:00', $date . ' 23:59:59', Common::REFERRER_TYPE_DIRECT_ENTRY, 'example.org/page/one.html']
        );
        self::assertNotFalse($idVisit);
        PrivacyManagerAPI::getInstance()->deleteDataSubjects([['idsite' => 1, 'idvisit' => $idVisit]]);
    }

    /**
     * Applies the remembered invalidations of the website as `core:archive` does before it archives it.
     */
    private function applyRememberedInvalidationsAsCronArchiving(): void
    {
        $cronArchive = new CronArchive();
        $cronArchive->init();
        $cronArchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain(1);
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

        // the tracker runs at the real time, in which the faked today is in the past, so it remembers to invalidate it
        Option::deleteLike('%report\_to\_invalidate\_%');
    }

    private function selectPage(string $url): int
    {
        return API::getInstance()->addPreprocessedPage(1, $url)['idpage'];
    }

    /**
     * Archives the period as browser archiving does, with the page's records and the visits.
     */
    /**
     * Archives the page alone, as `core:archive` runs the rebuild scheduled when it is selected.
     */
    /**
     * Archives this plugin alone, as `core:archive` processes its invalidation.
     */
    private function archivePluginAsCoreArchive(string $period, string $date, string $segment = ''): void
    {
        $_GET['trigger'] = 'archivephp';
        $_GET['pluginOnly'] = '1';
        Cache::getTransientCache()->flushAll();

        try {
            $params = new Parameters(new Site(1), PeriodFactory::build($period, $date), new Segment($segment, [1]));
            $params->setRequestedPlugin('Transitions');
            (new ArchiveLoader($params))->prepareArchive('Transitions');
        } finally {
            unset($_GET['trigger'], $_GET['pluginOnly']);
            Cache::getTransientCache()->flushAll();
        }
    }

    private function rebuildAsCoreArchive(int $idPage, string $period, string $date): void
    {
        $_GET['trigger'] = 'archivephp';
        $_GET['pluginOnly'] = '1';
        $_GET['requestedReport'] = Records::getRebuildReportName($idPage);
        Cache::getTransientCache()->flushAll();

        try {
            $params = new Parameters(new Site(1), PeriodFactory::build($period, $date), new Segment('', [1]));
            $params->setRequestedPlugin('Transitions');
            $params->setArchiveOnlyReport(Records::getRebuildReportName($idPage));
            (new ArchiveLoader($params))->prepareArchive('Transitions');
        } finally {
            unset($_GET['trigger'], $_GET['pluginOnly'], $_GET['requestedReport']);
            Cache::getTransientCache()->flushAll();
        }
    }

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

    /**
     * Counts the partial archives whose records are served, those not marked invalidated.
     */
    private function countPartialArchives(string $date1, int $periodId = 1): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_numeric_2010_03')
            . " p WHERE idsite = 1 AND period = ? AND date1 = ? AND name LIKE 'done%.Transitions' AND value = ?",
            [$periodId, $date1, ArchiveWriter::DONE_PARTIAL]
        );
    }

    private function getPartialDoneFlag(int $idSite, string $segment): string
    {
        return Rules::getDoneFlagArchiveContainsOnePlugin(new Segment($segment, [$idSite]), 'Transitions');
    }

    private function setLoader(
        int $maxLockWaitMilliseconds,
        int $maxSummedPeriods = StoredRecordsLoader::MAX_SUMMED_PERIODS,
        ?LockBackend $lockBackend = null
    ): void {
        StaticContainer::getContainer()->set(StoredRecordsLoader::class, new StoredRecordsLoader(
            StaticContainer::get(StoredRecordsReader::class),
            StaticContainer::get(Service::class),
            StaticContainer::get(PartialArchives::class),
            $lockBackend ?? StaticContainer::get(LockBackend::class),
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
