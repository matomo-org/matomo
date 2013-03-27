<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
require_once 'Login/Auth.php';

class LoginTest extends DatabaseTestCase
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
        Zend_Registry::set('access', $pseudoMockAccess);

        // we make sure the tests don't depend on the config file content
        Piwik_Config::getInstance()->superuser = array(
            'login'    => 'superusertest',
            'password' => md5('passwordsuperusertest'),
            'email'    => 'superuser@example.com'
        );
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureNoLoginNoTokenAuth()
    {
        // no login; no token auth
        $auth = new Piwik_Login_Auth();
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureEmptyLoginNoTokenAuth()
    {
        // empty login; no token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureNonExistentUser()
    {
        // non-existent user
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('nobody');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousNotExisting()
    {
        // anonymous user doesn't exist yet
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousNotExistentEmptyLogin()
    {
        // empty login; anonymous user doesn't exist yet
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousNotExistentEmptyLoginWithTokenAuth()
    {
        // API authentication; anonymous user doesn't exist yet
        $auth = new Piwik_Login_Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousNotExistentWithLoginAndTokenAuth()
    {
        // anonymous user doesn't exist yet
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousWithLogin()
    {
        Piwik::createAnonymousUser();

        // missing token_auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousEmptyLoginWithTokenAuth()
    {
        Piwik::createAnonymousUser();

        // empty login
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureAnonymousLoginTokenAuthMissmatch()
    {
        Piwik::createAnonymousUser();

        // not equal
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessAnonymousWithTokenAuth()
    {
        Piwik::createAnonymousUser();

        // API authentication
        $auth = new Piwik_Login_Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessAnonymous()
    {
        Piwik::createAnonymousUser();

        // valid login & token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('anonymous');
        $auth->setTokenAuth('anonymous');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS, $rc->getCode());
    }

    protected function _setUpUser()
    {
        $user = array('login'    => 'user',
                      'password' => "geqgeagae",
                      'email'    => "test@test.com",
                      'alias'    => "alias");
        Piwik_UsersManager_API::getInstance()->addUser($user['login'], $user['password'], $user['email'], $user['alias']);
        $password = md5($user['password']);
        $user['tokenAuth'] = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);
        return $user;
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserEmptyTokenAuth()
    {
        $user = $this->_setUpUser();

        // empty token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserInvalidTokenAuth()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($user['password']);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserInvalidTokenAuth2()
    {
        $user = $this->_setUpUser();

        // not a token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth(md5($user['password']));
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserEmptyLogin()
    {
        $user = $this->_setUpUser();

        // empty login
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('');
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch()
    {
        $user = $this->_setUpUser();

        // not equal
        $auth = new Piwik_Login_Auth();
        $auth->setLogin(0);
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch2()
    {
        $user = $this->_setUpUser();

        // not equal
        $auth = new Piwik_Login_Auth();
        $auth->setLogin(0);
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureUserLoginTokenAuthMissmatch3()
    {
        $user = $this->_setUpUser();

        // not equal
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessUserTokenAuth()
    {
        $user = $this->_setUpUser();

        // API authentication
        $auth = new Piwik_Login_Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessUserLoginAndTokenAuth()
    {
        $user = $this->_setUpUser();

        // valid login & token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($user['tokenAuth']);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessLoginAndHashedTokenAuth()
    {
        $user = $this->_setUpUser();

        // valid login & hashed token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $hash = $auth->getHashTokenAuth($user['login'], $user['tokenAuth']);
        $auth->setTokenAuth($hash);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureSuperUserEmptyTokenAuth()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // empty token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth('');
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureSuperUserInvalidTokenAuth()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // not a token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($user['password']);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureSuperUserInvalidTokenAuth2()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // not a token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($password);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureSuperUserEmptyLogin()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // empty login
        $auth = new Piwik_Login_Auth();
        $auth->setLogin('');
        $auth->setTokenAuth($tokenAuth);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateFailureSuperUserLoginTokenAuthMissmatch()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // not equal
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth(0);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::FAILURE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessSuperUserTokenAuth()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // API authentication
        $auth = new Piwik_Login_Auth();
        $auth->setLogin(null);
        $auth->setTokenAuth($tokenAuth);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessSuperLoginAndTokenAuth()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // valid login & token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $auth->setTokenAuth($tokenAuth);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, $rc->getCode());
    }

    /**
     * @group Plugins
     * @group Login
     */
    public function testAuthenticateSuccessSuperUserLoginAndHashedTokenAuth()
    {
        $user = Piwik_Config::getInstance()->superuser;
        $password = $user['password'];
        $tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

        // valid login & hashed token auth
        $auth = new Piwik_Login_Auth();
        $auth->setLogin($user['login']);
        $hash = $auth->getHashTokenAuth($user['login'], $tokenAuth);
        $auth->setTokenAuth($hash);
        $rc = $auth->authenticate();
        $this->assertEquals(Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, $rc->getCode());
    }
}
