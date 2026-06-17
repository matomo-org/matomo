<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

/**
 * @api
 * @since 5.0.0
 */
class Record
{
    public const TYPE_NUMERIC = 'numeric';
    public const TYPE_BLOB = 'blob';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $plugin = null;

    /**
     * @var string|int
     */
    private $columnToSortByBeforeTruncation;

    /**
     * @var int|null
     */
    private $maxRowsInTable;

    /**
     * @var int|null
     */
    private $maxRowsInSubtable;

    /**
     * @var string|null
     */
    private $countOfRecordName = null;

    /**
     * @var bool
     */
    private $countOfRecordNameIsRecursive = false;

    /**
     * @var bool
     */
    private $countOfRecordNameIsForLeafs = false;

    /**
     * @var array|null
     */
    private $columnToRenameAfterAggregation = null;

    /**
     * @var array|null
     */
    private $blobColumnAggregationOps = null;

    /**
     * @var callable|null
     */
    private $multiplePeriodTransform = null;

    /**
     * @var callable|null
     */
    private $aggregatedRecordTransform = null;

    /**
     * @var string|null
     */
    private $builtFromFlatRecord = null;

    /**
     * @var callable|null
     */
    private $flatToHierarchyPathCallback = null;

    /**
     * @var callable|null
     */
    private $legacyHierarchyToFlatReducerCallback = null;

    public static function make($type, $name)
    {
        $record = new Record();
        $record->setType($type);
        $record->setName($name);
        return $record;
    }

    public function setPlugin(?string $plugin): Record
    {
        $this->plugin = $plugin;
        return $this;
    }

    public function setName(string $name): Record
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new \Exception('Invalid record name: ' . $name . '. Only alphanumeric characters, hyphens and underscores are allowed.');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @param int|string $columnToSortByBeforeTruncation
     * @return Record
     */
    public function setColumnToSortByBeforeTruncation($columnToSortByBeforeTruncation)
    {
        $this->columnToSortByBeforeTruncation = $columnToSortByBeforeTruncation;
        return $this;
    }

    public function setMaxRowsInTable(?int $maxRowsInTable): Record
    {
        $this->maxRowsInTable = $maxRowsInTable;
        return $this;
    }

    public function setMaxRowsInSubtable(?int $maxRowsInSubtable): Record
    {
        $this->maxRowsInSubtable = $maxRowsInSubtable;
        return $this;
    }

    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int|string
     */
    public function getColumnToSortByBeforeTruncation()
    {
        return $this->columnToSortByBeforeTruncation;
    }

    public function getMaxRowsInTable(): ?int
    {
        return $this->maxRowsInTable;
    }

    public function getMaxRowsInSubtable(): ?int
    {
        return $this->maxRowsInSubtable;
    }

    public function setType(string $type): Record
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setIsCountOfBlobRecordRows(string $dependentRecordName, bool $isRecursive = false): Record
    {
        $this->countOfRecordName = $dependentRecordName;
        $this->countOfRecordNameIsRecursive = $isRecursive;
        return $this;
    }

    public function setIsCountOfBlobRecordLeafRows(string $dependentRecordName): Record
    {
        $this->countOfRecordName           = $dependentRecordName;
        $this->countOfRecordNameIsForLeafs = true;
        return $this;
    }

    public function getCountOfRecordName(): ?string
    {
        return $this->countOfRecordName;
    }

    public function getCountOfRecordNameIsRecursive(): bool
    {
        return $this->countOfRecordNameIsRecursive;
    }

    public function getCountOfRecordNameIsForLeafs(): bool
    {
        return $this->countOfRecordNameIsForLeafs;
    }

    /**
     * @param array|null $columnToRenameAfterAggregation
     */
    public function setColumnToRenameAfterAggregation(?array $columnToRenameAfterAggregation): Record
    {
        $this->columnToRenameAfterAggregation = $columnToRenameAfterAggregation;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getColumnToRenameAfterAggregation(): ?array
    {
        return $this->columnToRenameAfterAggregation;
    }

    /**
     * @param array|null $blobColumnAggregationOps
     */
    public function setBlobColumnAggregationOps(?array $blobColumnAggregationOps): Record
    {
        $this->blobColumnAggregationOps = $blobColumnAggregationOps;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getBlobColumnAggregationOps(): ?array
    {
        return $this->blobColumnAggregationOps;
    }

    public function setMultiplePeriodTransform(?callable $multiplePeriodTransform): Record
    {
        $this->multiplePeriodTransform = $multiplePeriodTransform;
        return $this;
    }

    public function getMultiplePeriodTransform(): ?callable
    {
        return $this->multiplePeriodTransform;
    }

    /**
     * Sets a transform applied to this blob record's aggregated table during non-day archiving,
     * after the day blobs have been aggregated together (additive columns summed, columns marked
     * 'skip' in the aggregation ops left untouched) and before the table is truncated and stored.
     *
     * Use this for columns that cannot be summed across child periods and must be recomputed from
     * the aggregated additive columns — for example a table-relative ratio, index or score. Mark
     * such a column 'skip' via {@see setBlobColumnAggregationOps()} so it is not summed, then
     * recompute it here. Because the transform runs before truncation, a column it (re)computes can
     * be used as {@see setColumnToSortByBeforeTruncation()}.
     *
     * Only used for non-day periods; the day archive builds the record from logs via the
     * RecordBuilder's aggregate() and should apply any equivalent computation there.
     *
     * Applies on both the standard blob path and the built-from-flat path ({@see setBuiltFromFlatRecord()}):
     * each record's transform runs on that record's own aggregated table, so a flat base record and the
     * hierarchy rebuilt from it are each transformed (the hierarchy after it is built) before being stored.
     *
     * @param callable|null $transform Signature:
     *                                 function (\Piwik\DataTable $table, ArchiveProcessor $archiveProcessor, Record $record): void
     *                                 The callback mutates $table in place.
     */
    public function setAggregatedRecordTransform(?callable $transform): Record
    {
        if (null !== $transform && $this->type !== self::TYPE_BLOB) {
            throw new \InvalidArgumentException('setAggregatedRecordTransform() can only be used with blob records.');
        }

        $this->aggregatedRecordTransform = $transform;
        return $this;
    }

    public function getAggregatedRecordTransform(): ?callable
    {
        return $this->aggregatedRecordTransform;
    }

    /**
     * Marks this blob record as being derived from a flat blob record during non-day aggregation.
     *
     * Use this when day archives store a flat representation and non-day archives should rebuild
     * hierarchy from it. The flat record must be present in getRecordMetadata().
     *
     * @param string $flatRecordName Name of the flat blob record to aggregate first.
     * @param callable $flatToHierarchyPathCallback Callback used when rebuilding hierarchy.
     *                                              Signature: function (Row $flatRow, ArchiveProcessor $archiveProcessor, Record $hierarchicalRecord): ?array
     *                                              Return value is the path of labels to map the flat row into the hierarchy.
     * @param callable|null $legacyHierarchyToFlatReducerCallback Optional callback that can merge legacy hierarchical
     *                                                            aggregates into the flat table when some periods do not
     *                                                            have the flat record yet.
     *                                                            Signature: function (DataTable $legacyHierarchy, DataTable $flatTable, ArchiveProcessor $archiveProcessor, Record $hierarchicalRecord): void
     *                                                            The callback is invoked once per legacy source period hierarchy table.
     */
    public function setBuiltFromFlatRecord(
        string $flatRecordName,
        callable $flatToHierarchyPathCallback,
        ?callable $legacyHierarchyToFlatReducerCallback = null
    ): Record {
        if ($this->type !== self::TYPE_BLOB) {
            throw new \InvalidArgumentException('setBuiltFromFlatRecord() can only be used with blob records.');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $flatRecordName)) {
            throw new \InvalidArgumentException('Invalid flat record name: ' . $flatRecordName);
        }

        $this->builtFromFlatRecord = $flatRecordName;
        $this->flatToHierarchyPathCallback = $flatToHierarchyPathCallback;
        $this->legacyHierarchyToFlatReducerCallback = $legacyHierarchyToFlatReducerCallback;

        return $this;
    }

    public function getBuiltFromFlatRecord(): ?string
    {
        return $this->builtFromFlatRecord;
    }

    /**
     * @see setBuiltFromFlatRecord()
     */
    public function getFlatToHierarchyPathCallback(): ?callable
    {
        return $this->flatToHierarchyPathCallback;
    }

    /**
     * @see setBuiltFromFlatRecord()
     */
    public function getLegacyHierarchyToFlatReducerCallback(): ?callable
    {
        return $this->legacyHierarchyToFlatReducerCallback;
    }
}
