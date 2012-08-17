<?php

/**
 * Tests the transitions plugin.
 */
class Test_Piwik_Integration_Transitions extends IntegrationTestCase
{
	protected static $dateTime = '2010-03-06 11:22:33';
	protected static $idSite = 1;
	
	private static $prefixCounter = 0;
	
	public static function setUpBeforeClass() 
	{ 
		parent::setUpBeforeClass(); 
		try { 
			self::setUpWebsitesAndGoals(); 
			self::trackVisits(); 
		} catch(Exception $e) { 
			// Skip whole test suite if an error occurs during setup 
			throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage()); 
		} 
	}
	
	/** 
	 * @dataProvider getApiForTesting 
	 * @group        Integration 
	 * @group        Transitions 
	 */ 
	public function testApi($api, $params) 
	{ 
		$this->runApiTests($api, $params);
	} 
	
	public function getApiForTesting() 
	{ 
		$return = array();
		$return[] = array('Transitions.getFullReport', array(
			'idSite' => self::$idSite,
			'date' => self::$dateTime,
			'otherRequestParameters' => array(
				'pageUrl' => 'http://example.org/page/one.html',
				'limitBeforeGrouping' => 2
			)
        ));
		return $return;
	}
    
	public function getOutputPrefix()
	{
		return 'Transitions';
	}
	
	protected static function setUpWebsitesAndGoals()
	{
		self::createWebsite(self::$dateTime);
	}

	protected static function trackVisits()
	{
		$visit1 = self::createVisit(1);
		$visit1->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q=%3C%3E%26%5C%22the%20pdo%20extension%20is%20required%20for%20this%20adapter%20but%20the%20extension%20is%20not%20loaded&source=web&cd=4&ved=0FjAD&url=http%3A%2F%2Fforum.piwik.org%2Fread.php%3F2%2C1011&ei=y-HHAQ&usg=AFQjCN2-nt5_GgDeg&cad=rja');
		self::trackPageView($visit1, 0, 'page/one.html');
		self::trackPageView($visit1, 0.1, 'sub/dir/page2.html');
		self::trackPageView($visit1, 0.2, 'page/one.html');
		self::trackPageView($visit1, 0.3, 'the/third_page.html?foo=bar');
		self::trackPageView($visit1, 0.4, 'page/one.html');
		self::trackPageView($visit1, 0.5, 'the/third_page.html?foo=bar');
		self::trackPageView($visit1, 0.6, 'page/one.html');
		self::trackPageView($visit1, 0.7, 'the/third_page.html?foo=baz#anchor1');
		self::trackPageView($visit1, 0.8, 'page/one.html');
		self::trackPageView($visit1, 0.9, 'page/one.html');
		self::trackPageView($visit1, 1.0, 'the/third_page.html?foo=baz#anchor2');
		self::trackPageView($visit1, 1.1, 'page/one.html');
		self::trackPageView($visit1, 1.2, 'page3.html');
        
		$visit2 = self::createVisit(2);
		$visit2->setUrlReferrer('http://www.external.com.vn/referrerPage-notCounted.html');
		self::trackPageView($visit2, 0, 'sub/dir/page2.html');
		self::trackPageView($visit2, 0.1, 'the/third_page.html?foo=bar');
		self::trackPageView($visit2, 0.2, 'page/one.html');
		self::trackPageView($visit2, 0.3, 'the/third_page.html?foo=baz#anchor1');
        
		$visit3 = self::createVisit(3);
		$visit3->setUrlReferrer('http://www.external.com.vn/referrerPage-counted.html');
		self::trackPageView($visit3, 0.1, 'page/one.html');
		self::trackPageView($visit3, 0.2, 'sub/dir/page2.html');
		self::trackPageView($visit3, 0.3, 'page/one.html');
		
		$visit4 = self::createVisit(4);
		self::trackPageView($visit4, 0, 'page/one.html?pk_campaign=TestCampaign&pk_kwd=TestKeyword');
		
		$visit5 = self::createVisit(5);
		self::trackPageView($visit5, 0, 'page/one.html');
	}
	
	private static function createVisit($id) {
		$visit = self::getTracker(self::$idSite, self::$dateTime, $defaultInit = true);
		$visit->setIp('156.5.3.'.$id);
		return $visit;
	}
	
	private static function trackPageView($visit, $timeOffset, $path) {
		// rotate protocol and www to make sure it doesn't matter
		$prefixes = array('http://', 'http://www.', 'https://', 'https://');
		$prefix = $prefixes[self::$prefixCounter];
		self::$prefixCounter = (self::$prefixCounter + 1) % 4;
		
		/** @var $visit PiwikTracker */
		$visit->setUrl($prefix.'example.org/'.$path);
		$visit->setForceVisitDateTime(Piwik_Date::factory(self::$dateTime)->addHour($timeOffset)->getDatetime());
		self::checkResponse($visit->doTrackPageView('page title'));
	}
	
}
