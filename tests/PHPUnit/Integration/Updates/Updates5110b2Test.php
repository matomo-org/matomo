<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updater\Migration\Config\Factory as ConfigFactory;
use Piwik\Updater\Migration\Db\Factory as DbFactory;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updater\Migration\Plugin\Factory as PluginFactory;
use Piwik\Updates\Updates_5_11_0_b2;

require_once PIWIK_INCLUDE_PATH . '/core/Updates/5.11.0-b2.php';

/**
 * @group Updates5110b2Test
 * @group Core
 */
class Updates5110B2Test extends IntegrationTestCase
{
    private const TEST_TABLE_MEDIUM = 'archive_blob_test_5110_medium';
    private const TEST_TABLE_LONG = 'archive_blob_test_5110_long';

    public function setUp(): void
    {
        parent::setUp();

        ArchiveBlobColumnType::clearCache();
        $this->dropTestTables();
    }

    public function tearDown(): void
    {
        $this->dropTestTables();
        ArchiveBlobColumnType::clearCache();

        parent::tearDown();
    }

    public function testGetMigrationsReturnsMigrationWhenMediumBlobTablesExist(): void
    {
        $this->createTestTable(self::TEST_TABLE_MEDIUM, 'MEDIUMBLOB');

        $migrations = $this->buildUpdate()->getMigrations($this->createMock(Updater::class));

        self::assertCount(1, $migrations);
        self::assertStringContainsString('archive_blob_tables_may_contain_mediumblob', (string) $migrations[0]);
    }

    public function testGetMigrationsReturnsEmptyWhenNoMediumBlobTables(): void
    {
        $this->createTestTable(self::TEST_TABLE_LONG, 'LONGBLOB');

        $migrations = $this->buildUpdate()->getMigrations($this->createMock(Updater::class));

        self::assertSame([], $migrations);
    }

    public function testGetMigrationsReturnsEmptyWhenNoArchiveBlobTablesExist(): void
    {
        // No archive_blob tables at all → no migration needed.
        $migrations = $this->buildUpdate()->getMigrations($this->createMock(Updater::class));

        self::assertSame([], $migrations);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function buildUpdate(): Updates_5_11_0_b2
    {
        $migrationFactory = new MigrationFactory(
            $this->getMockBuilder(DbFactory::class)->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder(PluginFactory::class)->disableOriginalConstructor()->getMock(),
            new ConfigFactory()
        );
        return new Updates_5_11_0_b2($migrationFactory);
    }

    private function createTestTable(string $tableBaseName, string $blobType): void
    {
        $tableName = Common::prefixTable($tableBaseName);
        Db::exec(sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (
                `idarchive`   INT UNSIGNED     NOT NULL,
                `name`        VARCHAR(190)     NOT NULL,
                `idsite`      INT(10) UNSIGNED DEFAULT NULL,
                `date1`       DATE             DEFAULT NULL,
                `date2`       DATE             DEFAULT NULL,
                `period`      TINYINT(3) UNSIGNED DEFAULT NULL,
                `ts_archived` DATETIME         DEFAULT NULL,
                `value`       %s               DEFAULT NULL,
                PRIMARY KEY (`idarchive`, `name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
            $tableName,
            $blobType
        ));
    }

    private function dropTestTables(): void
    {
        foreach ([self::TEST_TABLE_MEDIUM, self::TEST_TABLE_LONG] as $table) {
            try {
                Db::exec('DROP TABLE IF EXISTS `' . Common::prefixTable($table) . '`');
            } catch (\Exception $e) {
                // Ignore errors during cleanup.
            }
        }
    }
}
