<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitTime;

use Exception;
use Piwik\Archive;
use Piwik\DataTable;
use Piwik\Date;
use Piwik\Metrics;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Site;

require_once PIWIK_INCLUDE_PATH . '/plugins/VisitTime/functions.php';

/**
 * VisitTime API lets you access reports by Hour (Server time), and by Hour Local Time of your visitors.
 *
 * @method static \Piwik\Plugins\VisitTime\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @param int|string|int[] $idSite
     * @param string|null|false $segment
     * @return DataTable|DataTable\Map
     */
    protected function getDataTable(string $name, $idSite, string $period, string $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);

        $dataTable->filter('Sort', array('label', 'asc', true, false));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getTimeLabel'));
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    /**
     * Returns visit counts grouped by each visitor's local hour at the start of the visit.
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
     * @return DataTable|DataTable\Map Visit counts grouped by each visitor's local hour.
     */
    public function getVisitInformationPerLocalTime($idSite, string $period, string $date, $segment = false)
    {
        $table = $this->getDataTable(Archiver::LOCAL_TIME_RECORD_NAME, $idSite, $period, $date, $segment);
        $table->filter('AddSegmentValue');

        return $table;
    }

    /**
     * Returns visit counts grouped by the queried site's hour at the start of the visit.
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
     * @param bool $hideFutureHoursWhenToday Whether to omit hours later than the current hour in the site's
     *                                       timezone when querying today.
     * @return DataTable|DataTable\Map Visit counts grouped by hour in the queried site's timezone.
     */
    public function getVisitInformationPerServerTime($idSite, string $period, string $date, $segment = false, bool $hideFutureHoursWhenToday = false)
    {
        $table = $this->getDataTable(Archiver::SERVER_TIME_RECORD_NAME, $idSite, $period, $date, $segment);

        if ($table instanceof DataTable\Map && $table->getKeyName() === 'idSite') {
            foreach ($table->getDataTables() as $siteId => $dataTable) {
                $timezone = Site::getTimezoneFor($siteId);
                $dataTable->filter('Piwik\Plugins\VisitTime\DataTable\Filter\AddSegmentByLabelInUTC', [$timezone, $period, $date]);

                if ($hideFutureHoursWhenToday) {
                    $dataTable->filter(function (DataTable $dataTable) use ($siteId, $period, $date) {
                        $this->removeHoursInFuture($dataTable, $siteId, $period, $date);
                    });
                }
            }
        } else {
            $idSite = (int)$idSite;
            $timezone = Site::getTimezoneFor($idSite);
            $table->filter('Piwik\Plugins\VisitTime\DataTable\Filter\AddSegmentByLabelInUTC', [$timezone, $period, $date]);

            if ($hideFutureHoursWhenToday) {
                $table->filter(function (DataTable $dataTable) use ($idSite, $period, $date) {
                    $this->removeHoursInFuture($dataTable, $idSite, $period, $date);
                });
            }
        }

        return $table;
    }

    /**
     * Returns visit counts grouped by day of the week.
     *
     * @param int $idSite The numeric ID of the website to query. This endpoint does not support multiple sites.
     * @param 'day'|'week'|'month'|'year'|'range' $period The period to process, processes data for the period
     *                                                   containing the specified date.
     * @param string $date The date or date range to process.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX). Multiple dates are
     *                     not supported unless $period is 'range'.
     * @param string|null|false $segment Custom segment to filter the report.
     *                                   Example: "referrerName==example.com"
     *                                   Supports AND (;) and OR (,) operators.
     * @return DataTable Visit counts grouped into day-of-week rows for the requested period.
     */
    public function getByDayOfWeek($idSite, string $period, string $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        // metrics to query
        $metrics = Metrics::getVisitsMetricNames();
        unset($metrics[Metrics::INDEX_MAX_ACTIONS]);

        // disabled for multiple dates
        if (Period::isMultiplePeriod($date, $period)) {
            throw new Exception("VisitTime.getByDayOfWeek does not support multiple dates.");
        }

        // get metric data for every day within the supplied period
        $oPeriod = Period\Factory::makePeriodFromQueryParams(Site::getTimezoneFor($idSite), $period, $date);
        $dateRange = $oPeriod->getDateStart()->toString() . ',' . $oPeriod->getDateEnd()->toString();
        $archive = Archive::build($idSite, 'day', $dateRange, $segment);

        // disabled for multiple sites
        if (count($archive->getParams()->getIdSites()) > 1) {
            throw new Exception("VisitTime.getByDayOfWeek does not support multiple sites.");
        }

        /** @var DataTable\Map $numericTable */
        $numericTable = $archive->getDataTableFromNumeric($metrics);

        /** @var DataTable $dataTable */
        $dataTable = $numericTable->mergeChildren();

        // if there's no data for this report, don't bother w/ anything else
        if ($dataTable->getRowsCount() == 0) {
            return $dataTable;
        }

        // group by the day of the week (see below for dayOfWeekFromDate function)
        $dataTable->filter('GroupBy', array('label', __NAMESPACE__ . '\dayOfWeekFromDate'));

        // create new datatable w/ empty rows, then add calculated datatable
        $rows = array();
        foreach (array(1, 2, 3, 4, 5, 6, 7) as $day) {
            $rows[] = array('label' => $day, 'nb_visits' => 0);
        }
        $result = new DataTable();
        $result->addRowsFromSimpleArray($rows);
        $result->addDataTable($dataTable);

        // set day of week integer as metadata
        $result->filter('ColumnCallbackAddMetadata', array('label', 'day_of_week'));

        // translate labels
        $result->filter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\translateDayOfWeek'));

        // set datatable metadata for period start & finish
        $result->setMetadata('date_start', $oPeriod->getDateStart());
        $result->setMetadata('date_end', $oPeriod->getDateEnd());

        return $result;
    }

    protected function removeHoursInFuture(DataTable $table, int $idSite, string $period, string $date): DataTable
    {
        $site = new Site($idSite);

        if (
            $period == 'day'
            && ($date == 'today'
                || $date == Date::factory('now', $site->getTimezone())->toString())
        ) {
            $currentHour = Date::factory('now', $site->getTimezone())->toString('G');
            // If no data for today, this is an exception to the API output rule, as we normally return nothing:
            // we shall return all hours of the day, with nb_visits = 0
            if ($table->getRowsCount() == 0) {
                for ($hour = 0; $hour <= $currentHour; $hour++) {
                    $table->addRowFromSimpleArray(array('label' => $hour, 'nb_visits' => 0));
                }
                return $table;
            }

            $idsToDelete = array();
            foreach ($table->getRows() as $id => $row) {
                $hour = $row->getColumn('label');
                if ($hour > $currentHour) {
                    $idsToDelete[] = $id;
                }
            }
            $table->deleteRows($idsToDelete);
        }
        return $table;
    }
}
