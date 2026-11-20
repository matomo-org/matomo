<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Sqlite;

use Exception;
use PDO;

/**
 * Opens and configures SQLite connections. Shared by the Matomo adapter (`Piwik\Db\Adapter\Pdo\Sqlite`)
 * and the tracker adapter (`Piwik\Tracker\Db\Pdo\Sqlite`), so both connect the same way.
 *
 * The database is the file named by the `dbname` setting. A connection never creates it: the
 * file is created by {@link createDatabaseFile()}, which also switches it to WAL mode. Each
 * connection then sets `synchronous=FULL` and a busy timeout.
 *
 * @internal
 */
final class Connection
{
    /**
     * Minimum SQLite library version: window functions with `FILTER`, `RETURNING`, `DROP COLUMN`,
     * `group_concat(... ORDER BY ...)` (3.44).
     */
    public const MINIMUM_VERSION = '3.44.0';

    /**
     * How long a statement waits for another connection's write lock before failing, in milliseconds.
     * Can be changed with `[database] sqlite_busy_timeout`. Provisional, to be set from measurements.
     */
    public const DEFAULT_BUSY_TIMEOUT_MS = 5000;

    /**
     * Returns the absolute path of the database file of a `[database]`-style configuration.
     * A relative path is resolved against Matomo's user path (the directory holding `config/`).
     */
    public static function getDatabaseFile(array $config): string
    {
        $file = trim((string) ($config['dbname'] ?? ''));
        if ($file === '' || $file === ':memory:' || str_starts_with($file, 'file:')) {
            throw new Exception('SQLite: [database] dbname must be the path of the database file.');
        }

        if (!self::isAbsolutePath($file)) {
            $file = rtrim(PIWIK_USER_PATH, '/\\') . DIRECTORY_SEPARATOR . $file;
        }

        return $file;
    }

    public static function getDsn(string $file): string
    {
        return 'sqlite:' . $file;
    }

    /**
     * PDO options for opening an existing database file without creating it.
     */
    public static function getDriverOptions(): array
    {
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        // SQLITE_OPEN_READWRITE without SQLITE_OPEN_CREATE: a wrong path fails instead of creating an empty database
        $options[self::getConstant('ATTR_OPEN_FLAGS')] = self::getConstant('OPEN_READWRITE');

        return $options;
    }

    /**
     * Opens a configured connection to an existing database file.
     */
    public static function open(array $config): PDO
    {
        $file = self::getDatabaseFile($config);
        if (!is_file($file)) {
            throw new Exception(sprintf('SQLite: the database file %s does not exist.', $file));
        }

        $pdo = new PDO(self::getDsn($file), null, null, self::getDriverOptions());
        self::configure($pdo, $config);

        return $pdo;
    }

    /**
     * Applies the per-connection settings. Must run before the connection is used.
     */
    public static function configure(PDO $pdo, array $config): void
    {
        // extended result codes stay off: with them, constraint violations lose their SQLSTATE 23000,
        // by which Matomo recognizes duplicate keys, as on MySQL
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $busyTimeout = (int) ($config['sqlite_busy_timeout'] ?? self::DEFAULT_BUSY_TIMEOUT_MS);
        $pdo->exec('PRAGMA busy_timeout = ' . max(0, $busyTimeout));
        $pdo->exec('PRAGMA synchronous = FULL');
    }

    /**
     * Creates the database file if it doesn't exist and makes sure it uses WAL mode.
     *
     * @throws Exception if the file can't be created or doesn't accept WAL mode, e.g. on a network
     *                   file system without shared memory support
     */
    public static function createDatabaseFile(array $config): void
    {
        $file = self::getDatabaseFile($config);
        $directory = dirname($file);
        if (!is_dir($directory)) {
            throw new Exception(sprintf('SQLite: the directory %s of the database file does not exist.', $directory));
        }

        $pdo = new PDO(self::getDsn($file), null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $mode = $pdo->query('PRAGMA journal_mode = WAL')->fetchColumn();
        if (strtolower((string) $mode) !== 'wal') {
            throw new Exception(sprintf('SQLite: the database file %s can\'t use WAL mode (journal mode is %s).', $file, $mode));
        }
    }

    /**
     * Deletes the database file and its WAL and shared-memory files.
     */
    public static function deleteDatabaseFile(array $config): void
    {
        $file = self::getDatabaseFile($config);
        foreach (['', '-wal', '-shm', '-journal'] as $suffix) {
            if (is_file($file . $suffix)) {
                unlink($file . $suffix);
            }
        }
    }

    /**
     * Whether an `adapter` setting names the SQLite adapter, spelled as the adapter factories accept
     * it: `PDO\SQLITE` or `PDO_SQLITE`, in any case.
     */
    public static function isSqliteAdapter(string $adapter): bool
    {
        return strtolower(str_replace('_', '\\', $adapter)) === 'pdo\\sqlite';
    }

    /**
     * Binds the parameters of a statement with the types of their PHP values.
     *
     * PDO binds parameters as text by default. Unlike MySQL, SQLite then compares them as text with
     * anything but a column of numeric affinity, so `modified + lifetime >= ?` bound to `time()` is
     * never true. Integers, booleans and nulls are therefore bound as such; other values stay text.
     *
     * @param array $parameters positional (in order, whatever their keys) or named parameters
     */
    public static function bindValues(\PDOStatement $statement, array $parameters): void
    {
        $position = 0;
        foreach ($parameters as $key => $value) {
            if (is_bool($value)) {
                $value = (int) $value;
            }

            if ($value === null) {
                $type = PDO::PARAM_NULL;
            } elseif (is_int($value)) {
                $type = PDO::PARAM_INT;
            } else {
                $type = PDO::PARAM_STR;
            }

            $statement->bindValue(is_string($key) ? $key : ++$position, $value, $type);
        }
    }

    public static function isEnabled(): bool
    {
        return extension_loaded('pdo_sqlite') && in_array('sqlite', PDO::getAvailableDrivers(), true);
    }

    /**
     * Returns a pdo_sqlite constant: `Pdo\Sqlite::<name>` from PHP 8.4, `PDO::SQLITE_<name>` before.
     */
    private static function getConstant(string $name): int
    {
        return constant(defined('Pdo\Sqlite::' . $name) ? 'Pdo\Sqlite::' . $name : 'PDO::SQLITE_' . $name);
    }

    private static function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || str_starts_with($path, '\\') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $path) === 1;
    }
}
