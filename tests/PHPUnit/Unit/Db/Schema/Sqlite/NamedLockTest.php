<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Schema\Sqlite;

use PHPUnit\Framework\TestCase;
use Piwik\Db\Schema\Sqlite\NamedLock;

/**
 * MySQL's named-lock semantics on SQLite: per connection, counted, released with the connection.
 */
class NamedLockTest extends TestCase
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $databaseFile;

    public function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/matomo-namedlock-' . bin2hex(random_bytes(6));
        mkdir($this->directory);
        $this->databaseFile = $this->directory . '/matomo.db';
    }

    public function tearDown(): void
    {
        NamedLock::releaseAll();
        foreach (glob($this->directory . '/matomo.db-locks/*') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($this->directory . '/matomo.db-locks');
        rmdir($this->directory);
        parent::tearDown();
    }

    public function testAnotherConnectionWaitsForTheLock(): void
    {
        $first = new \stdClass();
        $second = new \stdClass();

        $this->assertTrue(NamedLock::isFree($second, $this->databaseFile, 'archive'));
        $this->assertTrue(NamedLock::acquire($first, $this->databaseFile, 'archive', 0));
        $this->assertFalse(NamedLock::isFree($first, $this->databaseFile, 'archive'));
        $this->assertFalse(NamedLock::isFree($second, $this->databaseFile, 'archive'));

        $start = microtime(true);
        $this->assertFalse(NamedLock::acquire($second, $this->databaseFile, 'archive', 1));
        $this->assertGreaterThanOrEqual(0.9, microtime(true) - $start);

        $this->assertTrue(NamedLock::acquire($second, $this->databaseFile, 'other', 0));
        $this->assertFalse(NamedLock::release($second, 'archive'));

        $this->assertTrue(NamedLock::release($first, 'archive'));
        $this->assertTrue(NamedLock::isFree($second, $this->databaseFile, 'archive'));
        $this->assertTrue(NamedLock::acquire($second, $this->databaseFile, 'archive', 0));
    }

    public function testTheSameConnectionTakesTheLockAgainAndReleasesItAsManyTimes(): void
    {
        $first = new \stdClass();
        $second = new \stdClass();

        $this->assertTrue(NamedLock::acquire($first, $this->databaseFile, 'archive', 0));
        $this->assertTrue(NamedLock::acquire($first, $this->databaseFile, 'archive', 0));

        $this->assertTrue(NamedLock::release($first, 'archive'));
        $this->assertFalse(NamedLock::acquire($second, $this->databaseFile, 'archive', 0));

        $this->assertTrue(NamedLock::release($first, 'archive'));
        $this->assertFalse(NamedLock::release($first, 'archive'));
        $this->assertTrue(NamedLock::acquire($second, $this->databaseFile, 'archive', 0));
    }

    public function testLocksAreReleasedWithTheConnection(): void
    {
        $first = new \stdClass();
        $second = new \stdClass();

        $this->assertTrue(NamedLock::acquire($first, $this->databaseFile, 'archive', 0));
        unset($first);
        gc_collect_cycles();

        $this->assertTrue(NamedLock::isFree($second, $this->databaseFile, 'archive'));
        $this->assertTrue(NamedLock::acquire($second, $this->databaseFile, 'archive', 0));
    }

    public function testTheLockFileIsRemovedOnRelease(): void
    {
        $connection = new \stdClass();
        $file = NamedLock::getLockFile($this->databaseFile, 'archive');

        $this->assertTrue(NamedLock::acquire($connection, $this->databaseFile, 'archive', 0));
        $this->assertFileExists($file);
        $this->assertSame($this->databaseFile . '-locks', dirname($file));

        $this->assertTrue(NamedLock::release($connection, 'archive'));
        if (DIRECTORY_SEPARATOR === '/') {
            $this->assertFileDoesNotExist($file);
        }
    }

    public function testAnotherProcessHoldsTheLockUntilItExits(): void
    {
        $code = 'require $argv[1]; $c = new stdClass();'
            . ' echo Piwik\Db\Schema\Sqlite\NamedLock::acquire($c, $argv[2], "archive", 0) ? "held\n" : "busy\n";'
            . ' fgets(STDIN);';
        $process = proc_open(
            [PHP_BINARY, '-r', $code, dirname(__DIR__, 6) . '/core/Db/Schema/Sqlite/NamedLock.php', $this->databaseFile],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w']],
            $pipes
        );
        $this->assertIsResource($process);

        try {
            $this->assertSame("held\n", fgets($pipes[1]));

            $connection = new \stdClass();
            $this->assertFalse(NamedLock::isFree($connection, $this->databaseFile, 'archive'));
            $this->assertFalse(NamedLock::acquire($connection, $this->databaseFile, 'archive', 0));
        } finally {
            fclose($pipes[0]);
            fclose($pipes[1]);
            proc_close($process);
        }

        $this->assertTrue(NamedLock::acquire($connection, $this->databaseFile, 'archive', 0));
    }
}
