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
	
	protected function setGeneralVariablesView($view)
	{
		parent::setGeneralVariablesView($view);
		$view->menu = Piwik_GetMenu();
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
		$view->content = '';
		return $view;
	}
	
	public function index()
	{
		$view = $this->getDefaultIndexView();
		echo $view->render();		
	}
}
