<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db;

use Piwik\Db\ClickhouseLogTableSync;
use ReflectionClass;

/**
 * Guards the two invariants the ClickHouse copies' schema depends on. Both fail
 * silently at runtime - with wrong report numbers rather than an error - so they are
 * asserted here instead.
 *
 * @group Core
 * @group ClickHouse
 */
class ClickhouseLogTableSyncTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Columns Matomo rewrites after the row is first inserted. A ReplacingMergeTree
     * deduplicates on its whole sorting key, and merges only collapse versions within a
     * partition, so using one of these as a sorting or partition key would leave both
     * versions of an updated row in place: the visit would be counted twice.
     */
    private const REWRITTEN_AFTER_INSERT = [
        // Tracker\Visit::handleExistingVisit() - every action of the visit.
        'log_visit' => ['visit_last_action_time', 'visit_total_actions', 'visit_total_time'],
        // Tracker\GoalManager::recordEcommerceGoal() rewrites the abandoned-cart row.
        'log_conversion' => ['server_time'],
        'log_conversion_item' => ['server_time'],
    ];

    public function testEveryLogTableHasASortingKey(): void
    {
        $sortingKeys = self::getSortingKeys();

        foreach (array_keys(ClickhouseLogTableSync::LOG_TABLES) as $table) {
            $this->assertArrayHasKey(
                $table,
                $sortingKeys,
                $table . ' is synced to ClickHouse but has no sorting key'
            );
        }
    }

    /**
     * The sorting key doubles as the deduplication key, so it has to identify a row at
     * least as precisely as MySQL's primary key does. Adding columns in front is what
     * gives the sparse index something to prune on; dropping one would merge distinct
     * rows into each other.
     */
    public function testEverySortingKeyContainsTheWholePrimaryKey(): void
    {
        $sortingKeys = self::getSortingKeys();

        foreach (ClickhouseLogTableSync::LOG_TABLES as $table => $primaryKeyColumns) {
            $sortedOn = array_keys($sortingKeys[$table]);

            foreach ($primaryKeyColumns as $column) {
                $this->assertContains(
                    $column,
                    $sortedOn,
                    sprintf(
                        '%s is sorted by (%s), which does not contain the primary key column %s - '
                        . 'ReplacingMergeTree would deduplicate rows that are not duplicates',
                        $table,
                        implode(', ', $sortedOn),
                        $column
                    )
                );
            }
        }
    }

    public function testNoSortingOrPartitionKeyUsesAColumnRewrittenAfterInsert(): void
    {
        $sortingKeys = self::getSortingKeys();
        $partitionColumns = self::getConstant('PARTITION_DATE_COLUMNS');

        foreach (self::REWRITTEN_AFTER_INSERT as $table => $mutableColumns) {
            foreach ($mutableColumns as $column) {
                $this->assertArrayNotHasKey(
                    $column,
                    $sortingKeys[$table],
                    sprintf('%s.%s is rewritten after insert and cannot be sorted on', $table, $column)
                );
                $this->assertNotSame(
                    $column,
                    $partitionColumns[$table] ?? null,
                    sprintf('%s.%s is rewritten after insert and cannot be partitioned on', $table, $column)
                );
            }
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function getSortingKeys(): array
    {
        return self::getConstant('SORTING_KEYS');
    }

    /**
     * @return array<string, mixed>
     */
    private static function getConstant(string $name): array
    {
        return (new ReflectionClass(ClickhouseLogTableSync::class))->getConstant($name);
    }
}
