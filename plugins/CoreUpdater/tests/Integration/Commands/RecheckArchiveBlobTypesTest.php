<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Integration\Commands;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreUpdater
 * @group RecheckArchiveBlobTypesTest
 */
class RecheckArchiveBlobTypesTest extends ConsoleCommandTestCase
{
    private const TEST_TABLE_MEDIUM = 'archive_blob_test_recheck_medium';
    private const TEST_TABLE_LONG   = 'archive_blob_test_recheck_long';

    public function setUp(): void
    {
        parent::setUp();

        ArchiveBlobColumnType::clearCache();
        $this->dropTestTables();
        $this->clearFlag();
    }

    public function tearDown(): void
    {
        $this->dropTestTables();
        $this->clearFlag();
        ArchiveBlobColumnType::clearCache();

        parent::tearDown();
    }

    // -----------------------------------------------------------------------
    // State 1: flag = 0 (not set) → nothing to do
    // -----------------------------------------------------------------------

    public function testCommandPrintsNothingToDoWhenFlagIsNotSet(): void
    {
        $this->clearFlag();

        $exitCode = $this->applicationTester->run(['command' => 'core:recheck-archive-blob-types']);

        self::assertSame(0, $exitCode, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString('nothing to do', $this->applicationTester->getDisplay());
    }

    // -----------------------------------------------------------------------
    // State 2: flag = 1 + still MEDIUMBLOB tables → flag unchanged
    // -----------------------------------------------------------------------

    public function testCommandLeaveFlagWhenMediumBlobTablesStillExist(): void
    {
        $this->createTestTable(self::TEST_TABLE_MEDIUM, 'MEDIUMBLOB');
        $this->setFlag('1');

        $exitCode = $this->applicationTester->run(['command' => 'core:recheck-archive-blob-types']);

        self::assertSame(0, $exitCode, $this->getCommandDisplayOutputErrorMessage());

        // Flag must still be set.
        $flag = Config::getInstance()->database['archive_blob_tables_may_contain_mediumblob'] ?? null;
        self::assertSame('1', $flag);

        $output = $this->applicationTester->getDisplay();
        self::assertStringContainsString(Common::prefixTable(self::TEST_TABLE_MEDIUM), $output);
    }

    // -----------------------------------------------------------------------
    // State 3: flag = 1 + all tables are LONGBLOB → flag removed
    // -----------------------------------------------------------------------

    public function testCommandRemovesFlagWhenAllTablesAreLongBlob(): void
    {
        $this->createTestTable(self::TEST_TABLE_LONG, 'LONGBLOB');
        $this->setFlag('1');

        $exitCode = $this->applicationTester->run(['command' => 'core:recheck-archive-blob-types']);

        self::assertSame(0, $exitCode, $this->getCommandDisplayOutputErrorMessage());

        // Flag must have been removed from local config.
        $flag = Config::getInstance()->getFromLocalConfig('database')['archive_blob_tables_may_contain_mediumblob'] ?? null;
        self::assertNull($flag, 'Expected flag to be removed from config');

        $output = $this->applicationTester->getDisplay();
        self::assertStringContainsString('flag has been removed', $output);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function setFlag(string $value): void
    {
        $config = Config::getInstance();
        $database = $config->database;
        $database['archive_blob_tables_may_contain_mediumblob'] = $value;
        $config->database = $database;
        // We don't forceSave() here to avoid touching the real config file in tests.
        // The command reads Config::getInstance() directly, which already has the value in memory.
    }

    private function clearFlag(): void
    {
        $config = Config::getInstance();
        $database = $config->database;
        unset($database['archive_blob_tables_may_contain_mediumblob']);
        $config->database = $database;
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
