<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Db\Schema;

use Piwik\Config\DatabaseConfig;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TiDbTest extends IntegrationTestCase
{
    public function testIsOptimizeInnoDBSupportedReturnsCorrectResult()
    {
        $schema = new Db\Schema\Tidb();
        $this->assertFalse($schema->isOptimizeInnoDBSupported());
    }

    public function testOptimize()
    {
        if (DatabaseConfig::getConfigValue('schema') !== 'Tidb') {
            self::markTestSkipped('Tidb is not available');
        }

        // create two myisam tables
        Db::exec("CREATE TABLE table1 (a INT) ENGINE=MYISAM");
        Db::exec("CREATE TABLE table2 (b INT) ENGINE=MYISAM");

        // create two innodb tables
        Db::exec("CREATE TABLE table3 (c INT) ENGINE=InnoDB");
        Db::exec("CREATE TABLE table4 (d INT) ENGINE=InnoDB");

        $schema = Db\Schema::getInstance();

        // optimizing not available for TiDb
        $this->assertFalse($schema->optimizeTables(['table1', 'table2']));
        $this->assertFalse($schema->optimizeTables(['table1', 'table2', 'table3', 'table4']));
        $this->assertFalse($schema->optimizeTables(['table3', 'table4']));
        $this->assertFalse($schema->optimizeTables(['table3', 'table4'], true));
    }
}
