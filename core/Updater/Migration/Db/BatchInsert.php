<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

use Piwik\Db;
use Piwik\Updater\Migration;

/**
 * Inserts a new record into an existing table.
 *
 * @see Factory::batchInsert()
 * @see Db\BatchInsert::tableInsertBatch()
 * @ignore
 *
 * We do not extend Migration\Db as it should be not printed to the users in queries preview when updating.
 */
class BatchInsert extends Migration
{
    private $table;
    private $columnNames;
    private $values;
    private $throwException;
    private $charset;

    /**
     * @param string $table
     * @param array $columnNames
     * @param array $values
     * @param bool $throwException
     * @param string $charset
     * @throws \Exception
     */
    public function __construct($table, $columnNames, $values, $throwException, $charset)
    {
        $this->table = $table;
        $this->columnNames = $columnNames;
        $this->values = $values;
        $this->throwException = (bool) $throwException;
        $this->charset = $charset;
    }

    public function shouldIgnoreError($exception)
    {
        return false;
    }

    public function __toString()
    {
        return '<batch insert>';
    }

    public function exec()
    {
        Db\BatchInsert::tableInsertBatch($this->table, $this->columnNames, $this->values, $this->throwException, $this->charset);
    }

    public function getColumnNames()
    {
        return $this->columnNames;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return boolean
     */
    public function doesThrowException()
    {
        return $this->throwException;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }
}
