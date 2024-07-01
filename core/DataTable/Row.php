<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataTable;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Log\LoggerInterface;

/**
 * This is what a {@link Piwik\DataTable} is composed of.
 *
 * DataTable rows contain columns, metadata and a subtable ID. Columns and metadata
 * are stored as an array of name => value mappings.
 *
 * @api
 */
class Row extends \ArrayObject
{
    public const COMPARISONS_METADATA_NAME = 'comparisons';

    /**
     * List of columns that cannot be summed. An associative array for speed.
     *
     * @var array
     */
    private static $unsummableColumns = array(
        'label' => true,
        'full_url' => true, // column used w/ old Piwik versions,
        DataTable::ARCHIVED_DATE_METADATA_NAME => true, // date column used in metadata for proportional tooltips
        DataTable::ARCHIVE_STATE_METADATA_NAME => true,
    );

    // @see sumRow - implementation detail
    public $maxVisitsSummed = 0;

    private $metadata = array();
    private $isSubtableLoaded = false;

    /**
     * @internal
     */
    public $subtableId = null;

    private $isSummaryRow = false;

    public const COLUMNS = 0;
    public const METADATA = 1;
    public const DATATABLE_ASSOCIATED = 3;

    /**
     * Constructor.
     *
     * @param array $row An array with the following structure:
     *
     *                       array(
     *                           Row::COLUMNS => array('label' => 'Matomo',
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
        if (isset($row[self::COLUMNS])) {
            $this->exchangeArray($row[self::COLUMNS]);
        }
        if (isset($row[self::METADATA])) {
            $this->metadata = $row[self::METADATA];
        }
        if (isset($row[self::DATATABLE_ASSOCIATED])) {
            if ($row[self::DATATABLE_ASSOCIATED] instanceof DataTable) {
                $this->setSubtable($row[self::DATATABLE_ASSOCIATED]);
            } else {
                $this->subtableId = $row[self::DATATABLE_ASSOCIATED];
            }
        }
    }

    /**
     * Used when archiving to serialize the Row's properties.
     * @return array
     * @ignore
     */
    public function export()
    {
        $metadataToPersist = $this->metadata;
        unset($metadataToPersist[self::COMPARISONS_METADATA_NAME]);
        return array(
            self::COLUMNS => $this->getArrayCopy(),
            self::METADATA => $metadataToPersist,
            self::DATATABLE_ASSOCIATED => $this->subtableId,
        );
    }

    /**
     * When destroyed, a row destroys its associated subtable if there is one.
     * @ignore
     */
    public function __destruct()
    {
        if ($this->isSubtableLoaded) {
            Manager::getInstance()->deleteTable($this->subtableId);
            $this->subtableId = null;
            $this->isSubtableLoaded = false;
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
            if (is_string($value)) {
                $value = "'$value'";
            } elseif (is_array($value)) {
                $value = var_export($value, true);
            }
            $columns[] = "'$column' => $value";
        }
        $columns = implode(", ", $columns);
        $metadata = array();
        foreach ($this->getMetadata() as $name => $value) {
            if (is_string($value)) {
                $value = "'$value'";
            } elseif (is_array($value)) {
                $value = var_export($value, true);
            }
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
        if (!$this->offsetExists($name)) {
            return false;
        }

        unset($this[$name]);
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
        if (isset($this[$oldName])) {
            $this[$newName] = $this[$oldName];
        }

        // outside the if () since we want to delete nulled columns
        if ($this->offsetExists($oldName)) {
            unset($this[$oldName]);
        }
    }

    /**
     * Returns a column by name.
     *
     * @param string $name The column name.
     * @return mixed|false  The column value or false if it doesn't exist.
     */
    public function getColumn($name)
    {
        if (!isset($this[$name])) {
            return false;
        }

        return $this[$name];
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
            return $this->metadata;
        }
        if (!isset($this->metadata[$name])) {
            return false;
        }
        return $this->metadata[$name];
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
        return $this->offsetExists($name);
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
        return $this->getArrayCopy();
    }

    /**
     * Returns the ID of the subDataTable.
     * If there is no such a table, returns null.
     *
     * @return int|null
     */
    public function getIdSubDataTable()
    {
        return $this->subtableId;
    }

    /**
     * Returns the associated subtable, if one exists. Returns `false` if none exists.
     *
     * @return DataTable|bool
     */
    public function getSubtable()
    {
        if ($this->isSubtableLoaded) {
            try {
                return Manager::getInstance()->getTable($this->subtableId);
            } catch (TableNotFoundException $e) {
                // edge case
            }
        }
        return false;
    }

    /**
     * @param int $subtableId
     * @ignore
     */
    public function setNonLoadedSubtableId($subtableId)
    {
        $this->subtableId = $subtableId;
        $this->isSubtableLoaded = false;
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
        if ($this->isSubtableLoaded) {
            $thisSubTable = $this->getSubtable();
        } else {
            $this->warnIfSubtableAlreadyExists($subTable);

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
        $this->subtableId = $subTable->getId();
        $this->isSubtableLoaded = true;

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
        return $this->isSubtableLoaded;
    }

    /**
     * Removes the subtable reference.
     */
    public function removeSubtable()
    {
        $this->subtableId = null;
        $this->isSubtableLoaded = false;
    }

    /**
     * Set all the columns at once. Overwrites **all** previously set columns.
     *
     * @param array $columns eg, `array('label' => 'www.php.net', 'nb_visits' => 15894)`
     */
    public function setColumns($columns)
    {
        $this->exchangeArray($columns);
    }

    /**
     * Set the value `$value` to the column called `$name`.
     *
     * @param string $name name of the column to set.
     * @param mixed $value value of the column to set.
     */
    public function setColumn($name, $value)
    {
        $this[$name] = $value;
    }

    /**
     * Set the value `$value` to the metadata called `$name`.
     *
     * @param string $name name of the metadata to set.
     * @param mixed $value value of the metadata to set.
     */
    public function setMetadata($name, $value)
    {
        $this->metadata[$name] = $value;
    }

    /**
     * Sets all metadata at once.
     *
     * @param array $metadata new metadata to set
     */
    public function setAllMetadata($metadata)
    {
        $this->metadata = $metadata;
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
            $this->metadata = array();
            return true;
        }
        if (!isset($this->metadata[$name])) {
            return false;
        }
        unset($this->metadata[$name]);
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
        if (isset($this[$name])) {
            throw new Exception("Column $name already in the array!");
        }
        $this->setColumn($name, $value);
    }

    /**
     * Add many columns to this row.
     *
     * @param array $columns Name/Value pairs, e.g., `array('name' => $value , ...)`
     * @return void
     * @throws Exception if any column name does not exist.
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
        if (isset($this->metadata[$name])) {
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
        $operationsIsArray = is_array($aggregationOperations);
        foreach ($rowToSum as $columnToSumName => $columnToSumValue) {
            if (!$this->isSummableColumn($columnToSumName)) {
                continue;
            }

            $thisColumnValue = $this->getColumn($columnToSumName);

            $operation = 'sum';
            if ($operationsIsArray && isset($aggregationOperations[$columnToSumName])) {
                $operationName = $aggregationOperations[$columnToSumName];
                if (is_string($operationName)) {
                    $operation = strtolower($operationName);
                } elseif (is_callable($operationName)) {
                    $operation = $operationName;
                }
            }

            // max_actions is a core metric that is generated in ArchiveProcess_Day. Therefore, it can be
            // present in any data table and is not part of the $aggregationOperations mechanism.
            if ($columnToSumName == Metrics::INDEX_MAX_ACTIONS) {
                $operation = 'max';
            }
            if (empty($operation)) {
                throw new Exception("Unknown aggregation operation for column $columnToSumName.");
            }

            $newValue = $this->getColumnValuesMerged($operation, $thisColumnValue, $columnToSumValue, $this, $rowToSum, $columnToSumName);

            $this->setColumn($columnToSumName, $newValue);
        }

        if ($enableCopyMetadata) {
            $this->sumRowMetadata($rowToSum, $aggregationOperations);
        }
    }

    /**
     */
    private function getColumnValuesMerged($operation, $thisColumnValue, $columnToSumValue, $thisRow, $rowToSum, $columnName = null)
    {
        switch ($operation) {
            case 'skip':
                $newValue = null;
                break;
            case 'max':
                if (!is_numeric($thisColumnValue)) {
                    $newValue = $columnToSumValue;
                } elseif (!is_numeric($columnToSumValue)) {
                    $newValue = $thisColumnValue;
                } else {
                    $newValue = max($thisColumnValue, $columnToSumValue);
                }
                break;
            case 'min':
                if (!is_numeric($thisColumnValue)) {
                    $newValue = $columnToSumValue;
                } elseif (!is_numeric($columnToSumValue)) {
                    $newValue = $thisColumnValue;
                } else {
                    $newValue = min($thisColumnValue, $columnToSumValue);
                }
                break;
            case 'sum':
                $newValue = $this->sumRowArray($thisColumnValue, $columnToSumValue, $columnName);
                break;
            case 'uniquearraymerge':
                if (is_array($thisColumnValue) && is_array($columnToSumValue)) {
                    foreach ($columnToSumValue as $columnSum) {
                        if (!in_array($columnSum, $thisColumnValue)) {
                            $thisColumnValue[] = $columnSum;
                        }
                    }
                } elseif (!is_array($thisColumnValue) && is_array($columnToSumValue)) {
                    $thisColumnValue = $columnToSumValue;
                }

                $newValue = $thisColumnValue;
                break;
            default:
                if (is_callable($operation)) {
                    return call_user_func($operation, $thisColumnValue, $columnToSumValue, $thisRow, $rowToSum);
                }

                throw new Exception("Unknown operation '$operation'.");
        }
        return $newValue;
    }

    /**
     * Sums the metadata in `$rowToSum` with the metadata in `$this` row.
     *
     * @param Row $rowToSum
     * @param array $aggregationOperations
     */
    public function sumRowMetadata($rowToSum, $aggregationOperations = array())
    {
        if (
            !empty($rowToSum->metadata)
            && !$this->isSummaryRow()
        ) {
            $aggregatedMetadata = array();

            if (is_array($aggregationOperations)) {
                // we need to aggregate value before value is overwritten by maybe another row
                foreach ($aggregationOperations as $column => $operation) {
                    $thisMetadata = $this->getMetadata($column);
                    $sumMetadata = $rowToSum->getMetadata($column);

                    if ($thisMetadata === false && $sumMetadata === false) {
                        continue;
                    }

                    $aggregatedMetadata[$column] = $this->getColumnValuesMerged($operation, $thisMetadata, $sumMetadata, $this, $rowToSum, $column);
                }
            }

            // We shall update metadata, and keep the metadata with the _most visits or pageviews_, rather than first or last seen
            $visits = max(
                $rowToSum->getColumn(Metrics::INDEX_PAGE_NB_HITS) || $rowToSum->getColumn(Metrics::INDEX_NB_VISITS),
                // Old format pre-1.2, @see also method doSumVisitsMetrics()
                $rowToSum->getColumn('nb_actions') || $rowToSum->getColumn('nb_visits')
            );
            if (
                ($visits && $visits > $this->maxVisitsSummed)
                || empty($this->metadata)
            ) {
                $this->maxVisitsSummed = $visits;
                $this->metadata = $rowToSum->metadata;
            }

            foreach ($aggregatedMetadata as $column => $value) {
                // we need to make sure aggregated value is used, and not metadata from $rowToSum
                $this->setMetadata($column, $value);
            }
        }
    }

    /**
     * Returns `true` if this row was added to a datatable as the summary row, `false` if otherwise.
     *
     * @return bool
     */
    public function isSummaryRow()
    {
        return $this->isSummaryRow;
    }

    public function setIsSummaryRow()
    {
        $this->isSummaryRow = true;
    }

    /**
     * Returns the associated comparisons DataTable, if any.
     *
     * @return DataTable|null
     */
    public function getComparisons()
    {
        $dataTableId = $this->getMetadata(self::COMPARISONS_METADATA_NAME);
        if (empty($dataTableId)) {
            return null;
        }
        return Manager::getInstance()->getTable($dataTableId);
    }

    /**
     * Associates the supplied table with this row as the comparisons table.
     *
     * @param DataTable $table
     */
    public function setComparisons(DataTable $table)
    {
        $this->setMetadata(self::COMPARISONS_METADATA_NAME, $table->getId());
    }

    /**
     * Helper function: sums 2 values
     *
     * @param number|bool $thisColumnValue
     * @param number|array $columnToSumValue
     * @param string|null $columnName for error reporting.
     *
     * @throws Exception
     * @return array|int
     */
    protected function sumRowArray($thisColumnValue, $columnToSumValue, $columnName = null)
    {
        if ($columnToSumValue === false) {
            return $thisColumnValue;
        }

        if (is_numeric($columnToSumValue)) {
            if ($thisColumnValue === false) {
                $thisColumnValue = 0;
            } elseif (!is_numeric($thisColumnValue)) {
                $label = $this->getColumn('label');
                $thisColumnDescription = $this->getColumnValueDescriptionForError($thisColumnValue);
                $columnToSumValueDescription = $this->getColumnValueDescriptionForError($columnToSumValue);
                throw new \Exception(sprintf(
                    'Trying to sum unsupported operands for column %s in row with label = %s: %s + %s',
                    $columnName,
                    $label,
                    $thisColumnDescription,
                    $columnToSumValueDescription
                ));
            }

            return $thisColumnValue + $columnToSumValue;
        }

        if ($thisColumnValue === false) {
            return $columnToSumValue;
        }

        if (is_array($columnToSumValue)) {
            $newValue = $thisColumnValue;
            foreach ($columnToSumValue as $arrayIndex => $arrayValue) {
                if (!is_numeric($arrayIndex) && !$this->isSummableColumn($arrayIndex)) {
                    continue;
                }
                if (!isset($newValue[$arrayIndex])) {
                    $newValue[$arrayIndex] = false;
                }
                $newValue[$arrayIndex] = $this->sumRowArray($newValue[$arrayIndex], $arrayValue, $columnName);
            }
            return $newValue;
        }

        $this->warnWhenSummingTwoStrings($thisColumnValue, $columnToSumValue, $columnName);

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
        if (is_array($elem2)) {
            return -1;
        }

        if ((string)$elem1 === (string)$elem2) {
            return 0;
        }

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
        if (
            !(is_null($row1->getIdSubDataTable())
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

    private function warnIfSubtableAlreadyExists(DataTable $subTable)
    {
        if (!is_null($this->subtableId)) {
            // we only print this warning out if the row isn't a summary row, and if the period start date of the
            // data is later than the deploy date to cloud for Matomo 4.4.1.
            //
            // In 4.4.1 two bugs surrounding the serialization of summary rows with subtables were fixed. Previously,
            // if a summary row had a subtable when it was inserted into the archive table, this warning would eventually
            // get triggered. To properly fix this corrupt data, we'd want to invalidate and reporcess it, BUT, that would
            // require a lot of compute resources, just for the subtable of a row most people would not look at.
            //
            // So instead, we simply ignore this issue for data that is for periods older than the deploy date for 4.4.1. If a user
            // wants to see this subtable data, they can invalidate a specific date and reprocess it. For newer data,
            // since the bugs were fixed, we don't expect to see the issue. So if the warning gets triggered in this case,
            // we log the warning in order to be notified.
            $period = $subTable->getMetadata('period');
            if (
                !$this->isSummaryRow()
                || $this->isStartDateLaterThanCloud441DeployDate($period)
            ) {
                $ex = new \Exception(sprintf(
                    "Row with label '%s' (columns = %s) has already a subtable id=%s but it was not loaded - overwriting the existing sub-table.",
                    $this->getColumn('label'),
                    implode(", ", $this->getColumns()),
                    $this->getIdSubDataTable()
                ));
                StaticContainer::get(LoggerInterface::class)->warning("{exception}", ['exception' => $ex]);
            }
        }
    }

    protected function warnWhenSummingTwoStrings($thisColumnValue, $columnToSumValue, $columnName = null)
    {
        if (is_string($columnToSumValue)) {
            $ex = new \Exception(sprintf(
                "Trying to add two strings in DataTable\Row::sumRowArray: %s + %s for column %s in row %s",
                $thisColumnValue,
                $columnToSumValue,
                $columnName,
                $this->__toString()
            ));
            StaticContainer::get(LoggerInterface::class)->warning("{exception}", ['exception' => $ex]);
        }
    }

    private function getColumnValueDescriptionForError($value)
    {
        $result = gettype($value);
        if (is_array($result)) {
            $result .= ' ' . json_encode($value);
        }
        return $result;
    }

    private function isStartDateLaterThanCloud441DeployDate($period)
    {
        if (
            empty($period)
            || !($period instanceof Period)
        ) {
            return true; // sanity check
        }

        $periodStartDate = $period->getDateStart();

        $cloudDeployDate = Date::factory('2021-08-11 12:00:00'); // 2021-08-12 00:00:00 NZST
        return $periodStartDate->isLater($cloudDeployDate);
    }

    public function sumRowWithLabelToSubtable(string $label, array $columns, ?array $aggregationOps = null): Row
    {
        $subtable = $this->getSubtable();
        if (empty($subtable)) {
            $subtable = new DataTable();
            $subtable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $aggregationOps);
            $this->setSubtable($subtable);
        }

        return $subtable->sumRowWithLabel($label, $columns, $aggregationOps);
    }
}
