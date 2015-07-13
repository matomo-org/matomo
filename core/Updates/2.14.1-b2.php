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
 * Update for version 2.14.0.
 */
class Updates_2_14_1_b2 extends Updates
{
    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @return array
     */
    static function getSql()
    {
        $updateSql = array(
            'ALTER TABLE `' . Common::prefixTable('site') . '` ADD COLUMN `exclude_unknown_urls` TINYINT(1) DEFAULT 0 AFTER `currency`' => array(1060)
        );
        return $updateSql;
    }

    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
