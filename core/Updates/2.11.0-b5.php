<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Plugin\Manager;
use Piwik\Updates;

class Updates_2_11_0_b5 extends Updates
{
    public static function update()
    {
        try {
            Manager::getInstance()->activatePlugin('Monolog');
        } catch (\Exception $e) {
        }
    }
}
