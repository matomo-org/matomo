<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;

class Updates_2_16_0_rc2 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();
        $pluginName = 'PiwikPro';

        try {
            if (!$pluginManager->isPluginActivated($pluginName)) {
                $pluginManager->activatePlugin($pluginName);
            }
        } catch (\Exception $e) {
        }
    }
}