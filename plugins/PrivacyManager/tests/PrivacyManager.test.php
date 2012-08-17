<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

class Test_Piwik_PrivacyManager extends Test_Integration
{
	// constants used in checking whether numeric tables are populated correctly.
	// 'done' entries exist for every period, even if there's no metric data, so we need the 
	// total archive count for each month.
	const TOTAL_JAN_ARCHIVE_COUNT = 37; // 31 + 4 + 1 + 1;
	const TOTAL_FEB_ARCHIVE_COUNT = 34; // 29 + 4 + 1;
	
	// the number of archive entries for a single metric if no purging is done. this #
	// is dependent on the number of periods for which there were visits.
	const JAN_METRIC_ARCHIVE_COUNT = 11; // 5 days + 4 weeks + 1 month + 1 year
	const FEB_METRIC_ARCHIVE_COUNT = 11; // 6 days + 4 weeks + 1 month

	// fake metric/report name used to make sure unwanted metrics are purged
	const GARBAGE_FIELD = 'abcdefg';
	
	private $idSite = null;
	private $dateTime = null;
	private $instance = null;
	private $daysAgoStart = 50;
	private $settings = null;
	
	private $unusedIdAction = null;
	
	public function __construct( $title = '' )
	{
		parent::__construct($title);
		$this->dateTime = Piwik_Date::factory('2012-02-28');
	}

	public function setUp()
	{
		parent::setUp();
		
		Piwik_TablePartitioning::$tablesAlreadyInstalled = null;

		Piwik_PrivacyManager_LogDataPurger::$selectSegmentSize = 2;
		Piwik_PrivacyManager_ReportsPurger::$selectSegmentSize = 2;
		Piwik::$lockPrivilegeGranted = null;
		
		// purging depends upon today's date, so 'older_than' parts must be dependent upon today
		$today = Piwik_Date::factory('today');
		$daysSinceToday = ($today->getTimestamp() - $this->dateTime->getTimestamp()) / (24 * 60 * 60);
		
		$monthsSinceToday = 0;
		for ($date = $today; $date->toString('Y-m') != $this->dateTime->toString('Y-m'); $date = $date->subMonth(1))
		{
			++$monthsSinceToday;
		}
		
		// set default config
		$settings = array();
		$settings['delete_logs_enable'] = 1;
		// purging log data from before 2012-01-24
		$settings['delete_logs_older_than'] = 35 + $daysSinceToday;
		$settings['delete_logs_schedule_lowest_interval'] = 7;
		$settings['delete_logs_max_rows_per_query'] = 100000;
		$settings['delete_reports_enable'] = 1;
		$settings['delete_reports_older_than'] = $monthsSinceToday;
		$settings['delete_reports_keep_basic_metrics'] = 0;
		$settings['delete_reports_keep_day_reports'] = 0;
		$settings['delete_reports_keep_week_reports'] = 0;
		$settings['delete_reports_keep_month_reports'] = 0;
		$settings['delete_reports_keep_year_reports'] = 0;
		$settings['delete_reports_keep_range_reports'] = 0;
		$settings['delete_reports_keep_segment_reports'] = 0;
		Piwik_PrivacyManager::savePurgeDataSettings($settings);
		
		$this->settings = $settings;
		$this->instance = new Piwik_PrivacyManager();
	}
	
	public function tearDown()
	{
		parent::tearDown();
		
		Piwik::$lockPrivilegeGranted = null;
		
		// remove archive tables (integration test teardown will only truncate)
		$archiveTables = $this->getArchiveTableNames();
		$archiveTables = array_merge($archiveTables['numeric'], $archiveTables['blob']);
		foreach ($archiveTables as $table)
		{
			Piwik_Query("DROP TABLE IF EXISTS ".Piwik_Common::prefixTable($table));
		}
		
		// refresh table name caches so next test will pass
		Piwik_TablePartitioning::$tablesAlreadyInstalled = null;
		Piwik::getTablesInstalled(true);
		
		// drop temporary tables
		$tempTableName = Piwik_PrivacyManager_LogDataPurger::TEMP_TABLE_NAME;
		Piwik_Query("DROP TABLE IF EXISTS ".Piwik_Common::prefixTable($tempTableName));
	}
	
	/** Make sure the first time deleteLogData is run, nothing happens. */
	public function test_deleteLogData_initialRun()
	{
		$this->addLogData();
		$this->addReportData();
		
		$this->instance->deleteLogData();
		
		// check that initial option is set
		$this->assertEqual(
			1, Piwik_GetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL));
		
		// perform other checks
		$this->checkNoDataChanges();
	}
	
	/** Make sure the first time deleteReportData is run, nothing happens. */
	public function test_deleteReportData_initialRun()
	{
		$this->addLogData();
		$this->addReportData();
		
		$this->instance->deleteReportData();
		
		// check that initial option is set
		$this->assertEqual(
			1, Piwik_GetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL));
		
		// perform other checks
		$this->checkNoDataChanges();
	}
	
	/** Make sure the task is not run when its scheduled for later. */
	public function test_purgeData_notTimeToRun()
	{
		$this->addLogData();
		$this->addReportData();
		
		$yesterdaySecs = Piwik_Date::factory('yesterday')->getTimestamp();
		
		Piwik_SetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL, 1);
		Piwik_SetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS, $yesterdaySecs);
		Piwik_SetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_REPORTS, $yesterdaySecs);
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkNoDataChanges();
	}
	
	/** Make sure purging data runs when scheduled. */
	public function test_purgeData_notInitialAndTimeToRun()
	{
		$this->addLogData();
		$this->addReportData();
		
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => -1
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		
		$archiveTables = $this->getArchiveTableNames();
		
		// January numeric table should be dropped
		$this->assertFalse($this->tableExists($archiveTables['numeric'][0])); // January
		
		// Check february metric count (5 metrics per period w/ visits + 1 'done' archive for every period)
		// + 1 garbage metric
		$febRowCount = self::FEB_METRIC_ARCHIVE_COUNT * 5 + self::TOTAL_FEB_ARCHIVE_COUNT + 1;
		$this->assertEqual($febRowCount, $this->getTableCount($archiveTables['numeric'][1])); // February
		
		// January blob table should be dropped
		$this->assertFalse($this->tableExists($archiveTables['blob'][0])); // January
		
		// Check february blob count (1 blob per period w/ visits + 1 garbage report)
		$this->assertEqual(self::FEB_METRIC_ARCHIVE_COUNT + 1, $this->getTableCount($archiveTables['blob'][1])); // February
	}
	
	/** Make sure nothing happens when deleting logs & reports are both disabled. */
	public function test_purgeData_bothDisabled()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_logs_enable' => 0,
			'delete_reports_enable' => 0
		));

		$this->addLogData();
		$this->addReportData();
		
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array();
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkNoDataChanges();
	}
	
	/** Test that purgeData works when there's no data. */
	public function test_purgeData_deleteLogsNoData()
	{
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array();
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->assertEqual(0, $this->getTableCount('log_visit'));
		$this->assertEqual(0, $this->getTableCount('log_conversion'));
		$this->assertEqual(0, $this->getTableCount('log_link_visit_action'));
		$this->assertEqual(0, $this->getTableCount('log_conversion_item'));
		
		$archiveTables = $this->getArchiveTableNames();
		$this->assertFalse($this->tableExists($archiveTables['numeric'][0])); // January
		$this->assertFalse($this->tableExists($archiveTables['numeric'][1])); // February
		$this->assertFalse($this->tableExists($archiveTables['blob'][0])); // January
		$this->assertFalse($this->tableExists($archiveTables['blob'][1])); // February
	}
	
	/** Test that purgeData works correctly when the 'keep basic metrics' setting is set to true. */
	public function test_purgeData_deleteReportsKeepBasicMetrics()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_basic_metrics' => 1
		));
		
		$this->addLogData();
		$this->addReportData();

		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => 1, // remove the garbage metric
			Piwik_Common::prefixTable('archive_blob_2012_01') => -1
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		
		$archiveTables = $this->getArchiveTableNames();
		
		// all numeric metrics should be saved except the garbage metric
		$janRowCount = $this->getExpectedNumericArchiveCountJan() - 1;
		$this->assertEqual($janRowCount, $this->getTableCount($archiveTables['numeric'][0])); // January
		
		// check february numerics not deleted
		$febRowCount = $this->getExpectedNumericArchiveCountFeb();
		$this->assertEqual($febRowCount, $this->getTableCount($archiveTables['numeric'][1])); // February
		
		// check that the january blob table was dropped
		$this->assertFalse($this->tableExists($archiveTables['blob'][0])); // January
		
		// check for no changes in the february blob table
		$this->assertEqual(self::FEB_METRIC_ARCHIVE_COUNT + 1, $this->getTableCount($archiveTables['blob'][1])); // February
	}
	
	/** Test that purgeData works correctly when the 'keep daily reports' setting is set to true. */
	public function test_purgeData_deleteReportsKeepDailyReports()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_day_reports' => 1
		));

		$this->addLogData();
		$this->addReportData();

		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => 10 // removing 4 weeks, 1 month & 1 year + 1 garbage report + 2 range reports + 1 segmented report
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		$this->checkReportsAndMetricsPurged($janBlobsRemaining = 5); // 5 blobs for 5 days
	}
	
	/** Test that purgeData works correctly when the 'keep weekly reports' setting is set to true. */
	public function test_purgeData_deleteReportsKeepWeeklyReports()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_week_reports' => 1
		));

		$this->addLogData();
		$this->addReportData();

		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => 11 // 5 days, 1 month & 1 year to remove + 1 garbage report + 2 range reports + 1 segmented report
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		$this->checkReportsAndMetricsPurged($janBlobsRemaining = 4); // 4 blobs for 4 weeks
	}
	
	/** Test that purgeData works correctly when the 'keep monthly reports' setting is set to true. */
	public function test_purgeData_deleteReportsKeepMonthlyReports()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_month_reports' => 1
		));

		$this->addLogData();
		$this->addReportData();
		
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => 14 // 5 days, 4 weeks, 1 year to remove + 1 garbage report + 2 range reports + 1 segmented report
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		$this->checkReportsAndMetricsPurged($janBlobsRemaining = 1); // 1 blob for 1 month
	}
	
	/** Test that purgeData works correctly when the 'keep yearly reports' setting is set to true. */
	public function test_purgeData_deleteReportsKeepYearlyReports()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_year_reports' => 1
		));

		$this->addLogData();
		$this->addReportData();
		
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => 14 // 5 days, 4 weeks & 1 year to remove + 1 garbage report + 2 range reports + 1 segmented report
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		$this->checkReportsAndMetricsPurged($janBlobsRemaining = 1); // 1 blob for 1 year
	}
	
	/** Test no concurrency issues when deleting log data from log_action table. */
	public function test_purgeLogData_concurrency()
	{
		Piwik_AddAction("LogDataPurger.actionsToKeepInserted.olderThan", array($this, 'addReferenceToUnusedAction'));
		
		$this->addLogData();
		
		$purger = Piwik_PrivacyManager_LogDataPurger::make($this->settings, true);
		
		$this->unusedIdAction = Piwik_FetchOne(
			"SELECT idaction FROM ".Piwik_Common::prefixTable('log_action')." WHERE name = ?",
			array('whatever.com/_40'));
		$this->assertTrue($this->unusedIdAction);
		
		// purge data
		$purger->purgeData();
		
		// check that actions were purged
		$this->assertEqual(22, $this->getTableCount('log_action')); // January
		
		// check that the unused action still exists
		$count = Piwik_FetchOne(
			"SELECT COUNT(*) FROM ".Piwik_Common::prefixTable('log_action')." WHERE idaction = ?",
			array($this->unusedIdAction));
		$this->assertEqual(1, $count);
		
		$this->unusedIdAction = null; // so the hook won't get executed twice
	}

	/** Tests that purgeData works correctly when the 'keep range reports' setting is set to true. */
	public function test_purgeData_deleteReportsKeepRangeReports()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_range_reports' => 1
		));
		
		$this->addLogData();
		$this->addReportData();
		
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => 13 // 5 days, 4 weeks, 1 month & 1 year + 1 garbage report + 1 segmented report
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		$this->checkReportsAndMetricsPurged($janBlobsRemaining = 2); // 2 range blobs
	}
	
	/** Tests that purgeData works correctly when the 'keep segment reports' setting is set to true. */
	public function test_purgeData_deleteReportsKeepSegmentsReports()
	{
		Piwik_PrivacyManager::savePurgeDataSettings(array(
			'delete_reports_keep_day_reports' => 1,
			'delete_reports_keep_segment_reports' => 1
		));
		
		$this->addLogData();
		$this->addReportData();
		
		// get purge data prediction
		$prediction = Piwik_PrivacyManager::getPurgeEstimate();
		
		// perform checks on prediction
		$expectedPrediction = array(
			Piwik_Common::prefixTable('log_conversion') => 6,
			Piwik_Common::prefixTable('log_link_visit_action') => 6,
			Piwik_Common::prefixTable('log_visit') => 3,
			Piwik_Common::prefixTable('log_conversion_item') => 3,
			Piwik_Common::prefixTable('archive_numeric_2012_01') => -1,
			Piwik_Common::prefixTable('archive_blob_2012_01') => 9 // 4 weeks, 1 month & 1 year + 1 garbage report + 2 range reports
		);
		$this->assertEqual($expectedPrediction, $prediction);
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		$this->instance->deleteReportData();
		
		// perform checks
		$this->checkLogDataPurged();
		$this->checkReportsAndMetricsPurged($janBlobsRemaining = 6); // 1 segmented blob + 5 day blobs
	}
	
	/** Tests that log actions are not purged when the lock privilege is not granted.
	 * (Simulates absence of lock privilege.)
	 */
	public function test_purgeData_deleteLogsWithoutLockPrivilege()
	{
		Piwik::$lockPrivilegeGranted = false;
		
		$this->addLogData();
		
		// purge data
		$this->setTimeToRun();
		$this->instance->deleteLogData();
		
		// perform checks
		$this->checkLogDataPurged($actionsPurged = false);
	}
	
	// --- utility functions follow ---
	
	private function addLogData()
	{
		// tracks visits on the following days:
		// - 2012-01-09
		// - 2012-01-14
		// - 2012-01-19
		// - 2012-01-24 <--- everything before this date is to be purged
		// - 2012-01-29
		// - 2012-02-03
		// - 2012-02-08
		// - 2012-02-13
		// - 2012-02-18
		// - 2012-02-23
		// - 2012-02-28
		// 6 visits in feb, 5 in jan
		
		// following actions are created:
		// - 'First page view'
		// - 'Second page view'
		// - 'SKU2'
		// - 'Canon SLR'
		// - 'Electronics & Cameras'
		// - for every visit (11 visits total):
		//   - http://whatever.com/_{$daysSinceLastVisit}
		//   - http://whatever.com/42/{$daysSinceLastVisit}
		
		$start = $this->dateTime;
		$this->idSite = $this->createWebsite('2012-01-01', $ecommerce=1);
		$idGoal = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'match all', 'url', 'http', 'contains');
		
		for ($daysAgo = $this->daysAgoStart; $daysAgo >= 0; $daysAgo -= 5) // one visit every 5 days
		{
			$dateTime = $start->subDay($daysAgo)->toString();
			$t = $this->getTracker($this->idSite, $dateTime, $defaultInit = true);
			$t->setUserAgent('Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)');
			
			// use $daysAgo to make sure new actions are created for every day and aren't used again.
			// when deleting visits, some of these actions will no longer be referenced in the DB.
			$t->setUrl("http://whatever.com/_$daysAgo");
			$t->doTrackPageView('First page view');
			
			$t->setUrl("http://whatever.com/42/$daysAgo");
			$t->doTrackPageView('Second page view');
			
			$t->addEcommerceItem($sku = 'SKU2', $name = 'Canon SLR' , $category = 'Electronics & Cameras',
								 $price = 1500, $quantity = 1);
			$t->doTrackEcommerceOrder($orderId = '937nsjusu '.$dateTime, $grandTotal = 1111.11, $subTotal = 1000,
									  $tax = 111, $shipping = 0.11, $discount = 666);
		}
	}
	
	private function addReportData()
	{
		$archive = Piwik_Archive::build($this->idSite, 'year', $this->dateTime);
		$archive->getNumeric('nb_visits', 'nb_hits');
		
		Piwik_VisitorInterest_API::getInstance()->getNumberOfVisitsPerVisitDuration(
			$this->idSite, 'year', $this->dateTime);
		
		// months are added via the 'year' period, but weeks must be done manually
		for ($daysAgo = $this->daysAgoStart; $daysAgo > 0; $daysAgo -= 7) // every week
		{
			$dateTime = $this->dateTime->subDay($daysAgo);
			
			$archive = Piwik_Archive::build($this->idSite, 'week', $dateTime);
			$archive->getNumeric('nb_visits');
			
			Piwik_VisitorInterest_API::getInstance()->getNumberOfVisitsPerVisitDuration(
				$this->idSite, 'week', $dateTime);
		}
		
		// add segment for one day
		$archive = Piwik_Archive::build($this->idSite, 'day', '2012-01-14', 'browserName==FF');
		$archive->getNumeric('nb_visits', 'nb_hits');
		
		Piwik_VisitorInterest_API::getInstance()->getNumberOfVisitsPerVisitDuration(
			$this->idSite, 'day', '2012-01-14', 'browserName==FF');
		
		// add range within January
		$rangeEnd = Piwik_Date::factory('2012-01-29');
		$rangeStart = $rangeEnd->subDay(1);
		$range = $rangeStart->toString('Y-m-d').",".$rangeEnd->toString('Y-m-d');
		
		$rangeArchive = Piwik_Archive::build($this->idSite, 'range', $range);
		$rangeArchive->getNumeric('nb_visits', 'nb_hits');
		
		Piwik_VisitorInterest_API::getInstance()->getNumberOfVisitsPerVisitDuration($this->idSite, 'range', $range);
		
		// add range between January & February
		$rangeStart = $rangeEnd;
		$rangeEnd = $rangeStart->addDay(3);
		$range = $rangeStart->toString('Y-m-d').",".$rangeEnd->toString('Y-m-d');
		
		$rangeArchive = Piwik_Archive::build($this->idSite, 'range', $range);
		$rangeArchive->getNumeric('nb_visits', 'nb_hits');
		
		Piwik_VisitorInterest_API::getInstance()->getNumberOfVisitsPerVisitDuration($this->idSite, 'range', $range);
		
		// when archiving is initiated, the archive metrics & reports for EVERY loaded plugin
		// are archived. don't want this test to depend on every possible metric, so get rid of
		// the unwanted archive data now.
		$metricsToSave = array(
			'nb_visits',
			'nb_actions',
			Piwik_Goals::getRecordName('revenue'),
			Piwik_Goals::getRecordName('nb_conversions', 1),
			Piwik_Goals::getRecordName('revenue', Piwik_Tracker_GoalManager::IDGOAL_ORDER)
		);
		
		$archiveTables = $this->getArchiveTableNames();
		foreach ($archiveTables['numeric'] as $table)
		{
			$realTable = Piwik_Common::prefixTable($table);
			Piwik_Query("DELETE FROM $realTable WHERE name NOT IN ('".implode("','", $metricsToSave)."') AND name NOT LIKE 'done%'");
		}
		foreach ($archiveTables['blob'] as $table)
		{
			$realTable = Piwik_Common::prefixTable($table);
			Piwik_Query("DELETE FROM $realTable WHERE name NOT IN ('VisitorInterest_timeGap')");
		}
		
		// add garbage metrics
		$janDate1 = '2012-01-05';
		$febDate1 = '2012-02-04';
		
		$sql = "INSERT INTO %s (idarchive,name,idsite,date1,date2,period,ts_archived,value)
		                VALUES (10000,?,1,?,?,?,?,?)";
		
		// one metric for jan & one for feb
		Piwik_Query(sprintf($sql, Piwik_Common::prefixTable($archiveTables['numeric'][0])),
					array(self::GARBAGE_FIELD, $janDate1, $janDate1, $janDate1, 1, 100));
		Piwik_Query(sprintf($sql, Piwik_Common::prefixTable($archiveTables['numeric'][1])),
					array(self::GARBAGE_FIELD, $febDate1, $febDate1, $febDate1, 1, 200));
		
		// add garbage reports
		Piwik_Query(sprintf($sql, Piwik_Common::prefixTable($archiveTables['blob'][0])),
					array(self::GARBAGE_FIELD, $janDate1, $janDate1, $janDate1, 10, 'blobval'));
		Piwik_Query(sprintf($sql, Piwik_Common::prefixTable($archiveTables['blob'][1])),
					array(self::GARBAGE_FIELD, $febDate1, $febDate1, $febDate1, 20, 'blobval'));
	}
	
	private function checkNoDataChanges()
	{
		// 11 visits total w/ 2 actions per visit & 2 conversions per visit. 1 e-commerce order per visit.
		$this->assertEqual(11, $this->getTableCount('log_visit'));
		$this->assertEqual(22, $this->getTableCount('log_conversion'));
		$this->assertEqual(22, $this->getTableCount('log_link_visit_action'));
		$this->assertEqual(11, $this->getTableCount('log_conversion_item'));
		$this->assertEqual(27, $this->getTableCount('log_action'));
		
		$archiveTables = $this->getArchiveTableNames();
		
		$janMetricCount = $this->getExpectedNumericArchiveCountJan();
		$this->assertEqual($janMetricCount, $this->getTableCount($archiveTables['numeric'][0])); // January
		
		// no range metric for february
		$febMetricCount = $this->getExpectedNumericArchiveCountFeb();
		$this->assertEqual($febMetricCount, $this->getTableCount($archiveTables['numeric'][1])); // February
		
		// 1 entry per period w/ visits + 1 garbage report + 2 range reports + 1 segment report
		$this->assertEqual(self::JAN_METRIC_ARCHIVE_COUNT + 1 + 2 + 1, $this->getTableCount($archiveTables['blob'][0])); // January
		$this->assertEqual(self::FEB_METRIC_ARCHIVE_COUNT + 1, $this->getTableCount($archiveTables['blob'][1])); // February
	}
	
	/**
	 * Helper method. Performs checks after reports are purged. Checks that the january numeric table
	 * was dropped, that the february metric & blob tables are unaffected, and that the january blob
	 * table has a certain number of blobs.
	 */
	private function checkReportsAndMetricsPurged( $janBlobsRemaining )
	{
		$archiveTables = $this->getArchiveTableNames();
		
		// check that the january numeric table was dropped
		$this->assertFalse($this->tableExists($archiveTables['numeric'][0])); // January
		
		// check february numerics not deleted
		$febRowCount = $this->getExpectedNumericArchiveCountFeb();
		$this->assertEqual($febRowCount, $this->getTableCount($archiveTables['numeric'][1])); // February
		
		// check the january blob count
		$this->assertEqual($janBlobsRemaining, $this->getTableCount($archiveTables['blob'][0])); // January
		
		// check for no changes in the february blob table (1 blob for every period w/ visits in feb + 1 garbage report)
		$this->assertEqual(self::FEB_METRIC_ARCHIVE_COUNT + 1, $this->getTableCount($archiveTables['blob'][1])); // February
	}
	
	private function checkLogDataPurged( $actionsPurged = true )
	{
		// 3 days removed by purge, so 3 visits, 6 conversions, 6 visit actions, 3 e-commerce orders
		// & 6 actions removed
		$this->assertEqual(8, $this->getTableCount('log_visit'));
		$this->assertEqual(16, $this->getTableCount('log_conversion'));
		$this->assertEqual(16, $this->getTableCount('log_link_visit_action'));
		$this->assertEqual(8, $this->getTableCount('log_conversion_item'));
		if ($actionsPurged)
		{
			$this->assertEqual(21, $this->getTableCount('log_action'));
		}
		else
		{
			$this->assertEqual(27, $this->getTableCount('log_action'));
		}
	}
	
	/**
	 * Event hook that adds a row into the DB that references unused idaction AFTER LogDataPurger
	 * does the insert into the temporary table. When log_actions are deleted, this idaction should still
	 * be kept. w/ the wrong strategy, it won't be and there will be a dangling reference
	 * in the log_link_visit_action table.
	 *
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	public function addReferenceToUnusedAction( $notification )
	{
		$unusedIdAction = $this->unusedIdAction;
		if (empty($unusedIdAction)) // make sure we only do this for one test case
		{
			return;
		}
		
		$tempTableName = Piwik_Common::prefixTable(Piwik_PrivacyManager_LogDataPurger::TEMP_TABLE_NAME);
		$logLinkVisitActionTable = Piwik_Common::prefixTable("log_link_visit_action");
		
		$sql = "INSERT INTO $logLinkVisitActionTable
							(idsite, idvisitor, server_time, idvisit, idaction_url, idaction_url_ref,
							idaction_name, idaction_name_ref, time_spent_ref_action)
					 VALUES (1, 'abc', NOW(), 15, $unusedIdAction, $unusedIdAction,
							 $unusedIdAction, $unusedIdAction, 1000)";
		
		Piwik_Query($sql);
	}
	
	private function setTimeToRun()
	{
		$lastDateSecs = Piwik_Date::factory('today')->subDay(8)->getTimestamp();
		
		Piwik_SetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL, 1);
		Piwik_SetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_LOGS, $lastDateSecs);
		Piwik_SetOption(Piwik_PrivacyManager::OPTION_LAST_DELETE_PIWIK_REPORTS, $lastDateSecs);
	}
	
	private function getTableCount( $tableName, $where = '' )
	{
		$sql = "SELECT COUNT(*) FROM ".Piwik_Common::prefixTable($tableName)." $where";
		return Piwik_FetchOne($sql);
	}
	
	private function tableExists( $tableName )
	{
		$dbName = Piwik_Config::getInstance()->database['dbname'];
		
		$sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ? AND table_name = ?";
		return Piwik_FetchOne($sql, array($dbName, Piwik_Common::prefixTable($tableName))) == 1;
	}
	
	private function getArchiveTableNames()
	{
		return array(
			'numeric' => array(
				'archive_numeric_2012_01',
				'archive_numeric_2012_02'
			),
			'blob' => array(
				'archive_blob_2012_01',
				'archive_blob_2012_02'
			)
		);
	}
	
	private function getExpectedNumericArchiveCountJan()
	{
		// 5 entries per period w/ visits
		// + 1 entry for every period in the month (the 'done' rows)
		// + 1 garbage metric
		// + 2 entries per range period (4 total) + 2 'done...' entries per range period (4 total)
		// + 2 entries per segment (2 total) + 2 'done...' entries per segment (2 total)
		return self::JAN_METRIC_ARCHIVE_COUNT * 5 + self::TOTAL_JAN_ARCHIVE_COUNT + 1 + 8 + 4;
	}
	
	private function getExpectedNumericArchiveCountFeb()
	{
		// (5 metrics per period w/ visits
		// + 1 'done' archive for every period)
		// + 1 garbage metric
		return self::FEB_METRIC_ARCHIVE_COUNT * 5 + self::TOTAL_FEB_ARCHIVE_COUNT + 1;
	}

	/** Irrelevant. Just needs to be defined. */
	public function getPathToTestDirectory()
	{
		return PIWIK_INCLUDE_PATH . '/tests/integration';
	}
}

