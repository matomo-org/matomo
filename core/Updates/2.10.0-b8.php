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

class Updates_2_10_0_b8 extends Updates
{
    public static function update()
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('Resolution');
        } catch (\Exception $e) {
        }
    }
}
