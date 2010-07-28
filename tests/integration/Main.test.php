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
 * all API functions and compare their HTML output with the 'expected output'.
 * 
 * If an algorithm changes in the Tracker or in the Archiving, tests can easily be run to check that 
 * the output changes as expected (eg. More accurate browser detection, adding a new metric in the 
 * API results, etc.
 * 
 * @see TODO list http://dev.piwik.org/trac/ticket/1465
 */
class Test_Piwik_Integration_Main extends Test_Integration
{
	/*
	 * Path where expected/processed output files are stored
	 */
	public function getPathToTestDirectory()
	{
		return PIWIK_INCLUDE_PATH . '/tests/integration';
	}
	
	/**
	 * This tests the output of the API plugin API 
	 * It will return metadata about all API reports from all plugins
	 * as well as the data itself, pre-processed and ready to be displayed
	 * @return 
	 */
	function test_apiGetReportMetadata()
	{
		$this->setApiNotToCall(array());
		$this->setApiToCall( 'API' );
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    	// Record 1st page view
        $t->setUrl( 'http://example.org/index.htm' );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42.256));
        
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	
	/*
	 * Testing various wrong Tracker requests and check that they behave as expected:
	 * not throwing errors and not recording data.
	 *  
	 * API will archive and output empty stats.
	 * 
	 */
	function test_noVisit()
	{
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
		
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
		
		// this will output empty XML result sets as no visit was tracked
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
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
    	
    	// Testing URL excluded parameters
    	$parameterToExclude = 'excluded_parameter';
    	Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new name', null, null, $parameterToExclude . ',anotherParameter');

    	// Record 1st page view
    	$urlPage1 = 'http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY?parameter=Should display';
        $t->setUrl( $urlPage1 );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        // Testing that / and index.htm above record with different URLs
        // Recording the 2nd page after 3 minutes
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.05)->getDatetime());
        $urlPage2 = 'http://example.org/' ;
        $t->setUrl( $urlPage2 );
        $t->setUrlReferer($urlPage1);
        $this->checkResponse($t->doTrackPageView( 'Second page view - should be registered as URL /'));
        
        $t->setUrlReferer($urlPage2);
        // Click on external link after 6 minutes (3rd action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
        $this->checkResponse($t->doTrackAction( 'http://dev.piwik.org/svn', 'link' ));
        
        // Click on file download after 12 minutes (4th action)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $this->checkResponse($t->doTrackAction( 'http://piwik.org/latest.zip', 'download' ));
        
        // Create Goal 1: Triggered by JS, after 18 minutes
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));
        
        // Track same Goal twice (after 24 minutes), should only be tracked once
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));
        
        // Final page view (after 27 min)
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.45)->getDatetime());
        $t->setUrl( 'http://example.org/index.htm' );
        $this->checkResponse($t->doTrackPageView( 'Looking at homepage (again)...'));
        
        // -
        // End of first visit: 24min
        
        // Create Goal 2: Matching on URL
        Piwik_Goals_API::getInstance()->addGoal($idSite, 'matching purchase.htm', 'url', 'purchase.htm', 'contains', false, $revenue = 1);

        // -
        // Start of returning visit, 1 hour after first page view
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrl( 'http://example.org/store/purchase.htm' );
        $t->setUrlReferer( 'http://search.yahoo.com/search?p=purchase');
        
        // Goal Tracking URL matching, testing custom referer including keyword
        $this->checkResponse($t->doTrackPageView( 'Purchasing...'));
        // -
        // End of second visit
        
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}

	/*
	 * Tests Tracker several websites, different days.
	 * Tests API for period=day/week/month/year, requesting data for both websites, 
	 * and requesting data for last N periods.
	 */
	function test_TwoVisitors_twoWebsites_differentDays()
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-01-03 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
    	$idSite2 = $this->createWebsite($dateTime);
    	$this->setApiToCall(array('VisitsSummary.get','Referers.getWebsites', 'Actions.getPageUrls'));
    	ob_start();
    	
    	// -
    	// First visitor on Idsite 1: one page view
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setUrlReferer( 'http://referer.com/page.htm?param=valuewith some spaces');
        $t->setUrl('http://example.org/homepage');
        $this->checkResponse($t->doTrackPageView(''));
        
        // - 
    	// Second new visitor on Idsite 1: one page view 
    	$t->setIp('1.5.6.8');
    	$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrlReferer( '' );
    	$t->setUserAgent('Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1');
    	$t->setUrl('http://example.org/products');
    	$this->checkResponse($t->doTrackPageView('second visitor, first page view'));

    	// -
    	// Second visitor again on Idsite 1: 2 page views 2 days later, 2010-01-05
    	$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->getDatetime());
        $t->setUrlReferer( 'http://referer.com/Other_Page.htm' );
    	$t->setUrl('http://example.org/homepage');
    	$this->checkResponse($t->doTrackPageView('second visitor, two days later a new visit'));
    	// Second page view 6 minutes later
    	$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.1)->getDatetime());
    	$t->setUrl('http://example.org/thankyou');
    	$this->checkResponse($t->doTrackPageView('second pageview'));
    	
    	// -
    	// First visitor on Idsite 2: one page view, with Website referer
        $t2 = $this->getTracker($idSite2, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime(), $defaultInit = true);
        $t2->setUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0;)');
        $t2->setUrlReferer('http://only-homepage-referer.com/');
        $t2->setUrl('http://example2.com/home');
        $this->checkResponse($t2->doTrackPageView('Website 2 page view'));
        
        // Returning visitor on Idsite 2 1 day later, one page view, with chinese referer
//TODO when we can test frequency, when Piwik_Tracker_Client supports cookies read/send
//    	$t2->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48 + 10)->getDatetime());
//        $t2->setUrlReferer('http://www.baidu.com/s?wd=%D0%C2+%CE%C5&n=2');
//        $t2->setUrl('http://example2.com/home');
//        $this->checkResponse($t2->doTrackPageView('I\'m a returning visitor...'));
        
        // -
    	// Test Referer.get* methods in XML
    	$periods = array('day', 'week', 'month', 'year');
    	// Request data for both websites at once
    	$idSite = 'all';
    	// Request data for the last 6 periods
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite = 'all', $dateTime, $periods, $setDateLastN = true);
	}
	
}