<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_12_b1 extends Updates
{
    static function isMajorUpdate()
    {
        return true;
    }

    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('log_link_visit_action') . '`
			 ADD `custom_float` FLOAT NULL DEFAULT NULL' => 1060
        );
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }

}
