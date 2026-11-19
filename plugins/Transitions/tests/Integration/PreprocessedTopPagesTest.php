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
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\ReArchiveList;
use Piwik\Db;
use Piwik\Http\BadRequestException;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\PreprocessedPagesBusyException;
use Piwik\Plugins\Transitions\PreprocessedPages\Records;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Transitions
 * @group PreprocessedTopPagesTest
 * @group Plugins
 */
class PreprocessedTopPagesTest extends IntegrationTestCase
{
    private const DATE = '2026-01-01';

    private API $api;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::clearAccess(true);
        Fixture::createWebsite('2010-01-01 00:00:00', 0, 'Site 1', 'http://example.com', 1, null, null, null, null, 0, 'sessionid');
        Fixture::createWebsite('2010-01-01 00:00:00', 0, 'Site 2', 'http://example.org');
        (new ReArchiveList())->setAll([]);

        $this->api = API::getInstance();
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    public function testAddsTheMostViewedPagesThatArentSelectedYet(): void
    {
        $this->trackRankedPages(9);
        // not a web page, so never added
        $this->trackPageviews(['android-app://com.example/home' => 30]);
        $this->api->addPreprocessedPage(1, 'http://example.com/page1');
        $this->api->addPreprocessedPage(1, 'https://www.example.com/page2');
        $this->api->addPreprocessedPage(1, 'http://example.com/page3#top');
        $this->api->addPreprocessedPage(1, 'http://example.com/page4');
        (new ReArchiveList())->setAll([]);
        $reads = $this->countReportReads();

        $added = $this->api->addTopPreprocessedPages(1, 5, 'month', self::DATE);

        $expectedUrls = ['http://example.com/page5', 'http://example.com/page6', 'http://example.com/page7', 'http://example.com/page8', 'http://example.com/page9'];
        self::assertSame($expectedUrls, array_column($added, 'url'));
        self::assertSame(['idpage', 'url', 'created_date', 'created_by', 'created_by_superuser'], array_keys($added[0]));
        self::assertSame(array_slice($this->api->getPreprocessedPages(1), 0, 5), array_reverse($added));
        self::assertCount(9, $this->api->getPreprocessedPages(1));
        // each added page schedules the rebuild of only its records, as a single add does
        self::assertSame(
            array_map(fn(array $page): string => Records::getRebuildReportName($page['idpage']), $added),
            $this->getScheduledReports()
        );
        self::assertSame([], $this->api->getPreprocessedPages(2));
        self::assertSame(1, $reads->count);
    }

    public function testRanksByPageviewsOfThePeriod(): void
    {
        $this->trackRankedPages(3);
        $this->trackPageviews(['http://example.com/other-day' => 20], '2026-01-02 10:00:00');

        self::assertSame(['http://example.com/page1'], array_column($this->api->addTopPreprocessedPages(1, 1, 'day', self::DATE), 'url'));
        self::assertSame(['http://example.com/other-day'], array_column($this->api->addTopPreprocessedPages(1, 1, 'range', '2026-01-01,2026-01-02'), 'url'));
        self::assertSame(['http://example.com/page2', 'http://example.com/page3'], array_column($this->api->addTopPreprocessedPages(1, 3, 'week', self::DATE), 'url'));
    }

    public function testAddsOnlyAsManyPagesAsTheWebsitesLimitLeavesFree(): void
    {
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 3];
        $this->trackRankedPages(6);
        $this->api->addPreprocessedPage(1, 'http://example.com/page1');

        $added = $this->api->addTopPreprocessedPages(1, 5, 'month', self::DATE);

        self::assertSame(['http://example.com/page2', 'http://example.com/page3'], array_column($added, 'url'));
        $this->assertAddTopFails(1, 5, 'month', self::DATE, "This website's limit of pre-processed pages (3) is reached. Remove a page to add another one.");
        self::assertCount(3, $this->api->getPreprocessedPages(1));
    }

    public function testFailsWhenTheWebsiteCantGetPages(): void
    {
        $this->trackRankedPages(1);
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 0];

        $this->assertAddTopFails(1, 5, 'month', self::DATE, 'No pages can be selected for pre-processed Transitions on this website.');
        self::assertSame([], $this->api->getPreprocessedPages(1));
    }

    public function testRestoresRemovedPagesWithTheirRow(): void
    {
        $this->trackRankedPages(2);
        $idPage = $this->api->addPreprocessedPage(1, 'https://www.example.com/page1')['idpage'];
        $this->api->removePreprocessedPage(1, $idPage);
        Db::query('UPDATE ' . Common::prefixTable('transitions_preprocessed_page') . ' SET created_date = ?', ['2025-01-01 00:00:00']);
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        FakeAccess::clearAccess(false, [1], [], 'alice');

        $added = $this->api->addTopPreprocessedPages(1, 1, 'month', self::DATE);

        self::assertSame($idPage, $added[0]['idpage']);
        // the URL stored when it was first added is kept
        self::assertSame('https://www.example.com/page1', $added[0]['url']);
        self::assertSame('alice', $added[0]['created_by']);
        self::assertNotSame('2025-01-01 00:00:00', $added[0]['created_date']);
        self::assertSame($added, $this->api->getPreprocessedPages(1));
        self::assertSame([Records::getRebuildReportName($idPage)], $this->getScheduledReports());
    }

    public function testAddsNothingWithoutPageviewsOrPagesThatArentSelected(): void
    {
        $this->trackRankedPages(2);

        self::assertSame([], $this->api->addTopPreprocessedPages(1, 5, 'month', '2025-12-01'));
        self::assertSame([], $this->api->addTopPreprocessedPages(1, 5, 'day', '2026-01-02'));
        self::assertCount(2, $this->api->addTopPreprocessedPages(1, 5, 'month', self::DATE));
        self::assertSame([], $this->api->addTopPreprocessedPages(1, 5, 'month', self::DATE));

        self::assertCount(2, $this->api->getPreprocessedPages(1));
        self::assertCount(2, $this->getScheduledReports());
    }

    public function testReadsFurtherDownTheRankingPastSkippedRows(): void
    {
        $pageviews = [];
        for ($i = 1; $i <= 40; $i++) {
            $pageviews['android-app://com.example/screen' . $i] = 10;
        }
        $this->trackPageviews($pageviews + ['http://example.com/web' => 5, 'http://example.com/web2' => 4]);
        $reads = $this->countReportReads();

        $added = $this->api->addTopPreprocessedPages(1, 1, 'month', self::DATE);

        self::assertSame(['http://example.com/web'], array_column($added, 'url'));
        // rows 1-11, 12-33, then 34-77, where the web pages are
        self::assertSame(3, $reads->count);
    }

    /**
     * Over HTTP, as the admin page calls it.
     */
    public function testStoresTrackedUrlsAsAddingThemOverHttpDoes(): void
    {
        $longUrl = 'https://example.com/' . str_repeat('a', (int) Config::getInstance()->Tracker['page_maximum_length'] + 50);
        $urls = [
            'https://example.com/<a>"\'?b=1&c=<x>&d="\'',
            'https://example.com/piwikとは',
            $longUrl,
        ];
        $this->trackPageviews(array_combine($urls, [3, 2, 1]));

        $added = Request::processRequest('Transitions.addTopPreprocessedPages', ['idSite' => 1, 'limit' => 5, 'period' => 'month', 'date' => self::DATE]);

        self::assertSame(
            [
                'https://example.com/<a>"\'?b=1&c=&lt;x&gt;&d=&quot;&#039;',
                'https://example.com/piwikとは',
                substr($longUrl, 0, (int) Config::getInstance()->Tracker['page_maximum_length']),
            ],
            array_column($added, 'url')
        );
        // the same pages as when the tracked URLs are added
        foreach ($added as $page) {
            $this->api->removePreprocessedPage(1, $page['idpage']);
        }
        foreach ($urls as $i => $url) {
            $readded = Request::processRequest('Transitions.addPreprocessedPage', ['idSite' => 1, 'pageUrl' => $url]);
            self::assertSame($added[$i]['idpage'], $readded['idpage']);
            self::assertSame($added[$i]['url'], $readded['url']);
        }
        self::assertCount(3, Db::fetchAll('SELECT idpage FROM ' . Common::prefixTable('transitions_preprocessed_page')));
        self::assertNotContains(null, StaticContainer::get(Service::class)->getIdActionsByPage(1));
    }

    /**
     * @dataProvider getInvalidArguments
     */
    public function testRejectsInvalidArguments(int $limit, string $period, string $date, string $expectedMessage): void
    {
        $this->trackRankedPages(1);

        $this->assertAddTopFails(1, $limit, $period, $date, $expectedMessage);
        self::assertSame([], $this->api->getPreprocessedPages(1));
    }

    /**
     * @return array<string, array{int, string, string, string}>
     */
    public function getInvalidArguments(): array
    {
        return [
            'limit 0' => [0, 'month', self::DATE, "Invalid value for the parameter 'limit'."],
            'negative limit' => [-1, 'month', self::DATE, "Invalid value for the parameter 'limit'."],
            'unknown period' => [5, 'decade', self::DATE, "The period 'decade' is not supported."],
            'invalid date' => [5, 'month', 'not-a-date', 'Date format must be'],
            'invalid range' => [5, 'range', '2026-01-01,not-a-date', 'not-a-date'],
            'list of periods' => [5, 'day', 'last7', "Invalid value for the parameter 'date'."],
            'list of periods by range' => [5, 'month', '2026-01-01,2026-03-01', "Invalid value for the parameter 'date'."],
        ];
    }

    /**
     * @dataProvider getDeniedRoles
     */
    public function testNonAdminsCannotAddTopPages(string $role): void
    {
        $this->trackRankedPages(1);
        $this->setRole($role);

        try {
            $this->api->addTopPreprocessedPages(1, 5, 'month', self::DATE);
            $this->fail('Expected a NoAccessException for role ' . $role);
        } catch (NoAccessException $e) {
            // expected
        }

        FakeAccess::clearAccess(true);
        self::assertSame([], $this->api->getPreprocessedPages(1));
    }

    /**
     * @return array<string, array{string}>
     */
    public function getDeniedRoles(): array
    {
        return [
            'anonymous' => ['anonymous'],
            'view' => ['view'],
            'write' => ['write'],
            'admin of another site' => ['admin of site 2'],
        ];
    }

    public function testOnlySuperUsersSeeTheLoginOfASuperUserWhoAddedThem(): void
    {
        $this->trackRankedPages(2);
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('root', 'secret-password-2', 'root@example.com');
        (new UsersModel())->setSuperUserAccess('root', true);
        FakeAccess::clearAccess(true, [], [], 'root');

        $added = $this->api->addTopPreprocessedPages(1, 1, 'month', self::DATE);
        self::assertSame('root', $added[0]['created_by']);
        self::assertTrue($added[0]['created_by_superuser']);

        FakeAccess::clearAccess(false, [1], [], 'alice');
        $listed = $this->api->getPreprocessedPages(1);
        self::assertNull($listed[0]['created_by']);
        self::assertTrue($listed[0]['created_by_superuser']);
    }

    public function testFailsWithoutAddingWhileTheWebsitesPagesAreChanged(): void
    {
        $this->trackRankedPages(2);
        $lock = new Lock(StaticContainer::get(LockBackend::class), Service::LOCK_NAMESPACE);
        self::assertTrue($lock->acquireLock('1'));

        try {
            $this->api->addTopPreprocessedPages(1, 5, 'month', self::DATE);
            $this->fail('Expected adding to fail while the site lock is held');
        } catch (PreprocessedPagesBusyException $e) {
            self::assertSame(409, $e->getCode());
        } finally {
            $lock->unlock();
        }

        self::assertSame([], $this->api->getPreprocessedPages(1));
        self::assertSame([], $this->getScheduledReports());
    }

    /**
     * Tracks `http://example.com/page<n>` for n = 1..$count, page1 the most viewed.
     */
    private function trackRankedPages(int $count): void
    {
        $pageviews = [];
        for ($i = 1; $i <= $count; $i++) {
            $pageviews['http://example.com/page' . $i] = $count + 1 - $i;
        }

        $this->trackPageviews($pageviews);
    }

    /**
     * @param array<string, int> $pageviews the number of pageviews by URL
     */
    private function trackPageviews(array $pageviews, string $dateTime = self::DATE . ' 10:00:00'): void
    {
        $tracker = Fixture::getTracker(1, $dateTime, $defaultInit = true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->enableBulkTracking();

        foreach ($pageviews as $url => $count) {
            for ($i = 0; $i < $count; $i++) {
                $tracker->setUrl($url);
                $tracker->doTrackPageView('title');
            }
        }

        Fixture::checkBulkTrackingResponse($tracker->doBulkTrack());
    }

    /**
     * @return object{count: int} the number of reads of the page URLs report from now on
     */
    private function countReportReads(): object
    {
        $reads = new \stdClass();
        $reads->count = 0;
        Piwik::addAction('API.Actions.getPageUrls', function () use ($reads): void {
            $reads->count++;
        });

        return $reads;
    }

    /**
     * @return list<string>
     */
    private function getScheduledReports(): array
    {
        return array_map(fn($entry) => json_decode($entry, true)['report'], (new ReArchiveList())->getAll());
    }

    private function assertAddTopFails(int $idSite, int $limit, string $period, string $date, string $expectedMessage): void
    {
        try {
            $this->api->addTopPreprocessedPages($idSite, $limit, $period, $date);
            $this->fail('Expected adding the top pages to fail');
        } catch (BadRequestException $e) {
            self::assertStringContainsString($expectedMessage, $e->getMessage());
            self::assertSame(400, $e->getCode());
        }
    }

    private function setRole(string $role): void
    {
        switch ($role) {
            case 'view':
                FakeAccess::clearAccess(false, [], [1], 'siteViewer');
                break;
            case 'write':
                FakeAccess::clearAccess(false, [], [], 'siteWriter', [1]);
                break;
            case 'admin of site 2':
                FakeAccess::clearAccess(false, [2], [2], 'otherAdmin');
                break;
            default:
                FakeAccess::clearAccess(false, [], [], 'anonymous');
        }
    }
}
