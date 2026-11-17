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
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Exception\ThingNotFoundException;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Http\BadRequestException;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Plugins\Transitions\API;
use Piwik\Plugins\Transitions\Tasks;
use Piwik\Plugins\Transitions\PreprocessedPages\ArchivingMode;
use Piwik\Plugins\Transitions\PreprocessedPages\Dao;
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
 * @group PreprocessedPagesTest
 * @group Plugins
 */
class PreprocessedPagesTest extends IntegrationTestCase
{
    /**
     * Old reports older than 3 months are deleted, of every period and segment; by default, monthly and yearly ones
     * are kept.
     */
    private const DELETE_ALL_OLD_REPORTS = [
        'delete_reports_enable' => 1,
        'delete_reports_older_than' => 3,
        'delete_reports_keep_day_reports' => 0,
        'delete_reports_keep_week_reports' => 0,
        'delete_reports_keep_month_reports' => 0,
        'delete_reports_keep_year_reports' => 0,
        'delete_reports_keep_segment_reports' => 0,
    ];

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
        Date::$now = null;
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
        if ($expectedCreatedBy !== null && !(new UsersModel())->userExists($expectedCreatedBy)) {
            UsersManagerAPI::getInstance()->addUser($expectedCreatedBy, 'secret-password-1', $expectedCreatedBy . '@example.com');
        }
        $this->setRole($role, 1);

        $added = $this->api->addPreprocessedPage(1, 'https://example.com/a');

        self::assertSame(['idpage', 'url', 'created_date', 'created_by', 'created_by_superuser'], array_keys($added));
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

        $this->assertAddFails(1, 'https://example.com/page21', "This website's limit of pre-processed pages (20) is reached. Remove a page to add another one.");
        self::assertCount(20, $this->api->getPreprocessedPages(1));
    }

    public function testSiteLimitOverridesTheGeneralLimit(): void
    {
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 5];

        for ($i = 1; $i <= 5; $i++) {
            $this->api->addPreprocessedPage(1, 'https://example.com/page' . $i);
        }

        $this->assertAddFails(1, 'https://example.com/page6', "This website's limit of pre-processed pages (5) is reached. Remove a page to add another one.");

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
        $this->assertAddFails(1, 'https://example.com/page5', "This website's limit of pre-processed pages (2) is reached. Remove a page to add another one.");

        $this->api->removePreprocessedPage(1, $this->api->getPreprocessedPages(1)[0]['idpage']);
        $this->assertAddFails(1, 'https://example.com/page5', "This website's limit of pre-processed pages (2) is reached. Remove a page to add another one.");
    }

    public function testLimitOfZeroBlocksAllPages(): void
    {
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => 0];

        $this->assertAddFails(1, 'https://example.com/a', 'No pages can be selected for pre-processed Transitions on this website.');
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
        self::assertSame([$idPage => $idAction], StaticContainer::get(Service::class)->getIdActionsByPage(1));
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
        self::assertSame([$added['idpage'] => $idAction], StaticContainer::get(Service::class)->getIdActionsByPage(1));
    }

    public function testRejectsUnknownSites(): void
    {
        foreach (
            [
                fn() => $this->api->getPreprocessedPages(99),
                fn() => $this->api->addPreprocessedPage(99, 'https://example.com/a'),
            ] as $call
        ) {
            try {
                $call();
                $this->fail('Expected an UnexpectedWebsiteFoundException');
            } catch (UnexpectedWebsiteFoundException $e) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testAddRemovesItsPageWhenTheSiteIsDeletedMeanwhile(): void
    {
        $dao = new class extends Dao {
            public function addPage(int $idSite, string $url, string $urlHash, string $createdDate, ?string $createdBy): bool
            {
                $added = parent::addPage($idSite, $url, $urlHash, $createdDate, $createdBy);
                // as if SitesManager.deleteSite ran, cleanup included, between the site check and the insert
                Db::query('DELETE FROM ' . Common::prefixTable('site') . ' WHERE idsite = ?', [$idSite]);

                return $added;
            }
        };
        $service = new Service($dao, StaticContainer::get(ArchiveInvalidator::class), StaticContainer::get(LockBackend::class));

        try {
            $service->addPage(2, 'https://example.org/a');
            $this->fail('Expected an UnexpectedWebsiteFoundException');
        } catch (UnexpectedWebsiteFoundException $e) {
            // expected
        }

        self::assertSame([], $dao->getPagesForSite(2));
    }

    /**
     * @dataProvider getInvalidLimits
     */
    public function testRejectsInvalidLimitsNamingTheSetting(string $section, $value): void
    {
        Config::getInstance()->$section = ['max_preprocessed_pages' => $value];

        $this->expectExceptionMessage('Invalid value for [' . $section . '] max_preprocessed_pages: expected an integer >= 0.');

        $this->api->addPreprocessedPage(1, 'https://example.com/a');
    }

    /**
     * @return array<string, array{string, mixed}>
     */
    public function getInvalidLimits(): array
    {
        return [
            'site, not a number' => ['Transitions_1', '20a'],
            'site, negative' => ['Transitions_1', -1],
            'site, negative string' => ['Transitions_1', '-5'],
            'site, decimal' => ['Transitions_1', '2.5'],
            'site, empty' => ['Transitions_1', ''],
            'general, not a number' => ['Transitions', 'twenty'],
            'general, negative' => ['Transitions', -3],
        ];
    }

    public function testAcceptsLimitsGivenAsNumericStrings(): void
    {
        Config::getInstance()->Transitions_1 = ['max_preprocessed_pages' => '1'];

        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->assertAddFails(1, 'https://example.com/b', "This website's limit of pre-processed pages (1) is reached. Remove a page to add another one.");
    }

    public function testMeasuresTheLengthOfUrlsSentOverHttpBeforeEncoding(): void
    {
        $url = 'https://example.com/a?' . str_repeat('&', Service::MAX_URL_LENGTH - 22);
        self::assertSame(Service::MAX_URL_LENGTH, mb_strlen($url));

        Request::processRequest('Transitions.addPreprocessedPage', ['idSite' => 1, 'pageUrl' => $url]);
        self::assertCount(1, $this->api->getPreprocessedPages(1));

        try {
            Request::processRequest('Transitions.addPreprocessedPage', ['idSite' => 1, 'pageUrl' => $url . 'b']);
            $this->fail('Expected a URL over the limit to be rejected');
        } catch (\Exception $e) {
            // the response builder rethrows API errors as a plain exception
            self::assertStringStartsWith('The page URL is too long. Use at most 4096 characters.', $e->getMessage());
        }
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
        } catch (PreprocessedPagesBusyException $e) {
            self::assertSame(
                'Another change to the pre-processed pages of this website is in progress. Please try again.',
                $e->getMessage()
            );
            self::assertSame(409, $e->getCode());
        } finally {
            $lock->unlock();
        }

        self::assertSame([], $this->api->getPreprocessedPages(1));
        // another site is not blocked by the lock of site 1
        $this->api->addPreprocessedPage(2, 'https://example.org/a');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        self::assertSame(['https://example.com/a'], $this->getUrls(1));
    }

    public function testRemovingWaitsForTheSiteLockAndFailsWithoutRemovingWhenItIsNotReleased(): void
    {
        $idPage = $this->api->addPreprocessedPage(1, 'https://example.com/a')['idpage'];
        $lock = new Lock(StaticContainer::get(LockBackend::class), Service::LOCK_NAMESPACE);
        self::assertTrue($lock->acquireLock('1'));

        try {
            $this->api->removePreprocessedPage(1, $idPage);
            $this->fail('Expected removing to fail while the site lock is held');
        } catch (PreprocessedPagesBusyException $e) {
            self::assertSame(409, $e->getCode());
        } finally {
            $lock->unlock();
        }

        self::assertSame(['https://example.com/a'], $this->getUrls(1));
        $this->api->removePreprocessedPage(1, $idPage);
        self::assertSame([], $this->getUrls(1));
    }

    public function testChangesOfTheScheduledRebuildsOfAllWebsitesWaitForEachOther(): void
    {
        $lock = new Lock(StaticContainer::get(LockBackend::class), Service::LOCK_NAMESPACE);
        self::assertTrue($lock->acquireLock('reArchiveList'));

        try {
            $this->api->addPreprocessedPage(2, 'https://example.org/a');
            $this->fail('Expected adding to fail while the scheduled rebuilds are changed');
        } catch (PreprocessedPagesBusyException $e) {
            self::assertSame(409, $e->getCode());
        } finally {
            $lock->unlock();
        }

        // nothing was written
        self::assertSame([], $this->getUrls(2));
        $this->api->addPreprocessedPage(2, 'https://example.org/a');
        self::assertSame(['https://example.org/a'], $this->getUrls(2));
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

    public function testALoginDeletedBeforeTheAddWasWrittenIsCleared(): void
    {
        // as if the user was deleted while the add waited for the lock
        FakeAccess::clearAccess(false, [1], [], 'deletedMeanwhile');

        self::assertNull($this->api->addPreprocessedPage(1, 'https://example.com/a')['created_by']);
        self::assertSame(
            [['created_by' => null]],
            Db::fetchAll('SELECT created_by FROM ' . Common::prefixTable('transitions_preprocessed_page'))
        );
    }

    public function testDeletingAUserClearsTheLoginOfRemovedPages(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        FakeAccess::clearAccess(false, [1], [], 'alice');
        $idPage = $this->api->addPreprocessedPage(1, 'https://example.com/a')['idpage'];
        $this->api->removePreprocessedPage(1, $idPage);

        $this->setRole('superuser', 1);
        UsersManagerAPI::getInstance()->deleteUser('alice');

        self::assertSame(
            [['created_by' => null]],
            Db::fetchAll('SELECT created_by FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' WHERE idpage = ?', [$idPage])
        );
    }

    public function testOnlySuperUsersSeeTheLoginsOfSuperUsers(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('root', 'secret-password-2', 'root@example.com');
        (new UsersModel())->setSuperUserAccess('root', true);

        FakeAccess::clearAccess(false, [1], [], 'alice');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        FakeAccess::clearAccess(true, [], [], 'root');
        $this->api->addPreprocessedPage(1, 'https://example.com/b');

        $creators = fn() => array_map(
            fn($page) => [$page['url'], $page['created_by'], $page['created_by_superuser']],
            $this->api->getPreprocessedPages(1)
        );
        self::assertSame([['https://example.com/b', 'root', true], ['https://example.com/a', 'alice', false]], $creators());

        FakeAccess::clearAccess(false, [1], [], 'alice');
        self::assertSame([['https://example.com/b', null, true], ['https://example.com/a', 'alice', false]], $creators());
    }

    /**
     * The removed page is returned as the list showed it, for example for the activity log of
     * `API.Transitions.removePreprocessedPage.end`.
     */
    public function testRemovingAPageReturnsItAsItWasListed(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('root', 'secret-password-2', 'root@example.com');
        (new UsersModel())->setSuperUserAccess('root', true);
        FakeAccess::clearAccess(false, [1], [], 'alice');
        $added = $this->api->addPreprocessedPage(1, 'https://example.com/a');
        FakeAccess::clearAccess(true, [], [], 'root');
        $addedBySuperUser = $this->api->addPreprocessedPage(1, 'https://example.com/b');

        FakeAccess::clearAccess(false, [1], [], 'alice');
        $listed = array_column($this->api->getPreprocessedPages(1), null, 'idpage');
        self::assertSame($listed[$added['idpage']], $this->api->removePreprocessedPage(1, $added['idpage']));
        $removed = $this->api->removePreprocessedPage(1, $addedBySuperUser['idpage']);
        self::assertSame($listed[$addedBySuperUser['idpage']], $removed);
        self::assertSame(['https://example.com/b', null, true], [$removed['url'], $removed['created_by'], $removed['created_by_superuser']]);
        self::assertSame([], $this->getUrls(1));
    }

    public function testDeletingASiteRemovesItsPages(): void
    {
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(2, 'https://example.org/a');

        SitesManagerAPI::getInstance()->deleteSite(2);

        self::assertSame(['https://example.com/a'], $this->getUrls(1));
        self::assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' WHERE idsite = 2'));
    }

    public function testActivatingThePluginClearsLoginsRegisteredAgainAfterThePageWasAdded(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');
        FakeAccess::clearAccess(false, [1], [], 'alice');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        FakeAccess::clearAccess(false, [1], [], 'bob');
        $this->api->addPreprocessedPage(1, 'https://example.com/b');
        $this->setRole('superuser', 1);

        // alice was deleted while the plugin was deactivated, and someone registered the same login later
        Db::query(
            'UPDATE ' . Common::prefixTable('transitions_preprocessed_page') . ' SET created_date = ?',
            ['2025-01-01 00:00:00']
        );
        Db::query('UPDATE ' . Common::prefixTable('user') . ' SET date_registered = ? WHERE login = ?', ['2025-06-01 00:00:00', 'alice']);
        Db::query('UPDATE ' . Common::prefixTable('user') . ' SET date_registered = ? WHERE login = ?', ['2024-06-01 00:00:00', 'bob']);

        PluginManager::getInstance()->getLoadedPlugin('Transitions')->activate();

        self::assertSame(
            ['https://example.com/b' => 'bob', 'https://example.com/a' => null],
            array_column($this->api->getPreprocessedPages(1), 'created_by', 'url')
        );
    }

    public function testActivatingThePluginCatchesUpOnDeletedUsersAndSites(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        FakeAccess::clearAccess(false, [1, 2], [], 'alice');
        $this->api->addPreprocessedPage(1, 'https://example.com/a');
        $this->api->addPreprocessedPage(2, 'https://example.org/a');
        $this->setRole('superuser', 1);
        ArchivingMode::rememberCronArchiving(1);
        ArchivingMode::rememberCronArchiving(2);

        // as if the plugin was deactivated while the user and the site were deleted
        Db::query('DELETE FROM ' . Common::prefixTable('user') . ' WHERE login = ?', ['alice']);
        Db::query('DELETE FROM ' . Common::prefixTable('site') . ' WHERE idsite = 2');

        PluginManager::getInstance()->getLoadedPlugin('Transitions')->activate();

        self::assertSame([null], array_column($this->api->getPreprocessedPages(1), 'created_by'));
        self::assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' WHERE idsite = 2'));
        Option::clearCache();
        self::assertSame(['Transitions.lastCronArchiving_1'], array_keys(Option::getLike('Transitions.lastCronArchiving%')));
    }

    /**
     * Upgraded installs can have tables of different collations, so the logins are never compared across tables.
     */
    public function testLoginsAreMatchedAlsoWhenThePagesTableHasAnotherCollationThanTheUserTable(): void
    {
        $pages = Common::prefixTable('transitions_preprocessed_page');
        $userCollation = Db::fetchOne(
            'SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [Common::prefixTable('user')]
        );
        $pagesCollation = Db::fetchOne(
            'SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$pages]
        );
        $otherCollation = $userCollation === 'utf8mb4_unicode_ci' ? 'utf8mb4_general_ci' : 'utf8mb4_unicode_ci';
        Db::query("ALTER TABLE `$pages` CONVERT TO CHARACTER SET utf8mb4 COLLATE $otherCollation");

        try {
            UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
            UsersManagerAPI::getInstance()->addUser('bob', 'secret-password-2', 'bob@example.com');
            FakeAccess::clearAccess(false, [1], [], 'alice');
            self::assertSame('alice', $this->api->addPreprocessedPage(1, 'https://example.com/a')['created_by']);
            FakeAccess::clearAccess(false, [1], [], 'bob');
            $this->api->addPreprocessedPage(1, 'https://example.com/b');
            FakeAccess::clearAccess(false, [1], [], 'deletedMeanwhile');
            self::assertNull($this->api->addPreprocessedPage(1, 'https://example.com/c')['created_by']);

            // as if the plugin was deactivated while alice was deleted and bob's login registered again
            Db::query('DELETE FROM ' . Common::prefixTable('user') . ' WHERE login = ?', ['alice']);
            Db::query('UPDATE ' . Common::prefixTable('user') . ' SET date_registered = ? WHERE login = ?', ['2037-01-01 00:00:00', 'bob']);
            PluginManager::getInstance()->getLoadedPlugin('Transitions')->activate();

            self::assertSame(
                [['created_by' => null], ['created_by' => null], ['created_by' => null]],
                Db::fetchAll("SELECT created_by FROM `$pages` ORDER BY idpage")
            );
        } finally {
            Db::query("ALTER TABLE `$pages` CONVERT TO CHARACTER SET utf8mb4 COLLATE $pagesCollation");
        }
    }

    public function testAddingAPageSchedulesReArchivingOfOnlyThatPage(): void
    {
        $this->trackPageview('http://example.com/tracked');

        $idPage = $this->api->addPreprocessedPage(1, 'https://www.example.com/tracked')['idpage'];

        self::assertSame(
            [['idSites' => [1], 'pluginName' => 'Transitions', 'report' => Records::getRebuildReportName($idPage)]],
            array_map(
                fn($entry) => array_intersect_key(json_decode($entry, true), ['idSites' => 0, 'pluginName' => 0, 'report' => 0]),
                (new ReArchiveList())->getAll()
            )
        );

        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();

        $invalidations = Db::fetchAll('SELECT DISTINCT idsite, name, report FROM ' . Common::prefixTable('archive_invalidations'));
        self::assertSame([['idsite' => 1, 'name' => 'done.Transitions', 'report' => Records::getRebuildReportName($idPage)]], $invalidations);
    }

    public function testAddingAnUntrackedPageSchedulesItsZeroRecords(): void
    {
        $idPage = $this->api->addPreprocessedPage(1, 'https://example.com/not-tracked-yet')['idpage'];

        self::assertSame([Records::getRebuildReportName($idPage)], $this->getScheduledReports());
    }

    public function testRemovingAPageClearsOnlyItsPendingReArchiving(): void
    {
        $this->trackPageview('http://example.com/a');
        $this->trackPageview('http://example.com/b');

        $idPageA = $this->api->addPreprocessedPage(1, 'http://example.com/a')['idpage'];
        $idPageB = $this->api->addPreprocessedPage(1, 'http://example.com/b')['idpage'];
        StaticContainer::get(ArchiveInvalidator::class)->applyScheduledReArchiving();
        self::assertGreaterThan(0, $this->countInvalidations(Records::getRebuildReportName($idPageA)));

        $this->api->removePreprocessedPage(1, $idPageA);

        self::assertSame(0, $this->countInvalidations(Records::getRebuildReportName($idPageA)));
        self::assertGreaterThan(0, $this->countInvalidations(Records::getRebuildReportName($idPageB)));
    }

    public function testRemovingAPageClearsItsQueuedReArchiving(): void
    {
        $this->trackPageview('http://example.com/a');
        $idPage = $this->api->addPreprocessedPage(1, 'http://example.com/a')['idpage'];
        self::assertCount(1, (new ReArchiveList())->getAll());

        $this->api->removePreprocessedPage(1, $idPage);

        self::assertSame([], (new ReArchiveList())->getAll());
    }

    public function testRemovedPagesAreNeitherListedNorCountedNorArchived(): void
    {
        $this->trackPageview('http://example.com/a');
        $idActionB = $this->trackPageview('http://example.com/b');
        $idPageA = $this->api->addPreprocessedPage(1, 'http://example.com/a')['idpage'];
        $idPageB = $this->api->addPreprocessedPage(1, 'http://example.com/b')['idpage'];

        $this->api->removePreprocessedPage(1, $idPageA);

        self::assertSame(['http://example.com/b'], $this->getUrls(1));
        $service = StaticContainer::get(Service::class);
        self::assertSame([$idPageB], $service->getPageIds(1));
        self::assertSame([$idPageB => $idActionB], $service->getIdActionsByPage(1));
        $status = $this->api->getPreprocessedPageStatus(1, 'http://example.com/a');
        self::assertFalse($status['selected']);
        self::assertNull($status['idPage']);
        self::assertSame(1, $status['used']);
        // its stored records are still served
        self::assertSame($idPageA, $service->getPageForReport(1, 'http://example.com/a')['idpage'] ?? null);
        $this->assertRemoveNotFound(1, $idPageA);

        $this->api->removePreprocessedPage(1, $idPageB);
        self::assertFalse(StaticContainer::get(Dao::class)->hasPagesForSite(1));
    }

    public function testRemovedPagesDontCountTowardsTheLimit(): void
    {
        Config::getInstance()->Transitions = ['max_preprocessed_pages' => 2];
        $idPageA = $this->api->addPreprocessedPage(1, 'https://example.com/a')['idpage'];
        $this->api->addPreprocessedPage(1, 'https://example.com/b');
        $this->api->removePreprocessedPage(1, $idPageA);

        $this->api->addPreprocessedPage(1, 'https://example.com/c');

        $this->assertAddFails(1, 'https://example.com/d', "This website's limit of pre-processed pages (2) is reached. Remove a page to add another one.");
        $this->assertAddFails(1, 'https://example.com/a', "This website's limit of pre-processed pages (2) is reached. Remove a page to add another one.");
    }

    public function testAddingARemovedPageAgainRestoresIt(): void
    {
        UsersManagerAPI::getInstance()->addUser('alice', 'secret-password-1', 'alice@example.com');
        $idPage = $this->api->addPreprocessedPage(1, 'http://example.com/a')['idpage'];
        $this->api->removePreprocessedPage(1, $idPage);
        self::assertSame([], $this->getScheduledReports());
        Db::query('UPDATE ' . Common::prefixTable('transitions_preprocessed_page') . ' SET created_date = ?', ['2025-01-01 00:00:00']);

        FakeAccess::clearAccess(false, [1], [], 'alice');
        $added = $this->api->addPreprocessedPage(1, 'http://example.com/a');

        self::assertSame($idPage, $added['idpage']);
        self::assertSame('alice', $added['created_by']);
        self::assertNotSame('2025-01-01 00:00:00', $added['created_date']);
        self::assertSame([$added], $this->api->getPreprocessedPages(1));
        self::assertNotNull(StaticContainer::get(Dao::class)->getPage(1, $idPage));
        // archiving skipped it while it was removed
        self::assertSame([Records::getRebuildReportName($idPage)], $this->getScheduledReports());
        $this->assertAddFails(1, 'http://example.com/a', 'This page is already selected.');
    }

    public function testResolvesTheIdActionsOfTrackedPages(): void
    {
        $idAction = $this->trackPageview('http://example.com/tracked');
        $idPageTracked = $this->api->addPreprocessedPage(1, 'https://www.example.com/tracked')['idpage'];
        $idPageNotTracked = $this->api->addPreprocessedPage(1, 'https://example.com/not-tracked-yet')['idpage'];

        self::assertSame([$idPageNotTracked => null, $idPageTracked => $idAction], StaticContainer::get(Service::class)->getIdActionsByPage(1));
        self::assertSame([$idPageTracked => $idAction], StaticContainer::get(Service::class)->getIdActionsByPage(1, [$idPageTracked]));
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

    /**
     * @return list<string>
     */
    private function getScheduledReports(): array
    {
        return array_map(fn($entry) => json_decode($entry, true)['report'], (new ReArchiveList())->getAll());
    }

    private function countInvalidations(string $report): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('archive_invalidations') . ' WHERE report = ?',
            [$report]
        );
    }

    /**
     * The last deletion of old reports deleted the records of pages removed before its newest deleted month, so their
     * rows are deleted too, unless a table that may hold the page's records still does. Selected pages, and pages
     * removed later, are kept.
     */
    public function testRemovedPagesAreDeletedWithTheirOldReports(): void
    {
        $removed = $this->addPagesRemovedAt([
            'https://example.com/old' => '2026-01-31 23:59:59',
            'https://example.com/kept' => '2026-02-01 00:00:00',
            'https://example.com/stillStored' => '2026-01-15 00:00:00',
        ]);
        $selected = $this->api->addPreprocessedPage(1, 'https://example.com/selected')['idpage'];
        $this->addPagesRemovedAt(['https://example.org/old' => '2025-06-01 00:00:00'], 2);
        // archived in a website's timezone, the page's last day can be in the next month's table, which isn't deleted
        $this->insertPageviewsRecord(1, $removed['https://example.com/stillStored'], '2026-02-01');
        PrivacyManager::savePurgeDataSettings(self::DELETE_ALL_OLD_REPORTS);
        // which deleted the reports of February and older ones
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_REPORTS, strtotime('2026-06-15'));

        (new Tasks())->deleteRemovedPreprocessedPagesOfDeletedReports();

        self::assertSame(
            [$removed['https://example.com/kept'], $removed['https://example.com/stillStored'], $selected],
            array_map('intval', array_column(Db::fetchAll('SELECT idpage FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' ORDER BY idpage'), 'idpage'))
        );

        // a page removed and deleted is added again as a new page
        $readded = $this->api->addPreprocessedPage(1, 'https://example.com/old');
        self::assertNotSame($removed['https://example.com/old'], $readded['idpage']);
    }

    public function testRemovedPagesAreKeptUntilTheDeletionOfOldReportsHasRun(): void
    {
        $this->addPagesRemovedAt(['https://example.com/old' => '2025-01-01 00:00:00']);
        PrivacyManager::savePurgeDataSettings(self::DELETE_ALL_OLD_REPORTS);
        Option::delete(PrivacyManager::OPTION_LAST_DELETE_PIWIK_REPORTS);

        (new Tasks())->deleteRemovedPreprocessedPagesOfDeletedReports();

        self::assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page')));
    }

    public function testARemovedPageRestoredAndRemovedAgainMeanwhileIsKept(): void
    {
        $idPage = $this->addPagesRemovedAt(['https://example.com/old' => '2025-01-01 00:00:00'])['https://example.com/old'];
        $dao = new class extends Dao {
            public function hasStoredRecords(int $idSite, int $idPage, array $months): bool
            {
                // as if an admin added the page again and removed it, between the check and the delete
                $this->restorePage($idPage, Date::now()->getDatetime(), null);
                $this->removePage($idSite, $idPage, Date::now()->getDatetime());

                return parent::hasStoredRecords($idSite, $idPage, $months);
            }
        };
        $service = new Service($dao, StaticContainer::get(ArchiveInvalidator::class), StaticContainer::get(LockBackend::class));

        self::assertSame(0, $service->deleteRemovedPagesOfDeletedReports(self::DELETE_ALL_OLD_REPORTS, strtotime('2026-06-15')));
        self::assertSame(1, (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page') . ' WHERE idpage = ? AND removed_date IS NOT NULL',
            [$idPage]
        ));
    }

    /**
     * @dataProvider getKeptOldReports
     */
    public function testRemovedPagesAreKeptWhileOldReportsAreKept(array $settings): void
    {
        $this->addPagesRemovedAt(['https://example.com/old' => '2025-01-01 00:00:00']);
        PrivacyManager::savePurgeDataSettings($settings + self::DELETE_ALL_OLD_REPORTS);
        Option::set(PrivacyManager::OPTION_LAST_DELETE_PIWIK_REPORTS, strtotime('2026-06-15'));

        (new Tasks())->deleteRemovedPreprocessedPagesOfDeletedReports();

        self::assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('transitions_preprocessed_page')));
    }

    /**
     * @return iterable<string, array{array<string, int>}>
     */
    public function getKeptOldReports(): iterable
    {
        yield 'deletion of old reports disabled' => [['delete_reports_enable' => 0]];
        foreach (['day', 'week', 'month', 'year', 'segment'] as $kept) {
            yield $kept . ' reports kept' => [['delete_reports_keep_' . $kept . '_reports' => 1]];
        }
    }

    /**
     * @param array<string, string> $removedDates by URL
     * @return array<string, int> the idpage by URL
     */
    private function addPagesRemovedAt(array $removedDates, int $idSite = 1): array
    {
        $idPages = [];
        foreach ($removedDates as $url => $removedDate) {
            $idPages[$url] = $this->api->addPreprocessedPage($idSite, $url)['idpage'];
            $this->api->removePreprocessedPage($idSite, $idPages[$url]);
            Db::query(
                'UPDATE ' . Common::prefixTable('transitions_preprocessed_page') . ' SET removed_date = ? WHERE idpage = ?',
                [$removedDate, $idPages[$url]]
            );
        }

        return $idPages;
    }

    private function insertPageviewsRecord(int $idSite, int $idPage, string $date): void
    {
        Db::query(
            'INSERT INTO `' . ArchiveTableCreator::getNumericTable(Date::factory($date), true) . '` (idarchive, name, idsite, date1, date2, period, ts_archived, value)'
            . ' VALUES (1, ?, ?, ?, ?, 1, ?, 1)',
            [Records::getRecordName($idPage, Records::PAGEVIEWS), $idSite, $date, $date, $date . ' 12:00:00']
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
