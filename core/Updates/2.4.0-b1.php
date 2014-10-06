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

class Updates_2_4_0_b1 extends Updates
{
    public static function update()
    {
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('Morpheus');
        } catch(\Exception $e) {
        }

        try {
            \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('Zeitgeist');
            self::deletePluginFromConfigFile('Zeitgeist');
        } catch(\Exception $e) {
        }
    }
}
