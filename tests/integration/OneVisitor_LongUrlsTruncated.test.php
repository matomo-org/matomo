<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests that filter_truncate works recursively in Page URLs report AND in the case there are 2 different data Keywords -> search engine
 */
class Test_Piwik_Integration_OneVisitor_LongUrlsTruncated extends Test_Integration_Facade
{
	protected $dateTime = '2010-03-06 01:22:33';
	protected $idSite = null;
	public function getApiToTest()
	{
    	$apiToCall = array('Referers.getKeywords', 'Actions.getPageUrls');

		return array(
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime, 'language' => 'fr',
				'otherRequestParameters' => array('expanded' => 1, 'filter_truncate' => 2)))
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'OneVisitor_LongUrlsTruncated';
	}
	
	public function setUp()
	{
		parent::setUp();
		
		$this->idSite = $this->createWebsite($this->dateTime);
	}

	protected function trackVisits()
	{
		// tests run in UTC, the Tracker in UTC
    	$dateTime = $this->dateTime;
    	$idSite = $this->idSite;

    	// Visit 1: keyword and few URLs
    	$t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setUrlReferrer( 'http://bing.com/search?q=Hello world');
        
        // Generate a few page views that will be truncated
        $t->setUrl( 'http://example.org/category/Page1');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page2');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page3');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page3');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page4');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page4');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category/Page4');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/category.htm');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/page.htm');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/index.htm');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/page.htm');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/page.htm');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        $t->setUrl( 'http://example.org/contact.htm');
        $this->checkResponse($t->doTrackPageView( 'Hello'));
        
        // VISIT 2 = Another keyword
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
        $t->setUrlReferrer( 'http://www.google.com.vn/url?q=Salut');
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        // Visit 3 = Another keyword
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->setUrlReferrer( 'http://www.google.com.vn/url?q=Kia Ora');
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));

        // Visit 4 = Kia Ora again
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(3)->getDatetime());
        $t->setUrlReferrer( 'http://www.google.com.vn/url?q=Kia Ora');
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));

        // Visit 5 = Another search engine
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(4)->getDatetime());
        $t->setUrlReferrer( 'http://nz.search.yahoo.com/search?p=Kia Ora');
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));

        // Visit 6 = Another search engine
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(5)->getDatetime());
        $t->setUrlReferrer( 'http://images.search.yahoo.com/search/images;_ylt=A2KcWcNKJzF?p=Kia%20Ora%20');
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
        
        // Visit 7 = Another search engine
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(6)->getDatetime());
        $t->setUrlReferrer( 'http://nz.bing.com/images/search?q=+++Kia+ora+++');
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));
	}
}

