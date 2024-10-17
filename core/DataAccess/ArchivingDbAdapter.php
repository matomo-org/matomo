<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Config;
use Piwik\Db\AdapterInterface;
use Piwik\DbHelper;
use Piwik\Log\LoggerInterface;
use Exception;

class ArchivingDbAdapter
{
    /**
     * @var AdapterInterface|\Zend_Db_Adapter_Abstract
     */
    private $wrapped;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $maxExecutionTime;

    public function __construct($wrapped, ?LoggerInterface $logger = null)
    {
        $this->wrapped = $wrapped;
        $this->logger = $logger;
        $this->maxExecutionTime = (float) Config::getInstance()->General['archiving_query_max_execution_time'];
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->wrapped, $name], $arguments);
    }

    public function exec($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    public function query($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    public function fetchAll($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    public function fetchRow($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    public function fetchOne($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    public function fetchAssoc($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public function isErrNo($e, $errno)
    {
        return $this->wrapped->isErrNo($e, $errno);
    }

    private function logSql($sql)
    {
        // Log on DEBUG level all SQL archiving queries
        if ($this->logger) {
            $this->logger->debug($sql);
        }
    }

    private function callFunction($function, ...$args)
    {

        try {
            return call_user_func_array([$this->wrapped, $function], $args);
        } catch (\Exception $e) {
            if (
                $this->isErrNo($e, \Piwik\Updater\Migration\Db::ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_QUERY_INTERRUPTED) ||
                $this->isErrNo($e, \Piwik\Updater\Migration\Db::ERROR_CODE_MAX_EXECUTION_TIME_EXCEEDED_SORT_ABORTED)
            ) {
                $this->logger->warning(
                    'Archiver query exceeded maximum execution time: {details}',
                    ['details' => json_encode($args, true)]
                );
            }
            throw $e;
        }
    }
}
