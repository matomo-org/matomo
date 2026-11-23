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
use Piwik\Updater\Migration\Db\AddColumn;
use Piwik\Updater\Migration\Db\DropColumns;
use Piwik\Updater\Migration\Db\AddColumns;
use Piwik\Updater\Migration\Db\AddIndex;
use Piwik\Updater\Migration\Db\AddUniqueKey;
use Piwik\Updater\Migration\Db\AlterTable;
use Piwik\Updater\Migration\Db\ChangeColumnType;
use Piwik\Updater\Migration\Db\CreateTable;
use Piwik\Updater\Migration\Db\DropColumn;
use Piwik\Updater\Migration\Db\DropIndex;
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

    public function testColumnDefaultsReadAsMysqlShowsThem(): void
    {
        $this->schema->createTable('t', "a INT NOT NULL, b INT NOT NULL DEFAULT 5, c VARCHAR(10) NOT NULL DEFAULT 'it''s', d VARCHAR(10) NULL, e VARCHAR(10) NOT NULL DEFAULT '', f DATETIME NULL DEFAULT NULL");

        $defaults = array_column($this->schema->getTableColumns('matomo_t'), 'Default', 'Field');
        // no default of its own (the implicit one stays internal) reads null, as on MySQL
        $this->assertSame(['a' => null, 'b' => '5', 'c' => "it's", 'd' => null, 'e' => '', 'f' => null], $defaults);
    }

    public function testColumnDefaultsReadTheTableAndTheTextTheDefinitionGives(): void
    {
        $this->schema->createTable('t', "a VARCHAR(20) NOT NULL DEFAULT 'use DEFAULT(x)', b INT NOT NULL, c ENUM('open','closed') NOT NULL, PRIMARY KEY (c)");
        $this->schema->createTable('u', "c ENUM('open','closed') NOT NULL PRIMARY KEY");
        // a temporary table shadowing it is the one read, as PRAGMA table_info reads it
        Db::exec('CREATE TEMPORARY TABLE matomo_t (a TEXT, b INT NOT NULL DEFAULT 4, c TEXT)');

        $this->assertSame(['a' => null, 'b' => '4', 'c' => null], array_column($this->schema->getTableColumns('matomo_t'), 'Default', 'Field'));
        Db::exec('DROP TABLE temp.matomo_t');
        $this->assertSame(['a' => 'use DEFAULT(x)', 'b' => null, 'c' => null], array_column($this->schema->getTableColumns('matomo_t'), 'Default', 'Field'));
        // an ENUM's implicit first value is no default of its own, wherever the key is declared
        $this->assertSame(['c' => null], array_column($this->schema->getTableColumns('matomo_u'), 'Default', 'Field'));
        Db::query('INSERT INTO matomo_t (a, b) VALUES (?, ?)', ['x', 1]);
        $this->assertSame('open', Db::fetchOne('SELECT c FROM matomo_t'));
    }

    public function testBinaryColumnsReadBackApartFromBlobs(): void
    {
        $this->schema->createTable('t', 'a BINARY(8) NOT NULL, b VARBINARY(16) NULL, c MEDIUMBLOB NULL, d BLOB NULL');

        $types = array_column($this->schema->getTableColumns('matomo_t'), 'Type', 'Field');
        $this->assertSame(['a' => 'blob(8)', 'b' => 'blob(16)', 'c' => 'blob', 'd' => 'blob'], $types);
        // still BLOB affinity: bytes are stored as given
        Db::query('INSERT INTO matomo_t (a, b) VALUES (?, ?)', ["\x00\x01", "\x02"]);
        $this->assertSame('text', Db::fetchOne('SELECT typeof(b) FROM matomo_t'));
    }

    public function testUnlockAllTablesEndsTheTransactionWhenCommitFails(): void
    {
        Db::exec('PRAGMA foreign_keys = ON');
        Db::exec('CREATE TABLE matomo_p (id INTEGER PRIMARY KEY)');
        Db::exec('CREATE TABLE matomo_c (p INTEGER REFERENCES matomo_p (id) DEFERRABLE INITIALLY DEFERRED)');

        Db::lockTables([], ['matomo_c']);
        Db::query('INSERT INTO matomo_c (p) VALUES (1)');
        try {
            Db::unlockAllTables();
            $this->fail('the deferred constraint should fail the commit');
        } catch (\Exception $e) {
            $this->assertStringContainsString('FOREIGN KEY constraint failed', $e->getMessage());
        }

        // rolled back, and back to autocommit
        $this->assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_c'));
        Db::exec('BEGIN');
        Db::exec('ROLLBACK');
        $this->assertSame(0, Db::unlockAllTables());
    }

    public function testUnlockAllTablesReleasesTheLockOfTheConnectionThatTookIt(): void
    {
        Db::exec('CREATE TABLE matomo_l (id INTEGER)');
        $owner = Db::get();
        Db::lockTables([], ['matomo_l']);
        Db::query('INSERT INTO matomo_l VALUES (1)');

        $other = new SqliteAdapter($this->config);
        Db::setDatabaseObject($other);
        Db::unlockAllTables();
        // the owner's transaction committed: the other connection sees the row and can write
        $other->query('INSERT INTO matomo_l VALUES (2)');
        $this->assertSame(2, (int) $other->fetchOne('SELECT COUNT(*) FROM matomo_l'));
        $other->closeConnection();
        Db::setDatabaseObject($owner);
    }

    public function testAbortTableLocksRollsBackTheWorkDoneUnderTheLock(): void
    {
        Db::exec('CREATE TABLE matomo_l (id INTEGER)');
        Db::lockTables([], ['matomo_l']);
        Db::query('INSERT INTO matomo_l VALUES (1)');

        Db::abortTableLocks();

        $this->assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_l'));
        // the lock is gone: another writer gets it
        $other = new SqliteAdapter($this->config);
        $other->query('BEGIN IMMEDIATE');
        $other->query('ROLLBACK');
        $other->closeConnection();
    }

    public function testALockedConnectionDroppedByItsUsersReleasesTheLock(): void
    {
        Db::exec('CREATE TABLE matomo_l (id INTEGER)');
        $owner = Db::get();
        $locked = new SqliteAdapter($this->config);
        Db::setDatabaseObject($locked);
        Db::lockTables([], ['matomo_l']);
        Db::setDatabaseObject($owner);
        unset($locked);

        // nothing keeps the locking connection open, so another writer gets the lock
        Db::exec('BEGIN IMMEDIATE');
        Db::exec('ROLLBACK');
        $this->assertSame(0, Db::unlockAllTables());
    }

    public function testLockPrivilegeIsGrantedWhileAnotherWriterHoldsTheLock(): void
    {
        $original = Db::$lockPrivilegeGranted;
        Db::$lockPrivilegeGranted = null;
        $other = new SqliteAdapter($this->config);
        $other->query('BEGIN IMMEDIATE');
        try {
            $this->assertTrue(Db::isLockPrivilegeGranted());
        } finally {
            $other->query('ROLLBACK');
            $other->closeConnection();
            Db::$lockPrivilegeGranted = $original;
        }
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

    /**
     * @dataProvider getTableRebuilds
     */
    public function testRebuiltTableIsDeclaredAsAFreshOne(string $before, array $specifications, string $after): void
    {
        $this->schema->createTable('t', $before);
        $this->schema->createTable('fresh', $after);

        $this->schema->alterTable('matomo_t', $specifications);

        $this->assertSame($this->getTableBody('matomo_fresh'), $this->getTableBody('matomo_t'));
    }

    public function getTableRebuilds(): iterable
    {
        yield 'type' => [
            "id INT NOT NULL, s VARCHAR(10) NOT NULL DEFAULT 'Open', n INT NULL, PRIMARY KEY (id)",
            ['MODIFY COLUMN s INT NOT NULL DEFAULT 0'],
            'id INT NOT NULL, s INT NOT NULL DEFAULT 0, n INT NULL, PRIMARY KEY (id)',
        ];
        yield 'name and type' => [
            'id INT NOT NULL, n INT NULL, PRIMARY KEY (id)',
            ['CHANGE n m VARCHAR(20) NOT NULL'],
            'id INT NOT NULL, m VARCHAR(20) NOT NULL, PRIMARY KEY (id)',
        ];
        yield 'several columns' => [
            'id INT NOT NULL, a INT NULL, b INT NULL, PRIMARY KEY (id)',
            ['CHANGE `a` `a` BIGINT NOT NULL', 'CHANGE `b` `b` VARCHAR(5) NULL'],
            'id INT NOT NULL, a BIGINT NOT NULL, b VARCHAR(5) NULL, PRIMARY KEY (id)',
        ];
        yield 'composite primary key added' => [
            'a INT NULL, b VARCHAR(10) NOT NULL',
            ['ADD PRIMARY KEY(`a`, `b`)'],
            'a INT NOT NULL, b VARCHAR(10) NOT NULL, PRIMARY KEY (a, b)',
        ];
        yield 'primary key replaced by an integer one' => [
            'a INT NOT NULL, b VARCHAR(10) NOT NULL, PRIMARY KEY (a, b)',
            ['DROP PRIMARY KEY', 'ADD PRIMARY KEY(`a`)'],
            'a INT NOT NULL, b VARCHAR(10) NOT NULL, PRIMARY KEY (a)',
        ];
        yield 'auto increment dropped' => [
            'id INT UNSIGNED NOT NULL AUTO_INCREMENT, s VARCHAR(10) NULL, PRIMARY KEY (id)',
            ['MODIFY id INT UNSIGNED NOT NULL'],
            'id INT UNSIGNED NOT NULL, s VARCHAR(10) NULL, PRIMARY KEY (id)',
        ];
        yield 'auto increment added' => [
            'id INT UNSIGNED NOT NULL, s VARCHAR(10) NULL, PRIMARY KEY (id)',
            ['MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT'],
            'id INT UNSIGNED NOT NULL AUTO_INCREMENT, s VARCHAR(10) NULL, PRIMARY KEY (id)',
        ];
    }

    public function testRebuildKeepsRowsIndexesTriggersAndTheCounter(): void
    {
        $this->schema->createTable('t', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, s VARCHAR(10) NULL, n INT NULL, PRIMARY KEY (id), KEY s (s), UNIQUE KEY n (n)');
        Db::exec('CREATE TABLE matomo_log (id INTEGER)');
        Db::exec('CREATE TRIGGER matomo_t_insert AFTER INSERT ON matomo_t BEGIN INSERT INTO matomo_log VALUES (NEW.id); END');
        Db::query("INSERT INTO matomo_t (s, n) VALUES ('a', 1), (NULL, NULL), ('c', 3)");
        Db::query('DELETE FROM matomo_t WHERE id = 3');

        $this->schema->alterTable('matomo_t', ['MODIFY COLUMN s VARCHAR(20) NOT NULL']);

        // a NULL gets the implicit default, as MySQL's ALTER stores it
        $this->assertSame([['id' => 1, 's' => 'a', 'n' => 1], ['id' => 2, 's' => '', 'n' => null]], Db::fetchAll('SELECT id, s, n FROM matomo_t ORDER BY id'));
        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 's'));
        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'n'));
        Db::query("INSERT INTO matomo_t (s, n) VALUES ('d', 4)");
        // the counter goes on after the deleted 3, and the trigger fires
        $this->assertSame(4, (int) Db::fetchOne("SELECT id FROM matomo_t WHERE s = 'd'"));
        $this->assertSame(4, (int) Db::fetchOne('SELECT MAX(id) FROM matomo_log'));
        $this->assertSame(0, (int) Db::fetchOne("SELECT COUNT(*) FROM sqlite_schema WHERE name LIKE '%rebuild%'"));
    }

    public function testRebuildIgnoresTemporaryObjectsOfTheSameName(): void
    {
        $this->schema->createTable('q', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, s VARCHAR(10) NULL, PRIMARY KEY (id)');
        Db::query("INSERT INTO matomo_q (s) VALUES ('a'), ('b'), ('c')");
        Db::query('DELETE FROM matomo_q WHERE id > 1');
        Db::exec('CREATE TEMP TABLE matomo_other (id INTEGER PRIMARY KEY AUTOINCREMENT)');
        Db::exec('INSERT INTO matomo_other DEFAULT VALUES');
        Db::exec('CREATE TEMP TABLE matomo_q__matomo_rebuild (x INTEGER)');
        try {
            $this->schema->alterTable('matomo_q', ['MODIFY s VARCHAR(20) NOT NULL']);

            $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM main.matomo_q'));
            $this->assertSame(0, (int) Db::fetchOne('SELECT COUNT(*) FROM temp.matomo_q__matomo_rebuild'));
            Db::query("INSERT INTO matomo_q (s) VALUES ('d')");
            // the deleted ids aren't issued again
            $this->assertSame(4, (int) Db::fetchOne("SELECT id FROM matomo_q WHERE s = 'd'"));
        } finally {
            Db::exec('DROP TABLE temp.matomo_q__matomo_rebuild');
            Db::exec('DROP TABLE temp.matomo_other');
        }
    }

    public function testRebuildKeepsDependentObjects(): void
    {
        Db::exec('CREATE TABLE matomo_t (id INTEGER NOT NULL, s TEXT, g INTEGER GENERATED ALWAYS AS (id * 2) STORED, PRIMARY KEY (id))');
        Db::exec('CREATE TABLE matomo_log (id INTEGER)');
        Db::exec('CREATE TEMP TABLE matomo_temp_log (id INTEGER)');
        Db::exec('CREATE VIEW matomo_v AS SELECT id FROM matomo_t');
        Db::exec('CREATE TRIGGER matomo_log_insert AFTER INSERT ON matomo_log BEGIN DELETE FROM matomo_t WHERE id = NEW.id; END');
        // a temporary trigger may write to a temporary table, which a permanent one can't name
        Db::exec('CREATE TEMP TRIGGER matomo_t_temp AFTER INSERT ON main.matomo_t BEGIN INSERT INTO matomo_temp_log VALUES (NEW.id + 100); END');
        try {
            Db::query("INSERT INTO matomo_t (id, s) VALUES (1, 'a')");

            $this->schema->alterTable('matomo_t', ['MODIFY s VARCHAR(20) NULL']);

            $this->assertSame([['id' => 1, 'g' => 2]], Db::fetchAll('SELECT id, g FROM matomo_t'));
            $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_v'));
            $this->assertSame(1, (int) Db::fetchOne("SELECT COUNT(*) FROM temp.sqlite_schema WHERE type = 'trigger' AND name = 'matomo_t_temp'"));
            $this->assertSame(0, (int) Db::fetchOne("SELECT COUNT(*) FROM main.sqlite_schema WHERE type = 'trigger' AND name = 'matomo_t_temp'"));
            Db::query("INSERT INTO matomo_t (id, s) VALUES (2, 'b')");
            $this->assertSame(102, (int) Db::fetchOne('SELECT MAX(id) FROM temp.matomo_temp_log'));
        } finally {
            Db::exec('DROP TRIGGER IF EXISTS temp.matomo_t_temp');
            Db::exec('DROP TABLE IF EXISTS temp.matomo_temp_log');
            Db::exec('DROP VIEW matomo_v');
        }
    }

    public function testChangedGeneratedColumnKeepsItsValues(): void
    {
        Db::exec('CREATE TABLE matomo_t (id INTEGER NOT NULL, g INTEGER GENERATED ALWAYS AS (id * 2) STORED, PRIMARY KEY (id))');
        Db::query('INSERT INTO matomo_t (id) VALUES (1), (2)');

        $this->schema->alterTable('matomo_t', ['MODIFY g INT NULL']);

        $this->assertEquals([['id' => 1, 'g' => 2], ['id' => 2, 'g' => 4]], Db::fetchAll('SELECT id, g FROM matomo_t ORDER BY id'));
    }

    public function testRebuildRefusesToRunWithForeignKeysOn(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, s VARCHAR(10) NULL, PRIMARY KEY (id)');
        Db::exec('CREATE TABLE matomo_child (id INTEGER REFERENCES matomo_t (id) ON DELETE CASCADE)');
        Db::query("INSERT INTO matomo_t (id, s) VALUES (1, 'a')");
        Db::exec('INSERT INTO matomo_child VALUES (1)');
        Db::exec('PRAGMA foreign_keys = ON');
        try {
            $this->schema->alterTable('matomo_t', ['MODIFY s VARCHAR(20) NOT NULL']);
            $this->fail('no exception');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('foreign_keys', $e->getMessage());
        } finally {
            Db::exec('PRAGMA foreign_keys = OFF');
        }
        $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_child'));
    }

    public function testFailedCopyLeavesTheCallersTransaction(): void
    {
        Db::exec("CREATE TABLE matomo_t (id INTEGER NOT NULL, s TEXT COLLATE BINARY, PRIMARY KEY (id), UNIQUE (s) ON CONFLICT ROLLBACK)");
        Db::query("INSERT INTO matomo_t VALUES (1, 'a'), (2, 'A')");
        Db::exec('CREATE TABLE matomo_other (id INTEGER)');
        Db::get()->beginTransaction();
        Db::exec('INSERT INTO matomo_other VALUES (1)');
        try {
            // the values collide once case is ignored
            $this->schema->alterTable('matomo_t', ['MODIFY s VARCHAR(10) NULL']);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->getConnection()->inTransaction());
        }
        Db::get()->commit();
        $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_other'));
        $this->assertSame(2, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_t'));
    }

    public function testRenamesApplyBeforeTheOtherSpecifications(): void
    {
        $this->schema->createTable('t', 'ident INT NOT NULL, b VARCHAR(10) NULL, PRIMARY KEY (ident, b)');
        // SQLite writes the renamed primary key column in double quotes
        $this->schema->alterTable('matomo_t', ['CHANGE ident id INT NOT NULL']);
        $this->schema->alterTable('matomo_t', ['CHANGE id idsite INT NOT NULL', 'ADD INDEX idsite (idsite)']);
        $this->schema->alterTable('matomo_t', ['CHANGE b B VARCHAR(10) NOT NULL']);

        $this->assertSame(['idsite', 'B'], array_keys($this->schema->getTableColumns('matomo_t')));
        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'idsite'));
        $this->assertSame('PRI', $this->schema->getTableColumns('matomo_t')['idsite']['Key']);
    }

    public function testChangesNameTheColumnsTheTableHadAsOnMysql(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, old VARCHAR(10) NULL, other VARCHAR(10) NULL, a INT NULL');
        Db::exec("INSERT INTO matomo_t (id, other, a) VALUES (1, 'o', 42)");

        // a dropped name can be taken by a rename, a renamed-away one by a new column
        $this->schema->alterTable('matomo_t', ['DROP COLUMN old', 'CHANGE other old VARCHAR(10) NULL', 'ADD INDEX old (old)']);
        $this->schema->alterTable('matomo_t', ['ADD COLUMN a INT DEFAULT 7', 'CHANGE a b INT NULL']);
        $this->assertSame(['id', 'old', 'b', 'a'], array_keys($this->schema->getTableColumns('matomo_t')));
        $this->assertEquals([['old' => 'o', 'b' => 42, 'a' => 7]], Db::fetchAll('SELECT old, b, a FROM matomo_t'));
        $this->assertTrue($this->schema->tableHasIndex('matomo_t', 'old'));

        // a change of a column added or dropped in the same statement is refused, and nothing applies
        $before = Db::fetchAll('SELECT sql FROM sqlite_schema ORDER BY name');
        foreach (
            [
                ['ADD COLUMN x INT NULL', 'MODIFY x BIGINT NOT NULL'],
                ['DROP COLUMN b', 'ADD COLUMN b INT DEFAULT 7', 'CHANGE b c INT NULL'],
                // MODIFY b names the b dropped, not the one the rename makes
                ['DROP COLUMN b', 'CHANGE a b INT NULL', 'MODIFY b BIGINT NOT NULL', 'ADD COLUMN a INT DEFAULT 7'],
                // each column is the source of one change at most
                ['MODIFY b BIGINT NULL', 'MODIFY b INT NULL', 'DROP COLUMN a'],
                ['CHANGE b c INT NULL', 'MODIFY b INT NULL'],
            ] as $specifications
        ) {
            try {
                $this->schema->alterTable('matomo_t', $specifications);
                $this->fail('no exception');
            } catch (\Exception $e) {
                $this->assertTrue(Db::get()->isErrNo($e, 1054), $e->getMessage());
            }
        }
        $this->assertSame($before, Db::fetchAll('SELECT sql FROM sqlite_schema ORDER BY name'));
        $this->assertEquals([['old' => 'o', 'b' => 42, 'a' => 7]], Db::fetchAll('SELECT old, b, a FROM matomo_t'));
    }

    public function testTwoAutoColumnsAreRefused(): void
    {
        $this->schema->createTable('t', 'a INT NOT NULL, b INT NOT NULL');
        try {
            $this->schema->alterTable('matomo_t', ['MODIFY a INT NOT NULL AUTO_INCREMENT', 'MODIFY b INT NOT NULL AUTO_INCREMENT', 'ADD PRIMARY KEY (b)']);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, 1075), $e->getMessage());
        }
        $this->assertSame('', $this->schema->getTableColumns('matomo_t')['a']['Key']);

        // an auto column left as it is still counts
        $this->schema->createTable('v', 'a INT NOT NULL AUTO_INCREMENT, b INT NOT NULL, PRIMARY KEY (a)');
        try {
            $this->schema->alterTable('matomo_v', ['MODIFY b INT NOT NULL AUTO_INCREMENT', 'DROP PRIMARY KEY', 'ADD PRIMARY KEY (b)']);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, 1075), $e->getMessage());
        }
        // moved to another column, it is one
        $this->schema->alterTable('matomo_v', ['MODIFY a INT NOT NULL', 'MODIFY b INT NOT NULL AUTO_INCREMENT', 'DROP PRIMARY KEY', 'ADD PRIMARY KEY (b)']);
        Db::exec('INSERT INTO matomo_v (a) VALUES (5)');
        $this->assertSame(1, (int) Db::fetchOne('SELECT b FROM matomo_v'));

        // an auto column kept as it is still counts
        $this->schema->createTable('u', 'a INT NOT NULL AUTO_INCREMENT, b INT NOT NULL, PRIMARY KEY (a)');
        try {
            $this->schema->alterTable('matomo_u', ['MODIFY a INT NOT NULL AUTO_INCREMENT', 'MODIFY b INT NOT NULL AUTO_INCREMENT', 'DROP PRIMARY KEY', 'ADD PRIMARY KEY (b)']);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, 1075), $e->getMessage());
        }
    }

    public function testAutoIncrementIsCheckedOnceEverySpecificationApplies(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL');
        $this->schema->alterTable('matomo_t', ['MODIFY id INT NOT NULL AUTO_INCREMENT', 'ADD PRIMARY KEY (id)']);

        Db::exec('INSERT INTO matomo_t DEFAULT VALUES');
        $this->assertSame(1, (int) Db::fetchOne('SELECT id FROM matomo_t'));
    }

    public function testRebuiltKeyColumnsAreDeclaredAsFreshOnes(): void
    {
        // dropping a sole integer key leaves an ordinary integer column
        $this->schema->createTable('a', 'id INT NOT NULL, PRIMARY KEY (id)');
        $this->schema->alterTable('matomo_a', ['DROP PRIMARY KEY']);
        $this->schema->createTable('b', 'id INT NOT NULL');
        $this->assertSame($this->getColumnDeclarations('matomo_b'), $this->getColumnDeclarations('matomo_a'));

        // a NULL default doesn't survive into a key
        $this->schema->createTable('c', 'id INT DEFAULT NULL');
        $this->schema->alterTable('matomo_c', ['ADD PRIMARY KEY (id)']);
        Db::exec('INSERT INTO matomo_c DEFAULT VALUES');
        $this->assertSame(0, (int) Db::fetchOne('SELECT id FROM matomo_c'));
    }

    public function testRebuildKeepsNativeTableAndColumnConstraints(): void
    {
        Db::exec('CREATE TABLE matomo_t (id INTEGER NOT NULL, s TEXT UNIQUE) STRICT');
        $this->schema->alterTable('matomo_t', ['MODIFY s VARCHAR(20) NULL']);

        Db::exec("INSERT INTO matomo_t VALUES (1, 'a')");
        foreach (["INSERT INTO matomo_t VALUES (2, 'a')", "INSERT INTO matomo_t VALUES ('x', 'b')"] as $sql) {
            try {
                Db::exec($sql);
                $this->fail('accepted: ' . $sql);
            } catch (\Exception $e) {
                $this->assertMatchesRegularExpression('/UNIQUE|cannot store/', $e->getMessage());
            }
        }
    }

    private function getColumnDeclarations(string $table): string
    {
        $sql = (string) Db::fetchOne('SELECT sql FROM sqlite_schema WHERE name = ?', [$table]);

        return substr($sql, strpos($sql, '('));
    }

    public function testRebuildOnAColumnPrefixKey(): void
    {
        $this->schema->createTable('p', 'a VARCHAR(200) NOT NULL, b VARCHAR(200) NOT NULL');
        $this->schema->alterTable('matomo_p', ['ADD PRIMARY KEY (a(3), b(3))']);

        $this->assertTrue($this->schema->tableHasIndex('matomo_p', 'PRIMARY'));
        Db::query("INSERT INTO matomo_p VALUES ('abc1', 'xyz1')");
        try {
            Db::query("INSERT INTO matomo_p VALUES ('ABC2', 'XYZ2')");
            $this->fail('the prefixes are unique');
        } catch (\Exception $e) {
            $this->assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
        }

        $this->schema->alterTable('matomo_p', ['DROP PRIMARY KEY']);
        $this->assertFalse($this->schema->tableHasIndex('matomo_p', 'PRIMARY'));
        Db::query("INSERT INTO matomo_p VALUES ('ABC2', 'XYZ2')");
    }

    public function testUnchangedAutoIncrementColumnIsNotRebuilt(): void
    {
        $this->schema->createTable('t', 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)');
        $before = Db::fetchOne("SELECT sql FROM sqlite_schema WHERE name = 'matomo_t'");

        $this->schema->alterTable('matomo_t', ['MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT']);

        // not even renamed through a rebuild
        $this->assertSame($before, Db::fetchOne("SELECT sql FROM sqlite_schema WHERE name = 'matomo_t'"));
    }

    /**
     * @dataProvider getRebuildErrors
     */
    public function testRebuildErrorsCarryMysqlsNumbers(string $definition, string $specification, int $errno): void
    {
        $this->schema->createTable('t', $definition);
        try {
            $this->schema->alterTable('matomo_t', [$specification]);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, $errno), $e->getMessage());
        }
    }

    public function getRebuildErrors(): iterable
    {
        yield 'unknown column' => ['id INT NOT NULL', 'MODIFY missing INT NULL', 1054];
        yield 'new name taken' => ['id INT NOT NULL, a INT NULL', 'CHANGE a id INT NULL', 1060];
        yield 'second primary key' => ['id INT NOT NULL, PRIMARY KEY (id)', 'ADD PRIMARY KEY(`id`)', 1068];
        yield 'primary key on a missing column' => ['id INT NOT NULL', 'ADD PRIMARY KEY(`missing`)', 1072];
        yield 'no primary key to drop' => ['id INT NOT NULL', 'DROP PRIMARY KEY', 1091];
        yield 'auto increment key dropped' => ['id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)', 'DROP PRIMARY KEY', 1075];
    }

    public function testRebuildOfAMissingTableFailsAsOnMysql(): void
    {
        try {
            $this->schema->alterTable('matomo_missing', ['MODIFY a INT NULL']);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertTrue(Db::get()->isErrNo($e, 1146));
        }
    }

    public function testFailedRebuildLeavesTheTableAsItWas(): void
    {
        $this->schema->createTable('t', "id INT NOT NULL, s VARCHAR(10) NULL, PRIMARY KEY (id), KEY s (s)");
        Db::query("INSERT INTO matomo_t VALUES (1, 'other')");
        $before = Db::fetchAll("SELECT type, name, sql FROM sqlite_schema ORDER BY name");

        try {
            // the stored value isn't one of the new ENUM's
            $this->schema->alterTable('matomo_t', ['ADD COLUMN x INT NULL', "MODIFY s ENUM('a', 'b') NULL"]);
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertStringContainsString('CHECK constraint failed', $e->getMessage());
        }

        $this->assertSame($before, Db::fetchAll("SELECT type, name, sql FROM sqlite_schema ORDER BY name"));
        $this->assertSame('other', Db::fetchOne('SELECT s FROM matomo_t'));
        $this->assertFalse((bool) Db::get()->getConnection()->inTransaction());
    }

    public function testAlterTableMigrationsReadTheTableWhenTheyRun(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, PRIMARY KEY (id)');
        // built together, as an update's migrations are, then run in order
        $add = new AddColumn('matomo_t', 'n', 'INT NULL', null);
        $change = new ChangeColumnType('matomo_t', 'n', 'VARCHAR(10) NOT NULL');
        // shown as SQLite runs it
        $this->assertSame('/* SQLite rebuilds the table matomo_t for: CHANGE `n` `n` VARCHAR(10) NOT NULL */;', (string) $change);

        $add->exec();
        $change->exec();

        $this->assertSame('text', $this->schema->getTableColumns('matomo_t')['n']['Type']);
    }

    public function testAbortTableLocksOfAMysqlConnectionLeaveTheSqliteLockAlone(): void
    {
        Db::exec('CREATE TABLE matomo_l (id INTEGER)');
        $sqlite = Db::get();
        Db::lockTables([], ['matomo_l']);
        Db::query('INSERT INTO matomo_l VALUES (1)');

        $mysql = $this->getRecordingMysqlAdapter();
        Db::setDatabaseObject($mysql);
        Db::abortTableLocks();
        $this->assertSame(['UNLOCK TABLES'], $mysql->statements);

        Db::setDatabaseObject($sqlite);
        Db::unlockAllTables();
        $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_l'));
    }

    public function testUnlockAllTablesOpensNoConnectionForItself(): void
    {
        Db::exec('CREATE TABLE matomo_l (id INTEGER)');
        $sqlite = Db::get();
        Db::lockTables([], ['matomo_l']);
        Db::query('INSERT INTO matomo_l VALUES (1)');

        Db::setDatabaseObject(null);
        try {
            $this->assertSame(0, Db::unlockAllTables());
            $this->assertFalse(Db::hasDatabaseObject());
        } finally {
            Db::setDatabaseObject($sqlite);
        }
        // committed on the connection that took the lock
        $this->assertSame(1, (int) Db::fetchOne('SELECT COUNT(*) FROM matomo_l'));
    }

    public function testLockPrivilegeOnSqliteIgnoresWhatAMysqlConnectionCached(): void
    {
        $cache = new \ReflectionProperty(Db::class, 'lockPrivilegeGranted');
        $cache->setAccessible(true);
        $cache->setValue(null, false);
        try {
            $this->assertTrue(Db::isLockPrivilegeGranted());
        } finally {
            $cache->setValue(null, null);
        }
    }

    public function testMigrationsForAMysqlConnectionAreMysqlWhateverTheConfiguredSchema(): void
    {
        $sqlite = Db::get();
        $schema = Config::getInstance()->database['schema'];
        Config::getInstance()->database['schema'] = 'Sqlite';
        \Piwik\Db\Schema::getInstance()->unsetSchema();
        Db::setDatabaseObject($this->getRecordingMysqlAdapter());
        try {
            $this->assertSame('ALTER TABLE `matomo_t` ADD COLUMN `c` INT NULL;', (string) new AddColumn('matomo_t', 'c', 'INT NULL', null));
            $this->assertSame('ALTER TABLE `matomo_t` ADD INDEX index_c (`c`);', (string) new AddIndex('matomo_t', ['c'], ''));
            $this->assertSame('ALTER TABLE `matomo_t` CHANGE `c` `c` BIGINT NULL;', (string) new ChangeColumnType('matomo_t', 'c', 'BIGINT NULL'));
            $this->assertMatchesRegularExpression('/^CREATE TABLE `matomo_t` \(`c` INT NULL\) ENGINE=\w+ DEFAULT CHARSET=\w+/', (string) new CreateTable('matomo_t', ['c' => 'INT NULL'], []));
        } finally {
            Db::setDatabaseObject($sqlite);
            Config::getInstance()->database['schema'] = $schema;
            \Piwik\Db\Schema::getInstance()->unsetSchema();
        }
    }

    public function testMigrationsBuiltOnSqliteRunInTheDialectOfTheConnectionTheyRunOn(): void
    {
        $sqlite = Db::get();
        $migrations = [
            new CreateTable('matomo_t', ['c' => 'INT NULL'], []),
            new AddColumn('matomo_t', 'd', 'VARCHAR(10) NULL', null),
        ];
        $mysql = $this->getRecordingMysqlAdapter();
        Db::setDatabaseObject($mysql);
        try {
            foreach ($migrations as $migration) {
                $migration->exec();
            }
        } finally {
            Db::setDatabaseObject($sqlite);
        }

        $this->assertCount(2, $mysql->statements);
        $this->assertMatchesRegularExpression('/^CREATE TABLE `matomo_t` \(`c` INT NULL\) ENGINE=/', $mysql->statements[0]);
        $this->assertSame('ALTER TABLE `matomo_t` ADD COLUMN `d` VARCHAR(10) NULL', $mysql->statements[1]);
    }

    public function testAnIgnoredErrorSkipsOnlyItsOwnSpecification(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, x INT NULL, y INT NULL');
        // built together, as an update's migrations are, then run in order
        $migrations = [
            new AddColumn('matomo_t', 'a', 'INT NULL', null),
            new AddColumns('matomo_t', ['a' => 'INT NULL', 'b' => 'INT NULL'], null),
            new DropColumn('matomo_t', 'x'),
            new DropColumns('matomo_t', ['x', 'y']),
        ];
        foreach ($migrations as $migration) {
            try {
                $migration->exec();
            } catch (\Exception $e) {
                $this->assertTrue($migration->shouldIgnoreError($e), $e->getMessage());
            }
        }

        $this->assertSame(['id', 'a', 'b'], array_keys($this->schema->getTableColumns('matomo_t')));
    }

    public function testRetriedSpecificationsAreUndoneByALaterError(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, a INT NULL');
        Db::exec('INSERT INTO matomo_t (id, a) VALUES (1, 5), (2, 5)');
        // the duplicate column stops the batch, the retry adds b, the unique key then fails on the rows
        $migration = new AlterTable('matomo_t', ['ADD COLUMN a INT NULL', 'ADD COLUMN b INT NULL', 'ADD UNIQUE KEY ua (a)'], 1060);
        try {
            $migration->exec();
            $this->fail('no exception');
        } catch (\Exception $e) {
            $this->assertFalse($migration->shouldIgnoreError($e), $e->getMessage());
        }

        $this->assertSame(['id', 'a'], array_keys($this->schema->getTableColumns('matomo_t')));
    }

    public function testColumnMigrationsReadTheTableWhenTheyRun(): void
    {
        $this->schema->createTable('t', 'id INT NOT NULL, a INT NULL');
        $add = new AddColumns('matomo_t', ['a' => 'INT NULL', 'b' => 'INT NULL'], null);
        $drop = new DropColumns('matomo_t', ['b', 'c']);
        $this->schema->alterTable('matomo_t', ['DROP COLUMN a', 'ADD COLUMN c INT NULL']);

        $add->exec();
        $this->assertSame(['id', 'c', 'a', 'b'], array_keys($this->schema->getTableColumns('matomo_t')));
        $drop->exec();
        $this->assertSame(['id', 'a'], array_keys($this->schema->getTableColumns('matomo_t')));
    }

    public function testIndexOnATableYetToBeCreatedIsDescribed(): void
    {
        $sql = (string) new AddUniqueKey('matomo_none', ['label(2)'], 'label2');

        $this->assertStringStartsWith('/* SQLite: ALTER TABLE `matomo_none` ADD UNIQUE', $sql);
    }

    public function testReplayedCreateTableLeavesTheTableAsItIs(): void
    {
        $columns = ['id' => 'INT NOT NULL', 'label' => 'VARCHAR(20) UNIQUE'];
        (new CreateTable('matomo_u', $columns, ['id']))->exec();
        Db::exec('CREATE TABLE matomo_v (id INT, label TEXT)');
        $before = Db::fetchAll('SELECT type, name, sql FROM sqlite_schema ORDER BY name');

        foreach (['matomo_u', 'matomo_v'] as $table) {
            try {
                (new CreateTable($table, $columns, ['id']))->exec();
                $this->fail('no exception');
            } catch (\Exception $e) {
                $this->assertTrue(Db::get()->isErrNo($e, 1050), $e->getMessage());
            }
        }

        $this->assertSame($before, Db::fetchAll('SELECT type, name, sql FROM sqlite_schema ORDER BY name'));
    }

    public function testUniquePrefixBuiltBeforeItsTableKeepsTheColumnCollation(): void
    {
        // built together, as an update's migrations are
        $create = new CreateTable('matomo_w', ['label' => 'VARCHAR(20) NOT NULL'], []);
        $unique = new AddUniqueKey('matomo_w', ['label(2)'], 'label2');
        $create->exec();
        $unique->exec();

        Db::query("INSERT INTO matomo_w VALUES ('AB1')");
        $this->expectExceptionMessage('UNIQUE constraint failed');
        Db::query("INSERT INTO matomo_w VALUES ('ab2')");
    }

    public function testQuotedColumnNames(): void
    {
        $this->schema->createTable('q', '`event-label` VARCHAR(10) NULL, `key` INT NULL, KEY `by-label` (`event-label`)');
        (new AddColumn('matomo_q', 'other col', 'INT NULL', null))->exec();
        $this->schema->alterTable('matomo_q', ['MODIFY `event-label` VARCHAR(20) NOT NULL', 'ADD INDEX `by-key` (`key`)']);
        (new DropIndex('matomo_q', 'by-key'))->exec();
        (new DropColumn('matomo_q', 'key'))->exec();

        $this->assertSame(['event-label', 'other col'], array_keys($this->schema->getTableColumns('matomo_q')));
        $this->assertTrue($this->schema->tableHasIndex('matomo_q', 'by-label'));
    }

    /**
     * A MySQL adapter that records its statements and runs no query.
     */
    private function getRecordingMysqlAdapter(): \Piwik\Db\Adapter\Pdo\Mysql
    {
        return new class (['host' => 'localhost', 'username' => 'u', 'password' => 'p', 'dbname' => 'd', 'enable_ssl' => false]) extends \Piwik\Db\Adapter\Pdo\Mysql {
            /**
             * @var string[]
             */
            public $statements = [];

            public function exec($sql)
            {
                $this->statements[] = $sql;
                return 0;
            }

            public function query($sql, $bind = [])
            {
                throw new \LogicException('unexpected query: ' . $sql);
            }
        };
    }

    private function getTableBody(string $table): string
    {
        $sql = (string) Db::fetchOne('SELECT sql FROM sqlite_schema WHERE type = ? AND name = ?', ['table', $table]);

        return substr($sql, strpos($sql, '('));
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

    public function testColumnsOfAMissingTableFailAsOnMysql(): void
    {
        $this->expectException(\Zend_Db_Statement_Exception::class);
        $this->expectExceptionMessage('no such table: matomo_missing');
        $this->schema->getTableColumns('matomo_missing');
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
