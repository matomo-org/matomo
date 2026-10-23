<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * Decides what a database error means for the transaction it happened in.
 *
 * This is the only place that lists these error codes. To handle a new one, add it to ENDS_TRANSACTION or
 * STATEMENT_ONLY: the tracker database adapters, bulk tracking and queued tracking all ask this class.
 */
final class TransactionErrors
{
    /**
     * MariaDB with innodb_snapshot_isolation: a row read by the transaction was changed by another one.
     */
    public const ERROR_CODE_RECORD_HAS_CHANGED = 1020;

    /**
     * The COMMIT itself failed.
     */
    public const ERROR_CODE_ERROR_DURING_COMMIT = 1180;

    public const ERROR_CODE_LOCK_WAIT_TIMEOUT = 1205;

    /**
     * InnoDB ran out of space for row locks.
     */
    public const ERROR_CODE_LOCK_TABLE_FULL = 1206;

    /**
     * Also returned by Galera / Percona XtraDB Cluster when a transaction fails certification.
     */
    public const ERROR_CODE_DEADLOCK = 1213;

    /**
     * MySQL Group Replication rejected the transaction at COMMIT.
     */
    public const ERROR_CODE_TRANSACTION_ROLLBACK_DURING_COMMIT = 3101;

    /**
     * The server has already rolled the whole transaction back. Nothing written in it survives, and anything
     * written afterwards on the same connection would be stored on its own.
     *
     * The message is a fallback for exceptions that carry no error number, e.g. when a caller rewrapped them.
     */
    private const ENDS_TRANSACTION = [
        self::ERROR_CODE_RECORD_HAS_CHANGED => 'Record has changed since last read',
        self::ERROR_CODE_ERROR_DURING_COMMIT => null,
        self::ERROR_CODE_LOCK_TABLE_FULL => 'The total number of locks exceeds the lock table size',
        self::ERROR_CODE_DEADLOCK => 'Deadlock found when trying to get lock',
        self::ERROR_CODE_TRANSACTION_ROLLBACK_DURING_COMMIT => 'Plugin instructed the server to rollback the current transaction',
    ];

    /**
     * Only the failed statement was rolled back, the transaction can still be used.
     *
     * A lock wait timeout rolls back the whole transaction instead when innodb_rollback_on_timeout is enabled,
     * which is why Matomo recommends keeping that setting off.
     */
    private const STATEMENT_ONLY = [
        self::ERROR_CODE_LOCK_WAIT_TIMEOUT => 'Lock wait timeout exceeded',
    ];

    /**
     * @param \Piwik\Tracker\Db|AdapterInterface $db the connection the error happened on
     */
    public static function endsTransaction($db, \Exception $e): bool
    {
        return self::matchesAny($db, $e, self::ENDS_TRANSACTION);
    }

    /**
     * Like endsTransaction(), but decides by the error number only. For exceptions known to carry the number, whose
     * message may also hold query parameters that must not decide anything.
     *
     * @param \Piwik\Tracker\Db|AdapterInterface $db the connection the error happened on
     */
    public static function endsTransactionByErrorNumber($db, \Exception $e): bool
    {
        return self::matchesAny($db, $e, array_fill_keys(array_keys(self::ENDS_TRANSACTION), null));
    }

    /**
     * @param \Piwik\Tracker\Db|AdapterInterface $db the connection the error happened on
     */
    public static function isStatementOnly($db, \Exception $e): bool
    {
        return self::matchesAny($db, $e, self::STATEMENT_ONLY);
    }

    /**
     * @param array<int, string|null> $errors
     */
    private static function matchesAny($db, \Exception $e, array $errors): bool
    {
        foreach ($errors as $code => $message) {
            if ($db->isErrNo($e, $code)) {
                return true;
            }

            // MySQL's message can quote the failing value, so only trust it when there is no error number to go by
            if ($message !== null && !self::hasErrorNumber($e) && stripos($e->getMessage(), $message) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function hasErrorNumber(\Exception $e): bool
    {
        // the code, e.g. for mysqli, or a number in the message as PDO reports it, e.g. "SQLSTATE[23000]: ...: 1062 ..."
        return !empty($e->getCode()) || preg_match('/(?:\[|\s)[0-9]{4}(?:\]|\s)/', $e->getMessage()) === 1;
    }
}
