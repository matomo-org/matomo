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
use Piwik\Updater;

/**
 */
class Updates_1_9_3_b3 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        // Insight was a temporary code name for Overlay
        $pluginToDelete = 'Insight';
        self::deletePluginFromConfigFile($pluginToDelete);
        \Piwik\Plugin\Manager::getInstance()->deletePluginFromFilesystem($pluginToDelete);

        // We also clean up 1.9.1 and delete Feedburner plugin
        \Piwik\Plugin\Manager::getInstance()->deletePluginFromFilesystem('Feedburner');
    }
}
