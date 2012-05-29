<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitsWithCustomVariables.test.php';

/**
 * Test CSV export with Expanded rows, Translated labels, Different languages
 */
class Test_Piwik_Integration_CsvExport extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
	public function __construct( $testName = '' )
	{
		parent::__construct($testName);
		$this->useEscapedQuotes = false;
		$this->doExtraQuoteTests = false;
	}
	
	public function getApiToTest()
	{
		$apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

		$enExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 0, 'translateColumnNames' => 1);

		$deExtraParam = array('expanded' => 0, 'flat' => 1, 'include_aggregate_rows' => 1, 'translateColumnNames' => 1);

		return array(
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime, 'format' => 'csv',
									'otherRequestParameters' => array('expanded' => 0, 'flat' => 0),
									'testSuffix' => '_xp0')),
			
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime, 'format' => 'csv',
									'otherRequestParameters' => $enExtraParam, 'language' => 'en',
									'testSuffix' => '_xp1_inner0_trans-en')),
			
			array($apiToCall, array('idSite' => $this->idSite, 'date' => $this->dateTime, 'format' => 'csv',
									'otherRequestParameters' => $deExtraParam, 'language' => 'de',
									'testSuffix' => '_xp1_inner1_trans-de')),
		);
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'csvExport';
	}
}

