<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugin\Manager;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_15_0_b16 extends Updates
{

    public function doUpdate(Updater $updater)
    {
        $this->uninstallPlugin('LeftMenu');
        $this->uninstallPlugin('ZenMode');
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
