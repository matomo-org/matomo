<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\tests\Integration;

use Piwik\API\Request;
use Piwik\Archive;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\RawLogDao;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\EventDispatcher;
use Piwik\Log\LoggerInterface;
use Piwik\NoAccessException;
use Piwik\Plugins\Overlay\API as OverlayAPI;
use Piwik\Plugins\PrivacyManager\ReportsPurger;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\Dao;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsLoader;
use Piwik\Plugins\Transitions\PreprocessedPages\StoredRecordsReader;
use Piwik\Plugins\Transitions\Transitions;
use Piwik\Tests\Fixtures\SomeVisitsManyPageviewsWithTransitions;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Serving the Transitions report from the stored records of pre-processed pages.
 *
 * @group Transitions
 * @group PreprocessedReportTest
 * @group Plugins
 */
class PreprocessedReportTest extends IntegrationTestCase
{
    /**
     * @var SomeVisitsManyPageviewsWithTransitions
     */
    public static $fixture;

    private const PAGE_URL = 'http://example.org/page/one.html';
    private const OTHER_PAGE_URL = 'http://example.org/sub/dir/page2.html';
    private const DAY = '2010-03-06';
    private const LATER_DAY = '2010-03-14';
    // a day, a day and a week, all with visits
    private const RANGE = '2010-03-06,2010-03-14';
    private const SEGMENT = 'referrerType==website';
    private const LOG_TABLES = ['log_visit', 'log_link_visit_action', 'log_conversion', 'log_conversion_item', 'log_page_view_time'];

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

        // a range's subperiods without an archive can't be told apart from not archived ones
        $tracker = Fixture::getTracker(1, '2010-03-07 10:00:00', $defaultInit = true);
        $tracker->setUrl(self::OTHER_PAGE_URL);
        Fixture::checkResponse($tracker->doTrackPageView('page title - sub/dir/page2.html'));
    }

    /**
     * @dataProvider getPeriodsAndLimits
     */
    public function testStoredDataEqualsTheLogQuery(string $period, string $date, int $limit): void
    {
        $raw = $this->request($period, $date, '', $limit);

        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, $period, $date);

        $this->assertStoredEqualsRaw($raw, $this->request($period, $date, '', $limit));
    }

    /**
     * @return array<string, array{string, string, int}>
     */
    public function getPeriodsAndLimits(): array
    {
        $cases = [];
        foreach (['day' => self::DAY, 'week' => self::DAY, 'month' => self::DAY, 'year' => self::DAY, 'range' => self::RANGE] as $period => $date) {
            foreach ([1, 5, 100] as $limit) {
                $cases[$period . ' limit ' . $limit] = [$period, $date, $limit];
            }
        }

        return $cases;
    }

    /**
     * @dataProvider getPeriodsOfSegment
     */
    public function testStoredDataOfAPreProcessedSegmentEqualsTheLogQuery(string $period, string $date): void
    {
        $raw = $this->request($period, $date, self::SEGMENT);

        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, $period, $date, self::SEGMENT);

        $stored = $this->request($period, $date, self::SEGMENT);
        $this->assertStoredEqualsRaw($raw, $stored);
        self::assertNotSame($this->request($period, $date)['pageMetrics'], $stored['pageMetrics']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getPeriodsOfSegment(): array
    {
        return ['day' => ['day', self::DAY], 'month' => ['month', self::DAY]];
    }

    /**
     * @dataProvider getParts
     */
    public function testPartsAreServedWithTheSameLoopSemantics(string $parts, string $period): void
    {
        $raw = $this->request($period, self::DAY, '', 0, $parts);

        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, $period, self::DAY);

        $stored = $this->request($period, self::DAY, '', 0, $parts);
        $this->assertStoredEqualsRaw($raw, $stored);

        $followingPages = isset($stored['followingPages']) ? $this->getRows($stored['followingPages']) : [];
        $selfRows = array_filter($followingPages, fn(array $row) => $row[0] === 'example.org/page/one.html');
        // only following actions alone count the reloads of the page as following it
        self::assertSame($parts === 'followingActions' ? 1 : 0, count($selfRows));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getParts(): array
    {
        $cases = [];
        foreach (
            [
                'internalReferrers',
                'followingActions',
                'followingActionsWithoutLoops',
                'followingActions,followingActionsWithoutLoops',
                'externalReferrers',
                'internalReferrers,followingActions',
                'all',
            ] as $parts
        ) {
            foreach (['day', 'month'] as $period) {
                $cases[$parts . ' ' . $period] = [$parts, $period];
            }
        }

        return $cases;
    }

    public function testViewAccessIsRequiredAndEnough(): void
    {
        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);

        FakeAccess::clearAccess(false, [], [1], 'viewer');
        self::assertSame(1, $this->request('day', self::DAY)['preprocessed']);

        FakeAccess::clearAccess(false, [], [], 'anonymous');
        $this->expectException(NoAccessException::class);
        $this->request('day', self::DAY);
    }

    /**
     * @dataProvider getFormats
     */
    public function testEachFormatRendersTheStoredResponseAsTheRawOne(string $format): void
    {
        // all rows, so no tie at a cut-off
        $raw = $this->requestFormat($format, 100);
        self::assertStringNotContainsString('preprocessed', $raw);

        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);
        $stored = $this->requestFormat($format, 100);

        if ($format === 'json') {
            $storedJson = json_decode($stored, true);
            self::assertIsArray($storedJson);
            self::assertSame(1, $storedJson['preprocessed']);
            self::assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $storedJson['preprocessedAt']);
            unset($storedJson['preprocessed'], $storedJson['preprocessedAt']);
            self::assertSame(json_decode($raw, true), $storedJson);
        } elseif ($format === 'xml') {
            $flags = '/\t<preprocessed>1<\/preprocessed>\n\t<preprocessedAt>\d{4}-\d\d-\d\d \d\d:\d\d:\d\d<\/preprocessedAt>\n/';
            self::assertMatchesRegularExpression($flags, $stored);
            self::assertSame($raw, preg_replace($flags, '', $stored));
        } else {
            // the report's nested arrays are not convertible to these formats, with or without the flag
            self::assertStringContainsString('Data structure returned is not convertible in the requested format', $raw);
            self::assertSame($raw, $stored);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public function getFormats(): array
    {
        $formats = [];
        foreach (['json', 'xml', 'csv', 'tsv', 'html'] as $format) {
            $formats[$format] = [$format];
        }

        return $formats;
    }

    private function requestFormat(string $format, int $limit): string
    {
        Cache::getTransientCache()->flushAll();
        $response = Request::processRequest('Transitions.getTransitionsForPageUrl', [
            'pageUrl' => self::PAGE_URL,
            'idSite' => 1,
            'period' => 'day',
            'date' => self::DAY,
            'limitBeforeGrouping' => $limit,
            'format' => $format,
        ]);
        self::assertIsString($response);

        return $response;
    }

    public function testPageTitlesAreAnsweredFromTheLogs(): void
    {
        $title = 'page title - page/one.html';
        $raw = $this->toArray(API::getInstance()->getTransitionsForPageTitle($title, 1, 'month', self::DAY));

        $this->archive($this->selectPage(self::PAGE_URL), 'month', self::DAY);

        self::assertSame($raw, $this->toArray(API::getInstance()->getTransitionsForPageTitle($title, 1, 'month', self::DAY)));
    }

    public function testALimitAboveTheRowLimitIsAnsweredFromTheLogs(): void
    {
        $raw = $this->request('month', self::DAY, '', 101);

        $this->archive($this->selectPage(self::PAGE_URL), 'month', self::DAY);

        self::assertSame($this->toArray($raw), $this->toArray($this->request('month', self::DAY, '', 101)));
        self::assertSame(1, $this->request('month', self::DAY, '', 100)['preprocessed']);
    }

    public function testALimitAboveALoweredRowLimitIsAnsweredFromTheLogs(): void
    {
        $raw = $this->request('month', self::DAY, '', 3);
        $this->archive($this->selectPage(self::PAGE_URL), 'month', self::DAY);

        // a lower limit than the reports were stored with
        Config::getInstance()->General['datatable_archiving_maximum_rows_transitions'] = 2;

        self::assertSame($this->toArray($raw), $this->toArray($this->request('month', self::DAY, '', 3)));
        self::assertSame(1, $this->request('month', self::DAY, '', 2)['preprocessed']);
    }

    /**
     * @dataProvider getPartlyCoveredPeriods
     */
    public function testAPeriodWithDaysWithoutRecordsIsAnsweredFromTheLogs(string $period, string $date): void
    {
        $raw = $this->request($period, $date);

        // the first day is archived before the page is selected, so it has no records of the page
        $this->archive(1, 'day', self::DAY);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, $period, $date);
        self::assertSame(1.0, $this->getDaysCounter($idPage, self::LATER_DAY));

        self::assertSame($this->toArray($raw), $this->toArray($this->request($period, $date)));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getPartlyCoveredPeriods(): array
    {
        return ['month' => ['month', self::DAY], 'range' => ['range', self::RANGE]];
    }

    public function testASegmentThatIsNotPreProcessedIsAnsweredFromTheLogs(): void
    {
        $raw = $this->request('day', self::DAY, 'browserCode==FF');

        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);

        self::assertSame($this->toArray($raw), $this->toArray($this->request('day', self::DAY, 'browserCode==FF')));
    }

    public function testAPageWithoutStoredDataIsAnsweredFromTheLogs(): void
    {
        $raw = $this->request('month', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL);

        $this->archive($this->selectPage(self::PAGE_URL), 'month', self::DAY);

        self::assertSame($this->toArray($raw), $this->toArray($this->request('month', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL)));
        // selected, but not archived since
        $this->selectPage(self::OTHER_PAGE_URL);
        self::assertSame($this->toArray($raw), $this->toArray($this->request('month', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL)));
    }

    public function testARemovedPageIsStillServedFromStoredData(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);
        $stored = $this->request('month', self::DAY);

        $service = StaticContainer::get(Service::class);
        foreach ($service->getPages(1) as $page) {
            $service->removePage(1, $page['idpage']);
        }

        self::assertSame($this->toArray($stored), $this->toArray($this->request('month', self::DAY)));
    }

    public function testStoredDataIsServedAfterTheLogsAreDeleted(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'day', self::DAY);
        $stored = $this->request('day', self::DAY);

        foreach (self::LOG_TABLES as $table) {
            Db::query('DELETE FROM ' . Common::prefixTable($table) . ' WHERE idsite = 1');
        }

        self::assertSame($this->toArray($stored), $this->toArray($this->request('day', self::DAY)));

        $this->expectExceptionMessage('NoDataForAction');
        $this->request('day', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL);
    }

    /**
     * @dataProvider getLongerPeriods
     */
    public function testNoLogTableIsReadWhenServedFromStoredData(string $period, string $date): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, $period, $date);
        $stored = $this->request($period, $date);

        $served = $this->withoutLogTables(fn() => $this->request($period, $date));

        self::assertSame($this->toArray($stored), $this->toArray($served));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getLongerPeriods(): array
    {
        return [
            'week' => ['week', self::DAY],
            'month' => ['month', self::DAY],
            'year' => ['year', self::DAY],
            'range' => ['range', self::RANGE],
        ];
    }

    public function testNoArchivingIsLaunched(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'day', self::DAY);
        $archiveRows = $this->countArchiveRows();

        // stored for the day, not archived for the month, so answered from the logs
        $this->request('day', self::DAY);
        $month = $this->request('month', self::DAY);
        $this->request('month', self::DAY, self::SEGMENT);

        self::assertArrayNotHasKey('preprocessed', $month);
        self::assertSame($archiveRows, $this->countArchiveRows());
    }

    public function testALongerPeriodIsServedFromItsOwnArchiveOnly(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);
        $stored = $this->request('month', self::DAY);

        // a day archive rebuilt with other visits, and another one gone
        Db::query(
            'UPDATE ' . Common::prefixTable('archive_numeric_2010_03') . " SET value = value + 5 WHERE period = 1 AND date1 = ? AND name = 'nb_visits'",
            [self::DAY]
        );
        Db::query('DELETE FROM ' . Common::prefixTable('archive_numeric_2010_03') . ' WHERE period = 1 AND date1 = ?', [self::LATER_DAY]);

        self::assertSame($this->toArray($stored), $this->toArray($this->withoutLogTables(fn() => $this->request('month', self::DAY))));
    }

    public function testStoredMonthsAreServedAfterOldDayReportsArePurged(): void
    {
        $rawDay = $this->toArray($this->request('day', self::DAY));
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);
        $month = $this->request('month', self::DAY);

        // keeps the month and year reports, deletes the day and week ones with their visits
        (new ReportsPurger(1, false, [3, 4], false, [], 1000))->purgeData();

        self::assertSame($this->toArray($month), $this->toArray($this->withoutLogTables(fn() => $this->request('month', self::DAY))));
        // with cron archiving; without it, a single day is built again in the request
        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;
        self::assertSame($rawDay, $this->toArray($this->request('day', self::DAY)));
    }

    public function testAYearArchivedAfterOldDayReportsArePurgedIsServed(): void
    {
        $raw = $this->request('year', self::DAY);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);

        // keeps the month and year reports, deletes the day and week ones with their visits
        (new ReportsPurger(1, false, [3, 4], false, [], 1000))->purgeData();
        $this->archive($idPage, 'year', self::DAY);

        $this->assertStoredEqualsRaw($raw, $this->withoutLogTables(fn() => $this->request('year', self::DAY)));
    }

    public function testTheRowsServedAreValidatedAndCappedByTheStoredRows(): void
    {
        self::assertSame(100, Records::getMaxRowsServed(1));

        Config::setSetting('Transitions', 'preprocessed_max_rows_served', '50');
        Config::setSetting('Transitions_1', 'preprocessed_max_rows_served', 20);
        self::assertSame(20, Records::getMaxRowsServed(1));
        self::assertSame(50, Records::getMaxRowsServed(2));

        Config::getInstance()->General['datatable_archiving_maximum_rows_transitions'] = 10;
        self::assertSame(10, Records::getMaxRowsServed(1));

        foreach (['0', 'many', '-3', '1.5'] as $value) {
            Config::setSetting('Transitions_1', 'preprocessed_max_rows_served', $value);
            try {
                Records::getMaxRowsServed(1);
                self::fail('Expected an exception for ' . $value);
            } catch (\Exception $e) {
                self::assertSame('Invalid value for [Transitions_1] preprocessed_max_rows_served: expected an integer >= 1.', $e->getMessage());
            }
        }
    }

    public function testAPageWhoseActionWasDeletedAsUnusedGetsZeroRecordsAfterwards(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'day', self::DAY);
        $stored = $this->request('day', self::DAY);

        $this->deleteLogsAndUnusedActions();
        self::assertNull(StaticContainer::get(Service::class)->getIdActionForUrl(self::PAGE_URL));
        self::assertSame($this->toArray($stored), $this->toArray($this->request('day', self::DAY)));

        // a later day with visits, but none to the page
        $this->trackPageview(self::LATER_DAY . ' 20:00:00', self::OTHER_PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);

        self::assertSame(0.0, $this->getStoredNumeric($idPage, Records::PAGEVIEWS, self::LATER_DAY));
        self::assertSame(1.0, $this->getDaysCounter($idPage, self::LATER_DAY));
        $month = $this->request('month', self::DAY);
        self::assertSame(1, $month['preprocessed']);
        self::assertSame($this->withoutDates($stored), $this->withoutDates($month));
    }

    public function testThePagesOfEveryIdActionAPageWasTrackedUnderAreServed(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'day', self::DAY);
        $first = $this->request('day', self::DAY);

        // tracked again twice, each time after its logs and its action were deleted
        $this->deleteLogsAndUnusedActions();
        $this->trackPageview(self::LATER_DAY . ' 20:00:00', self::PAGE_URL);
        $secondIdAction = StaticContainer::get(Service::class)->getIdActionForUrl(self::PAGE_URL);
        $this->archive($idPage, 'day', self::LATER_DAY);
        $second = $this->request('day', self::LATER_DAY);
        self::assertSame(1, $second['pageMetrics']['pageviews']);

        $this->deleteLogsAndUnusedActions();
        $this->trackPageview('2010-03-20 20:00:00', self::PAGE_URL);
        self::assertNotSame($secondIdAction, StaticContainer::get(Service::class)->getIdActionForUrl(self::PAGE_URL));
        $this->archive($idPage, 'month', self::DAY);

        self::assertSame($this->toArray($first), $this->toArray($this->request('day', self::DAY)));
        self::assertSame($this->toArray($second), $this->toArray($this->request('day', self::LATER_DAY)));
        $month = $this->request('month', self::DAY);
        self::assertSame(1, $month['preprocessed']);
        self::assertSame($first['pageMetrics']['pageviews'] + 2, $month['pageMetrics']['pageviews']);
    }

    public function testAUrlWithParametersExcludedSinceItWasTrackedIsAnsweredFromTheLogs(): void
    {
        $variantUrl = self::PAGE_URL . '?ref=newsletter';
        $this->trackPageview(self::DAY . ' 20:00:00', $variantUrl);
        $variantRaw = $this->request('day', self::DAY, '', 0, 'all', $variantUrl);

        SitesManagerAPI::getInstance()->updateSite(1, null, null, null, null, null, null, null, 'ref');
        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);
        self::assertSame(1, $this->request('day', self::DAY)['preprocessed']);

        self::assertSame($this->toArray($variantRaw), $this->toArray($this->request('day', self::DAY, '', 0, 'all', $variantUrl)));
    }

    public function testARemovedPageIsServedAfterItsActionIsDeletedAsUnused(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'day', self::DAY);
        $stored = $this->request('day', self::DAY);
        self::assertSame(1, $stored['preprocessed']);

        $service = StaticContainer::get(Service::class);
        foreach ($service->getPages(1) as $page) {
            $service->removePage(1, $page['idpage']);
        }
        $this->deleteLogsAndUnusedActions();
        self::assertNull($service->getIdActionForUrl(self::PAGE_URL));

        self::assertSame($this->toArray($stored), $this->toArray($this->request('day', self::DAY)));
    }

    public function testRangesOfMorePeriodsThanSummedAreAnsweredFromTheLogs(): void
    {
        $raw = $this->request('range', self::RANGE);
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'range', self::RANGE);
        self::assertSame(1, $this->request('range', self::RANGE)['preprocessed']);

        // the range has 3 subperiods
        StaticContainer::getContainer()->set(StoredRecordsLoader::class, new StoredRecordsLoader(
            StaticContainer::get(StoredRecordsReader::class),
            StaticContainer::get(Service::class),
            StaticContainer::get(LockBackend::class),
            StaticContainer::get(LoggerInterface::class),
            200,
            2
        ));

        self::assertSame($this->toArray($raw), $this->toArray($this->request('range', self::RANGE)));
    }

    public function testAnInvalidRowsServedSettingOnlyFailsStoredReports(): void
    {
        $rawOther = $this->request('day', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL);
        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);
        Config::setSetting('Transitions', 'preprocessed_max_rows_served', 'many');

        self::assertSame($this->toArray($rawOther), $this->toArray($this->request('day', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL)));
        self::assertNotEmpty($this->getRows(OverlayAPI::getInstance()->getFollowingPages(self::OTHER_PAGE_URL, 1, 'day', self::DAY)));

        $this->expectExceptionMessage('Invalid value for [Transitions] preprocessed_max_rows_served: expected an integer >= 1.');
        $this->request('day', self::DAY);
    }

    public function testAStorageRowLimitBelowOneIsRejected(): void
    {
        Config::getInstance()->General['datatable_archiving_maximum_rows_transitions'] = 0;

        $this->expectExceptionMessage('Invalid value for [General] datatable_archiving_maximum_rows_transitions: expected an integer >= 1.');
        Records::getMaxRows(1);
    }

    public function testADayWhoseListsArePurgedIsAnsweredFromTheLogs(): void
    {
        $rawDay = $this->toArray($this->request('day', self::DAY));
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'month', self::DAY);

        // a plugin can keep more metrics, here the page's numeric records, while the reports are deleted
        $metricsToKeep = ['nb_visits'];
        foreach (Records::NUMERICS as $part) {
            $metricsToKeep[] = Records::getRecordName($idPage, $part);
        }
        (new ReportsPurger(1, true, [3, 4], false, $metricsToKeep, 1000))->purgeData();
        self::assertSame(1.0, $this->getDaysCounter($idPage, self::DAY));

        self::assertSame($rawDay, $this->toArray($this->request('day', self::DAY)));
        self::assertSame(1, $this->request('month', self::DAY)['preprocessed']);
    }

    public function testSubperiodsBeforeTheSiteWasCreatedNeedNoRecords(): void
    {
        $range = '2010-02-27,2010-03-14';
        $raw = $this->request('range', $range);

        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'range', $range);

        $this->assertStoredEqualsRaw($raw, $this->request('range', $range));
    }

    public function testWeeksOfSitesArchivedWithoutVisitsAreServed(): void
    {
        EventDispatcher::getInstance()->addObserver('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
        });
        $raw = $this->request('week', self::LATER_DAY);

        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'week', self::LATER_DAY);
        // a day without visits got the page's records too
        self::assertNull($this->getDaysCounter($idPage, '2010-03-09'));
        self::assertSame(0.0, $this->getStoredNumeric($idPage, Records::PAGEVIEWS, '2010-03-09'));

        $this->assertStoredEqualsRaw($raw, $this->request('week', self::LATER_DAY));
        $this->expectExceptionMessage('NoDataForAction');
        $this->withoutLogTables(fn() => $this->request('day', '2010-03-09'));
    }

    /**
     * @dataProvider getUnnamedReferrerVisits
     */
    public function testUnnamedReferrersAreLeftOutOfTheDetailsAsByTheLogQuery(int $unnamedVisits): void
    {
        $pageUrl = 'http://example.org/landing.html';
        $visitor = 0;
        foreach (['http://a.example/x' => 3, 'http://b.example/y' => 2, 'http://unnamed.example/z' => $unnamedVisits] as $referrer => $visits) {
            for ($i = 0; $i < $visits; $i++) {
                $visitor++;
                $tracker = Fixture::getTracker(1, self::DAY . ' 18:00:00', $defaultInit = true);
                $tracker->setIp('10.0.0.' . $visitor);
                $tracker->setNewVisitorId();
                $tracker->setUrlReferrer($referrer);
                $tracker->setUrl($pageUrl);
                Fixture::checkResponse($tracker->doTrackPageView('landing'));
            }
        }
        // as with the referrer anonymised entirely
        Db::query('UPDATE ' . Common::prefixTable('log_visit') . " SET referer_url = '' WHERE referer_url LIKE 'http://unnamed.example/%'");
        $raw = $this->request('day', self::DAY, '', 2, 'externalReferrers', $pageUrl);

        $this->archive($this->selectPage($pageUrl), 'day', self::DAY);

        $stored = $this->request('day', self::DAY, '', 2, 'externalReferrers', $pageUrl);
        self::assertSame(1, $stored['preprocessed']);
        self::assertSame($raw['referrers'], $stored['referrers']);
    }

    /**
     * @return array<string, array{int}>
     */
    public function getUnnamedReferrerVisits(): array
    {
        return ['below the named ones' => [1], 'above the named ones' => [4]];
    }

    public function testTheSiteHasNoPagesWithoutTheTable(): void
    {
        $this->selectPage(self::PAGE_URL);
        $dao = StaticContainer::get(Dao::class);
        self::assertTrue($dao->hasPagesForSite(1));

        $table = Common::prefixTable(Dao::TABLE_NAME);
        Db::query('RENAME TABLE ' . $table . ' TO ' . $table . '_hidden');
        try {
            self::assertFalse($dao->hasPagesForSite(1));
        } finally {
            Db::query('RENAME TABLE ' . $table . '_hidden TO ' . $table);
        }
    }

    public function testAPageWithoutPageviewsHasNoDataFromStoredData(): void
    {
        // tracked on the first day only
        $pageUrl = 'http://example.org/page3.html';
        $idPage = $this->selectPage($pageUrl);
        $this->archive($idPage, 'day', self::LATER_DAY);
        self::assertSame(1.0, $this->getDaysCounter($idPage, self::LATER_DAY));

        $this->expectExceptionMessage('NoDataForAction');
        $this->withoutLogTables(fn() => $this->request('day', self::LATER_DAY, '', 0, 'all', $pageUrl));
    }

    public function testThePeriodLimitAppliesOnlyToTheLogQuery(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'year', self::DAY);
        $this->archive($idPage, 'range', self::RANGE);
        Config::setSetting('Transitions_1', 'max_period_allowed', 'day');

        self::assertFalse(API::getInstance()->isPeriodAllowed(1, 'year', self::DAY));
        self::assertSame(1, $this->request('year', self::DAY)['preprocessed']);
        self::assertSame(1, $this->request('range', self::RANGE)['preprocessed']);

        $requests = [
            'page without stored data' => fn() => $this->request('year', self::DAY, '', 0, 'all', self::OTHER_PAGE_URL),
            'limit above the row limit' => fn() => $this->request('year', self::DAY, '', 101),
            'segment not pre-processed' => fn() => $this->request('year', self::DAY, 'browserCode==FF'),
            // the week after has neither records nor visits
            'range not covered' => fn() => $this->request('range', '2010-03-06,2010-03-21'),
        ];
        foreach ($requests as $case => $request) {
            try {
                $request();
                self::fail('PeriodNotAllowed expected: ' . $case);
            } catch (\Exception $e) {
                self::assertSame('PeriodNotAllowed', $e->getMessage(), $case);
            }
        }
    }

    public function testPreprocessedAtIsTheTimeOfTheOldestArchive(): void
    {
        $idPage = $this->selectPage(self::PAGE_URL);
        $this->archive($idPage, 'range', self::RANGE);

        // the week of the later day is one of the range's subperiods
        Db::query(
            'UPDATE ' . Common::prefixTable('archive_numeric_2010_03') . " SET ts_archived = '2011-01-02 03:04:05'"
            . ' WHERE idsite = 1 AND period = 2 AND date1 = ?',
            ['2010-03-08']
        );

        self::assertSame('2011-01-02 03:04:05', $this->request('range', self::RANGE)['preprocessedAt']);
    }

    public function testOverlayIsUnchangedByDefault(): void
    {
        $raw = $this->getFollowingPages();

        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);

        self::assertSame($raw, $this->getFollowingPages());
        self::assertSame([], $this->withoutLogTables(fn() => $this->getFollowingPages()));
    }

    public function testOverlayReadsStoredDataWithASmallLimit(): void
    {
        Config::getInstance()->General['overlay_following_pages_limit'] = 100;
        $raw = $this->getFollowingPages();

        $this->archive($this->selectPage(self::PAGE_URL), 'day', self::DAY);

        $stored = $this->withoutLogTables(fn() => $this->getFollowingPages());
        sort($raw);
        sort($stored);
        self::assertSame($raw, $stored);
    }

    public function testTheJsGlobalTellsWhetherTheSiteHasPreprocessedPages(): void
    {
        $_GET['idSite'] = 1;
        try {
            self::assertStringContainsString('piwik.transitionsHasPreprocessedPages = false;', $this->getJsGlobals());

            $this->selectPage(self::PAGE_URL);
            self::assertStringContainsString('piwik.transitionsHasPreprocessedPages = true;', $this->getJsGlobals());

            FakeAccess::clearAccess(false, [], [], 'anonymous');
            self::assertStringContainsString('piwik.transitionsHasPreprocessedPages = false;', $this->getJsGlobals());
        } finally {
            unset($_GET['idSite']);
        }
    }

    private function getStoredNumeric(int $idPage, string $part, string $day): ?float
    {
        $value = Db::fetchOne(
            'SELECT value FROM ' . Common::prefixTable('archive_numeric_2010_03') . ' WHERE idsite = 1 AND period = 1 AND date1 = ? AND name = ?',
            [$day, Records::getRecordName($idPage, $part)]
        );

        return $value === false ? null : (float) $value;
    }

    private function getDaysCounter(int $idPage, string $day): ?float
    {
        $name = Records::getRecordName($idPage, Records::DAYS);
        $values = Archive::build(1, 'day', $day)->getNumeric([$name]);

        return is_array($values) && isset($values[$name]) ? (float) $values[$name] : null;
    }

    private function getJsGlobals(): string
    {
        $out = '';
        (new Transitions())->addJsGlobalVariables($out);

        return $out;
    }

    /**
     * @return list<array{0: mixed, 1: mixed}>
     */
    private function getFollowingPages(): array
    {
        return $this->getRows(OverlayAPI::getInstance()->getFollowingPages(self::PAGE_URL, 1, 'day', self::DAY));
    }

    /**
     * @param array<string, mixed> $raw
     * @param array<string, mixed> $stored
     */
    private function assertStoredEqualsRaw(array $raw, array $stored): void
    {
        self::assertArrayNotHasKey('preprocessed', $raw);
        self::assertSame(1, $stored['preprocessed']);
        self::assertMatchesRegularExpression('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $stored['preprocessedAt']);
        unset($stored['preprocessed'], $stored['preprocessedAt']);

        self::assertSame(array_keys($raw), array_keys($stored));
        foreach ($raw as $key => $value) {
            if ($value instanceof DataTable) {
                self::assertInstanceOf(DataTable::class, $stored[$key]);
                self::assertSame($this->ignoreTies($this->getRows($value)), $this->ignoreTies($this->getRows($stored[$key])), $key);
                $this->assertRankedAsTheLogQuery($this->getRows($stored[$key]), $key);
            } elseif ($key === 'referrers') {
                self::assertSame(count($value), count($stored[$key]));
                foreach ($value as $i => $referrer) {
                    $storedReferrer = $stored[$key][$i];
                    self::assertSame(array_keys($referrer), array_keys($storedReferrer), $referrer['label']);
                    self::assertSame([$referrer['label'], $referrer['shortName'], (int) $referrer['visits']], [$storedReferrer['label'], $storedReferrer['shortName'], $storedReferrer['visits']]);

                    if (isset($referrer['details'])) {
                        $rows = fn(array $details) => array_map(fn(array $detail) => [$detail['label'], (int) $detail['referrals']], $details);
                        self::assertSame($this->ignoreTies($rows($referrer['details'])), $this->ignoreTies($rows($storedReferrer['details'])), $referrer['label']);
                        $this->assertRankedAsTheLogQuery($rows($storedReferrer['details']), $referrer['label']);
                    }
                }
            } else {
                self::assertSame($value, $stored[$key], $key);
            }
        }
    }

    /**
     * The rows come by count, highest first, and `Others` last.
     *
     * @param list<array{0: mixed, 1: mixed}> $rows
     */
    private function assertRankedAsTheLogQuery(array $rows, string $message): void
    {
        $counts = [];
        foreach ($rows as $index => [$label, $count]) {
            if ($label === 'Others') {
                self::assertSame(count($rows) - 1, $index, $message);
            } else {
                $counts[] = (int) $count;
            }
        }

        $sorted = $counts;
        rsort($sorted);
        self::assertSame($sorted, $counts, $message);
    }

    /**
     * Rows of equal counts at the cut-off may differ, as the log query and the stored data order them differently.
     *
     * @param list<array{0: mixed, 1: mixed}> $rows
     * @return array<string, mixed>
     */
    private function ignoreTies(array $rows): array
    {
        $others = null;
        $named = [];
        foreach ($rows as [$label, $count]) {
            if ($label === 'Others') {
                $others = (int) $count;
            } else {
                $named[] = [(string) $label, (int) $count];
            }
        }

        $cutOff = $others === null || empty($named) ? null : min(array_column($named, 1));
        $aboveCutOff = [];
        $atCutOff = 0;
        foreach ($named as [$label, $count]) {
            if ($count === $cutOff) {
                $atCutOff++;
            } else {
                $aboveCutOff[$label] = $count;
            }
        }
        ksort($aboveCutOff);

        return ['rows' => $aboveCutOff, 'atCutOff' => $atCutOff, 'cutOff' => $cutOff, 'others' => $others];
    }

    /**
     * @return list<array{0: mixed, 1: mixed}>
     */
    private function getRows(DataTable $table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            $rows[] = [$row->getColumn('label'), $row->getColumn('referrals')];
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private function toArray(array $report): array
    {
        foreach ($report as $key => $value) {
            if ($value instanceof DataTable) {
                $report[$key] = $this->getRows($value);
            }
        }

        return $report;
    }

    /**
     * @return array<string, mixed>
     */
    private function request(
        string $period,
        string $date,
        string $segment = '',
        int $limit = 0,
        string $parts = 'all',
        string $pageUrl = self::PAGE_URL
    ): array {
        Cache::getTransientCache()->flushAll();

        return API::getInstance()->getTransitionsForAction($pageUrl, 'url', 1, $period, $date, $segment, $limit, $parts);
    }

    /**
     * Deletes the raw data of the website, then the actions no log row uses any more, as the scheduled log deletion does.
     */
    private function deleteLogsAndUnusedActions(): void
    {
        foreach (self::LOG_TABLES as $table) {
            Db::query('DELETE FROM ' . Common::prefixTable($table) . ' WHERE idsite = 1');
        }
        StaticContainer::get(RawLogDao::class)->deleteUnusedLogActions();
    }

    private function trackPageview(string $dateTime, string $url): void
    {
        $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('title'));
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private function withoutDates(array $report): array
    {
        unset($report['date'], $report['preprocessedAt']);

        return $this->toArray($report);
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
        Cache::getTransientCache()->flushAll();
        Archive::build(1, $period, $date, $segment)->getNumeric(['nb_visits', Records::getRebuildReportName($idPage)]);
        Cache::getTransientCache()->flushAll();
    }

    /**
     * @template T
     * @param callable(): T $request
     * @return T
     */
    private function withoutLogTables(callable $request)
    {
        foreach (self::LOG_TABLES as $table) {
            Db::query('RENAME TABLE ' . Common::prefixTable($table) . ' TO ' . Common::prefixTable($table . '_hidden'));
        }

        try {
            return $request();
        } finally {
            foreach (self::LOG_TABLES as $table) {
                Db::query('RENAME TABLE ' . Common::prefixTable($table . '_hidden') . ' TO ' . Common::prefixTable($table));
            }
        }
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

PreprocessedReportTest::$fixture = new SomeVisitsManyPageviewsWithTransitions();
