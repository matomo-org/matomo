<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Piwik;
use Piwik\Plugin\Dependency as PluginDependency;
use Piwik\SettingsPiwik;
use Piwik\Unzip;

/**
 *
 */
class PluginInstaller
{
    const PATH_TO_DOWNLOAD = '/tmp/latest/plugins/';
    const PATH_TO_EXTRACT = '/plugins/';

    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    public function installOrUpdatePluginFromMarketplace()
    {
        $tmpPluginZip = PIWIK_USER_PATH . self::PATH_TO_DOWNLOAD . $this->pluginName . '.zip';
        $tmpPluginFolder = PIWIK_USER_PATH . self::PATH_TO_DOWNLOAD . $this->pluginName;

        $tmpPluginZip = SettingsPiwik::rewriteTmpPathWithInstanceId($tmpPluginZip);
        $tmpPluginFolder = SettingsPiwik::rewriteTmpPathWithInstanceId($tmpPluginFolder);

        try {
            $this->makeSureFoldersAreWritable();
            $this->makeSurePluginNameIsValid();
            $this->downloadPluginFromMarketplace($tmpPluginZip);
            $this->extractPluginFiles($tmpPluginZip, $tmpPluginFolder);
            $this->makeSurePluginJsonExists($tmpPluginFolder);
            $metadata = $this->getPluginMetadataIfValid($tmpPluginFolder);
            $this->makeSureThereAreNoMissingRequirements($metadata);
            $this->copyPluginToDestination($tmpPluginFolder);

            Filesystem::deleteAllCacheOnUpdate($this->pluginName);

        } catch (\Exception $e) {

            $this->removeFileIfExists($tmpPluginZip);
            $this->removeFolderIfExists($tmpPluginFolder);

            throw $e;
        }

        $this->removeFileIfExists($tmpPluginZip);
        $this->removeFolderIfExists($tmpPluginFolder);
    }

    public function installOrUpdatePluginFromFile($pathToZip)
    {
        $tmpPluginFolder = PIWIK_USER_PATH . self::PATH_TO_DOWNLOAD . $this->pluginName;
        $tmpPluginFolder = SettingsPiwik::rewriteTmpPathWithInstanceId($tmpPluginFolder);

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
        Filechecks::dieIfDirectoriesNotWritable(array(self::PATH_TO_DOWNLOAD, self::PATH_TO_EXTRACT));
    }

    private function downloadPluginFromMarketplace($pluginZipTargetFile)
    {
        $this->removeFileIfExists($pluginZipTargetFile);

        $marketplace = new MarketplaceApiClient();

        try {
            $marketplace->download($this->pluginName, $pluginZipTargetFile);
        } catch (\Exception $e) {

            try {
                $downloadUrl = $marketplace->getDownloadUrl($this->pluginName);
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
        $missingDependencies = $dependency->getMissingDependencies($requires);

        if (!empty($missingDependencies)) {
            $message = '';
            foreach ($missingDependencies as $dep) {
                $params   = array(ucfirst($dep['requirement']), $dep['actualVersion'], $dep['requiredVersion']);
                $message .= Piwik::translate('CorePluginsAdmin_MissingRequirementsNotice', $params);
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
        $pluginTargetPath = PIWIK_USER_PATH . self::PATH_TO_EXTRACT . $this->pluginName;

        $this->removeFolderIfExists($pluginTargetPath);

        Filesystem::copyRecursive($tmpPluginFolder, PIWIK_USER_PATH . self::PATH_TO_EXTRACT);
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
        if (file_exists($targetTmpFile)) {
            unlink($targetTmpFile);
        }
    }

    /**
     * @throws PluginInstallerException
     */
    private function makeSurePluginNameIsValid()
    {
        try {
            $marketplace = new MarketplaceApiClient();
            $pluginDetails = $marketplace->getPluginInfo($this->pluginName);
        } catch (\Exception $e) {
            throw new PluginInstallerException($e->getMessage());
        }

        if (empty($pluginDetails)) {
            throw new PluginInstallerException('This plugin was not found in the Marketplace.');
        }
    }

}
