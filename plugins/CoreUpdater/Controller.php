<?php
require_once "Updater.php";
require_once "Version.php";

class Piwik_CoreUpdater_Controller extends Piwik_Controller
{
	const CONFIG_FILE_BACKUP = '/config/global.ini.auto-backup-before-update.php';
	const PATH_TO_EXTRACT_LATEST_VERSION = '/tmp/latest';
	const LATEST_PIWIK_URL = 'http://piwik.org/last.zip';

	private $componentsWithUpdateFile = array();
	private $coreError = false;
	private $warningMessages = array();
	private $errorMessages = array();
	private $deactivatedPlugins = array();
	
	public function newVersionAvailable()
	{
		Piwik::checkUserIsSuperUser();
		require_once "UpdateCheck.php";
		$this->checkNewVersionIsAvailableOrDie();
		
		$view = new Piwik_View('CoreUpdater/templates/update_new_version_available.tpl');
		$view->piwik_version = Piwik_Version::VERSION;
		$view->piwik_new_version = $newVersion;
		echo $view->render();
	}

	public function oneClickUpdate()
	{
		Piwik::checkUserIsSuperUser();
		$this->checkNewVersionIsAvailableOrDie();

		$steps = array(
			array('oneClick_Download', Piwik_Translate('CoreUpdate_DownloadingUpdateFromX', self::LATEST_PIWIK_URL)),
			array('oneClick_Unpack', Piwik_Translate('CoreUpdate_UnpackingTheUpdate')),
			array('oneClick_Verify', Piwik_Translate('CoreUpdater_VerifyingUnpackedFiles')),
			array('oneClick_CreateConfigFileBackup', Piwik_Translate('CoreUpdater_CreatingBackupOfConfigurationFile', self::CONFIG_FILE_BACKUP)),
			array('oneClick_Copy', Piwik_Translate('CoreUpdater_InstallingTheLatestVersion')),
			array('oneClick_Finished', Piwik_Translate('CoreUpdater_PiwikUpdatedSuccessfully')),
		);
		
		$errorMessage = false;
		$messages = array();
		foreach($steps as $step) {
			try {
				$method = $step[0];
				$message = $step[1];
				$this->$method();
				$messages[] = $message;
			} catch(Exception $e) {
				$errorMessage = $e->getMessage();
				break;
			}
		}
		
		$view = new Piwik_View('CoreUpdater/templates/update_one_click_done.tpl');
		$view->coreError = $errorMessage;
		$view->feedbackMessages = $messages;
		echo $view->render();
	}
	
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

	private function checkNewVersionIsAvailableOrDie()
	{
		if(!Piwik_UpdateCheck::isNewestVersionAvailable())
		{
			throw new Exception("Your Piwik version ".Piwik_Version::VERSION." is up to date.");
		}
	}
	
	private function oneClick_Download()
	{
		$this->pathPiwikZip = PIWIK_INCLUDE_PATH . self::PATH_TO_EXTRACT_LATEST_VERSION . '/last.zip';
		Piwik::checkDirectoriesWritableOrDie( array(self::PATH_TO_EXTRACT_LATEST_VERSION) );
		$fetched = Piwik::fetchRemoteFile(self::LATEST_PIWIK_URL, $this->pathPiwikZip);
	}
	
	private function oneClick_Unpack()
	{
		require_once 'PclZip/pclzip.lib.php';
		$archive = new PclZip($this->pathPiwikZip);

		$pathExtracted = PIWIK_INCLUDE_PATH.self::PATH_TO_EXTRACT_LATEST_VERSION;
		if ( false == ($archive_files = $archive->extract(
							PCLZIP_OPT_PATH, $pathExtracted)) )
		{
			throw new Exception('Incompatible archive: ' . $archive->errorInfo(true));
		}	
	
		if ( 0 == count($archive_files) )
		{
			throw new Exception('Empty archive.');
		}
		unlink($this->pathPiwikZip);
		$this->pathRootExtractedPiwik = $pathExtracted . '/piwik';
	}
	
	private function oneClick_Verify()
	{
		$someExpectedFiles = array( 
									'/config/global.ini.php',
									'/index.php',
									'/core/Piwik.php',
									'/piwik.php',
									'/plugins/API/API.php'
		);
		foreach($someExpectedFiles as $file) 
		{
			if(!is_file($this->pathRootExtractedPiwik . $file))
			{
				throw new Exception("Archive is incomplete: some files are missing (eg. $file).");
			}
		}
	}
	
	private function oneClick_CreateConfigFileBackup()
	{
		$configFileBefore = PIWIK_INCLUDE_PATH . '/config/global.ini.php';
		$configFileAfter = PIWIK_INCLUDE_PATH . self::CONFIG_FILE_BACKUP;
		Piwik::copy($configFileBefore, $configFileAfter);
	}
	
	private function oneClick_Copy()
	{
		Piwik::copyRecursive($this->pathRootExtractedPiwik, PIWIK_INCLUDE_PATH);
		Piwik::unlinkRecursive($this->pathRootExtractedPiwik, true);
	}
	
	private function oneClick_Finished()
	{
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
		$view->new_piwik_version = Piwik_Version::VERSION;
		$view->current_piwik_version = Piwik_GetOption('version_core');
	
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
		
		$view = new Piwik_View('CoreUpdater/templates/update_database_done.tpl');
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
