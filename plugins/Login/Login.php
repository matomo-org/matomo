<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Login
 */

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
			'description' => Piwik_Translate('Login_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
		return $info;
	}

	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.initAuthenticationObject'	=> 'initAuthenticationObject',
			'FrontController.NoAccessException'		=> 'noAccess',
			'API.Request.authenticate' => 'ApiRequestAuthenticate',
			'Login.initSession' => 'initSession',
		);
		return $hooks;
	}

	function noAccess( $notification )
	{
		$exception  = $notification->getNotificationObject();
		$exceptionMessage = $exception->getMessage();

		$controller = new Piwik_Login_Controller();
		$controller->login($exceptionMessage);
	}

	function ApiRequestAuthenticate($notification)
	{
		$tokenAuth = $notification->getNotificationObject();
		Zend_Registry::get('auth')->setLogin($login = null);
		Zend_Registry::get('auth')->setTokenAuth($tokenAuth);
	}

	function initAuthenticationObject($notification)
	{
		$auth = new Piwik_Login_Auth();
		Zend_Registry::set('auth', $auth);

		$action = Piwik::getAction();
		if(Piwik::getModule() === 'API'
			&& (empty($action) || $action == 'index'))
		{
			return;
		}

		$authCookieName = Zend_Registry::get('config')->General->login_cookie_name;
		$authCookieExpiry = time() + Zend_Registry::get('config')->General->login_cookie_expire;
		$authCookiePath = Zend_Registry::get('config')->General->login_cookie_path;
		$authCookie = new Piwik_Cookie($authCookieName, $authCookieExpiry, $authCookiePath);
		$defaultLogin = 'anonymous';
		$defaultTokenAuth = 'anonymous';
		if($authCookie->isCookieFound())
		{
			$defaultLogin = $authCookie->get('login');
			$defaultTokenAuth = $authCookie->get('token_auth');
		}
		$auth->setLogin($defaultLogin);
		$auth->setTokenAuth($defaultTokenAuth);
	}
	
	function initSession($notification)
	{
		$info = $notification->getNotificationObject();
		$login = $info['login'];
		$md5Password = $info['md5Password'];
		
		$tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($login, $md5Password);

		$auth = Zend_Registry::get('auth');
		$auth->setLogin($login);
		$auth->setTokenAuth($tokenAuth);

		$authResult = $auth->authenticate();
		if(!$authResult->isValid())
		{
			throw new Exception(Piwik_Translate('Login_LoginPasswordNotCorrect'));
		}

		$ns = new Zend_Session_Namespace('Piwik_Login.referer');
		unset($ns->referer);

		$authCookieName = Zend_Registry::get('config')->General->login_cookie_name;
		$authCookieExpiry = time() + Zend_Registry::get('config')->General->login_cookie_expire;
		$authCookiePath = Zend_Registry::get('config')->General->login_cookie_path;
		$cookie = new Piwik_Cookie($authCookieName, $authCookieExpiry, $authCookiePath);
		$cookie->set('login', $login);
		$cookie->set('token_auth', $authResult->getTokenAuth());
		$cookie->save();

		Zend_Session::regenerateId();
	}
}
