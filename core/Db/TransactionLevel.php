<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db;

use Piwik\Option;

/**
 * This class contains shared functions to control transaction level isolation.
 */
class TransactionLevel
{
    const TEST_OPTION_NAME = 'TransactionLevel.testOption';

    private $statusBackup;

    /**
     * @var \Piwik\Tracker\Db|\Piwik\Db\AdapterInterface|\Piwik\Db $db
     */
    private $db;

    /**
     * @param \Piwik\Tracker\Db|\Piwik\Db\AdapterInterface|\Piwik\Db $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Return boolean indicating whether transaction level can be set
     *
     * @return bool
     */
    public function canLikelySetTransactionLevel(): bool
    {
        return $this->db::canLikelySetTransactionLevel();
    }

    /**
     * Set the read uncommitted transaction level
     *
     * @return bool True if the level was set successfully
     */
    public function setUncommitted(): bool
    {

        if ($this->db->supportsUncommitted === false) {
            // we know "Uncommitted" transaction level is not supported, we don't need to do anything as it won't work to set the status
            return false;
        }

        // Attempt to get backup
        $backup = $this->db::getTransationIsolationLevel();
        if (!$backup) {
            $this->db->supportsUncommitted = false;
            return false;
        }

        // Attempt to set read uncommitted transaction level
        try {

            $this->db::setTransactionIsolationLevelReadUncommitted();
            $this->statusBackup = $backup;

            if ($this->db->supportsUncommitted === null) {
                // the first time we need to check if the transaction level actually works by
                // trying to set something w/ the new transaction isolation level
                Option::set(self::TEST_OPTION_NAME, '1');
            }

            $this->db->supportsUncommitted = true;
        } catch (\Exception $e) {
            // setting the transaction level status did not work
            // catch eg 1665 Cannot execute statement: impossible to write to binary log since BINLOG_FORMAT = STATEMENT
            // and at least one table uses a storage engine limited to row-based logging. InnoDB is limited to row-logging
            // when transaction isolation level is READ COMMITTED or READ UNCOMMITTED
            $this->db->supportsUncommitted = false;
            $this->restorePreviousStatus();
            return false;
        }

        return true;
    }

    /**
     * Restore the transaction isolation level to it's previous state
     *
     * @throws \Piwik\Tracker\Db\DbException
     *
     * @return void
     */
    public function restorePreviousStatus(): void
    {
        if ($this->statusBackup) {
            $value = strtoupper($this->statusBackup);
            $this->statusBackup = null;

            $this->db::restorePreviousTransactionIsolationLevel($value);
        }
    }
}
