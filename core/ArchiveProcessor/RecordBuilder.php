<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\DataTable;

/**
 * TODO docs
 * @api
 */
abstract class RecordBuilder
{
    /**
     * @var int
     */
    protected $maxRowsInTable;

    /**
     * @var int
     */
    protected $maxRowsInSubtable;

    /**
     * @var string|int
     */
    protected $columnToSortByBeforeTruncation;

    /**
     * @var int
     */
    protected $blobReportLimit;

    /**
     * @var array|null
     */
    protected $columnAggregationOps;

    public function __construct($maxRowsInTable = null, $maxRowsInSubtable = null,
                                $columnToSortByBeforeTruncation = null, $columnAggregationOps = null)
    {
        $this->maxRowsInTable = $maxRowsInTable;
        $this->maxRowsInSubtable = $maxRowsInSubtable;
        $this->columnToSortByBeforeTruncation = $columnToSortByBeforeTruncation;
        $this->columnAggregationOps = $columnAggregationOps;
    }

    public function isEnabled()
    {
        return true;
    }

    public function build(ArchiveProcessor $archiveProcessor)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $numericRecords = [];

        $records = $this->aggregate($archiveProcessor);
        foreach ($records as $recordName => $recordValue) {
            if ($recordValue instanceof DataTable) {
                $this->insertRecord($archiveProcessor, $recordName, $recordValue);

                Common::destroy($recordValue);
                unset($recordValue);
            } else {
                // collect numeric records so we can insert them all at once
                $numericRecords[$recordName] = $recordValue;
            }
        }
        unset($records);

        if (!empty($numericRecords)) {
            $archiveProcessor->insertNumericRecords($numericRecords);
        }
    }

    public function buildMultiplePeriod(ArchiveProcessor $archiveProcessor)
    {
        if (!$this->isEnabled()) {
            return;
        }

        $recordsBuilt = $this->getRecordMetadata($archiveProcessor);

        $numericRecords = array_filter($recordsBuilt, function (Record $r) { return $r->getType() == Record::TYPE_NUMERIC; });
        $blobRecords = array_filter($recordsBuilt, function (Record $r) { return $r->getType() == Record::TYPE_BLOB; });

        foreach ($blobRecords as $record) {
            $maxRowsInTable = $record->getMaxRowsInTable() ?? $this->maxRowsInTable;
            $maxRowsInSubtable = $record->getMaxRowsInSubtable() ?? $this->maxRowsInSubtable;
            $columnToSortByBeforeTruncation = $record->getColumnToSortByBeforeTruncation() ?? $this->columnToSortByBeforeTruncation;

            $archiveProcessor->aggregateDataTableRecords(
                $record->getName(),
                $maxRowsInTable,
                $maxRowsInSubtable,
                $columnToSortByBeforeTruncation,
                $this->columnAggregationOps
            );
        }

        if (!empty($numericRecords)) {
            $numericMetrics = array_map(function (Record $r) { return $r->getName(); }, $numericRecords);
            $archiveProcessor->aggregateNumericMetrics($numericMetrics, $this->columnAggregationOps);
        }
    }

    /**
     * @return Record[]
     */
    public abstract function getRecordMetadata(ArchiveProcessor $archiveProcessor);

    /**
     * @return (DataTable|int|float|string)[]
     */
    protected abstract function aggregate(ArchiveProcessor $archiveProcessor);

    private function insertRecord(ArchiveProcessor $archiveProcessor, $recordName, DataTable\DataTableInterface $record)
    {
        if ($record->getRowsCount() <= 0) {
            return;
        }

        $serialized = $record->getSerialized($this->maxRowsInTable, $this->maxRowsInSubtable, $this->columnToSortByBeforeTruncation);
        $archiveProcessor->insertBlobRecord($recordName, $serialized);
        unset($serialized);
    }

    public function getMaxRowsInTable()
    {
        return $this->maxRowsInTable;
    }

    public function getMaxRowsInSubtable()
    {
        return $this->maxRowsInSubtable;
    }

    public function getColumnToSortByBeforeTruncation()
    {
        return $this->columnToSortByBeforeTruncation;
    }

    public function getPluginName()
    {
        $className = get_class($this);
        $parts = explode('\\', $className);
        $parts = array_filter($parts);
        $plugin = $parts[2];
        return $plugin;
    }

    private function getQueryDecoration() // TODO: use query decoration in LogAggregator or wherever
    {
        $recordBuilderName = get_class($this);
        $recordBuilderName = explode('\\', $recordBuilderName);
        return end($recordBuilderName);
    }
}