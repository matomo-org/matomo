<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitsWithCustomVariables.test.php';

/**
 * testing a segment containing all supported fields
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchNONE extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
	// set lazily so when testing, asserts can be run
	private $segment = null;

	public function __construct($title = '')
	{
		parent::__construct($title);
		$this->doExtraQuoteTests = false;
	}
	
	public function getApiToTest()
	{
		$apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

		return array(
			array($apiToCall, array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => array('day', 'week'),
									'setDateLastN' => true, 'segment' => $this->getSegmentToTest(false)))
		);
	}
	
	public function getSegmentToTest($calledDuringTest)
	{
		if ($this->segment)
		{
			return $this->segment;
		}

		// Segment matching NONE
		$segments = Piwik_API_API::getInstance()->getSegmentsMetadata($this->idSite);
		$segmentExpression = array();
		
		$seenVisitorId = false;
		foreach($segments as $segment) { 
			$value = 'campaign';
			if($segment['segment'] == 'visitorId')
			{
				$seenVisitorId = true;
				$value = '34c31e04394bdc63';
			}
			if($segment['segment'] == 'visitEcommerceStatus')
			{
				$value = 'none';
			}
			$segmentExpression[] = $segment['segment'] .'!='.$value;
		}

		$this->segment = implode(";", $segmentExpression);

		// just checking that this segment was tested (as it has the only visible to admin flag)
		if ($calledDuringTest)
		{
			$this->assertTrue($seenVisitorId);
			$this->assertTrue(strlen($this->segment) > 100);
		}
		
		return $this->segment;
	}

	public function getAnotherApiToTest()
	{
		return array();
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'twoVisitsWithCustomVariables_segmentMatchNONE';
	}
	
	public function test_RunAllTests()
	{
		// get the segment used & make sure to do some extra tests before testing the API
		$this->getSegmentToTest(true);

		parent::test_RunAllTests();
	}
}

