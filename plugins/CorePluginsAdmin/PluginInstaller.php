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

        $this->makeSureFoldersAreWritable();
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
        $success = $marketplace->download($this->pluginName, $pluginZipTargetFile);

        if (!$success) {
            throw new \Exception('Failed to download plugin');
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
            throw new \Exception(Piwik_TranslateException('Plugin_ExceptionArchiveIncompatible', $archive->errorInfo()));
        }

        if (0 == count($pluginFiles)) {
            throw new \Exception(Piwik_TranslateException('Plugin Zip File Is Empty'));
        }
    }

    private function makeSurePluginJsonExists($tmpPluginFolder)
    {
        if (!file_exists($tmpPluginFolder . '/plugin.json')) {
            throw new \Exception('It is not a valid Plugin, missing plugin.json');
        }
    }

    private function copyPluginToDestination($tmpPluginFolder)
    {
        $pluginTargetPath = PIWIK_USER_PATH . self::PATH_TO_EXTRACT . $this->pluginName;

        $this->removeFolderIfExists($pluginTargetPath);
        Filesystem::copyRecursive($tmpPluginFolder, $pluginTargetPath);
    }

    /**
     * @param $pathExtracted
     */
    private function removeFolderIfExists($pathExtracted)
    {
        if (file_exists($pathExtracted)) {
            Filesystem::unlinkRecursive($pathExtracted, true);
        }
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

}
