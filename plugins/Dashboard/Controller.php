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
	function getListWidgets()
	{
		$widgets = Piwik_GetListWidgets();
		$json = json_encode($widgets);
		return $json;
	}
	
	public function embeddedIndex()
	{		
		$view = new Piwik_View('Dashboard/templates/index.tpl');
		$this->setGeneralVariablesView($view);
		$view->layout = $this->getLayout();
		$view->availableWidgets = $this->getListWidgets();
		echo $view->render();
	}
	public function index()
	{
		//add the header for stand-alone mode
		$view = new Piwik_View('Dashboard/templates/header.tpl');
		echo $view->render();
		$this->embeddedIndex();
	}
	
	/**
	 * Records the layout in the DB for the given user.
	 * Parameters must be checked BEFORE this function call
	 *
	 * @param string $login
	 * @param int $idDashboard
	 * @param string $layout
	 */
	protected function saveLayoutForUser( $login, $idDashboard, $layout)
	{
		$paramsBind = array($login, $idDashboard, $layout, $layout);
		Piwik_Query('INSERT INTO '.Piwik::prefixTable('user_dashboard') .
					' (login, iddashboard, layout)
						VALUES (?,?,?)
					ON DUPLICATE KEY UPDATE layout=?',
					$paramsBind);
	}
	
	/**
	 * Returns the layout in the DB for the given user, or false if the layout has not been set yet.
	 * Parameters must be checked BEFORE this function call
	 *
	 * @param string $login
	 * @param int $idDashboard
	 * @param string|false $layout
	 */
	protected function getLayoutForUser( $login, $idDashboard)
	{
		$paramsBind = array($login, $idDashboard);
		$return = Piwik_FetchAll('SELECT layout FROM '.Piwik::prefixTable('user_dashboard') .
					' WHERE login = ? AND iddashboard = ?', $paramsBind);
		if(count($return) == 0)
		{
			return false;
		}
		return $return[0]['layout'];
	}
	
	/**
	 * Saves the layout for the current user
	 * anonymous = in the session
	 * authenticated user = in the DB
	 */
	public function saveLayout()
	{
		$layout = Piwik_Common::getRequestVar('layout');
		$idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int' );
		$currentUser = Piwik::getCurrentUserLogin();

		if($currentUser == 'anonymous')
		{
			$_SESSION['layout'][$idDashboard] = $layout;
		}
		else
		{
			$this->saveLayoutForUser($currentUser,$idDashboard, $layout);
		}
	}
	
	/**
	 * Get the dashboard layout for the current user (anonymous or loggued user) 
	 *
	 * @return string $layout
	 */
	protected function getLayout()
	{
		$idDashboard = Piwik_Common::getRequestVar('idDashboard', 1, 'int' );
		$currentUser = Piwik::getCurrentUserLogin();

		if($currentUser == 'anonymous')
		{
			if(!isset($_SESSION['layout'][$idDashboard]))
			{
				return false;
			}
			return $_SESSION['layout'][$idDashboard];
		}
		else
		{
			return $this->getLayoutForUser($currentUser,$idDashboard);
		}		
	}
	
	protected function setGeneralVariablesView($view)
	{
		$view->date = $this->strDate;
		$oDate = Piwik_Date::factory($this->strDate);
		$view->prettyDate = $oDate->get("l jS F Y");
		
		$currentPeriod = Piwik_Common::getRequestVar('period');
		$otherPeriodsAvailable = array('day','week','month','year');
		
		$found = array_search($currentPeriod,$otherPeriodsAvailable);
		if($found !== false)
		{
			unset($otherPeriodsAvailable[$found]);
		}
		
		$view->period = $currentPeriod;
		$view->otherPeriods = $otherPeriodsAvailable;
		$view->idSite = Piwik_Common::getRequestVar('idSite');
		$view->userLogin = Piwik::getCurrentUserLogin();
		$view->sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
		$view->url = Piwik_Url::getCurrentUrl();
	}
}

