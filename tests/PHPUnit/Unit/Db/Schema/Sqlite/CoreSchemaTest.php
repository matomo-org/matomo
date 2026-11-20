<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Schema\Sqlite;

use PDO;
use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Db\Schema\Sqlite;
use Piwik\Db\Schema\Sqlite\DefinitionConverter;
use Piwik\Tests\Unit\Db\Sqlite\RequiresSqlite;

/**
 * The core tables, as converted from their MySQL definitions, compared with a reviewed copy and created
 * in an in-memory database.
 *
 * When a MySQL table definition changes, regenerate the copy with MATOMO_UPDATE_SQLITE_SCHEMA=1 and
 * review its diff.
 */
class CoreSchemaTest extends TestCase
{
    use RequiresSqlite;

    private const EXPECTED_FILE = PIWIK_INCLUDE_PATH . '/tests/resources/Db/sqlite-core-schema.sql';

    /**
     * @var string
     */
    private $originalPrefix;

    public function setUp(): void
    {
        parent::setUp();
        $this->originalPrefix = Config::getInstance()->database['tables_prefix'];
        Config::getInstance()->database['tables_prefix'] = 'matomo_';
    }

    public function tearDown(): void
    {
        Config::getInstance()->database['tables_prefix'] = $this->originalPrefix;
        parent::tearDown();
    }

    public function testConvertedCoreTablesMatchTheReviewedSchema(): void
    {
        $sql = '';
        foreach ($this->getConvertedTables() as $statements) {
            $sql .= implode(";\n", $statements) . ";\n\n";
        }

        if (getenv('MATOMO_UPDATE_SQLITE_SCHEMA')) {
            file_put_contents(self::EXPECTED_FILE, $sql);
        }

        $this->assertSame(file_get_contents(self::EXPECTED_FILE), $sql);
    }

    public function testEveryMysqlIndexIsCreatedWithItsQualifiedNameAndUniqueness(): void
    {
        $this->requireSqlite();
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        foreach ($this->getConvertedTables() as $statements) {
            foreach ($statements as $statement) {
                $pdo->exec($statement);
            }
        }

        $checked = 0;
        foreach ((new Sqlite())->getTablesCreateSql() as $mysqlSql) {
            $this->assertSame(1, preg_match('/CREATE TABLE `?(\w+)`?/', $mysqlSql, $match));
            $table = $match[1];

            $indexes = [];
            foreach ($pdo->query('PRAGMA index_list(' . $table . ')') as $index) {
                $indexes[$index['name']] = (int) $index['unique'];
            }

            preg_match_all('/^\s*(UNIQUE(?:\s+(?:KEY|INDEX))?|KEY|INDEX)\s+`?(\w+)`?\s*\(/mi', $mysqlSql, $matches, PREG_SET_ORDER);
            foreach ($matches as [, $kind, $name]) {
                $sqliteName = DefinitionConverter::getIndexName($table, $name);
                $this->assertArrayHasKey($sqliteName, $indexes, "$table: index $name");
                $this->assertSame(stripos($kind, 'UNIQUE') === 0 ? 1 : 0, $indexes[$sqliteName], "$table: uniqueness of $name");
                $checked++;
            }
        }

        $this->assertGreaterThan(20, $checked);
    }

    /**
     * @return array<string, string[]> the SQLite statements of each core table
     */
    private function getConvertedTables(): array
    {
        $converter = new DefinitionConverter();
        $tables = [];
        foreach ((new Sqlite())->getTablesCreateSql() as $name => $sql) {
            $tables[$name] = $converter->convertCreateTable($sql);
        }

        return $tables;
    }
}
