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

class Updates_2_4_0_b3 extends Updates
{
    public function doUpdate(Updater $updater)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('LeftMenu');
        } catch (\Exception $e) {
        }

        try {
            $pluginManager->deactivatePlugin('Zeitgeist');
        } catch (\Exception $e) {
        }

        try {
            $pluginManager->uninstallPlugin('Zeitgeist');
        } catch (\Exception $e) {
        }
    }
}
