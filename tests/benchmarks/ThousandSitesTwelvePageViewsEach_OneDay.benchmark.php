<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

class Benchmarks_ThousandSitesTwelvePageViewsEach_OneDay extends Test_Integration
{
	protected $dateTime = '2010-01-01';
	protected $idSites = array();
	protected $idGoals = array(); // maps site id to array of goal ids. only 500 sites w/ goals.
	
	public function setUp()
	{
		// add one thousand sites
		for ($i = 0; $i < 1000; ++$i)
		{
			$this->idSites[] = $this->createWebsite($this->dateTime, 1, "Site #$i");
		}
		
		// add goals to 500 sites
		$goals = Piwik_Goals_API::getInstance();
		foreach ($this->idSites as $idSite)
		{
			if ($idSite % 2 == 0)
			{
				$idGoal1 = $goals->addGoal($idSite, 'all', 'url', 'http', 'contains', false, 5);
				$idGoal2 = $goals->addGoal($idSite, 'all', 'url', 'http', 'contains');
				$idGoals[$idSite] = array($idGoal1, $idGoal2);
			}
			else
			{
				$this->idGoals[$idSite] = array();
			}
		}
		
		$urls = array();
		for ($i = 0; $i != 2; ++$i)
		{
			$url = "http://whatever.com/".($i-1)."/".($i+1);
			$title = "page view ".($i-1)." / ".($i+1);
			$urls[$url] = $title;
		}
		
		$visitTimes = array();
		$date = Piwik_Date::factory($this->dateTime);
		for ($i = 0; $i != 2; ++$i)
		{
			$visitTimes[] = $date->addHour($i)->toString();
		}
		
		// add 12 page views per site (2 visit w/ 2 page views for 3 visitors)
		foreach ($this->idSites as $idSite)
		{
			for ($visitor = 0; $visitor != 3; ++$visitor)
			{
				$t = $this->getTracker($idSite, $this->dateTime);
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
	}
	
	public function test_archiveVisitsSummary_andQueryAll()
	{
		Piwik_VisitsSummary_API::get('all', 'day', $this->dateTime);
	}
	
	public function test_archiveGoals_andQueryAll()
	{
		Piwik_Goals_API::get('all', 'day', $this->dateTime);
	}
}

