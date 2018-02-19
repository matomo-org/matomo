<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework;

use Piwik\Access;
use Piwik\Application\Environment;
use Piwik\Archive;
use Piwik\ArchiveProcessor\PluginsArchiver;
use Piwik\Auth;
use Piwik\Auth\Password;
use Piwik\Cache\Backend\File;
use Piwik\Cache as PiwikCache;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataTable\Manager as DataTableManager;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Ini\IniReader;
use Piwik\Log;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\Manager;
use Piwik\Plugins\API\ProcessedReport;
use Piwik\Plugins\LanguagesManager\API as APILanguageManager;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\ReportRenderer;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Singleton;
use Piwik\Site;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tracker;
use Piwik\Tracker\Cache;
use Piwik\Translate;
use Piwik\Url;
use PHPUnit_Framework_Assert;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use PiwikTracker;
use Piwik_LocalTracker;
use Piwik\Updater;
use Exception;
use ReflectionClass;

/**
 * Base type for all system test fixtures. System test fixtures
 * add visit and related data to the database before a test is run. Different
 * tests can use the same fixtures.
 *
 * This class defines a set of helper methods for fixture types. The helper
 * methods are public, but ideally they should only be used by fixture types.
 *
 * NOTE: YOU SHOULD NOT CREATE A NEW FIXTURE UNLESS THERE IS NO WAY TO MODIFY
 * AN EXISTING FIXTURE TO HANDLE YOUR USE CASE.
 *
 * Related TODO: we should try and reduce the amount of existing fixtures by
 *                merging some together.
 * @since 2.8.0
 */
class Fixture extends \PHPUnit_Framework_Assert
{
    const IMAGES_GENERATED_ONLY_FOR_OS = 'linux';
    const IMAGES_GENERATED_FOR_PHP = '5.6';
    const IMAGES_GENERATED_FOR_GD = '2.1.0';
    const DEFAULT_SITE_NAME = 'Piwik test';

    const ADMIN_USER_LOGIN = 'superUserLogin';
    const ADMIN_USER_PASSWORD = 'superUserPass';

    public $dbName = false;

    /**
     * @deprecated has no effect now.
     */
    public $createConfig = true;

    public $dropDatabaseInSetUp = true;
    public $dropDatabaseInTearDown = true;

    /**
     * @deprecated
     */
    public $loadTranslations = true;

    public $createSuperUser = true;
    public $removeExistingSuperUser = true;
    public $overwriteExisting = true;
    public $configureComponents = true;
    public $persistFixtureData = false;
    public $resetPersistedFixture = false;
    public $printToScreen = false;

    public $testCaseClass = false;
    public $extraPluginsToLoad = array();
    public $extraDiEnvironments = array();

    public $testEnvironment = null;

    /**
     * Extra DI configuration to use when creating the test environment. This will override configuration
     * returned by the `provideContainerConfig()` method.
     *
     * @var array
     */
    public $extraDefinitions = array();

    public $extraTestEnvVars = array();

    /**
     * @var Environment
     */
    public $piwikEnvironment;

    /**
     * @return string
     */
    protected static function getPythonBinary()
    {
        if (SettingsServer::isWindows()) {
            return "C:\Python27\python.exe";
        }

        if (SystemTestCase::isTravisCI()) {
            return 'python2.6';
        }

        return 'python';
    }

    public static function getTestRootUrl()
    {
        return self::getRootUrl() . 'tests/PHPUnit/proxy/';
    }

    public function loginAsSuperUser()
    {
        /** @var Auth $auth */
        $auth = $this->piwikEnvironment->getContainer()->get('Piwik\Auth');
        $auth->setLogin(Fixture::ADMIN_USER_LOGIN);
        $auth->setPassword(Fixture::ADMIN_USER_PASSWORD);
        Access::getInstance()->setSuperUserAccess(false);
        Access::getInstance()->reloadAccess(StaticContainer::get('Piwik\Auth'));
    }

    /** Adds data to Piwik. Creates sites, tracks visits, imports log files, etc. */
    public function setUp()
    {
        // empty
    }

    /** Does any clean up. Most of the time there will be no need to clean up. */
    public function tearDown()
    {
        // empty
    }

    public function getDbName()
    {
        if ($this->dbName !== false) {
            return $this->dbName;
        }

        if ($this->persistFixtureData) {
            $klass = new ReflectionClass($this);
            $id = Plugin::getPluginNameFromNamespace($klass->getNamespaceName()) . "_" . $klass->getShortName();
            return $id;
        }

        return self::getConfig()->database_tests['dbname'];
    }

    public function performSetUp($setupEnvironmentOnly = false)
    {
        // TODO: don't use static var, use test env var for this
        TestingEnvironmentManipulator::$extraPluginsToLoad = $this->extraPluginsToLoad;

        $this->dbName = $this->getDbName();

        if ($this->persistFixtureData) {
            $this->dropDatabaseInSetUp = false;
            $this->dropDatabaseInTearDown = false;
            $this->overwriteExisting = false;
            $this->removeExistingSuperUser = false;
        }

        $testEnv = $this->getTestEnvironment();
        $testEnv->delete();
        $testEnv->testCaseClass = $this->testCaseClass;
        $testEnv->fixtureClass = get_class($this);
        $testEnv->dbName = $this->dbName;
        $testEnv->extraDiEnvironments = $this->extraDiEnvironments;

        foreach ($this->extraTestEnvVars as $name => $value) {
            $testEnv->$name = $value;
        }

        $testEnv->save();

        $this->createEnvironmentInstance();

        if ($this->dbName === false) { // must be after test config is created
            $this->dbName = self::getConfig()->database['dbname'];
        }

        try {
            static::connectWithoutDatabase();

            if ($this->dropDatabaseInSetUp
                || $this->resetPersistedFixture
            ) {
                $this->dropDatabase();
            }

            DbHelper::createDatabase($this->dbName);
            DbHelper::disconnectDatabase();
            Tracker::disconnectCachedDbConnection();

            // reconnect once we're sure the database exists
            self::getConfig()->database['dbname'] = $this->dbName;
            Db::createDatabaseObject();

            Db::get()->query("SET wait_timeout=28800;");

            DbHelper::createTables();

            self::getPluginManager()->unloadPlugins();

        } catch (Exception $e) {
            static::fail("TEST INITIALIZATION FAILED: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        include "DataFiles/Providers.php";

        if (!$this->isFixtureSetUp()) {
            DbHelper::truncateAllTables();
        }

        // We need to be SU to create websites for tests
        Access::getInstance()->setSuperUserAccess();

        Cache::deleteTrackerCache();

        self::resetPluginsInstalledConfig();

        $testEnvironment = $this->getTestEnvironment();
        static::loadAllPlugins($testEnvironment, $this->testCaseClass, $this->extraPluginsToLoad);
        self::updateDatabase();
        self::installAndActivatePlugins($testEnvironment);

        $_GET = $_REQUEST = array();
        $_SERVER['HTTP_REFERER'] = '';

        FakeAccess::$superUserLogin = 'superUserLogin';

        File::$invalidateOpCacheBeforeRead = true;

        if ($this->configureComponents) {
            IPAnonymizer::deactivate();
            $dntChecker = new DoNotTrackHeaderChecker();
            $dntChecker->deactivate();
        }

        if ($this->createSuperUser) {
            self::createSuperUser($this->removeExistingSuperUser);
            if (!(Access::getInstance() instanceof FakeAccess)) {
                $this->loginAsSuperUser();
            }

            APILanguageManager::getInstance()->setLanguageForUser('superUserLogin', 'en');
        }

        SettingsPiwik::overwritePiwikUrl(self::getTestRootUrl());

        if ($setupEnvironmentOnly) {
            return;
        }

        PiwikCache::getTransientCache()->flushAll();

        if ($this->overwriteExisting
            || !$this->isFixtureSetUp()
        ) {
            $this->setUp();

            $this->markFixtureSetUp();
            $this->log("Database {$this->dbName} marked as successfully set up.");
        } else {
            $this->log("Using existing database {$this->dbName}.");
        }
    }

    /**
     * NOTE: This method should not be used to get a TestingEnvironmentVariables instance.
     * Instead just create a new instance.
     *
     * @return null|\Piwik\Tests\Framework\TestingEnvironmentVariables
     */
    public function getTestEnvironment()
    {
        if ($this->testEnvironment === null) {
            $this->testEnvironment = new TestingEnvironmentVariables();

            if (getenv('PIWIK_USE_XHPROF') == 1) {
                $this->testEnvironment->useXhprof = true;
            }
        }
        return $this->testEnvironment;
    }

    public function isFixtureSetUp()
    {
        $optionName = get_class($this) . '.setUpFlag';
        return Option::get($optionName) !== false;
    }

    public function markFixtureSetUp()
    {
        $optionName = get_class($this) . '.setUpFlag';
        Option::set($optionName, 1);
    }

    public function performTearDown()
    {
        // Note: avoid run SQL in the *tearDown() metohds because it randomly fails on Travis CI
        // with error Error while sending QUERY packet. PID=XX
        $this->tearDown();

        self::unloadAllPlugins();


        if ($this->dropDatabaseInTearDown) {
            $this->dropDatabase();
        }

        $this->clearInMemoryCaches();

        Log::unsetInstance();

        $this->destroyEnvironment();
    }

    public function clearInMemoryCaches()
    {
        Archive::clearStaticCache();
        DataTableManager::getInstance()->deleteAll();
        Option::clearCache();
        Site::clearCache();
        Cache::deleteTrackerCache();
        PiwikCache::getTransientCache()->flushAll();
        PiwikCache::getEagerCache()->flushAll();
        PiwikCache::getLazyCache()->flushAll();
        ArchiveTableCreator::clear();
        \Piwik\Plugins\ScheduledReports\API::$cache = array();
        Singleton::clearAll();
        PluginsArchiver::$archivers = array();

        Plugin\API::unsetAllInstances();
        $_GET = $_REQUEST = array();
        Translate::reset();

        self::getConfig()->Plugins; // make sure Plugins exists in a config object for next tests that use Plugin\Manager
        // since Plugin\Manager uses getFromGlobalConfig which doesn't init the config object
    }

    protected static function resetPluginsInstalledConfig()
    {
        $config = self::getConfig();
        $installed = $config->PluginsInstalled;
        $installed['PluginsInstalled'] = array();
        $config->PluginsInstalled = $installed;
    }

    protected static function rememberCurrentlyInstalledPluginsAcrossRequests(TestingEnvironmentVariables $testEnvironment)
    {
        $plugins = self::getPluginManager()->getInstalledPluginsName();

        $testEnvironment->overrideConfig('PluginsInstalled', 'PluginsInstalled', $plugins);
        $testEnvironment->save();
    }

    /**
     * @param \Piwik\Tests\Framework\TestingEnvironmentVariables|null $testEnvironment Ignored.
     * @param bool|false $testCaseClass Ignored.
     * @param array $extraPluginsToLoad Ignoerd.
     */
    public static function loadAllPlugins(TestingEnvironmentVariables $testEnvironment = null, $testCaseClass = false, $extraPluginsToLoad = array())
    {
        DbHelper::createTables();
        self::getPluginManager()->loadActivatedPlugins();
    }

    public static function installAndActivatePlugins(TestingEnvironmentVariables $testEnvironment)
    {
        $pluginsManager = self::getPluginManager();

        // Install plugins
        $messages = $pluginsManager->installLoadedPlugins();
        if(!empty($messages)) {
            Log::info("Plugin loading messages: %s", implode(" --- ", $messages));
        }

        // Activate them
        foreach($pluginsManager->getLoadedPlugins() as $plugin) {
            $name = $plugin->getPluginName();
            if (!$pluginsManager->isPluginActivated($name)) {
                $pluginsManager->activatePlugin($name);
            }
        }

        $pluginsManager->loadPluginTranslations();

        self::rememberCurrentlyInstalledPluginsAcrossRequests($testEnvironment);
    }

    private static function getPluginManager()
    {
        return Manager::getInstance();
    }

    private static function getConfig()
    {
        return Config::getInstance();
    }

    public static function unloadAllPlugins()
    {
        try {
            $manager = self::getPluginManager();
            $plugins = $manager->getLoadedPlugins();
            foreach ($plugins as $plugin) {
                $plugin->uninstall();
            }

            $manager->unloadPlugins();
        } catch (Exception $e) {
        }

        self::resetPluginsInstalledConfig();
        self::rememberCurrentlyInstalledPluginsAcrossRequests(new TestingEnvironmentVariables());
    }

    /**
     * Creates a website, then sets its creation date to a day earlier than specified dateTime
     * Useful to create a website now, but force data to be archived back in the past.
     *
     * @param string $dateTime eg '2010-01-01 12:34:56'
     * @param int $ecommerce
     * @param string $siteName
     *
     * @param bool|string $siteUrl
     * @param int $siteSearch
     * @param null|string $searchKeywordParameters
     * @param null|string $searchCategoryParameters
     * @param null|string $timezone
     * @param null|string $type eg 'website' or 'mobileapp'
     * @param null|string $settings eg 'website' or 'mobileapp'
     * @param int $excludeUnknownUrls
     * @return int    idSite of website created
     */
    public static function createWebsite($dateTime, $ecommerce = 0, $siteName = false, $siteUrl = false,
                                         $siteSearch = 1, $searchKeywordParameters = null,
                                         $searchCategoryParameters = null, $timezone = null, $type = null,
                                         $excludeUnknownUrls = 0)
    {
        if($siteName === false) {
            $siteName = self::DEFAULT_SITE_NAME;
        }
        $idSite = APISitesManager::getInstance()->addSite(
            $siteName,
            $siteUrl === false ? "http://piwik.net/" : $siteUrl,
            $ecommerce,
            $siteSearch, $searchKeywordParameters, $searchCategoryParameters,
            $ips = null,
            $excludedQueryParameters = null,
            $timezone,
            $currency = null,
            $group = null,
            $startDate = null,
            $excludedUserAgents = null,
            $keepURLFragments = null,
            $type,
            $settings = null,
            $excludeUnknownUrls
        );

        // Manually set the website creation date to a day earlier than the earliest day we record stats for
        Db::get()->update(Common::prefixTable("site"),
            array('ts_created' => Date::factory($dateTime)->subDay(1)->getDatetime()),
            "idsite = $idSite"
        );

        // Clear the memory Website cache
        Site::clearCache();
        Cache::deleteCacheWebsiteAttributes($idSite);

        return $idSite;
    }

    /**
     * Returns URL to Piwik root.
     *
     * @return string
     */
    public static function getRootUrl()
    {
        $config = self::getConfig();
        $piwikUrl = $config->tests['http_host'];
        $piwikUri = $config->tests['request_uri'];
        $piwikPort = $config->tests['port'];

        if($piwikUri == '@REQUEST_URI@') {
            throw new Exception("Piwik is mis-configured. Remove (or fix) the 'request_uri' entry below [tests] section in your config.ini.php. ");
        }

        if (!empty($piwikPort)) {
            $piwikUrl = $piwikUrl . ':' . $piwikPort;
        }

        if (strpos($piwikUrl, 'http://') !== 0) {
            $piwikUrl = 'http://' . $piwikUrl . '/';
        }

        $pathBeforeRoot = 'tests';
        // Running from a plugin
        if (strpos($piwikUrl, 'plugins/') !== false) {
            $pathBeforeRoot = 'plugins';
        }

        $testsInPath = strpos($piwikUrl, $pathBeforeRoot . '/');
        if ($testsInPath !== false) {
            $piwikUrl = substr($piwikUrl, 0, $testsInPath);
        }

        // in case force_ssl=1, or assume_secure_protocol=1, is set in tests
        // we don't want to require Travis CI or devs to setup HTTPS on their local machine
        $piwikUrl = str_replace("https://", "http://", $piwikUrl);

        // append REQUEST_URI (eg. when Piwik runs at http://localhost/piwik/)
        if($piwikUri != '/') {
            $piwikUrl .= $piwikUri;
        }

        return $piwikUrl;
    }

    /**
     * Returns URL to the proxy script, used to ensure piwik.php
     * uses the test environment, and allows variable overwriting
     *
     * @return string
     */
    public static function getTrackerUrl()
    {
        return self::getTestRootUrl() . 'piwik.php';
    }

    /**
     * Returns a PiwikTracker object that you can then use to track pages or goals.
     *
     * @param int     $idSite
     * @param string  $dateTime
     * @param boolean $defaultInit If set to true, the tracker object will have default IP, user agent, time, resolution, etc.
     * @param bool    $useLocal
     *
     * @return PiwikTracker
     */
    public static function getTracker($idSite, $dateTime, $defaultInit = true, $useLocal = false)
    {
        if ($useLocal) {
            require_once PIWIK_INCLUDE_PATH . '/tests/LocalTracker.php';
            $t = new Piwik_LocalTracker($idSite, self::getTrackerUrl());
        } else {
            $t = new PiwikTracker($idSite, self::getTrackerUrl());
        }
        $t->setForceVisitDateTime($dateTime);

        if ($defaultInit) {
            $t->setTokenAuth(self::getTokenAuth());
            $t->setIp('156.5.3.2');

            // Optional tracking
            $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)");
            $t->setBrowserLanguage('fr');
            $t->setLocalTime('12:34:06');
            $t->setResolution(1024, 768);
            $t->setBrowserHasCookies(true);
            $t->setPlugins($flash = true, $java = true, $director = false);
        }
        return $t;
    }

    /**
     * Checks that the response is a GIF image as expected.
     * Will fail the test if the response is not the expected GIF
     *
     * @param $response
     */
    public static function checkResponse($response)
    {
        $trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $expectedResponse = base64_decode($trans_gif_64);

        $url = "\n =========================== \n URL was: " . PiwikTracker::$DEBUG_LAST_REQUESTED_URL;
        self::assertEquals($expectedResponse, $response, "Expected GIF beacon, got: <br/>\n"
            . var_export($response, true)
            . "\n If you are stuck, you can enable [Tracker] debug=1; in config.ini.php to get more debug info."
            . "\n\n Also, please try to restart your webserver, and run the test again, this may help!"
            . base64_encode($response)
            . $url
        );
    }

    /**
     * Checks that the response from bulk tracking is a valid JSON
     * string. Will fail the test if JSON status is not success.
     *
     * @param $response
     */
    public static function checkBulkTrackingResponse($response) {
        $data = json_decode($response, true);
        if (!is_array($data) || empty($response)) {
            throw new Exception("Bulk tracking response (".$response.") is not an array: " . var_export($data, true) . "\n");
        }
        if(!isset($data['status'])) {
            throw new Exception("Returned data didn't have a status: " . var_export($data,true));
        }

        self::assertArrayHasKey('status', $data);
        self::assertEquals('success', $data['status'], "expected success, got: " . var_export($data, true));
    }

    public static function makeLocation($city, $region, $country, $lat = null, $long = null, $isp = null)
    {
        return array(LocationProvider::CITY_NAME_KEY    => $city,
                     LocationProvider::REGION_CODE_KEY  => $region,
                     LocationProvider::COUNTRY_CODE_KEY => $country,
                     LocationProvider::LATITUDE_KEY     => $lat,
                     LocationProvider::LONGITUDE_KEY    => $long,
                     LocationProvider::ISP_KEY          => $isp);
    }

    /**
     * Returns the Super User token auth that can be used in tests. Can be used to
     * do bulk tracking.
     *
     * @return string
     */
    public static function getTokenAuth()
    {
        $model = new \Piwik\Plugins\UsersManager\Model();
        $user  = $model->getUser(self::ADMIN_USER_LOGIN);

        return $user['token_auth'];
    }

    public static function createSuperUser($removeExisting = true)
    {
        $passwordHelper = new Password();

        $login    = self::ADMIN_USER_LOGIN;
        $password = $passwordHelper->hash(UsersManager::getPasswordHash(self::ADMIN_USER_PASSWORD));
        $token    = APIUsersManager::getInstance()->createTokenAuth($login);

        $model = new \Piwik\Plugins\UsersManager\Model();
        $user  = $model->getUser($login);

        if ($removeExisting) {
            $model->deleteUserOnly($login);
        }

        if (!empty($user) && !$removeExisting) {
            $token = $user['token_auth'];
        }
        if (empty($user) || $removeExisting) {
            $model->addUser($login, $password, 'hello@example.org', $login, $token, Date::now()->getDatetime());
        } else {
            $model->updateUser($login, $password, 'hello@example.org', $login, $token);
        }

        $setSuperUser = empty($user) || !empty($user['superuser_access']);
        $model->setSuperUserAccess($login, $setSuperUser);

        return $model->getUserByTokenAuth($token);
    }

    /**
     * Create three MAIL and two MOBILE scheduled reports
     *
     * Reports sent by mail can contain PNG graphs when the user specifies it.
     * Depending on the system under test, generated images differ slightly.
     * Because of this discrepancy, PNG graphs are only tested if the system under test
     * has the characteristics described in 'canImagesBeIncludedInScheduledReports'.
     * See tests/README.md for more detail.
     *
     * @see canImagesBeIncludedInScheduledReports
     * @param int $idSite id of website created
     */
    public static function setUpScheduledReports($idSite)
    {
        // retrieve available reports
        $availableReportMetadata = APIScheduledReports::getReportMetadata($idSite, ScheduledReports::EMAIL_TYPE);

        $availableReportIds = array();
        foreach ($availableReportMetadata as $reportMetadata) {
            $availableReportIds[] = $reportMetadata['uniqueId'];
        }

        //@review should we also test evolution graphs?
        // set-up mail report
        APIScheduledReports::getInstance()->addReport(
            $idSite,
            'Mail Test report',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT, // overridden in getApiForTestingScheduledReports()
            $availableReportIds,
            array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY)
        );

        // set-up sms report for one website
        APIScheduledReports::getInstance()->addReport(
            $idSite,
            'SMS Test report, one website',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            MobileMessaging::MOBILE_TYPE,
            MobileMessaging::SMS_FORMAT,
            array("MultiSites_getOne"),
            array("phoneNumbers" => array())
        );

        // set-up sms report for all websites
        APIScheduledReports::getInstance()->addReport(
            $idSite,
            'SMS Test report, all websites',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            MobileMessaging::MOBILE_TYPE,
            MobileMessaging::SMS_FORMAT,
            array("MultiSites_getAll"),
            array("phoneNumbers" => array())
        );

        if (self::canImagesBeIncludedInScheduledReports()) {
            // set-up mail report with images
            APIScheduledReports::getInstance()->addReport(
                $idSite,
                'Mail Test report',
                'day', // overridden in getApiForTestingScheduledReports()
                0,
                ScheduledReports::EMAIL_TYPE,
                ReportRenderer::HTML_FORMAT, // overridden in getApiForTestingScheduledReports()
                $availableReportIds,
                array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_AND_GRAPHS)
            );

            // set-up mail report with one row evolution based png graph
            APIScheduledReports::getInstance()->addReport(
                $idSite,
                'Mail Test report',
                'day',
                0,
                ScheduledReports::EMAIL_TYPE,
                ReportRenderer::HTML_FORMAT,
                array('Actions_getPageTitles'),
                array(
                     ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY,
                     ScheduledReports::EVOLUTION_GRAPH_PARAMETER => 'true',
                )
            );
        }
    }

    /**
     * Return true if system under test has Piwik core team's most common configuration
     */
    public static function canImagesBeIncludedInScheduledReports()
    {
        if(!function_exists('gd_info')) {
            echo "GD is not installed so cannot run these tests. please enable GD in PHP!\n";
            return false;
        }
        $gdInfo = gd_info();
        return
            stristr(php_uname(), self::IMAGES_GENERATED_ONLY_FOR_OS) &&
            strpos( phpversion(), self::IMAGES_GENERATED_FOR_PHP) !== false &&
            strpos( $gdInfo['GD Version'], self::IMAGES_GENERATED_FOR_GD) !== false;
    }

    //public static $geoIpDbUrl = 'http://piwik-team.s3.amazonaws.com/GeoIP.dat.gz';
    public static $geoIpDbUrl = 'http://piwik-team.s3.amazonaws.com/GeoLite2-Country.tar.gz';
    //public static $geoLiteCityDbUrl = 'http://piwik-team.s3.amazonaws.com/GeoLiteCity.dat.gz';
    public static $geoLiteCityDbUrl = 'http://piwik-team.s3.amazonaws.com/GeoLite2-City.tar.gz';

    public static function downloadGeoIpDbs()
    {
        $geoIpOutputDir = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';
        self::downloadAndUnzip(self::$geoIpDbUrl, $geoIpOutputDir, 'GeoLite2-Country.mmdb');
        self::downloadAndUnzip(self::$geoLiteCityDbUrl, $geoIpOutputDir, 'GeoLite2-City.mmdb');
    }

    public static function downloadAndUnzip($url, $outputDir, $filename)
    {
        $bufferSize = 1024 * 1024;

        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }

        $deflatedOut = $outputDir . '/' . $filename;
        $outfileName = $deflatedOut . '.tar.gz';

        if (file_exists($deflatedOut)) {
            $filesize = filesize($deflatedOut);
            if($filesize == 0) {
                throw new Exception("The file $deflatedOut is empty. Suggestion: delete it and try again.");
            }

            self::copyDownloadedGeoIp($deflatedOut, $filename);

            // Valid geoip db found
            return;
        }

        echo "Geoip database $outfileName is not found. Downloading from $url...\n";

        $dump = fopen($url, 'rb');
        if($dump === false){
            throw new Exception('Could not download Geoip database from ' . $url);
        }
        
        $outfile = fopen($outfileName, 'wb');
        if(!$outfile) {
            throw new Exception("Failed to create file $outfileName - please check permissions");
        }

        while (!feof($dump)) {
            fwrite($outfile, fread($dump, $bufferSize), $bufferSize);
        }
        fclose($dump);
        fclose($outfile);

        // unzip the dump
        exec("gunzip -c \"" . $outfileName . "\" > \"$deflatedOut\"", $output, $return);
        if ($return !== 0) {
            Log::info("gunzip failed with file that has following contents:");
            Log::info(file_get_contents($outfile));

            throw new Exception("gunzip failed($return): " . implode("\n", $output));
        }

        self::copyDownloadedGeoIp($deflatedOut, $filename);
    }

    private static function copyDownloadedGeoIp($deflatedOut, $filename)
    {
        $realFileOut = PIWIK_INCLUDE_PATH . '/' . LocationProvider\GeoIp::$geoIPDatabaseDir . '/' . $filename;
        if (!file_exists($realFileOut)) {
            copy($deflatedOut, $realFileOut);
        }
    }

    public static function executeLogImporter($logFile, $options, $allowFailure = false)
    {
        $python = self::getPythonBinary();

        // create the command
        $cmd = $python
            . ' "' . PIWIK_INCLUDE_PATH . '/misc/log-analytics/import_logs.py" ' # script loc
            . '-ddd ' // debug
            . '--url="' . self::getTestRootUrl() . '" ' # proxy so that piwik uses test config files
        ;

        foreach ($options as $name => $values) {
            if (!is_array($values)) {
                $values = array($values);
            }

            foreach ($values as $value) {
                $cmd .= $name;
                if ($value !== false) {
                    $cmd .= '="' . $value . '"';
                }
                $cmd .= ' ';
            }
        }

        $cmd .= '"' . $logFile . '" 2>&1';

        // run the command
        exec($cmd, $output, $result);
        if ($result !== 0
            && !$allowFailure
        ) {
            throw new Exception("log importer failed: " . implode("\n", $output) . "\n\ncommand used: $cmd");
        }

        return $output;
    }

    public static function siteCreated($idSite)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('site') . " WHERE idsite = ?", array($idSite)) != 0;
    }

    public static function goalExists($idSite, $idGoal)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('goal') . " WHERE idgoal = ? AND idsite = ?", array($idGoal, $idSite)) != 0;
    }

    /**
     * Connects to MySQL w/o specifying a database.
     */
    public static function connectWithoutDatabase()
    {
        $dbConfig = self::getConfig()->database;
        $oldDbName = $dbConfig['dbname'];
        $dbConfig['dbname'] = null;

        Db::createDatabaseObject($dbConfig);

        $dbConfig['dbname'] = $oldDbName;
    }

    /**
     * @deprecated
     */
    public static function createAccessInstance()
    {
    }

    public function dropDatabase($dbName = null)
    {
        $dbName = $dbName ?: $this->dbName ?: self::getConfig()->database_tests['dbname'];

        $this->log("Dropping database '$dbName'...");

        $iniReader = new IniReader();
        $config = $iniReader->readFile(PIWIK_INCLUDE_PATH . '/config/config.ini.php');
        $originalDbName = $config['database']['dbname'];
        if ($dbName == $originalDbName
            && $dbName != 'piwik_tests'
        ) { // santity check
            throw new \Exception("Trying to drop original database '$originalDbName'. Something's wrong w/ the tests.");
        }

        try {
            DbHelper::dropDatabase($dbName);
        } catch (Exception $e) {
            printf("Dropping database %s failed: %s\n", $dbName, $e->getMessage());
        }
    }

    public function log($message)
    {
        if ($this->printToScreen) {
            echo $message . "\n";
        }
    }

    // NOTE: since API_Request does sanitization, API methods do not. when calling them, we must
    // sometimes do sanitization ourselves.
    public static function makeXssContent($type, $sanitize = false)
    {
        $result = "<script>$('body').html('$type XSS!');</script>";
        if ($sanitize) {
            $result = Common::sanitizeInputValue($result);
        }
        return $result;
    }

    public static function updateDatabase($force = false)
    {
        Cache::deleteTrackerCache();
        Option::clearCache();

        if ($force) {
            // remove version options to force update
            Option::deleteLike('version%');
            Option::set('version_core', '0.0');
        }

        $updater = new Updater();
        $componentsWithUpdateFile = $updater->getComponentUpdates();
        if (empty($componentsWithUpdateFile)) {
            return false;
        }

        $result = $updater->updateComponents($componentsWithUpdateFile);
        if (!empty($result['coreError'])
            || !empty($result['warnings'])
            || !empty($result['errors'])
        ) {
            throw new \Exception("Failed to update database (errors or warnings found): " . print_r($result, true));
        }

        return $result;
    }

    /**
     * Use this method to return custom container configuration that you want to apply for the tests.
     *
     * @return array
     */
    public function provideContainerConfig()
    {
        return array();
    }

    public function createEnvironmentInstance()
    {
        $this->piwikEnvironment = new Environment($environment = null, $this->extraDefinitions);
        $this->piwikEnvironment->init();
    }

    public function destroyEnvironment()
    {
        if ($this->piwikEnvironment === null) {
            return;
        }

        $this->piwikEnvironment->destroy();
        $this->piwikEnvironment = null;
    }
}
