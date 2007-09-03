<?php
require "Login/Controller.php";
require "Cookie.php";

class Piwik_Login extends Piwik_Plugin
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'Login',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => false,
		);
		
		return $info;
	}
	
	function install()
	{
	}
	
	function uninstall()
	{
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.authSetCredentials' 		=> 'authSetCredentials',
			'FrontController.NoAccessException'			=> 'noAccess',
		);
		return $hooks;
	}
	
	function noAccess( $notification )
	{
		$exception  = $notification->getNotificationObject();
		$exceptionMessage = $exception->getMessage(); 
		
		$controller = new Piwik_Login_Controller;
		$controller->login($exceptionMessage);
	}
	
	function authSetCredentials($notification)
	{
		$auth = $notification->getNotificationObject();
					
		$authCookieName = 'piwik-auth';
		$authCookieExpiry = time() + 3600;

		$authCookie = new Piwik_Cookie($authCookieName, $authCookieExpiry);
		
		$login = $tokenAuth = 'abc';
		
		if($authCookie->isCookieFound())
		{
			$login = $authCookie->get('login');
			$tokenAuth =  $authCookie->get('token');
		}
		
		$this->prepareAuthObject( $login, $tokenAuth);		
	}
	
	static function prepareAuthObject( $login, $tokenAuth )
	{		
		$auth = Zend_Registry::get('auth');
		$auth->setTableName(Piwik::prefixTable('user'))
			->setIdentityColumn('login')
			->setCredentialColumn('token_auth')
//			->setCredentialTreatment('MD5(?)')
			->setIdentity($login)
	     	->setCredential($tokenAuth);
	}
}

