<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\DataTable\DataTableInterface;
use Piwik\Piwik;
use Piwik\Plugins\ExampleLogTables\RecordBuilders\AdminGroupVisits;

/**
 * API for plugin ExampleLogTables
 *
 * Every public method of an API class is a public HTTP endpoint the moment the plugin is
 * activated. There is no gate above it, so each one checks access for itself.
 *
 * Two conventions below are core's rather than a choice, and both are worth knowing before copying
 * a signature from here:
 *
 * - **`$idSite` stays untyped.** Every value reaching an API method over HTTP is a string, but the
 *   same method is called in process by core and by other plugins, and there `$idSite` may be an
 *   integer, a comma-separated list, an array of ids or the string `all`. Narrowing it to `string`
 *   makes the array case a `TypeError`.
 * - **`$segment` keeps the `false` default** that core's own API methods use. It is not a
 *   meaningful sentinel: `Segment` trims whatever it is given, so `false`, `null` and `''` all
 *   reach the archiving chain as "no segment". Match core's default rather than inventing one.
 *
 * Method docblocks in this class are plain instances of Matomo's public API template -- summary,
 * one described `@param` per parameter in signature order, one described `@return`. That is
 * deliberate: the docblock of an API method is the unit someone copies when writing their own, so
 * it carries the contract and nothing else. Anything explaining *why* belongs here, in the class
 * docblock, or beside the code it explains.
 *
 * @method static \Piwik\Plugins\ExampleLogTables\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the number of visits made by users belonging to a group flagged as an admin group.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                         - Single site ID (e.g. 1)
     *                         - Multiple site IDs (e.g. [1, 4, 5])
     *                         - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                    containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|false|null $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable|DataTable\Map Visits by users in an admin group, one row per requested period.
     */
    public function getAdminGroupVisits(
        $idSite,
        string $period,
        string $date,
        $segment = false
    ): DataTableInterface {
        Piwik::checkUserHasViewAccess($idSite);

        $archive = Archive::build($idSite, $period, $date, $segment);

        return $archive->getDataTableFromNumeric([AdminGroupVisits::NB_VISITS_ADMIN_GROUP_RECORD]);
    }
}
