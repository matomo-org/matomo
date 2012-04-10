<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * Tests the flattening of reports.
 */
class Test_Piwik_Integration_FlattenReports extends Test_Integration_Facade
{
	protected $dateTime = '2010-03-06 11:22:33';
	protected $idSite = null;
	
	public function getApiToTest()
	{
        $return = array();
        
		// referrers
		$return[] = array('Referers.getWebsites', array(
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'otherRequestParameters' => array(
				'flat' => '1',
				'expanded' => '0'
			)
		));
		
		// urls
		$return[] = array('Actions.getPageUrls', array(
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'otherRequestParameters' => array(
				'flat' => '1',
				'expanded' => '0'
			)
		));
		$return[] = array('Actions.getPageUrls', array(
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'testSuffix' => '_withAggregate',
			'otherRequestParameters' => array(
				'flat' => '1',
				'include_aggregate_rows' => '1',
				'expanded' => '0'
			)
		));
		
		// custom variables for multiple days
		$return[] = array('CustomVariables.getCustomVariables', array(
			'idSite' => $this->idSite,
			'date' => $this->dateTime,
			'otherRequestParameters' => array(
				'date' => '2010-03-06,2010-03-08',
				'flat' => '1',
				'include_aggregate_rows' => '1',
				'expanded' => '0'
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
		return 'FlattenReports';
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
		
		for ($referrerSite = 1; $referrerSite < 4; $referrerSite++)
		{
			for ($referrerPage = 1; $referrerPage < 3; $referrerPage++)
			{
				$offset = $referrerSite * 3 + $referrerPage;
				$t = $this->getTracker($idSite, Piwik_Date::factory($dateTime)->addHour($offset)->getDatetime());
				$t->setUrlReferrer('http://www.referrer'.$referrerSite.'.com/sub/dir/page'.$referrerPage.'.html');
				$t->setCustomVariable(1, 'CustomVarVisit', 'CustomVarValue'.$referrerPage, 'visit');
				for ($page = 0; $page < 3; $page++) {
					$t->setUrl('http://example.org/dir'.$referrerSite.'/sub/dir/page'.$page.'.html');
					$t->setCustomVariable(1, 'CustomVarPage', 'CustomVarValue'.$page, 'page');
        			$this->checkResponse($t->doTrackPageView('title'));
				}
			}
		}
		
		$t = $this->getTracker($idSite, Piwik_Date::factory($dateTime)->addHour(24)->getDatetime());
		$t->setCustomVariable(1, 'CustomVarVisit', 'CustomVarValue1', 'visit');
		$t->setUrl('http://example.org/sub/dir/dir1/page1.html');
		$t->setCustomVariable(1, 'CustomVarPage', 'CustomVarValue1', 'page');
		$this->checkResponse($t->doTrackPageView('title'));
	}
}

