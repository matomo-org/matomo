<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 *
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'redirectToCoreHomeIndex';
	}
	
	function redirectToCoreHomeIndex()
	{
		$defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
		$module = 'CoreHome';
		$action = 'index';
		
		// User preference: default report to load is the All Websites dashboard
		if($defaultReport == 'MultiSites' 
			&& Piwik_PluginsManager::getInstance()->isPluginActivated('MultiSites'))
		{
			$module = 'MultiSites';
		}
		if($defaultReport == Piwik::getLoginPluginName())
		{
			$module = Piwik::getLoginPluginName();
		}
		
		parent::redirectToIndex($module, $action);
	}
	
	public function showInContext()
	{
		$controllerName = Piwik_Common::getRequestVar('moduleToLoad');
		$actionName = Piwik_Common::getRequestVar('actionToLoad', 'index');
		$view = $this->getDefaultIndexView();
		$view->content = Piwik_FrontController::getInstance()->fetchDispatch( $controllerName, $actionName );
		echo $view->render();	
	}
	
	protected function getDefaultIndexView()
	{
		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetMenu();
		$view->content = '';
		return $view;
	}
	
	protected function setDateTodayIfWebsiteCreatedToday()
	{
		$date = Piwik_Common::getRequestVar('date', false);
		if($date == 'today') 
		{
			return;
		} 
		$websiteId = Piwik_Common::getRequestVar('idSite', false);
		if ($websiteId) {
			$website = new Piwik_Site($websiteId);
			$datetimeCreationDate = $this->site->getCreationDate()->getDatetime();
			$creationDateLocalTimezone = Piwik_Date::factory($datetimeCreationDate, $website->getTimezone())->toString('Y-m-d');
			$todayLocalTimezone = Piwik_Date::factory('now', $website->getTimezone())->toString('Y-m-d');
			if( $creationDateLocalTimezone == $todayLocalTimezone ) 
			{
				Piwik::redirectToModule( 'CoreHome', 'index', 
										array(	'date' => 'today', 
												'idSite' => $websiteId, 
												'period' => Piwik_Common::getRequestVar('period')) 
				);
			}
		}
	}
	
	public function index()
	{
		$this->setDateTodayIfWebsiteCreatedToday();
		$view = $this->getDefaultIndexView();
		echo $view->render();		
	}
}
