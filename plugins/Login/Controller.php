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

require_once "UsersManager/API.php";
require_once "Login/Form.php";
require_once "View.php";


/**
 * 
 * @package Piwik_Login
 */
class Piwik_Login_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'login';
	}
	
	function login( $messageNoAccess = null )
	{
		$form = new Piwik_Login_Form;
		$AccessErrorString = false;
		
		if($form->validate())
		{
			// value submitted in form
			$login = $form->getSubmitValue('form_login');
			$password = $form->getSubmitValue('form_password');
			$password = md5($password);
			
			$baseUrl = Piwik_Url::getCurrentUrlWithoutQueryString(); 
			$currentUrl = Piwik_Url::getCurrentUrl();		
			$urlToRedirect = Piwik_Common::getRequestVar('form_url', $currentUrl, 'string', $_POST);
			
			$urlToRedirect = htmlspecialchars_decode($urlToRedirect);
			
			$tokenAuth = Piwik_UsersManager_API::getTokenAuth($login, $password);
	
			Piwik_Login::prepareAuthObject($login, $tokenAuth);
			
			$auth = Zend_Registry::get('auth');
			
			if($auth->authenticate()->isValid())
			{
				$authCookieName = 'piwik-auth';
				$authCookieExpiry = time() + 3600;
				$cookie = new Piwik_Cookie($authCookieName, $authCookieExpiry);
				$cookie->set('login', $login);
				$tokenAuth = $auth->getTokenAuth();
				$cookie->set('token_auth', $tokenAuth);
				$cookie->save();
				
				Piwik_Url::redirectToUrl($urlToRedirect);
			}
			else
			{
				$messageNoAccess = 'login & password not correct';
			}
		}
	
		$view = new Piwik_View('login.tpl');	
		$view->AccessErrorString = $messageNoAccess;
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();
	}
	
	function logout()
	{		
		$authCookieName = 'piwik-auth';
		$cookie = new Piwik_Cookie($authCookieName);
		$cookie->delete();
		
		$baseUrl = Piwik_Url::getCurrentUrlWithoutQueryString();
	
		Piwik_Url::redirectToUrl($baseUrl);
	}
	
}

