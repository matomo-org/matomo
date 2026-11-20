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

    public function testDateTimeIntervalSql(): void
    {
        $mysql = new Mysql();
        $this->assertSame('pvt.server_time + INTERVAL pvt.time_spent SECOND', $mysql->getDateTimeIntervalSql('pvt.server_time', '+', 'pvt.time_spent', 'SECOND'));
        $this->assertSame('? - INTERVAL ? DAY', $mysql->getDateTimeIntervalSql('?', '-', '?', 'day'));

        $this->expectException(\InvalidArgumentException::class);
        $mysql->getDateTimeIntervalSql('?', '*', '?', 'DAY');
    }

    public function testScalarFunctionsKeepTheirMysqlSpelling(): void
    {
        $mysql = new Mysql();

        $this->assertSame('HOUR(log_visit.visit_last_action_time)', $mysql->getHourSql('log_visit.visit_last_action_time'));
        $this->assertSame('DAYOFWEEK(t.d)', $mysql->getDayOfWeekSql('t.d'));
        $this->assertSame('UNIX_TIMESTAMP(ts_last_seen)', $mysql->getUnixTimestampSql('ts_last_seen'));
        $this->assertSame('UNIX_TIMESTAMP()', $mysql->getUnixTimestampSql());
        $this->assertSame('NOW()', $mysql->getCurrentDateTimeSql());
        $this->assertSame('CHAR_LENGTH(name)', $mysql->getCharLengthSql('name'));
        $this->assertSame('TIMESTAMPDIFF(SECOND, server_time, ?)', $mysql->getSecondsBetweenSql('server_time', '?'));
        $this->assertSame('LEAST(1800, GREATEST(time_spent, 5))', $mysql->getLeastSql('1800', $mysql->getGreatestSql('time_spent', '5')));
        $this->assertSame(' FOR UPDATE', $mysql->getRowLockSql());
        $this->assertSame(' LOCK IN SHARE MODE', $mysql->getRowLockSql(false));
        $this->assertSame('TRUNCATE TABLE `matomo_session`', $mysql->getTruncateTableSql('matomo_session'));

        $this->expectException(\InvalidArgumentException::class);
        $mysql->getGreatestSql('a');
    }

    public function testIndexHints(): void
    {
        $this->assertSame(' FORCE INDEX (index_idsite_idvisitor_time)', (new Mysql())->getIndexHintSql('index_idsite_idvisitor_time'));
        $this->assertSame(' USE INDEX (index_idsite_datetime)', (new Mysql())->getIndexHintSql('index_idsite_datetime', false));
    }

    public function testTheDialectInterfaceStaysAsReleasedSoThatExistingSchemasStillLoad(): void
    {
        $this->assertSame([
            'getInsertIgnoreSql',
            'getOnDuplicateKeyUpdateSql',
            'getInsertedValueSql',
            'getGroupConcatSql',
            'getLikeEscapeSql',
        ], get_class_methods(\Piwik\Db\SqlDialectInterface::class));
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
        $this->assertSame(' FORCE INDEX (i)', $facade->getIndexHintSql('i'));
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
