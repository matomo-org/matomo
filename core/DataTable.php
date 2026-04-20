<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\DataTableInterface;
use Piwik\DataTable\Manager;
use Piwik\DataTable\Renderer\Html;
use Piwik\DataTable\Row;
use Piwik\DataTable\Row\DataTableSummaryRow;
use Piwik\DataTable\Simple;
use Piwik\DataTable\ViewRow;
use ReflectionClass;

/**
 * @see Common::destroy()
 */
require_once PIWIK_INCLUDE_PATH . '/core/Common.php';
require_once PIWIK_INCLUDE_PATH . "/core/DataTable/Bridges.php";

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
 * _Note: For convenience, [anonymous functions](https://www.php.net/manual/en/functions.anonymous.php)
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
 *         $dataTable = Archive::createDataTableFromArchive('MyPlugin_MyReport', $idSite, $period, $date, $segment, $expanded);
 *         $dataTable->filter('Sort', array(Metrics::INDEX_NB_VISITS, 'desc', $naturalSort = false, $expanded));
 *         $dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'url', __NAMESPACE__ . '\getUrlFromLabelForMyReport'));
 *         return $dataTable;
 *     }
 *
 * @implements \IteratorAggregate<int, Row>
 * @implements \ArrayAccess<int, Row>
 * @api
 */
class DataTable implements DataTableInterface, \IteratorAggregate, \ArrayAccess
{
    public const MAX_DEPTH_DEFAULT = 15;

    /** Name for metadata that describes the archiving state of a report */
    public const ARCHIVE_STATE_METADATA_NAME = 'archive_state';

    /** Name for metadata that describes when a report was archived. */
    public const ARCHIVED_DATE_METADATA_NAME = 'ts_archived';

    /** Name for metadata that describes which columns are empty and should not be shown. */
    public const EMPTY_COLUMNS_METADATA_NAME = 'empty_column';

    /** Name for metadata that describes the number of rows that existed before the Limit filter was applied. */
    public const TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME = 'total_rows_before_limit';

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
    public const COLUMN_AGGREGATION_OPS_METADATA_NAME = 'column_aggregation_ops';

    /**
     * Name for metadata that stores array of generic filters that should not be run on the table.
     */
    public const GENERIC_FILTERS_TO_DISABLE_METADATA_NAME = 'generic_filters_to_disable';

    /** The ID of the Summary Row. */
    public const ID_SUMMARY_ROW = -1;

    /** The ID of the Totals Row (matches LABEL_TOTALS_ROW). */
    public const ID_TOTALS_ROW = -2;

    /**
     * The ID of the special metadata row. This row only exists in the serialized row data and stores the datatable metadata.
     *
     * This allows us to save datatable metadata in archive data.
     */
    public const ID_ARCHIVED_METADATA_ROW = -3;

    /** The original label of the Summary Row. */
    public const LABEL_SUMMARY_ROW = -1;
    public const LABEL_TOTALS_ROW = -2;
    public const LABEL_ARCHIVED_METADATA_ROW = '__datatable_metadata__';

    /**
     * Name for metadata that contains extra {@link Piwik\Plugin\ProcessedMetric}s for a DataTable.
     * These metrics will be added in addition to the ones specified in the table's associated
     * {@link Piwik\Plugin\Report} class.
     */
    public const EXTRA_PROCESSED_METRICS_METADATA_NAME = 'extra_processed_metrics';

    public const ROW_IDENTIFIER_METADATA_NAME = 'rowIdentifier';

    /** Magic prefix that identifies a columnar-encoded blob (3 bytes: DEL 'C' DEL). Never produced by PHP serialize(). */
    public const COLUMNAR_BLOB_MAGIC = "\x7fC\x7f";

    /**
     * Maximum nesting level.
     * @var int
     */
    private static $maximumDepthLevelAllowed = self::MAX_DEPTH_DEFAULT;

    /**
     * rowId → packed int-indexed value array
     *
     * @var array<int, array<int, mixed>>
     */
    protected $rows = [];

    // ── Columnar packed storage ───────────────────────────────────────────────

    /**
     * Shared column schema — established on first addRow().
     *
     * @internal
     * @var string[]
     */
    public $columnNames = [];

    /**
     * Reverse map: column name → integer index into packed value arrays.
     *
     * @internal
     * @var array<string, int>
     */
    public $columnIndex = [];

    /**
     * Monotonically increasing row-ID counter.
     * Using count($this->rows) would produce collisions after deleteRow().
     *
     * @internal
     * @var int
     */
    protected $nextRowId = 0;

    /**
     * Sparse map of rowId → subtable ID (omit key when null).
     *
     * @internal
     * @var array<int, int>
     */
    protected $rowSubtableIds = [];

    /**
     * Set of rowIds whose subtable is actually loaded in Manager (as opposed to
     * merely stored as a serialised integer ID from a deserialised blob).
     *
     * In the original Row-based storage, Row::$isSubtableLoaded tracked this per
     * Row object.  Rows created via addRowsFromSerializedArray() had
     * $isSubtableLoaded = false, so Row::getSubtable() correctly returned false.
     * In packed storage there are no Row objects; this array replaces that flag.
     *
     * @internal
     * @var array<int, true>
     */
    protected $rowSubtableIdsLoaded = [];

    /**
     * Sparse map of rowId → metadata array (omit key when empty).
     *
     * @internal
     * @var array<int, array<string, mixed>>
     */
    protected $rowMetadata = [];


    /** @internal @var array<int, mixed>|null */
    protected $summaryRowData           = null;
    /** @internal @var array<string, mixed> */
    protected $summaryRowMetadata       = [];
    /** @internal @var int|null */
    protected $summarySubtableId        = null;
    /** @internal @var bool  true when summarySubtableId references a loaded DataTable in Manager */
    protected $summarySubtableIdLoaded  = false;

    /** @internal @var array<int, mixed>|null */
    protected $totalsRowData           = null;
    /** @internal @var array<string, mixed> */
    protected $totalsRowMetadata       = [];
    /** @internal @var int|null */
    protected $totalsSubtableId        = null;
    /** @internal @var bool  true when totalsSubtableId references a loaded DataTable in Manager */
    protected $totalsSubtableIdLoaded  = false;
    // ── End columnar packed storage ───────────────────────────────────────────

    /**
     * Id assigned to the DataTable, used to lookup the table using the DataTable_Manager
     *
     * @var int
     */
    protected $currentId;

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
     * @var string|false
     */
    protected $tableSortedBy = false;

    /**
     * List of BaseFilter queued to this table
     *
     * @var array<array{className: string|callable, parameters: array<scalar, mixed>}>
     */
    protected $queuedFilters = array();

    /**
     * List of disabled filter names eg 'Limit' or 'Sort'
     *
     * @var String[]
     */
    protected $disabledFilters = array();

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
     * @var array<string, int>
     */
    protected $rowsIndexByLabel = array();

    /**
     * Table metadata. Read [this](#class-desc-the-basics) to learn more.
     *
     * Any data that describes the data held in the table's rows should go here.
     *
     * Note: this field is protected so derived classes will serialize it.
     *
     * @var array<string, mixed>
     */
    protected $metadata = array();

    /**
     * Maximum number of rows allowed in this datatable (including the summary row).
     * If adding more rows is attempted, the extra rows get summed to the summary row.
     *
     * @var int
     */
    protected $maximumAllowedRows = 0;

    /** @var bool */
    protected $isBuiltWithoutArchives = true;

    /** @var bool Sentinel used by __destruct() to prevent double-destruction. */
    protected $isDestroyed = false;

    // ── Columnar schema management (private) ─────────────────────────────────

    private function establishSchema(array $columnNames): void
    {
        $this->columnNames = array_values($columnNames);
        $this->columnIndex = array_flip($this->columnNames);
    }

    private function extendSchema(string $newColName): void
    {
        $idx = count($this->columnNames);
        $this->columnNames[]            = $newColName;
        $this->columnIndex[$newColName] = $idx;
        // Existing rows are NOT backfilled. New rows built by addRow() use array_fill
        // and are always dense. Rows added before this extension stay sparse (missing
        // slot $idx); getPackedRow() / getPackedValue() use `?? null` so they return
        // null (= absent) for that slot without error.
    }

    // ── Columnar @internal helpers (public for ViewRow access) ───────────────

    /** @internal */
    public function getPackedValue(int $rowId, string $colName)
    {
        $idx = $this->columnIndex[$colName] ?? null;
        if ($idx === null) {
            return null;
        }
        if ($rowId === self::ID_SUMMARY_ROW) {
            return $this->summaryRowData[$idx] ?? null;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            return $this->totalsRowData[$idx] ?? null;
        }
        return $this->rows[$rowId][$idx] ?? null;
    }

    /** @internal */
    public function setPackedValue(int $rowId, string $colName, $value): void
    {
        if (!isset($this->columnIndex[$colName])) {
            $this->extendSchema($colName);
        }
        $idx = $this->columnIndex[$colName];
        if ($rowId === self::ID_SUMMARY_ROW) {
            $this->summaryRowData[$idx] = $value;
        } elseif ($rowId === self::ID_TOTALS_ROW) {
            $this->totalsRowData[$idx] = $value;
        } else {
            $this->rows[$rowId][$idx] = $value;
        }
    }

    /** @internal */
    public function rowColumnExists(int $rowId, string $colName): bool
    {
        $idx = $this->columnIndex[$colName] ?? null;
        if ($idx === null) {
            return false;
        }
        // null == absent: dense arrays always carry the key, but null means "not set".
        if ($rowId === self::ID_SUMMARY_ROW) {
            return ($this->summaryRowData[$idx] ?? null) !== null;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            return ($this->totalsRowData[$idx] ?? null) !== null;
        }
        return ($this->rows[$rowId][$idx] ?? null) !== null;
    }

    /** @internal */
    public function getPackedRow(int $rowId): array
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            $packed = $this->summaryRowData;
        } elseif ($rowId === self::ID_TOTALS_ROW) {
            $packed = $this->totalsRowData;
        } else {
            $packed = $this->rows[$rowId] ?? null;
        }
        if (empty($this->columnNames) || $packed === null) {
            return [];
        }
        $result = [];
        foreach ($this->columnNames as $idx => $name) {
            $val = $packed[$idx] ?? null;
            if ($val !== null) {
                $result[$name] = $val;
            }
        }
        return $result;
    }

    /** @internal */
    public function deletePackedColumn(int $rowId, string $colName): bool
    {
        $idx = $this->columnIndex[$colName] ?? null;
        if ($idx === null) {
            return false;
        }
        // Set to null (= absent) to keep the array dense; don't unset the key.
        if ($rowId === self::ID_SUMMARY_ROW) {
            if ($this->summaryRowData !== null) {
                $this->summaryRowData[$idx] = null;
            }
        } elseif ($rowId === self::ID_TOTALS_ROW) {
            if ($this->totalsRowData !== null) {
                $this->totalsRowData[$idx] = null;
            }
        } else {
            if (isset($this->rows[$rowId])) {
                $this->rows[$rowId][$idx] = null;
            }
        }
        return true;
    }

    /** @internal */
    public function getRowMetadata(int $rowId): array
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            return $this->summaryRowMetadata;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            return $this->totalsRowMetadata;
        }
        return $this->rowMetadata[$rowId] ?? [];
    }

    /** @internal */
    public function setRowMetadata(int $rowId, array $meta): void
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            $this->summaryRowMetadata = $meta;
            return;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            $this->totalsRowMetadata = $meta;
            return;
        }
        if (empty($meta)) {
            unset($this->rowMetadata[$rowId]);
        } else {
            $this->rowMetadata[$rowId] = $meta;
        }
    }

    /** @internal */
    public function setRowMetadataValue(int $rowId, string $key, $value): void
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            $this->summaryRowMetadata[$key] = $value;
            return;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            $this->totalsRowMetadata[$key] = $value;
            return;
        }
        $this->rowMetadata[$rowId][$key] = $value;
    }

    /** @internal */
    public function deleteRowMetadataKey(int $rowId, string $key): bool
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            $exists = isset($this->summaryRowMetadata[$key]);
            unset($this->summaryRowMetadata[$key]);
            return $exists;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            $exists = isset($this->totalsRowMetadata[$key]);
            unset($this->totalsRowMetadata[$key]);
            return $exists;
        }
        $exists = isset($this->rowMetadata[$rowId][$key]);
        unset($this->rowMetadata[$rowId][$key]);
        if (empty($this->rowMetadata[$rowId])) {
            unset($this->rowMetadata[$rowId]);
        }
        return $exists;
    }

    /** @internal */
    public function getRowSubtableId(int $rowId): ?int
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            return $this->summarySubtableId;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            return $this->totalsSubtableId;
        }
        return $this->rowSubtableIds[$rowId] ?? null;
    }

    /** @internal */
    public function setRowSubtableId(int $rowId, ?int $id): void
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            $this->summarySubtableId       = $id;
            $this->summarySubtableIdLoaded = ($id !== null); // always a loaded context
            return;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            $this->totalsSubtableId       = $id;
            $this->totalsSubtableIdLoaded = ($id !== null);
            return;
        }
        if ($id === null) {
            unset($this->rowSubtableIds[$rowId]);
            unset($this->rowSubtableIdsLoaded[$rowId]);
        } else {
            $this->rowSubtableIds[$rowId]       = $id;
            $this->rowSubtableIdsLoaded[$rowId] = true; // always a loaded context
        }
    }

    /**
     * Returns true when the subtable for $rowId was set via setSubtable() (i.e. the
     * DataTable is actually registered in Manager), as opposed to merely stored from
     * a deserialised blob (where the integer ID is present but no DataTable was ever
     * loaded).  Mirrors the per-Row $isSubtableLoaded flag from the original storage.
     *
     * @internal
     */
    public function isRowSubtableLoaded(int $rowId): bool
    {
        if ($rowId === self::ID_SUMMARY_ROW) {
            return $this->summarySubtableIdLoaded;
        }
        if ($rowId === self::ID_TOTALS_ROW) {
            return $this->totalsSubtableIdLoaded;
        }
        return isset($this->rowSubtableIdsLoaded[$rowId]);
    }

    /** @internal */
    public function getColumnCount(): int
    {
        return count($this->columnNames);
    }

    // ── End columnar helpers ──────────────────────────────────────────────────

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
        // destruct can be called several times; use $isDestroyed as sentinel
        // (avoids unset($this->rows) which would cause "Undefined property" errors
        // if any method is called on the object after destruction)
        if ($depth < self::$maximumDepthLevelAllowed && !$this->isDestroyed) {
            $this->isDestroyed = true;
            $depth++;
            Manager::getInstance()->setTableDeleted($this->currentId);
            $depth--;
        }
    }

    /**
     * Clone. Called when cloning the datatable. We need to make sure to create a new datatableId.
     * If we do not increase tableId it can result in segmentation faults when destructing a datatable.
     */
    public function __clone()
    {
        // registers this instance to the manager
        $this->currentId = Manager::getInstance()->addTable($this);
    }

    /**
     * @return void
     */
    public function setLabelsHaveChanged()
    {
        $this->indexNotUpToDate = true;
    }

    /**
     * does not update the summary row!
     * @param Row[]|null $rows
     * @return void
     * @ignore
     */
    public function setRows($rows)
    {
        if (!is_array($rows)) {
            $this->rows                 = [];
            $this->rowSubtableIds       = [];
            $this->rowSubtableIdsLoaded = [];
            $this->rowMetadata          = [];
            $this->columnNames          = [];
            $this->columnIndex          = [];
            $this->nextRowId            = 0;
            $this->indexNotUpToDate     = true;
            return;
        }

        // Materialize row data BEFORE clearing storage. When $rows contains ViewRow
        // objects that belong to this very table, clearing storage first would make
        // their getColumns() / getMetadata() calls return empty results.
        // We also capture the subtable-loaded flag so it can be restored faithfully
        // after the storage is wiped and rows are re-inserted.
        $materialized = [];
        foreach ($rows as $row) {
            $data                  = $row->export();
            $data['_subtableLoaded'] = $row->isSubtableLoaded();
            $materialized[]        = $data;
        }

        // Snapshot summary/totals as raw data before wiping storage.
        // We cannot use materialiseRow() here because that now binds the Row to
        // the DataTable; after storage is cleared, getColumns() on the bound Row
        // would return [] instead of the saved columns.
        $savedSummary = null;
        if ($this->summaryRowData !== null) {
            $savedSummary = [
                Row::COLUMNS              => $this->getPackedRow(self::ID_SUMMARY_ROW),
                Row::METADATA             => $this->summaryRowMetadata,
                Row::DATATABLE_ASSOCIATED => $this->summarySubtableId,
                '_loaded'                 => $this->summarySubtableIdLoaded,
            ];
        }
        $savedTotals = null;
        if ($this->totalsRowData !== null) {
            $savedTotals = [
                Row::COLUMNS              => $this->getPackedRow(self::ID_TOTALS_ROW),
                Row::METADATA             => $this->totalsRowMetadata,
                Row::DATATABLE_ASSOCIATED => $this->totalsSubtableId,
                '_loaded'                 => $this->totalsSubtableIdLoaded,
            ];
        }

        $this->rows                   = [];
        $this->rowSubtableIds         = [];
        $this->rowSubtableIdsLoaded   = [];
        $this->rowMetadata            = [];
        $this->columnNames            = [];
        $this->columnIndex            = [];
        $this->nextRowId              = 0;
        $this->indexNotUpToDate       = true;
        $this->summaryRowData         = null;
        $this->summaryRowMetadata     = [];
        $this->summarySubtableId      = null;
        $this->summarySubtableIdLoaded = false;
        $this->totalsRowData          = null;
        $this->totalsRowMetadata      = [];
        $this->totalsSubtableId       = null;
        $this->totalsSubtableIdLoaded = false;

        foreach ($materialized as $data) {
            $isLoaded  = $data['_subtableLoaded'];
            unset($data['_subtableLoaded']);
            $newRow = new Row($data);
            // Restore the subtable-loaded flag before addRow() reads isSubtableLoaded().
            // Row($data) constructor stores the ID but leaves isSubtableLoaded=false.
            if ($isLoaded && $newRow->subtableId !== null) {
                $newRow->setLoadedSubtableId($newRow->subtableId);
            }
            $this->addRow($newRow);
        }

        if ($savedSummary !== null) {
            $summaryRow = new Row([Row::COLUMNS => $savedSummary[Row::COLUMNS], Row::METADATA => $savedSummary[Row::METADATA]]);
            if ($savedSummary[Row::DATATABLE_ASSOCIATED] !== null) {
                if ($savedSummary['_loaded']) {
                    $summaryRow->setLoadedSubtableId($savedSummary[Row::DATATABLE_ASSOCIATED]);
                } else {
                    $summaryRow->setNonLoadedSubtableId($savedSummary[Row::DATATABLE_ASSOCIATED]);
                }
            }
            $this->addSummaryRow($summaryRow);
        }
        if ($savedTotals !== null) {
            $totalsRow = new Row([Row::COLUMNS => $savedTotals[Row::COLUMNS], Row::METADATA => $savedTotals[Row::METADATA]]);
            if ($savedTotals[Row::DATATABLE_ASSOCIATED] !== null) {
                if ($savedTotals['_loaded']) {
                    $totalsRow->setLoadedSubtableId($savedTotals[Row::DATATABLE_ASSOCIATED]);
                } else {
                    $totalsRow->setNonLoadedSubtableId($savedTotals[Row::DATATABLE_ASSOCIATED]);
                }
            }
            $this->setTotalsRow($totalsRow);
        }
    }

    /**
     * Sorts the DataTable rows using the supplied callback function.
     *
     * @param callable $functionCallback A comparison callback compatible with {@link usort}.
     * @param string $columnSortedBy The column name `$functionCallback` sorts by. This is stored
     *                               so we can determine how the DataTable was sorted in the future.
     * @return void
     */
    public function sort($functionCallback, $columnSortedBy)
    {
        $this->setTableSortedBy($columnSortedBy);

        // Sort by row IDs using ViewRow proxies so callbacks receive Row objects.
        $rowIds = array_keys($this->rows);
        usort($rowIds, function (int $a, int $b) use ($functionCallback): int {
            return $functionCallback(new ViewRow($this, $a), new ViewRow($this, $b));
        });
        $sorted = [];
        foreach ($rowIds as $id) {
            $sorted[$id] = $this->rows[$id];
        }
        $this->rows = $sorted;

        if ($this->isSortRecursiveEnabled()) {
            foreach ($this->getRowsWithoutSummaryRow() as $row) {
                $subTable = $row->getSubtable();
                if ($subTable) {
                    $subTable->enableRecursiveSort();
                    $subTable->sort($functionCallback, $columnSortedBy);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function setTotalsRow(Row $totalsRow)
    {
        $columns = $totalsRow->getColumns();
        foreach ($columns as $name => $_) {
            if (!isset($this->columnIndex[$name])) {
                $this->extendSchema($name);
            }
        }
        // Pack the totals row as a dense array (null = absent).
        $colCount = count($this->columnNames);
        $this->totalsRowData = $colCount > 0 ? array_fill(0, $colCount, null) : [];
        foreach ($columns as $name => $val) {
            $this->totalsRowData[$this->columnIndex[$name]] = $val;
        }
        $this->totalsRowMetadata      = $totalsRow->getMetadata();
        $this->totalsSubtableId       = $totalsRow->subtableId;
        $this->totalsSubtableIdLoaded = $totalsRow->isSubtableLoaded();
    }

    /**
     * @return Row|null
     */
    public function getTotalsRow()
    {
        if ($this->totalsRowData === null) {
            return null;
        }
        return new ViewRow($this, self::ID_TOTALS_ROW);
    }

    /**
     * @return Row|null
     */
    public function getSummaryRow()
    {
        if ($this->summaryRowData === null) {
            return null;
        }
        return new ViewRow($this, self::ID_SUMMARY_ROW);
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
     * @return void
     */
    public function enableRecursiveSort()
    {
        $this->enableRecursiveSort = true;
    }

    /**
     * @return bool
     * @ignore
     */
    public function isSortRecursiveEnabled()
    {
        return $this->enableRecursiveSort === true;
    }

    /**
     * @param string $column
     * @return void
     * @ignore
     */
    public function setTableSortedBy($column)
    {
        $this->indexNotUpToDate = true;
        $this->tableSortedBy = $column;
    }

    /**
     * Enables recursive filtering. If this method is called then the {@link filter()} method
     * will apply filters to every subtable in addition to this instance.
     * @return void
     */
    public function enableRecursiveFilters()
    {
        $this->enableRecursiveFilters = true;
    }

    /**
     * @return void
     * @ignore
     */
    public function disableRecursiveFilters()
    {
        $this->enableRecursiveFilters = false;
    }

    /**
     * Applies a filter to this datatable.
     *
     * If {@link enableRecursiveFilters()} was called, the filter will be applied
     * to all subtables as well.
     *
     * @param string|callable $className Class name, eg. `"Sort"` or "Piwik\DataTable\Filters\Sort"`. If no
     *                                  namespace is supplied, `Piwik\DataTable\Filter` is assumed. This parameter
     *                                  can also be a closure that takes a DataTable as its first parameter.
     * @param array $parameters Array of extra parameters to pass to the filter.
     * @return void
     */
    public function filter($className, $parameters = array())
    {
        if (
            $className instanceof \Closure
            || is_array($className)
        ) {
            array_unshift($parameters, $this);
            call_user_func_array($className, $parameters);
            return;
        }

        if (!is_string($className)) {
            throw new Exception('Unsupported filter provided');
        }

        if (in_array($className, $this->disabledFilters)) {
            return;
        }

        if (!class_exists($className, true)) {
            $className = 'Piwik\DataTable\Filter\\' . $className;
        }
        $reflectionObj = new ReflectionClass($className);

        // the first parameter of a filter is the DataTable
        // we add the current datatable as the parameter
        $parameters = array_merge(array($this), $parameters);

        /** @var BaseFilter $filter */
        $filter = $reflectionObj->newInstanceArgs($parameters);

        $filter->enableRecursive($this->enableRecursiveFilters);

        $filter->filter($this);
    }

    /**
     * Invokes `$filter` with this table and every table in `$otherTables`. The result of `$filter()` is returned.
     *
     * This method is used to iterate over multiple DataTable\Map's concurrently.
     *
     * See {@link Map::multiFilter()} for more information.
     *
     * @param DataTable[] $otherTables
     * @param callable $filter A function like `function (DataTable $thisTable, $otherTable1, $otherTable2) {}`.
     * @return mixed The result of $filter.
     */
    public function multiFilter($otherTables, $filter)
    {
        return $filter(...array_merge([$this], $otherTables));
    }

    /**
     * Applies a filter to all subtables but not to this datatable.
     *
     * @param string|callable $className Class name, eg. `"Sort"` or "Piwik\DataTable\Filters\Sort"`. If no
     *                                  namespace is supplied, `Piwik\DataTable\BaseFilter` is assumed. This parameter
     *                                  can also be a closure that takes a DataTable as its first parameter.
     * @param array $parameters Array of extra parameters to pass to the filter.
     * @return void
     */
    public function filterSubtables($className, $parameters = array())
    {
        foreach ($this->getRowsWithoutSummaryRow() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                $subtable->filter($className, $parameters);
                $subtable->filterSubtables($className, $parameters);
            }
        }
    }

    /**
     * Adds a filter and a list of parameters to the list of queued filters of all subtables. These filters will be
     * executed when {@link applyQueuedFilters()} is called.
     *
     * Filters that prettify the column values or don't need the full set of rows should be queued. This
     * way they will be run after the table is truncated which will result in better performance.
     *
     * @param string|callable $className The class name of the filter, eg. `'Limit'`.
     * @param array $parameters The parameters to give to the filter, eg. `array($offset, $limit)` for the Limit filter.
     * @return void
     */
    public function queueFilterSubtables($className, $parameters = array())
    {
        foreach ($this->getRowsWithoutSummaryRow() as $row) {
            $subtable = $row->getSubtable();
            if ($subtable) {
                $subtable->queueFilter($className, $parameters);
                $subtable->queueFilterSubtables($className, $parameters);
            }
        }
    }

    /**
     * Adds a filter and a list of parameters to the list of queued filters. These filters will be
     * executed when {@link applyQueuedFilters()} is called.
     *
     * Filters that prettify the column values or don't need the full set of rows should be queued. This
     * way they will be run after the table is truncated which will result in better performance.
     *
     * @param string|callable $className The class name of the filter, eg. `'Limit'`.
     * @param array $parameters The parameters to give to the filter, eg. `array($offset, $limit)` for the Limit filter.
     * @return void
     */
    public function queueFilter($className, $parameters = array())
    {
        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }
        $this->queuedFilters[] = ['className' => $className, 'parameters' => $parameters];
    }

    /**
     * Disable a specific filter to run on this DataTable in case you have already applied this filter or if you will
     * handle this filter manually by using a custom filter. Be aware if you disable a given filter, that filter won't
     * be ever executed. Even if another filter calls this filter on the DataTable.
     *
     * @param string $className  eg 'Limit' or 'Sort'. Passing a `Closure` or an `array($class, $methodName)` is not
     *                           supported yet. We check for exact match. So if you disable 'Limit' and
     *                           call `->filter('Limit')` this filter won't be executed. If you call
     *                           `->filter('Piwik\DataTable\Filter\Limit')` that filter will be executed. See it as a
     *                           feature.
     * @return void
     * @ignore
     */
    public function disableFilter($className)
    {
        $this->disabledFilters[] = $className;
    }

    /**
     * Applies all filters that were previously queued to the table. See {@link queueFilter()}
     * for more information.
     * @return void
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
     * @return void
     * @throws Exception
     */
    public function addDataTable(DataTable $tableToSum)
    {
        if ($tableToSum instanceof Simple) {
            if ($tableToSum->getRowsCount() > 1) {
                throw new Exception("Did not expect a Simple table with more than one row in addDataTable()");
            }
            $row = $tableToSum->getFirstRow();
            $this->aggregateRowFromSimpleTable($row);
        } else {
            $columnAggregationOps = $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME);
            foreach ($tableToSum->getRowsWithoutSummaryRow() as $row) {
                $this->aggregateRowWithLabel($row, $columnAggregationOps);
            }
            // we do not use getRows() as this method might get called 100k times when aggregating many datatables and
            // this takes a lot of time.
            $row = $tableToSum->getRowFromId(DataTable::ID_SUMMARY_ROW);
            if ($row) {
                $summaryViewRow = $this->summaryRowData !== null
                    ? new ViewRow($this, self::ID_SUMMARY_ROW)
                    : null;
                $this->aggregateRow($summaryViewRow, $row, $columnAggregationOps, true);
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
        if (is_int($rowId) && isset($this->rows[$rowId])) {
            return new ViewRow($this, $rowId);
        }
        if ($rowId == self::ID_SUMMARY_ROW && $this->summaryRowData !== null) {
            return new ViewRow($this, self::ID_SUMMARY_ROW);
        }
        if (
            empty($rowId)
            && $this->totalsRowData !== null
            && $label == ($this->getPackedValue(self::ID_TOTALS_ROW, 'label'))
        ) {
            return new ViewRow($this, self::ID_TOTALS_ROW);
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
     * @return int|false The row ID.
     */
    public function getRowIdFromLabel($label)
    {
        if ($this->indexNotUpToDate) {
            $this->rebuildIndex();
        }

        $label = (string) $label;

        if (!isset($this->rowsIndexByLabel[$label])) {
            // in case label is '-1' and there is no normal row w/ that label. Note: this is for BC since
            // in the past, it was possible to get the summary row by searching for the label '-1'
            if (
                $label == self::LABEL_SUMMARY_ROW
                && $this->summaryRowData !== null
            ) {
                return self::ID_SUMMARY_ROW;
            }

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
        $clone = new DataTable();
        if ($keepFilters) {
            $clone->queuedFilters = $this->queuedFilters;
        }
        $clone->metadata = $this->metadata;
        return $clone;
    }

    /**
     * Rebuilds the index used to lookup a row by label
     * @return void
     * @internal
     */
    public function rebuildIndex()
    {
        $this->rowsIndexByLabel = [];
        $this->rebuildIndexContinuously = true;

        $labelIdx = $this->columnIndex['label'] ?? null;
        if ($labelIdx === null) {
            $this->indexNotUpToDate = false;
            return;
        }

        foreach ($this->rows as $id => $packed) {
            $label = $packed[$labelIdx] ?? null;
            if ($label !== null) {
                $this->rowsIndexByLabel[(string) $label] = $id;
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
        if ($id === self::ID_SUMMARY_ROW) {
            return $this->summaryRowData !== null ? new ViewRow($this, self::ID_SUMMARY_ROW) : false;
        }
        return isset($this->rows[$id]) ? new ViewRow($this, $id) : false;
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
        foreach ($this->rowSubtableIds as $rowId => $subtableId) {
            if ($subtableId === $idSubTable) {
                return new ViewRow($this, $rowId);
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
     * @return Row `$row` or the summary row if we're at the maximum number of rows.
     */
    public function addRow(Row $row)
    {
        $columns = $row->getColumns();

        // if there is a upper limit on the number of allowed rows and the table is full,
        // add the new row to the summary row
        if (
            $this->maximumAllowedRows > 0
            && $this->getRowsCount() >= $this->maximumAllowedRows - 1
        ) {
            if ($this->summaryRowData === null) {
                // create the summary row if necessary
                $summaryColumns = array('label' => self::LABEL_SUMMARY_ROW) + $columns;
                $this->addSummaryRow(new Row(array(Row::COLUMNS => $summaryColumns)));
            } else {
                $summaryViewRow = new ViewRow($this, self::ID_SUMMARY_ROW);
                $summaryViewRow->sumRow(
                    $row,
                    $enableCopyMetadata = false,
                    $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME)
                );
            }
            return new ViewRow($this, self::ID_SUMMARY_ROW);
        }

        // Establish / extend schema
        if (empty($this->columnNames) && !empty($columns)) {
            $this->establishSchema(array_keys($columns));
        } else {
            foreach ($columns as $name => $_) {
                if (!isset($this->columnIndex[$name])) {
                    $this->extendSchema($name);
                }
            }
        }

        // Build a dense packed array (all C schema slots filled; null = absent).
        // Dense consecutive-integer keys let PHP use its fast "packed" layout
        // instead of a hashtable, giving the per-row memory savings Plan 2 modelled.
        $colCount = count($this->columnNames);
        $packed   = $colCount > 0 ? array_fill(0, $colCount, null) : [];
        foreach ($columns as $name => $val) {
            $packed[$this->columnIndex[$name]] = $val;
        }

        $rowId = $this->nextRowId++;
        $this->rows[$rowId] = $packed;

        // Copy subtable ID and metadata.
        // Transfer subtable ownership: clear isSubtableLoaded on the source Row so that
        // Row::__destruct() does not call deleteTable() when $row is garbage collected.
        $subtableId       = $row->subtableId;
        $subtableIsLoaded = $row->isSubtableLoaded(); // capture BEFORE removeSubtable()
        if ($subtableId !== null) {
            $this->rowSubtableIds[$rowId] = $subtableId;
            if ($subtableIsLoaded) {
                // Only mark as loaded when the Row's subtable is actually registered in
                // Manager.  Deserialized rows (from addRowsFromSerializedArray) have
                // isSubtableLoaded=false because the Row constructor sets $subtableId
                // directly without calling setSubtable().  Preserving that distinction
                // prevents ViewRow::getSubtable() from accidentally returning an unrelated
                // active DataTable whose Manager ID happens to match the serialised ID.
                $this->rowSubtableIdsLoaded[$rowId] = true;
            }
            $row->removeSubtable();
        }
        $meta = $row->getMetadata();
        if (!empty($meta)) {
            $this->rowMetadata[$rowId] = $meta;
        }

        // Update label index
        if (!$this->indexNotUpToDate && $this->rebuildIndexContinuously) {
            $label = $columns['label'] ?? null;
            if ($label !== null) {
                $this->rowsIndexByLabel[(string) $label] = $rowId;
            }
        } else {
            $this->indexNotUpToDate = true;
        }

        // Bind the original Row to this DataTable's packed storage so that external
        // references to $row remain "live": mutations made via $row are visible through
        // the packed storage, and subsequent $row->setSubtable() calls propagate back.
        // Return $row so identity checks (e.g. walkPath's overflow detection) work:
        //   $next = $table->addRow($row); if ($next !== $row) { /* table full */ }
        $row->bindToTable($this, $rowId);
        $row->bindSubtableCallback(function (int $newSubtableId) use ($rowId) {
            $this->setRowSubtableId($rowId, $newSubtableId);
        });

        return $row;
    }

    /**
     * Sets the summary row.
     *
     * _Note: A DataTable can have only one summary row._
     *
     * @return Row Returns `$row`.
     */
    public function addSummaryRow(Row $row)
    {
        $columns = $row->getColumns();

        // Extend schema for any new columns
        foreach ($columns as $name => $_) {
            if (!isset($this->columnIndex[$name])) {
                $this->extendSchema($name);
            }
        }

        // Pack the summary row as a dense array (null = absent).
        $colCount = count($this->columnNames);
        $this->summaryRowData = $colCount > 0 ? array_fill(0, $colCount, null) : [];
        foreach ($columns as $name => $val) {
            $this->summaryRowData[$this->columnIndex[$name]] = $val;
        }
        $this->summaryRowMetadata      = $row->getMetadata();
        $this->summarySubtableId       = $row->subtableId;
        $this->summarySubtableIdLoaded = $row->isSubtableLoaded(); // capture BEFORE removeSubtable

        // Transfer subtable ownership: prevent Row::__destruct() from deleting the subtable.
        if ($row->subtableId !== null) {
            $row->removeSubtable();
        }

        // NOTE: the summary row does not go in the index, since it will overwrite rows w/ label == -1

        // Bind the original Row to the DataTable's summary-row metadata storage.
        $row->bindToTable($this, self::ID_SUMMARY_ROW);
        $row->bindSubtableCallback(function (int $newSubtableId) {
            $this->summarySubtableId       = $newSubtableId;
            $this->summarySubtableIdLoaded = ($newSubtableId !== null);
        });

        return new ViewRow($this, self::ID_SUMMARY_ROW);
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
     * @return void
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
     * @return void
     */
    public function addRowFromSimpleArray($row)
    {
        $this->addRowsFromSimpleArray(array($row));
    }

    /**
     * Returns the array of Rows.
     * Internal logic in Matomo core should avoid using this method as it is time and memory consuming when being
     * executed thousands of times. The alternative is to use {@link getRowsWithoutSummaryRow()} + get the summary
     * row manually.
     *
     * @return Row[]
     */
    public function getRows()
    {
        $result = [];
        foreach (array_keys($this->rows) as $id) {
            $result[$id] = new ViewRow($this, $id);
        }
        if ($this->summaryRowData !== null) {
            $result[self::ID_SUMMARY_ROW] = new ViewRow($this, self::ID_SUMMARY_ROW);
        }
        return $result;
    }

    /**
     * Materialise a packed row into a Row object.
     *
     * The returned Row stores columns in its ArrayObject (for PHPUnit assertEquals
     * and Twig attribute access) and is also bound to this DataTable's packed storage
     * so that column mutations (setColumn, deleteColumn) and subtable assignments
     * propagate back immediately.
     *
     * PHPUnit's ObjectComparator compares ArrayObject subclasses via (array)$obj,
     * which returns only the ArrayObject internal items — NOT class properties such
     * as _boundTable.  Therefore binding does NOT break assertEquals.
     *
     * @internal
     */
    private function materialiseRow(int $rowId): Row
    {
        $columns    = $this->getPackedRow($rowId);
        $meta       = $this->getRowMetadata($rowId);
        $subtableId = $this->getRowSubtableId($rowId);

        // Binding-only: no column snapshot in ArrayObject.
        // Column reads/writes go through bound packed storage (offsetGet/Set intercepts).
        // Metadata snapshot is still set so Row::getMetadata() works on the Row object
        // before it is bound (constructor path) — binding overrides reads/writes after.
        $row = new Row([Row::METADATA => $meta]);

        // Bind to packed storage so column mutations propagate back.
        $row->bindToTable($this, $rowId);

        if ($subtableId !== null) {
            if ($this->isRowSubtableLoaded($rowId)) {
                $row->setLoadedSubtableId($subtableId);
            } else {
                $row->setNonLoadedSubtableId($subtableId);
            }
        }

        // Bind a subtable callback so setSubtable() propagates back.
        $row->bindSubtableCallback(function (int $newSubtableId) use ($rowId) {
            $this->setRowSubtableId($rowId, $newSubtableId);
        });

        return $row;
    }

    /**
     * @return Row[]
     * @ignore
     */
    public function getRowsWithoutSummaryRow()
    {
        $result = [];
        foreach (array_keys($this->rows) as $id) {
            $result[$id] = new ViewRow($this, $id);
        }
        return $result;
    }

    /**
     * @return int
     * @ignore
     */
    public function getRowsCountWithoutSummaryRow()
    {
        return count($this->rows);
    }

    /**
     * Returns an array containing all column values for the requested column.
     *
     * @param string|int $name The column name.
     * @return array The array of column values.
     */
    public function getColumn($name)
    {
        // Use ViewRow proxies instead of materialised snapshot rows — no column data copying.
        $columnValues = [];
        foreach ($this->getRowsWithoutSummaryRow() as $row) {
            $columnValues[] = $row->getColumn($name);
        }
        if ($this->summaryRowData !== null) {
            $columnValues[] = (new ViewRow($this, self::ID_SUMMARY_ROW))->getColumn($name);
        }
        return $columnValues;
    }

    /**
     * Returns an array containing all column values of columns whose name starts with `$name`.
     *
     * @param string $namePrefix The column name prefix.
     * @return array The array of column values.
     */
    public function getColumnsStartingWith($namePrefix)
    {
        $columnValues = [];
        foreach ($this->getRowsWithoutSummaryRow() as $row) {
            foreach ($row->getColumns() as $column => $value) {
                if (strpos($column, $namePrefix) === 0) {
                    $columnValues[] = $row->getColumn($column);
                }
            }
        }
        if ($this->summaryRowData !== null) {
            $summaryRow = new ViewRow($this, self::ID_SUMMARY_ROW);
            foreach ($summaryRow->getColumns() as $column => $value) {
                if (strpos($column, $namePrefix) === 0) {
                    $columnValues[] = $summaryRow->getColumn($column);
                }
            }
        }
        return $columnValues;
    }

    /**
     * Returns the names of every column this DataTable contains.
     *
     *_ Note: If column names still use their in-database INDEX values (@see Metrics), they
     *        will be converted to their string name in the array result._
     *
     * @return array Array of string column names.
     */
    public function getColumns()
    {
        // Columnar storage: column names are available directly from the schema.
        if (!empty($this->columnNames)) {
            $result = $this->columnNames;
        } else {
            // Empty table or no rows added yet — scan for the first non-empty row.
            $result = [];
            foreach ($this->getRowsWithoutSummaryRow() as $row) {
                $columns = $row->getColumns();
                if (!empty($columns)) {
                    $result = array_keys($columns);
                    break;
                }
            }
            if (empty($result) && $this->summaryRowData !== null) {
                $result = array_keys((new ViewRow($this, self::ID_SUMMARY_ROW))->getColumns());
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
        $metadataValues = [];
        foreach ($this->getRowsWithoutSummaryRow() as $row) {
            $metadataValues[] = $row->getMetadata($name);
        }
        if ($this->summaryRowData !== null) {
            $metadataValues[] = (new ViewRow($this, self::ID_SUMMARY_ROW))->getMetadata($name);
        }
        return $metadataValues;
    }

    /**
     * Delete row metadata by name in every row.
     *
     * @param string $name
     * @param bool $deleteRecursiveInSubtables
     * @return void
     */
    public function deleteRowsMetadata($name, $deleteRecursiveInSubtables = false)
    {
        foreach ($this->rowMetadata as $rowId => &$meta) {
            unset($meta[$name]);
            if (empty($meta)) {
                unset($this->rowMetadata[$rowId]);
            }
        }
        unset($meta);
        unset($this->summaryRowMetadata[$name]);
        unset($this->totalsRowMetadata[$name]);

        if ($deleteRecursiveInSubtables) {
            foreach ($this->rowSubtableIds as $subtableId) {
                $subTable = Manager::getInstance()->getTable($subtableId);
                if ($subTable) {
                    $subTable->deleteRowsMetadata($name, true);
                }
            }
        }
    }

    /**
     * Returns the number of rows in the table including the summary row.
     *
     * @return int
     */
    public function getRowsCount()
    {
        return count($this->rows) + ($this->summaryRowData !== null ? 1 : 0);
    }

    /**
     * Returns the first row of the DataTable.
     *
     * @return Row|false The first row or `false` if it cannot be found.
     */
    public function getFirstRow()
    {
        if (empty($this->rows)) {
            return $this->summaryRowData !== null ? new ViewRow($this, self::ID_SUMMARY_ROW) : false;
        }
        reset($this->rows);
        return new ViewRow($this, key($this->rows));
    }

    /**
     * Returns the last row of the DataTable. If there is a summary row, it
     * will always be considered the last row.
     *
     * @return Row|false The last row or `false` if it cannot be found.
     */
    public function getLastRow()
    {
        if ($this->summaryRowData !== null) {
            return new ViewRow($this, self::ID_SUMMARY_ROW);
        }
        if (empty($this->rows)) {
            return false;
        }
        end($this->rows);
        return new ViewRow($this, key($this->rows));
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
        $manager = Manager::getInstance();
        foreach ($this->rowSubtableIds as $subtableId) {
            if (isset($manager[$subtableId])) {
                $totalCount += $manager->getTable($subtableId)->getRowsCountRecursive();
            }
        }
        $totalCount += $this->getRowsCount();
        return $totalCount;
    }

    /**
     * Returns the number of leaf rows in the entire DataTable hierarchy. Only rows that do not contain a subtables are counted
     *
     * @return int
     */
    public function getLeafRowsCount()
    {
        $totalCount = 0;
        $manager = Manager::getInstance();
        foreach (array_keys($this->rows) as $rowId) {
            if (isset($this->rowSubtableIds[$rowId])) {
                $subtableId = $this->rowSubtableIds[$rowId];
                if (isset($manager[$subtableId])) {
                    $totalCount += $manager->getTable($subtableId)->getLeafRowsCount();
                    continue;
                }
            }
            $totalCount++;
        }
        return $totalCount;
    }

    /**
     * Delete a column by name in every row. This change is NOT applied recursively to all
     * subtables.
     *
     * @param string|int $name Column name to delete.
     * @return void
     */
    public function deleteColumn($name)
    {
        $this->deleteColumns(array($name));
    }

    public function __sleep()
    {
        return [
            'rows', 'columnNames', 'columnIndex', 'nextRowId',
            'rowSubtableIds', 'rowSubtableIdsLoaded', 'rowMetadata',
            'summaryRowData', 'summaryRowMetadata', 'summarySubtableId', 'summarySubtableIdLoaded',
            'totalsRowData',  'totalsRowMetadata',  'totalsSubtableId',  'totalsSubtableIdLoaded',
            'metadata',
        ];
    }

    /**
     * Rename a column in every row. This change is applied recursively to all subtables.
     *
     * @param string $oldName Old column name.
     * @param string $newName New column name.
     * @return void
     */
    public function renameColumn($oldName, $newName)
    {
        // Schema-level rename — zero data movement, O(1)
        if (isset($this->columnIndex[$oldName])) {
            $idx = $this->columnIndex[$oldName];
            $this->columnNames[$idx] = $newName;
            unset($this->columnIndex[$oldName]);
            $this->columnIndex[$newName] = $idx;
        }

        // Recurse into subtables
        foreach ($this->rowSubtableIds as $subtableId) {
            $subTable = Manager::getInstance()->getTable($subtableId);
            if ($subTable) {
                $subTable->renameColumn($oldName, $newName);
            }
        }
    }

    /**
     * Deletes several columns by name in every row.
     *
     * @param list<string|int> $names List of column names to delete.
     * @param bool $deleteRecursiveInSubtables Whether to apply this change to all subtables or not.
     * @return void
     */
    public function deleteColumns($names, $deleteRecursiveInSubtables = false)
    {
        $indicesToRemove = [];
        foreach ($names as $name) {
            if (isset($this->columnIndex[$name])) {
                $indicesToRemove[] = $this->columnIndex[$name];
            }
        }
        if (empty($indicesToRemove)) {
            return;
        }

        sort($indicesToRemove);

        // Build old-index → new-index mapping before altering the schema.
        // Indices in $indicesToRemove map to null (deleted); others shift down.
        $oldCount  = count($this->columnNames);
        $deleteSet = array_flip($indicesToRemove);
        $mapping   = [];
        $removed   = 0;
        for ($i = 0; $i < $oldCount; $i++) {
            if (isset($deleteSet[$i])) {
                $mapping[$i] = null; // column deleted
                $removed++;
            } else {
                $mapping[$i] = $i - $removed;
            }
        }

        // Remove from schema
        foreach (array_reverse($indicesToRemove) as $idx) {
            array_splice($this->columnNames, $idx, 1);
        }
        $this->columnIndex = array_flip($this->columnNames);

        // Rebuild every packed row as a new dense array under the updated schema.
        $newColCount   = count($this->columnNames);
        $rebuildPacked = function (array &$packed) use ($mapping, $newColCount): void {
            $newPacked = array_fill(0, $newColCount, null);
            foreach ($mapping as $oldIdx => $newIdx) {
                if ($newIdx !== null) {
                    $newPacked[$newIdx] = $packed[$oldIdx] ?? null;
                }
            }
            $packed = $newPacked;
        };
        foreach ($this->rows as &$packed) {
            $rebuildPacked($packed);
        }
        unset($packed);
        if ($this->summaryRowData !== null) {
            $rebuildPacked($this->summaryRowData);
        }
        if ($this->totalsRowData !== null) {
            $rebuildPacked($this->totalsRowData);
        }

        if ($deleteRecursiveInSubtables) {
            foreach ($this->rowSubtableIds as $subtableId) {
                $subTable = Manager::getInstance()->getTable($subtableId);
                if ($subTable) {
                    $subTable->deleteColumns($names, true);
                }
            }
        }
    }

    /**
     * Deletes a row by ID.
     *
     * @param int $id The row ID.
     * @return void
     * @throws Exception If the row `$id` cannot be found.
     */
    public function deleteRow($id)
    {
        if ($id === self::ID_SUMMARY_ROW) {
            $this->summaryRowData     = null;
            $this->summaryRowMetadata = [];
            $this->summarySubtableId  = null;
            return;
        }
        unset($this->rows[$id], $this->rowSubtableIds[$id], $this->rowMetadata[$id]);
        $this->indexNotUpToDate = true;
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

        // Use inclusive count (regular rows + summary row) to mirror the original behaviour:
        // the early-exit and summary-deletion logic both depend on whether offset/limit
        // reach the summary-row slot.
        $count = $this->getRowsCount();

        if ($offset >= $count) {
            return 0;
        }

        // if we delete until the end (or past all rows), delete the summary row as well.
        if ($limit === null || $limit >= $count) {
            $this->summaryRowData     = null;
            $this->summaryRowMetadata = [];
            $this->summarySubtableId  = null;
        }

        $ids      = array_keys($this->rows);
        $toDelete = array_slice($ids, $offset, $limit);

        foreach ($toDelete as $id) {
            unset($this->rows[$id], $this->rowSubtableIds[$id], $this->rowMetadata[$id]);
        }

        $this->indexNotUpToDate = true;
        return count($toDelete);
    }

    /**
     * Deletes a set of rows by ID.
     *
     * @param array $rowIds The list of row IDs to delete.
     * @return void
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
     * @return bool
     */
    public static function isEqual(DataTable $table1, DataTable $table2)
    {
        $table1->rebuildIndex();
        $table2->rebuildIndex();

        if ($table1->getRowsCount() != $table2->getRowsCount()) {
            return false;
        }

        // Use ViewRow objects so that Row::isEqual() can resolve loaded subtables
        // via getSubtable() (materialised Rows have isSubtableLoaded=false and would cause
        // a TypeError when Row::isEqual tries DataTable::isEqual on the returned false).
        $rowIds = array_keys($table1->rows);
        if ($table1->summaryRowData !== null) {
            $rowIds[] = self::ID_SUMMARY_ROW;
        }

        foreach ($rowIds as $id) {
            $row1 = new ViewRow($table1, $id);
            $row2 = $table2->getRowFromLabel($row1->getColumn('label'));
            if (
                $row2 === false
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
     * @param array $aSerializedDataTable Will contain all the output arrays
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
    public function getSerialized(
        $maximumRowsInDataTable = null,
        $maximumRowsInSubDataTable = null,
        $columnToSortByBeforeTruncation = null,
        &$aSerializedDataTable = array()
    ) {
        static $depth = 0;
        // make sure subtableIds are consecutive from 1 to N
        static $subtableId = 0;

        if ($depth > self::$maximumDepthLevelAllowed) {
            $depth = 0;
            $subtableId = 0;
            throw new Exception("Maximum recursion level of " . self::$maximumDepthLevelAllowed . " reached. Maybe you have set a DataTable\Row with an associated DataTable belonging already to one of its parent tables?");
        }

        // gather metadata before filters are called, so their metadata is not stored in serialized form
        $metadata = $this->getAllTableMetadata();
        foreach ($metadata as $key => $value) {
            if (!is_scalar($value)) {
                unset($metadata[$key]);
            }
        }

        if (!is_null($maximumRowsInDataTable)) {
            $this->filter(
                'Truncate',
                array($maximumRowsInDataTable - 1,
                      DataTable::LABEL_SUMMARY_ROW,
                      $columnToSortByBeforeTruncation,
                      $filterRecursive = false)
            );
        }

        $consecutiveSubtableIds = array();
        $forcedId = $subtableId;

        // For each row (including the summary row), recurse into subtables
        // and assign consecutive subtable IDs.  We operate on the packed
        // storage directly to avoid allocating Row / ViewRow objects.
        $manager = Manager::getInstance();

        // Gather all row IDs that might have subtables: regular rows + summary
        $rowIdsToCheck = array_keys($this->rows);
        if ($this->summaryRowData !== null) {
            $rowIdsToCheck[] = self::ID_SUMMARY_ROW;
        }

        foreach ($rowIdsToCheck as $id) {
            $storedSubtableId = ($id === self::ID_SUMMARY_ROW)
                ? $this->summarySubtableId
                : ($this->rowSubtableIds[$id] ?? null);

            if ($storedSubtableId !== null && isset($manager[$storedSubtableId])) {
                $subTable = $manager->getTable($storedSubtableId);
                $consecutiveSubtableIds[$id] = ++$subtableId;
                $depth++;
                $subTable->getSerialized($maximumRowsInSubDataTable, $maximumRowsInSubDataTable, $columnToSortByBeforeTruncation, $aSerializedDataTable);
                $depth--;
            }
        }

        // if the datatable is the parent we force the Id at 0 (this is part of the specification)
        if ($depth == 0) {
            $forcedId = 0;
            $subtableId = 0;
        }

        // we then serialize the rows and store them in the serialized dataTable
        if (!empty($this->columnNames)) {
            // Direct path: packed arrays → columnar blob, no intermediate export structures
            $aSerializedDataTable[$forcedId] = $this->serializeToColumnarBlob($consecutiveSubtableIds);
        } else {
            $rows = array();
            foreach ($this->rows as $id => $packed) {
                // Only use a consecutive subtableId assigned above — never fall back to the
                // raw stored subtableId. A stored ID whose subtable was not found in the Manager
                // (not loaded or already deleted) must be serialised as null, mirroring the
                // original behaviour of $row->removeSubtable() for non-loaded subtables.
                $subtableIdForRow = $consecutiveSubtableIds[$id] ?? null;
                $rows[$id] = [
                    Row::COLUMNS              => $this->getPackedRow($id),
                    Row::METADATA             => $this->rowMetadata[$id] ?? [],
                    Row::DATATABLE_ASSOCIATED => $subtableIdForRow,
                ];
            }

            if ($this->summaryRowData !== null) {
                $id = self::ID_SUMMARY_ROW;
                $subtableIdForRow = $consecutiveSubtableIds[$id] ?? null;
                // duplicating code above so we don't create a new array w/ getRows() above in this function which is
                // used heavily in matomo.
                $rows[$id] = [
                    Row::COLUMNS              => $this->getPackedRow(self::ID_SUMMARY_ROW),
                    Row::METADATA             => $this->summaryRowMetadata,
                    Row::DATATABLE_ASSOCIATED => $subtableIdForRow,
                ];
            }

            if (!empty($metadata)) {
                $metadataRow = new Row();
                $metadataRow->setColumns($metadata);

                // set the label so the row will be indexed correctly internally
                $metadataRow->setColumn('label', self::LABEL_ARCHIVED_METADATA_ROW);

                $rows[self::ID_ARCHIVED_METADATA_ROW] = $metadataRow->export();
            }

            $aSerializedDataTable[$forcedId] = $this->encodeRowsColumnar($rows);
            unset($rows);
        }

        return $aSerializedDataTable;
    }

    /**
     * Encodes `$rows` (the export array built by `getSerialized()`) into a columnar JSON blob prefixed
     * with `COLUMNAR_BLOB_MAGIC`. Column names are collected via a union pass over all rows so that no
     * column is ever dropped; each row's values are then a positional array aligned to that union list,
     * with `null` filling any column absent from a given row. Subtable IDs and per-row metadata are
     * stored as sparse maps.
     *
     * @param array $rows Array of exported row data keyed by row ID.
     * @return string The encoded blob.
     */
    private function encodeRowsColumnar(array $rows): string
    {
        $colNames    = [];
        $rowData     = [];
        $subtableMap = [];
        $metadataMap = [];
        $summaryRow  = null;
        $archivedMeta = null;

        // Extract special rows before column discovery.
        if (array_key_exists(self::ID_ARCHIVED_METADATA_ROW, $rows)) {
            $metaExport   = $rows[self::ID_ARCHIVED_METADATA_ROW];
            $archivedMeta = $metaExport[Row::COLUMNS];
            unset($archivedMeta['label']); // 'label' is the sentinel value, not real metadata
            unset($rows[self::ID_ARCHIVED_METADATA_ROW]);
        }

        $summaryExport = null;
        if (array_key_exists(self::ID_SUMMARY_ROW, $rows)) {
            $summaryExport = $rows[self::ID_SUMMARY_ROW];
            unset($rows[self::ID_SUMMARY_ROW]);
        }

        // Union pass: collect every column name that appears in any regular row or the summary row.
        // This prevents data loss when rows have heterogeneous column sets (e.g. multi-site responses,
        // goal reports where not every row carries every metric).
        $colNameSet = [];
        foreach ($rows as $export) {
            foreach (array_keys($export[Row::COLUMNS]) as $name) {
                $colNameSet[$name] = true;
            }
        }
        if ($summaryExport !== null) {
            foreach (array_keys($summaryExport[Row::COLUMNS]) as $name) {
                $colNameSet[$name] = true;
            }
        }
        $colNames = array_keys($colNameSet);

        // Encode regular rows.
        foreach ($rows as $id => $export) {
            $cols   = $export[Row::COLUMNS];
            $values = [];
            foreach ($colNames as $name) {
                $values[] = $cols[$name] ?? null;
            }
            $rowData[(string) $id] = $values;

            $subtableId = $export[Row::DATATABLE_ASSOCIATED];
            if ($subtableId !== null && $subtableId !== false) {
                $subtableMap[(string) $id] = (int) $subtableId;
            }

            $meta = $export[Row::METADATA] ?? [];
            if (!empty($meta)) {
                $metadataMap[(string) $id] = $meta;
            }
        }

        // Encode summary row.
        if ($summaryExport !== null) {
            $summaryValues = [];
            foreach ($colNames as $name) {
                $summaryValues[] = $summaryExport[Row::COLUMNS][$name] ?? null;
            }
            $summaryRow = $summaryValues;

            // Include the summary row's subtable ID in the sparse map (key = ID_SUMMARY_ROW = "-1").
            $summarySubtableId = $summaryExport[Row::DATATABLE_ASSOCIATED];
            if ($summarySubtableId !== null && $summarySubtableId !== false) {
                $subtableMap[(string) self::ID_SUMMARY_ROW] = (int) $summarySubtableId;
            }
        }

        $payload = [
            $colNames,
            $rowData,
            $subtableMap,
            $metadataMap,
            $summaryRow,
            empty($archivedMeta) ? null : $archivedMeta,
        ];

        $encoded = json_encode($payload, JSON_PRESERVE_ZERO_FRACTION);
        if ($encoded === false) {
            throw new \Exception('Failed to JSON-encode columnar blob: ' . json_last_error_msg());
        }
        return self::COLUMNAR_BLOB_MAGIC . $encoded;
    }

    /**
     * Serializes directly from packed columnar storage into a columnar blob, bypassing the
     * intermediate export-row array that `encodeRowsColumnar()` would otherwise require.
     *
     * @param array $consecutiveSubtableIds Map of rowId => consecutive subtable ID.
     * @return string Encoded blob (magic-prefixed JSON).
     */
    private function serializeToColumnarBlob(array $consecutiveSubtableIds): string
    {
        $rowData     = [];
        $subtableMap = [];
        $metadataMap = [];

        $nCols = count($this->columnNames);
        foreach ($this->rows as $id => $packed) {
            // Normalize to a full-length sequential array so JSON encodes it as an array,
            // not an object. Absent columns (sparse indices) are stored as null, matching
            // the contract used by encodeRowsColumnar(). null = "absent" in the blob format.
            $normalized = [];
            for ($i = 0; $i < $nCols; $i++) {
                $normalized[] = $packed[$i] ?? null;
            }
            $rowData[(string) $id] = $normalized;

            $subtableId = $consecutiveSubtableIds[$id] ?? null;
            if ($subtableId !== null) {
                $subtableMap[(string) $id] = $subtableId;
            }

            $meta = $this->rowMetadata[$id] ?? [];
            if (!empty($meta)) {
                $metadataMap[(string) $id] = $meta;
            }
        }

        // Summary row
        $summaryValues = null;
        if ($this->summaryRowData !== null) {
            $normalized = [];
            for ($i = 0; $i < $nCols; $i++) {
                $normalized[] = $this->summaryRowData[$i] ?? null;
            }
            $summaryValues = $normalized;
            $summarySubtable = $consecutiveSubtableIds[self::ID_SUMMARY_ROW] ?? null;
            if ($summarySubtable !== null) {
                $subtableMap[(string) self::ID_SUMMARY_ROW] = $summarySubtable;
            }
            if (!empty($this->summaryRowMetadata)) {
                $metadataMap[(string) self::ID_SUMMARY_ROW] = $this->summaryRowMetadata;
            }
        }

        // Archived table-level metadata
        $archivedMeta = null;
        $tableMeta = $this->getAllTableMetadata();
        foreach ($tableMeta as $key => $value) {
            if (!is_scalar($value)) {
                unset($tableMeta[$key]);
            }
        }
        if (!empty($tableMeta)) {
            $archivedMeta = $tableMeta;
        }

        $payload = [
            $this->columnNames,
            $rowData,
            $subtableMap,
            $metadataMap,
            $summaryValues,
            $archivedMeta,
        ];

        $encoded = json_encode($payload, JSON_PRESERVE_ZERO_FRACTION);
        if ($encoded === false) {
            throw new \Exception('Failed to JSON-encode columnar blob: ' . json_last_error_msg());
        }
        return self::COLUMNAR_BLOB_MAGIC . $encoded;
    }

    /**
     * Decodes a columnar blob (produced by `encodeRowsColumnar()`) back into the canonical `$rows`
     * array that `addRowsFromSerializedArray()` already knows how to consume.
     *
     * @param string $blob Blob starting with `COLUMNAR_BLOB_MAGIC`.
     * @return array Decoded rows array.
     */
    private function decodeColumnarBlob(string $blob): array
    {
        $json    = substr($blob, strlen(self::COLUMNAR_BLOB_MAGIC));
        $payload = json_decode($json, true, 512);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to JSON-decode columnar blob: ' . json_last_error_msg());
        }
        if (!is_array($payload)) {
            throw new \Exception('Columnar blob payload decoded to unexpected type, expected array');
        }
        if (count($payload) !== 6) {
            throw new \Exception('Columnar blob payload has unexpected field count: ' . count($payload) . ', expected 6');
        }

        [$colNames, $rowData, $subtableMap, $metadataMap, $summaryValues, $archivedMeta] = $payload;

        $rows = [];

        // Decode regular rows.
        foreach ($rowData as $idStr => $values) {
            $id      = (int) $idStr;
            // Filter out null-filled entries so absent columns are truly absent rather than present
            // with a null value. This keeps getColumns() consistent with getColumn() semantics.
            $columns = array_filter(array_combine($colNames, $values), static function ($v) {
                return $v !== null;
            });

            $rows[$id] = [
                Row::COLUMNS              => $columns,
                Row::METADATA             => $metadataMap[$idStr] ?? [],
                Row::DATATABLE_ASSOCIATED => $subtableMap[$idStr] ?? null,
            ];
        }

        // Decode summary row.
        if ($summaryValues !== null) {
            $rows[self::ID_SUMMARY_ROW] = [
                Row::COLUMNS              => array_filter(array_combine($colNames, $summaryValues), static function ($v) {
                    return $v !== null;
                }),
                Row::METADATA             => [],
                Row::DATATABLE_ASSOCIATED => $subtableMap[(string) self::ID_SUMMARY_ROW] ?? null,
            ];
        }

        // Restore archived table metadata as the sentinel row.
        if ($archivedMeta !== null) {
            $metaCols          = $archivedMeta;
            $metaCols['label'] = self::LABEL_ARCHIVED_METADATA_ROW;
            $rows[self::ID_ARCHIVED_METADATA_ROW] = [
                Row::COLUMNS              => $metaCols,
                Row::METADATA             => [],
                Row::DATATABLE_ASSOCIATED => null,
            ];
        }

        return $rows;
    }

    /**
     * Deserializes a columnar blob directly into packed storage, bypassing intermediate
     * export-row arrays and Row object construction.
     *
     * @param string $blob Blob starting with `COLUMNAR_BLOB_MAGIC`.
     */
    private function deserializeColumnarBlobDirect(string $blob): void
    {
        $json    = substr($blob, strlen(self::COLUMNAR_BLOB_MAGIC));
        $payload = json_decode($json, true, 512);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to JSON-decode columnar blob: ' . json_last_error_msg());
        }

        if (!is_array($payload) || count($payload) !== 6) {
            throw new \Exception('Columnar blob payload has unexpected field count: ' . (is_array($payload) ? count($payload) : 'non-array') . ', expected 6');
        }

        [$colNames, $rowData, $subtableMap, $metadataMap, $summaryValues, $archivedMeta] = $payload;

        // Establish or extend schema
        if (empty($this->columnNames)) {
            $this->establishSchema($colNames);
        } else {
            foreach ($colNames as $name) {
                if (!isset($this->columnIndex[$name])) {
                    $this->extendSchema($name);
                }
            }
        }

        // Decode regular rows directly into packed dense storage.
        // null in the blob means "absent" — stored as null in the dense array (not skipped).
        $colCount = count($this->columnNames);
        foreach ($rowData as $idStr => $values) {
            $id = (int) $idStr;

            $packed = array_fill(0, $colCount, null);
            foreach ($colNames as $srcIdx => $name) {
                $packed[$this->columnIndex[$name]] = $values[$srcIdx] ?? null;
            }
            $this->rows[$id] = $packed;

            $subtableId = $subtableMap[$idStr] ?? null;
            if ($subtableId !== null) {
                $this->rowSubtableIds[$id] = (int) $subtableId;
            }

            $meta = $metadataMap[$idStr] ?? [];
            if (!empty($meta)) {
                $this->rowMetadata[$id] = $meta;
            }
        }

        // Decode summary row
        if ($summaryValues !== null) {
            $packed = array_fill(0, $colCount, null);
            foreach ($colNames as $srcIdx => $name) {
                $packed[$this->columnIndex[$name]] = $summaryValues[$srcIdx] ?? null;
            }
            $this->summaryRowData     = $packed;
            $this->summaryRowMetadata = $metadataMap[(string) self::ID_SUMMARY_ROW] ?? [];
            $this->summarySubtableId  = $subtableMap[(string) self::ID_SUMMARY_ROW] ?? null;
        }

        // Restore archived table-level metadata
        if ($archivedMeta !== null) {
            foreach ($archivedMeta as $key => $value) {
                $this->setMetadata($key, $value);
            }
        }

        $this->indexNotUpToDate = true;
    }

    /** @var string[] */
    private static $previousRowClasses = [
        'O:39:"Piwik\DataTable\Row\DataTableSummaryRow"',
        'O:19:"Piwik\DataTable\Row"',
        'O:36:"Piwik_DataTable_Row_DataTableSummary"',
        'O:19:"Piwik_DataTable_Row"',
    ];

    /** @var string */
    private static $rowClassToUseForUnserialize = 'O:29:"Piwik_DataTable_SerializedRow"';

    /**
     * It is faster to unserialize existing serialized Row instances to "Piwik_DataTable_SerializedRow" and access the
     * `$row->c` property than implementing a "__wakeup" method in the Row instance to map the "$row->c" to $row->columns
     * etc. We're talking here about 15% faster reports aggregation in some cases. To be concrete: We have a test where
     * Archiving a year takes 1700 seconds with "__wakeup" and 1400 seconds with this method. Yes, it takes 300 seconds
     * to wake up millions of rows. We should be able to remove this code here end 2015 and use the "__wakeup" way by then.
     * Why? By then most new archives will have only arrays serialized anyway and therefore this mapping is rather an overhead.
     *
     * @param string $serialized
     * @return array
     * @throws Exception In case the unserialize fails
     */
    private function unserializeRows($serialized)
    {
        if (str_starts_with($serialized, self::COLUMNAR_BLOB_MAGIC)) {
            return $this->decodeColumnarBlob($serialized);
        }

        // Current archives only persist row arrays, so do not allow objects in the default path.
        $rows = Common::safe_unserialize($serialized, []);

        if (!$this->isValidRowsPayload($rows, $allowLegacySerializedRowObjects = false)) {
            $rows = false;
        }

        if ($rows === false) {
            // Legacy object payloads are attempted as a fallback for BC.
            $legacySerialized = str_replace(
                array_map(function ($class) {
                    return $class . ':';
                }, self::$previousRowClasses),
                self::$rowClassToUseForUnserialize . ':',
                $serialized
            );
            $rows = Common::safe_unserialize($legacySerialized, [
                \Piwik_DataTable_SerializedRow::class,
            ]);
        }

        if (!$this->isValidRowsPayload($rows, $allowLegacySerializedRowObjects = true)) {
            throw new Exception("The unserialization has failed!");
        }

        return $rows;
    }

    private function isValidRowsPayload($rows, bool $allowLegacySerializedRowObjects): bool
    {
        if (!is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if ($allowLegacySerializedRowObjects && $this->isValidLegacySerializedRowObject($row)) {
                continue;
            }

            if ($this->containsObject($row)) {
                return false;
            }
        }

        return true;
    }

    private function isValidLegacySerializedRowObject($row): bool
    {
        if (!$row instanceof \Piwik_DataTable_SerializedRow) {
            return false;
        }

        return isset($row->c) && is_array($row->c) && !$this->containsObject($row->c);
    }

    private function containsObject($value): bool
    {
        if (is_object($value)) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        $containsObject = false;

        try {
            array_walk_recursive($value, function ($entry) use (&$containsObject): void {
                if (is_object($entry)) {
                    $containsObject = true;
                }
            });
        } catch (\Throwable $error) {
            throw new Exception('The unserialization has failed! Array payload cannot be safely traversed.', 0, $error);
        }

        return $containsObject;
    }

    /**
     * Adds a set of rows from a serialized DataTable string.
     *
     * See {@link serialize()}.
     *
     * _Note: This function will successfully load DataTables serialized by Piwik 1.X._
     *
     * @param string $serialized A string with the format of a string in the array returned by
     *                          {@link serialize()}.
     * @return void
     * @throws Exception if `$serialized` is invalid.
     */
    public function addRowsFromSerializedArray($serialized)
    {
        if (str_starts_with($serialized, self::COLUMNAR_BLOB_MAGIC)) {
            $this->deserializeColumnarBlobDirect($serialized);
            return;
        }

        $rows = $this->unserializeRows($serialized);

        if (array_key_exists(self::ID_SUMMARY_ROW, $rows)) {
            if (is_array($rows[self::ID_SUMMARY_ROW])) {
                $this->addSummaryRow(new Row($rows[self::ID_SUMMARY_ROW]));
            } elseif (isset($rows[self::ID_SUMMARY_ROW]->c)) {
                $this->addSummaryRow(new Row($rows[self::ID_SUMMARY_ROW]->c)); // Pre Piwik 2.13
            }
            unset($rows[self::ID_SUMMARY_ROW]);
        }

        if (array_key_exists(self::ID_ARCHIVED_METADATA_ROW, $rows)) {
            $metadata = $rows[self::ID_ARCHIVED_METADATA_ROW][Row::COLUMNS];
            unset($metadata['label']);
            $this->setAllTableMetadata($metadata);
            unset($rows[self::ID_ARCHIVED_METADATA_ROW]);
        }

        foreach ($rows as $id => $row) {
            unset($rows[$id]); // free raw entry immediately to halve deserialization peak memory
            if (isset($row->c)) {
                $this->addRow(new Row($row->c)); // Pre Piwik 2.13
            } else {
                $this->addRow(new Row($row));
            }
        }
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
     * @return void
     */
    public function addRowsFromArray($array)
    {
        foreach ($array as $id => $row) {
            if (is_array($row)) {
                $row = new Row($row);
            }

            if ($id == self::ID_SUMMARY_ROW) {
                $this->addSummaryRow($row);
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
     * @return void
     * @throws Exception if `$array` is in an incorrect format.
     */
    public function addRowsFromSimpleArray($array)
    {
        if (count($array) === 0) {
            return;
        }

        $exceptionText = "Data structure returned is not convertible in the requested format: %s" .
            " Try to call this method with the parameters '&format=original&serialize=1'" .
            "; you will get the original php data structure serialized.";

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
            if (array_keys($array) === array_keys(array_fill(0, count($array), true))) {
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
                    // we define an exception we may throw if at one point we notice that we cannot handle the data structure
                    throw new Exception(
                        sprintf(
                            $exceptionText,
                            sprintf(
                                "Only integer keys supported for array columns on base level. Unsupported string '%s' found for row '%s'.",
                                $key,
                                substr(var_export($row, true), 0, 500)
                            )
                        )
                    );
                }
                // if any of the sub elements of row is an array we cannot handle this data structure...
                foreach ($row as $name => $subRow) {
                    if (is_array($subRow)) {
                        throw new Exception(
                            sprintf(
                                $exceptionText,
                                sprintf(
                                    "Multidimensional column values not supported. Found unexpected array value for column '%s' in row '%s': '%s'.",
                                    $name,
                                    $key,
                                    substr(var_export($subRow, true), 0, 500)
                                )
                            )
                        );
                    }
                }
                $row = new Row(array(Row::COLUMNS => $row));
            } else {
                // other (string, numbers...) => we build a line from this value
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
     * @return DataTable
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

            if ($label === RankingQuery::LABEL_SUMMARY_ROW) {
                $table->addSummaryRow(new Row($cleanRow));
            } else {
                $table->addRow(new Row($cleanRow));
            }
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
     * @return void
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
     * @return void
     */
    public function setMetadata($name, $value)
    {
        $this->metadata[$name] = $value;
    }

    /**
     * Deletes a metadata property by name.
     *
     * @param bool|string $name The metadata name (omit to delete all metadata)
     * @return bool True if the requested metadata was deleted
     */
    public function deleteMetadata($name = false): bool
    {
        if ($name === false) {
            $this->metadata = [];
            return true;
        }
        if (!isset($this->metadata[$name])) {
            return false;
        }
        unset($this->metadata[$name]);
        return true;
    }

    /**
     * Returns all table metadata.
     *
     * @return array<string, mixed>
     */
    public function getAllTableMetadata()
    {
        return $this->metadata;
    }

    /**
     * Sets several metadata values by name.
     *
     * @param array<string, mixed> $values Array mapping metadata names with metadata values.
     * @return void
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
     * @param array $metadata Array mapping metadata names with metadata values.
     * @return void
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
     * @return void
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
     * Read [https://en.wikipedia.org/wiki/Tree_(data_structure)#Traversal_methods](https://en.wikipedia.org/wiki/Tree_(data_structure)#Traversal_methods)
     * for more information about tree walking.
     *
     * @param array $path The path to walk. An array of label values. The first element
     *                    refers to a row in this DataTable, the second in a subtable of
     *                    the first row, the third a subtable of the second row, etc.
     * @param array|false $missingRowColumns The default columns to use when creating new rows.
     *                                      If this parameter is supplied, new rows will be
     *                                      created for path labels that cannot be found.
     * @param int $maxSubtableRows The maximum number of allowed rows in new subtables. New
     *                             subtables are only created if `$missingRowColumns` is provided.
     * @return array{0: false|Row, 1: int} First element is the found row or `false`. Second element is
     *                                     the number of path segments walked. If a row is found, this
     *                                     will be == to `count($path)`. Otherwise, it will be the index
     *                                     of the path segment that we could not find.
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
                    return [false, $i];
                } else {
                    // if we're adding missing rows, add a new row

                    $row = new DataTableSummaryRow();
                    $row->setColumns(array('label' => $segment) + $missingRowColumns);

                    $next = $table->addRow($row);

                    if ($next !== $row) {
                        // if the row wasn't added, the table is full

                        // Summary row, has no metadata
                        $next->deleteMetadata();
                        return [$next, $i];
                    }
                }
            }

            $table = $next->getSubtable();
            if ($table === false) {
                // if the row has no table (and thus no child rows), and we're not adding
                // missing rows, return false
                if ($missingRowColumns === false) {
                    return [false, $i];
                } elseif ($i != $pathLength - 1) {
                    // create subtable if missing, but only if not on the last segment

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

        return [$next, $i];
    }

    /**
     * Returns a new DataTable in which the rows of this table are replaced with the aggregatated rows of all its subtables.
     *
     * @param string|false $labelColumn If supplied the label of the parent row will be added to
     *                                 a new column in each subtable row.
     *
     *                                 If set to, `'label'` each subtable row's label will be prepended
     *                                 w/ the parent row's label. So `'child_label'` becomes
     *                                 `'parent_label - child_label'`.
     * @param bool $useMetadataColumn If true and if `$labelColumn` is supplied, the parent row's
     *                                label will be added as metadata and not a new column.
     * @return DataTable
     */
    public function mergeSubtables($labelColumn = false, $useMetadataColumn = false)
    {
        $result = new DataTable();
        $result->setAllTableMetadata($this->getAllTableMetadata());
        foreach ($this->getRowsWithoutSummaryRow() as $row) {
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
     * @return DataTable
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
     * @return DataTable
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
     * @param null|array<string|int, string> $columnAggregationOps
     * @return void
     * @throws \Exception
     */
    protected function aggregateRowWithLabel(Row $row, $columnAggregationOps)
    {
        $labelToLookFor = $row->getColumn('label');
        if ($labelToLookFor === false) {
            $message = sprintf(
                "Label column not found in the table to add in addDataTable(). Row: %s",
                var_export($row->getColumns(), true)
            );
            throw new Exception($message);
        }
        $rowFound = $this->getRowFromLabel($labelToLookFor);
        // if we find the summary row in the other table, ignore it, since we're aggregating normal rows in this method.
        // the summary row is aggregated explicitly after this method is called.
        if (
            !empty($rowFound)
            && $rowFound->isSummaryRow()
        ) {
            $rowFound = false;
        }
        $this->aggregateRow($rowFound, $row, $columnAggregationOps, false);
    }

    /**
     * @param Row|false|null $thisRow
     * @param array<string|int, string>|false|null $columnAggregationOps
     */
    private function aggregateRow($thisRow, Row $otherRow, $columnAggregationOps, bool $isSummaryRow): void
    {
        if (empty($thisRow)) {
            $thisRow = new Row();
            $otherRowLabel = $otherRow->getColumn('label');
            if ($otherRowLabel !== false) {
                $thisRow->addColumn('label', $otherRowLabel);
            }
            $thisRow->setAllMetadata($otherRow->getMetadata());

            if ($isSummaryRow) {
                // Capture the returned ViewRow so that sumRow() writes to packed storage.
                $thisRow = $this->addSummaryRow($thisRow);
            } else {
                // Capture the returned ViewRow so that sumRow() writes to packed storage.
                $thisRow = $this->addRow($thisRow);
            }
        }

        $thisRow->sumRow($otherRow, $copyMeta = true, $columnAggregationOps);

        // if the row to add has a subtable whereas the current row doesn't
        // we simply add it (cloning the subtable)
        // if the row has the subtable already
        // then we have to recursively sum the subtables
        $subTable = $otherRow->getSubtable();
        if ($subTable) {
            $subTable->metadata[self::COLUMN_AGGREGATION_OPS_METADATA_NAME] = $columnAggregationOps;
            $thisRow->sumSubtable($subTable);
        }
    }

    /**
     * @param Row|false $row
     * @return void
     */
    protected function aggregateRowFromSimpleTable($row)
    {
        if ($row === false) {
            return;
        }
        $thisRow = $this->getFirstRow();
        if ($thisRow === false) {
            $thisRow = new Row();
            $this->addRow($thisRow);
        }
        $thisRow->sumRow($row, true, $this->getMetadata(self::COLUMN_AGGREGATION_OPS_METADATA_NAME));
    }

    /**
     * Unsets all queued filters.
     * @return void
     */
    public function clearQueuedFilters()
    {
        $this->queuedFilters = [];
    }

    /**
     * @return array
     */
    public function getQueuedFilters()
    {
        return $this->queuedFilters;
    }

    /**
     * @return \ArrayIterator<int, Row>
     */
    public function getIterator(): \ArrayIterator
    {
        $result = [];
        foreach (array_keys($this->rows) as $id) {
            $result[$id] = new ViewRow($this, $id);
        }
        if ($this->summaryRowData !== null) {
            $result[self::ID_SUMMARY_ROW] = new ViewRow($this, self::ID_SUMMARY_ROW);
        }
        return new \ArrayIterator($result);
    }

    /**
     * @param int $offset
     */
    public function offsetExists($offset): bool
    {
        $row = $this->getRowFromId($offset);

        return false !== $row;
    }

    /**
     * @param int $offset
     */
    public function offsetGet($offset): Row
    {
        return $this->getRowFromId($offset);
    }

    /**
     * @param int $offset
     * @param Row $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->rows[$offset] = $value;
    }

    /**
     * @param int $offset
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        $this->deleteRow($offset);
    }

    /**
     * @param string|int|null $label
     * @param array $columns
     * @param array<string, string>|null $aggregationOps
     * @throws Exception
     */
    public function sumRowWithLabel($label, array $columns, ?array $aggregationOps = null): Row
    {
        $label = $label ?? '';

        $tableRow = new DataTable\Row([DataTable\Row::COLUMNS => ['label' => $label] + $columns]);

        if ($label === RankingQuery::LABEL_SUMMARY_ROW) {
            $existingRow = $this->getSummaryRow();
        } else {
            $existingRow = $this->getRowFromLabel($label);
        }

        if (empty($existingRow)) {
            if ($label === RankingQuery::LABEL_SUMMARY_ROW) {
                $this->addSummaryRow($tableRow);
            } else {
                $this->addRow($tableRow);
            }

            $existingRow = $tableRow;
        } else {
            $existingRow->sumRow($tableRow, true, $aggregationOps);
        }
        return $existingRow;
    }

    public function setAsBuiltWithoutArchives(bool $flag): void
    {
        $this->isBuiltWithoutArchives = $flag;
    }

    public function wasBuiltWithoutArchives(): bool
    {
        return $this->isBuiltWithoutArchives;
    }
}
