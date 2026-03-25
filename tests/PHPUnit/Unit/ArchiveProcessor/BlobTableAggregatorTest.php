<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Unit\ArchiveProcessor;

use PHPUnit\Framework\TestCase;
use Piwik\ArchiveProcessor\BlobTableAggregator;
use Piwik\DataTable;
use Piwik\DataTable\Row;

class BlobTableAggregatorTest extends TestCase
{
    public function testParseSubtableIdFromBlobNameReturnsIntegerForNumericSuffix(): void
    {
        $this->assertSame(123, BlobTableAggregator::parseSubtableIdFromBlobName('Actions_actions_123'));
        $this->assertNull(BlobTableAggregator::parseSubtableIdFromBlobName('Actions_actions_subtable'));
    }

    public function testAggregateBlobRowsRespectsRowFilter(): void
    {
        $rows = array_merge(
            $this->makeArchiveDataRowsForPeriod('Test_record', '2020-01-01', $this->makeSimpleTable('page-a', 3)),
            $this->makeArchiveDataRowsForPeriod('Test_record', '2020-01-02', $this->makeSimpleTable('page-b', 7))
        );

        [$result, $hasRows] = BlobTableAggregator::aggregateBlobRows(
            $rows,
            'Test_record',
            null,
            function (DataTable $table): void {
            },
            function (array $archiveDataRow): bool {
                return $archiveDataRow['date1'] === '2020-01-01';
            }
        );

        $this->assertTrue($hasRows);
        $this->assertSame(1, $result->getRowsCount());
        $row = $result->getRowFromLabel('page-a');
        $this->assertInstanceOf(Row::class, $row);
        $this->assertSame(3, $row->getColumn('nb_visits'));
    }

    public function testAggregateBlobRowsCallsMissingParentCallbackForOrphanedSubtable(): void
    {
        $rows = $this->makeArchiveDataRowsForPeriod(
            'Test_record',
            '2020-01-01',
            $this->makeTableWithSubtable('parent', 'child', 5)
        );
        $rows[] = [
            'name' => 'Test_record_999',
            'date1' => '2020-01-02',
            'date2' => '2020-01-02',
            'value' => $rows[1]['value'],
        ];

        $missingParents = [];
        [$result, $hasRows] = BlobTableAggregator::aggregateBlobRows(
            $rows,
            'Test_record',
            null,
            function (DataTable $table): void {
            },
            null,
            function (string $period, int $tableId) use (&$missingParents): void {
                $missingParents[] = [$period, $tableId];
            }
        );

        $this->assertTrue($hasRows);
        $this->assertContains(['2020-01-02,2020-01-02', 999], $missingParents);
        $this->assertGreaterThan(0, $result->getRowsCount());
        $rows = $result->getRows();
        $row = reset($rows);
        $this->assertInstanceOf(Row::class, $row);
    }

    private function makeSimpleTable(string $label, int $nbVisits): DataTable
    {
        $table = new DataTable();
        $table->addRowFromSimpleArray(['label' => $label, 'nb_visits' => $nbVisits]);
        return $table;
    }

    private function makeTableWithSubtable(string $parentLabel, string $childLabel, int $nbVisits): DataTable
    {
        $table = new DataTable();
        $row = new Row([Row::COLUMNS => ['label' => $parentLabel, 'nb_visits' => $nbVisits]]);

        $subtable = new DataTable();
        $subtable->addRowFromSimpleArray(['label' => $childLabel, 'nb_visits' => $nbVisits]);
        $row->setSubtable($subtable);

        $table->addRow($row);
        return $table;
    }

    /**
     * @return array<int, array{name: string, date1: string, date2: string, value: string}>
     */
    private function makeArchiveDataRowsForPeriod(string $recordName, string $date, DataTable $table): array
    {
        $rows = [];
        $serialized = $table->getSerialized();
        $rootKey = array_key_first($serialized);

        foreach ($serialized as $key => $value) {
            $name = $key === $rootKey ? $recordName : $recordName . '_' . $key;
            $rows[] = [
                'name' => $name,
                'date1' => $date,
                'date2' => $date,
                'value' => $value,
            ];
        }

        return $rows;
    }
}
