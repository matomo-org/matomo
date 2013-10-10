<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik;

use Exception;
use Piwik\API\Request;
use Piwik\API\ResponseBuilder;
use Piwik\Plugin\Controller;
use Piwik\Session;

/**
 * Front controller.
 * This is the class hit in the first place.
 * It dispatches the request to the right controller.
 *
 * For a detailed explanation, see the documentation on http://piwik.org/docs/plugins/framework-overview
 *
 * @package Piwik
 * @subpackage FrontController
 */
class FrontController extends Singleton
{
    /**
     * Set to false and the Front Controller will not dispatch the request
     *
     * @var bool
     */
    public static $enableDispatch = true;

    protected function prepareDispatch($module, $action, $parameters)
    {
        if (is_null($module)) {
            $defaultModule = 'CoreHome';
            $module = Common::getRequestVar('module', $defaultModule, 'string');
        }

        if (is_null($action)) {
            $action = Common::getRequestVar('action', false);
        }

        if (!Session::isFileBasedSessions()
            && ($module !== 'API' || ($action && $action !== 'index'))
        ) {
            Session::start();
        }

        if (is_null($parameters)) {
            $parameters = array();
        }

        if (!ctype_alnum($module)) {
            throw new Exception("Invalid module name '$module'");
        }

        if (!\Piwik\Plugin\Manager::getInstance()->isPluginActivated($module)) {
            throw new PluginDeactivatedException($module);
        }

        $controllerClassName = $this->getClassNameController($module);

        // FrontController's autoloader
        if (!class_exists($controllerClassName, false)) {
            $moduleController = PIWIK_INCLUDE_PATH . '/plugins/' . $module . '/Controller.php';
            if (!is_readable($moduleController)) {
                throw new Exception("Module controller $moduleController not found!");
            }
            require_once $moduleController; // prefixed by PIWIK_INCLUDE_PATH
        }

        $class = $this->getClassNameController($module);
        /** @var $controller Controller */
        $controller = new $class;
        if ($action === false) {
            $action = $controller->getDefaultAction();
        }

        if (!is_callable(array($controller, $action))) {
            throw new Exception("Action '$action' not found in the controller '$controllerClassName'.");
        }
        return array($module, $action, $parameters, $controller);
    }

    /**
     * Dispatches the request to the right plugin and executes the requested action on the plugin controller.
     *
     * @throws Exception|\Piwik\PluginDeactivatedException in case the plugin doesn't exist, the action doesn't exist, there is not enough permission, etc.
     *
     * @param string $module
     * @param string $action
     * @param array $parameters
     * @return void|mixed  The returned value of the calls, often nothing as the module print but don't return data
     * @see fetchDispatch()
     */
    public function dispatch($module = null, $action = null, $parameters = null)
    {
        if (self::$enableDispatch === false) {
            return;
        }

        // list($module, $action, $parameters, $controller)
        $params = $this->prepareDispatch($module, $action, $parameters);

        /**
         * Generic hook that plugins can use to modify any input to the function, or even change the plugin being
         * called. You could also use this to build an enhanced permission system. The event is triggered before every
         * call to a controller method.
         *
         * The `$params` array contains the following properties: `array($module, $action, $parameters, $controller)`
         */
        Piwik::postEvent('Request.dispatch', $params);

        /**
         * This event is similar to the `Request.dispatch` hook. It distinguishes the possibility to subscribe only to a
         * specific controller method instead of all controller methods. You can use it for example to modify any input
         * parameters or execute any other logic before the actual controller is called.
         */
        Piwik::postEvent(sprintf('Controller.%s.%s', $module, $action), array($parameters));

        try {
            $result = call_user_func_array(array($params[3], $params[1]), $params[2]);

            /**
             * This event is similar to the `Request.dispatch.end` hook. It distinguishes the possibility to subscribe
             * only to the end of a specific controller method instead of all controller methods. You can use it for
             * example to modify the response of a single controller method.
             */
            Piwik::postEvent(sprintf('Controller.%s.%s.end', $module, $action), array(&$result, $parameters));

            /**
             * Generic hook that plugins can use to modify any output of any controller method. The event is triggered
             * after a controller method is executed but before the result is send to the user. The parameters
             * originally passed to the method are available as well.
             */
            Piwik::postEvent('Request.dispatch.end', array(&$result, $parameters));

            return $result;

        } catch (NoAccessException $exception) {

            /**
             * This event is triggered in case the user wants to access anything in the Piwik UI but has not the
             * required permissions to do this. You can subscribe to this event to display a custom error message or
             * to display a custom login form in such a case.
             */
            Piwik::postEvent('User.isNotAuthorized', array($exception), $pending = true);
        } catch (Exception $e) {
            $debugTrace = $e->getTraceAsString();
            $message = Common::sanitizeInputValue($e->getMessage());
            Piwik_ExitWithMessage($message, $debugTrace, true, true);
        }
    }

    protected function getClassNameController($module)
    {
        return "\\Piwik\\Plugins\\$module\\Controller";
    }

    /**
     * Often plugins controller display stuff using echo/print.
     * Using this function instead of dispatch() returns the output string form the actions calls.
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array $parameters
     * @return string
     */
    public function fetchDispatch($controllerName = null, $actionName = null, $parameters = null)
    {
        ob_start();
        $output = $this->dispatch($controllerName, $actionName, $parameters);
        // if nothing returned we try to load something that was printed on the screen
        if (empty($output)) {
            $output = ob_get_contents();
        }
        ob_end_clean();
        return $output;
    }

    /**
     * Called at the end of the page generation
     *
     */
    public function __destruct()
    {
        try {
            if (class_exists('Piwik\\Profiler')) {
                Profiler::displayDbProfileReport();
                Profiler::printQueryCount();
                Log::debug(Registry::get('timer'));
            }
        } catch (Exception $e) {
        }
    }

    // Should we show exceptions messages directly rather than display an html error page?
    public static function shouldRethrowException()
    {
        // If we are in no dispatch mode, eg. a script reusing Piwik libs,
        // then we should return the exception directly, rather than trigger the event "bad config file"
        // which load the HTML page of the installer with the error.
        // This is at least required for misc/cron/archive.php and useful to all other scripts
        return (defined('PIWIK_ENABLE_DISPATCH') && !PIWIK_ENABLE_DISPATCH)
        || Common::isPhpCliMode()
        || SettingsServer::isArchivePhpTriggered();
    }

    /**
     * Loads the config file and assign to the global registry
     * This is overridden in tests to ensure test config file is used
     *
     * @return Exception
     */
    static public function createConfigObject()
    {
        $exceptionToThrow = false;
        try {
            Config::getInstance();
        } catch (Exception $exception) {

            /**
             * This event is triggered in case no configuration file is available. This usually means Piwik is not
             * installed yet. The event can be used to start the installation process or to display a custom error
             * message.
             */
            Piwik::postEvent('Config.NoConfigurationFile', array($exception), $pending = true);
            $exceptionToThrow = $exception;
        }
        return $exceptionToThrow;
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

        try {
            Registry::set('timer', new Timer);

            $directoriesToCheck = array(
                '/tmp/',
                '/tmp/assets/',
                '/tmp/cache/',
                '/tmp/logs/',
                '/tmp/tcpdf/',
                '/tmp/templates_c/',
            );

            Filechecks::dieIfDirectoriesNotWritable($directoriesToCheck);
            self::assignCliParametersToRequest();

            Translate::loadEnglishTranslation();

            $exceptionToThrow = self::createConfigObject();

            if (Session::isFileBasedSessions()) {
                Session::start();
            }

            $this->handleMaintenanceMode();
            $this->handleSSLRedirection();

            $pluginsManager = \Piwik\Plugin\Manager::getInstance();
            $pluginsToLoad = Config::getInstance()->Plugins['Plugins'];

            $pluginsManager->loadPlugins($pluginsToLoad);

            if ($exceptionToThrow) {
                throw $exceptionToThrow;
            }

            try {
                Db::createDatabaseObject();
                Option::get('TestingIfDatabaseConnectionWorked');
            } catch (Exception $exception) {
                if (self::shouldRethrowException()) {
                    throw $exception;
        }

                /**
                 * This event is triggered in case a config file is not in the correct format or in case required values
                 * are missing. The event can be used to start the installation process or to display a custom error
                 * message.
                 */
                Piwik::postEvent('Config.badConfigurationFile', array($exception), $pending = true);
                throw $exception;
            }

            // Init the Access object, so that eg. core/Updates/* can enforce Super User and use some APIs
            Access::getInstance();

            /**
             * This event is the first event triggered just after the platform is initialized and plugins are loaded.
             * You can use this event to do early initialization. Note: the user is not authenticated yet.
             */
            Piwik::postEvent('Request.dispatchCoreAndPluginUpdatesScreen');

            \Piwik\Plugin\Manager::getInstance()->installLoadedPlugins();

            // ensure the current Piwik URL is known for later use
            if (method_exists('Piwik\SettingsPiwik', 'getPiwikUrl')) {
                $host = SettingsPiwik::getPiwikUrl();
            }

            /**
             * This event is triggered before the user is authenticated. You can use it to create your own
             * authentication object which implements the `Piwik\Auth` interface, and override the default authentication logic.
             */
            Piwik::postEvent('Request.initAuthenticationObject');
            try {
                $authAdapter = Registry::get('auth');
            } catch (Exception $e) {
                throw new Exception("Authentication object cannot be found in the Registry. Maybe the Login plugin is not activated?
                                <br />You can activate the plugin by adding:<br />
                                <code>Plugins[] = Login</code><br />
                                under the <code>[Plugins]</code> section in your config/config.ini.php");
            }
            Access::getInstance()->reloadAccess($authAdapter);

            // Force the auth to use the token_auth if specified, so that embed dashboard
            // and all other non widgetized controller methods works fine
            if (($token_auth = Common::getRequestVar('token_auth', false, 'string')) !== false) {
                Request::reloadAuthUsingTokenAuth();
            }
            SettingsServer::raiseMemoryLimitIfNecessary();

            Translate::reloadLanguage();
            $pluginsManager->postLoadPlugins();

            /**
             * This event is triggered to check for updates. It is triggered after the platform is initialized and after
             * the user is authenticated but before the platform is going to dispatch the actual request. You can use
             * it to check for any updates.
             */
            Piwik::postEvent('Updater.checkForUpdates');
        } catch (Exception $e) {

            if (self::shouldRethrowException()) {
                throw $e;
            }

            $debugTrace = $e->getTraceAsString();
            Piwik_ExitWithMessage($e->getMessage(), $debugTrace, true);
        }
    }

    protected function handleMaintenanceMode()
    {
        if (Config::getInstance()->General['maintenance_mode'] == 1
            && !Common::isPhpCliMode()
        ) {
            $format = Common::getRequestVar('format', '');

            $message = "Piwik is in scheduled maintenance. Please come back later."
                . " The administrator can disable maintenance by editing the file piwik/config/config.ini.php and removing the following: "
                . " maintenance_mode=1 ";
            if (Config::getInstance()->Tracker['record_statistics'] == 0) {
                $message .= ' and record_statistics=0';
            }

            $exception = new Exception($message);
            // extend explain how to re-enable
            // show error message when record stats = 0
            if (empty($format)) {
                throw $exception;
            }
            $response = new ResponseBuilder($format);
            echo $response->getResponseException($exception);
            exit;
        }
    }

    protected function handleSSLRedirection()
    {
        if (!Common::isPhpCliMode()
            && Config::getInstance()->General['force_ssl'] == 1
            && !ProxyHttp::isHttps()
            // Specifically disable for the opt out iframe
            && !(Common::getRequestVar('module', '') == 'CoreAdminHome'
                && Common::getRequestVar('action', '') == 'optOut')
        ) {
            $url = Url::getCurrentUrl();
            $url = str_replace("http://", "https://", $url);
            Url::redirectToUrl($url);
        }
    }

    /**
     * Assign CLI parameters as if they were REQUEST or GET parameters.
     * You can trigger Piwik from the command line by
     * # /usr/bin/php5 /path/to/piwik/index.php -- "module=API&method=Actions.getActions&idSite=1&period=day&date=previous8&format=php"
     */
    public static function assignCliParametersToRequest()
    {
        if (isset($_SERVER['argc'])
            && $_SERVER['argc'] > 0
        ) {
            for ($i = 1; $i < $_SERVER['argc']; $i++) {
                parse_str($_SERVER['argv'][$i], $tmp);
                $_GET = array_merge($_GET, $tmp);
            }
        }
    }
}


/**
 * Exception thrown when the requested plugin is not activated in the config file
 *
 * @package Piwik
 * @subpackage FrontController
 */
class PluginDeactivatedException extends Exception
{
    public function __construct($module)
    {
        parent::__construct("The plugin $module is not enabled. You can activate the plugin on Settings > Plugins page in Piwik.");
    }
}
