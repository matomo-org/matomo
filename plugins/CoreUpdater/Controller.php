<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreUpdater
 */

/**
 *
 * @package Piwik_CoreUpdater
 */
class Piwik_CoreUpdater_Controller extends Piwik_Controller
{
    const CONFIG_FILE_BACKUP = '/config/global.ini.auto-backup-before-update.php';
    const PATH_TO_EXTRACT_LATEST_VERSION = '/tmp/latest/';

    private $coreError = false;
    private $warningMessages = array();
    private $errorMessages = array();
    private $deactivatedPlugins = array();

    static protected function getLatestZipUrl($newVersion)
    {
        if (@Piwik_Config::getInstance()->Debug['allow_upgrades_to_beta']) {
            return 'http://builds.piwik.org/piwik-' . $newVersion . '.zip';
        }
        return Piwik_Config::getInstance()->General['latest_version_url'];
    }

    public function newVersionAvailable()
    {
        Piwik::checkUserIsSuperUser();

        $newVersion = $this->checkNewVersionIsAvailableOrDie();

        $view = Piwik_View::factory('update_new_version_available');
        $view->piwik_version = Piwik_Version::VERSION;
        $view->piwik_new_version = $newVersion;
        $view->piwik_latest_version_url = self::getLatestZipUrl($newVersion);
        $view->can_auto_update = Piwik::canAutoUpdate();
        $view->makeWritableCommands = Piwik::getAutoUpdateMakeWritableMessage();
        echo $view->render();
    }

    public function oneClickUpdate()
    {
        Piwik::checkUserIsSuperUser();
        $this->newVersion = $this->checkNewVersionIsAvailableOrDie();

        Piwik::setMaxExecutionTime(0);

        $url = self::getLatestZipUrl($this->newVersion);
        $steps = array(
            array('oneClick_Download', Piwik_Translate('CoreUpdater_DownloadingUpdateFromX', $url)),
            array('oneClick_Unpack', Piwik_Translate('CoreUpdater_UnpackingTheUpdate')),
            array('oneClick_Verify', Piwik_Translate('CoreUpdater_VerifyingUnpackedFiles')),
            array('oneClick_CreateConfigFileBackup', Piwik_Translate('CoreUpdater_CreatingBackupOfConfigurationFile', self::CONFIG_FILE_BACKUP)),
            array('oneClick_Copy', Piwik_Translate('CoreUpdater_InstallingTheLatestVersion')),
            array('oneClick_Finished', Piwik_Translate('CoreUpdater_PiwikUpdatedSuccessfully')),
        );

        $errorMessage = false;
        $messages = array();
        foreach ($steps as $step) {
            try {
                $method = $step[0];
                $message = $step[1];
                $this->$method();
                $messages[] = $message;
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                break;
            }
        }

        // this is a magic template to trigger the Piwik_View_Update
        $view = Piwik_View::factory(Piwik_View::COREUPDATER_ONE_CLICK_DONE);
        $view->coreError = $errorMessage;
        $view->feedbackMessages = $messages;
        echo $view->render();
    }

    public function oneClickResults()
    {
        Piwik_API_Request::reloadAuthUsingTokenAuth($_POST);
        Piwik::checkUserIsSuperUser();

        $view = Piwik_View::factory('update_one_click_results');
        $view->coreError = Piwik_Common::getRequestVar('error', '', 'string', $_POST);
        $view->feedbackMessages = safe_unserialize(Piwik_Common::unsanitizeInputValue(Piwik_Common::getRequestVar('messages', '', 'string', $_POST)));
        echo $view->render();
    }

    private function checkNewVersionIsAvailableOrDie()
    {
        $newVersion = Piwik_UpdateCheck::isNewestVersionAvailable();
        if (!$newVersion) {
            throw new Exception(Piwik_TranslateException('CoreUpdater_ExceptionAlreadyLatestVersion', Piwik_Version::VERSION));
        }
        return $newVersion;
    }

    private function oneClick_Download()
    {
        $this->pathPiwikZip = PIWIK_USER_PATH . self::PATH_TO_EXTRACT_LATEST_VERSION . 'latest.zip';
        Piwik::checkDirectoriesWritableOrDie(array(self::PATH_TO_EXTRACT_LATEST_VERSION));

        // we catch exceptions in the caller (i.e., oneClickUpdate)
        $url = self::getLatestZipUrl($this->newVersion) . '?cb=' . $this->newVersion;
        $fetched = Piwik_Http::fetchRemoteFile($url, $this->pathPiwikZip);
    }

    private function oneClick_Unpack()
    {
        $pathExtracted = PIWIK_USER_PATH . self::PATH_TO_EXTRACT_LATEST_VERSION;
        $this->pathRootExtractedPiwik = $pathExtracted . 'piwik';

        if (file_exists($this->pathRootExtractedPiwik)) {
            Piwik::unlinkRecursive($this->pathRootExtractedPiwik, true);
        }

        $archive = Piwik_Unzip::factory('PclZip', $this->pathPiwikZip);

        if (0 == ($archive_files = $archive->extract($pathExtracted))) {
            throw new Exception(Piwik_TranslateException('CoreUpdater_ExceptionArchiveIncompatible', $archive->errorInfo()));
        }

        if (0 == count($archive_files)) {
            throw new Exception(Piwik_TranslateException('CoreUpdater_ExceptionArchiveEmpty'));
        }
        unlink($this->pathPiwikZip);
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
        foreach ($someExpectedFiles as $file) {
            if (!is_file($this->pathRootExtractedPiwik . $file)) {
                throw new Exception(Piwik_TranslateException('CoreUpdater_ExceptionArchiveIncomplete', $file));
            }
        }
    }

    private function oneClick_CreateConfigFileBackup()
    {
        $configFileBefore = PIWIK_USER_PATH . '/config/global.ini.php';
        $configFileAfter = PIWIK_USER_PATH . self::CONFIG_FILE_BACKUP;
        Piwik::copy($configFileBefore, $configFileAfter);
    }

    private function oneClick_Copy()
    {
        /*
         * Make sure the execute bit is set for this shell script
         */
        if (!Piwik_ArchiveProcessing::isBrowserTriggerArchivingEnabled()) {
            @chmod($this->pathRootExtractedPiwik . '/misc/cron/archive.sh', 0755);
        }

        /*
         * Copy all files to PIWIK_INCLUDE_PATH.
         * These files are accessed through the dispatcher.
         */
        Piwik::copyRecursive($this->pathRootExtractedPiwik, PIWIK_INCLUDE_PATH);

        /*
         * These files are visible in the web root and are generally
         * served directly by the web server.  May be shared.
         */
        if (PIWIK_INCLUDE_PATH !== PIWIK_DOCUMENT_ROOT) {
            /*
             * Copy PHP files that expect to be in the document root
             */
            $specialCases = array(
                '/index.php',
                '/piwik.php',
                '/js/index.php',
            );

            foreach ($specialCases as $file) {
                Piwik::copy($this->pathRootExtractedPiwik . $file, PIWIK_DOCUMENT_ROOT . $file);
            }

            /*
             * Copy the non-PHP files (e.g., images, css, javascript)
             */
            Piwik::copyRecursive($this->pathRootExtractedPiwik, PIWIK_DOCUMENT_ROOT, true);
        }

        /*
         * Config files may be user (account) specific
         */
        if (PIWIK_INCLUDE_PATH !== PIWIK_USER_PATH) {
            Piwik::copyRecursive($this->pathRootExtractedPiwik . '/config', PIWIK_USER_PATH . '/config');
        }

        Piwik::unlinkRecursive($this->pathRootExtractedPiwik, true);

        if (function_exists('apc_clear_cache')) {
            apc_clear_cache(); // clear the system (aka 'opcode') cache
        }
    }

    private function oneClick_Finished()
    {
    }

    public function index()
    {
        $language = Piwik_Common::getRequestVar('language', '');
        if (!empty($language)) {
            Piwik_LanguagesManager::setLanguageForSession($language);
        }
        $this->runUpdaterAndExit();
    }

    protected function runUpdaterAndExit()
    {
        $updater = new Piwik_Updater();
        $componentsWithUpdateFile = Piwik_CoreUpdater::getComponentUpdates($updater);
        if (empty($componentsWithUpdateFile)) {
            Piwik::redirectToModule('CoreHome');
        }

        Piwik::setMaxExecutionTime(0);

        $sqlQueries = $updater->getSqlQueriesToExecute();
        if (Piwik_Common::isPhpCliMode()) {
            $view = Piwik_View::factory('update_welcome');
            $this->doWelcomeUpdates($view, $componentsWithUpdateFile);
            echo $view->render();

            if (!$this->coreError
                && Piwik::getModule() == 'CoreUpdater'
            ) {
                $view = Piwik_View::factory('update_database_done');
                $this->doExecuteUpdates($view, $updater, $componentsWithUpdateFile);
                echo $view->render();
            }
        } else if (Piwik_Common::getRequestVar('updateCorePlugins', 0, 'integer') == 1) {
            $this->warningMessages = array();
            $view = Piwik_View::factory('update_database_done');
            $this->doExecuteUpdates($view, $updater, $componentsWithUpdateFile);

            if (count($sqlQueries) == 1 && !$this->coreError) {
                Piwik::redirectToModule('CoreHome');
            }

            echo $view->render();
        } else {
            $view = Piwik_View::factory('update_welcome');
            $view->queries = $sqlQueries;
            $view->isMajor = $updater->hasMajorDbUpdate();
            $this->doWelcomeUpdates($view, $componentsWithUpdateFile);
            echo $view->render();
        }
        exit;
    }

    private function doWelcomeUpdates($view, $componentsWithUpdateFile)
    {
        $view->new_piwik_version = Piwik_Version::VERSION;
        $view->commandUpgradePiwik = "<br /><code>php " . Piwik_Common::getPathToPiwikRoot() . "/index.php  -- \"module=CoreUpdater\" </code>";
        $pluginNamesToUpdate = array();
        $coreToUpdate = false;

        // handle case of existing database with no tables
        if (!Piwik::isInstalled()) {
            $this->errorMessages[] = Piwik_Translate('CoreUpdater_EmptyDatabaseError', Piwik_Config::getInstance()->database['dbname']);
            $this->coreError = true;
            $currentVersion = 'N/A';
        } else {
            $this->errorMessages = array();
            try {
                $currentVersion = Piwik_GetOption('version_core');
            } catch (Exception $e) {
                $currentVersion = '<= 0.2.9';
            }

            foreach ($componentsWithUpdateFile as $name => $filenames) {
                if ($name == 'core') {
                    $coreToUpdate = true;
                } else {
                    $pluginNamesToUpdate[] = $name;
                }
            }
        }

        // check file integrity
        $integrityInfo = Piwik::getFileIntegrityInformation();
        if (isset($integrityInfo[1])) {
            if ($integrityInfo[0] == false) {
                $this->warningMessages[] = '<b>' . Piwik_Translate('General_FileIntegrityWarningExplanation') . '</b>';
            }
            $this->warningMessages = array_merge($this->warningMessages, array_slice($integrityInfo, 1));
        }
        Piwik::deleteAllCacheOnUpdate();

        $view->coreError = $this->coreError;
        $view->warningMessages = $this->warningMessages;
        $view->errorMessages = $this->errorMessages;
        $view->current_piwik_version = $currentVersion;
        $view->pluginNamesToUpdate = $pluginNamesToUpdate;
        $view->coreToUpdate = $coreToUpdate;
    }

    private function doExecuteUpdates($view, $updater, $componentsWithUpdateFile)
    {
        $this->loadAndExecuteUpdateFiles($updater, $componentsWithUpdateFile);

        Piwik::deleteAllCacheOnUpdate();

        $view->coreError = $this->coreError;
        $view->warningMessages = $this->warningMessages;
        $view->errorMessages = $this->errorMessages;
        $view->deactivatedPlugins = $this->deactivatedPlugins;
    }

    private function loadAndExecuteUpdateFiles($updater, $componentsWithUpdateFile)
    {
        // if error in any core update, show message + help message + EXIT
        // if errors in any plugins updates, show them on screen, disable plugins that errored + CONTINUE
        // if warning in any core update or in any plugins update, show message + CONTINUE
        // if no error or warning, success message + CONTINUE
        foreach ($componentsWithUpdateFile as $name => $filenames) {
            try {
                $this->warningMessages = array_merge($this->warningMessages, $updater->update($name));
            } catch (Piwik_Updater_UpdateErrorException $e) {
                $this->errorMessages[] = $e->getMessage();
                if ($name == 'core') {
                    $this->coreError = true;
                    break;
                } else {
                    Piwik_PluginsManager::getInstance()->deactivatePlugin($name);
                    $this->deactivatedPlugins[] = $name;
                }
            }
        }
    }
}
