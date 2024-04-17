<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class SqlTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // create two myisam tables
        Db::exec("CREATE TABLE table1 (a INT) ENGINE=MYISAM");
        Db::exec("CREATE TABLE table2 (b INT) ENGINE=MYISAM");

        // create two innodb tables
        Db::exec("CREATE TABLE table3 (c INT) ENGINE=InnoDB");
        Db::exec("CREATE TABLE table4 (d INT) ENGINE=InnoDB");
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testOptimize()
    {
        // make sure optimizing myisam tables works
        $this->assertTrue(Db::optimizeTables(array('table1', 'table2')) !== false);

        // make sure optimizing both myisam & innodb results in optimizations
        $this->assertTrue(Db::optimizeTables(array('table1', 'table2', 'table3', 'table4')) !== false);

        // make sure innodb tables are skipped
        if (Db::isOptimizeInnoDBSupported()) {
            $this->assertTrue(Db::optimizeTables(array('table3', 'table4')));
        } else {
            $this->assertFalse(Db::optimizeTables(array('table3', 'table4')));
        }
    }
}
