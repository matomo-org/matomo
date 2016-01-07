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
use Piwik\Container\StaticContainer;
use Piwik\Cookie;
use Piwik\Log;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\QuickForm2;
use Piwik\Session;
use Piwik\Url;
use Piwik\View;

/**
 * Login controller
 * @api
 */
class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @var PasswordResetter
     */
    private $passwordResetter;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var SessionInitializer
     */
    private $sessionInitializer;

    /**
     * Constructor.
     *
     * @param PasswordResetter $passwordResetter
     * @param AuthInterface $auth
     * @param SessionInitializer $authenticatedSessionFactory
     */
    public function __construct($passwordResetter = null, $auth = null, $sessionInitializer = null)
    {
        parent::__construct();

        if (empty($passwordResetter)) {
            $passwordResetter = new PasswordResetter();
        }
        $this->passwordResetter = $passwordResetter;

        if (empty($auth)) {
            $auth = StaticContainer::get('Piwik\Auth');
        }
        $this->auth = $auth;

        if (empty($sessionInitializer)) {
            $sessionInitializer = new SessionInitializer();
        }
        $this->sessionInitializer = $sessionInitializer;
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
        $form->removeAttribute('action'); // remove action attribute, otherwise hash part will be lost
        if ($form->validate()) {
            $nonce = $form->getSubmitValue('form_nonce');
            if (Nonce::verifyNonce('Login.login', $nonce)) {
                $login = $form->getSubmitValue('form_login');
                $password = $form->getSubmitValue('form_password');
                $rememberMe = $form->getSubmitValue('form_rememberme') == '1';
                try {
                    $this->authenticateAndRedirect($login, $password, $rememberMe);
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

        $this->authenticateAndRedirect($login, $password, false, $urlToRedirect, $passwordHashed = true);
    }

    /**
     * Error message shown when an AJAX request has no access
     *
     * @param string $errorMessage
     * @return string
     */
    public function ajaxNoAccess($errorMessage)
    {
        return sprintf(
            '<div class="alert alert-danger">
                <p><strong>%s:</strong> %s</p>
                <p><a href="%s">%s</a></p>
            </div>',
            Piwik::translate('General_Error'),
            htmlentities($errorMessage, Common::HTML_ENCODING_QUOTE_STYLE, 'UTF-8', $doubleEncode = false),
            'index.php?module=' . Piwik::getLoginPluginName(),
            Piwik::translate('Login_LogIn')
        );
    }

    /**
     * Authenticate user and password.  Redirect if successful.
     *
     * @param string $login user name
     * @param string $password plain-text or hashed password
     * @param bool $rememberMe Remember me?
     * @param string $urlToRedirect URL to redirect to, if successfully authenticated
     * @param bool $passwordHashed indicates if $password is hashed
     * @return string failure message if unable to authenticate
     */
    protected function authenticateAndRedirect($login, $password, $rememberMe, $urlToRedirect = false, $passwordHashed = false)
    {
        Nonce::discardNonce('Login.login');

        $this->auth->setLogin($login);
        if ($passwordHashed === false) {
            $this->auth->setPassword($password);
        } else {
            $this->auth->setPasswordHash($password);
        }

        $this->sessionInitializer->initSession($this->auth, $rememberMe);

        // remove password reset entry if it exists
        $this->passwordResetter->removePasswordResetInfo($login);

        if (empty($urlToRedirect)) {
            $urlToRedirect = Url::getCurrentUrlWithoutQueryString();
        }

        Url::redirectToUrl($urlToRedirect);
    }

    protected function getMessageExceptionNoAccess()
    {
        $message = Piwik::translate('Login_InvalidNonceOrHeadersOrReferrer', array('<a href="?module=Proxy&action=redirect&url=' . urlencode('http://piwik.org/faq/how-to-install/#faq_98') . '" target="_blank">', '</a>'));

        $message .= $this->getMessageExceptionNoAccessWhenInsecureConnectionMayBeUsed();

        return $message;
    }

    /**
     * The Session cookie is set to a secure cookie, when SSL is mis-configured, it can cause the PHP session cookie ID to change on each page view.
     * Indicate to user how to solve this particular use case by forcing secure connections.
     *
     * @return string
     */
    protected function getMessageExceptionNoAccessWhenInsecureConnectionMayBeUsed()
    {
        $message = '';
        if(Url::isSecureConnectionAssumedByPiwikButNotForcedYet()) {
            $message = '<br/><br/>' . Piwik::translate('Login_InvalidNonceSSLMisconfigured',
                    array(
                        '<a href="?module=Proxy&action=redirect&url=' . urlencode('<a href="http://piwik.org/faq/how-to/faq_91/">') . '">',
                        '</a>',
                        'config/config.ini.php',
                        '<pre>force_ssl=1</pre>',
                        '<pre>[General]</pre>',
                    )
                );
        }
        return $message;
    }

    /**
     * Reset password action. Stores new password as hash and sends email
     * to confirm use.
     *
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

        try {
            $this->passwordResetter->initiatePasswordResetProcess($loginMail, $password);
        } catch (Exception $ex) {
            Log::debug($ex);

            return array($ex->getMessage());
        }

        return null;
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
            $this->passwordResetter->confirmNewPassword($login, $resetToken);
        } catch (Exception $ex) {
            Log::debug($ex);

            $errorMessage = $ex->getMessage();
        }

        if (is_null($errorMessage)) { // if success, show login w/ success message
            return $this->resetPasswordSuccess();
        } else {
            // show login page w/ error. this will keep the token in the URL
            return $this->login($errorMessage);
        }
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
     * Clear session information
     *
     * @param none
     * @return void
     */
    public static function clearSession()
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
}
