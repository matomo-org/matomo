<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);

        $dataTable->filter('Sort', array('label', 'asc', true, false));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', __NAMESPACE__ . '\getTimeLabel'));
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    public function getVisitInformationPerLocalTime($idSite, $period, $date, $segment = false)
    {
        $table = $this->getDataTable(Archiver::LOCAL_TIME_RECORD_NAME, $idSite, $period, $date, $segment);
        $table->filter('AddSegmentValue');

        return $table;
    }

    public function getVisitInformationPerServerTime($idSite, $period, $date, $segment = false, $hideFutureHoursWhenToday = false)
    {
        $table = $this->getDataTable(Archiver::SERVER_TIME_RECORD_NAME, $idSite, $period, $date, $segment);

        $timezone = Site::getTimezoneFor($idSite);
        $table->filter('Piwik\Plugins\VisitTime\DataTable\Filter\AddSegmentByLabelInUTC', array($timezone, $period, $date));

        if ($hideFutureHoursWhenToday) {
            if ($table instanceof DataTable\Map) {
                foreach ($table->getDataTables() as &$dataTable) {
                    $dataTable = $this->removeHoursInFuture($dataTable, $idSite, $period, $date);
                }
            } else {
                $table = $this->removeHoursInFuture($table, $idSite, $period, $date);
            }
        }

        return $table;
    }

    /**
     * Returns datatable describing the number of visits for each day of the week.
     *
     * @param string $idSite The site ID. Cannot refer to multiple sites.
     * @param string $period The period type: day, week, year, range...
     * @param string $date The start date of the period. Cannot refer to multiple dates.
     * @param bool|string $segment The segment.
     * @throws Exception
     * @return DataTable
     */
    public function getByDayOfWeek($idSite, $period, $date, $segment = false)
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

        $dataTable = $archive->getDataTableFromNumeric($metrics)->mergeChildren();

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

    /**
     * @param DataTable $table
     * @param int $idSite
     * @param string $period
     * @param string $date
     * @return mixed
     */
    protected function removeHoursInFuture($table, $idSite, $period, $date)
    {
        $site = new Site($idSite);

        if ($period == 'day'
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
