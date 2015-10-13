<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Date;
use Piwik\DbHelper;

class ArchiveTableCreator
{
    const NUMERIC_TABLE = "numeric";
    const BLOB_TABLE    = "blob";

    public static $tablesAlreadyInstalled = null;

    public static function getNumericTable(Date $date)
    {
        return self::getTable($date, self::NUMERIC_TABLE);
    }

    public static function getBlobTable(Date $date)
    {
        return self::getTable($date, self::BLOB_TABLE);
    }

    protected static function getTable(Date $date, $type)
    {
        $tableNamePrefix = "archive_" . $type;
        $tableName = $tableNamePrefix . "_" . self::getTableMonthFromDate($date);
        $tableName = Common::prefixTable($tableName);

        self::createArchiveTablesIfAbsent($tableName, $tableNamePrefix);

        return $tableName;
    }

    protected static function createArchiveTablesIfAbsent($tableName, $tableNamePrefix)
    {
        if (is_null(self::$tablesAlreadyInstalled)) {
            self::refreshTableList();
        }

        if (!in_array($tableName, self::$tablesAlreadyInstalled)) {
            self::getModel()->createArchiveTable($tableName, $tableNamePrefix);
            self::$tablesAlreadyInstalled[] = $tableName;
        }
    }

    private static function getModel()
    {
        return new Model();
    }

    public static function clear()
    {
        self::$tablesAlreadyInstalled = null;
    }

    public static function refreshTableList($forceReload = false)
    {
        self::$tablesAlreadyInstalled = DbHelper::getTablesInstalled($forceReload);
    }

    /**
     * Returns all table names archive_*
     *
     * @param string $type The type of table to return. Either `self::NUMERIC_TABLE` or `self::BLOB_TABLE`.
     * @return array
     */
    public static function getTablesArchivesInstalled($type = null)
    {
        if (is_null(self::$tablesAlreadyInstalled)) {
            self::refreshTableList();
        }

        if (empty($type)) {
            $tableMatchRegex = '/archive_(numeric|blob)_/';
        } else {
            $tableMatchRegex = '/archive_' . preg_quote($type) . '_/';
        }

        $archiveTables = array();
        foreach (self::$tablesAlreadyInstalled as $table) {
            if (preg_match($tableMatchRegex, $table)) {
                $archiveTables[] = $table;
            }
        }
        return $archiveTables;
    }

    public static function getDateFromTableName($tableName)
    {
        $tableName = Common::unprefixTable($tableName);
        $date      = str_replace(array('archive_numeric_', 'archive_blob_'), '', $tableName);

        return $date;
    }

    public static function getTableMonthFromDate(Date $date)
    {
        return $date->toString('Y_m');
    }

    public static function getTypeFromTableName($tableName)
    {
        if (strpos($tableName, 'archive_numeric_') !== false) {
            return self::NUMERIC_TABLE;
        }

        if (strpos($tableName, 'archive_blob_') !== false) {
            return self::BLOB_TABLE;
        }

        return false;
    }
}
