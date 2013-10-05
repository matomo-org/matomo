<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole;

use Piwik\Common;
use Piwik\Console\Command;
use Piwik\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class GeneratePluginBase extends Command
{
    /**
     * @param $pluginName
     * @return string
     */
    protected function getPluginPath($pluginName)
    {
        $pluginPath = PIWIK_INCLUDE_PATH . '/plugins/' . ucfirst($pluginName);
        return $pluginPath;
    }

    private function createFolderWithinPluginIfNotExists($pluginName, $folder)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        if (!file_exists($pluginName . $folder)) {
            Filesystem::mkdir($pluginPath . $folder, true);
        }
    }

    private function createFileWithinPluginIfNotExists($pluginName, $fileName, $content)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        if (!file_exists($pluginPath . $fileName)) {
            file_put_contents($pluginPath . $fileName, $content);
        }
    }

    /**
     * @param string $templateName  eg. 'controller' or 'api'
     * @param string $pluginName
     */
    protected function copyTemplateToPlugin($templateName, $pluginName)
    {
        $templateFolder = __DIR__ . '/templates/' . $templateName;

        $files = Filesystem::globr($templateFolder, '*');

        foreach ($files as $file) {
            $fileNamePlugin = str_replace($templateFolder, '', $file);

            if (is_dir($file)) {
                $this->createFolderWithinPluginIfNotExists($pluginName, $fileNamePlugin);
            } else {
                $template   = file_get_contents($file);
                $template = str_replace('PLUGINNAME', $pluginName, $template);
                $this->createFileWithinPluginIfNotExists($pluginName, $fileNamePlugin, $template);
            }

        }
    }

    protected function getPluginNamesHavingNotSpecificFile($filename)
    {
        $pluginDirs = \_glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);

        $pluginNames = array();
        foreach ($pluginDirs as $pluginDir) {
            if (!file_exists($pluginDir . '/' . $filename)) {
                $pluginNames[] = basename($pluginDir);
            }
        }

        return $pluginNames;
    }

}