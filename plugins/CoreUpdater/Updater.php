<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Container\StaticContainer;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\ReleaseChannels;
use Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin;
use Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient;
use Piwik\Plugins\CorePluginsAdmin\MarketplaceApiException;
use Piwik\Plugins\CorePluginsAdmin\PluginInstaller;
use Piwik\SettingsServer;
use Piwik\Translation\Translator;
use Piwik\Unzip;
use Piwik\Version;

class Updater
{
    const OPTION_LATEST_VERSION = 'UpdateCheck_LatestVersion';
    const PATH_TO_EXTRACT_LATEST_VERSION = '/latest/';
    const DOWNLOAD_TIMEOUT = 720;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var ReleaseChannels
     */
    private $releaseChannels;

    /**
     * @var string
     */
    private $tmpPath;

    public function __construct(Translator $translator, ReleaseChannels $releaseChannels, $tmpPath)
    {
        $this->translator = $translator;
        $this->releaseChannels = $releaseChannels;
        $this->tmpPath = $tmpPath;
    }

    /**
     * Returns the latest available version number. Does not perform a check whether a later version is available.
     *
     * @return false|string
     */
    public function getLatestVersion()
    {
        return Option::get(self::OPTION_LATEST_VERSION);
    }

    /**
     * @return bool
     */
    public function isNewVersionAvailable()
    {
        $latestVersion = self::getLatestVersion();
        return $latestVersion && version_compare(Version::VERSION, $latestVersion) === -1;
    }

    /**
     * @return bool
     */
    public function isUpdatingOverHttps()
    {
        $openSslEnabled = extension_loaded('openssl');
        $usingMethodSupportingHttps = (Http::getTransportMethod() !== 'socket');

        return $openSslEnabled && $usingMethodSupportingHttps;
    }

    /**
     * Update Piwik codebase by downloading and installing the latest version.
     *
     * @param bool $https Whether to use HTTPS if supported of not. If false, will use HTTP.
     * @return string[] Return an array of messages for the user.
     * @throws ArchiveDownloadException
     * @throws UpdaterException
     * @throws Exception
     */
    public function updatePiwik($https = true)
    {
        if (!$this->isNewVersionAvailable()) {
            throw new Exception($this->translator->translate('CoreUpdater_ExceptionAlreadyLatestVersion', Version::VERSION));
        }

        SettingsServer::setMaxExecutionTime(0);

        $newVersion = $this->getLatestVersion();
        $url = $this->getArchiveUrl($newVersion, $https);
        $messages = array();

        try {
            $archiveFile = $this->downloadArchive($newVersion, $url);
            $messages[] = $this->translator->translate('CoreUpdater_DownloadingUpdateFromX', $url);

            $extractedArchiveDirectory = $this->decompressArchive($archiveFile);
            $messages[] = $this->translator->translate('CoreUpdater_UnpackingTheUpdate');

            $this->verifyDecompressedArchive($extractedArchiveDirectory);
            $messages[] = $this->translator->translate('CoreUpdater_VerifyingUnpackedFiles');

            // we need to load the marketplace already here, otherwise it will use the new, updated file in Piwik 3
            $marketplace = new MarketplaceApiClient();
            require_once PIWIK_DOCUMENT_ROOT . '/plugins/CorePluginsAdmin/PluginInstaller.php';
            require_once PIWIK_DOCUMENT_ROOT . '/plugins/CorePluginsAdmin/MarketplaceApiException.php';

            $this->installNewFiles($extractedArchiveDirectory);
            $messages[] = $this->translator->translate('CoreUpdater_InstallingTheLatestVersion');

        } catch (ArchiveDownloadException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new UpdaterException($e, $messages);
        }

        try {
            if (CorePluginsAdmin::isMarketplaceEnabled()) {
                $messages[] = $this->translator->translate('CoreUpdater_CheckingForPluginUpdates');

                $pluginManager = PluginManager::getInstance();
                $pluginManager->loadAllPluginsAndGetTheirInfo();
                $loadedPlugins = $pluginManager->getLoadedPlugins();

                MarketplaceApiClient::clearAllCacheEntries();
                StaticContainer::getContainer()->set('marketplacePiwikVersion', $newVersion);

                $pluginsWithUpdate = $marketplace->checkUpdates($loadedPlugins);

                foreach ($pluginsWithUpdate as $pluginWithUpdate) {
                    $pluginName = $pluginWithUpdate['name'];

                    $messages[] = $this->translator->translate('CoreUpdater_UpdatingPluginXToVersionY',
                                                               array($pluginName, $pluginWithUpdate['version']));

                    $pluginInstaller = new PluginInstaller($pluginName);
                    $pluginInstaller->installOrUpdatePluginFromMarketplace();
                }
            }
        } catch (MarketplaceApiException $e) {
            // there is a problem with the connection to the server, ignore for now
        } catch (Exception $e) {
            throw new UpdaterException($e, $messages);
        }

        try {
            $disabledPluginNames = $this->disableIncompatiblePlugins($newVersion);
            if (!empty($disabledPluginNames)) {
                $messages[] = $this->translator->translate('CoreUpdater_DisablingIncompatiblePlugins', implode(', ', $disabledPluginNames));
            }
        } catch (Exception $e) {
            throw new UpdaterException($e, $messages);
        }

        return $messages;
    }

    private function downloadArchive($version, $url)
    {
        $path = $this->tmpPath . self::PATH_TO_EXTRACT_LATEST_VERSION;
        $archiveFile = $path . 'latest.zip';

        Filechecks::dieIfDirectoriesNotWritable(array($path));

        $url .= '?cb=' . $version;

        try {
            Http::fetchRemoteFile($url, $archiveFile, 0, self::DOWNLOAD_TIMEOUT);
        } catch (Exception $e) {
            // We throw a specific exception allowing to offer HTTP download if HTTPS failed
            throw new ArchiveDownloadException($e);
        }

        return $archiveFile;
    }

    private function decompressArchive($archiveFile)
    {
        $extractionPath = $this->tmpPath . self::PATH_TO_EXTRACT_LATEST_VERSION;

        $extractedArchiveDirectory = $extractionPath . 'piwik';

        // Remove previous decompressed archive
        if (file_exists($extractedArchiveDirectory)) {
            Filesystem::unlinkRecursive($extractedArchiveDirectory, true);
        }

        $archive = Unzip::factory('PclZip', $archiveFile);
        $archiveFiles = $archive->extract($extractionPath);

        if (0 == $archiveFiles) {
            throw new Exception($this->translator->translate('CoreUpdater_ExceptionArchiveIncompatible', $archive->errorInfo()));
        }

        if (0 == count($archiveFiles)) {
            throw new Exception($this->translator->translate('CoreUpdater_ExceptionArchiveEmpty'));
        }

        unlink($archiveFile);

        return $extractedArchiveDirectory;
    }

    private function verifyDecompressedArchive($extractedArchiveDirectory)
    {
        $someExpectedFiles = array(
            '/config/global.ini.php',
            '/index.php',
            '/core/Piwik.php',
            '/piwik.php',
            '/plugins/API/API.php'
        );
        foreach ($someExpectedFiles as $file) {
            if (!is_file($extractedArchiveDirectory . $file)) {
                throw new Exception($this->translator->translate('CoreUpdater_ExceptionArchiveIncomplete', $file));
            }
        }
    }

    private function disableIncompatiblePlugins($version)
    {
        $incompatiblePlugins = $this->getIncompatiblePlugins($version);
        $disabledPluginNames = array();

        foreach ($incompatiblePlugins as $plugin) {
            $name = $plugin->getPluginName();
            PluginManager::getInstance()->deactivatePlugin($name);
            $disabledPluginNames[] = $name;
        }

        return $disabledPluginNames;
    }

    private function installNewFiles($extractedArchiveDirectory)
    {
        // Make sure the execute bit is set for this shell script
        if (!Rules::isBrowserTriggerEnabled()) {
            @chmod($extractedArchiveDirectory . '/misc/cron/archive.sh', 0755);
        }

        $model = new Model();

        /*
         * Copy all files to PIWIK_INCLUDE_PATH.
         * These files are accessed through the dispatcher.
         */
        Filesystem::copyRecursive($extractedArchiveDirectory, PIWIK_INCLUDE_PATH);
        $model->removeGoneFiles($extractedArchiveDirectory, PIWIK_INCLUDE_PATH);

        /*
         * These files are visible in the web root and are generally
         * served directly by the web server.  May be shared.
         */
        if (PIWIK_INCLUDE_PATH !== PIWIK_DOCUMENT_ROOT) {
            // Copy PHP files that expect to be in the document root
            $specialCases = array(
                '/index.php',
                '/piwik.php',
                '/js/index.php',
            );

            foreach ($specialCases as $file) {
                Filesystem::copy($extractedArchiveDirectory . $file, PIWIK_DOCUMENT_ROOT . $file);
            }

            // Copy the non-PHP files (e.g., images, css, javascript)
            Filesystem::copyRecursive($extractedArchiveDirectory, PIWIK_DOCUMENT_ROOT, true);
            $model->removeGoneFiles($extractedArchiveDirectory, PIWIK_DOCUMENT_ROOT);
        }

        // Config files may be user (account) specific
        if (PIWIK_INCLUDE_PATH !== PIWIK_USER_PATH) {
            Filesystem::copyRecursive($extractedArchiveDirectory . '/config', PIWIK_USER_PATH . '/config');
        }

        Filesystem::unlinkRecursive($extractedArchiveDirectory, true);

        Filesystem::clearPhpCaches();
    }

    /**
     * @param string $version
     * @param bool $https Whether to use HTTPS if supported of not. If false, will use HTTP.
     * @return string
     */
    public function getArchiveUrl($version, $https = true)
    {
        $channel = $this->releaseChannels->getActiveReleaseChannel();
        $url = $channel->getDownloadUrlWithoutScheme($version);

        if ($this->isUpdatingOverHttps() && $https) {
            $url = 'https' . $url;
        } else {
            $url = 'http' . $url;
        }

        return $url;
    }

    private function getIncompatiblePlugins($piwikVersion)
    {
        return PluginManager::getInstance()->getIncompatiblePlugins($piwikVersion);
    }
}
