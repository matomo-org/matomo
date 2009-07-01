<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ExamplePlugin
 */

/**
 * 
 * @package Piwik_ExamplePlugin
 */
class Piwik_CorePluginsAdmin_Controller extends Piwik_Controller
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
			$plugins[$pluginName]= array( 	'activated' => Piwik_PluginsManager::getInstance()->isPluginActivated($pluginName),
											'alwaysActivated' => Piwik_PluginsManager::getInstance()->isPluginAlwaysActivated($pluginName),
											'info' => $oPlugin->getInformation()
									);
		}
		
		$view = new Piwik_View('CorePluginsAdmin/templates/manage.tpl');
		$view->pluginsName = $plugins;
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}

	function deactivate()
	{
		Piwik::checkUserIsSuperUser();
		$pluginName = Piwik_Common::getRequestVar('pluginName', null, 'string');
		Piwik_PluginsManager::getInstance()->deactivatePlugin($pluginName);
		Piwik_Url::redirectToUrl('index.php?module=CorePluginsAdmin');
	}

	function activate()
	{
		Piwik::checkUserIsSuperUser();
		$pluginName = Piwik_Common::getRequestVar('pluginName', null, 'string');
		Piwik_PluginsManager::getInstance()->activatePlugin($pluginName);
		Piwik_Url::redirectToUrl('index.php?module=CorePluginsAdmin');
	}
}
