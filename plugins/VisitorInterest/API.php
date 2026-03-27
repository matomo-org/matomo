<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitorInterest;

use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;

/**
 * VisitorInterest API lets you access two Visitor Engagement reports: number of visits per number of pages,
 * and number of visits per visit duration.
 *
 * @method static \Piwik\Plugins\VisitorInterest\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param string $name
     * @param int|string|int[] $idSite
     * @param string $period
     * @param string $date
     * @param string|null|false $segment
     * @param int $column
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable($name, $idSite, $period, $date, $segment, $column = Metrics::INDEX_NB_VISITS)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    /**
     * Returns visit duration distribution metrics for the requested site and period.
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
     * @return DataTable|DataTable\Map Visit counts grouped by visit duration ranges.
     */
    public function getNumberOfVisitsPerVisitDuration($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::TIME_SPENT_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->queueFilter('Sort', array('label', 'asc', true, false));
        $dataTable->queueFilter('AddSegmentByRangeLabel', array('visitDuration'));
        $dataTable->queueFilter('BeautifyTimeRangeLabels', array(
                                                                Piwik::translate('VisitorInterest_BetweenXYSeconds'),
                                                                Piwik::translate('Intl_OneMinuteShort'),
                                                                Piwik::translate('Intl_NMinutesShort')));
        return $dataTable;
    }

    /**
     * Returns page-depth distribution metrics for the requested site and period.
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
     * @return DataTable|DataTable\Map Visit counts grouped by pages-per-visit ranges.
     */
    public function getNumberOfVisitsPerPage($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(Archiver::PAGES_VIEWED_RECORD_NAME, $idSite, $period, $date, $segment);
        $dataTable->queueFilter('Sort', array('label', 'asc', true, false));
        $dataTable->queueFilter('AddSegmentByRangeLabel', array('actions'));
        $dataTable->queueFilter('BeautifyRangeLabels', array(
                                                            Piwik::translate('VisitorInterest_OnePage'),
                                                            Piwik::translate('VisitorInterest_NPages')));
        return $dataTable;
    }

    /**
     * Returns the distribution of visits by days since the previous visit.
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
     * @return DataTable|DataTable\Map Visit counts grouped by days since the last visit.
     */
    public function getNumberOfVisitsByDaysSinceLast($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(
            Archiver::DAYS_SINCE_LAST_RECORD_NAME,
            $idSite,
            $period,
            $date,
            $segment,
            Metrics::INDEX_NB_VISITS
        );
        $dataTable->queueFilter('AddSegmentByRangeLabel', array('daysSinceLastVisit'));
        $dataTable->queueFilter('BeautifyRangeLabels', array(Piwik::translate('Intl_OneDay'), Piwik::translate('Intl_NDays')));
        return $dataTable;
    }

    /**
     * Returns the distribution of visits by lifetime visit count.
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
     * @return DataTable|DataTable\Map Visit counts grouped by visit count ranges.
     */
    public function getNumberOfVisitsByVisitCount($idSite, $period, $date, $segment = false)
    {
        $dataTable = $this->getDataTable(
            Archiver::VISITS_COUNT_RECORD_NAME,
            $idSite,
            $period,
            $date,
            $segment,
            Metrics::INDEX_NB_VISITS
        );

        $dataTable->queueFilter('AddSegmentByRangeLabel', array('visitCount'));
        $dataTable->queueFilter('BeautifyRangeLabels', array(
                                                            Piwik::translate('General_OneVisit'), Piwik::translate('General_NVisits')));

        return $dataTable;
    }
}
