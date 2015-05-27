<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;
use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Exception\DatabaseSchemaIsNewerThanCodebaseException;
use Piwik\Http\ControllerResolver;
use Piwik\Http\Router;
use Piwik\Plugins\CoreAdminHome\CustomLogo;

/**
 * This singleton dispatches requests to the appropriate plugin Controller.
 *
 * Piwik uses this class for all requests that go through **index.php**. Plugins can
 * use it to call controller actions of other plugins.
 *
 * ### Examples
 *
 * **Forwarding controller requests**
 *
 *     public function myConfiguredRealtimeMap()
 *     {
 *         $_GET['changeVisitAlpha'] = false;
 *         $_GET['removeOldVisits'] = false;
 *         $_GET['showFooterMessage'] = false;
 *         return FrontController::getInstance()->dispatch('UserCountryMap', 'realtimeMap');
 *     }
 *
 * **Using other plugin controller actions**
 *
 *     public function myPopupWithRealtimeMap()
 *     {
 *         $_GET['changeVisitAlpha'] = false;
 *         $_GET['removeOldVisits'] = false;
 *         $_GET['showFooterMessage'] = false;
 *         $realtimeMap = FrontController::getInstance()->fetchDispatch('UserCountryMap', 'realtimeMap');
 *
 *         $view = new View('@MyPlugin/myPopupWithRealtimeMap.twig');
 *         $view->realtimeMap = $realtimeMap;
 *         return $realtimeMap->render();
 *     }
 *
 * For a detailed explanation, see the documentation [here](http://piwik.org/docs/plugins/framework-overview).
 *
 * @method static \Piwik\FrontController getInstance()
 */
class FrontController extends Singleton
{
    const DEFAULT_MODULE = 'CoreHome';

    /**
     * Set to false and the Front Controller will not dispatch the request
     *
     * @var bool
     */
    public static $enableDispatch = true;

    /**
     * Executes the requested plugin controller method.
     *
     * @throws Exception|\Piwik\PluginDeactivatedException in case the plugin doesn't exist, the action doesn't exist,
     *                                                     there is not enough permission, etc.
     *
     * @param string $module The name of the plugin whose controller to execute, eg, `'UserCountryMap'`.
     * @param string $action The controller method name, eg, `'realtimeMap'`.
     * @param array $parameters Array of parameters to pass to the controller method.
     * @return void|mixed The returned value of the call. This is the output of the controller method.
     * @api
     */
    public function dispatch($module = null, $action = null, $parameters = null)
    {
        if (self::$enableDispatch === false) {
            return;
        }

        $filter = new Router();
        $redirection = $filter->filterUrl(Url::getCurrentUrl());
        if ($redirection !== null) {
            Url::redirectToUrl($redirection);
            return;
        }

        try {
            $result = $this->doDispatch($module, $action, $parameters);
            return $result;
        } catch (NoAccessException $exception) {
            Log::debug($exception);

            /**
             * Triggered when a user with insufficient access permissions tries to view some resource.
             *
             * This event can be used to customize the error that occurs when a user is denied access
             * (for example, displaying an error message, redirecting to a page other than login, etc.).
             *
             * @param \Piwik\NoAccessException $exception The exception that was caught.
             */
            Piwik::postEvent('User.isNotAuthorized', array($exception), $pending = true);
        }
    }

    /**
     * Executes the requested plugin controller method and returns the data, capturing anything the
     * method `echo`s.
     *
     * _Note: If the plugin controller returns something, the return value is returned instead
     * of whatever is in the output buffer._
     *
     * @param string $module The name of the plugin whose controller to execute, eg, `'UserCountryMap'`.
     * @param string $actionName The controller action name, eg, `'realtimeMap'`.
     * @param array $parameters Array of parameters to pass to the controller action method.
     * @return string The `echo`'d data or the return value of the controller action.
     * @deprecated
     */
    public function fetchDispatch($module = null, $actionName = null, $parameters = null)
    {
        ob_start();
        $output = $this->dispatch($module, $actionName, $parameters);
        // if nothing returned we try to load something that was printed on the screen
        if (empty($output)) {
            $output = ob_get_contents();
        } else {
            // if something was returned, flush output buffer as it is meant to be written to the screen
            ob_flush();
        }
        ob_end_clean();
        return $output;
    }

    /**
     * Called at the end of the page generation
     */
    public function __destruct()
    {
        try {
            if (class_exists('Piwik\\Profiler')
                && !SettingsServer::isTrackerApiRequest()
            ) {
                // in tracker mode Piwik\Tracker\Db\Pdo\Mysql does currently not implement profiling
                Profiler::displayDbProfileReport();
                Profiler::printQueryCount();
            }
        } catch (Exception $e) {
            Log::debug($e);
        }
    }

    // Should we show exceptions messages directly rather than display an html error page?
    public static function shouldRethrowException()
    {
        // If we are in no dispatch mode, eg. a script reusing Piwik libs,
        // then we should return the exception directly, rather than trigger the event "bad config file"
        // which load the HTML page of the installer with the error.
        return (defined('PIWIK_ENABLE_DISPATCH') && !PIWIK_ENABLE_DISPATCH)
        || Common::isPhpCliMode()
        || SettingsServer::isArchivePhpTriggered();
    }

    public static function setUpSafeMode()
    {
        register_shutdown_function(array('\\Piwik\\FrontController', 'triggerSafeModeWhenError'));
    }

    public static function triggerSafeModeWhenError()
    {
        $lastError = error_get_last();
        if (!empty($lastError) && $lastError['type'] == E_ERROR) {
            Common::sendResponseCode(500);

            $controller = FrontController::getInstance();
            $controller->init();
            $message = $controller->dispatch('CorePluginsAdmin', 'safemode', array($lastError));

            echo $message;
        }
    }

    /**
     * Must be called before dispatch()
     * - checks that directories are writable,
     * - loads the configuration file,
     * - loads the plugin,
     * - inits the DB connection,
     * - etc.
     *
     * @throws Exception
     * @return void
     */
    public function init()
    {
        static $initialized = false;
        if ($initialized) {
            return;
        }
        $initialized = true;

        $tmpPath = StaticContainer::get('path.tmp');

        $directoriesToCheck = array(
            $tmpPath,
            $tmpPath . '/assets/',
            $tmpPath . '/cache/',
            $tmpPath . '/logs/',
            $tmpPath . '/tcpdf/',
            $tmpPath . '/templates_c/',
        );

        Filechecks::dieIfDirectoriesNotWritable($directoriesToCheck);

        $this->handleMaintenanceMode();
        $this->handleProfiler();
        $this->handleSSLRedirection();

        Plugin\Manager::getInstance()->loadPluginTranslations();
        Plugin\Manager::getInstance()->loadActivatedPlugins();

        // try to connect to the database
        try {
            Db::createDatabaseObject();
            Db::fetchAll("SELECT DATABASE()");
        } catch (Exception $exception) {
            if (self::shouldRethrowException()) {
                throw $exception;
            }

            Log::debug($exception);

            /**
             * Triggered when Piwik cannot connect to the database.
             *
             * This event can be used to start the installation process or to display a custom error
             * message.
             *
             * @param Exception $exception The exception thrown from creating and testing the database
             *                             connection.
             */
            Piwik::postEvent('Db.cannotConnectToDb', array($exception), $pending = true);

            throw $exception;
        }

        // try to get an option (to check if data can be queried)
        try {
            Option::get('TestingIfDatabaseConnectionWorked');
        } catch (Exception $exception) {
            if (self::shouldRethrowException()) {
                throw $exception;
            }

            Log::debug($exception);

            /**
             * Triggered when Piwik cannot access database data.
             *
             * This event can be used to start the installation process or to display a custom error
             * message.
             *
             * @param Exception $exception The exception thrown from trying to get an option value.
             */
            Piwik::postEvent('Config.badConfigurationFile', array($exception), $pending = true);

            throw $exception;
        }

        // Init the Access object, so that eg. core/Updates/* can enforce Super User and use some APIs
        Access::getInstance();

        /**
         * Triggered just after the platform is initialized and plugins are loaded.
         *
         * This event can be used to do early initialization.
         *
         * _Note: At this point the user is not authenticated yet._
         */
        Piwik::postEvent('Request.dispatchCoreAndPluginUpdatesScreen');

        $this->throwIfPiwikVersionIsOlderThanDBSchema();

        \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

        // ensure the current Piwik URL is known for later use
        if (method_exists('Piwik\SettingsPiwik', 'getPiwikUrl')) {
            SettingsPiwik::getPiwikUrl();
        }

        /**
         * Triggered before the user is authenticated, when the global authentication object
         * should be created.
         *
         * Plugins that provide their own authentication implementation should use this event
         * to set the global authentication object (which must derive from {@link Piwik\Auth}).
         *
         * **Example**
         *
         *     Piwik::addAction('Request.initAuthenticationObject', function() {
         *         StaticContainer::getContainer()->set('Piwik\Auth', new MyAuthImplementation());
         *     });
         */
        Piwik::postEvent('Request.initAuthenticationObject');
        try {
            $authAdapter = StaticContainer::get('Piwik\Auth');
        } catch (Exception $e) {
            $message = "Authentication object cannot be found in the container. Maybe the Login plugin is not activated?
                        <br />You can activate the plugin by adding:<br />
                        <code>Plugins[] = Login</code><br />
                        under the <code>[Plugins]</code> section in your config/config.ini.php";

            $ex = new AuthenticationFailedException($message);
            $ex->setIsHtmlMessage();

            throw $ex;
        }
        Access::getInstance()->reloadAccess($authAdapter);

        // Force the auth to use the token_auth if specified, so that embed dashboard
        // and all other non widgetized controller methods works fine
        if (Common::getRequestVar('token_auth', false, 'string') !== false) {
            Request::reloadAuthUsingTokenAuth();
        }
        SettingsServer::raiseMemoryLimitIfNecessary();

        \Piwik\Plugin\Manager::getInstance()->postLoadPlugins();

        /**
         * Triggered after the platform is initialized and after the user has been authenticated, but
         * before the platform has handled the request.
         *
         * Piwik uses this event to check for updates to Piwik.
         */
        Piwik::postEvent('Platform.initialized');
    }

    protected function prepareDispatch($module, $action, $parameters)
    {
        if (is_null($module)) {
            $module = Common::getRequestVar('module', self::DEFAULT_MODULE, 'string');
        }

        if (is_null($action)) {
            $action = Common::getRequestVar('action', false);
        }

        if (SettingsPiwik::isPiwikInstalled()
            && ($module !== 'API' || ($action && $action !== 'index'))
        ) {
            Session::start();

            $this->closeSessionEarlyForFasterUI();
        }

        if (is_null($parameters)) {
            $parameters = array();
        }

        if (!ctype_alnum($module)) {
            throw new Exception("Invalid module name '$module'");
        }

        list($module, $action) = Request::getRenamedModuleAndAction($module, $action);

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated($module)) {
            throw new PluginDeactivatedException($module);
        }

        return array($module, $action, $parameters);
    }

    protected function handleMaintenanceMode()
    {
        if ((Config::getInstance()->General['maintenance_mode'] != 1) || Common::isPhpCliMode()) {
            return;
        }
        Common::sendResponseCode(503);

        $logoUrl = null;
        $faviconUrl = null;
        try {
            $logo = new CustomLogo();
            $logoUrl = $logo->getHeaderLogoUrl();
            $faviconUrl = $logo->getPathUserFavicon();
        } catch (Exception $ex) {
        }
        $logoUrl = $logoUrl ?: 'plugins/Morpheus/images/logo-header.png';
        $faviconUrl = $faviconUrl ?: 'plugins/CoreHome/images/favicon.ico';

        $page = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/maintenance.tpl');
        $page = str_replace('%logoUrl%', $logoUrl, $page);
        $page = str_replace('%faviconUrl%', $faviconUrl, $page);
        $page = str_replace('%piwikTitle%', Piwik::getRandomTitle(), $page);
        echo $page;
        exit;
    }

    protected function handleSSLRedirection()
    {
        // Specifically disable for the opt out iframe
        if (Piwik::getModule() == 'CoreAdminHome' && Piwik::getAction() == 'optOut') {
            return;
        }
        // Disable Https for VisitorGenerator
        if (Piwik::getModule() == 'VisitorGenerator') {
            return;
        }
        if (Common::isPhpCliMode()) {
            return;
        }
        // Only enable this feature after Piwik is already installed
        if (!SettingsPiwik::isPiwikInstalled()) {
            return;
        }
        // proceed only when force_ssl = 1
        if (!SettingsPiwik::isHttpsForced()) {
            return;
        }
        Url::redirectToHttps();
    }

    private function closeSessionEarlyForFasterUI()
    {
        $isDashboardReferrer = !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'module=CoreHome&action=index') !== false;
        $isAllWebsitesReferrer = !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'module=MultiSites&action=index') !== false;

        if ($isDashboardReferrer
            && !empty($_POST['token_auth'])
            && Common::getRequestVar('widget', 0, 'int') === 1
        ) {
            Session::close();
        }

        if (($isDashboardReferrer || $isAllWebsitesReferrer)
            && Common::getRequestVar('viewDataTable', '', 'string') === 'sparkline'
        ) {
            Session::close();
        }
    }

    private function handleProfiler()
    {
        if (!empty($_GET['xhprof'])) {
            $mainRun = $_GET['xhprof'] == 1; // core:archive command sets xhprof=2
            Profiler::setupProfilerXHProf($mainRun);
        }
    }

    /**
     * @param $module
     * @param $action
     * @param $parameters
     * @return mixed
     */
    private function doDispatch($module, $action, $parameters)
    {
        list($module, $action, $parameters) = $this->prepareDispatch($module, $action, $parameters);

        /**
         * Triggered directly before controller actions are dispatched.
         *
         * This event can be used to modify the parameters passed to one or more controller actions
         * and can be used to change the controller action being dispatched to.
         *
         * @param string &$module The name of the plugin being dispatched to.
         * @param string &$action The name of the controller method being dispatched to.
         * @param array &$parameters The arguments passed to the controller action.
         */
        Piwik::postEvent('Request.dispatch', array(&$module, &$action, &$parameters));

        /** @var ControllerResolver $controllerResolver */
        $controllerResolver = StaticContainer::get('Piwik\Http\ControllerResolver');

        $controller = $controllerResolver->getController($module, $action, $parameters);

        /**
         * Triggered directly before controller actions are dispatched.
         *
         * This event exists for convenience and is triggered directly after the {@hook Request.dispatch}
         * event is triggered.
         *
         * It can be used to do the same things as the {@hook Request.dispatch} event, but for one controller
         * action only. Using this event will result in a little less code than {@hook Request.dispatch}.
         *
         * @param array &$parameters The arguments passed to the controller action.
         */
        Piwik::postEvent(sprintf('Controller.%s.%s', $module, $action), array(&$parameters));

        $result = call_user_func_array($controller, $parameters);

        /**
         * Triggered after a controller action is successfully called.
         *
         * This event exists for convenience and is triggered immediately before the {@hook Request.dispatch.end}
         * event is triggered.
         *
         * It can be used to do the same things as the {@hook Request.dispatch.end} event, but for one
         * controller action only. Using this event will result in a little less code than
         * {@hook Request.dispatch.end}.
         *
         * @param mixed &$result The result of the controller action.
         * @param array $parameters The arguments passed to the controller action.
         */
        Piwik::postEvent(sprintf('Controller.%s.%s.end', $module, $action), array(&$result, $parameters));

        /**
         * Triggered after a controller action is successfully called.
         *
         * This event can be used to modify controller action output (if any) before the output is returned.
         *
         * @param mixed &$result The controller action result.
         * @param array $parameters The arguments passed to the controller action.
         */
        Piwik::postEvent('Request.dispatch.end', array(&$result, $module, $action, $parameters));

        return $result;
    }

    /**
     * This method ensures that Piwik Platform cannot be running when using a NEWER database.
     */
    private function throwIfPiwikVersionIsOlderThanDBSchema()
    {
        // When developing this situation happens often when switching branches
        if (Development::isEnabled()) {
            return;
        }

        $updater = new Updater();

        $dbSchemaVersion = $updater->getCurrentComponentVersion('core');
        $current = Version::VERSION;
        if (-1 === version_compare($current, $dbSchemaVersion)) {
            $messages = array(
                Piwik::translate('General_ExceptionDatabaseVersionNewerThanCodebase', array($current, $dbSchemaVersion)),
                Piwik::translate('General_ExceptionDatabaseVersionNewerThanCodebaseWait'),
                // we cannot fill in the Super User emails as we are failing before Authentication was ready
                Piwik::translate('General_ExceptionContactSupportGeneric', array('', ''))
            );
            throw new DatabaseSchemaIsNewerThanCodebaseException(implode(" ", $messages));
        }
    }
}
