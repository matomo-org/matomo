<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugin\Manager;
use Piwik\Sequence;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_9_0_b1 extends Updates
{
    static function getSql()
    {
        $sql = self::getSqlsForMigratingUserSettingsToDevicesDetection();
        $sql = self::addQueryToCreateSequenceTable($sql);
        $sql = self::addArchivingIdMigrationQueries($sql);

        return $sql;
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

        try {
            Manager::getInstance()->activatePlugin('TestRunner');
        } catch (\Exception $e) {

        }
    }

    private static function addArchivingIdMigrationQueries($sql)
    {
        $tables = ArchiveTableCreator::getTablesArchivesInstalled();

        foreach ($tables as $table) {
            $type = ArchiveTableCreator::getTypeFromTableName($table);

            if ($type === ArchiveTableCreator::NUMERIC_TABLE) {
                $maxId = Db::fetchOne('SELECT MAX(idarchive) FROM ' . $table);

                if (!empty($maxId)) {
                    $maxId = (int) $maxId + 500;
                } else {
                    $maxId = 1;
                }

                $sequence = new Sequence($table);
                $query = $sequence->getQueryToCreateSequence($maxId);
                $sql[$query] = false;
            }
        }

        return $sql;
    }

    /**
     * @return string
     */
    private static function addQueryToCreateSequenceTable($sql)
    {
        $dbSettings = new Db\Settings();
        $engine  = $dbSettings->getEngine();
        $table   = Common::prefixTable('sequence');

        $query = "CREATE TABLE `$table` (
                `name` VARCHAR(120) NOT NULL,
                `value` BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY(`name`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8";

        $sql[$query] = 1050;

        return $sql;
    }

    private static function getSqlsForMigratingUserSettingsToDevicesDetection()
    {
        $sql = array();

        $sql = self::updateBrowserEngine($sql);

        return $sql;
    }

    static function update()
    {
        Updater::updateDatabase(__FILE__, self::getSql());

        self::updateIPAnonymizationSettings();

        try {
            Manager::getInstance()->activatePlugin('TestRunner');
        } catch (\Exception $e) {

        }
    }

    private static function updateBrowserEngine($sql)
    {
        $sql[sprintf("ALTER TABLE `%s` ADD COLUMN `config_browser_engine` VARCHAR(10) NOT NULL", Common::prefixTable('log_visit'))] = 1060;

        $browserEngineMatch = array(
            'Trident' => array('IE'),
            'Gecko' => array('NS', 'PX', 'FF', 'FB', 'CA', 'GA', 'KM', 'MO', 'SM', 'CO', 'FE', 'KP', 'KZ', 'TB'),
            'KHTML' => array('KO'),
            'WebKit' => array('SF', 'CH', 'OW', 'AR', 'EP', 'FL', 'WO', 'AB', 'IR', 'CS', 'FD', 'HA', 'MI', 'GE', 'DF', 'BB', 'BP', 'TI', 'CF', 'RK', 'B2', 'NF'),
            'Presto' => array('OP'),
        );

        // Update visits, fill in now missing engine
        $engineUpdate = "''";
        $ifFragment = "IF (`config_browser_name` IN ('%s'), '%s', %s)";

        foreach ($browserEngineMatch AS $engine => $browsers) {

            $engineUpdate = sprintf($ifFragment, implode("','", $browsers), $engine, $engineUpdate);
        }

        $engineUpdate = sprintf("UPDATE %s SET `config_browser_engine` = %s", Common::prefixTable('log_visit'), $engineUpdate);
        $sql[$engineUpdate] = false;

        $archiveBlobTables = Db::get()->fetchCol("SHOW TABLES LIKE '%archive_blob%'");

        // for each blob archive table, rename UserSettings_browserType to DevicesDetection_browserEngines
        foreach ($archiveBlobTables as $table) {

            // try to rename old archives
            $sql[sprintf("UPDATE IGNORE %s SET name='DevicesDetection_browserEngines' WHERE name = 'UserSettings_browserType'", $table)] = false;
        }

        return $sql;
    }

    private static function updateIPAnonymizationSettings()
    {
        $optionName = 'PrivacyManager.ipAnonymizerEnabled';

        $value = Option::get($optionName);

        if ($value !== false) {
            // If the config is defined, nothing to do
            return;
        }

        // We disable IP anonymization if it wasn't configured (because by default it has gone from disabled to enabled)
        Option::set($optionName, '0');
    }
}
