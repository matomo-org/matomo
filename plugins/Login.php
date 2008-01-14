<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Login
 */
require "Login/Controller.php";
require "Cookie.php";


/**
 * 
 * @package Piwik_Login
 */
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
		// Create auth object
		$authAdapter = new Piwik_Auth();
     	Zend_Registry::set('auth', $authAdapter);
		
		$tokenAuthAPIInUrl = Piwik_Common::getRequestVar('token_auth', '', 'string');
		if( !empty($tokenAuthAPIInUrl))
		{
			$authAdapter->setCredential($tokenAuthAPIInUrl);
		}
		else
		{
			// cookie based authentication
			$authCookieName = 'piwik-auth';
			$authCookieExpiry = time() + 3600;
	
			$authCookie = new Piwik_Cookie($authCookieName, $authCookieExpiry);
			
			// by defaul the login is anonymous
			$login = 'anonymous';
			// and the token_auth anonymous. 
			// Note that the user created in the DB has a token_auth value of anonymous
			$tokenAuth = 'anonymous';
			
			if($authCookie->isCookieFound())
			{
				$login = $authCookie->get('login');
				$tokenAuth =  $authCookie->get('token_auth');
			}
			self::prepareAuthObject($login, $tokenAuth);
		}
	}
	
	static function prepareAuthObject( $login, $tokenAuth )
	{		
		$auth = Zend_Registry::get('auth');
		$auth->setTableName(Piwik::prefixTable('user'))
			->setIdentityColumn('login')
			->setCredentialColumn('token_auth')
			->setIdentity($login)
	     	->setCredential($tokenAuth);
	}
}

