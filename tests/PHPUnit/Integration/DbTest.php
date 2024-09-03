<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\TableMetadata;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 */
class DbTest extends IntegrationTestCase
{
    private $dbReaderConfigBackup;

    public function setUp(): void
    {
        parent::setUp();

        $this->dbReaderConfigBackup = Config::getInstance()->database_reader;
    }

    public function tearDown(): void
    {
        Db::destroyDatabaseObject();
        Config::getInstance()->database_reader = $this->dbReaderConfigBackup;
        parent::tearDown();
    }

    // this test is for PDO which will fail if execute() is called w/ a null param value
    public function testInsertWithNull()
    {
        $GLOBALS['abc'] = 1;
        $table = Common::prefixTable('testtable');
        Db::exec("CREATE TABLE `$table` (
                      testid BIGINT NOT NULL AUTO_INCREMENT,
                      testvalue BIGINT NULL,
                      PRIMARY KEY (testid)
                  )");

        Db::query("INSERT INTO `$table` (testvalue) VALUES (?)", [4]);
        Db::query("INSERT INTO `$table` (testvalue) VALUES (?)", [null]);

        $values = Db::fetchAll("SELECT testid, testvalue FROM `$table`");

        $expected = [
            ['testid' => 1, 'testvalue' => 4],
            ['testid' => 2, 'testvalue' => null],
        ];

        $this->assertEquals($expected, $values);
    }

    public function testGetColumnNamesFromTable()
    {
        $this->assertColumnNames('access', array('idaccess', 'login', 'idsite', 'access'));
        $this->assertColumnNames('option', array('option_name', 'option_value', 'autoload'));
    }

    public function testGetDb()
    {
        $db = Db::get();
        $this->assertNotEmpty($db);
        $this->assertTrue($db instanceof Db\AdapterInterface);
    }

    public function testHasReaderDatabaseObjectByDefaultNotInUse()
    {
        $this->assertFalse(Db::hasReaderDatabaseObject());
    }

    public function testHasReaderConfiguredByDefaultNotConfigured()
    {
        $this->assertFalse(Db::hasReaderConfigured());
    }

    public function testGetReaderWhenNotConfiguredStillReturnsRegularDbConnection()
    {
        $this->assertFalse(Db::hasReaderConfigured());// ensure no reader is configured
        $db = Db::getReader();
        $this->assertNotEmpty($db);
        $this->assertTrue($db instanceof Db\AdapterInterface);
    }

    public function testWithReader()
    {
        Config::getInstance()->database_reader = Config::getInstance()->database;

        $this->assertFalse(Db::hasReaderDatabaseObject());
        $this->assertTrue(Db::hasReaderConfigured());

        $db = Db::getReader();
        $this->assertNotEmpty($db);
        $this->assertTrue($db instanceof Db\AdapterInterface);

        $this->assertTrue(Db::hasReaderDatabaseObject());
        Db::destroyDatabaseObject();
        $this->assertFalse(Db::hasReaderDatabaseObject());
    }

    public function testWithReaderCreatesDifferentConnectionForDb()
    {
        Config::getInstance()->database_reader = Config::getInstance()->database;

        $db = Db::getReader();
        $this->assertNotSame($db->getConnection(), Db::get()->getConnection());
    }

    public function testWithoutReaderUsesSameDbConnection()
    {
        $this->assertFalse(Db::hasReaderConfigured());
        $this->assertFalse(Db::hasReaderDatabaseObject());

        $db = Db::getReader();
        $this->assertSame($db->getConnection(), Db::get()->getConnection());
    }

    public function testWithReaderCanReconnectToWriterIfServerHasGoneAway(): void
    {
        Config::getInstance()->database_reader = Config::getInstance()->database;

        $connectionId = $this->setUpMySQLHasGoneAwayConnection();
        $reconnectionId = Db::executeWithDatabaseWriterReconnectionAttempt(function () {
            return Db::query('SELECT CONNECTION_ID()')->fetchColumn();
        });

        self::assertNotSame($connectionId, $reconnectionId);
    }

    public function testWithReaderDoesNotInterceptNonGoneAwayErrors(): void
    {
        Config::getInstance()->database_reader = Config::getInstance()->database;

        $expectedConnectionId = Db::query('SELECT CONNECTION_ID()')->fetchColumn();
        $dbException = null;

        try {
            Db::executeWithDatabaseWriterReconnectionAttempt(function () {
                Db::query('SHOW SYNTAX ERROR');
            });
        } catch (Exception $e) {
            $dbException = $e;
        }

        self::assertNotNull($dbException, 'Expected database exception was not thrown');
        self::assertTrue(
            Db::get()->isErrNo(
                $dbException,
                \Piwik\Updater\Migration\Db::ERROR_CODE_SYNTAX_ERROR
            )
        );

        // verify the connection has not been replaced
        $connectionId = Db::query('SELECT CONNECTION_ID()')->fetchColumn();

        self::assertSame($expectedConnectionId, $connectionId);
    }

    public function testWithoutReaderDoesNotReconnectIfServerHasGoneAway(): void
    {
        $this->setUpMySQLHasGoneAwayConnection();

        $dbException = null;

        try {
            Db::executeWithDatabaseWriterReconnectionAttempt(function () {
                Db::query('SELECT 1');
            });
        } catch (Exception $e) {
            $dbException = $e;
        }

        self::assertNotNull($dbException, 'Expected database exception was not thrown');
        self::assertTrue(
            Db::get()->isErrNo(
                $dbException,
                \Piwik\Updater\Migration\Db::ERROR_CODE_MYSQL_SERVER_HAS_GONE_AWAY
            )
            || false !== stripos($dbException->getMessage(), 'server has gone away')
        );
    }

    private function assertColumnNames($tableName, $expectedColumnNames)
    {
        $tableMetadataAccess = new TableMetadata();
        $colmuns = $tableMetadataAccess->getColumns(Common::prefixTable($tableName));

        $this->assertEquals($expectedColumnNames, $colmuns);
    }

    /**
     * @dataProvider getDbAdapter
     */
    public function testSqlModeIsSetPDO($adapter, $expectedClass)
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

    public function testGetDbLockShouldThrowAnExceptionIfDbLockNameIsTooLong()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('name has to be 64 characters or less');

        Db::getDbLock(str_pad('test', 65, '1'));
    }

    public function testGetDbLockShouldGetLock()
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

    /**
     * @dataProvider getDbAdapter
     */
    public function testGetRowCount($adapter, $expectedClass)
    {
        Db::destroyDatabaseObject();
        Config::getInstance()->database['adapter'] = $adapter;
        $db = Db::get();
        // make sure test is useful and setting adapter works
        $this->assertInstanceOf($expectedClass, $db);

        $result = $db->query('select 21');
        $this->assertEquals(1, $db->rowCount($result));
    }

    public function getDbAdapter()
    {
        return array(
            array('Mysqli', 'Piwik\Db\Adapter\Mysqli'),
            array('PDO\MYSQL', 'Piwik\Db\Adapter\Pdo\Mysql')
        );
    }

    /**
     * Forces Db::get() to return a database connection that
     * will throw "server has gone away" when running a query.
     *
     * @return string The MySQL connection id of the killed connection
     */
    private function setUpMySQLHasGoneAwayConnection(): string
    {
        // get extra connection to kill database connection
        // circumvents "query execution was interrupted" errors
        $db = Db::get();
        Db::setDatabaseObject(null);

        // connect and kill connection
        $connectionId = Db::query('SELECT CONNECTION_ID()')->fetchColumn();
        $db->exec('KILL ' . $connectionId);

        // clean up extra connection
        $db->closeConnection();
        unset($db);

        return (string) $connectionId;
    }
}
