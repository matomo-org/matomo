<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugin\Dependency as PluginDependency;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\Environment;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Unzip;
use Piwik\Plugins\Marketplace\Api\Client;

/**
 *
 */
class PluginInstaller
{
    const PATH_TO_DOWNLOAD = '/latest/plugins/';

    private $pluginName;

    /**
     * Null if Marketplace Plugin is not installed
     * @var Client|null
     */
    private $marketplaceClient;

    /**
     * PluginInstaller constructor.
     * @param Client|null $client
     */
    public function __construct($client = null)
    {
        if (!empty($client)) {
            $this->marketplaceClient = $client;
        } elseif (Marketplace::isMarketplaceEnabled()) {
            // we load it manually as marketplace might not be loaded
            $this->marketplaceClient = StaticContainer::get('Piwik\Plugins\Marketplace\Api\Client');
        }
    }

    public function installOrUpdatePluginFromMarketplace($pluginName)
    {
        $this->checkMarketplaceIsEnabled();

        $this->pluginName = $pluginName;

        try {
            $this->makeSureFoldersAreWritable();
            $this->makeSurePluginNameIsValid();

            $tmpPluginZip = $this->downloadPluginFromMarketplace();
            $tmpPluginFolder = dirname($tmpPluginZip) . '/' . basename($tmpPluginZip, '.zip') .  '/';
            $this->extractPluginFiles($tmpPluginZip, $tmpPluginFolder);
            $this->makeSurePluginJsonExists($tmpPluginFolder);
            $metadata = $this->getPluginMetadataIfValid($tmpPluginFolder);
            $this->makeSureThereAreNoMissingRequirements($metadata);
            $this->copyPluginToDestination($tmpPluginFolder);

            Filesystem::deleteAllCacheOnUpdate($this->pluginName);

            $pluginManager = PluginManager::getInstance();
            if ($pluginManager->isPluginLoaded($this->pluginName)) {
                $plugin = PluginManager::getInstance()->getLoadedPlugin($this->pluginName);
                if (!empty($plugin)) {
                    $plugin->reloadPluginInformation();
                }
            }

        } catch (\Exception $e) {

            if (!empty($tmpPluginZip)) {
                Filesystem::deleteFileIfExists($tmpPluginZip);
            }
            if (!empty($tmpPluginFolder)) {
                $this->removeFolderIfExists($tmpPluginFolder);
            }

            throw $e;
        }

        $this->removeFileIfExists($tmpPluginZip);
        $this->removeFolderIfExists($tmpPluginFolder);
    }

    public function installOrUpdatePluginFromFile($pathToZip)
    {
        $tmpPluginName = 'uploaded' . Common::generateUniqId();
        $tmpPluginFolder = StaticContainer::get('path.tmp') . self::PATH_TO_DOWNLOAD . $tmpPluginName;

        try {
            $this->makeSureFoldersAreWritable();
            $this->extractPluginFiles($pathToZip, $tmpPluginFolder);

            $this->makeSurePluginJsonExists($tmpPluginFolder);
            $metadata = $this->getPluginMetadataIfValid($tmpPluginFolder);
            $this->makeSureThereAreNoMissingRequirements($metadata);

            $this->pluginName = $metadata->name;

            $this->fixPluginFolderIfNeeded($tmpPluginFolder);
            $this->copyPluginToDestination($tmpPluginFolder);

            Filesystem::deleteAllCacheOnUpdate($this->pluginName);

        } catch (\Exception $e) {

            $this->removeFileIfExists($pathToZip);
            $this->removeFolderIfExists($tmpPluginFolder);

            throw $e;
        }

        $this->removeFileIfExists($pathToZip);
        $this->removeFolderIfExists($tmpPluginFolder);

        return $metadata;
    }

    private function makeSureFoldersAreWritable()
    {
        $dirs = array(
            StaticContainer::get('path.tmp') . self::PATH_TO_DOWNLOAD,
            Manager::getPluginsDirectory()
        );
        // we do not require additional plugin directories to be writeable ({@link Manager::getPluginsDirectories()})
        // as we only upload to core plugins directory anyway
        Filechecks::dieIfDirectoriesNotWritable($dirs);
    }

    /**
     * @return false|string   false on failed download, or a path to the downloaded zip file
     * @throws PluginInstallerException
     */
    private function downloadPluginFromMarketplace()
    {
        try {
            return $this->marketplaceClient->download($this->pluginName);
        } catch (\Exception $e) {

            try {
                $downloadUrl = $this->marketplaceClient->getDownloadUrl($this->pluginName);
                $errorMessage = sprintf('Failed to download plugin from %s: %s', $downloadUrl, $e->getMessage());

            } catch (\Exception $ex) {
                $errorMessage = sprintf('Failed to download plugin: %s', $e->getMessage());
            }

            throw new PluginInstallerException($errorMessage);
        }
    }

    /**
     * @param $pluginZipFile
     * @param $pathExtracted
     * @throws \Exception
     */
    private function extractPluginFiles($pluginZipFile, $pathExtracted)
    {
        $archive = Unzip::factory('PclZip', $pluginZipFile);

        $this->removeFolderIfExists($pathExtracted);

        if (0 == ($pluginFiles = $archive->extract($pathExtracted))) {
            throw new PluginInstallerException(Piwik::translate('CoreUpdater_ExceptionArchiveIncompatible', $archive->errorInfo()));
        }

        if (0 == count($pluginFiles)) {
            throw new PluginInstallerException(Piwik::translate('Plugin Zip File Is Empty'));
        }
    }

    private function makeSurePluginJsonExists($tmpPluginFolder)
    {
        $pluginJsonPath = $this->getPathToPluginJson($tmpPluginFolder);

        if (!file_exists($pluginJsonPath)) {
            throw new PluginInstallerException('Plugin is not valid, it is missing the plugin.json file.');
        }
    }

    private function makeSureThereAreNoMissingRequirements($metadata)
    {
        $requires = array();
        if (!empty($metadata->require)) {
            $requires = (array) $metadata->require;
        }

        $dependency = new PluginDependency();
        $dependency->setEnvironment($this->getEnvironment());
        $missingDependencies = $dependency->getMissingDependencies($requires);

        if (!empty($missingDependencies)) {
            $message = '';
            foreach ($missingDependencies as $dep) {
                if (empty($dep['actualVersion'])) {
                    $params   = array(ucfirst($dep['requirement']), $dep['requiredVersion'], $metadata->name);
                    $message .= Piwik::translate('CorePluginsAdmin_MissingRequirementsPleaseInstallNotice', $params);
                } else {
                    $params   = array(ucfirst($dep['requirement']), $dep['actualVersion'], $dep['requiredVersion']);
                    $message .= Piwik::translate('CorePluginsAdmin_MissingRequirementsNotice', $params);
                }

            }

            throw new PluginInstallerException($message);
        }
    }

    private function getPluginMetadataIfValid($tmpPluginFolder)
    {
        $pluginJsonPath = $this->getPathToPluginJson($tmpPluginFolder);

        $metadata = file_get_contents($pluginJsonPath);
        $metadata = json_decode($metadata);

        if (empty($metadata)) {
            throw new PluginInstallerException('Plugin is not valid, plugin.json is empty or does not contain valid JSON.');
        }

        if (empty($metadata->name)) {
            throw new PluginInstallerException('Plugin is not valid, the plugin.json file does not specify the plugin name.');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $metadata->name)) {
            throw new PluginInstallerException('The plugin name specified in plugin.json contains illegal characters. ' .
                'Plugin name can only contain following characters: [a-zA-Z0-9-_].');
        }

        if (empty($metadata->version)) {
            throw new PluginInstallerException('Plugin is not valid, the plugin.json file does not specify the plugin version.');
        }

        if (empty($metadata->description)) {
            throw new PluginInstallerException('Plugin is not valid, the plugin.json file does not specify a description.');
        }

        return $metadata;
    }

    private function getPathToPluginJson($tmpPluginFolder)
    {
        $firstSubFolder = $this->getNameOfFirstSubfolder($tmpPluginFolder);
        $path = $tmpPluginFolder . DIRECTORY_SEPARATOR . $firstSubFolder . DIRECTORY_SEPARATOR . 'plugin.json';

        return $path;
    }

    /**
     * @param $pluginDir
     * @throws PluginInstallerException
     * @return string
     */
    private function getNameOfFirstSubfolder($pluginDir)
    {
        if (!($dir = opendir($pluginDir))) {
            return false;
        }
        $firstSubFolder = '';

        while ($file = readdir($dir)) {
            if ($file[0] != '.' && is_dir($pluginDir . DIRECTORY_SEPARATOR . $file)) {
                $firstSubFolder = $file;
                break;
            }
        }

        if (empty($firstSubFolder)) {
            throw new PluginInstallerException('The plugin ZIP file does not contain a subfolder, but Piwik expects plugin files to be within a subfolder in the Zip archive.');
        }

        return $firstSubFolder;
    }

    private function fixPluginFolderIfNeeded($tmpPluginFolder)
    {
        $firstSubFolder = $this->getNameOfFirstSubfolder($tmpPluginFolder);

        if ($firstSubFolder === $this->pluginName) {
            return;
        }

        $from = $tmpPluginFolder . DIRECTORY_SEPARATOR . $firstSubFolder;
        $to = $tmpPluginFolder . DIRECTORY_SEPARATOR . $this->pluginName;
        rename($from, $to);
    }

    private function copyPluginToDestination($tmpPluginFolder)
    {
        $pluginsDir = Manager::getPluginsDirectory();

        if (!empty($GLOBALS['MATOMO_PLUGIN_COPY_DIR'])) {
            $pluginsDir = $GLOBALS['MATOMO_PLUGIN_COPY_DIR'];
        }
        $pluginTargetPath = $pluginsDir . $this->pluginName;

        $this->removeFolderIfExists($pluginTargetPath);

        Filesystem::copyRecursive($tmpPluginFolder, $pluginsDir);
    }

    /**
     * @param $pathExtracted
     */
    private function removeFolderIfExists($pathExtracted)
    {
        Filesystem::unlinkRecursive($pathExtracted, true);
    }

    /**
     * @param $targetTmpFile
     */
    private function removeFileIfExists($targetTmpFile)
    {
        Filesystem::deleteFileIfExists($targetTmpFile);
    }

    /**
     * @throws PluginInstallerException
     */
    private function makeSurePluginNameIsValid()
    {
        try {
            $pluginDetails = $this->marketplaceClient->getPluginInfo($this->pluginName);
        } catch (\Exception $e) {
            throw new PluginInstallerException($e->getMessage());
        }

        if (empty($pluginDetails)) {
            throw new PluginInstallerException('This plugin was not found in the Marketplace.');
        }
    }

    private function checkMarketplaceIsEnabled()
    {
        if (!isset($this->marketplaceClient)) {
            throw new PluginInstallerException('Marketplace plugin needs to be enabled to perform this action.');
        }
    }

    private function getEnvironment()
    {
        if ($this->marketplaceClient) {
            return $this->marketplaceClient->getEnvironment();
        } else {
            return StaticContainer::get(Environment::class);
        }
    }

}
