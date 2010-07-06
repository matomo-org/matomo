<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
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
    
	
	public function tearDown()
	{
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
		$archiveProcessing = $this->createArchiveProcessing('month', date('Y-m-d'), 'UTC+10');
		
		// min finished timestamp considered when looking at archive timestamp 
		$dateMinArchived = Piwik_Date::today()->setTimezone('UTC+10')->getTimestamp();
		$this->assertEqual($archiveProcessing->getMinTimeArchivedProcessed(), $dateMinArchived);
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
		Piwik_ArchiveProcessing::setBrowserTriggerArchiving(true);
		
		$archiveProcessing = $this->createArchiveProcessing('day', 'today', 'UTC-1');
		
		// we look at anything processed within the time to live range
		$dateMinArchived = time() - Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
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
		
		$this->assertEqual($archiveProcessing->getStartDatetimeUTC(), date('Y-m-d').' 01:00:00');
		$this->assertEqual($archiveProcessing->getEndDatetimeUTC(), date('Y-m-d', time()+86400).' 00:59:59');
		$this->assertTrue($archiveProcessing->isArchiveTemporary());
	}
	
}

