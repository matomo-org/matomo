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
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\Plugins\SitesManager\SiteUrls;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Db\DbException;
use Piwik\Tracker\Db\Mysqli;
use Piwik\Tracker\Db\Pdo\Mysql;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit;
use Piwik\Tracker\VisitInterface;

/**
 * Class used by the logging script piwik.php called by the javascript tag.
 * Handles the visitor & his/her actions on the website, saves the data in the DB,
 * saves information in the cookie, etc.
 *
 * We try to include as little files as possible (no dependency on 3rd party modules).
 *
 */
class Tracker
{
    protected $stateValid = self::STATE_NOTHING_TO_NOTICE;
    /**
     * @var Db
     */
    protected static $db = null;

    const STATE_NOTHING_TO_NOTICE = 1;
    const STATE_LOGGING_DISABLE = 10;
    const STATE_EMPTY_REQUEST = 11;
    const STATE_NOSCRIPT_REQUEST = 13;

    // We use hex ID that are 16 chars in length, ie. 64 bits IDs
    const LENGTH_HEX_ID_STRING = 16;
    const LENGTH_BINARY_ID = 8;

    protected static $pluginsNotToLoad = array();
    protected static $pluginsToLoad = array();

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

    protected function outputAccessControlHeaders()
    {
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        if ($requestMethod !== 'GET') {
            $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
            Common::sendHeader('Access-Control-Allow-Origin: ' . $origin);
            Common::sendHeader('Access-Control-Allow-Credentials: true');
        }
    }

    public function clear()
    {
        $this->stateValid = self::STATE_NOTHING_TO_NOTICE;
    }

    /**
     * Do not load the specified plugins (used during testing, to disable Provider plugin)
     * @param array $plugins
     */
    public static function setPluginsNotToLoad($plugins)
    {
        self::$pluginsNotToLoad = $plugins;
    }

    /**
     * Get list of plugins to not load
     *
     * @return array
     */
    public static function getPluginsNotToLoad()
    {
        return self::$pluginsNotToLoad;
    }

    /**
     * Update Tracker config
     *
     * @param string $name Setting name
     * @param mixed $value Value
     */
    private static function updateTrackerConfig($name, $value)
    {
        $section = Config::getInstance()->Tracker;
        $section[$name] = $value;
        Config::getInstance()->Tracker = $section;
    }

    protected function initRequests($args)
    {
        $rawData = self::getRawBulkRequest();
        if (!empty($rawData)) {
            $this->usingBulkTracking = strpos($rawData, '"requests"') || strpos($rawData, "'requests'");
            if ($this->usingBulkTracking) {
                return $this->authenticateBulkTrackingRequests($rawData);
            }
        }

        // Not using bulk tracking
        $this->requests = $args ? $args : (!empty($_GET) || !empty($_POST) ? array($_GET + $_POST) : array());
    }

    private static function getRequestsArrayFromBulkRequest($rawData)
    {
        $rawData = trim($rawData);
        $rawData = Common::sanitizeLineBreaks($rawData);

        // POST data can be array of string URLs or array of arrays w/ visit info
        $jsonData = json_decode($rawData, $assoc = true);

        $tokenAuth = Common::getRequestVar('token_auth', false, 'string', $jsonData);

        $requests = array();
        if (isset($jsonData['requests'])) {
            $requests = $jsonData['requests'];
        }

        return array($requests, $tokenAuth);
    }

    private function isBulkTrackingRequireTokenAuth()
    {
        return !empty(Config::getInstance()->Tracker['bulk_requests_require_authentication']);
    }

    private function authenticateBulkTrackingRequests($rawData)
    {
        list($this->requests, $tokenAuth) = $this->getRequestsArrayFromBulkRequest($rawData);

        $bulkTrackingRequireTokenAuth = $this->isBulkTrackingRequireTokenAuth();
        if ($bulkTrackingRequireTokenAuth) {
            if (empty($tokenAuth)) {
                throw new Exception("token_auth must be specified when using Bulk Tracking Import. "
                    . " See <a href='http://developer.piwik.org/api-reference/tracking-api'>Tracking Doc</a>");
            }
        }

        if (!empty($this->requests)) {
            foreach ($this->requests as &$request) {
                // if a string is sent, we assume its a URL and try to parse it
                if (is_string($request)) {
                    $params = array();

                    $url = @parse_url($request);
                    if (!empty($url)) {
                        @parse_str($url['query'], $params);
                        $request = $params;
                    }
                }

                $requestObj = new Request($request, $tokenAuth);
                $this->loadTrackerPlugins($requestObj);

                if ($bulkTrackingRequireTokenAuth
                    && !$requestObj->isAuthenticated()
                ) {
                    throw new Exception(sprintf("token_auth specified does not have Admin permission for idsite=%s", $requestObj->getIdSite()));
                }
                $request = $requestObj;
            }
        }

        return $tokenAuth;
    }

    /**
     * Main - tracks the visit/action
     *
     * @param array $args Optional Request Array
     */
    public function main($args = null)
    {
        if (!SettingsPiwik::isPiwikInstalled()) {
            return $this->handleEmptyRequest();
        }
        try {
            $tokenAuth = $this->initRequests($args);
        } catch (Exception $ex) {
            $this->exitWithException($ex, true);
        }

        $this->initOutputBuffer();

        if (!empty($this->requests)) {
            $this->beginTransaction();

            try {
                foreach ($this->requests as $params) {
                    $isAuthenticated = $this->trackRequest($params, $tokenAuth);
                }
                $this->runScheduledTasksIfAllowed($isAuthenticated);
                $this->commitTransaction();
            } catch (DbException $e) {
                Common::printDebug($e->getMessage());
                $this->rollbackTransaction();
            }

        } else {
            $this->handleEmptyRequest();
        }

        Piwik::postEvent('Tracker.end');

        $this->end();

        $this->flushOutputBuffer();

        $this->performRedirectToUrlIfSet();
    }

    protected function initOutputBuffer()
    {
        ob_start();
    }

    protected function flushOutputBuffer()
    {
        ob_end_flush();
    }

    protected function getOutputBuffer()
    {
        return ob_get_contents();
    }

    protected function beginTransaction()
    {
        $this->transactionId = null;
        if (!$this->shouldUseTransactions()) {
            return;
        }
        $this->transactionId = self::getDatabase()->beginTransaction();
    }

    protected function commitTransaction()
    {
        if (empty($this->transactionId)) {
            return;
        }
        self::getDatabase()->commit($this->transactionId);
    }

    protected function rollbackTransaction()
    {
        if (empty($this->transactionId)) {
            return;
        }
        self::getDatabase()->rollback($this->transactionId);
    }

    /**
     * @return bool
     */
    protected function shouldUseTransactions()
    {
        $isBulkRequest = count($this->requests) > 1;
        return $isBulkRequest && $this->isTransactionSupported();
    }

    /**
     * @return bool
     */
    protected function isTransactionSupported()
    {
        return (bool)Config::getInstance()->Tracker['bulk_requests_use_transaction'];
    }

    protected function shouldRunScheduledTasks()
    {
        // don't run scheduled tasks in CLI mode from Tracker, this is the case
        // where we bulk load logs & don't want to lose time with tasks
        return !Common::isPhpCliMode()
        && $this->getState() != self::STATE_LOGGING_DISABLE;
    }

    /**
     * Tracker requests will automatically trigger the Scheduled tasks.
     * This is useful for users who don't setup the cron,
     * but still want daily/weekly/monthly PDF reports emailed automatically.
     *
     * This is similar to calling the API CoreAdminHome.runScheduledTasks
     */
    protected static function runScheduledTasks()
    {
        $now = time();

        // Currently, there are no hourly tasks. When there are some,
        // this could be too aggressive minimum interval (some hours would be skipped in case of low traffic)
        $minimumInterval = Config::getInstance()->Tracker['scheduled_tasks_min_interval'];

        // If the user disabled browser archiving, he has already setup a cron
        // To avoid parallel requests triggering the Scheduled Tasks,
        // Get last time tasks started executing
        $cache = Cache::getCacheGeneral();

        if ($minimumInterval <= 0
            || empty($cache['isBrowserTriggerEnabled'])
        ) {
            Common::printDebug("-> Scheduled tasks not running in Tracker: Browser archiving is disabled.");
            return;
        }

        $nextRunTime = $cache['lastTrackerCronRun'] + $minimumInterval;

        if ((defined('DEBUG_FORCE_SCHEDULED_TASKS') && DEBUG_FORCE_SCHEDULED_TASKS)
            || $cache['lastTrackerCronRun'] === false
            || $nextRunTime < $now
        ) {
            $cache['lastTrackerCronRun'] = $now;
            Cache::setCacheGeneral($cache);
            self::initCorePiwikInTrackerMode();
            Option::set('lastTrackerCronRun', $cache['lastTrackerCronRun']);
            Common::printDebug('-> Scheduled Tasks: Starting...');

            // save current user privilege and temporarily assume Super User privilege
            $isSuperUser = Piwik::hasUserSuperUserAccess();

            // Scheduled tasks assume Super User is running
            Piwik::setUserHasSuperUserAccess();

            // While each plugins should ensure that necessary languages are loaded,
            // we ensure English translations at least are loaded
            Translate::loadEnglishTranslation();

            ob_start();
            CronArchive::$url = SettingsPiwik::getPiwikUrl();
            $cronArchive = new CronArchive();
            $cronArchive->runScheduledTasksInTrackerMode();

            $resultTasks = ob_get_contents();
            ob_clean();

            // restore original user privilege
            Piwik::setUserHasSuperUserAccess($isSuperUser);

            foreach (explode('</pre>', $resultTasks) as $resultTask) {
                Common::printDebug(str_replace('<pre>', '', $resultTask));
            }

            Common::printDebug('Finished Scheduled Tasks.');
        } else {
            Common::printDebug("-> Scheduled tasks not triggered.");
        }
        Common::printDebug("Next run will be from: " . date('Y-m-d H:i:s', $nextRunTime) . ' UTC');
    }

    public static $initTrackerMode = false;

    /**
     * Used to initialize core Piwik components on a piwik.php request
     * Eg. when cache is missed and we will be calling some APIs to generate cache
     */
    public static function initCorePiwikInTrackerMode()
    {
        if (SettingsServer::isTrackerApiRequest()
            && self::$initTrackerMode === false
        ) {
            self::$initTrackerMode = true;
            require_once PIWIK_INCLUDE_PATH . '/core/Option.php';

            Access::getInstance();
            Config::getInstance();

            try {
                Db::get();
            } catch (Exception $e) {
                Db::createDatabaseObject();
            }

            \Piwik\Plugin\Manager::getInstance()->loadCorePluginsDuringTracker();
        }
    }

    /**
     * Echos an error message & other information, then exits.
     *
     * @param Exception $e
     * @param bool $authenticated
     */
    protected function exitWithException($e, $authenticated = false)
    {
        if ($this->hasRedirectUrl()) {
            $this->performRedirectToUrlIfSet();
            exit;
        }

        Common::sendHeader('HTTP/1.1 500 Internal Server Error');
        error_log(sprintf("Error in Piwik (tracker): %s", str_replace("\n", " ", $this->getMessageFromException($e))));

        if ($this->usingBulkTracking) {
            // when doing bulk tracking we return JSON so the caller will know how many succeeded
            $result = array(
                'status' => 'error',
                'tracked' => $this->countOfLoggedRequests
            );
            // send error when in debug mode or when authenticated (which happens when doing log importing,
            if ((isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG'])
                || $authenticated
            ) {
                $result['message'] = $this->getMessageFromException($e);
            }
            Common::sendHeader('Content-Type: application/json');
            echo Common::json_encode($result);
            die(1);
            exit;
        }

        if (isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG']) {
            Common::sendHeader('Content-Type: text/html; charset=utf-8');
            $trailer = '<span style="color: #888888">Backtrace:<br /><pre>' . $e->getTraceAsString() . '</pre></span>';
            $headerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/simpleLayoutHeader.tpl');
            $footerPage = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/Morpheus/templates/simpleLayoutFooter.tpl');
            $headerPage = str_replace('{$HTML_TITLE}', 'Piwik &rsaquo; Error', $headerPage);

            echo $headerPage . '<p>' . $this->getMessageFromException($e) . '</p>' . $trailer . $footerPage;
        } // If not debug, but running authenticated (eg. during log import) then we display raw errors
        elseif ($authenticated) {
            Common::sendHeader('Content-Type: text/html; charset=utf-8');
            echo $this->getMessageFromException($e);
        } else {
            $this->outputTransparentGif();
        }
        die(1);
        exit;
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
     * @param Request $request
     */
    protected function init(Request $request)
    {
        $this->loadTrackerPlugins($request);
        $this->handleDisabledTracker();
        $this->handleEmptyRequest($request);
    }

    /**
     * Cleanup
     */
    protected function end()
    {
        if ($this->usingBulkTracking) {
            $result = array(
                'status' => 'success',
                'tracked' => $this->countOfLoggedRequests
            );

            $this->outputAccessControlHeaders();

            Common::sendHeader('Content-Type: application/json');
            echo Common::json_encode($result);
            exit;
        }
        switch ($this->getState()) {
            case self::STATE_LOGGING_DISABLE:
                $this->outputTransparentGif ();
                Common::printDebug("Logging disabled, display transparent logo");
                break;

            case self::STATE_EMPTY_REQUEST:
                Common::printDebug("Empty request => Piwik page");
                echo "<a href='/'>Piwik</a> is a free/libre web <a href='http://piwik.org'>analytics</a> that lets you keep control of your data.";
                break;

            case self::STATE_NOSCRIPT_REQUEST:
            case self::STATE_NOTHING_TO_NOTICE:
            default:
                $this->outputTransparentGif ();
                Common::printDebug("Nothing to notice => default behaviour");
                break;
        }
        Common::printDebug("End of the page.");

        if ($GLOBALS['PIWIK_TRACKER_DEBUG'] === true) {
            if (isset(self::$db)) {
                self::$db->recordProfiling();
                Profiler::displayDbTrackerProfile(self::$db);
            }
        }

        self::disconnectDatabase();
    }

    /**
     * Factory to create database objects
     *
     * @param array $configDb Database configuration
     * @throws Exception
     * @return \Piwik\Tracker\Db\Mysqli|\Piwik\Tracker\Db\Pdo\Mysql
     */
    public static function factory($configDb)
    {
        /**
         * Triggered before a connection to the database is established by the Tracker.
         *
         * This event can be used to change the database connection settings used by the Tracker.
         *
         * @param array $dbInfos Reference to an array containing database connection info,
         *                       including:
         *
         *                       - **host**: The host name or IP address to the MySQL database.
         *                       - **username**: The username to use when connecting to the
         *                                       database.
         *                       - **password**: The password to use when connecting to the
         *                                       database.
         *                       - **dbname**: The name of the Piwik MySQL database.
         *                       - **port**: The MySQL database port to use.
         *                       - **adapter**: either `'PDO\MYSQL'` or `'MYSQLI'`
         *                       - **type**: The MySQL engine to use, for instance 'InnoDB'
         */
        Piwik::postEvent('Tracker.getDatabaseConfig', array(&$configDb));

        switch ($configDb['adapter']) {
            case 'PDO\MYSQL':
            case 'PDO_MYSQL': // old format pre Piwik 2
                require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Db/Pdo/Mysql.php';
                return new Mysql($configDb);

            case 'MYSQLI':
                require_once PIWIK_INCLUDE_PATH . '/core/Tracker/Db/Mysqli.php';
                return new Mysqli($configDb);
        }

        throw new Exception('Unsupported database adapter ' . $configDb['adapter']);
    }

    public static function connectPiwikTrackerDb()
    {
        $db = null;
        $configDb = Config::getInstance()->database;

        if (!isset($configDb['port'])) {
            // before 0.2.4 there is no port specified in config file
            $configDb['port'] = '3306';
        }

        $db = Tracker::factory($configDb);
        $db->connect();

        return $db;
    }

    protected static function connectDatabaseIfNotConnected()
    {
        if (!is_null(self::$db)) {
            return;
        }

        try {
            self::$db = self::connectPiwikTrackerDb();
        } catch (Exception $e) {
            throw new DbException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @return Db
     */
    public static function getDatabase()
    {
        self::connectDatabaseIfNotConnected();
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
     * @return \Piwik\Tracker\Visit
     */
    protected function getNewVisitObject()
    {
        $visit = null;

        /**
         * Triggered before a new **visit tracking object** is created. Subscribers to this
         * event can force the use of a custom visit tracking object that extends from
         * {@link Piwik\Tracker\VisitInterface}.
         *
         * @param \Piwik\Tracker\VisitInterface &$visit Initialized to null, but can be set to
         *                                              a new visit object. If it isn't modified
         *                                              Piwik uses the default class.
         */
        Piwik::postEvent('Tracker.makeNewVisitObject', array(&$visit));

        if (is_null($visit)) {
            $visit = new Visit();
        } elseif (!($visit instanceof VisitInterface)) {
            throw new Exception("The Visit object set in the plugin must implement VisitInterface");
        }
        return $visit;
    }

    protected function outputTransparentGif ()
    {
        if (isset($GLOBALS['PIWIK_TRACKER_DEBUG'])
            && $GLOBALS['PIWIK_TRACKER_DEBUG']
        ) {
            return;
        }

        if (strlen($this->getOutputBuffer()) > 0) {
            // If there was an error during tracker, return so errors can be flushed
            return;
        }
        $transGifBase64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        Common::sendHeader('Content-Type: image/gif');

        $this->outputAccessControlHeaders();

        print(base64_decode($transGifBase64));
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

    protected function loadTrackerPlugins(Request $request)
    {
        // Adding &dp=1 will disable the provider plugin, if token_auth is used (used to speed up bulk imports)
        $disableProvider = $request->getParam('dp');
        if (!empty($disableProvider)) {
            Tracker::setPluginsNotToLoad(array('Provider'));
        }

        try {
            $pluginsTracker = \Piwik\Plugin\Manager::getInstance()->loadTrackerPlugins();
            Common::printDebug("Loading plugins: { " . implode(", ", $pluginsTracker) . " }");
        } catch (Exception $e) {
            Common::printDebug("ERROR: " . $e->getMessage());
        }
    }

    protected function handleEmptyRequest(Request $request = null)
    {
        if (is_null($request)) {
            $request = new Request($_GET + $_POST);
        }
        $countParameters = $request->getParamsCount();
        if ($countParameters == 0) {
            $this->setState(self::STATE_EMPTY_REQUEST);
        }
        if ($countParameters == 1) {
            $this->setState(self::STATE_NOSCRIPT_REQUEST);
        }
    }

    protected function handleDisabledTracker()
    {
        $saveStats = Config::getInstance()->Tracker['record_statistics'];
        if ($saveStats == 0) {
            $this->setState(self::STATE_LOGGING_DISABLE);
        }
    }

    protected function getTokenAuth()
    {
        if (!is_null($this->tokenAuth)) {
            return $this->tokenAuth;
        }

        return Common::getRequestVar('token_auth', false);
    }

    public static function setTestEnvironment($args = null, $requestMethod = null)
    {
        if (is_null($args)) {
            $postData = self::getRequestsArrayFromBulkRequest(self::getRawBulkRequest());
            $args = $_GET + $postData;
        }
        if (is_null($requestMethod) && array_key_exists('REQUEST_METHOD', $_SERVER)) {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
        } else if (is_null($requestMethod)) {
            $requestMethod = 'GET';
        }

        // Do not run scheduled tasks during tests
        self::updateTrackerConfig('scheduled_tasks_min_interval', 0);

        // if nothing found in _GET/_POST and we're doing a POST, assume bulk request. in which case,
        // we have to bypass authentication
        if (empty($args) && $requestMethod == 'POST') {
            self::updateTrackerConfig('tracking_requests_require_authentication', 0);
        }

        // Tests can force the use of 3rd party cookie for ID visitor
        if (Common::getRequestVar('forceUseThirdPartyCookie', false, null, $args) == 1) {
            self::updateTrackerConfig('use_third_party_id_cookie', 1);
        }

        // Tests using window_look_back_for_visitor
        if (Common::getRequestVar('forceLargeWindowLookBackForVisitor', false, null, $args) == 1
            // also look for this in bulk requests (see fake_logs_replay.log)
            || strpos(json_encode($args, true), '"forceLargeWindowLookBackForVisitor":"1"') !== false
        ) {
            self::updateTrackerConfig('window_look_back_for_visitor', 2678400);
        }

        // Tests can force the enabling of IP anonymization
        if (Common::getRequestVar('forceIpAnonymization', false, null, $args) == 1) {

            self::connectDatabaseIfNotConnected();

            $privacyConfig = new PrivacyManagerConfig();
            $privacyConfig->ipAddressMaskLength = 2;

            \Piwik\Plugins\PrivacyManager\IPAnonymizer::activate();
        }

        $pluginsDisabled = array('Provider');

        // Disable provider plugin, because it is so slow to do many reverse ip lookups
        self::setPluginsNotToLoad($pluginsDisabled);
    }

    /**
     * Gets the error message to output when a tracking request fails.
     *
     * @param Exception $e
     * @return string
     */
    private function getMessageFromException($e)
    {
        // Note: duplicated from FormDatabaseSetup.isAccessDenied
        // Avoid leaking the username/db name when access denied
        if ($e->getCode() == 1044 || $e->getCode() == 42000) {
            return "Error while connecting to the Piwik database - please check your credentials in config/config.ini.php file";
        }
        if(Common::isPhpCliMode()) {
            return $e->getMessage() . "\n" . $e->getTraceAsString();
        }
        return $e->getMessage();
    }

    /**
     * @param $params
     * @param $tokenAuth
     * @return array
     */
    protected function trackRequest($params, $tokenAuth)
    {
        if ($params instanceof Request) {
            $request = $params;
        } else {
            $request = new Request($params, $tokenAuth);
        }

        $this->init($request);

        $isAuthenticated = $request->isAuthenticated();

        try {
            if ($this->isVisitValid()) {
                Common::printDebug("Current datetime: " . date("Y-m-d H:i:s", $request->getCurrentTimestamp()));

                $visit = $this->getNewVisitObject();
                $visit->setRequest($request);
                $visit->handle();
            } else {
                Common::printDebug("The request is invalid: empty request, or maybe tracking is disabled in the config.ini.php via record_statistics=0");
            }
        } catch (DbException $e) {
            Common::printDebug("Exception: " . $e->getMessage());
            $this->exitWithException($e, $isAuthenticated);
        } catch (Exception $e) {
            $this->exitWithException($e, $isAuthenticated);
        }
        $this->clear();

        // increment successfully logged request count. make sure to do this after try-catch,
        // since an excluded visit is considered 'successfully logged'
        ++$this->countOfLoggedRequests;
        return $isAuthenticated;
    }

    protected function runScheduledTasksIfAllowed($isAuthenticated)
    {
        // Do not run schedule task if we are importing logs
        // or doing custom tracking (as it could slow down)
        try {
            if (!$isAuthenticated
                && $this->shouldRunScheduledTasks()
            ) {
                self::runScheduledTasks();
            }
        } catch (Exception $e) {
            $this->exitWithException($e);
        }
    }

    /**
     * @return string
     */
    protected static function getRawBulkRequest()
    {
        return file_get_contents("php://input");
    }

    private function getRedirectUrl()
    {
        return Common::getRequestVar('redirecturl', false, 'string');
    }

    private function hasRedirectUrl()
    {
        $redirectUrl = $this->getRedirectUrl();

        return !empty($redirectUrl);
    }

    private function performRedirectToUrlIfSet()
    {
        if (!$this->hasRedirectUrl()) {
            return;
        }

        if (empty($this->requests)) {
            return;
        }

        $redirectUrl = $this->getRedirectUrl();
        $host        = Url::getHostFromUrl($redirectUrl);

        if (empty($host)) {
            return;
        }

        $urls     = new SiteUrls();
        $siteUrls = $urls->getAllCachedSiteUrls();
        $siteIds  = $this->getAllSiteIdsWithinRequest();

        foreach ($siteIds as $siteId) {
            if (empty($siteUrls[$siteId])) {
                continue;
            }

            if (Url::isHostInUrls($host, $siteUrls[$siteId])) {
                Url::redirectToUrl($redirectUrl);
            }
        }
    }

    private function getAllSiteIdsWithinRequest()
    {
        if (empty($this->requests)) {
            return array();
        }

        $siteIds = array();

        foreach ($this->requests as $request) {
            $siteIds[] = (int) $request['idsite'];
        }

        return array_unique($siteIds);
    }

}
