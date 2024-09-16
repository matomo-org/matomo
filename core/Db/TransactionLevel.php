<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

use Piwik\Db;
use Piwik\Option;

class TransactionLevel
{
    public const TEST_OPTION_NAME = 'TransactionLevel.testOption';

    private $statusBackup;

    /**
     * @var TransactionalDatabaseInterface $transactionalDatabase
     */
    private $transactionalDatabase;

    public function __construct(TransactionalDatabaseInterface $transactionalDatabase)
    {
        $this->transactionalDatabase = $transactionalDatabase;
    }

    public function canLikelySetTransactionLevel()
    {
        $dbSettings = new Db\Settings();

        return strtolower($dbSettings->getEngine()) === 'innodb';
    }

    /**
     * @deprecated Use `setTransactionLevelForNonLockingReads`
     */
    public function setUncommitted()
    {
        return $this->setTransactionLevelForNonLockingReads();
    }

    public function setTransactionLevelForNonLockingReads(): bool
    {
        if ($this->transactionalDatabase->getSupportsTransactionLevelForNonLockingReads() === false) {
            // we know "Uncommitted" transaction level is not supported, we don't need to do anything as it won't work to set the status
            return false;
        }

        try {
            $backup = $this->transactionalDatabase->getCurrentTransactionIsolationLevelForSession();
        } catch (\Exception $e) {
            $this->transactionalDatabase->setSupportsTransactionLevelForNonLockingReads(false);
            return false;
        }

        try {
            $this->transactionalDatabase->setTransactionIsolationLevel(Schema::getInstance()->getSupportedReadIsolationTransactionLevel());

            $this->statusBackup = $backup;

            if ($this->transactionalDatabase->getSupportsTransactionLevelForNonLockingReads() === null) {
                // the first time we need to check if the transaction level actually works by
                // trying to set something w/ the new transaction isolation level
                Option::set(self::TEST_OPTION_NAME, '1');
            }

            $this->transactionalDatabase->setSupportsTransactionLevelForNonLockingReads(true);
        } catch (\Exception $e) {
            // setting the transaction level status did not work
            // catch eg 1665 Cannot execute statement: impossible to write to binary log since BINLOG_FORMAT = STATEMENT and at least one table uses a storage engine limited to row-based logging. InnoDB is limited to row-logging when transaction isolation level is READ COMMITTED or READ UNCOMMITTED
            $this->transactionalDatabase->setSupportsTransactionLevelForNonLockingReads(false);
            $this->restorePreviousStatus();
            return false;
        }

        return true;
    }

    public function restorePreviousStatus()
    {
        if ($this->statusBackup) {
            $value = strtoupper($this->statusBackup);
            $this->statusBackup = null;

            $value = str_replace('-', ' ', $value);
            if (in_array($value, array('REPEATABLE READ', 'READ COMMITTED', 'SERIALIZABLE'))) {
                $this->transactionalDatabase->setTransactionIsolationLevel($value);
            } elseif ($value !== 'READ UNCOMMITTED') {
                $this->transactionalDatabase->setTransactionIsolationLevel('REPEATABLE READ');
            }
        }
    }
}
