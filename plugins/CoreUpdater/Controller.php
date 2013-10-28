<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreUpdater
 */
namespace Piwik\Plugins\CoreUpdater;

use Exception;
use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\DbHelper;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Unzip;
use Piwik\UpdateCheck;
use Piwik\Updater;
use Piwik\UpdaterErrorException;
use Piwik\Version;
use Piwik\View;
use Piwik\View\OneClickDone;

/**
 *
 * @package CoreUpdater
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

    static protected function getLatestZipUrl($newVersion)
    {
        if (@Config::getInstance()->Debug['allow_upgrades_to_beta']) {
            return 'http://builds.piwik.org/piwik-' . $newVersion . '.zip';
        }
        return Config::getInstance()->General['latest_version_url'];
    }

    public function newVersionAvailable()
    {
        Piwik::checkUserIsSuperUser();

        $newVersion = $this->checkNewVersionIsAvailableOrDie();

        $view = new View('@CoreUpdater/newVersionAvailable');
        $view->piwik_version = Version::VERSION;
        $view->piwik_new_version = $newVersion;
        $view->piwik_latest_version_url = self::getLatestZipUrl($newVersion);
        $view->can_auto_update = Filechecks::canAutoUpdate();
        $view->makeWritableCommands = Filechecks::getAutoUpdateMakeWritableMessage();
        echo $view->render();
    }

    public function oneClickUpdate()
    {
        Piwik::checkUserIsSuperUser();
        $this->newVersion = $this->checkNewVersionIsAvailableOrDie();

        SettingsServer::setMaxExecutionTime(0);

        $url = self::getLatestZipUrl($this->newVersion);
        $steps = array(
            array('oneClick_Download', Piwik::translate('CoreUpdater_DownloadingUpdateFromX', $url)),
            array('oneClick_Unpack', Piwik::translate('CoreUpdater_UnpackingTheUpdate')),
            array('oneClick_Verify', Piwik::translate('CoreUpdater_VerifyingUnpackedFiles')),
            array('oneClick_CreateConfigFileBackup', Piwik::translate('CoreUpdater_CreatingBackupOfConfigurationFile', self::CONFIG_FILE_BACKUP)),
            array('oneClick_Copy', Piwik::translate('CoreUpdater_InstallingTheLatestVersion')),
            array('oneClick_Finished', Piwik::translate('CoreUpdater_PiwikUpdatedSuccessfully')),
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
        $view = new OneClickDone(Piwik::getCurrentUserTokenAuth());
        $view->coreError = $errorMessage;
        $view->feedbackMessages = $messages;
        echo $view->render();
    }

    public function oneClickResults()
    {
        Request::reloadAuthUsingTokenAuth($_POST);
        Piwik::checkUserIsSuperUser();

        $view = new View('@CoreUpdater/oneClickResults');
        $view->coreError = Common::getRequestVar('error', '', 'string', $_POST);
        $view->feedbackMessages = safe_unserialize(Common::unsanitizeInputValue(Common::getRequestVar('messages', '', 'string', $_POST)));
        echo $view->render();
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

    public static function clearPhpCaches()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache(); // clear the system (aka 'opcode') cache
        }

        if (function_exists('opcache_reset')) {
            opcache_reset(); // reset the opcode cache (php 5.5.0+)
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
        $this->pathPiwikZip = SettingsPiwik::rewriteTmpPathWithHostname($pathPiwikZip);

        Filechecks::dieIfDirectoriesNotWritable(array(self::PATH_TO_EXTRACT_LATEST_VERSION));

        // we catch exceptions in the caller (i.e., oneClickUpdate)
        $url = self::getLatestZipUrl($this->newVersion) . '?cb=' . $this->newVersion;

        Http::fetchRemoteFile($url, $this->pathPiwikZip);
    }

    private function oneClick_Unpack()
    {
        $pathExtracted = PIWIK_USER_PATH . self::PATH_TO_EXTRACT_LATEST_VERSION;
        $pathExtracted = SettingsPiwik::rewriteTmpPathWithHostname($pathExtracted);

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

    private function oneClick_Copy()
    {
        /*
         * Make sure the execute bit is set for this shell script
         */
        if (!Rules::isBrowserTriggerEnabled()) {
            @chmod($this->pathRootExtractedPiwik . '/misc/cron/archive.sh', 0755);
        }

        /*
         * Copy all files to PIWIK_INCLUDE_PATH.
         * These files are accessed through the dispatcher.
         */
        Filesystem::copyRecursive($this->pathRootExtractedPiwik, PIWIK_INCLUDE_PATH);

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
        }

        /*
         * Config files may be user (account) specific
         */
        if (PIWIK_INCLUDE_PATH !== PIWIK_USER_PATH) {
            Filesystem::copyRecursive($this->pathRootExtractedPiwik . '/config', PIWIK_USER_PATH . '/config');
        }

        Filesystem::unlinkRecursive($this->pathRootExtractedPiwik, true);

        self::clearPhpCaches();
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
        $this->runUpdaterAndExit();
    }

    protected function runUpdaterAndExit()
    {
        $updater = new Updater();
        $componentsWithUpdateFile = CoreUpdater::getComponentUpdates($updater);
        if (empty($componentsWithUpdateFile)) {
            Piwik::redirectToModule('CoreHome');
        }

        SettingsServer::setMaxExecutionTime(0);

        $cli = Common::isPhpCliMode() ? '_cli' : '';
        $welcomeTemplate = '@CoreUpdater/runUpdaterAndExit_welcome' . $cli;
        $doneTemplate = '@CoreUpdater/runUpdaterAndExit_done' . $cli;
        $viewWelcome = new View($welcomeTemplate);
        $viewDone = new View($doneTemplate);

        if (Common::isPhpCliMode()) {
            $this->doWelcomeUpdates($viewWelcome, $componentsWithUpdateFile);
            echo $viewWelcome->render();

            if (!$this->coreError && Piwik::getModule() == 'CoreUpdater') {
                $this->doExecuteUpdates($viewDone, $updater, $componentsWithUpdateFile);
                echo $viewDone->render();
            }
        } else {
            if (Common::getRequestVar('updateCorePlugins', 0, 'integer') == 1) {
                $this->warningMessages = array();
                $this->doExecuteUpdates($viewDone, $updater, $componentsWithUpdateFile);

                $this->redirectToDashboardWhenNoError($updater);

                echo $viewDone->render();
            } else {
                $viewWelcome->queries = $updater->getSqlQueriesToExecute();
                $viewWelcome->isMajor = $updater->hasMajorDbUpdate();
                $this->doWelcomeUpdates($viewWelcome, $componentsWithUpdateFile);
                echo $viewWelcome->render();
            }
        }
        exit;
    }

    private function doWelcomeUpdates($view, $componentsWithUpdateFile)
    {
        $view->new_piwik_version = Version::VERSION;
        $view->commandUpgradePiwik = "<br /><code>php " . Filesystem::getPathToPiwikRoot() . "/index.php  -- \"module=CoreUpdater\" </code>";
        $pluginNamesToUpdate = array();
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
        $view->coreToUpdate = $coreToUpdate;
    }

    private function doExecuteUpdates($view, $updater, $componentsWithUpdateFile)
    {
        $this->loadAndExecuteUpdateFiles($updater, $componentsWithUpdateFile);

        Filesystem::deleteAllCacheOnUpdate();

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
            } catch (UpdaterErrorException $e) {
                $this->errorMessages[] = $e->getMessage();
                if ($name == 'core') {
                    $this->coreError = true;
                    break;
                } else {
                    \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($name);
                    $this->deactivatedPlugins[] = $name;
                }
            }
        }
    }
}
