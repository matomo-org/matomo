<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Exception;
use Piwik\API\Request;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Manager;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Exception\DatabaseSchemaIsNewerThanCodebaseException;
use Piwik\Exception\PluginDeactivatedException;
use Piwik\Exception\PluginRequiresInternetException;
use Piwik\Exception\StylesheetLessCompileException;
use Piwik\Http\ControllerResolver;
use Piwik\Http\Router;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Session\SessionAuth;
use Piwik\Session\SessionInitializer;
use Piwik\SupportedBrowser;
use Psr\Log\LoggerInterface;

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
 *         $realtimeMap = FrontController::getInstance()->dispatch('UserCountryMap', 'realtimeMap');
 *
 *         $view = new View('@MyPlugin/myPopupWithRealtimeMap.twig');
 *         $view->realtimeMap = $realtimeMap;
 *         return $realtimeMap->render();
 *     }
 *
 * For a detailed explanation, see the documentation [here](https://developer.piwik.org/guides/how-piwik-works).
 *
 * @method static \Piwik\FrontController getInstance()
 */
class FrontController extends Singleton
{
    const DEFAULT_MODULE = 'CoreHome';
    const DEFAULT_LOGIN = 'anonymous';
    const DEFAULT_TOKEN_AUTH = 'anonymous';

    // public for tests
    public static $requestId = null;

    /**
     * Set to false and the Front Controller will not dispatch the request
     *
     * @var bool
     */
    public static $enableDispatch = true;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @param $lastError
     * @return string
     * @throws AuthenticationFailedException
     * @throws Exception
     */
    private static function generateSafeModeOutputFromError($lastError)
    {
        Common::sendResponseCode(500);

        $controller = FrontController::getInstance();
        try {
            $controller->init();
            $message = $controller->dispatch('CorePluginsAdmin', 'safemode', array($lastError));
        } catch(Exception $e) {
            // may fail in safe mode (eg. global.ini.php not found)
            $message = sprintf("Matomo encountered an error: %s (which lead to: %s)", $lastError['message'], $e->getMessage());
        }

        return $message;
    }

    /**
     * @param Exception $e
     * @return string
     */
    public static function generateSafeModeOutputFromException($e)
    {
        StaticContainer::get(LoggerInterface::class)->error('Uncaught exception: {exception}', [
            'exception' => $e,
            'ignoreInScreenWriter' => true,
        ]);

        $error = array(
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        );

        if (isset(self::$requestId)) {
            $error['request_id'] = self::$requestId;
        }

        $error['backtrace'] = ' on ' . $error['file'] . '(' . $error['line'] . ")\n";
        $error['backtrace'] .= $e->getTraceAsString();

        $exception = $e;
        while ($exception = $exception->getPrevious()) {
            $error['backtrace'] .= "\ncaused by: " . $exception->getMessage();
            $error['backtrace'] .= ' on ' . $exception->getFile() . '(' . $exception->getLine() . ")\n";
            $error['backtrace'] .= $exception->getTraceAsString();
        }

        return self::generateSafeModeOutputFromError($error);
    }

    /**
     * Executes the requested plugin controller method.
     *
     * @throws Exception|\Piwik\Exception\PluginDeactivatedException in case the plugin doesn't exist, the action doesn't exist,
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
        } catch (\Twig\Error\RuntimeError $e) {
            if ($e->getPrevious() && !$e->getPrevious() instanceof \Twig\Error\RuntimeError) {
                // a regular exception unrelated to twig was triggered while rendering an a view, for example as part of a triggered event
                // we want to ensure to show the regular error message response instead of the safemode as it's likely wrong user input
                throw $e;
            } else {
                echo $this->generateSafeModeOutputFromException($e);
                exit;
            }
        } catch(StylesheetLessCompileException $e) {
            echo $this->generateSafeModeOutputFromException($e);
            exit;
        } catch(\Error $e) {
            echo $this->generateSafeModeOutputFromException($e);
            exit;
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
        Manager::getInstance()->deleteAll();

        $lastError = error_get_last();

        if (!empty($lastError) && isset(self::$requestId)) {
            $lastError['request_id'] = self::$requestId;
        }

        if (!empty($lastError) && $lastError['type'] == E_ERROR) {
            $lastError['backtrace'] = ' on ' . $lastError['file'] . '(' . $lastError['line'] . ")\n"
                . ErrorHandler::getFatalErrorPartialBacktrace();

            StaticContainer::get(LoggerInterface::class)->error('Fatal error encountered: {exception}', [
                'exception' => $lastError,
                'ignoreInScreenWriter' => true,
            ]);

            $message = self::generateSafeModeOutputFromError($lastError);
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
        if ($this->initialized) {
            return;
        }

        self::setRequestIdHeader();

        $this->initialized = true;

        $tmpPath = StaticContainer::get('path.tmp');

        $directoriesToCheck = array(
            $tmpPath,
            $tmpPath . '/assets/',
            $tmpPath . '/cache/',
            $tmpPath . '/logs/',
            $tmpPath . '/tcpdf/',
            StaticContainer::get('path.tmp.templates'),
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

        $module = Piwik::getModule();
        $action = Piwik::getAction();

        if (empty($module)
            || empty($action)
            || $module !== 'Installation'
            || !in_array($action, array('getInstallationCss', 'getInstallationJs'))) {
            \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();
        }

        // ensure the current Piwik URL is known for later use
        if (method_exists('Piwik\SettingsPiwik', 'getPiwikUrl')) {
            SettingsPiwik::getPiwikUrl();
        }

        $loggedIn = false;

        //move this up unsupported Browser do not create session
        if ($this->isSupportedBrowserCheckNeeded()) {
            SupportedBrowser::checkIfBrowserSupported();
        }

        // don't use sessionauth in cli mode
        // try authenticating w/ session first...
        $sessionAuth = $this->makeSessionAuthenticator();
        if ($sessionAuth) {
            $loggedIn = Access::getInstance()->reloadAccess($sessionAuth);
        }

        // ... if session auth fails try normal auth (which will login the anonymous user)
        if (!$loggedIn) {
            $authAdapter = $this->makeAuthenticator();
            $success = Access::getInstance()->reloadAccess($authAdapter);

            if ($success
                && Piwik::isUserIsAnonymous()
                && $authAdapter->getLogin() === 'anonymous' //double checking the login
                && Piwik::isUserHasSomeViewAccess()
                && Session::isSessionStarted()
                && Session::isWritable()) { // only if session was started and writable, don't do it eg for API
                // usually the session would be started when someone logs in using login controller. But in this
                // case we need to init session here for anoynymous users
                $init = StaticContainer::get(SessionInitializer::class);
                $init->initSession($authAdapter);
            }
        } else {
            $this->makeAuthenticator($sessionAuth); // Piwik\Auth must be set to the correct Login plugin
        }



        // Force the auth to use the token_auth if specified, so that embed dashboard
        // and all other non widgetized controller methods works fine
        if (Common::getRequestVar('token_auth', '', 'string') !== ''
            && Request::shouldReloadAuthUsingTokenAuth(null)
        ) {
            Request::reloadAuthUsingTokenAuth();
            Request::checkTokenAuthIsNotLimited($module, $action);
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
            if ($action !== false) {
                // If a value was provided, check it has the correct type.
                $action = Common::getRequestVar('action', null, 'string');
            }
        }

        if (Session::isSessionStarted()) {
            $this->closeSessionEarlyForFasterUI();
        }

        if (is_null($parameters)) {
            $parameters = array();
        }

        if (!ctype_alnum($module)) {
            throw new Exception("Invalid module name '$module'");
        }

        list($module, $action) = Request::getRenamedModuleAndAction($module, $action);

        if (!SettingsPiwik::isInternetEnabled() && \Piwik\Plugin\Manager::getInstance()->doesPluginRequireInternetConnection($module)) {
            throw new PluginRequiresInternetException($module);
        }

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated($module)) {
            throw new PluginDeactivatedException($module);
        }

        return array($module, $action, $parameters);
    }

    protected function handleMaintenanceMode()
    {
        if ((GeneralConfig::getConfigValue('maintenance_mode') != 1) || Common::isPhpCliMode() ) {
            return;
        }

        // as request matomo behind load balancer should not return 503. https://github.com/matomo-org/matomo/issues/18054
        if (GeneralConfig::getConfigValue('multi_server_environment') != 1) {
            Common::sendResponseCode(503);
        }

        $logoUrl = 'plugins/Morpheus/images/logo.svg';
        $faviconUrl = 'plugins/CoreHome/images/favicon.png';
        try {
            $logo = new CustomLogo();
            if ($logo->hasSVGLogo()) {
                $logoUrl = $logo->getSVGLogoUrl();
            } else {
                $logoUrl = $logo->getHeaderLogoUrl();
            }
            $faviconUrl = $logo->getPathUserFavicon();
        } catch (Exception $ex) {
        }

        $recordStatistics = Config::getInstance()->Tracker['record_statistics'];
        $trackMessage = '';

        if ($recordStatistics) {
          $trackMessage = 'Your analytics data will continue to be tracked as normal.';
        } else {
          $trackMessage = 'While the maintenance mode is active, data tracking is disabled.';
        }

        $page = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/maintenance.tpl');
        $page = str_replace('%logoUrl%', $logoUrl, $page);
        $page = str_replace('%faviconUrl%', $faviconUrl, $page);
        $page = str_replace('%piwikTitle%', Piwik::getRandomTitle(), $page);

        $page = str_replace('%trackMessage%', $trackMessage, $page);

        echo $page;
        exit;
    }

    protected function handleSSLRedirection()
    {
        // Specifically disable for the opt out iframe
        if (Piwik::getModule() == 'CoreAdminHome' && (Piwik::getAction() == 'optOut' || Piwik::getAction() == 'optOutJS')) {
            return;
        }
        // Disable Https for VisitorGenerator
        if (Piwik::getModule() == 'VisitorGenerator') {
            return;
        }
        if (Common::isPhpCliMode()) {
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
        $profilerEnabled = Config::getInstance()->Debug['enable_php_profiler'] == 1;
        if (!$profilerEnabled) {
            return;
        }

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

        if (!StaticContainer::get('EnableDbVersionCheck')) {
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

    private function makeSessionAuthenticator()
    {
        if (Common::isPhpClimode()
            && !defined('PIWIK_TEST_MODE')
        ) { // don't use the session auth during CLI requests
            return null;
        }

        if (Common::getRequestVar('token_auth', '', 'string') !== '' && !Common::getRequestVar('force_api_session', 0)) {
             return null;
         }

        $module = Common::getRequestVar('module', self::DEFAULT_MODULE, 'string');
        $action = Common::getRequestVar('action', false);

        // the session must be started before using the session authenticator,
        // so we do it here, if this is not an API request.
        if (SettingsPiwik::isMatomoInstalled()
            && ($module !== 'API' || ($action && $action !== 'index'))
            && !($module === 'CoreAdminHome' && $action === 'optOutJS')
        ) {
            /**
             * @ignore
             */
            Piwik::postEvent('Session.beforeSessionStart');

            Session::start();
            return StaticContainer::get(SessionAuth::class);
        }

        return null;
    }

    private function makeAuthenticator(SessionAuth $auth = null)
    {
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

        if ($auth) {
            $authAdapter->setLogin($auth->getLogin());
            $authAdapter->setTokenAuth($auth->getTokenAuth());
        } else {
            $authAdapter->setLogin(self::DEFAULT_LOGIN);
            $authAdapter->setTokenAuth(self::DEFAULT_TOKEN_AUTH);
        }

        return $authAdapter;
    }

    public static function getUniqueRequestId()
    {
        if (self::$requestId === null) {
            self::$requestId = substr(Common::generateUniqId(), 0, 5);
        }
        return self::$requestId;
    }

    private static function setRequestIdHeader()
    {
        $requestId = self::getUniqueRequestId();
        Common::sendHeader("X-Matomo-Request-Id: $requestId");
    }

    private function isSupportedBrowserCheckNeeded()
    {
        if (defined('PIWIK_ENABLE_DISPATCH') && !PIWIK_ENABLE_DISPATCH) {
            return false;
        }

        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if ($userAgent === '') {
            return false;
        }

        $isTestMode = defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE;
        if (!$isTestMode && Common::isPhpCliMode() === true) {
            return false;
        }

        if (Piwik::getModule() === 'API' && (empty(Piwik::getAction()) || Piwik::getAction() === 'index' || Piwik::getAction() === 'glossary')) {
            return false;
        }

        if (Piwik::getModule() === 'Widgetize') {
            return true;
        }

        $generalConfig = Config::getInstance()->General;
        if ($generalConfig['enable_framed_pages'] == '1' || $generalConfig['enable_framed_settings'] == '1') {
            return true;
        }

        if (Common::getRequestVar('token_auth', '', 'string') !== '') {
            return true;
        }

        if (Piwik::isUserIsAnonymous()) {
            return true;
        }

        return false;
    }
}
