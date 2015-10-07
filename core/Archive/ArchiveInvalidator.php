<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Archive;

use Piwik\Archive\ArchiveInvalidator\InvalidationResultInfo;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Period;

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
    private $rememberArchivedReportIdStart = 'report_to_invalidate_';

    /**
     * @var Model
     */
    private $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

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
     * @param $idSites int[]
     * @param $dates Date[]
     * @param $period string TODO: make multiple
     * @param bool $cascadeDown
     * @return InvalidationResultInfo
     * @throws \Exception
     */
    public function markArchivesAsInvalidated(array $idSites, array $dates, $period, $cascadeDown = false)
    {
        $invalidationInfo = new InvalidationResultInfo();

        $datesToInvalidate = $this->removeDatesThatHaveBeenPurged($dates, $invalidationInfo);

        $periods = $this->getPeriodsToInvalidate($datesToInvalidate, $period, $cascadeDown);
        $periods = $this->getPeriodsByYearMonthAndType($periods);
        $this->markArchivesInvalidated($idSites, $periods);

        $yearMonths = array_keys($periods);
        $this->markInvalidatedArchivesForReprocessAndPurge($idSites, $yearMonths);

        foreach ($idSites as $idSite) {
            foreach ($dates as $date) {
                $this->forgetRememberedArchivedReportsToInvalidate($idSite, $date);
            }
        }

        return $invalidationInfo;
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

        foreach ($dates as $date) {
            $period = Period\Factory::build($periodType, $date);
            $periodsToInvalidate[] = $period;

            // cascade up since parent archives will no longer be valid
            $periodsToInvalidate = array_merge($periodsToInvalidate, $period->getAllParentPeriods());

            if ($cascadeDown) {
                $periodsToInvalidate = array_merge($periodsToInvalidate, $period->getAllOverlappingChildPeriods());
            }
        }

        return $this->getUniquePeriods($periodsToInvalidate);
    }

    /**
     * @param Period[] $periods
     * @return Period[]
     */
    private function getUniquePeriods($periods)
    {
        $result = array();
        foreach ($periods as $period) {
            $result[$period->getRangeString()] = $period;
        }
        return array_values($result);
    }

    /**
     * @param Period[] $periods
     * @return Period[][][]
     */
    private function getPeriodsByYearMonthAndType($periods)
    {
        $result = array();
        foreach ($periods as $period) {
            $yearMonth = ArchiveTableCreator::getTableMonthFromDate($period->getDateStart());
            $periodType = $period->getId();

            $result[$yearMonth][$periodType][] = $period;
        }
        return $result;
    }

    /**
     * @param int[] $idSites
     * @param Period[][] $periods
     * @throws \Exception
     */
    private function markArchivesInvalidated($idSites, $periods)
    {
        $archiveNumericTables = ArchiveTableCreator::getTablesArchivesInstalled($type = ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveNumericTables as $table) {
            $tableDate = ArchiveTableCreator::getDateFromTableName($table);
            if (empty($periods[$tableDate])) {
                continue;
            }

            $this->model->updateArchiveAsInvalidated($table, $idSites, $periods[$tableDate]);
        }
    }

    /**
     * @param Date[] $dates
     * @param InvalidationResultInfo $invalidationInfo
     * @return \Piwik\Date[]
     */
    private function removeDatesThatHaveBeenPurged($dates, InvalidationResultInfo $invalidationInfo)
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

    private function findOlderDateWithLogs(InvalidationResultInfo $info)
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
    private function markInvalidatedArchivesForReprocessAndPurge(array $idSites, $yearMonths)
    {
        $store = new SitesToReprocessDistributedList();
        $store->add($idSites);

        $archivesToPurge = new ArchivesToPurgeDistributedList();
        $archivesToPurge->add($yearMonths);
    }
}
