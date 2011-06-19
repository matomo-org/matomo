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
	protected $orderBy = 'visits';
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
		$displayRevenueColumn = Piwik_Common::isGoalPluginEnabled();
		
		// overwrites the default Date set in the parent controller 
		// Instead of the default current website's local date, 
		// we set "today" or "yesterday" based on the default Piwik timezone
		$piwikDefaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();
		$dateRequest = Piwik_Common::getRequestVar('date', 'today');
		$period = Piwik_Common::getRequestVar('period', 'day');	
		$date = $dateRequest;
		if($period != 'range')
		{
			$date = $this->getDateParameterInTimezone($dateRequest, $piwikDefaultTimezone);
			$date = $date->toString();
		}
		
		$mySites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess();
		
		$ids = 'all';
		
		// Current date - select metrics
		$dataTableArray = Piwik_VisitsSummary_API::getInstance()->get($ids, $period, $date, $segment = false, $columns = array('nb_visits', 'nb_actions'));
		$currentVisits = $this->getArrayFromAPI($dataTableArray, 'nb_visits');
		$currentActions = $this->getArrayFromAPI($dataTableArray, 'nb_actions');
		if($displayRevenueColumn)
		{
		    $dataTableArray = Piwik_Goals_API::getInstance()->get($ids, $period, $date, $segment = false, $idGoal = false, $columns = array('revenue'));
		    $currentRevenue = $this->getArrayFromAPI($dataTableArray, 'revenue');
		}
		// Previous date
		$lastVisits = $lastActions = $lastRevenue = array();
		if($period != 'range')
		{
			$lastDate = Piwik_Period_Range::removePeriod($period, Piwik_Date::factory($date), $n = 1 );
			$dataTableArray = Piwik_VisitsSummary_API::getInstance()->get($ids, $period, $lastDate, $segment = false, $columns = array('nb_visits', 'nb_actions'));
			$lastVisits =  $this->getArrayFromAPI($dataTableArray, 'nb_visits');
			$lastActions =  $this->getArrayFromAPI($dataTableArray, 'nb_actions');
			if($displayRevenueColumn)
			{
			    $dataTableArray = Piwik_Goals_API::getInstance()->get($ids, $period, $lastDate, $segment = false, $idGoal = false, $columns = array('revenue'));
			    $lastRevenue = $this->getArrayFromAPI($dataTableArray, 'revenue');
			}
		}
		
		$visitsSummary = $this->getChangeCurrentVsLast($currentVisits, $lastVisits);
		$actionsSummary = $this->getChangeCurrentVsLast($currentActions, $lastActions);
		if($displayRevenueColumn)
		{
		    $revenueSummary = $this->getChangeCurrentVsLast($currentRevenue, $lastRevenue);
		}
		$totalVisits = $totalActions = $totalRevenue = 0;
		
		foreach($mySites as &$site)
		{
			$idSite = $site['idsite'];
			if($period != 'range')
			{
				$site['lastVisits'] = $lastVisits[$idSite];
				$site['lastActions'] = $lastActions[$idSite];
				if($displayRevenueColumn)
				{
				    $site['lastRevenue'] = $lastRevenue[$idSite];
				}
			}
			
			$site['visits'] = $currentVisits[$idSite];
			$site['actions'] = $currentActions[$idSite];
			$totalVisits += $site['visits'];
			$totalActions += $site['actions'];
			$site['visitsSummaryValue'] = $visitsSummary[$idSite];
			$site['actionsSummaryValue'] = $actionsSummary[$idSite];
			$site['revenue'] = $site['revenueSummaryValue'] = 0;
			if($displayRevenueColumn)
			{
    			$site['revenue'] = $currentRevenue[$idSite];
    			$totalRevenue += $site['revenue'];
    			$site['revenueSummaryValue'] = $revenueSummary[$idSite];
			}
		}
		$mySites = $this->applyPrettyMoney($mySites);
		
		$view = new Piwik_View("MultiSites/templates/index.tpl");
		$view->mySites = $mySites;
		$view->evolutionBy = $this->evolutionBy;
		$view->period = $period;
		$view->dateRequest = $dateRequest;
		$view->page = $this->page;
		$view->limit = $this->limit;
		$view->orderBy = $this->orderBy;
		$view->order = $this->order;
		$view->totalVisits = $totalVisits;
		$view->totalRevenue = $totalRevenue;
		$view->displayRevenueColumn = $displayRevenueColumn;
		$view->totalActions = $totalActions;
	
		$params = $this->getGraphParamsModified();
		$view->dateSparkline = $period == 'range' ? $dateRequest : $params['date'];
		
		$view->autoRefreshTodayReport = false;
		// if the current date is today, or yesterday, 
		// in case the website is set to UTC-12), or today in UTC+14, we refresh the page every 5min
		if(in_array($date, array(	'today', date('Y-m-d'), 
											'yesterday', Piwik_Date::factory('yesterday')->toString('Y-m-d'),
											Piwik_Date::factory('now', 'UTC+14')->toString('Y-m-d'))))
		{
			
			$view->autoRefreshTodayReport = Zend_Registry::get('config')->General->multisites_refresh_after_seconds;
		}
		$this->setGeneralVariablesView($view);
		$this->setMinMaxDateAcrossWebsites($mySites, $view);
		$view->show_sparklines = Zend_Registry::get('config')->General->show_multisites_sparklines;

		echo $view->render();
	}
	
	protected function applyPrettyMoney($sites)
	{
		foreach($sites as &$site)
		{
			$revenue = "-";
			if(!empty($site['revenue']))
			{
				$revenue = Piwik::getPrettyMoney($site['revenue'], $site['idsite'], $htmlAllowed = false); 
			}
			$site['revenue'] = '"'. $revenue . '"';
		}
		return $sites;
	}
	
	protected function getChangeCurrentVsLast($current, $last)
	{
		$evolution = array();
		foreach($current as $idSite => $value)
		{
			$evolution[$idSite] = $this->getEvolutionPercentage($value, isset($last[$idSite]) ? $last[$idSite] : 0);
		}
		return $evolution;
	}

	private function getEvolutionPercentage($current, $last)
	{
		if($current == 0 && $last == 0)
		{
			$evolution = 0;
		}
		elseif($last == 0)
		{
			$evolution = 100;
		}
		else
		{
			$evolution = (($current - $last) / $last) * 100;
		}

		$output = round($evolution,2);

		return $output;
	}

	protected function getArrayFromAPI($dataTableArray, $column)
	{
		$values = array();
		foreach($dataTableArray->getArray() as $id => $row)
		{
			$firstRow = $row->getFirstRow();
			$value = 0;
			if($firstRow)
			{
				$value = $firstRow->getColumn($column);
			}
			if($column == 'revenue')
			{
				$value = round($value);
			}
			$values[$id] = $value;
		}
		return $values;
	}
	
	/**
	 * The Multisites reports displays the first calendar date as the earliest day available for all websites.
	 * Also, today is the later "today" available across all timezones.
	 * @param array $mySites
	 * @param Piwik_View $view
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
	
	public function getEvolutionGraph( $fetch = false, $columns = false)
	{
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
		}
		$api = "VisitsSummary.get";
		
		if($columns == 'revenue')
		{
			$api = "Goals.get";
		}
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, $api);
		$columns = !is_array($columns) ? array($columns) : $columns;
		$view->setColumnsToDisplay($columns);
		return $this->renderView($view, $fetch);
	}
}
