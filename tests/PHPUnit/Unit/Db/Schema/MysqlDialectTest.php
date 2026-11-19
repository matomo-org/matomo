<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Schema;

use PHPUnit\Framework\TestCase;
use Piwik\Db\Schema;
use Piwik\Db\Schema\Mariadb;
use Piwik\Db\Schema\Mysql;
use Piwik\Db\SchemaInterface;

/**
 * The MySQL dialect must keep returning exactly the SQL Matomo emitted before the dialect methods existed.
 */
class MysqlDialectTest extends TestCase
{
    public function testInsertIgnoreAndUpsertFragments(): void
    {
        $mysql = new Mysql();

        $this->assertSame('INSERT IGNORE', $mysql->getInsertIgnoreSql());
        $this->assertSame('ON DUPLICATE KEY UPDATE', $mysql->getOnDuplicateKeyUpdateSql());
        $this->assertSame('VALUES(idaction_url)', $mysql->getInsertedValueSql('idaction_url'));
    }

    public function testLikeEscapeIsEmptyBecauseBackslashIsTheDefaultEscapeCharacter(): void
    {
        $this->assertSame('', (new Mysql())->getLikeEscapeSql());
    }

    /**
     * @dataProvider getGroupConcatCases
     */
    public function testGetGroupConcatSql(array $arguments, string $expected): void
    {
        $this->assertSame($expected, (new Mysql())->getGroupConcatSql(...$arguments));
    }

    public function getGroupConcatCases(): iterable
    {
        yield 'ordered, default separator' => [
            ["CONCAT(idarchive,'|',`name`,'|',`value`)", 'idarchive DESC'],
            "GROUP_CONCAT(CONCAT(idarchive,'|',`name`,'|',`value`) ORDER BY idarchive DESC SEPARATOR ',')",
        ];
        yield 'ordered by several columns' => [
            ["CONCAT(idarchive, '.', value)", 'ts_archived DESC, idarchive DESC'],
            "GROUP_CONCAT(CONCAT(idarchive, '.', value) ORDER BY ts_archived DESC, idarchive DESC SEPARATOR ',')",
        ];
        yield 'unordered, custom separator' => [
            ['a.access', '', '|'],
            "GROUP_CONCAT(a.access SEPARATOR '|')",
        ];
    }

    /**
     * @dataProvider getInvalidSeparators
     */
    public function testGetGroupConcatSqlRejectsSeparatorsThatWouldNeedQuoting(string $separator): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Mysql())->getGroupConcatSql('a', '', $separator);
    }

    public function getInvalidSeparators(): iterable
    {
        yield 'single quote' => ["'"];
        yield 'double quote' => ['"'];
        yield 'backslash' => ['\\'];
    }

    public function testMariadbInheritsTheMysqlDialect(): void
    {
        $mariadb = new Mariadb();

        $this->assertSame('INSERT IGNORE', $mariadb->getInsertIgnoreSql());
        $this->assertSame('', $mariadb->getLikeEscapeSql());
    }

    public function testFacadeDelegatesToTheConfiguredSchema(): void
    {
        $schema = $this->getMockBuilder(Mysql::class)->onlyMethods(['getInsertIgnoreSql'])->getMock();
        $schema->method('getInsertIgnoreSql')->willReturn('INSERT OR IGNORE');
        $this->assertSame('INSERT OR IGNORE', $this->facadeWith($schema)->getInsertIgnoreSql());
    }

    public function testFacadeFallsBackToMysqlForASchemaWithoutDialect(): void
    {
        $facade = $this->facadeWith($this->createMock(SchemaInterface::class));

        $this->assertSame('INSERT IGNORE', $facade->getInsertIgnoreSql());
        $this->assertSame('ON DUPLICATE KEY UPDATE', $facade->getOnDuplicateKeyUpdateSql());
        $this->assertSame('VALUES(c)', $facade->getInsertedValueSql('c'));
        $this->assertSame("GROUP_CONCAT(c ORDER BY c SEPARATOR ',')", $facade->getGroupConcatSql('c', 'c'));
        $this->assertSame('', $facade->getLikeEscapeSql());
    }

    /**
     * Returns a facade instance configured with the given schema, leaving the shared singleton untouched.
     */
    private function facadeWith(SchemaInterface $schema): Schema
    {
        $facade = (new \ReflectionClass(Schema::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Schema::class, 'schema'))->setValue($facade, $schema);

        return $facade;
    }
}
