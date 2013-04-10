<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package PluginsFunctions
 */

/**
 * SQL wrapper
 *
 * @package PluginsFunctions
 */
class Piwik_Sql
{
    /**
     * Returns the database adapter to use
     *
     * @return Piwik_Tracker_Db|Piwik_Db_Adapter_Interface
     */
    static private function getDb()
    {
        $db = null;
        if (!empty($GLOBALS['PIWIK_TRACKER_MODE'])) {
            $db = Piwik_Tracker::getDatabase();
        }
        if ($db === null) {
            $db = Zend_Registry::get('db');
        }
        return $db;
    }

    /**
     * Executes an unprepared SQL query on the DB.  Recommended for DDL statements, e.g., CREATE/DROP/ALTER.
     * The return result is DBMS-specific. For MySQLI, it returns the number of rows affected.  For PDO, it returns the Zend_Db_Statement object
     * If you want to fetch data from the DB you should use the function Piwik_FetchAll()
     *
     * @param string $sql  SQL Query
     * @return integer|Zend_Db_Statement
     */
    static public function exec($sql)
    {
        $db = Zend_Registry::get('db');
        $profiler = $db->getProfiler();
        $q = $profiler->queryStart($sql, Zend_Db_Profiler::INSERT);
        $return = self::getDb()->exec($sql);
        $profiler->queryEnd($q);
        return $return;
    }

    /**
     * Executes a SQL query on the DB and returns the Zend_Db_Statement object
     * If you want to fetch data from the DB you should use the function Piwik_FetchAll()
     *
     * See also http://framework.zend.com/manual/en/zend.db.statement.html
     *
     * @param string $sql         SQL Query
     * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
     * @return Zend_Db_Statement
     */
    static public function query($sql, $parameters = array())
    {
        return self::getDb()->query($sql, $parameters);
    }

    /**
     * Executes the SQL Query and fetches all the rows from the database query
     *
     * @param string $sql         SQL Query
     * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
     * @return array (one row in the array per row fetched in the DB)
     */
    static public function fetchAll($sql, $parameters = array())
    {
        return self::getDb()->fetchAll($sql, $parameters);
    }

    /**
     * Fetches first row of result from the database query
     *
     * @param string $sql         SQL Query
     * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
     * @return array
     */
    static public function fetchRow($sql, $parameters = array())
    {
        return self::getDb()->fetchRow($sql, $parameters);
    }

    /**
     * Fetches first column of first row of result from the database query
     *
     * @param string $sql         SQL Query
     * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
     * @return string
     */
    static public function fetchOne($sql, $parameters = array())
    {
        return self::getDb()->fetchOne($sql, $parameters);
    }

    /**
     * Fetches result from the database query as an array of associative arrays.
     *
     * @param string $sql         SQL query
     * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
     * @return array
     */
    static public function fetchAssoc($sql, $parameters = array())
    {
        return self::getDb()->fetchAssoc($sql, $parameters);
    }

    /**
     * Deletes all desired rows in a table, while using a limit. This function will execute a
     * DELETE query until there are no more rows to delete.
     *
     * @param string $table            The name of the table to delete from. Must be prefixed.
     * @param string $where            The where clause of the query. Must include the WHERE keyword.
     * @param int $maxRowsPerQuery  The maximum number of rows to delete per DELETE query.
     * @param array $parameters       Parameters to bind in the query.
     * @return int  The total number of rows deleted.
     */
    static public function deleteAllRows($table, $where, $maxRowsPerQuery = 100000, $parameters = array())
    {
        $sql = "DELETE FROM $table $where LIMIT " . (int)$maxRowsPerQuery;

        // delete rows w/ a limit
        $totalRowsDeleted = 0;
        do {
            $rowsDeleted = self::query($sql, $parameters)->rowCount();

            $totalRowsDeleted += $rowsDeleted;
        } while ($rowsDeleted >= $maxRowsPerQuery);

        return $totalRowsDeleted;
    }

    /**
     * Runs an OPTIMIZE TABLE query on the supplied table or tables. The table names must be prefixed.
     *
     * @param string|array $tables  The name of the table to optimize or an array of tables to optimize.
     * @return Zend_Db_Statement
     */
    static public function optimizeTables($tables)
    {
        $optimize = Piwik_Config::getInstance()->General['enable_sql_optimize_queries'];
        if (empty($optimize)) {
            return;
        }

        if (empty($tables)) {
            return false;
        }
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        // filter out all InnoDB tables
        $nonInnoDbTables = array();
        foreach (Piwik_FetchAll("SHOW TABLE STATUS") as $row) {
            if (strtolower($row['Engine']) != 'innodb'
                && in_array($row['Name'], $tables)
            ) {
                $nonInnoDbTables[] = $row['Name'];
            }
        }

        if (empty($nonInnoDbTables)) {
            return false;
        }

        // optimize the tables
        return self::query("OPTIMIZE TABLE " . implode(',', $nonInnoDbTables));
    }

    /**
     * Drops the supplied table or tables. The table names must be prefixed.
     *
     * @param string|array $tables  The name of the table to drop or an array of table names to drop.
     * @return Zend_Db_Statement
     */
    static public function dropTables($tables)
    {
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return self::query("DROP TABLE " . implode(',', $tables));
    }

    /**
     * Locks the supplied table or tables. The table names must be prefixed.
     *
     * @param string|array $tablesToRead   The table or tables to obtain 'read' locks on.
     * @param string|array $tablesToWrite  The table or tables to obtain 'write' locks on.
     * @return Zend_Db_Statement
     */
    static public function lockTables($tablesToRead, $tablesToWrite = array())
    {
        if (!is_array($tablesToRead)) {
            $tablesToRead = array($tablesToRead);
        }
        if (!is_array($tablesToWrite)) {
            $tablesToWrite = array($tablesToWrite);
        }

        $lockExprs = array();
        foreach ($tablesToWrite as $table) {
            $lockExprs[] = $table . " WRITE";
        }
        foreach ($tablesToRead as $table) {
            $lockExprs[] = $table . " READ";
        }

        return self::exec("LOCK TABLES " . implode(', ', $lockExprs));
    }

    /**
     * Releases all table locks.
     *
     * @return Zend_Db_Statement
     */
    static public function unlockAllTables()
    {
        return self::exec("UNLOCK TABLES");
    }

    /**
     * Performs a SELECT on a table one chunk at a time and returns the first
     * fetched value.
     *
     * @param string $sql    The SQL to perform. The last two conditions of the WHERE
     *                       expression must be as follows: 'id >= ? AND id < ?' where
     *                       'id' is the int id of the table. If $step < 0, the condition
     *                       should be 'id <= ? AND id > ?'.
     * @param int    $first  The minimum ID to loop from.
     * @param int    $last   The maximum ID to loop to.
     * @param int    $step   The maximum number of rows to scan in each smaller SELECT.
     * @param array  $params parameters to bind in the query, array( param1 => value1, param2 => value2)
     *
     * @return array
     */
    static public function segmentedFetchFirst($sql, $first, $last, $step, $params)
    {
        $result = false;
        if ($step > 0) {
            for ($i = $first; $result === false && $i <= $last; $i += $step) {
                $result = self::fetchOne($sql, array_merge($params, array($i, $i + $step)));
            }
        } else {
            for ($i = $first; $result === false && $i >= $last; $i += $step) {
                $result = self::fetchOne($sql, array_merge($params, array($i, $i + $step)));
            }
        }
        return $result;
    }

    /**
     * Performs a SELECT on a table one chunk at a time and returns an array
     * of every fetched value.
     *
     * @param string $sql    The SQL to perform. The last two conditions of the WHERE
     *                       expression must be as follows: 'id >= ? AND id < ?' where
     *                      'id' is the int id of the table.
     * @param int    $first  The minimum ID to loop from.
     * @param int    $last   The maximum ID to loop to.
     * @param int    $step   The maximum number of rows to scan in each smaller SELECT.
     * @param array  $params Parameters to bind in the query, array( param1 => value1, param2 => value2)
     *
     * @return array
     */
    static public function segmentedFetchOne($sql, $first, $last, $step, $params)
    {
        $result = array();
        if ($step > 0) {
            for ($i = $first; $i <= $last; $i += $step) {
                $result[] = self::fetchOne($sql, array_merge($params, array($i, $i + $step)));
            }
        } else {
            for ($i = $first; $i >= $last; $i += $step) {
                $result[] = self::fetchOne($sql, array_merge($params, array($i, $i + $step)));
            }
        }
        return $result;
    }

    /**
     * Performs a SELECT on a table one chunk at a time and returns an array
     * of every fetched row.
     *
     * @param string $sql    The SQL to perform. The last two conditions of the WHERE
     *                       expression must be as follows: 'id >= ? AND id < ?' where
     *                      'id' is the int id of the table.
     * @param int    $first  The minimum ID to loop from.
     * @param int    $last   The maximum ID to loop to.
     * @param int    $step   The maximum number of rows to scan in each smaller SELECT.
     * @param array  $params Parameters to bind in the query, array( param1 => value1, param2 => value2)
     *
     * @return array
     */
    static public function segmentedFetchAll($sql, $first, $last, $step, $params)
    {
        $result = array();
        if ($step > 0) {
            for ($i = $first; $i <= $last; $i += $step) {
                $currentParams = array_merge($params, array($i, $i + $step));
                $result = array_merge($result, self::fetchAll($sql, $currentParams));
            }
        } else {
            for ($i = $first; $i >= $last; $i += $step) {
                $currentParams = array_merge($params, array($i, $i + $step));
                $result = array_merge($result, self::fetchAll($sql, $currentParams));
            }
        }
        return $result;
    }

    /**
     * Performs a non-SELECT query on a table one chunk at a time.
     *
     * @param string $sql    The SQL to perform. The last two conditions of the WHERE
     *                       expression must be as follows: 'id >= ? AND id < ?' where
     *                      'id' is the int id of the table.
     * @param int    $first  The minimum ID to loop from.
     * @param int    $last   The maximum ID to loop to.
     * @param int    $step   The maximum number of rows to scan in each smaller query.
     * @param array  $params Parameters to bind in the query, array( param1 => value1, param2 => value2)
     *
     * @return array
     */
    static public function segmentedQuery($sql, $first, $last, $step, $params)
    {
        if ($step > 0) {
            for ($i = $first; $i <= $last; $i += $step) {
                $currentParams = array_merge($params, array($i, $i + $step));
                self::query($sql, $currentParams);
            }
        } else {
            for ($i = $first; $i >= $last; $i += $step) {
                $currentParams = array_merge($params, array($i, $i + $step));
                self::query($sql, $currentParams);
            }
        }
    }

    /**
     * Attempts to get a named lock. This function uses a timeout of 1s, but will
     * retry a set number of time.
     *
     * @param string $lockName The lock name.
     * @param int $maxRetries The max number of times to retry.
     * @return bool true if the lock was obtained, false if otherwise.
     */
    static public function getDbLock($lockName, $maxRetries = 30)
    {
        /*
         * the server (e.g., shared hosting) may have a low wait timeout
         * so instead of a single GET_LOCK() with a 30 second timeout,
         * we use a 1 second timeout and loop, to avoid losing our MySQL
         * connection
         */
        $sql = 'SELECT GET_LOCK(?, 1)';

        $db = Zend_Registry::get('db');

        while ($maxRetries > 0) {
            if ($db->fetchOne($sql, array($lockName)) == '1') {
                return true;
            }
            $maxRetries--;
        }
        return false;
    }

    /**
     * Releases a named lock.
     *
     * @param string $lockName The lock name.
     * @return bool true if the lock was released, false if otherwise.
     */
    static public function releaseDbLock($lockName)
    {
        $sql = 'SELECT RELEASE_LOCK(?)';

        $db = Zend_Registry::get('db');
        return $db->fetchOne($sql, array($lockName)) == '1';
    }
}

/**
 * Executes an unprepared SQL query on the DB.  Recommended for DDL statements, e.g., CREATE/DROP/ALTER.
 * The return result is DBMS-specific. For MySQLI, it returns the number of rows affected.  For PDO, it returns the Zend_Db_Statement object
 * If you want to fetch data from the DB you should use the function Piwik_FetchAll()
 *
 * @see Piwik_Sql::exec
 *
 * @param string $sqlQuery  SQL Query
 * @return integer|Zend_Db_Statement
 */
function Piwik_Exec($sqlQuery)
{
    return Piwik_Sql::exec($sqlQuery);
}

/**
 * Executes a SQL query on the DB and returns the Zend_Db_Statement object
 * If you want to fetch data from the DB you should use the function Piwik_FetchAll()
 *
 * See also http://framework.zend.com/manual/en/zend.db.statement.html
 *
 * @see Piwik_Sql::query
 *
 * @param string $sqlQuery    SQL Query
 * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return Zend_Db_Statement
 */
function Piwik_Query($sqlQuery, $parameters = array())
{
    return Piwik_Sql::query($sqlQuery, $parameters);
}

/**
 * Executes the SQL Query and fetches all the rows from the database query
 *
 * @see Piwik_Sql::fetchAll
 *
 * @param string $sqlQuery    SQL Query
 * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array  (one row in the array per row fetched in the DB)
 */
function Piwik_FetchAll($sqlQuery, $parameters = array())
{
    return Piwik_Sql::fetchAll($sqlQuery, $parameters);
}

/**
 * Fetches first row of result from the database query
 *
 * @see Piwik_Sql::fetchRow
 *
 * @param string $sqlQuery    SQL Query
 * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array
 */
function Piwik_FetchRow($sqlQuery, $parameters = array())
{
    return Piwik_Sql::fetchRow($sqlQuery, $parameters);
}

/**
 * Fetches first column of first row of result from the database query
 *
 * @see Piwik_Sql::fetchOne
 *
 * @param string $sqlQuery    SQL Query
 * @param array $parameters  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return string
 */
function Piwik_FetchOne($sqlQuery, $parameters = array())
{
    return Piwik_Sql::fetchOne($sqlQuery, $parameters);
}

/**
 * Fetches result from the database query as an array of associative arrays.
 *
 * @param string $sqlQuery
 * @param array $parameters Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array
 */
function Piwik_FetchAssoc($sqlQuery, $parameters = array())
{
    return Piwik_Sql::fetchAssoc($sqlQuery, $parameters);
}

/**
 * Deletes all desired rows in a table, while using a limit. This function will execute a
 * DELETE query until there are no more rows to delete.
 *
 * @see Piwik_Sql::deleteAllRows
 *
 * @param string $table            The name of the table to delete from. Must be prefixed.
 * @param string $where            The where clause of the query. Must include the WHERE keyword.
 * @param int $maxRowsPerQuery  The maximum number of rows to delete per DELETE query.
 * @param array $parameters       Parameters to bind in the query.
 * @return int  The total number of rows deleted.
 */
function Piwik_DeleteAllRows($table, $where, $maxRowsPerQuery, $parameters = array())
{
    return Piwik_Sql::deleteAllRows($table, $where, $maxRowsPerQuery, $parameters);
}

/**
 * Runs an OPTIMIZE TABLE query on the supplied table or tables. The table names must be prefixed.
 *
 * @see Piwik_Sql::optimizeTables
 *
 * @param string|array $tables  The name of the table to optimize or an array of tables to optimize.
 * @return Zend_Db_Statement
 */
function Piwik_OptimizeTables($tables)
{
    return Piwik_Sql::optimizeTables($tables);
}

/**
 * Drops the supplied table or tables. The table names must be prefixed.
 *
 * @see Piwik_Sql::dropTables
 *
 * @param string|array $tables  The name of the table to drop or an array of table names to drop.
 * @return Zend_Db_Statement
 */
function Piwik_DropTables($tables)
{
    return Piwik_Sql::dropTables($tables);
}

/**
 * Locks the supplied table or tables. The table names must be prefixed.
 *
 * @see Piwik_Sql::lockTables
 *
 * @param string|array $tablesToRead   The table or tables to obtain 'read' locks on.
 * @param string|array $tablesToWrite  The table or tables to obtain 'write' locks on.
 * @return Zend_Db_Statement
 */
function Piwik_LockTables($tablesToRead, $tablesToWrite = array())
{
    return Piwik_Sql::lockTables($tablesToRead, $tablesToWrite);
}

/**
 * Releases all table locks.
 *
 * @see Piwik_Sql::unlockAllTables
 *
 * @return Zend_Db_Statement
 */
function Piwik_UnlockAllTables()
{
    return Piwik_Sql::unlockAllTables();
}

/**
 * Performs a SELECT on a table one chunk at a time and returns the first
 * fetched value.
 *
 * This function will break up a SELECT into several smaller SELECTs and
 * should be used when performing a SELECT that can take a long time to finish.
 * Using several smaller SELECTs will ensure that the table will not be locked
 * for too long.
 *
 * @see Piwik_Sql::segmentedFetchFirst
 *
 * @param string  $sql     The SQL to perform. The last two conditions of the WHERE
 *                         expression must be as follows: 'id >= ? AND id < ?' where
 *                         'id' is the int id of the table.
 * @param int     $first   The minimum ID to loop from.
 * @param int     $last    The maximum ID to loop to.
 * @param int     $step    The maximum number of rows to scan in each smaller SELECT.
 * @param array   $params  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 *
 * @return string
 */
function Piwik_SegmentedFetchFirst($sql, $first, $last, $step, $params = array())
{
    return Piwik_Sql::segmentedFetchFirst($sql, $first, $last, $step, $params);
}

/**
 * Performs a SELECT on a table one chunk at a time and returns an array
 * of every fetched value.
 *
 * This function will break up a SELECT into several smaller SELECTs and
 * should be used when performing a SELECT that can take a long time to finish.
 * Using several smaller SELECTs will ensure that the table will not be locked
 * for too long.
 *
 * @see Piwik_Sql::segmentedFetchFirst
 *
 * @param string  $sql     The SQL to perform. The last two conditions of the WHERE
 *                         expression must be as follows: 'id >= ? AND id < ?' where
 *                         'id' is the int id of the table.
 * @param int     $first   The minimum ID to loop from.
 * @param int     $last    The maximum ID to loop to.
 * @param int     $step    The maximum number of rows to scan in each smaller SELECT.
 * @param array   $params  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 *
 * @return array
 */
function Piwik_SegmentedFetchOne($sql, $first, $last, $step, $params = array())
{
    return Piwik_Sql::segmentedFetchOne($sql, $first, $last, $step, $params);
}

/**
 * Performs a SELECT on a table one chunk at a time and returns an array
 * of every fetched row.
 *
 * This function will break up a SELECT into several smaller SELECTs and
 * should be used when performing a SELECT that can take a long time to finish.
 * Using several smaller SELECTs will ensure that the table will not be locked
 * for too long.
 *
 * @see Piwik_Sql::segmentedFetchFirst
 *
 * @param string  $sql     The SQL to perform. The last two conditions of the WHERE
 *                         expression must be as follows: 'id >= ? AND id < ?' where
 *                         'id' is the int id of the table.
 * @param int     $first   The minimum ID to loop from.
 * @param int     $last    The maximum ID to loop to.
 * @param int     $step    The maximum number of rows to scan in each smaller SELECT.
 * @param array   $params  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 *
 * @return array
 */
function Piwik_SegmentedFetchAll($sql, $first, $last, $step, $params = array())
{
    return Piwik_Sql::segmentedFetchAll($sql, $first, $last, $step, $params);
}

/**
 * Performs a query on a table one chunk at a time and returns an array of
 * every fetched row.
 *
 * This function will break up a non-SELECT query (like an INSERT, UPDATE, or
 * DELETE) into smaller queries and should be used when performing an operation
 * that can take a long time to finish. Using several small queries will ensure
 * that the table will not be locked for too long.
 *
 * @see Piwik_Sql::segmentedQuery
 *
 * @param string  $sql     The SQL to perform. The last two conditions of the WHERE
 *                         expression must be as follows: 'id >= ? AND id < ?' where
 *                         'id' is the int id of the table.
 * @param int     $first   The minimum ID to loop from.
 * @param int     $last    The maximum ID to loop to.
 * @param int     $step    The maximum number of rows to scan in each smaller query.
 * @param array   $params  Parameters to bind in the query, array( param1 => value1, param2 => value2)
 *
 * @return array
 */
function Piwik_SegmentedQuery($sql, $first, $last, $step, $params = array())
{
    return Piwik_Sql::segmentedQuery($sql, $first, $last, $step, $params);
}

/**
 * Attempts to get a named lock. This function uses a timeout of 1s, but will
 * retry a set number of time.
 *
 * @see Piwik_Sql::getDbLock
 *
 * @param string $lockName The lock name.
 * @param int $maxRetries The max number of times to retry.
 * @return bool true if the lock was obtained, false if otherwise.
 */
function Piwik_GetDbLock($lockName, $maxRetries = 30)
{
    return Piwik_Sql::getDbLock($lockName, $maxRetries);
}

/**
 * Releases a named lock.
 *
 * @see Piwik_Sql::releaseDbLock
 *
 * @param string $lockName The lock name.
 * @return bool true if the lock was released, false if otherwise.
 */
function Piwik_ReleaseDbLock($lockName)
{
    return Piwik_Sql::releaseDbLock($lockName);
}

