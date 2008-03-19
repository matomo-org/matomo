<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Controller.php 169 2008-01-14 05:41:15Z matt $
 * 
 * @package Piwik_ExamplePlugin
 */



/**
 * 
 * @package Piwik_ExamplePlugin
 */
class Piwik_PluginsAdmin_Controller extends Piwik_Controller
{	
	function index()
	{
		Piwik::checkUserIsSuperUser();
		
		$listPlugins = Piwik_PluginsManager::getInstance()->readPluginsDirectory();
		
		$loadedPlugins = Piwik_PluginsManager::getInstance()->getLoadedPlugins();
		$plugins = array();

		foreach($listPlugins as $pluginName)
		{
			$oPlugin = Piwik_PluginsManager::getInstance()->loadPlugin($pluginName);
			$plugins[$pluginName]= array( 	'activated' => Piwik_PluginsManager::getInstance()->isPluginEnabled($pluginName),
											'alwaysActivated' => Piwik_PluginsManager::getInstance()->isPluginAlwaysActivated($pluginName),
											'info' => $oPlugin->getInformation()
									);
		}
		
		$view = new Piwik_View('PluginsAdmin/templates/manage.tpl');
		
		$view->pluginsName = $plugins;
				
		echo $view->render();
	}

	function deactivate()
	{
		Piwik::checkUserIsSuperUser();
		
		$pluginName = Piwik_Common::getRequestVar('pluginName', null, 'string');
		Piwik_PluginsManager::getInstance()->deactivatePlugin($pluginName);
		
		Piwik_Url::redirectToUrl('index.php?module=AdminHome&action=showInContext&moduleToLoad=PluginsAdmin');
		
	}
	function activate()
	{
		Piwik::checkUserIsSuperUser();
		
		$pluginName = Piwik_Common::getRequestVar('pluginName', null, 'string');
		Piwik_PluginsManager::getInstance()->activatePlugin($pluginName);

		Piwik_Url::redirectToUrl('index.php?module=AdminHome&action=showInContext&moduleToLoad=PluginsAdmin');
		
	}
}
