<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitors_TwoWebsites_DifferentDays.test.php';

/**
 * Same as TwoVisitors_twoWebsites_differentDays but with goals that convert
 * on every url.
 */
class Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays_Conversions extends Test_Piwik_Integration_TwoVisitors_TwoWebsites_DifferentDays
{
	public function __construct($title = '')
	{
		parent::__construct($title);
		$this->allowConversions = true;
	}

	protected function getApiToCall()
	{
		return array('Goals.getDaysToConversion', 'MultiSites.getAll');
	}

	public function getApiToTest()
	{
		$result = parent::getApiToTest();

		// Tests that getting a visits summary metric (nb_visits) & a Goal's metric (Goal_revenue)
		// at the same time works.
		$dateTime = '2010-01-03,2010-01-06';
		$columns = 'nb_visits,'.Piwik_Goals::getRecordName('conversion_rate');
		
		$result[] = array(
			'VisitsSummary.get', array('idSite' => 'all', 'date' => $dateTime, 'periods' => 'range',
									   'otherRequestParameters' => array('columns' => $columns),
									   'testSuffix' => '_getMetricsFromDifferentReports')
		);

		return $result;
	}

	public function getControllerActionsToTest()
	{
		return array(
			// test MultiSites.index using default testing level
			array('MultiSites.index', array('date' => $this->dateTime, 'period' => 'month', 'idSite' => $this->idSite1)),
			
			// test all widgets using default testing level
			array('all', array('date' => $this->dateTime, 'period' => 'day', 'idSite' => $this->idSite1)),
		);
	}
	
	public function getOutputPrefix()
	{
		return 'TwoVisitors_twoWebsites_differentDays_Conversions';
	}
}
