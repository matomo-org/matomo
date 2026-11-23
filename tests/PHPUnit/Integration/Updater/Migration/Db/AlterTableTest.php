<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration\Db;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration\Db\Factory;

/**
 * Column and primary key changes give the same rows and errors on every engine.
 *
 * @group Core
 */
class AlterTableTest extends IntegrationTestCase
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var string
     */
    private $table;

    public function setUp(): void
    {
        parent::setUp();
        $this->factory = new Factory();
        $this->table = Common::prefixTable('altertable');
        Db::exec("DROP TABLE IF EXISTS `{$this->table}`");
        DbHelper::createTable('altertable', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, label VARCHAR(20) NULL, amount INT NULL, PRIMARY KEY (id), KEY label (label)');
        Db::query("INSERT INTO `{$this->table}` (label, amount) VALUES ('a', 1), (NULL, NULL), ('c', 3)");
        Db::query("DELETE FROM `{$this->table}` WHERE id = 3");
    }

    public function tearDown(): void
    {
        Db::exec("DROP TABLE IF EXISTS `{$this->table}`");
        parent::tearDown();
    }

    public function testChangingAColumnKeepsTheRowsTheIndexAndTheCounter(): void
    {
        $this->factory->changeColumnTypes('altertable', [
            'label' => "VARCHAR(50) NOT NULL DEFAULT ''",
            'amount' => 'BIGINT NOT NULL',
        ])->exec();
        $this->factory->changeColumn('altertable', 'amount', 'total', 'BIGINT NOT NULL DEFAULT 0')->exec();

        // a NULL becomes the implicit default (Matomo's sql_mode isn't strict)
        $this->assertEquals([
            ['id' => 1, 'label' => 'a', 'total' => 1],
            ['id' => 2, 'label' => '', 'total' => 0],
        ], Db::fetchAll("SELECT id, label, total FROM `{$this->table}` ORDER BY id"));
        $this->assertTrue(DbHelper::tableHasIndex($this->table, 'label'));
        $this->assertSame(['id', 'label', 'total'], array_keys(DbHelper::getTableColumns($this->table)));

        Db::query("INSERT INTO `{$this->table}` (label) VALUES ('d')");
        $this->assertEquals(4, Db::fetchOne("SELECT id FROM `{$this->table}` WHERE label = 'd'"));
    }

    public function testReplacingThePrimaryKey(): void
    {
        $this->factory->changeColumnType('altertable', 'id', 'INT UNSIGNED NOT NULL')->exec();
        $this->factory->dropPrimaryKey('altertable')->exec();
        $this->factory->addPrimaryKey('altertable', ['id', 'label'])->exec();

        Db::query("INSERT INTO `{$this->table}` (id, label) VALUES (1, 'b')");
        $this->expectExceptionMessageMatches('/Duplicate entry|UNIQUE constraint failed/');
        Db::query("INSERT INTO `{$this->table}` (id, label) VALUES (1, 'a')");
    }

    /**
     * @dataProvider getExpectedErrors
     */
    public function testErrorsAMigrationIgnoresHaveMysqlsNumbers(string $method, array $arguments): void
    {
        $migration = $this->factory->$method(...$arguments);
        if (Fixture::isSqlite() && is_array($arguments[1]) && count($arguments[1]) > 1) {
            // as separate statements would: the error skips its own specification only
            $migration->exec();
            // label, NULL before, is now NOT NULL
            $this->assertSame('NO', DbHelper::getTableColumns($this->table)['label']['Null']);
            return;
        }
        try {
            $migration->exec();
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue($migration->shouldIgnoreError($e), $e->getMessage());
        }

        $this->assertSame(['id', 'label', 'amount'], array_keys(DbHelper::getTableColumns($this->table)));
    }

    public function getExpectedErrors(): iterable
    {
        yield 'unknown column' => ['changeColumnType', ['altertable', 'missing', 'INT NULL']];
        yield 'renamed onto an existing column' => ['changeColumn', ['altertable', 'amount', 'label', 'INT NULL']];
        yield 'several types, one column unknown' => ['changeColumnTypes', ['altertable', ['label' => 'VARCHAR(30) NOT NULL', 'missing' => 'INT NULL']]];
        yield 'second primary key' => ['addPrimaryKey', ['altertable', ['label']]];
    }
}
