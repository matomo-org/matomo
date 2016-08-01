<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\Access;
use Piwik\AuthResult;
use Piwik\Db;
use Piwik\NoAccessException;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class AccessTest extends IntegrationTestCase
{
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
        $access = new Access();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->hasSuperUserAccess());
    }

    public function test_GetLogin_UserIsNotAnonymous_WhenSuperUserAccess()
    {
        $access = new Access();
        $access->setSuperUserAccess(true);
        $this->assertNotEmpty($access->getLogin());
        $this->assertNotSame('anonymous', $access->getLogin());
    }

    public function testHasSuperUserAccessWithNoSuperUserAccess()
    {
        $access = new Access();
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
     * @expectedException \Piwik\NoAccessException
     */
    public function testCheckUserHasSuperUserAccessWithEmptyAccess()
    {
        $access = new Access();
        $access->checkUserHasSuperUserAccess();
    }

    public function testCheckUserHasSuperUserAccessWithSuperUserAccess()
    {
        $access = new Access();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSuperUserAccess();
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function testCheckUserHasSomeAdminAccessWithEmptyAccess()
    {
        $access = new Access();
        $access->checkUserHasSomeAdminAccess();
    }

    public function testCheckUserHasSomeAdminAccessWithSuperUserAccess()
    {
        $access = new Access();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSomeAdminAccess();
    }

    public function test_isUserHasSomeAdminAccess_WithSuperUserAccess()
    {
        $access = new Access();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->isUserHasSomeAdminAccess());
    }

    public function test_isUserHasSomeAdminAccess_WithOnlyViewAccess()
    {
        $access = new Access();
        $this->assertFalse($access->isUserHasSomeAdminAccess());
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function test_CheckUserHasSomeAdminAccessWithSomeAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasSomeAdminAccess();
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function test_checkUserHasAdminAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasAdminAccess('2');
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function test_checkUserHasSomeViewAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasSomeViewAccess();
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function test_checkUserHasViewAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasViewAccess('2');
    }

    public function testCheckUserHasSomeAdminAccessWithSomeAccess()
    {
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->once())
             ->method('getRawSitesWithSomeViewAccess')
             ->will($this->returnValue($this->buildAdminAccessForSiteIds(array(2, 9))));

        $mock->checkUserHasSomeAdminAccess();
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function testCheckUserHasSomeViewAccessWithEmptyAccess()
    {
        $access = new Access();
        $access->checkUserHasSomeViewAccess();
    }

    public function testCheckUserHasSomeViewAccessWithSuperUserAccess()
    {
        $access = new Access();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSomeViewAccess();
    }

    public function testCheckUserHasSomeViewAccessWithSomeAccess()
    {
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildViewAccessForSiteIds(array(1, 2, 3, 4))));

        $mock->checkUserHasSomeViewAccess();
    }

    /**
     * @expectedException \Piwik\NoAccessException
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
        /** @var Access $mock */
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildViewAccessForSiteIds(array(1, 2, 3, 4))));

        $mock->checkUserHasViewAccess('1,3');
    }

    public function testCheckUserHasViewAccessWithSomeAccessSuccessAllSites()
    {
        /** @var Access $mock */
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->any())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildViewAccessForSiteIds(array(1, 2, 3, 4))));

        $mock->checkUserHasViewAccess('all');
    }

    /**
     * @expectedException \Piwik\NoAccessException
     */
    public function testCheckUserHasViewAccessWithSomeAccessFailure()
    {
        $mock = $this->getMockBuilder('Piwik\Access')->setMethods(array('getSitesIdWithAtLeastViewAccess'))->getMock();

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess(array(1, 5));
    }

    public function testCheckUserHasAdminAccessWithSuperUserAccess()
    {
        $access = new Access();
        $access->setSuperUserAccess(true);
        $access->checkUserHasAdminAccess(array());
    }

    /**
     * @expectedException \Piwik\NoAccessException
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
     * @expectedException \Piwik\NoAccessException
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
        $access = new Access();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->reloadAccess(null));
    }

    public function testReloadAccess_ShouldResetTokenAuthAndLogin_IfAuthIsNotValid()
    {
        $mock = $this->createAuthMockWithAuthResult(AuthResult::SUCCESS);
        $access = new Access();

        $this->assertTrue($access->reloadAccess($mock));
        $this->assertSame('login', $access->getLogin());
        $this->assertSame('token', $access->getTokenAuth());

        $mock = $this->createAuthMockWithAuthResult(AuthResult::FAILURE);

        $this->assertFalse($access->reloadAccess($mock));
        $this->assertNull($access->getLogin());
        $this->assertNull($access->getTokenAuth());
    }

    public function testReloadAccessWithMockedAuthValid()
    {
        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token')));

        $mock->expects($this->any())->method('getName')->will($this->returnValue("test name"));

        $access = new Access();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->hasSuperUserAccess());
    }

    public function test_reloadAccess_loadSitesIfNeeded_doesActuallyResetAllSiteIdsAndRequestThemAgain()
    {
        /** @var Access $mock */
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->at(0))
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds(array(1,2,3,4))));

        $mock->expects($this->at(1))
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds(array(1))));

        // should succeed as permission to 1,2,3,4
        $mock->checkUserHasAdminAccess('1,3');

        // should clear permissions
        $mock->reloadAccess();

        try {
            // should fail as now only permission to site 1
            $mock->checkUserHasAdminAccess('1,3');
            $this->fail('An expected exception has not been triggered. Permissions were not resetted');
        } catch (NoAccessException $e) {

        }

        $mock->checkUserHasAdminAccess('1'); // it should have access to site "1"

        $mock->setSuperUserAccess(true);

        $mock->reloadAccess();

        // should now have permission as it is a superuser
        $mock->checkUserHasAdminAccess('1,3');
    }

    public function test_doAsSuperUser_ChangesSuperUserAccessCorrectly()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());

        Access::doAsSuperUser(function () {
            AccessTest::assertTrue(Access::getInstance()->hasSuperUserAccess());
        });

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());
    }

    public function test_doAsSuperUser_RemovesSuperUserAccess_IfExceptionThrown()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());

        try {
            Access::doAsSuperUser(function () {
                throw new Exception();
            });

            $this->fail("Exception was not propagated by doAsSuperUser.");
        } catch (Exception $ex)
        {
            // pass
        }

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());
    }

    public function test_doAsSuperUser_ReturnsCallbackResult()
    {
        $result = Access::doAsSuperUser(function () {
            return 24;
        });
        $this->assertEquals(24, $result);
    }

    public function test_reloadAccess_DoesNotRemoveSuperUserAccess_IfUsedInDoAsSuperUser()
    {
        Access::getInstance()->setSuperUserAccess(false);

        Access::doAsSuperUser(function () {
            $access = Access::getInstance();

            AccessTest::assertTrue($access->hasSuperUserAccess());
            $access->reloadAccess();
            AccessTest::assertTrue($access->hasSuperUserAccess());
        });
    }

    private function buildAdminAccessForSiteIds($siteIds)
    {
        $access = array();

        foreach ($siteIds as $siteId) {
            $access[] = array('access' => 'admin', 'idsite' => $siteId);
        }

        return $access;
    }

    private function buildViewAccessForSiteIds($siteIds)
    {
        $access = array();

        foreach ($siteIds as $siteId) {
            $access[] = array('access' => 'admin', 'idsite' => $siteId);
        }

        return $access;
    }

    private function createPiwikAuthMockInstance()
    {
        return $this->getMockBuilder('Piwik\\Auth')
                    ->setMethods(array('authenticate', 'getName', 'getTokenAuthSecret', 'getLogin', 'setTokenAuth', 'setLogin',
            'setPassword', 'setPasswordHash'))
                    ->getMock();
    }

    private function createAccessMockWithAccessToSitesButUnauthenticated($idSites)
    {
        $mock = $this->getMockBuilder('Piwik\Access')
                     ->setMethods(array('getRawSitesWithSomeViewAccess', 'loadSitesIfNeeded'))
                     ->getMock();

        // this method will be actually never called as it is unauthenticated. The tests are supposed to fail if it
        // suddenly does get called as we should not query for sites if it is not authenticated.
        $mock->expects($this->any())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds($idSites)));

        return $mock;
    }

    private function createAccessMockWithAuthenticatedUser($methodsToMock = array())
    {
        $methods = array('authenticate');

        foreach ($methodsToMock as $methodToMock) {
            $methods[] = $methodToMock;
        }

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->atLeast(1))
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token')));

        $mock = $this->getMockBuilder('Piwik\Access')->setMethods($methods)->getMock();
        $mock->reloadAccess($authMock);

        return $mock;
    }

    private function createAuthMockWithAuthResult($resultCode)
    {
        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult($resultCode, 'login', 'token')));

        return $mock;
    }

}
