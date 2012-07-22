<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

class Benchmarks_OneSiteTwelveThousandPageViews_Year extends Test_Integration
{
	protected $dateTime = '2010-01-01';
	protected $idSite = null;
	protected $idGoal1 = null;
	protected $idGoal2 = null;
	
	public function setUp()
	{
		// add one site
		$this->idSite = $this->createWebsite($this->dateTime, 1, "Site #0");
		
		// add two goals
		$goals = Piwik_Goals_API::getInstance();
		$this->idGoal1 = $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
		$this->idGoal2 = $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains');
		
		$urls = array();
		for ($i = 0; $i != 2; ++$i)
		{
			$url = "http://whatever.com/".($i-1)."/".($i+1);
			$title = "page view ".($i-1)." / ".($i+1);
			$urls[$url] = $title;
		}
		
		$visitTimes = array();
		$date = Piwik_Date::factory($this->dateTime);
		for ($i = 0; $i != 250; ++$i)
		{
			// spread visits out through entire year
			$day = floor((365 * $i) / 250);
			
			for ($j = 0; $j != 2; ++$j)
			{
				$visitTimes[] = $date->addDay($day)->addHour($j)->toString();
			}
		}
		
		// add 12,000 page views (2 visits of 2 page views for 12 visitors on 250 days spread out through year)
		for ($visitor = 0; $visitor != 12; ++$visitor)
		{
			$t = $this->getTracker($this->idSite, $this->dateTime);
			foreach ($visitTimes as $visitTime)
			{
				$t->setForceVisitDateTime($visitTime);
				foreach ($urls as $url => $title)
				{
					$t->setUrl($url);
					$t->doTrackPageView($title);
				}
			}
		}
	}
	
	public function test_archiveVisitsSummary()
	{
		Piwik_VisitsSummary_API::get($this->idSite, 'year', $this->dateTime);
	}
	
	public function test_archiveGoals()
	{
		Piwik_Goals_API::get($this->idSite, 'year', $this->dateTime);
	}
}
