<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater;

use Piwik\Filesystem;

class Model
{
    public function getPluginsFromDirectoy($directoryToLook)
    {
        $directories = _glob($directoryToLook . '/plugins/' . '*', GLOB_ONLYDIR);

        $directories = array_map(function ($directory) use ($directoryToLook) {
            return str_replace($directoryToLook, '', $directory);
        }, $directories);

        return $directories;
    }

    public function removeGoneFiles($source, $target)
    {
        Filesystem::unlinkTargetFilesNotPresentInSource($source . '/core', $target . '/core');

        foreach ($this->getPluginsFromDirectoy($source) as $pluginDir) {
            Filesystem::unlinkTargetFilesNotPresentInSource($source . $pluginDir, $target . $pluginDir);
        }
    }
}
