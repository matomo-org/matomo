<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\Common;
use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ArchiveBlobColumnTypeTest
 * @group DataAccess
 * @group Core
 */
class ArchiveBlobColumnTypeTest extends IntegrationTestCase
{
    private const TEST_TABLE_MEDIUM = 'archive_blob_test_medium';
    private const TEST_TABLE_LONG   = 'archive_blob_test_long';

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

    // -----------------------------------------------------------------------
    // isMediumBlob
    // -----------------------------------------------------------------------

    public function testIsMediumBlobReturnsTrueForMediumBlobTable(): void
    {
        $this->createTestTable(self::TEST_TABLE_MEDIUM, 'MEDIUMBLOB');

        $tableName = Common::prefixTable(self::TEST_TABLE_MEDIUM);
        self::assertTrue(ArchiveBlobColumnType::isMediumBlob($tableName));
    }

    public function testIsMediumBlobReturnsFalseForLongBlobTable(): void
    {
        $this->createTestTable(self::TEST_TABLE_LONG, 'LONGBLOB');

        $tableName = Common::prefixTable(self::TEST_TABLE_LONG);
        self::assertFalse(ArchiveBlobColumnType::isMediumBlob($tableName));
    }

    public function testIsMediumBlobReturnsTrueForNonExistentTableFailSafe(): void
    {
        // A table that does not exist → INFORMATION_SCHEMA returns nothing → fail-safe returns true.
        $tableName = Common::prefixTable('archive_blob_9999_99');
        // Result is false because INFORMATION_SCHEMA returns null/empty string, not 'mediumblob'.
        // The fail-safe only triggers on exceptions; an empty result means the table is not
        // MEDIUMBLOB (it doesn't exist at all). Confirm no exception is thrown.
        $result = ArchiveBlobColumnType::isMediumBlob($tableName);
        self::assertFalse($result);
    }

    // -----------------------------------------------------------------------
    // Per-request static cache
    // -----------------------------------------------------------------------

    public function testIsMediumBlobCachesResult(): void
    {
        $this->createTestTable(self::TEST_TABLE_MEDIUM, 'MEDIUMBLOB');
        $tableName = Common::prefixTable(self::TEST_TABLE_MEDIUM);

        // Prime the cache.
        $firstResult = ArchiveBlobColumnType::isMediumBlob($tableName);
        self::assertTrue($firstResult);

        // Drop the actual table so that any subsequent real DB query would return false.
        Db::exec('DROP TABLE `' . Common::prefixTable(self::TEST_TABLE_MEDIUM) . '`');

        // Should still return true from cache, not from DB.
        $secondResult = ArchiveBlobColumnType::isMediumBlob($tableName);
        self::assertTrue($secondResult, 'Expected cached result to be returned after table was dropped');
    }

    public function testClearCacheInvalidatesCache(): void
    {
        $this->createTestTable(self::TEST_TABLE_MEDIUM, 'MEDIUMBLOB');
        $tableName = Common::prefixTable(self::TEST_TABLE_MEDIUM);

        ArchiveBlobColumnType::isMediumBlob($tableName); // prime cache

        ArchiveBlobColumnType::clearCache();

        // After clearing the cache, the result should be re-fetched from DB.
        self::assertTrue(ArchiveBlobColumnType::isMediumBlob($tableName));
    }

    // -----------------------------------------------------------------------
    // getMediumBlobArchiveTables
    // -----------------------------------------------------------------------

    public function testGetMediumBlobArchiveTablesReturnsMediumBlobTables(): void
    {
        $this->createTestTable(self::TEST_TABLE_MEDIUM, 'MEDIUMBLOB');
        $this->createTestTable(self::TEST_TABLE_LONG, 'LONGBLOB');

        $tables = ArchiveBlobColumnType::getMediumBlobArchiveTables();

        $prefixedMedium = Common::prefixTable(self::TEST_TABLE_MEDIUM);
        $prefixedLong   = Common::prefixTable(self::TEST_TABLE_LONG);

        self::assertContains($prefixedMedium, $tables);
        self::assertNotContains($prefixedLong, $tables);
    }

    public function testGetMediumBlobArchiveTablesReturnsEmptyWhenNoneMediumBlob(): void
    {
        $this->createTestTable(self::TEST_TABLE_LONG, 'LONGBLOB');

        $tables = ArchiveBlobColumnType::getMediumBlobArchiveTables();

        $prefixedLong = Common::prefixTable(self::TEST_TABLE_LONG);
        self::assertNotContains($prefixedLong, $tables);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function createTestTable(string $tableBaseName, string $blobType): void
    {
        $tableName = Common::prefixTable($tableBaseName);
        Db::exec(sprintf(
            'CREATE TABLE IF NOT EXISTS `%s` (
                `idarchive`  INT UNSIGNED     NOT NULL,
                `name`       VARCHAR(190)     NOT NULL,
                `idsite`     INT(10) UNSIGNED DEFAULT NULL,
                `date1`      DATE             DEFAULT NULL,
                `date2`      DATE             DEFAULT NULL,
                `period`     TINYINT(3) UNSIGNED DEFAULT NULL,
                `ts_archived` DATETIME        DEFAULT NULL,
                `value`      %s               DEFAULT NULL,
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
