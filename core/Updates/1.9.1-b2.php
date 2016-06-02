<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_9_1_b2 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE ' . Common::prefixTable('site') . " DROP `feedburnerName`" => 1091
        );
    }

    public function doUpdate(Updater $updater)
    {
        // manually remove ExampleFeedburner column
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));

        // remove ExampleFeedburner plugin
        $pluginToDelete = 'ExampleFeedburner';
        self::deletePluginFromConfigFile($pluginToDelete);
    }
}
