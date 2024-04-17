<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 2.15.0-b17.
 */
class Updates_2_15_0_b17 extends Updates
{
    /**
     * Perform the incremental version update.
     *
     * This method should perform all updating logic. If you define queries in an overridden `getMigrationQueries()`
     * method, you must call {@link Updater::executeMigrations()} here.
     *
     * See {@link Updates} for an example.
     *
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $this->removeDeprecatedDebugConfig('enable_measure_piwik_usage_in_idsite');
    }

    private function removeDeprecatedDebugConfig($name)
    {
        $config = Config::getInstance();
        $debug = $config->Debug;
        unset($debug[$name]);
        $config->Debug = $debug;
        $config->forceSave();
    }
}
