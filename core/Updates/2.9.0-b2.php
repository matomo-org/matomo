<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Plugin\Manager;
use Piwik\Updates;

/**
 */
class Updates_2_9_0_b2 extends Updates
{
    static function getSql()
    {
        return array();
    }

    static function update()
    {
        try {
            Manager::getInstance()->activatePlugin('TestRunner');
        } catch (\Exception $e) {

        }
    }
}
