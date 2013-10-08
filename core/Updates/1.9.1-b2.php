<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Updates
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_1_9_1_b2 extends Updates
{
    static function getSql($schema = 'Myisam')
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
