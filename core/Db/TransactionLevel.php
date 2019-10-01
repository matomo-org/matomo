<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db;

use Piwik\Db;
use Piwik\Option;

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

    public function canLikelySetTransactionLevel()
    {
        $dbSettings = new Db\Settings();

        return strtolower($dbSettings->getEngine()) === 'innodb';
    }

    public function setUncommitted()
    {
        try {
            $backup = $this->db->fetchOne('SELECT @@TX_ISOLATION');
        } catch (\Exception $e) {
            try {
                $backup = $this->db->fetchOne('SELECT @@transaction_isolation');
            } catch (\Exception $e) {
                return false;
            }
        }

        try {
            $this->db->query('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
            $this->statusBackup = $backup;

            Option::set(self::TEST_OPTION_NAME, '1'); // try setting something w/ the new transaction isolation level
        } catch (\Exception $e) {
            // catch eg 1665 Cannot execute statement: impossible to write to binary log since BINLOG_FORMAT = STATEMENT and at least one table uses a storage engine limited to row-based logging. InnoDB is limited to row-logging when transaction isolation level is READ COMMITTED or READ UNCOMMITTED
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
                $this->db->query('SET SESSION TRANSACTION ISOLATION LEVEL '.$value);
            } elseif ($value !== 'READ UNCOMMITTED') {
                $this->db->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            }
        }

    }

}
