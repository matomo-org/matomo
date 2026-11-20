<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\Db\AdapterInterface;
use Piwik\Db\Sqlite\Connection;

/**
 * Used for generating auto increment ids.
 *
 * Example:
 *
 * $sequence = new Sequence('my_sequence_name');
 * $id = $sequence->getNextId();
 * $db->insert('anytable', array('id' => $id, '...' => '...'));
 */
class Sequence
{
    public const TABLE_NAME = 'sequence';
    /**
     * @var string
     */
    private $name;

    /**
     * @var AdapterInterface
     */
    private $db;

    /**
     * @var string
     */
    private $table;

    /**
     * The name of the table or sequence you want to get an id for.
     *
     * @param string $name eg 'archive_numeric_2014_11'
     * @param AdapterInterface|null $db You can optionally pass a DB adapter to make it work against another database.
     * @param string|null $tablePrefix
     */
    public function __construct($name, $db = null, $tablePrefix = null)
    {
        $this->name = $name;
        $this->db = $db ?: Db::get();
        $this->table = $this->getTableName($tablePrefix);
    }

    /**
     * Creates / initializes a new sequence.
     *
     * @param int $initialValue
     * @return int The actually used value to initialize the table.
     *
     * @throws \Exception in case a sequence having this name already exists.
     */
    public function create($initialValue = 0)
    {
        $initialValue = (int) $initialValue;

        $this->db->insert($this->table, array('name' => $this->name, 'value' => $initialValue));

        return $initialValue;
    }

    /**
     * Returns true if the sequence exist.
     *
     * @return bool
     */
    public function exists()
    {
        // a SELECT's row count is undefined in PDO, and 0 on SQLite
        $query = $this->db->query('SELECT 1 FROM `' . $this->table . '` WHERE name = ?', $this->name);

        return $query->fetchColumn() !== false;
    }

    /**
     * Get / allocate / reserve a new id for the current sequence. Important: Getting the next id will fail in case
     * no such sequence exists. Make sure to create one if needed, see {@link create()}.
     *
     * @return int
     * @throws Exception
     */
    public function getNextId()
    {
        // SQLite lacks LAST_INSERT_ID(expr): RETURNING reads the value in the same, atomic, statement
        $isSqlite = Connection::isSqliteConnection($this->db);
        if ($isSqlite) {
            $sql = 'UPDATE `' . $this->table . '` SET value = value + 1 WHERE name = ? RETURNING value';
        } else {
            $sql = 'UPDATE ' . $this->table . ' SET value = LAST_INSERT_ID(value + 1) WHERE name = ?';
        }

        $result = $this->db->query($sql, array($this->name));

        if ($isSqlite) {
            $rows = $result->fetchAll(\PDO::FETCH_NUM);
            if (count($rows) !== 1) {
                throw new Exception("Sequence '" . $this->name . "' not found.");
            }

            return (int) $rows[0][0];
        }

        $rowCount = $result->rowCount();

        if (1 !== $rowCount) {
            throw new Exception("Sequence '" . $this->name . "' not found.");
        }

        $createdId = $this->db->lastInsertId();

        return (int) $createdId;
    }

    /**
     * Returns the current max id.
     * @return ?int
     * @internal
     */
    public function getCurrentId()
    {
        $sql   = 'SELECT value FROM `' . $this->table . '` WHERE name = ?';

        $id = $this->db->fetchOne($sql, array($this->name));

        if (!empty($id) || '0' === $id || 0 === $id) {
            return (int) $id;
        }

        return null;
    }

    private function getTableName($prefix)
    {
        return ($prefix !== null) ? $prefix . self::TABLE_NAME : Common::prefixTable(self::TABLE_NAME);
    }
}
