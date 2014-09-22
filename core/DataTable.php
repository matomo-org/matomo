<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Closure;
use Exception;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Renderer\Html;
use Piwik\DataTable\Row;
use Piwik\DataTable\Row\DataTableSummaryRow;
use Piwik\DataTable\Simple;
use Piwik\DataTable\TableNotFoundException;
use ReflectionClass;

/**
 * @see Common::destroy()
 */
require_once PIWIK_INCLUDE_PATH . '/core/Common.php';

/**
 * The primary data structure used to store analytics data in Piwik.
 *
 * <a name="class-desc-the-basics"></a>
 * ### The Basics
 *
 * DataTables consist of rows and each row consists of columns. A column value can be
 * a numeric, a string or an array.
 *
 * Every row has an ID. The ID is either the index of the row or {@link ID_SUMMARY_ROW}.
 *
 * DataTables are hierarchical data structures. Each row can also contain an additional
 * nested sub-DataTable (commonly referred to as a 'subtable').
 *
 * Both DataTables and DataTable rows can hold **metadata**. _DataTable metadata_ is information
 * regarding all the data, such as the site or period that the data is for. _Row metadata_
 * is information regarding that row, such as a browser logo or website URL.
 *
 * Finally, all DataTables contain a special _summary_ row. This row, if it exists, is
 * always at the end of the DataTable.
 *
 * ### Populating DataTables
 *
 * Data can be added to DataTables in three different ways. You can either:
 *
 * 1. create rows one by one and add them through {@link addRow()} then truncate if desired,
 * 2. create an array of DataTable\Row instances or an array of arrays and add them using
 *    {@link addRowsFromArray()} or {@link addRowsFromSimpleArray()}
 *    then truncate if desired,
 * 3. or set the maximum number of allowed rows (with {@link setMaximumAllowedRows()})
 *    and add rows one by one.
 *
 * If you want to eventually truncate your data (standard practice for all Piwik plugins),
 * the third method is the most memory efficient. It is, unfortunately, not always possible
 * to use since it requires that the data be sorted before adding.
 *
 * ### Manipulating DataTables
 *
 * There are two ways to manipulate a DataTable. You can either:
 *
 * 1. manually iterate through each row and manipulate the data,
 * 2. or you can use predefined filters.
 *
 * A filter is a class that has a 'filter' method which will manipulate a DataTable in
 * some way. There are several predefined Filters that allow you to do common things,
 * such as,
 *
 * - add a new column to each row,
 * - add new metadata to each row,
 * - modify an existing column value for each row,
 * - sort an entire DataTable,
 * - and more.
 *
 * Using these filters instead of writing your own code will increase code clarity and
 * reduce code redundancy. Additionally, filters have the advantage that they can be
 * applied to DataTable\Map instances. So you can visit every DataTable in a {@link DataTable\Map}
 * without having to write a recursive visiting function.
 *
 * All predefined filters exist in the **Piwik\DataTable\BaseFilter** namespace.
 *
 * _Note: For convenience, [anonymous functions](http://www.php.net/manual/en/functions.anonymous.php)
 * can be used as DataTable filters._
 *
 * ### Applying Filters
 *
 * Filters can be applied now (via {@link filter()}), or they can be applied later (via
 * {@link queueFilter()}).
 *
 * Filters that sort rows or manipulate the number of rows should be applied right away.
 * Non-essential, presentation filters should be queued.
 *
 * ### Learn more
 *
 * - See **{@link ArchiveProcessor}** to learn how DataTables are persisted.
 *
 * ### Examples
 *
 * **Populating a DataTable**
 *
 *     // adding one row at a time
 *     $dataTable = new DataTable();
 *     $dataTable->addRow(new Row(array(
 *         Row::COLUMNS => array('label' => 'thing1', 'nb_visits' => 1, 'nb_actions' => 1),
 *         Row::METADATA => array('url' => 'http://thing1.com')
 *     )));
 *     $dataTable->addRow(new Row(array(
 *         Row::COLUMNS => array('label' => 'thing2', 'nb_visits' => 2, 'nb_actions' => 2),
 *         Row::METADATA => array('url' => 'http://thing2.com')
 *     )));
 *
 *     // using an array of rows
 *     $dataTable = new DataTable();
 *     $dataTable->addRowsFromArray(array(
 *         array(
 *             Row::COLUMNS => array('label' => 'thing1', 'nb_visits' => 1, 'nb_actions' => 1),
 *             Row::METADATA => array('url' => 'http://thing1.com')
 *         ),
 *         array(
 *             Row::COLUMNS => array('label' => 'thing2', 'nb_visits' => 2, 'nb_actions' => 2),
 *             Row::METADATA => array('url' => 'http://thing2.com')
 *         )
 *     ));
 *
 *     // using a "simple" array
 *     $dataTable->addRowsFromSimpleArray(array(
 *         array('label' => 'thing1', 'nb_visits' => 1, 'nb_actions' => 1),
 *         array('label' => 'thing2', 'nb_visits' => 2, 'nb_actions' => 2)
 *     ));
 *
 * **Getting & setting metadata**
 *
 *     $dataTable = \Piwik\Plugins\Referrers\API::getInstance()->getSearchEngines($idSite = 1, $period = 'day', $date = '2007-07-24');
 *     $oldPeriod = $dataTable->metadata['period'];
 *     $dataTable->metadata['period'] = Period\Factory::build('week', Date::factory('2013-10-18'));
 *
 * **Serializing & unserializing**
 *
 *     $maxRowsInTable = Config::getInstance()->General['datatable_archiving_maximum_rows_standard'];j
 *
 *     $dataTable = // ... build by aggregating visits ...
 *     $serializedData = $dataTable->getSerialized($maxRowsInTable, $maxRowsInSubtable = $maxRowsInTable,
 *                                                 $columnToSortBy = Metrics::INDEX_NB_VISITS);
 *
 *     $serializedDataTable = $serializedData[0];
 *     $serailizedSubTable = $serializedData[$idSubtable];
 *
 * **Filtering for an API method**
 *
 *     public function getMyReport($idSite, $period, $date, $segment = false, $expanded = false)
 *     {
 *         $dataTable = Archive::getDataTableFromArchive('MyPlugin_MyReport', $idSite, $period, $date, $segment, $expanded);
 *         $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS, 'desc', $naturalSort = false, $expanded));
 *         $dataTable->queueFilter('ReplaceColumnNames');
 *         $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', __NAMESPACE__ . '\getUrlFromLabelForMyReport'));
 *         return $dataTable;
 *     }
 *
 *
 * @api
 */
class DataTable implements DataTableInterface, \IteratorAggregate, \ArrayAccess
{
    const MAX_DEPTH_DEFAULT = 15;

    /** Name for metadata that describes when a report was archived. */
    const ARCHIVED_DATE_METADATA_NAME = 'archived_date';

    /** Name for metadata that describes which columns are empty and should not be shown. */
    const EMPTY_COLUMNS_METADATA_NAME = 'empty_column';

    /** Name for metadata that describes the number of rows that existed before the Limit filter was applied. */
    const TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME = 'total_rows_before_limit';

    /**
     * Name for metadata that describes how individual columns should be aggregated when {@link addDataTable()}
     * or {@link Piwik\DataTable\Row::sumRow()} is called.
     *
     * This metadata value must be an array that maps column names with valid operations. Valid aggregation operations are:
     *
     * - `'skip'`: do nothing
     * - `'max'`: does `max($column1, $column2)`
     * - `'min'`: does `min($column1, $column2)`
     * - `'sum'`: does `$column1 + $column2`
     *
     * See {@link addDataTable()} and {@link DataTable\Row::sumRow()} for more information.
     */
    const COLUMN_AGGREGATION_OPS_METADATA_NAME = 'column_aggregation_ops';

    /** The ID of the Summary Row. */
    const ID_SUMMARY_ROW = -1;

    /** The original label of the Summary Row. */
    const LABEL_SUMMARY_ROW = -1;

    /**
     * Maximum nesting level.
     */
    private static $maximumDepthLevelAllowed = self::MAX_DEPTH_DEFAULT;

    /**
     * Array of Row
     *
     * @var Row[]
     */
    protected $rows = array();

    /**
     * Id assigned to the DataTable, used to lookup the table using the DataTable_Manager
     *
     * @var int
     */
    protected $currentId;

    /**
     * Current depth level of this data table
     * 0 is the parent data table
     *
     * @var int
     */
    protected $depthLevel = 0;

    /**
     * This flag is set to false once we modify the table in a way that outdates the index
     *
     * @var bool
     */
    protected $indexNotUpToDate = true;

    /**
     * This flag sets the index to be rebuild whenever a new row is added,
     * as opposed to re-building the full index when getRowFromLabel is called.
     * This is to optimize and not rebuild the full Index in the case where we
     * add row, getRowFromLabel, addRow, getRowFromLabel thousands of times.
     *
     * @var bool
     */
    protected $rebuildIndexContinuously = false;

    /**
     * Column name of last time the table was sorted
     *
     * @var string
     */
    protected $tableSortedBy = false;

    /**
     * List of BaseFilter queued to this table
     *
     * @var array
     */
    protected $queuedFilters = array();

    /**
     * We keep track of the number of rows before applying the LIMIT filter that deletes some rows
     *
     * @var int
     */
    protected $rowsCountBeforeLimitFilter = 0;

    /**
     * Defaults to false for performance reasons (most of the time we don't need recursive sorting so we save a looping over the dataTable)
     *
     * @var bool
     */
    protected $enableRecursiveSort = false;

    /**
     * When the table and all subtables are loaded, this flag will be set to true to ensure filters are applied to all subtables
     *
     * @var bool
     */
    protected $enableRecursiveFilters = false;

    /**
     * @var array
     */
    protected $rowsIndexByLabel = array();

    /**
     * @var \Piwik\DataTable\Row
     */
    protected $summaryRow = null;

    /**
     * Table metadata. Read [this](#class-desc-the-basics) to learn more.
     *
     * Any data that describes the data held in the table's rows should go here.
     *
     * @var array
     */
    private $metadata = array();

    /**
     * Maximum number of rows allowed in this datatable (including the summary row).
     * If adding more rows is attempted, the extra rows get summed to the summary row.
     *
     * @var int
     */
    protected $maximumAllowedRows = 0;

    /**
     * Constructor. Creates an empty DataTable.
     */
    public function __construct()
    {
        // registers this instance to the manager
        $this->currentId = Manager::getInstance()->addTable($this);
    }

    /**
     * Destructor. Makes sure DataTable memory will be cleaned up.
     */
    public function __destruct()
    {
        static $depth = 0;
        // destruct can be called several times
        if ($depth < self::$maximumDepthLevelAllowed
            && isset($this->rows)
        ) {
            $depth++;
            foreach ($this->getRows() as $row) {
                Common::destroy($row);
            }
            unset($this->rows);
            Manager::getInstance()->setTableDeleted($this->getId());
            $depth--;
        }
    }

    /**
     * Sorts the DataTable rows using the supplied callback function.
     *
     * @param string $functionCallback A comparison callback compatible with {@link usort}.
     * @param string $columnSortedBy The column name `$functionCallback` sorts by. This is stored
     *                               so we can determine how the DataTable was sorted in the future.
     */
    public function sort($functionCallback, $columnSortedBy)
    {
        $this->indexNotUpToDate = true;
        $this->tableSortedBy = $columnSortedBy;
        usort($this->rows, $functionCallback);

        if ($this->enableRecursiveSort === true) {
            foreach ($this->getRows() as $row) {
                if (($idSubtable = $row->getIdSubDataTable()) !== null) {
                    $table = Manager::getInstance()->getTable($idSubtable);
                    $table->enableRecursiveSort();
                    $table->sort($functionCallback, $columnSortedBy);
                }
            }
        }
    }

    /**
     * Returns the name of the column this table was sorted by (if any).
     *
     * See {@link sort()}.
     *
     * @return false|string The sorted column name or false if none.
     */
    public function getSortedByColumnName()
    {
        return $this->tableSortedBy;
    }

    /**
     * Enables recursive sorting. If this method is called {@link sort()} will also sort all
     * subtables.
     */
    public function enableRecursiveSort()
    {
        $this->enableRecursiveSort = true;
    }

    /**
     * Enables recursive filtering. If this method is called then the {@link filter()} method
     * will apply filters to every subtable in addition to this instance.
     */
    public function enableRecursiveFilters()
    {
        $this->enableRecursiveFilters = true;
    }

    /**
     * Applies a filter to this datatable.
     *
     * If {@link enableRecursiveFilters()} was called, the filter will be applied
     * to all subtables as well.
     *
     * @param string|Closure $className Class name, eg. `"Sort"` or "Piwik\DataTable\Filters\Sort"`. If no
     *                                  namespace is supplied, `Piwik\DataTable\BaseFilter` is assumed. This parameter
     *                                  can also be a closure that takes a DataTable as its first parameter.
     * @param array $parameters Array of extra parameters to pass to the filter.
     */
    public function filter($className, $parameters = array())
    {
        if ($className instanceof \Closure
            || is_array($className)
        ) {
            array_unshift($parameters, $this);
            call_user_func_array($className, $parameters);
            return;
        }

        if (!class_exists($className, true)) {
            $className = 'Piwik\DataTable\Filter\\' . $className;
        }
        $reflectionObj = new ReflectionClass($className);

        // the first parameter of a filter is the DataTable
        // we add the current datatable as the parameter
        $parameters = array_merge(array($this), $parameters);

        $filter = $reflectionObj->newInstanceArgs($parameters);

        $filter->enableRecursive($this->enableRecursiveFilters);

        $filter->filter($this);
    }

    /**
     * Adds a filter and a list of parameters to the list of queued filters. These filters will be
     * executed when {@link applyQueuedFilters()} is called.
     *
     * Filters that prettify the column values or don't need the full set of rows should be queued. This
     * way they will be run after the table is truncated which will result in better performance.
     *
     * @param string|Closure $className The class name of the filter, eg. `'Limit'`.
     * @param array $parameters The parameters to give to the filter, eg. `array($offset, $limit)` for the Limit filter.
     */
    public function queueFilter($className, $parameters = array())
    {
        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }
        $this->queuedFilters[] = array('className' => $className, 'parameters' => $parameters);
    }

    /**
     * Applies all filters that were previously queued to the table. See {@link queueFilter()}
     * for more information.
     */
    public function applyQueuedFilters()
    {
        foreach ($this->queuedFilters as $filter) {
            $this->filter($filter['className'], $filter['parameters']);
        }
        $this->clearQueuedFilters();
    }

    /**
     * Sums a DataTable to this one.
     *
     * This method will sum rows that have the same label. If a row is found in `$tableToSum` whose
     * label is not found in `$this`, the row will be added to `$this`.
     *
     * If the subtables for this table are loaded, they will be summed as well.
     *
     * Rows are summed together by summing individual columns. By default columns are summed by
     * adding one column value to another. Some columns cannot be aggregated this way. In these
     * cases, the {@link COLUMN_AGGREGATION_OPS_METADATA_NAME}
     * metadata can be used to specify a different type of operation.
     *
     * @param \Piwik\DataTable $tableToSum
     */
    public function addDataTable(DataTable $tableToSum, $doAggregateSubTables = true)
    {
        if($tableToSum instanceof Simple) {
            if($tableToSum->getRowsCount() > 1) {
                throw new Exception("Did not expect a Simple table with more than one row in addDataTable()");
            }
            $row = $tableToSum->getFirstRow();
            $this->aggregateRowFromSimpleTable($row);
        } else {
            foreach ($tableToSum->getRows() as $row) {
                $this->aggregateRowWithLabel($row, $doAggregateSubTables);
            }
        }
    }

    /**
     * Returns the Row whose `'label'` column is equal to `$label`.
     *
     * This method executes in constant time except for the first call which caches row
     * label => row ID mappings.
     *
     * @param string $label `'label'` column value to look for.
     * @return Row|false The row if found, `false` if otherwise.
     */
    public function getRowFromLabel($label)
    {
        $rowId = $this->getRowIdFromLabel($label);
        if ($rowId instanceof Row) {
            return $rowId;
        }
        if (is_int($rowId) && isset($this->rows[$rowId])) {
            return $this->rows[$rowId];
        }
        if ($rowId == self::ID_SUMMARY_ROW
            && !empty($this->summaryRow)
        ) {
            return $this->summaryRow;
        }
        return false;
    }

    /**
     * Returns the row id for the row whose `'label'` column is equal to `$label`.
     *
     * This method executes in constant time except for the first call which caches row
     * label => row ID mappings.
     *
     * @param string $label `'label'` column value to look for.
     * @return int The row ID.
     */
    public function getRowIdFromLabel($label)
    {
        $this->rebuildIndexContinuously = true;
        if ($this->indexNotUpToDate) {
            $this->rebuildIndex();
        }

        if ($label === self::LABEL_SUMMARY_ROW
            && !is_null($this->summaryRow)
        ) {
            return self::ID_SUMMARY_ROW;
        }

        $label = (string)$label;
        if (!isset($this->rowsIndexByLabel[$label])) {
            return false;
        }
        return $this->rowsIndexByLabel[$label];
    }

    /**
     * Returns an empty DataTable with the same metadata and queued filters as `$this` one.
     *
     * @param bool $keepFilters Whether to pass the queued filter list to the new DataTable or not.
     * @return DataTable
     */
    public function getEmptyClone($keepFilters = true)
    {
        $clone = new DataTable;
        if ($keepFilters) {
            $clone->queuedFilters = $this->queuedFilters;
        }
        $clone->metadata = $this->metadata;
        return $clone;
    }

    /**
     * Rebuilds the index used to lookup a row by label
     */
    private function rebuildIndex()
    {
        foreach ($this->getRows() as $id => $row) {
            $label = $row->getColumn('label');
            if ($label !== false) {
                $this->rowsIndexByLabel[$label] = $id;
            }
        }
        $this->indexNotUpToDate = false;
    }

    /**
     * Returns a row by ID. The ID is either the index of the row or {@link ID_SUMMARY_ROW}.
     *
     * @param int $id The row ID.
     * @return Row|false The Row or false if not found.
     */
    public function getRowFromId($id)
    {
        if (!isset($this->rows[$id])) {
            if ($id == self::ID_SUMMARY_ROW
                && !is_null($this->summaryRow)
            ) {
                return $this->summaryRow;
            }
            return false;
        }
        return $this->rows[$id];
    }

    /**
     * Returns the row that has a subtable with ID matching `$idSubtable`.
     *
     * @param int $idSubTable The subtable ID.
     * @return Row|false The row or false if not found
     */
    public function getRowFromIdSubDataTable($idSubTable)
    {
        $idSubTable = (int)$idSubTable;
        foreach ($this->rows as $row) {
            if ($row->getIdSubDataTable() === $idSubTable) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Adds a row to this table.
     *
     * If {@link setMaximumAllowedRows()} was called and the current row count is
     * at the maximum, the new row will be summed to the summary row. If there is no summary row,
     * this row is set as the summary row.
     *
     * @param Row $row
     * @return Row `$row` or the summary row if we're at the maximum number of rows.
     */
    public function addRow(Row $row)
    {
        // if there is a upper limit on the number of allowed rows and the table is full,
        // add the new row to the summary row
        if ($this->maximumAllowedRows > 0
            && $this->getRowsCount() >= $this->maximumAllowedRows - 1
        ) {
            if ($this->summaryRow === null) // create the summary row if necessary
            {
                $columns = array('label' => self::LABEL_SUMMARY_ROW) + $row->getColumns();
                $this->addSummaryRow(new Row(array(Row::COLUMNS => $columns)));
            } else {
                $this->summaryRow->sumRow(
                    $row, $enableCopyMetadata = false, $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME));
            }
            return $this->summaryRow;
        }

        $this->rows[] = $row;
        if (!$this->indexNotUpToDate
            && $this->rebuildIndexContinuously
        ) {
            $label = $row->getColumn('label');
            if ($label !== false) {
                $this->rowsIndexByLabel[$label] = count($this->rows) - 1;
            }
        }
        return $row;
    }

    /**
     * Sets the summary row.
     *
     * _Note: A DataTable can have only one summary row._
     *
     * @param Row $row
     * @return Row Returns `$row`.
     */
    public function addSummaryRow(Row $row)
    {
        $this->summaryRow = $row;

        // add summary row to index
        if (!$this->indexNotUpToDate
            && $this->rebuildIndexContinuously
        ) {
            $label = $row->getColumn('label');
            if ($label !== false) {
                $this->rowsIndexByLabel[$label] = self::ID_SUMMARY_ROW;
            }
        }

        return $row;
    }

    /**
     * Returns the DataTable ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->currentId;
    }

    /**
     * Adds a new row from an array.
     *
     * You can add row metadata with this method.
     *
     * @param array $row eg. `array(Row::COLUMNS => array('visits' => 13, 'test' => 'toto'),
     *                              Row::METADATA => array('mymetadata' => 'myvalue'))`
     */
    public function addRowFromArray($row)
    {
        $this->addRowsFromArray(array($row));
    }

    /**
     * Adds a new row a from an array of column values.
     *
     * Row metadata cannot be added with this method.
     *
     * @param array $row eg. `array('name' => 'google analytics', 'license' => 'commercial')`
     */
    public function addRowFromSimpleArray($row)
    {
        $this->addRowsFromSimpleArray(array($row));
    }

    /**
     * Returns the array of Rows.
     *
     * @return Row[]
     */
    public function getRows()
    {
        if (is_null($this->summaryRow)) {
            return $this->rows;
        } else {
            return $this->rows + array(self::ID_SUMMARY_ROW => $this->summaryRow);
        }
    }

    /**
     * Returns an array containing all column values for the requested column.
     *
     * @param string $name The column name.
     * @return array The array of column values.
     */
    public function getColumn($name)
    {
        $columnValues = array();
        foreach ($this->getRows() as $row) {
            $columnValues[] = $row->getColumn($name);
        }
        return $columnValues;
    }

    /**
     * Returns an array containing all column values of columns whose name starts with `$name`.
     *
     * @param $namePrefix The column name prefix.
     * @return array The array of column values.
     */
    public function getColumnsStartingWith($namePrefix)
    {
        $columnValues = array();
        foreach ($this->getRows() as $row) {
            $columns = $row->getColumns();
            foreach ($columns as $column => $value) {
                if (strpos($column, $namePrefix) === 0) {
                    $columnValues[] = $row->getColumn($column);
                }
            }
        }
        return $columnValues;
    }

    /**
     * Returns the names of every column this DataTable contains. This method will return the
     * columns of the first row with data and will assume they occur in every other row as well.
     *
     *_ Note: If column names still use their in-database INDEX values (@see Metrics), they
     *        will be converted to their string name in the array result._
     *
     * @return array Array of string column names.
     */
    public function getColumns()
    {
        $result = array();
        foreach ($this->getRows() as $row) {
            $columns = $row->getColumns();
            if (!empty($columns)) {
                $result = array_keys($columns);
                break;
            }
        }

        // make sure column names are not DB index values
        foreach ($result as &$column) {
            if (isset(Metrics::$mappingFromIdToName[$column])) {
                $column = Metrics::$mappingFromIdToName[$column];
            }
        }

        return $result;
    }

    /**
     * Returns an array containing the requested metadata value of each row.
     *
     * @param string $name The metadata column to return.
     * @return array
     */
    public function getRowsMetadata($name)
    {
        $metadataValues = array();
        foreach ($this->getRows() as $row) {
            $metadataValues[] = $row->getMetadata($name);
        }
        return $metadataValues;
    }

    /**
     * Returns the number of rows in the table including the summary row.
     *
     * @return int
     */
    public function getRowsCount()
    {
        if (is_null($this->summaryRow)) {
            return count($this->rows);
        } else {
            return count($this->rows) + 1;
        }
    }

    /**
     * Returns the first row of the DataTable.
     *
     * @return Row|false The first row or `false` if it cannot be found.
     */
    public function getFirstRow()
    {
        if (count($this->rows) == 0) {
            if (!is_null($this->summaryRow)) {
                return $this->summaryRow;
            }
            return false;
        }
        return reset($this->rows);
    }

    /**
     * Returns the last row of the DataTable. If there is a summary row, it
     * will always be considered the last row.
     *
     * @return Row|false The last row or `false` if it cannot be found.
     */
    public function getLastRow()
    {
        if (!is_null($this->summaryRow)) {
            return $this->summaryRow;
        }

        if (count($this->rows) == 0) {
            return false;
        }

        return end($this->rows);
    }

    /**
     * Returns the number of rows in the entire DataTable hierarchy. This is the number of rows in this DataTable
     * summed with the row count of each descendant subtable.
     *
     * @return int
     */
    public function getRowsCountRecursive()
    {
        $totalCount = 0;
        foreach ($this->rows as $row) {
            if (($idSubTable = $row->getIdSubDataTable()) !== null) {
                $subTable = Manager::getInstance()->getTable($idSubTable);
                $count = $subTable->getRowsCountRecursive();
                $totalCount += $count;
            }
        }

        $totalCount += $this->getRowsCount();
        return $totalCount;
    }

    /**
     * Delete a column by name in every row. This change is NOT applied recursively to all
     * subtables.
     *
     * @param string $name Column name to delete.
     */
    public function deleteColumn($name)
    {
        $this->deleteColumns(array($name));
    }

    public function __sleep()
    {
        return array('rows', 'summaryRow');
    }

    /**
     * Rename a column in every row. This change is applied recursively to all subtables.
     *
     * @param string $oldName Old column name.
     * @param string $newName New column name.
     */
    public function renameColumn($oldName, $newName, $doRenameColumnsOfSubTables = true)
    {
        foreach ($this->getRows() as $row) {
            $row->renameColumn($oldName, $newName);

            if($doRenameColumnsOfSubTables) {
                if (($idSubDataTable = $row->getIdSubDataTable()) !== null) {
                    Manager::getInstance()->getTable($idSubDataTable)->renameColumn($oldName, $newName);
                }
            }
        }
        if (!is_null($this->summaryRow)) {
            $this->summaryRow->renameColumn($oldName, $newName);
        }
    }

    /**
     * Deletes several columns by name in every row.
     *
     * @param array $names List of column names to delete.
     * @param bool $deleteRecursiveInSubtables Whether to apply this change to all subtables or not.
     */
    public function deleteColumns($names, $deleteRecursiveInSubtables = false)
    {
        foreach ($this->getRows() as $row) {
            foreach ($names as $name) {
                $row->deleteColumn($name);
            }
            if (($idSubDataTable = $row->getIdSubDataTable()) !== null) {
                Manager::getInstance()->getTable($idSubDataTable)->deleteColumns($names, $deleteRecursiveInSubtables);
            }
        }
        if (!is_null($this->summaryRow)) {
            foreach ($names as $name) {
                $this->summaryRow->deleteColumn($name);
            }
        }
    }

    /**
     * Deletes a row by ID.
     *
     * @param int $id The row ID.
     * @throws Exception If the row `$id` cannot be found.
     */
    public function deleteRow($id)
    {
        if ($id === self::ID_SUMMARY_ROW) {
            $this->summaryRow = null;
            return;
        }
        if (!isset($this->rows[$id])) {
            throw new Exception("Trying to delete unknown row with idkey = $id");
        }
        unset($this->rows[$id]);
    }

    /**
     * Deletes rows from `$offset` to `$offset + $limit`.
     *
     * @param int $offset The offset to start deleting rows from.
     * @param int|null $limit The number of rows to delete. If `null` all rows after the offset
     *                        will be removed.
     * @return int The number of rows deleted.
     */
    public function deleteRowsOffset($offset, $limit = null)
    {
        if ($limit === 0) {
            return 0;
        }

        $count = $this->getRowsCount();
        if ($offset >= $count) {
            return 0;
        }

        // if we delete until the end, we delete the summary row as well
        if (is_null($limit)
            || $limit >= $count
        ) {
            $this->summaryRow = null;
        }

        if (is_null($limit)) {
            $spliced = array_splice($this->rows, $offset);
        } else {
            $spliced = array_splice($this->rows, $offset, $limit);
        }
        $countDeleted = count($spliced);
        return $countDeleted;
    }

    /**
     * Deletes a set of rows by ID.
     *
     * @param array $rowIds The list of row IDs to delete.
     * @throws Exception If a row ID cannot be found.
     */
    public function deleteRows(array $rowIds)
    {
        foreach ($rowIds as $key) {
            $this->deleteRow($key);
        }
    }

    /**
     * Returns a string representation of this DataTable for convenient viewing.
     *
     * _Note: This uses the **html** DataTable renderer._
     *
     * @return string
     */
    public function __toString()
    {
        $renderer = new Html();
        $renderer->setTable($this);
        return (string)$renderer;
    }

    /**
     * Returns true if both DataTable instances are exactly the same.
     *
     * DataTables are equal if they have the same number of rows, if
     * each row has a label that exists in the other table, and if each row
     * is equal to the row in the other table with the same label. The order
     * of rows is not important.
     *
     * @param \Piwik\DataTable $table1
     * @param \Piwik\DataTable $table2
     * @return bool
     */
    public static function isEqual(DataTable $table1, DataTable $table2)
    {
        $rows1 = $table1->getRows();

        $table1->rebuildIndex();
        $table2->rebuildIndex();

        if ($table1->getRowsCount() != $table2->getRowsCount()) {
            return false;
        }

        foreach ($rows1 as $row1) {
            $row2 = $table2->getRowFromLabel($row1->getColumn('label'));
            if ($row2 === false
                || !Row::isEqual($row1, $row2)
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Serializes an entire DataTable hierarchy and returns the array of serialized DataTables.
     *
     * The first element in the returned array will be the serialized representation of this DataTable.
     * Every subsequent element will be a serialized subtable.
     *
     * This DataTable and subtables can optionally be truncated before being serialized. In most
     * cases where DataTables can become quite large, they should be truncated before being persisted
     * in an archive.
     *
     * The result of this method is intended for use with the {@link ArchiveProcessor::insertBlobRecord()} method.
     *
     * @throws Exception If infinite recursion detected. This will occur if a table's subtable is one of its parent tables.
     * @param int $maximumRowsInDataTable If not null, defines the maximum number of rows allowed in the serialized DataTable.
     * @param int $maximumRowsInSubDataTable If not null, defines the maximum number of rows allowed in serialized subtables.
     * @param string $columnToSortByBeforeTruncation The column to sort by before truncating, eg, `Metrics::INDEX_NB_VISITS`.
     * @return array The array of serialized DataTables:
     *
     *                   array(
     *                       // this DataTable (the root)
     *                       0 => 'eghuighahgaueytae78yaet7yaetae',
     *
     *                       // a subtable
     *                       1 => 'gaegae gh gwrh guiwh uigwhuige',
     *
     *                       // another subtable
     *                       2 => 'gqegJHUIGHEQjkgneqjgnqeugUGEQHGUHQE',
     *
     *                       // etc.
     *                   );
     */
    public function getSerialized($maximumRowsInDataTable = null,
                                  $maximumRowsInSubDataTable = null,
                                  $columnToSortByBeforeTruncation = null)
    {
        static $depth = 0;

        if ($depth > self::$maximumDepthLevelAllowed) {
            $depth = 0;
            throw new Exception("Maximum recursion level of " . self::$maximumDepthLevelAllowed . " reached. Maybe you have set a DataTable\Row with an associated DataTable belonging already to one of its parent tables?");
        }
        if (!is_null($maximumRowsInDataTable)) {
            $this->filter('Truncate',
                array($maximumRowsInDataTable - 1,
                      DataTable::LABEL_SUMMARY_ROW,
                      $columnToSortByBeforeTruncation,
                      $filterRecursive = false)
            );
        }

        // For each row, get the serialized row
        // If it is associated to a sub table, get the serialized table recursively ;
        // but returns all serialized tables and subtable in an array of 1 dimension
        $aSerializedDataTable = array();
        foreach ($this->rows as $row) {
            if (($idSubTable = $row->getIdSubDataTable()) !== null) {
                $subTable = null;
                try {
                    $subTable = Manager::getInstance()->getTable($idSubTable);
                } catch(TableNotFoundException $e) {
                    // This occurs is an unknown & random data issue. Catch Exception and remove subtable from the row.
                    $row->removeSubtable();
                    // Go to next row
                    continue;
                }

                $depth++;
                $aSerializedDataTable = $aSerializedDataTable + $subTable->getSerialized($maximumRowsInSubDataTable, $maximumRowsInSubDataTable, $columnToSortByBeforeTruncation);
                $depth--;
            }
        }
        // we load the current Id of the DataTable
        $forcedId = $this->getId();

        // if the datatable is the parent we force the Id at 0 (this is part of the specification)
        if ($depth == 0) {
            $forcedId = 0;
        }

        // we then serialize the rows and store them in the serialized dataTable
        $addToRows = array(self::ID_SUMMARY_ROW => $this->summaryRow);

        $aSerializedDataTable[$forcedId] = serialize($this->rows + $addToRows);
        foreach ($this->rows as &$row) {
            $row->cleanPostSerialize();
        }

        return $aSerializedDataTable;
    }

    /**
     * Adds a set of rows from a serialized DataTable string.
     *
     * See {@link serialize()}.
     *
     * _Note: This function will successfully load DataTables serialized by Piwik 1.X._
     *
     * @param string $stringSerialized A string with the format of a string in the array returned by
     *                                 {@link serialize()}.
     * @throws Exception if `$stringSerialized` is invalid.
     */
    public function addRowsFromSerializedArray($stringSerialized)
    {
        require_once PIWIK_INCLUDE_PATH . "/core/DataTable/Bridges.php";

        $serialized = unserialize($stringSerialized);
        if ($serialized === false) {
            throw new Exception("The unserialization has failed!");
        }
        $this->addRowsFromArray($serialized);
    }

    /**
     * Adds multiple rows from an array.
     *
     * You can add row metadata with this method.
     *
     * @param array $array Array with the following structure
     *
     *                         array(
     *                             // row1
     *                             array(
     *                                 Row::COLUMNS => array( col1_name => value1, col2_name => value2, ...),
     *                                 Row::METADATA => array( metadata1_name => value1,  ...), // see Row
     *                             ),
     *                             // row2
     *                             array( ... ),
     *                         )
     */
    public function addRowsFromArray($array)
    {
        foreach ($array as $id => $row) {
            if (is_array($row)) {
                $row = new Row($row);
            }
            if ($id == self::ID_SUMMARY_ROW) {
                $this->summaryRow = $row;
            } else {
                $this->addRow($row);
            }
        }
    }

    /**
     * Adds multiple rows from an array containing arrays of column values.
     *
     * Row metadata cannot be added with this method.
     *
     * @param array $array Array with the following structure:
     *
     *                       array(
     *                             array( col1_name => valueA, col2_name => valueC, ...),
     *                             array( col1_name => valueB, col2_name => valueD, ...),
     *                       )
     * @throws Exception if `$array` is in an incorrect format.
     */
    public function addRowsFromSimpleArray($array)
    {
        if (count($array) === 0) {
            return;
        }

        // we define an exception we may throw if at one point we notice that we cannot handle the data structure
        $e = new Exception(" Data structure returned is not convertible in the requested format." .
            " Try to call this method with the parameters '&format=original&serialize=1'" .
            "; you will get the original php data structure serialized." .
            " The data structure looks like this: \n \$data = " . var_export($array, true) . "; ");

        // first pass to see if the array has the structure
        // array(col1_name => val1, col2_name => val2, etc.)
        // with val* that are never arrays (only strings/numbers/bool/etc.)
        // if we detect such a "simple" data structure we convert it to a row with the correct columns' names
        $thisIsNotThatSimple = false;

        foreach ($array as $columnValue) {
            if (is_array($columnValue) || is_object($columnValue)) {
                $thisIsNotThatSimple = true;
                break;
            }
        }
        if ($thisIsNotThatSimple === false) {
            // case when the array is indexed by the default numeric index
            if (array_keys($array) == array_keys(array_fill(0, count($array), true))) {
                foreach ($array as $row) {
                    $this->addRow(new Row(array(Row::COLUMNS => array($row))));
                }
            } else {
                $this->addRow(new Row(array(Row::COLUMNS => $array)));
            }
            // we have converted our simple array to one single row
            // => we exit the method as the job is now finished
            return;
        }

        foreach ($array as $key => $row) {
            // stuff that looks like a line
            if (is_array($row)) {
                /**
                 * We make sure we can convert this PHP array without losing information.
                 * We are able to convert only simple php array (no strings keys, no sub arrays, etc.)
                 *
                 */

                // if the key is a string it means that some information was contained in this key.
                // it cannot be lost during the conversion. Because we are not able to handle properly
                // this key, we throw an explicit exception.
                if (is_string($key)) {
                    throw $e;
                }
                // if any of the sub elements of row is an array we cannot handle this data structure...
                foreach ($row as $subRow) {
                    if (is_array($subRow)) {
                        throw $e;
                    }
                }
                $row = new Row(array(Row::COLUMNS => $row));
            } // other (string, numbers...) => we build a line from this value
            else {
                $row = new Row(array(Row::COLUMNS => array($key => $row)));
            }
            $this->addRow($row);
        }
    }

    /**
     * Rewrites the input `$array`
     *
     *     array (
     *         LABEL => array(col1 => X, col2 => Y),
     *         LABEL2 => array(col1 => X, col2 => Y),
     *     )
     *
     * to a DataTable with rows that look like:
     *
     *     array (
     *         array( Row::COLUMNS => array('label' => LABEL, col1 => X, col2 => Y)),
     *         array( Row::COLUMNS => array('label' => LABEL2, col1 => X, col2 => Y)),
     *     )
     *
     * Will also convert arrays like:
     *
     *     array (
     *         LABEL => X,
     *         LABEL2 => Y,
     *     )
     *
     * to:
     *
     *     array (
     *         array( Row::COLUMNS => array('label' => LABEL, 'value' => X)),
     *         array( Row::COLUMNS => array('label' => LABEL2, 'value' => Y)),
     *     )
     *
     * @param array $array Indexed array, two formats supported, see above.
     * @param array|null $subtablePerLabel An array mapping label values with DataTable instances to associate as a subtable.
     * @return \Piwik\DataTable
     */
    public static function makeFromIndexedArray($array, $subtablePerLabel = null)
    {
        $table = new DataTable();
        foreach ($array as $label => $row) {
            $cleanRow = array();

            // Support the case of an $array of single values
            if (!is_array($row)) {
                $row = array('value' => $row);
            }
            // Put the 'label' column first
            $cleanRow[Row::COLUMNS] = array('label' => $label) + $row;
            // Assign subtable if specified
            if (isset($subtablePerLabel[$label])) {
                $cleanRow[Row::DATATABLE_ASSOCIATED] = $subtablePerLabel[$label];
            }
            $table->addRow(new Row($cleanRow));
        }
        return $table;
    }

    /**
     * Sets the maximum depth level to at least a certain value. If the current value is
     * greater than `$atLeastLevel`, the maximum nesting level is not changed.
     *
     * The maximum depth level determines the maximum number of subtable levels in the
     * DataTable tree. For example, if it is set to `2`, this DataTable is allowed to
     * have subtables, but the subtables are not.
     *
     * @param int $atLeastLevel
     */
    public static function setMaximumDepthLevelAllowedAtLeast($atLeastLevel)
    {
        self::$maximumDepthLevelAllowed = max($atLeastLevel, self::$maximumDepthLevelAllowed);
        if (self::$maximumDepthLevelAllowed < 1) {
            self::$maximumDepthLevelAllowed = 1;
        }
    }

    /**
     * Returns metadata by name.
     *
     * @param string $name The metadata name.
     * @return mixed|false The metadata value or `false` if it cannot be found.
     */
    public function getMetadata($name)
    {
        if (!isset($this->metadata[$name])) {
            return false;
        }
        return $this->metadata[$name];
    }

    /**
     * Sets a metadata value by name.
     *
     * @param string $name The metadata name.
     * @param mixed $value
     */
    public function setMetadata($name, $value)
    {
        $this->metadata[$name] = $value;
    }

    /**
     * Returns all table metadata.
     *
     * @return array
     */
    public function getAllTableMetadata()
    {
        return $this->metadata;
    }

    /**
     * Sets several metadata values by name.
     *
     * @param array $values Array mapping metadata names with metadata values.
     */
    public function setMetadataValues($values)
    {
        foreach ($values as $name => $value) {
            $this->metadata[$name] = $value;
        }
    }

    /**
     * Sets metadata, erasing existing values.
     *
     * @param array $values Array mapping metadata names with metadata values.
     */
    public function setAllTableMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Sets the maximum number of rows allowed in this datatable (including the summary
     * row). If adding more then the allowed number of rows is attempted, the extra
     * rows are summed to the summary row.
     *
     * @param int $maximumAllowedRows If `0`, the maximum number of rows is unset.
     */
    public function setMaximumAllowedRows($maximumAllowedRows)
    {
        $this->maximumAllowedRows = $maximumAllowedRows;
    }

    /**
     * Traverses a DataTable tree using an array of labels and returns the row
     * it finds or `false` if it cannot find one. The number of path segments that
     * were successfully walked is also returned.
     *
     * If `$missingRowColumns` is supplied, the specified path is created. When
     * a subtable is encountered w/o the required label, a new row is created
     * with the label, and a new subtable is added to the row.
     *
     * Read [http://en.wikipedia.org/wiki/Tree_(data_structure)#Traversal_methods](http://en.wikipedia.org/wiki/Tree_(data_structure)#Traversal_methods)
     * for more information about tree walking.
     *
     * @param array $path The path to walk. An array of label values. The first element
     *                    refers to a row in this DataTable, the second in a subtable of
     *                    the first row, the third a subtable of the second row, etc.
     * @param array|bool $missingRowColumns The default columns to use when creating new rows.
     *                                      If this parameter is supplied, new rows will be
     *                                      created for path labels that cannot be found.
     * @param int $maxSubtableRows The maximum number of allowed rows in new subtables. New
     *                             subtables are only created if `$missingRowColumns` is provided.
     * @return array First element is the found row or `false`. Second element is
     *               the number of path segments walked. If a row is found, this
     *               will be == to `count($path)`. Otherwise, it will be the index
     *               of the path segment that we could not find.
     */
    public function walkPath($path, $missingRowColumns = false, $maxSubtableRows = 0)
    {
        $pathLength = count($path);

        $table = $this;
        $next = false;
        for ($i = 0; $i < $pathLength; ++$i) {
            $segment = $path[$i];

            $next = $table->getRowFromLabel($segment);
            if ($next === false) {
                // if there is no table to advance to, and we're not adding missing rows, return false
                if ($missingRowColumns === false) {
                    return array(false, $i);
                } else // if we're adding missing rows, add a new row
                {
                    $row = new DataTableSummaryRow();
                    $row->setColumns(array('label' => $segment) + $missingRowColumns);

                    $next = $table->addRow($row);

                    if ($next !== $row) // if the row wasn't added, the table is full
                    {
                        // Summary row, has no metadata
                        $next->deleteMetadata();
                        return array($next, $i);
                    }
                }
            }

            $table = $next->getSubtable();
            if ($table === false) {
                // if the row has no table (and thus no child rows), and we're not adding
                // missing rows, return false
                if ($missingRowColumns === false) {
                    return array(false, $i);
                } else if ($i != $pathLength - 1) // create subtable if missing, but only if not on the last segment
                {
                    $table = new DataTable();
                    $table->setMaximumAllowedRows($maxSubtableRows);
                    $table->metadata[self::COLUMN_AGGREGATION_OPS_METADATA_NAME]
                        = $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME);
                    $next->setSubtable($table);
                    // Summary row, has no metadata
                    $next->deleteMetadata();
                }
            }
        }

        return array($next, $i);
    }

    /**
     * Returns a new DataTable in which the rows of this table are replaced with the aggregatated rows of all its subtables.
     *
     * @param string|bool $labelColumn If supplied the label of the parent row will be added to
     *                                 a new column in each subtable row.
     *
     *                                 If set to, `'label'` each subtable row's label will be prepended
     *                                 w/ the parent row's label. So `'child_label'` becomes
     *                                 `'parent_label - child_label'`.
     * @param bool $useMetadataColumn If true and if `$labelColumn` is supplied, the parent row's
     *                                label will be added as metadata and not a new column.
     * @return \Piwik\DataTable
     */
    public function mergeSubtables($labelColumn = false, $useMetadataColumn = false)
    {
        $result = new DataTable();
        foreach ($this->getRows() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable !== false) {
                $parentLabel = $row->getColumn('label');

                // add a copy of each subtable row to the new datatable
                foreach ($subtable->getRows() as $id => $subRow) {
                    $copy = clone $subRow;

                    // if the summary row, add it to the existing summary row (or add a new one)
                    if ($id == self::ID_SUMMARY_ROW) {
                        $existing = $result->getRowFromId(self::ID_SUMMARY_ROW);
                        if ($existing === false) {
                            $result->addSummaryRow($copy);
                        } else {
                            $existing->sumRow($copy, $copyMeta = true, $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME));
                        }
                    } else {
                        if ($labelColumn !== false) {
                            // if we're modifying the subtable's rows' label column, then we make
                            // sure to prepend the existing label w/ the parent row's label. otherwise
                            // we're just adding the parent row's label as a new column/metadata.
                            $newLabel = $parentLabel;
                            if ($labelColumn == 'label') {
                                $newLabel .= ' - ' . $copy->getColumn('label');
                            }

                            // modify the child row's label or add new column/metadata
                            if ($useMetadataColumn) {
                                $copy->setMetadata($labelColumn, $newLabel);
                            } else {
                                $copy->setColumn($labelColumn, $newLabel);
                            }
                        }

                        $result->addRow($copy);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Returns a new DataTable created with data from a 'simple' array.
     *
     * See {@link addRowsFromSimpleArray()}.
     *
     * @param array $array
     * @return \Piwik\DataTable
     */
    public static function makeFromSimpleArray($array)
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray($array);
        return $dataTable;
    }

    /**
     * Creates a new DataTable instance from a serialized DataTable string.
     *
     * See {@link getSerialized()} and {@link addRowsFromSerializedArray()}
     * for more information on DataTable serialization.
     *
     * @param string $data
     * @return \Piwik\DataTable
     */
    public static function fromSerializedArray($data)
    {
        $result = new DataTable();
        $result->addRowsFromSerializedArray($data);
        return $result;
    }

    /**
     * Aggregates the $row columns to this table.
     *
     * $row must have a column "label". The $row will be summed to this table's row with the same label.
     *
     * @param $row
     * @throws \Exception
     */
    protected function aggregateRowWithLabel(Row $row, $doAggregateSubTables = true)
    {
        $labelToLookFor = $row->getColumn('label');
        if ($labelToLookFor === false) {
            throw new Exception("Label column not found in the table to add in addDataTable()");
        }
        $rowFound = $this->getRowFromLabel($labelToLookFor);
        if ($rowFound === false) {
            if ($labelToLookFor === self::LABEL_SUMMARY_ROW) {
                $this->addSummaryRow($row);
            } else {
                $this->addRow($row);
            }
        } else {
            $rowFound->sumRow($row, $copyMeta = true, $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME));

            if($doAggregateSubTables) {
                // if the row to add has a subtable whereas the current row doesn't
                // we simply add it (cloning the subtable)
                // if the row has the subtable already
                // then we have to recursively sum the subtables
                if (($idSubTable = $row->getIdSubDataTable()) !== null) {
                    $subTable = Manager::getInstance()->getTable($idSubTable);
                    $subTable->metadata[self::COLUMN_AGGREGATION_OPS_METADATA_NAME]
                        = $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME);
                    $rowFound->sumSubtable($subTable);
                }
            }
        }
    }

    /**
     * @param $row
     */
    protected function aggregateRowFromSimpleTable($row)
    {
        if ($row === false) {
            return;

        }
        $thisRow = $this->getFirstRow();
        if ($thisRow === false) {
            $thisRow = new Row;
            $this->addRow($thisRow);
        }
        $thisRow->sumRow($row, $copyMeta = true, $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME));
    }

    /**
     * Unsets all queued filters.
     */
    public function clearQueuedFilters()
    {
        $this->queuedFilters = array();
    }

    /**
     * @return \ArrayIterator|Row[]
     */
    public function getIterator() {
        return new \ArrayIterator($this->getRows());
    }

    public function offsetExists($offset)
    {
        $row = $this->getRowFromId($offset);

        return false !== $row;
    }

    public function offsetGet($offset)
    {
        return $this->getRowFromId($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->rows[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->deleteRow($offset);
    }
}
