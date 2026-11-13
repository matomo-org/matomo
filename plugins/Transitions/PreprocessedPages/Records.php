<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Config;
use Piwik\Config\GeneralConfig;

/**
 * Names and layout of the archive records of a pre-processed page, `Transitions_<idpage>_<part>`. They are keyed by the
 * page's row, so they outlive the page URL's `log_action` row and a removal of the page.
 *
 * The list records hold one row per action, with the label and the count in `Metrics::INDEX_NB_ACTIONS`, plus a
 * summary row for the actions beyond {@link getMaxRows()}. The referrers record holds one row per referrer type (label =
 * `Common::REFERRER_TYPE_*`, visits in `Metrics::INDEX_NB_VISITS`) with a subtable of the referrers of that type
 * (same columns, plus a summary row). The numeric records are written for every archived page, also when 0, and
 * also when the URL isn't tracked. `days` counts the days with visits whose records were built from the logs: 1 in a
 * day archive with visits, summed for longer periods.
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
     * Number of days of the archive's period that had visits, in the Transitions archives of a website with selected
     * pages: a period is covered for a page when its `days` counter equals this. A longer period has none when one of
     * its subperiods with visits has none, so it is never covered.
     */
    public const DAYS_WITH_VISITS = 'Transitions_daysWithVisits';

    /**
     * Label of search engines without a name in the referrers subtable; the report shows it as `General_Unknown`.
     */
    public const UNKNOWN_SEARCH_ENGINE_LABEL = '';

    public const DEFAULT_MAX_ROWS = 1000;
    public const DEFAULT_MAX_ROWS_SERVED = 100;

    public static function getRecordName(int $idPage, string $part): string
    {
        return 'Transitions_' . $idPage . '_' . $part;
    }

    /**
     * Name under which a rebuild of all records of the page is scheduled. It is one of the page's records, so
     * archiving can tell whether an archive holds it.
     */
    public static function getRebuildReportName(int $idPage): string
    {
        return self::getRecordName($idPage, self::PAGEVIEWS);
    }

    /**
     * Returns the idpage of the page a record belongs to, or null for another record.
     */
    public static function getIdPageOfRecord(string $recordName): ?int
    {
        if (!preg_match('/^Transitions_(\d+)_[a-zA-Z]+$/', $recordName, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * Number of rows each list keeps, besides the summary row, in every period: `[General]
     * datatable_archiving_maximum_rows_transitions`. Longer periods are summed from shorter ones, so they need more rows
     * than a report shows to keep its top rows accurate.
     */
    public static function getMaxRows(int $idSite): int
    {
        $maxRows = GeneralConfig::getIntegerConfigValue('datatable_archiving_maximum_rows_transitions', self::DEFAULT_MAX_ROWS, $idSite);
        if ($maxRows < 1) {
            throw new \Exception('Invalid value for [General] datatable_archiving_maximum_rows_transitions: expected an integer >= 1.');
        }

        return $maxRows;
    }

    /**
     * The most rows a report served from the records shows: `[Transitions_<idSite>]` or `[Transitions]`
     * `preprocessed_max_rows_served`, at most {@link getMaxRows()}.
     */
    public static function getMaxRowsServed(int $idSite): int
    {
        return min(self::getConfigValue($idSite, 'preprocessed_max_rows_served', self::DEFAULT_MAX_ROWS_SERVED, 1), self::getMaxRows($idSite));
    }

    /**
     * An integer of at least `$min` from the website's `[Transitions_<idSite>]` section, else from `[Transitions]`,
     * else the default. Any other value throws an exception naming the setting.
     */
    public static function getConfigValue(int $idSite, string $name, int $default, int $min = 0): int
    {
        $config = Config::getInstance();
        $siteSection = 'Transitions_' . $idSite;

        foreach ([$siteSection => $config->$siteSection, 'Transitions' => $config->Transitions] as $sectionName => $section) {
            if (!is_array($section) || !array_key_exists($name, $section)) {
                continue;
            }

            $value = $section[$name];
            if (is_string($value) && ctype_digit($value)) {
                $value = (int) $value;
            }
            if (is_int($value) && $value >= $min) {
                return $value;
            }

            throw new \Exception('Invalid value for [' . $sectionName . '] ' . $name . ': expected an integer >= ' . $min . '.');
        }

        return $default;
    }
}
