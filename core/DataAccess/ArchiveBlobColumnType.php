<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Log\LoggerInterface;

/**
 * Detects whether a given archive_blob table stores its `value` column as MEDIUMBLOB or LONGBLOB
 * by querying INFORMATION_SCHEMA.COLUMNS.
 *
 * Results are cached in a static array keyed by table name for the duration of the request so
 * that each table is only looked up once.
 *
 * On any DB error (missing permissions, unexpected schema, etc.) the detector returns `true`
 * (treat as MEDIUMBLOB) as a fail-safe: it is safer to apply the row-limit cap unnecessarily
 * than to allow a blob that exceeds 16 MB to be silently truncated.
 */
final class ArchiveBlobColumnType
{
    /**
     * The [database] config key used to signal that archive_blob tables may still contain
     * MEDIUMBLOB `value` columns (set by the 5.10.0-b1 migration on existing installs).
     */
    public const CONFIG_KEY = 'archive_blob_tables_may_contain_mediumblob';

    /**
     * Per-request cache: table name → isMediumBlob result.
     *
     * @var array<string, bool>
     */
    private static $cache = [];

    /**
     * Returns `true` when the `value` column of `$tableName` is MEDIUMBLOB, `false` when it is
     * LONGBLOB (or any other wider type). On any INFORMATION_SCHEMA query failure returns `true`
     * as a fail-safe so the row-limit cap is applied.
     */
    public static function isMediumBlob(string $tableName): bool
    {
        if (array_key_exists($tableName, self::$cache)) {
            return self::$cache[$tableName];
        }

        try {
            $columnType = Db::fetchOne(
                'SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME   = ?
                    AND COLUMN_NAME  = ?',
                [$tableName, 'value']
            );

            $normalized = strtolower((string) $columnType);
            if ($normalized === '') {
                // INFORMATION_SCHEMA returned no row: the table or column is missing.
                // This is unexpected (callers create the table before calling us) and
                // indicates a race condition or schema corruption.  Apply the cap conservatively.
                StaticContainer::get(LoggerInterface::class)->warning(
                    'ArchiveBlobColumnType: INFORMATION_SCHEMA returned no row for table {table}; applying cap conservatively.',
                    ['table' => $tableName]
                );
                $result = true;
            } else {
                // MySQL returns the type in lowercase ('mediumblob', 'longblob'); use
                // case-insensitive comparison so we are not surprised by unusual drivers.
                $result = ($normalized === 'mediumblob');
            }
        } catch (\Exception $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'ArchiveBlobColumnType: could not determine column type for table {table}: {exception}',
                ['table' => $tableName, 'exception' => $e]
            );
            // Fail-safe: treat as MEDIUMBLOB so the cap is applied.
            $result = true;
        }

        self::$cache[$tableName] = $result;
        return $result;
    }

    /**
     * Clears the per-request static cache.  Useful in tests.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Checks whether any archive_blob_* tables in the current schema still use MEDIUMBLOB for
     * their `value` column.  If none remain, the
     * `[database] archive_blob_tables_may_contain_mediumblob` ({@see CONFIG_KEY}) config flag is
     * removed.
     *
     * If the flag is not set (0 / unset) this method returns immediately without any I/O so that
     * fresh installs pay zero runtime cost during updates.
     *
     * Used by:
     *  - `core/Updater.php` after each component update finishes and at the end of a full batch.
     *  - `plugins/CoreUpdater/Commands/RecheckArchiveBlobTypes.php` as an on-demand CLI tool.
     *
     * @return string[]  Names of archive_blob tables that still use MEDIUMBLOB.
     *                   An empty array means no MEDIUMBLOB tables remain and the flag was cleared.
     *                   A non-empty array means the flag was left as-is.
     *                   Returns an empty array (without I/O) when the flag was not set.
     */
    public static function recheckAndUpdateFlag(): array
    {
        $flag = (int) (Config::getInstance()->database[self::CONFIG_KEY] ?? 0);
        if ($flag === 0) {
            // Nothing to do on fresh installs or after the flag was already cleared.
            return [];
        }

        $mediumBlobTables = self::getMediumBlobArchiveTables();
        if (empty($mediumBlobTables)) {
            // All tables have been migrated (or none existed). Remove the flag.
            $config = Config::getInstance();
            $database = $config->database;
            unset($database[self::CONFIG_KEY]);
            $config->database = $database;
            $config->forceSave();
        }

        return $mediumBlobTables;
    }

    /**
     * Returns the names of all archive_blob_* tables in the current schema whose `value` column
     * is MEDIUMBLOB.  Only tables whose name begins with the configured Matomo table prefix are
     * inspected, so tables belonging to other Matomo instances (or unrelated tables that happen to
     * contain "archive_blob_" in their name) are never returned.
     *
     * @return string[]
     */
    public static function getMediumBlobArchiveTables(): array
    {
        // Build a prefix-anchored LIKE pattern, e.g. "matomo_archive\_blob\_%".
        // LIKE-escape any '%' or '_' in the prefix itself so a weird table-prefix cannot
        // accidentally broaden the match.
        $rawPrefix = Common::prefixTable('archive_blob_');
        $likePrefix = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $rawPrefix);
        $likePattern = $likePrefix . '%';

        try {
            $rows = Db::fetchAll(
                "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME   LIKE ?
                    AND COLUMN_NAME  = ?
                    AND LOWER(COLUMN_TYPE) = ?",
                [$likePattern, 'value', 'mediumblob']
            );
        } catch (\Exception $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'ArchiveBlobColumnType: could not query INFORMATION_SCHEMA for archive_blob tables: {exception}',
                ['exception' => $e]
            );
            return [];
        }

        return array_column($rows, 'TABLE_NAME');
    }
}
