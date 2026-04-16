<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Exception;
use Piwik\API\Request;
use Piwik\Request\AuthenticationToken;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\IP;
use Piwik\NoAccessException;
use Piwik\Piwik;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Plugins\Login\Security\LoginFromDifferentCountryDetection;
use Piwik\Plugins\UsersManager\UserLoginHelper;
use Piwik\Session;
use Piwik\SettingsServer;

class Login extends \Piwik\Plugin
{
    /**
     * @var bool
     */
    private $hasAddedFailedAttempt = false;

    /**
     * @var bool
     */
    private $hasPerformedBruteForceCheck = false;

    /**
     * @var bool
     */
    private $hasPerformedBruteForceCheckForUserPwdLogin = false;

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $hooks = [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'User.isNotAuthorized'             => 'noAccess',
            'API.Request.authenticate'         => 'apiRequestAuthenticate',
            'AssetManager.getJavaScriptFiles'  => 'getJsFiles',
            'AssetManager.getStylesheetFiles'  => 'getStylesheetFiles',
            'Session.beforeSessionStart'       => 'beforeSessionStart',

            // for brute force prevention of all tracking + reporting api requests
            'Request.initAuthenticationObject' => 'onInitAuthenticationObject',
            'API.UsersManager.createAppSpecificTokenAuth' => 'beforeCreateAppSpecificTokenAuthCheckBruteForce', // doesn't require auth but can be used to authenticate

            // for brute force prevention of all UI requests
            'Controller.Login.logme'           => 'beforeLoginCheckBruteForce',
            'Controller.Login.'                => 'beforeLoginCheckBruteForce',
            'Controller.Login.index'           => 'beforeLoginCheckBruteForce',
            'Controller.Login.confirmResetPassword' => 'beforeLoginCheckBruteForce',
            'Controller.Login.confirmPassword' => 'beforeLoginCheckBruteForce',
            'Controller.Login.resetPassword'   => 'beforeLoginCheckBruteForce',
            'Controller.Login.login'           => 'beforeLoginCheckBruteForce',
            'Controller.TwoFactorAuth.loginTwoFactorAuth' => 'beforeLoginCheckBruteForce',
            'Controller.Login.acceptInvitation' => 'beforeLoginCheckBruteForce',
            'Controller.Login.declineInvitation' => 'beforeLoginCheckBruteForce',
            'Login.authenticate.successful'    => 'beforeLoginCheckBruteForce',
            'Login.beforeLoginCheckAllowed'    => 'beforeLoginCheckBruteForce',
            'Login.recordFailedLoginAttempt'   => 'onFailedLoginRecordAttempt', // record any failed attempt in UI
            'Login.authenticate.failed'        => 'onFailedLoginRecordAttempt', // record any failed attempt in UI
            'API.Request.authenticate.failed'  => 'onFailedAPILogin', // record any failed attempt in Reporting API
            'Tracker.Request.authenticate.failed' => 'onFailedLoginRecordAttempt', // record any failed attempt in Tracker API

            // for 'Login from a different country' notification
            'Login.authenticate.processSuccessfulSession.end' => 'checkLoginFromAnotherCountry',
        ];

        $loginPlugin = Piwik::getLoginPluginName();

        if ($loginPlugin && $loginPlugin !== 'Login') {
            $hooks['Controller.' . $loginPlugin . '.logme']           = 'beforeLoginCheckBruteForce';
            $hooks['Controller.' . $loginPlugin . '.']                = 'beforeLoginCheckBruteForce';
            $hooks['Controller.' . $loginPlugin . '.index']           = 'beforeLoginCheckBruteForce';
            $hooks['Controller.' . $loginPlugin . '.confirmResetPassword'] = 'beforeLoginCheckBruteForce';
            $hooks['Controller.' . $loginPlugin . '.confirmPassword'] = 'beforeLoginCheckBruteForce';
            $hooks['Controller.' . $loginPlugin . '.resetPassword']   = 'beforeLoginCheckBruteForce';
            $hooks['Controller.' . $loginPlugin . '.login']           = 'beforeLoginCheckBruteForce';
        }

        return $hooks;
    }

    /**
     * @param string[] $translations
     * @return void
     */
    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'Login_CurrentlyBlockedIPs';
        $translations[] = 'Login_CurrentlyBlockedIPsUnblockInfo';
        $translations[] = 'Login_UnblockAllIPs';
        $translations[] = 'Login_CurrentlyBlockedIPsUnblockConfirm';
        $translations[] = 'Login_IPsAlwaysBlocked';
    }

    /**
     * @return true
     */
    public function isTrackerPlugin()
    {
        return true;
    }

    /**
     * @return void
     */
    public function onInitAuthenticationObject()
    {
        if (SettingsServer::isTrackerApiRequest() || Request::isRootRequestApiRequest()) {
            // we check it for all API requests...
            // we do not check it for other UI requests as otherwise we would be logging out someone possibly already
            // logged in with a valid session which we don't want currently... regular UI requests are checked through
            // 1) any successful or failed login attempt, plus through specific controller action that a user can use
            // to log in
            $this->beforeLoginCheckBruteForce();
        }
    }

    /**
     * @return void
     */
    public function onFailedLoginRecordAttempt()
    {
        // we're always making sure on any success or failed login to check if user is actually allowed to log in
        // in case for some reason it forgot to run the check
        $this->beforeLoginCheckBruteForce();

        // we are recording new failed attempts only when user can currently log in and is not blocked...
        // this is to kind of block eg a certain IP continuously. could alternatively also still keep writing those failed
        // attempts into the log and only allow login attempts again after the user had no login attempts for the configured
        // time frame
        $bruteForce = StaticContainer::get('Piwik\Plugins\Login\Security\BruteForceDetection');
        if ($bruteForce->isEnabled() && !$this->hasAddedFailedAttempt) {
            $login = $this->getUsernameUsedInPasswordLogin();
            $bruteForce->addFailedAttempt(IP::getIpFromHeader(), $login);
            // we make sure to log max one failed login attempt per request... otherwise we might log 3 or many more
            // if eg API is called etc.
            $this->hasAddedFailedAttempt = true;
        }
    }

    /**
     * @return void
     */
    public function onFailedAPILogin()
    {
        $this->onFailedLoginRecordAttempt();

        // Only throw an exception if this is an API request
        if ($this->isModuleIsAPI()) {
            // Throw an exception if a token was provided but it was invalid
            if (StaticContainer::get(AuthenticationToken::class)->wasTokenAuthProvidedSecurely()) {
                throw new NoAccessException('Unable to authenticate with the provided token. It is either invalid or expired.');
            } else {
                throw new NoAccessException('Unable to authenticate with the provided token. It is either invalid, expired or is required to be sent as a POST parameter.');
            }
        }
    }

    /**
     * @param string $login
     * @return void
     */
    public function checkLoginFromAnotherCountry($login)
    {
        if ('anonymous' === $login) {
            // do not send notification to "anonymous"
            return;
        }

        $loginFromDifferentCountryDetection = StaticContainer::get(LoginFromDifferentCountryDetection::class);
        if ($loginFromDifferentCountryDetection->isEnabled()) {
            $loginFromDifferentCountryDetection->check($login);
        }
    }

    /**
     * @return void
     */
    public function beforeLoginCheckBruteForce()
    {
        $this->performBruteForceCheck($this->getUsernameUsedInPasswordLogin());
    }

    /**
     * @param array<string, mixed> $params
     */
    public function beforeCreateAppSpecificTokenAuthCheckBruteForce(array $params): void
    {
        $userLogin = $params['userLogin'] ?? '';

        if (!is_string($userLogin)) {
            $userLogin = '';
        }

        $this->performBruteForceCheck($this->normalizeUserLogin($userLogin));
    }

    private function normalizeUserLogin(string $userLogin): string
    {
        return UserLoginHelper::normalizeLoginOrEmailToLogin($userLogin);
    }

    private function performBruteForceCheck(?string $login): void
    {
        /** @var BruteForceDetection $bruteForce */
        $bruteForce = StaticContainer::get('Piwik\Plugins\Login\Security\BruteForceDetection');

        if (!$bruteForce->isEnabled()) {
            return;
        }

        if (!$this->hasPerformedBruteForceCheck && !$bruteForce->isAllowedToLogin(IP::getIpFromHeader())) {
            throw new Exception(Piwik::translate('Login_LoginNotAllowedBecauseBlocked'));
        }

        // for performance reasons we make sure to execute it only once per request
        $this->hasPerformedBruteForceCheck = true;

        if (
            empty($login)
            || strtolower($login) === 'anonymous'
        ) {
            return; // can't do the check if we don't know the login
        }

        if (!$this->hasPerformedBruteForceCheckForUserPwdLogin && $bruteForce->isUserLoginBlocked($login)) {
            $ex = new NoAccessException(Piwik::translate('Login_LoginNotAllowedBecauseUserLoginBlocked'), 403);
            throw $ex;
        }
        // for performance reasons we make sure to execute it only once per request
        $this->hasPerformedBruteForceCheckForUserPwdLogin = true;
    }

    /**
     * @param string[] $jsFiles
     * @return void
     */
    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Login/javascripts/login.js";
        $jsFiles[] = "plugins/Login/javascripts/bruteforcelog.js";
    }

    /**
     * @param string[] $stylesheetFiles
     * @return void
     */
    public function getStylesheetFiles(&$stylesheetFiles)
    {
        $stylesheetFiles[] = "plugins/Login/stylesheets/login.less";
        $stylesheetFiles[] = "plugins/Login/stylesheets/variables.less";
    }

    /**
     * @return void
     */
    public function beforeSessionStart()
    {
        if (!$this->shouldHandleRememberMe()) {
            return;
        }

        // if this is a login request & form_rememberme was set, change the session cookie expire time before starting the session
        if (\Piwik\Request::fromPost()->getBoolParameter('form_rememberme', false)) {
            $loginCookieExpire = GeneralConfig::getConfigValue('login_cookie_expire');
            if (!is_numeric($loginCookieExpire)) {
                $loginCookieExpire = null;
            } else {
                $loginCookieExpire = (int) $loginCookieExpire;
            }

            Session::rememberMe($loginCookieExpire);
        }
    }

    private function shouldHandleRememberMe(): bool
    {
        $module = Piwik::getModule();
        $action = Piwik::getAction();
        return ($module == 'Login' || $module == 'CoreHome') && (empty($action) || $action == 'index' || $action == 'login');
    }

    /**
     * Redirects to Login form with error message.
     * Listens to User.isNotAuthorized hook.
     *
     * @return void
     */
    public function noAccess(Exception $exception)
    {
        $frontController = FrontController::getInstance();

        if (Common::isXmlHttpRequest()) {
            $response = $frontController->dispatch(Piwik::getLoginPluginName(), 'ajaxNoAccess', [$exception->getMessage()]);
            echo is_string($response) ? $response : '';
            return;
        }

        $response = $frontController->dispatch(Piwik::getLoginPluginName(), 'login', [$exception->getMessage()]);
        echo is_string($response) ? $response : '';
    }

    /**
     * Set login name and authentication token for API request.
     * Listens to API.Request.authenticate hook.
     *
     * @param string $tokenAuth
     * @return void
     */
    public function apiRequestAuthenticate(
        #[\SensitiveParameter]
        $tokenAuth
    ) {
        $this->beforeLoginCheckBruteForce();

        /** @var \Piwik\Auth $auth */
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setLogin($login = null);
        $auth->setTokenAuth($tokenAuth);
    }

    /**
     * @return bool
     */
    protected static function isModuleIsAPI()
    {
        return Piwik::getModule() === 'API'
            && (Piwik::getAction() == '' || Piwik::getAction() === 'index');
    }

    private function getUsernameUsedInPasswordLogin(): string
    {
        $login = StaticContainer::get(\Piwik\Auth::class)->getLogin();
        if (empty($login) || $login === 'anonymous') {
            $login = \Piwik\Request::fromRequest()->getStringParameter('form_login', '');
            if (Piwik::getAction() === 'logme') {
                $login = \Piwik\Request::fromRequest()->getStringParameter('login', $login);
            }
        }

        return $this->normalizeUserLogin($login);
    }

    /**
     * @return void
     */
    public function deactivate()
    {
        Session::destroyAllSessions();
    }
}
