<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Settings\Storage\Backend;

use Piwik\Concurrency\Lock;
use Piwik\Container\StaticContainer;
use Piwik\Db;

abstract class BaseSettingsTable implements BackendInterface
{
    /**
     * @var Db\AdapterInterface
     */
    protected $db;

    /** @var Lock */
    protected $lock;

    public function __construct()
    {
        $this->lock = StaticContainer::getContainer()->make(
            Lock::class,
            array ('lockKeyStart' => 'PluginSettingsTable')
        );
    }

    protected function initDbIfNeeded()
    {
        if (!isset($this->db)) {
            // we do not want to create a db connection on backend creation
            $this->db = Db::get();
        }
    }

    /**
     * Saves (persists) the current setting values in the database.
     * @param array $values Key/value pairs of setting values to be written
     */
    public function save($values)
    {
        $this->initDbIfNeeded();

        $values = array_filter($values, function($value) {
            return isset($value);
        });

        $table = $this->getTableName();
        $columnNames = '`' . implode('`, `', $this->getColumnNamesToInsert()) . '`';
        $bind = $this->buildVarsToInsert($values);

        // Generate multi-row insert statement - one set of (?, ?, ?, ?, ?) for each row we want to insert
        $sql = "INSERT INTO $table ($columnNames) VALUES ";
        $insertSubclauses = array_fill(0, count($values), "(?, ?, ?, ?, ?)");
        $sql .= implode(', ' , $insertSubclauses);

        $lockKey = $this->getStorageId();
        $this->lock->execute($lockKey, function() use ($sql, $bind) {
            $this->delete();
            $this->db->query($sql, $bind);
        });
    }

    /**
     * Clean the value to be written to DB.
     * Sample usage:
     * <code>list($value, $jsonEncoded) = self::cleanValue($value)
     * @param mixed $value
     * @return array with 2 values - first the cleaned value, and second an int (1 or 0) indicating whether it has
     * been JSON encoded.
     */
    protected static function cleanValue($value)
    {
        if (is_array($value) || is_object($value)) {
            $jsonEncoded = 1;
            $value = json_encode($value);
        } else {
            $jsonEncoded = 0;
            if (is_bool($value)) {
                // we are currently not storing booleans as json as it could result in trouble with the UI and regress
                // preselecting the correct value
                $value = (int) $value;
            }
        }
        return array($value, $jsonEncoded);
    }

    /**
     * Get the prefixed table name.
     * @return string
     */
    protected abstract function getTableName();

    /**
     * Get the list of column names for the SQL insert statement
     * @return array
     */
    protected abstract function getColumnNamesToInsert();

    /**
     * Produce the bind params list for the SQL insert statement.
     * @param array $values     Key/value pairs of settings to be written
     * @return array            Bind params for insert statement
     */
    protected abstract function buildVarsToInsert(array $values);

}