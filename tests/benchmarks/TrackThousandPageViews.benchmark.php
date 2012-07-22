<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

class Benchmarks_TrackThousandPageViews extends Test_Integration
{
	protected $dateTime = '2012-01-01';
	protected $idSite = null;
	protected $idGoal = null;
	protected $urls = array();
	protected $titles = array();
	protected $visitDates = array();
	protected $tracker = null;
	
	public function setUp()
	{
		$this->idSite = $this->createWebsite($this->dateTime, 1, "Site #0");
		
		// add one goal
		$this->idGoal = Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains');
		
		for ($i = 0; $i != 10; ++$i)
		{
			$this->urls[] = "http://whatever.com/".($i-1)."/".($i+1);
			$this->titles[] = "page view ".($i-1)." / ".($i+1);
		}
		
		$this->tracker = $this->getTracker($this->idSite, $this->dateTime);
		
		$date = Piwik_Date::factory($this->dateTime);
		for ($i = 0; $i != 10; ++$i)
		{
			for ($j = 0; $j != 10; ++$j)
			{
				$this->visitDates[] = $date->addDay($i)->addHour($j)->toString();
			}
		}
	}
	
	public function test_normalTracking()
	{
		// 10 days, 10 visits, 10 actions
		foreach ($this->visitDates as $visitDate)
		{
			for ($i = 0; $i != count($this->urls); ++$i)
			{
				$this->tracker->setUrl($this->urls[$i]);
				$this->tracker->setForceVisitDateTime($visitDate);
				$this->tracker->doTrackPageView($this->titles[$i]);
			}
		}
	}
	
	public function test_bulkTracking()
	{
		$this->tracker->enableBulkTracking();
		$this->test_normalTracking();
		$this->tracker->doBulkTrack();
	}
}
