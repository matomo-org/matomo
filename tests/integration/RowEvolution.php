<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests the method API.getRowEvolution
 */
class Test_Piwik_Integration_RowEvolution extends Test_Integration_Facade
{
	protected $today = '2010-03-06 11:22:33';
	protected $idSite = null;
	
	public function getApiToTest()
	{
		$return = array();
		
		$config = array(
            'testSuffix' => '_referrer1',
            'idSite' => $this->idSite,
            'date' => $this->today,
            'otherRequestParameters' => array(
                'date' => '2010-02-06,2010-03-06',
				'period' => 'day',
				'apiModule' => 'Referers',
				'apiAction' => 'getWebsites',
                'label' => urlencode('www.referrer2.com'),
                'expanded' => 0
            )
        );
		
		$return[] = array('API.getRowEvolution', $config);
		
		
		$config['testSuffix'] = '_referrer2';
		$config['otherRequestParameters']['label'] = urlencode(urlencode('www.referrer0.com').'>'
				.urlencode('theReferrerPage1.html'));
		
		$return[] = array('API.getRowEvolution', $config);
		
		
		$config['testSuffix'] = '_pageTitles';
		$config['otherRequestParameters']['apiModule'] = 'Actions';
		$config['otherRequestParameters']['apiAction'] = 'getPageTitles';
		$config['otherRequestParameters']['label'] = urlencode('incredible title 0');
		
		$return[] = array('API.getRowEvolution', $config);
		
		
		$config['testSuffix'] = '_pageUrls';
		$config['otherRequestParameters']['apiModule'] = 'Actions';
		$config['otherRequestParameters']['apiAction'] = 'getPageUrls';
		$config['otherRequestParameters']['label'] = 'my>dir>'.urlencode('/page3');
		
		$return[] = array('API.getRowEvolution', $config);
		
        
		return $return;
	}
    
	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'RowEvolution';
	}
	
	public function setUp()
	{
		parent::setUp();
		$this->idSite = $this->createWebsite('2010-02-01 11:22:33');
	}

	protected function trackVisits()
	{
		$dateTime = $this->today;
    	$idSite = $this->idSite;
        
		for ($daysIntoPast = 30; $daysIntoPast >= 0; $daysIntoPast--)
		{
			$visitDateTime = Piwik_Date::factory($dateTime)->subDay($daysIntoPast)->getDatetime();
			$t = $this->getTracker($idSite, $visitDateTime, $defaultInit = true);
			$t->setUrlReferrer('http://www.referrer'.($daysIntoPast % 5).'.com/theReferrerPage'.($daysIntoPast % 2).'.html');
			$t->setUrl('http://example.org/my/dir/page'.($daysIntoPast % 4));
			$t->setForceVisitDateTime($visitDateTime);
			$this->checkResponse($t->doTrackPageView('incredible title '.($daysIntoPast % 3)));
		}
	}
}

