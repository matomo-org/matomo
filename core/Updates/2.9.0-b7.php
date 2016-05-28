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
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_9_0_b7 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        $sql = array();
        $sql = self::addCreateSequenceTableQuery($sql);
        $sql = self::addArchivingIdMigrationQueries($sql);

        return $sql;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
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

                $query = self::getQueryToCreateSequence($table, $maxId);
                // refs  #6696, ignores  Integrity constraint violation: 1062 Duplicate entry 'piwik_archive_numeric_2010_01' for key 'PRIMARY'
                $sql[$query] = '1062';
            }
        }

        return $sql;
    }

    private static function getQueryToCreateSequence($name, $initialValue)
    {
        $table = self::getSequenceTableName();
        $query = sprintf("INSERT INTO %s (name, value) VALUES ('%s', %d)", $table, $name, $initialValue);

        return $query;
    }

    /**
     * @return string
     */
    private static function addCreateSequenceTableQuery($sql)
    {
        $dbSettings = new Db\Settings();
        $engine = $dbSettings->getEngine();
        $table  = self::getSequenceTableName();

        $query = "CREATE TABLE `$table` (
                `name` VARCHAR(120) NOT NULL,
                `value` BIGINT(20) UNSIGNED NOT NULL,
                PRIMARY KEY(`name`)
        ) ENGINE=$engine DEFAULT CHARSET=utf8";

        $sql[$query] = 1050;

        return $sql;
    }

    private static function getSequenceTableName()
    {
        return Common::prefixTable('sequence');
    }
}
