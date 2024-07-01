<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\Db\Adapter;
use Piwik\Db\Schema;

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
 * @api
 */
class Db
{
    public const SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

    private static $connection = null;
    private static $readerConnection = null;

    private static $logQueries = true;

    // this is used for indicate TransactionLevel Cache
    public $supportsUncommitted;
    /**
     * Returns the database connection and creates it if it hasn't been already.
     *
     * @return \Piwik\Tracker\Db|\Piwik\Db\AdapterInterface|\Piwik\Db
     */
    public static function get()
    {
        if (SettingsServer::isTrackerApiRequest()) {
            return Tracker::getDatabase();
        }

        if (!self::hasDatabaseObject()) {
            self::createDatabaseObject();
        }

        return self::$connection;
    }

    /**
     * @internal
     * @ignore
     * @return bool
     */
    public static function hasReaderConfigured()
    {
        $readerConfig = Config::getInstance()->database_reader;

        return !empty($readerConfig['host']);
    }

    /**
     * Returns the database connection and creates it if it hasn't been already. Make sure to not write any data on
     * the reader and only use the connection to read data.
     *
     * @since Matomo 3.12
     *
     * @return \Piwik\Tracker\Db|\Piwik\Db\AdapterInterface|\Piwik\Db
     */
    public static function getReader()
    {
        if (!self::hasReaderConfigured()) {
            return self::get();
        }

        if (!self::hasReaderDatabaseObject()) {
            self::createReaderDatabaseObject();
        }

        return self::$readerConnection;
    }

    /**
     * Returns an array with the Database connection information.
     *
     * @param array|null $dbConfig
     * @return array
     */
    public static function getDatabaseConfig($dbConfig = null)
    {
        $config = Config::getInstance();

        if (is_null($dbConfig)) {
            $dbConfig = $config->database;
        }

        /**
         * Triggered before a database connection is established.
         *
         * This event can be used to change the settings used to establish a connection.
         *
         * @param array *$dbInfos Reference to an array containing database connection info,
         *                        including:
         *
         *                        - **host**: The host name or IP address to the MySQL database.
         *                        - **username**: The username to use when connecting to the
         *                                        database.
         *                        - **password**: The password to use when connecting to the
         *                                       database.
         *                        - **dbname**: The name of the Piwik MySQL database.
         *                        - **port**: The MySQL database port to use.
         *                        - **adapter**: either `'PDO\MYSQL'` or `'MYSQLI'`
         *                        - **type**: The MySQL engine to use, for instance 'InnoDB'
         */
        Piwik::postEvent('Db.getDatabaseConfig', array(&$dbConfig));

        $dbConfig['profiler'] = @$config->Debug['enable_sql_profiler'];

        return $dbConfig;
    }

    /**
     * For tests only.
     * @param $connection
     * @ignore
     * @internal
     */
    public static function setDatabaseObject($connection)
    {
        self::$connection = $connection;
    }

    /**
     * Connects to the database.
     *
     * Shouldn't be called directly, use {@link get()} instead.
     *
     * @param array|null $dbConfig Connection parameters in an array. Defaults to the `[database]`
     *                             INI config section.
     */
    public static function createDatabaseObject($dbConfig = null)
    {
        $dbConfig = self::getDatabaseConfig($dbConfig);

        $db = @Adapter::factory($dbConfig['adapter'], $dbConfig);

        self::$connection = $db;
    }

    /**
     * Connects to the reader database.
     *
     * Shouldn't be called directly, use {@link get()} instead.
     *
     * @param array|null $dbConfig Connection parameters in an array. Defaults to the `[database]`
     *                             INI config section.
     *
     * @since Matomo 3.12
     */
    public static function createReaderDatabaseObject($dbConfig = null)
    {
        if (!isset($dbConfig)) {
            $dbConfig = Config::getInstance()->database_reader;
        }

        $masterDbConfig = self::getDatabaseConfig();
        $dbConfig = self::getDatabaseConfig($dbConfig);
        $dbConfig['adapter'] = $masterDbConfig['adapter'];
        $dbConfig['schema'] = $masterDbConfig['schema'];
        $dbConfig['type'] = $masterDbConfig['type'];
        $dbConfig['tables_prefix'] = $masterDbConfig['tables_prefix'];
        $dbConfig['charset'] = $masterDbConfig['charset'];

        $db = @Adapter::factory($dbConfig['adapter'], $dbConfig);

        if (!empty($dbConfig['aurora_readonly_read_committed'])) {
            $db->exec('set session aurora_read_replica_read_committed = ON;set session transaction isolation level read committed;');
        }

        self::$readerConnection = $db;
    }

    /**
     * Detect whether a database object is initialized / created or not.
     *
     * @internal
     */
    public static function hasDatabaseObject()
    {
        return isset(self::$connection);
    }

    /**
     * Detect whether a database object is initialized / created or not.
     *
     * @internal
     */
    public static function hasReaderDatabaseObject()
    {
        return isset(self::$readerConnection);
    }

    /**
     * Disconnects and destroys the database connection.
     *
     * For tests.
     */
    public static function destroyDatabaseObject()
    {
        if (self::hasDatabaseObject()) {
            DbHelper::disconnectDatabase();
        }
        self::$connection = null;

        if (self::hasReaderDatabaseObject()) {
            self::$readerConnection->closeConnection();
        }
        self::$readerConnection = null;
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
        /** @var \Zend_Db_Adapter_Abstract $db */
        $db = self::get();
        $profiler = $db->getProfiler();
        $q = $profiler->queryStart($sql, \Zend_Db_Profiler::INSERT);

        try {
            self::logSql(__FUNCTION__, $sql);

            $return = self::get()->exec($sql);
        } catch (Exception $ex) {
            self::logExtraInfoIfDeadlock($ex);
            throw $ex;
        }

        $profiler->queryEnd($q);

        return $return;
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
        try {
            self::logSql(__FUNCTION__, $sql, $parameters);

            return self::get()->query($sql, $parameters);
        } catch (Exception $ex) {
            self::logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
    }

    /**
     * Executes a callback with potential recovery from a "MySQL server has gone away" error.
     *
     * If the callback throws a "MySQL server has gone away" exception
     * it will be called again after a single reconnection attempt.
     *
     * @param callable $callback
     *
     * @return mixed
     *
     * @throws Exception
     *
     * @internal
     */
    public static function executeWithDatabaseWriterReconnectionAttempt(callable $callback)
    {
        try {
            return $callback();
        } catch (Exception $ex) {
            // only attempt reconnection in a reader/writer configuration
            if (!self::hasReaderConfigured()) {
                throw $ex;
            }

            // only attempt reconnection if we encounter a "server has gone away" error
            if (
                !self::get()->isErrNo($ex, Updater\Migration\Db::ERROR_CODE_MYSQL_SERVER_HAS_GONE_AWAY)
                && false === stripos($ex->getMessage(), 'server has gone away')
            ) {
                throw $ex;
            }

            // reconnect and retry query
            // after a 100ms wait (to avoid re-hitting a network problem immediately)
            self::$connection = null;

            usleep(100 * 1000);
            self::createDatabaseObject();

            return $callback();
        }
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
        try {
            self::logSql(__FUNCTION__, $sql, $parameters);

            return self::get()->fetchAll($sql, $parameters);
        } catch (Exception $ex) {
            self::logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
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
        try {
            self::logSql(__FUNCTION__, $sql, $parameters);

            return self::get()->fetchRow($sql, $parameters);
        } catch (Exception $ex) {
            self::logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
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
        try {
            self::logSql(__FUNCTION__, $sql, $parameters);

            return self::get()->fetchOne($sql, $parameters);
        } catch (Exception $ex) {
            self::logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
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
        try {
            self::logSql(__FUNCTION__, $sql, $parameters);

            return self::get()->fetchAssoc($sql, $parameters);
        } catch (Exception $ex) {
            self::logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
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
     * @param string $orderBy The column to order by and the order by direction, eg, `idvisit ASC`.
     * @param int $maxRowsPerQuery The maximum number of rows to delete per `DELETE` query.
     * @param array $parameters Parameters to bind for each query.
     * @return int The total number of rows deleted.
     */
    public static function deleteAllRows($table, $where, $orderBy, $maxRowsPerQuery = 100000, $parameters = array())
    {
        $orderByClause = $orderBy ? "ORDER BY $orderBy" : "";

        $sql = "DELETE FROM $table $where $orderByClause
                LIMIT " . (int)$maxRowsPerQuery;

        // delete rows w/ a limit
        $totalRowsDeleted = 0;

        do {
            $rowsDeleted = self::query($sql, $parameters)->rowCount();

            $totalRowsDeleted += $rowsDeleted;
        } while ($rowsDeleted >= $maxRowsPerQuery);

        return $totalRowsDeleted;
    }

    /**
     * Runs an `OPTIMIZE TABLE` query on the supplied table or tables.
     *
     * Tables will only be optimized if the `[General] enable_sql_optimize_queries` INI config option is
     * set to **1**.
     *
     * @param string|array $tables The name of the table to optimize or an array of tables to optimize.
     *                             Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param bool $force If true, the `OPTIMIZE TABLE` query will be run even if InnoDB tables are being used.
     * @return bool
     * @deprecated  will be removed in Matomo 6
     *              use Schema::getInstance()->optimizeTables() instead
     */
    public static function optimizeTables($tables, $force = false)
    {
        $tables = !is_array($tables) ? [$tables] : $tables;
        return Schema::getInstance()->optimizeTables($tables, (bool) $force);
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
        if (!is_array($tables)) {
            $tables = array($tables);
        }

        return self::query("DROP TABLE `" . implode('`,`', $tables) . "`");
    }

    /**
     * Drops all tables
     */
    public static function dropAllTables()
    {
        $tablesAlreadyInstalled = DbHelper::getTablesInstalled();
        self::dropTables($tablesAlreadyInstalled);
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
     * **NOTE:** Piwik does not require the `LOCK TABLES` privilege to be available. Piwik
     * should still work if it has not been granted.
     *
     * @return \Zend_Db_Statement
     */
    public static function unlockAllTables()
    {
        return self::exec("UNLOCK TABLES");
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
        $result = array();

        if ($step > 0) {
            for ($i = $first; $i <= $last; $i += $step) {
                $currentParams = array_merge($params, array($i, $i + $step));
                $result        = array_merge($result, self::fetchAll($sql, $currentParams));
            }
        } else {
            for ($i = $first; $i >= $last; $i += $step) {
                $currentParams = array_merge($params, array($i, $i + $step));
                $result        = array_merge($result, self::fetchAll($sql, $currentParams));
            }
        }

        return $result;
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
     * retry a set number of times.
     *
     * @param string $lockName The lock name.
     * @param int $maxRetries The max number of times to retry.
     * @return bool `true` if the lock was obtained, `false` if otherwise.
     * @throws \Exception if Lock name is too long
     */
    public static function getDbLock($lockName, $maxRetries = 30)
    {
        if (strlen($lockName) > 64) {
            throw new \Exception('DB lock name has to be 64 characters or less for MySQL 5.7 compatibility.');
        }

        /*
         * the server (e.g., shared hosting) may have a low wait timeout
         * so instead of a single GET_LOCK() with a 30 second timeout,
         * we use a 1 second timeout and loop, to avoid losing our MySQL
         * connection
         */
        $sql = 'SELECT GET_LOCK(?, 1)';

        $db = self::get();

        while ($maxRetries > 0) {
            $result = $db->fetchOne($sql, array($lockName));
            if ($result == '1') {
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
     * @return bool `true` if the lock was released, `false` if otherwise.
     */
    public static function releaseDbLock($lockName)
    {
        $sql = 'SELECT RELEASE_LOCK(?)';

        $db = self::get();
        return $db->fetchOne($sql, array($lockName)) == '1';
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
                Db::lockTables(Common::prefixTable('site_url'));
                Db::unlockAllTables();

                self::$lockPrivilegeGranted = true;
            } catch (Exception $ex) {
                self::$lockPrivilegeGranted = false;
            }
        }

        return self::$lockPrivilegeGranted;
    }

    private static function logExtraInfoIfDeadlock($ex)
    {
        if (
            !self::get()->isErrNo($ex, 1213)
            && !self::get()->isErrNo($ex, 1205)
        ) {
            return;
        }

        try {
            $deadlockInfo = self::fetchAll("SHOW ENGINE INNODB STATUS");

            // log using exception so backtrace appears in log output
            Log::debug(new Exception("Encountered deadlock: " . print_r($deadlockInfo, true)));
        } catch (\Exception $e) {
            //  1227 Access denied; you need (at least one of) the PROCESS privilege(s) for this operation
        }
    }

    private static function logSql($functionName, $sql, $parameters = array())
    {
        self::checkBoundParametersIfInDevMode($sql, $parameters);

        if (
            self::$logQueries === false
            || @Config::getInstance()->Debug['log_sql_queries'] != 1
        ) {
            return;
        }

        // NOTE: at the moment we don't log parameters in order to avoid sensitive information leaks
        Log::debug("Db::%s() executing SQL: %s", $functionName, $sql);
    }

    private static function checkBoundParametersIfInDevMode($sql, $parameters)
    {
        if (!Development::isEnabled()) {
            return;
        }

        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        foreach ($parameters as $index => $parameter) {
            if ($parameter instanceof Date) {
                throw new \Exception("Found bound parameter (index = $index) is Date instance which will not work correctly in following SQL: $sql");
            }
        }
    }

    /**
     * @param bool $enable
     */
    public static function enableQueryLog($enable)
    {
        self::$logQueries = $enable;
    }

    /**
     * @return boolean
     */
    public static function isQueryLogEnabled()
    {
        return self::$logQueries;
    }

    /**
     * @deprecated will be removed with Matomo 6
     *             use Schema::getInstance()->isOptimizeInnoDBSupported() instead
     */
    public static function isOptimizeInnoDBSupported($version = null)
    {
        return Db\Schema::getInstance()->isOptimizeInnoDBSupported();
    }
}
