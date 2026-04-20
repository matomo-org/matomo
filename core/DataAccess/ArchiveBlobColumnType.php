<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

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

            // MySQL returns the type in lowercase ('mediumblob', 'longblob'); use
            // case-insensitive comparison so we are not surprised by unusual drivers.
            $result = (strtolower((string) $columnType) === 'mediumblob');
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
     * `[database] archive_blob_tables_may_contain_mediumblob` config flag is removed.
     *
     * If the flag is not set (0 / unset) this method returns immediately without any I/O so that
     * fresh installs pay zero runtime cost during updates.
     *
     * Used by:
     *  - `core/Updater.php` after each component update finishes and at the end of a full batch.
     *  - `plugins/CoreUpdater/Commands/RecheckArchiveBlobTypes.php` as an on-demand CLI tool.
     *
     * @return bool  `true` when MEDIUMBLOB tables were found (flag left as-is or not set),
     *               `false` when flag was unset because no MEDIUMBLOB tables remain.
     */
    public static function recheckAndUpdateFlag(): bool
    {
        $flag = (int) (Config::getInstance()->database['archive_blob_tables_may_contain_mediumblob'] ?? 0);
        if ($flag === 0) {
            // Nothing to do on fresh installs or after the flag was already cleared.
            return false;
        }

        $mediumBlobTables = self::getMediumBlobArchiveTables();
        if (empty($mediumBlobTables)) {
            // All tables have been migrated (or none existed). Remove the flag.
            $config = Config::getInstance();
            $database = $config->database;
            unset($database['archive_blob_tables_may_contain_mediumblob']);
            $config->database = $database;
            $config->forceSave();
            return false;
        }

        return true;
    }

    /**
     * Returns the names of all archive_blob_* tables in the current schema whose `value` column
     * is MEDIUMBLOB.
     *
     * @return string[]
     */
    public static function getMediumBlobArchiveTables(): array
    {
        try {
            $rows = Db::fetchAll(
                "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME   LIKE ?
                    AND COLUMN_NAME  = ?
                    AND LOWER(COLUMN_TYPE) = ?",
                ['%archive\_blob\_%', 'value', 'mediumblob']
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
