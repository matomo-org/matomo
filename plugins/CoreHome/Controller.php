<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 561 2008-07-21 00:00:35Z matt $
 * 
 * @package Piwik_CoreHome
 * 
 */


require_once "API/Request.php";
require_once "ViewDataTable.php";

/**
 * 
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'redirectToIndex';
	}
	function redirectToIndex()
	{
		$sitesId = Piwik_SitesManager_API::getSitesIdWithAtLeastViewAccess();
		if(!empty($sitesId))
		{
			$firstSiteId = $sitesId[0];
			$firstSite = new Piwik_Site($firstSiteId);
			if ($firstSite->getCreationDate()->isToday()) 
			{
				$defaultDate = 'today';
			}
			else
			{
				$defaultDate = Zend_Registry::get('config')->General->default_day;
			}
			header("Location:index.php?module=CoreHome&action=index&idSite=$firstSiteId&period=day&date=$defaultDate");
		}
		else
		{
			if(($currentLogin = Piwik::getCurrentUserLogin()) != 'anonymous')
			{
				Piwik_ExitWithMessage( sprintf(Piwik_Translate('CoreHome_NoPrivileges'),$currentLogin).
				"<br /><br />&nbsp;&nbsp;&nbsp;<b><a href='?module=Login&amp;action=logout'>&rsaquo; ".Piwik_Translate('General_Logout')."</a></b><br />");
			}
			else
			{
				Piwik_FrontController::dispatch('Login');
			}
		}
		exit;
	}
	
	protected function setGeneralVariablesView($view)
	{
		// date
		$view->date = $this->strDate;
		$oDate = Piwik_Date::factory($this->strDate);
		$localizedDateFormat = Piwik_Translate('CoreHome_LocalizedDateFormat');
		$view->prettyDate = $oDate->getLocalized($localizedDateFormat);
		
		// period
		$currentPeriod = Piwik_Common::getRequestVar('period');
		$otherPeriodsAvailable = array('day', 'week', 'month', 'year');

		$otherPeriodsNames = array(
			'day' => Piwik_Translate('CoreHome_PeriodDay'),
			'week' => Piwik_Translate('CoreHome_PeriodWeek'),
			'month' => Piwik_Translate('CoreHome_PeriodMonth'),
			'year' => Piwik_Translate('CoreHome_PeriodYear')
			);
		
		$found = array_search($currentPeriod,$otherPeriodsAvailable);
		if($found !== false)
		{
			unset($otherPeriodsAvailable[$found]);
		}
		
		$view->period = $currentPeriod;
		$view->otherPeriods = $otherPeriodsAvailable;
		$view->periodsNames = $otherPeriodsNames;
		
		// other
		$view->idSite = Piwik_Common::getRequestVar('idSite');
		
		$view->userLogin = Piwik::getCurrentUserLogin();
		$view->sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
		$view->url = Piwik_Url::getCurrentUrl();
		
		$view->menu = Piwik_GetMenu();
		$view->menuJson = json_encode($view->menu);
		//var_dump($view->menuJson);
	}

	public function showInContext()
	{
		$controllerName = Piwik_Common::getRequestVar('moduleToLoad');
		$actionName = Piwik_Common::getRequestVar('actionToLoad', 'index');
				
		$view = $this->getDefaultIndexView();
		$view->basicHtmlView = true;
		$view->content = Piwik_FrontController::getInstance()->fetchDispatch( $controllerName, $actionName );
		echo $view->render();	
	}
	
	protected function getDefaultIndexView()
	{
		
		$view = new Piwik_View('CoreHome/templates/index.tpl');
		$this->setGeneralVariablesView($view);
		
		$site = new Piwik_Site($view->idSite);
		$minDate = $site->getCreationDate();
		
		$view->minDateYear = $minDate->toString('Y');
		$view->minDateMonth = $minDate->toString('m');
		$view->minDateDay = $minDate->toString('d');
		
		$view->basicHtmlView = false;
		$view->content = '';
		return $view;
	}
	public function index()
	{
		$view = $this->getDefaultIndexView();
		echo $view->render();		
	}
}
	
