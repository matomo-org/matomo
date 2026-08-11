<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use PHPUnit\Framework\MockObject\MockObject;
use Exception;
use Piwik\Access;
use Piwik\AuthResult;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

class TestCustomCap extends Access\Capability
{
    public const ID = 'testcustomcap';
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

class TestWriteCap extends Access\Capability
{
    public const ID = 'testwritecap';

    public function getId(): string
    {
        return self::ID;
    }

    public function getName(): string
    {
        return 'writecap';
    }

    public function getCategory(): string
    {
        return 'test';
    }

    public function getDescription(): string
    {
        return 'write capability';
    }

    public function getIncludedInRoles(): array
    {
        return [Access\Role\Write::ID, Access\Role\Admin::ID];
    }
}

class TestAdminOnlyCap extends Access\Capability
{
    public const ID = 'testadminonlycap';

    public function getId(): string
    {
        return self::ID;
    }

    public function getName(): string
    {
        return 'adminonlycap';
    }

    public function getCategory(): string
    {
        return 'test';
    }

    public function getDescription(): string
    {
        return 'admin only capability';
    }

    public function getIncludedInRoles(): array
    {
        return [Access\Role\Admin::ID];
    }
}

class TestScopedTokenCapabilitiesProvider extends Access\CapabilitiesProvider
{
    public function getAllCapabilities(): array
    {
        return [
            new TestWriteCap(),
            new TestAdminOnlyCap(),
        ];
    }

    public function getAllCapabilityIds(): array
    {
        return [
            TestWriteCap::ID,
            TestAdminOnlyCap::ID,
        ];
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

    private function getAccessWithScopedTokenCapabilityProvider()
    {
        return new Access(new Access\RolesProvider(), new TestScopedTokenCapabilitiesProvider());
    }

    /**
     * @return Access&MockObject
     */
    private function getAccessMockWithScopedTokenCapabilityProvider()
    {
        return $this->getMockBuilder('Piwik\Access')
            ->setConstructorArgs([new Access\RolesProvider(), new TestScopedTokenCapabilitiesProvider()])
            ->onlyMethods(['getRawSitesWithSomeViewAccess'])
            ->getMock();
    }

    public function testLoadSitesIfNeededAutomaticallyAssignsCapabilityWhenIncludedInRole()
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

    public function testLoadSitesIfNeededDoesNotAutomaticallyAssignCapabilityWhenNotIncludedInRole()
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

    public function testGetLoginUserIsNotAnonymousWhenSuperUserAccess()
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

    public function testIsUserHasSomeAdminAccessWithSuperUserAccess()
    {
        self::expectNotToPerformAssertions();

        $access = $this->getAccess();
        $access->setSuperUserAccess(true);
    }

    public function testIsUserHasSomeAdminAccessWithOnlyViewAccess()
    {
        $access = $this->getAccess();
        $this->assertFalse($access->isUserHasSomeAdminAccess());
    }

    public function testCheckUserHasSomeAdminAccessWithSomeAccessFailsIfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasSomeAdminAccess();
    }

    public function testCheckUserHasAdminAccessFailsIfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasAdminAccess('2');
    }

    public function testCheckUserHasSomeViewAccessFailsIfUserHasPermissionsToSitesButIsNotAuthenticated()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $mock = $this->createAccessMockWithAccessToSitesButUnauthenticated(array(2, 9));
        $mock->checkUserHasSomeViewAccess();
    }

    public function testCheckUserHasViewAccessFailsIfUserHasPermissionsToSitesButIsNotAuthenticated()
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
        $this->expectException(\Piwik\Http\BadRequestException::class);
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
        /** @var Access&MockObject $mock */
        $mock = $this->createAccessMockWithAuthenticatedUser(array('getRawSitesWithSomeViewAccess'));

        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildViewAccessForSiteIds(array(1, 2, 3, 4))));

        $mock->checkUserHasViewAccess('1,3');
    }

    public function testCheckUserHasViewAccessWithSomeAccessSuccessAllSites()
    {
        /** @var Access&MockObject $mock */
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
        $this->expectException(\Piwik\Http\BadRequestException::class);
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
        $this->expectException(\Piwik\Http\BadRequestException::class);
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

    public function testReloadAccessShouldResetTokenAuthAndLoginIfAuthIsNotValid()
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

    public function testReloadAccessClampsAdminRoleToWriteWhenTokenAccessLevelIsWrite()
    {
        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token', ['token_access_level' => 'write'])));

        $mock = $this->getMockBuilder('Piwik\Access')->onlyMethods(['getRawSitesWithSomeViewAccess'])->getMock();
        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds([1])));

        $this->assertTrue($mock->reloadAccess($authMock));
        $this->assertEquals('write', $mock->getRoleForSite(1));
        $this->assertFalse($mock->isUserHasSomeAdminAccess());
        $this->assertTrue($mock->isUserHasSomeWriteAccess());
    }

    public function testReloadAccessClampsAdminRoleToViewWhenTokenAccessLevelIsView()
    {
        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token', ['token_access_level' => 'view'])));

        $mock = $this->getMockBuilder('Piwik\Access')->onlyMethods(['getRawSitesWithSomeViewAccess'])->getMock();
        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds([1])));

        $this->assertTrue($mock->reloadAccess($authMock));
        $this->assertEquals('view', $mock->getRoleForSite(1));
        $this->assertFalse($mock->isUserHasSomeAdminAccess());
        $this->assertFalse($mock->isUserHasSomeWriteAccess());
    }

    public function testReloadAccessDoesNotClampAdminRoleWhenTokenAccessLevelIsNull()
    {
        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token', ['token_access_level' => null])));

        $mock = $this->getMockBuilder('Piwik\Access')->onlyMethods(['getRawSitesWithSomeViewAccess'])->getMock();
        $mock->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds([1])));

        $this->assertTrue($mock->reloadAccess($authMock));
        $this->assertEquals('admin', $mock->getRoleForSite(1));
        $this->assertTrue($mock->isUserHasSomeAdminAccess());
    }

    public function testReloadAccessRemovesSuperuserAndAppliesWriteRoleToAllSitesWhenTokenIsCapped()
    {
        $idSite1 = Fixture::createWebsite('2010-01-02 00:00:00');
        $idSite2 = Fixture::createWebsite('2010-01-03 00:00:00');

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'write']
            )));

        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertEquals('write', $access->getRoleForSite($idSite1));
        $this->assertEquals('write', $access->getRoleForSite($idSite2));
        $this->assertFalse($access->isUserHasSomeAdminAccess());
        $this->assertTrue($access->isUserHasSomeWriteAccess());
    }

    public function testReloadAccessRemovesSuperuserAndAppliesAdminRoleToAllSitesWhenTokenIsCappedToAdmin()
    {
        $idSite1 = Fixture::createWebsite('2010-01-02 00:00:00');
        $idSite2 = Fixture::createWebsite('2010-01-03 00:00:00');

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'admin']
            )));

        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertEquals('admin', $access->getRoleForSite($idSite1));
        $this->assertEquals('admin', $access->getRoleForSite($idSite2));
        $this->assertTrue($access->isUserHasSomeAdminAccess());
    }

    public function testReloadAccessRemovesSuperuserAndAppliesViewRoleToAllSitesWhenTokenIsCappedToView()
    {
        $idSite1 = Fixture::createWebsite('2010-01-02 00:00:00');
        $idSite2 = Fixture::createWebsite('2010-01-03 00:00:00');

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'view']
            )));

        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertEquals('view', $access->getRoleForSite($idSite1));
        $this->assertEquals('view', $access->getRoleForSite($idSite2));
        $this->assertFalse($access->isUserHasSomeAdminAccess());
        $this->assertFalse($access->isUserHasSomeWriteAccess());
    }

    public function testReloadAccessAppliesCappedSuperuserRoleToAllSitesWithoutAccessTableRows()
    {
        $idSite1 = Fixture::createWebsite('2010-01-02 00:00:00');
        $idSite2 = Fixture::createWebsite('2010-01-03 00:00:00');

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'write']
            )));

        $access = $this->getMockBuilder('Piwik\Access')->onlyMethods(['getRawSitesWithSomeViewAccess'])->getMock();
        $access->expects($this->any())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue([]));

        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertEquals('write', $access->getRoleForSite($idSite1));
        $this->assertEquals('write', $access->getRoleForSite($idSite2));

        // getRoleForSite() answers without enumerating, so assert the site lists separately: those do
        // enumerate, and both views of the clamp must agree.
        $this->assertEquals([$idSite1, $idSite2], array_values($access->getSitesIdWithAtLeastWriteAccess()));
        $this->assertEquals([], array_values($access->getSitesIdWithAdminAccess()));
    }

    public function testGetRoleForSiteDoesNotEnumerateSitesForCappedSuperuserToken()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'write']
            )));

        // Enumerating every site to answer a single-site question is what made tracker authentication
        // scan the whole site table on every request, and it turned a transient failure of that query
        // into a silent rejection of a token that should have been accepted.
        $sitesModelMock = $this->getMockBuilder(\Piwik\Plugins\SitesManager\Model::class)
            ->onlyMethods(['getSitesId'])
            ->getMock();
        $sitesModelMock->expects($this->never())->method('getSitesId');

        $access = $this->getMockBuilder('Piwik\Access')
            ->onlyMethods(['getRawSitesWithSomeViewAccess', 'getSitesManagerModel'])
            ->getMock();
        $access->expects($this->any())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue([]));
        $access->expects($this->any())
            ->method('getSitesManagerModel')
            ->will($this->returnValue($sitesModelMock));

        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertEquals('write', $access->getRoleForSite($idSite));
    }

    public function testGetRoleForSiteIgnoresLowerExplicitAccessRowForCappedSuperuserToken()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'write']
            )));

        // A leftover view row from before the user was promoted must not reduce the token below what the
        // same token grants on a site with no row at all; an uncapped superuser ignores such rows too.
        $access = $this->getMockBuilder('Piwik\Access')->onlyMethods(['getRawSitesWithSomeViewAccess'])->getMock();
        $access->expects($this->any())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue([['idsite' => $idSite, 'access' => 'view']]));

        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertEquals('write', $access->getRoleForSite($idSite));
        $this->assertEquals([$idSite], array_values($access->getSitesIdWithAtLeastWriteAccess()));
    }

    public function testReloadAccessKeepsSuperuserWhenTokenIsNotCapped()
    {
        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => null]
            )));

        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertTrue($access->hasSuperUserAccess());
    }

    public function testReloadAccessTreatsEmptyStringAccessLevelInAuthContextAsUnscoped()
    {
        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => '']
            )));

        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertTrue($access->hasSuperUserAccess());
    }

    public function testReloadAccessDeniesAllAccessWhenAuthContextDeclaresUnrecognisedAccessLevel()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $mock = $this->createPiwikAuthMockInstance();
        $mock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'notalevel']
            )));

        // A scope that cannot be recognised must deny rather than fall through to the user's full access.
        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($mock));
        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertSame('noaccess', $access->getRoleForSite($idSite));
        $this->assertEmpty($access->getSitesIdWithAtLeastViewAccess());
    }

    public function testReloadAccessKeepsTokenAccessLevelWhenReloadedWhileSuperUserAccessIsSet()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->any())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS,
                'adminlogin',
                'token',
                ['token_access_level' => 'view']
            )));

        /** @var Access&MockObject $access */
        $access = $this->getMockBuilder('Piwik\Access')->onlyMethods(['getRawSitesWithSomeViewAccess'])->getMock();
        $access->expects($this->any())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue([['idsite' => $idSite, 'access' => 'admin']]));

        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertSame('view', $access->getRoleForSite($idSite));

        // Any reloadAccess() reached while super-user access is temporarily on, as happens inside
        // Access::doAsSuperUser(), short-circuits without authenticating. The cap has to survive that, or the
        // request carries on with the user's real admin role as soon as super-user access is given back up.
        $access->setSuperUserAccess(true);
        $this->assertTrue($access->reloadAccess());
        $access->setSuperUserAccess(false);

        $this->assertSame('view', $access->getRoleForSite($idSite));
        $this->assertFalse($access->isUserHasSomeAdminAccess());
        $this->assertFalse($access->isUserHasSomeWriteAccess());
    }

    public function testReloadAccessKeepsCappedSuperuserTokenWhenReloadedWhileSuperUserAccessIsSet()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->any())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'view']
            )));

        $access = $this->getAccess();
        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertSame('view', $access->getRoleForSite($idSite));

        $access->setSuperUserAccess(true);
        $this->assertTrue($access->reloadAccess());
        $access->setSuperUserAccess(false);

        $this->assertFalse($access->hasSuperUserAccess());
        $this->assertSame('view', $access->getRoleForSite($idSite));
    }

    public function testReloadAccessClampsRoleFromTokenRowWhenAuthPluginOmitsAuthContext()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $login = 'scopedfallbackuser';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'fallback@example.org', \Piwik\Date::now()->getDatetime());
        $model->addUserAccess($login, Access\Role\Admin::ID, [$idSite]);

        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $token, 'fallback', \Piwik\Date::now()->getDatetime(), null, false, false, 'view');

        $_GET['token_auth'] = $token;
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $token));

            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            $this->assertSame('view', $access->getRoleForSite($idSite));
            $this->assertFalse($access->isUserHasSomeAdminAccess());
            $this->assertFalse($access->isUserHasSomeWriteAccess());
        } finally {
            unset($_GET['token_auth']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessClampsSuperuserFromTokenRowWhenAuthPluginOmitsAuthContext()
    {
        $idSite1 = Fixture::createWebsite('2010-01-02 00:00:00');
        $idSite2 = Fixture::createWebsite('2010-01-03 00:00:00');

        $login = 'scopedfallbacksuperuser';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'fallbacksuper@example.org', \Piwik\Date::now()->getDatetime());
        $model->setSuperUserAccess($login, true);

        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $token, 'fallback super', \Piwik\Date::now()->getDatetime(), null, false, false, 'view');

        $_GET['token_auth'] = $token;
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $login, $token));

            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            $this->assertFalse($access->hasSuperUserAccess());
            $this->assertSame('view', $access->getRoleForSite($idSite1));
            $this->assertSame('view', $access->getRoleForSite($idSite2));
        } finally {
            unset($_GET['token_auth']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessSkipsTokenRowFallbackWhenAuthResultTokenDoesNotMatchSubmittedToken()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $login = 'scopedmismatchuser';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'mismatch@example.org', \Piwik\Date::now()->getDatetime());
        $model->addUserAccess($login, Access\Role\Admin::ID, [$idSite]);

        // The submitted (outer) token is scoped to view; the AuthResult is for a *different* token,
        // simulating a bulk-API sub-request authenticated against its own token while the outer
        // request still carries token_auth=outer in $_GET.
        $outerToken = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $outerToken, 'outer', \Piwik\Date::now()->getDatetime(), null, false, false, 'view');

        $subRequestToken = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $subRequestToken, 'sub-request', \Piwik\Date::now()->getDatetime(), null, false, false, null);

        $_GET['token_auth'] = $outerToken;
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $subRequestToken));

            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            // The outer token's "view" cap must NOT clamp the sub-request's role; the sub-request
            // authenticated with an unscoped token, so admin access stays.
            $this->assertSame('admin', $access->getRoleForSite($idSite));
        } finally {
            unset($_GET['token_auth']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessSkipsTokenRowFallbackWhenNoSubmittedToken()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $login = 'scopednosubmissionuser';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'nosubmission@example.org', \Piwik\Date::now()->getDatetime());
        $model->addUserAccess($login, Access\Role\Admin::ID, [$idSite]);

        // No $_GET['token_auth'] — simulates password/session auth where the AuthResult still carries
        // a tokenAuth (pre-existing session token) but no token was submitted with this request.
        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $token, 'password-auth', \Piwik\Date::now()->getDatetime(), null, false, false, 'view');

        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $token));

            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            // No submitted token => no clamp.
            $this->assertSame('admin', $access->getRoleForSite($idSite));
        } finally {
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessDoesNotOpenAnApiSessionOnATrackerRequest()
    {
        // Tracker\Request::authenticateSuperUserOrAdminOrWrite() reaches reloadAccess() for every token
        // that misses the per-site tracking token cache. `module`/`action`/`force_api_session` are not part
        // of the tracker's request contract, so a tracking request that sets them must not be able to open
        // an API session — otherwise an unauthenticated client can make matomo.php start a session.
        $model = new \Piwik\Plugins\UsersManager\Model();
        $token = $model->generateRandomTokenAuth();

        $_GET['module'] = 'API';
        $_GET['action'] = 'index';
        $_POST['token_auth'] = $token;
        $_POST['force_api_session'] = '1';

        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        $sessionAuthMock = $this->createMock(\Piwik\Session\SessionAuth::class);
        $sessionAuthMock->expects($this->never())->method('authenticate');
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Session\SessionAuth::class, $sessionAuthMock);

        \Piwik\SettingsServer::setIsTrackerApiRequest();

        // Compared rather than asserted false, so the test does not depend on whether something earlier
        // in the process already started a session.
        $wasSessionStarted = \Piwik\Session::isSessionStarted();

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::FAILURE, null, $token));

            $access = $this->getAccess();
            $this->assertFalse($access->reloadAccess($authMock));
            $this->assertSame($wasSessionStarted, \Piwik\Session::isSessionStarted());
        } finally {
            \Piwik\SettingsServer::setIsNotTrackerApiRequest();
            unset($_GET['module'], $_GET['action'], $_POST['token_auth'], $_POST['force_api_session']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessSkipsTokenRowFallbackWhenAuthPluginProvidesAuthContext()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $login = 'scopedfallbackoverride';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'override@example.org', \Piwik\Date::now()->getDatetime());
        $model->addUserAccess($login, Access\Role\Admin::ID, [$idSite]);

        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $token, 'override', \Piwik\Date::now()->getDatetime(), null, false, false, 'view');

        $_GET['token_auth'] = $token;
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $token, ['token_access_level' => null]));

            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            $this->assertSame('admin', $access->getRoleForSite($idSite));
        } finally {
            unset($_GET['token_auth']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessFallsBackToTokenRowWhenAuthContextOmitsTokenAccessLevel()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $login = 'scopedfallbackotherkeys';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'otherkeys@example.org', \Piwik\Date::now()->getDatetime());
        $model->addUserAccess($login, Access\Role\Admin::ID, [$idSite]);

        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $token, 'other keys', \Piwik\Date::now()->getDatetime(), null, false, false, 'view');

        $_GET['token_auth'] = $token;
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $token, ['some_plugin_detail' => 'value']));

            // An auth plugin that passes a context for reasons of its own, without declaring a token access
            // level, must not switch scope clamping off.
            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            $this->assertSame('view', $access->getRoleForSite($idSite));
            $this->assertFalse($access->isUserHasSomeAdminAccess());
        } finally {
            unset($_GET['token_auth']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessDeniesAllAccessWhenTokenRowAccessLevelIsNotRecognised()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');

        $login = 'scopedfallbackcorrupt';
        $model = new \Piwik\Plugins\UsersManager\Model();
        $model->addUser($login, 'pwhash', 'corrupt@example.org', \Piwik\Date::now()->getDatetime());
        $model->addUserAccess($login, Access\Role\Admin::ID, [$idSite]);

        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth($login, $token, 'corrupt', \Piwik\Date::now()->getDatetime(), null, false, false, 'notalevel');

        $_GET['token_auth'] = $token;
        \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());

        try {
            $authMock = $this->createPiwikAuthMockInstance();
            $authMock->expects($this->once())
                ->method('authenticate')
                ->willReturn(new AuthResult(AuthResult::SUCCESS, $login, $token));

            $access = $this->getAccess();
            $this->assertTrue($access->reloadAccess($authMock));
            $this->assertSame('noaccess', $access->getRoleForSite($idSite));
            $this->assertEmpty($access->getSitesIdWithAtLeastViewAccess());
        } finally {
            unset($_GET['token_auth']);
            \Piwik\Container\StaticContainer::getContainer()->set(\Piwik\Request\AuthenticationToken::class, new \Piwik\Request\AuthenticationToken());
        }
    }

    public function testReloadAccessScopedTokenRebuildsCapabilitiesFromCappedWriteRole()
    {
        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token', ['token_access_level' => 'write'])));

        $access = $this->getAccessMockWithScopedTokenCapabilityProvider();
        $access->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($this->buildAdminAccessForSiteIds([1])));

        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertSame('write', $access->getRoleForSite(1));
        $access->checkUserHasCapability(1, TestWriteCap::ID);
        $this->expectException(NoAccessException::class);
        $access->checkUserHasCapability(1, TestAdminOnlyCap::ID);
    }

    public function testReloadAccessScopedTokenIgnoresExplicitCapabilityRowsThatExceedCappedRole()
    {
        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', 'token', ['token_access_level' => 'write'])));

        $accessRows = [
            ['access' => 'admin', 'idsite' => 1],
            ['access' => TestAdminOnlyCap::ID, 'idsite' => 1],
        ];

        $access = $this->getAccessMockWithScopedTokenCapabilityProvider();
        $access->expects($this->once())
            ->method('getRawSitesWithSomeViewAccess')
            ->will($this->returnValue($accessRows));

        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertSame('write', $access->getRoleForSite(1));
        $this->expectException(NoAccessException::class);
        $access->checkUserHasCapability(1, TestAdminOnlyCap::ID);
    }

    public function testReloadAccessScopedSuperuserTokenRebuildsCapabilitiesForAllSitesFromCappedRole()
    {
        $idSite1 = Fixture::createWebsite('2010-01-02 00:00:00');
        $idSite2 = Fixture::createWebsite('2010-01-03 00:00:00');

        $authMock = $this->createPiwikAuthMockInstance();
        $authMock->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token',
                ['token_access_level' => 'write']
            )));

        $access = $this->getAccessWithScopedTokenCapabilityProvider();
        $this->assertTrue($access->reloadAccess($authMock));
        $this->assertSame('write', $access->getRoleForSite($idSite1));
        $this->assertSame('write', $access->getRoleForSite($idSite2));
        $access->checkUserHasCapability($idSite1, TestWriteCap::ID);
        $access->checkUserHasCapability($idSite2, TestWriteCap::ID);
    }

    public function testReloadAccessOnSharedSingletonClampsScopedSuperUserTokenAndSurvivesInterleavedDoAsSuperUser()
    {
        Fixture::createWebsite('2010-01-02 00:00:00');

        $singleton = Access::getInstance();
        $this->assertSame($singleton, Access::getInstance(), 'Access::getInstance() must return a shared singleton');

        $singleton->setSuperUserAccess(false);

        $scopedAuth = $this->createPiwikAuthMockInstance();
        $scopedAuth->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(new AuthResult(
                AuthResult::SUCCESS_SUPERUSER_AUTH_CODE,
                'superuserlogin',
                'token-view',
                ['token_access_level' => 'view']
            )));

        $this->assertTrue($singleton->reloadAccess($scopedAuth));
        $this->assertFalse(
            $singleton->hasSuperUserAccess(),
            'Singleton-shared Access must clamp super-user state when token has access_level cap'
        );
        $this->assertFalse($singleton->isUserHasSomeWriteAccess());
        $this->assertFalse($singleton->isUserHasSomeAdminAccess());

        $insideDoAsSuperUser = false;
        Access::doAsSuperUser(function () use (&$insideDoAsSuperUser) {
            $insideDoAsSuperUser = Access::getInstance()->hasSuperUserAccess();
        });
        $this->assertTrue($insideDoAsSuperUser, 'doAsSuperUser must temporarily elevate inside the callback');

        $this->assertFalse(
            $singleton->hasSuperUserAccess(),
            'Scoped-token clamping must be restored on the shared singleton after exiting doAsSuperUser'
        );
        $this->assertFalse($singleton->isUserHasSomeWriteAccess());
        $this->assertFalse($singleton->isUserHasSomeAdminAccess());
    }

    public function testReloadAccessLoadSitesIfNeededDoesActuallyResetAllSiteIdsAndRequestThemAgain()
    {
        /** @var Access&MockObject $mock */
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

    public function testDoAsSuperUserChangesSuperUserAccessCorrectly()
    {
        Access::getInstance()->setSuperUserAccess(false);

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());

        Access::doAsSuperUser(function () {
            AccessTest::assertTrue(Access::getInstance()->hasSuperUserAccess());
        });

        $this->assertFalse(Access::getInstance()->hasSuperUserAccess());
    }

    public function testDoAsSuperUserRemovesSuperUserAccessIfExceptionThrown()
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

    public function testDoAsSuperUserReturnsCallbackResult()
    {
        $result = Access::doAsSuperUser(function () {
            return 24;
        });
        $this->assertEquals(24, $result);
    }

    public function testReloadAccessDoesNotRemoveSuperUserAccessIfUsedInDoAsSuperUser()
    {
        Access::getInstance()->setSuperUserAccess(false);

        Access::doAsSuperUser(function () {
            $access = Access::getInstance();

            AccessTest::assertTrue($access->hasSuperUserAccess());
            $access->reloadAccess();
            AccessTest::assertTrue($access->hasSuperUserAccess());
        });
    }

    public function testGetAccessForSiteWhenUserHasAdminAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-02 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'admin', $idSite);

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('admin', Access::getInstance()->getRoleForSite($idSite));
    }

    public function testGetAccessForSiteWhenUserHasViewAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'view', $idSite);

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('view', Access::getInstance()->getRoleForSite($idSite));
    }

    public function testGetAccessForSiteWhenUserHasWriteAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');
        UsersManagerAPI::getInstance()->setUserAccess('testuser', 'write', $idSite);

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('write', Access::getInstance()->getRoleForSite($idSite));
    }

    public function testGetAccessForSiteWhenUserHasNoAccess()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');
        UsersManagerAPI::getInstance()->addUser('testuser', 'testpass', 'testuser@email.com');

        $this->switchUser('testuser');

        Access::getInstance()->setSuperUserAccess(false);
        $this->assertEquals('noaccess', Access::getInstance()->getRoleForSite($idSite));
    }

    public function testGetAccessForSiteWhenUserIsSuperUser()
    {
        $idSite = Fixture::createWebsite('2010-01-03 00:00:00');

        Access::getInstance()->setSuperUserAccess(true);
        $this->assertEquals('admin', Access::getInstance()->getRoleForSite($idSite));
    }

    public function testAPIPermissionResponseCode()
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

    public function testAPIWithAuthorizationHeader()
    {
        Fixture::createSuperUser();

        $url = Fixture::getTestRootUrl() . '?' . http_build_query([
                'module' => 'API',
                'method' => 'API.getMatomoVersion',
            ]);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . Fixture::getTokenAuth()]);
        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        curl_close($ch);

        $this->assertEquals(200, $responseInfo["http_code"]);
        self::assertStringContainsString('<result>' . Version::VERSION . '</result>', $response);
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

    /**
     * @param string[] $methodsToMock
     * @return Access&MockObject
     */
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
