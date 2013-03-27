<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class AccessTest extends DatabaseTestCase
{
    /**
     * @group Core
     * @group Access
     */
    public function testGetListAccess()
    {
        $accessList = Piwik_Access::getListAccess();
        $shouldBe = array('noaccess', 'view', 'admin', 'superuser');
        $this->assertEquals($shouldBe, $accessList);
    }

    /**
     * @group Core
     * @group Access
     */
    public function testGetTokenAuthWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $this->assertNull($access->getTokenAuth());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testGetLoginWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $this->assertNull($access->getLogin());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testIsSuperUserWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $this->assertFalse($access->isSuperUser());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testIsSuperUserWithSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $this->assertTrue($access->isSuperUser());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testIsSuperUserWithNoSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(false);
        $this->assertFalse($access->isSuperUser());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testGetSitesIdWithAtLeastViewAccessWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $this->assertEmpty($access->getSitesIdWithAtLeastViewAccess());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testGetSitesIdWithAdminAccessWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $this->assertEmpty($access->getSitesIdWithAdminAccess());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testGetSitesIdWithViewAccessWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $this->assertEmpty($access->getSitesIdWithViewAccess());
    }

    /**
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserIsSuperUserWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $access->checkUserIsSuperUser();
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserIsSuperUserWithSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $access->checkUserIsSuperUser();
    }

    /**
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserHasSomeAdminAccessWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $access->checkUserHasSomeAdminAccess();
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasSomeAdminAccessWithSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $access->checkUserHasSomeAdminAccess();
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasSomeAdminAccessWithSomeAccess()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAdminAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAdminAccess')
            ->will($this->returnValue(array(2, 9)));

        $mock->checkUserHasSomeAdminAccess();
    }

    /**
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserHasSomeViewAccessWithEmptyAccess()
    {
        $access = new Piwik_Access();
        $access->checkUserHasSomeViewAccess();
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasSomeViewAccessWithSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $access->checkUserHasSomeViewAccess();
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasSomeViewAccessWithSomeAccess()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasSomeViewAccess();
    }

    /**
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserHasViewAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $access = new Piwik_Access();
        $access->checkUserHasViewAccess(array());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasViewAccessWithSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $access->checkUserHasViewAccess(array());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasViewAccessWithSomeAccessSuccessIdSitesAsString()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess('1,3');
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasViewAccessWithSomeAccessSuccessAllSites()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->any())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess('all');
    }

    /**
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserHasViewAccessWithSomeAccessFailure()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAtLeastViewAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess(array(1, 5));
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasAdminAccessWithSuperUserAccess()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $access->checkUserHasAdminAccess(array());
    }

    /**
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserHasAdminAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $access = new Piwik_Access();
        $access->checkUserHasViewAccess(array());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasAdminAccessWithSomeAccessSuccessIdSitesAsString()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAdminAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAdminAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasAdminAccess('1,3');
    }

    /**
     * @group Core
     * @group Access
     */
    public function testCheckUserHasAdminAccessWithSomeAccessSuccessAllSites()
    {
        $mock = $this->getMock(
            'Piwik_Access',
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
     * @group Core
     * @group Access
     * @expectedException Piwik_Access_NoAccessException
     */
    public function testCheckUserHasAdminAccessWithSomeAccessFailure()
    {
        $mock = $this->getMock(
            'Piwik_Access',
            array('getSitesIdWithAdminAccess')
        );

        $mock->expects($this->once())
            ->method('getSitesIdWithAdminAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasAdminAccess(array(1, 5));
    }

    /**
     * @group Core
     * @group Access
     */
    public function testReloadAccessWithEmptyAuth()
    {
        $access = new Piwik_Access();
        $this->assertFalse($access->reloadAccess(null));
    }

    /**
     * @group Core
     * @group Access
     */
    public function testReloadAccessWithEmptyAuthSuperUser()
    {
        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $access->setSuperUser(true);
        $this->assertTrue($access->reloadAccess(null));
    }

    /**
     * @group Core
     * @group Access
     */
    public function testReloadAccessWithMockedAuthValid()
    {
        $mock = $this->getMock('Piwik_Login_Auth', array('authenticate'));
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new Piwik_Auth_Result(Piwik_Auth_Result::SUCCESS, 'login', 'token')));

        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->isSuperUser());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testReloadAccessWithMockedAuthSuperUser()
    {
        $mock = $this->getMock('Piwik_Login_Auth', array('authenticate'));
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new Piwik_Auth_Result(Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, 'superuser', 'superusertoken')));

        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertTrue($access->isSuperUser());
    }

    /**
     * @group Core
     * @group Access
     */
    public function testReloadAccessWithMockedAuthInvalidUser()
    {
        $mock = $this->getMock('Piwik_Login_Auth', array('authenticate'));
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new Piwik_Auth_Result(Piwik_Auth_Result::FAILURE_CREDENTIAL_INVALID, null, null)));

        $access = new Piwik_Access();
        Zend_Registry::set('access', $access);
        $this->assertFalse($access->reloadAccess($mock));
    }

}