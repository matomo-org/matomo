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
			'description' => 'Login Authentication plugin, reading the credentials from the config/config.inc.php file for the Super User, and from the Database for the other users. Can be easily replaced to introduce a new Authentication mechanism (OpenID, htaccess, custom Auth, etc.).',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		return $info;
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.initAuthenticationObject'	=> 'initAuthenticationObject',
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
	
	function initAuthenticationObject($notification)
	{
		$authAdapter = new Piwik_Login_Auth();
     	Zend_Registry::set('auth', $authAdapter);
		
     	if(Piwik::getModule() === 'API' && Piwik::getAction() != 'listAllAPI')
     	{
			$tokenAuthAPIInUrl = Piwik_Common::getRequestVar('token_auth', 'anonymous', 'string');
			if( !empty($tokenAuthAPIInUrl))
			{
				$authAdapter->setTokenAuth($tokenAuthAPIInUrl);
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
		$auth->setLogin($login);
		$auth->setTokenAuth($tokenAuth);
	}
}
