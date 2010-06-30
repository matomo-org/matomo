<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CorePluginsAdmin
 */

/**
 *
 * @package Piwik_CorePluginsAdmin
 */
class Piwik_CorePluginsAdmin_Controller extends Piwik_Controller
{	
	function index()
	{
		Piwik::checkUserIsSuperUser();
		
		$plugins = array();
	
		$listPlugins = Piwik_PluginsManager::getInstance()->readPluginsDirectory();
		foreach($listPlugins as $pluginName)
		{
			$oPlugin = Piwik_PluginsManager::getInstance()->loadPlugin($pluginName);
			$plugins[$pluginName] = array(
			 	'activated' => Piwik_PluginsManager::getInstance()->isPluginActivated($pluginName),
				'alwaysActivated' => Piwik_PluginsManager::getInstance()->isPluginAlwaysActivated($pluginName),
			);
		}

		Piwik_PluginsManager::getInstance()->loadTranslations();

		$loadedPlugins = Piwik_PluginsManager::getInstance()->getLoadedPlugins();
		foreach($loadedPlugins as $oPlugin)
		{
			$pluginName = $oPlugin->getClassName();
			$plugins[$pluginName]['info'] = $oPlugin->getInformation();
		}

		$view = Piwik_View::factory('manage');
		$view->pluginsName = $plugins;
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		if(!Zend_Registry::get('config')->isFileWritable())
		{
			$view->configFileNotWritable = true;
		}
		echo $view->render();
	}

	public function deactivate()
	{
		Piwik::checkUserIsSuperUser();
		$this->checkTokenInUrl();
		$pluginName = Piwik_Common::getRequestVar('pluginName', null, 'string');
		Piwik_PluginsManager::getInstance()->deactivatePlugin($pluginName);
		Piwik_Url::redirectToUrl('index.php?module=CorePluginsAdmin&action=index');
	}

	public function activate()
	{
		Piwik::checkUserIsSuperUser();
		$this->checkTokenInUrl();
		$pluginName = Piwik_Common::getRequestVar('pluginName', null, 'string');
		Piwik_PluginsManager::getInstance()->activatePlugin($pluginName);
		Piwik_Url::redirectToUrl('index.php?module=CorePluginsAdmin&action=index');
	}
}
