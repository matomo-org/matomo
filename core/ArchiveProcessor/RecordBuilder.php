<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Archive;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Piwik;

/**
 * Inherit from this class to define archiving logic for one or more records.
 */
abstract class RecordBuilder
{
    /**
     * @var int|null
     */
    protected $maxRowsInTable;

    /**
     * @var int|null
     */
    protected $maxRowsInSubtable;

    /**
     * @var string|int|null
     */
    protected $columnToSortByBeforeTruncation;

    /**
     * @var array|null
     */
    protected $columnAggregationOps;

    /**
     * @var array<string|int,string|int>|null
     */
    protected $columnToRenameAfterAggregation;

    /**
     * @param array|null $columnAggregationOps
     * @param array<string|int,string|int>|null $columnToRenameAfterAggregation
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
        $blobRecordsByName = [];
        foreach ($blobRecords as $blobRecord) {
            $blobRecordsByName[$blobRecord->getName()] = $blobRecord;
        }

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

        $processedFlatRecords = [];
        foreach ($blobRecords as $record) {
            if (
                !empty($requestedReports)
                && (!in_array($record->getName(), $requestedReports)
                    || in_array($record->getName(), $foundRequestedReports))
            ) {
                continue;
            }

            if (isset($processedFlatRecords[$record->getName()])) {
                continue;
            }

            $maxRowsInTable = $record->getMaxRowsInTable() ?? $this->maxRowsInTable;
            $maxRowsInSubtable = $record->getMaxRowsInSubtable() ?? $this->maxRowsInSubtable;
            $columnToSortByBeforeTruncation = $record->getColumnToSortByBeforeTruncation() ?? $this->columnToSortByBeforeTruncation;
            $columnToRenameAfterAggregation = $record->getColumnToRenameAfterAggregation() ?? $this->columnToRenameAfterAggregation;
            $columnAggregationOps = $record->getBlobColumnAggregationOps() ?? $this->columnAggregationOps;

            if (
                $this->aggregateBuiltFromFlatRecordForNonDay(
                    $archiveProcessor,
                    $record,
                    $blobRecordsByName,
                    $columnAggregationOps,
                    $columnToRenameAfterAggregation,
                    $columnToSortByBeforeTruncation,
                    $processedFlatRecords
                )
            ) {
                continue;
            }

            // only do recursive row counts if there is a numeric record that depends on it
            $countRecursiveRows = $countLeafRows = [];
            foreach ($numericRecords as $numeric) {
                if (
                    $numeric->getCountOfRecordName() == $record->getName()
                ) {
                    if ($numeric->getCountOfRecordNameIsRecursive()) {
                        $countRecursiveRows[] = $numeric->getCountOfRecordName();
                    }
                    if ($numeric->getCountOfRecordNameIsForLeafs()) {
                        $countLeafRows[] = $numeric->getCountOfRecordName();
                    }
                }
            }

            $counts = $archiveProcessor->aggregateDataTableRecords(
                $record->getName(),
                $maxRowsInTable,
                $maxRowsInSubtable,
                $columnToSortByBeforeTruncation,
                $columnAggregationOps,
                $columnToRenameAfterAggregation,
                $countRecursiveRows,
                $countLeafRows
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

                if ($record->getCountOfRecordNameIsForLeafs()) {
                    $recordCountMetricValues[$record->getName()] = $count['leafs'];
                } elseif ($record->getCountOfRecordNameIsRecursive()) {
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

    protected function aggregateBuiltFromFlatRecordForNonDay(
        ArchiveProcessor $archiveProcessor,
        Record $hierarchicalRecord,
        array $blobRecordsByName,
        ?array $columnAggregationOps,
        ?array $columnToRenameAfterAggregation,
        ?string $columnToSortByBeforeTruncation,
        array &$processedFlatRecords
    ): bool {
        $flatRecordName = $hierarchicalRecord->getBuiltFromFlatRecord();
        if (empty($flatRecordName)) {
            return false;
        }

        $flatToHierarchyPathCallback = $hierarchicalRecord->getFlatToHierarchyPathCallback();
        if (!is_callable($flatToHierarchyPathCallback)) {
            return false;
        }

        $flatRecord = $blobRecordsByName[$flatRecordName] ?? null;
        if (empty($flatRecord)) {
            return false;
        }

        $flatColumnAggregationOps = $flatRecord->getBlobColumnAggregationOps() ?? $this->columnAggregationOps;
        $flatColumnToRenameAfterAggregation = $flatRecord->getColumnToRenameAfterAggregation() ?? $this->columnToRenameAfterAggregation;
        $flatColumnToSortByBeforeTruncation = $flatRecord->getColumnToSortByBeforeTruncation() ?? $this->columnToSortByBeforeTruncation;
        $flatMaxRowsInTable = $flatRecord->getMaxRowsInTable() ?? $this->maxRowsInTable;

        $periodsWithFlatRecord = $this->getPeriodsWithRootBlob($archiveProcessor, $flatRecordName);
        $allSubperiodKeys = $this->getAllSubperiodKeys($archiveProcessor);
        $periodsWithoutFlatRecord = array_diff_key($allSubperiodKeys, $periodsWithFlatRecord);

        [$flatTable, $hasFlatSourceData] = $this->aggregateDataTableFromBlobs(
            $archiveProcessor,
            $flatRecordName,
            $flatColumnAggregationOps,
            $flatColumnToRenameAfterAggregation,
            $periodsWithFlatRecord
        );

        $hasLegacyFallbackData = false;
        $legacyReducerCallback = $hierarchicalRecord->getLegacyHierarchyToFlatReducerCallback();
        if (!empty($periodsWithoutFlatRecord) && is_callable($legacyReducerCallback)) {
            [$legacyHierarchicalTable, $hasLegacyFallbackData] = $this->aggregateDataTableFromBlobs(
                $archiveProcessor,
                $hierarchicalRecord->getName(),
                $columnAggregationOps,
                $columnToRenameAfterAggregation,
                $periodsWithoutFlatRecord
            );

            if ($hasLegacyFallbackData) {
                call_user_func($legacyReducerCallback, $legacyHierarchicalTable, $flatTable, $archiveProcessor, $hierarchicalRecord);
            }
            Common::destroy($legacyHierarchicalTable);
        }

        if (!$hasFlatSourceData && !$hasLegacyFallbackData) {
            Common::destroy($flatTable);
            return false;
        }

        $flatSerialized = $flatTable->getSerialized(
            $flatMaxRowsInTable,
            null,
            $flatColumnToSortByBeforeTruncation
        );
        $archiveProcessor->insertBlobRecord($flatRecordName, $flatSerialized);
        $processedFlatRecords[$flatRecordName] = true;

        $hierarchicalTable = $this->buildHierarchicalTableFromFlatTable(
            $flatTable,
            $columnAggregationOps,
            $flatToHierarchyPathCallback,
            $archiveProcessor,
            $hierarchicalRecord
        );

        $this->beforeInsertBuiltFromFlatHierarchyRecord($archiveProcessor, $hierarchicalRecord, $hierarchicalTable, $flatTable);

        $hierarchicalSerialized = $hierarchicalTable->getSerialized(
            null,
            null,
            $columnToSortByBeforeTruncation
        );
        $archiveProcessor->insertBlobRecord($hierarchicalRecord->getName(), $hierarchicalSerialized);

        Common::destroy($hierarchicalTable);
        Common::destroy($flatTable);

        return true;
    }

    protected function beforeInsertBuiltFromFlatHierarchyRecord(
        ArchiveProcessor $archiveProcessor,
        Record $hierarchicalRecord,
        DataTable $hierarchicalTable,
        DataTable $flatTable
    ): void {
    }

    protected function buildHierarchicalTableFromFlatTable(
        DataTable $flatTable,
        ?array $columnAggregationOps,
        callable $flatToHierarchyPathCallback,
        ArchiveProcessor $archiveProcessor,
        Record $hierarchicalRecord
    ): DataTable {
        $hierarchicalTable = new DataTable();
        if (!empty($columnAggregationOps)) {
            $hierarchicalTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnAggregationOps);
        }

        foreach ($flatTable->getRows() as $flatRow) {
            if ($flatRow->isSummaryRow()) {
                if ($this->isSummaryRowEmpty($flatRow)) {
                    continue;
                }

                $summaryRow = $hierarchicalTable->getRowFromId(DataTable::ID_SUMMARY_ROW);
                if ($summaryRow === false) {
                    $summaryRow = clone $flatRow;
                    $summaryRow->setIsSummaryRow();
                    $hierarchicalTable->addSummaryRow($summaryRow);
                    continue;
                }

                $this->sumRowIntoDestination($flatRow, $summaryRow, $columnAggregationOps);
                continue;
            }

            $path = call_user_func($flatToHierarchyPathCallback, $flatRow, $archiveProcessor, $hierarchicalRecord);
            if (!is_array($path) || empty($path)) {
                continue;
            }

            [$destinationRow, $level] = $hierarchicalTable->walkPath($path, [], 0);
            if (!$destinationRow instanceof Row) {
                continue;
            }

            $this->sumRowIntoDestination($flatRow, $destinationRow, $columnAggregationOps);
        }

        return $hierarchicalTable;
    }

    protected function sumRowIntoDestination(Row $source, Row $destination, ?array $columnAggregationOps): void
    {
        $sourceCopy = clone $source;
        $destination->sumRow($sourceCopy, true, $columnAggregationOps ?? []);
    }

    protected function isSummaryRowEmpty(Row $summaryRow): bool
    {
        foreach ($summaryRow->getColumns() as $name => $value) {
            if ($name === 'label') {
                continue;
            }

            if (is_array($value)) {
                if (!empty($value)) {
                    return false;
                }
                continue;
            }

            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }

    protected function aggregateDataTableFromBlobs(
        ArchiveProcessor $archiveProcessor,
        string $recordName,
        ?array $columnsAggregationOperation,
        ?array $columnsToRenameAfterAggregation,
        ?array $periodsToInclude = null
    ): array {
        $tableIdToResultRowMapping = [];
        $result = new DataTable();

        if (!empty($columnsAggregationOperation)) {
            $result->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
        }

        $hasRows = false;
        foreach ($this->querySingleBlobRows($archiveProcessor, $recordName) as $archiveDataRow) {
            $period = $archiveDataRow['date1'] . ',' . $archiveDataRow['date2'];
            if ($periodsToInclude !== null && !isset($periodsToInclude[$period])) {
                continue;
            }

            $hasRows = true;
            $tableId = $archiveDataRow['name'] == $recordName ? null : $this->getSubtableIdFromBlobName($archiveDataRow['name']);

            $blobTable = DataTable::fromSerializedArray($archiveDataRow['value']);
            $blobTable->filter(function (DataTable $table) use ($archiveProcessor, $columnsToRenameAfterAggregation) {
                $archiveProcessor->renameColumnsAfterAggregation($table, $columnsToRenameAfterAggregation);
            });

            if ($tableId === null) {
                $tableToAddTo = $result;
            } elseif (!empty($tableIdToResultRowMapping[$period][$tableId])) {
                $rowToAddTo = $tableIdToResultRowMapping[$period][$tableId];
                if (!$rowToAddTo->getIdSubDataTable()) {
                    $newTable = new DataTable();
                    if (!empty($columnsAggregationOperation)) {
                        $newTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
                    }
                    $rowToAddTo->setSubtable($newTable);
                }

                $tableToAddTo = $rowToAddTo->getSubtable();
            } else {
                Common::destroy($blobTable);
                continue;
            }

            $tableToAddTo->addDataTable($blobTable);

            foreach ($blobTable->getRows() as $blobTableRow) {
                $label = $blobTableRow->getColumn('label');
                $subtableId = $blobTableRow->getIdSubDataTable();
                if (empty($subtableId)) {
                    continue;
                }

                $rowToAddTo = $tableToAddTo->getRowFromLabel($label);
                if ($rowToAddTo instanceof Row) {
                    $tableIdToResultRowMapping[$period][$subtableId] = $rowToAddTo;
                }
            }

            Common::destroy($blobTable);
        }

        return [$result, $hasRows];
    }

    protected function getPeriodsWithRootBlob(ArchiveProcessor $archiveProcessor, string $recordName): array
    {
        $result = [];
        foreach ($this->querySingleBlobRows($archiveProcessor, $recordName) as $archiveDataRow) {
            if ($archiveDataRow['name'] !== $recordName) {
                continue;
            }

            $period = $archiveDataRow['date1'] . ',' . $archiveDataRow['date2'];
            $result[$period] = true;
        }

        return $result;
    }

    protected function querySingleBlobRows(ArchiveProcessor $archiveProcessor, string $recordName): iterable
    {
        $archive = Archive::factory(
            $archiveProcessor->getParams()->getSegment(),
            $archiveProcessor->getParams()->getPeriod()->getSubperiods(),
            [$archiveProcessor->getParams()->getSite()->getId()]
        );
        if (!method_exists($archive, 'querySingleBlob')) {
            return [];
        }

        return $archive->querySingleBlob($recordName);
    }

    protected function getAllSubperiodKeys(ArchiveProcessor $archiveProcessor): array
    {
        $result = [];
        foreach ($archiveProcessor->getParams()->getPeriod()->getSubperiods() as $period) {
            $result[$period->getDateStart()->toString() . ',' . $period->getDateEnd()->toString()] = true;
        }

        return $result;
    }

    protected function getSubtableIdFromBlobName(string $recordName): ?int
    {
        $parts = explode('_', $recordName);
        $id = end($parts);

        if (!is_numeric($id)) {
            return null;
        }

        return (int) $id;
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
     * @return array<string, DataTable|int|float|string> Record values indexed by their record name, eg, `['MyPlugin_MyRecord' => new DataTable()]`
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
            $maxRowsInTable ?? $this->maxRowsInTable,
            $maxRowsInSubtable ?? $this->maxRowsInSubtable,
            $columnToSortByBeforeTruncation ?? $this->columnToSortByBeforeTruncation
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
