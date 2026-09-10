<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Config\DatabaseConfig;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveBlobColumnType;
use Piwik\Log\LoggerInterface;

/**
 * Reduces configured archive row limits on legacy `archive_blob_YYYY_MM` tables whose `value`
 * column is still MEDIUMBLOB, so a limit configured above what such a column can hold does not
 * lead to gzip-compressed DataTable blobs being silently truncated by MySQL at 16 MB.
 *
 * The cap applies only when the `[database] archive_blob_tables_may_contain_mediumblob` flag is
 * set, the target table is still MEDIUMBLOB, and a row limit above {@see MEDIUMBLOB_MAX_ROWS} is
 * configured. Tables created since the LONGBLOB schema change are unaffected, so in practice this
 * is limited to re-archiving periods that predate the upgrade.
 *
 * A `null` or `0` limit means "store every row" and is left alone. Those records were already
 * written unbounded before this cap existed; the cap bounds a limit an operator configured, it
 * does not impose one where there was none.
 *
 * This bounds a record's row count, not the byte size MySQL stores. Subtables are written in
 * chunks (see {@see \Piwik\Archive\Chunk}), so it reduces the risk of exceeding 16 MB rather than
 * ruling it out.
 */
final class ArchiveBlobRowCap
{
    /**
     * Configured row limits above this value are reduced to it when the table is MEDIUMBLOB.
     *
     * Not configurable: exposing it would let operators raise it back above the safe threshold.
     */
    private const MEDIUMBLOB_MAX_ROWS = 100000;

    /**
     * Tables already logged about, so a capped archiving run reports once per table, not per record.
     *
     * @var array<string, true>
     */
    private static array $logged = [];

    /**
     * Returns `true` when the cap may need to run (flag is set).
     *
     * Use this as a cheap guard before obtaining the target table name so that callers do not
     * perform any I/O (DB table-list lookup, INFORMATION_SCHEMA query) when the flag is unset.
     */
    public static function isCapPossiblyNeeded(): bool
    {
        return (int) (DatabaseConfig::getConfigValue(ArchiveBlobColumnType::CONFIG_KEY) ?? 0) !== 0;
    }

    /**
     * Returns the effective row limit to use when serializing a DataTable into `$tableName`.
     *
     * @param int|null $configuredMax The configured row limit; `null` or `0` mean "no limit".
     * @param string $tableName Fully-prefixed archive_blob table name.
     * @return int|null The configured limit, reduced to {@see MEDIUMBLOB_MAX_ROWS} if it exceeded it.
     */
    public static function capMaxRows(?int $configuredMax, string $tableName): ?int
    {
        if (
            $configuredMax === null
            || $configuredMax <= self::MEDIUMBLOB_MAX_ROWS
            || !self::isCapPossiblyNeeded()
        ) {
            return $configuredMax;
        }

        if (!ArchiveBlobColumnType::isMediumBlob($tableName)) {
            return $configuredMax;
        }

        if (!isset(self::$logged[$tableName])) {
            self::$logged[$tableName] = true;
            StaticContainer::get(LoggerInterface::class)->info(
                'Archive row limits are capped at {max} for table {table}, which still uses a MEDIUMBLOB value column.'
                . ' Convert it to LONGBLOB and run core:recheck-archive-blob-types to restore the configured limits.',
                ['max' => self::MEDIUMBLOB_MAX_ROWS, 'table' => $tableName]
            );
        }

        return self::MEDIUMBLOB_MAX_ROWS;
    }
}
