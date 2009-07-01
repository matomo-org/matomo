<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_CoreHome
 * 
 */
require_once "API/Request.php";
require_once "ViewDataTable.php";

/**
 * @package Piwik_Dashboard
 */
class Piwik_Dashboard_Controller extends Piwik_Controller
{
	protected function getDashboardView($template)
	{
		$view = new Piwik_View($template);
		$this->setGeneralVariablesView($view);
		$layout = html_entity_decode($this->getLayout());
		if(!empty($layout)
			&& strstr($layout, '[[') == false) {
			$layout = "'$layout'";
		}
		$view->layout = $layout;
		$view->availableWidgets = json_encode(Piwik_GetWidgetsList());
		return $view;
	}
	
	public function embeddedIndex()
	{
		$view = $this->getDashboardView('Dashboard/templates/index.tpl');
		echo $view->render();
	}
	
	public function index()
	{
		$view = $this->getDashboardView('Dashboard/templates/standalone.tpl');
		echo $view->render();
	}
	
	/**
	 * Records the layout in the DB for the given user.
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
}


























