<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
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
	
	/**
	 * Test the Yearly metadata API response, 
	 * with no visits, with custom response language 
	 */
	function test_apiGetReportMetadata_year()
	{
		$this->setApiNotToCall(array());
		$this->setApiToCall( array('API.getProcessedReport', 
									'API.getReportMetadata', 
									'LanguagesManager.getTranslationsForLanguage', 
									'LanguagesManager.getAvailableLanguageNames',
									'SitesManager.getJavascriptTag') );
		$dateTime = '2009-01-04 00:11:42';
		$idSite = $this->createWebsite($dateTime);
		$language = 'fr';
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime, 'year', $setDateLastN = false, $language);
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
	 *   
	 *   NO cookie support
	 */
	function test_OneVisitorTwoVisits() 
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-03-06 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        
		$t->disableCookieSupport();
        $this->doTest_oneVisitorTwoVisits($t, $dateTime, $idSite );
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	
	/*
	 * Same as before, but with cookie support, which incurs some slight changes 
	 * in the reporting data (more accurate unique visitor count, better referer tracking for goals, etc.)
	 */
	function test_OneVisitorTwoVisits_withCookieSupport() 
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-03-06 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        
        $this->doTest_oneVisitorTwoVisits($t, $dateTime, $idSite );
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite, $dateTime);
	}
	

	private function doTest_oneVisitorTwoVisits($t, $dateTime, $idSite )
	{
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
        $this->checkResponse($t->doTrackAction( 'http://piwik.org/path/again/latest.zip', 'download' ));
        
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
        $this->checkResponse($t->doTrackPageView( 'Checkout/Purchasing...'));
        // -
        // End of second visit
	}
	
	/*
	 * Tests Tracker several websites, different days.
	 * Tests API for period=day/week/month/year, requesting data for both websites, 
	 * and requesting data for last N periods.
	 * Also tests a visit that spans over 2 days.
	 * And testing empty URL and empty Page name request
	 * Also testing a click on a mailto counted as outlink
	 */
	function test_TwoVisitors_twoWebsites_differentDays()
	{
		// Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-01-03 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
    	$idSite2 = $this->createWebsite($dateTime);
    	$this->setApiToCall(array('VisitFrequency.get', 
    								'VisitsSummary.get',
    								'Referers.getWebsites', 
    								'Actions.getPageUrls', 
    								'Actions.getPageTitles',
    	                            'Actions.getOutlinks'));
    	ob_start();
    	
    	// -
    	// First visitor on Idsite 1: two page views
    	$datetimeSpanOverTwoDays = '2010-01-03 23:55:00'; 
        $visitorA = $this->getTracker($idSite, $datetimeSpanOverTwoDays, $defaultInit = true);
        $visitorA->setUrlReferer( 'http://referer.com/page.htm?param=valuewith some spaces');
        $visitorA->setUrl('http://example.org/homepage');
        $this->checkResponse($visitorA->doTrackPageView('first page view'));
    	$visitorA->setForceVisitDateTime(Piwik_Date::factory($datetimeSpanOverTwoDays)->addHour(0.1)->getDatetime());
    	$visitorA->setUrl('  ');
        $this->checkResponse($visitorA->doTrackPageView('  '));
        
        // - 
    	// Second new visitor on Idsite 1: one page view 
        $visitorB = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    	$visitorB->setIp('100.52.656.83');
    	$visitorB->setResolution(800, 300);
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $visitorB->setUrlReferer( '' );
    	$visitorB->setUserAgent('Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1');
    	$visitorB->setUrl('http://example.org/products');
    	$this->checkResponse($visitorB->doTrackPageView('first page view'));

    	// -
    	// Second visitor again on Idsite 1: 2 page views 2 days later, 2010-01-05
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->getDatetime());
        $visitorB->setUrlReferer( 'http://referer.com/Other_Page.htm' );
    	$visitorB->setUrl('http://example.org/homepage');
    	$this->checkResponse($visitorB->doTrackPageView('second visitor/two days later/a new visit'));
    	// Second page view 6 minutes later
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.1)->getDatetime());
    	$visitorB->setUrl('http://example.org/thankyou');
    	$this->checkResponse($visitorB->doTrackPageView('second visitor/two days later/second page view'));
    	
    	// Testing a strange combination causing an error in r3767
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.2)->getDatetime());
    	$this->checkResponse($visitorB->doTrackAction('mailto:test@example.org', 'link'));
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.25)->getDatetime());
    	$this->checkResponse($visitorB->doTrackAction('mailto:test@example.org/strangelink', 'link'));
    	
    	// -
    	// First visitor on Idsite 2: one page view, with Website referer
        $visitorAsite2 = $this->getTracker($idSite2, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime(), $defaultInit = true);
        $visitorAsite2->setUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0;)');
        $visitorAsite2->setUrlReferer('http://only-homepage-referer.com/');
        $visitorAsite2->setUrl('http://example2.com/home');
        $this->checkResponse($visitorAsite2->doTrackPageView('Website 2 page view'));
        
        // Returning visitor on Idsite 2 1 day later, one page view, with chinese referer
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
	
	function test_twoVisitsWithCustomVariables()
	{
	    // Tests run in UTC, the Tracker in UTC
    	$dateTime = '2010-01-03 11:22:33';
    	$idSite = $this->createWebsite($dateTime);
    	$this->setApiToCall(array(	'VisitsSummary.get',
    	                            'CustomVariables.getCustomVariables'
    	));
    	ob_start();
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
    	// -
        $visitorA = $this->getTracker($idSite, $dateTime, $defaultInit = true);

        // At first, visitor custom var is set to LoggedOut
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
    	$visitorA->setUrl('http://example.org/homepage');
    	$visitorA->setVisitorCustomVar($id = 1, $name = 'VisitorType', $value = 'LoggedOut');
        $this->checkResponse($visitorA->doTrackPageView('Homepage'));
        
        // After login, set to LoggedIn, should overwrite previous value
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
    	$visitorA->setUrl('http://example.org/profile');
    	$visitorA->setVisitorCustomVar($id = 1, $name = 'VisitorType', $value = 'LoggedIn');
        $this->checkResponse($visitorA->doTrackPageView('Profile page'));
        
        $visitorA->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
    	$visitorA->setVisitorCustomVar($id = 2, $name = 'NOTSETBECAUSE EMPTY VALUE', $value = '');
        $this->checkResponse($visitorA->doTrackPageView('Profile page'));
    	$this->checkResponse($visitorA->doTrackGoal($idGoal));
        
        // - 
    	// Second new visitor on Idsite 1: one page view 
        $visitorB = $this->getTracker($idSite, $dateTime, $defaultInit = true);
    	$visitorB->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913 Firefox/3.0.6');
    	$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
    	$visitorB->setVisitorCustomVar($id = 1, $name = 'VisitorType', $value = 'LoggedOut');
    	$visitorB->setVisitorCustomVar($id = 2, $name = 'Othercustom value which should be truncated abcdefghijklmnopqrstuvwxyz', $value = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz');
    	$visitorB->setVisitorCustomVar($id = -2, $name = 'not tracked', $value = 'not tracked');
    	$visitorB->setVisitorCustomVar($id = 6, $name = 'not tracked', $value = 'not tracked');
    	$visitorB->setVisitorCustomVar($id = 6, $name = array('not tracked'), $value = 'not tracked');
    	$visitorB->setUrl('http://example.org/homepage');
    	$this->checkResponse($visitorB->doTrackGoal($idGoal, 1000));

    	// Test Referer.get* methods in XML
    	$periods = array('day', 'week');
    	// Request data for both websites at once
    	$idSite = 'all';
    	// Request data for the last 6 periods
        $this->callGetApiCompareOutput(__FUNCTION__, 'xml', $idSite = 'all', $dateTime, $periods, $setDateLastN = true);
	}
	
}
