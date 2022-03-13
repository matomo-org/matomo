<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Login;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Plugins\UsersManager\Model AS UsersModel;
use Piwik\QuickForm2;
use Piwik\Session;
use Piwik\Url;
use Piwik\UrlHelper;
use Piwik\View;

/**
 * Login controller
 * @api
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    const NONCE_CONFIRMRESETPASSWORD = 'loginConfirmResetPassword';

    /**
     * @var PasswordResetter
     */
    protected $passwordResetter;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var \Piwik\Session\SessionInitializer
     */
    protected $sessionInitializer;

    /**
     * @var BruteForceDetection
     */
    protected $bruteForceDetection;

    /**
     * @var SystemSettings
     */
    protected $systemSettings;

    /*
     * @var PasswordVerifier
     */
    protected $passwordVerify;

    /**
     * Constructor.
     *
     * @param PasswordResetter $passwordResetter
     * @param AuthInterface $auth
     * @param SessionInitializer $sessionInitializer
     * @param PasswordVerifier $passwordVerify
     * @param BruteForceDetection $bruteForceDetection
     * @param SystemSettings $systemSettings
     */
    public function __construct($passwordResetter = null, $auth = null, $sessionInitializer = null, $passwordVerify = null, $bruteForceDetection = null, $systemSettings = null)
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

        if (empty($passwordVerify)) {
            $passwordVerify = StaticContainer::get('Piwik\Plugins\Login\PasswordVerifier');
        }
        $this->passwordVerify = $passwordVerify;

        if (empty($sessionInitializer)) {
            $sessionInitializer = new \Piwik\Session\SessionInitializer();
        }
        $this->sessionInitializer = $sessionInitializer;

        if (empty($bruteForceDetection)) {
            $bruteForceDetection = StaticContainer::get('Piwik\Plugins\Login\Security\BruteForceDetection');
        }
        $this->bruteForceDetection = $bruteForceDetection;

        if (empty($systemSettings)) {
            $systemSettings = StaticContainer::get('Piwik\Plugins\Login\SystemSettings');
        }
        $this->systemSettings = $systemSettings;
    }

    /**
     * Default action
     *
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
            $messageNoAccess = Nonce::verifyNonceWithErrorMessage('Login.login', $nonce, null);

            // validate if there is error message
            if ($messageNoAccess === "") {
                $loginOrEmail = $form->getSubmitValue('form_login');
                $login = $this->getLoginFromLoginOrEmail($loginOrEmail);

                $password = $form->getSubmitValue('form_password');
                try {
                    $this->authenticateAndRedirect($login, $password);
                } catch (Exception $e) {
                    $messageNoAccess = $e->getMessage();
                }
            }
        }

        if ($messageNoAccess) {
            http_response_code(403);
        }

        $view = new View('@Login/login');
        $view->AccessErrorString = $messageNoAccess;
        $view->infoMessage = nl2br($infoMessage);
        $view->addForm($form);
        $this->configureView($view);
        self::setHostValidationVariablesView($view);

        return $view->render();
    }

    private function getLoginFromLoginOrEmail($loginOrEmail)
    {
        $model = new UsersModel();
        if (!$model->userExists($loginOrEmail)) {
            $user = $model->getUserByEmail($loginOrEmail);
            if (!empty($user)) {
                return $user['login'];
            }
        }

        return $loginOrEmail;
    }

    /**
     * Configure common view properties
     *
     * @param View $view
     */
    protected function configureView($view)
    {
        $this->setBasicVariablesNoneAdminView($view);

        $view->linkTitle = Piwik::getRandomTitle();

        // crsf token: don't trust the submitted value; generate/fetch it from session data
        $view->nonce = Nonce::getNonce('Login.login');
    }

    public function confirmPassword()
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        if (!$this->passwordVerify->hasPasswordVerifyBeenRequested()) {
            throw new Exception('Not available');
        }

        if (!Url::isValidHost()) {
            throw new Exception("Cannot confirm password with untrusted hostname!");
        }

        $nonceKey = 'confirmPassword';
        $messageNoAccess = '';

        if (!empty($_POST)) {
            $nonce = Common::getRequestVar('nonce', null, 'string', $_POST);
            $password = Common::getRequestVar('password', null, 'string', $_POST);
            if ($password) {
                $password = Common::unsanitizeInputValue($password);
            }
            $errorMessage = Nonce::verifyNonceWithErrorMessage($nonceKey, $nonce);
            if ($errorMessage !== "") {
                $messageNoAccess = $errorMessage;
            } elseif ($this->passwordVerify->isPasswordCorrect(Piwik::getCurrentUserLogin(), $password)) {
                $this->passwordVerify->setPasswordVerifiedCorrectly();
                return;
            } else {
                $messageNoAccess = Piwik::translate('Login_WrongPasswordEntered');
            }
        }

        return $this->renderTemplate('@Login/confirmPassword', array(
            'nonce' => Nonce::getNonce($nonceKey),
            'AccessErrorString' => $messageNoAccess,
            'loginPlugin' => Piwik::getLoginPluginName(),
        ));
    }

    /**
     * Form-less login
     * @see how to use it on http://piwik.org/faq/how-to/#faq_30
     * @throws Exception
     * @return void
     */
    function logme()
    {
        if (Config::getInstance()->General['login_allow_logme'] == 0) {
            throw new Exception('This functionality has been disabled in config');
        }

        $password = Common::getRequestVar('password', null, 'string');

        $login = Common::getRequestVar('login', null, 'string');
        if (Piwik::hasTheUserSuperUserAccess($login)) {
            throw new Exception(Piwik::translate('Login_ExceptionInvalidSuperUserAccessAuthenticationMethod', array("logme")));
        }

        $currentUrl = 'index.php';

        if ($this->idSite) {
            $currentUrl .= '?idSite=' . $this->idSite;
        }

        $urlToRedirect = Common::getRequestVar('url', $currentUrl, 'string');
        $urlToRedirect = Common::unsanitizeInputValue($urlToRedirect);

        $this->authenticateAndRedirect($login, $password, $urlToRedirect, $passwordHashed = true);
    }

    public function bruteForceLog()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate('bruteForceLog', array(
            'blockedIps' => $this->bruteForceDetection->getCurrentlyBlockedIps(),
            'blacklistedIps' => $this->systemSettings->blacklistedBruteForceIps->getValue()
        ));
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
     * @param string $urlToRedirect URL to redirect to, if successfully authenticated
     * @param bool $passwordHashed indicates if $password is hashed
     * @return string failure message if unable to authenticate
     */
    protected function authenticateAndRedirect($login, $password, $urlToRedirect = false, $passwordHashed = false)
    {
        Nonce::discardNonce('Login.login');

        $this->auth->setLogin($login);
        if ($passwordHashed === false) {
            $this->auth->setPassword($password);
        } else {
            $this->auth->setPasswordHash($password);
        }

        $this->sessionInitializer->initSession($this->auth);

        // remove password reset entry if it exists
        $this->passwordResetter->removePasswordResetInfo($login);

        $parsedUrl = parse_url($urlToRedirect);

        // only use redirect url if host is trusted
        if (!empty($parsedUrl['host']) && !Url::isValidHost($parsedUrl['host'])) {
            $e = new \Piwik\Exception\Exception('The redirect URL host is not valid, it is not a trusted host. If this URL is trusted, you can allow this in your config.ini.php file by adding the line <i>trusted_hosts[] = "'.Common::sanitizeInputValue($parsedUrl['host']).'"</i> under <i>[General]</i>');
            $e->setIsHtmlMessage();
            throw $e;
        }

        if (empty($urlToRedirect)) {
            $redirect = Common::unsanitizeInputValue(Common::getRequestVar('form_redirect', false));
            $redirectParams = UrlHelper::getArrayFromQueryString(UrlHelper::getQueryFromUrl($redirect));
            $module = Common::getRequestVar('module', '', 'string', $redirectParams);
            // when module is login, we redirect to home...
            if (!empty($module) && $module !== 'Login' && $module !== Piwik::getLoginPluginName() && $redirect) {
                $host = Url::getHostFromUrl($redirect);
                $currentHost = Url::getHost();
                $currentHost = explode(':', $currentHost, 2)[0];

                // we only redirect to a trusted host
                if (!empty($host) && !empty($currentHost) && $host == $currentHost && Url::isValidHost($host)
                ) {
                    $urlToRedirect = $redirect;
                }
            }
        }

        if (empty($urlToRedirect)) {
            $urlToRedirect = Url::getCurrentUrlWithoutQueryString();
        }

        Url::redirectToUrl($urlToRedirect);
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
            $errorMessage = Nonce::verifyNonceWithErrorMessage('Login.login', $nonce);
            if ($errorMessage === "") {
                $formErrors = $this->resetPasswordFirstStep($form);
                if (empty($formErrors)) {
                    $infoMessage = Piwik::translate('Login_ConfirmationLinkSent');
                }
            } else {
                $formErrors = array($errorMessage);
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
    protected function resetPasswordFirstStep($form)
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
        if (!Url::isValidHost()) {
            throw new Exception("Cannot confirm reset password with untrusted hostname!");
        }

        $errorMessage = null;
        $passwordHash = null;

        $login = Common::getRequestVar('login');
        $resetToken = Common::getRequestVar('resetToken');

        try {
            $passwordHash = $this->passwordResetter->checkValidConfirmPasswordToken($login, $resetToken);
        } catch (Exception $ex) {
            Log::debug($ex);

            $errorMessage = $ex->getMessage();
        }

        if (!empty($errorMessage)) {
            return $this->login($errorMessage);
        }

        if (!empty($_POST['nonce'])
            && !empty($_POST['mtmpasswordconfirm'])
            && !empty($resetToken)
            && !empty($login)
            && !empty($passwordHash)
            && empty($errorMessage)) {
            Nonce::checkNonce(self::NONCE_CONFIRMRESETPASSWORD, $_POST['nonce']);
            if ($this->passwordResetter->doesResetPasswordHashMatchesPassword($_POST['mtmpasswordconfirm'], $passwordHash)) {
                $this->passwordResetter->setHashedPasswordForLogin($login, $passwordHash);
                return $this->resetPasswordSuccess();
            } else {
                $errorMessage = Piwik::translate('Login_ConfirmPasswordResetWrongPassword');
            }
        }

        $nonce = Nonce::getNonce(self::NONCE_CONFIRMRESETPASSWORD);

        return $this->renderTemplateAs('confirmResetPassword', array(
            'nonce' => $nonce,
            'errorMessage' => $errorMessage
        ), 'basic');
    }

    /**
     * The action used after a password is successfully reset. Displays the login
     * screen with an extra message. A separate action is used instead of returning
     * the HTML in confirmResetPassword so the resetToken won't be in the URL.
     */
    public function resetPasswordSuccess()
    {
        $_POST = array(); // prevent showing error message username and password is missing
        return $this->login($errorMessage = null, $infoMessage = Piwik::translate('Login_PasswordChanged'));
    }

    /**
     * Clear session information
     *
     * @return void
     */
    public static function clearSession()
    {
        $sessionFingerprint = new Session\SessionFingerprint();
        $sessionFingerprint->clear();

        Session::expireSessionCookie();
    }

    /**
     * Logout current user
     *
     * @return void
     */
    public function logout()
    {
        Piwik::postEvent('Login.logout', array(Piwik::getCurrentUserLogin()));

        self::clearSession();

        $logoutUrl = @Config::getInstance()->General['login_logout_url'];
        if (empty($logoutUrl)) {
            Piwik::redirectToModule('CoreHome');
        } else {
            Url::redirectToUrl($logoutUrl);
        }
    }
}
