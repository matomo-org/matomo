<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * 
 */
class Test_Piwik_Integration_VisitsInPast_InvalidateOldReports extends Test_Integration_Facade
{
	protected $dateTimeFirstDateWebsite1 = '2010-03-06 01:22:33';
	protected $dateTimeDateInPastWebsite1 = '2010-01-06 01:22:33';
	
	protected $dateTimeFirstDateWebsite2 = '2010-01-03 20:22:33';
	protected $dateTimeDateInPastWebsite2 = '2009-10-30 01:22:33';
	protected $idSite = null;
	protected $idSite2 = null;
	
	/**
	 * This should NOT return data for old dates before website creation
	 */
	public function getApiToTest()
	{
		// We test a typical Numeric and a Recursive blob reports
    	$apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');
		
		// We also test a segment 
		//TODO
		
    	// Build tests for the 2 websites
    	$test = array($apiToCall, array('idSite' => $this->idSite, 
    			'testSuffix' => 'Website'.$this->idSite.'_OldReportsShouldNotAppear',
    			'date' => $this->dateTimeDateInPastWebsite1,
    			'periods' => 'month',
    			'setDateLastN' => 4, // 4months ahead
 				'otherRequestParameters' => array('expanded' => 1)));
    	$testWebsite2 = $test;
    	$testWebsite2[1]['idSite'] = $this->idSite2;
    	$testWebsite2[1]['testSuffix'] = 'Website'.$this->idSite2.'_OldReportsShouldNotAppear';
    	$testWebsite2[1]['date'] = $this->dateTimeDateInPastWebsite2;
    	
		$this->tests = array(
			$test,
			$testWebsite2
		);
		return $this->tests;
	}
	
	/**
	 * This is called after getApiToTest()
	 * WE invalidate old reports and check that data is now returned for old dates
	 */
	public function getAnotherApiToTest()
	{
		// 1) Invalidate old reports for the 2 websites
		// Test invalidate 1 date only
		$r = new Piwik_API_Request("module=API&method=CoreAdminHome.invalidateArchivedReports
			&idSites=4,5,6,55,-1,s',1&dates=2010-01-03");
		$r->process();
		
		// Test invalidate comma separated dates
		$r = new Piwik_API_Request("module=API&method=CoreAdminHome.invalidateArchivedReports
			&idSites=".$this->idSite.",".$this->idSite2."&dates=2010-01-06,2009-10-30");
		$r->process();
		
		// test invalidate date in the past
		$r = new Piwik_API_Request("module=API&method=CoreAdminHome.invalidateArchivedReports
			&idSites=".$this->idSite2."&dates=2009-06-29");
		$r->process();
		
		// invalidate a date more recent to check the date is only updated when it's earlier than current
		$r = new Piwik_API_Request("module=API&method=CoreAdminHome.invalidateArchivedReports
			&idSites=".$this->idSite2."&dates=2010-03-03");
		$r->process();
		
		// 2) Call API again, with an older date, which should now return data
		// website 1
    	$this->tests[0][1]['testSuffix'] = 'Website'.$this->idSite.'_OldReportsShouldAppear';
    	// website2
    	$this->tests[1][1]['testSuffix'] = 'Website'.$this->idSite2.'_OldReportsShouldAppear';

    	return $this->tests;
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'VisitsInPast_InvalidateOldReports';
	}
	
	public function setUp()
	{
		parent::setUp();
		
		// Create 2 websites
		$this->idSite = $this->createWebsite($this->dateTimeFirstDateWebsite1);
		$this->idSite2 = $this->createWebsite($this->dateTimeFirstDateWebsite2);
	}

	protected function trackVisits()
	{
    	/** 
    	 * Track Visits normal date for the 2 websites
    	 */
		
		// WEBSITE 1
    	$t = $this->getTracker($this->idSite, $this->dateTimeFirstDateWebsite1, $defaultInit = true);
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page2');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page3');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/Home');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/Contact');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/Contact/ThankYou');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        
		// WEBSITE 2
    	$t = $this->getTracker($this->idSite2, $this->dateTimeFirstDateWebsite2, $defaultInit = true);
		$t->setIp('156.15.13.12');
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page2');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page3');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/Home');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/Contact');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/Contact/ThankYou');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        
    	/**
    	 * Track visits in the past (before website creation date) for the 2 websites
    	 */
    	// WEBSITE1 
        $t = $this->getTracker($this->idSite, $this->dateTimeDateInPastWebsite1, $defaultInit = true);
		$t->setIp('156.5.55.2');
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page2');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Pagexx');
        $this->checkResponse($t->doTrackPageView( 'Blabla'));
        
    	// WEBSITE2
    	$t = $this->getTracker($this->idSite2, $this->dateTimeDateInPastWebsite2, $defaultInit = true);
		$t->setIp('156.52.3.22');
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page2');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Pageyy');
        $this->checkResponse($t->doTrackPageView( 'Blabla'));
        $t->setForceVisitDateTime(Piwik_Date::factory($this->dateTimeDateInPastWebsite2)->addHour(0.1)->getDatetime());
        $t->setUrl( 'http://example.org/category/Pageyy');
        $this->checkResponse($t->doTrackPageView( 'Blabla'));
	}
}

