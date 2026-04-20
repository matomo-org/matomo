<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Config\DatabaseConfig;
use Piwik\DataAccess\ArchiveBlobColumnType;

/**
 * Caps archive row limits to prevent gzip-compressed DataTable blobs from exceeding the 16 MB
 * limit of legacy MEDIUMBLOB `archive_blob_YYYY_MM` tables.
 *
 * ### When the cap activates
 *
 * Only when ALL of the following are true:
 *  1. `[database] archive_blob_tables_may_contain_mediumblob = 1` in config.ini.php.
 *  2. The target archive_blob table still uses a MEDIUMBLOB `value` column.
 *  3. The configured row limit exceeds {@see MEDIUMBLOB_CAP_TRIGGER} (100 000).
 *     A `null` / 0 limit (meaning "unlimited") is treated as exceeding the trigger.
 *
 * When any condition is false the configured value is returned unchanged so fresh installs and
 * fully-migrated installs pay zero overhead.
 *
 * ### Why the thresholds are hardcoded
 *
 * Exposing `MEDIUMBLOB_CAP` as a config key would let operators raise it above the safe threshold
 * and reintroduce the truncation risk.  The constants are intentionally not configurable.
 */
final class ArchiveBlobRowCap
{
    /**
     * Configured row limits above this value trigger the cap when the table is MEDIUMBLOB.
     * `null` (unlimited) is also considered to exceed this threshold.
     */
    private const MEDIUMBLOB_CAP_TRIGGER = 100000;

    /**
     * The maximum row limit applied when the table is MEDIUMBLOB and the configured limit
     * exceeds {@see MEDIUMBLOB_CAP_TRIGGER}.
     */
    private const MEDIUMBLOB_CAP = 50000;

    /**
     * Returns `true` when the MEDIUMBLOB cap logic may need to run (flag is set to 1).
     *
     * Use this as a cheap guard before obtaining the target table name so that callers do not
     * perform any I/O (DB table-list lookup, INFORMATION_SCHEMA query) when the flag is unset.
     */
    public static function isCapPossiblyNeeded(): bool
    {
        return (int)(DatabaseConfig::getConfigValue(ArchiveBlobColumnType::CONFIG_KEY) ?? 0) !== 0;
    }

    /**
     * Returns the effective maximum number of top-level rows to store in a DataTable blob,
     * applying the MEDIUMBLOB cap when necessary.
     *
     * @param int|null $configuredMax  The operator-configured row limit (null = unlimited).
     * @param string   $tableName      Fully-prefixed archive_blob table name (used to look up
     *                                 the column type from INFORMATION_SCHEMA).
     * @return int|null  The (possibly capped) row limit, or `null` when no cap is needed and
     *                   the original limit was `null`.
     */
    public static function capMaxRows(?int $configuredMax, string $tableName): ?int
    {
        return self::applyCap($configuredMax, $tableName);
    }

    /**
     * Returns the effective maximum number of rows per subtable, applying the MEDIUMBLOB cap
     * when necessary.
     *
     * @param int|null $configuredMax  The operator-configured subtable row limit (null = unlimited).
     * @param string   $tableName      Fully-prefixed archive_blob table name.
     */
    public static function capMaxSubtableRows(?int $configuredMax, string $tableName): ?int
    {
        return self::applyCap($configuredMax, $tableName);
    }

    /**
     * Core cap logic shared by both public methods.
     *
     */
    private static function applyCap(?int $configuredMax, string $tableName): ?int
    {
        // Fast-path: skip all I/O when the flag is not set (fresh installs).
        $flag = (int)(DatabaseConfig::getConfigValue(ArchiveBlobColumnType::CONFIG_KEY) ?? 0);
        if ($flag === 0) {
            return $configuredMax;
        }

        // null means "unlimited" — treat as exceeding the trigger.
        if ($configuredMax !== null && $configuredMax <= self::MEDIUMBLOB_CAP_TRIGGER) {
            return $configuredMax;
        }

        // I/O only when flag=1 and limit exceeds trigger.
        if (!ArchiveBlobColumnType::isMediumBlob($tableName)) {
            return $configuredMax;
        }

        return self::MEDIUMBLOB_CAP;
    }
}
