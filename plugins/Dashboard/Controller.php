<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 241 2008-01-26 01:30:37Z matt $
 * 
 * @package Piwik_Home
 * 
 */


require_once "API/Request.php";
require_once "ViewDataTable.php";

/**
 * 
 * @package Piwik_Dashboard
 */
class Piwik_Dashboard_Controller extends Piwik_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->currentControllerName = 'Dashboard';
		
		//FIXME: copy paste of Home controller => should be refactored
		//in a 'master' controller for statistics (tracs #91)
		$this->strDate = Piwik_Common::getRequestVar('date', 'yesterday','string');
		
		// the date looks like YYYY-MM-DD we can build it
		try{
			$this->date = Piwik_Date::factory($this->strDate);
			$this->strDate = $this->date->toString();
		} catch(Exception $e){
		// the date looks like YYYY-MM-DD,YYYY-MM-DD or other format
			// case the date looks like a range
			$this->date = null;
		}
	}
	
	function getListWidgets()
	{
		$widgets = Piwik_GetListWidgets();
		$json = json_encode($widgets);
		return $json;
	}
	
	function getDefaultAction()
	{
		return 'redirectToIndex';
	}
	
	function redirectToIndex()
	{
		header("Location:?module=Dashboard&action=index&idSite=1&period=day&date=yesterday");
	}
	
	public function index()
	{
		$view = new Piwik_View('Dashboard/templates/index.tpl');
		$this->setGeneralVariablesView($view);
		echo $view->render();
	}
	
	//FIXME: copy paste of Home controller => should be refactored
	//in a 'master' controller for statistics (tracs #91)
	protected function setGeneralVariablesView($view)
	{
		// date
		$view->date = $this->strDate;
		$oDate = new Piwik_Date($this->strDate);
		$view->prettyDate = $oDate->get("l jS F Y");
		
		// period
		$currentPeriod = Piwik_Common::getRequestVar('period');
		$otherPeriodsAvailable = array('day','week','month','year');
		
		$found = array_search($currentPeriod,$otherPeriodsAvailable);
		if($found !== false)
		{
			unset($otherPeriodsAvailable[$found]);
		}
		
		$view->period = $currentPeriod;
		$view->otherPeriods = $otherPeriodsAvailable;
		
		// other
		$view->idSite = Piwik_Common::getRequestVar('idSite');
		
		$view->userLogin = Piwik::getCurrentUserLogin();
		$view->sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
		$view->url = Piwik_Url::getCurrentUrl();
	}
}