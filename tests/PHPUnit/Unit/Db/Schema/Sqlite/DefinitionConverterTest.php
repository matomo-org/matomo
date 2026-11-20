<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Schema\Sqlite;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Piwik\Db\Schema\Sqlite\DefinitionConverter;
use Piwik\Tests\Unit\Db\Sqlite\RequiresSqlite;

class DefinitionConverterTest extends TestCase
{
    use RequiresSqlite;

    /**
     * @var DefinitionConverter
     */
    private $converter;

    public function setUp(): void
    {
        parent::setUp();
        $this->converter = new DefinitionConverter();
    }

    /**
     * @dataProvider getColumnCases
     */
    public function testConvertColumnDefinition(string $mysql, string $expected): void
    {
        $this->assertSame($expected, $this->converter->convertColumnDefinition('t', $mysql));
    }

    public function getColumnCases(): iterable
    {
        yield 'unsigned integer with default' => ['TINYINT(1) UNSIGNED NOT NULL DEFAULT 0', 'INTEGER NOT NULL DEFAULT 0'];
        yield 'bigint nullable' => ['BIGINT UNSIGNED NULL', 'INTEGER'];
        yield 'decimal' => ['DECIMAL(10,2) NULL DEFAULT NULL', 'REAL DEFAULT NULL'];
        yield 'varchar is case-insensitive' => ["VARCHAR(255) NOT NULL DEFAULT ''", "TEXT NOT NULL DEFAULT '' COLLATE NOCASE"];
        yield 'binary collation stays case-sensitive' => ['VARCHAR(100) COLLATE utf8mb4_bin NOT NULL', 'TEXT NOT NULL'];
        yield 'binary' => ['VARBINARY(8) NOT NULL', 'BLOB NOT NULL'];
        yield 'datetime' => ['DATETIME NULL', 'TEXT'];
    }

    public function testAutoIncrementBecomesAnIntegerPrimaryKey(): void
    {
        $statements = $this->converter->convertCreateTable(
            'CREATE TABLE t (id INT UNSIGNED NOT NULL AUTO_INCREMENT, name VARCHAR(10) NOT NULL, PRIMARY KEY (id), INDEX (name)) ENGINE=InnoDB'
        );

        $this->assertSame([
            "CREATE TABLE `t` (\n  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,\n  `name` TEXT NOT NULL COLLATE NOCASE\n)",
            'CREATE INDEX `t__name` ON `t` (`name`)',
        ], $statements);
    }

    public function testAutoIncrementOutsideAPrimaryKeyOfItsOwnIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->converter->convertCreateTable('CREATE TABLE t (a INT NOT NULL, id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY (a, id))');
    }

    public function testIndexPrefixIsKeptOnlyWhereItDecidesUniqueness(): void
    {
        $statements = $this->converter->convertCreateTable(
            'CREATE TABLE t (a VARCHAR(500) NOT NULL, b VARCHAR(500) COLLATE utf8mb4_bin NOT NULL, KEY ka (a(50)), UNIQUE KEY ub (b(20)), UNIQUE ua (a(10)))'
        );

        $this->assertSame('CREATE INDEX `t__ka` ON `t` (`a`)', $statements[1]);
        $this->assertSame('CREATE UNIQUE INDEX `t__ub` ON `t` (substr(`b`, 1, 20))', $statements[2]);
        $this->assertSame('CREATE UNIQUE INDEX `t__ua` ON `t` (substr(`a`, 1, 10) COLLATE NOCASE)', $statements[3]);
    }

    public function testUniquePrefixIndexRejectsRowsThatOnlyDifferAfterThePrefixOrInCase(): void
    {
        $this->requireSqlite();
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        foreach ($this->converter->convertCreateTable('CREATE TABLE t (a VARCHAR(100) NOT NULL, UNIQUE KEY ua (a(3)))') as $statement) {
            $pdo->exec($statement);
        }

        $pdo->exec("INSERT INTO t VALUES ('abcdef')");
        foreach (['abcxyz', 'ABCdef'] as $duplicate) {
            try {
                $pdo->exec("INSERT INTO t VALUES ('$duplicate')");
                $this->fail("$duplicate was accepted");
            } catch (\PDOException $e) {
                $this->assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
            }
        }
    }

    public function testUnnamedIndexesGetMysqlDefaultNames(): void
    {
        $statements = $this->converter->convertCreateTable('CREATE TABLE t (a INT, b INT, INDEX (a), INDEX (a, b), UNIQUE (b))');

        $this->assertSame('CREATE INDEX `t__a` ON `t` (`a`)', $statements[1]);
        $this->assertSame('CREATE INDEX `t__a_2` ON `t` (`a`, `b`)', $statements[2]);
        $this->assertSame('CREATE UNIQUE INDEX `t__b` ON `t` (`b`)', $statements[3]);
    }

    public function testIfNotExistsAppliesToTheTableAndItsIndexes(): void
    {
        $statements = $this->converter->convertTableDefinition('t', 'a INT, KEY ka (a)', true);

        $this->assertStringStartsWith('CREATE TABLE IF NOT EXISTS `t`', $statements[0]);
        $this->assertSame('CREATE INDEX IF NOT EXISTS `t__ka` ON `t` (`a`)', $statements[1]);
    }

    /**
     * @dataProvider getUnsupportedDefinitions
     */
    public function testUnsupportedDefinitionsAreRejected(string $definition): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->converter->convertTableDefinition('t', $definition);
    }

    public function getUnsupportedDefinitions(): iterable
    {
        yield 'enum' => ["a ENUM('x','y') NOT NULL"];
        yield 'fulltext' => ['a TEXT, FULLTEXT KEY f (a)'];
        yield 'foreign key' => ['a INT, FOREIGN KEY (a) REFERENCES u (id)'];
        yield 'prefixed primary key' => ['a VARCHAR(100) NOT NULL, PRIMARY KEY (a(10))'];
    }
}
