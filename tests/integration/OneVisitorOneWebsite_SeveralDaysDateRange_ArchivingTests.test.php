<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/OneVisitorOneWebsite_SeveralDaysDateRange.test.php';

/**
 * Tests some API using range periods & makes sure the correct amount of blob/numeric
 * archives are created.
 */
class Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange_ArchivingTests extends Test_Piwik_Integration_OneVisitorOneWebsite_SeveralDaysDateRange
{
	public function getApiToTest()
	{
		$apiToCall = array('Actions.getPageUrls',
						   'VisitsSummary.get',
						   'UserSettings.getResolution',
						   'VisitFrequency.get',
						   'VisitTime.getVisitInformationPerServerTime');

		// 2 segments: ALL and another way of expressing ALL but triggering the Segment code path 
		$segments = array(
			false,
			'country!=aa',
			'pageUrl!=ThisIsNotKnownPageUrl',
		);

		// Running twice just as health check that second call also works
		$result = array();
		for($i = 0; $i <=1; $i++)
		{
			foreach($segments as $segment)
			{
				$result[] = array($apiToCall, array('idSite' => $this->idSite, 'date' => '2010-12-15,2011-01-15',
													'periods' => array('range'), 'segment' => $segment));
			}
		}
		
		return $result;
	}

	public function getControllerActionsToTest()
	{
		return array();
	}
	
	public function test_RunAllTests()
	{
		parent::test_RunAllTests();
		
		if (Test_Integration::$apiTestingLevel != Test_Integration::NO_API_TESTING)
		{
			$this->_checkExpectedRowsInArchiveTable();
			
			// Force Purge to happen again this table (since it was called at end of archiving)
			Piwik_SetOption( Piwik_ArchiveProcessing_Period::FLAG_TABLE_PURGED . Piwik_Common::prefixTable('archive_blob_2010_12'), '2010-01-01');
			Piwik_SetOption( Piwik_ArchiveProcessing_Period::FLAG_TABLE_PURGED . Piwik_Common::prefixTable('archive_blob_2011_01'), '2010-01-01');
			
			foreach(array('archive_numeric_2010_12','archive_blob_2010_12','archive_numeric_2011_01','archive_blob_2011_01') as $table ) 
			{
				// INSERT some ERROR records.
				// We use period=range since the test below is restricted to these
				Piwik_Query(" INSERT INTO `"
					. Piwik_Common::prefixTable($table)
					."` (`idarchive` ,`name` ,`idsite` ,`date1` ,`date2` ,`period` ,`ts_archived` ,`value`)
					VALUES  (10000, 'done', '1', '2010-12-06', '2010-12-06', '".Piwik::$idPeriods['range']."', '2010-12-06' , '".Piwik_ArchiveProcessing::DONE_ERROR."'), 
							(20000, 'doneX', '2', '2012-07-06', '2012-07-06', '".Piwik::$idPeriods['range']."', '' , '".Piwik_ArchiveProcessing::DONE_ERROR."')"
				);
			}
			
			// We've added error rows which should fail the following test
			$this->_checkExpectedRowsInArchiveTable( $expectPass = false );
			
			// Test Scheduled tasks Table Optimize, and Delete old reports
			Piwik_CoreAdminHome::purgeOutdatedArchives();
			Piwik_CoreAdminHome::optimizeArchiveTable();
			
			// Check that only the good reports were not deleted:
			$this->_checkExpectedRowsInArchiveTable();
		}
	}
	
	// Check that requesting period "Range" means 
	protected function _checkExpectedRowsInArchiveTable( $expectPass = true )
	{
		// only processing the requested Plugin blob (Actions in this case), not all Plugins blobs
		$tests = array(
			// 4 blobs for the Actions plugin, 7 blobs for UserSettings, 2 blobs VisitTime
			'archive_blob_2010_12' => (4 + 7 + 2) * 3, 
			// (VisitsSummary 5 metrics + 1 flag - no Unique visitors for range) 
			// + 1 flag archive UserSettings
			// + (Actions 1 flag + 2 metrics - pageviews, unique pageviews)
			// + (Frequency 5 metrics + 1 flag)
			// + 1 flag VisitTime 
			// * 3 segments
			'archive_numeric_2010_12' => (6 + 1 + 3 + 6 + 1 ) * 3,   
	
			// all "Range" records are in December
			'archive_blob_2011_01' => 0,
			'archive_numeric_2011_01' => 0,
		);
		foreach($tests as $table => $expectedRows)
		{
			$sql = "SELECT count(*) FROM " . Piwik_Common::prefixTable($table) . " WHERE period = ".Piwik::$idPeriods['range'];
			$countBlobs = Zend_Registry::get('db')->fetchOne($sql);
			if($expectPass) {
				$this->assertEqual( $expectedRows, $countBlobs, "$table expected $expectedRows, got $countBlobs" );
			} else {
				$this->assertTrue( $expectedRows < $countBlobs, "$table expected $expectedRows < $countBlobs" );
			}
		}
		
	}
}

