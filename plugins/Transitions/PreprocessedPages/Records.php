<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Config\GeneralConfig;

/**
 * Names and layout of the archive records of a pre-processed page, `Transitions_<idaction>_<part>`.
 *
 * The list records hold one row per action, with the label and the count in `Metrics::INDEX_NB_ACTIONS`, plus a
 * summary row for the actions beyond the row limit. The referrers record holds one row per referrer type (label =
 * `Common::REFERRER_TYPE_*`, visits in `Metrics::INDEX_NB_VISITS`) with a subtable of the referrers of that type
 * (same columns, plus a summary row). The numeric records are written for every archived page, also when 0.
 * `days` counts the days whose records were built from the logs: 1 in a day archive, summed for longer periods.
 */
final class Records
{
    public const PREVIOUS_PAGES = 'previousPages';
    public const PREVIOUS_SITE_SEARCHES = 'previousSiteSearches';
    public const FOLLOWING_PAGES = 'followingPages';
    public const FOLLOWING_SITE_SEARCHES = 'followingSiteSearches';
    public const OUTLINKS = 'outlinks';
    public const DOWNLOADS = 'downloads';
    public const REFERRERS = 'referrers';

    public const PAGEVIEWS = 'pageviews';
    public const LOOPS = 'loops';
    public const ENTRIES = 'entries';
    public const EXITS = 'exits';
    public const DAYS = 'days';

    /**
     * Lists of actions; the following actions leave out reloads of the page, which are counted in `loops`.
     */
    public const ACTION_LISTS = [
        self::PREVIOUS_PAGES,
        self::PREVIOUS_SITE_SEARCHES,
        self::FOLLOWING_PAGES,
        self::FOLLOWING_SITE_SEARCHES,
        self::OUTLINKS,
        self::DOWNLOADS,
    ];

    public const NUMERICS = [
        self::PAGEVIEWS,
        self::LOOPS,
        self::ENTRIES,
        self::EXITS,
        self::DAYS,
    ];

    /**
     * Label of search engines without a name in the referrers subtable; the report shows it as `General_Unknown`.
     */
    public const UNKNOWN_SEARCH_ENGINE_LABEL = '';

    public const DEFAULT_MAX_ROWS = 100;

    public static function getRecordName(int $idAction, string $part): string
    {
        return 'Transitions_' . $idAction . '_' . $part;
    }

    /**
     * Name under which a rebuild of all records of the page is scheduled. It is one of the page's records, so
     * archiving can tell whether an archive holds it.
     */
    public static function getRebuildReportName(int $idAction): string
    {
        return self::getRecordName($idAction, self::PAGEVIEWS);
    }

    /**
     * Returns the idaction of the page a record belongs to, or null for another record.
     */
    public static function getIdActionOfRecord(string $recordName): ?int
    {
        if (!preg_match('/^Transitions_(\d+)_[a-zA-Z]+$/', $recordName, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Number of rows each list keeps, besides the summary row: `[General] datatable_archiving_maximum_rows_transitions`.
     */
    public static function getMaxRows(int $idSite): int
    {
        return max(1, GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_transitions', self::DEFAULT_MAX_ROWS, $idSite));
    }
}
