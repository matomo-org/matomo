<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/TwoVisitsWithCustomVariables.test.php';

/**
 * Tests use of custom variable segments.
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchVisitorType extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
	public function getApiToTest()
	{
		// Segment matching some
		$segments = array('customVariableName1==VisitorType;customVariableValue1==LoggedIn',
						'customVariableName1==VisitorType;customVariableValue1=@LoggedI');
		
		$apiToCall = array('Referers.getKeywords', 'CustomVariables.getCustomVariables', 'VisitsSummary.get');
		
		$periods = array('day', 'week');
		
		// We run it twice just to check that running archiving twice for same input parameters doesn't create more records/overhead
		$result = array();
		for($i = 1; $i <= 2; $i++)
		{
			foreach($segments as $segment)
			{
				$result[] = array(
					$apiToCall, array('idSite' => 'all', 'date' => $this->dateTime, 'periods' => $periods,
									  'setDateLastN' => true, 'segment' => $segment)
				);
			}
		}


		return $result;
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function getOutputPrefix()
	{
		return 'twoVisitsWithCustomVariables_segmentMatchVisitorType';
	}
	
	public function test_RunAllTests()
	{
		parent::test_RunAllTests();
		
		// ----------------------------------------------
		// Implementation Checks
		// ---------------------------------------------- 
		// Verify that, when a segment is specified, only the requested report is processed
		// In this case, check that only the Custom Variables blobs have been processed
		
		if (Test_Integration::$apiTestingLevel != Test_Integration::NO_API_TESTING)
		{
			$tests = array(
				// 1) CHECK 'day' archive stored in January
				// We expect 2 segments * (1 custom variable name + 2 ref metrics + 6 subtable for the custom var values + 5 Referers blob)
				'archive_blob_2010_01' => 28,
				// This contains all 'last N' weeks & days, 
				// (1 metrics 
				//  + 2 referer metrics 
				//  + 3 done flag ) 
				//  * 2 segments 
				// + 1 Done flag per Plugin, for each "Last N" date
				'archive_numeric_2010_01' => 138,
		
				// 2) CHECK 'week' archive stored in December (week starts the month before)
				// We expect 2 segments * (1 custom variable name + 2 ref metrics + 6 subtable for the values of the name + 5 referers blob)
				'archive_blob_2009_12' => 28,
				// 6 metrics, 
				// 2 Referer metrics (Referers_distinctSearchEngines/Referers_distinctKeywords), 
				// 3 done flag (referers, CustomVar, VisitsSummary), 
				// X * 2 segments
				'archive_numeric_2009_12' => (6 + 2 + 3) * 2,
			);
			foreach($tests as $table => $expectedRows)
			{
				$sql = "SELECT count(*) FROM " . Piwik_Common::prefixTable($table) ;
				$countBlobs = Zend_Registry::get('db')->fetchOne($sql);
				$this->assertEqual( $expectedRows, $countBlobs, "$table: %s");
			}
		}
	}
}

