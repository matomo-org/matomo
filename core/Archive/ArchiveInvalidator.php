<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\Archive\ArchiveInvalidator\InvalidationResult;
use Piwik\ArchiveProcessor\ArchivingStatus;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\CliMulti\Process;
use Piwik\Db;
use Piwik\Option;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Period;
use Piwik\Segment;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tracker\Cache;

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
    const TRACKER_CACHE_KEY = 'ArchiveInvalidator.rememberToInvalidate';

    private $rememberArchivedReportIdStart = 'report_to_invalidate_';

    /**
     * @var Model
     */
    private $model;

    /**
     * @var ArchivingStatus
     */
    private $archivingStatus;

    public function __construct(Model $model, ArchivingStatus $archivingStatus)
    {
        $this->model = $model;
        $this->archivingStatus = $archivingStatus;
    }

    public function getAllRememberToInvalidateArchivedReportsLater()
    {
        // we do not really have to get the value first. we could simply always try to call set() and it would update or
        // insert the record if needed but we do not want to lock the table (especially since there are still some
        // MyISAM installations)
        $values = Option::getLike('%' . $this->rememberArchivedReportIdStart . '%');

        $all = [];
        foreach ($values as $name => $value) {
            $suffix = substr($name, strpos($name, $this->rememberArchivedReportIdStart));
            $suffix = str_replace($this->rememberArchivedReportIdStart, '', $suffix);
            list($idSite, $dateStr) = explode('_', $suffix);

            $all[$idSite][$dateStr] = $value;
        }
        return $all;
    }

    public function rememberToInvalidateArchivedReportsLater($idSite, Date $date)
    {
        if (SettingsServer::isTrackerApiRequest()) {
            $value = $this->getRememberedArchivedReportsOptionFromTracker($idSite, $date->toString());
        } else {
            // To support multiple transactions at once, look for any other process to have set (and committed)
            // this report to be invalidated.
            $key   = $this->buildRememberArchivedReportIdForSiteAndDate($idSite, $date->toString());

            // we do not really have to get the value first. we could simply always try to call set() and it would update or
            // insert the record if needed but we do not want to lock the table (especially since there are still some
            // MyISAM installations)
            $value = Option::getLike('%' . $key . '%');
        }

        // getLike() returns an empty array rather than 'false'
        if (empty($value)) {
            // In order to support multiple concurrent transactions, add our pid to the end of the key so that it will just insert
            // rather than waiting on some other process to commit before proceeding.The issue is that with out this, more than
            // one process is trying to add the exact same value to the table, which causes contention. With the pid suffixed to
            // the value, each process can successfully enter its own row in the table. The net result will be the same. We could
            // always just set this, but it would result in a lot of rows in the options table.. more than needed.  With this
            // change you'll have at most N rows per date/site, where N is the number of parallel requests on this same idsite/date
            // that happen to run in overlapping transactions.
            $mykey = $this->buildRememberArchivedReportIdProcessSafe($idSite, $date->toString());
            Option::set($mykey, '1');
            Cache::clearCacheGeneral();
            return $mykey;
        }
    }

    private function getRememberedArchivedReportsOptionFromTracker($idSite, $dateStr)
    {
        $cacheKey = self::TRACKER_CACHE_KEY;

        $generalCache = Cache::getCacheGeneral();
        if (empty($generalCache[$cacheKey][$idSite][$dateStr])) {
            return [];
        }

        return $generalCache[$cacheKey][$idSite][$dateStr];
    }

    public function getRememberedArchivedReportsThatShouldBeInvalidated()
    {
        $reports = Option::getLike('%' . $this->rememberArchivedReportIdStart . '%_%');

        $sitesPerDay = array();

        foreach ($reports as $report => $value) {
            $report = substr($report, strpos($report, $this->rememberArchivedReportIdStart));
            $report = str_replace($this->rememberArchivedReportIdStart, '', $report);
            $report = explode('_', $report);
            $siteId = (int) $report[0];
            $date   = $report[1];

            if (empty($siteId)) {
                continue;
            }

            if (empty($sitesPerDay[$date])) {
                $sitesPerDay[$date] = array();
            }

            $sitesPerDay[$date][] = $siteId;
        }

        return $sitesPerDay;
    }

    private function buildRememberArchivedReportIdForSite($idSite)
    {
        return $this->rememberArchivedReportIdStart . (int) $idSite;
    }
    
    private function buildRememberArchivedReportIdForSiteAndDate($idSite, $date)
    {
        $id  = $this->buildRememberArchivedReportIdForSite($idSite);
        $id .= '_' . trim($date);

        return $id;
    }

    // This version is multi process safe on the insert of a new date to invalidate.
    private function buildRememberArchivedReportIdProcessSafe($idSite, $date)
    {
        $id = Common::getRandomString(4, 'abcdefghijklmnoprstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ') . '_';
        $id .= $this->buildRememberArchivedReportIdForSiteAndDate($idSite, $date);
        $id .= '_' . Common::getProcessId();

        return $id;
    }

    public function forgetRememberedArchivedReportsToInvalidateForSite($idSite)
    {
        $id = $this->buildRememberArchivedReportIdForSite($idSite);
        $this->deleteOptionLike($id);
        Cache::clearCacheGeneral();
    }

    /**
     * @internal
     * After calling this method, make sure to call Cache::clearCacheGeneral(); For performance reasons we don't call
     * this here immediately in case there are multiple invalidations.
     */
    public function forgetRememberedArchivedReportsToInvalidate($idSite, Date $date)
    {
        $id = $this->buildRememberArchivedReportIdForSiteAndDate($idSite, $date->toString());

        // The process pid is added to the end of the entry in order to support multiple concurrent transactions.
        //  So this must be a deleteLike call to get all the entries, where there used to only be one.
        $this->deleteOptionLike($id);
        Cache::clearCacheGeneral();
    }

    private function deleteOptionLike($id)
    {
        // we're not using deleteLike since it maybe could cause deadlocks see https://github.com/matomo-org/matomo/issues/15545
        // we want to reduce number of rows scanned and only delete specific primary key
        $keys = Option::getLike('%' . $id . '%');

        if (empty($keys)) {
            return;
        }

        $keys = array_keys($keys);

        $placeholders = Common::getSqlStringFieldsArray($keys);

        $table = Common::prefixTable('option');
        Db::query('DELETE FROM `' . $table . '` WHERE `option_name` IN (' . $placeholders . ')', $keys);
    }

    /**
     * @param $idSites int[]
     * @param $dates Date[]
     * @param $period string
     * @param $segment Segment
     * @param bool $cascadeDown
     * @return InvalidationResult
     * @throws \Exception
     */
    public function markArchivesAsInvalidated(array $idSites, array $dates, $period, Segment $segment = null, $cascadeDown = false)
    {
        $invalidationInfo = new InvalidationResult();

        // quick fix for #15086, if we're only invalidating today's date for a site, don't add the site to the list of sites
        // to reprocess.
        $hasMoreThanJustToday = [];
        foreach ($idSites as $idSite) {
            $hasMoreThanJustToday[$idSite] = true;
            $tz = Site::getTimezoneFor($idSite);

            if (($period == 'day' || $period === false)
                && count($dates) == 1
                && $dates[0]->toString() == Date::factoryInTimezone('today', $tz)
            ) {
                $hasMoreThanJustToday[$idSite] = false;
            }
        }

        /**
         * Triggered when a Matomo user requested the invalidation of some reporting archives. Using this event, plugin
         * developers can automatically invalidate another site, when a site is being invalidated. A plugin may even
         * remove an idSite from the list of sites that should be invalidated to prevent it from ever being
         * invalidated.
         *
         * **Example**
         *
         *     public function getIdSitesToMarkArchivesAsInvalidates(&$idSites)
         *     {
         *         if (in_array(1, $idSites)) {
         *             $idSites[] = 5; // when idSite 1 is being invalidated, also invalidate idSite 5
         *         }
         *     }
         *
         * @param array &$idSites An array containing a list of site IDs which are requested to be invalidated.
         */
        Piwik::postEvent('Archiving.getIdSitesToMarkArchivesAsInvalidated', array(&$idSites));
        // we trigger above event on purpose here and it is good that the segment was created like
        // `new Segment($segmentString, $idSites)` because when a user adds a site via this event, the added idSite
        // might not have this segment meaning we avoid a possible error. For the workflow to work, any added or removed
        // idSite does not need to be added to $segment.

        $datesToInvalidate = $this->removeDatesThatHaveBeenPurged($dates, $invalidationInfo);

        if (empty($period)) {
            // if the period is empty, we don't need to cascade in any way, since we'll remove all periods
            $periodDates = $this->getDatesByYearMonthAndPeriodType($dates);
        } else {
            $periods = $this->getPeriodsToInvalidate($datesToInvalidate, $period, $cascadeDown);
            $periodDates = $this->getPeriodDatesByYearMonthAndPeriodType($periods);
        }

        $periodDates = $this->getUniqueDates($periodDates);

        $this->markArchivesInvalidated($idSites, $periodDates, $segment);

        $yearMonths = array_keys($periodDates);
        $this->markInvalidatedArchivesForReprocessAndPurge($idSites, $yearMonths, $hasMoreThanJustToday);

        foreach ($idSites as $idSite) {
            foreach ($dates as $date) {
                $this->forgetRememberedArchivedReportsToInvalidate($idSite, $date);
            }
        }
        Cache::clearCacheGeneral();

        return $invalidationInfo;
    }

    /**
     * @param $idSites int[]
     * @param $dates Date[]
     * @param $period string
     * @param $segment Segment
     * @param bool $cascadeDown
     * @return InvalidationResult
     * @throws \Exception
     */
    public function markArchivesOverlappingRangeAsInvalidated(array $idSites, array $dates, Segment $segment = null)
    {
        $invalidationInfo = new InvalidationResult();

        $ranges = array();
        foreach ($dates as $dateRange) {
            $ranges[] = $dateRange[0] . ',' . $dateRange[1];
        }
        $periodsByType = array(Period\Range::PERIOD_ID => $ranges);

        $invalidatedMonths = array();
        $archiveNumericTables = ArchiveTableCreator::getTablesArchivesInstalled($type = ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveNumericTables as $table) {
            $tableDate = ArchiveTableCreator::getDateFromTableName($table);

            $result = $this->model->updateArchiveAsInvalidated($table, $idSites, $periodsByType, $segment);
            $rowsAffected = $result->rowCount();
            if ($rowsAffected > 0) {
                $invalidatedMonths[] = $tableDate;
            }
        }

        foreach ($idSites as $idSite) {
            foreach ($dates as $dateRange) {
                $this->forgetRememberedArchivedReportsToInvalidate($idSite, $dateRange[0]);
                $invalidationInfo->processedDates[] = $dateRange[0];
            }
        }

        Cache::clearCacheGeneral();

        $archivesToPurge = new ArchivesToPurgeDistributedList();
        $archivesToPurge->add($invalidatedMonths);

        return $invalidationInfo;
    }

    /**
     * @param string[][][] $periodDates
     * @return string[][][]
     */
    private function getUniqueDates($periodDates)
    {
        $result = array();
        foreach ($periodDates as $yearMonth => $periodsByYearMonth) {
            foreach ($periodsByYearMonth as $periodType => $periods) {
                $result[$yearMonth][$periodType] = array_unique($periods);
            }
        }
        return $result;
    }

    /**
     * @param Date[] $dates
     * @param string $periodType
     * @param bool $cascadeDown
     * @return Period[]
     */
    private function getPeriodsToInvalidate($dates, $periodType, $cascadeDown)
    {
        $periodsToInvalidate = array();

        if ($periodType == 'range') {
            $rangeString = $dates[0] . ',' . $dates[1];
            $periodsToInvalidate[] = Period\Factory::build('range', $rangeString);
            return $periodsToInvalidate;
        }

        foreach ($dates as $date) {
            $period = Period\Factory::build($periodType, $date);
            $periodsToInvalidate[] = $period;

            if ($cascadeDown) {
                $periodsToInvalidate = array_merge($periodsToInvalidate, $period->getAllOverlappingChildPeriods());
            }

            if ($periodType != 'year') {
                $periodsToInvalidate[] = Period\Factory::build('year', $date);
            }
        }

        return $periodsToInvalidate;
    }

    /**
     * @param Period[] $periods
     * @return string[][][]
     */
    private function getPeriodDatesByYearMonthAndPeriodType($periods)
    {
        $result = array();
        foreach ($periods as $period) {
            $date = $period->getDateStart();
            $periodType = $period->getId();

            $yearMonth = ArchiveTableCreator::getTableMonthFromDate($date);
            $dateString = $date->toString();
            if ($periodType == Period\Range::PERIOD_ID) {
                $dateString = $period->getRangeString();
            }
            $result[$yearMonth][$periodType][] = $dateString;
        }
        return $result;
    }

    /**
     * Called when deleting all periods.
     *
     * @param Date[] $dates
     * @return string[][][]
     */
    private function getDatesByYearMonthAndPeriodType($dates)
    {
        $result = array();
        foreach ($dates as $date) {
            $yearMonth = ArchiveTableCreator::getTableMonthFromDate($date);
            $result[$yearMonth][null][] = $date->toString();

            // since we're removing all periods, we must make sure to remove year periods as well.
            // this means we have to make sure the january table is processed.
            $janYearMonth = $date->toString('Y') . '_01';
            $result[$janYearMonth][null][] = $date->toString();
        }
        return $result;
    }

    /**
     * @param int[] $idSites
     * @param string[][][] $dates
     * @throws \Exception
     */
    private function markArchivesInvalidated($idSites, $dates, Segment $segment = null)
    {
        $archiveNumericTables = ArchiveTableCreator::getTablesArchivesInstalled($type = ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveNumericTables as $table) {
            $tableDate = ArchiveTableCreator::getDateFromTableName($table);
            if (empty($dates[$tableDate])) {
                continue;
            }

            $this->model->updateArchiveAsInvalidated($table, $idSites, $dates[$tableDate], $segment);
        }
    }

    /**
     * @param Date[] $dates
     * @param InvalidationResult $invalidationInfo
     * @return \Piwik\Date[]
     */
    private function removeDatesThatHaveBeenPurged($dates, InvalidationResult $invalidationInfo)
    {
        $this->findOlderDateWithLogs($invalidationInfo);

        $result = array();
        foreach ($dates as $date) {
            // we should only delete reports for dates that are more recent than N days
            if ($invalidationInfo->minimumDateWithLogs
                && $date->isEarlier($invalidationInfo->minimumDateWithLogs)
            ) {
                $invalidationInfo->warningDates[] = $date->toString();
                continue;
            }

            $result[] = $date;
            $invalidationInfo->processedDates[] = $date->toString();
        }
        return $result;
    }

    private function findOlderDateWithLogs(InvalidationResult $info)
    {
        // If using the feature "Delete logs older than N days"...
        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();
        $logsDeletedWhenOlderThanDays = (int)$purgeDataSettings['delete_logs_older_than'];
        $logsDeleteEnabled = $purgeDataSettings['delete_logs_enable'];

        if ($logsDeleteEnabled
            && $logsDeletedWhenOlderThanDays
        ) {
            $info->minimumDateWithLogs = Date::factory('today')->subDay($logsDeletedWhenOlderThanDays);
        }
    }

    /**
     * @param array $idSites
     * @param array $yearMonths
     */
    private function markInvalidatedArchivesForReprocessAndPurge(array $idSites, $yearMonths, $hasMoreThanJustToday)
    {
        $store = new SitesToReprocessDistributedList();
        foreach ($idSites as $idSite) {
            if (!empty($hasMoreThanJustToday[$idSite])) {
                $store->add($idSite);
            }
        }

        $archivesToPurge = new ArchivesToPurgeDistributedList();
        $archivesToPurge->add($yearMonths);
    }
}
