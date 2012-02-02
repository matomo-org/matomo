<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitsWithCustomVariables.test.php';

/**
 * Tests use of custom variable segments.
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentContains extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
	public function getApiToTest()
	{
		$return = array();
		
		$api = array('Actions.getPageUrls', 'Actions.getPageTitles', 'VisitsSummary.get');
		$segmentsToTest = array(
		// array( SegmentString , TestSuffix , Array of API to test)
			array("pageTitle=@".urlencode('*_)%'), '_SegmentPageTitleContainsStrangeCharacters', array('Actions.getPageTitles', 'VisitsSummary.get')),
			array("pageUrl=@".urlencode('user/profile'), '_SegmentPageUrlContains', $api),
			array("pageTitle=@Profile%20pa", '_SegmentPageTitleContains', $api),
			array("pageUrl!@user/profile", '_SegmentPageUrlExcludes', $api),
			array("pageTitle!@Profile%20pa", '_SegmentPageTitleExcludes', $api),
		);
		
		foreach($segmentsToTest as $segment) 
		{
			// Also test "Page URL / Page title CONTAINS string" feature
			$return[] = array($segment[2], 
								array('idSite' => $this->idSite, 'date' => $this->dateTime, 'periods' => array('day'),
								  'setDateLastN' => false, 
								  'segment' => $segment[0],
								  'testSuffix' => $segment[1])
								);
		}
		return $return;
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
}

