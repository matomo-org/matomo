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
 * by querying INFORMATION_SCHEMA.
 *
 * Results are cached per table name for the duration of the request. Only conclusive results are
 * cached, so a transient query failure does not pin the answer for the rest of the process.
 *
 * {@see isMediumBlob()} returns `true` when it cannot tell, as a fail-safe: applying the row-limit
 * cap unnecessarily is safer than letting a blob exceed 16 MB and be silently truncated. The
 * methods that decide whether the cap is still needed at all fail the other way and throw, so a
 * failed lookup can never be mistaken for "all tables have been migrated".
 */
final class ArchiveBlobColumnType
{
    /**
     * The [database] config key signalling that archive_blob tables may still have MEDIUMBLOB
     * `value` columns. Set by the 6.0.0-b2 migration on affected installs.
     */
    public const CONFIG_KEY = 'archive_blob_tables_may_contain_mediumblob';

    /**
     * Per-request cache: table name => isMediumBlob result.
     *
     * @var array<string, bool>
     */
    private static array $cache = [];

    /**
     * Returns `true` when the `value` column of `$tableName` is MEDIUMBLOB, `false` when it is
     * LONGBLOB (or any other wider type), and `true` when the type could not be determined.
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
        } catch (\Exception $e) {
            StaticContainer::get(LoggerInterface::class)->warning(
                'ArchiveBlobColumnType: could not determine column type for table {table}: {exception}',
                ['table' => $tableName, 'exception' => $e]
            );
            // Fail-safe, but not cached: the next call may succeed.
            return true;
        }

        // MySQL reports the type lowercase; compare case-insensitively anyway.
        $normalized = strtolower((string) $columnType);

        if ($normalized === '') {
            // Callers create the table before calling us, so a missing table or column means a
            // race or schema corruption. Apply the cap conservatively and do not cache it.
            StaticContainer::get(LoggerInterface::class)->warning(
                'ArchiveBlobColumnType: INFORMATION_SCHEMA returned no row for table {table}; applying cap conservatively.',
                ['table' => $tableName]
            );
            return true;
        }

        self::$cache[$tableName] = ($normalized === 'mediumblob');
        return self::$cache[$tableName];
    }

    /**
     * Clears the per-request cache. Useful in tests.
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Removes the {@see CONFIG_KEY} flag when no archive_blob table uses MEDIUMBLOB any more.
     *
     * Returns immediately without I/O when the flag is not set, so fresh installs pay nothing.
     *
     * @return string[] Names of archive_blob tables that still use MEDIUMBLOB. Empty means none
     *                  remain and the flag was cleared, or that the flag was not set.
     * @throws \Exception If the tables could not be listed. The flag is left untouched, so a
     *                    failed check never disables the cap.
     */
    public static function recheckAndUpdateFlag(): array
    {
        $flag = (int) (Config::getInstance()->database[self::CONFIG_KEY] ?? 0);
        if ($flag === 0) {
            return [];
        }

        $mediumBlobTables = self::getMediumBlobArchiveTables();
        if (empty($mediumBlobTables)) {
            $config = Config::getInstance();
            $database = $config->database;
            unset($database[self::CONFIG_KEY]);
            $config->database = $database;
            $config->forceSave();
        }

        return $mediumBlobTables;
    }

    /**
     * Returns the names of all archive_blob tables whose `value` column is MEDIUMBLOB.
     *
     * Only tables starting with the configured table prefix are inspected, so tables of other
     * Matomo instances in the same schema are never returned.
     *
     * @return string[]
     * @throws \Exception If INFORMATION_SCHEMA could not be queried.
     */
    public static function getMediumBlobArchiveTables(): array
    {
        // Prefix-anchored LIKE pattern, e.g. "matomo\_archive\_blob\_%". The prefix is LIKE-escaped
        // so a prefix containing '%' or '_' cannot broaden the match.
        $rawPrefix = Common::prefixTable('archive_blob_');
        $likePrefix = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $rawPrefix);

        $rows = Db::fetchAll(
            "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME   LIKE ?
                AND COLUMN_NAME  = ?
                AND LOWER(COLUMN_TYPE) = ?",
            [$likePrefix . '%', 'value', 'mediumblob']
        );

        return array_column($rows, 'TABLE_NAME');
    }
}
