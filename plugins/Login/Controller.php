<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Login;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Cookie;
use Piwik\IP;
use Piwik\Mail;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\QuickForm2;
use Piwik\Session;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

require_once PIWIK_INCLUDE_PATH . '/core/Config.php';

/**
 * Login controller
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * Generate hash on user info and password
     *
     * @param string $userInfo User name, email, etc
     * @param string $password
     * @return string
     */
    private function generateHash($userInfo, $password)
    {
        // mitigate rainbow table attack
        $passwordLen = strlen($password) / 2;
        $hash = Common::hash(
            $userInfo . substr($password, 0, $passwordLen)
            . SettingsPiwik::getSalt() . substr($password, $passwordLen)
        );
        return $hash;
    }

    /**
     * Default action
     *
     * @param none
     * @return string
     */
    function index()
    {
        return $this->login();
    }

    /**
     * Login form
     *
     * @param string $messageNoAccess Access error message
     * @param bool $infoMessage
     * @internal param string $currentUrl Current URL
     * @return string
     */
    function login($messageNoAccess = null, $infoMessage = false)
    {
        $form = new FormLogin();
        if ($form->validate()) {
            $nonce = $form->getSubmitValue('form_nonce');
            if (Nonce::verifyNonce('Login.login', $nonce)) {
                $login = $form->getSubmitValue('form_login');
                $password = $form->getSubmitValue('form_password');
                $rememberMe = $form->getSubmitValue('form_rememberme') == '1';
                $md5Password = md5($password);
                try {
                    $this->authenticateAndRedirect($login, $md5Password, $rememberMe);
                } catch (Exception $e) {
                    $messageNoAccess = $e->getMessage();
                }
            } else {
                $messageNoAccess = $this->getMessageExceptionNoAccess();
            }
        }

        $view = new View('@Login/login');
        $view->AccessErrorString = $messageNoAccess;
        $view->infoMessage = nl2br($infoMessage);
        $view->addForm($form);
        $this->configureView($view);
        self::setHostValidationVariablesView($view);

        return $view->render();
    }

    /**
     * Configure common view properties
     *
     * @param View $view
     */
    private function configureView($view)
    {
        $this->setBasicVariablesView($view);

        $view->linkTitle = Piwik::getRandomTitle();

        // crsf token: don't trust the submitted value; generate/fetch it from session data
        $view->nonce = Nonce::getNonce('Login.login');
    }

    /**
     * Form-less login
     * @see how to use it on http://piwik.org/faq/how-to/#faq_30
     * @throws Exception
     * @return void
     */
    function logme()
    {
        $password = Common::getRequestVar('password', null, 'string');
        $this->checkPasswordHash($password);

        $login = Common::getRequestVar('login', null, 'string');
        if (Piwik::hasTheUserSuperUserAccess($login)) {
            throw new Exception(Piwik::translate('Login_ExceptionInvalidSuperUserAccessAuthenticationMethod', array("logme")));
        }

        $currentUrl = 'index.php';

        if (($idSite = Common::getRequestVar('idSite', false, 'int')) !== false) {
            $currentUrl .= '?idSite=' . $idSite;
        }

        $urlToRedirect = Common::getRequestVar('url', $currentUrl, 'string');
        $urlToRedirect = Common::unsanitizeInputValue($urlToRedirect);

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
    protected function authenticateAndRedirect($login, $md5Password, $rememberMe, $urlToRedirect = false)
    {
        Nonce::discardNonce('Login.login');

        \Piwik\Registry::get('auth')->initSession($login, $md5Password, $rememberMe);

        if(empty($urlToRedirect)) {
            $urlToRedirect = Url::getCurrentUrlWithoutQueryString();
        }

        Url::redirectToUrl($urlToRedirect);
    }

    protected function getMessageExceptionNoAccess()
    {
        $message = Piwik::translate('Login_InvalidNonceOrHeadersOrReferrer', array('<a href="?module=Proxy&action=redirect&url=' . urlencode('http://piwik.org/faq/how-to-install/#faq_98') . '" target="_blank">', '</a>'));
        // Should mention trusted_hosts or link to FAQ
        return $message;
    }

    /**
     * Reset password action. Stores new password as hash and sends email
     * to confirm use.
     *
     * @param none
     */
    function resetPassword()
    {
        $infoMessage = null;
        $formErrors = null;

        $form = new FormResetPassword();
        if ($form->validate()) {
            $nonce = $form->getSubmitValue('form_nonce');
            if (Nonce::verifyNonce('Login.login', $nonce)) {
                $formErrors = $this->resetPasswordFirstStep($form);
                if (empty($formErrors)) {
                    $infoMessage = Piwik::translate('Login_ConfirmationLinkSent');
                }
            } else {
                $formErrors = array($this->getMessageExceptionNoAccess());
            }
        } else {
            // if invalid, display error
            $formData = $form->getFormData();
            $formErrors = $formData['errors'];
        }

        $view = new View('@Login/resetPassword');
        $view->infoMessage = $infoMessage;
        $view->formErrors = $formErrors;

        return $view->render();
    }

    /**
     * Saves password reset info and sends confirmation email.
     *
     * @param QuickForm2 $form
     * @return array Error message(s) if an error occurs.
     */
    private function resetPasswordFirstStep($form)
    {
        $loginMail = $form->getSubmitValue('form_login');
        $password  = $form->getSubmitValue('form_password');

        // check the password
        try {
            UsersManager::checkPassword($password);
        } catch (Exception $ex) {
            return array($ex->getMessage());
        }

        // get the user's login
        if ($loginMail === 'anonymous') {
            return array(Piwik::translate('Login_InvalidUsernameEmail'));
        }

        $user = self::getUserInformation($loginMail);
        if ($user === null) {
            return array(Piwik::translate('Login_InvalidUsernameEmail'));
        }

        $login = $user['login'];

        // if valid, store password information in options table, then...
        Login::savePasswordResetInfo($login, $password);

        // ... send email with confirmation link
        try {
            $this->sendEmailConfirmationLink($user);
        } catch (Exception $ex) {
            // remove password reset info
            Login::removePasswordResetInfo($login);

            return array($ex->getMessage() . Piwik::translate('Login_ContactAdmin'));
        }

        return null;
    }

    /**
     * Sends email confirmation link for a password reset request.
     *
     * @param array $user User info for the requested password reset.
     */
    private function sendEmailConfirmationLink($user)
    {
        $login = $user['login'];
        $email = $user['email'];

        // construct a password reset token from user information
        $resetToken = self::generatePasswordResetToken($user);

        $ip = IP::getIpFromHeader();
        $url = Url::getCurrentUrlWithoutQueryString()
            . "?module=Login&action=confirmResetPassword&login=" . urlencode($login)
            . "&resetToken=" . urlencode($resetToken);

        // send email with new password
        $mail = new Mail();
        $mail->addTo($email, $login);
        $mail->setSubject(Piwik::translate('Login_MailTopicPasswordChange'));
        $bodyText = str_replace(
                '\n',
                "\n",
                sprintf(Piwik::translate('Login_MailPasswordChangeBody'), $login, $ip, $url)
            ) . "\n";
        $mail->setBodyText($bodyText);

        $fromEmailName = Config::getInstance()->General['login_password_recovery_email_name'];
        $fromEmailAddress = Config::getInstance()->General['login_password_recovery_email_address'];
        $mail->setFrom($fromEmailAddress, $fromEmailName);

        $replytoEmailName = Config::getInstance()->General['login_password_recovery_replyto_email_name'];
        $replytoEmailAddress = Config::getInstance()->General['login_password_recovery_replyto_email_address'];
        $mail->setReplyTo($replytoEmailAddress, $replytoEmailName);

        @$mail->send();
    }

    /**
     * Password reset confirmation action. Finishes the password reset process.
     * Users visit this action from a link supplied in an email.
     */
    public function confirmResetPassword()
    {
        $errorMessage = null;

        $login = Common::getRequestVar('login', '');
        $resetToken = Common::getRequestVar('resetToken', '');

        try {
            // get password reset info & user info
            $user = self::getUserInformation($login);
            if ($user === null) {
                throw new Exception(Piwik::translate('Login_InvalidUsernameEmail'));
            }

            // check that the reset token is valid
            $resetPassword = Login::getPasswordToResetTo($login);
            if ($resetPassword === false || !self::isValidToken($resetToken, $user)) {
                throw new Exception(Piwik::translate('Login_InvalidOrExpiredToken'));
            }

            // reset password of user
            $this->setNewUserPassword($user, $resetPassword);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();
        }

        if (is_null($errorMessage)) // if success, show login w/ success message
        {
            $this->redirectToIndex(Piwik::getLoginPluginName(), 'resetPasswordSuccess');
            return;
        } else {
            // show login page w/ error. this will keep the token in the URL
            return $this->login($errorMessage);
        }
    }

    /**
     * Sets the password for a user.
     *
     * @param array $user User info.
     * @param string $passwordHash The hashed password to use.
     * @throws Exception
     */
    private function setNewUserPassword($user, $passwordHash)
    {
        $this->checkPasswordHash($passwordHash);
        API::getInstance()->updateUser(
            $user['login'], $passwordHash, $email = false, $alias = false, $isPasswordHashed = true);
    }

    /**
     * The action used after a password is successfully reset. Displays the login
     * screen with an extra message. A separate action is used instead of returning
     * the HTML in confirmResetPassword so the resetToken won't be in the URL.
     */
    public function resetPasswordSuccess()
    {
        return $this->login($errorMessage = null, $infoMessage = Piwik::translate('Login_PasswordChanged'));
    }

    /**
     * Get user information
     *
     * @param string $loginMail user login or email address
     * @return array ("login" => '...', "email" => '...', "password" => '...') or null, if user not found
     */
    protected function getUserInformation($loginMail)
    {
        Piwik::setUserHasSuperUserAccess();

        $user = null;
        if (API::getInstance()->userExists($loginMail)) {
            $user = API::getInstance()->getUser($loginMail);
        } else if (API::getInstance()->userEmailExists($loginMail)) {
            $user = API::getInstance()->getUserByEmail($loginMail);
        }

        return $user;
    }

    /**
     * Generate a password reset token.  Expires in (roughly) 24 hours.
     *
     * @param array $user user information
     * @param int $timestamp Unix timestamp
     * @return string generated token
     */
    protected function generatePasswordResetToken($user, $timestamp = null)
    {
        /*
         * Piwik does not store the generated password reset token.
         * This avoids a database schema change and SQL queries to store, retrieve, and purge (expired) tokens.
         */
        if (!$timestamp) {
            $timestamp = time() + 24 * 60 * 60; /* +24 hrs */
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
        for ($i = 0; $i <= 24; $i++) {
            $generatedToken = self::generatePasswordResetToken($user, $now + $i * 60 * 60);
            if ($generatedToken === $token) {
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
        $authCookieName = Config::getInstance()->General['login_cookie_name'];
        $cookie = new Cookie($authCookieName);
        $cookie->delete();

        Session::expireSessionCookie();
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

        $logoutUrl = @Config::getInstance()->General['login_logout_url'];
        if (empty($logoutUrl)) {
            Piwik::redirectToModule('CoreHome');
        } else {
            Url::redirectToUrl($logoutUrl);
        }
    }

    /**
     * @param $password
     * @throws \Exception
     */
    protected function checkPasswordHash($password)
    {
        if (strlen($password) != 32) {
            throw new Exception(Piwik::translate('Login_ExceptionPasswordMD5HashExpected'));
        }
    }
}
