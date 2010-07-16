<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
	protected $limit = 20;
	protected $period;
	protected $date;
	protected $dateToStr;


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
		$date = $this->getDateParameterInTimezone($date, $piwikDefaultTimezone);
		$this->setDate($date);
		
		$mySites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess();
		$params = $this->getGraphParamsModified();
		$this->dateToStr = $params['date'];

		$ids = 'all';
		$this->period = Piwik_Common::getRequestVar('period', 'day');		

		$lastDate =  date('Y-m-d',strtotime("-1 ".$this->period, strtotime($this->strDate)));

		$visits = Piwik_VisitsSummary_API::getInstance()->getVisits($ids, $this->period, $this->strDate);
		$lastVisits = Piwik_VisitsSummary_API::getInstance()->getVisits($ids, $this->period, $lastDate);

		$actions = Piwik_VisitsSummary_API::getInstance()->getActions($ids, $this->period, $this->strDate);
		$lastActions = Piwik_VisitsSummary_API::getInstance()->getActions($ids, $this->period, $lastDate);

		$uniqueUsers = Piwik_VisitsSummary_API::getInstance()->getUniqueVisitors($ids, $this->period, $this->strDate);
		$lastUniqueUsers = Piwik_VisitsSummary_API::getInstance()->getUniqueVisitors($ids, $this->period, $lastDate);

		$visitsSummary = $this->getSummary($lastVisits, $visits, $mySites, "visits");
		$actionsSummary = $this->getSummary($lastActions, $actions, $mySites, "actions");
		$uniqueSummary = $this->getSummary($lastUniqueUsers, $uniqueUsers, $mySites, "unique");

		$visitsArray = $visits->getArray();
		$actionsArray = $actions->getArray();
		$uniqueUsersArray = $uniqueUsers->getArray();
		$lastVisitsArray = $lastVisits->getArray();
		$lastActionsArray = $lastActions->getArray();
		$lastUniqueUsersArray = $lastUniqueUsers->getArray();
		foreach($mySites as &$site)
		{
			$idSite = $site['idsite'];
			$site['visits'] = array_shift($visitsArray[$idSite]->getColumn(0));
			$site['actions'] = array_shift($actionsArray[$idSite]->getColumn(0));
			$site['unique'] = array_shift($uniqueUsersArray[$idSite]->getColumn(0));
			$site['lastVisits'] = array_shift($lastVisitsArray[$idSite]->getColumn(0));
			$site['lastActions'] = array_shift($lastActionsArray[$idSite]->getColumn(0));
			$site['lastUnique'] = array_shift($lastUniqueUsersArray[$idSite]->getColumn(0));
			$site['visitsSummaryValue'] = $visitsSummary[$idSite];
			$site['actionsSummaryValue'] = $actionsSummary[$idSite];
			$site['uniqueSummaryValue'] = $uniqueSummary[$idSite];
		
		}
		
		$view = new Piwik_View("MultiSites/templates/index.tpl");
		$view->mySites = $mySites;
		$view->evolutionBy = $this->evolutionBy;
		$view->period = $this->period;
		$view->date = $this->strDate;
		$view->page = $this->page;
		$view->limit = $this->limit;
		$view->orderBy = $this->orderBy;
		$view->order = $this->order;
		$view->dateToStr = $this->dateToStr;
	
		$view->autoRefreshTodayReport = false;
		// if the current date is today, or yesterday, 
		// in case the website is set to UTC-12), or today in UTC+14, we refresh the page every 5min
		if(in_array($this->strDate, array(	'today', date('Y-m-d'), 
											'yesterday', Piwik_Date::factory('yesterday')->toString('Y-m-d'),
											Piwik_Date::factory('now', 'UTC+14')->toString('Y-m-d'))))
		{
			$view->autoRefreshTodayReport = true;
		}
		$this->setGeneralVariablesView($view);
		$this->setMinMaxDateAcrossWebsites($mySites, $view);
		
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
		$maxDate = Piwik_Date::now();
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
			$current = array_shift($currentVisitsArray[$idSite]->getColumn(0));
			$last = array_shift($lastVisitsArray[$idSite]->getColumn(0));
			$summaryArray[$idSite] = $this->fillSummary($current, $last, $this->evolutionBy);
		}
		return $summaryArray;
	}

	private function fillSummary($current, $last, $type)
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
