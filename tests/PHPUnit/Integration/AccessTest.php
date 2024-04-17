<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\Access;
use Piwik\AuthResult;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestCustomCap extends Access\Capability
{
    const ID = 'testcustomcap';
    public function getId(): string
    {
        return self::ID;
    }
    public function getName(): string
    {
        return 'customcap';
    }
    public function getCategory(): string
    {
        return 'test';
    }
    public function getDescription(): string
    {
        return 'lorem ipsum';
    }
    public function getIncludedInRoles(): array
    {
        return array(Access\Role\Admin::ID);
    }
}

/**
 * @group Core
 * @group AccessTest
 */
class AccessTest extends IntegrationTestCase
{
    public function testGetListAccess()
    {
        $roleProvider = new Access\RolesProvider();
        $accessList = $roleProvider->getAllRoleIds();
        $shouldBe = array('view', 'write', 'admin');
        $this->assertEquals($shouldBe, $accessList);
    }

    private function getAccess()
    {
        return new Access(new Access\RolesProvider(), new Access\CapabilitiesProvider());
    }

    public function test_loadSitesIfNeeded_automaticallyAssignsCapabilityWhenIncludedInRole()
    {
        Piwik::addAction('Access.Capability.addCapabilities', function (&$cap) {
            $cap[] = new TestCustomCap();
        });
        \Piwik\Cache::flushAll();

        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'admin', $idSite);

        $this->switchUser('testuser');

        $access = Access::getInstance();
        $access->setSuperUserAccess(false);
        $this->assertEquals('admin', $access->getRoleForSite($idSite));
        $access->checkUserHasCapability($idSite, TestCustomCap::ID);
    }

    public function test_loadSitesIfNeeded_doesNotAutomaticallyAssignCapabilityWhenNotIncludedInRole()
    {
        self::expectException(NoAccessException::class);

        Piwik::addAction('Access.Capability.addCapabilities', function (&$cap) {
            $cap[] = new TestCustomCap();
        });

        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'write', $idSite);

        $this->switchUser('testuser');

        $access = Access::getInstance();
        $access->setSuperUserAccess(false);
        $this->assertEquals('write', $access->getRoleForSite($idSite));

        $access->checkUserHasCapability($idSite, TestCustomCap::ID);
    }

    public function testGetTokenAuthWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertNull($access->getTokenAuth());
    }

    public function testGetLoginWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertNull($access->getLogin());
    }

    public function testHasSuperUserAccessWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertFalse($access->hasSuperUserAccess());
    }

    public function testHasSuperUserAccessWithSuperUserAccess()
    {
        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->hasSuperUserAccess());
    }

    public function test_GetLogin_UserIsNotAnonymous_WhenSuperUserAccess()
    {
        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
        $this->assertNotEmpty($access->getLogin());
        $this->assertNotSame('anonymous', $access->getLogin());
    }

    public function testHasSuperUserAccessWithNoSuperUserAccess()
    {
        $access = $this->getAccess();
        $access->setSuperUserAccess(false);
        $this->assertFalse($access->hasSuperUserAccess());
    }

    public function testGetSitesIdWithAtLeastViewAccessWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertEmpty($access->getSitesIdWithAtLeastViewAccess());
    }

    public function testGetSitesIdWithAdminAccessWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertEmpty($access->getSitesIdWithAdminAccess());
    }

    public function testGetSitesIdWithWriteAccessWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertEmpty($access->getSitesIdWithWriteAccess());
    }

    public function testGetSitesIdWithViewAccessWithEmptyAccess()
    {
        $access = $this->getAccess();
        $this->assertEmpty($access->getSitesIdWithViewAccess());
    }

    public function testCheckUserHasSuperUserAccessWithEmptyAccess()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $access = $this->getAccess();
        $access->checkUserHasSuperUserAccess();
    }

    public function testCheckUserHasSuperUserAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSuperUserAccess();
    }

    public function testCheckUserHasSomeAdminAccessWithEmptyAccess()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $access = $this->getAccess();
        $access->checkUserHasSomeAdminAccess();
    }

    public function testCheckUserHasSomeAdminAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
        $access->checkUserHasSomeAdminAccess();
    }

    public function test_isUserHasSomeAdminAccess_WithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
    }

    public function test_isUserHasSomeAdminAccess_WithOnlyViewAccess()
    {
        $access = $this->getAccess();
        $this->assertFalse($access->isUserHasSomeAdminAccess());
    }

    public function test_CheckUserHasSomeAdminAccessWithSomeAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasSomeAdminAccess();
    }

    public function test_checkUserHasAdminAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasAdminAccess('2');
    }

    public function test_checkUserHasSomeViewAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasSomeViewAccess();
    }

    public function test_checkUserHasViewAccessFails_IfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
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

    public function testCheckUserHasSomeViewAccessWithEmptyAccess()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $access = $this->getAccess();
        $access->checkUserHasSomeViewAccess();
    }

    public function testCheckUserHasSomeViewAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = $this->getAccess();
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

    public function testCheckUserHasSomeWriteAccessWithSomeAccess()
    {
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildWriteAccessForSiteIds(array(1, 2, 3, 4))));

        $mock->checkUserHasSomeWriteAccess();
    }

    public function testCheckUserHasSomeWriteAccessWithSomeAccessDoesNotHaveAccess()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildWriteAccessForSiteIds(array())));

        $mock->checkUserHasSomeWriteAccess();
    }

    public function testCheckUserHasViewAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $access = $this->getAccess();
        $access->checkUserHasViewAccess(array());
    }

    public function testCheckUserHasViewAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

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

    public function testCheckUserHasViewAccessWithSomeAccessFailure()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->getMockBuilder('Piwik\Access')->onlyMethods(array('getSitesIdWithAtLeastViewAccess'))->getMock();

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastViewAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasViewAccess(array(1, 5));
    }

    public function testCheckUserHasWriteAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $access = $this->getAccess();
        $access->checkUserHasWriteAccess(array());
    }

    public function testCheckUserHasWriteAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = Access::getInstance();
        $access->setSuperUserAccess(true);
        $access->checkUserHasWriteAccess(array());
    }

    public function testCheckUserHasWriteAccessWithSomeAccessFailure()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->getMockBuilder('Piwik\Access')->onlyMethods(array('getSitesIdWithAtLeastWriteAccess'))->getMock();

        $mock->expects($this->once())
            ->method('getSitesIdWithAtLeastWriteAccess')
            ->will($this->returnValue(array(1, 2, 3, 4)));

        $mock->checkUserHasWriteAccess(array(1, 5));
    }

    public function testCheckUserHasAdminAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
        $access->checkUserHasAdminAccess(array());
    }

    public function testCheckUserHasAdminAccessWithEmptyAccessNoSiteIdsGiven()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $access = $this->getAccess();
        $access->checkUserHasViewAccess(array());
    }

    public function testCheckUserHasAdminAccessWithSomeAccessSuccessIdSitesAsString()
    {
        $mock = $this->createPartialMock(
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
        self::expectNotToPerformAssertions();

        $mock = $this->createPartialMock(
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

    public function testCheckUserHasAdminAccessWithSomeAccessFailure()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createPartialMock(
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
        $access = $this->getAccess();
        $this->assertFalse($access->reloadAccess(null));
    }

    public function testReloadAccessWithEmptyAuthSuperUser()
    {
        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->reloadAccess(null));
    }

    public function testReloadAccess_ShouldResetTokenAuthAndLogin_IfAuthIsNotValid()
    {
        $mock = $this->createAuthMockWithAuthResult(AuthResult::SUCCESS);
        $access = $this->getAccess();

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

        $access = $this->getAccess();
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
            $this->fail('An expected exception has not been triggered. Permissions were not reset');
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
        } catch (Exception $ex) {
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

    public function test_getAccessForSite_whenUserHasAdminAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'admin', $idSite);

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('admin', Access::getInstance()->getRoleForSite($idSite));
    }

    public function test_getAccessForSite_whenUserHasViewAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'view', $idSite);

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('view', Access::getInstance()->getRoleForSite($idSite));
    }

    public function test_getAccessForSite_whenUserHasWriteAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'write', $idSite);

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('write', Access::getInstance()->getRoleForSite($idSite));
    }

    public function test_getAccessForSite_whenUserHasNoAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('noaccess', Access::getInstance()->getRoleForSite($idSite));
    }

    public function test_getAccessForSite_whenUserIsSuperUser()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');

        Access::getInstance()->setSuperUserAccess(true);
        $this->assertEquals('admin', Access::getInstance()->getRoleForSite($idSite));
    }

    public function test_APIPermissionResponseCode()
    {
        $url = Fixture::getTestRootUrl() . '?' . http_build_query([
                'module'     => 'API',
                'method'     => 'API.getMatomoVersion',
                'token_auth' => 'DOES_NOT_EXIST',
            ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        $this->assertEquals(401, $responseInfo["http_code"]);
    }

    private function switchUser($user)
    {
        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, $user, 'token')));

        Access::getInstance()->setSuperUserAccess(false);
        Access::getInstance()->reloadAccess($mock);
        Access::getInstance()->setSuperUserAccess(true);
    }

    private function buildAdminAccessForSiteIds($siteIds)
    {
        $access = array();

        foreach ($siteIds as $siteId) {
            $access[] = array('access' => 'admin', 'idsite' => $siteId);
        }

        return $access;
    }

    private function buildWriteAccessForSiteIds($siteIds)
    {
        $access = array();

        foreach ($siteIds as $siteId) {
            $access[] = array('access' => 'write', 'idsite' => $siteId);
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
                    ->onlyMethods(array('authenticate', 'getName', 'getTokenAuthSecret', 'getLogin', 'setTokenAuth', 'setLogin',
            'setPassword', 'setPasswordHash'))
                    ->getMock();
    }

    private function createAccessMockWithAccessToSitesButUnauthenticated($idSites)
    {
        $mock = $this->getMockBuilder('Piwik\Access')
                     ->onlyMethods(array('getRawSitesWithSomeViewAccess', 'loadSitesIfNeeded'))
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
        $methods = [];

        foreach ($methodsToMock as $methodToMock) {
            $methods[] = $methodToMock;
        }

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->atLeast(1))
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token')));

        $mock = $this->getMockBuilder('Piwik\Access')->onlyMethods($methods)->getMock();
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
