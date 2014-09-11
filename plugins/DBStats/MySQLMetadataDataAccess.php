<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Exception;
use Piwik\Config;
use Piwik\Db;

/**
 * Data Access Object that serves MySQL stats.
 */
class MySQLMetadataDataAccess
{
    public function getDBStatus()
    {
        if (function_exists('mysql_connect')) {
            $configDb = Config::getInstance()->database;
            $link = mysql_connect($configDb['host'], $configDb['username'], $configDb['password']);
            $status = mysql_stat($link);
            mysql_close($link);
            $status = explode("  ", $status);
        } else {
            $fullStatus = Db::fetchAssoc('SHOW STATUS');
            if (empty($fullStatus)) {
                throw new Exception('Error, SHOW STATUS failed');
            }

            $status = array(
                'Uptime'                 => $fullStatus['Uptime']['Value'],
                'Threads'                => $fullStatus['Threads_running']['Value'],
                'Questions'              => $fullStatus['Questions']['Value'],
                'Slow queries'           => $fullStatus['Slow_queries']['Value'],
                'Flush tables'           => $fullStatus['Flush_commands']['Value'],
                'Open tables'            => $fullStatus['Open_tables']['Value'],
                'Opens'                  => 'unavailable', // not available via SHOW STATUS
                'Queries per second avg' => 'unavailable' // not available via SHOW STATUS
            );
        }

        return $status;
    }

    public function getTableStatus($tableName)
    {
        return Db::fetchRow("SHOW TABLE STATUS LIKE ?", array($tableName));
    }

    public function getAllTablesStatus()
    {
        return Db::fetchAll("SHOW TABLE STATUS");
    }

    public function getRowCountsByArchiveName($tableName, $extraCols)
    {
        // otherwise, create data table & cache it
        $sql = "SELECT name as 'label', COUNT(*) as 'row_count'$extraCols FROM $tableName GROUP BY name";
        return Db::fetchAll($sql);
    }

    public function getColumnsFromTable($tableName)
    {
        return Db::fetchAll("SHOW COLUMNS FROM " . $tableName);
    }
}