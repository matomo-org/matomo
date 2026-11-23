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
use Piwik\Db\Sqlite\ExposureProbe;
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

        // the probe itself is tested by ExposureProbeTest; the test web server serves every file
        $this->setProbedStatus(403);
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

    public function testReportsAServedDatabase(): void
    {
        $this->setAttested(true);
        $this->setProbedStatus(200);

        [$result] = $this->runCheck();

        $this->assertSame(DiagnosticResult::STATUS_ERROR, $result->getStatus());
        $this->assertStringContainsString('anyone can download', $this->describe($result));
        $this->assertStringContainsString('.db-wal: exposed', $this->describe($result));
    }

    public function testReportsADatabaseThatCanNotBeProvenProtected(): void
    {
        $this->setAttested(true);
        $this->setProbedStatus(500);

        [$result] = $this->runCheck();

        $this->assertSame(DiagnosticResult::STATUS_ERROR, $result->getStatus());
        $this->assertStringContainsString('401, 403 or 404', $this->describe($result));
    }

    public function testWarnsWhenTheUrlDoesNotServeMatomo(): void
    {
        $this->setAttested(true);
        // e.g. a wrong URL that answers 404 to everything
        $this->setProbedStatus(null);

        [$result] = $this->runCheck();

        $this->assertSame(DiagnosticResult::STATUS_WARNING, $result->getStatus());
        $this->assertStringContainsString('could not check', $this->describe($result));
    }

    /**
     * Makes every probed URL answer with a status; a 2xx serves the file. Matomo's matomo.js is always served,
     * unless the status is null.
     */
    private function setProbedStatus(?int $status): void
    {
        StaticContainer::getContainer()->set(ExposureProbe::class, new ExposureProbe(function (string $url) use ($status) {
            if ($status !== null && str_ends_with($url, '/matomo.js')) {
                return ['status' => 200, 'body' => (string) file_get_contents(PIWIK_INCLUDE_PATH . '/matomo.js', false, null, 0, 64)];
            }
            $status = $status ?? 404;
            $body = '';
            if ($status === 200) {
                $file = Connection::getDatabaseFile(Config::getInstance()->database);
                $body = (string) file_get_contents(dirname($file) . '/' . rawurldecode(basename($url)));
            }
            return ['status' => $status, 'body' => $body];
        }));
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
