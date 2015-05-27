<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Plugin\Manager;

class Updates_2_14_0_b1 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $this->uninstallPlugin('UserSettings');
    }

    private function uninstallPlugin($plugin)
    {
        $pluginManager = Manager::getInstance();

        if ($pluginManager->isPluginInstalled($plugin)) {
            if ($pluginManager->isPluginActivated($plugin)) {
                $pluginManager->deactivatePlugin($plugin);
            }

            $pluginManager->unloadPlugin($plugin);
            $pluginManager->uninstallPlugin($plugin);
        } else {
            $this->makeSurePluginIsRemovedFromFilesystem($plugin);
        }
    }

    private function makeSurePluginIsRemovedFromFilesystem($plugin)
    {
        Manager::deletePluginFromFilesystem($plugin);
    }
}
