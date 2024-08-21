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

class MysqlTest extends IntegrationTestCase
{
    /**
     * @dataProvider getIsOptimizeInnoDBTestData
     */
    public function testIsOptimizeInnoDBSupportedReturnsCorrectResult($version, $expectedResult)
    {
        $schema = $this->getMockBuilder(Db\Schema\Mysql::class)->onlyMethods(['getVersion'])->getMock();
        $schema->method('getVersion')->willReturn($version);
        $this->assertEquals($expectedResult, $schema->isOptimizeInnoDBSupported());
    }

    public function getIsOptimizeInnoDBTestData()
    {
        return array(
            array("10.0.17-MariaDB-1~trusty", false),
            array("10.1.1-MariaDB-1~trusty", true),
            array("10.2.0-MariaDB-1~trusty", true),
            array("10.6.19-0ubuntu0.14.04.1", false),
            array("8.0.11-TiDB-v8.1.0", false),
            array("", false),
            array("0", false),
            array("slkdf(@*#lkesjfMariaDB", false),
            array("slkdfjq3rujlkv", false),
        );
    }

    public function testOptimize()
    {
        if (DatabaseConfig::getConfigValue('schema') !== 'Mysql') {
            self::markTestSkipped('Mysql is not available');
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

        // innodb should be skipped by default
        $this->assertFalse($schema->optimizeTables(['table3', 'table4']));

        // should optimize when forced
        $this->assertTrue($schema->optimizeTables(['table3', 'table4'], true));
    }
}
