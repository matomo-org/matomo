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
use Piwik\Updater\Migration\Db\Statements;

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

    /**
     * @var mixed
     */
    private $originalAdapter;

    public function setUp(): void
    {
        parent::setUp();
        $this->requireSqlite();

        $this->originalPrefix = Config::getInstance()->database['tables_prefix'];
        $this->originalAdapter = Config::getInstance()->database['adapter'];
        Config::getInstance()->database['tables_prefix'] = 'matomo_';
        Config::getInstance()->database['adapter'] = 'PDO\SQLITE';

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
            Config::getInstance()->database['adapter'] = $this->originalAdapter;
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

        $this->schema->createTable('p', 'a VARCHAR(200) NOT NULL, PRIMARY KEY (a(64))');
        $this->assertTrue($this->schema->tableHasIndex('matomo_p', 'PRIMARY'));
        $this->schema->createTable('n', 'a INT');
        $this->assertFalse($this->schema->tableHasIndex('matomo_n', 'PRIMARY'));
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

    public function testAdapterFactoriesCreateTheSqliteAdapters(): void
    {
        foreach (['PDO\SQLITE', 'PDO_SQLITE', 'pdo sqlite'] as $adapterName) {
            $dbInfos = $this->config;
            $adapter = Db\Adapter::factory($adapterName, $dbInfos);
            $this->assertInstanceOf(SqliteAdapter::class, $adapter);
            $this->assertSame('1', (string) $adapter->fetchOne('SELECT 1'));
            $adapter->closeConnection();

            $tracker = \Piwik\Tracker\Db::factory($this->config + ['adapter' => $adapterName]);
            $this->assertInstanceOf(SqliteTrackerAdapter::class, $tracker);
            $tracker->connect();
            $this->assertSame('1', (string) $tracker->fetchOne('SELECT 1'));
            $tracker->disconnect();
        }
    }

    public function testCollationsAreReadWhateverTheDefaultsContain(): void
    {
        $this->schema->createTable('t', "id INT NOT NULL, d VARCHAR(10) NOT NULL DEFAULT 'a,b', PRIMARY KEY (id)");

        $this->execute($this->schema->getAlterTableSql('matomo_t', ['ADD UNIQUE KEY ud (d(3))']));

        Db::query("INSERT INTO matomo_t VALUES (1, 'abc1')");
        $this->expectExceptionMessage('UNIQUE constraint failed');
        Db::query("INSERT INTO matomo_t VALUES (2, 'ABC2')");
    }

    public function testRemovingAnExplicitDefaultIsRejected(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, n INT NOT NULL DEFAULT 0, PRIMARY KEY (id)');

        $this->expectException(\InvalidArgumentException::class);
        $this->schema->getAlterTableSql('matomo_t', ['MODIFY COLUMN n INT NOT NULL']);
    }

    /**
     * @dataProvider getSoleIntegerPrimaryKeys
     */
    public function testSoleIntegerPrimaryKeyIsNotAssignedBySqlite(string $definition): void
    {
        $this->schema->createTable('t', $definition);

        // as MySQL without strict mode, a missing key is the implicit default 0, not a new rowid
        Db::query('INSERT INTO matomo_t (n) VALUES (1)');
        $this->assertSame([['id' => 0, 'n' => 1]], array_map(function ($row) {
            return array_map('intval', $row);
        }, Db::fetchAll('SELECT id, n FROM matomo_t')));

        $this->expectExceptionMessage('UNIQUE constraint failed');
        Db::query('INSERT INTO matomo_t (n) VALUES (2)');
    }

    public function getSoleIntegerPrimaryKeys(): iterable
    {
        yield 'table primary key' => ['id INT NOT NULL, n INT, PRIMARY KEY (id)'];
        yield 'column primary key' => ['id BIGINT PRIMARY KEY, n INT'];
    }

    public function testIndexNamesOfDifferentTablesDontCollide(): void
    {
        $this->schema->createTable('a__b', 'c INT, KEY c (c)');
        $this->schema->createTable('a', 'b__c INT, KEY b__c (b__c)');

        $this->assertTrue($this->schema->tableHasIndex('matomo_a__b', 'c'));
        $this->assertTrue($this->schema->tableHasIndex('matomo_a', 'b__c'));
    }

    public function testFailedAlterLeavesTheTableUnchanged(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, label VARCHAR(10) NULL, PRIMARY KEY (id), KEY label (label)');
        $statements = new Statements($this->schema->getAlterTableSql('matomo_t', [
            'DROP INDEX label',
            'ADD COLUMN x INT NULL',
            'ADD INDEX bad (missing)',
        ]), []);

        try {
            $statements->exec();
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertStringContainsString('no such column', $e->getMessage());
        }

        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'label'));
        $this->assertSame(['id', 'label'], array_keys($this->schema->getTableColumns('matomo_t')));
    }

    public function testFailedAlterIsAtomicWhateverTheConfigurationFileSays(): void
    {
        // the connection is SQLite, e.g. selected by the Db.getDatabaseConfig event
        Config::getInstance()->database['adapter'] = 'PDO\MYSQL';
        $this->testFailedAlterLeavesTheTableUnchanged();
    }

    public function testNamedLocksFollowTheConnectionAndAreReleasedWhenItCloses(): void
    {
        // the configuration names MySQL, the connection is SQLite
        $originalSchema = Config::getInstance()->database['schema'] ?? null;
        Config::getInstance()->database['adapter'] = 'PDO\MYSQL';
        Config::getInstance()->database['schema'] = 'Mysql';
        \Piwik\Db\Schema::getInstance()->unsetSchema();

        $first = Db::get();
        $second = new SqliteAdapter($this->config);
        try {
            $this->assertTrue(Db::getDbLock('archive.lock', 1));
            $this->assertFalse(\Piwik\Db\Schema::getInstance()->isNamedLockFree('archive.lock'));

            Db::setDatabaseObject($second);
            $this->assertFalse(Db::getDbLock('archive.lock', 1));
            $this->assertFalse(Db::releaseDbLock('archive.lock'));

            $first->closeConnection();
            $this->assertTrue(\Piwik\Db\Schema::getInstance()->isNamedLockFree('archive.lock'));
            $this->assertTrue(Db::getDbLock('archive.lock', 1));
            $this->assertTrue(Db::releaseDbLock('archive.lock'));
        } finally {
            Db::setDatabaseObject($first);
            $second->closeConnection();
            Config::getInstance()->database['schema'] = $originalSchema;
            \Piwik\Db\Schema::getInstance()->unsetSchema();
        }
    }

    public function testTheSqlFollowsTheConnectionWhateverTheConfigurationSays(): void
    {
        $originalSchema = Config::getInstance()->database['schema'] ?? null;
        $sqlite = Db::get();
        $facade = \Piwik\Db\Schema::getInstance();
        try {
            Config::getInstance()->database['schema'] = 'Mysql';
            $facade->unsetSchema();
            $this->assertSame("CAST(strftime('%H', x) AS INTEGER)", $facade->getHourSql('x'));
            $this->assertSame('INSERT OR IGNORE', $facade->getInsertIgnoreSql());

            // the configuration names SQLite, the connection is another engine's
            Config::getInstance()->database['schema'] = 'Sqlite';
            $facade->unsetSchema();
            Db::setDatabaseObject(new \stdClass());
            $this->assertSame('HOUR(x)', $facade->getHourSql('x'));
            $this->assertSame('INSERT IGNORE', $facade->getInsertIgnoreSql());
            $this->assertSame('x - INTERVAL 1 DAY', $facade->getDateTimeIntervalSql('x', '-', '1', 'DAY'));
        } finally {
            Db::setDatabaseObject($sqlite);
            Config::getInstance()->database['schema'] = $originalSchema;
            $facade->unsetSchema();
        }
    }

    public function testIgnoredErrorsSkipOnlyTheirStatement(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, label VARCHAR(10) NULL, PRIMARY KEY (id)');
        $statements = new Statements($this->schema->getAlterTableSql('matomo_t', [
            'ADD COLUMN label VARCHAR(10) NULL',
            'ADD COLUMN x INT NULL',
        ]), [Statements::ERROR_CODE_DUPLICATE_COLUMN]);

        $statements->exec();

        $this->assertSame(['id', 'label', 'x'], array_keys($this->schema->getTableColumns('matomo_t')));
    }

    public function testUniqueBinaryPrefixComparesBytes(): void
    {
        $this->schema->createTable('t', 'b VARBINARY(100) NOT NULL, UNIQUE KEY ub (b(4))');

        Db::query('INSERT INTO matomo_t VALUES (?)', ["ab\0c1"]);
        Db::query('INSERT INTO matomo_t VALUES (?)', ["ab\0d1"]);
        $this->expectExceptionMessage('UNIQUE constraint failed');
        Db::query('INSERT INTO matomo_t VALUES (?)', ["ab\0c2"]);
    }

    public function testAlterFindsColumnTypesWhateverTheCaseOfTheTableName(): void
    {
        $this->schema->createTable('m_bInAlt', 'b VARBINARY(100) NOT NULL');
        $this->execute($this->schema->getAlterTableSql('matomo_m_binalt', ['ADD UNIQUE KEY ub (b(3))']));

        Db::query('INSERT INTO matomo_m_bInAlt VALUES (?)', ["\0AB"]);
        Db::query('INSERT INTO matomo_m_bInAlt VALUES (?)', ["\0CD"]);
        $this->assertSame(2, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_m_bInAlt'));
    }

    public function testEnumColumnsAreCheckedAndDefaultToTheirFirstValue(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');
        Db::query('INSERT INTO matomo_t (id) VALUES (1)');
        $this->execute($this->schema->getAlterTableSql('matomo_t', ["ADD COLUMN s ENUM('ready','done') NOT NULL"]));

        $this->assertSame('ready', Db::fetchOne('SELECT s FROM matomo_t WHERE id = 1'));
        // the same definition is a no-op, other values are a change
        $this->assertSame([], $this->schema->getAlterTableSql('matomo_t', ["MODIFY COLUMN s ENUM('ready','done') NOT NULL"]));
        try {
            $this->schema->getAlterTableSql('matomo_t', ["MODIFY COLUMN s ENUM('new','old') NOT NULL"]);
            $this->fail('no exception');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('unsupported', $e->getMessage());
        }

        $this->expectExceptionMessage('CHECK constraint failed');
        Db::query("INSERT INTO matomo_t VALUES (2, 'other')");
    }

    public function testEnumValuesAreReadAsMysqlLiterals(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');
        // keywords inside a value are not the column's attributes
        $this->execute($this->schema->getAlterTableSql('matomo_t', [
            "ADD COLUMN s ENUM('NOT NULL','wait') NULL",
            "ADD COLUMN e ENUM('x\\\\y','it''s') NULL",
            "ADD COLUMN d VARCHAR(10) NOT NULL DEFAULT 'a\\'b\\\\'",
        ]));

        Db::query('INSERT INTO matomo_t (id, s, e) VALUES (1, ?, ?)', ['NOT NULL', 'x\\y']);
        Db::query('INSERT INTO matomo_t (id, e) VALUES (2, ?)', ["it's"]);
        $this->assertSame([[null, "it's", "a'b\\"]], array_map('array_values', Db::fetchAll('SELECT s, e, d FROM matomo_t WHERE id = 2')));
        $this->assertSame([], $this->schema->getAlterTableSql('matomo_t', ["MODIFY COLUMN e ENUM('x\\\\y','it''s') NULL"]));

        foreach ([['s', 'other'], ['e', 'x\\\\y']] as [$column, $value]) {
            try {
                Db::query('INSERT INTO matomo_t (id, ' . $column . ') VALUES (3, ?)', [$value]);
                $this->fail('Accepted ' . $value);
            } catch (\Exception $e) {
                $this->assertStringContainsString('CHECK constraint failed', $e->getMessage());
            }
        }
    }

    public function testBackslashEscapedQuotesAndDoubleQuotedStrings(): void
    {
        $this->schema->createTable('t', "id INT NOT NULL, e ENUM('it\\'s','wait') NULL, d VARCHAR(10) NOT NULL DEFAULT 'a\\'b', q VARCHAR(10) NOT NULL DEFAULT \"\", PRIMARY KEY (id)");
        Db::query('INSERT INTO matomo_t (id, e) VALUES (1, ?)', ["it's"]);

        $this->assertSame([["it's", "a'b", '']], array_map('array_values', Db::fetchAll('SELECT e, d, q FROM matomo_t')));
        $this->assertSame([], $this->schema->getAlterTableSql('matomo_t', ["MODIFY COLUMN e ENUM('it\\'s','wait') NULL"]));
    }

    /**
     * @dataProvider getUnchangedIntegerKeyChanges
     */
    public function testUnchangedIntegerPrimaryKeyIsANoOp(string $specification): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');

        $this->assertSame([], $this->schema->getAlterTableSql('matomo_t', [$specification]));
    }

    public function getUnchangedIntegerKeyChanges(): iterable
    {
        yield 'modify' => ['MODIFY COLUMN id INT NOT NULL'];
        yield 'change' => ['CHANGE COLUMN id id INT NOT NULL'];
    }

    public function testSoleIntegerPrimaryKeyRejectsText(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');
        Db::query('INSERT INTO matomo_t VALUES (?)', ['5']);
        $this->assertSame(5, (int) Db::fetchOne('SELECT id FROM matomo_t'));

        $this->expectExceptionMessage('CHECK constraint failed');
        Db::query('INSERT INTO matomo_t VALUES (?)', ['not-an-id']);
    }

    public function testPrimaryKeyOnPrefixesIsInTheMetadata(): void
    {
        $this->schema->createTable('p', 'a VARCHAR(200) NOT NULL, b VARCHAR(200) NOT NULL, PRIMARY KEY (a(3), b(3))');

        $columns = Db::get()->describeTable('matomo_p');
        $this->assertSame([1, 2], [$columns['a']['PRIMARY_POSITION'], $columns['b']['PRIMARY_POSITION']]);
        Db::query("INSERT INTO matomo_p VALUES ('abc1', 'xyz1')");
        $this->expectExceptionMessage('UNIQUE constraint failed');
        Db::query("INSERT INTO matomo_p VALUES ('ABC2', 'XYZ2')");
    }

    public function testIdentifiersAreQuotedWithBackticks(): void
    {
        $this->assertSame('`a`.`b`', Db::get()->quoteIdentifier('a.b'));
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
