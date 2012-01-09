<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';
require_once PIWIK_INCLUDE_PATH . '/tests/core/Database.test.php';

Mock::generate('Piwik_Auth');

/**
 * Base class for Integration tests.
 * 
 * Provides helpers to track data and then call API get* methods to check outputs automatically.
 * 
 */
abstract class Test_Integration extends Test_Database
{
	/**
	 * Widget testing level constant. If Test_Integration::$widgetTestingLevel is
	 * set to this, controller actions will not be tested.
	 */
	const NO_WIDGET_TESTING = 0;
	
	/**
	 * Widget testing level constant. If Test_Integration::$widgetTestingLevel is
	 * set to this, controller actions will be checked for non-fatal errors, but
	 * the output will be ignored.
	 */
	const CHECK_WIDGET_ERRORS = 1;
	
	/**
	 * Widget testing level constant. If Test_Integration::$widgetTestingLevel is
	 * set to this, controller actions will be run & their output will be checked with
	 * expected output files.
	 */
	const COMPARE_WIDGET_OUTPUT = 2;

	/**
	 * Determines how much of controller actions are tested (if at all).
	 */
	static public $widgetTestingLevel = self::CHECK_WIDGET_ERRORS;

	const DEFAULT_USER_PASSWORD = 'nopass';

	abstract function getPathToTestDirectory();

	/**
	 * Load english translations to ensure API response have english text
	 * @see tests/core/Test_Database#setUp()
	 */
	function setUp() 
	{
		parent::setUp();

		// Make sure translations are loaded to check messages in English 
    	Piwik_Translate::getInstance()->loadEnglishTranslation();
    	
    	// List of Modules, or Module.Method that should not be called as part of the XML output compare
    	// Usually these modules either return random changing data, or are already tested in specific unit tests. 
		$this->setApiNotToCall(array(
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
		));
		$this->setApiToCall( array());

		if (self::$widgetTestingLevel != self::NO_WIDGET_TESTING)
		{
			Piwik::setUserIsSuperUser();
				
			// create users for controller testing
			$usersApi = Piwik_UsersManager_API::getInstance();
			$usersApi->addUser('anonymous', self::DEFAULT_USER_PASSWORD, 'anonymous@anonymous.com');
			$usersApi->addUser('view', self::DEFAULT_USER_PASSWORD, 'view@view.com');
			$usersApi->addUser('admin', self::DEFAULT_USER_PASSWORD, 'admin@admin.com');
		}
	}
	
	function tearDown() 
	{
		parent::tearDown();
    	Piwik_Translate::getInstance()->unloadEnglishTranslation();
	}
	
	protected $apiToCall = array();
	protected $apiNotToCall = array();
	
	/**
	 * Forces the test to only call and fetch XML for the specified plugins, 
	 * or exact API methods.
	 * 
	 * If not called, all default tests will be executed.
	 * 
	 * @param $apiToCall array( 'ExampleAPI', 'Plugin.getData' )
	 * @return void
	 */
	protected function setApiToCall( $apiToCall )
	{
		if(func_num_args() != 1) 
		{
			throw new Exception('setApiToCall expects an array');
		}
		if(!is_array($apiToCall))
		{
			$apiToCall = array($apiToCall);
		}
		$this->apiToCall = $apiToCall;
	}
	
	/**
	 * Sets a list of API methods to not call during the test
	 * @param $apiNotToCall eg. 'ExampleAPI.getPiwikVersion'
	 * @return void
	 */
	protected function setApiNotToCall( $apiNotToCall )
	{
		if(!is_array($apiNotToCall))
		{
			$apiNotToCall = array($apiNotToCall);
		}
		$this->apiNotToCall = $apiNotToCall;
	}
	
	
	/**
	 * Returns a PiwikTracker object that you can then use to track pages or goals.
	 * 
	 * @param $idSite
	 * @param $dateTime
	 * @param $defaultInit If set to true, the tracker object will have default IP, user agent, time, resolution, etc.
	 * @return PiwikTracker
	 */
	protected function getTracker($idSite, $dateTime, $defaultInit = true )
	{
        $t = new PiwikTracker( $idSite, $this->getTrackerUrl());
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
	 * @param $dateTime eg '2010-01-01 12:34:56'
	 * @return $idSite of website created
	 */
	protected function createWebsite( $dateTime, $ecommerce = 0, $siteName = 'Piwik test' )
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

		// add access to all test users if doing controller tests
		if (self::$widgetTestingLevel != self::NO_WIDGET_TESTING)
		{
			$usersApi = Piwik_UsersManager_API::getInstance();
			$usersApi->setUserAccess('anonymous', 'view', array($idSite));
			$usersApi->setUserAccess('view', 'view', array($idSite));
			$usersApi->setUserAccess('admin', 'admin', array($idSite));
		}

		return $idSite;
	}
	
	/**
	 * Checks that the response is a GIF image as expected.
	 * @return Will fail the test if the response is not the expected GIF
	 */
	protected function checkResponse($response)
	{
    	$trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
		$expectedResponse = base64_decode($trans_gif_64); 
		$this->assertEqual($expectedResponse, $response, "");
		if($response == $expectedResponse) 
		{
			$this->pass();
			return;
		}
		echo "Expected GIF beacon, got: <br/>\n" . $response ."<br/>\n";
	}
	
	/**
	 * Returns URL to the proxy script, used to ensure piwik.php 
	 * uses the test environment, and allows variable overwriting
	 * 
	 * @return string
	 */
	protected function getTrackerUrl()
	{
		$piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();

		$pathBeforeRoot = 'tests';
		// Running from a plugin
		if(strpos($piwikUrl, 'plugins/') !== false)
		{
			$pathBeforeRoot = 'plugins';
		}
		$piwikUrl = substr($piwikUrl, 0, strpos($piwikUrl, $pathBeforeRoot.'/')) . 'tests/integration/proxy-piwik.php'; 
		return $piwikUrl;
	}
	
	/**
	 * Initializes the test
	 * @param $title
	 * @return void
	 */
	function __construct($title = '')
	{
		parent::__construct($title);

		if (Test_Integration::$widgetTestingLevel != self::NO_WIDGET_TESTING)
		{
			self::initializeControllerTesting();
		}

    	Piwik::createAccessObject();
    	Piwik_PostEvent('FrontController.initAuthenticationObject');
    	
    	// We need to be SU to create websites for tests
    	Piwik::setUserIsSuperUser();

    	// Load and install plugins
    	$pluginsManager = Piwik_PluginsManager::getInstance();
    	$pluginsManager->loadPlugins( Zend_Registry::get('config')->Plugins->Plugins->toArray() );
    	$pluginsManager->installLoadedPlugins();
	}

	/**
	 * Initializes parts of Piwik so controller actions can be called & tested.
	 */
	public static function initializeControllerTesting()
	{
		static $initialized = false;

		if (!$initialized)
		{
			Zend_Registry::set('timer', new Piwik_Timer);

			$pluginsManager = Piwik_PluginsManager::getInstance();
			$pluginsToLoad = Zend_Registry::get('config')->Plugins->Plugins->toArray();
			$pluginsManager->loadPlugins( $pluginsToLoad );

			$initialized = true;
		}
	}
	
	/**
	 * Given a list of default parameters to set, returns the URLs of APIs to call
	 * If any API was specified in setApiToCall() we ensure only these are tested.
	 * If any API is set as excluded (see list below) then it will be ignored.
	 * 
	 * @param $parametersToSet 
	 * @param $formats Array of 'format' to fetch from API
	 * @param $periods Array of 'period' to query API
	 * @param $setDateLastN If set to true, the 'date' parameter will be rewritten to query instead a range of dates, rather than one period only.
	 * @param $language 2 letter language code, defaults to default piwik language
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
    			if(!empty($this->apiToCall)
    				&& in_array($moduleName, $this->apiToCall) === false
    				&& in_array($apiId, $this->apiToCall) === false)
    			{
    				$skipped[] = $apiId;
    				continue;
    			}
    			// Excluded modules from test
    			elseif(
    				(strpos($methodName, 'get') !== 0
    				|| in_array($moduleName, $this->apiNotToCall) === true
    				|| in_array($apiId, $this->apiNotToCall) === true
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
    				if($setDateLastN === true)
    				{
    					if(!isset($parametersToSet['dateRewriteBackup']))
    					{
    						$parametersToSet['dateRewriteBackup'] = $parametersToSet['date'];
    					}
    					$lastCount = 6;
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
//    	var_dump($skipped);
//    	var_dump($requestUrls);
//    	exit;
    	return $requestUrls;
	}
	
	/**
	 * Will call all get* methods on authorized modules, 
	 * force the archiving,
	 * record output in XML files
	 * and compare with the expected outputs.
	 * 
	 * @param $testName Used to write the output in a file, used as filename prefix
	 * @param $formats String or array of formats to fetch from API 
	 * @param $idSite Id site
	 * @param $dateTime Date time string of reports to request
	 * @param $periods String or array of strings of periods (day, week, month, year)
	 * @param $setDateLastN When set to true, 'date' parameter passed to API request will be rewritten to query a range of dates rather than 1 date only
	 * @param $language 2 letter language code to request data in
	 * @param $segment Custom Segment to query the data  for
	 * @param $visitorId Only used for Live! API testing
	 * @param $abandonedCarts Only used in Goals API testing
	 * 
	 * @return bool Passed or failed
	 */
	function callGetApiCompareOutput($testName, $formats = 'xml', $idSite = false, $dateTime = false, $periods = false,
			$setDateLastN = false, $language = false, $segment = false, $visitorId = false, $abandonedCarts = false,
			$idGoal = false, $apiModule = false, $apiAction = false, $otherRequestParameters = array())
	{
		$pass = true;
		
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
			'idSite' 	=> $idSite,
			'date'		=> $periods == array('range') ? $dateTime : date('Y-m-d', strtotime($dateTime)),
			'expanded'  => '1',
			'piwikUrl'  => 'http://example.org/piwik/',
			// Used in getKeywordsForPageUrl
			'url'		=> 'http://example.org/store/purchase.htm',
			
			// Used in Actions.getPageUrl, .getDownload, etc.
			// tied to Main.test.php doTest_oneVisitorTwoVisits
			// will need refactoring when these same API functions are tested in a new function
			'downloadUrl' 	=> urlencode('http://piwik.org/path/again/latest.zip?phpsessid=this is ignored when searching'),
			'outlinkUrl' 	=> urlencode('http://dev.piwik.org/svn'),
			'pageUrl' 		=> urlencode('http://example.org/index.htm?sessionid=this is also ignored by default'),
			'pageName' 		=> urlencode(' Checkout / Purchasing... '),
		
			// do not show the millisec timer in response or tests would always fail as value is changing
			'showTimer'     => 0,
		
			'language' => $language ? $language : 'en',
			'abandonedCarts' => $abandonedCarts ? 1 : 0,
			'idSites' => $idSite,
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
		
		Zend_Registry::get('config')->General->time_before_today_archive_considered_outdated = 10;
		
		$requestUrls = $this->generateUrlsApi($parametersToSet, $formats, $periods, $setDateLastN, $language, $segment);
    	
    	foreach($requestUrls as $apiId => $requestUrl)
    	{
//    		echo "$requestUrl <br>";
			$isLiveMustDeleteDates = strpos($requestUrl, 'Live.getLastVisits') !== false;
    		$request = new Piwik_API_Request($requestUrl);

			list($processedFilePath, $expectedFilePath) = $this->getProcessedAndExpectedPaths($testName, $apiId);
    		
    		// Cast as string is important. For example when calling 
    		// with format=original, objects or php arrays can be returned.
    		// we also hide errors to prevent the 'headers already sent' in the ResponseBuilder (which sends Excel headers multiple times eg.)
    		$response = (string)$request->process();
    		
    		if($isLiveMustDeleteDates)
    		{
    			$response = $this->removeAllLiveDatesFromXml($response);
    		}
    		
    		file_put_contents( $processedFilePath, $response );
    		
    		$expected = $this->loadExpectedFile($expectedFilePath);
    		if (empty($expected))
    		{
    			continue;
    		}

			// When tests run on Windows EOL delimiters are not the same as UNIX default EOL used in the renderers
    		$expected = str_replace("\r\n", "\n", $expected); 
    		$response = str_replace("\r\n", "\n", $response); 

    		// @todo This should not vary between systems AFAIK... "idsubdatatable can differ" 
    		$expected = $this->removeXmlElement($expected, 'idsubdatatable',$testNotSmallAfter = false);
    		$response = $this->removeXmlElement($response, 'idsubdatatable',$testNotSmallAfter = false);
    		
    		$removeEndOfLines = false;
    		if($isLiveMustDeleteDates)
    		{
    			$expected = $this->removeAllLiveDatesFromXml($expected);
    		}
    		// If date=lastN the <prettyDate> element will change each day, we remove XML element before comparison
    		elseif(strpos($dateTime, 'last') !== false
    			|| strpos($dateTime, 'today') !== false
    			|| strpos($dateTime, 'now') !== false
    			)
    		{
    			if(strpos($requestUrl, 'API.getProcessedReport') !== false)
	    		{
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
    			$removeEndOfLines =true;
    		}
    		
    		// is there a better way to test for the current DB type in use?
    		if(Zend_Registry::get('db') instanceof Piwik_Db_Adapter_Mysqli)
    		{
    			// Do not test for TRUNCATE(SUM()) returning .00 on mysqli since this is not working 
    			// http://bugs.php.net/bug.php?id=54508
    			$expected = str_replace('.00</revenue>', '</revenue>', $expected);
    			$response = str_replace('.00</revenue>', '</revenue>', $response);
    			$expected = str_replace('.1</revenue>', '</revenue>', $expected);
    			$expected = str_replace('.11</revenue>', '</revenue>', $expected);
    			$response = str_replace('.11</revenue>', '</revenue>', $response);
    			$response = str_replace('.1</revenue>', '</revenue>', $response);
    		}
    		
    		// Hack so we dont file_put_contents (see below) the files with the end of lines removed (not readable)
    		$responseToTest = $response;
    		$expectedToTest = $expected;
    		if($removeEndOfLines)
    		{
    			$responseToTest = str_replace("\n", "", $response);
    			$expectedToTest = str_replace("\n", "", $expected);
    		}
    		$pass = $pass && $this->assertEqual(trim($responseToTest), trim($expectedToTest), "<br/>\nDifferences with expected in: $processedFilePath %s ");
    		if(trim($response) != trim($expected))
    		{
    			var_dump('ERROR FOR ' . $apiId . ' -- FETCHED RESPONSE, then EXPECTED RESPONSE - '.$requestUrl);
    			echo "\n";
    			var_dump($response);
    			echo "\n";
    			var_dump($expected);
    			echo "\n";
    		}
    		else
    		{
    			file_put_contents( $processedFilePath, $response );
    		}
    	}
    	if($pass) {
    		$this->pass();
    	} else { 
    		$this->fail();
    	}
    	return $pass;
	}
	
	/**
	 * Calls a set of controller actions & either checks the result against
	 * expected output or just checks if errors occurred when called.
	 * 
	 * The behavior of this function can be modified by setting
	 * Test_Integration::$widgetTestingLevel (or $testingLevelOverride):
	 * <ul>
	 *   <li>If set to <b>NO_WIDGET_TESTING</b> this function simply returns.<li>
	 *   <li>If set to <b>CHECK_WIDGET_ERRORS</b> controller actions are called &
	 *       this function will just check for errors.</li>
	 *   <li>If set to <b>COMPARE_WIDGET_OUTPUT</b> controller actions are
	 *       called & the output is checked against expected output.</li>
	 * </ul>
	 * 
	 * @param string $testName Unique name of this test group. Expected/processed
	 *                         file names use this as a prefix.
	 * @param array $actions Array of controller actions to call. Each element
	 *                       must be in the following format: 'Controller.action'
	 * @param array $requestParameters The request parameters to set.
	 * @param array $userTypes The user types to test the controller with. Can contain
	 *                         these values: 'anonymous', 'view', 'admin', 'superuser'.
	 *                         Defaults to all four.
	 * @param int $testingLevelOverride Overrides Test_Integration::$widgetTestingLevel.
	 */
	public function callWidgetsCompareOutput(
		$testName, $actions, $requestParameters, $userTypes = false, $testingLevelOverride = false)
	{
		// deal with the testing level
		if (Test_Integration::$widgetTestingLevel == self::NO_WIDGET_TESTING)
		{
			return;
		}

		if (!$testingLevelOverride)
		{
			$testingLevelOverride = self::$widgetTestingLevel;
		}
		
		// process $userTypes argument
		if (!$userTypes)
		{
			$userTypes = array('anonymous', 'view', 'admin', 'superuser');
		}
		else if (!is_array($userTypes))
		{
			$userTypes = array($userTypes);
		}

		// get all testable controller actions if necessary
		if ($actions == 'all')
		{
			$actions = $this->findAllControllerActions();
		}

		$oldGet = $_GET;

		// run the tests
		foreach ($actions as $controllerAction)
		{
			list($controllerName, $actionName) = explode('.', $controllerAction);
			
			foreach ($userTypes as $userType)
			{
				$this->setUserType($userType);
				
				try
				{
					// set request parameters
					$_GET = array();
					foreach ($requestParameters as $key => $value)
					{
						$_GET[$key] = $value;
					}
					
					$_GET['module'] = $controllerName;
					$_GET['action'] = $actionName;

					if ($testingLevelOverride == self::CHECK_WIDGET_ERRORS)
					{
						$this->errorsOccurredInTest = array();
						set_error_handler(array($this, "customErrorHandler"));
					}

					// call controller action
					$response = Piwik_FrontController::getInstance()->fetchDispatch();
			
					list($processedFilePath, $expectedFilePath) = $this->getProcessedAndExpectedPaths(
						$testName . '_' . $userType, $controllerAction, 'html');

					if ($testingLevelOverride == self::CHECK_WIDGET_ERRORS)
					{
						restore_error_handler();

						if (!empty($this->errorsOccurredInTest))
						{
							// write processed (only if there are errors)
							file_put_contents($processedFilePath, $response);

							$this->fail("PHP Errors occurred in calling controller action '$controllerAction':");
							foreach ($this->errorsOccurredInTest as $error)
							{
								echo "&nbsp;   $error<br/>\n";
							}
						}
						else
						{
							$this->pass();
						}
					}
					else // check against expected
					{
						// write raw processed response
						file_put_contents($processedFilePath, $response);

						// load expected
						$expected = $this->loadExpectedFile($expectedFilePath);
						if (!$expected)
						{
							continue;
						}

						// normalize eol delimeters
						$expected = str_replace("\r\n", "\n", $expected); 
						$response = str_replace("\r\n", "\n", $response);
				
						// check against expected
						$passed = $this->assertEqual(trim($expected), trim($response),
							"<br/>\nDifferences with expected in: $processedFilePath %s ");

						if (!$passed)
						{
							var_dump('ERROR FOR ' . $controllerAction . ' -- FETCHED RESPONSE, then EXPECTED RESPONSE - ');
							echo "\n";
							var_dump($response);
							echo "\n";
							var_dump($expected);
							echo "\n";
						}
					}
				}
				catch (Exception $e)
				{
					$this->fail("EXCEPTION THROWN IN $controllerAction: ".$e->getTraceAsString());
				}
			}
		}

		// reset $_GET to old values
		$_GET = array();
		foreach ($oldGet as $key => $value)
		{
			$_GET[$key] = $value;
		}
		
		// set user type
		$this->setUserType('superuser');
	}
	
	/**
	 * Sets the access privilegs of the current user to the specified user type.
	 * 
	 * @param $userType string Can be 'superuser', 'admin', 'view' or 'anonymous'.
	 */
	protected function setUserType( $userType )
	{
		if ($userType == 'superuser')
		{
			$authResultObj = new Piwik_Auth_Result(
				Piwik_Auth_Result::SUCCESS_SUPERUSER_AUTH_CODE, 'superUserLogin', 'dummyTokenAuth');
		}
		else
		{
			$authResultObj = new Piwik_Auth_Result(0, $userType, 'dummyTokenAuth');
		}
	
		$authObj = new MockPiwik_Auth();
		$authObj->setReturnValue('getName', 'Login');
		$authObj->setReturnValue('authenticate', $authResultObj);
		
		Zend_Registry::get('access')->reloadAccess($authObj);
	}
	
	/**
	 * Set of messages for errors that occurred during the invocation of a
	 * controller action. If not empty, there was an error in the controller.
	 */
	private $errorsOccurredInTest = array();
	
	/**
	 * A custom error handler used with <code>set_error_handler</code>. If
	 * an error occurs, a message describing it is saved in an array.
	 */
	public function customErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (strpos(strtolower($errstr), 'cannot modify header information - headers already sent')) // HACK
		{
			$this->errorsOccurredInTest[] = "$errfile($errline): - $errstr";
		}
	}
	
	/**
	 * Returns a list of all available controller actions.
	 */
	protected function findAllControllerActions()
	{
		// list of plugins & actions that should not be tested this way. at the moment, only
		// read-only operations can be tested.
		// 
		// NOTE: at the moment whole plugins are blacklisted. its possible to only specify the
		// actions, but this becomes harder to maintain. Using a mock config that doesn't save,
		// might remove the need for a blacklist... will also need to know which actions require
		// admin access & which don't.
		static $blacklist = array(
			'CorePluginsAdmin', 'CoreAdminHome', 'CoreHome', 'CoreUpdater', 'Proxy', 'Dashboard',
			'Feedback', 'UsersManager', 'Installation', 'LanguagesManager', 'Login', 'VisitorGenerator',
			'Widgetize', 'PrivacyManager', 'ImageGraph', 'ExampleFeedburner.saveFeedburnerName',
			'Referers.getKeywordsForPage', // tries to do a request to this host, but when testing, the url
										   // ends up as http://whatever/tests/integration/?...
										   // Problem is w/ Piwik_Url::getCurrentUrlWithoutFileName (FIXME)
			'SitesManager', // some API calls require admin access (like getDefaultCurrency (why this?)), so
							// testing w/ anonymous access fails.
			'UserCountryMap.outputImage', 'UserCountryMap.exportImage', // uses Proxy
			'Goals.getLastNbConversionsGraph','Goals.getLastConversionRateGraph','Goals.getLastRevenueGraph',
		);

		// the cached result		
		static $result = null;
		if (!is_null($result))
		{
			return $result;
		}

		// methods in the Piwik_Controller class, which will be detected but should not be
		// considered controller actios
		$baseControllerMethods = get_class_methods('Piwik_Controller');
	
		$result = array();

		$plugins = Piwik_PluginsManager::getInstance()->getLoadedPlugins();
		foreach ($plugins as $plugin)
		{
			$pluginName = $plugin->getPluginName();
			$controllerClass = 'Piwik_' . $pluginName . '_Controller';
			
			if (in_array($pluginName, $blacklist) || !class_exists($controllerClass))
			{
				continue;
			}
			
			foreach (get_class_methods($controllerClass) as $methodName)
			{
				if ($methodName == '__construct' || $methodName == '__destruct' || $methodName == $controllerClass
					|| in_array($methodName, $baseControllerMethods)
					|| $methodName == 'getEvolutionGraph' // evolution graphs require a columns request var,
														  // so they can't be tested here
					)
				{
					continue;
				}

				$actionId = "$pluginName.$methodName";
				if (in_array($actionId, $blacklist))
				{
					continue;
				}

				$result[] = $actionId;
			}
		}

		return $result;
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
}

