<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
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
class Test_Piwik_Integration_OneVisitorTwoVisits extends Test_Integration_Facade
{
	protected $idSite = null;
	protected $dateTime = null;

	public function getApiToTest()
	{
		return array(
			array('all', array('idSite' => $this->idSite, 'date' => $this->dateTime)),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function setUp()
	{
		parent::setUp();
		
		// tests run in UTC, the Tracker in UTC
		$this->dateTime = '2010-03-06 11:22:33';
		$this->idSite = $this->createWebsite($this->dateTime);
	}
	
	protected function trackVisits()
	{
		$t = $this->getTracker($this->idSite, $this->dateTime, $defaultInit = true);
		$this->trackVisitsImpl($t);
	}
	
	protected function trackVisitsImpl($t)
	{
		$dateTime = $this->dateTime;
		$idSite = $this->idSite;

		$t->disableCookieSupport();

		$t->setUrlReferrer( 'http://referer.com/page.htm?param=valuewith some spaces');
		
		// testing URL excluded parameters
		$parameterToExclude = 'excluded_parameter';
		Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new name', $url=array('http://site.com'),$ecommerce = 0, $excludedIps = null, $parameterToExclude . ',anotherParameter');

		// Record 1st page view
		$urlPage1 = 'http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY&parameter=Should display';
		$t->setUrl( $urlPage1 );
		$this->checkResponse($t->doTrackPageView( 'incredible title!'));
		
		// testing that / and index.htm above record with different URLs
		// Recording the 2nd page after 3 minutes
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.05)->getDatetime());
		$urlPage2 = 'http://example.org/' ;
		$t->setUrl( $urlPage2 );
//		$t->setUrlReferrer($urlPage1);
		$this->checkResponse($t->doTrackPageView( 'Second page view - should be registered as URL /'));
		
//		$t->setUrlReferrer($urlPage2);
		// Click on external link after 6 minutes (3rd action)
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
		$this->checkResponse($t->doTrackAction( 'http://dev.piwik.org/svn', 'link' ));
		
		// Click on file download after 12 minutes (4th action)
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
		$this->checkResponse($t->doTrackAction( 'http://piwik.org/path/again/latest.zip', 'download' ));
		
		// Create Goal 1: Triggered by JS, after 18 minutes
		$idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
		
		// Change to Thai  browser to ensure the conversion is credited to FR instead (the visitor initial country)
		$t->setBrowserLanguage('th'); 
		$this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));
		
		// Track same Goal twice (after 24 minutes), should only be tracked once
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
		$this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));
		
		$t->setBrowserLanguage('fr'); 
		// Final page view (after 27 min)
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.45)->getDatetime());
		$t->setUrl( 'http://example.org/index.htm' );
		$this->checkResponse($t->doTrackPageView( 'Looking at homepage (again)...'));
		
		// -
		// End of first visit: 24min
		
		// Create Goal 2: Matching on URL
		Piwik_Goals_API::getInstance()->addGoal($idSite, 'matching purchase.htm', 'url', '(.*)store\/purchase\.(.*)', 'regex', false, $revenue = 1);

		// -
		// Start of returning visit, 1 hour after first page view
		$t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
		$t->setUrl( 'http://example.org/store/purchase.htm' );
		$t->setUrlReferrer( 'http://search.yahoo.com/search?p=purchase');
		// Temporary, until we implement 1st party cookies in PiwikTracker
		$t->DEBUG_APPEND_URL = '&_idvc=2';
		
		// Goal Tracking URL matching, testing custom referer including keyword
		$this->checkResponse($t->doTrackPageView( 'Checkout/Purchasing...'));
		// -
		// End of second visit
		
	}
}
