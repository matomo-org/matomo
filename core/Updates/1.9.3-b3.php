<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updates;

/**
 */
class Updates_1_9_3_b3 extends Updates
{
    static function update()
    {
        // Insight was a temporary code name for Overlay
        $pluginToDelete = 'Insight';
        self::deletePluginFromConfigFile($pluginToDelete);
        \Piwik\Plugin\Manager::getInstance()->deletePluginFromFilesystem($pluginToDelete);

        // We also clean up 1.9.1 and delete Feedburner plugin
        \Piwik\Plugin\Manager::getInstance()->deletePluginFromFilesystem('Feedburner');
    }
}
