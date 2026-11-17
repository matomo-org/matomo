<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\tests\Integration;

use Piwik\Access;
use Piwik\Archive;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\CronArchive\ReArchiveList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log\LoggerInterface;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugins\PrivacyManager\API as PrivacyManagerAPI;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Segment;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeLogger;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * `core:archive` with pre-processed pages: two websites with the same visits, of which only the first selects pages,
 * a pre-processed segment, and range archives as browsers and `core:archive` build them. The visits are on days of the
 * month before last, so only archives of periods holding today or yesterday are archived again by every run.
 *
 * The clock of the test is fixed at the time the fixture was built, so its days don't change during a run. The
 * archiving requests run with the real clock, which only matters for the periods holding today or yesterday.
 *
 * @group Transitions
 * @group PreprocessedCronArchivingTest
 * @group Plugins
 */
class PreprocessedCronArchivingTest extends IntegrationTestCase
{
    private const SEGMENT = 'referrerType==website';
    private const PAGE_URL = 'https://example.org/page/one.html';
    private const OTHER_PAGE_URL = 'https://example.org/page/two.html';
    private const NEW_PAGE_URL = 'https://example.org/page/three.html';
    private const REARCHIVE_MONTHS = 'last3';

    /**
     * @var int|null
     */
    private static $now = null;

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        self::$now = time();
        Date::$now = self::$now;
        Fixture::createSuperUser();
        // archived by core:archive only
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        Rules::setBrowserTriggerArchiving(false);
        foreach ([1, 2] as $idSite) {
            // created before the rebuild window
            Fixture::createWebsite(self::getFirstDay()->subMonth(3)->setDay(1)->getDatetime(), 0, 'Site ' . $idSite, 'https://example.org');
        }
        SegmentEditorAPI::getInstance()->add('website', self::SEGMENT, null, true);

        foreach ([1, 2] as $idSite) {
            self::trackVisits($idSite);
        }

        // selected before the first run, without the rebuild that adding them schedules, so the run is a normal one
        API::getInstance()->addPreprocessedPage(1, self::PAGE_URL);
        API::getInstance()->addPreprocessedPage(1, self::OTHER_PAGE_URL);
        (new ReArchiveList())->setAll([]);

        self::runCoreArchive();
        self::archiveRanges();
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function setUp(): void
    {
        parent::setUp();

        Date::$now = self::$now;

        Config::getInstance()->General['rearchive_reports_in_past_last_n_months'] = self::REARCHIVE_MONTHS;
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
    }

    /**
     * R-A1
     */
    public function testANormalRunArchivesEverythingAsWithoutSelectedPagesPlusThePagesRecords(): void
    {
        $withPages = $this->getArchivedRecords(1);
        self::assertSame($this->getArchivedRecords(2), $this->withoutTransitionsRecords($withPages));

        $segmentDoneFlag = Rules::getDoneFlagArchiveContainsAllPlugins(new Segment(self::SEGMENT, [1]));
        $expected = [];
        foreach ($this->getPeriodsWithVisits() as $period) {
            foreach (['done', $segmentDoneFlag] as $doneFlag) {
                $expected[] = $period . ' ' . $doneFlag;
            }
        }

        $withRecords = [];
        foreach ($withPages as $key => $records) {
            $hasPageRecords = isset($records[Records::getRecordName(1, Records::PAGEVIEWS)])
                && isset($records[Records::getRecordName(2, Records::PAGEVIEWS)])
                && isset($records[Records::getRecordName(1, Records::REFERRERS)]);
            if ($hasPageRecords) {
                $withRecords[] = $key;
            }
        }
        sort($expected);
        sort($withRecords);
        self::assertSame($expected, $withRecords);
        self::assertGreaterThan(0, $this->getStoredNumeric(1, 'done', Records::getRecordName(1, Records::PAGEVIEWS), 'month'));
    }

    /**
     * R-A2
     */
    public function testAddingAPageInvalidatesOnlyItsRecordsForAllSegmentsWithinTheWindow(): void
    {
        $before = $this->getInvalidations();
        $archivesBefore = $this->getArchiveRows(fn(array $row) => true);

        $idPage = API::getInstance()->addPreprocessedPage(1, self::NEW_PAGE_URL)['idpage'];
        $report = Records::getRebuildReportName($idPage);

        $scheduled = array_map(fn($item) => json_decode($item, true), (new ReArchiveList())->getAll());
        self::assertSame([['idSites' => [1], 'pluginName' => 'Transitions', 'report' => $report, 'startDate' => null, 'segment' => null]], $scheduled);
        self::assertSame($before, $this->getInvalidations());

        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();

        // no archive is marked invalidated, the ranges of every plugin included
        self::assertSame($archivesBefore, $this->getArchiveRows(fn(array $row) => true));
        self::assertNotEmpty(array_filter($archivesBefore, fn(array $row) => (int) $row['period'] === 5));

        $added = array_values(array_diff_key($this->getInvalidations(), $before));
        self::assertNotEmpty($added);
        $segmentDoneFlag = Rules::getDoneFlagArchiveContainsOnePlugin(new Segment(self::SEGMENT, [1]), 'Transitions');
        $daysByDoneFlag = [];
        foreach ($added as $row) {
            self::assertSame(1, (int) $row['idsite']);
            self::assertSame($report, $row['report']);
            self::assertContains($row['name'], ['done.Transitions', $segmentDoneFlag]);
            if ((int) $row['period'] === 1) {
                $daysByDoneFlag[$row['name']][] = $row['date1'];
            }
        }

        // every day of the window, for all visits and the segment
        $expectedDays = [];
        for ($day = Date::yesterday()->subMonth(3)->setDay(1); $day->isEarlier(Date::today()); $day = $day->addDay(1)) {
            $expectedDays[] = $day->toString();
        }
        foreach (['done.Transitions', $segmentDoneFlag] as $doneFlag) {
            $days = $daysByDoneFlag[$doneFlag] ?? [];
            sort($days);
            self::assertSame($expectedDays, $days, $doneFlag);
        }
    }

    /**
     * R-A3: other archives are kept as they are, the all-plugins archives too, and the page's records are built.
     */
    public function testArchivingAnAddedPageLeavesEveryOtherArchiveUntouched(): void
    {
        $before = $this->getArchiveRows(fn(array $row) => true);

        $idPage = API::getInstance()->addPreprocessedPage(1, self::NEW_PAGE_URL)['idpage'];
        self::runCoreArchive();

        // archives of today's and yesterday's periods are archived again by every run, with or without pages
        $isUntouchable = fn(array $row) => !$this->holdsTodayOrYesterday($row) && !str_ends_with($row['done'], '.Transitions');
        $after = $this->getArchiveRows($isUntouchable);
        self::assertSame(array_filter($before, $isUntouchable), $after);
        self::assertNotEmpty($after);

        // the ranges are among them
        self::assertNotEmpty(array_filter($after, fn(array $row) => (int) $row['period'] === 5 && $row['done'] === 'done'));
        self::assertNotEmpty(array_filter($after, fn(array $row) => (int) $row['period'] === 5 && $row['done'] === 'done.VisitsSummary'));

        // no archive of another plugin, or for all plugins, of any table was invalidated
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $numericTable) {
            self::assertSame(0, (int) Db::fetchOne(
                'SELECT COUNT(*) FROM ' . $numericTable . " WHERE name LIKE 'done%' AND name NOT LIKE '%.Transitions' AND value IN (?, ?)",
                [ArchiveWriter::DONE_INVALIDATED, ArchiveWriter::DONE_ERROR_INVALIDATED]
            ), $numericTable);
        }

        // the archives of today's and yesterday's periods, such as this year's, hold the same as without pages
        self::assertSame($this->getArchivedRecords(2), $this->withoutTransitionsRecords($this->getArchivedRecords(1)));

        // the page's records are built in partial archives, for all visits and the segment
        $segmentDoneFlag = Rules::getDoneFlagArchiveContainsOnePlugin(new Segment(self::SEGMENT, [1]), 'Transitions');
        foreach (['done.Transitions', $segmentDoneFlag] as $doneFlag) {
            foreach (['day', 'month'] as $period) {
                self::assertNotNull($this->getStoredNumeric(1, $doneFlag, Records::getRecordName($idPage, Records::PAGEVIEWS), $period), "$doneFlag $period");
            }
        }
    }

    /**
     * R-A3: invalidations remembered before the page was added are applied by the same run, and a following run
     * archives as on the website without pages.
     */
    public function testInvalidationsRememberedBeforeAPageIsAddedAreAppliedAndFollowingRunsArchiveAsWithoutPages(): void
    {
        $lateDay = self::getFirstDay();
        $erasureDay = self::getFirstDay()->addDay(1);
        $visits = [];
        foreach ([1, 2] as $idSite) {
            $visits[$idSite] = [
                'late' => $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $lateDay),
                'erasure' => $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $erasureDay),
            ];

            // late data, and a data subject's visit deleted
            self::trackVisit($idSite, $lateDay->toString('Y-m-d 20:00:00'), self::PAGE_URL, '', 100);
            $idVisit = Db::fetchOne(
                'SELECT idvisit FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ? AND visit_last_action_time >= ? AND visit_last_action_time < ? LIMIT 1',
                [$idSite, $erasureDay->getDatetime(), $erasureDay->addDay(1)->getDatetime()]
            );
            PrivacyManagerAPI::getInstance()->deleteDataSubjects([['idsite' => $idSite, 'idvisit' => $idVisit]]);
        }
        $remembered = StaticContainer::get(ArchiveInvalidator::class)->getRememberedArchivedReportsThatShouldBeInvalidated(1);
        self::assertArrayHasKey($lateDay->toString(), $remembered);
        self::assertArrayHasKey($erasureDay->toString(), $remembered);

        API::getInstance()->addPreprocessedPage(1, self::NEW_PAGE_URL);
        // core:archive applies a remembered date only once the day's archive is older than its time to live
        Date::$now = self::$now + 3600;
        self::runCoreArchive();

        foreach ([1, 2] as $idSite) {
            self::assertSame([], array_intersect_key(
                StaticContainer::get(ArchiveInvalidator::class)->getRememberedArchivedReportsThatShouldBeInvalidated($idSite),
                [$lateDay->toString() => 1, $erasureDay->toString() => 1]
            ), "site $idSite");
            self::assertSame($visits[$idSite]['late'] + 1, $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $lateDay), "site $idSite");
            self::assertSame($visits[$idSite]['erasure'] - 1, $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $erasureDay), "site $idSite");
        }

        self::runCoreArchive();
        self::assertSame($this->getArchivedRecords(2), $this->withoutTransitionsRecords($this->getArchivedRecords(1)));
    }

    /**
     * R-A3: an archive of the page in progress, for example in another process, doesn't hold back the other archives of
     * the same or longer periods, nor those of the other websites.
     */
    public function testArchivingAnAddedPageDoesNotHoldBackOtherArchiving(): void
    {
        $lateDay = self::getFirstDay();
        $idPage = API::getInstance()->addPreprocessedPage(1, self::NEW_PAGE_URL)['idpage'];
        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();

        // a late visit on both websites, tracked once the page's rebuild is queued
        $visits = [];
        foreach ([1, 2] as $idSite) {
            $visits[$idSite] = [
                'day' => $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $lateDay),
                'month' => $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'month', $lateDay),
            ];
            self::trackVisit($idSite, $lateDay->toString('Y-m-d 20:00:00'), self::PAGE_URL, '', 100);
        }

        // another process archives the page's records of the day of the late visit, and never finishes
        $inProgress = Db::query(
            'UPDATE ' . Common::prefixTable('archive_invalidations') . ' SET status = 1, ts_started = ? WHERE idsite = 1 AND period = 1 AND date1 = ? AND name = ? AND report = ?',
            [Date::now()->getDatetime(), $lateDay->toString(), 'done.Transitions', Records::getRebuildReportName($idPage)]
        )->rowCount();
        self::assertSame(1, $inProgress);

        // core:archive applies a remembered date only once the day's archive is older than its time to live
        Date::$now = self::$now + 3600;
        self::runCoreArchive();

        // the late visit is archived for all plugins by this run, on both websites
        foreach ([1, 2] as $idSite) {
            self::assertSame($visits[$idSite]['day'] + 1, $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $lateDay), "site $idSite");
            self::assertSame($visits[$idSite]['month'] + 1, $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'month', $lateDay), "site $idSite");
        }

        // the page's other records are archived, only the longer periods of the day in progress wait for it
        $pageRows = Db::fetchAll(
            'SELECT DISTINCT period FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE idsite = 1 AND report = ? AND date1 <= ? AND date2 >= ? ORDER BY period',
            [Records::getRebuildReportName($idPage), $lateDay->toString(), $lateDay->toString()]
        );
        $periods = array_map('intval', array_column($pageRows, 'period'));
        self::assertContains(1, $periods);
        self::assertContains(2, $periods);
        self::assertGreaterThan(0, $this->getStoredNumeric(1, 'done.Transitions', Records::getRecordName($idPage, Records::PAGEVIEWS), 'day', $lateDay->addDay(1)));
    }

    /**
     * R-A1, R-A3: runs stopped by --max-archives-to-process, which continue the shared list of websites, lose nothing:
     * the following runs archive every other invalidation and the single reports of every website, the added page's
     * and another plugin's, re-archived as core updates do.
     */
    public function testRunsStoppedByTheirLimitArchiveEverythingInLaterRuns(): void
    {
        $lateDay = self::getFirstDay();
        $idPage = API::getInstance()->addPreprocessedPage(1, self::NEW_PAGE_URL)['idpage'];
        StaticContainer::get(ArchiveInvalidator::class)->scheduleReArchiving([1, 2], 'Actions', 'Actions_hits', $lateDay);
        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();

        // tracked once the single reports are queued, which forgets the dates remembered before
        $visits = [];
        foreach ([1, 2] as $idSite) {
            $visits[$idSite] = $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $lateDay);
            self::trackVisit($idSite, $lateDay->toString('Y-m-d 20:00:00'), self::PAGE_URL, '', 100);
        }

        // core:archive applies a remembered date only once the day's archive is older than its time to live
        Date::$now = self::$now + 3600;
        $processed = [];
        $runs = 0;
        do {
            $output = self::runCoreArchive(3);
            $processedByRun = [];
            foreach (explode(PHP_EOL, $output) as $line) {
                if (preg_match('/^Processing invalidation: .*idsite = (\d+), period = (\w+)\(([\d-]+) .*name = (done[^,]*),/', $line, $matches)) {
                    $processedByRun[] = (str_contains($matches[4], '.') ? 'report ' : '') . "{$matches[1]} {$matches[2]} {$matches[3]}";
                }
            }
            $processed = array_merge($processed, $processedByRun);
            $runs++;
        } while ($runs < 40 && Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . " WHERE report IS NOT NULL AND report <> ''") > 0);

        // more than one run per list of websites, and every single report was archived
        self::assertGreaterThan(2, $runs);
        self::assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . " WHERE report IS NOT NULL AND report <> ''"));

        // the late visit was archived for all plugins on both websites
        foreach ([1, 2] as $idSite) {
            self::assertSame($visits[$idSite] + 1, $this->getStoredNumeric($idSite, 'done', 'nb_visits', 'day', $lateDay), "site $idSite");
            self::assertContains("$idSite day {$lateDay->toString()}", $processed);
            self::assertContains("report $idSite day {$lateDay->toString()}", $processed);
            self::assertNotNull($this->getStoredNumeric($idSite, 'done.Actions', 'Actions_hits', 'day', $lateDay), "site $idSite");
        }
        self::assertGreaterThan(0, $this->getStoredNumeric(1, 'done.Transitions', Records::getRecordName($idPage, Records::PAGEVIEWS), 'day', $lateDay));
        self::assertGreaterThan(0, $this->getStoredNumeric(1, 'done.Transitions', Records::getRecordName($idPage, Records::PAGEVIEWS), 'month', $lateDay));
    }

    /**
     * R-A1, R-A3: a run stopped by a signal, maintenance mode or a kill once it archived the first website loses
     * nothing: it archived that website's single reports in its turn, and the run that continues the shared list
     * archives those of the next website.
     */
    public function testARunStoppedAfterAWebsiteLosesNothing(): void
    {
        if (!extension_loaded('pcntl')) {
            self::markTestSkipped('pcntl is required');
        }

        $idPage = API::getInstance()->addPreprocessedPage(1, self::NEW_PAGE_URL)['idpage'];
        StaticContainer::get(ArchiveInvalidator::class)->scheduleReArchiving([1, 2], 'Actions', 'Actions_hits', self::getFirstDay());

        $archiver = null;
        Piwik::addAction('CronArchive.archiveSingleSite.finish', function ($idSite) use (&$archiver) {
            if ($archiver instanceof CronArchive && (int) $idSite === 1) {
                $archiver->handleSignal(\SIGINT);
            }
        });
        $output = self::runCoreArchive(null, function (CronArchive $cronArchive) use (&$archiver) {
            $archiver = $cronArchive;
        });
        $archiver = null;
        self::assertStringContainsString('signal to abort received', $output);
        $singleReportsOfSites = 'SELECT DISTINCT idsite FROM ' . Common::prefixTable('archive_invalidations') . " WHERE report IS NOT NULL AND report <> '' ORDER BY idsite";
        self::assertEquals([['idsite' => 2]], Db::fetchAll($singleReportsOfSites));
        self::assertGreaterThan(0, $this->getStoredNumeric(1, 'done.Transitions', Records::getRecordName($idPage, Records::PAGEVIEWS), 'month'));

        $output = self::runCoreArchive();
        self::assertStringContainsString('Continuing ongoing archiving run', $output);
        self::assertStringNotContainsString('Start processing archives for site 1.', $output);
        self::assertSame([], Db::fetchAll($singleReportsOfSites));
        self::assertNotNull($this->getStoredNumeric(2, 'done.Actions', 'Actions_hits', 'day', self::getFirstDay()));
    }

    private static function getFirstDay(): Date
    {
        return Date::today()->subMonth(2)->setDay(5);
    }

    /**
     * A range archive for all plugins, as `core:archive` builds them for custom ranges, and one of a plugin, as browser
     * archiving does, of each website.
     */
    private static function archiveRanges(): void
    {
        $firstDay = self::getFirstDay()->toString();
        $general = Config::getInstance()->General;
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 1;
        Rules::setBrowserTriggerArchiving(true);

        try {
            foreach ([1, 2] as $idSite) {
                Archive::build($idSite, 'range', $firstDay . ',' . self::getFirstDay()->addDay(1)->toString())->getNumeric('nb_visits');
                $_GET['trigger'] = 'archivephp';
                try {
                    Access::doAsSuperUser(function () use ($idSite, $firstDay) {
                        Archive::build($idSite, 'range', $firstDay . ',' . self::getFirstDay()->addDay(2)->toString())->getNumeric('nb_visits');
                    });
                } finally {
                    unset($_GET['trigger']);
                }
            }
        } finally {
            Config::getInstance()->General = $general;
            Rules::setBrowserTriggerArchiving(false);
        }
    }

    private static function trackVisits(int $idSite): void
    {
        $day = self::getFirstDay();
        for ($i = 0; $i < 3; $i++) {
            $date = $day->addDay($i)->toString('Y-m-d');
            self::trackVisit($idSite, "$date 08:00:00", self::PAGE_URL, 'https://referrer.example.com/a', 3 * $i + 1);
            self::trackVisit($idSite, "$date 09:00:00", self::OTHER_PAGE_URL, '', 3 * $i + 2);
            self::trackVisit($idSite, "$date 10:00:00", self::PAGE_URL, 'https://other.example.net/b', 3 * $i + 3);
        }
    }

    /**
     * @param int $visitor the same for the same visit of each website, so their reports are the same
     */
    private static function trackVisit(int $idSite, string $dateTime, string $firstUrl, string $referrer, int $visitor): void
    {
        $tracker = Fixture::getTracker($idSite, $dateTime, $defaultInit = true);
        $tracker->setVisitorId(substr(md5('visitor' . $visitor), 0, 16));
        $tracker->setIp('10.2.0.' . $visitor);
        if ($referrer !== '') {
            $tracker->setUrlReferrer($referrer);
        }
        $urls = [$firstUrl, self::OTHER_PAGE_URL, self::NEW_PAGE_URL, self::PAGE_URL];
        foreach ($urls as $i => $url) {
            $tracker->setForceVisitDateTime(Date::factory($dateTime)->addPeriod($i, 'minute')->getDatetime());
            $tracker->setUrl($url);
            Fixture::checkResponse($tracker->doTrackPageView('page ' . $i));
        }
    }

    private static function runCoreArchive(?int $maxArchivesToProcess = null, ?callable $onStart = null): string
    {
        $logger = new FakeLogger();
        StaticContainer::getContainer()->set(LoggerInterface::class, $logger);
        $archiver = new CronArchive($logger);
        $archiver->maxArchivesToProcess = $maxArchivesToProcess;
        if ($onStart !== null) {
            $onStart($archiver);
        }
        $archiver->main();
        self::assertStringNotContainsString('ERROR', $logger->output);

        return $logger->output;
    }

    /**
     * The records of the newest finished archive for all plugins of each period and segment.
     *
     * @return array<string, array<string, mixed>> by "<period> <date1>,<date2> <done flag>"
     */
    private function getArchivedRecords(int $idSite): array
    {
        $records = [];
        foreach (ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE) as $numericTable) {
            $archives = Db::fetchAll(
                'SELECT idarchive, period, date1, date2, name FROM ' . $numericTable . " WHERE idsite = ? AND name LIKE 'done%' AND name NOT LIKE '%.%' AND value = ?"
                . ' ORDER BY idarchive',
                [$idSite, ArchiveWriter::DONE_OK]
            );
            $blobTable = str_replace('_numeric_', '_blob_', $numericTable);
            foreach ($archives as $archive) {
                $key = $this->getPeriodLabel((int) $archive['period']) . ' ' . $archive['date1'] . ',' . $archive['date2'] . ' ' . $archive['name'];
                $values = [];
                foreach (Db::fetchAll('SELECT name, value FROM ' . $numericTable . ' WHERE idarchive = ? AND name NOT LIKE ?', [$archive['idarchive'], 'done%']) as $row) {
                    $values[$row['name']] = (float) $row['value'];
                }
                foreach (Db::fetchAll('SELECT name, value FROM ' . $blobTable . ' WHERE idarchive = ?', [$archive['idarchive']]) as $row) {
                    $values[$row['name']] = @gzuncompress($row['value']) ?: $row['value'];
                }
                ksort($values);
                // the newest one wins
                $records[$key] = $values;
            }
        }
        ksort($records);

        return $records;
    }

    /**
     * @param array<string, array<string, mixed>> $records
     * @return array<string, array<string, mixed>>
     */
    private function withoutTransitionsRecords(array $records): array
    {
        foreach ($records as $key => $values) {
            $records[$key] = array_filter($values, fn($name) => !str_starts_with((string) $name, 'Transitions_'), ARRAY_FILTER_USE_KEY);
        }

        return $records;
    }

    /**
     * @return list<string> "<period> <date1>,<date2>" of the periods holding the fixture's visits
     */
    private function getPeriodsWithVisits(): array
    {
        $periods = [];
        for ($i = 0; $i < 3; $i++) {
            $day = self::getFirstDay()->addDay($i);
            foreach (['day', 'week', 'month', 'year'] as $label) {
                $period = PeriodFactory::build($label, $day);
                $periods[$label . ' ' . $period->getRangeString()] = true;
            }
        }

        return array_keys($periods);
    }

    /**
     * Every row of every archive, with its done flag, keyed by table and idarchive and name.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getArchiveRows(callable $filter): array
    {
        $rows = [];
        foreach (ArchiveTableCreator::getTablesArchivesInstalled() as $table) {
            $numericTable = str_replace('_blob_', '_numeric_', $table);
            $all = Db::fetchAll(
                'SELECT d.idarchive, d.idsite, d.period, d.date1, d.date2, d.name, d.ts_archived, ' . (str_contains($table, '_blob_') ? 'MD5(d.value)' : 'd.value') . ' AS value, f.name AS done, f.value AS done_value'
                . ' FROM ' . $table . ' d JOIN ' . $numericTable . " f ON f.idarchive = d.idarchive AND f.name LIKE 'done%'"
            );
            foreach ($all as $row) {
                if ($filter($row)) {
                    $rows[$table . ' ' . $row['idarchive'] . ' ' . $row['name']] = $row;
                }
            }
        }
        ksort($rows);

        return $rows;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function holdsTodayOrYesterday(array $row): bool
    {
        return $row['date1'] <= Date::today()->toString() && $row['date2'] >= Date::yesterday()->toString();
    }

    private function getStoredNumeric(int $idSite, string $doneFlag, string $name, string $period, ?Date $date = null): ?float
    {
        $periodObject = PeriodFactory::build($period, $date ?? self::getFirstDay());
        $value = Db::fetchOne(
            'SELECT r.value FROM ' . ArchiveTableCreator::getNumericTable($periodObject->getDateStart(), false) . ' r JOIN '
            . ArchiveTableCreator::getNumericTable($periodObject->getDateStart(), false) . ' f ON f.idarchive = r.idarchive AND f.name = ? AND f.value IN (?, ?)'
            . ' WHERE r.idsite = ? AND r.period = ? AND r.date1 = ? AND r.date2 = ? AND r.name = ? ORDER BY r.idarchive DESC LIMIT 1',
            [
                $doneFlag,
                ArchiveWriter::DONE_OK,
                ArchiveWriter::DONE_PARTIAL,
                $idSite,
                $periodObject->getId(),
                $periodObject->getDateStart()->toString(),
                $periodObject->getDateEnd()->toString(),
                $name,
            ]
        );

        return $value === false || $value === null ? null : (float) $value;
    }

    private function getPeriodLabel(int $periodId): string
    {
        return array_flip(Piwik::$idPeriods)[$periodId];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getInvalidations(): array
    {
        $rows = [];
        foreach (Db::fetchAll('SELECT idinvalidation, idsite, period, date1, date2, name, report FROM ' . Common::prefixTable('archive_invalidations')) as $row) {
            $rows[(string) $row['idinvalidation']] = $row;
        }

        return $rows;
    }
}
