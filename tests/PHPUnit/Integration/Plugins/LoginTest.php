<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\AuthResult;
use Piwik\Config;
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

        //finally we set the user as a super user by default
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);

        // we make sure the tests don't depend on the config file content
        Config::getInstance()->superuser = array(
            'login'    => 'superUserLogin',
            'password' => md5('passwordsuperusertest'),
            'email'    => 'superuser@example.com'
        );

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
        $this->assertUserLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessAnonymous()
    {
        DbHelper::createAnonymousUser();

        // valid login & token auth
        $rc = $this->authenticate($login = 'anonymous', $authToken = 'anonymous');
        $this->assertUserLogin($rc);
    }

    protected function _setUpUser()
    {
        $user = array('login'    => 'user',
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");
        API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);
        $password = md5($user['password']);
        $user['tokenAuth'] = API::getInstance()->getTokenAuth($user['login'], $password);
        return $user;
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
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch()
    {
        $user = $this->_setUpUser();

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
    public function testAuthenticateSuccessLoginAndHashedTokenAuth()
    {
        $user = $this->_setUpUser();
        $hash = $this->auth->getHashTokenAuth($user['login'], $user['tokenAuth']);

        // valid login & hashed token auth
        $rc = $this->authenticate($user['login'], $tokenAuth = $hash);
        $this->assertUserLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserEmptyTokenAuth()
    {
        $user = $this->getSuperUserInfo();

        // empty token auth
        $rc = $this->authenticate($user['login'], $tokenAuth = '');
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserInvalidTokenAuth()
    {
        $user = $this->getSuperUserInfo();

        // not a token auth
        $rc = $this->authenticate($user['login'], $user['password']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserEmptyLogin()
    {
        $user = $this->getSuperUserInfo();

        // empty login
        $rc = $this->authenticate($login = '', $user['tokenAuth']);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserLoginTokenAuthMissmatch()
    {
        $user = $this->getSuperUserInfo();

        // not equal
        $rc = $this->authenticate($user['login'], $tokenAuth = 0);
        $this->assertFailedLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessSuperUserTokenAuth()
    {
        $user = $this->getSuperUserInfo();

        // API authentication
        $rc = $this->authenticate($login = null, $user['tokenAuth']);
        $this->assertSuperUserLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessSuperLoginAndTokenAuth()
    {
        $user = $this->getSuperUserInfo();

        // valid login & token auth
        $rc = $this->authenticate($user['login'], $user['tokenAuth']);
        $this->assertSuperUserLogin($rc);
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessSuperUserLoginAndHashedTokenAuth()
    {
        // valid login & hashed token auth
        $user = $this->getSuperUserInfo();
        $hash = $this->auth->getHashTokenAuth($user['login'], $user['tokenAuth']);

        $rc = $this->authenticate($user['login'], $tokenAuth = $hash);
        $this->assertSuperUserLogin($rc);
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

    private function assertSuperUserLogin(AuthResult $authResult)
    {
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $authResult->getCode());
    }

    private function assertUserLogin(AuthResult $authResult)
    {
        $this->assertEquals(AuthResult::SUCCESS, $authResult->getCode());
    }

    private function getSuperUserInfo()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        $user['tokenAuth'] = $tokenAuth;

        return $user;
    }
}
