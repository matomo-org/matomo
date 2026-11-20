<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Db\Schema\Sqlite;

/**
 * MySQL's named locks (`GET_LOCK()`, `RELEASE_LOCK()`, `IS_FREE_LOCK()`) for SQLite, with `flock()` on
 * one file per name in the `<database file>-locks/` directory.
 *
 * As on MySQL, a lock belongs to a connection: the same connection may take it again, and must
 * release it as many times; another connection, even in the same process, waits for it. The locks of
 * a connection are released when the connection object is destroyed or the process exits, as MySQL
 * releases them on disconnect. The directory sits next to the database, never in `tmp/`, because a
 * lock file removed while held would let a second process take the lock.
 *
 * A lock file is removed on release. A process that opened it before then sees, once it holds the
 * lock, that the path no longer names its file, and starts over.
 *
 * @internal
 */
final class NamedLock
{
    private const MIN_WAIT_MICROSECONDS = 5000;
    private const MAX_WAIT_MICROSECONDS = 50000;

    /**
     * @var \WeakMap<object, array<string, array{handle: resource, file: string, count: int}>>|null
     */
    private static $held;

    /**
     * Takes the lock, waiting up to `$timeoutSeconds`. Returns false when the wait times out.
     */
    public static function acquire(object $connection, string $databaseFile, string $name, float $timeoutSeconds): bool
    {
        $held = self::getHeld();
        $locks = $held[$connection] ?? [];
        if (isset($locks[$name])) {
            $locks[$name]['count']++;
            $held[$connection] = $locks;
            return true;
        }

        $file = self::getLockFile($databaseFile, $name);
        $deadline = microtime(true) + max(0.0, $timeoutSeconds);
        $wait = self::MIN_WAIT_MICROSECONDS;

        while (true) {
            $handle = self::tryLock($file);
            if ($handle !== null) {
                $locks[$name] = ['handle' => $handle, 'file' => $file, 'count' => 1];
                $held[$connection] = $locks;
                return true;
            }

            $remaining = $deadline - microtime(true);
            if ($remaining <= 0) {
                return false;
            }

            usleep((int) min($wait, $remaining * 1000000));
            $wait = min($wait * 2, self::MAX_WAIT_MICROSECONDS);
        }
    }

    /**
     * Releases the lock once. Returns false if this connection doesn't hold it.
     */
    public static function release(object $connection, string $name): bool
    {
        $held = self::getHeld();
        $locks = $held[$connection] ?? [];
        if (!isset($locks[$name])) {
            return false;
        }

        $locks[$name]['count']--;
        if ($locks[$name]['count'] === 0) {
            self::unlock($locks[$name]['handle'], $locks[$name]['file']);
            unset($locks[$name]);
        }

        $held[$connection] = $locks;
        return true;
    }

    /**
     * Whether no connection holds the lock.
     */
    public static function isFree(object $connection, string $databaseFile, string $name): bool
    {
        $locks = self::getHeld()[$connection] ?? [];
        if (isset($locks[$name])) {
            return false;
        }

        $handle = @fopen(self::getLockFile($databaseFile, $name), 'r');
        if ($handle === false) {
            return true;
        }

        $free = flock($handle, LOCK_SH | LOCK_NB);
        fclose($handle);

        return $free;
    }

    /**
     * Releases every lock, as the end of the process would. For tests.
     */
    public static function releaseAll(): void
    {
        foreach (self::getHeld() as $locks) {
            foreach ($locks as $lock) {
                self::unlock($lock['handle'], $lock['file']);
            }
        }

        self::$held = null;
    }

    public static function getLockFile(string $databaseFile, string $name): string
    {
        return $databaseFile . '-locks' . DIRECTORY_SEPARATOR . hash('sha256', $name) . '.lock';
    }

    /**
     * @return resource|null the handle holding the lock, null if another connection holds it
     */
    private static function tryLock(string $file)
    {
        $directory = dirname($file);
        if (!is_dir($directory) && !@mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('SQLite: cannot create the lock directory %s.', $directory));
        }

        // a few attempts, for a file removed by its previous holder between our open and our lock
        for ($attempt = 0; $attempt < 100; $attempt++) {
            $handle = @fopen($file, 'c');
            if ($handle === false) {
                throw new \RuntimeException(sprintf('SQLite: cannot open the lock file %s.', $file));
            }

            if (!flock($handle, LOCK_EX | LOCK_NB)) {
                fclose($handle);
                return null;
            }

            clearstatcache(true, $file);
            $current = @stat($file);
            $own = fstat($handle);
            if ($current !== false && $own !== false && $current['ino'] === $own['ino'] && $current['dev'] === $own['dev']) {
                return $handle;
            }

            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return null;
    }

    /**
     * @param resource $handle
     */
    private static function unlock($handle, string $file): void
    {
        // removed while still locked, so that a waiter can tell its file was released (Windows keeps it)
        @unlink($file);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private static function getHeld(): \WeakMap
    {
        if (self::$held === null) {
            self::$held = new \WeakMap();
        }

        return self::$held;
    }
}
