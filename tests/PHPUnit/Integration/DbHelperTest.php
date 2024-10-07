<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Segment;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

/**
 * @group Core
 * @group DbHelper
 */
class DbHelperTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DbHelper::dropDatabase('newdb; create database anotherdb;');
        DbHelper::dropDatabase('testdb');
    }

    public function testTableExists()
    {
        $this->assertFalse(DbHelper::tableExists('foobar'));
        $this->assertTrue(DbHelper::tableExists(Common::prefixTable('user_token_auth')));
        $this->assertFalse(DbHelper::tableExists(Common::prefixTable('user_t%oke%n_auth')));
    }

    public function testGetInstallVersionIsCurrentVersion()
    {
        $this->assertSame(Version::VERSION, DbHelper::getInstallVersion());
    }

    public function testRecordInstallVersionSetsCurrentVersion()
    {
        Option::delete(Db\Schema\Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION);
        $this->assertEmpty(DbHelper::getInstallVersion());
        $this->assertEquals('0', DbHelper::getInstallVersion()); // since php 8.1 this is required

        DbHelper::recordInstallVersion();
        $this->assertSame(Version::VERSION, DbHelper::getInstallVersion());
    }

    public function testRecordInstallVersionDoesNotOverwritePreviouslySetVersion()
    {
        $this->setInstallVersion('2.1.0');
        DbHelper::recordInstallVersion();
        DbHelper::recordInstallVersion();
        DbHelper::recordInstallVersion();
        $this->assertSame('2.1.0', DbHelper::getInstallVersion());
    }

    public function testWasMatomoInstalledBeforeVersionSameVersion()
    {
        $this->setInstallVersion('2.1.0');
        $this->assertFalse(DbHelper::wasMatomoInstalledBeforeVersion('2.1.0'));
    }

    public function testWasMatomoInstalledBeforeVersionWhenUsedNewerVersion()
    {
        $this->setInstallVersion('2.1.0');
        $this->assertFalse(DbHelper::wasMatomoInstalledBeforeVersion('2.0.0'));
    }

    public function testWasMatomoInstalledBeforeVersionWhenWasInstalledBeforeThatVersion()
    {
        $this->setInstallVersion('2.1.0');
        $this->assertTrue(DbHelper::wasMatomoInstalledBeforeVersion('2.2.0'));
    }

    private function setInstallVersion($version)
    {
        Option::set(Db\Schema\Mysql::OPTION_NAME_MATOMO_INSTALL_VERSION, $version);
    }

    public function testCreateDatabaseEscapesInputProperly()
    {
        $dbName = 'newdb`; create database anotherdb;`';
        DbHelper::createDatabase($dbName);

        $this->assertDbExists($dbName);
        $this->assertDbNotExists('anotherdb');
    }

    public function testDropDatabaseEscapesInputProperly()
    {
        DbHelper::createDatabase("testdb");
        $this->assertDbExists('testdb');

        DbHelper::dropDatabase('testdb`; create database anotherdb;`');
        $this->assertDbExists('testdb');
        $this->assertDbNotExists('anotherdb');
    }

    public function testAddOriginHintToQuery()
    {
        $expected = 'SELECT /* segmenthash 37d1b27c81afefbcf0961472b9abdb0f */ /* sites 1 */ /* 2022-01-01,2022-01-02 */ /* origin test */ idvisit FROM log_visit WHERE idvisit > 1 LIMIT 1';

        $segment = new Segment('countryCode==fr', [1]);
        $sql = "SELECT idvisit FROM " . Common::prefixTable('log_visit') . " WHERE idvisit > 1 LIMIT 1";
        $startDate = Date::factory('2022-01-01 00:00:00');
        $endDate = Date::factory('2022-01-02 23:59:59');
        $sites = [1];

        $result = DbHelper::addOriginHintToQuery($sql, 'origin test', $startDate, $endDate, $sites, $segment);
        self::assertEquals($expected, $result);
    }

    public function testGetDefaultCollationForCharset(): void
    {
        $charset = 'utf8mb4';
        $collation = DbHelper::getDefaultCollationForCharset($charset);
        $expectedPrefix = $charset . '_';

        // exact collation depends on the database used
        // but should always start with the charset
        self::assertStringStartsWith($expectedPrefix, $collation);
    }

    public function testGetDefaultCollationForCharsetWithoutDefault(): void
    {
        self::assertSame('', DbHelper::getDefaultCollationForCharset('invalid'));
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
