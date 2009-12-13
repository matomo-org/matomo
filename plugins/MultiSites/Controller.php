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

	public function index()
	{
		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		$view->basicHtmlView = true;

		$view->content = $this->getSitesInfo();

		echo $view->render();		
	}

	public function getSitesInfo()
	{
		$view = Piwik_View::factory('getSitesInfo');
		$this->setGeneralVariablesView($view);
		$view->basicHtmlView = true;

		$mySites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();

		$params = $this->getGraphParamsModified();
		$this->dateToStr = $params['date'];

		$ids = 'all';
		$this->period = PiwiK_Common::getRequestVar('period', 'day');

		$this->date = PiwiK_Common::getRequestVar('date', 'today');
		$lastDate =  date('Y-m-d',strtotime("-1 ".$this->period, strtotime($this->date)));

		$visits = Piwik_VisitsSummary_API::getVisits($ids, $this->period, $this->date);
		$lastVisits = Piwik_VisitsSummary_API::getVisits($ids, $this->period, $lastDate);

		$actions = Piwik_VisitsSummary_API::getActions($ids, $this->period, $this->date);
		$lastActions = Piwik_VisitsSummary_API::getActions($ids, $this->period, $lastDate);

		$uniqueUsers = Piwik_VisitsSummary_API::getUniqueVisitors($ids, $this->period, $this->date);
		$lastUniqueUsers = Piwik_VisitsSummary_API::getUniqueVisitors($ids, $this->period, $lastDate);

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

		$view->mySites = $mySites;
		$view->arrowDown = '<img src="plugins/MultiSites/images/arrow_asc.gif" width="16px" height="16px" />';
		$view->arrowUp = '<img src="plugins/MultiSites/images/arrow_desc.gif" width="16px" height="16px" />';
		$view->evolutionBy = $this->evolutionBy;
		$view->period = $this->period;
		$view->date = $this->date;
		$view->page = $this->page;
		$view->limit = $this->limit;
		$view->orderBy = $this->orderBy;
		$view->order = $this->order;
		$view->dateToStr = $this->dateToStr;

		$this->setPeriodVariablesView($view);
		$period = Piwik_Period::factory(Piwik_Common::getRequestVar('period'), Piwik_Date::factory($this->strDate));
		$view->prettyDate = $period->getLocalizedLongString();

		return $view->render();
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
		if(($current == 0 && $last == 0) || $current == 0)
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
		$view->setColumnsToDisplay($columns);
		return $this->renderView($view, $fetch);
	}
}
