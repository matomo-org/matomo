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
    static function getSql()
    {
        return array(
            'ALTER TABLE ' . Common::prefixTable('site') . " DROP `feedburnerName`" => 1091
        );
    }

    static function update()
    {
        // manually remove ExampleFeedburner column
        Updater::updateDatabase(__FILE__, self::getSql());

        // remove ExampleFeedburner plugin
        $pluginToDelete = 'ExampleFeedburner';
        self::deletePluginFromConfigFile($pluginToDelete);
    }
}
