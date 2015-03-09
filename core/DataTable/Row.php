<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable;

use Exception;
use Piwik\DataTable;
use Piwik\Log;
use Piwik\Metrics;

/**
 * This is what a {@link Piwik\DataTable} is composed of.
 *
 * DataTable rows contain columns, metadata and a subtable ID. Columns and metadata
 * are stored as an array of name => value mappings.
 *
 *
 * @api
 */
class Row implements \ArrayAccess, \IteratorAggregate
{
    /**
     * List of columns that cannot be summed. An associative array for speed.
     *
     * @var array
     */
    private static $unsummableColumns = array(
        'label'    => true,
        'full_url' => true // column used w/ old Piwik versions,
    );

    /**
     * This array contains the row information:
     * - array indexed by self::COLUMNS contains the columns, pairs of (column names, value)
     * - (optional) array indexed by self::METADATA contains the metadata,  pairs of (metadata name, value)
     * - (optional) integer indexed by self::DATATABLE_ASSOCIATED contains the ID of the DataTable associated to this row.
     *   This ID can be used to read the DataTable from the DataTable_Manager.
     *
     * @var array
     * @see constructor for more information
     * @ignore
     */
    public $c = array();

    private $subtableIdWasNegativeBeforeSerialize = false;

    // @see sumRow - implementation detail
    public $maxVisitsSummed = 0;

    const COLUMNS = 0;
    const METADATA = 1;
    const DATATABLE_ASSOCIATED = 3;

    /**
     * Constructor.
     *
     * @param array $row An array with the following structure:
     *
     *                       array(
     *                           Row::COLUMNS => array('label' => 'Piwik',
     *                                                 'column1' => 42,
     *                                                 'visits' => 657,
     *                                                 'time_spent' => 155744),
     *                           Row::METADATA => array('logo' => 'test.png'),
     *                           Row::DATATABLE_ASSOCIATED => $subtable // DataTable object
     *                                                                  // (but in the row only the ID will be stored)
     *                       )
     */
    public function __construct($row = array())
    {
        $this->c[self::COLUMNS] = array();
        $this->c[self::METADATA] = array();
        $this->c[self::DATATABLE_ASSOCIATED] = null;

        if (isset($row[self::COLUMNS])) {
            $this->c[self::COLUMNS] = $row[self::COLUMNS];
        }
        if (isset($row[self::METADATA])) {
            $this->c[self::METADATA] = $row[self::METADATA];
        }
        if (isset($row[self::DATATABLE_ASSOCIATED])
            && $row[self::DATATABLE_ASSOCIATED] instanceof DataTable
        ) {
            $this->setSubtable($row[self::DATATABLE_ASSOCIATED]);
        }
    }

    /**
     * Because $this->c[self::DATATABLE_ASSOCIATED] is negative when the table is in memory,
     * we must prior to serialize() call, make sure the ID is saved as positive integer
     *
     * Only serialize the "c" member
     * @ignore
     */
    public function __sleep()
    {
        if (!empty($this->c[self::DATATABLE_ASSOCIATED])
            && $this->c[self::DATATABLE_ASSOCIATED] < 0
        ) {
            $this->switchFlagSubtableIsLoaded();
            $this->subtableIdWasNegativeBeforeSerialize = true;
        }
        return array('c');
    }

    /**
     * Must be called after the row was serialized and __sleep was called.
     * @ignore
     */
    public function cleanPostSerialize()
    {
        if ($this->subtableIdWasNegativeBeforeSerialize) {
            $this->switchFlagSubtableIsLoaded();
            $this->subtableIdWasNegativeBeforeSerialize = false;
        }
    }

    /**
     * note: also used by unit tests
     * @ignore
     */
    public function switchFlagSubtableIsLoaded()
    {
        $this->c[self::DATATABLE_ASSOCIATED] = -1 * $this->c[self::DATATABLE_ASSOCIATED];
    }

    /**
     * When destroyed, a row destroys its associated subtable if there is one.
     * @ignore
     */
    public function __destruct()
    {
        if ($this->isSubtableLoaded()) {
            Manager::getInstance()->deleteTable($this->getIdSubDataTable());
            $this->c[self::DATATABLE_ASSOCIATED] = null;
        }
    }

    /**
     * Applies a basic rendering to the Row and returns the output.
     *
     * @return string describing the row. Example:
     *                "- 1 ['label' => 'piwik', 'nb_uniq_visitors' => 1685, 'nb_visits' => 1861] [] [idsubtable = 1375]"
     */
    public function __toString()
    {
        $columns = array();
        foreach ($this->getColumns() as $column => $value) {
            if (is_string($value)) $value = "'$value'";
            elseif (is_array($value)) $value = var_export($value, true);
            $columns[] = "'$column' => $value";
        }
        $columns = implode(", ", $columns);
        $metadata = array();
        foreach ($this->getMetadata() as $name => $value) {
            if (is_string($value)) $value = "'$value'";
            elseif (is_array($value)) $value = var_export($value, true);
            $metadata[] = "'$name' => $value";
        }
        $metadata = implode(", ", $metadata);
        $output = "# [" . $columns . "] [" . $metadata . "] [idsubtable = " . $this->getIdSubDataTable() . "]<br />\n";
        return $output;
    }

    /**
     * Deletes the given column.
     *
     * @param string $name The column name.
     * @return bool `true` on success, `false` if the column does not exist.
     */
    public function deleteColumn($name)
    {
        if (!$this->hasColumn($name)) {
            return false;
        }
        unset($this->c[self::COLUMNS][$name]);
        return true;
    }

    /**
     * Renames a column.
     *
     * @param string $oldName The current name of the column.
     * @param string $newName The new name of the column.
     */
    public function renameColumn($oldName, $newName)
    {
        if (isset($this->c[self::COLUMNS][$oldName])) {
            $this->c[self::COLUMNS][$newName] = $this->c[self::COLUMNS][$oldName];
        }
        // outside the if () since we want to delete nulled columns
        unset($this->c[self::COLUMNS][$oldName]);
    }

    /**
     * Returns a column by name.
     *
     * @param string $name The column name.
     * @return mixed|false  The column value or false if it doesn't exist.
     */
    public function getColumn($name)
    {
        if (!isset($this->c[self::COLUMNS][$name])) {
            return false;
        }

        return $this->c[self::COLUMNS][$name];
    }

    /**
     * Returns the array of all metadata, or one requested metadata value.
     *
     * @param string|null $name The name of the metadata to return or null to return all metadata.
     * @return mixed
     */
    public function getMetadata($name = null)
    {
        if (is_null($name)) {
            return $this->c[self::METADATA];
        }
        if (!isset($this->c[self::METADATA][$name])) {
            return false;
        }
        return $this->c[self::METADATA][$name];
    }

    private function getColumnsRaw()
    {
        return $this->c[self::COLUMNS];
    }

    /**
     * Returns true if a column having the given name is already registered. The value will not be evaluated, it will
     * just check whether a column exists independent of its value.
     *
     * @param string $name
     * @return bool
     */
    public function hasColumn($name)
    {
        return array_key_exists($name, $this->c[self::COLUMNS]);
    }

    /**
     * Returns the array containing all the columns.
     *
     * @return array  Example:
     *
     *                    array(
     *                        'column1'   => VALUE,
     *                        'label'     => 'www.php.net'
     *                        'nb_visits' => 15894,
     *                    )
     */
    public function getColumns()
    {
        return $this->c[self::COLUMNS];
    }

    /**
     * Returns the ID of the subDataTable.
     * If there is no such a table, returns null.
     *
     * @return int|null
     */
    public function getIdSubDataTable()
    {
        return !is_null($this->c[self::DATATABLE_ASSOCIATED])
            // abs() is to ensure we return a positive int, @see isSubtableLoaded()
            ? abs($this->c[self::DATATABLE_ASSOCIATED])
            : null;
    }

    /**
     * Returns the associated subtable, if one exists. Returns `false` if none exists.
     *
     * @return DataTable|bool
     */
    public function getSubtable()
    {
        if ($this->isSubtableLoaded()) {
            try {
                return Manager::getInstance()->getTable($this->getIdSubDataTable());
            } catch(TableNotFoundException $e) {
                // edge case
            }
        }
        return false;
    }

    /**
     * Sums a DataTable to this row's subtable. If this row has no subtable a new
     * one is created.
     *
     * See {@link Piwik\DataTable::addDataTable()} to learn how DataTables are summed.
     *
     * @param DataTable $subTable Table to sum to this row's subtable.
     */
    public function sumSubtable(DataTable $subTable)
    {
        if ($this->isSubtableLoaded()) {
            $thisSubTable = $this->getSubtable();
        } else {
            $this->warnIfSubtableAlreadyExists();

            $thisSubTable = new DataTable();
            $this->setSubtable($thisSubTable);
        }
        $columnOps = $subTable->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME);
        $thisSubTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnOps);
        $thisSubTable->addDataTable($subTable);
    }

    /**
     * Attaches a subtable to this row, overwriting the existing subtable,
     * if any.
     *
     * @param DataTable $subTable DataTable to associate to this row.
     * @return DataTable Returns `$subTable`.
     */
    public function setSubtable(DataTable $subTable)
    {
        // Hacking -1 to ensure value is negative, so we know the table was loaded
        // @see isSubtableLoaded()
        $this->c[self::DATATABLE_ASSOCIATED] = -1 * $subTable->getId();
        return $subTable;
    }

    /**
     * Returns `true` if the subtable is currently loaded in memory via {@link Piwik\DataTable\Manager}.
     *
     * @return bool
     */
    public function isSubtableLoaded()
    {
        // self::DATATABLE_ASSOCIATED are set as negative values,
        // as a flag to signify that the subtable is loaded in memory
        return !is_null($this->c[self::DATATABLE_ASSOCIATED])
        && $this->c[self::DATATABLE_ASSOCIATED] < 0;
    }

    /**
     * Removes the subtable reference.
     */
    public function removeSubtable()
    {
        $this->c[self::DATATABLE_ASSOCIATED] = null;
    }

    /**
     * Set all the columns at once. Overwrites **all** previously set columns.
     *
     * @param array eg, `array('label' => 'www.php.net', 'nb_visits' => 15894)`
     */
    public function setColumns($columns)
    {
        $this->c[self::COLUMNS] = $columns;
    }

    /**
     * Set the value `$value` to the column called `$name`.
     *
     * @param string $name name of the column to set.
     * @param mixed $value value of the column to set.
     */
    public function setColumn($name, $value)
    {
        $this->c[self::COLUMNS][$name] = $value;
    }

    /**
     * Set the value `$value` to the metadata called `$name`.
     *
     * @param string $name name of the metadata to set.
     * @param mixed $value value of the metadata to set.
     */
    public function setMetadata($name, $value)
    {
        $this->c[self::METADATA][$name] = $value;
    }

    /**
     * Deletes one metadata value or all metadata values.
     *
     * @param bool|string $name Metadata name (omit to delete entire metadata).
     * @return bool `true` on success, `false` if the column didn't exist
     */
    public function deleteMetadata($name = false)
    {
        if ($name === false) {
            $this->c[self::METADATA] = array();
            return true;
        }
        if (!isset($this->c[self::METADATA][$name])) {
            return false;
        }
        unset($this->c[self::METADATA][$name]);
        return true;
    }

    /**
     * Add a new column to the row. If the column already exists, throws an exception.
     *
     * @param string $name name of the column to add.
     * @param mixed $value value of the column to set or a PHP callable.
     * @throws Exception if the column already exists.
     */
    public function addColumn($name, $value)
    {
        if (isset($this->c[self::COLUMNS][$name])) {
            throw new Exception("Column $name already in the array!");
        }
        $this->setColumn($name, $value);
    }

    /**
     * Add many columns to this row.
     *
     * @param array $columns Name/Value pairs, e.g., `array('name' => $value , ...)`
     * @throws Exception if any column name does not exist.
     * @return void
     */
    public function addColumns($columns)
    {
        foreach ($columns as $name => $value) {
            try {
                $this->addColumn($name, $value);
            } catch (Exception $e) {
            }
        }

        if (!empty($e)) {
            throw $e;
        }
    }

    /**
     * Add a new metadata to the row. If the metadata already exists, throws an exception.
     *
     * @param string $name name of the metadata to add.
     * @param mixed $value value of the metadata to set.
     * @throws Exception if the metadata already exists.
     */
    public function addMetadata($name, $value)
    {
        if (isset($this->c[self::METADATA][$name])) {
            throw new Exception("Metadata $name already in the array!");
        }
        $this->setMetadata($name, $value);
    }

    private function isSummableColumn($columnName)
    {
        return empty(self::$unsummableColumns[$columnName]);
    }

    /**
     * Sums the given `$rowToSum` columns values to the existing row column values.
     * Only the int or float values will be summed. Label columns will be ignored
     * even if they have a numeric value.
     *
     * Columns in `$rowToSum` that don't exist in `$this` are added to `$this`.
     *
     * @param \Piwik\DataTable\Row $rowToSum The row to sum to this row.
     * @param bool $enableCopyMetadata Whether metadata should be copied or not.
     * @param array|bool $aggregationOperations for columns that should not be summed, determine which
     *                                     aggregation should be used (min, max). format:
     *                                     `array('column name' => 'function name')`
     * @throws Exception
     */
    public function sumRow(Row $rowToSum, $enableCopyMetadata = true, $aggregationOperations = false)
    {
        foreach ($rowToSum->getColumnsRaw() as $columnToSumName => $columnToSumValue) {
            if (!$this->isSummableColumn($columnToSumName)) {
                continue;
            }

            $thisColumnValue = $this->getColumn($columnToSumName);

            $operation = 'sum';
            if (is_array($aggregationOperations) && isset($aggregationOperations[$columnToSumName])) {
                $operation = strtolower($aggregationOperations[$columnToSumName]);
            }

            // max_actions is a core metric that is generated in ArchiveProcess_Day. Therefore, it can be
            // present in any data table and is not part of the $aggregationOperations mechanism.
            if ($columnToSumName == Metrics::INDEX_MAX_ACTIONS) {
                $operation = 'max';
            }
            if (empty($operation)) {
                throw new Exception("Unknown aggregation operation for column $columnToSumName.");
            }

            $newValue = $this->getColumnValuesMerged($operation, $thisColumnValue, $columnToSumValue);

            $this->setColumn($columnToSumName, $newValue);
        }

        if ($enableCopyMetadata) {
            $this->sumRowMetadata($rowToSum);
        }
    }

    /**
     */
    private function getColumnValuesMerged($operation, $thisColumnValue, $columnToSumValue)
    {
        switch ($operation) {
            case 'skip':
                $newValue = null;
                break;
            case 'max':
                $newValue = max($thisColumnValue, $columnToSumValue);
                break;
            case 'min':
                if (!$thisColumnValue) {
                    $newValue = $columnToSumValue;
                } else if (!$columnToSumValue) {
                    $newValue = $thisColumnValue;
                } else {
                    $newValue = min($thisColumnValue, $columnToSumValue);
                }
                break;
            case 'sum':
                $newValue = $this->sumRowArray($thisColumnValue, $columnToSumValue);
                break;
            default:
                throw new Exception("Unknown operation '$operation'.");
        }
        return $newValue;
    }

    /**
     * Sums the metadata in `$rowToSum` with the metadata in `$this` row.
     *
     * @param Row $rowToSum
     */
    public function sumRowMetadata($rowToSum)
    {
        if (!empty($rowToSum->c[self::METADATA])
            && !$this->isSummaryRow()
        ) {
            // We shall update metadata, and keep the metadata with the _most visits or pageviews_, rather than first or last seen
            $visits = max($rowToSum->getColumn(Metrics::INDEX_PAGE_NB_HITS) || $rowToSum->getColumn(Metrics::INDEX_NB_VISITS),
                // Old format pre-1.2, @see also method doSumVisitsMetrics()
                $rowToSum->getColumn('nb_actions') || $rowToSum->getColumn('nb_visits'));
            if (($visits && $visits > $this->maxVisitsSummed)
                || empty($this->c[self::METADATA])
            ) {
                $this->maxVisitsSummed = $visits;
                $this->c[self::METADATA] = $rowToSum->c[self::METADATA];
            }
        }
    }

    /**
     * Returns `true` if this row is the summary row, `false` if otherwise. This function
     * depends on the label of the row, and so, is not 100% accurate.
     *
     * @return bool
     */
    public function isSummaryRow()
    {
        return $this->getColumn('label') === DataTable::LABEL_SUMMARY_ROW;
    }

    /**
     * Helper function: sums 2 values
     *
     * @param number|bool $thisColumnValue
     * @param number|array $columnToSumValue
     *
     * @throws Exception
     * @return array|int
     */
    protected function sumRowArray($thisColumnValue, $columnToSumValue)
    {
        if (is_numeric($columnToSumValue)) {
            if ($thisColumnValue === false) {
                $thisColumnValue = 0;
            }
            return $thisColumnValue + $columnToSumValue;
        }

        if ($columnToSumValue === false) {
            return $thisColumnValue;
        }

        if ($thisColumnValue === false) {
            return $columnToSumValue;
        }

        if (is_array($columnToSumValue)) {
            $newValue = $thisColumnValue;
            foreach ($columnToSumValue as $arrayIndex => $arrayValue) {
                if (!isset($newValue[$arrayIndex])) {
                    $newValue[$arrayIndex] = false;
                }
                $newValue[$arrayIndex] = $this->sumRowArray($newValue[$arrayIndex], $arrayValue);
            }
            return $newValue;
        }

        $this->warnWhenSummingTwoStrings($thisColumnValue, $columnToSumValue);

        return 0;
    }

    /**
     * Helper function to compare array elements
     *
     * @param mixed $elem1
     * @param mixed $elem2
     * @return bool
     * @ignore
     */
    public static function compareElements($elem1, $elem2)
    {
        if (is_array($elem1)) {
            if (is_array($elem2)) {
                return strcmp(serialize($elem1), serialize($elem2));
            }
            return 1;
        }
        if (is_array($elem2))
            return -1;

        if ((string)$elem1 === (string)$elem2)
            return 0;

        return ((string)$elem1 > (string)$elem2) ? 1 : -1;
    }

    /**
     * Helper function that tests if two rows are equal.
     *
     * Two rows are equal if:
     *
     * - they have exactly the same columns / metadata
     * - they have a subDataTable associated, then we check that both of them are the same.
     *
     * Column order is not important.
     *
     * @param \Piwik\DataTable\Row $row1 first to compare
     * @param \Piwik\DataTable\Row $row2 second to compare
     * @return bool
     */
    public static function isEqual(Row $row1, Row $row2)
    {
        //same columns
        $cols1 = $row1->getColumns();
        $cols2 = $row2->getColumns();

        $diff1 = array_udiff($cols1, $cols2, array(__CLASS__, 'compareElements'));
        $diff2 = array_udiff($cols2, $cols1, array(__CLASS__, 'compareElements'));

        if ($diff1 != $diff2) {
            return false;
        }

        $dets1 = $row1->getMetadata();
        $dets2 = $row2->getMetadata();

        ksort($dets1);
        ksort($dets2);

        if ($dets1 != $dets2) {
            return false;
        }

        // either both are null
        // or both have a value
        if (!(is_null($row1->getIdSubDataTable())
            && is_null($row2->getIdSubDataTable())
        )
        ) {
            $subtable1 = $row1->getSubtable();
            $subtable2 = $row2->getSubtable();
            if (!DataTable::isEqual($subtable1, $subtable2)) {
                return false;
            }
        }
        return true;
    }

    public function offsetExists($offset)
    {
        return $this->hasColumn($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getColumn($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->setColumn($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->deleteColumn($offset);
    }

    public function getIterator() {
        return new \ArrayIterator($this->c[self::COLUMNS]);
    }

    private function warnIfSubtableAlreadyExists()
    {
        if (!is_null($this->c[self::DATATABLE_ASSOCIATED])) {
            Log::warning(
                "Row with label '%s' (columns = %s) has already a subtable id=%s but it was not loaded - overwriting the existing sub-table.",
                $this->getColumn('label'),
                implode(", ", $this->getColumns()),
                $this->getIdSubDataTable()
            );
        }
    }

    protected function warnWhenSummingTwoStrings($thisColumnValue, $columnToSumValue)
    {
        if (is_string($columnToSumValue)) {
            Log::warning(
                "Trying to add two strings in DataTable\Row::sumRowArray: %s + %s for row %s",
                $thisColumnValue,
                $columnToSumValue,
                $this->__toString()
            );
        }
    }

}
