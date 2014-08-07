<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Db\Adapter;
use Piwik\Db\AdapterInterface;
use Piwik\Tracker;

/**
 * Contains SQL related helper functions for Piwik's MySQL database.
 *
 * Plugins should always use this class to execute SQL against the database.
 *
 * ### Examples
 *
 *     $rows = Db::fetchAll("SELECT col1, col2 FROM mytable WHERE thing = ?", array('thingvalue'));
 *     foreach ($rows as $row) {
 *         doSomething($row['col1'], $row['col2']);
 *     }
 *
 *     $value = Db::fetchOne("SELECT MAX(col1) FROM mytable");
 *     doSomethingElse($value);
 *
 *     Db::query("DELETE FROM mytable WHERE id < ?", array(23));
 *
 * This class is a static proxy to \Piwik\Db\Db
 *
 * @deprecated Use \Piwik\Db\Db instead
 *
 * @api
 */
class Db
{
    /**
     * Returns the database connection and creates it if it hasn't been already.
     *
     * @return \Piwik\Tracker\Db|\Piwik\Db\AdapterInterface|\Piwik\Db
     */
    public static function get()
    {
        return self::getInstance()->getDbAdapter();
    }

    /**
     * @return Db\Db
     */
    public static function getInstance()
    {
        return StaticContainer::getContainer()->get('Piwik\Db\Db');
    }

    /**
     * Executes an unprepared SQL query. Recommended for DDL statements like `CREATE`,
     * `DROP` and `ALTER`. The return value is DBMS-specific. For MySQLI, it returns the
     * number of rows affected. For PDO, it returns a
     * [Zend_Db_Statement](http://framework.zend.com/manual/1.12/en/zend.db.statement.html) object.
     *
     * @param string $sql The SQL query.
     * @throws \Exception If there is an error in the SQL.
     * @return integer|\Zend_Db_Statement
     */
    public static function exec($sql)
    {
        return self::getInstance()->exec($sql);
    }

    /**
     * Executes an SQL query and returns the [Zend_Db_Statement](http://framework.zend.com/manual/1.12/en/zend.db.statement.html)
     * for the query.
     *
     * This method is meant for non-query SQL statements like `INSERT` and `UPDATE. If you want to fetch
     * data from the DB you should use one of the fetch... functions.
     *
     * @param string $sql The SQL query.
     * @param array $parameters Parameters to bind in the query, eg, `array(param1 => value1, param2 => value2)`.
     * @throws \Exception If there is a problem with the SQL or bind parameters.
     * @return \Zend_Db_Statement
     */
    public static function query($sql, $parameters = array())
    {
        return self::getInstance()->query($sql, $parameters);
    }

    /**
     * Executes an SQL `SELECT` statement and returns all fetched rows from the result set.
     *
     * @param string $sql The SQL query.
     * @param array $parameters Parameters to bind in the query, eg, `array(param1 => value1, param2 => value2)`.
     * @throws \Exception If there is a problem with the SQL or bind parameters.
     * @return array The fetched rows, each element is an associative array mapping column names
     *               with column values.
     */
    public static function fetchAll($sql, $parameters = array())
    {
        return self::getInstance()->fetchAll($sql, $parameters);
    }

    /**
     * Executes an SQL `SELECT` statement and returns the first row of the result set.
     *
     * @param string $sql The SQL query.
     * @param array $parameters Parameters to bind in the query, eg, `array(param1 => value1, param2 => value2)`.
     * @throws \Exception If there is a problem with the SQL or bind parameters.
     * @return array The fetched row, each element is an associative array mapping column names
     *               with column values.
     */
    public static function fetchRow($sql, $parameters = array())
    {
        return self::getInstance()->fetchRow($sql, $parameters);
    }

    /**
     * Executes an SQL `SELECT` statement and returns the first column value of the first
     * row in the result set.
     *
     * @param string $sql The SQL query.
     * @param array $parameters Parameters to bind in the query, eg, `array(param1 => value1, param2 => value2)`.
     * @throws \Exception If there is a problem with the SQL or bind parameters.
     * @return string
     */
    public static function fetchOne($sql, $parameters = array())
    {
        return self::getInstance()->fetchOne($sql, $parameters);
    }

    /**
     * Executes an SQL `SELECT` statement and returns the entire result set indexed by the first
     * selected field.
     *
     * @param string $sql The SQL query.
     * @param array $parameters Parameters to bind in the query, eg, `array(param1 => value1, param2 => value2)`.
     * @throws \Exception If there is a problem with the SQL or bind parameters.
     * @return array eg,
     *               ```
     *               array('col1value1' => array('col2' => '...', 'col3' => ...),
     *                     'col1value2' => array('col2' => '...', 'col3' => ...))
     *               ```
     */
    public static function fetchAssoc($sql, $parameters = array())
    {
        return self::getInstance()->fetchAssoc($sql, $parameters);
    }

    /**
     * Deletes all desired rows in a table, while using a limit. This function will execute many
     * DELETE queries until there are no more rows to delete.
     *
     * Use this function when you need to delete many thousands of rows from a table without
     * locking the table for too long.
     *
     * **Example**
     *
     *     // delete all visit rows whose ID is less than a certain value, 100000 rows at a time
     *     $idVisit = // ...
     *     Db::deleteAllRows(Common::prefixTable('log_visit'), "WHERE idvisit <= ?", "idvisit ASC", 100000, array($idVisit));
     *
     * @param string $table The name of the table to delete from. Must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param string $where The where clause of the query. Must include the WHERE keyword.
     * @param int $orderBy The column to order by and the order by direction, eg, `idvisit ASC`.
     * @param int $maxRowsPerQuery The maximum number of rows to delete per `DELETE` query.
     * @param array $parameters Parameters to bind for each query.
     * @return int The total number of rows deleted.
     */
    public static function deleteAllRows($table, $where, $orderBy, $maxRowsPerQuery = 100000, $parameters = array())
    {
        return self::getInstance()->deleteAllRows($table, $where, $orderBy, $maxRowsPerQuery, $parameters);
    }

    /**
     * Runs an `OPTIMIZE TABLE` query on the supplied table or tables.
     *
     * Tables will only be optimized if the `[General] enable_sql_optimize_queries` INI config option is
     * set to **1**.
     *
     * @param string|array $tables The name of the table to optimize or an array of tables to optimize.
     *                             Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @return \Zend_Db_Statement
     */
    public static function optimizeTables($tables)
    {
        return self::getInstance()->optimizeTables($tables);
    }

    /**
     * Drops the supplied table or tables.
     *
     * @param string|array $tables The name of the table to drop or an array of table names to drop.
     *                             Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @return \Zend_Db_Statement
     */
    public static function dropTables($tables)
    {
        return self::getInstance()->dropTables($tables);
    }

    /**
     * Drops all tables
     */
    public static function dropAllTables()
    {
        self::getInstance()->dropAllTables();
    }

    /**
     * Get columns information from table
     *
     * @param string|array $table The name of the table you want to get the columns definition for.
     * @return \Zend_Db_Statement
     */
    public static function getColumnNamesFromTable($table)
    {
        return self::getInstance()->getColumnNamesFromTable($table);
    }

    /**
     * Locks the supplied table or tables.
     *
     * **NOTE:** Piwik does not require the `LOCK TABLES` privilege to be available. Piwik
     * should still work if it has not been granted.
     *
     * @param string|array $tablesToRead The table or tables to obtain 'read' locks on. Table names must
     *                                   be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param string|array $tablesToWrite The table or tables to obtain 'write' locks on. Table names must
     *                                    be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @return \Zend_Db_Statement
     */
    public static function lockTables($tablesToRead, $tablesToWrite = array())
    {
        return self::getInstance()->lockTables($tablesToRead, $tablesToWrite);
    }

    /**
     * Releases all table locks.
     *
     * **NOTE:** Piwik does not require the `LOCK TABLES` privilege to be available. Piwik
     * should still work if it has not been granted.
     *
     * @return \Zend_Db_Statement
     */
    public static function unlockAllTables()
    {
        return self::getInstance()->unlockAllTables();
    }

    /**
     * Performs a `SELECT` statement on a table one chunk at a time and returns the first
     * successfully fetched value.
     *
     * This function will execute a query on one set of rows in a table. If nothing
     * is fetched, it will execute the query on the next set of rows and so on until
     * the query returns a value.
     *
     * This function will break up a `SELECT into several smaller `SELECT`s and
     * should be used when performing a `SELECT` that can take a long time to finish.
     * Using several smaller `SELECT`s will ensure that the table will not be locked
     * for too long.
     *
     * **Example**
     *
     *     // find the most recent visit that is older than a certain date
     *     $dateStart = // ...
     *     $sql = "SELECT idvisit
     *           FROM $logVisit
     *          WHERE '$dateStart' > visit_last_action_time
     *            AND idvisit <= ?
     *            AND idvisit > ?
     *       ORDER BY idvisit DESC
     *          LIMIT 1";
     *
     *     // since visits
     *     return Db::segmentedFetchFirst($sql, $maxIdVisit, 0, -self::$selectSegmentSize);
     *
     * @param string $sql The SQL to perform. The last two conditions of the `WHERE`
     *                    expression must be as follows: `'id >= ? AND id < ?'` where
     *                    **id** is the int id of the table.
     * @param int $first The minimum ID to loop from.
     * @param int $last The maximum ID to loop to.
     * @param int $step The maximum number of rows to scan in one query.
     * @param array $params Parameters to bind in the query, eg, `array(param1 => value1, param2 => value2)`
     *
     * @return string
     */
    public static function segmentedFetchFirst($sql, $first, $last, $step, $params = array())
    {
        return self::getInstance()->segmentedFetchFirst($sql, $first, $last, $step, $params);
    }

    /**
     * Performs a `SELECT` on a table one chunk at a time and returns an array
     * of every fetched value.
     *
     * This function will break up a `SELECT` query into several smaller queries by
     * using only a limited number of rows at a time. It will accumulate the results
     * of each smaller query and return the result.
     *
     * This function should be used when performing a `SELECT` that can
     * take a long time to finish. Using several smaller queries will ensure that
     * the table will not be locked for too long.
     *
     * @param string $sql The SQL to perform. The last two conditions of the `WHERE`
     *                    expression must be as follows: `'id >= ? AND id < ?'` where
     *                    **id** is the int id of the table.
     * @param int $first The minimum ID to loop from.
     * @param int $last The maximum ID to loop to.
     * @param int $step The maximum number of rows to scan in one query.
     * @param array $params Parameters to bind in the query, `array(param1 => value1, param2 => value2)`
     * @return array An array of primitive values.
     */
    public static function segmentedFetchOne($sql, $first, $last, $step, $params = array())
    {
        return self::getInstance()->segmentedFetchOne($sql, $first, $last, $step, $params);
    }

    /**
     * Performs a SELECT on a table one chunk at a time and returns an array
     * of every fetched row.
     *
     * This function will break up a `SELECT` query into several smaller queries by
     * using only a limited number of rows at a time. It will accumulate the results
     * of each smaller query and return the result.
     *
     * This function should be used when performing a `SELECT` that can
     * take a long time to finish. Using several smaller queries will ensure that
     * the table will not be locked for too long.
     *
     * @param string $sql The SQL to perform. The last two conditions of the `WHERE`
     *                    expression must be as follows: `'id >= ? AND id < ?'` where
     *                    **id** is the int id of the table.
     * @param int $first The minimum ID to loop from.
     * @param int $last The maximum ID to loop to.
     * @param int $step The maximum number of rows to scan in one query.
     * @param array $params Parameters to bind in the query, array( param1 => value1, param2 => value2)
     * @return array An array of rows that includes the result set of every smaller
     *               query.
     */
    public static function segmentedFetchAll($sql, $first, $last, $step, $params = array())
    {
        return self::getInstance()->segmentedFetchAll($sql, $first, $last, $step, $params);
    }

    /**
     * Performs a `UPDATE` or `DELETE` statement on a table one chunk at a time.
     *
     * This function will break up a query into several smaller queries by
     * using only a limited number of rows at a time.
     *
     * This function should be used when executing a non-query statement will
     * take a long time to finish. Using several smaller queries will ensure that
     * the table will not be locked for too long.
     *
     * @param string $sql The SQL to perform. The last two conditions of the `WHERE`
     *                    expression must be as follows: `'id >= ? AND id < ?'` where
     *                    **id** is the int id of the table.
     * @param int $first The minimum ID to loop from.
     * @param int $last The maximum ID to loop to.
     * @param int $step The maximum number of rows to scan in one query.
     * @param array $params Parameters to bind in the query, `array(param1 => value1, param2 => value2)`
     */
    public static function segmentedQuery($sql, $first, $last, $step, $params = array())
    {
        self::getInstance()->segmentedQuery($sql, $first, $last, $step, $params);
    }

    /**
     * Returns `true` if a table in the database, `false` if otherwise.
     *
     * @param string $tableName The name of the table to check for. Must be prefixed.
     * @return bool
     */
    public static function tableExists($tableName)
    {
        return self::getInstance()->tableExists($tableName);
    }

    /**
     * Attempts to get a named lock. This function uses a timeout of 1s, but will
     * retry a set number of times.
     *
     * @param string $lockName The lock name.
     * @param int $maxRetries The max number of times to retry.
     * @return bool `true` if the lock was obtained, `false` if otherwise.
     */
    public static function getDbLock($lockName, $maxRetries = 30)
    {
        return self::getInstance()->getDbLock($lockName, $maxRetries);
    }

    /**
     * Releases a named lock.
     *
     * @param string $lockName The lock name.
     * @return bool `true` if the lock was released, `false` if otherwise.
     */
    public static function releaseDbLock($lockName)
    {
        return self::getInstance()->releaseDbLock($lockName);
    }

    /**
     * Cached result of isLockprivilegeGranted function.
     *
     * Public so tests can simulate the situation where the lock tables privilege isn't granted.
     *
     * @var bool
     * @ignore
     */
    public static $lockPrivilegeGranted = null;

    /**
     * Checks whether the database user is allowed to lock tables.
     *
     * @return bool
     */
    public static function isLockPrivilegeGranted()
    {
        if (is_null(self::$lockPrivilegeGranted)) {
            try {
                self::lockTables(Common::prefixTable('log_visit'));
                self::unlockAllTables();

                self::$lockPrivilegeGranted = true;
            } catch (Exception $ex) {
                self::$lockPrivilegeGranted = false;
            }
        }

        return self::$lockPrivilegeGranted;
    }
}
