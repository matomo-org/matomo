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
use Piwik\Log;
use Piwik\Session;
use Zend_Registry;

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
class FrontController
{
    /**
     * Set to false and the Front Controller will not dispatch the request
     *
     * @var bool
     */
    public static $enableDispatch = true;

    private static $instance = null;

    /**
     * returns singleton
     *
     * @return \Piwik\FrontController
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
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

        if (!PluginsManager::getInstance()->isPluginActivated($module)) {
            throw new PluginDeactivatedException($module);
        }

        $controllerClassName = $this->getClassNameController( $module );

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

        // Generic hook that plugins can use to modify any input to the function,
        // or even change the plugin being called
        $params = array($controller, $action, $parameters);
        Piwik_PostEvent('FrontController.dispatch', $params);

        try {
            return call_user_func_array(array($params[0], $params[1]), $params[2]);
        } catch (NoAccessException $e) {
            Piwik_PostEvent('FrontController.NoAccessException', array($e), $pending = true);
        } catch (Exception $e) {
            $debugTrace = $e->getTraceAsString();
            $message = Common::sanitizeInputValue($e->getMessage());
            Piwik_ExitWithMessage($message, $debugTrace, true);
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
                Piwik::log(Zend_Registry::get('timer'));
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
        } catch (Exception $e) {
            Piwik_PostEvent('FrontController.NoConfigurationFile', array($e), $pending = true);
            $exceptionToThrow = $e;
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
            \Zend_Registry::set('timer', new Timer);

            $directoriesToCheck = array(
                '/tmp/',
                '/tmp/templates_c/',
                '/tmp/cache/',
                '/tmp/assets/',
                '/tmp/tcpdf/'
            );

            Filechecks::dieIfDirectoriesNotWritable($directoriesToCheck);
            self::assignCliParametersToRequest();

            Translate::getInstance()->loadEnglishTranslation();

            $exceptionToThrow = self::createConfigObject();

            if (Session::isFileBasedSessions()) {
                Session::start();
            }

            $this->handleMaintenanceMode();
            $this->handleSSLRedirection();

            $pluginsManager = PluginsManager::getInstance();
            $pluginsToLoad = Config::getInstance()->Plugins['Plugins'];

            $pluginsManager->loadPlugins($pluginsToLoad);

            if ($exceptionToThrow) {
                throw $exceptionToThrow;
            }

            try {
                DbHelper::createDatabaseObject();
            } catch (Exception $e) {
                if (self::shouldRethrowException()) {
                    throw $e;
                }
                Piwik_PostEvent('FrontController.badConfigurationFile', array($e), $pending = true);
                throw $e;
            }

            // Init the Access object, so that eg. core/Updates/* can enforce Super User and use some APIs
            Access::getInstance();

            Piwik_PostEvent('FrontController.dispatchCoreAndPluginUpdatesScreen');

            PluginsManager::getInstance()->installLoadedPlugins();

            // ensure the current Piwik URL is known for later use
            if (method_exists('Piwik\SettingsPiwik', 'getPiwikUrl')) {
                $host = SettingsPiwik::getPiwikUrl();
            }

            Piwik_PostEvent('FrontController.initAuthenticationObject');
            try {
                $authAdapter = \Zend_Registry::get('auth');
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

            Translate::getInstance()->reloadLanguage();
            $pluginsManager->postLoadPlugins();

            Piwik_PostEvent('FrontController.checkForUpdates');
        } catch (Exception $e) {

            if (self::shouldRethrowException()) {
                throw $e;
            }

            $trace = $e->getTraceAsString();
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
