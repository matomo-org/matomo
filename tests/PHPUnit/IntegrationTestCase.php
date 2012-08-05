<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
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

    public static function setUpBeforeClass()
    {
        try {
            Piwik::createConfigObject();
            Piwik_Config::getInstance()->setTestEnvironment();

            $dbConfig = Piwik_Config::getInstance()->database;
            $dbName = $dbConfig['dbname'];
            $dbConfig['dbname'] = null;

            Piwik::createDatabaseObject($dbConfig);

            Piwik::dropDatabase();
            Piwik::createDatabase($dbName);
            Piwik::disconnectDatabase();

            Piwik::createDatabaseObject();
            Piwik::createTables();
            Piwik::createLogObject();

            Piwik_PluginsManager::getInstance()->loadPlugins(array());

        } catch(Exception $e) {
            self::fail("TEST INITIALIZATION FAILED: " .$e->getMessage());
        }

        include "DataFiles/SearchEngines.php";
        include "DataFiles/Languages.php";
        include "DataFiles/Countries.php";
        include "DataFiles/Currencies.php";
        include "DataFiles/LanguageToCountry.php";

        Piwik::createAccessObject();
        Piwik_PostEvent('FrontController.initAuthenticationObject');

        // We need to be SU to create websites for tests
        Piwik::setUserIsSuperUser();

        // Load and install plugins
        $pluginsManager = Piwik_PluginsManager::getInstance();
        $plugins = Piwik_Config::getInstance()->Plugins['Plugins'];

        $pluginsManager->loadPlugins( $plugins );
        $pluginsManager->installLoadedPlugins();

        $_GET = $_REQUEST = array();
        $_SERVER['HTTP_REFERER'] = '';

        // Make sure translations are loaded to check messages in English
        Piwik_Translate::getInstance()->loadEnglishTranslation();

        // List of Modules, or Module.Method that should not be called as part of the XML output compare
        // Usually these modules either return random changing data, or are already tested in specific unit tests.
        self::setApiNotToCall(self::$defaultApiNotToCall);
        self::setApiToCall( array());
    }

    public static function tearDownAfterClass()
    {
        try {
            $plugins = Piwik_PluginsManager::getInstance()->getLoadedPlugins();
            foreach($plugins AS $plugin) {
                $plugin->uninstall();
            }
            Piwik_PluginsManager::getInstance()->unloadPlugins();
        } catch (Exception $e) {}
        Piwik::dropDatabase();
        Piwik_DataTable_Manager::getInstance()->deleteAll();
        Piwik_Option::getInstance()->clearCache();
        Piwik_Site::clearCache();
        Piwik_Common::deleteTrackerCache();
        Piwik_Config::getInstance()->clear();
        Piwik_TablePartitioning::$tablesAlreadyInstalled = null;
        Zend_Registry::_unsetInstance();

        $_GET = $_REQUEST = array();
        Piwik_Translate::getInstance()->unloadEnglishTranslation();

        // re-enable tag cloud shuffling
        Piwik_Visualization_Cloud::$debugDisableShuffle = true;

    }

    protected static $apiToCall = array();
    protected static $apiNotToCall = array();

    public static $defaultApiNotToCall = array(
        'LanguagesManager',
        'DBStats',
        'UsersManager',
        'SitesManager',
        'ExampleUI',
        'Live',
        'SEO',
        'ExampleAPI',
        'PDFReports',
        'API',
        'ImageGraph',
    );

    const DEFAULT_USER_PASSWORD = 'nopass';

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
    protected static function setApiToCall( $apiToCall )
    {
        if(func_num_args() != 1)
        {
            throw new Exception('setApiToCall expects an array');
        }
        if(!is_array($apiToCall))
        {
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
    protected static function setApiNotToCall( $apiNotToCall )
    {
        if(!is_array($apiNotToCall))
        {
            $apiNotToCall = array($apiNotToCall);
        }
        self::$apiNotToCall = $apiNotToCall;
    }

    /**
     * Returns a PiwikTracker object that you can then use to track pages or goals.
     *
     * @param         $idSite
     * @param         $dateTime
     * @param boolean $defaultInit If set to true, the tracker object will have default IP, user agent, time, resolution, etc.
     *
     * @return PiwikTracker
     */
    public static function getTracker($idSite, $dateTime, $defaultInit = true )
    {
        $t = new PiwikTracker( $idSite, self::getTrackerUrl());
        $t->setForceVisitDateTime($dateTime);

        if($defaultInit)
        {
            $t->setIp('156.5.3.2');

            // Optional tracking
            $t->setUserAgent( "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)");
            $t->setBrowserLanguage('fr');
            $t->setLocalTime( '12:34:06' );
            $t->setResolution( 1024, 768 );
            $t->setBrowserHasCookies(true);
            $t->setPlugins($flash = true, $java = true, $director = false);
        }
        return $t;
    }

    /**
     * Creates a website, then sets its creation date to a day earlier than specified dateTime
     * Useful to create a website now, but force data to be archived back in the past.
     *
     * @param string  $dateTime eg '2010-01-01 12:34:56'
     * @param int     $ecommerce
     * @param string  $siteName
     *
     * @return int    idSite of website created
     */
    public static function createWebsite( $dateTime, $ecommerce = 0, $siteName = 'Piwik test' )
    {
        $idSite = Piwik_SitesManager_API::getInstance()->addSite(
            $siteName,
            "http://piwik.net/",
            $ecommerce,
            $ips = null,
            $excludedQueryParameters = null,
            $timezone = null,
            $currency = null
        );

        // Manually set the website creation date to a day earlier than the earliest day we record stats for
        Zend_Registry::get('db')->update(Piwik_Common::prefixTable("site"),
            array('ts_created' => Piwik_Date::factory($dateTime)->subDay(1)->getDatetime()),
            "idsite = $idSite"
        );

        // Clear the memory Website cache
        Piwik_Site::clearCache();

        return $idSite;
    }

    /**
     * Checks that the response is a GIF image as expected.
     * Will fail the test if the response is not the expected GIF
     *
     * @param $response
     */
    protected static function checkResponse($response)
    {
        $trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $expectedResponse = base64_decode($trans_gif_64);
        self::assertEquals($expectedResponse, $response, "Expected GIF beacon, got: <br/>\n" . $response ."<br/>\n");
    }

    /**
     * Returns URL to the proxy script, used to ensure piwik.php
     * uses the test environment, and allows variable overwriting
     *
     * @return string
     */
    protected static function getTrackerUrl()
    {
        $piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();

        $pathBeforeRoot = 'tests';
        // Running from a plugin
        if(strpos($piwikUrl, 'plugins/') !== false)
        {
            $pathBeforeRoot = 'plugins';
        }
        $piwikUrl = substr($piwikUrl, 0, strpos($piwikUrl, $pathBeforeRoot.'/')) . 'tests/PHPUnit/proxy-piwik.php';
        return $piwikUrl;
    }

    /**
     * Given a list of default parameters to set, returns the URLs of APIs to call
     * If any API was specified in setApiToCall() we ensure only these are tested.
     * If any API is set as excluded (see list below) then it will be ignored.
     *
     * @param array       $parametersToSet Parameters to set in api call
     * @param array       $formats         Array of 'format' to fetch from API
     * @param array       $periods         Array of 'period' to query API
     * @param bool        $setDateLastN    If set to true, the 'date' parameter will be rewritten to query instead a range of dates, rather than one period only.
     * @param bool|string $language        2 letter language code, defaults to default piwik language
     * @param bool|string $segment
     *
     * @return array of API URLs query strings
     */
    protected function generateUrlsApi( $parametersToSet, $formats, $periods, $setDateLastN = false, $language = false, $segment = false )
    {
        // Get the URLs to query against the API for all functions starting with get*
        $skipped = $requestUrls = array();
        $apiMetadata = new Piwik_API_DocumentationGenerator;
        foreach(Piwik_API_Proxy::getInstance()->getMetadata() as $class => $info)
        {
            $moduleName = Piwik_API_Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach($info as $methodName => $infoMethod)
            {
                $apiId = $moduleName.'.'.$methodName;

                // If Api to test were set, we only test these
                if(!empty(self::$apiToCall)
                    && in_array($moduleName, self::$apiToCall) === false
                    && in_array($apiId, self::$apiToCall) === false)
                {
                    $skipped[] = $apiId;
                    continue;
                }
                // Excluded modules from test
                elseif(
                    (strpos($methodName, 'get') !== 0
                        || in_array($moduleName, self::$apiNotToCall) === true
                        || in_array($apiId, self::$apiNotToCall) === true
                        || $methodName == 'getLogoUrl'
                        || $methodName == 'getHeaderLogoUrl'
                    )
                )
                {
                    $skipped[] = $apiId;
                    continue;
                }

                foreach($periods as $period)
                {
                    $parametersToSet['period'] = $period;

                    // If date must be a date range, we process this date range by adding 6 periods to it
                    if($setDateLastN)
                    {
                        if(!isset($parametersToSet['dateRewriteBackup']))
                        {
                            $parametersToSet['dateRewriteBackup'] = $parametersToSet['date'];
                        }

                        $lastCount = (int)$setDateLastN;
                        if($setDateLastN === true)
                        {
                            $lastCount = 6;
                        }
                        $firstDate = $parametersToSet['dateRewriteBackup'];
                        $secondDate = date('Y-m-d', strtotime("+$lastCount " . $period . "s", strtotime($firstDate)));
                        $parametersToSet['date'] = $firstDate . ',' . $secondDate;
                    }

                    // Set response language
                    if($language !== false)
                    {
                        $parametersToSet['language'] = $language;
                    }
                    // Generate for each specified format
                    foreach($formats as $format)
                    {
                        $parametersToSet['format'] = $format;
                        $parametersToSet['hideIdSubDatable'] = 1;
                        $parametersToSet['serialize'] = 1;

                        $exampleUrl = $apiMetadata->getExampleUrl($class, $methodName, $parametersToSet);
                        if($exampleUrl === false)
                        {
                            $skipped[] = $apiId;
                            continue;
                        }

                        // Remove the first ? in the query string
                        $exampleUrl = substr($exampleUrl, 1);
                        $apiRequestId = $apiId;
                        if(strpos($exampleUrl, 'period=') !== false)
                        {
                            $apiRequestId .= '_' . $period;
                        }

                        $apiRequestId .= '.' . $format;

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
     * @param string|array      $formats        String or array of formats to fetch from API
     * @param int|bool          $idSite         Id site
     * @param string|bool       $dateTime       Date time string of reports to request
     * @param array|bool|string $periods        String or array of strings of periods (day, week, month, year)
     * @param bool              $setDateLastN   When set to true, 'date' parameter passed to API request will be rewritten to query a range of dates rather than 1 date only
     * @param string|bool       $language       2 letter language code to request data in
     * @param string|bool       $segment        Custom Segment to query the data  for
     * @param string|bool       $visitorId      Only used for Live! API testing
     * @param bool              $abandonedCarts Only used in Goals API testing
     * @param bool              $idGoal
     * @param bool              $apiModule
     * @param bool              $apiAction
     * @param array             $otherRequestParameters
     *
     * @return array
     */
    protected function _generateApiUrls($formats = 'xml', $idSite = false, $dateTime = false, $periods = false,
                                         $setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false,
                                         $idGoal = false, $apiModule = false, $apiAction = false, $otherRequestParameters = array())
    {
        list($pathProcessed, $pathExpected) = $this->getProcessedAndExpectedDirs();

        if($periods === false)
        {
            $periods = 'day';
        }
        if(!is_array($periods))
        {
            $periods = array($periods);
        }
        if(!is_array($formats))
        {
            $formats = array($formats);
        }
        if(!is_writable($pathProcessed))
        {
            $this->fail('To run the tests, you need to give write permissions to the following directory (create it if it doesn\'t exist).<code><br/>mkdir '. $pathProcessed.'<br/>chmod 777 '.$pathProcessed.'</code><br/>');
        }
        $parametersToSet = array(
            'idSite'           => $idSite,
            'date'             => $periods == array('range') ? $dateTime : date('Y-m-d', strtotime($dateTime)),
            'expanded'         => '1',
            'piwikUrl'         => 'http://example.org/piwik/',
            // Used in getKeywordsForPageUrl
            'url'              => 'http://example.org/store/purchase.htm',

            // Used in Actions.getPageUrl, .getDownload, etc.
            // tied to Main.test.php doTest_oneVisitorTwoVisits
            // will need refactoring when these same API functions are tested in a new function
            'downloadUrl'      => urlencode('http://piwik.org/path/again/latest.zip?phpsessid=this is ignored when searching'),
            'outlinkUrl'       => urlencode('http://dev.piwik.org/svn'),
            'pageUrl'          => urlencode('http://example.org/index.htm?sessionid=this is also ignored by default'),
            'pageName'         => urlencode(' Checkout / Purchasing... '),

            // do not show the millisec timer in response or tests would always fail as value is changing
            'showTimer'        => 0,

            'language'         => $language ? $language : 'en',
            'abandonedCarts'   => $abandonedCarts ? 1 : 0,
            'idSites'          => $idSite,
        );
        $parametersToSet = array_merge($parametersToSet, $otherRequestParameters);
        if(!empty($visitorId ))
        {
            $parametersToSet['visitorId'] = $visitorId;
        }
        if(!empty($apiModule ))
        {
            $parametersToSet['apiModule'] = $apiModule;
        }
        if(!empty($apiAction))
        {
            $parametersToSet['apiAction'] = $apiAction;
        }
        if(!empty($segment))
        {
            $parametersToSet['segment'] = $segment;
        }
        if($idGoal !== false)
        {
            $parametersToSet['idGoal'] = $idGoal;
        }

        $requestUrls = $this->generateUrlsApi($parametersToSet, $formats, $periods, $setDateLastN, $language, $segment);
        return $requestUrls;
    }

    protected function _testApiUrl($testName, $apiId, $requestUrl)
    {
        $isLiveMustDeleteDates = strpos($requestUrl, 'Live.getLastVisits') !== false;
        $request               = new Piwik_API_Request($requestUrl);
        $dateTime             = Piwik_Common::getRequestVar('date', '', 'string', Piwik_Common::getArrayFromQueryString($requestUrl));

        list($processedFilePath, $expectedFilePath) = $this->getProcessedAndExpectedPaths($testName, $apiId);

        // Cast as string is important. For example when calling
        // with format=original, objects or php arrays can be returned.
        // we also hide errors to prevent the 'headers already sent' in the ResponseBuilder (which sends Excel headers multiple times eg.)
        $response = (string)$request->process();

        if ($isLiveMustDeleteDates) {
            $response = $this->removeAllLiveDatesFromXml($response);
        }

        file_put_contents($processedFilePath, $response);

        $expected = $this->loadExpectedFile($expectedFilePath);
        if (empty($expected)) {
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
            $expected = $this->removeXmlElement($expected, 'sum_daily_nb_uniq_visitors');
            $response = $this->removeXmlElement($response, 'sum_daily_nb_uniq_visitors');
            $expected = $this->removeXmlElement($expected, 'nb_visits_converted');
            $response = $this->removeXmlElement($response, 'nb_visits_converted');
            $expected = $this->removeXmlElement($expected, 'imageGraphUrl');
            $response = $this->removeXmlElement($response, 'imageGraphUrl');
        }

        // is there a better way to test for the current DB type in use?
        if (Zend_Registry::get('db') instanceof Piwik_Db_Adapter_Mysqli) {
            // Do not test for TRUNCATE(SUM()) returning .00 on mysqli since this is not working
            // http://bugs.php.net/bug.php?id=54508
            $expected = str_replace('.00</revenue>', '</revenue>', $expected);
            $response = str_replace('.00</revenue>', '</revenue>', $response);
            $expected = str_replace('.1</revenue>', '</revenue>', $expected);
            $expected = str_replace('.11</revenue>', '</revenue>', $expected);
            $response = str_replace('.11</revenue>', '</revenue>', $response);
            $response = str_replace('.1</revenue>', '</revenue>', $response);
        }

        if (strpos($requestUrl, 'format=xml') !== false) {
            $this->assertXmlStringEqualsXmlString($expected, $response, "Differences with expected in: $processedFilePath %s ");
        } else {
            $this->assertEquals($expected, $response, "Differences with expected in: $processedFilePath %s ");
        }
        if (trim($response) == trim($expected)) {
            file_put_contents($processedFilePath, $response);
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
            'visitorId'
        );
        foreach($toRemove as $xml) {
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
        $input = preg_replace('/(<'.$xmlElement.'>.+?<\/'.$xmlElement.'>)/', '', $input);
        //check we didn't delete the whole string
        if($testNotSmallAfter)
        {
            $this->assertTrue(strlen($input) > 100);
        }
        return $input;
    }

    private function getProcessedAndExpectedDirs()
    {
        $path = $this->getPathToTestDirectory();
        return array($path . '/processed/', $path . '/expected/');
    }

    private function getProcessedAndExpectedPaths($testName, $testId, $format = null)
    {
        $filename = $testName . '__' . $testId;
        if ($format)
        {
            $filename .= ".$format";
        }

        list($processedDir, $expectedDir) = $this->getProcessedAndExpectedDirs();

        return array($processedDir . $filename, $expectedDir . $filename);
    }

    private function loadExpectedFile($filePath)
    {
        $result = @file_get_contents($filePath);
        if(empty($result))
        {
            $expectedDir = dirname($filePath);
            $this->fail(" ERROR: Could not find expected API output '$filePath'. For new tests, to pass the test, you can copy files from the processed/ directory into $expectedDir  after checking that the output is valid. %s ");
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
    public function getApiForTesting() {
        return array();
    }

    /**
     * Gets the string prefix used in the name of the expected/processed output files.
     */
    public function getOutputPrefix()
    {
        return str_replace('Test_Piwik_Integration_', '', get_class($this));
    }

    protected function _setCallableApi($api)
    {
        if ($api == 'all')
        {
            self::setApiToCall(array());
            self::setApiNotToCall(self::$defaultApiNotToCall);
        }
        else
        {
            if (!is_array($api))
            {
                $api = array($api);
            }

            self::setApiToCall($api);
            self::setApiNotToCall(array('API.getPiwikVersion'));
        }
    }

    /**
     * Runs API tests.
     */
    protected function runApiTests($api, $params)
    {
        $testName = 'test_' . $this->getOutputPrefix();

        $this->_setCallableApi($api);

        if (isset($params['disableArchiving']) && $params['disableArchiving'] === true)
        {
            Piwik_ArchiveProcessing::$forceDisableArchiving = true;
        }
        else
        {
            Piwik_ArchiveProcessing::$forceDisableArchiving = false;
        }

        if (isset($params['language']))
        {
            $this->changeLanguage($params['language']);
        }

        $testSuffix = isset($params['testSuffix']) ? $params['testSuffix'] : '';

        $requestUrls = $this->_generateApiUrls(
                    isset($params['format']) ? $params['format'] : 'xml',
                    isset($params['idSite']) ? $params['idSite'] : false,
                    isset($params['date']) ? $params['date'] : false,
                    isset($params['periods']) ? $params['periods'] : false,
                    isset($params['setDateLastN']) ? $params['setDateLastN'] : false,
                    isset($params['language']) ? $params['language'] : false,
                    isset($params['segment']) ? $params['segment'] : false,
                    isset($params['visitorId']) ? $params['visitorId'] : false,
                    isset($params['abandonedCarts']) ? $params['abandonedCarts'] : false,
                    isset($params['idGoal']) ? $params['idGoal'] : false,
                    isset($params['apiModule']) ? $params['apiModule'] : false,
                    isset($params['apiAction']) ? $params['apiAction'] : false,
                    isset($params['otherRequestParameters']) ? $params['otherRequestParameters'] : array());

        foreach($requestUrls as $apiId => $requestUrl)
        {
            $this->_testApiUrl( $testName . $testSuffix, $apiId, $requestUrl);
        }

        // change the language back to en
        if ($this->lastLanguage != 'en')
        {
            $this->changeLanguage('en');
        }
    }

    /**
     * changing the language within one request is a bit fancy
     * in order to keep the core clean, we need a little hack here
     *
     * @param string $langId
     */
    protected function changeLanguage( $langId )
    {
        if ($this->lastLanguage != $langId)
        {
            $_GET['language'] = $langId;
            Piwik_Translate::reset();
            Piwik_Translate::getInstance()->reloadLanguage($langId);
        }

        $this->lastLanguage = $langId;
    }

    /**
     * Path where expected/processed output files are stored. Can be overridden.
     */
    public function getPathToTestDirectory()
    {
        /**
         * Use old path as long as files were not moved
         * @todo move files
         */
        //return dirname(__FILE__).DIRECTORY_SEPARATOR.'Integration';
        return dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'integration';
    }
}
