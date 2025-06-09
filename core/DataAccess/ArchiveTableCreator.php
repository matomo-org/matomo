<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Date;

class ArchiveTableCreator
{
    public const NUMERIC_TABLE = "numeric";
    public const BLOB_TABLE    = "blob";

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

    public static function refreshTableList()
    {
        self::$tablesAlreadyInstalled = self::getModel()->getInstalledArchiveTables();
    }

    /**
     * Returns all table names archive_*
     *
     * @param string $type The type of table to return. Either `self::NUMERIC_TABLE` or `self::BLOB_TABLE`.
     * @param bool   $forceReload
     * @return array
     */
    public static function getTablesArchivesInstalled($type = null, $forceReload = false)
    {
        if (
            is_null(self::$tablesAlreadyInstalled)
            || $forceReload
        ) {
            self::refreshTableList();
        }

        if (empty($type)) {
            return self::$tablesAlreadyInstalled;
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

    /**
     * Returns the latest table name archive_*.
     * If no type is specified, blob table is returned when both blob and numeric are found for the same year_month.
     *
     * @param string|null $type The type of archive table to return. Either `self::NUMERIC_TABLE` or `self::BLOB_TABLE`.
     * @param bool   $forceReload
     * @return string|null
     */
    public static function getLatestArchiveTableInstalled(?string $type = null, bool $forceReload = false): ?string
    {
        $archiveTables = static::getTablesArchivesInstalled($type, $forceReload);

        // skip if there is no archive table (yet)
        if (0 === count($archiveTables)) {
            return null;
        }

        // sort tables so we have them in descending order of their date
        usort($archiveTables, function ($a, $b) {
            return static::getDateFromTableName($b) <=> static::getDateFromTableName($a);
        });

        return $archiveTables[0];
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
