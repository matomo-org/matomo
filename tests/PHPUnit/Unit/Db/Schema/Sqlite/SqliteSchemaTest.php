<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Schema\Sqlite;

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Db;
use Piwik\Db\Adapter\Pdo\Sqlite as SqliteAdapter;
use Piwik\Db\Schema\Sqlite;
use Piwik\Db\Sqlite\Connection;
use Piwik\Tests\Unit\Db\Sqlite\RequiresSqlite;
use Piwik\Tracker\Db\Pdo\Sqlite as SqliteTrackerAdapter;

/**
 * The SQLite schema and both adapters on a database file.
 */
class SqliteSchemaTest extends TestCase
{
    use RequiresSqlite;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Sqlite
     */
    private $schema;

    /**
     * @var mixed
     */
    private $originalPrefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->requireSqlite();

        $this->originalPrefix = Config::getInstance()->database['tables_prefix'];
        Config::getInstance()->database['tables_prefix'] = 'matomo_';

        $this->config = ['dbname' => sys_get_temp_dir() . '/matomo-sqlite-schema-test-' . bin2hex(random_bytes(4)) . '.db'];
        Connection::createDatabaseFile($this->config);
        Db::setDatabaseObject(new SqliteAdapter($this->config));
        $this->schema = new Sqlite();
    }

    public function tearDown(): void
    {
        if ($this->config !== null) {
            Db::get()->closeConnection();
            Db::setDatabaseObject(null);
            Connection::deleteDatabaseFile($this->config);
            Config::getInstance()->database['tables_prefix'] = $this->originalPrefix;
        }
        parent::tearDown();
    }

    public function testCreateTableLeavesAnExistingTableAsItIs(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');
        // a later version of the definition, with a column and an index the table doesn't have
        $this->schema->createTable('t', 'id INT NOT NULL, label VARCHAR(10) NOT NULL, PRIMARY KEY (id), KEY label (label)');

        $this->assertSame(['id'], array_keys($this->schema->getTableColumns('matomo_t')));
        $this->assertFalse($this->schema->tableHasIndex('matomo_t', 'label'));
    }

    public function testAlterAddsColumnsBeforeIndexesOnThemWithTheirCollation(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');

        $this->execute($this->schema->getAlterTableSql('matomo_t', [
            'ADD UNIQUE KEY ulabel (label(3))',
            'ADD COLUMN label VARCHAR(30) NOT NULL',
        ]));

        Db::query("INSERT INTO matomo_t VALUES (1, 'abc1')");
        $this->expectExceptionMessage('UNIQUE constraint failed');
        Db::query("INSERT INTO matomo_t VALUES (2, 'ABC2')");
    }

    public function testAlterDropsIndexesBeforeTheirColumns(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, label VARCHAR(10) NULL, PRIMARY KEY (id), KEY label (label)');

        $this->execute($this->schema->getAlterTableSql('matomo_t', ['DROP COLUMN label', 'DROP INDEX label']));

        $this->assertSame(['id'], array_keys($this->schema->getTableColumns('matomo_t')));
    }

    public function testModifyToTheSameDefinitionIsANoOp(): void
    {
        $this->schema->createTable('t', "id INT NOT NULL, n INT NOT NULL, s VARCHAR(10) NOT NULL DEFAULT 'Open', PRIMARY KEY (id)");
        $this->execute($this->schema->getAlterTableSql('matomo_t', ['ADD COLUMN m INT NOT NULL']));

        $this->assertSame([], $this->schema->getAlterTableSql('matomo_t', [
            'MODIFY COLUMN n INT NOT NULL',
            "MODIFY COLUMN s VARCHAR(10) NOT NULL DEFAULT 'Open'",
            // added with the default SQLite needs, as declared
            'MODIFY COLUMN m INT NOT NULL',
        ]));
    }

    /**
     * @dataProvider getRealColumnChanges
     */
    public function testModifyToAnotherDefinitionIsRejected(string $specification): void
    {
        $this->schema->createTable('t', "id INT NOT NULL, s VARCHAR(10) NOT NULL DEFAULT 'Open', PRIMARY KEY (id)");

        $this->expectException(\InvalidArgumentException::class);
        $this->schema->getAlterTableSql('matomo_t', [$specification]);
    }

    public function getRealColumnChanges(): iterable
    {
        yield 'default differs in case' => ["MODIFY COLUMN s VARCHAR(10) NOT NULL DEFAULT 'OPEN'"];
        yield 'nullable' => ["MODIFY COLUMN s VARCHAR(10) NULL DEFAULT 'Open'"];
        yield 'type' => ["MODIFY COLUMN s INT NOT NULL DEFAULT 0"];
    }

    public function testTableHasIndex(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, n INT, PRIMARY KEY (id), KEY Index_N (n)');

        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'PRIMARY'));
        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'email'));
        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'index_n'));
        $this->assertFalse($this->schema->tableHasIndex('matomo_t', 'n'));
    }

    public function testGetTablesLike(): void
    {
        $this->schema->createTableFromTemplate('archive_numeric', 'matomo_archive_numeric_2026_01');
        $this->schema->createTableFromTemplate('archive_numeric', 'matomo_archive_numeric_2026_02');
        $this->schema->createTable('archive_numericx', 'id INT');

        $this->assertSame(
            ['matomo_archive_numeric_2026_01', 'matomo_archive_numeric_2026_02'],
            $this->schema->getTablesLike('matomo\_archive\_numeric\_%')
        );
        $this->assertSame([], $this->schema->getTablesLike("it's"));
    }

    public function testTrackerTransactionsAndTheirRollbackAreSeenByTheOtherAdapter(): void
    {
        Db::query('CREATE TABLE t (id INTEGER PRIMARY KEY, n INTEGER NOT NULL)');
        $tracker = new SqliteTrackerAdapter($this->config);
        $tracker->connect();

        $xid = $tracker->beginTransaction();
        $tracker->query('INSERT INTO t VALUES (?, ?)', [1, 10]);
        $tracker->rollBack($xid);

        // the transaction is over, whatever PDO knows about it, so the next one starts
        $xid = $tracker->beginTransaction();
        $this->assertNotNull($xid);
        $tracker->query('INSERT INTO t VALUES (?, ?)', [2, 20]);
        $tracker->commit($xid);

        $this->assertSame([['id' => 2, 'n' => 20]], array_map(function ($row) {
            return array_map('intval', $row);
        }, Db::fetchAll('SELECT id, n FROM t')));

        // and the other way round, with typed parameters
        Db::query('INSERT INTO t VALUES (?, ?)', [3, 30]);
        $this->assertSame(2, (int) $tracker->fetchOne('SELECT COUNT(*) FROM t WHERE n + 0 >= ?', [20]));
        $tracker->disconnect();
    }

    private function execute(array $statements): void
    {
        foreach ($statements as $statement) {
            Db::exec($statement);
        }
    }
}
