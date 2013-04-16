<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * A DataTable is composed of rows.
 *
 * A row is composed of:
 * - columns often at least a 'label' column containing the description
 *        of the row, and some numeric values ('nb_visits', etc.)
 * - metadata: other information never to be shown as 'columns'
 * - idSubtable: a row can be linked to a SubTable
 *
 * IMPORTANT: Make sure that the column named 'label' contains at least one non-numeric character.
 *            Otherwise the method addDataTable() or sumRow() would fail because they would consider
 *            the 'label' as being a numeric column to sum.
 *
 * PERFORMANCE: Do *not* add new fields except if necessary in this object. New fields will be
 *              serialized and recorded in the DB millions of times. This object size is critical and must be under control.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Row
{
    /**
     * List of columns that cannot be summed. An associative array for speed.
     *
     * @var array
     */
    private static $unsummableColumns = array(
        'label'    => true,
        'full_url' => true // column used w/ old Piwik versions
    );

    /**
     * This array contains the row information:
     * - array indexed by self::COLUMNS contains the columns, pairs of (column names, value)
     * - (optional) array indexed by self::METADATA contains the metadata,  pairs of (metadata name, value)
     * - (optional) integer indexed by self::DATATABLE_ASSOCIATED contains the ID of the Piwik_DataTable associated to this row.
     *   This ID can be used to read the DataTable from the DataTable_Manager.
     *
     * @var array
     * @see constructor for more information
     */
    public $c = array();
    private $subtableIdWasNegativeBeforeSerialize = false;

    // @see sumRow - implementation detail
    public $maxVisitsSummed = 0;

    const COLUMNS = 0;
    const METADATA = 1;
    const DATATABLE_ASSOCIATED = 3;


    /**
     * Efficient load of the Row structure from a well structured php array
     *
     * @param array $row  The row array has the structure
     *                     array(
     *                           Piwik_DataTable_Row::COLUMNS => array(
     *                                                                 'label' => 'Piwik',
     *                                                                 'column1' => 42,
     *                                                                 'visits' => 657,
     *                                                                 'time_spent' => 155744,
     *                                                                 ),
     *                            Piwik_DataTable_Row::METADATA => array(
     *                                                                  'logo' => 'test.png'
     *                                                                  ),
     *                            Piwik_DataTable_Row::DATATABLE_ASSOCIATED => #Piwik_DataTable object
     *                                                                         (but in the row only the ID will be stored)
     *                           )
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
            && $row[self::DATATABLE_ASSOCIATED] instanceof Piwik_DataTable
        ) {
            $this->setSubtable($row[self::DATATABLE_ASSOCIATED]);
        }
    }

    /**
     * Because $this->c[self::DATATABLE_ASSOCIATED] is negative when the table is in memory,
     * we must prior to serialize() call, make sure the ID is saved as positive integer
     *
     * Only serialize the "c" member
     */
    public function __sleep()
    {
        if (!empty($this->c[self::DATATABLE_ASSOCIATED])
            && $this->c[self::DATATABLE_ASSOCIATED] < 0
        ) {
            $this->c[self::DATATABLE_ASSOCIATED] = -1 * $this->c[self::DATATABLE_ASSOCIATED];
            $this->subtableIdWasNegativeBeforeSerialize = true;
        }
        return array('c');
    }

    /**
     * Must be called after the row was serialized and __sleep was called
     *
     */
    public function cleanPostSerialize()
    {
        if ($this->subtableIdWasNegativeBeforeSerialize) {
            $this->c[self::DATATABLE_ASSOCIATED] = -1 * $this->c[self::DATATABLE_ASSOCIATED];
            $this->subtableIdWasNegativeBeforeSerialize = false;
        }
    }

    /**
     * When destroyed, a row destroys its associated subTable if there is one
     */
    public function __destruct()
    {
        if ($this->isSubtableLoaded()) {
            Piwik_DataTable_Manager::getInstance()->deleteTable($this->getIdSubDataTable());
            $this->c[self::DATATABLE_ASSOCIATED] = null;
        }
    }

    /**
     * Applies a basic rendering to the Row and returns the output
     *
     * @return string characterizing the row. Example: - 1 ['label' => 'piwik', 'nb_uniq_visitors' => 1685, 'nb_visits' => 1861, 'nb_actions' => 2271, 'max_actions' => 13, 'sum_visit_length' => 920131, 'bounce_count' => 1599] [] [idsubtable = 1375]
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
     * Deletes the given column
     *
     * @param string $name  Column name
     * @return bool  True on success, false if the column didn't exist
     */
    public function deleteColumn($name)
    {
        if (!array_key_exists($name, $this->c[self::COLUMNS])) {
            return false;
        }
        unset($this->c[self::COLUMNS][$name]);
        return true;
    }

    /**
     * Renames the given column
     *
     * @param string $oldName
     * @param string $newName
     */
    public function renameColumn($oldName, $newName)
    {
        if (isset($this->c[self::COLUMNS][$oldName])) {
            $this->c[self::COLUMNS][$newName] = $this->c[self::COLUMNS][$oldName];
        }
        // outside the if() since we want to delete nulled columns
        unset($this->c[self::COLUMNS][$oldName]);
    }

    /**
     * Returns the given column
     *
     * @param string $name  Column name
     * @return mixed|false  The column value
     */
    public function getColumn($name)
    {
        if (!isset($this->c[self::COLUMNS][$name])) {
            return false;
        }
        return $this->c[self::COLUMNS][$name];
    }

    /**
     * Returns the array of all metadata,
     * or the specified metadata
     *
     * @param string $name  Metadata name
     * @return mixed|array|false
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

    /**
     * Returns the array containing all the columns
     *
     * @return array  Example: array(
     *                              'column1'   => VALUE,
     *                              'label'     => 'www.php.net'
     *                              'nb_visits' => 15894,
     *                              )
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
     * Returns the associated subtable, if one exists.
     *
     * @return Piwik_DataTable|false
     */
    public function getSubtable()
    {
        if ($this->isSubtableLoaded()) {
            return Piwik_DataTable_Manager::getInstance()->getTable($this->getIdSubDataTable());
        }
        return false;
    }

    /**
     * Sums a DataTable to this row subDataTable.
     * If this row doesn't have a SubDataTable yet, we create a new one.
     * Then we add the values of the given DataTable to this row's DataTable.
     *
     * @param Piwik_DataTable $subTable  Table to sum to this row's subDatatable
     * @see Piwik_DataTable::addDataTable() for the algorithm used for the sum
     */
    public function sumSubtable(Piwik_DataTable $subTable)
    {
        if ($this->isSubtableLoaded()) {
            $thisSubTable = $this->getSubtable();
        } else {
            $thisSubTable = new Piwik_DataTable();
            $this->addSubtable($thisSubTable);
        }
        $thisSubTable->setColumnAggregationOperations($subTable->getColumnAggregationOperations());
        $thisSubTable->addDataTable($subTable);
    }


    /**
     * Set a DataTable to be associated to this row.
     * If the row already has a DataTable associated to it, throws an Exception.
     *
     * @param Piwik_DataTable $subTable  DataTable to associate to this row
     * @return Piwik_DataTable Returns $subTable.
     * @throws Exception
     */
    public function addSubtable(Piwik_DataTable $subTable)
    {
        if (!is_null($this->c[self::DATATABLE_ASSOCIATED])) {
            throw new Exception("Adding a subtable to the row, but it already has a subtable associated.");
        }
        return $this->setSubtable($subTable);
    }

    /**
     * Set a DataTable to this row. If there is already
     * a DataTable associated, it is simply overwritten.
     *
     * @param Piwik_DataTable $subTable  DataTable to associate to this row
     * @return Piwik_DataTable Returns $subTable.
     */
    public function setSubtable(Piwik_DataTable $subTable)
    {
        // Hacking -1 to ensure value is negative, so we know the table was loaded
        // @see isSubtableLoaded()
        $this->c[self::DATATABLE_ASSOCIATED] = -1 * $subTable->getId();
        return $subTable;
    }

    /**
     * Returns true if the subtable is currently loaded in memory via DataTable_Manager
     *
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
     * Remove the sub table reference
     */
    public function removeSubtable()
    {
        $this->c[self::DATATABLE_ASSOCIATED] = null;
    }

    /**
     * Set all the columns at once. Overwrites previously set columns.
     *
     * @param array  array(
     *                    'label'       => 'www.php.net'
     *                    'nb_visits'   => 15894,
     *                    )
     */
    public function setColumns($columns)
    {
        $this->c[self::COLUMNS] = $columns;
    }

    /**
     * Set the value $value to the column called $name.
     *
     * @param string $name   name of the column to set
     * @param mixed $value  value of the column to set
     */
    public function setColumn($name, $value)
    {
        $this->c[self::COLUMNS][$name] = $value;
    }

    /**
     * Set the value $value to the metadata called $name.
     *
     * @param string $name   name of the metadata to set
     * @param mixed $value  value of the metadata to set
     */
    public function setMetadata($name, $value)
    {
        $this->c[self::METADATA][$name] = $value;
    }

    /**
     * Deletes the given metadata
     *
     * @param bool|string $name  Meta column name (omit to delete entire metadata)
     * @return bool  True on success, false if the column didn't exist
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
     * Add a new column to the row. If the column already exists, throws an exception
     *
     * @param string $name   name of the column to add
     * @param mixed $value  value of the column to set
     * @throws Exception
     */
    public function addColumn($name, $value)
    {
        if (isset($this->c[self::COLUMNS][$name])) {
//			debug_print_backtrace();
            throw new Exception("Column $name already in the array!");
        }
        $this->c[self::COLUMNS][$name] = $value;
    }

    /**
     * Add columns to the row
     *
     * @param array $columns  Name/Value pairs, e.g., array( name => value , ...)
     *
     * @throws Exception
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
     * Add a new metadata to the row. If the column already exists, throws an exception.
     *
     * @param string $name   name of the metadata to add
     * @param mixed $value  value of the metadata to set
     * @throws Exception
     */
    public function addMetadata($name, $value)
    {
        if (isset($this->c[self::METADATA][$name])) {
            throw new Exception("Metadata $name already in the array!");
        }
        $this->c[self::METADATA][$name] = $value;
    }

    /**
     * Sums the given $row columns values to the existing row' columns values.
     * It will sum only the int or float values of $row.
     * It will not sum the column 'label' even if it has a numeric value.
     * If a given column doesn't exist in $this then it is added with the value of $row.
     * If the column already exists in $this then we have
     *         this.columns[idThisCol] += $row.columns[idThisCol]
     *
     * @param Piwik_DataTable_Row $rowToSum
     * @param bool                $enableCopyMetadata
     * @param array               $aggregationOperations  for columns that should not be summed, determine which
     *                                                    aggregation should be used (min, max).
     *                                                    format: column name => function name
     */
    public function sumRow(Piwik_DataTable_Row $rowToSum, $enableCopyMetadata = true, $aggregationOperations = null)
    {
        foreach ($rowToSum->getColumns() as $columnToSumName => $columnToSumValue) {
            if (!isset(self::$unsummableColumns[$columnToSumName])) // make sure we can add this column
            {
                $thisColumnValue = $this->getColumn($columnToSumName);

                $operation = (is_array($aggregationOperations) && isset($aggregationOperations[$columnToSumName]) ? 
                    strtolower($aggregationOperations[$columnToSumName]) : 'sum');
                
                // max_actions is a core metric that is generated in ArchiveProcess_Day. Therefore, it can be
                // present in any data table and is not part of the $aggregationOperations mechanism.
                if ($columnToSumName == Piwik_Archive::INDEX_MAX_ACTIONS) {
                    $operation = 'max';
                }
                $newValue = $this->getColumnValuesMerged($operation, $thisColumnValue, $columnToSumValue);
                $this->setColumn($columnToSumName, $newValue);
            }
        }

        if ($enableCopyMetadata) {
            $this->sumRowMetadata($rowToSum);
        }
    }

    /**
     *
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
            default:
                $newValue = $this->sumRowArray($thisColumnValue, $columnToSumValue);
                break;
        }
        return $newValue;
    }

    public function sumRowMetadata($rowToSum)
    {
        if (!empty($rowToSum->c[self::METADATA])
            && !$this->isSummaryRow()
        ) {
            // We shall update metadata, and keep the metadata with the _most visits or pageviews_, rather than first or last seen
            $visits = max($rowToSum->getColumn(Piwik_Archive::INDEX_PAGE_NB_HITS) || $rowToSum->getColumn(Piwik_Archive::INDEX_NB_VISITS),
                // Old format pre-1.2, @see also method updateInterestStats()
                $rowToSum->getColumn('nb_actions') || $rowToSum->getColumn('nb_visits'));
            if (($visits && $visits > $this->maxVisitsSummed)
                || empty($this->c[self::METADATA])
            ) {
                $this->maxVisitsSummed = $visits;
                $this->c[self::METADATA] = $rowToSum->c[self::METADATA];
            }
        }
    }

    public function isSummaryRow()
    {
        return $this->getColumn('label') === Piwik_DataTable::LABEL_SUMMARY_ROW;
    }

    /**
     * Helper function: sums 2 values
     *
     * @param number|bool  $thisColumnValue
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

        if (is_array($columnToSumValue)) {
            if ($thisColumnValue == false) {
                return $columnToSumValue;
            }
            $newValue = $thisColumnValue;
            foreach ($columnToSumValue as $arrayIndex => $arrayValue) {
                if (!isset($newValue[$arrayIndex])) {
                    $newValue[$arrayIndex] = false;
                }
                $newValue[$arrayIndex] = $this->sumRowArray($newValue[$arrayIndex], $arrayValue);
            }
            return $newValue;
        }

        if (is_string($columnToSumValue)) {
            if ($thisColumnValue === false) {
                return $columnToSumValue;
            } else if ($columnToSumValue === false) {
                return $thisColumnValue;
            } else {
                throw new Exception("Trying to add two strings values in DataTable_Row::sumRowArray: "
                    . "'$thisColumnValue' + '$columnToSumValue'");
            }
        }

        return 0;
    }

    /**
     * Helper function to compare array elements
     *
     * @param mixed $elem1
     * @param mixed $elem2
     * @return bool
     */
    static public function compareElements($elem1, $elem2)
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
     * Helper function to test if two rows are equal.
     *
     * Two rows are equal
     * - if they have exactly the same columns / metadata
     * - if they have a subDataTable associated, then we check that both of them are the same.
     *
     * @param Piwik_DataTable_Row $row1  first to compare
     * @param Piwik_DataTable_Row $row2  second to compare
     * @return bool
     */
    static public function isEqual(Piwik_DataTable_Row $row1, Piwik_DataTable_Row $row2)
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
            if (!Piwik_DataTable::isEqual($subtable1, $subtable2)) {
                return false;
            }
        }
        return true;
    }
}
