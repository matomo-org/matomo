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

		// get url from POSTed form or GET parameter (getting back from password remind form)
		$urlToRedirect = Piwik_Common::getRequestVar('form_url', htmlspecialchars(Piwik_Url::getCurrentUrl()), 'string');
		$urlToRedirect = htmlspecialchars_decode($urlToRedirect);
		if($form->validate())
		{
			$login = $form->getSubmitValue('form_login');
			$password = $form->getSubmitValue('form_password');
			$authenticated = $this->authenticateAndRedirect($login, $password, $urlToRedirect);
			if($authenticated === false)
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
	
	function logme()
	{
		$login = Piwik_Common::getRequestVar('login', null, 'string');
		$password = Piwik_Common::getRequestVar('password', null, 'string');
		$urlToRedirect = Piwik_Common::getRequestVar('url', Piwik_Url::getCurrentUrlWithoutFileName(), 'string');
		$authenticated = $this->authenticateAndRedirect($login, $password, $urlToRedirect);
		if($authenticated === false)
		{
			echo Piwik_Translate('Login_LoginPasswordNotCorrect');
		}
	}
	
	protected function authenticateAndRedirect($login, $password, $urlToRedirect)
	{
		if(strlen($password) != 32) 
		{
			$password = md5($password);
		}
		$tokenAuth = Piwik_UsersManager_API::getTokenAuth($login, $password);
		
		$auth = Zend_Registry::get('auth');
		$auth->setLogin($login);
		$auth->setTokenAuth($tokenAuth);
		$authResult = $auth->authenticate();
		if($authResult->isValid())
		{
			$authCookieName = 'piwik-auth';
			$authCookieExpiry = time() + 3600;
			$cookie = new Piwik_Cookie($authCookieName, $authCookieExpiry);
			$cookie->set('login', $login);
			$cookie->set('token_auth', $authResult->getTokenAuth());
			$cookie->save();

			$urlToRedirect = htmlspecialchars_decode($urlToRedirect);
			Piwik_Url::redirectToUrl($urlToRedirect);
		}
		return false;
	}
	
	function lostPassword($messageNoAccess = null)
	{
		$form = new Piwik_Login_PasswordForm;
		$currentUrl = Piwik_Url::getCurrentUrlWithoutQueryString();
		$urlToRedirect = Piwik_Common::getRequestVar('form_url', htmlspecialchars($currentUrl), 'string');

		if($form->validate())
		{
			$loginMail = $form->getSubmitValue('form_login');
			Piwik::setUserIsSuperUser();

			$user = null;

			if( Piwik_UsersManager_API::userExists($loginMail) )
			{
				$user = Piwik_UsersManager_API::getUser($loginMail);
			}
			else if( Piwik_UsersManager_API::userEmailExists($loginMail) )
			{
				$user = Piwik_UsersManager_API::getUserByEmail($loginMail);
			}

			if( $user === null )
			{
				$messageNoAccess = Piwik_Translate('Login_InvalidUsernameEmail');
			}
			else
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
					$mail->setBodyText(sprintf(Piwik_Translate('Login_MailPasswordRecoveryBody'),
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
		}
		$view = new Piwik_View('Login/templates/lostPassword.tpl');
		$view->AccessErrorString = $messageNoAccess;
		// make navigation login form -> reset password -> login form remember your first url
		$view->urlToRedirect = $urlToRedirect;
		$view->linkTitle = Piwik::getRandomTitle();
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();
	}

	static public function clearSession()
	{
		$authCookieName = 'piwik-auth';
		$cookie = new Piwik_Cookie($authCookieName);
		$cookie->delete();	
	}
	
	public function logout()
	{
		self::clearSession();
		Piwik::redirectToModule('CoreHome');
	}
}
