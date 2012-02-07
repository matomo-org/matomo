<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * testing various wrong Tracker requests and check that they behave as expected:
 * not throwing errors and not recording data.
 *  
 * API will archive and output empty stats.
 */
class Test_Piwik_Integration_NoVisit extends Test_Integration_Facade
{
	protected $idSite = null;
	protected $dateTime = null;

	public function getApiToTest()
	{
		// this will output empty XML result sets as no visit was tracked
		return array(
			array('all', array('idSite' => $this->idSite, 'date' => $this->dateTime)),
			array('all', array('idSite' => $this->idSite, 'date' => $this->dateTime, 'periods' => array('day', 'week'),
							   'setDateLastN' => true, 'testSuffix' => '_PeriodIsLast')),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'noVisit';
	}

	public function setUp()
	{
		parent::setUp();

		$this->dateTime = '2009-01-04 00:11:42';
		$this->idSite = $this->createWebsite($this->dateTime);
	}

	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
		$idSite = $this->idSite;
		
		// Trigger invalid website
		$trackerInvalidWebsite = $this->getTracker($idSiteFake = 0, $dateTime, $defaultInit = true);
		$response = Piwik_Http::fetchRemoteFile($trackerInvalidWebsite->getUrlTrackPageView());
		$this->assertTrue(strpos($response, 'Invalid idSite') !== false, 'invalid website ID');

		// Trigger wrong website
		$trackerWrongWebsite = $this->getTracker($idSiteFake = 33, $dateTime, $defaultInit = true);
		$response = Piwik_Http::fetchRemoteFile($trackerWrongWebsite->getUrlTrackPageView());
		$this->assertTrue(strpos($response, 'The requested website id = 33 couldn\'t be found') !== false, 'non-existent website ID');

		// Trigger empty request
		$trackerUrl = $this->getTrackerUrl();
		$response = Piwik_Http::fetchRemoteFile($trackerUrl);
		$this->assertTrue(strpos($response, 'web analytics') !== false, 'Piwik empty request response not correct: ' . $response);
		
		$t = $this->getTracker($idSite, $dateTime, $defaultInit = true);
		
		// test GoogleBot UA visitor
		$t->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
		$this->checkResponse($t->doTrackPageView('bot visit, please do not record'));
		
		// Test IP Exclusion works with or without IP exclusion
		foreach(array(false, true) as $enable)
		{
			// 	Enable IP Anonymization
			$t->DEBUG_APPEND_URL = '&forceIpAnonymization=' . (int)$enable;
			
			// test with excluded IP
			$t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)'); // restore normal user agent	
			$excludedIp = '154.1.12.34';
			Piwik_SitesManager_API::getInstance()->updateSite($idSite, 'new site name', $url=array('http://site.com'),$ecommerce = 0, $excludedIp . ',1.2.3.4');
			$t->setIp($excludedIp);
			$this->checkResponse($t->doTrackPageView('visit from IP excluded'));
			
			// test with global list of excluded IPs 
			$excludedIpBis = '145.5.3.4';
			Piwik_SitesManager_API::getInstance()->setGlobalExcludedIps($excludedIpBis);
			$t->setIp($excludedIpBis);
			$this->checkResponse($t->doTrackPageView('visit from IP globally excluded'));
		}
				
		try {
			@$t->setAttributionInfo(array());
			$this->fail();
		} catch(Exception $e) {}
		
		try {
			$t->setAttributionInfo(json_encode('test'));
			$this->fail();
		} catch(Exception $e) {}
		
		$t->setAttributionInfo(json_encode(array()));
	}
}
