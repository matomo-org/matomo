<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Period;
use Piwik\Period\Week;
use Piwik\Plugins\SitesManager\Model as SitesManagerModel;
use Piwik\Site;

/**
 * Service that can be used to invalidate archives or add archive references to a list so they will
 * be invalidated later.
 *
 * Archives are put in an "invalidated" state by setting the done flag to `ArchiveWriter::DONE_INVALIDATED`.
 * This class also adds the archive's associated site to the a distributed list and adding the archive's year month to another
 * distributed list.
 *
 * CronArchive will reprocess the archive data for all sites in the first list, and a scheduled task
 * will purge the old, invalidated data in archive tables identified by the second list.
 *
 * Until CronArchive, or browser triggered archiving, re-processes data for an invalidated archive, the invalidated
 * archive data will still be displayed in the UI and API.
 *
 * ### Deferred Invalidation
 *
 * Invalidating archives means running queries on one or more archive tables. In some situations, like during
 * tracking, this is not desired. In such cases, archive references can be added to a list via the
 * rememberToInvalidateArchivedReportsLater method, which will add the reference to a distributed list
 *
 * Later, during Piwik's normal execution, the list will be read and every archive it references will
 * be invalidated.
 */
class ArchiveInvalidator
{
    private $warningDates = array();
    private $processedDates = array();
    private $minimumDateWithLogs = false;
    private $invalidDates = array();

    private $rememberArchivedReportIdStart = 'report_to_invalidate_';

    public function rememberToInvalidateArchivedReportsLater($idSite, Date $date)
    {
        $key   = $this->buildRememberArchivedReportId($idSite, $date->toString());
        $value = Option::get($key);

        // we do not really have to get the value first. we could simply always try to call set() and it would update or
        // insert the record if needed but we do not want to lock the table (especially since there are still some
        // MyISAM installations)

        if (false === $value) {
            Option::set($key, '1');
        }
    }

    public function getRememberedArchivedReportsThatShouldBeInvalidated()
    {
        $reports = Option::getLike($this->rememberArchivedReportIdStart . '%_%');

        $sitesPerDay = array();

        foreach ($reports as $report => $value) {
            $report = str_replace($this->rememberArchivedReportIdStart, '', $report);
            $report = explode('_', $report);
            $siteId = (int) $report[0];
            $date   = $report[1];

            if (empty($sitesPerDay[$date])) {
                $sitesPerDay[$date] = array();
            }

            $sitesPerDay[$date][] = $siteId;
        }

        return $sitesPerDay;
    }

    private function buildRememberArchivedReportId($idSite, $date)
    {
        $id  = $this->buildRememberArchivedReportIdForSite($idSite);
        $id .= '_' . trim($date);

        return $id;
    }

    private function buildRememberArchivedReportIdForSite($idSite)
    {
        return $this->rememberArchivedReportIdStart . (int) $idSite;
    }

    public function forgetRememberedArchivedReportsToInvalidateForSite($idSite)
    {
        $id = $this->buildRememberArchivedReportIdForSite($idSite) . '_%';
        Option::deleteLike($id);
    }

    /**
     * @internal
     */
    public function forgetRememberedArchivedReportsToInvalidate($idSite, Date $date)
    {
        $id = $this->buildRememberArchivedReportId($idSite, $date->toString());

        Option::delete($id);
    }

    /**
     * @param $idSites array
     * @param $dates string
     * @param $period string
     * @return array
     * @throws \Exception
     */
    public function markArchivesAsInvalidated(array $idSites, $dates, $period)
    {
        $this->findOlderDateWithLogs();
        $datesToInvalidate = $this->getDatesToInvalidateFromString($dates);
        $minDate = $this->getMinimumDateToInvalidate($datesToInvalidate);

        $this->updateSiteCreatedTime($idSites, $minDate);

        $datesByMonth = $this->getDatesByYearMonth($datesToInvalidate);
        $this->markArchivesInvalidatedFor($idSites, $period, $datesByMonth);

        $this->persistInvalidatedArchives($idSites, $datesByMonth);

        foreach ($idSites as $idSite) {
            foreach ($datesToInvalidate as $date) {
                $this->forgetRememberedArchivedReportsToInvalidate($idSite, $date);
            }
        }

        return $this->makeOutputLogs();
    }

    private function updateSiteCreatedTime($idSites, Date $minDate)
    {
        $idSites    = Site::getIdSitesFromIdSitesString($idSites);
        $minDateSql = $minDate->subDay(1)->getDatetime();

        $model = new SitesManagerModel();
        $model->updateSiteCreatedTime($idSites, $minDateSql);
    }

    /**
     * @param $toInvalidate
     * @return bool|Date
     * @throws \Exception
     */
    private function getMinimumDateToInvalidate($toInvalidate)
    {
        /* @var $date Date */
        $minDate = false;
        foreach ($toInvalidate as $date) {
            // Keep track of the minimum date for each website
            if ($minDate === false
                || $date->isEarlier($minDate)
            ) {
                $minDate = $date;
            }
        }
        if (empty($minDate)) {
            throw new \Exception("Check the 'dates' parameter is a valid date.");
        }
        return $minDate;
    }

    /**
     * @param $idSites
     * @param $period string
     * @param $datesByMonth array
     * @throws \Exception
     */
    private function markArchivesInvalidatedFor($idSites, $period, $datesByMonth)
    {
        $invalidateForPeriodId = $this->getPeriodId($period);

        // In each table, invalidate day/week/month/year containing this date
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

        $archiveNumericTables = array_filter($archiveTables, function ($name) {
            return ArchiveTableCreator::getTypeFromTableName($name) == ArchiveTableCreator::NUMERIC_TABLE;
        });

        foreach ($archiveNumericTables as $table) {
            // Extract Y_m from table name
            $suffix = ArchiveTableCreator::getDateFromTableName($table);
            if (!isset($datesByMonth[$suffix])) {
                continue;
            }
            // Dates which are to be deleted from this table
            $datesToDelete = $datesByMonth[$suffix];
            self::getModel()->updateArchiveAsInvalidated($table, $idSites, $invalidateForPeriodId, $datesToDelete);
        }
    }

    /**
     * Ensure the specified dates are valid.
     * Store invalid date so we can log them
     * @param array $dates
     * @return Date[]
     */
    private function getDatesToInvalidateFromString($dates)
    {
        $toInvalidate = array();

        $dates = explode(',', trim($dates));
        $dates = array_unique($dates);

        foreach ($dates as $theDate) {
            $theDate = trim($theDate);
            try {
                $date = Date::factory($theDate);
            } catch (\Exception $e) {
                $this->invalidDates[] = $theDate;
                continue;
            }
            if ($date->toString() == $theDate) {
                $toInvalidate[] = $date;
            } else {
                $this->invalidDates[] = $theDate;
            }
        }

        return $toInvalidate;
    }

    private function findOlderDateWithLogs()
    {
        // If using the feature "Delete logs older than N days"...
        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();
        $logsDeletedWhenOlderThanDays = $purgeDataSettings['delete_logs_older_than'];
        $logsDeleteEnabled = $purgeDataSettings['delete_logs_enable'];

        if ($logsDeleteEnabled
            && $logsDeletedWhenOlderThanDays
        ) {
            $this->minimumDateWithLogs = Date::factory('today')->subDay($logsDeletedWhenOlderThanDays);
        }
    }

    /**
     * Given the list of dates, process which tables YYYY_MM we should delete from
     *
     * @param $datesToInvalidate Date[]
     * @return array
     */
    private function getDatesByYearMonth($datesToInvalidate)
    {
        $datesByMonth = array();
        foreach ($datesToInvalidate as $date) {
            // we should only delete reports for dates that are more recent than N days
            if ($this->minimumDateWithLogs
                && $date->isEarlier($this->minimumDateWithLogs)
            ) {
                $this->warningDates[] = $date->toString();
                continue;
            }

            $this->processedDates[] = $date->toString();

            $month = $date->toString('Y_m');
            // For a given date, we must invalidate in the monthly archive table
            $datesByMonth[$month][] = $date->toString();

            // But also the year stored in January
            $year = $date->toString('Y_01');
            $datesByMonth[$year][] = $date->toString();

            // but also weeks overlapping several months stored in the month where the week is starting
            /* @var $week Week */
            $week = Period\Factory::build('week', $date);
            $weekAsString = $week->getDateStart()->toString('Y_m');
            $datesByMonth[$weekAsString][] = $date->toString();
        }
        return $datesByMonth;
    }

    /**
     * @return array
     */
    private function makeOutputLogs()
    {
        $output = array();
        if ($this->warningDates) {
            $output[] = 'Warning: the following Dates have not been invalidated, because they are earlier than your Log Deletion limit: ' .
                implode(", ", $this->warningDates) .
                "\n The last day with logs is " . $this->minimumDateWithLogs . ". " .
                "\n Please disable 'Delete old Logs' or set it to a higher deletion threshold (eg. 180 days or 365 years).'.";
        }
        if ($this->invalidDates) {
            $output[] = 'Warning: some of the Dates to invalidate were invalid: ' .
                implode(", ", $this->invalidDates) . ". Piwik simply ignored those and proceeded with the others.";
        }

        $output[] = "Success. The following dates were invalidated successfully: " . implode(", ", $this->processedDates);
        return $output;
    }

    /**
     * @param $period
     * @return bool|int
     */
    private function getPeriodId($period)
    {
        if (!empty($period)) {
            $period = Period\Factory::build($period, Date::today());
        }
        $invalidateForPeriod = $period ? $period->getId() : false;
        return $invalidateForPeriod;
    }

    /**
     * @param array $idSites
     * @param $datesByMonth
     */
    private function persistInvalidatedArchives(array $idSites, $datesByMonth)
    {
        $yearMonths = array_keys($datesByMonth);
        $yearMonths = array_unique($yearMonths);

        $store = new SitesToReprocessDistributedList();
        $store->add($idSites);

        $archivesToPurge = new ArchivesToPurgeDistributedList();
        $archivesToPurge->add($yearMonths);
    }

    private static function getModel()
    {
        return new Model();
    }
}
