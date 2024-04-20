<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\API;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\AuthResult;
use Piwik\Common;
use Piwik\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use ReflectionClass;

/**
 * @group Core
 */
class RequestTest extends IntegrationTestCase
{
    /** @var \Piwik\Auth|\PHPUnit\Framework\MockObject\MockObject */
    private $auth;
    /** @var \Piwik\Access|\PHPUnit\Framework\MockObject\MockObject */
    private $access;

    private $userAuthToken = 'token';

    private $idSitesAccess = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->idSitesAccess = [
            'view'      => array(1),
            'write'     => array(),
            'admin'     => array(),
            'superuser' => array(),
        ];
    }

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        Fixture::createWebsite('2018-02-03 00:00:00');
    }

    public function test_process_shouldNotReloadAccessIfNoTokenAuthIsGiven()
    {
        $this->assertAccessNotReloaded();

        $request = new Request(array('method' => 'API.getPiwikVersion'));
        $request->process();

        $this->assertSameUserAsBeforeIsAuthenticated();
    }

    public function test_process_shouldNotReloadAccessIfSameAuthTokenIsAlreadyLoaded()
    {
        $this->assertAccessNotReloaded();

        $request = new Request(array('method' => 'API.getPiwikVersion', 'token_auth' => $this->access->getTokenAuth()));
        $request->process();

        $this->assertSameUserAsBeforeIsAuthenticated();
    }

    public function test_process_shouldReloadAccessIfAuthTokenIsDifferent()
    {
        // make sure tokenAuth is different then set 'AnYTOkEN' token
        $this->assertEquals('token', $this->access->getTokenAuth());

        $this->assertAccessReloadedAndRestored('AnYTOkEN');

        $request = new Request(array('method' => 'API.getPiwikVersion', 'token_auth' => 'AnYTOkEN'));
        $request->process();

        // make sure token auth was restored after it was loaded with AnYTOkEN
        $this->assertSameUserAsBeforeIsAuthenticated();
    }

    public function test_process_shouldReloadAccessIfAuthTokenIsDifferentButEmpty()
    {
        $this->assertEquals('token', $this->access->getTokenAuth());
        $this->assertAccessReloadedAndRestored('');

        $request = new Request(array('method' => 'API.getPiwikVersion', 'token_auth' => ''));
        $request->process();

        $this->assertSameUserAsBeforeIsAuthenticated();
    }

    public function test_process_shouldKeepSuperUserPermission_IfAccessWasManuallySet()
    {
        $this->access->setSuperUserAccess(true);
        $this->assertAccessReloadedAndRestored('difFenrenT');

        $request = new Request(array('method' => 'API.getPiwikVersion', 'token_auth' => 'difFenrenT'));
        $request->process();

        // make sure token auth was restored after it was loaded with difFenrenT
        $this->assertSameUserAsBeforeIsAuthenticated();
        $this->assertTrue($this->access->hasSuperUserAccess());
    }

    public function test_isApiRequest_shouldDetectIfItIsApiRequestOrNot()
    {
        $this->assertFalse(Request::isApiRequest(array()));
        $this->assertFalse(Request::isApiRequest(array('module' => '', 'method' => '')));
        $this->assertFalse(Request::isApiRequest(array('module' => 'API'))); // no method
        $this->assertFalse(Request::isApiRequest(array('module' => 'CoreHome', 'method' => 'index.test'))); // not api
        $this->assertFalse(Request::isApiRequest(array('module' => 'API', 'method' => 'testmethod'))); // no valid action
        $this->assertTrue(Request::isApiRequest(array('module' => 'API', 'method' => 'test.method')));
    }

    public function test_checkTokenAuthIsNotLimited_allowsSuperUserTokenAuth_ifCurrentRequestIsForAPI()
    {
        $this->expectNotToPerformAssertions();

        Common::$isCliMode = false;
        $this->access->setSuperUserAccess(true);

        Request::checkTokenAuthIsNotLimited('API', 'index');
    }

    public function test_checkTokenAuthIsNotLimited_allowsSuperUserTokenAuth_ifCurrentlyInCliMode()
    {
        $this->expectNotToPerformAssertions();

        Common::$isCliMode = true;
        $this->access->setSuperUserAccess(true);

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_doesNotAllowSuperUserTokenAuth_ifCurrentlyInUiRequest()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widgetize_TooHighAccessLevel');

        Common::$isCliMode = false;
        $this->access->setSuperUserAccess(true);

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_doesNotAllowSuperUserTokenAuth_ifCurrentlyInUiRequestAndEnableConfigSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 1;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widgetize_TooHighAccessLevel');

        Common::$isCliMode = false;
        $this->access->setSuperUserAccess(true);

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_doesNotAllowWriteTokenAuth_ifConfigNotSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 0;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widgetize_ViewAccessRequired');

        $this->idSitesAccess['view'] = [];
        $this->idSitesAccess['write'] = [1];
        $this->access->reloadAccess($this->auth);
        $this->access->setSuperUserAccess(false);
        $this->assertFalse($this->access->hasSuperUserAccess());
        $this->assertTrue($this->access->isUserHasSomeWriteAccess());

        Common::$isCliMode = false;

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_doesNotAllowAdminTokenAuth_ifConfigNotSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 0;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Widgetize_ViewAccessRequired');

        $this->idSitesAccess['view'] = [];
        $this->idSitesAccess['admin'] = [1];
        $this->access->reloadAccess($this->auth);
        $this->access->setSuperUserAccess(false);
        $this->assertFalse($this->access->hasSuperUserAccess());
        $this->assertTrue($this->access->isUserHasSomeAdminAccess());

        Common::$isCliMode = false;

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_allowsWriteTokenAuth_ifConfigSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 1;

        $this->idSitesAccess['view'] = [];
        $this->idSitesAccess['write'] = [1];
        $this->access->reloadAccess($this->auth);
        $this->access->setSuperUserAccess(false);
        $this->assertFalse($this->access->hasSuperUserAccess());
        $this->assertTrue($this->access->isUserHasSomeWriteAccess());

        Common::$isCliMode = false;

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_allowsAdminTokenAuth_ifConfigSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 1;

        $this->idSitesAccess['view'] = [];
        $this->idSitesAccess['admin'] = [1];
        $this->access->reloadAccess($this->auth);
        $this->access->setSuperUserAccess(false);
        $this->assertFalse($this->access->hasSuperUserAccess());
        $this->assertTrue($this->access->isUserHasSomeAdminAccess());

        Common::$isCliMode = false;

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_allowsViewTokenAuth_ifConfigSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 1;

        $this->idSitesAccess['view'] = [1];
        $this->access->reloadAccess($this->auth);
        $this->access->setSuperUserAccess(false);
        $this->assertFalse($this->access->hasSuperUserAccess());
        $this->assertFalse($this->access->isUserHasSomeAdminAccess());
        $this->assertFalse($this->access->isUserHasSomeWriteAccess());

        Common::$isCliMode = false;

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    public function test_checkTokenAuthIsNotLimited_allowsViewTokenAuth_ifConfigNotSet()
    {
        Config::getInstance()->General['enable_framed_allow_write_admin_token_auth'] = 0;

        $this->idSitesAccess['view'] = [1];
        $this->access->reloadAccess($this->auth);
        $this->access->setSuperUserAccess(false);
        $this->assertFalse($this->access->hasSuperUserAccess());
        $this->assertFalse($this->access->isUserHasSomeAdminAccess());
        $this->assertFalse($this->access->isUserHasSomeWriteAccess());

        Common::$isCliMode = false;

        Request::checkTokenAuthIsNotLimited('SomePlugin', 'someMethod');
    }

    private function assertSameUserAsBeforeIsAuthenticated()
    {
        $this->assertEquals($this->userAuthToken, $this->access->getTokenAuth());
    }

    private function assertAccessNotReloaded()
    {
        $this->access->expects($this->never())->method('reloadAccess');
    }

    private function assertAccessReloadedAndRestored($expectedTokenToBeReloaded)
    {
        $this->access->expects($this->exactly(2))->method('reloadAccess');

        // verify access reloaded
        $this->auth->expects($this->at(0))->method('setLogin')->with($this->equalTo(null));
        $this->auth->expects($this->at(1))->method('setTokenAuth')->with($this->equalTo($expectedTokenToBeReloaded));
        $this->auth->expects($this->at(2))->method('authenticate')->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login1', $expectedTokenToBeReloaded)));

        // verify access restored
        $this->auth->expects($this->at(3))->method('setLogin')->with($this->equalTo(null));
        $this->auth->expects($this->at(4))->method('setTokenAuth')->with($this->equalTo($tokenRestored = $this->userAuthToken));
        $this->auth->expects($this->at(5))->method('authenticate')->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', $this->userAuthToken)));
    }

    private function createAuthMock()
    {
        $authMock = $this->getMockBuilder('Piwik\Plugins\Login\Auth')
                         ->setMethods(array('authenticate', 'setTokenAuth', 'setLogin'))
                         ->getMock();

        $authMock->expects($this->any())
                 ->method('authenticate')
                 ->will($this->returnValue(new AuthResult(AuthResult::SUCCESS, 'login', $this->userAuthToken)));

        return $authMock;
    }

    private function createAccessMock($auth)
    {
        $mock = $this->getMockBuilder('Piwik\Access')
                     ->onlyMethods(array('loadSitesIfNeeded', 'reloadAccess', 'getTokenAuth'))
                     ->enableProxyingToOriginalMethods()
                     ->getMock();
        $mock->method('loadSitesIfNeeded')->willReturnCallback(function () use ($mock) {
            // setting the property directly since enableProxyingToOriginalMethods() will just proxy to the original
            // method after this mock method is called. (we can't not call enableProxyingToOriginalMethods() because
            // some tests require it)
            $reflection = new ReflectionClass(Access::class);
            $reflectionProperty = $reflection->getProperty('idsitesByAccess');
            $reflectionProperty->setAccessible(true);

            $reflectionProperty->setValue($mock, $this->idSitesAccess);
        });
        $mock->reloadAccess($auth);

        return $mock;
    }

    public function provideContainerConfig()
    {
        $this->auth   = $this->createAuthMock();
        $this->access = $this->createAccessMock($this->auth);
        return array(
            'Piwik\Auth'     => $this->auth,
            'Piwik\Access' => $this->access
        );
    }
}
