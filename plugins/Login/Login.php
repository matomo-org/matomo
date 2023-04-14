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
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Http\HttpCodeException;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\Login\Security\BruteForceDetection;
use Piwik\Session;
use Piwik\SettingsServer;

/**
 *
 */
class Login extends \Piwik\Plugin
{
    private $hasAddedFailedAttempt = false;
    private $hasPerformedBruteForceCheck = false;
    private $hasPerformedBruteForceCheckForUserPwdLogin = false;

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $hooks = array(
            'User.isNotAuthorized'             => 'noAccess',
            'API.Request.authenticate'         => 'ApiRequestAuthenticate',
            'AssetManager.getJavaScriptFiles'  => 'getJsFiles',
            'AssetManager.getStylesheetFiles'  => 'getStylesheetFiles',
            'Session.beforeSessionStart'       => 'beforeSessionStart',

            // for brute force prevention of all tracking + reporting api requests
            'Request.initAuthenticationObject' => 'onInitAuthenticationObject',
            'API.UsersManager.createAppSpecificTokenAuth' => 'beforeLoginCheckBruteForce', // doesn't require auth but can be used to authenticate

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
            'Login.beforeLoginCheckAllowed'  => 'beforeLoginCheckBruteForce',
            'Login.recordFailedLoginAttempt'  => 'onFailedLoginRecordAttempt', // record any failed attempt in UI
            'Login.authenticate.failed'        => 'onFailedLoginRecordAttempt', // record any failed attempt in UI
            'API.Request.authenticate.failed' => 'onFailedLoginRecordAttempt', // record any failed attempt in Reporting API
            'Tracker.Request.authenticate.failed' => 'onFailedLoginRecordAttempt', // record any failed attempt in Tracker API
        );

        $loginPlugin = Piwik::getLoginPluginName();

        if ($loginPlugin && $loginPlugin !== 'Login') {
            $hooks['Controller.'.$loginPlugin.'.logme']           = 'beforeLoginCheckBruteForce';
            $hooks['Controller.'.$loginPlugin. '.']               = 'beforeLoginCheckBruteForce';
            $hooks['Controller.'.$loginPlugin.'.index']           = 'beforeLoginCheckBruteForce';
            $hooks['Controller.'.$loginPlugin.'.confirmResetPassword'] = 'beforeLoginCheckBruteForce';
            $hooks['Controller.'.$loginPlugin.'.confirmPassword'] = 'beforeLoginCheckBruteForce';
            $hooks['Controller.'.$loginPlugin.'.resetPassword']   = 'beforeLoginCheckBruteForce';
            $hooks['Controller.'.$loginPlugin.'.login']           = 'beforeLoginCheckBruteForce';
        }

        return $hooks;
    }

    public function isTrackerPlugin()
    {
        return true;
    }

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

    public function beforeLoginCheckBruteForce()
    {
        $bruteForce = StaticContainer::get('Piwik\Plugins\Login\Security\BruteForceDetection');
        if (!$this->hasPerformedBruteForceCheck && $bruteForce->isEnabled() && !$bruteForce->isAllowedToLogin(IP::getIpFromHeader())) {
            throw new Exception(Piwik::translate('Login_LoginNotAllowedBecauseBlocked'));
        }

        // for performance reasons we make sure to execute it only once per request
        $this->hasPerformedBruteForceCheck = true;

        // now check that user login (from any ip) is not blocked
        $login = $this->getUsernameUsedInPasswordLogin();
        if (empty($login)
            || $login == 'anonymous'
        ) {
            return; // can't do the check if we don't know the login
        }

        /** @var BruteForceDetection $bruteForce */
        $bruteForce = StaticContainer::get('Piwik\Plugins\Login\Security\BruteForceDetection');
        if (!$this->hasPerformedBruteForceCheckForUserPwdLogin && $bruteForce->isEnabled() && $bruteForce->isUserLoginBlocked($login)) {
            $ex = new HttpCodeException(Piwik::translate('Login_LoginNotAllowedBecauseUserLoginBlocked'), 403);
            throw $ex;
        }
        // for performance reasons we make sure to execute it only once per request
        $this->hasPerformedBruteForceCheckForUserPwdLogin = true;
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Login/javascripts/login.js";
        $jsFiles[] = "plugins/Login/javascripts/bruteforcelog.js";
    }

   public function getStylesheetFiles(&$stylesheetFiles)
    {
        $stylesheetFiles[] = "plugins/Login/stylesheets/login.less";
        $stylesheetFiles[] = "plugins/Login/stylesheets/variables.less";
    }

    public function beforeSessionStart()
    {
        if (!$this->shouldHandleRememberMe()) {
            return;
        }

        // if this is a login request & form_rememberme was set, change the session cookie expire time before starting the session
        $rememberMe = isset($_POST['form_rememberme']) ? $_POST['form_rememberme'] : null;
        if ($rememberMe == '1') {
            Session::rememberMe(Config::getInstance()->General['login_cookie_expire']);
        }
    }

    private function shouldHandleRememberMe()
    {
        $module = Piwik::getModule();
        $action = Piwik::getAction();
        return ($module == 'Login' || $module == 'CoreHome') && (empty($action) || $action == 'index' || $action == 'login');
    }

    /**
     * Redirects to Login form with error message.
     * Listens to User.isNotAuthorized hook.
     */
    public function noAccess(Exception $exception)
    {
        $frontController = FrontController::getInstance();

        if (Common::isXmlHttpRequest()) {
            echo $frontController->dispatch(Piwik::getLoginPluginName(), 'ajaxNoAccess', array($exception->getMessage()));
            return;
        }

        echo $frontController->dispatch(Piwik::getLoginPluginName(), 'login', array($exception->getMessage()));
    }

    /**
     * Set login name and authentication token for API request.
     * Listens to API.Request.authenticate hook.
     */
    public function ApiRequestAuthenticate($tokenAuth)
    {
        $this->beforeLoginCheckBruteForce();

        /** @var \Piwik\Auth $auth */
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setLogin($login = null);
        $auth->setTokenAuth($tokenAuth);
    }

    protected static function isModuleIsAPI()
    {
        return Piwik::getModule() === 'API'
                && (Piwik::getAction() == '' || Piwik::getAction() == 'index');
    }

    private function getUsernameUsedInPasswordLogin()
    {
        $login = StaticContainer::get(\Piwik\Auth::class)->getLogin();
        if (empty($login) || $login == 'anonymous') {
            $login = Common::getRequestVar('form_login', false);
            if (Piwik::getAction() === 'logme') {
                $login = Common::getRequestVar('login', $login);
            }
        }

        return $login;
    }


}
