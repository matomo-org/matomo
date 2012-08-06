<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * This tests the output of the API plugin API 
 * It will return metadata about all API reports from all plugins
 * as well as the data itself, pre-processed and ready to be displayed
 * @return 
 */
class Test_Piwik_Integration_ApiGetReportMetadata extends Test_Integration_Facade
{
	protected $dateTime = '2009-01-04 00:11:42';
	protected $idSite = null;
	protected $idGoal = null;
	protected $idGoal2 = null;

	public function getApiToTest()
	{
		return array(
			array('API', array('idSite' => $this->idSite, 'date' => $this->dateTime)),
			
			// test w/ hideMetricsDocs=true
			array('API.getMetadata', array('idSite' => $this->idSite, 'date' => $this->dateTime,
										   'apiModule' => 'Actions', 'apiAction' => 'get',
										   'testSuffix' => '_hideMetricsDoc',
										   'otherRequestParameters' => array('hideMetricsDoc' => 1)) ),
			array('API.getProcessedReport', array('idSite' => $this->idSite, 'date' => $this->dateTime,
												  'apiModule' => 'Actions', 'apiAction' => 'get',
												  'testSuffix' => '_hideMetricsDoc',
												  'otherRequestParameters' => array('hideMetricsDoc' => 1)) ),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}

	public function getOutputPrefix()
	{
		return 'apiGetReportMetadata';
	}
	
	public function setUp()
	{
		parent::setUp();
		
		$this->idSite = $this->createWebsite($this->dateTime, $ecommerce = 1);
		$this->idGoal = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions = 1);
		$this->idGoal2 = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'Goal 2 - Hello', 'url', 'hellow', 'contains', $caseSensitive=false, $revenue=10, $allowMultipleConversions = 0);
		$this->idGoal3 = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
	}

	protected function trackVisits()
	{
		$idSite = $this->idSite;
		$dateTime = $this->dateTime;

        $t = $this->getTracker($idSite, $dateTime, $defaultInit = true);

    	// Record 1st page view
        $t->setUrl( 'http://example.org/index.htm' );
        $this->checkResponse($t->doTrackPageView( 'incredible title!'));

        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($this->idGoal3, $revenue = 42.256));
	}
}

