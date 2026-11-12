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
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\EventDispatcher;
use Piwik\NoAccessException;
use Piwik\Plugins\Overlay\API as OverlayAPI;
use Piwik\Plugins\PrivacyManager\ReportsPurger;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\Dao;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
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
    private const LOG_TABLES = ['log_visit', 'log_link_visit_action', 'log_conversion', 'log_conversion_item'];

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

        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, $period, $date);

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

        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, $period, $date, self::SEGMENT);

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

        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, $period, self::DAY);

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

        // the records keep 100 rows, but the current limit is lower
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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, $period, $date);
        self::assertSame(1.0, $this->getDaysCounter($idAction, self::LATER_DAY));

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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'month', self::DAY);
        $stored = $this->request('month', self::DAY);

        $service = StaticContainer::get(Service::class);
        foreach ($service->getPages(1) as $page) {
            $service->removePage(1, $page['idpage']);
        }

        self::assertSame($this->toArray($stored), $this->toArray($this->request('month', self::DAY)));
    }

    public function testStoredDataIsServedAfterTheLogsAreDeleted(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'day', self::DAY);
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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, $period, $date);
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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'day', self::DAY);
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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'month', self::DAY);
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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'month', self::DAY);
        $month = $this->request('month', self::DAY);

        // keeps the month and year reports, deletes the day and week ones with their visits
        (new ReportsPurger(1, false, [3, 4], false, [], 1000))->purgeData();

        self::assertSame($this->toArray($month), $this->toArray($this->withoutLogTables(fn() => $this->request('month', self::DAY))));
        self::assertSame($rawDay, $this->toArray($this->request('day', self::DAY)));
    }

    public function testADayWhoseListsArePurgedIsAnsweredFromTheLogs(): void
    {
        $rawDay = $this->toArray($this->request('day', self::DAY));
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'month', self::DAY);

        // a plugin can keep more metrics, here the page's numeric records, while the reports are deleted
        $metricsToKeep = ['nb_visits'];
        foreach (Records::NUMERICS as $part) {
            $metricsToKeep[] = Records::getRecordName($idAction, $part);
        }
        (new ReportsPurger(1, true, [3, 4], false, $metricsToKeep, 1000))->purgeData();
        self::assertSame(1.0, $this->getDaysCounter($idAction, self::DAY));

        self::assertSame($rawDay, $this->toArray($this->request('day', self::DAY)));
        self::assertSame(1, $this->request('month', self::DAY)['preprocessed']);
    }

    public function testSubperiodsBeforeTheSiteWasCreatedNeedNoRecords(): void
    {
        $range = '2010-02-27,2010-03-14';
        $raw = $this->request('range', $range);

        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'range', $range);

        $this->assertStoredEqualsRaw($raw, $this->request('range', $range));
    }

    public function testWeeksOfSitesArchivedWithoutVisitsAreServed(): void
    {
        EventDispatcher::getInstance()->addObserver('Archiving.getIdSitesToArchiveWhenNoVisits', function (&$idSites) {
            $idSites[] = 1;
        });
        $raw = $this->request('week', self::LATER_DAY);

        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'week', self::LATER_DAY);
        // a day without visits got the page's records too
        self::assertNull($this->getDaysCounter($idAction, '2010-03-09'));
        self::assertSame(0.0, $this->getStoredNumeric($idAction, Records::PAGEVIEWS, '2010-03-09'));

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
        $idAction = $this->selectPage($pageUrl);
        $this->archive($idAction, 'day', self::LATER_DAY);
        self::assertSame(1.0, $this->getDaysCounter($idAction, self::LATER_DAY));

        $this->expectExceptionMessage('NoDataForAction');
        $this->withoutLogTables(fn() => $this->request('day', self::LATER_DAY, '', 0, 'all', $pageUrl));
    }

    public function testThePeriodLimitAppliesOnlyToTheLogQuery(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'year', self::DAY);
        $this->archive($idAction, 'range', self::RANGE);
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
        $idAction = $this->selectPage(self::PAGE_URL);
        $this->archive($idAction, 'range', self::RANGE);

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

    private function getStoredNumeric(int $idAction, string $part, string $day): ?float
    {
        $value = Db::fetchOne(
            'SELECT value FROM ' . Common::prefixTable('archive_numeric_2010_03') . ' WHERE idsite = 1 AND period = 1 AND date1 = ? AND name = ?',
            [$day, Records::getRecordName($idAction, $part)]
        );

        return $value === false ? null : (float) $value;
    }

    private function getDaysCounter(int $idAction, string $day): ?float
    {
        $name = Records::getRecordName($idAction, Records::DAYS);
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
            } elseif ($key === 'referrers') {
                self::assertSame(count($value), count($stored[$key]));
                foreach ($value as $i => $referrer) {
                    $storedReferrer = $stored[$key][$i];
                    self::assertSame(array_keys($referrer), array_keys($storedReferrer), $referrer['label']);
                    self::assertSame([$referrer['label'], $referrer['shortName'], (int) $referrer['visits']], [$storedReferrer['label'], $storedReferrer['shortName'], $storedReferrer['visits']]);

                    if (isset($referrer['details'])) {
                        $rows = fn(array $details) => array_map(fn(array $detail) => [$detail['label'], (int) $detail['referrals']], $details);
                        self::assertSame($this->ignoreTies($rows($referrer['details'])), $this->ignoreTies($rows($storedReferrer['details'])), $referrer['label']);
                    }
                }
            } else {
                self::assertSame($value, $stored[$key], $key);
            }
        }
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

    private function selectPage(string $url): int
    {
        API::getInstance()->addPreprocessedPage(1, $url);
        $idAction = StaticContainer::get(Service::class)->getIdActionForUrl($url);
        self::assertNotNull($idAction);

        return $idAction;
    }

    /**
     * Archives the period as browser archiving does, with the page's records and the visits.
     */
    private function archive(int $idAction, string $period, string $date, string $segment = ''): void
    {
        Cache::getTransientCache()->flushAll();
        Archive::build(1, $period, $date, $segment)->getNumeric(['nb_visits', Records::getRebuildReportName($idAction)]);
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
