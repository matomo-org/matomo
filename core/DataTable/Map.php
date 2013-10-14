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
namespace Piwik\DataTable;

use Piwik\DataTable;
use Piwik\DataTable\Renderer\Console;

/**
 * The DataTable Map is a way to store an array of dataTable.
 * The Map implements some of the features of the DataTable such as queueFilter, getRowsCount.
 *
 * @package Piwik
 * @subpackage DataTable
 *
 * @api
 */
class Map implements DataTableInterface
{
    /**
     * Array containing the DataTable withing this Set
     *
     * @var DataTable[]
     */
    protected $array = array();

    /**
     * This is the label used to index the tables.
     * For example if the tables are indexed using the timestamp of each period
     * eg. $this->array[1045886960] = new DataTable();
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
     * Returns the number of DataTable in this DataTable\Map
     *
     * @return int
     */
    public function getRowsCount()
    {
        return count($this->getDataTables());
    }

    /**
     * Queue a filter to the DataTable\Map will queue this filter to every DataTable of the DataTable\Map.
     *
     * @param string $className Filter name, eg. Limit
     * @param array $parameters Filter parameters, eg. array( 50, 10 )
     */
    public function queueFilter($className, $parameters = array())
    {
        foreach ($this->getDataTables() as $table) {
            $table->queueFilter($className, $parameters);
        }
    }

    /**
     * Apply the filters previously queued to each of the DataTable of this DataTable\Map.
     */
    public function applyQueuedFilters()
    {
        foreach ($this->getDataTables() as $table) {
            $table->applyQueuedFilters();
        }
    }

    /**
     * Apply a filter to all tables in the array
     *
     * @param string $className Name of filter class
     * @param array $parameters Filter parameters
     */
    public function filter($className, $parameters = array())
    {
        foreach ($this->getDataTables() as $id => $table) {
            $table->filter($className, $parameters);
        }
    }

    /**
     * Returns the array of DataTable
     *
     * @return DataTable[]
     */
    public function getDataTables()
    {
        return $this->array;
    }

    /**
     * Returns the table with the specified label.
     *
     * @param string $label
     * @return DataTable
     */
    public function getTable($label)
    {
        return $this->array[$label];
    }

    /**
     * Returns the first row
     * This method can be used to treat DataTable and DataTable\Map in the same way
     *
     * @return Row
     */
    public function getFirstRow()
    {
        foreach ($this->getDataTables() as $table) {
            $row = $table->getFirstRow();
            if ($row !== false) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Adds a new DataTable to the DataTable\Map
     *
     * @param DataTable $table
     * @param string $label Label used to index this table in the array
     */
    public function addTable($table, $label)
    {
        $this->array[$label] = $table;
    }

    /**
     * Returns a string output of this DataTable\Map (applying the default renderer to every DataTable
     * of this DataTable\Map).
     *
     * @return string
     */
    public function __toString()
    {
        $renderer = new Console();
        $renderer->setTable($this);
        return (string)$renderer;
    }

    /**
     * @see DataTable::enableRecursiveSort()
     */
    public function enableRecursiveSort()
    {
        foreach ($this->getDataTables() as $table) {
            $table->enableRecursiveSort();
        }
    }

    /**
     * Renames the given column
     *
     * @see DataTable::renameColumn
     * @param string $oldName
     * @param string $newName
     */
    public function renameColumn($oldName, $newName)
    {
        foreach ($this->getDataTables() as $table) {
            $table->renameColumn($oldName, $newName);
        }
    }

    /**
     * Deletes the given columns
     *
     * @see DataTable::deleteColumns
     * @param array $columns
     * @param bool $deleteRecursiveInSubtables This param is currently not used
     */
    public function deleteColumns($columns, $deleteRecursiveInSubtables = false)
    {
        foreach ($this->getDataTables() as $table) {
            $table->deleteColumns($columns);
        }
    }

    public function deleteRow($id)
    {
        foreach ($this->getDataTables() as $table) {
            $table->deleteRow($id);
        }
    }

    /**
     * Deletes the given column
     *
     * @see DataTable::deleteColumn
     * @param string $name
     */
    public function deleteColumn($name)
    {
        foreach ($this->getDataTables() as $table) {
            $table->deleteColumn($name);
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
        foreach ($this->getDataTables() as $table) {
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
     * this instance holds. If this DataTable\Map instance holds an array
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
     * If this instance holds an array of DataTable\Maps, this function will
     * transform it from:
     * <code>
     * Outer Label 0:            // the outer DataTable\Map
     *   Inner Label 0:            // one of the inner DataTable\Maps
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
     * In addition, if this instance holds an array of DataTable\Maps, the
     * metadata of the first child is used as the metadata of the result.
     *
     * This function can be used, for example, to smoosh IndexedBySite archive
     * query results into one DataTable w/ different rows differentiated by site ID.
     *
     * @return DataTable|Map
     */
    public function mergeChildren()
    {
        $firstChild = reset($this->array);

        if ($firstChild instanceof Map) {
            $result = $firstChild->getEmptyClone();

            /** @var $subDataTableMap Map */
            foreach ($this->getDataTables() as $label => $subDataTableMap) {
                foreach ($subDataTableMap->getDataTables() as $innerLabel => $subTable) {
                    if (!isset($result->array[$innerLabel])) {
                        $dataTable = new DataTable();
                        $dataTable->metadata = $subTable->metadata;

                        $result->addTable($dataTable, $innerLabel);
                    }

                    $this->copyRowsAndSetLabel($result->array[$innerLabel], $subTable, $label);
                }
            }
        } else {
            $result = new DataTable();

            foreach ($this->getDataTables() as $label => $subTable) {
                $this->copyRowsAndSetLabel($result, $subTable, $label);
            }
        }

        return $result;
    }

    /**
     * Utility function used by mergeChildren. Copies the rows from one table,
     * sets their 'label' columns to a value and adds them to another table.
     *
     * @param DataTable $toTable The table to copy rows to.
     * @param DataTable $fromTable The table to copy rows from.
     * @param string $label The value to set the 'label' column of every copied row.
     */
    private function copyRowsAndSetLabel($toTable, $fromTable, $label)
    {
        foreach ($fromTable->getRows() as $fromRow) {
            $oldColumns = $fromRow->getColumns();
            unset($oldColumns['label']);

            $columns = array_merge(array('label' => $label), $oldColumns);
            $row = new Row(array(
                                Row::COLUMNS              => $columns,
                                Row::METADATA             => $fromRow->getMetadata(),
                                Row::DATATABLE_ASSOCIATED => $fromRow->getIdSubDataTable()
                           ));
            $toTable->addRow($row);
        }
    }

    /**
     * Adds a DataTable to all the tables in this array
     * NOTE: Will only add $tableToSum if the childTable has some rows
     *
     * @param DataTable $tableToSum
     */
    public function addDataTable(DataTable $tableToSum)
    {
        foreach ($this->getDataTables() as $childTable) {
            if ($childTable->getRowsCount() > 0) {
                $childTable->addDataTable($tableToSum);
            }
        }
    }

    /**
     * Returns a new DataTable\Map w/ child tables that have had their
     * subtables merged.
     *
     * @see DataTable::mergeSubtables
     *
     * @return Map
     */
    public function mergeSubtables()
    {
        $result = $this->getEmptyClone();
        foreach ($this->getDataTables() as $label => $childTable) {
            $result->addTable($childTable->mergeSubtables(), $label);
        }
        return $result;
    }

    /**
     * Returns a new DataTable\Map w/o any child DataTables, but with
     * the same key name as this instance.
     *
     * @return Map
     */
    public function getEmptyClone()
    {
        $dataTableMap = new Map;
        $dataTableMap->setKeyName($this->getKeyName());
        return $dataTableMap;
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
        foreach ($this->getDataTables() as $childTable) {
            $childData = $childTable->getMetadata($name);
            if (is_array($childData)) {
                $data = array_intersect($data, $childData);
            }
        }
        return array_values($data);
    }

    /**
     * @see DataTable::getColumns()
     *
     * @return array
     */
    public function getColumns()
    {
        foreach ($this->getDataTables() as $childTable) {
            if ($childTable->getRowsCount() > 0) {
                return $childTable->getColumns();
            }
        }
        return array();
    }
}
