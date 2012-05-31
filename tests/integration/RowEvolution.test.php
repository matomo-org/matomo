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
		
		// Websites, hierarchical
		$config['testSuffix'] = '_referrer2';
		$referrerLabel = urlencode('www.referrer0.com').'>'.urlencode('theReferrerPage1.html');
		$config['otherRequestParameters']['label'] = urlencode($referrerLabel);
		$return[] = array('API.getRowEvolution', $config);
		
		// Websites, multiple labels including one hierarchical
		$config['testSuffix'] = '_referrerMulti1';
		$referrerLabel = urlencode($referrerLabel).','.urlencode('www.referrer2.com');
		$config['otherRequestParameters']['label'] = urlencode($referrerLabel);
		$return[] = array('API.getRowEvolution', $config);
		
        // Keywords, label containing > and ,
		$config['otherRequestParameters']['apiAction'] = 'getKeywords';
		$config['testSuffix'] = '_LabelReservedCharacters';
		$keywords = urlencode($this->keywords[0]).','.urlencode($this->keywords[1]);
		$config['otherRequestParameters']['label'] = urlencode($keywords);
		$return[] = array('API.getRowEvolution', $config);
        
		// Keywords, hierarchical
		$config['otherRequestParameters']['apiAction'] = 'getSearchEngines';
		$config['testSuffix'] = '_LabelReservedCharactersHierarchical';
		$keywords = "Google>".urlencode(strtolower($this->keywords[0]))
					.',Google>'.urlencode(strtolower($this->keywords[1]))
					.',Google>'.urlencode(strtolower($this->keywords[2]));
		// Test multiple labels search engines, Google should also have a 'logo' entry
		$config['otherRequestParameters']['label'] = urlencode($keywords) . ",Google";
		$return[] = array('API.getRowEvolution', $config);
		
		// Actions > Pages titles, standard label
		$config['testSuffix'] = '_pageTitles';
		$config['periods'] = array('day', 'week');
		$config['otherRequestParameters']['apiModule'] = 'Actions';
		$config['otherRequestParameters']['apiAction'] = 'getPageTitles';
		$config['otherRequestParameters']['label'] = urlencode('incredible title 0');
		$return[] = array('API.getRowEvolution', $config);
		
		// Actions > Page titles, multiple labels
		$config['testSuffix'] = '_pageTitlesMulti';
		$label = urlencode('incredible title 0').','.urlencode('incredible title 2');
		$config['otherRequestParameters']['label'] = urlencode($label);
		$return[] = array('API.getRowEvolution', $config);
		
		// Actions > Page URLS, hierarchical label
		$config['testSuffix'] = '_pageUrls';
		$config['periods'] = array('range');
		$config['otherRequestParameters']['date'] = '2010-03-01,2010-03-06';
		$config['otherRequestParameters']['apiModule'] = 'Actions';
		$config['otherRequestParameters']['apiAction'] = 'getPageUrls';
		$config['otherRequestParameters']['label'] = urlencode('my>dir>'.urlencode('/page3?foo=bar&baz=bar'));
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
        
    	$this->keywords = array(
    		'free > proprietary', // BUG! testing a keyword containing > 
    		'peace "," not war', // testing a keyword containing ,
    		'justice )(&^#%$ NOT corruption!',
    	);
		for ($daysIntoPast = 30; $daysIntoPast >= 0; $daysIntoPast--)
		{
			// Visit 1: referrer website + test page views
			$visitDateTime = Piwik_Date::factory($dateTime)->subDay($daysIntoPast)->getDatetime();
			$t = $this->getTracker($idSite, $visitDateTime, $defaultInit = true);
			$t->setUrlReferrer('http://www.referrer'.($daysIntoPast % 5).'.com/theReferrerPage'.($daysIntoPast % 2).'.html');
			$t->setUrl('http://example.org/my/dir/page'.($daysIntoPast % 4).'?foo=bar&baz=bar');
			$t->setForceVisitDateTime($visitDateTime);
			$this->checkResponse($t->doTrackPageView('incredible title '.($daysIntoPast % 3)));
			
			// VISIT 2: search engine
			$t->setForceVisitDateTime(Piwik_Date::factory($visitDateTime)->addHour(3)->getDatetime());
			$t->setUrlReferrer('http://google.com/search?q='.urlencode($this->keywords[$daysIntoPast%3]));
			$this->checkResponse($t->doTrackPageView('not an incredible title '));
		}
	}
}

