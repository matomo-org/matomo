<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\DbHelper;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin;
use Piwik\Plugins\CorePluginsAdmin\Marketplace;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Unzip;
use Piwik\UpdateCheck;
use Piwik\Updater;
use Piwik\Version;
use Piwik\View\OneClickDone;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{
    const CONFIG_FILE_BACKUP = '/config/global.ini.auto-backup-before-update.php';
    const PATH_TO_EXTRACT_LATEST_VERSION = '/tmp/latest/';

    private $coreError = false;
    private $warningMessages = array();
    private $errorMessages = array();
    private $deactivatedPlugins = array();
    private $pathPiwikZip = false;
    private $newVersion;

    protected static function getLatestZipUrl($newVersion)
    {
        if (@Config::getInstance()->Debug['allow_upgrades_to_beta']) {
            return 'http://builds.piwik.org/piwik-' . $newVersion . '.zip';
        }
        return Config::getInstance()->General['latest_version_url'];
    }

    public function newVersionAvailable()
    {
        Piwik::checkUserHasSuperUserAccess();

        $newVersion = $this->checkNewVersionIsAvailableOrDie();

        $view = new View('@CoreUpdater/newVersionAvailable');
        $this->addCustomLogoInfo($view);

        $view->piwik_version = Version::VERSION;
        $view->piwik_new_version = $newVersion;

        $incompatiblePlugins = $this->getIncompatiblePlugins($newVersion);

        $marketplacePlugins = array();
        try {
            if (!empty($incompatiblePlugins)) {
                $marketplace = new Marketplace();
                $marketplacePlugins = $marketplace->getAllAvailablePluginNames();
            }
        } catch (\Exception $e) {}

        $view->marketplacePlugins = $marketplacePlugins;
        $view->incompatiblePlugins = $incompatiblePlugins;
        $view->piwik_latest_version_url = self::getLatestZipUrl($newVersion);
        $view->can_auto_update  = Filechecks::canAutoUpdate();
        $view->makeWritableCommands = Filechecks::getAutoUpdateMakeWritableMessage();

        return $view->render();
    }

    public function oneClickUpdate()
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->newVersion = $this->checkNewVersionIsAvailableOrDie();

        SettingsServer::setMaxExecutionTime(0);

        $url = self::getLatestZipUrl($this->newVersion);
        $steps = array(
            array('oneClick_Download', Piwik::translate('CoreUpdater_DownloadingUpdateFromX', $url)),
            array('oneClick_Unpack', Piwik::translate('CoreUpdater_UnpackingTheUpdate')),
            array('oneClick_Verify', Piwik::translate('CoreUpdater_VerifyingUnpackedFiles')),
            array('oneClick_CreateConfigFileBackup', Piwik::translate('CoreUpdater_CreatingBackupOfConfigurationFile', self::CONFIG_FILE_BACKUP))
        );
        $incompatiblePlugins = $this->getIncompatiblePlugins($this->newVersion);
        if (!empty($incompatiblePlugins)) {
            $namesToDisable = array();
            foreach ($incompatiblePlugins as $incompatiblePlugin) {
                $namesToDisable[] = $incompatiblePlugin->getPluginName();
            }
            $steps[] = array('oneClick_DisableIncompatiblePlugins', Piwik::translate('CoreUpdater_DisablingIncompatiblePlugins', implode(', ', $namesToDisable)));
        }

        $steps[] = array('oneClick_Copy', Piwik::translate('CoreUpdater_InstallingTheLatestVersion'));
        $steps[] = array('oneClick_Finished', Piwik::translate('CoreUpdater_PiwikUpdatedSuccessfully'));

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

        $view = new OneClickDone(Piwik::getCurrentUserTokenAuth());
        $view->coreError = $errorMessage;
        $view->feedbackMessages = $messages;

        $this->addCustomLogoInfo($view);

        return $view->render();
    }

    public function oneClickResults()
    {
        $view = new View('@CoreUpdater/oneClickResults');
        $view->coreError = Common::getRequestVar('error', '', 'string', $_POST);
        $view->feedbackMessages = safe_unserialize(Common::unsanitizeInputValue(Common::getRequestVar('messages', '', 'string', $_POST)));
        $this->addCustomLogoInfo($view);
        return $view->render();
    }

    protected function redirectToDashboardWhenNoError($updater)
    {
        if (count($updater->getSqlQueriesToExecute()) == 1
            && !$this->coreError
            && empty($this->warningMessages)
            && empty($this->errorMessages)
            && empty($this->deactivatedPlugins)
        ) {
            Piwik::redirectToModule('CoreHome');
        }
    }

    private function checkNewVersionIsAvailableOrDie()
    {
        $newVersion = UpdateCheck::isNewestVersionAvailable();
        if (!$newVersion) {
            throw new Exception(Piwik::translate('CoreUpdater_ExceptionAlreadyLatestVersion', Version::VERSION));
        }
        return $newVersion;
    }

    private function oneClick_Download()
    {
        $pathPiwikZip = PIWIK_USER_PATH . self::PATH_TO_EXTRACT_LATEST_VERSION . 'latest.zip';
        $this->pathPiwikZip = SettingsPiwik::rewriteTmpPathWithInstanceId($pathPiwikZip);

        Filechecks::dieIfDirectoriesNotWritable(array(self::PATH_TO_EXTRACT_LATEST_VERSION));

        // we catch exceptions in the caller (i.e., oneClickUpdate)
        $url = self::getLatestZipUrl($this->newVersion) . '?cb=' . $this->newVersion;

        Http::fetchRemoteFile($url, $this->pathPiwikZip);
    }

    private function oneClick_Unpack()
    {
        $pathExtracted = PIWIK_USER_PATH . self::PATH_TO_EXTRACT_LATEST_VERSION;
        $pathExtracted = SettingsPiwik::rewriteTmpPathWithInstanceId($pathExtracted);

        $this->pathRootExtractedPiwik = $pathExtracted . 'piwik';

        if (file_exists($this->pathRootExtractedPiwik)) {
            Filesystem::unlinkRecursive($this->pathRootExtractedPiwik, true);
        }

        $archive = Unzip::factory('PclZip', $this->pathPiwikZip);

        if (0 == ($archive_files = $archive->extract($pathExtracted))) {
            throw new Exception(Piwik::translate('CoreUpdater_ExceptionArchiveIncompatible', $archive->errorInfo()));
        }

        if (0 == count($archive_files)) {
            throw new Exception(Piwik::translate('CoreUpdater_ExceptionArchiveEmpty'));
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
                throw new Exception(Piwik::translate('CoreUpdater_ExceptionArchiveIncomplete', $file));
            }
        }
    }

    private function oneClick_CreateConfigFileBackup()
    {
        $configFileBefore = PIWIK_USER_PATH . '/config/global.ini.php';
        $configFileAfter = PIWIK_USER_PATH . self::CONFIG_FILE_BACKUP;
        Filesystem::copy($configFileBefore, $configFileAfter);
    }

    private function oneClick_DisableIncompatiblePlugins()
    {
        $plugins = $this->getIncompatiblePlugins($this->newVersion);

        foreach ($plugins as $plugin) {
            PluginManager::getInstance()->deactivatePlugin($plugin->getPluginName());
        }
    }

    private function oneClick_Copy()
    {
        /*
         * Make sure the execute bit is set for this shell script
         */
        if (!Rules::isBrowserTriggerEnabled()) {
            @chmod($this->pathRootExtractedPiwik . '/misc/cron/archive.sh', 0755);
        }

        $model = new Model();

        /*
         * Copy all files to PIWIK_INCLUDE_PATH.
         * These files are accessed through the dispatcher.
         */
        Filesystem::copyRecursive($this->pathRootExtractedPiwik, PIWIK_INCLUDE_PATH);
        $model->removeGoneFiles($this->pathRootExtractedPiwik, PIWIK_INCLUDE_PATH);

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
                Filesystem::copy($this->pathRootExtractedPiwik . $file, PIWIK_DOCUMENT_ROOT . $file);
            }

            /*
             * Copy the non-PHP files (e.g., images, css, javascript)
             */
            Filesystem::copyRecursive($this->pathRootExtractedPiwik, PIWIK_DOCUMENT_ROOT, true);
            $model->removeGoneFiles($this->pathRootExtractedPiwik, PIWIK_DOCUMENT_ROOT);
        }

        /*
         * Config files may be user (account) specific
         */
        if (PIWIK_INCLUDE_PATH !== PIWIK_USER_PATH) {
            Filesystem::copyRecursive($this->pathRootExtractedPiwik . '/config', PIWIK_USER_PATH . '/config');
        }

        Filesystem::unlinkRecursive($this->pathRootExtractedPiwik, true);

        Filesystem::clearPhpCaches();
    }

    private function oneClick_Finished()
    {
    }

    public function index()
    {
        $language = Common::getRequestVar('language', '');
        if (!empty($language)) {
            LanguagesManager::setLanguageForSession($language);
        }

        try {
            return $this->runUpdaterAndExit();
        } catch(NoUpdatesFoundException $e) {
            Piwik::redirectToModule('CoreHome');
        }
    }

    public function runUpdaterAndExit($doDryRun = null)
    {
        $updater = new Updater();
        $componentsWithUpdateFile = CoreUpdater::getComponentUpdates($updater);
        if (empty($componentsWithUpdateFile)) {
            throw new NoUpdatesFoundException("Everything is already up to date.");
        }

        SettingsServer::setMaxExecutionTime(0);

        $cli = Common::isPhpCliMode() ? '_cli' : '';
        $welcomeTemplate = '@CoreUpdater/runUpdaterAndExit_welcome' . $cli;
        $doneTemplate = '@CoreUpdater/runUpdaterAndExit_done' . $cli;

        $viewWelcome = new View($welcomeTemplate);
        $this->addCustomLogoInfo($viewWelcome);

        $viewDone = new View($doneTemplate);
        $this->addCustomLogoInfo($viewDone);

        $doExecuteUpdates = Common::getRequestVar('updateCorePlugins', 0, 'integer') == 1;

        if(is_null($doDryRun)) {
            $doDryRun = !$doExecuteUpdates;
        }

        if($doDryRun) {
            $viewWelcome->queries = $updater->getSqlQueriesToExecute();
            $viewWelcome->isMajor = $updater->hasMajorDbUpdate();
            $this->doWelcomeUpdates($viewWelcome, $componentsWithUpdateFile);
            return $viewWelcome->render();
        }

        // CLI
        if (Common::isPhpCliMode()) {
            $this->doWelcomeUpdates($viewWelcome, $componentsWithUpdateFile);
            $output = $viewWelcome->render();

            // Proceed with upgrade in CLI only if user specifically asked for it, or if running console command
            $isUpdateRequested = Common::isRunningConsoleCommand() || Piwik::getModule() == 'CoreUpdater';

            if (!$this->coreError && $isUpdateRequested) {
                $this->doExecuteUpdates($viewDone, $updater, $componentsWithUpdateFile);
                $output .= $viewDone->render();
            }
            return $output;
        }

        // Web
        if ($doExecuteUpdates) {
            $this->warningMessages = array();
            $this->doExecuteUpdates($viewDone, $updater, $componentsWithUpdateFile);

            $this->redirectToDashboardWhenNoError($updater);

            return $viewDone->render();
        }

        exit;
    }

    private function doWelcomeUpdates($view, $componentsWithUpdateFile)
    {
        $view->new_piwik_version = Version::VERSION;
        $view->commandUpgradePiwik = "<br /><code>php " . Filesystem::getPathToPiwikRoot() . "/console core:update </code>";
        $pluginNamesToUpdate = array();
        $dimensionsToUpdate = array();
        $coreToUpdate = false;

        // handle case of existing database with no tables
        if (!DbHelper::isInstalled()) {
            $this->errorMessages[] = Piwik::translate('CoreUpdater_EmptyDatabaseError', Config::getInstance()->database['dbname']);
            $this->coreError = true;
            $currentVersion = 'N/A';
        } else {
            $this->errorMessages = array();
            try {
                $currentVersion = Option::get('version_core');
            } catch (Exception $e) {
                $currentVersion = '<= 0.2.9';
            }

            foreach ($componentsWithUpdateFile as $name => $filenames) {
                if ($name == 'core') {
                    $coreToUpdate = true;
                } elseif (0 === strpos($name, 'log_')) {
                    $dimensionsToUpdate[] = $name;
                } else {
                    $pluginNamesToUpdate[] = $name;
                }
            }
        }

        // check file integrity
        $integrityInfo = Filechecks::getFileIntegrityInformation();
        if (isset($integrityInfo[1])) {
            if ($integrityInfo[0] == false) {
                $this->warningMessages[] = Piwik::translate('General_FileIntegrityWarningExplanation');
            }
            $this->warningMessages = array_merge($this->warningMessages, array_slice($integrityInfo, 1));
        }
        Filesystem::deleteAllCacheOnUpdate();

        $view->coreError = $this->coreError;
        $view->warningMessages = $this->warningMessages;
        $view->errorMessages = $this->errorMessages;
        $view->current_piwik_version = $currentVersion;
        $view->pluginNamesToUpdate = $pluginNamesToUpdate;
        $view->dimensionsToUpdate = $dimensionsToUpdate;
        $view->coreToUpdate = $coreToUpdate;
    }

    private function doExecuteUpdates($view, $updater, $componentsWithUpdateFile)
    {
        $result = CoreUpdater::updateComponents($updater, $componentsWithUpdateFile);

        $this->coreError       = $result['coreError'];
        $this->warningMessages = $result['warnings'];
        $this->errorMessages   = $result['errors'];
        $this->deactivatedPlugins = $result['deactivatedPlugins'];
        $view->coreError = $this->coreError;
        $view->warningMessages = $this->warningMessages;
        $view->errorMessages = $this->errorMessages;
        $view->deactivatedPlugins = $this->deactivatedPlugins;
    }

    private function getIncompatiblePlugins($piwikVersion)
    {
        return PluginManager::getInstance()->getIncompatiblePlugins($piwikVersion);
    }

}
