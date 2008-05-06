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
require_once "Login/PasswordForm.php";
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

		$currentUrl = Piwik_Url::getCurrentUrl();
		// get url from POSTed form or GET parameter (getting back from password remind form)
		$urlToRedirect = Piwik_Common::getRequestVar('form_url', htmlspecialchars($currentUrl), 'string');
					
		if($form->validate())
		{
			// value submitted in form
			$login = $form->getSubmitValue('form_login');
			$password = $form->getSubmitValue('form_password');
			$password = md5($password);
			
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
				
				$urlToRedirect = htmlspecialchars_decode($urlToRedirect);				
				Piwik_Url::redirectToUrl($urlToRedirect);
			}
			else
			{
				$messageNoAccess = Piwik_Translate('Login_LoginPasswordNotCorrect');
			}
		}
		$view = new Piwik_View('Login/templates/login.tpl');
		// make navigation login form -> reset password -> login form remember your first url
		$view->urlToRedirect = $urlToRedirect;
		$view->AccessErrorString = $messageNoAccess;
		$view->linkTitle = Piwik::getRandomTitle();
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();
	}
	
	function lostpassword($messageNoAccess = null)
	{
		$form = new Piwik_Login_PasswordForm;
		$AccessErrorString = false;
		
		$currentUrl = Piwik_Url::getCurrentUrlWithoutQueryString();	
		$urlToRedirect = Piwik_Common::getRequestVar('form_url', htmlspecialchars($currentUrl), 'string');
		
		if($form->validate())
		{
			// value submitted in form (login or email)
			$loginMail = $form->getSubmitValue('form_login');

			// get admin privileges before calling API
			Piwik::setUserIsSuperUser();
			
			$user = null;
			
			// determine if given value is login or email
			if( Piwik_UsersManager_API::userExists($loginMail) )
			{
				$user = Piwik_UsersManager_API::getUser($loginMail);
			}
			else if( Piwik_UsersManager_API::userEmailExists($loginMail) )
			{
				$user = Piwik_UsersManager_API::getUserByEmail($loginMail);

			}
			
			// if user exists
			if( $user != null )
			{
				$view = new Piwik_View('Login/templates/passwordsent.tpl');
							
				$login = $user['login'];
				$email = $user['email'];
							
				$randomPassword = Piwik_Common::getRandomString(8);
				
				Piwik_UsersManager_API::updateUser($login, $randomPassword);

				// send email with new password
				try 
				{
					$mail = new Piwik_Mail();
					$mail->addTo($email, $login);
					$mail->setSubject(Piwik_Translate('Login_MailTopicPasswordRecovery'));				
					$mail->setBodyText(sprintf(Piwik_Translate('Login_MailBodyPasswordRecovery'),
						$login, $randomPassword, Piwik_Url::getCurrentUrlWithoutQueryString()));

					$host = $_SERVER['HTTP_HOST'];
					if(strlen($host) == 0)
					{
						$host = 'piwik.org';
					}
					$mail->setFrom('password-recovery@'.$host, 'Piwik');
					@$mail->send();
				}
				catch(Exception $e)
				{
					$view->ErrorString = $e->getMessage();
				}

				$view->linkTitle = Piwik::getRandomTitle();
				$view->urlToRedirect = $urlToRedirect;
				echo $view->render();

				return;
			}
			else
			{
				$messageNoAccess = Piwik_Translate('Login_InvalidUsernameEmail');
			}
		}	
		$view = new Piwik_View('Login/templates/lostpassword.tpl');	
		$view->AccessErrorString = $messageNoAccess;
		// make navigation login form -> reset password -> login form remember your first url		
		$view->urlToRedirect = $urlToRedirect;
		$view->linkTitle = Piwik::getRandomTitle();
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();		
	}
	
	function logout()
	{		
		$authCookieName = 'piwik-auth';
		$cookie = new Piwik_Cookie($authCookieName);
		$cookie->delete();
		
		// after logout we redirect to the Homepage instead of the referer
		Piwik::redirectToModule('Home');
	}
	
}

