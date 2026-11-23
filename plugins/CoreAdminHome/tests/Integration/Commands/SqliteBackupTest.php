<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration\Commands;

use PDO;
use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\Commands\SqliteBackup;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;

/**
 * @group CoreAdminHome
 */
class SqliteBackupTest extends ConsoleCommandTestCase
{
    /**
     * @var string
     */
    private $directory;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::isSqlite()) {
            $this->markTestSkipped('Backs up a SQLite database');
        }

        $this->directory = sys_get_temp_dir() . '/matomo-sqlite-backup-' . bin2hex(random_bytes(4));
        mkdir($this->directory, 0700);
    }

    public function tearDown(): void
    {
        if ($this->directory !== null && is_dir($this->directory)) {
            foreach (glob($this->directory . '/*') as $file) {
                unlink($file);
            }
            rmdir($this->directory);
        }

        parent::tearDown();
    }

    public function testBackupIsACompleteDatabaseInWalMode(): void
    {
        Db::query('INSERT INTO ' . Common::prefixTable('option') . " (option_name, option_value, autoload) VALUES ('backup_marker', 'kept', 0)");
        $file = $this->directory . '/matomo.sqlite';

        // a permissive umask: the copy must be private regardless
        $umask = umask(0);
        try {
            $this->assertSame(0, $this->runBackup(['file' => $file]), $this->getCommandDisplayOutputErrorMessage());
        } finally {
            umask($umask);
        }

        $copy = new PDO('sqlite:' . $file);
        $this->assertSame('wal', $copy->query('PRAGMA journal_mode')->fetchColumn());
        $this->assertSame('kept', $copy->query('SELECT option_value FROM ' . Common::prefixTable('option') . " WHERE option_name = 'backup_marker'")->fetchColumn());
        $copy = null;

        $this->assertSame([$file], glob($this->directory . '/*'), 'no temporary file is left');
        $this->assertSame(0600, fileperms($file) & 0777);
        $this->assertSame(0, $this->runBackup(['file' => $file, '--verify' => true]), $this->getCommandDisplayOutputErrorMessage());
    }

    public function testBackupDoesNotOverwriteAFile(): void
    {
        $file = $this->directory . '/existing';
        file_put_contents($file, 'mine');

        $this->assertNotEquals(0, $this->runBackup(['file' => $file]));

        $this->assertSame('mine', file_get_contents($file));
        $this->assertSame([$file], glob($this->directory . '/*'));
    }

    public function testPublishingNeverReplacesAFileCreatedDuringTheBackup(): void
    {
        $temporaryFile = $this->directory . '/copy.tmp';
        $file = $this->directory . '/target';
        file_put_contents($temporaryFile, 'copy');
        file_put_contents($file, 'created meanwhile');
        $publish = \Closure::bind(function (string $from, string $to) {
            $this->publish($from, $to);
        }, new SqliteBackup(), SqliteBackup::class);

        try {
            $publish($temporaryFile, $file);
            $this->fail('the existing file is kept');
        } catch (\Exception $e) {
            $this->assertStringContainsString('was created during the backup', $e->getMessage());
        }

        $this->assertSame('created meanwhile', file_get_contents($file));
    }

    public function testVerifyRefusesACopyNotInWalMode(): void
    {
        // what a plain VACUUM INTO gives
        $file = $this->directory . '/plain.sqlite';
        Db::query('VACUUM INTO ?', [$file]);

        $this->assertNotEquals(0, $this->runBackup(['file' => $file, '--verify' => true]));
        $this->assertStringContainsString('not in WAL mode', $this->applicationTester->getDisplay());
    }

    public function testVerifyRefusesAFileThatIsNotADatabase(): void
    {
        $file = $this->directory . '/garbage.sqlite';
        file_put_contents($file, str_repeat('not a database ', 100));

        $this->assertNotEquals(0, $this->runBackup(['file' => $file, '--verify' => true]));
    }

    private function runBackup(array $arguments): int
    {
        return $this->applicationTester->run(array_merge(['command' => 'database:sqlite-backup'], $arguments));
    }
}
