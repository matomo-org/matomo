<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

/**
 * @api
 * @since 5.0.0
 */
class Record
{
    const TYPE_NUMERIC = 'numeric';
    const TYPE_BLOB = 'blob';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

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

    /**
     * @param string|null $plugin
     * @return Record
     */
    public function setPlugin(?string $plugin): Record
    {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * @param string $name
     * @return Record
     */
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

    /**
     * @param int|null $maxRowsInTable
     * @return Record
     */
    public function setMaxRowsInTable(?int $maxRowsInTable): Record
    {
        $this->maxRowsInTable = $maxRowsInTable;
        return $this;
    }

    /**
     * @param int|null $maxRowsInSubtable
     * @return Record
     */
    public function setMaxRowsInSubtable(?int $maxRowsInSubtable): Record
    {
        $this->maxRowsInSubtable = $maxRowsInSubtable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    /**
     * @return string
     */
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

    /**
     * @return int|null
     */
    public function getMaxRowsInTable(): ?int
    {
        return $this->maxRowsInTable;
    }

    /**
     * @return int|null
     */
    public function getMaxRowsInSubtable(): ?int
    {
        return $this->maxRowsInSubtable;
    }

    /**
     * @param string $type
     * @return Record
     */
    public function setType(string $type): Record
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
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

    /**
     * @return string|null
     */
    public function getCountOfRecordName(): ?string
    {
        return $this->countOfRecordName;
    }

    /**
     * @return bool
     */
    public function getCountOfRecordNameIsRecursive(): bool
    {
        return $this->countOfRecordNameIsRecursive;
    }

    /**
     * @param array|null $columnToRenameAfterAggregation
     * @return Record
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
     * @return Record
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

    /**
     * @param ?callable $multiplePeriodTransform
     * @return Record
     */
    public function setMultiplePeriodTransform(?callable $multiplePeriodTransform): Record
    {
        $this->multiplePeriodTransform = $multiplePeriodTransform;
        return $this;
    }

    /**
     * @return callable
     */
    public function getMultiplePeriodTransform(): ?callable
    {
        return $this->multiplePeriodTransform;
    }
}
