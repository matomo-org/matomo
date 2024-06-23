<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function testAuthenticateFailureNoLoginNoTokenAuth()
    {
        // no login; no token auth
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureEmptyLoginNoTokenAuth()
    {
        // empty login; no token auth
        $this->auth->setLogin('');
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureNonExistentUser()
    {
        // non-existent user
        $this->auth->setLogin('nobody');
        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousNotExisting()
    {
        // anonymous user doesn't exist yet
        $rc = $this->authenticate($login = 'anonymous', $authToken = '');
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousNotExistentEmptyLogin()
    {
        // empty login; anonymous user doesn't exist yet
        $rc = $this->authenticate($login = '', $authToken = 'anonymous');

        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousNotExistentEmptyLoginWithTokenAuth()
    {
        // API authentication; anonymous user doesn't exist yet
        $rc = $this->authenticate($login = null, $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousNotExistentWithLoginAndTokenAuth()
    {
        // anonymous user doesn't exist yet
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousWithLogin()
    {
        DbHelper::createAnonymousUser();

        // missing token_auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = '');
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousEmptyLoginWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // empty login
        $rc = $this->authenticate($login = '', $authToken = 'anonymous');
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureAnonymousLoginTokenAuthMissmatch()
    {
        DbHelper::createAnonymousUser();

        // not equal
        $rc = $this->authenticate($login = 'anonymous', $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateSuccessAnonymousWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // API authentication
        $rc = $this->authenticate($login = null, $authToken = 'anonymous');
        $this->assertUserLogin($rc, $login = 'anonymous', $tokenLength = 9);
    }

    public function testAuthenticateSuccessAnonymous()
    {
        DbHelper::createAnonymousUser();

        // valid login & token auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertUserLogin($rc, $login = 'anonymous', $tokenLength = 9);
    }

    public function testAuthenticateFailureUserEmptyTokenAuth()
    {
        $user = $this->setUpUser();

        // empty token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = '');
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserInvalidTokenAuth()
    {
        $user = $this->setUpUser();

        // not a token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = $user['password']);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserInvalidTokenAuth2()
    {
        $user = $this->setUpUser();

        // not a token auth
        $rc = $this->authenticate($login = $user['login'], $authToken = md5($user['password']));
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserEmptyLogin()
    {
        $user = $this->setUpUser();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserWithSuperUserAccessEmptyLogin()
    {
        $user = $this->setUpUser();
        $this->setUpSuperUserAccessViaDb();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserLoginTokenAuthMissmatch()
    {
        $this->setUpUser();

        // not equal
        $rc = $this->authenticate($login = 0, $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserLoginTokenAuthMissmatch2()
    {
        $user = $this->setUpUser();

        // not equal
        $rc = $this->authenticate($login = 0, $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserLoginTokenAuthMissmatch3()
    {
        $user = $this->setUpUser();

        // not equal
        $rc = $this->authenticate($user['login'], $authToken = 0);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateFailureUserWithSuperUserAccessLoginTokenAuthMissmatch()
    {
        $user = $this->setUpUser();
        $this->setUpSuperUserAccessViaDb();

        // not equal
        $rc = $this->authenticate($login = null, $authToken = $user['password']);
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticateSuccessUserTokenAuth()
    {
        $user = $this->setUpUser();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertUserLogin($rc);
    }

    public function testAuthenticateSuccessUserWithSuperUserAccessByTokenAuth()
    {
        $user = $this->setUpUser();
        $this->setUpSuperUserAccessViaDb();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertSuperUserLogin($rc, 'user');
    }

    public function testAuthenticateSuccessUserLoginAndTokenAuthWithAnonymous()
    {
        DbHelper::createAnonymousUser();

        $user = $this->setUpUser();

        // valid login & token auth
        $rc = $this->authenticate('anonymous', 'anonymous');
        $this->assertUserLogin($rc, 'anonymous', strlen('anonymous'));
    }

    public function testAuthenticateSuccessUserLoginAndTokenAuth()
    {
        $user = $this->setUpUser();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertUserLogin($rc);
    }

    public function testAuthenticateSuccessUserWithSuperUserAccessLoginAndTokenAuth()
    {
        $user = $this->setUpUser();
        $this->setUpSuperUserAccessViaDb();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertSuperUserLogin($rc, 'user');
    }

    public function testAuthenticateSuccessWithValidPassword()
    {
        $user = $this->setUpUser();
        $this->auth->setLogin($user['login']);
        $this->auth->setPassword($user['password']);

        $rc = $this->auth->authenticate();
        $this->assertUserLogin($rc);
        // Check that the token auth is correct in the result
        $this->assertEquals(32, strlen($rc->getTokenAuth()));
        $this->assertTrue(ctype_xdigit($rc->getTokenAuth()));
    }

    public function testAuthenticateSuccessWithSuperUserPassword()
    {
        $user = $this->setUpUser();
        $this->setUpSuperUserAccessViaDb();

        $this->auth->setLogin($user['login']);
        $this->auth->setPassword($user['password']);

        $rc = $this->auth->authenticate();
        $this->assertSuperUserLogin($rc, 'user');
    }

    public function testAuthenticateFailsWithInvalidPassword()
    {
        $user = $this->setUpUser();
        $this->auth->setLogin($user['login']);
        $this->auth->setPassword('foo bar');

        $rc = $this->auth->authenticate();
        $this->assertFailedLogin($rc);
    }

    public function testAuthenticatePrioritizesPasswordAuthentication()
    {
        $user = $this->setUpUser();
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
    public function testAuthenticateWithPasswordIsCaseInsensitiveForLogin()
    {
        $user = $this->setUpUser();
        $this->auth->setLogin('uSeR');
        $this->auth->setPassword($user['password']);

        $rc = $this->auth->authenticate();
        $this->assertUserLogin($rc);
        // Check that the login + token auth is correct in the result
        $this->assertEquals($user['login'], $rc->getIdentity());
        $this->assertEquals(32, strlen($rc->getTokenAuth()));
        $this->assertTrue(ctype_xdigit($rc->getTokenAuth()));
    }

    protected function setUpUser()
    {
        $user = array(
          'login'            => 'user',
          'password'         => 'geqgeagae',
          'email'            => 'test@test.com',
          'superuser_access' => 0
        );

        API::getInstance()->addUser($user['login'], $user['password'], $user['email']);

        $model = new \Piwik\Plugins\UsersManager\Model();
        $tokenAuth = $model->generateRandomTokenAuth();
        $model->addTokenAuth($user['login'], $tokenAuth, 'many users test', Date::now()->getDatetime());

        $user['tokenAuth'] = $tokenAuth;

        return $user;
    }

    private function setUpSuperUserAccessViaDb()
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
