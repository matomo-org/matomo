<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * Inherit from this class to define archiving logic for one or more records.
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
     * @var string|null
     */
    protected $columnToSortByBeforeTruncation;

    /**
     * @var array|null
     */
    protected $columnAggregationOps;

    /**
     * @var array|null
     */
    protected $columnToRenameAfterAggregation;

    /**
     * @param int|null $maxRowsInTable
     * @param int|null $maxRowsInSubtable
     * @param string|null $columnToSortByBeforeTruncation
     * @param array|null $columnAggregationOps
     */
    public function __construct(
        ?int $maxRowsInTable = null,
        ?int $maxRowsInSubtable = null,
        ?string $columnToSortByBeforeTruncation = null,
        ?array $columnAggregationOps = null,
        ?array $columnToRenameAfterAggregation = null
    ) {
        $this->maxRowsInTable = $maxRowsInTable;
        $this->maxRowsInSubtable = $maxRowsInSubtable;
        $this->columnToSortByBeforeTruncation = $columnToSortByBeforeTruncation;
        $this->columnAggregationOps = $columnAggregationOps;
        $this->columnToRenameAfterAggregation = $columnToRenameAfterAggregation;
    }

    public function isEnabled(ArchiveProcessor $archiveProcessor): bool
    {
        return true;
    }

    /**
     * Uses the protected `aggregate()` function to build records by aggregating log table data directly, then
     * inserts them as archive data.
     *
     * @param ArchiveProcessor $archiveProcessor
     * @return void
     */
    public function buildFromLogs(ArchiveProcessor $archiveProcessor): void
    {
        if (!$this->isEnabled($archiveProcessor)) {
            return;
        }

        $recordsBuilt = $this->getRecordMetadata($archiveProcessor);

        $recordMetadataByName = [];
        foreach ($recordsBuilt as $recordMetadata) {
            if (!($recordMetadata instanceof Record)) {
                continue;
            }

            $recordMetadataByName[$recordMetadata->getName()] = $recordMetadata;
        }

        $numericRecords = [];

        $records = $this->aggregate($archiveProcessor);
        foreach ($records as $recordName => $recordValue) {
            if (empty($recordMetadataByName[$recordName])) {
                if ($recordValue instanceof DataTable) {
                    Common::destroy($recordValue);
                }
                continue;
            }

            if ($recordValue instanceof DataTable) {
                $record = $recordMetadataByName[$recordName];

                $maxRowsInTable = $record->getMaxRowsInTable() ?? $this->maxRowsInTable;
                $maxRowsInSubtable = $record->getMaxRowsInSubtable() ?? $this->maxRowsInSubtable;
                $columnToSortByBeforeTruncation = $record->getColumnToSortByBeforeTruncation() ?? $this->columnToSortByBeforeTruncation;

                $this->insertBlobRecord($archiveProcessor, $recordName, $recordValue, $maxRowsInTable, $maxRowsInSubtable, $columnToSortByBeforeTruncation);

                Common::destroy($recordValue);
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

    /**
     * Builds records for non-day periods by aggregating day records together, then inserts
     * them as archive data.
     *
     * @param ArchiveProcessor $archiveProcessor
     * @return void
     */
    public function buildForNonDayPeriod(ArchiveProcessor $archiveProcessor): void
    {
        if (!$this->isEnabled($archiveProcessor)) {
            return;
        }

        $requestedReports = $archiveProcessor->getParams()->getArchiveOnlyReportAsArray();
        $foundRequestedReports = $archiveProcessor->getParams()->getFoundRequestedReports();

        $recordsBuilt = $this->getRecordMetadata($archiveProcessor);

        $numericRecords = array_filter($recordsBuilt, function (Record $r) {
            return $r->getType() == Record::TYPE_NUMERIC;
        });
        $blobRecords = array_filter($recordsBuilt, function (Record $r) {
            return $r->getType() == Record::TYPE_BLOB;
        });

        $aggregatedCounts = [];

        // make sure if there are requested numeric records that depend on blob records, that the blob records will be archived first
        foreach ($numericRecords as $record) {
            if (
                empty($record->getCountOfRecordName())
                || !in_array($record->getName(), $requestedReports)
            ) {
                continue;
            }

            $dependentRecordName = $record->getCountOfRecordName();
            if (!in_array($dependentRecordName, $requestedReports)) {
                $requestedReports[] = $dependentRecordName;
            }

            // we need to aggregate the blob record to get the count, so even if it's found, we must re-aggregate it
            // TODO: this could potentially be optimized away, but it would be non-trivial given the current ArchiveProcessor API
            $indexInFoundRecords = array_search($dependentRecordName, $foundRequestedReports);
            if ($indexInFoundRecords !== false) {
                unset($foundRequestedReports[$indexInFoundRecords]);
            }
        }

        foreach ($blobRecords as $record) {
            if (
                !empty($requestedReports)
                && (!in_array($record->getName(), $requestedReports)
                    || in_array($record->getName(), $foundRequestedReports))
            ) {
                continue;
            }

            $maxRowsInTable = $record->getMaxRowsInTable() ?? $this->maxRowsInTable;
            $maxRowsInSubtable = $record->getMaxRowsInSubtable() ?? $this->maxRowsInSubtable;
            $columnToSortByBeforeTruncation = $record->getColumnToSortByBeforeTruncation() ?? $this->columnToSortByBeforeTruncation;
            $columnToRenameAfterAggregation = $record->getColumnToRenameAfterAggregation() ?? $this->columnToRenameAfterAggregation;
            $columnAggregationOps = $record->getBlobColumnAggregationOps() ?? $this->columnAggregationOps;

            // only do recursive row count if there is a numeric record that depends on it
            $countRecursiveRows = false;
            foreach ($numericRecords as $numeric) {
                if (
                    $numeric->getCountOfRecordName() == $record->getName()
                    && $numeric->getCountOfRecordNameIsRecursive()
                ) {
                    $countRecursiveRows = true;
                    break;
                }
            }

            $counts = $archiveProcessor->aggregateDataTableRecords(
                $record->getName(),
                $maxRowsInTable,
                $maxRowsInSubtable,
                $columnToSortByBeforeTruncation,
                $columnAggregationOps,
                $columnToRenameAfterAggregation,
                $countRecursiveRows
            );

            $aggregatedCounts = array_merge($aggregatedCounts, $counts);
        }

        if (!empty($numericRecords)) {
            // handle metrics that are aggregated using metric values from child periods
            $autoAggregateMetrics = array_filter($numericRecords, function (Record $r) {
                return empty($r->getCountOfRecordName());
            });
            $autoAggregateMetrics = array_map(function (Record $r) {
                return $r->getName();
            }, $autoAggregateMetrics);

            if (!empty($requestedReports)) {
                $autoAggregateMetrics = array_filter($autoAggregateMetrics, function ($name) use ($requestedReports, $foundRequestedReports) {
                    return in_array($name, $requestedReports) && !in_array($name, $foundRequestedReports);
                });
            }

            $autoAggregateMetrics = array_values($autoAggregateMetrics);

            if (!empty($autoAggregateMetrics)) {
                $archiveProcessor->aggregateNumericMetrics($autoAggregateMetrics, $this->columnAggregationOps);
            }

            // handle metrics that are set to counts of blob records
            $recordCountMetricValues = [];

            $recordCountMetrics = array_filter($numericRecords, function (Record $r) {
                return !empty($r->getCountOfRecordName());
            });
            foreach ($recordCountMetrics as $record) {
                $dependentRecordName = $record->getCountOfRecordName();
                if (empty($aggregatedCounts[$dependentRecordName])) {
                    continue; // dependent record not archived, so skip this metric
                }

                $count = $aggregatedCounts[$dependentRecordName];

                if ($record->getCountOfRecordNameIsRecursive()) {
                    $recordCountMetricValues[$record->getName()] = $count['recursive'];
                } else {
                    $recordCountMetricValues[$record->getName()] = $count['level0'];
                }

                $transform = $record->getMultiplePeriodTransform();
                if (!empty($transform)) {
                    $recordCountMetricValues[$record->getName()] = $transform($recordCountMetricValues[$record->getName()], $count);
                }
            }

            if (!empty($recordCountMetricValues)) {
                $archiveProcessor->insertNumericRecords($recordCountMetricValues);
            }
        }
    }

    /**
     * Returns metadata for records primarily used when aggregating over non-day periods. Every numeric/blob
     * record your RecordBuilder creates should have an associated piece of record metadata.
     *
     * @return Record[]
     */
    abstract public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array;

    /**
     * Derived classes should define this method to aggregate log data for a single day and return the records
     * to store indexed by record names.
     *
     * @return (DataTable|int|float|string)[] Record values indexed by their record name, eg, `['MyPlugin_MyRecord' => new DataTable()]`
     */
    abstract protected function aggregate(ArchiveProcessor $archiveProcessor): array;

    protected function insertBlobRecord(
        ArchiveProcessor $archiveProcessor,
        string $recordName,
        DataTable $record,
        ?int $maxRowsInTable,
        ?int $maxRowsInSubtable,
        ?string $columnToSortByBeforeTruncation
    ): void {
        $serialized = $record->getSerialized(
            $maxRowsInTable ?: $this->maxRowsInTable,
            $maxRowsInSubtable ?: $this->maxRowsInSubtable,
            $columnToSortByBeforeTruncation ?: $this->columnToSortByBeforeTruncation
        );
        $archiveProcessor->insertBlobRecord($recordName, $serialized);
        unset($serialized);
    }

    public function getMaxRowsInTable(): ?int
    {
        return $this->maxRowsInTable;
    }

    public function getMaxRowsInSubtable(): ?int
    {
        return $this->maxRowsInSubtable;
    }

    public function getColumnToSortByBeforeTruncation(): ?string
    {
        return $this->columnToSortByBeforeTruncation;
    }

    public function getPluginName(): string
    {
        return Piwik::getPluginNameOfMatomoClass(get_class($this));
    }

    /**
     * Returns an extra hint for LogAggregator to add to log aggregation SQL. Can be overridden if you'd
     * like the origin hint to have more information.
     *
     * @return string
     */
    public function getQueryOriginHint(): string
    {
        $recordBuilderName = get_class($this);
        $recordBuilderName = explode('\\', $recordBuilderName);
        return end($recordBuilderName);
    }

    /**
     * Returns true if at least one of the given reports is handled by this RecordBuilder instance
     * when invoked with the given ArchiveProcessor.
     *
     * @param ArchiveProcessor $archiveProcessor Archiving parameters, like idSite, can influence the list of
     *                                           all records a RecordBuilder produces, so it is required here.
     * @param string[] $requestedReports The list of requested reports to check for.
     * @return bool
     */
    public function isBuilderForAtLeastOneOf(ArchiveProcessor $archiveProcessor, array $requestedReports): bool
    {
        $recordMetadata = $this->getRecordMetadata($archiveProcessor);
        foreach ($recordMetadata as $record) {
            if (in_array($record->getName(), $requestedReports)) {
                return true;
            }
        }
        return false;
    }
}
