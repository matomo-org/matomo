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
require "Login/Auth.php";
require "Cookie.php";

/**
 * 
 * @package Piwik_Login
 */
class Piwik_Login extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'Login',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		return $info;
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.authSetCredentials'	=> 'authSetCredentials',
			'FrontController.NoAccessException'		=> 'noAccess',
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
		$authAdapter = new Piwik_Login_Auth();
     	Zend_Registry::set('auth', $authAdapter);
		
     	if(Piwik::getModule() === 'API')
     	{
			$tokenAuthAPIInUrl = Piwik_Common::getRequestVar('token_auth', '', 'string');
			if( !empty($tokenAuthAPIInUrl))
			{
				$authAdapter->setCredential($tokenAuthAPIInUrl);
			}
     	}
		else
		{
			$authCookieName = 'piwik-auth';
			$authCookieExpiry = time() + 3600;
			$authCookie = new Piwik_Cookie($authCookieName, $authCookieExpiry);
			$defaultLogin = 'anonymous';
			$defaultTokenAuth = 'anonymous';
			if($authCookie->isCookieFound())
			{
				$defaultLogin = $authCookie->get('login');
				$defaultTokenAuth = $authCookie->get('token_auth');
			}
			self::prepareAuthObject($defaultLogin, $defaultTokenAuth);
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
