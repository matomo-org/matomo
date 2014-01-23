<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\AuthResult;

/**
 * Class Core_AccessTest
 *
 * @group Core
 */
class Core_AccessTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        Access::setSingletonInstance(null);
    }

    public function testGetListAccess()
    {
        $accessList = Access::getListAccess();
        $shouldBe = array('noaccess', 'view', 'admin', 'superuser');
        $this->assertEquals($shouldBe, $accessList);
    }

    public function testGetTokenAuthWithEmptyAccess()
    {
        $access = new Access();
        $this->assertNull($access->getTokenAuth());
    }

    public function testGetLoginWithEmptyAccess()
    {
        $access = new Access();
        $this->assertNull($access->getLogin());
    }

    public function testHasSuperUserAccessWithEmptyAccess()
    {
        $access = new Access();
        $this->assertFalse($access->hasSuperUserAccess());
    }

    public function testHasSuperUserAccessWithSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->hasSuperUserAccess());
    }

    public function testHasSuperUserAccessWithNoSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(false);
        $this->assertFalse($access->hasSuperUserAccess());
    }

    public function testGetSitesIdWithAtLeastViewAccessWithEmptyAccess()
    {
        $access = new Access();
        $this->assertEmpty($access->getSitesIdWithAtLeastViewAccess());
    }

    public function testGetSitesIdWithAdminAccessWithEmptyAccess()
    {
        $access = new Access();
        $this->assertEmpty($access->getSitesIdWithAdminAccess());
    }

    public function testGetSitesIdWithViewAccessWithEmptyAccess()
    {
        $access = new Access();
        $this->assertEmpty($access->getSitesIdWithViewAccess());
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasSuperUserAccessWithEmptyAccess()
    {
        $access = new Access();
        $access->checkUserHasSuperUserAccess();
    }

    public function testCheckUserHasSuperUserAccessWithSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSuperUserAccess();
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasSomeAdminAccessWithEmptyAccess()
    {
        $access = new Access();
        $access->checkUserHasSomeAdminAccess();
    }

    public function testCheckUserHasSomeAdminAccessWithSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSomeAdminAccess();
    }

    public function testCheckUserHasSomeAdminAccessWithSomeAccess()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAdminAccess')
        );

        $mock->expects($this->once())
             ->method('getSitesIdWithAdminAccess')
             ->will($this->returnValue(array(2, 9)));

        $mock->checkUserHasSomeAdminAccess();
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasSomeViewAccessWithEmptyAccess()
    {
        $access = new Access();
        $access->checkUserHasSomeViewAccess();
    }

    public function testCheckUserHasSomeViewAccessWithSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSomeViewAccess();
    }

    public function testCheckUserHasSomeViewAccessWithSomeAccess()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasSomeViewAccess();
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasViewAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $access = new Access();
        $access->checkUserHasViewAccess(array());
    }

    public function testCheckUserHasViewAccessWithSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->checkUserHasViewAccess(array());
    }

    public function testCheckUserHasViewAccessWithSomeAccessSuccessIdSitesAsString()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess('1,3');
    }

    public function testCheckUserHasViewAccessWithSomeAccessSuccessAllSites()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->any())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess('all');
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasViewAccessWithSomeAccessFailure()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess(array(1, 5));
    }

    public function testCheckUserHasAdminAccessWithSuperUserAccess()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->checkUserHasAdminAccess(array());
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasAdminAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $access = new Access();
        $access->checkUserHasViewAccess(array());
    }

    public function testCheckUserHasAdminAccessWithSomeAccessSuccessIdSitesAsString()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAdminAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAdminAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasAdminAccess('1,3');
    }

    public function testCheckUserHasAdminAccessWithSomeAccessSuccessAllSites()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAdminAccess', 'getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->any())
            ->method('getSitesIdWithAdminAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->expects($this->any())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasAdminAccess('all');
    }

    /**
     * @expectedException Piwik\NoAccessException
     */
    public function testCheckUserHasAdminAccessWithSomeAccessFailure()
    {
        $mock = $this->getMock(
            'Piwik\Access',
            array('getSitesIdWithAdminAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAdminAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasAdminAccess(array(1, 5));
    }

    public function testReloadAccessWithEmptyAuth()
    {
        $access = new Access();
        $this->assertFalse($access->reloadAccess(null));
    }

    public function testReloadAccessWithEmptyAuthSuperUser()
    {
        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->reloadAccess(null));
    }

    public function testReloadAccessWithMockedAuthValid()
    {
        $mock = $this->getMock('\\Piwik\\Auth', array('authenticate', 'getName', 'initSession'));
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token')));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $access = Access::getInstance();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->hasSuperUserAccess());
    }
}