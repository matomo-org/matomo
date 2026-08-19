<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\ClickHouse;

use PHPUnit\Framework\TestCase;
use Piwik\Db\Adapter\Clickhouse;

/**
 * Round-trip test for the analytics adapter (Piwik\Db\Adapter\Clickhouse): the same
 * class Db::getAnalytics() hands to LogAggregator and the Live model, exercised
 * against a real ClickHouse server — including the MySQL→ClickHouse dialect
 * translation and positional-bind conversion on the way in.
 *
 * @group ClickHouse
 */
class AdapterSmokeTest extends TestCase
{
    private function createAdapter(): Clickhouse
    {
        $adapter = new Clickhouse([
            'host' => getenv('CLICKHOUSE_HOST') ?: 'clickhouse',
            'port' => getenv('CLICKHOUSE_PORT') ?: '8123',
            'username' => getenv('CLICKHOUSE_USER') ?: 'matomo',
            'password' => getenv('CLICKHOUSE_PASSWORD') ?: 'matomo',
            'dbname' => getenv('CLICKHOUSE_DATABASE') ?: 'default',
        ]);
        $adapter->getConnection();

        return $adapter;
    }

    public function testAdapterRoundTripWithMysqlFlavouredSql(): void
    {
        $adapter = $this->createAdapter();
        $adapter->checkServerVersion();

        $adapter->exec('DROP TABLE IF EXISTS ci_adapter_smoke');
        $adapter->exec(
            'CREATE TABLE ci_adapter_smoke (id UInt32, label Nullable(String), num UInt32)'
            . ' ENGINE = MergeTree ORDER BY id'
        );
        $adapter->exec("INSERT INTO ci_adapter_smoke VALUES (1, 'first', 10), (2, NULL, 20), (3, 'third', 30)");

        // fetchAll with positional binds
        $rows = $adapter->fetchAll('SELECT id, label FROM ci_adapter_smoke WHERE id >= ? ORDER BY id', [2]);
        self::assertCount(2, $rows);
        self::assertEquals(2, $rows[0]['id']);
        self::assertNull($rows[0]['label']);

        // MySQL-flavoured SQL goes through the dialect translator: IFNULL and a LIKE
        // against a numeric column are both MySQL-isms ClickHouse would reject verbatim.
        $rows = $adapter->fetchAll(
            "SELECT IFNULL(label, 'unknown') AS label FROM ci_adapter_smoke WHERE num LIKE ? ORDER BY id",
            ['2%']
        );
        self::assertCount(1, $rows);
        self::assertSame('unknown', $rows[0]['label']);

        // query() must return a statement usable in while ($row = $query->fetch()) loops
        $statement = $adapter->query('SELECT id FROM ci_adapter_smoke ORDER BY id');
        $ids = [];
        while ($row = $statement->fetch()) {
            $ids[] = (int) $row['id'];
        }
        self::assertSame([1, 2, 3], $ids);
        self::assertSame(3, $statement->rowCount());

        // scalar helpers
        self::assertEquals(3, $adapter->fetchOne('SELECT count(*) FROM ci_adapter_smoke'));
        $row = $adapter->fetchRow('SELECT id, num FROM ci_adapter_smoke WHERE id = ?', [3]);
        self::assertEquals(30, $row['num']);
        $assoc = $adapter->fetchAssoc('SELECT id, num FROM ci_adapter_smoke ORDER BY id');
        self::assertEquals(20, $assoc[2]['num']);

        $adapter->exec('DROP TABLE IF EXISTS ci_adapter_smoke');
    }

    public function testFailingQueryThrowsWithTranslatedSqlInMessage(): void
    {
        $adapter = $this->createAdapter();

        try {
            $adapter->fetchAll('SELECT nonexistent_column FROM nonexistent_table_xyz');
            self::fail('Expected the ClickHouse error to propagate');
        } catch (\Exception $e) {
            self::assertStringContainsString('ClickHouse query failed', $e->getMessage());
            self::assertStringContainsString('nonexistent_table_xyz', $e->getMessage());
        }
    }
}
