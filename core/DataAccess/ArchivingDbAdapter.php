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

/**
 * Wraps a database adapter for archiving queries. It adds the configured maximum execution time hint (if any)
 * to each query and logs the SQL on debug level.
 */
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
     * @var float
     */
    private $maxExecutionTime;

    /**
     * @internal
     */
    public function __construct($wrapped, ?LoggerInterface $logger = null)
    {
        $this->wrapped = $wrapped;
        $this->logger = $logger;
        $this->maxExecutionTime = (float) Config::getInstance()->General['archiving_query_max_execution_time'];
    }

    /**
     * @internal
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->wrapped, $name], $arguments);
    }

    /**
     * @internal
     */
    public function exec($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    /**
     * Executes an SQL query and returns the statement.
     *
     * @param string $sql The SQL query.
     * @param mixed ...$params Optionally an array of values to bind in the query.
     * @return mixed The statement returned by the database adapter.
     * @api
     */
    public function query($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    /**
     * Executes an SQL `SELECT` statement and returns all rows.
     *
     * @param string $sql The SQL query.
     * @param mixed ...$params Optionally an array of values to bind in the query.
     * @return array The result rows.
     * @api
     */
    public function fetchAll($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    /**
     * Executes an SQL `SELECT` statement and returns the first row.
     *
     * @param string $sql The SQL query.
     * @param mixed ...$params Optionally an array of values to bind in the query.
     * @return array|false The first row, `false` if there is none.
     * @api
     */
    public function fetchRow($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    /**
     * Executes an SQL `SELECT` statement and returns the first column of the first row.
     *
     * @param string $sql The SQL query.
     * @param mixed ...$params Optionally an array of values to bind in the query.
     * @return string|int|float|null|false The first column of the first row, or `false` if there is none.
     * @api
     */
    public function fetchOne($sql, ...$params)
    {
        $sql = DbHelper::addMaxExecutionTimeHintToQuery($sql, $this->maxExecutionTime);
        $this->logSql($sql);

        return $this->callFunction(__FUNCTION__, $sql, ...$params);
    }

    /**
     * Executes an SQL `SELECT` statement and returns all rows indexed by the first selected column.
     *
     * @param string $sql The SQL query.
     * @param mixed ...$params Optionally an array of values to bind in the query.
     * @return array The rows indexed by the first selected column.
     * @api
     */
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
     * @param int|string $errno
     * @return bool
     * @internal
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
