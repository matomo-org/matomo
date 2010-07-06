<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
Mock::generate('Piwik_Access');

require_once PIWIK_INCLUDE_PATH . '/core/Tracker/PiwikTracker.php';
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
		$this->apiToCall= $apiToCall;
	}
	
	protected $apiToCall = array();
	
	/*
	 * Checks that the response is a GIF image as expected.
	 * 
	 */
	protected function checkResponse($response)
	{
    	$trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
		$expectedResponse = base64_decode($trans_gif_64); 
		$this->assertEqual($expectedResponse, $response);
		
		if($response == $expectedResponse) 
		{
			$this->pass();
			return;
		}
		echo $response;
		$this->fail("Response was not the expected GIF image: see above. ");
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
		$piwikUrl = substr($piwikUrl, 0, strpos($piwikUrl, 'tests/')) . 'tests/integration/proxy-piwik.php'; 
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
	 * Load english translations to ensure API response have english text
	 * @see tests/core/Test_Database#setUp()
	 */
	function setUp() 
	{
		parent::setUp();
		// Make sure translations are loaded to check messages in English 
    	Piwik_Translate::getInstance()->loadEnglishTranslation();
	}
	
	function tearDown() 
	{
		parent::setUp();
    	Piwik_Translate::getInstance()->unloadEnglishTranslation();
	}
	
	/**
	 * Given a list of default parameters to set, returns the URLs of APIs to call
	 * If any API was specified in setApiToCall() we ensure only these are tested.
	 * If any API is set as excluded (see list below) then it will be ignored.
	 * 
	 * @param $parametersToSet 
	 * @param $formats Array of formats to fetch from API
	 * @return array of API URLs query strings
	 */ 
	protected function generateUrlsApi( $parametersToSet, $formats )
	{
		// List of Modules, or Module.Method that should not be called as part of the XML output compare
		// Usually these modules either return random changing data, or are already tester in specific unit tests. 
		// Live! should also be tested and its API finalized. 
		$apiNotToTest = array(
			'LanguagesManager',
			'DBStats',
			'UsersManager',
			'SitesManager',
			'ExampleUI',
			'Live',
			'SEO',
			'ExampleAPI',
		);
		
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
    			if(!empty($this->apiToCall))
    			{
        			if(in_array($moduleName, $this->apiToCall) === false
        				&& in_array($apiId, $this->apiToCall) === false)
        			{
        				$skipped[] = $apiId;
        				continue;
    				}
    			}
    			// Excluded modules from test
    			elseif(strpos($methodName, 'get') !== 0
    				|| in_array($moduleName, $apiNotToTest) === true
    				|| in_array($apiId, $apiNotToTest) === true)
    			{
    				$skipped[] = $apiId;
    				continue;
    			}
    			
    			// Generate for each specified format
    			foreach($formats as $format)
    			{
    				$parametersToSet['format'] = $format;
        			$exampleUrl = $apiMetadata->getExampleUrl($class, $methodName, $parametersToSet);
        			if($exampleUrl === false) 
        			{
        				$skipped[] = $apiId;
        				continue;
        			}
        			
        			// Remove the first ? in the query string
        			$exampleUrl = substr($exampleUrl, 1);
        			$apiRequestId = $apiId . '.' . $format;
    				$requestUrls[$apiRequestId] = $exampleUrl;
    			}
    		}
    	}
//    	var_dump($skipped);
//    	var_dump($requestUrls);
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
	 * @param $idSite
	 * @param $dateTime
	 * @return void
	 */
	function callGetApiCompareOutput($testName, $formats = 'xml', $idSite = false, $dateTime = false)
	{
		$parametersToSet = array(
			'idSite' 	=> $idSite,
			'period' 	=> 'day',
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
		$requestUrls = $this->generateUrlsApi($parametersToSet, $formats);
    	
    	foreach($requestUrls as $apiId => $requestUrl)
    	{
    		$request = new Piwik_API_Request($requestUrl);
    		$path = $this->getPathToTestDirectory();
    		$pathProcessed = $path . "/processed/";
    		$pathExpected = $path . "/expected/";
    		
        	// $TEST_NAME - $API_METHOD
    		$filename = $testName . '__' . $apiId;
    		
    		$response = $request->process();
    		file_put_contents( $pathProcessed . $filename, $response );
    		
    		$expected = file_get_contents( $pathExpected . $filename);
    		if(empty($expected))
    		{
    			$this->fail(" ERROR: Could not find set of 'expected' files. For new tests, to pass the test, you can copy files from /processed into $pathExpected  after checking the output is valid.");
    		}
    		$this->assertEqual($response, $expected, "In $filename, %s");
    		if($response != $expected){
    			echo $apiId;
    			var_dump($response);
    		}
    	}
    	$this->pass();
	}
	
}