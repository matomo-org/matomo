<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

if(!class_exists('Piwik_Login_Auth', false))
{
	require_once 'Login/Auth.php';
}
require_once 'Database.test.php';

class Test_Piwik_Login extends Test_Database
{
	function setUp()
	{
		parent::setUp();
    	
		// setup the access layer
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::setIdSitesView( array(1,2));
		FakeAccess::setIdSitesAdmin( array(3,4));
		
		//finally we set the user as a super user by default
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
		
		// we make sure the tests don't depend on the config file content
		Piwik_Config::getInstance()->superuser = array(
			'login'=>'superusertest',
			'password'=>md5('passwordsuperusertest'),
			'email'=>'superuser@example.com'
		);
	}

	public function test_authenticate()
	{
		// no login; no token auth
		$auth = new Piwik_Login_Auth();
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// empty login; no token auth
		$auth->setLogin('');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// non-existent user
		$auth->setLogin('nobody');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// anonymous user doesn't exist yet
		$auth->setLogin('anonymous');
		$auth->setTokenAuth('');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// empty login; anonymous user doesn't exist yet
		$auth->setLogin('');
		$auth->setTokenAuth('anonymous');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// API authentication; anonymous user doesn't exist yet
		$auth->setLogin(null);
		$auth->setTokenAuth('anonymous');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// anonymous user doesn't exist yet
		$auth->setLogin('anonymous');
		$auth->setTokenAuth('anonymous');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		Piwik::createAnonymousUser();

		// missing token_auth
		$auth->setLogin('anonymous');
		$auth->setTokenAuth('');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// empty login
		$auth->setLogin('');
		$auth->setTokenAuth('anonymous');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not equal
		$auth->setLogin('anonymous');
		$auth->setTokenAuth(0);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// API authentication
		$auth->setLogin(null);
		$auth->setTokenAuth('anonymous');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS );

		// valid login & token auth
		$auth->setLogin('anonymous');
		$auth->setTokenAuth('anonymous');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS );

	   	$user = array( 'login'=>'user',
    					'password'=>"geqgeagae",
    					'email'=>"test@test.com",
    					'alias'=>"alias");
    	Piwik_UsersManager_API::getInstance()->addUser($user['login'],$user['password'] ,$user['email'] ,$user['alias'] );
		$password = md5($user['password']);
		$tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

		// empty token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth('');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not a token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth($user['password']);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not a token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth($password);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// empty login
		$auth->setLogin('');
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not equal
		$auth->setLogin(0);
		$auth->setTokenAuth(0);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not equal
		$auth->setLogin(0);
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not equal
		$auth->setLogin($user['login']);
		$auth->setTokenAuth(0);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// API authentication
		$auth->setLogin(null);
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS );

		// valid login & token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS );

		// valid login & hashed token auth
		$auth->setLogin($user['login']);
		$hash = $auth->getHashTokenAuth($user['login'], $tokenAuth);
		$auth->setTokenAuth($hash);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS );

		$user = Piwik_Config::getInstance()->superuser;
		$password = $user['password'];
		$tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($user['login'], $password);

		// empty token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth('');
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not a token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth($user['password']);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not a token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth($password);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// empty login
		$auth->setLogin('');
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// not equal
		$auth->setLogin($user['login']);
		$auth->setTokenAuth(0);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::FAILURE );

		// API authentication
		$auth->setLogin(null);
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE );

		// valid login & token auth
		$auth->setLogin($user['login']);
		$auth->setTokenAuth($tokenAuth);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE );

		// valid login & hashed token auth
		$auth->setLogin($user['login']);
		$hash = $auth->getHashTokenAuth($user['login'], $tokenAuth);
		$auth->setTokenAuth($hash);
		$rc = $auth->authenticate();
		$this->assertEqual( $rc->getCode(), Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE );
	}
}
