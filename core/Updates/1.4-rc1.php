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
class Updates_1_4_rc1 extends Updates
{
    static function getSql()
    {
        return array(
            'UPDATE `' . Common::prefixTable('pdf') . '`
		    	SET format = "pdf"'              => '42S22',
            'ALTER TABLE `' . Common::prefixTable('pdf') . '`
		    	ADD COLUMN `format` VARCHAR(10)' => '42S22',
        );
    }

    static function update()
    {
        try {
            Updater::updateDatabase(__FILE__, self::getSql());
        } catch (\Exception $e) {
        }
    }
}
