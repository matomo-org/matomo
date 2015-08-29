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
class Updates_2_7_0_b4 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('Contents');
        } catch (\Exception $e) {
        }
    }

    public static function isMajorUpdate()
    {
        return true;
    }
}
