<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Renderer\Console;

/**
 * Stores an array of {@link DataTable}s indexed by one type of {@link DataTable} metadata (such as site ID
 * or period).
 *
 * DataTable Maps are returned on all queries that involve multiple sites and/or multiple
 * periods. The Maps will contain a {@link DataTable} for each site and period combination.
 *
 * The Map implements some {@link DataTable} such as {@link queueFilter()} and {@link getRowsCount}.
 *
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
     * @see self::getKeyName()
     * @var string
     */
    protected $keyName = 'defaultKeyName';

    /**
     * Returns a string description of the data used to index the DataTables.
     *
     * This label is used by DataTable Renderers (it becomes a column name or the XML description tag).
     *
     * @return string eg, `'idSite'`, `'period'`
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * Set the name of they metadata used to index {@link DataTable}s. See {@link getKeyName()}.
     *
     * @param string $name
     */
    public function setKeyName($name)
    {
        $this->keyName = $name;
    }

    /**
     * Returns the number of {@link DataTable}s in this DataTable\Map.
     *
     * @return int
     */
    public function getRowsCount()
    {
        return count($this->getDataTables());
    }

    /**
     * Queue a filter to {@link DataTable} child of contained by this instance.
     *
     * See {@link Piwik\DataTable::queueFilter()} for more information..
     *
     * @param string|Closure $className Filter name, eg. `'Limit'` or a Closure.
     * @param array $parameters Filter parameters, eg. `array(50, 10)`.
     */
    public function queueFilter($className, $parameters = array())
    {
        foreach ($this->getDataTables() as $table) {
            $table->queueFilter($className, $parameters);
        }
    }

    /**
     * Apply the filters previously queued to each DataTable contained by this DataTable\Map.
     */
    public function applyQueuedFilters()
    {
        foreach ($this->getDataTables() as $table) {
            $table->applyQueuedFilters();
        }
    }

    /**
     * Apply a filter to all tables contained by this instance.
     *
     * @param string|Closure $className Name of filter class or a Closure.
     * @param array $parameters Parameters to pass to the filter.
     */
    public function filter($className, $parameters = array())
    {
        foreach ($this->getDataTables() as $table) {
            $table->filter($className, $parameters);
        }
    }

    /**
     * Returns the array of DataTables contained by this class.
     *
     * @return DataTable[]|Map[]
     */
    public function getDataTables()
    {
        return $this->array;
    }

    /**
     * Returns the table with the specific label.
     *
     * @param string $label
     * @return DataTable|Map
     */
    public function getTable($label)
    {
        return $this->array[$label];
    }

    /**
     * Returns the first element in the Map's array.
     *
     * @return DataTable|Map|false
     */
    public function getFirstRow()
    {
        return reset($this->array);
    }

    /**
     * Returns the last element in the Map's array.
     *
     * @return DataTable|Map|false
     */
    public function getLastRow()
    {
        return end($this->array);
    }

    /**
     * Adds a new {@link DataTable} or Map instance to this DataTable\Map.
     *
     * @param DataTable|Map $table
     * @param string $label Label used to index this table in the array.
     */
    public function addTable($table, $label)
    {
        $this->array[$label] = $table;
    }

    /**
     * Returns a string output of this DataTable\Map (applying the default renderer to every {@link DataTable}
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
     * See {@link DataTable::enableRecursiveSort()}.
     */
    public function enableRecursiveSort()
    {
        foreach ($this->getDataTables() as $table) {
            $table->enableRecursiveSort();
        }
    }

    /**
     * Renames the given column in each contained {@link DataTable}.
     *
     * See {@link DataTable::renameColumn()}.
     *
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
     * Deletes the specified columns in each contained {@link DataTable}.
     *
     * See {@link DataTable::deleteColumns()}.
     *
     * @param array $columns The columns to delete.
     * @param bool $deleteRecursiveInSubtables This param is currently not used.
     */
    public function deleteColumns($columns, $deleteRecursiveInSubtables = false)
    {
        foreach ($this->getDataTables() as $table) {
            $table->deleteColumns($columns);
        }
    }

    /**
     * Deletes a table from the array of DataTables.
     *
     * @param string $id The label associated with {@link DataTable}.
     */
    public function deleteRow($id)
    {
        unset($this->array[$id]);
    }

    /**
     * Deletes the given column in every contained {@link DataTable}.
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
     * Returns the array containing all column values in all contained {@link DataTable}s for the requested column.
     *
     * @param string $name The column name.
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
     * Merges the rows of every child {@link DataTable} into a new one and
     * returns it. This function will also set the label of the merged rows
     * to the label of the {@link DataTable} they were originally from.
     *
     * The result of this function is determined by the type of DataTable
     * this instance holds. If this DataTable\Map instance holds an array
     * of DataTables, this function will transform it from:
     *
     *     Label 0:
     *       DataTable(row1)
     *     Label 1:
     *       DataTable(row2)
     *
     * to:
     *
     *     DataTable(row1[label = 'Label 0'], row2[label = 'Label 1'])
     *
     * If this instance holds an array of DataTable\Maps, this function will
     * transform it from:
     *
     *     Outer Label 0:            // the outer DataTable\Map
     *       Inner Label 0:            // one of the inner DataTable\Maps
     *         DataTable(row1)
     *       Inner Label 1:
     *         DataTable(row2)
     *     Outer Label 1:
     *       Inner Label 0:
     *         DataTable(row3)
     *       Inner Label 1:
     *         DataTable(row4)
     *
     * to:
     *
     *     Inner Label 0:
     *       DataTable(row1[label = 'Outer Label 0'], row3[label = 'Outer Label 1'])
     *     Inner Label 1:
     *       DataTable(row2[label = 'Outer Label 0'], row4[label = 'Outer Label 1'])
     *
     * If this instance holds an array of DataTable\Maps, the
     * metadata of the first child is used as the metadata of the result.
     *
     * This function can be used, for example, to smoosh IndexedBySite archive
     * query results into one DataTable w/ different rows differentiated by site ID.
     *
     * Note: This DataTable/Map will be destroyed and will be no longer usable after the tables have been merged into
     *       the new dataTable to reduce memory usage. Destroying all DataTables witihn the Map also seems to fix a
     *       Segmentation Fault that occurred in the AllWebsitesDashboard when having > 16k sites.
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
                        $dataTable->setMetadataValues($subTable->getAllTableMetadata());

                        $result->addTable($dataTable, $innerLabel);
                    }

                    $this->copyRowsAndSetLabel($result->array[$innerLabel], $subTable, $label);
                }
            }
        } else {
            $result = new DataTable();

            foreach ($this->getDataTables() as $label => $subTable) {
                $this->copyRowsAndSetLabel($result, $subTable, $label);
                Common::destroy($subTable);
            }

            $this->array = array();
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
     * Sums a DataTable to all the tables in this array.
     *
     * _Note: Will only add `$tableToSum` if the childTable has some rows._
     *
     * See {@link Piwik\DataTable::addDataTable()}.
     *
     * @param DataTable $tableToSum
     */
    public function addDataTable(DataTable $tableToSum)
    {
        foreach ($this->getDataTables() as $childTable) {
            $childTable->addDataTable($tableToSum);
        }
    }

    /**
     * Returns a new DataTable\Map w/ child tables that have had their
     * subtables merged.
     *
     * See {@link DataTable::mergeSubtables()}.
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
     * Returns the intersection of children's metadata arrays (what they all have in common).
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
     * See {@link DataTable::getColumns()}.
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
