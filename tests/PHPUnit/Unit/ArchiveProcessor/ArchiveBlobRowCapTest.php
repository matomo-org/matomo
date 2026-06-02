<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace PHPUnit\Unit\ArchiveProcessor;

use PHPUnit\Framework\TestCase;
use Piwik\ArchiveProcessor\ArchiveBlobRowCap;
use Piwik\Config;
use Piwik\DataAccess\ArchiveBlobColumnType;

/**
 * @group ArchiveBlobRowCapTest
 * @group Core
 */
class ArchiveBlobRowCapTest extends TestCase
{
    private const TABLE = 'matomo_archive_blob_2024_01';

    /**
     * @var mixed
     */
    private $originalDatabaseConfig;

    public function setUp(): void
    {
        parent::setUp();

        // Save original database config section and clear the flag.
        $this->originalDatabaseConfig = Config::getInstance()->database;
        $this->clearFlag();
        ArchiveBlobColumnType::clearCache();
    }

    public function tearDown(): void
    {
        // Restore original database config section.
        Config::getInstance()->database = $this->originalDatabaseConfig;
        ArchiveBlobColumnType::clearCache();

        parent::tearDown();
    }

    // -----------------------------------------------------------------------
    // isCapPossiblyNeeded
    // -----------------------------------------------------------------------

    public function testIsCapPossiblyNeededReturnsFalseWhenFlagUnset(): void
    {
        $this->setFlag(0);

        self::assertFalse(ArchiveBlobRowCap::isCapPossiblyNeeded());
    }

    public function testIsCapPossiblyNeededReturnsTrueWhenFlagSet(): void
    {
        $this->setFlag(1);

        self::assertTrue(ArchiveBlobRowCap::isCapPossiblyNeeded());
    }

    // -----------------------------------------------------------------------
    // Flag = 0 (unset) fast-path tests
    // -----------------------------------------------------------------------

    public function testCapMaxRowsFlagUnsetReturnsConfiguredValue(): void
    {
        $this->setFlag(0);

        self::assertSame(150000, ArchiveBlobRowCap::capMaxRows(150000, self::TABLE));
    }

    public function testCapMaxSubtableRowsFlagUnsetReturnsConfiguredValue(): void
    {
        $this->setFlag(0);

        self::assertSame(150000, ArchiveBlobRowCap::capMaxSubtableRows(150000, self::TABLE));
    }

    public function testCapMaxRowsFlagUnsetNullReturnsNull(): void
    {
        $this->setFlag(0);

        self::assertNull(ArchiveBlobRowCap::capMaxRows(null, self::TABLE));
    }

    // -----------------------------------------------------------------------
    // Flag = 1 + MEDIUMBLOB table
    // -----------------------------------------------------------------------

    public function testCapMaxRowsMediumBlobConfigured150000ReturnsCap(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(true);

        self::assertSame(50000, ArchiveBlobRowCap::capMaxRows(150000, self::TABLE));
    }

    public function testCapMaxRowsMediumBlobConfigured50000ReturnsUnchanged(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(true);

        // Already at or below trigger — no cap applied.
        self::assertSame(50000, ArchiveBlobRowCap::capMaxRows(50000, self::TABLE));
    }

    public function testCapMaxRowsMediumBlobJustAboveTriggerReturnsCap(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(true);

        self::assertSame(50000, ArchiveBlobRowCap::capMaxRows(100001, self::TABLE));
    }

    public function testCapMaxRowsMediumBlobAtTriggerReturnsUnchanged(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(true);

        // Exactly at the trigger boundary — should NOT be capped.
        self::assertSame(100000, ArchiveBlobRowCap::capMaxRows(100000, self::TABLE));
    }

    public function testCapMaxRowsMediumBlobNullReturnsCap(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(true);

        // null = unlimited — treated as exceeding trigger.
        self::assertSame(50000, ArchiveBlobRowCap::capMaxRows(null, self::TABLE));
    }

    // -----------------------------------------------------------------------
    // Flag = 1 + LONGBLOB table
    // -----------------------------------------------------------------------

    public function testCapMaxRowsLongBlobConfigured150000ReturnsUnchanged(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(false);

        self::assertSame(150000, ArchiveBlobRowCap::capMaxRows(150000, self::TABLE));
    }

    public function testCapMaxRowsLongBlobNullReturnsNull(): void
    {
        $this->setFlag(1);
        $this->mockIsMediumBlob(false);

        self::assertNull(ArchiveBlobRowCap::capMaxRows(null, self::TABLE));
    }

    // -----------------------------------------------------------------------
    // Fail-safe: DB error path → cap applied (MEDIUMBLOB fail-safe)
    // -----------------------------------------------------------------------

    public function testCapMaxRowsDBErrorReturnsCap(): void
    {
        $this->setFlag(1);
        // Prime the static cache with true (MEDIUMBLOB) to simulate the fail-safe result
        // that ArchiveBlobColumnType::isMediumBlob() would return after catching a DB exception.
        $this->primeCacheAsMediumBlob(self::TABLE);

        self::assertSame(50000, ArchiveBlobRowCap::capMaxRows(150000, self::TABLE));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function setFlag(int $value): void
    {
        $config = Config::getInstance();
        /** @var array<string, scalar> $database */
        $database = $config->database;
        if ($value === 0) {
            unset($database['archive_blob_tables_may_contain_mediumblob']);
        } else {
            $database['archive_blob_tables_may_contain_mediumblob'] = (string) $value;
        }
        $config->database = $database;
    }

    private function clearFlag(): void
    {
        $this->setFlag(0);
    }

    /**
     * Primes the ArchiveBlobColumnType static cache for $tableName so that isMediumBlob()
     * returns $result without hitting the DB.
     */
    private function mockIsMediumBlob(bool $result): void
    {
        // Use reflection to set the private static cache directly.
        $reflection = new \ReflectionClass(ArchiveBlobColumnType::class);
        $cacheProperty = $reflection->getProperty('cache');
        $cacheProperty->setAccessible(true);
        $cacheProperty->setValue(null, [self::TABLE => $result]);
    }

    private function primeCacheAsMediumBlob(string $tableName): void
    {
        $this->mockIsMediumBlob(true);
    }
}
