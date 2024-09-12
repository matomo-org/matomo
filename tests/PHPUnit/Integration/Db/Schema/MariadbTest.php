<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Db\Schema;

use Piwik\Config\DatabaseConfig;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class MariadbTest extends IntegrationTestCase
{
    /**
     * @dataProvider getIsOptimizeInnoDBTestData
     */
    public function testIsOptimizeInnoDBSupportedReturnsCorrectResult($version, $expectedResult)
    {
        $schema = $this->getMockBuilder(Db\Schema\Mariadb::class)->onlyMethods(['getVersion'])->getMock();
        $schema->method('getVersion')->willReturn($version);
        $this->assertEquals($expectedResult, $schema->isOptimizeInnoDBSupported());
    }

    public function getIsOptimizeInnoDBTestData()
    {
        return array(
            array("10.0.17-MariaDB-1~trusty", false),
            array("10.1.1-MariaDB-1~trusty", true),
            array("10.2.0-MariaDB-1~trusty", true),
            array("10.6.19-0ubuntu0.14.04.1", true), // we expect true, as the version is high enough
            array("8.0.11-TiDB-v8.1.0", false),
            array("", false),
            array("0", false),
            array("slkdf(@*#lkesjfMariaDB", false),
            array("slkdfjq3rujlkv", false),
        );
    }

    public function testOptimize()
    {
        if (DatabaseConfig::getConfigValue('schema') !== 'Mariadb') {
            self::markTestSkipped('Mariadb is not available');
        }

        // create two myisam tables
        Db::exec("CREATE TABLE table1 (a INT) ENGINE=MYISAM");
        Db::exec("CREATE TABLE table2 (b INT) ENGINE=MYISAM");

        // create two innodb tables
        Db::exec("CREATE TABLE table3 (c INT) ENGINE=InnoDB");
        Db::exec("CREATE TABLE table4 (d INT) ENGINE=InnoDB");

        $schema = Db\Schema::getInstance();

        // make sure optimizing myisam tables works
        $this->assertTrue($schema->optimizeTables(['table1', 'table2']));

        // make sure optimizing both myisam & innodb results in optimizations
        $this->assertTrue($schema->optimizeTables(['table1', 'table2', 'table3', 'table4']));

        // optimizing innodb should be available for mariadb
        $this->assertTrue($schema->optimizeTables(['table3', 'table4']), true);

        // should optimize when forced
        $this->assertTrue($schema->optimizeTables(['table3', 'table4'], true), true);
    }

    /**
     * @dataProvider getTableCreateOptionsTestData
     */
    public function testTableCreateOptions(array $optionOverrides, string $expected): void
    {
        if (DatabaseConfig::getConfigValue('schema') !== 'Mariadb') {
            self::markTestSkipped('Mariadb is not available');
        }

        foreach ($optionOverrides as $name => $value) {
            DatabaseConfig::setConfigValue($name, $value);
        }

        $schema = Db\Schema::getInstance();

        self::assertSame($expected, $schema->getTableCreateOptions());
    }

    public function getTableCreateOptionsTestData(): iterable
    {
        yield 'default charset, empty collation' => [
            ['collation' => ''],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC'
        ];

        yield 'override charset, empty collation' => [
            ['charset' => 'utf8mb3', 'collation' => ''],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb3'
        ];

        yield 'default charset, override collation' => [
            ['collation' => 'utf8mb4_swedish_ci'],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_swedish_ci ROW_FORMAT=DYNAMIC'
        ];

        yield 'override charset and collation' => [
            ['charset' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci'],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci'
        ];
    }
}
