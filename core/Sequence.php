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
use Piwik\Db\AdapterInterface;

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
    private $name;

    /**
     * @var AdapterInterface
     */
    private $db;

    /**
     * The name of the table or sequence you want to get an id for.
     *
     * @param string $name eg 'archive_numeric_2014_11'
     * @param AdapterInterface $db You can optionally pass a DB adapter to make it work against another database.
     */
    public function __construct($name, $db = null)
    {
        $this->name = $name;
        $this->db = $db ?: Db::get();
    }

    private function getTableName()
    {
        return Common::prefixTable('sequence');
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

        $table = $this->getTableName();

        $this->db->insert($table, array('name' => $this->name, 'value' => $initialValue));

        return $initialValue;
    }

    /**
     * Returns true if the sequence exist.
     *
     * @return bool
     */
    public function exists()
    {
        $query = $this->db->query('SELECT * FROM ' . $this->getTableName() . ' WHERE name = ?', $this->name);

        return $query->rowCount() > 0;
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
        $table = $this->getTableName();
        $sql   = 'UPDATE ' . $table . ' SET value = LAST_INSERT_ID(value + 1) WHERE name = ?';

        $result   = $this->db->query($sql, array($this->name));
        $rowCount = $result->rowCount();

        if (1 !== $rowCount) {
            throw new Exception("Sequence '" . $this->name . "' not found.");
        }

        $createdId = $this->db->lastInsertId();

        return (int) $createdId;
    }

    /**
     * Returns the current max id.
     * @return int
     * @internal
     */
    public function getCurrentId()
    {
        $table = $this->getTableName();
        $sql   = 'SELECT value FROM ' . $table . ' WHERE name = ?';

        $id = $this->db->fetchOne($sql, array($this->name));

        if (!empty($id) || '0' === $id || 0 === $id) {
            return (int) $id;
        }
    }
}
