<?php
require_once "UsersManager.php";
require_once "Form.php";
class Piwik_Login_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'login';
	}
	
	function login()
	{
		print("<br>-----------<br>--LOGIN PAGE<br>-----------");
		
		$form = new Piwik_Login_Form;
		
		if($form->validate())
		{
			$authCookieName = 'piwik-auth';
			$authCookieExpiry = time() + 3600;
	
			// value submitted in form
			$login = 'root';
			$password = 'nintendo';
			
			$baseUrl = Piwik_Url::getCurrentHost() . Piwik_Url::getCurrentScriptName(); 
					
			$urlToRedirect = Piwik_Common::getRequestVar('url', $baseUrl, 'string', $_POST);
			$currentUrl = Piwik_Url::getCurrentUrl();
			
			$tokenAuth = Piwik_UsersManager_API::getTokenAuth($login,$password);
	
			Piwik_Login::prepareAuthObject($login, $tokenAuth);
			
			$auth = Zend_Registry::get('auth');
			
			if($auth->authenticate()->isValid())
			{
				print("Authenticated, redirecting to $urlToRedirect");
				
				
				if($currentUrl === $urlToRedirect)
				{
					print("We redirect to the homepage! $baseUrl");
				}
			}
			else
			{
				print("Error authenticated");
				
			}
		}
	
		$view = new Piwik_View('home.tpl');	
		$view->addForm( $form );
		$view->render();
	}
}
?>
