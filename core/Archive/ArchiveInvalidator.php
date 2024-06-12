<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Archive;

use Piwik\Archive\ArchiveInvalidator\InvalidationResult;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\ReArchiveList;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Segment;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Log\LoggerInterface;

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
    public const TRACKER_CACHE_KEY = 'ArchiveInvalidator.rememberToInvalidate';

    public const INVALIDATION_STATUS_QUEUED = 0;
    public const INVALIDATION_STATUS_IN_PROGRESS = 1;

    private $rememberArchivedReportIdStart = 'report_to_invalidate_';

    /**
     * @var Model
     */
    private $model;

    /**
     * @var SegmentArchiving
     */
    private $segmentArchiving;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int[]
     */
    private $allIdSitesCache;

    public function __construct(Model $model, LoggerInterface $logger)
    {
        $this->model = $model;
        $this->segmentArchiving = null;
        $this->logger = $logger;
    }

    public function getAllRememberToInvalidateArchivedReportsLater()
    {
        // we do not really have to get the value first. we could simply always try to call set() and it would update or
        // insert the record if needed but we do not want to lock the table (especially since there are still some
        // MyISAM installations)
        $values = Option::getLike('%' . str_replace('_', '\_', $this->rememberArchivedReportIdStart) . '%');

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
            $value = Option::getLike('%' . str_replace('_', '\_', $key) . '%');
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

    public function getDaysWithRememberedInvalidationsForSite(int $idSite): array
    {
        return array_keys($this->getRememberedArchivedReportsThatShouldBeInvalidated($idSite));
    }

    public function getRememberedArchivedReportsThatShouldBeInvalidated(int $idSite = null)
    {
        if (null === $idSite) {
            $optionName = $this->rememberArchivedReportIdStart . '%';
        } else {
            $optionName = $this->buildRememberArchivedReportIdForSite($idSite);
        }

        $reports = Option::getLike('%' . str_replace('_', '\_', $optionName) . '\_%');
        $sitesPerDay = [];

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
                $sitesPerDay[$date] = [];
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
        $id = $this->buildRememberArchivedReportIdForSite($idSite) . '_';
        $hasDeletedSomething = $this->deleteOptionLike($id);
        if ($hasDeletedSomething) {
            Cache::clearCacheGeneral();
        }
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
        return $this->deleteOptionLike($id);
    }

    /**
     * @param $id
     * @return bool true if a record was deleted, false otherwise.
     * @throws \Zend_Db_Statement_Exception
     */
    private function deleteOptionLike($id)
    {
        // we're not using deleteLike since it maybe could cause deadlocks see https://github.com/matomo-org/matomo/issues/15545
        // we want to reduce number of rows scanned and only delete specific primary key
        $keys = Option::getLike('%' . str_replace('_', '\_', $id) . '%');

        if (empty($keys)) {
            return false;
        }

        $keys = array_keys($keys);

        $placeholders = Common::getSqlStringFieldsArray($keys);

        $table = Common::prefixTable('option');
        $db = Db::query('DELETE FROM `' . $table . '` WHERE `option_name` IN (' . $placeholders . ')', $keys);
        return (bool) $db->rowCount();
    }

    /**
     * @param $idSites int[]
     * @param $dates Date[]|string[]
     * @param $period string
     * @param $segment Segment
     * @param bool $cascadeDown
     * @param bool $forceInvalidateNonexistentRanges set true to force inserting rows for ranges in archive_invalidations
     * @param string $name null to make sure every plugin is archived when this invalidation is processed by core:archive,
     *                     or a plugin name to only archive the specific plugin.
     * @param bool $ignorePurgeLogDataDate
     * @param bool $doNotCreateInvalidations If true, archives will only be marked as invalid, but no archive_invalidation record will be created
     * @return InvalidationResult
     * @throws \Exception
     */
    public function markArchivesAsInvalidated(
        array $idSites,
        array $dates,
        $period,
        Segment $segment = null,
        $cascadeDown = false,
        $forceInvalidateNonexistentRanges = false,
        $name = null,
        $ignorePurgeLogDataDate = false,
        $doNotCreateInvalidations = false
    ) {
        $plugin = null;
        if ($name && strpos($name, '.') !== false) {
            list($plugin) = explode('.', $name);
        } elseif ($name) {
            $plugin = $name;
        }

        if (
            $plugin
            && !Manager::getInstance()->isPluginActivated($plugin)
        ) {
            throw new \Exception("Plugin is not activated: '$plugin'");
        }

        $invalidationInfo = new InvalidationResult();

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
         * @param array $dates An array containing the dates to invalidate.
         * @param string $period A string containing the period to be invalidated.
         * @param Segment $segment A Segment Object containing segment to invalidate.
         * @param string $name A string containing the name of the archive to be invalidated.
         * @param bool $isPrivacyDeleteData A boolean value if event is triggered via Privacy delete visit action.
         */
        Piwik::postEvent('Archiving.getIdSitesToMarkArchivesAsInvalidated', array(&$idSites, $dates, $period, $segment, $name, $isPrivacyDeleteData = false));
        // we trigger above event on purpose here and it is good that the segment was created like
        // `new Segment($segmentString, $idSites)` because when a user adds a site via this event, the added idSite
        // might not have this segment meaning we avoid a possible error. For the workflow to work, any added or removed
        // idSite does not need to be added to $segment.

        $datesToInvalidate = $this->removeDatesThatHaveBeenPurged($dates, $period, $invalidationInfo, $ignorePurgeLogDataDate);

        $allPeriodsToInvalidate = $this->getAllPeriodsByYearMonth($period, $datesToInvalidate, $cascadeDown);

        $this->markArchivesInvalidated($idSites, $allPeriodsToInvalidate, $segment, $period != 'range', $forceInvalidateNonexistentRanges, $name, $doNotCreateInvalidations);

        $isInvalidatingDays = $period == 'day' || $cascadeDown || empty($period);
        $isNotInvalidatingSegment = empty($segment) || empty($segment->getString());

        if (
            $isInvalidatingDays
            && $isNotInvalidatingSegment
        ) {
            $hasDeletedAny = false;

            foreach ($idSites as $idSite) {
                foreach ($dates as $date) {
                    if (is_string($date)) {
                        $date = Date::factory($date);
                    }

                    $hasDeletedAny = $this->forgetRememberedArchivedReportsToInvalidate($idSite, $date) || $hasDeletedAny;
                }
            }

            if ($hasDeletedAny) {
                Cache::clearCacheGeneral();
            }
        }

        return $invalidationInfo;
    }

    private function getAllPeriodsByYearMonth($periodOrAll, $dates, $cascadeDown, &$result = [])
    {
        $periods = $periodOrAll ? [$periodOrAll] : ['day'];
        foreach ($periods as $period) {
            foreach ($dates as $date) {
                $periodObj = $this->makePeriod($date, $period);

                $result[$this->getYearMonth($periodObj)][$this->getUniquePeriodId($periodObj)] = $periodObj;

                // cascade down
                if (
                    $cascadeDown
                    && $period != 'range'
                ) {
                    $this->addChildPeriodsByYearMonth($result, $periodObj);
                }

                // cascade up
                // if the period spans multiple years or months, it won't be used when aggregating parent periods, so
                // we can avoid invalidating it
                if (
                    $this->shouldPropagateUp($periodObj)
                    && $period != 'range'
                ) {
                    $this->addParentPeriodsByYearMonth($result, $periodObj);
                }
            }
        }

        return $result;
    }

    private function shouldPropagateUp(Period $periodObj)
    {
        return $periodObj->getDateStart()->toString('Y') == $periodObj->getDateEnd()->toString('Y')
            && $periodObj->getDateStart()->toString('m') == $periodObj->getDateEnd()->toString('m');
    }

    private function addChildPeriodsByYearMonth(&$result, Period $period)
    {
        if ($period->getLabel() == 'range') {
            return;
        } elseif (
            $period->getLabel() == 'day'
            && $this->shouldPropagateUp($period)
        ) {
            $this->addParentPeriodsByYearMonth($result, $period);
            return;
        }

        foreach ($period->getSubperiods() as $subperiod) {
            $result[$this->getYearMonth($subperiod)][$this->getUniquePeriodId($subperiod)] = $subperiod;
            $this->addChildPeriodsByYearMonth($result, $subperiod);
        }
    }

    private function addParentPeriodsByYearMonth(&$result, Period $period, Date $originalDate = null)
    {
        if (
            $period->getLabel() == 'year'
            || $period->getLabel() == 'range'
            || !Period\Factory::isPeriodEnabledForAPI($period->getParentPeriodLabel())
        ) {
            return;
        }

        $originalDate = $originalDate ?? $period->getDateStart();

        $parentPeriod = Period\Factory::build($period->getParentPeriodLabel(), $originalDate);
        $result[$this->getYearMonth($parentPeriod)][$this->getUniquePeriodId($parentPeriod)] = $parentPeriod;
        $this->addParentPeriodsByYearMonth($result, $parentPeriod, $originalDate);
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
            $ranges[] = Period\Factory::build('range', $dateRange[0] . ',' . $dateRange[1]);
        }

        $invalidatedMonths = array();
        $archiveNumericTables = ArchiveTableCreator::getTablesArchivesInstalled($type = ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveNumericTables as $table) {
            $tableDate = ArchiveTableCreator::getDateFromTableName($table);

            $rowsAffected = $this->model->updateArchiveAsInvalidated($table, $idSites, $ranges, $segment);
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

        return $invalidationInfo;
    }

    /**
     * Schedule rearchiving of reports for a single plugin or single report for N months in the past. The next time
     * core:archive is run, they will be processed.
     *
     * @param int[]|string $idSites A list of idSites or 'all'
     * @param string $plugin
     * @param string|null $report
     * @param Date|null $startDate
     * @throws \Exception
     * @api
     */
    public function reArchiveReport($idSites, string $plugin = null, string $report = null, Date $startDate = null, Segment $segment = null)
    {
        $date2 = Date::today();

        $earliestDateToRearchive = Piwik::getEarliestDateToRearchive();
        if (empty($startDate)) {
            if (empty($earliestDateToRearchive)) {
                return null; // INI setting set to 0 months so no rearchiving
            }

            $startDate = $earliestDateToRearchive;
        } elseif (!empty($earliestDateToRearchive)) {
            // don't allow archiving further back than the rearchive_reports_in_past_last_n_months date allows
            $startDate = $startDate->isEarlier($earliestDateToRearchive) ? $earliestDateToRearchive : $startDate;
        }

        if ($idSites === 'all') {
            $idSites = $this->getAllSitesId();
        }

        $dates = [];
        $date = $startDate;
        while ($date->isEarlier($date2)) {
            $dates[] = $date;
            $date = $date->addDay(1);
        }

        if (empty($dates)) {
            return;
        }

        $name = $plugin;
        if (!empty($report)) {
            $name .= '.' . $report;
        }

        $this->markArchivesAsInvalidated($idSites, $dates, 'day', $segment, $cascadeDown = false, $forceInvalidateRanges = false, $name);
        if (
            empty($segment)
            && Rules::shouldProcessSegmentsWhenReArchivingReports()
        ) {
            foreach ($idSites as $idSite) {
                foreach (Rules::getSegmentsToProcess([$idSite]) as $segment) {
                    $this->markArchivesAsInvalidated(
                        $idSites,
                        $dates,
                        'day',
                        new Segment($segment, [$idSite]),
                        $cascadeDown = false,
                        $forceInvalidateRanges = false,
                        $name
                    );
                }
            }
        }
    }

    /**
     * Remove invalidations for a specific report or all invalidations for a specific plugin. If your plugin supports
     * archiving data in the past, you may want to call this method to remove any pending invalidations if, for example,
     * your plugin is deactivated or a report deleted.
     *
     * @param int|int[] $idSite one or more site IDs or 'all' for all site IDs
     * @param string $string
     * @param string|null $report
     */
    public function removeInvalidations($idSite, $plugin, $report = null)
    {
        if (empty($report)) {
            $this->model->removeInvalidationsLike($idSite, $plugin);
        } else {
            $this->model->removeInvalidations($idSite, $plugin, $report);
        }
    }

    /**
     * Schedules a re-archiving reports without propagating exceptions. This is scheduled
     * since adding invalidations can take a long time and delay UI response times.
     *
     * @param int|int[]|'all' $idSites
     * @param string|int $pluginName
     * @param string|null $report
     * @param Date|null $startDate
     */
    public function scheduleReArchiving(
        $idSites,
        string $pluginName = null,
        $report = null,
        Date $startDate = null,
        Segment $segment = null
    ) {
        if (!empty($report)) {
            $this->removeInvalidationsSafely($idSites, $pluginName, $report);
        }
        try {
            $reArchiveList = new ReArchiveList($this->logger);
            $reArchiveList->add(json_encode([
                'idSites' => $idSites,
                'pluginName' => $pluginName,
                'report' => $report,
                'startDate' => $startDate ? $startDate->getTimestamp() : null,
                'segment' => $segment ? $segment->getOriginalString() : null,
            ]));
        } catch (\Throwable $ex) {
            $this->logger->info("Failed to schedule rearchiving of past reports for $pluginName plugin.");
        }
    }

    /**
     * Applies the queued archiving rearchiving entries.
     */
    public function applyScheduledReArchiving()
    {
        $reArchiveList = new ReArchiveList($this->logger);
        $items = $reArchiveList->getAll();

        foreach ($items as $item) {
            try {
                $entry = @json_decode($item, true);
                if (empty($entry)) {
                    continue;
                }

                $idSites = Site::getIdSitesFromIdSitesString($entry['idSites']);

                $this->reArchiveReport(
                    $idSites,
                    $entry['pluginName'],
                    $entry['report'],
                    !empty($entry['startDate']) ? Date::factory((int) $entry['startDate']) : null,
                    !empty($entry['segment']) ? new Segment($entry['segment'], $idSites) : null
                );
            } catch (\Throwable $ex) {
                $this->logger->info("Failed to create invalidations for report re-archiving (idSites = {idSites}, pluginName = {pluginName}, report = {report}, startDate = {startDateTs}): {ex}", [
                    'idSites' => json_encode($entry['idSites']),
                    'pluginName' => $entry['pluginName'],
                    'report' => $entry['report'],
                    'startDateTs' => $entry['startDate'],
                    'ex' => $ex,
                ]);
            } finally {
                $reArchiveList->remove([$item]);
            }
        }
    }

    /**
     * Calls removeInvalidations() without propagating exceptions.
     *
     * @param int|int[]|'all' $idSites
     * @param string $pluginName
     * @param string|null $report
     */
    public function removeInvalidationsSafely($idSites, $pluginName, $report = null)
    {
        try {
            $this->removeInvalidations($idSites, $pluginName, $report);
            $this->removeInvalidationsFromDistributedList($idSites, $pluginName, $report);
        } catch (\Throwable $ex) {
            $logger = StaticContainer::get(LoggerInterface::class);
            $logger->debug("Failed to remove invalidations the for $pluginName plugin.");
        }
    }

    public function removeInvalidationsFromDistributedList($idSites, $pluginName = null, $report = null)
    {
        $list = new ReArchiveList();
        $entries = $list->getAll();

        if ($idSites === 'all') {
            $idSites = $this->getAllSitesId();
        }

        foreach ($entries as $index => $entry) {
            $entry = @json_decode($entry, true);
            if (empty($entry)) {
                unset($entries[$index]);
                continue;
            }

            $entryPluginName = $entry['pluginName'];
            if (
                !empty($pluginName)
                && $pluginName != $entryPluginName
            ) {
                continue;
            }

            $entryReport = $entry['report'];
            if (
                !empty($pluginName)
                && !empty($report)
                && $report != $entryReport
            ) {
                continue;
            }

            $sitesInEntry = $entry['idSites'];
            if ($sitesInEntry === 'all') {
                $sitesInEntry = $this->getAllSitesId();
            }

            $diffSites = array_diff($sitesInEntry, $idSites);
            if (empty($diffSites)) {
                unset($entries[$index]);
                continue;
            }

            $entry['idSites'] = $diffSites;

            $entries[$index] = json_encode($entry);
        }

        $list->setAll(array_values($entries));
    }

    /**
     * @param int[] $idSites
     * @param string[][][] $dates
     * @throws \Exception
     */
    private function markArchivesInvalidated(
        $idSites,
        $dates,
        Segment $segment = null,
        $removeRanges = false,
        $forceInvalidateNonexistentRanges = false,
        $name = null,
        $doNotCreateInvalidations = false
    ) {
        $idSites = array_map('intval', $idSites);

        $yearMonths = [];

        foreach ($dates as $tableDate => $datesForTable) {
            $tableDateObj = Date::factory($tableDate);

            $table = ArchiveTableCreator::getNumericTable($tableDateObj);
            $yearMonths[] = $tableDateObj->toString('Y_m');

            $this->model->updateArchiveAsInvalidated($table, $idSites, $datesForTable, $segment, $forceInvalidateNonexistentRanges, $name, $doNotCreateInvalidations);

            if ($removeRanges) {
                $this->model->updateRangeArchiveAsInvalidated($table, $idSites, $datesForTable, $segment);
            }
        }

        $this->markInvalidatedArchivesForReprocessAndPurge($yearMonths);
    }

    /**
     * @param Date[] $dates
     * @param InvalidationResult $invalidationInfo
     * @return \Piwik\Date[]
     */
    private function removeDatesThatHaveBeenPurged($dates, $period, InvalidationResult $invalidationInfo, $ignorePurgeLogDataDate)
    {
        $this->findOlderDateWithLogs($invalidationInfo);

        $result = array();
        foreach ($dates as $date) {
            $periodObj = $this->makePeriod($date, $period ?: 'day');

            // we should only delete reports for dates that are more recent than N days
            if (
                $invalidationInfo->minimumDateWithLogs
                && !$ignorePurgeLogDataDate
                && ($periodObj->getDateEnd()->isEarlier($invalidationInfo->minimumDateWithLogs)
                    || $periodObj->getDateStart()->isEarlier($invalidationInfo->minimumDateWithLogs))
            ) {
                $invalidationInfo->warningDates[] = $date;
                continue;
            }

            $result[] = $date;
            $invalidationInfo->processedDates[] = $date;
        }
        return $result;
    }

    private function findOlderDateWithLogs(InvalidationResult $info)
    {
        // If using the feature "Delete logs older than N days"...
        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();
        $logsDeletedWhenOlderThanDays = (int)$purgeDataSettings['delete_logs_older_than'];
        $logsDeleteEnabled = $purgeDataSettings['delete_logs_enable'];

        if (
            $logsDeleteEnabled
            && $logsDeletedWhenOlderThanDays
        ) {
            $info->minimumDateWithLogs = Date::factory('today')->subDay($logsDeletedWhenOlderThanDays);
        }
    }

    /**
     * @param array $idSites
     * @param array $yearMonths
     */
    private function markInvalidatedArchivesForReprocessAndPurge($yearMonths)
    {
        $archivesToPurge = new ArchivesToPurgeDistributedList();
        $archivesToPurge->add($yearMonths);
    }

    private function getYearMonth(Period $period)
    {
        return $period->getDateStart()->toString('Y-m-01');
    }

    private function getUniquePeriodId(Period $period)
    {
        return $period->getId() . '.' . $period->getRangeString();
    }

    private function makePeriod($date, $period)
    {
        if (
            $period === 'range'
            && strpos($date, ',') === false
        ) {
            $date = $date . ',' . $date;
            return new Period\Range('range', $date);
        } else {
            return Period\Factory::build($period, $date);
        }
    }

    private function getSegmentArchiving()
    {
        if (empty($this->segmentArchiving)) {
            $this->segmentArchiving = new SegmentArchiving();
        }
        return $this->segmentArchiving;
    }

    private function getAllSitesId()
    {
        if (isset($this->allIdSitesCache)) {
            return $this->allIdSitesCache;
        }

        $model = new \Piwik\Plugins\SitesManager\Model();
        $this->allIdSitesCache = $model->getSitesId();
        return $this->allIdSitesCache;
    }
}
