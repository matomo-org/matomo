<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

class Piwik_DataAccess_ArchiveTableCreator
{
    const NUMERIC_TABLE = "numeric";

    const BLOB_TABLE = "blob";

    static public $tablesAlreadyInstalled = null;

    static public function getNumericTable(Piwik_Date $date)
    {
        return self::getTable($date, self::NUMERIC_TABLE);
    }

    static public function getBlobTable(Piwik_Date $date)
    {
        return self::getTable($date, self::BLOB_TABLE);
    }

    static protected function getTable(Piwik_Date $date, $type)
    {
        $tableNamePrefix = "archive_" . $type;
        $tableName = $tableNamePrefix . "_" . $date->toString('Y_m');
        $tableName = Piwik_Common::prefixTable($tableName);
        self::createArchiveTablesIfAbsent($tableName, $tableNamePrefix);
        return $tableName;
    }

    static protected function createArchiveTablesIfAbsent($tableName, $tableNamePrefix)
    {
        if (is_null(self::$tablesAlreadyInstalled)) {
            self::refreshTableList();
        }

        if (!in_array($tableName, self::$tablesAlreadyInstalled)) {
            $db = Zend_Registry::get('db');
            $sql = Piwik::getTableCreateSql($tableNamePrefix);

            // replace table name template by real name
            $tableNamePrefix = Piwik_Common::prefixTable($tableNamePrefix);
            $sql = str_replace($tableNamePrefix, $tableName, $sql);
            try {
                $db->query($sql);
            } catch (Exception $e) {
                // accept mysql error 1050: table already exists, throw otherwise
                if (!$db->isErrNo($e, '1050')) {
                    throw $e;
                }
            }
            self::$tablesAlreadyInstalled[] = $tableName;
        }
    }

    static public function clear()
    {
        self::$tablesAlreadyInstalled = null;
    }
    static public function refreshTableList($forceReload = false)
    {
        self::$tablesAlreadyInstalled = Piwik::getTablesInstalled($forceReload);
    }
}