<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
 * VisitTime API lets you access reports by Hour (Server time), and by Hour Local Time of your visitors.
 *
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime_API
{
    static private $instance = null;

    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected function getDataTable($name, $idSite, $period, $date, $segment)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $archive = Piwik_Archive::build($idSite, $period, $date, $segment);
        $dataTable = $archive->getDataTable($name);
        $dataTable->filter('Sort', array('label', 'asc', true));
        $dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getTimeLabel'));
        $dataTable->queueFilter('ReplaceColumnNames');
        return $dataTable;
    }

    public function getVisitInformationPerLocalTime($idSite, $period, $date, $segment = false)
    {
        return $this->getDataTable('VisitTime_localTime', $idSite, $period, $date, $segment);
    }

    public function getVisitInformationPerServerTime($idSite, $period, $date, $segment = false, $hideFutureHoursWhenToday = false)
    {
        $table = $this->getDataTable('VisitTime_serverTime', $idSite, $period, $date, $segment);
        if ($hideFutureHoursWhenToday) {
            $table = $this->removeHoursInFuture($table, $idSite, $period, $date);
        }
        return $table;
    }

    /**
     * Returns datatable describing the number of visits for each day of the week.
     *
     * @param string $idSite The site ID. Cannot refer to multiple sites.
     * @param string $period The period type: day, week, year, range...
     * @param string $date The start date of the period. Cannot refer to multiple dates.
     * @param string $segment The segment.
     * @return Piwik_DataTable
     */
    public function getByDayOfWeek($idSite, $period, $date, $segment = false)
    {
        Piwik::checkUserHasViewAccess($idSite);

        // disabled for multiple sites/dates
        if (Piwik_Archive::isMultipleSites($idSite)) {
            throw new Exception("VisitTime.getByDayOfWeek does not support multiple sites.");
        }

        if (Piwik_Archive::isMultiplePeriod($date, $period)) {
            throw new Exception("VisitTime.getByDayOfWeek does not support multiple dates.");
        }

        // metrics to query
        $metrics = Piwik_ArchiveProcessing::getCoreMetrics();

        // get metric data for every day within the supplied period
        $oSite = new Piwik_Site($idSite);
        $oPeriod = Piwik_Archive::makePeriodFromQueryParams($oSite, $period, $date);
        $dateRange = $oPeriod->getDateStart()->toString() . ',' . $oPeriod->getDateEnd()->toString();

        $archive = Piwik_Archive::build($idSite, 'day', $dateRange, $segment);
        $dataTable = $archive->getDataTableFromNumeric($metrics)->mergeChildren();

        // if there's no data for this report, don't bother w/ anything else
        if ($dataTable->getRowsCount() == 0) {
            return $dataTable;
        }

        // group by the day of the week (see below for dayOfWeekFromDate function)
        $dataTable->filter('GroupBy', array('label', 'Piwik_VisitTime_dayOfWeekFromDate'));

        // create new datatable w/ empty rows, then add calculated datatable
        $rows = array();
        foreach (array(1, 2, 3, 4, 5, 6, 7) as $day) {
            $rows[] = array('label' => $day, 'nb_visits' => 0);
        }

        $result = new Piwik_DataTable();
        $result->addRowsFromSimpleArray($rows);
        $result->addDataTable($dataTable);

        // set day of week integer as metadata
        $result->filter('ColumnCallbackAddMetadata', array('label', 'day_of_week'));

        // translate labels
        $result->filter('ColumnCallbackReplace', array('label', 'Piwik_VisitTime_translateDayOfWeek'));

        // set datatable metadata for period start & finish
        $result->setMetadata('date_start', $oPeriod->getDateStart());
        $result->setMetadata('date_end', $oPeriod->getDateEnd());

        return $result;
    }

    protected function removeHoursInFuture($table, $idSite, $period, $date)
    {
        $site = new Piwik_Site($idSite);

        if ($period == 'day'
            && ($date == 'today'
                || $date == Piwik_Date::factory('now', $site->getTimezone())->toString())
        ) {
            $currentHour = Piwik_Date::factory('now', $site->getTimezone())->toString('G');
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

function Piwik_getTimeLabel($label)
{
    return sprintf(Piwik_Translate('VisitTime_NHour'), $label);
}

/**
 * Returns the day of the week for a date string, without creating a new
 * Piwik_Date instance.
 *
 * @param string $dateStr
 * @return int The day of the week (1-7)
 */
function Piwik_VisitTime_dayOfWeekFromDate($dateStr)
{
    return date('N', strtotime($dateStr));
}

/**
 * Returns translated long name of a day of the week.
 *
 * @param int $dayOfWeek 1-7, for Sunday-Saturday
 * @return string
 */
function Piwik_VisitTime_translateDayOfWeek($dayOfWeek)
{
    return Piwik_Translate('General_LongDay_' . $dayOfWeek);
}
