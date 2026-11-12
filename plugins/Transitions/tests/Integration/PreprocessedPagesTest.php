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
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\ReArchiveList;
use Piwik\Db;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http\BadRequestException;
use Piwik\NoAccessException;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\PreprocessedPages\Service;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Transitions
 * @group PreprocessedPagesTest
 * @group Plugins
 */
class PreprocessedPagesTest extends IntegrationTestCase
{
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

    /**
     * @dataProvider getDeniedRoles
     */
    public function testNonAdminsCannotListAddOrRemovePages(string $role): void
    {
        $idPage = $this->api->addPreprocessedPage(1, 'https://example.com/a')['idpage'];
        $this->setRole($role, 1);

        foreach (
            [
                fn() => $this->api->getPreprocessedPages(1),
                fn() => $this->api->addPreprocessedPage(1, 'https://example.com/b'),
                fn() => $this->api->removePreprocessedPage(1, $idPage),
            ] as $call
        ) {
            try {
                $call();
                $this->fail('Expected a NoAccessException for role ' . $role);
            } catch (NoAccessException $e) {
                // expected
            }
        }

        $this->setRole('superuser', 1);
        self::assertSame(['https://example.com/a'], $this->getUrls(1));
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

    /**
     * @dataProvider getAllowedRoles
     */
    public function testAdminsAndSuperUsersCanListAddAndRemovePages(string $role, ?string $expectedCreatedBy): void
    {
        $this->setRole($role, 1);

        $added = $this->api->addPreprocessedPage(1, 'https://example.com/a');

        self::assertSame(['idpage', 'url', 'created_date', 'created_by'], array_keys($added));
        self::assertSame('https://example.com/a', $added['url']);
        self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $added['created_date']);
        self::assertSame($expectedCreatedBy, $added['created_by']);
        self::assertSame([$added], $this->api->getPreprocessedPages(1));

        $this->api->removePreprocessedPage(1, $added['idpage']);

        self::assertSame([], $this->api->getPreprocessedPages(1));
    }

    /**
     * @return array<string, array{string, string|null}>
     */
    public function getAllowedRoles(): array
    {
        return [
            'admin' => ['admin', 'siteAdmin'],
            'superuser' => ['superuser', 'superUserLogin'],
        ];
    }

    public function testPagesAreListedNewestFirstPerSite(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/first');
        $this->api->addPreprocessedPage(1, 'https://example.com/second');
        $this->api->addPreprocessedPage(2, 'https://example.org/other');

        self::assertSame(['https://example.com/second', 'https://example.com/first'], $this->getUrls(1));
        self::assertSame(['https://example.org/other'], $this->getUrls(2));
    }

    public function testRejectsPagesBeyondTheDefaultLimit(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $this->api->addPreprocessedPage(1, 'https://example.com/page' . $i);
        }

        $this->assertAddFails(1, 'https://example.com/page21', 'You can select at most 20 pages for pre-processed Transitions.');
        self::assertCount(20, $this->api->getPreprocessedPages(1));
    }

    public function testSiteLimitOverridesTheGeneralLimit(): void
    {
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 5];

        for ($i = 1; $i <= 5; $i++) {
            $this->api->addPreprocessedPage(1, 'https://example.com/page' . $i);
        }

        $this->assertAddFails(1, 'https://example.com/page6', 'You can select at most 5 pages for pre-processed Transitions.');

        for ($i = 1; $i <= 6; $i++) {
            $this->api->addPreprocessedPage(2, 'https://example.org/page' . $i);
        }
        self::assertCount(6, $this->api->getPreprocessedPages(2));
    }

    public function testLoweringTheLimitKeepsExistingPagesAndBlocksNewOnes(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            $this->api->addPreprocessedPage(1, 'https://example.com/page' . $i);
        }

        Config::getInstance()->Transitions = ['max_preprocessed_pages' => 2];

        self::assertCount(4, $this->api->getPreprocessedPages(1));
        $this->assertAddFails(1, 'https://example.com/page5', 'You can select at most 2 pages for pre-processed Transitions.');

        $this->api->removePreprocessedPage(1, $this->api->getPreprocessedPages(1)[0]['idpage']);
        $this->assertAddFails(1, 'https://example.com/page5', 'You can select at most 2 pages for pre-processed Transitions.');
    }

    public function testLimitOfZeroBlocksAllPages(): void
    {
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 0];

        $this->assertAddFails(1, 'https://example.com/a', 'You can select at most 0 pages for pre-processed Transitions.');
    }

    /**
     * @dataProvider getDuplicateUrls
     */
    public function testRejectsUrlsThatAreTheSamePage(string $listedUrl, string $duplicateUrl): void
    {
        $this->api->addPreprocessedPage(1, $listedUrl);

        $this->assertAddFails(1, $duplicateUrl, 'This page is already selected.');
        self::assertSame([$listedUrl], $this->getUrls(1));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function getDuplicateUrls(): array
    {
        return [
            'protocol and www' => ['http://example.com/a', 'https://www.example.com/a'],
            'www removed' => ['https://www.example.com/a', 'https://example.com/a'],
            'same url' => ['https://example.com/a', 'https://example.com/a'],
            'host case' => ['https://example.com/a', 'HTTPS://EXAMPLE.COM/a'],
            'surrounding whitespace' => ['https://example.com/a', ' https://example.com/a '],
            'excluded site parameter' => ['https://example.com/a?x=1', 'https://example.com/a?x=1&sessionid=abc'],
            'campaign parameter' => ['https://example.com/a', 'https://example.com/a?utm_source=newsletter'],
            'fragment' => ['https://example.com/a', 'https://example.com/a#section'],
        ];
    }

    public function testDistinctPathsAndQueriesAreDifferentPages(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(1, 'https://example.com/A');
        $this->api->addPreprocessedPage(1, 'https://example.com/a?x=1');
        $this->api->addPreprocessedPage(2, 'https://example.com/a');

        self::assertCount(3, $this->api->getPreprocessedPages(1));
        self::assertCount(1, $this->api->getPreprocessedPages(2));
    }

    public function testStoresTheUrlWithoutExcludedParameters(): void
    {
        $added = $this->api->addPreprocessedPage(1, 'https://example.com/a?sessionid=abc&x=1&utm_campaign=c#section');

        self::assertSame('https://example.com/a?x=1', $added['url']);
    }

    public function testUrlsSentOverHttpAreStoredAsTheTrackerStoresThem(): void
    {
        $url = 'https://example.com/<a>?b=1&c=<x>';
        $idAction = $this->trackPageview($url);

        $idPage = Request::processRequest('Transitions.addPreprocessedPage', ['idSite' => 1, 'pageUrl' => $url])['idpage'];

        // the tracker encodes query parameter values, but not the path
        self::assertSame(['https://example.com/<a>?b=1&c=&lt;x&gt;'], $this->getUrls(1));
        self::assertSame([$idPage => $idAction], StaticContainer::get(Service::class)->getIdActionsForSite(1));
    }

    public function testKeepsMarkupAndMultibyteCharactersAsGiven(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/<script>alert(1)</script>"\'');
        $this->api->addPreprocessedPage(1, 'https://example.com/piwikとは');

        self::assertSame(
            ['https://example.com/piwikとは', 'https://example.com/<script>alert(1)</script>"\''],
            $this->getUrls(1)
        );
    }

    /**
     * @dataProvider getInvalidUrls
     */
    public function testRejectsInvalidUrls(string $url): void
    {
        $this->assertAddFails(1, $url, 'Please enter a valid page URL starting with http:// or https://.');
        self::assertSame([], $this->api->getPreprocessedPages(1));
    }

    /**
     * @return array<string, array{string}>
     */
    public function getInvalidUrls(): array
    {
        return [
            'empty' => [''],
            'no protocol' => ['example.com/a'],
            'protocol-relative' => ['//example.com/a'],
            'other protocol' => ['ftp://example.com/a'],
            'javascript' => ['javascript:alert(1)'],
            'no host' => ['https://'],
            'no host with path' => ['https:///a'],
        ];
    }

    public function testRejectsUrlsLongerThanTheLimit(): void
    {
        $url = 'https://example.com/' . str_repeat('a', Service::MAX_URL_LENGTH);

        $this->assertAddFails(1, $url, 'The page URL is too long. Use at most 4096 characters.');

        self::assertSame([], $this->api->getPreprocessedPages(1));
    }

    public function testTruncatesLongUrlsAsTheTrackerDoes(): void
    {
        $url = 'https://example.com/' . str_repeat('a', Service::MAX_URL_LENGTH - 20);
        $idAction = $this->trackPageview($url);

        $added = $this->api->addPreprocessedPage(1, $url);

        self::assertSame(substr($url, 0, (int) Config::getInstance()->Tracker['page_maximum_length']), $added['url']);
        self::assertSame([$added['idpage'] => $idAction], StaticContainer::get(Service::class)->getIdActionsForSite(1));
    }

    public function testRejectsUnknownSites(): void
    {
        $this->expectException(UnexpectedWebsiteFoundException::class);

        $this->api->addPreprocessedPage(99, 'https://example.com/a');
    }

    public function testRemovingAPageOfAnotherSiteOrAnUnknownPageIsNotFound(): void
    {
        $idPage = $this->api->addPreprocessedPage(1, 'https://example.com/a')['idpage'];

        $this->setRole('admin of site 2', 2);
        $this->assertRemoveNotFound(2, $idPage);
        $this->assertRemoveNotFound(2, 12345);

        $this->setRole('superuser', 1);
        $this->api->removePreprocessedPage(1, $idPage);
        $this->assertRemoveNotFound(1, $idPage);
    }

    public function testAddWaitsForTheSiteLockAndFailsWithoutAddingWhenItIsNotReleased(): void
    {
        $lock = new Lock(StaticContainer::get(LockBackend::class), Service::LOCK_NAMESPACE);
        self::assertTrue($lock->acquireLock('1'));

        try {
            $this->api->addPreprocessedPage(1, 'https://example.com/a');
            $this->fail('Expected adding to fail while the site lock is held');
        } catch (\Exception $e) {
            self::assertSame('Could not get the lock for ID: 1', $e->getMessage());
        } finally {
            $lock->unlock();
        }

        self::assertSame([], $this->api->getPreprocessedPages(1));
        // another site is not blocked by the lock of site 1
        $this->api->addPreprocessedPage(2, 'https://example.org/a');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        self::assertSame(['https://example.com/a'], $this->getUrls(1));
    }

    public function testDeletingAUserClearsTheLoginThatAddedPages(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');

        FakeAccess::clearAccess(false, [1], [], 'alice');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        FakeAccess::clearAccess(false, [1], [], 'bob');
        $this->api->addPreprocessedPage(1, 'https://example.com/b');

        $this->setRole('superuser', 1);
        UsersManagerAPI::getInstance()->deleteUser('alice');

        self::assertSame(
            [['https://example.com/b', 'bob'], ['https://example.com/a', null]],
            array_map(fn($page) => [$page['url'], $page['created_by']], $this->api->getPreprocessedPages(1))
        );
    }

    public function testDeletingASiteRemovesItsPages(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(2, 'https://example.org/a');

        SitesManagerAPI::getInstance()->deleteSite(2);

        self::assertSame(['https://example.com/a'], $this->getUrls(1));
        self::assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' WHERE idsite = 2'));
    }

    public function testActivatingThePluginCatchesUpOnDeletedUsersAndSites(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        FakeAccess::clearAccess(false, [1, 2], [], 'alice');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(2, 'https://example.org/a');
        $this->setRole('superuser', 1);

        // as if the plugin was deactivated while the user and the site were deleted
        Db::query('DELETE FROM ' . Common::prefixTable('user') . ' WHERE login = ?', ['alice']);
        Db::query('DELETE FROM ' . Common::prefixTable('site') . ' WHERE idsite = 2');

        PluginManager::getInstance()->getLoadedPlugin('Transitions')->activate();

        self::assertSame([null], array_column($this->api->getPreprocessedPages(1), 'created_by'));
        self::assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' WHERE idsite = 2'));
    }

    public function testAddingATrackedPageSchedulesReArchivingOfOnlyThatPage(): void
    {
        $idAction = $this->trackPageview('http://example.com/tracked');

        $this->api->addPreprocessedPage(1, 'https://www.example.com/tracked');

        self::assertSame(
            [['idSites' => [1], 'pluginName' => 'Transitions', 'report' => 'Transitions_' . $idAction]],
            array_map(
                fn($entry) => array_intersect_key(json_decode($entry, true), ['idSites' => 0, 'pluginName' => 0, 'report' => 0]),
                (new ReArchiveList())->getAll()
            )
        );

        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();

        $invalidations = Db::fetchAll('SELECT DISTINCT idsite, name, report FROM ' . Common::prefixTable('archive_invalidations'));
        self::assertSame([['idsite' => 1, 'name' => 'done.Transitions', 'report' => 'Transitions_' . $idAction]], $invalidations);
    }

    public function testAddingAnUntrackedPageSchedulesNothing(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/not-tracked-yet');

        self::assertSame([], (new ReArchiveList())->getAll());
    }

    public function testRemovingAPageClearsOnlyItsPendingReArchiving(): void
    {
        $idActionA = $this->trackPageview('http://example.com/a');
        $idActionB = $this->trackPageview('http://example.com/b');

        $idPageA = $this->api->addPreprocessedPage(1, 'http://example.com/a')['idpage'];
        $this->api->addPreprocessedPage(1, 'http://example.com/b');
        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();
        self::assertGreaterThan(0, $this->countInvalidations('Transitions_' . $idActionA));

        $this->api->removePreprocessedPage(1, $idPageA);

        self::assertSame(0, $this->countInvalidations('Transitions_' . $idActionA));
        self::assertGreaterThan(0, $this->countInvalidations('Transitions_' . $idActionB));
    }

    public function testRemovingAPageClearsItsQueuedReArchiving(): void
    {
        $this->trackPageview('http://example.com/a');
        $idPage = $this->api->addPreprocessedPage(1, 'http://example.com/a')['idpage'];
        self::assertCount(1, (new ReArchiveList())->getAll());

        $this->api->removePreprocessedPage(1, $idPage);

        self::assertSame([], (new ReArchiveList())->getAll());
    }

    public function testResolvesTheIdActionsOfTrackedPages(): void
    {
        $idAction = $this->trackPageview('http://example.com/tracked');
        $idPageTracked = $this->api->addPreprocessedPage(1, 'https://www.example.com/tracked')['idpage'];
        $this->api->addPreprocessedPage(1, 'https://example.com/not-tracked-yet');

        self::assertSame([$idPageTracked => $idAction], StaticContainer::get(Service::class)->getIdActionsForSite(1));
    }

    private function trackPageview(string $url): int
    {
        $tracker = Fixture::getTracker(1, '2026-01-01 10:00:00', true, true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());
        $tracker->setUrl($url);
        Fixture::checkResponse($tracker->doTrackPageView('title'));

        return (int) Db::fetchOne(
            'SELECT idaction_url FROM ' . Common::prefixTable('log_link_visit_action') . ' ORDER BY idlink_va DESC LIMIT 1'
        );
    }

    private function countInvalidations(string $report): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE report = ?',
            [$report]
        );
    }

    /**
     * @return string[]
     */
    private function getUrls(int $idSite): array
    {
        return array_column($this->api->getPreprocessedPages($idSite), 'url');
    }

    private function assertAddFails(int $idSite, string $url, string $expectedMessage): void
    {
        try {
            $this->api->addPreprocessedPage($idSite, $url);
            $this->fail('Expected adding ' . $url . ' to fail');
        } catch (BadRequestException $e) {
            self::assertSame($expectedMessage, $e->getMessage());
            self::assertSame(400, $e->getCode());
        }
    }

    private function assertRemoveNotFound(int $idSite, int $idPage): void
    {
        try {
            $this->api->removePreprocessedPage($idSite, $idPage);
            $this->fail('Expected removing page ' . $idPage . ' of site ' . $idSite . ' to fail');
        } catch (ThingNotFoundException $e) {
            self::assertSame('This page is not selected for pre-processed Transitions.', $e->getMessage());
            self::assertSame(404, $e->getCode());
        }
    }

    private function setRole(string $role, int $idSite): void
    {
        switch ($role) {
            case 'superuser':
                FakeAccess::clearAccess(true);
                break;
            case 'admin':
                FakeAccess::clearAccess(false, [$idSite], [], 'siteAdmin');
                break;
            case 'write':
                FakeAccess::clearAccess(false, [], [], 'siteWriter', [$idSite]);
                break;
            case 'view':
                FakeAccess::clearAccess(false, [], [$idSite], 'siteViewer');
                break;
            case 'admin of site 2':
                FakeAccess::clearAccess(false, [2], [2], 'otherAdmin');
                break;
            default:
                FakeAccess::clearAccess(false, [], [], 'anonymous');
        }
    }
}
