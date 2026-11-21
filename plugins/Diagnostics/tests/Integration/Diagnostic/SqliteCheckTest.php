<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Diagnostics\tests\Integration\Diagnostic;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Db\Sqlite\Connection;
use Piwik\Option;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\SqliteCheck;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Diagnostics
 */
class SqliteCheckTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('Checks a SQLite database');
        }
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function testReportsTheDatabaseState(): void
    {
        $this->setAttested(true);

        [$result] = $this->runCheck();

        $this->assertSame(DiagnosticResult::STATUS_OK, $result->getStatus(), $this->describe($result));
        $this->assertStringContainsString('journal_mode = wal', $this->describe($result));
        $this->assertStringContainsString('synchronous = FULL', $this->describe($result));
    }

    public function testAnUnqualifiedLibraryIsAnErrorUntilAttested(): void
    {
        $version = (string) Db::fetchOne('SELECT sqlite_version()');
        if (Connection::getRuntimeQualification($version, false) !== Connection::RUNTIME_UNQUALIFIED) {
            $this->markTestSkipped("SQLite $version includes the WAL-reset fix");
        }
        $this->setAttested(false);

        [$result] = $this->runCheck();

        $this->assertSame(DiagnosticResult::STATUS_ERROR, $result->getStatus());
        $this->assertStringContainsString('sqlite_wal_reset_fix_attested = 1', $this->describe($result));
    }

    public function testWarnsAfterRepeatedIncompleteUnusedActionsPurges(): void
    {
        $this->setAttested(true);

        Option::set(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE, (string) (SqliteCheck::INCOMPLETE_PURGES_WARNING - 1));
        [$result] = $this->runCheck();
        $this->assertSame(DiagnosticResult::STATUS_OK, $result->getStatus());

        Option::set(PrivacyManager::OPTION_UNUSED_LOG_ACTIONS_PURGE_INCOMPLETE, (string) SqliteCheck::INCOMPLETE_PURGES_WARNING);
        [$result] = $this->runCheck();
        $this->assertSame(DiagnosticResult::STATUS_WARNING, $result->getStatus());
        $this->assertStringContainsString('Tasks.deleteLogData', $this->describe($result));
    }

    /**
     * @return DiagnosticResult[]
     */
    private function runCheck(): array
    {
        return StaticContainer::get(SqliteCheck::class)->execute();
    }

    private function setAttested(bool $isAttested): void
    {
        $database = Config::getInstance()->database;
        $database['sqlite_wal_reset_fix_attested'] = $isAttested ? 1 : 0;
        Config::getInstance()->database = $database;
    }

    private function describe(DiagnosticResult $result): string
    {
        return implode("\n", array_map(function ($item) {
            return $item->getStatus() . ': ' . $item->getComment();
        }, $result->getItems()));
    }
}
