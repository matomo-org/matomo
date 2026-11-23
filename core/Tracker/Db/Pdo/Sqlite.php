<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker\Db\Pdo;

use Exception;
use Piwik\Db\Schema\Sqlite\NamedLock;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ErrorCodes;
use Piwik\Db\Sqlite\MysqlError;
use Piwik\Db\TransactionErrors;

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

    /**
     * The absolute path of the database file.
     */
    public function getDatabaseFile(): string
    {
        return Connection::getDatabaseFile($this->config);
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

    /**
     * Closes the connection, releasing its named locks as a disconnect does on MySQL.
     */
    public function disconnect()
    {
        NamedLock::releaseConnection($this);
        parent::disconnect();
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

    /**
     * SQLite ends a transaction by itself on some errors (full disk, I/O, out of memory, interrupt,
     * sometimes busy), where MySQL keeps it open. So after any error in a transaction, the state SQLite
     * is in decides, not a list of errors: if the transaction is gone, it is marked aborted and nothing
     * after the error can be committed; if it is still open, the error ends the statement only, as on
     * MySQL (e.g. a duplicate key a batch catches).
     */
    protected function markTransactionAbortedIfNeeded(Exception $e): void
    {
        if (!$this->isInTransaction() || $this->isTransactionAborted()) {
            return;
        }

        $isOpen = $this->isTransactionStillOpen();
        if ($isOpen === true) {
            return;
        }

        if ($isOpen === null) {
            $this->closeUnknownTransaction();
        }
        $this->inTransaction = false;
        $this->markTransactionAborted(new MysqlError('SQLite ended the transaction', TransactionErrors::ERROR_CODE_DEADLOCK, $e));
    }

    /**
     * SQLite's driver ends fetchAll() at an error while stepping through the rows and returns the rows read
     * so far, without throwing. The error may also have ended the transaction.
     */
    protected function failIfFetchFailed(\PDOStatement $statement): void
    {
        $error = $statement->errorInfo();
        if (($error[0] ?? '00000') === '00000') {
            return;
        }

        $e = new \PDOException('SQLSTATE[' . $error[0] . ']: ' . ($error[2] ?? 'error'), (int) ($error[1] ?? 0));
        $e->errorInfo = $error;
        throw $e;
    }

    /**
     * Whether SQLite still has the transaction open: a BEGIN is refused within one. Null when that can't be
     * told (the BEGIN failed otherwise, or the probe's own transaction couldn't be rolled back): the
     * transaction is then treated as gone, so a batch never commits in part, and closed for certain.
     */
    private function isTransactionStillOpen(): ?bool
    {
        try {
            $this->connection->exec('BEGIN');
        } catch (\PDOException $e) {
            return stripos($e->getMessage(), 'within a transaction') !== false ? true : null;
        }

        try {
            $this->connection->exec('ROLLBACK');
        } catch (\PDOException $e) {
            return null;
        }

        return false;
    }

    /**
     * Rolls back whatever may still be open; if even that fails, reopens the connection, so a transaction
     * can't keep the write lock past this one.
     */
    private function closeUnknownTransaction(): void
    {
        try {
            $this->connection->exec('ROLLBACK');
            return;
        } catch (\PDOException $e) {
            if (stripos($e->getMessage(), 'no transaction is active') !== false) {
                return;
            }
        }

        $this->disconnect();
        try {
            $this->connect();
        } catch (Exception $e) {
            // the request fails with the original error; the next query reports the connection error
        }
    }
}
