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
 * Login controller
 *
 * @package Piwik_Login
 */
class Piwik_Login_Controller extends Piwik_Controller
{
	/**
	 * Get referer to redirect to upon successful login.
	 * Remembers referer URL even if navigation is: login form -> reset password -> login form
	 *
	 * @returns string
	 */
	static public function getRefererToRedirect()
	{
		// retrieve any previously saved referer
		$ns = new Zend_Session_Namespace('Piwik_Login.referer');
		$referer = $ns->referer;
		if(empty($referer))
		{
			// if the referer contains module=Login, Installation, or CoreUpdater, we instead redirect to the doc root
			$referer = Piwik_Url::getLocalReferer();
			if(empty($referer) || preg_match('/module=(Login|Installation|CoreUpdater)/', $referer))
			{
				$referer = 'index.php';
			}
			$ns->referer = $referer;
			$ns->setExpirationSeconds(300, 'referer');
		}
		else if(!Piwik_Url::isLocalUrl($referer))
		{
			$referer = 'index.php';
		}

		return $referer;
	}

	/**
	 * Default action
	 *
	 * @param none
	 * @return void
	 */
	function index()
	{
		$this->login();
	}

	/**
	 * Login form
	 *
	 * @param string $messageNoAccess Access error message
	 * @param string $currentUrl Current URL
	 * @return void
	 */
	function login($messageNoAccess = null)
	{
		$urlToRedirect = self::getRefererToRedirect();

		$form = new Piwik_Login_Form();
		if($form->validate())
		{
			$nonce = $form->getSubmitValue('form_nonce');
			if(Piwik_Nonce::verifyNonce('Piwik_Login.login', $nonce))
			{
				$login = $form->getSubmitValue('form_login');
				$password = $form->getSubmitValue('form_password');
				$md5Password = md5($password);
				$messageNoAccess = $this->authenticateAndRedirect($login, $md5Password, $urlToRedirect);
			}
		}

		$view = Piwik_View::factory('login');
		$view->AccessErrorString = $messageNoAccess;
		$view->nonce = Piwik_Nonce::getNonce('Piwik_Login.login');
		$view->linkTitle = Piwik::getRandomTitle();
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();
	}

	/**
	 * Form-less login
	 *
	 * @param none
	 * @return void
	 */
	function logme()
	{
		$password = Piwik_Common::getRequestVar('password', null, 'string');
		if(strlen($password) != 32)
		{
			throw new Exception("The password parameter is expected to be a MD5 hash of the password.");
		}

		$login = Piwik_Common::getRequestVar('login', null, 'string');
		if($login == Zend_Registry::get('config')->superuser->login)
		{
			throw new Exception("The Super User cannot be authenticated using this URL.");
		}

		$currentUrl = 'index.php';
		$urlToRedirect = Piwik_Common::getRequestVar('url', $currentUrl, 'string');
		$urlToRedirect = htmlspecialchars_decode($urlToRedirect);

		$authenticated = $this->authenticateAndRedirect($login, $password, $urlToRedirect);
		if($authenticated === false)
		{
			echo Piwik_Translate('Login_LoginPasswordNotCorrect');
		}
	}

	/**
	 * Authenticate user and password.  Redirect if successful.
	 *
	 * @param string $login (user name)
	 * @param string $md5Password (md5 hash of password)
	 * @param string $urlToRedirect (URL to redirect to, if successfully authenticated)
	 * @return string (failure message if unable to authenticate)
	 */
	protected function authenticateAndRedirect($login, $md5Password, $urlToRedirect)
	{
		$tokenAuth = Piwik_UsersManager_API::getInstance()->getTokenAuth($login, $md5Password);

		$auth = Zend_Registry::get('auth');
		$auth->setLogin($login);
		$auth->setTokenAuth($tokenAuth);

		$authResult = $auth->authenticate();
		if(!$authResult->isValid())
		{
			return Piwik_Translate('Login_LoginPasswordNotCorrect');
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

		Piwik_Url::redirectToUrl($urlToRedirect);
	}

	/**
	 * Lost password form.  Email password reset information.
	 *
	 * @param none
	 * @return void
	 */
	function lostPassword()
	{
		$messageNoAccess = null;
		$urlToRedirect = self::getRefererToRedirect();

		$form = new Piwik_Login_PasswordForm();
		if($form->validate())
		{
			$loginMail = $form->getSubmitValue('form_login');
			$messageNoAccess = $this->lostPasswordFormValidated($loginMail);
		}

		$view = Piwik_View::factory('lostPassword');
		$view->AccessErrorString = $messageNoAccess;
		$view->linkTitle = Piwik::getRandomTitle();
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();
	}

	/**
	 * Validate user (by username or email address).
	 *
	 * @param string $loginMail (user name or email address)
	 * @param string $urlToRedirect (URL to redirect to, if successfully validated)
	 * @return string (failure message if unable to validate)
	 */
	protected function lostPasswordFormValidated($loginMail)
	{
		$user = self::getUserInformation($loginMail);
		if( $user === null )
		{
			return Piwik_Translate('Login_InvalidUsernameEmail');
		}

		$view = Piwik_View::factory('passwordsent');

		$login = $user['login'];
		$email = $user['email'];

		// construct a password reset token from user information
		$resetToken = self::generatePasswordResetToken($user);

		$ip = Piwik_Common::getIpString();
		$url = Piwik_Url::getCurrentUrlWithoutQueryString() . "?module=Login&action=resetPassword&token=$resetToken";

		// send email with new password
		try
		{
			$mail = new Piwik_Mail();
			$mail->addTo($email, $login);
			$mail->setSubject(Piwik_Translate('Login_MailTopicPasswordRecovery'));
			$mail->setBodyText(
				str_replace(
					'\n',
					"\n",
					sprintf(Piwik_Translate('Login_MailPasswordRecoveryBody'), $login, $ip, $url, $resetToken)
				) . "\n"
			);

			$piwikHost = $_SERVER['HTTP_HOST'];
			if(strlen($piwikHost) == 0)
			{
				$piwikHost = 'piwik.org';
			}

			$fromEmailName = Zend_Registry::get('config')->General->login_password_recovery_email_name;
			$fromEmailAddress = Zend_Registry::get('config')->General->login_password_recovery_email_address;
			$fromEmailAddress = str_replace('{DOMAIN}', $piwikHost, $fromEmailAddress);
			$mail->setFrom($fromEmailAddress, $fromEmailName);
			@$mail->send();
		}
		catch(Exception $e)
		{
			$view->ErrorString = $e->getMessage();
		}

		$view->linkTitle = Piwik::getRandomTitle();
		echo $view->render();

		exit;
	}

	/**
	 * Reset password form.  Enter new password here.
	 *
	 * @param none
	 * @return void
	 */
	function resetPassword()
	{
		$messageNoAccess = null;
		$urlToRedirect = self::getRefererToRedirect();

		$form = new Piwik_Login_ResetPasswordForm();
		if($form->validate())
		{
			$loginMail = $form->getSubmitValue('form_login');
			$token = $form->getSubmitValue('form_token');
			$password = $form->getSubmitValue('form_password');
			$messageNoAccess = $this->resetPasswordFormValidated($loginMail, $token, $password);
		}

		$view = Piwik_View::factory('resetPassword');
		$view->AccessErrorString = $messageNoAccess;
		$view->linkTitle = Piwik::getRandomTitle();
		$view->addForm( $form );
		$view->subTemplate = 'genericForm.tpl';
		echo $view->render();
	}

	/**
	 * Validate password reset request.  If successful, set new password and redirect.
	 *
	 * @param string $loginMail (user name or email address)
	 * @param string $token (password reset token)
	 * @param array of string $newPassword (new password)
	 * @param string $urlToRedirect (URL to redirect to, if successfully validated)
	 * @return string (failure message)
	 */
	protected function resetPasswordFormValidated($loginMail, $token, $password)
	{
		$user = self::getUserInformation($loginMail);
		if( $user === null )
		{
			return Piwik_Translate('Login_InvalidUsernameEmail');
		}

		if(!self::isValidToken($token, $user))
		{
			return Piwik_Translate('Login_InvalidOrExpiredToken');
		}

		try
		{
			if( $user['email'] == Zend_Registry::get('config')->superuser->email )
			{
				$user['password'] = md5($password);
				Zend_Registry::get('config')->superuser = $user;
			}
			else
			{
				Piwik_UsersManager_API::getInstance()->updateUser($user['login'], $password);
			}
		}
		catch(Exception $e)
		{
			$view->ErrorString = $e->getMessage();
		}

		$view = Piwik_View::factory('passwordchanged');
		$view->linkTitle = Piwik::getRandomTitle();
		echo $view->render();

		exit;
	}

	/**
	 * Get user information
	 *
	 * @param string $loginMail (user login or email address)
	 * @return array ("login" => '...', "email" => '...', "password" => '...') or null, if user not found
	 */
	protected function getUserInformation($loginMail)
	{
		Piwik::setUserIsSuperUser();

		$user = null;
		if( $loginMail == Zend_Registry::get('config')->superuser->email
			|| $loginMail == Zend_Registry::get('config')->superuser->login )
		{
			$user = array(
					'login' => Zend_Registry::get('config')->superuser->login,
					'email' => Zend_Registry::get('config')->superuser->email,
					'password' => Zend_Registry::get('config')->superuser->password,
			);
		}
		else if( Piwik_UsersManager_API::getInstance()->userExists($loginMail) )
		{
			$user = Piwik_UsersManager_API::getInstance()->getUser($loginMail);
		}
		else if( Piwik_UsersManager_API::getInstance()->userEmailExists($loginMail) )
		{
			$user = Piwik_UsersManager_API::getInstance()->getUserByEmail($loginMail);
		}

		return $user;
	}

	/**
	 * Generate a password reset token.  Expires in (roughly) 24 hours.
	 *
	 * @param array (user information)
	 * @param int $timestamp (Unix timestamp)
	 * @return string (generated token)
	 */
	protected function generatePasswordResetToken($user, $timestamp = null)
	{
		/*
		 * Piwik does not stored the generated password reset token.
		 * This avoids a database schema change and SQL queries to store, retrieve, and purge (expired) tokens.
		 */
		if(!$timestamp)
		{
			$timestamp = time() + 24*60*60; /* +24 hrs */
		}

		$expiry = strftime('%Y%m%d%H', $timestamp); 
		$token = md5($expiry . $user['login'] . $user['email'] . $user['password']);
		return $token;
	}

	/**
	 * Validate token.
	 *
	 * @param string $token
	 * @param array $user (user information)
	 * @return bool (true if valid, false otherwise)
	 */
	protected function isValidToken($token, $user)
	{
		$now = time();

		// token valid for 24 hrs (give or take, due to the coarse granularity in our strftime format string)
		for($i = 0; $i <= 24; $i++)
		{
			$generatedToken = self::generatePasswordResetToken($user, $now + $i*60*60);
			if($generatedToken == $token)
			{
				return true;
			}
		}

		// fails if token is invalid, expired, password already changed, other user information has changed, ...
		return false;
	}

	/**
	 * Clear session information
	 *
	 * @param none
	 * @return void
	 */
	static public function clearSession()
	{
		$authCookieName = Zend_Registry::get('config')->General->login_cookie_name;
		$cookie = new Piwik_Cookie($authCookieName);
		$cookie->delete();

		Zend_Session::expireSessionCookie();
	}

	/**
	 * Logout current user
	 *
	 * @param none
	 * @return void
	 */
	public function logout()
	{
		self::clearSession();
		Piwik::redirectToModule('CoreHome');
	}
}
