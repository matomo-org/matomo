<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once "Database.test.php";
class Test_Piwik_ArchiveProcessing extends Test_Database
{
	function __construct()
	{
		parent::__construct();
		if(!Piwik::isTimezoneSupportEnabled())
		{
			echo "ArchiveProcessing.test.php: Timezone support is not enabled on this server, so some unit tests were skipped.";
		}
	}
	public function setUp()
	{
		parent::setUp();

		// setup the access layer
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
	}

	private function createWebsite($timezone = 'UTC')
	{
		$idsite = Piwik_SitesManager_API::getInstance()->addSite(
												"site1",
												array("http://piwik.net"), 
												$ecommerce=0,
												$excludedIps = "",
												$excludedQueryParameters = "",
												$timezone);
												
		Piwik_Site::clearCache();
    	return new Piwik_Site($idsite);
	}

	private function createArchiveProcessing($periodLabel, $dateLabel, $siteTimezone)
	{
		$site = $this->createWebsite($siteTimezone);
		$date = Piwik_Date::factory($dateLabel);
		$period = Piwik_Period::factory($periodLabel, $date);
		
		$archiveProcessing = Piwik_ArchiveProcessing::factory($periodLabel);
		$archiveProcessing->setSite($site);
		$archiveProcessing->setPeriod($period);
		$archiveProcessing->setSegment(new Piwik_Segment('', $site->getId()));
		$archiveProcessing->init();
		return $archiveProcessing;
	}

	// test of validity of an archive, for a month not finished
	public function test_init_currentMonth()
	{
		$siteTimezone = 'UTC+10';
		$now = time();
		
		$dateLabel = date('Y-m-d', $now);
		$archiveProcessing = $this->createArchiveProcessing('month', $dateLabel, $siteTimezone);
		$archiveProcessing->time = $now;
		
		// min finished timestamp considered when looking at archive timestamp 
		$timeout = Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
		$this->assertTrue($timeout >= 10);
		$dateMinArchived = $now - $timeout;

		$minTimestamp = $archiveProcessing->getMinTimeArchivedProcessed();
		$this->assertEqual($minTimestamp, $dateMinArchived, Piwik_Date::factory($minTimestamp)->getDatetime() . " != " . Piwik_Date::factory($dateMinArchived)->getDatetime());
		$this->assertTrue($archiveProcessing->isArchiveTemporary());
	}
	
	// test of validity of an archive, for a month in the past
	public function test_init_dayInPast()
	{
		$archiveProcessing = $this->createArchiveProcessing('day', '2010-01-01', 'UTC');
		
		// min finished timestamp considered when looking at archive timestamp 
		$dateMinArchived = Piwik_Date::factory('2010-01-02')->getTimestamp();
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed() + 1, $dateMinArchived);
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), '2010-01-01 00:00:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), '2010-01-01 23:59:59');
		$this->assertFalse($archiveProcessing->isArchiveTemporary());
	}

	// test of validity of an archive, for a non UTC date in the past
	public function test_init_dayInPast_NonUTCWebsite()
	{
		$timezone = 'UTC+5.5';
		$archiveProcessing = $this->createArchiveProcessing('day', '2010-01-01', $timezone);
		// min finished timestamp considered when looking at archive timestamp 
		$dateMinArchived = Piwik_Date::factory('2010-01-01 18:30:00');
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed() + 1, $dateMinArchived->getTimestamp());
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), '2009-12-31 18:30:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), '2010-01-01 18:29:59');
		$this->assertFalse($archiveProcessing->isArchiveTemporary());
	}

	// test of validity of an archive, for a non UTC month in the past
	public function test_init_monthInPast_NonUTCWebsite()
	{
		$timezone = 'UTC-5.5';
		$archiveProcessing = $this->createArchiveProcessing('month', '2010-01-02', $timezone);
		// min finished timestamp considered when looking at archive timestamp 
		$dateMinArchived = Piwik_Date::factory('2010-02-01 05:30:00');
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed() + 1, $dateMinArchived->getTimestamp());
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), '2010-01-01 05:30:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), '2010-02-01 05:29:59');
		$this->assertFalse($archiveProcessing->isArchiveTemporary());
	}
	
	// test of validity of an archive, for today's archive
	public function test_init_today()
	{
		$now = time();
		$siteTimezone = 'UTC-1';
		$timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
		$dateLabel = date('Y-m-d', $timestamp);

		Piwik_ArchiveProcessing::setBrowserTriggerArchiving(true);
		
		$archiveProcessing = $this->createArchiveProcessing('day', $dateLabel, $siteTimezone);
		$archiveProcessing->time = $now;
		
		// we look at anything processed within the time to live range
		$dateMinArchived = $now - Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);
		$this->assertTrue($archiveProcessing->isArchiveTemporary());

		// when browsers don't trigger archives, we force ArchiveProcessing 
		// to fetch any of the most recent archive
		Piwik_ArchiveProcessing::setBrowserTriggerArchiving(false);
		// see isArchivingDisabled()
		// Running in CLI doesn't impact the time to live today's archive we are loading
		// From CLI, we will not return data that is 'stale' 
		if(!Piwik_Common::isPhpCliMode())
		{
			$dateMinArchived = 0;
		}
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), date('Y-m-d', $timestamp).' 01:00:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), date('Y-m-d', $timestamp+86400).' 00:59:59');
		$this->assertTrue($archiveProcessing->isArchiveTemporary());
	}

	public function test_init_today_europe()
	{
        if(Piwik::isTimezoneSupportEnabled())
        {
			$now = time();
			$siteTimezone = 'Europe/Paris';
			$timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
			$dateLabel = date('Y-m-d', $timestamp);

			Piwik_ArchiveProcessing::setBrowserTriggerArchiving(true);

			$archiveProcessing = $this->createArchiveProcessing('day', $dateLabel, $siteTimezone);
			$archiveProcessing->time = $now;

			// we look at anything processed within the time to live range
			$dateMinArchived = $now - Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
			$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);
			$this->assertTrue($archiveProcessing->isArchiveTemporary());

			// when browsers don't trigger archives, we force ArchiveProcessing
			// to fetch any of the most recent archive
			Piwik_ArchiveProcessing::setBrowserTriggerArchiving(false);
			// see isArchivingDisabled()
			// Running in CLI doesn't impact the time to live today's archive we are loading
			// From CLI, we will not return data that is 'stale'
			if(!Piwik_Common::isPhpCliMode())
			{
			    $dateMinArchived = 0;
			}
			$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);

			// this test varies with DST
			$this->assertTrue($archiveProcessing->getStartDatetimeUTC() == date('Y-m-d', $timestamp-86400).' 22:00:00' ||
			    $archiveProcessing->getStartDatetimeUTC() == date('Y-m-d', $timestamp-86400).' 23:00:00');
			$this->assertTrue($archiveProcessing->getEndDatetimeUTC() == date('Y-m-d', $timestamp).' 21:59:59' ||
			    $archiveProcessing->getEndDatetimeUTC() == date('Y-m-d', $timestamp).' 22:59:59');

			$this->assertTrue($archiveProcessing->isArchiveTemporary());
        }
	}

	public function test_init_today_toronto()
	{
		if(Piwik::isTimezoneSupportEnabled())
		{
			$now = time();
			$siteTimezone = 'America/Toronto';
			$timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
			$dateLabel = date('Y-m-d', $timestamp);

			Piwik_ArchiveProcessing::setBrowserTriggerArchiving(true);

			$archiveProcessing = $this->createArchiveProcessing('day', $dateLabel, $siteTimezone);
			$archiveProcessing->time = $now;

			// we look at anything processed within the time to live range
			$dateMinArchived = $now - Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
			$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);
			$this->assertTrue($archiveProcessing->isArchiveTemporary());

			// when browsers don't trigger archives, we force ArchiveProcessing
			// to fetch any of the most recent archive
			Piwik_ArchiveProcessing::setBrowserTriggerArchiving(false);
			// see isArchivingDisabled()
			// Running in CLI doesn't impact the time to live today's archive we are loading
			// From CLI, we will not return data that is 'stale'
			if(!Piwik_Common::isPhpCliMode())
			{
			    $dateMinArchived = 0;
			}
			$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);

			// this test varies with DST
			$this->assertTrue($archiveProcessing->getStartDatetimeUTC() == date('Y-m-d', $timestamp).' 04:00:00' ||
			    $archiveProcessing->getStartDatetimeUTC() == date('Y-m-d', $timestamp).' 05:00:00');
			$this->assertTrue($archiveProcessing->getEndDatetimeUTC() == date('Y-m-d', $timestamp+86400).' 03:59:59' ||
			    $archiveProcessing->getEndDatetimeUTC() == date('Y-m-d', $timestamp+86400).' 04:59:59');

			$this->assertTrue($archiveProcessing->isArchiveTemporary());
		}
		
	}

	// TESTING BATCH INSERT
	public function test_tableInsertBatch()
	{
		$table = Piwik_Common::prefixTable('site_url');
		$data = $this->getDataInsert();
		$didWeUseBulk = Piwik::tableInsertBatch($table, array('idsite', 'url'), $data);
		if(version_compare(PHP_VERSION, '5.2.9') < 0 ||
			version_compare(PHP_VERSION, '5.3.7') >= 0 ||
			Piwik_Config::getInstance()->database['adapter'] != 'PDO_MYSQL')
		{
			$this->assertTrue($didWeUseBulk, "The test didn't LOAD DATA INFILE but fallbacked to plain INSERT, but we must unit test this function!");
		}
		$this->checkTableIsExpected($table, $data);
		
		// INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc) 
		Piwik::tableInsertBatch($table, array('idsite', 'url'), $data);
		$this->checkTableIsExpected($table, $data);
	}

	// TESTING PLAIN INSERTS
	public function test_tableInsertBatchIterate()
	{
		$table = Piwik_Common::prefixTable('site_url');
		$data = $this->getDataInsert();
		Piwik::tableInsertBatchIterate($table, array('idsite', 'url'), $data);
		$this->checkTableIsExpected($table, $data);

		// If we insert AGAIN, expect to throw an error because the primary key already exists
		try {
			Piwik::tableInsertBatchIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = false);	
			$this->fail();
		} catch (Exception $e) {
			$this->pass();
		}
		
		// However if we insert with keyword REPLACE, then the new data should be saved
		Piwik::tableInsertBatchIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = true );
		$this->checkTableIsExpected($table, $data);
	}
	
	// TESTING BATCH INSERT (BLOB)
	public function test_tableInsertBatchBlob()
	{
		$siteTimezone = 'America/Toronto';
		$timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
		$dateLabel = '2011-03-31';
		$archiveProcessing = $this->createArchiveProcessing('day', $dateLabel, $siteTimezone);

		$table = $archiveProcessing->getTableArchiveBlobName();

		$data = $this->getBlobDataInsert();
		$didWeUseBulk = Piwik::tableInsertBatch($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data);
		if(version_compare(PHP_VERSION, '5.2.9') < 0 ||
			version_compare(PHP_VERSION, '5.3.7') >= 0 ||
			Piwik_Config::getInstance()->database['adapter'] != 'PDO_MYSQL')
		{
			$this->assertTrue($didWeUseBulk, "The test didn't LOAD DATA INFILE but fallbacked to plain INSERT, but we must unit test this function!");
		}
		$this->checkTableIsExpectedBlob($table, $data);
		
		// INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc) 
		Piwik::tableInsertBatch($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data);
		$this->checkTableIsExpectedBlob($table, $data);
	}

	// TESTING PLAIN INSERTS (BLOB)
	public function test_tableInsertBatchIterateBlob()
	{
		$siteTimezone = 'America/Toronto';
		$timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
		$dateLabel = '2011-03-31';
		$archiveProcessing = $this->createArchiveProcessing('day', $dateLabel, $siteTimezone);

		$table = $archiveProcessing->getTableArchiveBlobName();

		$data = $this->getBlobDataInsert();
		Piwik::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data);
		$this->checkTableIsExpectedBlob($table, $data);

		// If we insert AGAIN, expect to throw an error because the primary key already exist
		try {
			Piwik::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $ignoreWhenDuplicate = false);	
			$this->fail();
		} catch (Exception $e) {
			$this->pass();
		}
		
		// However if we insert with keyword REPLACE, then the new data should be saved
		Piwik::tableInsertBatchIterate($table, array('idarchive', 'name', 'idsite', 'date1', 'date2', 'period', 'ts_archived', 'value'), $data, $ignoreWhenDuplicate = true );
		$this->checkTableIsExpectedBlob($table, $data);
	}
	
	protected function checkTableIsExpected($table, $data)
	{
		$fetched = Piwik_FetchAll('SELECT * FROM '.$table);
		foreach($data as $id => $row) {
			$this->assertEqual($fetched[$id]['idsite'], $data[$id][0], "record $id is not {$data[$id][0]}");
			$this->assertEqual($fetched[$id]['url'], $data[$id][1], "Record $id bug, not {$data[$id][1]} BUT {$fetched[$id]['url']}");
		}
	}

	protected function checkTableIsExpectedBlob($table, $data)
	{
		$fetched = Piwik_FetchAll('SELECT * FROM '.$table);
		foreach($data as $id => $row) {
			$this->assertEqual($fetched[$id]['idarchive'], $data[$id][0], "record $id idarchive is not '{$data[$id][0]}'");
			$this->assertEqual($fetched[$id]['name'], $data[$id][1], "record $id name is not '{$data[$id][1]}'");
			$this->assertEqual($fetched[$id]['idsite'], $data[$id][2], "record $id idsite is not '{$data[$id][2]}'");
			$this->assertEqual($fetched[$id]['date1'], $data[$id][3], "record $id date1 is not '{$data[$id][3]}'");
			$this->assertEqual($fetched[$id]['date2'], $data[$id][4], "record $id date2 is not '{$data[$id][4]}'");
			$this->assertEqual($fetched[$id]['period'], $data[$id][5], "record $id period is not '{$data[$id][5]}'");
			$this->assertEqual($fetched[$id]['ts_archived'], $data[$id][6], "record $id ts_archived is not '{$data[$id][6]}'");
			$this->assertEqual($fetched[$id]['value'], $data[$id][7], "record $id value is unexpected");
		}
	}

	/*
	 * Schema for site_url table:
	 *	site_url (
	 *		idsite INTEGER(10) UNSIGNED NOT NULL,
	 *		url VARCHAR(255) NOT NULL,
	 *		PRIMARY KEY(idsite, url)
	 *	)
	 */
	protected function getDataInsert()
	{
		return array(
			array(1, 'test'),
			array(2, 'te" \n st2'),
			array(3, " \n \r \t test"),

			// these aren't expected to work on a column of datatype VARCHAR
//			array(4, gzcompress( " \n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942")),
//			array(5, gzcompress('test4')),

			array(6, 'test5'),
			array(7, '简体中文'),
			array(8, '"'),
			array(9, "'"),
			array(10, '\\'),
			array(11, '\\"'),
			array(12, '\\\''),
			array(13, "\t"),
			array(14, "test \x00 null"),
			array(15, "\x00\x01\x02\0x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f"),
		);
	}

	// see archive_blob table
	protected function getBlobDataInsert()
	{
		$ts = '2011-03-31 17:48:00';
		$str = '';
		for($i = 0; $i < 256; $i++)
		{
			$str .= chr($i);
		}
		$array[] = array(1, 'bytes 0-255', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, $str);

		$array[] = array(2, 'compressed string', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, gzcompress( " \n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942\n \r \t teste eigaj oegheao geaoh guoea98742983 2 342942"));

		$str = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . '/tests/core/Piwik/lipsum.txt');
		$array[] = array(3, 'lorem ipsum', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, $str);

		$array[] = array(4, 'lorem ipsum compressed', 1, '2011-03-31', '2011-03-31', Piwik::$idPeriods['day'], $ts, gzcompress($str));

		return $array;
	}
}
