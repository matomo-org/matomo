<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_API
 */

/**
 * 
 * @package Piwik_API
 */
class Piwik_API_Controller extends Piwik_Controller
{
	function index()
	{
		// when calling the API through http, we limit the number of returned results
		if(!isset($_GET['filter_limit']))
		{
			$_GET['filter_limit'] = Zend_Registry::get('config')->General->API_datatable_default_limit;
		}
		$request = new Piwik_API_Request('token_auth='.Piwik_Common::getRequestVar('token_auth', 'anonymous', 'string'));
		echo $request->process();
	}

	public function listAllMethods()
	{
		$ApiDocumentation = new Piwik_API_DocumentationGenerator();
		echo $ApiDocumentation->getAllInterfaceString( $outputExampleUrls = true, $prefixUrls = Piwik_Common::getRequestVar('prefixUrl', '') );
	}
	
	public function listAllAPI()
	{
		$view = new Piwik_View("API/templates/listAllAPI.tpl");
		$this->setGeneralVariablesView($view);
		
		$ApiDocumentation = new Piwik_API_DocumentationGenerator();
		$view->countLoadedAPI = Piwik_API_Proxy::getInstance()->getCountRegisteredClasses();
		$view->list_api_methods_with_links = $ApiDocumentation->getAllInterfaceString();
		echo $view->render();
	}
}

