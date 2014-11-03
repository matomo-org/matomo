<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;

class Sequence
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    private function getTableName()
    {
        return Common::prefixTable('sequence');
    }

    public function getCurrentId()
    {
        $table = $this->getTableName();
        $sql   = 'SELECT value FROM ' . $table . ' WHERE name = ? LIMIT 1';

        $db = Db::get();
        $id = $db->fetchOne($sql, array($this->name));

        if (!empty($id)) {
            return (int) $id;
        }
    }

    public function create($initialValue = 1)
    {
        $table = $this->getTableName();
        $db    = $this->getDb();

        $db->insert($table, array('name' => $this->name, 'value' => $initialValue));

        return $initialValue;
    }

    public function getQueryToCreateSequence($initialValue)
    {
        $table = $this->getTableName();
        $query = sprintf("INSERT INTO %s (name, value) VALUES ('%s', %d)", $table, $this->name, $initialValue);

        return $query;
    }

    public  function getNextId()
    {
        $table = $this->getTableName();
        $sql   = 'UPDATE ' . $table . ' SET value = LAST_INSERT_ID(value + 1) WHERE name = ?';

        $db       = Db::get();
        $result   = $db->query($sql, array($this->name));
        $rowCount = $result->rowCount();

        if (1 !== $rowCount) {
            throw new Exception("Sequence '" . $this->name . "' not found.");
        }

        $createdId = $db->lastInsertId();

        return (int) $createdId;
    }

    private function getDb()
    {
        return Db::get();
    }
}
