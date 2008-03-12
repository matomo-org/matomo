<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Home
 * 
 */


require_once "API/Request.php";
require_once "ViewDataTable.php";

/**
 * 
 * @package Piwik_Home
 */
class Piwik_Home_Controller extends Piwik_Controller
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
			header("Location:?module=Home&action=index&idSite=$firstSiteId&period=day&date=yesterday");
		}
		else
		{
			if(($currentLogin = Piwik::getCurrentUserLogin()) != 'anonymous')
			{
				Piwik_ExitWithMessage( "You are logged in as '$currentLogin' but it seems you don't have any permission set in Piwik.
				<br />Ask your Piwik administrator to give you 'view' access to a website.
				<br /><br />&nbsp;&nbsp;&nbsp;<b><a href='?module=Login&action=logout'>&rsaquo; Logout from Piwik</a></b><br />");
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
		$oDate = new Piwik_Date($this->strDate);
		$view->prettyDate = $oDate->get("l j\<\s\u\p\>S\<\/\s\u\p\> F Y");
		
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
		
		$view = new Piwik_View('Home/templates/index.tpl');
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