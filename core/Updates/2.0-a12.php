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
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_2_0_a12 extends Updates
{
    public static function getSql()
    {
        $result = array(
            'ALTER TABLE ' . Common::prefixTable('logger_message') . ' MODIFY level VARCHAR(16) NULL' => false
        );

        $unneededLogTables = array('logger_exception', 'logger_error', 'logger_api_call');
        foreach ($unneededLogTables as $table) {
            $tableName = Common::prefixTable($table);

            try {
                $rows = Db::fetchOne("SELECT COUNT(*) FROM $tableName");
                if ($rows == 0) {
                    $result["DROP TABLE $tableName"] = false;
                }
            } catch (\Exception $ex) {
                // ignore
            }
        }

        return $result;
    }

    public static function update()
    {
        // change level column in logger_message table to string & remove other logging tables if empty
        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
