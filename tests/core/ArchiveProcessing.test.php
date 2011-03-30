<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once "Database.test.php";
class Test_Piwik_ArchiveProcessing extends Test_Database
{
	public function setUp()
	{
		parent::setUp();

		// setup the access layer
		$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
	}
    
	
	// TESTING BATCH INSERT
	public function test_databaseBatchInsert()
	{
		$table = Piwik_Common::prefixTable('site_url');
	
		$data = $this->getDataInsert();
		$didWeUseBulk = Piwik::databaseInsertBatch($table, array('idsite', 'url'), $data);
		if(PHP_VERSION != '5.2.6') {
			$this->assertTrue($didWeUseBulk, " The test didn't LOAD DATA INFILE but fallbacked to plain INSERT, but we must unit test this function!");
		}
		$this->checkTableIsExpected($table, $data);
		
		// INSERT again the bulk. Because we use keyword LOCAL the data will be REPLACED automatically (see mysql doc) 
		Piwik::databaseInsertBatch($table, array('idsite', 'url'), $data);
		$this->checkTableIsExpected($table, $data);
	}

	// TESTING PLAIN INSERTS
	public function test_databaseBatchIterate()
	{
		$table = Piwik_Common::prefixTable('site_url');
		$data = $this->getDataInsert();
		Piwik::databaseInsertIterate($table, array('idsite', 'url'), $data);
		$this->checkTableIsExpected($table, $data);

		// If we insert AGAIN, expect to throw an error because the primary key already exists
		try {
			Piwik::databaseInsertIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = false);	
			$this->fail();
		} catch (Exception $e) {
			$this->pass();
		}
		
		// However if we insert with keyword REPLACE, then the new data should be saved
		Piwik::databaseInsertIterate($table, array('idsite', 'url'), $data, $ignoreWhenDuplicate = true );
		$this->checkTableIsExpected($table, $data);
	}
	
	protected function checkTableIsExpected($table, $data)
	{
		$fetched = Piwik_FetchAll('SELECT * FROM '.$table);
		foreach($data as $id => $row) {
			$this->assertEqual($fetched[$id]['idsite'], $data[$id][0]);
			$this->assertEqual($fetched[$id]['url'], $data[$id][1]);
		}
	}
	/*
	 * site_url (
		  idsite INTEGER(10) UNSIGNED NOT NULL,
		  url VARCHAR(255) NOT NULL,
		  PRIMARY KEY(idsite, url)
	)
	 */
	protected function getDataInsert()
	{
		return array(
			array(1, 'test'),
			array(2, 'te" \n st2'),
			array(3, " \n \r \t test"),
			array(4, 'test4'),
			array(5, 'test5'),
		);
	}
	private function createWebsite($timezone = 'UTC')
	{
		$idsite = Piwik_SitesManager_API::getInstance()->addSite(
												"site1",
												array("http://piwik.net"), 
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
		$archiveProcessing->init();
		return $archiveProcessing;
	}

	// test of validity of an archive, for a month not finished
	public function test_init_currentMonth()
	{
		$siteTimezone = 'UTC+10';
		$timestamp = Piwik_Date::factory('now', $siteTimezone)->getTimestamp();
		$dateLabel = date('Y-m-d', $timestamp);

		$archiveProcessing = $this->createArchiveProcessing('month', $dateLabel, $siteTimezone);
		
		// min finished timestamp considered when looking at archive timestamp 
		$dateMinArchived = Piwik_Date::factory($dateLabel)->setTimezone($siteTimezone)->getTimestamp();
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
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), '2010-01-01 00:00:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), '2010-01-01 23:59:59');
		$this->assertFalse($archiveProcessing->isArchiveTemporary());
	}

	// test of validity of an archive, for a non UTC date in the past
	public function test_init_dayInPast_NonUTCWebsite()
	{
		$timezone = 'UTC+5.5';
		$archiveProcessing = $this->createArchiveProcessing('day', '2010-01-01', 'UTC+5.5');
		// min finished timestamp considered when looking at archive timestamp 
		$dateMinArchived = Piwik_Date::factory('2010-01-01 18:30:00');
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived->getTimestamp());
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), '2009-12-31 18:30:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), '2010-01-01 18:29:59');
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
}
