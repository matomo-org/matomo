<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitsWithCustomVariables.test.php';

class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchALL_NoGoalData extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
	public function __construct($title = '')
	{
		parent::__construct($title);
		
		$this->width = 1111;
		$this->height = 222;
		$this->doExtraQuoteTests = false;
	}

	public function getApiToTest()
	{
		$apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');
	
        // Segment matching ALL
        // + adding DOES NOT CONTAIN segment always matched, to test this particular operator
		$resolution = $this->width.'x'.$this->height;
        $segment = 'resolution=='.$resolution.';customVariableName1!@randomvalue does not exist';

		return array(
			array($apiToCall, array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => array('day', 'week'),
									'setDateLastN' => true, 'segment' => $segment))
		);
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
		return 'twoVisitsWithCustomVariables_segmentMatchALL_noGoalData';
	}
}

