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
            'login'    => 'superusertest',
            'password' => md5('passwordsuperusertest'),
            'email'    => 'superuser@example.com'
        );
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureNoLoginNoTokenAuth()
    {
        // no login; no token auth
        $auth = new Auth();
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureEmptyLoginNoTokenAuth()
    {
        // empty login; no token auth
        $auth = new Auth();
        $auth->setLogin('');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureNonExistentUser()
    {
        // non-existent user
        $auth = new Auth();
        $auth->setLogin('nobody');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExisting()
    {
        // anonymous user doesn't exist yet
        $auth = new Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExistentEmptyLogin()
    {
        // empty login; anonymous user doesn't exist yet
        $auth = new Auth();
        $auth->setLogin('');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExistentEmptyLoginWithTokenAuth()
    {
        // API authentication; anonymous user doesn't exist yet
        $auth = new Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousNotExistentWithLoginAndTokenAuth()
    {
        // anonymous user doesn't exist yet
        $auth = new Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousWithLogin()
    {
        DbHelper::createAnonymousUser();

        // missing token_auth
        $auth = new Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousEmptyLoginWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // empty login
        $auth = new Auth();
        $auth->setLogin('');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureAnonymousLoginTokenAuthMissmatch()
    {
        DbHelper::createAnonymousUser();

        // not equal
        $auth = new Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessAnonymousWithTokenAuth()
    {
        DbHelper::createAnonymousUser();

        // API authentication
        $auth = new Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessAnonymous()
    {
        DbHelper::createAnonymousUser();

        // valid login & token auth
        $auth = new Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $rc->getCode());
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
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserInvalidTokenAuth()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($user['password']);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserInvalidTokenAuth2()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth(md5($user['password']));
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserEmptyLogin()
    {
        $user = $this->_setUpUser();

        // empty login
        $auth = new Auth();
        $auth->setLogin('');
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch()
    {
        $user = $this->_setUpUser();

        // not equal
        $auth = new Auth();
        $auth->setLogin(0);
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch2()
    {
        $user = $this->_setUpUser();

        // not equal
        $auth = new Auth();
        $auth->setLogin(0);
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch3()
    {
        $user = $this->_setUpUser();

        // not equal
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessUserTokenAuth()
    {
        $user = $this->_setUpUser();

        // API authentication
        $auth = new Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessUserLoginAndTokenAuth()
    {
        $user = $this->_setUpUser();

        // valid login & token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessLoginAndHashedTokenAuth()
    {
        $user = $this->_setUpUser();

        // valid login & hashed token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $hash = $auth->getHashTokenAuth($user['login'], $user['tokenAuth']);
        $auth->setTokenAuth($hash);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserEmptyTokenAuth()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // empty token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserInvalidTokenAuth()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // not a token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($user['password']);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserInvalidTokenAuth2()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // not a token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($password);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserEmptyLogin()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // empty login
        $auth = new Auth();
        $auth->setLogin('');
        $auth->setTokenAuth($tokenAuth);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateFailureSuperUserLoginTokenAuthMissmatch()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // not equal
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessSuperUserTokenAuth()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // API authentication
        $auth = new Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth($tokenAuth);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessSuperLoginAndTokenAuth()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // valid login & token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($tokenAuth);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $rc->getCode());
    }

    /**
     * @group Plugins
     */
    public function testAuthenticateSuccessSuperUserLoginAndHashedTokenAuth()
    {
        $user = Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = API::getInstance()->getTokenAuth($user['login'], $password);

        // valid login & hashed token auth
        $auth = new Auth();
        $auth->setLogin($user['login']);
        $hash = $auth->getHashTokenAuth($user['login'], $tokenAuth);
        $auth->setTokenAuth($hash);
        $rc = $auth->authenticate();
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $rc->getCode());
    }
}
