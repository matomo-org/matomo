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
}
