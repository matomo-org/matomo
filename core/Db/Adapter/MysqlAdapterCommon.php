<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db\Adapter;

use Piwik\Db;
use Exception;
use Piwik\DbHelper;
use Piwik\Log;
use Piwik\Piwik;
use Piwik\Tracker\Db\DbException;

/**
 * This class contain MySQL specific functionality shared by the PDO\Mysql and Mysqli adapters
 */
class MysqlAdapterCommon
{

    const SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

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
     */
    public static function optimizeTables($tables, bool $force = false): bool
    {

        if (!self::isOptimizeInnoDBSupported()
            && !$force
        ) {
            // filter out all InnoDB tables
            $myisamDbTables = array();
            foreach (self::getTableStatus() as $row) {
                if (strtolower($row['Engine']) == 'myisam'
                    && in_array($row['Name'], $tables)
                ) {
                    $myisamDbTables[] = $row['Name'];
                }
            }

            $tables = $myisamDbTables;
        }

        if (empty($tables)) {
            return false;
        }

        // optimize the tables
        $success = true;
        foreach ($tables as &$t) {
            $ok = Db::get()->query('OPTIMIZE TABLE ' . $t);
            if (!$ok) {
                $success = false;
            }
        }

        return $success;
    }

    private static function getTableStatus()
    {
        return Db::fetchAll("SHOW TABLE STATUS");
    }

    private static function isOptimizeInnoDBSupported($version = null)
    {
        if ($version === null) {
            $version = Db::fetchOne("SELECT VERSION()");
        }

        $version = strtolower($version);

        if (strpos($version, "mariadb") === false) {
            return false;
        }

        $semanticVersion = strstr($version, '-', $beforeNeedle = true);
        return version_compare($semanticVersion, '10.1.1', '>=');
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
    public static function getDbLock(string $lockName, int $maxRetries = 30): bool
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

        $db = Db::get();

        while ($maxRetries > 0) {
            $result = $db->fetchOne($sql, [$lockName]);
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
    public static function releaseDbLock(string $lockName): bool
    {
        $sql = 'SELECT RELEASE_LOCK(?)';

        $db = Db::get();
        return $db->fetchOne($sql, [$lockName]) == '1';
    }

    /**
     * Locks the supplied table or tables.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available. Matomo
     * should still work if it has not been granted.
     *
     * @param array $tablesToRead The table or tables to obtain 'read' locks on. Table names must
     *                                   be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param array $tablesToWrite The table or tables to obtain 'write' locks on. Table names must
     *                                    be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @return void
     */
    public static function lockTables(array $tablesToRead, array $tablesToWrite = []): void
    {

        $lockExprs = [];
        foreach ($tablesToWrite as $table) {
            $lockExprs[] = $table . " WRITE";
        }

        foreach ($tablesToRead as $table) {
            $lockExprs[] = $table . " READ";
        }

        Db::get()->exec("LOCK TABLES " . implode(', ', $lockExprs));
    }

    /**
     * Releases all table locks.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available. It
     * should still work if this has not been granted.
     *
     * @return void
     */
    public static function unlockAllTables(): void
    {
        Db::get()->exec("UNLOCK TABLES");
    }

    /**
     * Log additional information for deadlock exceptions
     *
     * @param $ex
     *
     * @return void
     */
    public static function logExtraInfoIfDeadlock($ex): void
    {

        if (!Db::get()->isErrNo($ex, 1213)
            && !Db::get()->isErrNo($ex, 1205)
        ) {
            return;
        }

        try {
            $deadlockInfo = Db::fetchAll("SHOW ENGINE INNODB STATUS");

            // log using exception so backtrace appears in log output
            Log::debug(new Exception("Encountered deadlock: " . print_r($deadlockInfo, true)));
        } catch(\Exception $e) {
            //  1227 Access denied; you need (at least one of) the PROCESS privilege(s) for this operation
        }
    }

    /**
     * Intercepts certain exception messages and replaces leaky ones with ones that don't reveal too much info
     *
     * @param string $message
     *
     * @return string
     */
    public static function overriddenExceptionMessage(string $message): string
    {
        $safeMessageMap = array(
            // add any exception search terms and their replacement message here
            '[2006]'                        => Piwik::translate('General_ExceptionDatabaseUnavailable'),
            'MySQL server has gone away'    => Piwik::translate('General_ExceptionDatabaseUnavailable'),
            '[1698]'                        => Piwik::translate('General_ExceptionDatabaseAccess'),
            'Access denied'                 => Piwik::translate('General_ExceptionDatabaseAccess')
        );

        foreach ($safeMessageMap as $search_term => $safeMessage) {
            if (strpos($message, $search_term) !== false) {
                return $safeMessage;
            }
        }

        return '';
    }

    /**
     * Get the row format
     *
     * @param string $usedCharset
     *
     * @return string
     */
    public static function getRowFormat(string $usedCharset): string
    {
        return $usedCharset === 'utf8mb4' ? 'ROW_FORMAT=DYNAMIC' : '';
    }

    /**
     * Return boolean indicating whether transaction level can be set
     *
     * @return bool
     */
    public static function canLikelySetTransactionLevel(): bool
    {
        $dbSettings = new Db\Settings();
        return strtolower($dbSettings->getEngine()) === 'innodb';
    }


    /**
     * Get the current transaction isolation level
     *
     * @return null|string
     */
    public static function getTransationIsolationLevel(): ?string
    {
        try {
            $backup = Db::get()->fetchOne('SELECT @@TX_ISOLATION');
        } catch (\Exception $e) {
            try {
                $backup = Db::get()->fetchOne('SELECT @@transaction_isolation');
            } catch (\Exception $e) {
                Db::get()->supportsUncommitted = false;
                return null;
            }
        }
        return $backup;
    }

    /**
     * Set the transaction isolation level to read uncommitted
     *
     * @return void
     * @throws \Piwik\Tracker\Db\DbException
     */
    public static function setTransactionIsolationLevelReadUncommitted(): void
    {
        Db::get()->query('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
    }

    /**
     * Set the transaction isolation level to read uncommitted
     *
     * @param string $previous The previous transaction isolation status result
     *
     * @return void
     */
    public static function restorePreviousTransactionIsolationLevel(string $previous): void
    {
        $previous = str_replace('-', ' ', $previous);
        if (in_array($previous, array('REPEATABLE READ', 'READ COMMITTED', 'SERIALIZABLE'))) {
            Db::get()->query('SET SESSION TRANSACTION ISOLATION LEVEL '.$previous);
        } elseif ($previous !== 'READ UNCOMMITTED') {
            Db::get()->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
        }
    }

    /**
     * Returns the default database charset to use
     *
     * @return string
     * @throws Exception
     */
    public static function getDefaultCharset(): string
    {
        $result = Db::get()->fetchRow("SHOW CHARACTER SET LIKE 'utf8mb4'");

        if (empty($result)) {
            return 'utf8'; // charset not available
        }

        $result = Db::get()->fetchRow("SHOW VARIABLES LIKE 'character_set_database'");

        if (!empty($result) && $result['Value'] === 'utf8mb4') {
            return 'utf8mb4'; // database has utf8mb4 charset, so assume it can be used
        }

        $result = Db::get()->fetchRow("SHOW VARIABLES LIKE 'innodb_file_per_table'");

        if (empty($result) || $result['Value'] !== 'ON') {
            return 'utf8'; // innodb_file_per_table is required for utf8mb4
        }

        return 'utf8mb4';
    }

    /**
     * Returns sql queries to convert all installed tables to utf8mb4
     *
     * @return array
     */
    public static function getUtf8mb4ConversionQueries(): array
    {

        $allTables = DbHelper::getTablesInstalled();

        $queries   = [];

        foreach ($allTables as $table) {
            $queries[] = "ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4;";
        }

        return $queries;
    }

    /**
     * Adds a MAX_EXECUTION_TIME hint into a SELECT query if $limit is bigger than 1
     *
     * @param string $sql  query to add hint to
     * @param int $limit  time limit in seconds
     * @return string
     */
    public static function addMaxExecutionTimeHintToQuery(string $sql, int $limit): string
    {

        $sql = trim($sql);
        $pos = stripos($sql, 'SELECT');
        if ($pos !== false) {

            $timeInMs = $limit * 1000;
            $timeInMs = (int) $timeInMs;
            $maxExecutionTimeHint = ' /*+ MAX_EXECUTION_TIME('.$timeInMs.') */ ';

            $sql = substr_replace($sql, 'SELECT ' . $maxExecutionTimeHint, $pos, strlen('SELECT'));
        }

        return $sql;
    }

    /**
     * Execute any additional session setup that should happen after the tracker database connection is established
     *
     * This method should be overridden by descendent db adapters as needed
     *
     * @param array $trackerConfig
     * @param Db    $db
     *
     * @return void
     *
     * @throws DbException
     */
    public static function doTrackerPostConnectionSetup(array $trackerConfig, Db $db): void
    {
        if (!empty($trackerConfig['innodb_lock_wait_timeout']) && $trackerConfig['innodb_lock_wait_timeout'] > 0){
            // we set this here because we only want to set this config if a connection is actually created.
            $time = (int) $trackerConfig['innodb_lock_wait_timeout'];
            $db->query('SET @@innodb_lock_wait_timeout = ' . $time);
        }
    }

}
