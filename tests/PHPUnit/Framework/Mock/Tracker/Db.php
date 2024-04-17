<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Tracker;

use Piwik\Tracker\Db\Pdo\Mysql;

class Db extends Mysql
{
    public $commitTransactionId = false;
    public $rollbackTransactionId = false;
    public $beganTransaction = false;
    public $connectCalled = false;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct($dbInfo, $driverName = 'mysql')
    {
        $this->dsn = 'testdrivername';
        $this->username = 'testuser';
        $this->password = 'testpassword';
        $this->charset = 'testcharset';
    }

    public function connect()
    {
        $this->connectCalled = true;
    }

    /**
     * Start Transaction
     * @return string TransactionID
     */
    public function beginTransaction()
    {
        $this->beganTransaction = true;
        return 'my4929transactionid';
    }

    public function commit($xid)
    {
        $this->commitTransactionId = $xid;
    }

    public function rollBack($xid)
    {
        $this->rollbackTransactionId = $xid;
    }
}
