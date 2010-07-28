<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
Mock::generate('Piwik_Access');

require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';
require_once PIWIK_INCLUDE_PATH . '/tests/core/Database.test.php';

/**
 * Base class for Integration tests.
 * 
 * Provides helpers to track data and then call API get* methods to check outputs automatically.
 * 
 */
abstract class Test_Integration extends Test_Database
{
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
		));
		$this->setApiToCall( array());
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
            $t->setCustomData( array('id' => 10, 'name' => 'test') );
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
	protected function createWebsite( $dateTime )
	{
    	$idSite = Piwik_SitesManager_API::getInstance()->addSite(
    					"Piwik test",
    					"http://piwik.net/", 
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
	 * Given a list of default parameters to set, returns the URLs of APIs to call
	 * If any API was specified in setApiToCall() we ensure only these are tested.
	 * If any API is set as excluded (see list below) then it will be ignored.
	 * 
	 * @param $parametersToSet 
	 * @param $formats Array of 'format' to fetch from API
	 * @param $periods Array of 'period' to query API
	 * @param $setDateLastN If set to true, the 'date' parameter will be rewritten to query instead a range of dates, rather than one period only.
	 * 
	 * @return array of API URLs query strings
	 */ 
	protected function generateUrlsApi( $parametersToSet, $formats, $periods, $setDateLastN = false )
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
    				|| in_array($apiId, $this->apiNotToCall) === true)
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
	 * @param $setDateLastN When set to true, 'date' parameter passed to API request will be rewritten to query a range of dates rather than 1 date only
	 * 
	 * @return void
	 */
	function callGetApiCompareOutput($testName, $formats = 'xml', $idSite = false, $dateTime = false, $periods = 'day', $setDateLastN = false)
	{
		$path = $this->getPathToTestDirectory();
		$pathProcessed = $path . "/processed/";
		$pathExpected = $path . "/expected/";
		
		if(!is_writable($pathProcessed))
		{
			$this->fail('To run the tests, you need to give write permissions to the following directory (create it if it doesn\'t exist).<code><br/>mkdir '. $pathProcessed.'<br/>chmod 777 '.$pathProcessed.'</code><br/>');
		}
		$parametersToSet = array(
			'idSite' 	=> $idSite,
			'date'		=> date('Y-m-d', strtotime($dateTime)),
			'expanded'  => '1',
			'piwikUrl'  => 'http://example.org/piwik/'
		);
		
		// Give it enough time for the current API test to finish (call all get* APIs)
		Zend_Registry::get('config')->General->time_before_today_archive_considered_outdated = 10;
		
		if(!is_array($formats))
		{
			$formats = array($formats);
		}
		if(!is_array($periods))
		{
			$periods = array($periods);
		}
		$requestUrls = $this->generateUrlsApi($parametersToSet, $formats, $periods, $setDateLastN);
    	
    	foreach($requestUrls as $apiId => $requestUrl)
    	{
    		$request = new Piwik_API_Request($requestUrl);
    		
        	// $TEST_NAME - $API_METHOD
    		$filename = $testName . '__' . $apiId;
    		
    		// Cast as string is important. For example when calling 
    		// with format=original, objects or php arrays can be returned.
    		// we also hide errors to prevent the 'headers already sent' in the ResponseBuilder (which sends Excel headers multiple times eg.)
    		$response = (string)$request->process();
    		$processedFilePath = $pathProcessed . $filename;
    		file_put_contents( $processedFilePath, $response );
    		
    		$expectedFilePath = $pathExpected . $filename;
    		$expected = file_get_contents($expectedFilePath  );
    		if(empty($expected))
    		{
    			$this->fail(" ERROR: Could not find expected API output '$expectedFilePath'. For new tests, to pass the test, you can copy files from the processed/ directory into $pathExpected  after checking that the output is valid.");
    			continue;
    		}
			// When tests run on Windows EOL delimiters are not the same as UNIX default EOL used in the renderers
    		$expected = str_replace("\r\n", "\n", $expected); 
    		$this->assertEqual(trim($response), trim($expected), "<br/>\nDifferences with expected in: $processedFilePath ");
    		if($response != $expected)
    		{
    			var_dump('ERROR FOR ' . $apiId . ' -- FETCHED RESPONSE, then EXPECTED RESPONSE');
    			echo "\n";
    			var_dump($response);
    			echo "\n";
    			var_dump($expected);
    			echo "\n";
    		}
    	}
    	$this->pass();
	}
	
}
