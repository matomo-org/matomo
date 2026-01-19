<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentEditor
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }
        if (!Fixture::siteCreated(2)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }
    }

    public function testGetAllForOneWebsiteReturnsSortedSegments()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAdminUser();

        $expectedOrder = [
            // 1) my segments
            'segment 1',
            'segment 3',
            'segment 7',

            // 2) segments created by a super user that were shared with all users
            'segment 5',
            'segment 9',

            // 3) segments created by other users (which are visible to all super users)
            // not a super user, so can't see those
        ];

        $segments     = $this->api->getAll($idSite = 1);
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    public function testGetAllForAllWebsitesReturnsSortedSegments()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAdminUser();

        $expectedOrder = [
            // 1) my segments
            'segment 1',
            'segment 2',
            'segment 3',
            'segment 7',

            // 2) segments created by a super user that were shared with all users
            'segment 5',
            'segment 6',
            'segment 9',

            // 3) segments created by other users (which are visible to all super users)
            // not a super user, so can't see those
        ];

        $segments     = $this->api->getAll();
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    public function testGetAllForAllWebsitesReturnsSortedSegmentsAsSuperUser()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAnotherSuperUser();

        $expectedOrder = [
            // 1) my segments
            'segment 9',

            // 2) segments created by a super user that were shared with all users
            'segment 5',
            'segment 6',

            // 3) segments created by other users (which are visible to all super users)
            'segment 1',
            'segment 2',
            'segment 3',
            'segment 4',
            'segment 7',
            'segment 8',
        ];

        $segments     = $this->api->getAll();
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    public function testGetAllForOneWebsiteReturnsSortedSegmentsAsSuperUser()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAnotherSuperUser();

        $expectedOrder = [
            // 1) my segments
            'segment 9',

            // 2) segments created by a super user that were shared with all users
            'segment 5',

            // 3) segments created by other users (which are visible to all super users)
            'segment 1',
            'segment 3',
            'segment 4',
            'segment 7',
            'segment 8',
        ];

        $segments     = $this->api->getAll($idSite = 1);
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    /**
     * @return bool|int
     */
    protected function createSegments()
    {
        Rules::setBrowserTriggerArchiving(false);
        $this->setAdminUser();
        $this->api->add('segment 1', 'visitCount<2', $idSite = 1, $autoArchive = true, $enableAllUsers = false);
        $this->api->add('segment 2', 'countryCode==fr', $idSite = 2, $autoArchive = false, $enableAllUsers = false);
        $this->api->add('segment 3', 'visitCount<2', $idSite = 1, $autoArchive = true, $enableAllUsers = false);

        $this->setSuperUser();
        $this->api->add('segment 4', 'countryCode!=fr', $idSite = false, $autoArchive = false, $enableAllUsers = false);
        $this->api->add('segment 5', 'countryCode!=fr', $idSite = 1, $autoArchive = false, $enableAllUsers = true);
        $this->api->add('segment 6', 'visitCount<2', $idSite = 2, $autoArchive = true, $enableAllUsers = true);

        $this->setAdminUser();
        $this->api->add('segment 7', 'visitCount<2', $idSite = 1, $autoArchive = true, $enableAllUsers = false);

        $this->setAnotherAdminUser();
        $this->api->add('segment 8', 'visitCount<2', $idSite = 1, $autoArchive = true, $enableAllUsers = false);

        $this->setAnotherSuperUser();
        $this->api->add('segment 9', 'countryCode!=fr', $idSite = false, $autoArchive = false, $enableAllUsers = true);
        Rules::setBrowserTriggerArchiving(true);
    }

    public function testUserCanStillEditSegmentAfterSuperUserSharedIt()
    {
        self::expectNotToPerformAssertions();

        $segment = 'pageUrl=@%252F1';
        Fixture::createWebsite('2020-03-03 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;

        FakeAccess::$identity    = 'normalUser';
        FakeAccess::$superUser   = false;
        FakeAccess::$idSitesView = [1];

        $idSegment = Request::processRequest('SegmentEditor.add', [
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 1,
            'enabledAllUsers' => 0,
        ]);

        FakeAccess::$identity    = 'superUserLogin';
        FakeAccess::$superUser   = true;
        FakeAccess::$idSitesView = [];

        Request::processRequest('SegmentEditor.update', [
            'idSegment'       => $idSegment,
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 1,
            'enabledAllUsers' => 1,
        ]);

        FakeAccess::$identity    = 'normalUser';
        FakeAccess::$superUser   = false;
        FakeAccess::$idSitesView = [1];

        Request::processRequest('SegmentEditor.update', [
            'idSegment'       => $idSegment,
            'name'            => 'new segment name',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 1,
            'enabledAllUsers' => 1,
        ]);
    }

    public function testNormalUserCannotCreateSharedSegment()
    {
        self::expectException(\Exception::class);
        self::expectExceptionMessage('enabledAllUsers=1 requires Super User access');

        $segment = 'pageUrl=@%252F1';
        Fixture::createWebsite('2020-03-03 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;

        FakeAccess::$identity    = 'normalUser';
        FakeAccess::$superUser   = false;
        FakeAccess::$idSitesView = [1];

        $idSegment = Request::processRequest('SegmentEditor.add', [
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 1,
            'enabledAllUsers' => 1,
        ]);
    }


    public function testUserCanNoLongerEditSegmentAfterSuperUserSharedItAcrossSites()
    {
        $segment = 'pageUrl=@%252F1';
        Fixture::createWebsite('2020-03-03 00:00:00');

        Config::getInstance()->General['enable_browser_archiving_triggering'] = 0;

        FakeAccess::$identity    = 'normalUser';
        FakeAccess::$superUser   = false;
        FakeAccess::$idSitesView = [1];

        $idSegment = Request::processRequest('SegmentEditor.add', [
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 1,
            'enabledAllUsers' => 0,
        ]);

        FakeAccess::$identity    = 'superUserLogin';
        FakeAccess::$superUser   = true;
        FakeAccess::$idSitesView = [];

        Request::processRequest('SegmentEditor.update', [
            'idSegment'       => $idSegment,
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 0,
            'autoArchive'     => 1,
            'enabledAllUsers' => 0,
        ]);

        self::expectException(\Exception::class);
        self::expectExceptionMessage('SegmentEditor_UpdatingAllSitesSegmentPermittedToSuperUser');

        FakeAccess::$identity    = 'normalUser';
        FakeAccess::$superUser   = false;
        FakeAccess::$idSitesView = [1];

        Request::processRequest('SegmentEditor.update', [
            'idSegment'       => $idSegment,
            'name'            => 'new segment name',
            'definition'      => $segment,
            'idSite'          => 0,
            'autoArchive'     => 1,
            'enabledAllUsers' => 0,
        ]);
    }

    /**
     * @dataProvider requiredAccessProvider
     */
    public function testNormalUserCannotChangeSegmentScopeBetweenSitesWhenNoPermissionForTargetSite(string $requiredAccess): void
    {
        $segment = 'pageUrl=@%252F1';

        $this->withAddingSegmentAccess($requiredAccess, function () use ($segment, $requiredAccess) {
            FakeAccess::$identity  = 'normalUser';
            FakeAccess::$superUser = false;
            $this->setAccessForRequiredAccess($requiredAccess, [1], [1, 2]);

            $idSegment = Request::processRequest('SegmentEditor.add', [
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 1,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            self::expectException(\Exception::class);
            if ($requiredAccess !== 'view') {
                self::expectExceptionMessage('Changing value for enable_only_idsite requires permission to add segments for the target site.');
            }

            Request::processRequest('SegmentEditor.update', [
                'idSegment'       => $idSegment,
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 2,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);
        });
    }

    /**
     * @dataProvider requiredAccessProvider
     */
    public function testUserCanChangeSegmentScopeBetweenSitesWithPermissionForTargetSite(string $requiredAccess): void
    {
        $segment = 'pageUrl=@%252F1';

        $this->withAddingSegmentAccess($requiredAccess, function () use ($segment, $requiredAccess) {
            FakeAccess::$identity  = 'normalUser';
            FakeAccess::$superUser = false;
            $this->setAccessForRequiredAccess($requiredAccess, [1, 2]);

            $idSegment = Request::processRequest('SegmentEditor.add', [
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 1,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            Request::processRequest('SegmentEditor.update', [
                'idSegment'       => $idSegment,
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 2,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            $updatedSegment = $this->api->get($idSegment);
            $this->assertSame(2, (int)$updatedSegment['enable_only_idsite']);
        });
    }

    public function testSuperUserCanChangeSegmentScopeBetweenSitesWithoutTargetSitePermission(): void
    {
        $segment = 'pageUrl=@%252F1';

        $this->withAddingSegmentAccess('admin', function () use ($segment) {
            FakeAccess::$identity    = 'superUserLogin';
            FakeAccess::$superUser   = true;
            FakeAccess::$idSitesView = [];

            $idSegment = Request::processRequest('SegmentEditor.add', [
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 1,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            Request::processRequest('SegmentEditor.update', [
                'idSegment'       => $idSegment,
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 2,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            $updatedSegment = $this->api->get($idSegment);
            $this->assertSame(2, (int)$updatedSegment['enable_only_idsite']);
        });
    }

    public function testUserCannotChangeSegmentScopeBetweenSitesWhenNotSegmentOwner(): void
    {
        $segment = 'pageUrl=@%252F1';

        $this->withAddingSegmentAccess('view', function () use ($segment) {
            FakeAccess::$identity    = 'segmentOwner';
            FakeAccess::$superUser   = false;
            FakeAccess::$idSitesView = [1, 2];

            $idSegment = Request::processRequest('SegmentEditor.add', [
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 1,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            FakeAccess::$identity    = 'anotherUser';
            FakeAccess::$superUser   = false;
            FakeAccess::$idSitesView = [1, 2];

            self::expectException(\Exception::class);
            self::expectExceptionMessage('SegmentEditor_UpdatingForeignSegmentPermittedToSuperUser');

            Request::processRequest('SegmentEditor.update', [
                'idSegment'       => $idSegment,
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 2,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);
        });
    }

    public function testUserCannotChangeSegmentScopeFromSiteToAllSitesEvenWithPermissions(): void
    {
        $segment = 'pageUrl=@%252F1';

        $this->withAddingSegmentAccess('admin', function () use ($segment) {
            FakeAccess::$identity     = 'normalUser';
            FakeAccess::$superUser    = false;
            FakeAccess::$idSitesAdmin = [1];
            FakeAccess::$idSitesView  = [1];

            $idSegment = Request::processRequest('SegmentEditor.add', [
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 1,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            self::expectException(\Exception::class);
            self::expectExceptionMessage('SegmentEditor_UpdatingForeignSegmentPermittedToSuperUser');

            Request::processRequest('SegmentEditor.update', [
                'idSegment'       => $idSegment,
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 0,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);
        });
    }

    public function testUserCannotChangeSegmentScopeFromAllSitesToSiteWhenSegmentOwnedBySuperUser(): void
    {
        $segment = 'pageUrl=@%252F1';

        $this->withAddingSegmentAccess('admin', function () use ($segment) {
            FakeAccess::$identity    = 'superUserLogin';
            FakeAccess::$superUser   = true;
            FakeAccess::$idSitesView = [];

            $idSegment = Request::processRequest('SegmentEditor.add', [
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 0,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);

            FakeAccess::$identity     = 'normalUser';
            FakeAccess::$superUser    = false;
            FakeAccess::$idSitesAdmin = [1];
            FakeAccess::$idSitesView  = [1];

            self::expectException(\Exception::class);
            self::expectExceptionMessage('SegmentEditor_UpdatingForeignSegmentPermittedToSuperUser');

            Request::processRequest('SegmentEditor.update', [
                'idSegment'       => $idSegment,
                'name'            => 'test segment',
                'definition'      => $segment,
                'idSite'          => 1,
                'autoArchive'     => 0,
                'enabledAllUsers' => 0,
            ]);
        });
    }

    public function testSuperUserCanChangeSegmentScopeFromSiteToAllSites(): void
    {
        $segment = 'pageUrl=@%252F1';

        FakeAccess::$identity    = 'superUserLogin';
        FakeAccess::$superUser   = true;
        FakeAccess::$idSitesView = [1];

        $idSegment = Request::processRequest('SegmentEditor.add', [
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 0,
            'enabledAllUsers' => 0,
        ]);

        Request::processRequest('SegmentEditor.update', [
            'idSegment'       => $idSegment,
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 0,
            'autoArchive'     => 0,
            'enabledAllUsers' => 0,
        ]);

        $updatedSegment = $this->api->get($idSegment);
        $this->assertSame(0, (int)$updatedSegment['enable_only_idsite']);
    }

    public function testSuperUserCanChangeSegmentScopeFromAllSitesToSite(): void
    {
        $segment = 'pageUrl=@%252F1';

        FakeAccess::$identity    = 'superUserLogin';
        FakeAccess::$superUser   = true;
        FakeAccess::$idSitesView = [1];

        $idSegment = Request::processRequest('SegmentEditor.add', [
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 0,
            'autoArchive'     => 0,
            'enabledAllUsers' => 0,
        ]);

        Request::processRequest('SegmentEditor.update', [
            'idSegment'       => $idSegment,
            'name'            => 'test segment',
            'definition'      => $segment,
            'idSite'          => 1,
            'autoArchive'     => 0,
            'enabledAllUsers' => 0,
        ]);

        $updatedSegment = $this->api->get($idSegment);
        $this->assertSame(1, (int)$updatedSegment['enable_only_idsite']);
    }


    protected function setSuperUser($userName = 'superUserLogin')
    {
        FakeAccess::clearAccess($superUser = true, $idSitesAdmin = [], $idSitesView = [], $userName);
    }

    protected function setAnotherSuperUser()
    {
        $this->setSuperUser('anotherSuperUser');
    }

    protected function setAdminUser($userName = 'myUserLogin')
    {
        FakeAccess::clearAccess($superUser = false, $idSitesAdmin = [1, 2], $idSitesView = [1, 2], $userName);
    }

    protected function setAnotherAdminUser()
    {
        $this->setAdminUser('anotherUserWithAdmin');
    }

    public function requiredAccessProvider(): iterable
    {
        yield 'view access' => ['view'];
        yield 'write access' => ['write'];
        yield 'admin access' => ['admin'];
    }

    protected function setAccessForRequiredAccess(string $requiredAccess, array $sitesForRequiredAccess, array $viewSites = []): void
    {
        FakeAccess::$idSitesView  = $viewSites;
        FakeAccess::$idSitesWrite = [];
        FakeAccess::$idSitesAdmin = [];

        switch ($requiredAccess) {
            case 'view':
                FakeAccess::$idSitesView = $sitesForRequiredAccess;
                break;
            case 'write':
                FakeAccess::$idSitesWrite = $sitesForRequiredAccess;
                break;
            case 'admin':
                FakeAccess::$idSitesAdmin = $sitesForRequiredAccess;
                break;
            default:
                throw new \InvalidArgumentException('Unknown access level for segment permissions test.');
        }
    }

    protected function withAddingSegmentAccess(string $accessLevel, callable $callback): void
    {
        $originalAccess                                                  = Config::getInstance()->General['adding_segment_requires_access'];
        Config::getInstance()->General['adding_segment_requires_access'] = $accessLevel;

        try {
            $callback();
        } finally {
            Config::getInstance()->General['adding_segment_requires_access'] = $originalAccess;
        }
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    protected function createAdminUser()
    {
        \Piwik\Plugins\UsersManager\API::getInstance()->addUser('myUserLogin', 'password', 'test@test.com');
    }

    /**
     * @param $segments
     * @return array
     */
    protected function getNamesFromSegments($segments)
    {
        $segmentNames = [];
        foreach ($segments as $segment) {
            $segmentNames[] = $segment['name'];
        }
        return $segmentNames;
    }
}
