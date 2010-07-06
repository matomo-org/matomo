<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}


require_once PIWIK_INCLUDE_PATH . '/tests/core/Database.test.php';
require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Runs integration / acceptance tests
 * 
 * The test calls the Piwik tracker with known sets of data, expected errors, 
 * and can test the output of the tracker beacon, as well as calling 
 * all API functions, and Widgets, and compare their XML/HTML output with the 'expected output'.
 * 
 * If an algorithm changes in the Tracker or in the Archiving, tests can easily be run to check that 
 * the output changes as expected (eg. More accurate browser detection, adding a new metric in the 
 * API results, etc.
 * 
 */
class Test_Piwik_Integration_Main extends Test_Integration
{
	public function getPathToTestDirectory()
	{
		return PIWIK_INCLUDE_PATH . '/tests/integration';
	}
	
	/**
	 *  API Tests
	 * request day/week/month/year for full feature set
	 * request all formats for full feature set
	 * request multiple sites
	 * 
	 * Fetch API methods, get those get* with idSite, date, period parameters
	 * Known rules for expanded, idSubtable, Live! parameters, typeReferer 
	 * Blacklist of API, UsersManager, SitesManager,LanguagesManager
	 * Force today archive, then make sure all tests use same cached archive.
	 * Call all API methods, check XML and record new output in directory.
	 * 
	 *  Widget Tests
	 * Loop over all widgets and call with standard parameters. Check HTML output.
	 * 
	 */
	// TODO
	// ExampleUI own integration test
	// test periods
	// test several websites
	// test all formats
	
	
	/*
	 * Testing various wrong Tracker requests. 
	 * API will archive and output empty stats.
	 */
	function test_noVisit()
	{
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
		// TODO Check piwik.php GIF output on all requests
		
		// Trigger wrong website
        $trackerWrongWebsite = $this->getTracker($idSiteFake = 33, $dateTime, $defaultInit = true);
        $this->checkResponse($trackerWrongWebsite->doTrackPageView('index page'));
        
		// Trigger empty request
		$trackerUrl = $this->getTrackerUrl();
		$response = Piwik_Http::fetchRemoteFile($trackerUrl);
		$this->assertTrue(strpos($response, 'web analytics') !== false, 'Piwik empty request response not correct: ' . $response);
		
		// test GoogleBot UA visitor
		$t->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
		$this->checkResponse($t->doTrackPageView('bot visit, please do not record'));
		
		// test with excluded IP
		$t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)'); // restore normal user agent	
		$excludedIp = '154.1.12.34';
		Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new site name', null, $excludedIp . ',1.2.3.4');
		$t->setIp($excludedIp);
		$this->checkResponse($t->doTrackPageView('visit from IP excluded'));
		
		// test with global list of excluded IPs 
		$excludedIpBis = '145.5.3.4';
		Piwik_SitesManager_API::getInstance()->setGlobalExcludedIps($excludedIpBis);
		$t->setIp($excludedIpBis);
		$this->checkResponse($t->doTrackPageView('visit from IP globally excluded'));
		
		
        $this->callGetApiCompareOutput(__FUNCTION__, $idSite, $dateTime);
	}
	
	
	/*
	 * This use case covers many simple tracking features.
	 * - Tracking Goal by manual trigger, and URL matching, with custom revenue
	 * - Tracking the same Goal twice only records it once
	 * - Tracks 2 page views, a click and a file download
	 * - URLs parameters exclude is tested
	 * - In a returning visit, tracks a Goal conversion 
	 *   URL matching, with custom referer and keyword
	 */
	function test_OneVisitorTwoVisits() 
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-03-06 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
    	
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setUrlReferer( 'http://referer.com/page.htm?param=valuewith some spaces');
    	ob_start();
    	
    	// Testing URL excluded parameters
    	$parameterToExclude = 'excluded_parameter';
    	Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new name', null, null, $parameterToExclude . ',anotherParameter');

    	// Record 1st page view
        $t->setUrl( 'http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY?parameter=Should display' );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        // Testing that / and index.htm above record with different URLs
        // Recording the 2nd page after 3 minutes
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $t->setUrl( 'http://example.org/' );
        $this->checkResponse($t->doTrackPageView( 'Second page view - should be registered as URL /'));
        
        // Click on external link after 6 minutes (3rd action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $this->checkResponse($t->doTrackAction( 'http://dev.piwik.org/svn', 'link' ));
        
        // Click on file download after 12 minutes (4th action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $this->checkResponse($t->doTrackAction( 'http://piwik.org/latest.zip', 'download' ));
        
        // Create Goal 1: Triggered by JS, after 18 minutes
        Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal = 1, $revenue = 42));
        
        // Track same Goal twice (after 24 minutes), should only be tracked once
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal = 1, $revenue = 42));
        // -
        // End of first visit: 24min
        
        // Create Goal 2: Matching on URL
        Piwik_Goals_API::getInstance()->addGoal($idSite, 'matching purchase.htm', 'url', 'purchase.htm', 'contains', false, $revenue = 1);

        // -
        // Start of returning visit, 1 hour after first page view
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrlReferer( 'http://search.yahoo.com/search?p=purchase');
        $t->setUrl( 'http://example.org/store/purchase.htm' );
        
        // Goal Tracking URL matching, testing custom referer including keyword
        $this->checkResponse($t->doTrackPageView( 'Purchasing...'));
        // -
        // End of second visit
        
        $this->callGetApiCompareOutput(__FUNCTION__, $idSite, $dateTime);
	}

	
	
	/**
	 * To do list (to be converted as a ticket)
	 * - test std API parameters filter_*
	 * - report on performance of tracker and API/archiving. Reuse in a load testing suite. 
	 *   How to report on historical data though? possible with Hudson? 
	 * - Unit test for Goal API add/update/delete, LanguagesManager, DBStats, SEO.getRank, Live.getLast*
	 * - Tracker API should forward http headers 'HTTP_CLIENT_IP' 'HTTP_X_FORWARDED_FOR', used in the tracking algorithm
	 *   which would allow for testing their parsing
	 * - Tracker test/set/forward request/response cookies  
	 * - Test API with subtables
	 *        23 => string 'Piwik_Referers_API.getSearchEnginesFromKeywordId' (length=48)
     *        24 => string 'Piwik_Referers_API.getKeywordsFromSearchEngineId' (length=48)
	 *        25 => string 'Piwik_Referers_API.getKeywordsFromCampaignId' (length=44)
	 *        26 => string 'Piwik_Referers_API.getUrlsFromWebsiteId' (length=39)
	 *  - Test API authentication with token_auth
	 *  PiwikTracker:
	 *  - Unknown visitor, detect outlink on one of the alias host -> is it new visit, or request to ignore? probably ignore
	 *  - API_URL should be set when file is downloaded
	 *  - set cookies in request
 	 *  - set cookies from response (optional)
	 */
}