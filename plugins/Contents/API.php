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
     * @param int|string|int[] $idSite A site ID, a comma-separated list of IDs, an array of IDs, or 'all' for all sites.
     * @param string $period Period identifier enabled for the API (commonly 'day', 'week', 'month', 'year', 'range';
     *                       custom period identifiers may also be enabled).
     * @param string|\Piwik\Date $date Date or date range to query. Supported inputs include:
     *                                - A single date accepted by the date parser (for example 'YYYY-MM-DD',
     *                                  'now', 'today', 'yesterday', 'yesterdaySameTime', 'tomorrow',
     *                                  or 'last week'/'last-week'/'last month'/'last year').
     *                                - A multiple-period selector 'lastN' or 'previousN' where N is digits.
     *                                - A date range 'YYYY-MM-DD,YYYY-MM-DD', where the end may also be
     *                                  'today', 'now', 'yesterday', or 'last week/month/year'.
     *                                Relative keywords are evaluated in the site timezone when a single site
     *                                is requested; otherwise UTC is used. For date ranges, the end date uses
     *                                that timezone only when it is a relative keyword, while the start date
     *                                is parsed without a timezone override.
     * @param string|false $segment Segment definition string, or false for no segment.
     * @param int|numeric-string|'all'|false|null $idSubtable Subtable ID to load. Use a numeric ID, 'all' to
     *                                                       load all subtables, or false/null for the top-level
     *                                                       table.
     * @return DataTable|DataTable\Map Data table containing content name metrics.
     */
    public function getContentNames($idSite, $period, $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, false, $idSubtable);
    }

    /**
     * Returns a report of content pieces for the requested site and date selection.
     *
     * @param int|string|int[] $idSite A site ID, a comma-separated list of IDs, an array of IDs, or 'all' for all sites.
     * @param string $period Period identifier enabled for the API (commonly 'day', 'week', 'month', 'year', 'range';
     *                       custom period identifiers may also be enabled).
     * @param string|\Piwik\Date $date Date or date range to query. Supported inputs include:
     *                                - A single date accepted by the date parser (for example 'YYYY-MM-DD',
     *                                  'now', 'today', 'yesterday', 'yesterdaySameTime', 'tomorrow',
     *                                  or 'last week'/'last-week'/'last month'/'last year').
     *                                - A multiple-period selector 'lastN' or 'previousN' where N is digits.
     *                                - A date range 'YYYY-MM-DD,YYYY-MM-DD', where the end may also be
     *                                  'today', 'now', 'yesterday', or 'last week/month/year'.
     *                                Relative keywords are evaluated in the site timezone when a single site
     *                                is requested; otherwise UTC is used. For date ranges, the end date uses
     *                                that timezone only when it is a relative keyword, while the start date
     *                                is parsed without a timezone override.
     * @param string|false $segment Segment definition string, or false for no segment.
     * @param int|numeric-string|'all'|false|null $idSubtable Subtable ID to load. Use a numeric ID, 'all' to
     *                                                       load all subtables, or false/null for the top-level
     *                                                       table.
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
