<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * tests Tracker several websites, different days.
 * tests API for period=day/week/month/year, requesting data for both websites, 
 * and requesting data for last N periods.
 * Also tests a visit that spans over 2 days.
 * And testing empty URL and empty Page name request
 * Also testing a click on a mailto counted as outlink
 * Also testing metadata API for multiple periods
 */
class Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays extends Test_Integration_Facade
{
	protected $idSite1 = null;
	protected $idSite2 = null;
	protected $idGoal1 = null;
	protected $idGoal2 = null;
	protected $dateTime = null;
	protected $allowConversions = false;

	protected function getApiToCall()
	{
		return array('VisitFrequency.get',
					'VisitsSummary.get',
					'Referers.getWebsites',
					'Actions.getPageUrls',
					'Actions.getPageTitles',
					'Actions.getOutlinks',
					'Actions.getPageTitle',
					'Actions.getPageUrl',
					'VisitorInterest.getNumberOfVisitsByDaysSinceLast');
	}

	public function getApiToTest()
	{
		$apiToCall = $this->getApiToCall();
		$singlePeriodApi = array('VisitsSummary.get', 'Goals.get');

		$periods = array('day', 'week', 'month', 'year');
		
		$result = array(
			// Request data for the last 6 periods and idSite=all
			array($apiToCall, array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => $periods,
									'setDateLastN' => true)),
			
			// Request data for the last 6 periods and idSite=1
			array($apiToCall, array('idSite' => $this->idSite1, 'date' => $this->dateTime, 'periods' => $periods,
									'setDateLastN' => true, 'testSuffix' => '_idSiteOne_')),

			// We also test a single period to check that this use case (Reports per idSite in the response) works
			array($singlePeriodApi, array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => array('day', 'month'),
										  'setDateLastN' => false, 'testSuffix' => '_NotLastNPeriods')),
		);
		
		// testing metadata API for multiple periods
		$apiToCall = array_diff($apiToCall, array('Actions.getPageTitle', 'Actions.getPageUrl'));
		foreach($apiToCall as $api)
		{
			list($apiModule, $apiAction) = explode(".", $api);

			$result[] = array(
				'API.getProcessedReport', array('idSite' => $this->idSite1, 'date' => $this->dateTime,
												'periods' => array('day'), 'setDateLastN' => true,
												'apiModule' => $apiModule, 'apiAction' => $apiAction,
												'testSuffix' => '_'.$api.'_firstSite_lastN')
			);
		}
		
		return $result;
	}

	public function getControllerActionsToTest()
	{
		return array();
	}

	public function getOutputPrefix()
	{
		return 'TwoVisitors_twoWebsites_differentDays';
	}

	public function setUp()
	{
		parent::setUp();

		// tests run in UTC, the Tracker in UTC
		$this->dateTime = '2010-01-03 11:22:33';
		$ecommerce = $this->allowConversions ? 1 : 0;

		$this->idSite1 = $this->createWebsite($this->dateTime, $ecommerce, "Site 1");
		$this->idSite2 = $this->createWebsite($this->dateTime, 0, "Site 2");
	}

	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
		$idSite = $this->idSite1;
		$idSite2 = $this->idSite2;

		if ($this->allowConversions)
		{
			$this->idGoal1 = Piwik_Goals_API::getInstance()->addGoal($idSite, 'all', 'url', 'http', 'contains', false, 5);
			$this->idGoal2 = Piwik_Goals_API::getInstance()->addGoal($idSite2, 'all', 'url', 'http', 'contains');
		}

		// -
		// First visitor on Idsite 1: two page views
		$datetimeSpanOverTwoDays = '2010-01-03 23:55:00'; 
		$visitorA = $this->getTracker($idSite, $datetimeSpanOverTwoDays, $defaultInit = true);
		$visitorA->setUrlReferrer( 'http://referer.com/page.htm?param=valuewith some spaces');
		$visitorA->setUrl('http://example.org/index.htm');
		$visitorA->DEBUG_APPEND_URL = '&_idts='.Piwik_Date::factory($datetimeSpanOverTwoDays)->getTimestamp();
		$this->checkResponse($visitorA->doTrackPageView('first page view'));

		$visitorA->setForceVisitDateTime(Piwik_Date::factory($datetimeSpanOverTwoDays)->addHour(0.1)->getDatetime());
		// testing with empty URL and empty page title
		$visitorA->setUrl('  ');
		$this->checkResponse($visitorA->doTrackPageView('  '));
		
		// - 
		// Second new visitor on Idsite 1: one page view 
		$visitorB = $this->getTracker($idSite, $dateTime, $defaultInit = true);
		$visitorB->enableBulkTracking();
		// calc token auth by hand in test environment
		$tokenAuth = md5(
			Piwik_Config::getInstance()->superuser['login'].Piwik_Config::getInstance()->superuser['password']);
		$visitorB->setTokenAuth($tokenAuth);
		$visitorB->setIp('100.52.156.83');
		$visitorB->setResolution(800, 300);
		$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
		$visitorB->setUrlReferrer( '' );
		$visitorB->setUserAgent('Opera/9.63 (Windows NT 5.1; U; en) Presto/2.1.1');
		$visitorB->setUrl('http://example.org/products');
		$visitorB->DEBUG_APPEND_URL = '&_idts='.Piwik_Date::factory($dateTime)->addHour(1)->getTimestamp();
		$this->assertTrue($visitorB->doTrackPageView('first page view'));

		// -
		// Second visitor again on Idsite 1: 2 page views 2 days later, 2010-01-05
		$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->getDatetime());
		// visitor_returning is set to 1 only when visit count more than 1
		// Temporary, until we implement 1st party cookies in PiwikTracker
		$visitorB->DEBUG_APPEND_URL .= '&_idvc=2&_viewts='.Piwik_Date::factory($dateTime)->getTimestamp();

		$visitorB->setUrlReferrer( 'http://referer.com/Other_Page.htm' );
		$visitorB->setUrl('http://example.org/index.htm');
		$this->assertTrue($visitorB->doTrackPageView('second visitor/two days later/a new visit'));
		// Second page view 6 minutes later
		$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.1)->getDatetime());
		$visitorB->setUrl('http://example.org/thankyou');
		$this->assertTrue($visitorB->doTrackPageView('second visitor/two days later/second page view'));
		
		// testing a strange combination causing an error in r3767
		$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.2)->getDatetime());
		$this->assertTrue($visitorB->doTrackAction('mailto:test@example.org', 'link'));
		$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.25)->getDatetime());
		$this->assertTrue($visitorB->doTrackAction('mailto:test@example.org/strangelink', 'link'));
		
		// Actions.getPageTitle tested with this title
		$visitorB->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48)->addHour(0.25)->getDatetime());
		$this->assertTrue($visitorB->doTrackPageView('Checkout / Purchasing...'));
		$this->checkResponse($visitorB->doBulkTrack());
		
		// -
		// First visitor on Idsite 2: one page view, with Website referer
		$visitorAsite2 = $this->getTracker($idSite2, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime(), $defaultInit = true);
		$visitorAsite2->setUserAgent('Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0;)');
		$visitorAsite2->setUrlReferrer('http://only-homepage-referer.com/');
		$visitorAsite2->setUrl('http://example2.com/home');
		$visitorAsite2->DEBUG_APPEND_URL = '&_idts='.Piwik_Date::factory($dateTime)->addHour(24)->getTimestamp();
		$this->checkResponse($visitorAsite2->doTrackPageView('Website 2 page view'));
		// test with invalid URL
		$visitorAsite2->setUrl('this is invalid url');
		// and an empty title
		$this->checkResponse($visitorAsite2->doTrackPageView(''));
		
		// Returning visitor on Idsite 2 1 day later, one page view, with chinese referer
//		$t2->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(48 + 10)->getDatetime());
//		$t2->setUrlReferrer('http://www.baidu.com/s?wd=%D0%C2+%CE%C5&n=2');
//		$t2->setUrl('http://example2.com/home');
//		$this->checkResponse($t2->doTrackPageView('I\'m a returning visitor...'));
	}
}
