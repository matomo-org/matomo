<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess;

use Piwik\ArchiveProcessor\ArchivingStatus;
use Piwik\Concurrency\Lock;
use Piwik\Db\AdapterInterface;
use Psr\Log\LoggerInterface;

class ArchivingDbAdapter
{
    /**
     * @var AdapterInterface|\Zend_Db_Adapter_Abstract
     */
    private $wrapped;

    /**
     * @var Lock
     */
    private $archivingLock;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($wrapped, Lock $archivingLock = null, LoggerInterface $logger = null)
    {
        $this->wrapped = $wrapped;
        $this->archivingLock = $archivingLock;
        $this->logger = $logger;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->wrapped, $name], $arguments);
    }

    public function exec($sql)
    {
        $this->reexpireLock();
        $this->logSql($sql);

        return call_user_func_array([$this->wrapped, __FUNCTION__], func_get_args());
    }

    public function query($sql)
    {
        $this->reexpireLock();
        $this->logSql($sql);

        return call_user_func_array([$this->wrapped, __FUNCTION__], func_get_args());
    }

    public function fetchAll($sql)
    {
        $this->reexpireLock();
        $this->logSql($sql);

        return call_user_func_array([$this->wrapped, __FUNCTION__], func_get_args());
    }

    public function fetchRow($sql)
    {
        $this->reexpireLock();
        $this->logSql($sql);

        return call_user_func_array([$this->wrapped, __FUNCTION__], func_get_args());
    }

    public function fetchOne($sql)
    {
        $this->reexpireLock();
        $this->logSql($sql);

        return call_user_func_array([$this->wrapped, __FUNCTION__], func_get_args());
    }

    public function fetchAssoc($sql)
    {
        $this->reexpireLock();
        $this->logSql($sql);

        return call_user_func_array([$this->wrapped, __FUNCTION__], func_get_args());
    }

    private function logSql($sql)
    {
        // Log on DEBUG level all SQL archiving queries
        if ($this->logger) {
            $this->logger->debug($sql);
        }
    }

    private function reexpireLock()
    {
        if ($this->archivingLock) {
            $this->archivingLock->reexpireLock();
        }
    }
}