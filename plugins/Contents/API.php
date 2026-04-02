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
 * The Contents API exposes content tracking reports grouped by content name and content piece.
 *
 * @method static \Piwik\Plugins\Contents\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns content tracking metrics grouped by content name.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     *                                   When provided, returns the content pieces for the selected content name row.
     * @return DataTable|DataTable\Map Content name rows with impressions, interactions, and interaction rate for the
     *                                 requested site and period.
     */
    public function getContentNames($idSite, string $period, string $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $idSubtable);
    }

    /**
     * Returns content tracking metrics grouped by content piece.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5") or "all"
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @param int|null|false $idSubtable Subtable ID to fetch instead of the top-level report.
     *                                   When provided, returns the content names for the selected content piece row.
     * @return DataTable|DataTable\Map Content piece rows with impressions, interactions, and interaction rate for the
     *                                 requested site and period.
     */
    public function getContentPieces($idSite, string $period, string $date, $segment = false, $idSubtable = false)
    {
        return $this->getDataTable(__FUNCTION__, $idSite, $period, $date, $segment, $idSubtable);
    }

    /**
     * @param int|string|int[] $idSite
     * @param string|null|false $segment
     * @param int|null|false $idSubtable
     * @return DataTable|DataTable\Map
     */
    private function getDataTable(string $name, $idSite, string $period, string $date, $segment, $idSubtable)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $recordName = Dimensions::getRecordNameForAction($name);
        $dataTable = Archive::createDataTableFromArchive($recordName, $idSite, $period, $date, $segment, false, false, $idSubtable);

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
     * @param DataTable|DataTable\Map $dataTable
     */
    private function filterDataTable($dataTable): void
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
