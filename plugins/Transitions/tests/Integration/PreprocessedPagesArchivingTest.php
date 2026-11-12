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
use Piwik\ArchiveProcessor\Loader;
use Piwik\ArchiveProcessor\Parameters;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Cache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Piwik;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Segment;
use Piwik\Site;
use Piwik\Tests\Fixtures\SomeVisitsManyPageviewsWithTransitions;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Action;

/**
 * @group Transitions
 * @group PreprocessedPagesArchivingTest
 * @group Plugins
 */
class PreprocessedPagesArchivingTest extends IntegrationTestCase
{
    /**
     * @var SomeVisitsManyPageviewsWithTransitions
     */
    public static $fixture;

    private const PAGE_URL = 'http://example.org/page/one.html';
    private const OTHER_PAGE_URL = 'http://example.org/sub/dir/page2.html';
    private const DAY = '2010-03-06';
    private const LATER_DAY = '2010-03-14';
    private const SEGMENT = 'referrerType==website';

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        // tracking in the past needs a token
        $fixture->createSuperUser = true;
    }

    public function tearDown(): void
    {
        unset($_GET['trigger'], $_GET['pluginOnly'], $_GET['requestedReport']);

        parent::tearDown();
    }

    public function testNoRecordsForAWebsiteWithoutSelectedPages(): void
    {
        $this->archive('day', self::DAY);

        self::assertSame([], $this->getStoredRecordNames(1, self::DAY));
    }

    public function testDayRecordsAreBuiltForSelectedPagesOnly(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);

        $this->archive('day', self::DAY);

        self::assertSame($this->getExpectedRecordNames($idAction), $this->getStoredRecordNames(1, self::DAY));
    }

    public function testDayRecordsEqualTheRawLogQuery(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);

        self::assertSame($this->getRawReport('day', self::DAY), $this->getStoredReport($idAction, 'day', self::DAY));
    }

    public function testDayRecordsOfASegmentEqualTheRawLogQuery(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);

        $stored = $this->getStoredReport($idAction, 'day', self::DAY, self::SEGMENT);

        self::assertSame($this->getRawReport('day', self::DAY, self::SEGMENT), $stored);
        self::assertNotSame($this->getStoredReport($idAction, 'day', self::DAY), $stored);
    }

    public function testDayRecordsEqualTheRawLogQueryForMorePagesThanOneQueryTakes(): void
    {
        // one visit through 17 pages, more than each query takes at once
        $pageUrls = [];
        $tracker = Fixture::getTracker(1, self::DAY . ' 15:00:00', $defaultInit = true);
        $tracker->setUrlReferrer('http://a.example/x');
        for ($i = 0; $i < 17; $i++) {
            $pageUrls[$i] = 'http://example.org/many/' . $i . '.html';
            $tracker->setForceVisitDateTime(Date::factory(self::DAY . ' 15:00:00')->addPeriod($i, 'minute')->getDatetime());
            $tracker->setUrl($pageUrls[$i]);
            Fixture::checkResponse($tracker->doTrackPageView('many ' . $i));
        }

        $idActions = [];
        foreach ($pageUrls as $i => $pageUrl) {
            $idActions[$i] = $this->selectPage($pageUrl);
        }

        foreach ([0, 8, 12, 16] as $i) {
            self::assertSame($this->getRawReport('day', self::DAY, '', $pageUrls[$i]), $this->getStoredReport($idActions[$i], 'day', self::DAY), $pageUrls[$i]);
        }
    }

    public function testNumericRecordsAreWrittenWhenThePageHasNoData(): void
    {
        // tracked on the first day only, while the website has visits on the later day too
        $idAction = $this->selectPage('http://example.org/page3.html');

        $this->archive('day', self::LATER_DAY);

        $numerics = Db::fetchAll(
            'SELECT name, value FROM ' . Common::prefixTable('archive_numeric_2010_03')
            . ' WHERE idsite = 1 AND period = 1 AND date1 = ? AND name LIKE ? ORDER BY name',
            [self::LATER_DAY, 'Transitions\_%']
        );
        $expected = [];
        foreach (Records::NUMERICS as $numeric) {
            // the day was archived for the page, although it has no data
            $value = $numeric === Records::DAYS ? 1.0 : 0.0;
            $expected[] = ['name' => Records::getRecordName($idAction, $numeric), 'value' => $value];
        }
        usort($expected, fn($a, $b) => strcmp($a['name'], $b['name']));

        self::assertSame($expected, $numerics);
    }

    public function testTheDaysCounterCountsTheDaysWithRecords(): void
    {
        $this->trackEntryFromWebsite('2010-03-05 15:00:00', 'http://a.example/x');
        $idAction = $this->selectPage(self::PAGE_URL);

        $this->archive('week', self::DAY);

        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::DAYS, 1, '2010-03-05'));
        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::DAYS, 1, self::DAY));
        // no visits on that day, so nothing was archived
        self::assertNull($this->getStoredNumeric($idAction, Records::DAYS, 1, '2010-03-07'));
        self::assertSame(2.0, $this->getStoredNumeric($idAction, Records::DAYS, 2, '2010-03-01'));
    }

    public function testTheDaysCounterLeavesOutDaysArchivedBeforeThePageWasSelected(): void
    {
        $this->archive('day', self::DAY);
        $idAction = $this->selectPage(self::PAGE_URL);

        $this->archive('month', self::DAY);

        self::assertNull($this->getStoredNumeric($idAction, Records::DAYS, 1, self::DAY));
        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::DAYS, 1, self::LATER_DAY));
        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::DAYS, 3, '2010-03-01'));
    }

    public function testAPageTrackedAfterItWasAddedGetsItsEarlierDaysRebuiltWithZeros(): void
    {
        $earlierDay = Date::factory('today')->subDay(3)->toString();
        $laterDay = Date::factory('today')->subDay(1)->toString();
        $pageUrl = 'http://example.org/launched-later.html';
        $this->trackPageview($earlierDay . ' 10:00:00', 'http://example.org/page/one.html');
        API::getInstance()->addPreprocessedPage(1, $pageUrl);

        // not tracked yet, so not archived
        $this->archive('day', $earlierDay);

        $this->trackPageview($laterDay . ' 10:00:00', $pageUrl);
        $this->archive('day', $laterDay);
        $idAction = StaticContainer::get(Service::class)->getIdActionForUrl($pageUrl);
        self::assertNotNull($idAction);
        self::assertNull($this->getStoredNumeric($idAction, Records::DAYS, 1, $earlierDay));
        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::PAGEVIEWS, 1, $laterDay));

        // finding the idaction scheduled a rebuild, which core:archive runs
        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();
        $_GET['trigger'] = 'archivephp';
        $_GET['pluginOnly'] = '1';
        $_GET['requestedReport'] = Records::getRebuildReportName($idAction);
        $params = new Parameters(new Site(1), PeriodFactory::build('day', $earlierDay), new Segment('', [1]));
        $params->setRequestedPlugin('Transitions');
        $params->setArchiveOnlyReport(Records::getRebuildReportName($idAction));
        (new Loader($params))->prepareArchive('Transitions');

        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::DAYS, 1, $earlierDay));
        self::assertSame(0.0, $this->getStoredNumeric($idAction, Records::PAGEVIEWS, 1, $earlierDay));
    }

    public function testMonthAndYearEqualTheRawLogQuery(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);

        self::assertSame($this->getRawReport('month', self::DAY), $this->getStoredReport($idAction, 'month', self::DAY));
        self::assertSame($this->getRawReport('year', self::DAY), $this->getStoredReport($idAction, 'year', self::DAY));
        self::assertSame($this->getRawReport('week', self::LATER_DAY), $this->getStoredReport($idAction, 'week', self::LATER_DAY));
        self::assertSame($this->getRawReport('range', '2010-03-05,2010-03-14'), $this->getStoredReport($idAction, 'range', '2010-03-05,2010-03-14'));
    }

    public function testListsOfSeveralPagesKeepTheirOwnRowsAndOthers(): void
    {
        Config::getInstance()->General['datatable_archiving_maximum_rows_transitions'] = 1;
        $pageUrls = [self::PAGE_URL, 'http://example.org/the/third_page.html?foo=bar'];
        $raw = [];
        foreach ($pageUrls as $pageUrl) {
            // before archiving, so it comes from the logs
            $raw[$pageUrl] = $this->getRawReport('day', self::DAY, '', $pageUrl, 1);
        }

        $idActions = [];
        foreach ($pageUrls as $pageUrl) {
            $idActions[$pageUrl] = $this->selectPage($pageUrl);
        }

        foreach ($pageUrls as $pageUrl) {
            self::assertSame($raw[$pageUrl], $this->getStoredReport($idActions[$pageUrl], 'day', self::DAY), $pageUrl);
            self::assertArrayHasKey('Others', $raw[$pageUrl]['previousPages'], $pageUrl);
        }
    }

    public function testActionsOfCustomTypesDoNotMixUpPages(): void
    {
        // a plugin's action type above the ones the report uses, next to two pages with consecutive idactions
        $idActionA = $this->insertAction('example.org/custom/a', Action::TYPE_PAGE_URL);
        $idActionB = $this->insertAction('example.org/custom/b', Action::TYPE_PAGE_URL);
        $idActionCustom = $this->insertAction('custom-17', 17);
        self::assertSame($idActionA + 1, $idActionB);
        $idActionOne = StaticContainer::get(Service::class)->getIdActionForUrl(self::PAGE_URL);
        $this->insertActionAfter($idActionOne, $idActionA);
        $this->insertActionAfter($idActionA, $idActionCustom);
        $this->insertActionAfter($idActionA, $idActionB);

        $pageUrls = ['http://example.org/custom/a', 'http://example.org/custom/b'];
        $raw = [];
        foreach ($pageUrls as $pageUrl) {
            $raw[$pageUrl] = $this->getRawReport('day', self::DAY, '', $pageUrl);
        }
        self::assertSame($idActionA, $this->selectPage($pageUrls[0]));
        self::assertSame($idActionB, $this->selectPage($pageUrls[1]));

        self::assertSame($raw[$pageUrls[0]], $this->getStoredReport($idActionA, 'day', self::DAY));
        self::assertSame($raw[$pageUrls[1]], $this->getStoredReport($idActionB, 'day', self::DAY));
        self::assertSame([], $raw[$pageUrls[1]]['followingPages']);
    }

    public function testLongerPeriodsAreSummedFromTheStoredDays(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);
        $firstDay = $this->getStoredReport($idAction, 'day', self::DAY);
        $laterDay = $this->getStoredReport($idAction, 'day', self::LATER_DAY);

        // only the stored days can account for this
        Db::query(
            'UPDATE ' . Common::prefixTable('archive_numeric_2010_03') . ' SET value = value + 1000 WHERE period = 1 AND date1 = ? AND name = ?',
            [self::DAY, Records::getRecordName($idAction, Records::PAGEVIEWS)]
        );

        $month = $this->getStoredReport($idAction, 'month', self::DAY);

        self::assertSame($firstDay['pageMetrics']['pageviews'] + $laterDay['pageMetrics']['pageviews'] + 1000, $month['pageMetrics']['pageviews']);
        foreach (['loops', 'entries', 'exits'] as $metric) {
            self::assertSame($firstDay['pageMetrics'][$metric] + $laterDay['pageMetrics'][$metric], $month['pageMetrics'][$metric]);
        }
        foreach (Records::ACTION_LISTS as $list) {
            self::assertSame($this->sumCounts($firstDay[$list], $laterDay[$list]), $month[$list], $list);
        }
    }

    public function testListsKeepTheConfiguredNumberOfRowsPlusOthers(): void
    {
        Config::getInstance()->General['datatable_archiving_maximum_rows_transitions'] = 1;
        $this->trackEntryFromWebsite(self::DAY . ' 15:00:00', 'http://a.example/x');
        $this->trackEntryFromWebsite(self::DAY . ' 16:00:00', 'http://b.example/y');
        $this->trackEntryFromWebsite(self::LATER_DAY . ' 15:00:00', 'http://a.example/x');
        $idAction = $this->selectPage(self::PAGE_URL);
        $raw = $this->getRawReport('day', self::DAY);
        $rawMonth = $this->getRawReport('month', self::DAY);

        foreach (['day', 'month'] as $period) {
            $this->archive($period, self::DAY);
            $archive = Archive::build(1, $period, self::DAY);
            $expected = $period === 'day' ? $raw : $rawMonth;

            $previousPages = $archive->getDataTable(Records::getRecordName($idAction, Records::PREVIOUS_PAGES));
            self::assertSame(1, $previousPages->getRowsCountWithoutSummaryRow(), $period);
            self::assertSame(
                array_sum($expected['previousPages']) - max($expected['previousPages']),
                (int) $previousPages->getSummaryRow()->getColumn(Metrics::INDEX_NB_ACTIONS),
                $period
            );
            self::assertSame(max($expected['previousPages']), (int) $previousPages->getFirstRow()->getColumn(Metrics::INDEX_NB_ACTIONS));

            $referrers = $archive->getDataTableExpanded(Records::getRecordName($idAction, Records::REFERRERS));
            $websites = $referrers->getRowFromLabel(Common::REFERRER_TYPE_WEBSITE)->getSubtable();
            self::assertSame(1, $websites->getRowsCountWithoutSummaryRow(), $period);
            self::assertSame($expected['referrers']['website']['visits'], $this->getTotal($websites, Metrics::INDEX_NB_VISITS), $period);
        }
    }

    /**
     * @dataProvider getSegments
     */
    public function testARebuildRequestBuildsOnlyTheRequestedPage(string $segment): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);
        $otherIdAction = $this->selectPage(self::OTHER_PAGE_URL);
        $this->archive('day', self::DAY, $segment);

        // as core:archive runs a scheduled rebuild
        $_GET['trigger'] = 'archivephp';
        $_GET['pluginOnly'] = '1';
        $_GET['requestedReport'] = Records::getRebuildReportName($idAction);
        foreach (['day', 'week'] as $period) {
            $params = new Parameters(new Site(1), PeriodFactory::build($period, self::DAY), new Segment($segment, [1]));
            $params->setRequestedPlugin('Transitions');
            $params->setArchiveOnlyReport(Records::getRebuildReportName($idAction));
            (new Loader($params))->prepareArchive('Transitions');
        }

        $doneFlag = Rules::getDoneFlagArchiveContainsOnePlugin(new Segment($segment, [1]), 'Transitions');
        $periodIds = ['day' => 1, 'week' => 2];
        $dates = ['day' => self::DAY, 'week' => '2010-03-01'];
        foreach ($periodIds as $period => $periodId) {
            $idArchive = (int) Db::fetchOne(
                'SELECT idarchive FROM ' . Common::prefixTable('archive_numeric_2010_03')
                . ' WHERE idsite = 1 AND period = ? AND date1 = ? AND name = ? AND value = ?',
                [$periodId, $dates[$period], $doneFlag, ArchiveWriter::DONE_PARTIAL]
            );
            self::assertGreaterThan(0, $idArchive, $period);
            self::assertSame($this->getExpectedRecordNames($idAction), $this->getStoredRecordNames($periodId, $dates[$period], $idArchive), $period);
        }

        self::assertNotEmpty(array_intersect($this->getExpectedRecordNames($otherIdAction), $this->getStoredRecordNames(1, self::DAY)));
        self::assertEmpty(array_intersect($this->getExpectedRecordNames($otherIdAction), $this->getStoredRecordNames(2, '2010-03-01')));
        self::assertSame(1.0, $this->getStoredNumeric($idAction, Records::DAYS, 2, '2010-03-01'));
    }

    /**
     * @return array<string, array{string}>
     */
    public function getSegments(): array
    {
        return ['all visits' => [''], 'segment' => [self::SEGMENT]];
    }

    public function testLongerPeriodsOfASegmentEqualTheRawLogQuery(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);

        self::assertSame($this->getRawReport('month', self::DAY, self::SEGMENT), $this->getStoredReport($idAction, 'month', self::DAY, self::SEGMENT));
        self::assertSame($this->getRawReport('week', self::DAY, self::SEGMENT), $this->getStoredReport($idAction, 'week', self::DAY, self::SEGMENT));
    }

    public function testARemovedPageIsNoLongerArchived(): void
    {
        $idAction = $this->selectPage(self::PAGE_URL);
        $otherIdAction = $this->selectPage(self::OTHER_PAGE_URL);
        $service = StaticContainer::get(Service::class);
        foreach ($service->getPages(1) as $page) {
            if ($page['url'] === self::OTHER_PAGE_URL) {
                $service->removePage(1, $page['idpage']);
            }
        }

        $this->archive('day', self::DAY);

        self::assertSame($this->getExpectedRecordNames($idAction), $this->getStoredRecordNames(1, self::DAY));
        self::assertNotSame($idAction, $otherIdAction);
    }

    private function insertAction(string $name, int $type): int
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('log_action') . ' (name, hash, type, url_prefix) VALUES (?, CRC32(?), ?, NULL)',
            [$name, $name, $type]
        );

        return (int) Db::get()->lastInsertId();
    }

    /**
     * Adds an action to a visit of the fixture's first day, following the given action.
     */
    private function insertActionAfter(int $idActionRef, int $idAction): void
    {
        Db::query(
            'INSERT INTO ' . Common::prefixTable('log_link_visit_action')
            . ' (idsite, idvisitor, idvisit, idaction_url_ref, idaction_name_ref, server_time, idaction_url, idaction_name)'
            . ' SELECT idsite, idvisitor, idvisit, ?, NULL, server_time, ?, NULL FROM ' . Common::prefixTable('log_link_visit_action')
            . ' WHERE idsite = 1 AND server_time >= ? AND server_time < ? ORDER BY idlink_va LIMIT 1',
            [$idActionRef, $idAction, self::DAY . ' 00:00:00', self::DAY . ' 23:59:59']
        );
    }

    private function trackPageview(string $dateTime, string $url): void
    {
        $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('title'));
    }

    private function trackEntryFromWebsite(string $dateTime, string $referrerUrl): void
    {
        $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
        $tracker->setUrlReferrer($referrerUrl);
        $tracker->setUrl(self::PAGE_URL);
        Fixture::checkResponse($tracker->doTrackPageView('page title - page/one.html'));
    }

    private function selectPage(string $url): int
    {
        API::getInstance()->addPreprocessedPage(1, $url);
        $idAction = StaticContainer::get(Service::class)->getIdActionForUrl($url);
        self::assertNotNull($idAction);

        return $idAction;
    }

    private function archive(string $period, string $date, string $segment = ''): void
    {
        Cache::getTransientCache()->flushAll();
        // reading any record of the plugin archives it
        Archive::build(1, $period, $date, $segment)->getNumeric(Records::getRebuildReportName(1));
    }

    private function getStoredNumeric(int $idAction, string $numeric, int $periodId, string $date1): ?float
    {
        $value = Db::fetchOne(
            'SELECT value FROM ' . ArchiveTableCreator::getNumericTable(Date::factory($date1), false)
            . ' WHERE idsite = 1 AND period = ? AND date1 = ? AND name = ? ORDER BY ts_archived DESC',
            [$periodId, $date1, Records::getRecordName($idAction, $numeric)]
        );

        return $value === false ? null : (float) $value;
    }

    /**
     * @return list<string>
     */
    private function getExpectedRecordNames(int $idAction): array
    {
        $names = [];
        foreach (array_merge(Records::ACTION_LISTS, [Records::REFERRERS], Records::NUMERICS) as $part) {
            $names[] = Records::getRecordName($idAction, $part);
        }
        sort($names);

        return $names;
    }

    /**
     * Names of the page records stored for the period, leaving out subtable chunks.
     *
     * @return list<string>
     */
    private function getStoredRecordNames(int $periodId, string $date1, ?int $idArchive = null): array
    {
        $names = [];
        foreach (['archive_numeric_2010_03', 'archive_blob_2010_03'] as $table) {
            $sql = 'SELECT DISTINCT name FROM ' . Common::prefixTable($table) . ' WHERE idsite = 1 AND period = ? AND date1 = ? AND name LIKE ?';
            $bind = [$periodId, $date1, 'Transitions\_%'];
            if ($idArchive !== null) {
                $sql .= ' AND idarchive = ?';
                $bind[] = $idArchive;
            }
            foreach (Db::fetchAll($sql, $bind) as $row) {
                if (Records::getIdActionOfRecord($row['name']) !== null) {
                    $names[] = $row['name'];
                }
            }
        }
        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    /**
     * The stored records of the page, in the shape of {@link getRawReport()}.
     *
     * @return array<string, mixed>
     */
    private function getStoredReport(int $idAction, string $period, string $date, string $segment = ''): array
    {
        $this->archive($period, $date, $segment);
        $archive = Archive::build(1, $period, $date, $segment);

        $numericNames = [];
        foreach (Records::NUMERICS as $numeric) {
            $numericNames[$numeric] = Records::getRecordName($idAction, $numeric);
        }
        $numerics = $archive->getNumeric(array_values($numericNames));

        $report = ['pageMetrics' => []];
        foreach (['loops', 'pageviews', 'entries', 'exits'] as $metric) {
            $report['pageMetrics'][$metric] = (int) $numerics[$numericNames[$metric]];
        }
        foreach (Records::ACTION_LISTS as $list) {
            $report[$list] = $this->getCounts($archive->getDataTable(Records::getRecordName($idAction, $list)), Metrics::INDEX_NB_ACTIONS);
        }

        $report['referrers'] = [];
        $referrers = $archive->getDataTableExpanded(Records::getRecordName($idAction, Records::REFERRERS));
        foreach ($referrers->getRows() as $row) {
            $subtable = $row->getSubtable();
            $details = $subtable ? $this->getCounts($subtable, Metrics::INDEX_NB_VISITS) : [];
            if (isset($details[Records::UNKNOWN_SEARCH_ENGINE_LABEL])) {
                $details[Piwik::translate('General_Unknown')] = $details[Records::UNKNOWN_SEARCH_ENGINE_LABEL];
                unset($details[Records::UNKNOWN_SEARCH_ENGINE_LABEL]);
            }
            ksort($details);

            $report['referrers'][\Piwik\Plugins\Referrers\getReferrerTypeFromShortName($row->getColumn('label'))] = [
                'visits' => (int) $row->getColumn(Metrics::INDEX_NB_VISITS),
                'details' => $details,
            ];
        }
        ksort($report['referrers']);

        return $report;
    }

    /**
     * The raw-log report, as label => count. By default with a row limit above the stored one, so the logs are queried,
     * and every row of the fixture.
     *
     * @return array<string, mixed>
     */
    private function getRawReport(string $period, string $date, string $segment = '', string $pageUrl = self::PAGE_URL, ?int $limit = null): array
    {
        $raw = API::getInstance()->getTransitionsForAction($pageUrl, 'url', 1, $period, $date, $segment, $limit ?? Records::getMaxRows(1) + 1);
        self::assertArrayNotHasKey('preprocessed', $raw);

        $report = ['pageMetrics' => array_map('intval', $raw['pageMetrics'])];
        foreach (Records::ACTION_LISTS as $list) {
            $report[$list] = $this->getCounts($raw[$list], 'referrals');
        }

        $report['referrers'] = [];
        foreach ($raw['referrers'] as $referrer) {
            if (empty($referrer['visits'])) {
                // the placeholder row of a page without entries
                continue;
            }

            $details = [];
            foreach ($referrer['details'] ?? [] as $detail) {
                $details[$detail['label']] = (int) $detail['referrals'];
            }
            ksort($details);
            $report['referrers'][$referrer['shortName']] = ['visits' => (int) $referrer['visits'], 'details' => $details];
        }
        ksort($report['referrers']);

        return $report;
    }

    /**
     * @return array<string, int> count by label, the summary row as `Others`
     */
    private function getCounts(DataTable $table, $column): array
    {
        $counts = [];
        foreach ($table->getRows() as $row) {
            $label = $row->isSummaryRow() ? 'Others' : (string) $row->getColumn('label');
            $counts[$label] = (int) $row->getColumn($column);
        }
        ksort($counts);

        return $counts;
    }

    private function getTotal(DataTable $table, int $column): int
    {
        return array_sum($this->getCounts($table, $column));
    }

    /**
     * @param array<string, int> $counts
     * @param array<string, int> $otherCounts
     * @return array<string, int>
     */
    private function sumCounts(array $counts, array $otherCounts): array
    {
        foreach ($otherCounts as $label => $count) {
            $counts[$label] = ($counts[$label] ?? 0) + $count;
        }
        ksort($counts);

        return $counts;
    }
}

PreprocessedPagesArchivingTest::$fixture = new SomeVisitsManyPageviewsWithTransitions();
