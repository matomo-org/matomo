<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugin;
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 2.15.0-b20.
 */
class Updates_2_15_0_b20 extends Updates
{

    /**
     * Perform the incremental version update.
     *
     * This method should preform all updating logic. If you define queries in an overridden `getMigrationQueries()`
     * method, you must call {@link Updater::executeMigrationQueries()} here.
     *
     * See {@link Updates} for an example.
     *
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $this->makeSurePluginIsRemovedFromFilesystem('ZenMode');
        $this->makeSurePluginIsRemovedFromFilesystem('LeftMenu');
    }

    private function makeSurePluginIsRemovedFromFilesystem($plugin)
    {
        Plugin\Manager::deletePluginFromFilesystem($plugin);
    }
}
