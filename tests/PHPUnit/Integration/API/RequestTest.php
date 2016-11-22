<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\API;

use Piwik\API\Request;
use Piwik\AuthResult;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class RequestTest extends IntegrationTestCase
{
    /** @var \Piwik\Auth|\PHPUnit_Framework_MockObject_MockObject */
    private $auth;
    /** @var \Piwik\Access|\PHPUnit_Framework_MockObject_MockObject */
    private $access;

    private $userAuthToken = 'token';

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
                     ->setMethods(array('getTokenAuth', 'reloadAccess'))
                     ->enableProxyingToOriginalMethods()
                     ->getMock();
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
