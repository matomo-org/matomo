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
 * Update for version 2.10.0-b4.
 */
class Updates_2_10_0_b4 extends Updates
{

    public static function update()
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('BulkTracking');
        } catch (\Exception $e) {
        }
    }
}
