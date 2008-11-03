<?php
require_once "Updater.php";
require_once "Version.php";

class Piwik_CoreUpdater_Controller 
{
	private $componentsWithUpdateFile = array();
	private $coreError = false;
	private $warningMessages = array();
	private $errorMessages = array();
	private $deactivatedPlugins = array();
	
	public function checkForCoreAndPluginsUpdates()
	{
		$this->updater = new Piwik_Updater();
		$this->updater->addComponentToCheck('core', Piwik_Version::VERSION);
		
		$plugins = Piwik_PluginsManager::getInstance()->getInstalledPlugins();
		foreach($plugins as $pluginName => $plugin)
		{
			$this->updater->addComponentToCheck($pluginName, $plugin->getVersion());
		}
		
		$this->componentsWithUpdateFile = $this->updater->getComponentsWithUpdateFile();
		if(count($this->componentsWithUpdateFile) == 0)
		{
			return;
		}
		
		$this->runUpdaterAndExit();
	}
	
	private function runUpdaterAndExit()
	{
		if(Piwik_Common::getRequestVar('updateCorePlugins', 0, 'integer') == 1)
		{
			$this->doExecuteUpdates();
		}
		else
		{
			$this->doWelcomeUpdates();
		}
		exit;
	}
	
	private function doWelcomeUpdates()
	{
		$view = new Piwik_View('CoreUpdater/templates/update_welcome.tpl');
		$view->piwik_version = Piwik_Version::VERSION;
	
		$pluginNamesToUpdate = array();
		$coreToUpdate = false;
		foreach($this->componentsWithUpdateFile as $name => $filenames)
		{
			if($name == 'core')
			{
				$coreToUpdate = true;
			}
			else
			{
				$pluginNamesToUpdate[] = $name;
			}
		}

		$view->pluginNamesToUpdate = $pluginNamesToUpdate;
		$view->coreToUpdate = $coreToUpdate; 
		echo $view->render();
	}

	private function doExecuteUpdates()
	{
		$this->loadAndExecuteUpdateFiles();
		
		$view = new Piwik_View('CoreUpdater/templates/update_done.tpl');
		$view->coreError = $this->coreError;
		$view->warningMessages = $this->warningMessages;
		$view->errorMessages = $this->errorMessages;
		$view->deactivatedPlugins = $this->deactivatedPlugins;
		echo $view->render();
	}

	private function loadAndExecuteUpdateFiles()
	{
		// if error in any core update, show message + help message + EXIT
		// if errors in any plugins updates, show them on screen, disable plugins that errored + CONTINUE
		// if warning in any core update or in any plugins update, show message + CONTINUE
		// if no error or warning, success message + CONTINUE
		foreach($this->componentsWithUpdateFile as $name => $filenames)
		{
			try {
				$this->warningMessages = array_merge($this->warningMessages, $this->updater->update($name));
			} catch (UpdateErrorException $e) {
				$this->errorMessages[] = $e->getMessage();
				if($name == 'core') 
				{
					$this->coreError = true;
					break;
				}
				else
				{
					Piwik_PluginsManager::getInstance()->deactivatePlugin($name);
					$this->deactivatedPlugins[] = $name;
				}
			}
		}
	}
}