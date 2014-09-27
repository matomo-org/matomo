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
class Updates_2_8_0_b1 extends Updates
{
    static function update()
    {
        try {
            $browserEngineMatch = array(
                'Trident' => array('IE'),
                'Gecko'   => array('NS', 'PX', 'FF', 'FB', 'CA', 'GA', 'KM', 'MO', 'SM', 'CO', 'FE', 'KP', 'KZ', 'TB'),
                'KHTML'   => array('KO'),
                'WebKit'  => array('SF', 'CH', 'OW', 'AR', 'EP', 'FL', 'WO', 'AB', 'IR', 'CS', 'FD', 'HA', 'MI', 'GE', 'DF', 'BB', 'BP', 'TI', 'CF', 'RK', 'B2', 'NF'),
                'Presto'  => array('OP'),
            );

            foreach ($browserEngineMatch AS $engine => $browsers) {

                // Update visits, fill in now missing engine
                $updateSql = sprintf("UPDATE %s SET `config_browser_engine` = '%s' WHERE `config_browser_name` IN ('%s')", Common::prefixTable('log_visit'), $engine, implode("','", $browsers));
                echo $updateSql;
                Updater::executeMigrationQuery($updateSql, false, __FILE__);
            }

            exit;

            $archiveBlobTables = Db::get()->fetchCol("SHOW TABLES LIKE '%archive_blob%'");

            // for each blob archive table, rename UserSettings_* to DevicesDetection_* and reachive some data
            foreach ($archiveBlobTables as $table) {

                // try to rename old archives
                $updateSql = sprintf("UPDATE IGNORE %s SET name='DevicesDetection_browserEngines' WHERE name = 'UserSettings_browserType'", $table);
                Updater::executeMigrationQuery($updateSql, false, __FILE__);

                // remove remaining archives, that couldn't be renamed
                $deleteSql = sprintf("DELETE FROM %s WHERE name = 'UserSettings_browserType'", $table);
                Updater::executeMigrationQuery($deleteSql, false, __FILE__);


            }

        } catch (\Exception $e) {
        }
        exit;
    }
}
