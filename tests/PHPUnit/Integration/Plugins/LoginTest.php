<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\AuthResult;
use Piwik\DbHelper;
use Piwik\Plugins\Login\Auth;
use Piwik\Plugins\UsersManager\API;

require_once 'Login/Auth.php';

/**
 * Class Plugins_LoginTest
 *
 * @group Plugins
 */
class Plugins_LoginTest extends DatabaseTestCase
{

    /**
     * @var Auth
     */
    private $auth;

    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a Super User by default
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);

        $this->auth = new Auth();
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureNoLoginNoTokenAuth()
    {
        // no login; no token auth
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureEmptyLoginNoTokenAuth()
    {
        // empty login; no token auth
        $this->auth->setLogin('');
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureNonExistentUser()
    {
        // non-existent user
        $this->auth->setLogin('nobody');
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExisting()
    {
        // anonymous user doesn't exist yet
        $rc = $this->authenticate($login = 'anonymous', $authToken = '');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExistentEmptyLogin()
    {
        // empty login; anonymous user doesn't exist yet
        $rc = $this->authenticate($login = '', $authToken = 'anonymous');

        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExistentEmptyLoginWithTokenAuth()
    {
        // API authentication; anonymous user doesn't exist yet
        $rc = $this->authenticate($login = null, $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExistentWithLoginAndTokenAuth()
    {
        // anonymous user doesn't exist yet
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousWithLogin()
    {
        DbHelper::createAnonymousUser();

        // missing token_auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = '');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousEmptyLoginWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // empty login
        $rc = $this->authenticate($login = '', $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousLoginTokenAuthMissmatch()
    {
        DbHelper::createAnonymousUser();

        // not equal
        $rc = $this->authenticate($login = 'anonymous', $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessAnonymousWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // API authentication
        $rc = $this->authenticate($login = null, $authToken = 'anonymous');
        $this->assertUserLogin($rc, $login = 'anonymous', $tokenLength = 9);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessAnonymous()
    {
        DbHelper::createAnonymousUser();

        // valid login & token auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertUserLogin($rc, $login = 'anonymous', $tokenLength = 9);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserEmptyTokenAuth()
    {
        $user = $this->_setUpUser();

        // empty token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = '');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserInvalidTokenAuth()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = $user['password']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserInvalidTokenAuth2()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = md5($user['password']));
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserEmptyLogin()
    {
        $user = $this->_setUpUser();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserWithSuperUserAccessEmptyLogin()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch()
    {
        $this->_setUpUser();

        // not equal
        $rc = $this->authenticate($login = 0, $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch2()
    {
        $user = $this->_setUpUser();

        // not equal
        $rc = $this->authenticate($login = 0, $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch3()
    {
        $user = $this->_setUpUser();

        // not equal
        $rc = $this->authenticate($user['login'], $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserWithSuperUserAccessLoginTokenAuthMissmatch()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // not equal
        $rc = $this->authenticate($login = null, $authToken = $user['password']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessUserTokenAuth()
    {
        $user = $this->_setUpUser();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertUserLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessUserWithSuperUserAccessByTokenAuth()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertSuperUserLogin($rc, 'user');
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessUserLoginAndTokenAuth()
    {
        $user = $this->_setUpUser();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertUserLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessUserWithSuperUserAccessLoginAndTokenAuth()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertSuperUserLogin($rc, 'user');
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessLoginAndHashedTokenAuth()
    {
        $user = $this->_setUpUser();
        $hash = $this->auth->getHashTokenAuth($user['login'], $user['tokenAuth']);

        // valid login & hashed token auth
        $rc = $this->authenticate($user['login'], $tokenAuth = $hash);
        $this->assertUserLogin($rc);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Prevent session initialize.
     */
    public function testPreventSessionHook()
    {
        $user = $this->_setUpUser();
        $auth = $this->getMockedAuth($mockCreateLoginCookieBasedOnAuthResult = false);

        \Piwik\Piwik::addAction(
            'Login.preventInitSession',
            function ($loginData) use ($user) {
                $this->assertArrayHasKey('login', $loginData);
                $this->assertEquals($user['login'], $loginData['login']);

                $this->assertArrayHasKey('md5Password', $loginData);
                $this->assertEquals(md5($user['password']), $loginData['md5Password']);

                throw new \Exception('Prevent session initialize.');
            }
        );

        $auth->initSession($user['login'], md5($user['password']), false);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Prevent session initialize after successful login.
     */
    public function testInitSessionHook()
    {
        $user = $this->_setUpUser();
        $auth = $this->getMockedAuth(
            $mockCreateLoginCookieBasedOnAuthResult = false,
            $mockAuthenticate = true
        );

        \Piwik\Piwik::addAction(
            'Login.initSession',
            function ($loginData) use ($user) {
                $this->assertArrayHasKey('login', $loginData);
                $this->assertEquals($user['login'], $loginData['login']);

                $this->assertArrayHasKey('md5Password', $loginData);
                $this->assertEquals(md5($user['password']), $loginData['md5Password']);

                throw new \Exception('Prevent session initialize after successful login.');
            }
        );

        $auth->initSession($user['login'], md5($user['password']), false);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Login_LoginPasswordNotCorrect
     */
    public function testInitSessionHookWithBadCredentials()
    {
        $user = $this->_setUpUser();
        $auth = $this->getMockedAuth(
            $mockCreateLoginCookieBasedOnAuthResult = false,
            $mockAuthenticate = true,
            $mockedAuthenticateShouldReturn = false,
            3
        );

        \Piwik\Piwik::addAction(
            'Login.initSession',
            function () {
                throw new \Exception('Prevent session initialize after successful login.');
            }
        );

        $auth->initSession($user['login'], md5($user['password']), false);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Login successful.
     */
    public function testSuccessfulHook()
    {
        $user = $this->_setUpUser();
        $auth = $this->getMockedAuth(
            $mockCreateLoginCookieBasedOnAuthResult = true,
            $mockAuthenticate = true,
            $mockedAuthenticateShouldReturn = true,
            2
        );

        \Piwik\Piwik::addAction(
            'Login.successful',
            function () {
                throw new \Exception('Login successful.');
            }
        );

        $auth->initSession($user['login'], md5($user['password']), false);
    }

    protected function _setUpUser()
    {
        $user = array('login'    => 'user',
                      'password' => 'geqgeagae',
                      'email'    => 'test@test.com',
                      'alias'    => 'alias',
                      'superuser_access' => 0);

        API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);

        $user['tokenAuth'] = API::getInstance()->getTokenAuth($user['login'], md5($user['password']));

        return $user;
    }

    private function _setUpSuperUserAccessViaDb()
    {
        API::getInstance()->setSuperUserAccess('user', true);
    }

    private function authenticate($login, $tokenAuth)
    {
        $this->auth->setLogin($login);
        $this->auth->setTokenAuth($tokenAuth);

        return $this->auth->authenticate();
    }

    private function assertFailedLogin(AuthResult $authResult)
    {
        $this->assertEquals(AuthResult::FAILURE, $authResult->getCode());
    }

    private function assertSuperUserLogin(AuthResult $authResult, $login = 'superUserLogin', $tokenLength = 32)
    {
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $authResult->getCode());
        $this->assertEquals($login, $authResult->getIdentity());
        $this->assertEquals($tokenLength, strlen($authResult->getTokenAuth()));
    }

    private function assertUserLogin(AuthResult $authResult, $login = 'user', $tokenLength = 32)
    {
        $this->assertEquals(AuthResult::SUCCESS, $authResult->getCode());
        $this->assertEquals($login, $authResult->getIdentity());
        $this->assertEquals($tokenLength, strlen($authResult->getTokenAuth()));
    }

    /**
     * @param bool $mockCreateLoginCookieBasedOnAuthResult
     * @param bool $mockAuthenticate
     * @param bool $mockedAuthenticateShouldReturn
     * @param int $authenticateCalls
     * @return Auth|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedAuth($mockCreateLoginCookieBasedOnAuthResult = true, $mockAuthenticate = false,
                                     $mockedAuthenticateShouldReturn = true, $authenticateCalls = 1)
    {
        $methods = array('regenerateSessionId');
        if ($mockCreateLoginCookieBasedOnAuthResult) {
            $methods[] = 'createLoginCookieBasedOnAuthResult';
        }

        if ($mockAuthenticate) {
            $methods[] = 'authenticate';
        }

        /**
         * @var \Piwik\Plugins\Login\Auth|\PHPUnit_Framework_MockObject_MockObject $auth
         */
        $auth = $this->getMockBuilder('\Piwik\Plugins\Login\Auth')
            ->setMethods($methods)
            ->getMock();

        $auth->expects($this->once())
            ->method('regenerateSessionId')
            ->will($this->returnValue(null));

        if ($mockCreateLoginCookieBasedOnAuthResult) {
            $auth->expects($this->once())
                ->method('createLoginCookieBasedOnAuthResult')
                ->will($this->returnSelf());
        }

        if ($mockAuthenticate) {
            $auth->expects($this->once())
                ->method('authenticate')
                ->will($this->returnValue(
                    $this->getMockedAuthResult(
                        $mockedAuthenticateShouldReturn,
                        $authenticateCalls
                    )
                )
            );
        }

        return $auth;
    }

    /**
     * @param bool $wasAuthenticationSuccessful
     * @param int $authenticateCalls
     * @return PHPUnit_Framework_MockObject_MockObject|\Piwik\AuthResult
     */
    public function getMockedAuthResult($wasAuthenticationSuccessful = true, $authenticateCalls = 1)
    {
        /**
         * @var \Piwik\AuthResult|\PHPUnit_Framework_MockObject_MockObject $authResult
         */
        $authResult = $this->getMockBuilder('\Piwik\AuthResult')
            ->setMethods(array('wasAuthenticationSuccessful'))
            ->disableOriginalConstructor()
            ->getMock();

        $authResult->expects($this->exactly($authenticateCalls))
            ->method('wasAuthenticationSuccessful')
            ->will($this->returnValue($wasAuthenticationSuccessful));

        return $authResult;
    }

}
