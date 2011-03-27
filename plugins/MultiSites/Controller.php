<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_MultiSites
 */

/**
 *
 * @package Piwik_MultiSites
 */
class Piwik_MultiSites_Controller extends Piwik_Controller
{
	protected $orderBy = 'names';
	protected $order = 'desc';
	protected $evolutionBy = 'visits';
	protected $mySites = array();
	protected $page = 1;
	protected $limit = 0;
	protected $period;
	protected $date;

	function __construct()
	{
		parent::__construct();
		
		$this->limit = Zend_Registry::get('config')->General->all_websites_website_per_page;
	}

	function index()
	{
		$this->getSitesInfo();
	}


	public function getSitesInfo()
	{
		Piwik::checkUserHasSomeViewAccess();
		// overwrites the default Date set in the parent controller 
		// Instead of the default current website's local date, 
		// we set "today" or "yesterday" based on the default Piwik timezone
		$piwikDefaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();
		$date = Piwik_Common::getRequestVar('date', 'today');
		$period = Piwik_Common::getRequestVar('period', 'day');	
		if($period != 'range')
		{
			$date = $this->getDateParameterInTimezone($date, $piwikDefaultTimezone);
			$date = $date->toString();
		}
		
		$mySites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess();
		$params = $this->getGraphParamsModified();

		$ids = 'all';
		$lastDate =  date('Y-m-d',strtotime("-1 ".$period, strtotime($date)));

		$visits = Piwik_VisitsSummary_API::getInstance()->getVisits($ids, $period, $date);
		$actions = Piwik_VisitsSummary_API::getInstance()->getActions($ids, $period, $date);
		$uniqueUsers = Piwik_VisitsSummary_API::getInstance()->getUniqueVisitors($ids, $period, $date);
		
		if($period != 'range')
		{
			$lastVisits = Piwik_VisitsSummary_API::getInstance()->getVisits($ids, $period, $lastDate);
			$lastActions = Piwik_VisitsSummary_API::getInstance()->getActions($ids, $period, $lastDate);
			$lastUniqueUsers = Piwik_VisitsSummary_API::getInstance()->getUniqueVisitors($ids, $period, $lastDate);
			$visitsSummary = $this->getSummary($lastVisits, $visits, $mySites, "visits");
			$actionsSummary = $this->getSummary($lastActions, $actions, $mySites, "actions");
			$uniqueSummary = $this->getSummary($lastUniqueUsers, $uniqueUsers, $mySites, "unique");
			$lastVisitsArray = $lastVisits->getArray();
			$lastActionsArray = $lastActions->getArray();
			$lastUniqueUsersArray = $lastUniqueUsers->getArray();
		}

		$visitsArray = $visits->getArray();
		$actionsArray = $actions->getArray();
		$uniqueUsersArray = $uniqueUsers->getArray();
		
		$totalVisits = $totalActions = 0;
		foreach($mySites as &$site)
		{
			$idSite = $site['idsite'];
			$tmp = $visitsArray[$idSite]->getColumn(0);
			$site['visits'] = $tmp[0];
			$totalVisits += $tmp[0];
			$tmp = $actionsArray[$idSite]->getColumn(0);
			$site['actions'] = $tmp[0];
			$totalActions += $tmp[0];
			$tmp = $uniqueUsersArray[$idSite]->getColumn(0);
			$site['unique'] = $tmp[0];
			
			
			if($period != 'range')
			{
				$tmp = $lastVisitsArray[$idSite]->getColumn(0);
				$site['lastVisits'] = $tmp[0];
				$tmp = $lastActionsArray[$idSite]->getColumn(0);
				$site['lastActions'] = $tmp[0];
				$tmp = $lastUniqueUsersArray[$idSite]->getColumn(0);
				$site['lastUnique'] = $tmp[0];
			}
			$site['visitsSummaryValue'] = isset($visitsSummary[$idSite]) ? $visitsSummary[$idSite] : 0;
			$site['actionsSummaryValue'] = isset($actionsSummary[$idSite]) ? $actionsSummary[$idSite] : 0;
			$site['uniqueSummaryValue'] = isset($uniqueSummary[$idSite]) ? $uniqueSummary[$idSite] : 0;
			
		}
		
		$view = new Piwik_View("MultiSites/templates/index.tpl");
		$view->mySites = $mySites;
		$view->evolutionBy = $this->evolutionBy;
		$view->period = $period;
		$view->date = $date;
		$view->page = $this->page;
		$view->limit = $this->limit;
		$view->orderBy = $this->orderBy;
		$view->order = $this->order;
		$view->totalVisits = $totalVisits;
		$view->totalActions = $totalActions;
	
		$view->autoRefreshTodayReport = false;
		// if the current date is today, or yesterday, 
		// in case the website is set to UTC-12), or today in UTC+14, we refresh the page every 5min
		if(in_array($date, array(	'today', date('Y-m-d'), 
											'yesterday', Piwik_Date::factory('yesterday')->toString('Y-m-d'),
											Piwik_Date::factory('now', 'UTC+14')->toString('Y-m-d'))))
		{
			$view->autoRefreshTodayReport = true;
		}
		$this->setGeneralVariablesView($view);
		$this->setMinMaxDateAcrossWebsites($mySites, $view);
		$view->show_sparklines = Zend_Registry::get('config')->General->show_multisites_sparklines;

		echo $view->render();
	}

	/**
	 * The Multisites reports displays the first calendar date as the earliest day available for all websites.
	 * Also, today is the later "today" available across all timezones.
	 * @param $mySites
	 * @param $view
	 * @return void
	 */
	private function setMinMaxDateAcrossWebsites($mySites, $view)
	{
		$minDate = null;
		$maxDate = Piwik_Date::now()->subDay(1);
		foreach($mySites as &$site)
		{
			// look for 'now' in the website's timezone
			$timezone = $site['timezone'];
			$date = Piwik_Date::factory('now', $timezone);
			if($date->isLater($maxDate))
			{
				$maxDate = clone $date;
			}
			
			// look for the absolute minimum date
			$creationDate = $site['ts_created'];
			$date = Piwik_Date::factory($creationDate, $timezone);
			if(is_null($minDate) 
				|| $date->isEarlier($minDate))
			{
				$minDate = clone $date;
			}
		}
		$this->setMinDateView($minDate, $view);
		$this->setMaxDateView($maxDate, $view);
	}
	
	private function getSummary($lastVisits, $currentVisits, $mySites, $type)
	{
		$currentVisitsArray = $currentVisits->getArray();
		$lastVisitsArray = $lastVisits->getArray();
		$summaryArray = array();
		foreach($mySites as $site)
		{
			$idSite = $site['idsite'];
			$tmp = $currentVisitsArray[$idSite]->getColumn(0);
			$current = $tmp[0];
			$tmp = $lastVisitsArray[$idSite]->getColumn(0);
			$last = $tmp[0];
			$summaryArray[$idSite] = $this->fillSummary($current, $last);
		}
		return $summaryArray;
	}

	private function fillSummary($current, $last)
	{
		if($current == 0 && $last == 0)
		{
			$summary = 0;
		}
		elseif($last == 0)
		{
			$summary = 100;
		}
		else
		{
			$summary = (($current - $last) / $last) * 100;
		}

		$output = round($summary,2);

		return $output;
	}

	public function getEvolutionGraph( $fetch = false, $columns = false)
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "VisitsSummary.get");
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
		}
		$columns = !is_array($columns) ? array($columns) : $columns;
		$view->setColumnsToDisplay($columns);
		return $this->renderView($view, $fetch);
	}
}
