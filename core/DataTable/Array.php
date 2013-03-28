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
 * The DataTable_Array is a way to store an array of dataTable.
 * The Piwik_DataTable_Array implements some of the features of the Piwik_DataTable such as queueFilter, getRowsCount.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Array
{
    /**
     * Array containing the DataTable withing this Piwik_DataTable_Array
     *
     * @var Piwik_DataTable[]
     */
    protected $array = array();

    /**
     * This is the label used to index the tables.
     * For example if the tables are indexed using the timestamp of each period
     * eg. $this->array[1045886960] = new Piwik_DataTable();
     * the keyName would be 'timestamp'.
     *
     * This label is used in the Renderer (it becomes a column name or the XML description tag)
     *
     * @var string
     */
    protected $keyName = 'defaultKeyName';

    /**
     * Returns the keyName string @see self::$keyName
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * Set the keyName @see self::$keyName
     *
     * @param string $name
     */
    public function setKeyName($name)
    {
        $this->keyName = $name;
    }

    /**
     * Returns the number of DataTable in this DataTable_Array
     *
     * @return int
     */
    public function getRowsCount()
    {
        return count($this->array);
    }

    /**
     * Queue a filter to the DataTable_Array will queue this filter to every DataTable of the DataTable_Array.
     *
     * @param string $className   Filter name, eg. Piwik_DataTable_Filter_Limit
     * @param array $parameters  Filter parameters, eg. array( 50, 10 )
     */
    public function queueFilter($className, $parameters = array())
    {
        foreach ($this->array as $table) {
            $table->queueFilter($className, $parameters);
        }
    }

    /**
     * Apply the filters previously queued to each of the DataTable of this DataTable_Array.
     */
    public function applyQueuedFilters()
    {
        foreach ($this->array as $table) {
            $table->applyQueuedFilters();
        }
    }

    /**
     * Apply a filter to all tables in the array
     *
     * @param string $className   Name of filter class
     * @param array $parameters  Filter parameters
     */
    public function filter($className, $parameters = array())
    {
        foreach ($this->array as $id => $table) {
            $table->filter($className, $parameters);
        }
    }

    /**
     * Returns the array of DataTable
     *
     * @return Piwik_DataTable[]
     */
    public function getArray()
    {
        return $this->array;
    }

    /**
     * Returns the table with the specified label.
     *
     * @param string $label
     * @return Piwik_DataTable
     */
    public function getTable($label)
    {
        return $this->array[$label];
    }

    /**
     * Returns the first row
     * This method can be used to treat DataTable and DataTable_Array in the same way
     *
     * @return Piwik_DataTable_Row
     */
    public function getFirstRow()
    {
        foreach ($this->array as $table) {
            $row = $table->getFirstRow();
            if ($row !== false) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Adds a new DataTable to the DataTable_Array
     *
     * @param Piwik_DataTable $table
     * @param string $label  Label used to index this table in the array
     */
    public function addTable($table, $label)
    {
        $this->array[$label] = $table;
    }

    /**
     * Returns a string output of this DataTable_Array (applying the default renderer to every DataTable
     * of this DataTable_Array).
     *
     * @return string
     */
    public function __toString()
    {
        $renderer = new Piwik_DataTable_Renderer_Console();
        $renderer->setTable($this);
        return (string)$renderer;
    }

    /**
     * @see Piwik_DataTable::enableRecursiveSort()
     */
    public function enableRecursiveSort()
    {
        foreach ($this->array as $table) {
            $table->enableRecursiveSort();
        }
    }

    /**
     * Renames the given column
     *
     * @see Piwik_DataTable::renameColumn
     * @param string $oldName
     * @param string $newName
     */
    public function renameColumn($oldName, $newName)
    {
        foreach ($this->array as $table) {
            $table->renameColumn($oldName, $newName);
        }
    }

    /**
     * Deletes the given columns
     *
     * @see Piwik_DataTable::deleteColumns
     * @param array $columns
     */
    public function deleteColumns($columns)
    {
        foreach ($this->array as $table) {
            $table->deleteColumns($columns);
        }
    }

    public function deleteRow($id)
    {
        foreach ($this->array as $table) {
            $table->deleteRow($id);
        }
    }

    /**
     * Deletes the given column
     *
     * @see Piwik_DataTable::deleteColumn
     * @param string $column
     */
    public function deleteColumn($column)
    {
        foreach ($this->array as $table) {
            $table->deleteColumn($column);
        }
    }

    /**
     * Returns the array containing all rows values in all data tables for the requested column
     *
     * @param string $name
     * @return array
     */
    public function getColumn($name)
    {
        $values = array();
        foreach ($this->array as $table) {
            $moreValues = $table->getColumn($name);
            foreach ($moreValues as &$value) {
                $values[] = $value;
            }
        }
        return $values;
    }

    /**
     * Merges the rows of every child DataTable into a new DataTable and
     * returns it. This function will also set the label of the merged rows
     * to the label of the DataTable they were originally from.
     *
     * The result of this function is determined by the type of DataTable
     * this instance holds. If this DataTable_Array instance holds an array
     * of DataTables, this function will transform it from:
     * <code>
     * Label 0:
     *   DataTable(row1)
     * Label 1:
     *   DataTable(row2)
     * </code>
     * to:
     * <code>
     * DataTable(row1[label = 'Label 0'], row2[label = 'Label 1'])
     * </code>
     *
     * If this instance holds an array of DataTable_Arrays, this function will
     * transform it from:
     * <code>
     * Outer Label 0:            // the outer DataTable_Array
     *   Inner Label 0:            // one of the inner DataTable_Arrays
     *     DataTable(row1)
     *   Inner Label 1:
     *     DataTable(row2)
     * Outer Label 1:
     *   Inner Label 0:
     *     DataTable(row3)
     *   Inner Label 1:
     *     DataTable(row4)
     * </code>
     * to:
     * <code>
     * Inner Label 0:
     *   DataTable(row1[label = 'Outer Label 0'], row3[label = 'Outer Label 1'])
     * Inner Label 1:
     *   DataTable(row2[label = 'Outer Label 0'], row4[label = 'Outer Label 1'])
     * </code>
     *
     * In addition, if this instance holds an array of DataTable_Arrays, the
     * metadata of the first child is used as the metadata of the result.
     *
     * This function can be used, for example, to smoosh IndexedBySite archive
     * query results into one DataTable w/ different rows differentiated by site ID.
     *
     * @return Piwik_DataTable|Piwik_DataTable_Array
     */
    public function mergeChildren()
    {
        $firstChild = reset($this->array);

        if ($firstChild instanceof Piwik_DataTable_Array) {
            $result = $firstChild->getEmptyClone();

            foreach ($this->array as $label => $subTableArray) {
                foreach ($subTableArray->array as $innerLabel => $subTable) {
                    if (!isset($result->array[$innerLabel])) {
                        $dataTable = new Piwik_DataTable();
                        $dataTable->metadata = $subTable->metadata;

                        $result->addTable($dataTable, $innerLabel);
                    }

                    $this->copyRowsAndSetLabel($result->array[$innerLabel], $subTable, $label);
                }
            }
        } else {
            $result = new Piwik_DataTable();

            foreach ($this->array as $label => $subTable) {
                $this->copyRowsAndSetLabel($result, $subTable, $label);
            }
        }

        return $result;
    }

    /**
     * Utility function used by mergeChildren. Copies the rows from one table,
     * sets their 'label' columns to a value and adds them to another table.
     *
     * @param Piwik_DataTable $toTable    The table to copy rows to.
     * @param Piwik_DataTable $fromTable  The table to copy rows from.
     * @param string $label      The value to set the 'label' column of every copied row.
     */
    private function copyRowsAndSetLabel($toTable, $fromTable, $label)
    {
        foreach ($fromTable->getRows() as $fromRow) {
            $oldColumns = $fromRow->getColumns();
            unset($oldColumns['label']);

            $columns = array_merge(array('label' => $label), $oldColumns);
            $row = new Piwik_DataTable_Row(array(
                                                Piwik_DataTable_Row::COLUMNS              => $columns,
                                                Piwik_DataTable_Row::METADATA             => $fromRow->getMetadata(),
                                                Piwik_DataTable_Row::DATATABLE_ASSOCIATED => $fromRow->getIdSubDataTable()
                                           ));
            $toTable->addRow($row);
        }
    }

    /**
     * Adds a DataTable to all the tables in this array
     * NOTE: Will only add $tableToSum if the childTable has some rows
     *
     * @param Piwik_DataTable $tableToSum
     */
    public function addDataTable(Piwik_DataTable $tableToSum)
    {
        foreach ($this->getArray() as $childTable) {
            if ($childTable->getRowsCount() > 0) {
                $childTable->addDataTable($tableToSum);
            }
        }
    }

    /**
     * Returns a new DataTable_Array w/ child tables that have had their
     * subtables merged.
     *
     * @see Piwik_DataTable::mergeSubtables
     *
     * @return Piwik_DataTable_Array
     */
    public function mergeSubtables()
    {
        $result = $this->getEmptyClone();
        foreach ($this->array as $label => $childTable) {
            $result->addTable($childTable->mergeSubtables(), $label);
        }
        return $result;
    }

    /**
     * Returns a new DataTable_Array w/o any child DataTables, but with
     * the same key name as this instance.
     *
     * @return Piwik_DataTable_Array
     */
    public function getEmptyClone()
    {
        $newTableArray = new Piwik_DataTable_Array;
        $newTableArray->setKeyName($this->getKeyName());
        return $newTableArray;
    }

    /**
     * Returns the intersection of children's meta data arrays
     *
     * @param string $name The metadata name.
     * @return mixed
     */
    public function getMetadataIntersectArray($name)
    {
        $data = array();
        foreach ($this->getArray() as $childTable) {
            $childData = $childTable->getMetadata($name);
            if (is_array($childData)) {
                $data = array_intersect($data, $childData);
            }
        }
        return array_values($data);
    }

}
