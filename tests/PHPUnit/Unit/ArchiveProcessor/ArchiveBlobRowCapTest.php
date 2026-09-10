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
    private const MAX_ROWS = 100000;

    /**
     * @var mixed
     */
    private $originalDatabaseConfig;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalDatabaseConfig = Config::getInstance()->database;
        $this->setFlag(0);
        ArchiveBlobColumnType::clearCache();
        $this->resetLoggedTables();
    }

    public function tearDown(): void
    {
        Config::getInstance()->database = $this->originalDatabaseConfig;
        ArchiveBlobColumnType::clearCache();
        $this->resetLoggedTables();

        parent::tearDown();
    }

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

    public function testCapMaxRowsFlagUnsetReturnsConfiguredValueWithoutQueryingColumnType(): void
    {
        $this->setFlag(0);
        // No cache entry, so a lookup would hit the DB and fail this unit test.
        self::assertSame(150000, ArchiveBlobRowCap::capMaxRows(150000, self::TABLE));
    }

    /**
     * @dataProvider getUncappedLimits
     */
    public function testCapMaxRowsMediumBlobLeavesLimitsAtOrBelowMaxUnchanged(?int $configured): void
    {
        $this->setFlag(1);
        $this->cacheColumnTypeAsMediumBlob(true);

        self::assertSame($configured, ArchiveBlobRowCap::capMaxRows($configured, self::TABLE));
    }

    public function getUncappedLimits(): array
    {
        return [
            'null means no row limit, and stays that way' => [null],
            'zero means no row limit, and stays that way' => [0],
            'well below the maximum' => [500],
            'exactly at the maximum' => [self::MAX_ROWS],
        ];
    }

    /**
     * @dataProvider getCappedLimits
     */
    public function testCapMaxRowsMediumBlobReducesLimitsAboveMax(int $configured): void
    {
        $this->setFlag(1);
        $this->cacheColumnTypeAsMediumBlob(true);

        self::assertSame(self::MAX_ROWS, ArchiveBlobRowCap::capMaxRows($configured, self::TABLE));
    }

    public function getCappedLimits(): array
    {
        return [
            'just above the maximum' => [self::MAX_ROWS + 1],
            'the flat actions limit an operator might configure' => [500000],
        ];
    }

    /**
     * A higher configured limit must never result in fewer stored rows.
     */
    public function testCapMaxRowsIsMonotonicAroundTheMaximum(): void
    {
        $this->setFlag(1);
        $this->cacheColumnTypeAsMediumBlob(true);

        $atMax = ArchiveBlobRowCap::capMaxRows(self::MAX_ROWS, self::TABLE);
        $aboveMax = ArchiveBlobRowCap::capMaxRows(self::MAX_ROWS + 1, self::TABLE);

        self::assertGreaterThanOrEqual($atMax, $aboveMax);
    }

    public function testCapMaxRowsLongBlobReturnsConfiguredValue(): void
    {
        $this->setFlag(1);
        $this->cacheColumnTypeAsMediumBlob(false);

        self::assertSame(150000, ArchiveBlobRowCap::capMaxRows(150000, self::TABLE));
    }

    private function setFlag(int $value): void
    {
        $config = Config::getInstance();
        /** @var array<string, scalar> $database */
        $database = $config->database;
        if ($value === 0) {
            unset($database[ArchiveBlobColumnType::CONFIG_KEY]);
        } else {
            $database[ArchiveBlobColumnType::CONFIG_KEY] = (string) $value;
        }
        $config->database = $database;
    }

    /**
     * Seeds the column-type cache so isMediumBlob() answers without touching the DB.
     */
    private function cacheColumnTypeAsMediumBlob(bool $isMediumBlob): void
    {
        $cache = new \ReflectionProperty(ArchiveBlobColumnType::class, 'cache');
        $cache->setAccessible(true);
        $cache->setValue(null, [self::TABLE => $isMediumBlob]);
    }

    private function resetLoggedTables(): void
    {
        $logged = new \ReflectionProperty(ArchiveBlobRowCap::class, 'logged');
        $logged->setAccessible(true);
        $logged->setValue(null, []);
    }
}
