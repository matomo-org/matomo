<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\Integration;

use Piwik\ArchiveProcessor\Rules;
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

    public function test_getAll_forOneWebsite_returnsSortedSegments()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAdminUser();

        $expectedOrder = array(
            // 1) my segments
            'segment 1',
            'segment 3',
            'segment 7',

            // 2) segments created by a super user that were shared with all users
            'segment 5',
            'segment 9',

            // 3) segments created by other users (which are visible to all super users)
            // not a super user, so can't see those
        );

        $segments = $this->api->getAll($idSite = 1);
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    public function test_getAll_forAllWebsites_returnsSortedSegments()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAdminUser();

        $expectedOrder = array(
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
        );

        $segments = $this->api->getAll();
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    public function test_getAll_forAllWebsites_returnsSortedSegments_asSuperUser()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAnotherSuperUser();

        $expectedOrder = array(
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
        );

        $segments = $this->api->getAll();
        $segmentNames = $this->getNamesFromSegments($segments);
        $this->assertSame($expectedOrder, $segmentNames);
    }

    public function test_getAll_forOneWebsite_returnsSortedSegments_asSuperUser()
    {
        $this->createAdminUser();
        $this->createSegments();
        $this->setAnotherSuperUser();

        $expectedOrder = array(
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
        );

        $segments = $this->api->getAll($idSite = 1);
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

    protected function setSuperUser($userName = 'superUserLogin')
    {
        FakeAccess::clearAccess($superUser = true, $idSitesAdmin = array(), $idSitesView = array(), $userName);
    }

    protected function setAnotherSuperUser()
    {
        $this->setSuperUser('anotherSuperUser');
    }

    protected function setAdminUser($userName = 'myUserLogin')
    {
        FakeAccess::clearAccess($superUser = false, $idSitesAdmin = array(1,2), $idSitesView = array(1,2), $userName);
    }

    protected function setAnotherAdminUser()
    {
        $this->setAdminUser('anotherUserWithAdmin');
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
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
        $segmentNames = array();
        foreach ($segments as $segment) {
            $segmentNames[] = $segment['name'];
        }
        return $segmentNames;
    }

}
