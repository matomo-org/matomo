<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests the URL normalization.
 */
class Test_Piwik_Integration_UrlNormalization extends Test_Integration_Facade
{
	protected $dateTime = '2010-03-06 11:22:33';
	protected $idSite = null;
	
	public function getApiToTest()
	{
		$return = array();
		$return[] = array('Actions.getPageUrls', array(
			'testSuffix' => '_urls',
            'idSite' => $this->idSite,
            'date' => $this->dateTime,
        ));
		$return[] = array('Actions.getPageTitles', array(
			'testSuffix' => '_titles',
            'idSite' => $this->idSite,
            'date' => $this->dateTime,
        ));
		$return[] = array('Actions.getPageUrls', array(
			'testSuffix' => '_pagesSegmented',
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'segment' => 'pageUrl==https://WWw.example.org/foo/bar2.html',
		));
		$return[] = array('Actions.getPageUrls', array(
			'testSuffix' => '_pagesSegmented',
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'segment' => 'pageUrl==example.org/foo/bar2.html',
		));
		$return[] = array('Actions.getPageUrls', array(
			'testSuffix' => '_pagesSegmentedRef',
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'segment' => 'referrerUrl==http://www.google.com/search?q=piwik',
		));
		$return[] = array('Referers.getKeywordsForPageUrl', array(
			'testSuffix' => '_keywords',
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'otherRequestParameters' => array(
				'url' => 'http://WWW.example.org/foo/bar.html'
			)
		));
		return $return;
	}
    
	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'UrlNormalization';
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->idSite = $this->createWebsite($this->dateTime);
	}

	protected function trackVisits()
	{
		$dateTime = $this->dateTime;
    	$idSite = $this->idSite;
        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);
        
		$t->setUrlReferrer('http://www.google.com/search?q=piwik');
        $t->setUrl('http://example.org/foo/bar.html');
        $this->checkResponse($t->doTrackPageView('http://incredible.title/'));
        
        $t->setUrl('https://example.org/foo/bar.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
        $this->checkResponse($t->doTrackPageView('https://incredible.title/'));
        
        $t->setUrl('https://wWw.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackPageView('http://www.incredible.title/'));
        
        $t->setUrl('http://WwW.example.org/foo/bar2.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.4)->getDatetime());
        $this->checkResponse($t->doTrackPageView('https://www.incredible.title/'));
        
        $t->setUrl('http://www.example.org/foo/bar3.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.5)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible.title/'));
        
        $t->setUrl('https://example.org/foo/bar4.html');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.6)->getDatetime());
        $this->checkResponse($t->doTrackPageView('incredible.title/'));
	}
	
	public function test_RunAllTests()
	{
		parent::test_RunAllTests();
		
		$sql = "SELECT count(*) FROM " . Piwik_Common::prefixTable('log_action');
		$count = Zend_Registry::get('db')->fetchOne($sql);
		$expected = 9; // 4 urls + 5 titles
		$this->assertEqual( $expected, $count, "only $expected actions expected" );
		
		$sql = "SELECT name, url_prefix FROM " . Piwik_Common::prefixTable('log_action')
				. " WHERE type = " . Piwik_Tracker_Action::TYPE_ACTION_URL
				. " ORDER BY idaction ASC";
		$urls = Zend_Registry::get('db')->fetchAll($sql);
		$expected = array(
			array('name' => 'example.org/foo/bar.html', 'url_prefix' => 0),
			array('name' => 'example.org/foo/bar2.html', 'url_prefix' => 3),
			array('name' => 'example.org/foo/bar3.html', 'url_prefix' => 1),
			array('name' => 'example.org/foo/bar4.html', 'url_prefix' => 2)
		);
		$this->assertEqual( $expected, $urls, "normalization went wrong" );
	}
	
}

