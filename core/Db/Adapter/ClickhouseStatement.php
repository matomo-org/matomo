<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Adapter;

/**
 * Minimal statement object returned by {@see Clickhouse::query()}.
 *
 * Archiving code (LogAggregator, RecordBuilder implementations) calls
 * `$query->fetch()` in a while-loop to iterate result rows. This class wraps the
 * pre-fetched rows array and emulates the parts of the Zend_Db_Statement interface
 * those callers use; the remaining methods are stubs.
 */
class ClickhouseStatement
{
    /** @var array<int, array<string, mixed>> */
    private $rows;

    /** @var int */
    private $position = 0;

    /**
     * @param array<int, array<string, mixed>> $rows Pre-parsed result rows.
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    /**
     * Returns the next row as an associative array, or false when exhausted.
     * The parameters exist for interface compatibility and are ignored — all
     * callers use the default fetch mode.
     *
     * @return array<string, mixed>|false
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if ($this->position >= count($this->rows)) {
            return false;
        }
        return $this->rows[$this->position++];
    }

    /**
     * Returns all remaining rows as an array of associative arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll($style = null, $col = null)
    {
        $remaining = array_slice($this->rows, $this->position);
        $this->position = count($this->rows);
        return $remaining;
    }

    public function fetchColumn($col = 0)
    {
        $row = $this->fetch();
        if ($row === false) {
            return false;
        }
        $values = array_values($row);
        return $values[$col] ?? false;
    }

    public function fetchObject($class = 'stdClass', array $config = [])
    {
        $row = $this->fetch();
        if ($row === false) {
            return false;
        }
        return (object) $row;
    }

    public function rowCount()
    {
        return count($this->rows);
    }

    public function columnCount()
    {
        return isset($this->rows[0]) ? count($this->rows[0]) : 0;
    }

    /**
     * No-op: rows are already fully buffered in memory.
     */
    public function closeCursor()
    {
    }

    // -------------------------------------------------------------------------
    // Zend_Db_Statement interface stubs — not used by archiving callers
    // -------------------------------------------------------------------------

    public function bindColumn($column, &$param, $type = null)
    {
    }

    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
    }

    public function bindValue($parameter, $value, $type = null)
    {
    }

    public function errorCode()
    {
        return null;
    }

    public function errorInfo()
    {
        return [];
    }

    public function execute(array $params = [])
    {
        return true;
    }

    public function getAttribute($key)
    {
        return null;
    }

    public function getColumnMeta($column)
    {
        return false;
    }

    public function nextRowset()
    {
        return false;
    }

    public function setAttribute($key, $val)
    {
    }

    public function setFetchMode($mode)
    {
    }
}
