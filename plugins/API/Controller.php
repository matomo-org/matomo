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

require_once "API/Request.php";


/**
 * 
 * @package Piwik_API
 */
class Piwik_API_Controller extends Piwik_Controller
{
	function index()
	{
		$request = new Piwik_API_Request();
		echo $request->process();
	}
	
	protected function init()
	{
		$plugins = Piwik_PluginsManager::getInstance()->getLoadedPluginsName();
		
		$loaded = 0;
		foreach( $plugins as $plugin )
		{		
			$plugin = Piwik::unprefixClass($plugin);
				
			try {
				Piwik_API_Proxy::getInstance()->registerClass($plugin);
				$loaded++;
			}
			catch(Exception $e){
			}
		}
		return $loaded;
	}
	
	function listAllMethods()
	{
		$this->init();
		echo Piwik_API_Proxy::getInstance()->getAllInterfaceString( $outputExampleUrls = true, $prefixUrls = Piwik_Common::getRequestVar('prefixUrl', '') );
	}
	
	function listAllAPI()
	{
		$view = new Piwik_View("API/templates/listAllAPI.tpl");
		$this->setGeneralVariablesView($view);
		$view->countLoadedAPI = $this->init();
		$view->list_api_methods_with_links = Piwik_API_Proxy::getInstance()->getAllInterfaceString();
		echo $view->render();
	}
	
}

