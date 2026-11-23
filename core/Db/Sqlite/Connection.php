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
     * The first builds of each maintained branch with the fix for the WAL-reset corruption bug: 3.44.6,
     * 3.50.7, then every release from 3.51.3.
     */
    private const WAL_RESET_FIX = ['3.44' => '3.44.6', '3.50' => '3.50.7', 'from' => '3.51.3'];

    /** The library is older than MINIMUM_VERSION. */
    public const RUNTIME_UNSUPPORTED = 'unsupported';

    /**
     * The library may lack the WAL-reset fix, and the administrator has not attested that their build
     * includes it (`[database] sqlite_wal_reset_fix_attested = 1`).
     */
    public const RUNTIME_UNQUALIFIED = 'unqualified';

    public const RUNTIME_QUALIFIED = 'qualified';

    /**
     * Whether a SQLite library version can run Matomo. A version string is never taken to include a fix
     * a distribution may have backported: only the attestation says so.
     */
    public static function getRuntimeQualification(string $version, bool $isWalResetFixAttested): string
    {
        if (version_compare($version, self::MINIMUM_VERSION, '<')) {
            return self::RUNTIME_UNSUPPORTED;
        }

        $branch = implode('.', array_slice(explode('.', $version), 0, 2));
        $fixed = isset(self::WAL_RESET_FIX[$branch])
            ? version_compare($version, self::WAL_RESET_FIX[$branch], '>=')
            : version_compare($version, self::WAL_RESET_FIX['from'], '>=');

        return $fixed || $isWalResetFixAttested ? self::RUNTIME_QUALIFIED : self::RUNTIME_UNQUALIFIED;
    }

    /**
     * The version of the SQLite library that pdo_sqlite uses, without a database.
     */
    public static function getLibraryVersion(): string
    {
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return (string) $pdo->query('SELECT sqlite_version()')->fetchColumn();
    }

    /**
     * Whether the library has SQLite's math functions (`FLOOR()` etc.), which Matomo's queries use. They are
     * built in unless the library was compiled without them.
     */
    public static function hasMathFunctions(): bool
    {
        try {
            $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            return (int) $pdo->query('SELECT floor(1.5)')->fetchColumn() === 1;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * How long a statement waits for another connection's write lock before failing, in milliseconds.
     * Can be changed with `[database] sqlite_busy_timeout`. In a benchmark with tracking, hourly archiving and
     * the raw-data deletion running together, the longest wait was 0.63 s.
     */
    public const DEFAULT_BUSY_TIMEOUT_MS = 5000;

    /**
     * The size the WAL file is truncated back to once checkpointed. SQLite otherwise keeps the file at its
     * largest: a long reader during a large deletion grew it to 1.9 GB, next to a 2.7 GB database.
     */
    public const JOURNAL_SIZE_LIMIT_BYTES = 64 * 1024 * 1024;

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
        $pdo->exec('PRAGMA journal_size_limit = ' . self::JOURNAL_SIZE_LIMIT_BYTES);
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
     * it: `PDO\SQLITE`, `PDO_SQLITE` or `PDO SQLITE`, in any case.
     */
    public static function isSqliteAdapter(string $adapter): bool
    {
        return str_replace(['_', '\\'], ' ', strtolower($adapter)) === 'pdo sqlite';
    }

    /**
     * Whether a connection object is one of the SQLite adapters. Code choosing SQL by engine asks the
     * connection it runs on, whatever the configuration file says.
     */
    public static function isSqliteConnection($db): bool
    {
        if ($db instanceof \Piwik\DataAccess\ArchivingDbAdapter) {
            $db = $db->getWrappedAdapter();
        }
        return $db instanceof \Piwik\Db\Adapter\Pdo\Sqlite || $db instanceof \Piwik\Tracker\Db\Pdo\Sqlite;
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
