<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataTable\Manager;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\API;
use Piwik\ReportRenderer;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Translate;
use Piwik\UrlHelper;

require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';

/**
 * Base class for Integration tests.
 *
 * Provides helpers to track data and then call API get* methods to check outputs automatically.
 *
 */
abstract class IntegrationTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Identifies the last language used in an API/Controller call.
     *
     * @var string
     */
    protected $lastLanguage;

    /**
     * Creates a config object for use w/ tests.
     */
    public static function createTestConfig()
    {
        Config::getInstance()->setTestEnvironment();
    }

    /**
     * Sets up access instance.
     */
    public static function createAccessInstance()
    {
        Access::setSingletonInstance(null);
        Access::getInstance();
        Piwik::postEvent('Request.initAuthenticationObject');
    }
    
    /**
     * Connects to MySQL w/o specifying a database.
     */
    public static function connectWithoutDatabase()
    {
        $dbConfig = Config::getInstance()->database;
        $oldDbName = $dbConfig['dbname'];
        $dbConfig['dbname'] = null;

        Db::createDatabaseObject($dbConfig);

        $dbConfig['dbname'] = $oldDbName;
    }

    public static function setUpBeforeClass()
    {
        self::_setUpBeforeClass();

        if (isset(static::$fixture)) {
            self::setupFixture(static::$fixture);
        }
    }

    /**
     * @param bool $installPlugins
     */
    protected static function installAndLoadPlugins($installPlugins)
    {
        $pluginsManager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $pluginsManager->readPluginsDirectory();

        $pluginsManager->loadPlugins($plugins);
        if ($installPlugins)
        {
            $pluginsManager->installLoadedPlugins();
        }
    }

    public static function loadAllPlugins()
    {
        $pluginsManager = \Piwik\Plugin\Manager::getInstance();
        $pluginsToLoad = Config::getInstance()->Plugins['Plugins'];
        $pluginsToLoad[] = 'DevicesDetection';
        
        $pluginsManager->loadPlugins($pluginsToLoad);
    }

    public static function unloadAllPlugins()
    {
        try {
            $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPlugins();
            foreach ($plugins AS $plugin) {
                $plugin->uninstall();
            }
            \Piwik\Plugin\Manager::getInstance()->unloadPlugins();
        } catch (Exception $e) {
        }
    }

    protected static function setupFixture($fixture)
    {
        try {
            $fixture->setUp();
        } catch (Exception $e) {
            self::fail("Failed to setup fixture: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    protected static function teardownFixture($fixture)
    {
        if (isset(static::$fixture)) {
            self::tearDownFixture(static::$fixture);
        }

        $fixture->tearDown();
    }

    /**
     * setupBeforeClass' implementation. Can be called by derived classes in case
     * they need to do some custom setup procedure.
     */
    public static function _setUpBeforeClass($dbName = false, $createEmptyDatabase = true, $createConfig = true, $installPlugins = null)
    {
        try {
            \Piwik\SettingsPiwik::$piwikUrlCache = '';

            if ($createConfig) {
                static::createTestConfig();
            }

            if ($dbName === false) // must be after test config is created
            {
                $dbName = Config::getInstance()->database['dbname'];
            }

            self::connectWithoutDatabase();
            if ($createEmptyDatabase) {
                DbHelper::dropDatabase();
            }
            DbHelper::createDatabase($dbName);
            DbHelper::disconnectDatabase();

            // reconnect once we're sure the database exists
            Config::getInstance()->database['dbname'] = $dbName;
            Db::createDatabaseObject();

            DbHelper::createTables();

            \Piwik\Plugin\Manager::getInstance()->loadPlugins(array());
        } catch (Exception $e) {
            self::fail("TEST INITIALIZATION FAILED: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }

        include "DataFiles/SearchEngines.php";
        include "DataFiles/Languages.php";
        include "DataFiles/Countries.php";
        include "DataFiles/Currencies.php";
        include "DataFiles/LanguageToCountry.php";
        include "DataFiles/Providers.php";
        
        static::createAccessInstance();

        // We need to be SU to create websites for tests
        Piwik::setUserIsSuperUser();

        Cache::deleteTrackerCache();
        if ($installPlugins === null) $installPlugins = $createEmptyDatabase;
        self::installAndLoadPlugins( $installPlugins);


        $_GET = $_REQUEST = array();
        $_SERVER['HTTP_REFERER'] = '';

        // Make sure translations are loaded to check messages in English
        Translate::reloadLanguage('en');
        API::getInstance()->setLanguageForUser('superUserLogin', 'en');

        // List of Modules, or Module.Method that should not be called as part of the XML output compare
        // Usually these modules either return random changing data, or are already tested in specific unit tests.
        self::setApiNotToCall(self::$defaultApiNotToCall);
        self::setApiToCall(array());
        
        FakeAccess::$superUserLogin = 'superUserLogin';
        
        \Piwik\SettingsPiwik::$cachedKnownSegmentsToArchive = null;
        \Piwik\CacheFile::$invalidateOpCacheBeforeRead = true;
    }

    public static function tearDownAfterClass()
    {
        self::_tearDownAfterClass();
    }

    public static function _tearDownAfterClass($dropDatabase = true)
    {
        \Piwik\SettingsPiwik::$piwikUrlCache = null;
        IntegrationTestCase::unloadAllPlugins();

        if ($dropDatabase) {
            DbHelper::dropDatabase();
        }
        Manager::getInstance()->deleteAll();
        Option::clearCache();
        Site::clearCache();
        Cache::deleteTrackerCache();
        Config::getInstance()->clear();
        ArchiveTableCreator::clear();
        \Piwik\Plugins\ScheduledReports\API::$cache = array();
        \Piwik\Registry::unsetInstance();

        $_GET = $_REQUEST = array();
        Translate::unloadEnglishTranslation();
    }

    public function setUp()
    {
        // Make sure the browser running the test does not influence the Country detection code
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';

        $this->changeLanguage('en');
    }

    protected static $apiToCall = array();
    protected static $apiNotToCall = array();

    public static $defaultApiNotToCall = array(
        'LanguagesManager',
        'DBStats',
        'Dashboard',
        'UsersManager',
        'SitesManager',
        'ExampleUI',
        'Overlay',
        'Live',
        'SEO',
        'ExampleAPI',
        'ScheduledReports',
        'MobileMessaging',
        'Transitions',
        'API',
        'ImageGraph',
        'Annotations',
        'SegmentEditor',
        'UserCountry.getLocationFromIP',
        'Dashboard',
    );

    const DEFAULT_USER_PASSWORD = 'nopass';

    protected $missingExpectedFiles = array();
    protected $comparisonFailures = array();

    /**
     * Forces the test to only call and fetch XML for the specified plugins,
     * or exact API methods.
     * If not called, all default tests will be executed.
     *
     * @param array $apiToCall array( 'ExampleAPI', 'Plugin.getData' )
     *
     * @throws Exception
     * @return void
     */
    protected static function setApiToCall($apiToCall)
    {
        if (func_num_args() != 1) {
            throw new Exception('setApiToCall expects an array');
        }
        if (!is_array($apiToCall)) {
            $apiToCall = array($apiToCall);
        }
        self::$apiToCall = $apiToCall;
    }

    /**
     * Sets a list of API methods to not call during the test
     *
     * @param string $apiNotToCall eg. 'ExampleAPI.getPiwikVersion'
     *
     * @return void
     */
    protected static function setApiNotToCall($apiNotToCall)
    {
        if (!is_array($apiNotToCall)) {
            $apiNotToCall = array($apiNotToCall);
        }
        self::$apiNotToCall = $apiNotToCall;
    }

    protected function alertWhenImagesExcludedFromTests()
    {
        if (!Test_Piwik_BaseFixture::canImagesBeIncludedInScheduledReports()) {
            $this->markTestSkipped(
                'Scheduled reports generated during integration tests will not contain the image graphs. ' .
                    'For tests to generate images, use a machine with the following specifications : ' .
                    'OS = '.Test_Piwik_BaseFixture::IMAGES_GENERATED_ONLY_FOR_OS.', PHP = '.Test_Piwik_BaseFixture::IMAGES_GENERATED_FOR_PHP .
                    ' and GD = ' . Test_Piwik_BaseFixture::IMAGES_GENERATED_FOR_GD
            );
        }
    }

    /**
     * Return 4 Api Urls for testing scheduled reports :
     * - one in HTML format with all available reports
     * - one in PDF format with all available reports
     * - two in SMS (one for each available report: MultiSites.getOne & MultiSites.getAll)
     *
     * @param string $dateTime eg '2010-01-01 12:34:56'
     * @param string $period eg 'day', 'week', 'month', 'year'
     * @return array
     */
    protected static function getApiForTestingScheduledReports($dateTime, $period)
    {
        $apiCalls = array();

        // HTML Scheduled Report
        array_push(
            $apiCalls,
            array(
                 'ScheduledReports.generateReport',
                 array(
                     'testSuffix'             => '_scheduled_report_in_html_tables_only',
                     'date'                   => $dateTime,
                     'periods'                => array($period),
                     'format'                 => 'original',
                     'fileExtension'          => 'html',
                     'otherRequestParameters' => array(
                         'idReport'     => 1,
                         'reportFormat' => ReportRenderer::HTML_FORMAT,
                         'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                     )
                 )
            )
        );

        // We run this particular test on one PHP version only (which should run on Travis CI + Most devs)
        if (stristr(phpversion(), '5.4') !== false) {
            // PDF Scheduled Report
            // tests/PHPUnit/Integration/processed/test_ecommerceOrderWithItems_scheduled_report_in_pdf_tables_only__ScheduledReports.generateReport_week.original.pdf
            array_push(
                $apiCalls,
                array(
                     'ScheduledReports.generateReport',
                     array(
                         'testSuffix'             => '_scheduled_report_in_pdf_tables_only',
                         'date'                   => $dateTime,
                         'periods'                => array($period),
                         'format'                 => 'original',
                         'fileExtension'          => 'pdf',
                         'otherRequestParameters' => array(
                             'idReport'     => 1,
                             'reportFormat' => ReportRenderer::PDF_FORMAT,
                             'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                         )
                     )
                )
            );
        }

        // SMS Scheduled Report, one site
        array_push(
            $apiCalls,
            array(
                 'ScheduledReports.generateReport',
                 array(
                     'testSuffix'             => '_scheduled_report_via_sms_one_site',
                     'date'                   => $dateTime,
                     'periods'                => array($period),
                     'format'                 => 'original',
                     'fileExtension'          => 'sms.txt',
                     'otherRequestParameters' => array(
                         'idReport'   => 2,
                         'outputType' => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                     )
                 )
            )
        );

        // SMS Scheduled Report, all sites
        array_push(
            $apiCalls,
            array(
                 'ScheduledReports.generateReport',
                 array(
                     'testSuffix'             => '_scheduled_report_via_sms_all_sites',
                     'date'                   => $dateTime,
                     'periods'                => array($period),
                     'format'                 => 'original',
                     'fileExtension'          => 'sms.txt',
                     'otherRequestParameters' => array(
                         'idReport'   => 3,
                         'outputType' => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                     )
                 )
            )
        );

        if (Test_Piwik_BaseFixture::canImagesBeIncludedInScheduledReports()) {
            // HTML Scheduled Report with images
            array_push(
                $apiCalls,
                array(
                     'ScheduledReports.generateReport',
                     array(
                         'testSuffix'             => '_scheduled_report_in_html_tables_and_graph',
                         'date'                   => $dateTime,
                         'periods'                => array($period),
                         'format'                 => 'original',
                         'fileExtension'          => 'html',
                         'otherRequestParameters' => array(
                             'idReport'     => 4,
                             'reportFormat' => ReportRenderer::HTML_FORMAT,
                             'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                         )
                     )
                )
            );

            // mail report with one row evolution based png graph
            array_push(
                $apiCalls,
                array(
                     'ScheduledReports.generateReport',
                     array(
                         'testSuffix'             => '_scheduled_report_in_html_row_evolution_graph',
                         'date'                   => $dateTime,
                         'periods'                => array($period),
                         'format'                 => 'original',
                         'fileExtension'          => 'html',
                         'otherRequestParameters' => array(
                             'idReport'     => 5,
                             'outputType'   => \Piwik\Plugins\ScheduledReports\API::OUTPUT_RETURN
                         )
                     )
                )
            );
        }

        return $apiCalls;
    }

    /**
     * Given a list of default parameters to set, returns the URLs of APIs to call
     * If any API was specified in setApiToCall() we ensure only these are tested.
     * If any API is set as excluded (see list below) then it will be ignored.
     *
     * @param array $parametersToSet Parameters to set in api call
     * @param array $formats         Array of 'format' to fetch from API
     * @param array $periods         Array of 'period' to query API
     * @param bool  $supertableApi
     * @param bool  $setDateLastN    If set to true, the 'date' parameter will be rewritten to query instead a range of dates, rather than one period only.
     * @param bool|string $language        2 letter language code, defaults to default piwik language
     * @param bool|string $fileExtension
     *
     * @throws Exception
     *
     * @return array of API URLs query strings
     */
    protected function generateUrlsApi($parametersToSet, $formats, $periods, $supertableApi = false, $setDateLastN = false, $language = false, $fileExtension = false)
    {
        // Get the URLs to query against the API for all functions starting with get*
        $skipped = $requestUrls = array();
        $apiMetadata = new DocumentationGenerator;
        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach ($info as $methodName => $infoMethod) {
                $apiId = $moduleName . '.' . $methodName;

                // If Api to test were set, we only test these
                if (!empty(self::$apiToCall)
                    && in_array($moduleName, self::$apiToCall) === false
                    && in_array($apiId, self::$apiToCall) === false
                ) {
//	                echo "Skipped $apiId... \n";
                    $skipped[] = $apiId;
                    continue;
                } // Excluded modules from test
                elseif (
                    ((strpos($methodName, 'get') !== 0 && $methodName != 'generateReport')
                        || in_array($moduleName, self::$apiNotToCall) === true
                        || in_array($apiId, self::$apiNotToCall) === true
                        || $methodName == 'getLogoUrl'
                        || $methodName == 'getSVGLogoUrl'
                        || $methodName == 'hasSVGLogo'
                        || $methodName == 'getHeaderLogoUrl'
                    )
                ) {
//	                echo "Skipped $apiId... \n";
                    $skipped[] = $apiId;
                    continue;
                }

                foreach ($periods as $period) {
                    $parametersToSet['period'] = $period;

                    // If date must be a date range, we process this date range by adding 6 periods to it
                    if ($setDateLastN) {
                        if (!isset($parametersToSet['dateRewriteBackup'])) {
                            $parametersToSet['dateRewriteBackup'] = $parametersToSet['date'];
                        }

                        $lastCount = (int)$setDateLastN;
                        if ($setDateLastN === true) {
                            $lastCount = 6;
                        }
                        $firstDate = $parametersToSet['dateRewriteBackup'];
                        $secondDate = date('Y-m-d', strtotime("+$lastCount " . $period . "s", strtotime($firstDate)));
                        $parametersToSet['date'] = $firstDate . ',' . $secondDate;
                    }

                    // Set response language
                    if ($language !== false) {
                        $parametersToSet['language'] = $language;
                    }

                    // set idSubtable if subtable API is set
                    if ($supertableApi !== false) {
                        $request = new Request(array(
                                                              'module'    => 'API',
                                                              'method'    => $supertableApi,
                                                              'idSite'    => $parametersToSet['idSite'],
                                                              'period'    => $parametersToSet['period'],
                                                              'date'      => $parametersToSet['date'],
                                                              'format'    => 'php',
                                                              'serialize' => 0,
                                                         ));

                        // find first row w/ subtable
                        foreach ($request->process() as $row) {
                            if (isset($row['idsubdatatable'])) {
                                $parametersToSet['idSubtable'] = $row['idsubdatatable'];
                                break;
                            }
                        }

                        // if no subtable found, throw
                        if (!isset($parametersToSet['idSubtable'])) {
                            throw new Exception(
                                "Cannot find subtable to load for $apiId in $supertableApi.");
                        }
                    }

                    // Generate for each specified format
                    foreach ($formats as $format) {
                        $parametersToSet['format'] = $format;
                        $parametersToSet['hideIdSubDatable'] = 1;
                        $parametersToSet['serialize'] = 1;

                        $exampleUrl = $apiMetadata->getExampleUrl($class, $methodName, $parametersToSet);
                        
                        if ($exampleUrl === false) {
                            $skipped[] = $apiId;
                            continue;
                        }

                        // Remove the first ? in the query string
                        $exampleUrl = substr($exampleUrl, 1);
                        $apiRequestId = $apiId;
                        if (strpos($exampleUrl, 'period=') !== false) {
                            $apiRequestId .= '_' . $period;
                        }

                        $apiRequestId .= '.' . $format;

                        if ($fileExtension) {
                            $apiRequestId .= '.' . $fileExtension;
                        }

                        $requestUrls[$apiRequestId] = $exampleUrl;
                    }
                }
            }
        }
        return $requestUrls;
    }

    /**
     * Will return all api urls for the given data
     *
     * @param string|array $formats        String or array of formats to fetch from API
     * @param int|bool $idSite         Id site
     * @param string|bool $dateTime       Date time string of reports to request
     * @param array|bool|string $periods        String or array of strings of periods (day, week, month, year)
     * @param bool $setDateLastN   When set to true, 'date' parameter passed to API request will be rewritten to query a range of dates rather than 1 date only
     * @param string|bool $language       2 letter language code to request data in
     * @param string|bool $segment        Custom Segment to query the data  for
     * @param string|bool $visitorId      Only used for Live! API testing
     * @param bool $abandonedCarts Only used in Goals API testing
     * @param bool $idGoal
     * @param bool $apiModule
     * @param bool $apiAction
     * @param array $otherRequestParameters
     * @param array|bool $supertableApi
     * @param array|bool $fileExtension
     *
     * @return array
     */
    protected function _generateApiUrls($formats = 'xml', $idSite = false, $dateTime = false, $periods = false,
                                        $setDateLastN = false, $language = false, $segment = false, $visitorId = false,
                                        $abandonedCarts = false, $idGoal = false, $apiModule = false, $apiAction = false,
                                        $otherRequestParameters = array(), $supertableApi = false, $fileExtension = false)
    {
        list($pathProcessed, $pathExpected) = self::getProcessedAndExpectedDirs();

        if ($periods === false) {
            $periods = 'day';
        }
        if (!is_array($periods)) {
            $periods = array($periods);
        }
        if (!is_array($formats)) {
            $formats = array($formats);
        }
        if (!is_writable($pathProcessed)) {
            $this->fail('To run the tests, you need to give write permissions to the following directory (create it if it doesn\'t exist).<code><br/>mkdir ' . $pathProcessed . '<br/>chmod 777 ' . $pathProcessed . '</code><br/>');
        }
        $parametersToSet = array(
            'idSite'         => $idSite,
            'date'           => ($periods == array('range') || strpos($dateTime, ',') !== false) ?
                                    $dateTime : date('Y-m-d', strtotime($dateTime)),
            'expanded'       => '1',
            'piwikUrl'       => 'http://example.org/piwik/',
            // Used in getKeywordsForPageUrl
            'url'            => 'http://example.org/store/purchase.htm',

            // Used in Actions.getPageUrl, .getDownload, etc.
            // tied to Main.test.php doTest_oneVisitorTwoVisits
            // will need refactoring when these same API functions are tested in a new function
            'downloadUrl'    => 'http://piwik.org/path/again/latest.zip?phpsessid=this is ignored when searching',
            'outlinkUrl'     => 'http://dev.piwik.org/svn',
            'pageUrl'        => 'http://example.org/index.htm?sessionid=this is also ignored by default',
            'pageName'       => ' Checkout / Purchasing... ',

            // do not show the millisec timer in response or tests would always fail as value is changing
            'showTimer'      => 0,

            'language'       => $language ? $language : 'en',
            'abandonedCarts' => $abandonedCarts ? 1 : 0,
            'idSites'        => $idSite,
        );
        $parametersToSet = array_merge($parametersToSet, $otherRequestParameters);
        if (!empty($visitorId)) {
            $parametersToSet['visitorId'] = $visitorId;
        }
        if (!empty($apiModule)) {
            $parametersToSet['apiModule'] = $apiModule;
        }
        if (!empty($apiAction)) {
            $parametersToSet['apiAction'] = $apiAction;
        }
        if (!empty($segment)) {
            $parametersToSet['segment'] = urlencode($segment);
        }
        if ($idGoal !== false) {
            $parametersToSet['idGoal'] = $idGoal;
        }

        $requestUrls = $this->generateUrlsApi($parametersToSet, $formats, $periods, $supertableApi, $setDateLastN, $language, $fileExtension);

        $this->checkEnoughUrlsAreTested($requestUrls);

        return $requestUrls;
    }

    protected function checkEnoughUrlsAreTested($requestUrls)
    {
        $countUrls = count($requestUrls);
        $approximateCountApiToCall = count(self::$apiToCall);
        if (empty($requestUrls)
            || $approximateCountApiToCall > $countUrls
        ) {
            throw new Exception("Only generated $countUrls API calls to test but was expecting more for this test.\n" .
                    "Want to test APIs: " . implode(", ", self::$apiToCall) . ")\n" .
                    "But only generated these URLs: \n" . implode("\n", $requestUrls) . ")\n"
            );
        }
    }

    protected function _testApiUrl($testName, $apiId, $requestUrl, $compareAgainst)
    {
        $isTestLogImportReverseChronological = strpos($testName, 'ImportedInRandomOrderTest') === false;
        $isLiveMustDeleteDates = (strpos($requestUrl, 'Live.getLastVisits') !== false
                                  || strpos($requestUrl, 'Live.getVisitorProfile') !== false)
                                // except for that particular test that we care about dates!
                                && $isTestLogImportReverseChronological;

        $request = new Request($requestUrl);
        $dateTime = Common::getRequestVar('date', '', 'string', UrlHelper::getArrayFromQueryString($requestUrl));

        list($processedFilePath, $expectedFilePath) =
            $this->getProcessedAndExpectedPaths($testName, $apiId, $format = null, $compareAgainst);

        // Cast as string is important. For example when calling
        // with format=original, objects or php arrays can be returned.
        // we also hide errors to prevent the 'headers already sent' in the ResponseBuilder (which sends Excel headers multiple times eg.)
        $response = (string)$request->process();

        if ($isLiveMustDeleteDates) {
            $response = $this->removeAllLiveDatesFromXml($response);
        }

        // normalize date markups and document ID in pdf files :
        // - /LastModified (D:20120820204023+00'00')
        // - /CreationDate (D:20120820202226+00'00')
        // - /ModDate (D:20120820202226+00'00')
        // - /M (D:20120820202226+00'00')
        // - /ID [ <0f5cc387dc28c0e13e682197f485fe65> <0f5cc387dc28c0e13e682197f485fe65> ]
        $response = preg_replace('/\(D:[0-9]{14}/', '(D:19700101000000', $response);
        $response = preg_replace('/\/ID \[ <.*> ]/', '', $response);

        file_put_contents($processedFilePath, $response);

        $expected = $this->loadExpectedFile($expectedFilePath);
        if (empty($expected)) {
            print("The expected file is not found at '$expectedFilePath'. The Processed response was:");
            print("\n----------------------------\n\n");
            var_dump($response);
            print("\n----------------------------\n");
            return;
        }

        // @todo This should not vary between systems AFAIK... "idsubdatatable can differ"
        $expected = $this->removeXmlElement($expected, 'idsubdatatable', $testNotSmallAfter = false);
        $response = $this->removeXmlElement($response, 'idsubdatatable', $testNotSmallAfter = false);

        if ($isLiveMustDeleteDates) {
            $expected = $this->removeAllLiveDatesFromXml($expected);
        } // If date=lastN the <prettyDate> element will change each day, we remove XML element before comparison
        elseif (strpos($dateTime, 'last') !== false
            || strpos($dateTime, 'today') !== false
            || strpos($dateTime, 'now') !== false
        ) {
            if (strpos($requestUrl, 'API.getProcessedReport') !== false) {
                $expected = $this->removePrettyDateFromXml($expected);
                $response = $this->removePrettyDateFromXml($response);
            }

            // avoid build failure when running just before midnight, generating visits in the future
            // Note: disabled when 'segment' is a hack:
            //       instead we should only remove these fields for the specific test that was failing.
            if(strpos($requestUrl, 'segment') === false) {
                $expected = $this->removeXmlElement($expected, 'sum_daily_nb_uniq_visitors');
                $response = $this->removeXmlElement($response, 'sum_daily_nb_uniq_visitors');
                $expected = $this->removeXmlElement($expected, 'nb_visits_converted');
                $response = $this->removeXmlElement($response, 'nb_visits_converted');
            }

            $expected = $this->removeXmlElement($expected, 'visitServerHour');
            $response = $this->removeXmlElement($response, 'visitServerHour');

            if (strpos($requestUrl, 'date=') !== false) {
                $regex = "/date=[-0-9,%Ca-z]+/"; // need to remove %2C which is encoded ,
                $expected = preg_replace($regex, 'date=', $expected);
                $response = preg_replace($regex, 'date=', $response);
            }
        }

        // if idSubtable is in request URL, make sure idSubtable values are not in any urls
        if (strpos($requestUrl, 'idSubtable=') !== false) {
            $regex = "/idSubtable=[0-9]+/";
            $expected = preg_replace($regex, 'idSubtable=', $expected);
            $response = preg_replace($regex, 'idSubtable=', $response);
        }

        // Do not test for TRUNCATE(SUM()) returning .00 on mysqli since this is not working
        // http://bugs.php.net/bug.php?id=54508
        $expected = str_replace('.000000</l', '</l', $expected); //lat/long
        $response = str_replace('.000000</l', '</l', $response); //lat/long
        $expected = str_replace('.00</revenue>', '</revenue>', $expected);
        $response = str_replace('.00</revenue>', '</revenue>', $response);
        $response = str_replace('.1</revenue>', '</revenue>', $response);
        $expected = str_replace('.1</revenue>', '</revenue>', $expected);
        $expected = str_replace('.11</revenue>', '</revenue>', $expected);
        $response = str_replace('.11</revenue>', '</revenue>', $response);

        try {
            if (strpos($requestUrl, 'format=xml') !== false) {
                $this->assertXmlStringEqualsXmlString($expected, $response, "Differences with expected in: $processedFilePath");
            } else {
                $this->assertEquals(strlen($expected), strlen($response), "Differences with expected in: $processedFilePath");
                $this->assertEquals($expected, $response, "Differences with expected in: $processedFilePath");
            }

            if (trim($response) == trim($expected)) {
                file_put_contents($processedFilePath, $response);
            }
        } catch (Exception $ex) {
            $this->comparisonFailures[] = $ex;
        }
    }

    protected function removeAllLiveDatesFromXml($input)
    {
        $toRemove = array(
            'serverDate',
            'firstActionTimestamp',
            'lastActionTimestamp',
            'lastActionDateTime',
            'serverTimestamp',
            'serverTimePretty',
            'serverDatePretty',
            'serverDatePrettyFirstAction',
            'serverTimePrettyFirstAction',
            'goalTimePretty',
            'serverTimePretty',
            'visitorId',
            'nextVisitorId',
            'previousVisitorId',
            'visitServerHour',
            'date',
            'prettyDate',
            'serverDateTimePrettyFirstAction'
        );
        foreach ($toRemove as $xml) {
            $input = $this->removeXmlElement($input, $xml);
        }
        return $input;
    }

    protected function removePrettyDateFromXml($input)
    {
        return $this->removeXmlElement($input, 'prettyDate');
    }

    protected function removeXmlElement($input, $xmlElement, $testNotSmallAfter = true)
    {
        // Only raise error if there was some data before
        $testNotSmallAfter = strlen($input > 100) && $testNotSmallAfter;

        $input = preg_replace('/(<' . $xmlElement . '>.+?<\/' . $xmlElement . '>)/', '', $input);
        //check we didn't delete the whole string
        if ($testNotSmallAfter) {
            $this->assertTrue(strlen($input) > 100);
        }
        return $input;
    }

    protected static function getProcessedAndExpectedDirs()
    {
        $path = self::getPathToTestDirectory();
        return array($path . '/processed/', $path . '/expected/');
    }

    private function getProcessedAndExpectedPaths($testName, $testId, $format = null, $compareAgainst = false)
    {
        $filenameSuffix = '__' . $testId;
        if ($format) {
            $filenameSuffix .= ".$format";
        }

        $processedFilename = $testName . $filenameSuffix;
        $expectedFilename = ($compareAgainst ?: $testName) . $filenameSuffix;

        list($processedDir, $expectedDir) = self::getProcessedAndExpectedDirs();

        return array($processedDir . $processedFilename, $expectedDir . $expectedFilename);
    }

    private function loadExpectedFile($filePath)
    {
        $result = @file_get_contents($filePath);
        if (empty($result)) {
            $expectedDir = dirname($filePath);
            $this->missingExpectedFiles[] = $filePath;
            return null;
        }
        return $result;
    }

    /**
     * Returns an array describing the API methods to call & compare with
     * expected output.
     *
     * The returned array must be of the following format:
     * <code>
     * array(
     *     array('SomeAPI.method', array('testOption1' => 'value1', 'testOption2' => 'value2'),
     *     array(array('SomeAPI.method', 'SomeOtherAPI.method'), array(...)),
     *     .
     *     .
     *     .
     * )
     * </code>
     *
     * Valid test options:
     * <ul>
     *   <li><b>testSuffix</b> The suffix added to the test name. Helps determine
     *   the filename of the expected output.</li>
     *   <li><b>format</b> The desired format of the output. Defaults to 'xml'.</li>
     *   <li><b>idSite</b> The id of the website to get data for.</li>
     *   <li><b>date</b> The date to get data for.</li>
     *   <li><b>periods</b> The period or periods to get data for. Can be an array.</li>
     *   <li><b>setDateLastN</b> Flag describing whether to query for a set of
     *   dates or not.</li>
     *   <li><b>language</b> The language to use.</li>
     *   <li><b>segment</b> The segment to use.</li>
     *   <li><b>visitorId</b> The visitor ID to use.</li>
     *   <li><b>abandonedCarts</b> Whether to look for abandoned carts or not.</li>
     *   <li><b>idGoal</b> The goal ID to use.</li>
     *   <li><b>apiModule</b> The value to use in the apiModule request parameter.</li>
     *   <li><b>apiAction</b> The value to use in the apiAction request parameter.</li>
     *   <li><b>otherRequestParameters</b> An array of extra request parameters to use.</li>
     *   <li><b>disableArchiving</b> Disable archiving before running tests.</li>
     * </ul>
     *
     * All test options are optional, except 'idSite' & 'date'.
     */
    public function getApiForTesting()
    {
        return array();
    }

    /**
     * Gets the string prefix used in the name of the expected/processed output files.
     */
    public static function getOutputPrefix()
    {
        return str_replace('Test_Piwik_Integration_', '', get_called_class());
    }

    protected function _setCallableApi($api)
    {
        if ($api == 'all') {
            self::setApiToCall(array());
            self::setApiNotToCall(static::$defaultApiNotToCall);
        } else {
            if (!is_array($api)) {
                $api = array($api);
            }

            self::setApiToCall($api);

            if (!in_array('UserCountry.getLocationFromIP', $api)) {
                self::setApiNotToCall(array(
                                           'API.getPiwikVersion',
                                           'UserCountry.getLocationFromIP'
                                      ));
            } else {
                self::setApiNotToCall(array());
            }
        }
    }

    /**
     * Runs API tests.
     */
    protected function runApiTests($api, $params)
    {
        $testName = 'test_' . static::getOutputPrefix();
        $this->missingExpectedFiles = array();
        $this->comparisonFailures = array();

        $this->_setCallableApi($api);

        if (isset($params['disableArchiving']) && $params['disableArchiving'] === true) {
            Rules::$archivingDisabledByTests = true;
            Config::getInstance()->General['browser_archiving_disabled_enforce'] = 1;
        } else {
            Rules::$archivingDisabledByTests = false;
            Config::getInstance()->General['browser_archiving_disabled_enforce'] = 0;
        }

        if (isset($params['language'])) {
            $this->changeLanguage($params['language']);
        }

        $testSuffix = isset($params['testSuffix']) ? $params['testSuffix'] : '';

        $requestUrls = $this->_generateApiUrls(
            isset($params['format']) ? $params['format'] : 'xml',
            isset($params['idSite']) ? $params['idSite'] : false,
            isset($params['date']) ? $params['date'] : false,
            isset($params['periods']) ? $params['periods'] : (isset($params['period']) ? $params['period'] : false),
            isset($params['setDateLastN']) ? $params['setDateLastN'] : false,
            isset($params['language']) ? $params['language'] : false,
            isset($params['segment']) ? $params['segment'] : false,
            isset($params['visitorId']) ? $params['visitorId'] : false,
            isset($params['abandonedCarts']) ? $params['abandonedCarts'] : false,
            isset($params['idGoal']) ? $params['idGoal'] : false,
            isset($params['apiModule']) ? $params['apiModule'] : false,
            isset($params['apiAction']) ? $params['apiAction'] : false,
            isset($params['otherRequestParameters']) ? $params['otherRequestParameters'] : array(),
            isset($params['supertableApi']) ? $params['supertableApi'] : false,
            isset($params['fileExtension']) ? $params['fileExtension'] : false);

        $compareAgainst = isset($params['compareAgainst']) ? ('test_' . $params['compareAgainst']) : false;
        foreach ($requestUrls as $apiId => $requestUrl) {
            $this->_testApiUrl($testName . $testSuffix, $apiId, $requestUrl, $compareAgainst);
        }

        // change the language back to en
        if ($this->lastLanguage != 'en') {
            $this->changeLanguage('en');
        }

        if (!empty($this->missingExpectedFiles)) {
            $expectedDir = dirname(reset($this->missingExpectedFiles));
            $this->fail(" ERROR: Could not find expected API output '"
                . implode("', '", $this->missingExpectedFiles)
                . "'. For new tests, to pass the test, you can copy files from the processed/ directory into"
                . " $expectedDir  after checking that the output is valid. %s ");
        }

        // Display as one error all sub-failures
        if (!empty($this->comparisonFailures)) {
            $messages = '';
            $i = 1;
            foreach ($this->comparisonFailures as $failure) {
                $msg = $failure->getMessage();
                $msg = strtok($msg, "\n");
                $messages .= "\n#" . $i++ . ": " . $msg;
            }
            $messages .= " \n ";
            print($messages);
            $first = reset($this->comparisonFailures);
            throw $first;
        }
    }

    /**
     * changing the language within one request is a bit fancy
     * in order to keep the core clean, we need a little hack here
     *
     * @param string $langId
     */
    protected function changeLanguage($langId)
    {
        if ($this->lastLanguage != $langId) {
            $_GET['language'] = $langId;
            Translate::reset();
            Translate::reloadLanguage($langId);
        }

        $this->lastLanguage = $langId;
    }

    /**
     * Path where expected/processed output files are stored.
     */
    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Integration';
    }

    /**
     * Returns an array associating table names w/ lists of row data.
     *
     * @return array
     */
    protected static function getDbTablesWithData()
    {
        $result = array();
        foreach (DbHelper::getTablesInstalled() as $tableName) {
            $result[$tableName] = Db::fetchAll("SELECT * FROM $tableName");
        }
        return $result;
    }

    /**
     * Truncates all tables then inserts the data in $tables into each
     * mapped table.
     *
     * @param array $tables Array mapping table names with arrays of row data.
     */
    protected static function restoreDbTables($tables)
    {
        // truncate existing tables
        DbHelper::truncateAllTables();

        // insert data
        $existingTables = DbHelper::getTablesInstalled();
        foreach ($tables as $table => $rows) {
            // create table if it's an archive table
            if (strpos($table, 'archive_') !== false && !in_array($table, $existingTables)) {
                $tableType = strpos($table, 'archive_numeric') !== false ? 'archive_numeric' : 'archive_blob';

                $createSql = DbHelper::getTableCreateSql($tableType);
                $createSql = str_replace(Common::prefixTable($tableType), $table, $createSql);
                Db::query($createSql);
            }

            if (empty($rows)) {
                continue;
            }

            $rowsSql = array();
            $bind = array();
            foreach ($rows as $row) {
                $values = array();
                foreach ($row as $name => $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else if (is_numeric($value)) {
                        $values[] = $value;
                    } else if (!ctype_print($value)) {
                        $values[] = "x'" . bin2hex(substr($value, 1)) . "'";
                    } else {
                        $values[] = "?";
                        $bind[] = $value;
                    }
                }

                $rowsSql[] = "(" . implode(',', $values) . ")";
            }

            $sql = "INSERT INTO $table VALUES " . implode(',', $rowsSql);
            Db::query($sql, $bind);
        }
    }

    /**
     * Drops all archive tables.
     */
    public static function deleteArchiveTables()
    {
        foreach (ArchiveTableCreator::getTablesArchivesInstalled() as $table) {
            Db::query("DROP TABLE IF EXISTS $table");
        }

        ArchiveTableCreator::refreshTableList($forceReload = true);
    }

}
