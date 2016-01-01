<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db;

use Exception;

/**
 */
interface AdapterInterface
{
    /**
     * Reset the configuration variables in this adapter.
     */
    public function resetConfig();

    /**
     * Check database server version
     *
     * @throws Exception if database version is less than required version
     */
    public function checkServerVersion();

    /**
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Returns true if this adapter supports blobs as fields
     *
     * @return bool
     */
    public function hasBlobDataType();

    /**
     * Returns true if this adapter supports bulk loading
     *
     * @return bool
     */
    public function hasBulkLoader();

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string $errno
     * @return bool
     */
    public function isErrNo(Exception $e, $errno);

    /**
     * Is the connection character set equal to utf8?
     *
     * @return bool
     */
    public function isConnectionUTF8();

    /**
     * @return \Zend_Db_Profiler
     */
    public function getProfiler();

    /**
     * TODO
     *
     * @param string $sql
     * @return mixed
     */
    public function exec($sql);

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     */
    public function query($sql, $parameters = array());

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     */
    public function fetchAll($sql, $parameters = array());

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     */
    public function fetchRow($sql, $parameters = array());

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     */
    public function fetchOne($sql, $parameters = array());

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     */
    public function fetchAssoc($sql, $parameters = array());

    /**
     * TODO
     *
     * @param $sql
     * @param $parameters
     * @return mixed
     */
    public function fetchCol($sql, $parameters = array());

    /**
     * TODO
     *
     * @return string
     */
    public function lastInsertId();

    /**
     * TODO
     */
    public function closeConnection();

    /**
     * TODO
     *
     * @return bool
     */
    public function isConnected();

    /**
     * TODO
     *
     * @param $table
     * @param array $bind
     */
    public function insert($table, array $bind);

    /**
     * TODO
     *
     * @param $table
     * @param array $bind
     * @param string $where
     */
    public function update($table, array $bind, $where = '');

    /**
     * TODO
     *
     * @return mixed
     */
    public function beginTransaction();

    /**
     * TODO
     *
     * @return mixed
     */
    public function commit();

    /**
     * TODO
     *
     * @return mixed
     */
    public function rollBack();
}
