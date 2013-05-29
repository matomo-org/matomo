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

/**
 * Class used by the logging script piwik.php called by the javascript tag.
 * Handles the visitor & his/her actions on the website, saves the data in the DB,
 * saves information in the cookie, etc.
 *
 * We try to include as little files as possible (no dependency on 3rd party modules).
 *
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker
{
    protected $stateValid = self::STATE_NOTHING_TO_NOTICE;
    /**
     * @var Piwik_Tracker_Db
     */
    protected static $db = null;

    const STATE_NOTHING_TO_NOTICE = 1;
    const STATE_LOGGING_DISABLE = 10;
    const STATE_EMPTY_REQUEST = 11;
    const STATE_NOSCRIPT_REQUEST = 13;

    // We use hex ID that are 16 chars in length, ie. 64 bits IDs
    const LENGTH_HEX_ID_STRING = 16;
    const LENGTH_BINARY_ID = 8;

    // These are also hardcoded in the Javascript
    const MAX_CUSTOM_VARIABLES = 5;
    const MAX_LENGTH_CUSTOM_VARIABLE = 200;

    protected $authenticated = false;
    static protected $forcedDateTime = null;
    static protected $forcedIpString = null;
    static protected $forcedVisitorId = null;

    static protected $pluginsNotToLoad = array();
    static protected $pluginsToLoad = array();

    /**
     * The set of visits to track.
     *
     * @var array
     */
    private $requests = array();

    /**
     * The token auth supplied with a bulk visits POST.
     *
     * @var string
     */
    private $tokenAuth = null;

    /**
     * Whether we're currently using bulk tracking or not.
     *
     * @var bool
     */
    private $usingBulkTracking = false;

    /**
     * The number of requests that have been successfully logged.
     *
     * @var int
     */
    private $countOfLoggedRequests = 0;

    public function clear()
    {
        self::$forcedIpString = null;
        self::$forcedDateTime = null;
        self::$forcedVisitorId = null;
        $this->stateValid = self::STATE_NOTHING_TO_NOTICE;
        $this->authenticated = false;
    }

    public static function setForceIp($ipString)
    {
        self::$forcedIpString = $ipString;
    }

    public static function setForceDateTime($dateTime)
    {
        self::$forcedDateTime = $dateTime;
    }

    public static function setForceVisitorId($visitorId)
    {
        self::$forcedVisitorId = $visitorId;
    }

    public function getCurrentTimestamp()
    {
        if (!is_null(self::$forcedDateTime)) {
            return strtotime(self::$forcedDateTime);
        }
        return time();
    }

    /**
     * Do not load the specified plugins (used during testing, to disable Provider plugin)
     * @param array $plugins
     */
    static public function setPluginsNotToLoad($plugins)
    {
        self::$pluginsNotToLoad = $plugins;
    }

    /**
     * Get list of plugins to not load
     *
     * @return array
     */
    static public function getPluginsNotToLoad()
    {
        return self::$pluginsNotToLoad;
    }

    static public function getPluginsToLoad()
    {
        return self::$pluginsToLoad;
    }
    static public function setPluginsToLoad($plugins)
    {
        self::$pluginsToLoad = $plugins;
    }



    /**
     * Update Tracker config
     *
     * @param string $name  Setting name
     * @param mixed $value Value
     */
    static private function updateTrackerConfig($name, $value)
    {
        $section = Piwik_Config::getInstance()->Tracker;
        $section[$name] = $value;
        Piwik_Config::getInstance()->Tracker = $section;
    }

    protected function initRequests($args)
    {
        $rawData = file_get_contents("php://input");
        if (!empty($rawData)) {
            $this->usingBulkTracking = strpos($rawData, '"requests"') || strpos($rawData, "'requests'");
            if ($this->usingBulkTracking) {
                return $this->initBulkTrackingRequests($rawData);
            }
        }

        // Not using bulk tracking
        $this->requests = $args ? $args : (!empty($_GET) || !empty($_POST) ? array($_GET + $_POST) : array());
    }

    private function initBulkTrackingRequests($rawData)
    {
        // POST data can be array of string URLs or array of arrays w/ visit info
        $jsonData = Piwik_Common::json_decode($rawData, $assoc = true);

        if (isset($jsonData['requests'])) {
            $this->requests = $jsonData['requests'];
        }
        $this->tokenAuth = Piwik_Common::getRequestVar('token_auth', false, null, $jsonData);
        if (empty($this->tokenAuth)) {
            throw new Exception(" token_auth must be specified when using Bulk Tracking Import. See <a href='http://piwik.org/docs/tracking-api/reference/'>Tracking Doc</a>");
        }
        if (!empty($this->requests)) {
            $idSiteForAuthentication = 0;

            foreach ($this->requests as &$request) {
                // if a string is sent, we assume its a URL and try to parse it
                if (is_string($request)) {
                    $params = array();

                    $url = @parse_url($request);
                    if (!empty($url)) {
                        @parse_str($url['query'], $params);
                        $request = $params;
                        if (isset($request['idsite']) && !$idSiteForAuthentication) {
                            $idSiteForAuthentication = $request['idsite'];
                        }
                    }
                }
            }

            // a Bulk Tracking request that is not authenticated should fail
            if (!$this->authenticateSuperUserOrAdmin(array('idsite' => $idSiteForAuthentication))) {
                throw new Exception(" token_auth specified is not valid for site " . intval($idSiteForAuthentication));
            }
        }
    }

    /**
     * Main - tracks the visit/action
     *
     * @param array $args Optional Request Array
     */
    public function main($args = null)
    {
        $displayedGIF = false;
        $this->initRequests($args);
        if (!empty($this->requests)) {
            // handle all visits
            foreach ($this->requests as $request) {
                $this->init($request);

                if (!$displayedGIF && !$this->authenticated) {
                    $this->outputTransparentGif();
                    $displayedGIF = true;
                }

                try {
                    if ($this->isVisitValid()) {
                        self::connectDatabaseIfNotConnected();

                        $visit = $this->getNewVisitObject();
                        $visit->setRequest($request);
                        $visit->handle();
                        unset($visit);
                    } else {
                        printDebug("The request is invalid: empty request, or maybe tracking is disabled in the config.ini.php via record_statistics=0");
                    }
                } catch (Piwik_Tracker_Db_Exception $e) {
                    printDebug("<b>" . $e->getMessage() . "</b>");
                    $this->exitWithException($e, $this->authenticated);
                } catch (Piwik_Tracker_Visit_Excluded $e) {
                } catch (Exception $e) {
                    $this->exitWithException($e, $this->authenticated);
                }
                $this->clear();

                // increment successfully logged request count. make sure to do this after try-catch,
                // since an excluded visit is considered 'successfully logged'
                ++$this->countOfLoggedRequests;
            }

            if (!$displayedGIF) {
                $this->outputTransparentGif();
                $displayedGIF = true;
            }
        } else {
            $this->handleEmptyRequest($_GET + $_POST);
        }

        // run scheduled task
        try {
            if ($this->shouldRunScheduledTasks()) {
                self::runScheduledTasks($now = $this->getCurrentTimestamp());
            }
        } catch (Exception $e) {
            $this->exitWithException($e, $this->authenticated);
        }

        $this->end();
    }

    protected function shouldRunScheduledTasks()
    {
        // don't run scheduled tasks in CLI mode from Tracker, this is the case
        // where we bulk load logs & don't want to lose time with tasks
        return !Piwik_Common::isPhpCliMode()
            && !$this->authenticated
            && $this->getState() != self::STATE_LOGGING_DISABLE;
    }

    /**
     * Tracker requests will automatically trigger the Scheduled tasks.
     * This is useful for users who don't setup the cron,
     * but still want daily/weekly/monthly PDF reports emailed automatically.
     *
     * This is similar to calling the API CoreAdminHome.runScheduledTasks (see misc/cron/archive.php)
     *
     * @param int $now  Current timestamp
     */
    protected static function runScheduledTasks($now)
    {
        // Currently, there is no hourly tasks. When there are some,
        // this could be too agressive minimum interval (some hours would be skipped in case of low traffic)
        $minimumInterval = Piwik_Config::getInstance()->Tracker['scheduled_tasks_min_interval'];

        // If the user disabled browser archiving, he has already setup a cron
        // To avoid parallel requests triggering the Scheduled Tasks,
        // Get last time tasks started executing
        $cache = Piwik_Tracker_Cache::getCacheGeneral();
        if ($minimumInterval <= 0
            || empty($cache['isBrowserTriggerArchivingEnabled'])
        ) {
            printDebug("-> Scheduled tasks not running in Tracker: Browser archiving is disabled.");
            return;
        }
        $nextRunTime = $cache['lastTrackerCronRun'] + $minimumInterval;
        if ((isset($GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS']) && $GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS'])
            || $cache['lastTrackerCronRun'] === false
            || $nextRunTime < $now
        ) {
            $cache['lastTrackerCronRun'] = $now;
            Piwik_Tracker_Cache::setCacheGeneral($cache);
            self::initCorePiwikInTrackerMode();
            Piwik_SetOption('lastTrackerCronRun', $cache['lastTrackerCronRun']);
            printDebug('-> Scheduled Tasks: Starting...');

            // save current user privilege and temporarily assume super user privilege
            $isSuperUser = Piwik::isUserIsSuperUser();

            // Scheduled tasks assume Super User is running
            Piwik::setUserIsSuperUser();

            // While each plugins should ensure that necessary languages are loaded,
            // we ensure English translations at least are loaded
            Piwik_Translate::getInstance()->loadEnglishTranslation();

            $resultTasks = Piwik_TaskScheduler::runTasks();

            // restore original user privilege
            Piwik::setUserIsSuperUser($isSuperUser);

            printDebug($resultTasks);
            printDebug('Finished Scheduled Tasks.');
        } else {
            printDebug("-> Scheduled tasks not triggered.");
        }
        printDebug("Next run will be from: " . date('Y-m-d H:i:s', $nextRunTime) . ' UTC');
    }

    static public $initTrackerMode = false;

    /*
     * Used to initialize core Piwik components on a piwik.php request
     * Eg. when cache is missed and we will be calling some APIs to generate cache
     */
    static public function initCorePiwikInTrackerMode()
    {
        if (!empty($GLOBALS['PIWIK_TRACKER_MODE'])
            && self::$initTrackerMode === false
        ) {
            self::$initTrackerMode = true;
            require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
            require_once PIWIK_INCLUDE_PATH . '/core/Option.php';
            try {
                $access = Zend_Registry::get('access');
            } catch (Exception $e) {
                Piwik::createAccessObject();
            }
            try {
                $config = Piwik_Config::getInstance();
            } catch (Exception $e) {
                Piwik::createConfigObject();
            }
            try {
                $db = Zend_Registry::get('db');
            } catch (Exception $e) {
                Piwik::createDatabaseObject();
            }

            $pluginsManager = Piwik_PluginsManager::getInstance();
            $pluginsToLoad = Piwik_Config::getInstance()->Plugins['Plugins'];
            $pluginsForcedNotToLoad = Piwik_Tracker::getPluginsNotToLoad();
            $pluginsToLoad = array_diff($pluginsToLoad, $pluginsForcedNotToLoad);
            $pluginsToLoad = array_merge($pluginsToLoad, Piwik_Tracker::getPluginsToLoad());
            $pluginsManager->loadPlugins($pluginsToLoad);
        }
    }

    /**
     * Echos an error message & other information, then exits.
     *
     * @param Exception $e
     * @param bool $authenticated
     */
    protected function exitWithException($e, $authenticated)
    {
        if ($this->usingBulkTracking) {
            // when doing bulk tracking we return JSON so the caller will know how many succeeded
            $result = array('succeeded' => $this->countOfLoggedRequests);

            // send error when in debug mode or when authenticated (which happens when doing log importing,
            // for example)
            if ((isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG']) || $authenticated) {
                $result['error'] = Piwik_Tracker_GetErrorMessage($e);
            }

            echo Piwik_Common::json_encode($result);

            exit;
        } else {
            Piwik_Tracker_ExitWithException($e, $authenticated);
        }
    }

    /**
     * Returns the date in the "Y-m-d H:i:s" PHP format
     *
     * @param int $timestamp
     * @return string
     */
    public static function getDatetimeFromTimestamp($timestamp)
    {
        return date("Y-m-d H:i:s", $timestamp);
    }

    /**
     * Initialization
     */
    protected function init($request)
    {
        $this->handleTrackingApi($request);
        $this->loadTrackerPlugins($request);
        $this->handleDisabledTracker();
        $this->handleEmptyRequest($request);

        printDebug("Current datetime: " . date("Y-m-d H:i:s", $this->getCurrentTimestamp()));
    }

    /**
     * Cleanup
     */
    protected function end()
    {
        switch ($this->getState()) {
            case self::STATE_LOGGING_DISABLE:
                printDebug("Logging disabled, display transparent logo");
                break;

            case self::STATE_EMPTY_REQUEST:
                printDebug("Empty request => Piwik page");
                echo "<a href='/'>Piwik</a> is a free open source web <a href='http://piwik.org'>analytics</a> that lets you keep control of your data.";
                break;

            case self::STATE_NOSCRIPT_REQUEST:
            case self::STATE_NOTHING_TO_NOTICE:
            default:
                printDebug("Nothing to notice => default behaviour");
                break;
        }
        printDebug("End of the page.");

        if ($GLOBALS['PIWIK_TRACKER_DEBUG'] === true) {
            if (isset(self::$db)) {
                self::$db->recordProfiling();
                Piwik::printSqlProfilingReportTracker(self::$db);
            }
        }

        self::disconnectDatabase();
    }

    /**
     * Factory to create database objects
     *
     * @param array $configDb Database configuration
     * @throws Exception
     * @return Piwik_Tracker_Db_Mysqli|Piwik_Tracker_Db_Pdo_Mysql
     */
    public static function factory($configDb)
    {
        switch ($configDb['adapter']) {
            case 'PDO_MYSQL':
                require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Db/Pdo/Mysql.php';
                return new Piwik_Tracker_Db_Pdo_Mysql($configDb);

            case 'MYSQLI':
                require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Db/Mysqli.php';
                return new Piwik_Tracker_Db_Mysqli($configDb);
        }

        throw new Exception('Unsupported database adapter ' . $configDb['adapter']);
    }

    public static function connectPiwikTrackerDb()
    {
        $db = null;
        $configDb = Piwik_Config::getInstance()->database;

        if (!isset($configDb['port'])) {
            // before 0.2.4 there is no port specified in config file
            $configDb['port'] = '3306';
        }

        Piwik_PostEvent('Tracker.getDatabaseConfig', $configDb);

        $db = self::factory($configDb);
        $db->connect();

        return $db;
    }

    public static function connectDatabaseIfNotConnected()
    {
        if (!is_null(self::$db)) {
            return;
        }

        try {
            $db = null;
            Piwik_PostEvent('Tracker.createDatabase', $db);
            if (is_null($db)) {
                $db = self::connectPiwikTrackerDb();
            }
            self::$db = $db;
        } catch (Exception $e) {
            throw new Piwik_Tracker_Db_Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return Piwik_Tracker_Db
     */
    public static function getDatabase()
    {
        return self::$db;
    }

    public static function disconnectDatabase()
    {
        if (isset(self::$db)) {
            self::$db->disconnect();
            self::$db = null;
        }
    }

    /**
     * Returns the Tracker_Visit object.
     * This method can be overwritten to use a different Tracker_Visit object
     *
     * @throws Exception
     * @return Piwik_Tracker_Visit
     */
    protected function getNewVisitObject()
    {
        $visit = null;
        Piwik_PostEvent('Tracker.getNewVisitObject', $visit);

        if (is_null($visit)) {
            $visit = new Piwik_Tracker_Visit(self::$forcedIpString, self::$forcedDateTime, $this->authenticated);
            $visit->setForcedVisitorId(self::$forcedVisitorId);
        } elseif (!($visit instanceof Piwik_Tracker_Visit_Interface)) {
            throw new Exception("The Visit object set in the plugin must implement Piwik_Tracker_Visit_Interface");
        }
        return $visit;
    }

    protected function outputTransparentGif()
    {
        if (!isset($GLOBALS['PIWIK_TRACKER_DEBUG']) || !$GLOBALS['PIWIK_TRACKER_DEBUG']) {
            $trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
            $this->sendHeader('Content-Type: image/gif');

            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

            if ($requestMethod !== 'GET') {
                $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
                $this->sendHeader('Access-Control-Allow-Origin: ' . $origin);
                $this->sendHeader('Access-Control-Allow-Credentials: true');
            }

            print(base64_decode($trans_gif_64));
        }
    }

    protected function sendHeader($header)
    {
        Piwik_Common::sendHeader($header);
    }

    protected function isVisitValid()
    {
        return $this->stateValid !== self::STATE_LOGGING_DISABLE
            && $this->stateValid !== self::STATE_EMPTY_REQUEST;
    }

    protected function getState()
    {
        return $this->stateValid;
    }

    protected function setState($value)
    {
        $this->stateValid = $value;
    }

    protected function loadTrackerPlugins($request)
    {
        // Adding &dp=1 will disable the provider plugin, if token_auth is used (used to speed up bulk imports)
        if (isset($request['dp'])
            && !empty($request['dp'])
            && $this->authenticated
        ) {
            Piwik_Tracker::setPluginsNotToLoad(array('Provider'));
        }

        try {
            $pluginsTracker = Piwik_Config::getInstance()->Plugins_Tracker['Plugins_Tracker'];
            if (count($pluginsTracker) > 0) {
                $pluginsTracker = $pluginsTracker;
                $pluginsTracker = array_diff($pluginsTracker, self::getPluginsNotToLoad());
                Piwik_PluginsManager::getInstance()->doNotLoadAlwaysActivatedPlugins();

                Piwik_PluginsManager::getInstance()->loadPlugins($pluginsTracker);

                printDebug("Loading plugins: { " . implode(",", $pluginsTracker) . " }");
            }
        } catch (Exception $e) {
            printDebug("ERROR: " . $e->getMessage());
        }
    }

    protected function handleEmptyRequest($request)
    {
        $countParameters = count($request);
        if ($countParameters == 0) {
            $this->setState(self::STATE_EMPTY_REQUEST);
        }
        if ($countParameters == 1) {
            $this->setState(self::STATE_NOSCRIPT_REQUEST);
        }
    }

    protected function handleDisabledTracker()
    {
        $saveStats = Piwik_Config::getInstance()->Tracker['record_statistics'];
        if ($saveStats == 0) {
            $this->setState(self::STATE_LOGGING_DISABLE);
        }
    }

    protected function authenticateSuperUserOrAdmin($request)
    {
        $tokenAuth = $this->getTokenAuth();

        if (!$tokenAuth) {
            return false;
        }
        $superUserLogin = Piwik_Config::getInstance()->superuser['login'];
        $superUserPassword = Piwik_Config::getInstance()->superuser['password'];
        if (md5($superUserLogin . $superUserPassword) == $tokenAuth) {
            $this->authenticated = true;
            return true;
        }

        // Now checking the list of admin token_auth cached in the Tracker config file
        $idSite = Piwik_Common::getRequestVar('idsite', false, 'int', $request);
        if (!empty($idSite)
            && $idSite > 0
        ) {
            $website = Piwik_Tracker_Cache::getCacheWebsiteAttributes($idSite);
            $adminTokenAuth = $website['admin_token_auth'];
            if (in_array($tokenAuth, $adminTokenAuth)) {
                $this->authenticated = true;
                return true;
            }
        }
        printDebug("WARNING! token_auth = $tokenAuth is not valid, Super User / Admin was NOT authenticated");

        return false;
    }

    protected function getTokenAuth()
    {
        if (!is_null($this->tokenAuth)) {
            return $this->tokenAuth;
        }

        return Piwik_Common::getRequestVar('token_auth', false);
    }

    /**
     * This method allows to set custom IP + server time + visitor ID, when using Tracking API.
     * These two attributes can be only set by the Super User (passing token_auth).
     */
    protected function handleTrackingApi($request)
    {
        $shouldAuthenticate = Piwik_Config::getInstance()->Tracker['tracking_requests_require_authentication'];
        if ($shouldAuthenticate) {
            if (!$this->authenticateSuperUserOrAdmin($request)) {
                return;
            }
            printDebug("token_auth is authenticated!");
        } else {
            printDebug("token_auth authentication not required");
        }

        // Custom IP to use for this visitor
        $customIp = Piwik_Common::getRequestVar('cip', false, 'string', $request);
        if (!empty($customIp)) {
            $this->setForceIp($customIp);
        }

        // Custom server date time to use
        $customDatetime = Piwik_Common::getRequestVar('cdt', false, 'string', $request);
        if (!empty($customDatetime)) {
            $this->setForceDateTime($customDatetime);
        }

        // Forced Visitor ID to record the visit / action
        $customVisitorId = Piwik_Common::getRequestVar('cid', false, 'string', $request);
        if (!empty($customVisitorId)) {
            $this->setForceVisitorId($customVisitorId);
        }
    }

    public static function setTestEnvironment($args = null, $requestMethod = null)
    {
        if (is_null($args)) {
            $args = $_GET + $_POST;
        }
        if (is_null($requestMethod)) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        }

        // Do not run scheduled tasks during tests
        self::updateTrackerConfig('scheduled_tasks_min_interval', 0);

        // if nothing found in _GET/_POST and we're doing a POST, assume bulk request. in which case,
        // we have to bypass authentication
        if (empty($args) && $requestMethod == 'POST') {
            self::updateTrackerConfig('tracking_requests_require_authentication', 0);
        }

        // Tests can force the use of 3rd party cookie for ID visitor
        if (Piwik_Common::getRequestVar('forceUseThirdPartyCookie', false, null, $args) == 1) {
            self::updateTrackerConfig('use_third_party_id_cookie', 1);
        }

        // Tests using window_look_back_for_visitor
        if (Piwik_Common::getRequestVar('forceLargeWindowLookBackForVisitor', false, null, $args) == 1) {
            self::updateTrackerConfig('window_look_back_for_visitor', 2678400);
        }

        // Tests can force the enabling of IP anonymization
        $forceIpAnonymization = false;
        if (Piwik_Common::getRequestVar('forceIpAnonymization', false, null, $args) == 1) {
            self::updateTrackerConfig('ip_address_mask_length', 2);

            $section = Piwik_Config::getInstance()->Plugins_Tracker;
            $section['Plugins_Tracker'][] = "AnonymizeIP";
            Piwik_Config::getInstance()->Plugins_Tracker = $section;

            $forceIpAnonymization = true;
        }

        // Custom IP to use for this visitor
        $customIp = Piwik_Common::getRequestVar('cip', false, null, $args);
        if (!empty($customIp)) {
            self::setForceIp($customIp);
        }

        // Custom server date time to use
        $customDatetime = Piwik_Common::getRequestVar('cdt', false, null, $args);
        if (!empty($customDatetime)) {
            self::setForceDateTime($customDatetime);
        }

        // Custom visitor id
        $customVisitorId = Piwik_Common::getRequestVar('cid', false, null, $args);
        if (!empty($customVisitorId)) {
            self::setForceVisitorId($customVisitorId);
        }
        $pluginsDisabled = array('Provider');
        if (!$forceIpAnonymization) {
            $pluginsDisabled[] = 'AnonymizeIP';
        }

        // Disable provider plugin, because it is so slow to do reverse ip lookup in dev environment somehow
        self::setPluginsNotToLoad($pluginsDisabled);

        // we load 'DevicesDetection' in tests only (disabled by default)
        self::setPluginsToLoad( array('DevicesDetection') );
    }
}

/**
 * Gets the error message to output when a tracking request fails.
 *
 * @param Exception $e
 * @return string
 */
function Piwik_Tracker_GetErrorMessage($e)
{
    // Note: duplicated from FormDatabaseSetup.isAccessDenied
    // Avoid leaking the username/db name when access denied
    if ($e->getCode() == 1044 || $e->getCode() == 42000) {
        return "Error while connecting to the Piwik database - please check your credentials in config/config.ini.php file";
    } else {
        return $e->getMessage();
    }
}

/**
 * Displays exception in a friendly UI and exits.
 *
 * @param Exception $e
 * @param bool      $authenticated
 */
function Piwik_Tracker_ExitWithException($e, $authenticated = false)
{
    Piwik_Common::sendHeader('Content-Type: text/html; charset=utf-8');

    if (isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG']) {
        $trailer = '<span style="color: #888888">Backtrace:<br /><pre>' . $e->getTraceAsString() . '</pre></span>';
        $headerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/themes/default/simple_structure_header.tpl');
        $footerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/themes/default/simple_structure_footer.tpl');
        $headerPage = str_replace('{$HTML_TITLE}', 'Piwik &rsaquo; Error', $headerPage);

        echo $headerPage . '<p>' . Piwik_Tracker_GetErrorMessage($e) . '</p>' . $trailer . $footerPage;
    } // If not debug, but running authenticated (eg. during log import) then we display raw errors
    elseif ($authenticated) {
        echo Piwik_Tracker_GetErrorMessage($e);
    }
    exit;
}

