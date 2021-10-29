<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

class DbHelperTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DbHelper::dropDatabase('newdb; create database anotherdb;');
        DbHelper::dropDatabase('testdb');
    }

    public function test_tableExists()
    {
        $this->assertFalse(DbHelper::tableExists('foobar'));
        $this->assertTrue(DbHelper::tableExists(Common::prefixTable('user_token_auth')));
        $this->assertFalse(DbHelper::tableExists(Common::prefixTable('user_t%oke%n_auth')));
    }

    public function test_getInstallVersion_isCurrentVersion()
    {
        $this->assertSame(Version::VERSION, DbHelper::getInstallVersion());
    }

    public function test_recordInstallVersion_setsCurrentVersion()
    {
        Option::delete(Db\Schema\Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION);
        $this->assertEmpty(DbHelper::getInstallVersion());
        $this->assertEquals('0', DbHelper::getInstallVersion()); // since php 8.1 this is required

        DbHelper::recordInstallVersion();
        $this->assertSame(Version::VERSION, DbHelper::getInstallVersion());
    }

    public function test_recordInstallVersion_doesNotOverwritePreviouslySetVersion()
    {
        $this->setInstallVersion('2.1.0');
        DbHelper::recordInstallVersion();
        DbHelper::recordInstallVersion();
        DbHelper::recordInstallVersion();
        $this->assertSame('2.1.0', DbHelper::getInstallVersion());
    }

    public function test_wasMatomoInstalledBeforeVersion_sameVersion()
    {
        $this->setInstallVersion('2.1.0');
        $this->assertFalse(DbHelper::wasMatomoInstalledBeforeVersion('2.1.0'));
    }

    public function test_wasMatomoInstalledBeforeVersion_whenUsedNewerVersion()
    {
        $this->setInstallVersion('2.1.0');
        $this->assertFalse(DbHelper::wasMatomoInstalledBeforeVersion('2.0.0'));
    }

    public function test_wasMatomoInstalledBeforeVersion_whenWasInstalledBeforeThatVersion()
    {
        $this->setInstallVersion('2.1.0');
        $this->assertTrue(DbHelper::wasMatomoInstalledBeforeVersion('2.2.0'));
    }

    private function setInstallVersion($version)
    {
        Option::set(Db\Schema\Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION, $version);
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
        self::assertTrue(in_array($this->cleanName($dbName), $dbs));
    }

    private function assertDbNotExists($dbName)
    {
        $dbs = Db::fetchAll("SHOW DATABASES");
        $dbs = array_column($dbs, 'Database');
        self::assertTrue(!in_array($this->cleanName($dbName), $dbs));
    }

    private function cleanName($dbName)
    {
        return str_replace('`', '', $dbName);
    }
}
