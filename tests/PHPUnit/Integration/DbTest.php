<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DbTest extends IntegrationTestCase
{
    public function test_getColumnNamesFromTable()
    {
        $this->assertColumnNames('access', array('login', 'idsite', 'access'));
        $this->assertColumnNames('option', array('option_name', 'option_value', 'autoload'));
    }

    private function assertColumnNames($tableName, $expectedColumnNames)
    {
        $colmuns = Db::getColumnNamesFromTable(Common::prefixTable($tableName));

        $this->assertEquals($expectedColumnNames, $colmuns);
    }

    /**
     * @dataProvider getIsOptimizeInnoDBTestData
     */
    public function test_isOptimizeInnoDBSupported_ReturnsCorrectResult($version, $expectedResult)
    {
        $result = Db::isOptimizeInnoDBSupported($version);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider getDbAdapter
     */
    public function test_SqlMode_IsSet_PDO($adapter, $expectedClass)
    {
        Db::destroyDatabaseObject();
        Config::getInstance()->database['adapter'] = $adapter;
        $db = Db::get();
        // make sure test is useful and setting adapter works
        $this->assertInstanceOf($expectedClass, $db);
        $result = $db->fetchOne('SELECT @@SESSION.sql_mode');

        $expected = 'NO_AUTO_VALUE_ON_ZERO';
        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessagelock name has to be 64 characters or less
     */
    public function test_getDbLock_shouldThrowAnException_IfDbLockNameIsTooLong()
    {
        Db::getDbLock(str_pad('test', 65, '1'));
    }

    public function test_getDbLock_shouldGetLock()
    {
        $db = Db::get();
        $this->assertTrue(Db::getDbLock('MyLock'));
        // same session still has lock
        $this->assertTrue(Db::getDbLock('MyLock'));

        Db::setDatabaseObject(null);
        // different session, should not be able to acquire lock
        $this->assertFalse(Db::getDbLock('MyLock', 1));
        // different session cannot release lock
        $this->assertFalse(Db::releaseDbLock('MyLock'));
        Db::destroyDatabaseObject();

        // release lock again by using previous session
        Db::setDatabaseObject($db);
        $this->assertTrue(Db::releaseDbLock('MyLock'));
        Db::destroyDatabaseObject();
    }

    public function getDbAdapter()
    {
        return array(
            array('Mysqli', 'Piwik\Db\Adapter\Mysqli'),
            array('PDO\MYSQL', 'Piwik\Db\Adapter\Pdo\Mysql')
        );
    }

    public function getIsOptimizeInnoDBTestData()
    {
        return array(
            array("10.0.17-MariaDB-1~trusty", false),
            array("10.1.1-MariaDB-1~trusty", true),
            array("10.2.0-MariaDB-1~trusty", true),
            array("10.6.19-0ubuntu0.14.04.1", false),

            // for sanity. maybe not ours.
            array("", false),
            array(0, false),
            array(false, false),
            array("slkdf(@*#lkesjfMariaDB", false),
            array("slkdfjq3rujlkv", false),
        );
    }
}
