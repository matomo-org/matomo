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
class Updates_1_5_rc6 extends Updates
{
    static function update()
    {
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('PrivacyManager');
        } catch (\Exception $e) {
        }
    }
}

