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
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Session;

/**
 *
 */
class Login extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        $hooks = array(
            'User.isNotAuthorized'             => 'noAccess',
            'API.Request.authenticate'         => 'ApiRequestAuthenticate',
            'AssetManager.getJavaScriptFiles'  => 'getJsFiles',
            'AssetManager.getStylesheetFiles'  => 'getStylesheetFiles',
            'Session.beforeSessionStart'       => 'beforeSessionStart',
        );
        return $hooks;
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Login/javascripts/login.js";
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
        $module = Common::getRequestVar('module', false);
        $action = Common::getRequestVar('action', false);
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

    /**
     * @param $auth
     * @deprecated authenticating via cookie is handled in core by SessionAuth
     */
    public static function initAuthenticationFromCookie(\Piwik\Auth $auth, $activateCookieAuth)
    {
        // empty
    }

}
