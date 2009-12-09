<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Dashboard
 */

/**
 *
 * @package Piwik_Dashboard
 */
class Piwik_Dashboard_Controller extends Piwik_Controller
{
	protected function getDashboardView($template)
	{
		$view = Piwik_View::factory($template);
		$this->setGeneralVariablesView($view);

		$view->layout = $this->getLayout();
		$view->availableWidgets = json_encode(Piwik_GetWidgetsList());
		return $view;
	}
	
	public function embeddedIndex()
	{
		$view = $this->getDashboardView('index');
		echo $view->render();
	}
	
	public function index()
	{
		$view = $this->getDashboardView('standalone');
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
			$session = new Zend_Session_Namespace("Piwik_Dashboard");
			$session->dashboardLayout = $layout;
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
			$session = new Zend_Session_Namespace("Piwik_Dashboard");

			if(!isset($session->dashboardLayout))
			{
				return false;
			}
			$layout = $session->dashboardLayout;
		}
		else
		{
			$layout = $this->getLayoutForUser($currentUser,$idDashboard);
		}
	
		// layout was JSON.stringified
		$layout = html_entity_decode($layout);
		$layout = str_replace("\\\"", "\"", $layout);

		// compatibility with the old layout format
		if(!empty($layout)
			&& strstr($layout, '[[') == false) {
			$layout = "'$layout'";
		}
		
		// if the json decoding works (ie. new Json format)
		// we will only return the widgets that are from enabled plugins
		if($layoutObject = json_decode($layout, $assoc = true)) 
		{
			foreach($layoutObject as &$row) 
			{
				foreach($row as $widgetId => $widget)
				{
					if(isset($widget->parameters->module)) {
    					$pluginName = $widget->parameters->module;
    					if(!Piwik_PluginsManager::getInstance()->isPluginActivated($pluginName))
    					{
    						unset($row[$widgetId]);
    					}
					}
				}
			}
			$layout = json_encode($layoutObject);
		}
		return $layout;
	}
}


























