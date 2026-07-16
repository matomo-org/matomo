<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * Internal helper for aggregating blob rows into a DataTable.
 *
 * @internal
 */
final class BlobTableAggregator
{
    /**
     * @param iterable<array{name: string, date1: string, date2: string, value: string}> $archiveDataRows
     * @param callable(DataTable):void $renameColumnsCallback
     * @param callable(array{name: string, date1: string, date2: string, value: string}):bool|null $shouldIncludeRow
     * @param callable(string, int):void|null $onMissingParentTable
     * @return array{0: DataTable, 1: bool}
     */
    public static function aggregateBlobRows(
        iterable $archiveDataRows,
        string $recordName,
        ?array $columnsAggregationOperation,
        callable $renameColumnsCallback,
        ?callable $shouldIncludeRow = null,
        ?callable $onMissingParentTable = null
    ): array {
        // maps period & subtable ID in database to the Row instance in $result that subtable should be added to
        // [$row['date1'].','.$row['date2']][$tableId] = $row in $result
        $tableIdToResultRowMapping = [];
        $result = new DataTable();
        $hasRows = false;

        if (!empty($columnsAggregationOperation)) {
            $result->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
        }

        foreach ($archiveDataRows as $archiveDataRow) {
            if ($shouldIncludeRow !== null && !$shouldIncludeRow($archiveDataRow)) {
                continue;
            }

            $hasRows = true;
            $period = $archiveDataRow['date1'] . ',' . $archiveDataRow['date2'];
            $tableId = $archiveDataRow['name'] === $recordName
                ? null
                : self::parseSubtableIdFromBlobName($archiveDataRow['name']);

            $blobTable = DataTable::fromSerializedArray($archiveDataRow['value']);
            $blobTable->filter(function (DataTable $table) use ($renameColumnsCallback) {
                $renameColumnsCallback($table);
            });

            if ($tableId === null) {
                $tableToAddTo = $result;
            } elseif (empty($tableIdToResultRowMapping[$period][$tableId])) {
                if ($onMissingParentTable !== null) {
                    $onMissingParentTable($period, $tableId);
                }
                Common::destroy($blobTable);
                continue;
            } else {
                $rowToAddTo = $tableIdToResultRowMapping[$period][$tableId];
                if (!$rowToAddTo->getIdSubDataTable()) {
                    $newTable = new DataTable();
                    if (!empty($columnsAggregationOperation)) {
                        $newTable->setMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME, $columnsAggregationOperation);
                    }
                    $rowToAddTo->setSubtable($newTable);
                }

                $tableToAddTo = $rowToAddTo->getSubtable();
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

    public static function parseSubtableIdFromBlobName(string $recordName): ?int
    {
        $parts = explode('_', $recordName);
        $id = end($parts);

        if (!is_numeric($id)) {
            return null;
        }

        return (int) $id;
    }
}
