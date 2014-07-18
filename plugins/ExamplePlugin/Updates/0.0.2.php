<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExamplePlugin;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 0.0.2.
 */
class Updates_0_0_2 extends Updates
{
    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @return array
     */
    static function getSql()
    {
        $errorCodesToIgnore = array(1060);
        $tableName = Common::prefixTable('log_visit');
        $updateSql = "ALTER TABLE `" . $tableName . "` CHANGE `example` `example` BOOLEAN NOT NULL";

        return array(
            // $updateSql => $errorCodesToIgnore
        );
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
