<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CorePluginsAdmin
 */
namespace Piwik\Plugins\CorePluginsAdmin;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\SettingsPiwik;
use Piwik\Unzip;

/**
 *
 * @package CorePluginsAdmin
 */
class PluginInstaller
{
    const PATH_TO_DOWNLOAD = '/tmp/latest/plugins/';
    const PATH_TO_EXTRACT  = '/plugins/';

    private $pluginName;

    public function __construct($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    public function installOrUpdatePluginFromMarketplace()
    {
        $tmpPluginZip    = PIWIK_USER_PATH . self::PATH_TO_DOWNLOAD . $this->pluginName . '.zip';
        $tmpPluginFolder = PIWIK_USER_PATH . self::PATH_TO_DOWNLOAD . $this->pluginName;

        $tmpPluginZip = SettingsPiwik::rewriteTmpPathWithHostname($tmpPluginZip);
        $tmpPluginFolder = SettingsPiwik::rewriteTmpPathWithHostname($tmpPluginFolder);

        $this->makeSureFoldersAreWritable();
        $this->makeSurePluginNameIsValid();
        $this->downloadPluginFromMarketplace($tmpPluginZip);
        $this->extractPluginFiles($tmpPluginZip, $tmpPluginFolder);
        $this->makeSurePluginJsonExists($tmpPluginFolder);
        $this->copyPluginToDestination($tmpPluginFolder);

        $this->removeFileIfExists($tmpPluginZip);
        $this->removeFolderIfExists($tmpPluginFolder);
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
                $downloadUrl  = $marketplace->getDownloadUrl($this->pluginName);
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
            throw new PluginInstallerException(Piwik_TranslateException('Plugin_ExceptionArchiveIncompatible', $archive->errorInfo()));
        }

        if (0 == count($pluginFiles)) {
            throw new PluginInstallerException(Piwik_TranslateException('Plugin Zip File Is Empty'));
        }
    }

    private function makeSurePluginJsonExists($tmpPluginFolder)
    {
        if (!file_exists($tmpPluginFolder . DIRECTORY_SEPARATOR . $this->pluginName . DIRECTORY_SEPARATOR . 'plugin.json')) {
            throw new PluginInstallerException('Plugin is not valid, it is missing the plugin.json file.');
        }
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
            $marketplace   = new MarketplaceApiClient();
            $pluginDetails = $marketplace->getPluginInfo($this->pluginName);
        } catch (\Exception $e) {
            throw new PluginInstallerException($e->getMessage());
        }

        if (empty($pluginDetails)) {
            throw new PluginInstallerException('This plugin was not found in the Marketplace.');
        }
    }

}
