<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Db;

use Exception;
use Piwik\Db\AdapterInterface;
use Piwik\Db\AdapterWrapper;
use Psr\Log\LoggerInterface;

/**
 * TODO
 */
class TestAdapterWrapper implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $wrapped;

    public function __construct(AdapterInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    private function call($name, $arguments)
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        return call_user_func_array(array($this->wrapped, $name), $arguments);
    }

    private function connect()
    {
        $this->wrapped->exec("SET wait_timeout=28800;");
    }

    public function resetConfig()
    {
        // empty (for tests, we sometimes want to disconnect & reconnect, so leave this empty)
    }

    public function checkServerVersion()
    {
        return $this->wrapped->checkServerVersion();
    }

    public function isEnabled()
    {
        return $this->wrapped->isEnabled();
    }

    public function hasBlobDataType()
    {
        return $this->wrapped->hasBlobDataType();
    }

    public function hasBulkLoader()
    {
        return $this->wrapped->hasBulkLoader();
    }

    public function isErrNo(Exception $e, $errno)
    {
        return $this->wrapped->isErrNo($e, $errno);
    }

    public function isConnectionUTF8()
    {
        return $this->wrapped->isConnectionUTF8();
    }

    public function getProfiler()
    {
        return $this->wrapped->getProfiler();
    }

    public function exec($sql)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function query($sql, $parameters = array())
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function fetchAll($sql, $parameters = array())
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function fetchRow($sql, $parameters = array())
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function fetchOne($sql, $parameters = array())
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function fetchAssoc($sql, $parameters = array())
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function fetchCol($sql, $parameters = array())
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function lastInsertId()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function closeConnection()
    {
        $this->wrapped->closeConnection();
    }

    public function isConnected()
    {
        return $this->wrapped->isConnected();
    }

    public function insert($table, array $bind)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function update($table, array $bind, $where = '')
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function beginTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function commit()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    public function rollBack()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}