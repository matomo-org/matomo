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
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_0_2_33 extends Updates
{
    static function getSql()
    {
        $sqlarray = array(
            // 0.2.33 [1020]
            'ALTER TABLE `' . Common::prefixTable('user_dashboard') . '`
				CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci '                                                                      => '1146',
            'ALTER TABLE `' . Common::prefixTable('user_language') . '`
				CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ' => '1146',
        );

        // alter table to set the utf8 collation
        $tablesToAlter = DbHelper::getTablesInstalled(true);
        foreach ($tablesToAlter as $table) {
            $sqlarray['ALTER TABLE `' . $table . '`
				CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci '] = false;
        }

        return $sqlarray;
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
