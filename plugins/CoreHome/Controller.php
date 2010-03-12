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
		// redirect to Login only for anonymous user
		if((bool)Zend_Registry::get('config')->General->default_module_login == true
			&& Piwik::getCurrentUserLogin() == 'anonymous')
		{
			return Piwik_FrontController::dispatch('Login', false);
		}
		parent::redirectToIndex('CoreHome', 'index');
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
		$view = Piwik_View::factory('index');
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetMenu();
		$view->content = '';
		return $view;
	}
	
	protected function setDateTodayIfWebsiteCreatedToday()
	{
		$date = Piwik_Common::getRequestVar('date', false);
		$date = Piwik_Date::factory($date);
		if($date->isToday()) {
			return;
		} 
		$websiteId = Piwik_Common::getRequestVar('idSite', false);
		if ($websiteId) {
			$website = new Piwik_Site($websiteId);
			if( $website->getCreationDate()->isToday() ) {
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
