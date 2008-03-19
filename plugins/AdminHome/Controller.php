<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version 
 * 
 * @package Piwik_AdminHome
 * 
 */

require_once "API/Request.php";


/**
 * 
 * @package Piwik_AdminHome
 */
class Piwik_AdminHome_Controller extends Piwik_Controller
{
	function getDefaultAction()
	{
		return 'redirectToIndex';
	}
	function redirectToIndex()
	{
		header("Location:index.php?module=AdminHome&action=showInContext&moduleToLoad=PluginsAdmin");
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
		
		$view = new Piwik_View('AdminHome/templates/index.tpl');
		$view->menu = Piwik_GetAdminMenu();
		$view->menuJson = json_encode($view->menu);
		
		$view->userLogin = Piwik::getCurrentUserLogin();
		$view->sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
		$view->url = Piwik_Url::getCurrentUrl();
		
		$view->basicHtmlView = false;
		$view->content = '';
		return $view;
	}
	
	public function index()
	{
		Piwik::checkUserIsSuperUser();
		
		$view = $this->getDefaultIndexView();
		echo $view->render();
	}
}

