<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker\Db\Pdo;

use Exception;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ErrorCodes;

/**
 * SQLite tracker adapter (`adapter = PDO\SQLITE`). Queries, fetches and the transaction bookkeeping
 * are the PDO MySQL adapter's; connecting, transactions and error numbers are SQLite's.
 */
class Sqlite extends Mysql
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var bool
     */
    private $inTransaction = false;

    public function __construct($dbInfo)
    {
        $this->config = $dbInfo;
        $this->dsn = Connection::getDsn(Connection::getDatabaseFile($dbInfo));
    }

    public function connect()
    {
        $timer = self::$profiling ? $this->initProfiler() : null;

        $this->connection = Connection::open($this->config);
        $this->inTransaction = false;

        if ($timer !== null) {
            $this->recordQueryProfile('connect', $timer);
        }
    }

    public function isMysqlServerHasGoneAwayError(Exception $e)
    {
        return false;
    }

    public function reconnect(Exception $e)
    {
        throw $e;
    }

    public function isErrNo($e, $errno)
    {
        return ErrorCodes::isErrNo($e, $errno);
    }

    protected function executeStatement(\PDOStatement $statement, array $parameters): void
    {
        Connection::bindValues($statement, $parameters);
        $statement->execute();
    }

    /**
     * Matomo's explicit transactions all write, so they take the write lock when they start. The
     * transaction statements are sent as SQL, so they behave the same on every PHP version; PDO doesn't
     * see them before PHP 8.4, so whether one is open is tracked here.
     */
    protected function beginConnectionTransaction(): bool
    {
        $this->connection->exec('BEGIN IMMEDIATE');
        $this->inTransaction = true;

        return true;
    }

    protected function commitConnectionTransaction(): bool
    {
        // a failed COMMIT (e.g. busy) leaves the transaction open, to be rolled back
        $this->connection->exec('COMMIT');
        $this->inTransaction = false;

        return true;
    }

    protected function rollBackConnectionTransaction(): bool
    {
        try {
            $this->connection->exec('ROLLBACK');
        } catch (\PDOException $e) {
            // SQLite already rolled back, e.g. after an I/O error
            if (stripos($e->getMessage(), 'no transaction is active') === false) {
                throw $e;
            }
        }
        $this->inTransaction = false;

        return true;
    }

    protected function isConnectionInTransaction(): bool
    {
        return $this->inTransaction;
    }
}
