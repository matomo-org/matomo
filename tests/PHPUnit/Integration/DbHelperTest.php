<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class DbHelperTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        DbHelper::dropDatabase('newdb; create database anotherdb;');
        DbHelper::dropDatabase('testdb');
    }

    public function test_createDatabase_escapesInputProperly()
    {
        $dbName = 'newdb`; create database anotherdb;`';
        DbHelper::createDatabase($dbName);

        $this->assertDbExists($dbName);
        $this->assertDbNotExists('anotherdb');
    }

    public function test_dropDatabase_escapesInputProperly()
    {
        DbHelper::createDatabase("testdb");
        $this->assertDbExists('testdb');

        DbHelper::dropDatabase('testdb`; create database anotherdb;`');
        $this->assertDbExists('testdb');
        $this->assertDbNotExists('anotherdb');
    }

    private function assertDbExists($dbName)
    {
        $dbs = Db::fetchAll("SHOW DATABASES");
        $dbs = array_column($dbs, 'Database');
        $this->assertContains($this->cleanName($dbName), $dbs);
    }

    private function assertDbNotExists($dbName)
    {
        $dbs = Db::fetchAll("SHOW DATABASES");
        $dbs = array_column($dbs, 'Database');
        $this->assertNotContains($this->cleanName($dbName), $dbs);
    }

    private function cleanName($dbName)
    {
        return str_replace('`', '', $dbName);
    }
}
