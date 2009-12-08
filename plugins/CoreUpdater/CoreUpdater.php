<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreUpdater
 */

/**
 *
 * @package Piwik_CoreUpdater
 */
class Piwik_CoreUpdater extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Updater',
			'description' => 'Piwik updating mechanism',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.dispatchCoreAndPluginUpdatesScreen' => 'dispatch',
			'FrontController.checkForUpdates' => 'updateCheck',
		);
		return $hooks;
	}

	function dispatch()
	{
		$language = Piwik_Common::getRequestVar('language', '', 'string');
		if($language != '')
		{
			$updaterController = new Piwik_CoreUpdater_Controller();
			$updaterController->saveLanguage();
			exit;
		}

		$updater = new Piwik_Updater();
		$updater->addComponentToCheck('core', Piwik_Version::VERSION);
		
		$plugins = Piwik_PluginsManager::getInstance()->getInstalledPlugins();
		foreach($plugins as $pluginName => $plugin)
		{
			$updater->addComponentToCheck($pluginName, $plugin->getVersion());
		}
		
		$componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
		if(count($componentsWithUpdateFile) == 0)
		{
			return;
		}
			
		$updaterController = new Piwik_CoreUpdater_Controller();
		$updaterController->runUpdaterAndExit($updater, $componentsWithUpdateFile);
	}	

	function updateCheck()
	{
		Piwik_UpdateCheck::check();
	}
}
