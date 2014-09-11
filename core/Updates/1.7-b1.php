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
class Updates_1_7_b1 extends Updates
{
    static function getSql()
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('pdf') . '`
		    	ADD COLUMN `aggregate_reports_format` TINYINT(1) NOT NULL AFTER `reports`'                => 1060,
            'UPDATE `' . Common::prefixTable('pdf') . '`
		    	SET `aggregate_reports_format` = 1' => false,
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
