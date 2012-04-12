<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
	 * Generate hash on user info and password
	 *
	 * @param string $userinfo User name, email, etc
	 * @param string $password
	 * @return string
	 */
	private function generateHash($userInfo, $password)
	{
		// mitigate rainbow table attack
		$passwordLen = strlen($password) / 2;
		$hash = Piwik_Common::hash(
			$userInfo . substr($password, 0, $passwordLen)
			. Piwik_Common::getSalt() . substr($password, $passwordLen)
		);
		return $hash;
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
		self::checkForceSslLogin();

		$form = new Piwik_Login_FormLogin();
		if($form->validate())
		{
			$nonce = $form->getSubmitValue('form_nonce');
			if(Piwik_Nonce::verifyNonce('Piwik_Login.login', $nonce))
			{
				$login = $form->getSubmitValue('form_login');
				$password = $form->getSubmitValue('form_password');
				$rememberMe = $form->getSubmitValue('form_rememberme') == '1';
				$md5Password = md5($password);
				try {
					$this->authenticateAndRedirect($login, $md5Password, $rememberMe);
				} catch(Exception $e) {
					$messageNoAccess = $e->getMessage();
				}
			}
			else
			{
				$messageNoAccess = $this->getMessageExceptionNoAccess();
			}
		}

		$view = Piwik_View::factory('login');
		$view->AccessErrorString = $messageNoAccess;
		$view->addForm( $form );
		$this->configureView($view);
		echo $view->render();
	}

	/**
	 * Configure common view properties
	 *
	 * @param Piwik_View $view
	 */
	private function configureView($view)
	{
		$this->setBasicVariablesView($view);

		$view->linkTitle = Piwik::getRandomTitle();

		$view->forceSslLogin = Piwik_Config::getInstance()->General['force_ssl_login'];

		// crsf token: don't trust the submitted value; generate/fetch it from session data
		$view->nonce = Piwik_Nonce::getNonce('Piwik_Login.login');
	}

	/**
	 * Form-less login
	 * @see how to use it on http://piwik.org/faq/how-to/#faq_30
	 * @param none
	 * @return void
	 */
	function logme()
	{
		self::checkForceSslLogin();

		$password = Piwik_Common::getRequestVar('password', null, 'string');
		if(strlen($password) != 32)
		{
			throw new Exception(Piwik_TranslateException('Login_ExceptionPasswordMD5HashExpected'));
		}

		$login = Piwik_Common::getRequestVar('login', null, 'string');
		if($login == Piwik_Config::getInstance()->superuser['login'])
		{
			throw new Exception(Piwik_TranslateException('Login_ExceptionInvalidSuperUserAuthenticationMethod', array("logme")));
		}

		$currentUrl = 'index.php';

		if(($idSite = Piwik_Common::getRequestVar('idSite', false, 'int')) !== false)
		{
			$currentUrl .= '?idSite='.$idSite;
		}

		$urlToRedirect = Piwik_Common::getRequestVar('url', $currentUrl, 'string');
		$urlToRedirect = Piwik_Common::unsanitizeInputValue($urlToRedirect);

		$this->authenticateAndRedirect($login, $password, false, $urlToRedirect);
	}

	/**
	 * Authenticate user and password.  Redirect if successful.
	 *
	 * @param string $login user name
	 * @param string $md5Password md5 hash of password
	 * @param bool $rememberMe Remember me?
	 * @param string $urlToRedirect URL to redirect to, if successfully authenticated
	 * @return string failure message if unable to authenticate
	 */
	protected function authenticateAndRedirect($login, $md5Password, $rememberMe, $urlToRedirect = 'index.php')
	{
		$info = array(	'login' => $login, 
						'md5Password' => $md5Password,
						'rememberMe' => $rememberMe,
		);
		Piwik_Nonce::discardNonce('Piwik_Login.login');
		Piwik_PostEvent('Login.initSession', $info);
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
		self::checkForceSslLogin();

		$messageNoAccess = null;

		$form = new Piwik_Login_FormPassword();
		if($form->validate())
		{
			$nonce = $form->getSubmitValue('form_nonce');
			if(Piwik_Nonce::verifyNonce('Piwik_Login.login', $nonce))
			{
				$loginMail = $form->getSubmitValue('form_login');
				$messageNoAccess = $this->lostPasswordFormValidated($loginMail);
			}
			else
			{
				$messageNoAccess = $this->getMessageExceptionNoAccess();
			}
		}

		$view = Piwik_View::factory('lostPassword');
		$view->AccessErrorString = $messageNoAccess;
		$view->addForm( $form );
		$this->configureView($view);
		echo $view->render();
	}

	protected function getMessageExceptionNoAccess()
	{
		return Piwik_Translate('Login_InvalidNonceOrHeadersOrReferer', array('<a href="?module=Proxy&action=redirect&url='.urlencode('http://piwik.org/faq/how-to-install/#faq_98').'" target="_blank">', '</a>'));
	}

	/**
	 * Validate user (by username or email address).
	 *
	 * @param string $loginMail user name or email address
	 * @return string failure message if unable to validate
	 */
	protected function lostPasswordFormValidated($loginMail)
	{
		if( $loginMail === 'anonymous' )
		{
			return Piwik_Translate('Login_InvalidUsernameEmail');
		}

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

		$ip = Piwik_IP::getIpFromHeader();
		$url = Piwik_Url::getCurrentUrlWithoutQueryString() . "?module=Login&action=resetPassword";

		// send email with new password
		try
		{
			$mail = new Piwik_Mail();
			$mail->addTo($email, $login);
			$mail->setSubject(Piwik_Translate('Login_MailTopicPasswordRecovery'));
			$bodyText = str_replace(
					'\n',
					"\n",
					sprintf(Piwik_Translate('Login_MailPasswordRecoveryBody'), $login, $ip, $url, $resetToken)
				) . "\n";
			$mail->setBodyText($bodyText);


			$fromEmailName = Piwik_Config::getInstance()->General['login_password_recovery_email_name'];
			$fromEmailAddress = Piwik_Config::getInstance()->General['login_password_recovery_email_address'];
			$mail->setFrom($fromEmailAddress, $fromEmailName);
			@$mail->send();
		}
		catch(Exception $e)
		{
			$view->ErrorString = $e->getMessage();
		}
		$this->configureView($view);
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
		self::checkForceSslLogin();

		$messageNoAccess = null;

		$form = new Piwik_Login_FormResetPassword();
		if($form->validate())
		{
			$nonce = $form->getSubmitValue('form_nonce');
			if(Piwik_Nonce::verifyNonce('Piwik_Login.login', $nonce))
			{
				$loginMail = $form->getSubmitValue('form_login');
				$token = $form->getSubmitValue('form_token');
				$password = $form->getSubmitValue('form_password');
				$messageNoAccess = $this->resetPasswordFormValidated($loginMail, $token, $password);
			}
			else
			{
				$messageNoAccess = $this->getMessageExceptionNoAccess();
			}
		}

		$view = Piwik_View::factory('resetPassword');
		$view->AccessErrorString = $messageNoAccess;
		$view->forceSslLogin = Piwik_Config::getInstance()->General['force_ssl_login'];
		$view->addForm( $form );
		$this->configureView($view);
		echo $view->render();
	}

	/**
	 * Validate password reset request.  If successful, set new password and redirect.
	 *
	 * @param string $loginMail user name or email address
	 * @param string $token password reset token
	 * @param string $password new password
	 * @return string failure message
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

		$view = Piwik_View::factory('passwordchanged');
		try
		{
			if( $user['email'] == Piwik::getSuperUserEmail() )
			{
				if(!Piwik_Config::getInstance()->isFileWritable())
				{
					throw new Exception(Piwik_Translate('General_ConfigFileIsNotWritable', array("(config/config.ini.php)","<br/>")));
				}

				$user['password'] = md5($password);
				Piwik_Config::getInstance()->superuser = $user;
				Piwik_Config::getInstance()->forceSave();
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

		$this->configureView($view);
		echo $view->render();
		exit;
	}

	/**
	 * Get user information
	 *
	 * @param string $loginMail user login or email address
	 * @return array ("login" => '...', "email" => '...', "password" => '...') or null, if user not found
	 */
	protected function getUserInformation($loginMail)
	{
		Piwik::setUserIsSuperUser();

		$user = null;
		if( $loginMail == Piwik::getSuperUserEmail()
			|| $loginMail == Piwik_Config::getInstance()->superuser['login'] )
		{
			$user = array(
					'login' => Piwik_Config::getInstance()->superuser['login'],
					'email' => Piwik::getSuperUserEmail(),
					'password' => Piwik_Config::getInstance()->superuser['password'],
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
	 * @param array user information
	 * @param int $timestamp Unix timestamp
	 * @return string generated token
	 */
	protected function generatePasswordResetToken($user, $timestamp = null)
	{
		/*
		 * Piwik does not store the generated password reset token.
		 * This avoids a database schema change and SQL queries to store, retrieve, and purge (expired) tokens.
		 */
		if(!$timestamp)
		{
			$timestamp = time() + 24*60*60; /* +24 hrs */
		}

		$expiry = strftime('%Y%m%d%H', $timestamp); 
		$token = $this->generateHash(
			$expiry . $user['login'] . $user['email'],
			$user['password']
		);
		return $token;
	}

	/**
	 * Validate token.
	 *
	 * @param string $token
	 * @param array $user user information
	 * @return bool true if valid, false otherwise
	 */
	protected function isValidToken($token, $user)
	{
		$now = time();

		// token valid for 24 hrs (give or take, due to the coarse granularity in our strftime format string)
		for($i = 0; $i <= 24; $i++)
		{
			$generatedToken = self::generatePasswordResetToken($user, $now + $i*60*60);
			if($generatedToken === $token)
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
		$authCookieName = Piwik_Config::getInstance()->General['login_cookie_name'];
		$cookie = new Piwik_Cookie($authCookieName);
		$cookie->delete();

		Piwik_Session::expireSessionCookie();
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

	/**
	 * Check force_ssl_login and redirect if connection isn't secure and not using a reverse proxy
	 *
	 * @param none
	 * @return void
	 */
	protected function checkForceSslLogin()
	{
		$forceSslLogin = Piwik_Config::getInstance()->General['force_ssl_login'];
		if($forceSslLogin
			&& !Piwik::isHttps())
		{
			$url = 'https://'
				. Piwik_Url::getCurrentHost()
				. Piwik_Url::getCurrentScriptName()
				. Piwik_Url::getCurrentQueryString();
			Piwik_Url::redirectToUrl($url);
		}
	}
}
