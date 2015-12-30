<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

use Psr\Log\LoggerInterface;
use Zend_Db_Adapter_Abstract;

/**
 * TODO
 *
 * @api
 */
class Connection
{
    private static $queryLogEnabled = false;

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    private $adapter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $logSqlQueries;

    public function __construct($dbConfig, LoggerInterface $logger = null, $logSqlQueries = false)
    {
        $this->adapter = Adapter::factory($dbConfig['adapter'], $dbConfig);
        $this->logger = $logger;
        $this->logSqlQueries = $logSqlQueries;
    }

    /**
     * TODO
     *
     * @param $sql
     * @return mixed
     * @throws \Exception
     * @throws \Zend_Db_Profiler_Exception
     */
    public function exec($sql)
    {
        $profiler = $this->adapter->getProfiler();
        $q = $profiler->queryStart($sql, \Zend_Db_Profiler::INSERT);

        try {
            $this->logSql(__FUNCTION__, $sql);

            $return = $this->adapter->exec($sql);
        } catch (\Exception $ex) {
            $this->logExtraInfoIfDeadlock($ex);
            throw $ex;
        }

        $profiler->queryEnd($q);

        return $return;
    }

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return \Zend_Db_Statement_Interface
     * @throws \Exception
     */
    public function query($sql, $parameters = array())
    {
        try {
            $this->logSql(__FUNCTION__, $sql, $parameters);

            return $this->adapter->query($sql, $parameters);
        } catch (\Exception $ex) {
            $this->logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
    }

    /**
     * @param $sql
     * @param $parameters
     * @return mixed
     * @throws \Exception
     */
    public function fetchAll($sql, $parameters = array())
    {
        try {
            $this->logSql(__FUNCTION__, $sql, $parameters);

            return $this->adapter->fetchAll($sql, $parameters);
        } catch (\Exception $ex) {
            $this->logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
    }

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     * @throws \Exception
     */
    public function fetchRow($sql, $parameters = array())
    {
        try {
            $this->logSql(__FUNCTION__, $sql, $parameters);

            return $this->adapter->fetchRow($sql, $parameters);
        } catch (\Exception $ex) {
            $this->logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
    }

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     * @throws \Exception
     */
    public function fetchOne($sql, $parameters = array())
    {
        try {
            $this->logSql(__FUNCTION__, $sql, $parameters);

            return $this->adapter->fetchOne($sql, $parameters);
        } catch (\Exception $ex) {
            $this->logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
    }

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return array
     * @throws \Exception
     */
    public function fetchAssoc($sql, $parameters)
    {
        try {
            $this->logSql(__FUNCTION__, $sql, $parameters);

            return $this->adapter->fetchAssoc($sql, $parameters);
        } catch (\Exception $ex) {
            $this->logExtraInfoIfDeadlock($ex);
            throw $ex;
        }
    }

    /**
     * TODO
     */
    public function disconnect()
    {
        $this->adapter->closeConnection();
    }

    private function logSql($functionName, $sql, $parameters = array())
    {
        if (!self::$queryLogEnabled
            || !$this->logSqlQueries
        ) {
            return;
        }

        // NOTE: at the moment we don't log parameters in order to avoid sensitive information leaks
        $this->logger->debug("Db::{function}() executing SQL: {sql}", array(
            'function' => $functionName,
            'sql' => $sql
        ));
    }

    private function logExtraInfoIfDeadlock($ex)
    {
        if (!$this->adapter->isErrNo($ex, 1213)) {
            return;
        }

        try {
            $deadlockInfo = $this->fetchAll("SHOW ENGINE INNODB STATUS");

            // log using exception so backtrace appears in log output
            $this->logger->debug("Encountered deadlock: {info}", array(
                'info' => print_r($deadlockInfo, true),
                'exception' => new \Exception(),
            ));
        } catch(\Exception $e) {
            //  1227 Access denied; you need (at least one of) the PROCESS privilege(s) for this operation
        }
    }

    public function createDatabase($dbName)
    {
        $this->exec("CREATE DATABASE IF NOT EXISTS " . $dbName . " DEFAULT CHARACTER SET utf8");
    }

    /**
     * @return AdapterInterface|Zend_Db_Adapter_Abstract
     * @deprecated
     */
    public function getImpl() // TODO: remove this method eventually
    {
        return $this->adapter;
    }

    public static function isQueryLogEnabled()
    {
        return self::$queryLogEnabled;
    }

    public static function enableQueryLog($value)
    {
        self::$queryLogEnabled = $value;
    }
}