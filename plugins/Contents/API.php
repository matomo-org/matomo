<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Contents;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\Contents\Archiver;

/**
 * API for content engagement reports.
 *
 * Exposes reports for content names and content pieces, including their
 * aggregated metrics over the requested period and date selection.
 *
 * @method static \Piwik\Plugins\Contents\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns a report of content names for the requested site and date selection.
     *
     * @param int|string|int[] $idSite    Website ID(s) to query.
     *                                   - Single site ID (e.g. 1)
     *                                   - Multiple site IDs (e.g. [1, 4, 5])
     *                                   - Comma-separated list ("1,4,5") or "all"
     *                                   Dates and periods parameters are interpreted in the website timezone.
     *                                   When querying multiple sites, dates and period parameters are interpreted using the UTC timezone.
     *
     * @param string           $period   The period to request statistics for, returns data for the period containing the specified date.
     *                                   Allowed values: "day", "week", "month", "year", "range".
     *
     * @param string           $date     The date or date range to request data for.
     *                                   'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                                   or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     *
     * @param string|bool      $segment  (Optional) Custom segment to filter the report.
     *                                   Example: "referrerName==twitter.com"
     *                                   Supports AND (;) and OR (,) operators.
     *                                   [See documentation:](https://developer.matomo.org/api-reference/reporting-api-segmentation)
     *
     * @param int|string|false $idSubtable Subtable ID to load, 'all' to load all subtables, or false for root.
     * @return DataTable|DataTable\Map Data table containing content name metrics.
     */
    public function getContentNames($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable);
    }

    /**
     * Returns a report of content pieces for the requested site and date selection.
     *
     * @param int|string|int[] $idSite   Website ID(s) to query.
     *                                   - Single site ID (e.g. 1)
     *                                   - Multiple site IDs (e.g. [1, 4, 5])
     *                                   - Comma-separated list ("1,4,5") or "all"
     *                                   Dates and periods parameters are interpreted in the website timezone.
     *                                   When querying multiple sites, dates and period parameters are interpreted using the UTC timezone.
     *
     * @param string           $period   The period to request statistics for, returns data for the period containing the specified date.
     *                                   Allowed values: "day", "week", "month", "year", "range".
     *
     * @param string           $date     The date or date range to request data for.
     *                                   'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                                   or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     *
     * @param string|bool      $segment  (Optional) Custom segment to filter the report.
     *                                   Example: "referrerName==twitter.com"
     *                                   Supports AND (;) and OR (,) operators.
     *                                   [See documentation:](https://developer.matomo.org/api-reference/reporting-api-segmentation)
     *
     * @param int|string|false $idSubtable Subtable ID to load, 'all' to load all subtables, or false for root.
     * @return DataTable|DataTable\Map Data table containing content piece metrics.
     */
    public function getContentPieces($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable);
    }

    private function getDataTable($name, $idSite, $period, $date, $segment, $expanded, $idSubtable)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $recordName = Dimensions::getRecordNameForAction($name);
        $dataTable  = Archive::createDataTableFromArchive($recordName, $idSite, $period, $date, $segment, $expanded, $flat = false, $idSubtable);

        if (empty($idSubtable)) {
            $dataTable->filter('AddSegmentValue', array(function ($label) {
                if ($label === Archiver::CONTENT_PIECE_NOT_SET) {
                    return false;
                }

                return $label;
            }));
        }

        $this->filterDataTable($dataTable);
        return $dataTable;
    }

    /**
     * @param DataTable $dataTable
     */
    private function filterDataTable($dataTable)
    {
        $dataTable->queueFilter('ReplaceColumnNames');
        $dataTable->queueFilter('ReplaceSummaryRowLabel');
        $dataTable->filter(function (DataTable $table) {
            $row = $table->getRowFromLabel(Archiver::CONTENT_PIECE_NOT_SET);
            if ($row) {
                $row->setColumn('label', Piwik::translate('General_NotDefined', Piwik::translate('Contents_ContentPiece')));
            }
        });
    }
}
