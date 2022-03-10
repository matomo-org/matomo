<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\AuthResult;
use Piwik\Date;
use Piwik\DbHelper;
use Piwik\Plugins\Login\Auth;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Plugins
 * @group Plugins_LoginTest
 */
class LoginTest extends IntegrationTestCase
{
    /**
     * @var Auth
     */
    private $auth;

    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::setIdSitesView(array(1, 2));
        FakeAccess::setIdSitesAdmin(array(3, 4));

        //finally we set the user as a Super User by default
        FakeAccess::$superUser = true;

        $this->auth = new Auth();
    }

    public function test_authenticate_failureNoLoginNoTokenAuth()
    {
        // no login; no token auth
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureEmptyLoginNoTokenAuth()
    {
        // empty login; no token auth
        $this->auth->setLogin('');
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureNonExistentUser()
    {
        // non-existent user
        $this->auth->setLogin('nobody');
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousNotExisting()
    {
        // anonymous user doesn't exist yet
        $rc = $this->authenticate($login = 'anonymous', $authToken = '');
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousNotExistentEmptyLogin()
    {
        // empty login; anonymous user doesn't exist yet
        $rc = $this->authenticate($login = '', $authToken = 'anonymous');

        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousNotExistentEmptyLoginWithTokenAuth()
    {
        // API authentication; anonymous user doesn't exist yet
        $rc = $this->authenticate($login = null, $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousNotExistentWithLoginAndTokenAuth()
    {
        // anonymous user doesn't exist yet
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousWithLogin()
    {
        DbHelper::createAnonymousUser();

        // missing token_auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = '');
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousEmptyLoginWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // empty login
        $rc = $this->authenticate($login = '', $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureAnonymousLoginTokenAuthMissmatch()
    {
        DbHelper::createAnonymousUser();

        // not equal
        $rc = $this->authenticate($login = 'anonymous', $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_successAnonymousWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // API authentication
        $rc = $this->authenticate($login = null, $authToken = 'anonymous');
        $this->assertUserLogin($rc, $login = 'anonymous', $tokenLength = 9);
    }

    public function test_authenticate_successAnonymous()
    {
        DbHelper::createAnonymousUser();

        // valid login & token auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertUserLogin($rc, $login = 'anonymous', $tokenLength = 9);
    }

    public function test_authenticate_failureUserEmptyTokenAuth()
    {
        $user = $this->_setUpUser();

        // empty token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = '');
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserInvalidTokenAuth()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = $user['password']);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserInvalidTokenAuth2()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = md5($user['password']));
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserEmptyLogin()
    {
        $user = $this->_setUpUser();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserWithSuperUserAccessEmptyLogin()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserLoginTokenAuthMissmatch()
    {
        $this->_setUpUser();

        // not equal
        $rc = $this->authenticate($login = 0, $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserLoginTokenAuthMissmatch2()
    {
        $user = $this->_setUpUser();

        // not equal
        $rc = $this->authenticate($login = 0, $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserLoginTokenAuthMissmatch3()
    {
        $user = $this->_setUpUser();

        // not equal
        $rc = $this->authenticate($user['login'], $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_failureUserWithSuperUserAccessLoginTokenAuthMissmatch()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // not equal
        $rc = $this->authenticate($login = null, $authToken = $user['password']);
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_successUserTokenAuth()
    {
        $user = $this->_setUpUser();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertUserLogin($rc);
    }

    public function test_authenticate_successUserWithSuperUserAccessByTokenAuth()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertSuperUserLogin($rc, 'user');
    }

    public function test_authenticate_successUserLoginAndTokenAuthWithAnonymous()
    {
        DbHelper::createAnonymousUser();

        $user = $this->_setUpUser();

        // valid login & token auth
        $rc = $this->authenticate('anonymous', 'anonymous');
        $this->assertUserLogin($rc, 'anonymous', strlen('anonymous'));
    }

    public function test_authenticate_successUserLoginAndTokenAuth()
    {
        $user = $this->_setUpUser();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertUserLogin($rc);
    }

    public function test_authenticate_successUserWithSuperUserAccessLoginAndTokenAuth()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertSuperUserLogin($rc, 'user');
    }

    public function test_authenticate_successWithValidPassword()
    {
        $user = $this->_setUpUser();
        $this->auth->setLogin($user['login']);
        $this->auth->setPassword($user['password']);

        $rc = $this->auth->authenticate();
        $this->assertUserLogin($rc);
        // Check that the token auth is correct in the result
        $this->assertEquals(32, strlen($rc->getTokenAuth()));
        $this->assertTrue(ctype_xdigit($rc->getTokenAuth()));
    }

    public function test_authenticate_successWithSuperUserPassword()
    {
        $user = $this->_setUpUser();
        $this->_setUpSuperUserAccessViaDb();

        $this->auth->setLogin($user['login']);
        $this->auth->setPassword($user['password']);

        $rc = $this->auth->authenticate();
        $this->assertSuperUserLogin($rc, 'user');
    }

    public function test_authenticate_failsWithInvalidPassword()
    {
        $user = $this->_setUpUser();
        $this->auth->setLogin($user['login']);
        $this->auth->setPassword('foo bar');

        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function test_authenticate_prioritizesPasswordAuthentication()
    {
        $user = $this->_setUpUser();
        $this->auth->setLogin($user['login']);
        $this->auth->setPassword($user['password']); // correct password
        $this->auth->setTokenAuth('foo bar'); // invalid token

        // Authentication should succeed because authenticating via password is favored
        $rc = $this->auth->authenticate();
        $this->assertUserLogin($rc);
        // Check that the token auth is correct in the result
        $this->assertEquals(32, strlen($rc->getTokenAuth()));
        $this->assertTrue(ctype_xdigit($rc->getTokenAuth()));
    }

    /**
     * @group Plugins
     * @see https://github.com/piwik/piwik/issues/8548
     */
    public function test_authenticate_withPasswordIsCaseInsensitiveForLogin()
    {
        $user = $this->_setUpUser();
        $this->auth->setLogin('uSeR');
        $this->auth->setPassword($user['password']);

        $rc = $this->auth->authenticate();
        $this->assertUserLogin($rc);
        // Check that the login + token auth is correct in the result
        $this->assertEquals($user['login'], $rc->getIdentity());
        $this->assertEquals(32, strlen($rc->getTokenAuth()));
        $this->assertTrue(ctype_xdigit($rc->getTokenAuth()));
    }

    protected function _setUpUser()
    {
        $user = array('login'    => 'user',
                      'password' => 'geqgeagae',
                      'email'    => 'test@test.com',
                      'superuser_access' => 0);

        API::getInstance()->addUser($user['login'], $user['password'], $user['email']);

        $model  = new \Piwik\Plugins\UsersManager\Model();
        $tokenAuth = $model->generateRandomTokenAuth();
        $model->addTokenAuth($user['login'], $tokenAuth, 'many users test', Date::now()->getDatetime());

        $user['tokenAuth'] = $tokenAuth;

        return $user;
    }

    private function _setUpSuperUserAccessViaDb()
    {
        $userUpdater = new UserUpdater();
        $userUpdater->setSuperUserAccessWithoutCurrentPassword('user', true);
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
        $this->assertEquals(AuthResult::SUCCESS, $authResult->getCode(), 'Authentication failed');
        $this->assertEquals($login, $authResult->getIdentity());
        $this->assertEquals($tokenLength, strlen($authResult->getTokenAuth()));
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
