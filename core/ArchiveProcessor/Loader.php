<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ArchiveProcessor;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\ArchiveProcessor;
use Piwik\Cache;
use Piwik\CacheId;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Context;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\DataAccess\Model;
use Piwik\DataAccess\RawLogDao;
use Piwik\Date;
use Piwik\Period;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Log\LoggerInterface;
use Piwik\CronArchive\SegmentArchiving;

/**
 * This class uses PluginsArchiver class to trigger data aggregation and create archives.
 */
class Loader
{
    private static $archivingDepth = 0;

    /**
     * @var Parameters
     */
    protected $params;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    /**
     * @var \Matomo\Cache\Cache
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    /**
     * @var Model
     */
    private $dataAccessModel;

    /**
     * @var bool
     */
    private $invalidateBeforeArchiving;

    public function __construct(Parameters $params, $invalidateBeforeArchiving = false)
    {
        $this->params = $params;
        $this->invalidateBeforeArchiving = $invalidateBeforeArchiving;
        $this->invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $this->cache = Cache::getTransientCache();
        $this->logger = StaticContainer::get(LoggerInterface::class);
        $this->rawLogDao = new RawLogDao();
        $this->dataAccessModel = new Model();
    }

    /**
     * @return bool
     */
    protected function isThereSomeVisits($visits)
    {
        return $visits > 0;
    }

    /**
     * @return bool
     */
    protected function mustProcessVisitCount($visits)
    {
        return $visits === false;
    }

    public function prepareArchive($pluginName)
    {
        return Context::changeIdSite($this->params->getSite()->getId(), function () use ($pluginName) {
            try {
                ++self::$archivingDepth;
                return $this->prepareArchiveImpl($pluginName);
            } finally {
                --self::$archivingDepth;
            }
        });
    }

    /**
     * @throws \Exception
     */
    private function prepareArchiveImpl($pluginName)
    {
        $this->params->setRequestedPlugin($pluginName);

        if (SettingsServer::isArchivePhpTriggered()) {
            $requestedReport = Common::getRequestVar('requestedReport', '', 'string');
            if (!empty($requestedReport)) {
                $this->params->setArchiveOnlyReport($requestedReport);
            }
        }

        // invalidate existing archives before we start archiving in case data was tracked in the past. if the archive is
        // made invalid, we will correctly re-archive below.
        if (
            $this->invalidateBeforeArchiving
            && Rules::isBrowserTriggerEnabled()
        ) {
            $this->invalidatedReportsIfNeeded();
        }
        // load existing data from archive
        $data = $this->loadArchiveData();
        if (sizeof($data) == 2) {
            return $data;
        }
        [$idArchives, $visits, $visitsConverted, $foundRecords] = $data;

        // only lock meet those conditions
        if (ArchiveProcessor::$isRootArchivingRequest && !SettingsServer::isArchivePhpTriggered()) {
            $lockId = $this->makeArchivingLockId();

            //ini lock
            $lock = new LoaderLock($lockId);

            //set mysql lock the entire process if another process is running
            $lock->setLock();

            try {
                $data = $this->loadArchiveData();

                if (sizeof($data) == 2) {
                    return $data;
                }

                [$idArchives, $visits, $visitsConverted, $foundRecords] = $data;

                return $this->insertArchiveData($visits, $visitsConverted, $idArchives, $foundRecords);
            } finally {
                $lock->unlock();
            }
        } else {
            return $this->insertArchiveData($visits, $visitsConverted, $idArchives, $foundRecords);
        }
    }


    /**
     * @param $visits
     * @param $visitsConverted
     * @return int[]
     */
    protected function insertArchiveData($visits, $visitsConverted, $existingArchives, $foundRecords)
    {
        if (SettingsServer::isArchivePhpTriggered()) {
            $this->logger->info("initiating archiving via core:archive for " . $this->params);
        }

        if (!empty($foundRecords)) {
            $this->params->setFoundRequestedReports($foundRecords);
        }

        [$visits, $visitsConverted] = $this->prepareCoreMetricsArchive($visits, $visitsConverted);
        [$idArchive, $visits] = $this->prepareAllPluginsArchive($visits, $visitsConverted);
        $idArchivesToQuery = [$idArchive];

        if (!empty($foundRecords)) {
            $idArchivesToQuery = array_merge($idArchivesToQuery, $existingArchives ?: []);
        }

        return [$idArchivesToQuery, $visits];
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function makeArchivingLockId()
    {
        $doneFlag = Rules::getDoneStringFlagFor(
            [$this->params->getSite()->getId()],
            $this->params->getSegment(),
            $this->params->getPeriod()->getLabel(),
            $this->params->getRequestedPlugin()
        );

        return $this->params->getPeriod()->getDateStart()->toString() . $this->params->getPeriod()->getDateEnd()->toString() . '.' . $doneFlag;
    }

    /**
     * @return array|false[]
     */
    protected function loadArchiveData()
    {
        // this hack was used to check the main function goes to return or continue
        // NOTE: $idArchives will contain the latest DONE_OK/DONE_INVALIDATED archive as well as any partial archives
        // with a ts_archived >= the DONE_OK/DONE_INVALIDATED date.
        $archiveInfo = $this->loadExistingArchiveIdFromDb();
        $idArchives = $archiveInfo['idArchives'];
        $visits = $archiveInfo['visits'];
        $visitsConverted = $archiveInfo['visitsConverted'];
        $tsArchived = $archiveInfo['tsArchived'];
        $doneFlagValue = $archiveInfo['doneFlagValue'];
        $existingArchives = $archiveInfo['existingRecords'];

        $requestedRecords = $this->params->getArchiveOnlyReportAsArray();
        $isMissingRequestedRecords = !empty($requestedRecords) && is_array($existingArchives) && count($requestedRecords) != count($existingArchives);

        if (
            !empty($idArchives)
            && !Rules::isActuallyForceArchivingSinglePlugin()
            && !$this->shouldForceInvalidatedArchive($doneFlagValue, $tsArchived)
            && !$isMissingRequestedRecords
        ) {
            // we have a usable idarchive (it's not invalidated and it's new enough), and we are not archiving
            // a single report
            return [$idArchives, $visits];
        }

        // NOTE: this optimization helps when archiving large periods. eg, if archiving a year w/ a segment where
        // there are not visits in the entire year, we don't have to go through and do anything. but, w/o this
        // code, we will end up launching archiving for each month, week and day, even though we don't have to.
        //
        // we don't create an archive in this case, because the archive may be in progress in some way, so a 0
        // visits archive can be inaccurate in the long run.
        if ($this->canSkipThisArchive()) {
            if (!empty($idArchives)) {
                return [$idArchives, $visits];
            } else {
                return [false, 0];
            }
        }

        if (self::$archivingDepth > 1) {
            $this->logger->debug(sprintf("Sub-period archive requires processing. Archiving depth: %d", self::$archivingDepth));
            $this->params->logStatusDebug();
        }

        return [$idArchives, $visits, $visitsConverted, $existingArchives];
    }

    /**
     * Prepares the core metrics if needed.
     *
     * @param $visits
     * @return array
     */
    protected function prepareCoreMetricsArchive($visits, $visitsConverted)
    {
        $createSeparateArchiveForCoreMetrics = $this->mustProcessVisitCount($visits)
                                && !$this->doesRequestedPluginIncludeVisitsSummary();

        if ($createSeparateArchiveForCoreMetrics) {
            $requestedPlugin = $this->params->getRequestedPlugin();
            $requestedReport = $this->params->getArchiveOnlyReport();

            $this->params->setRequestedPlugin('VisitsSummary');
            $this->params->setArchiveOnlyReport(null);

            $metrics = Context::executeWithQueryParameters(['requestedReport' => ''], function () {
                $pluginsArchiver = new PluginsArchiver($this->params);
                $metrics = $pluginsArchiver->callAggregateCoreMetrics();
                $pluginsArchiver->finalizeArchive();
                return $metrics;
            });

            $this->params->setRequestedPlugin($requestedPlugin);
            $this->params->setArchiveOnlyReport($requestedReport);

            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        return array($visits, $visitsConverted);
    }

    protected function prepareAllPluginsArchive($visits, $visitsConverted)
    {
        $pluginsArchiver = new PluginsArchiver($this->params);

        if (
            $this->mustProcessVisitCount($visits)
            || $this->doesRequestedPluginIncludeVisitsSummary()
        ) {
            $metrics = $pluginsArchiver->callAggregateCoreMetrics();
            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        $forceArchivingWithoutVisits = !$this->isThereSomeVisits($visits) && $this->shouldArchiveForSiteEvenWhenNoVisits();
        $pluginsArchiver->callAggregateAllPlugins($visits, $visitsConverted, $forceArchivingWithoutVisits);

        $idArchive = $pluginsArchiver->finalizeArchive();

        return array($idArchive, $visits);
    }

    protected function doesRequestedPluginIncludeVisitsSummary()
    {
        $processAllReportsIncludingVisitsSummary =
                Rules::shouldProcessReportsAllPlugins(array($this->params->getSite()->getId()), $this->params->getSegment(), $this->params->getPeriod()->getLabel());
        $doesRequestedPluginIncludeVisitsSummary = $processAllReportsIncludingVisitsSummary
                                                        || $this->params->getRequestedPlugin() == 'VisitsSummary';
        return $doesRequestedPluginIncludeVisitsSummary;
    }

    protected function isArchivingForcedToTrigger()
    {
        $period = $this->params->getPeriod()->getLabel();
        $debugSetting = 'always_archive_data_period'; // default

        if ($period == 'day') {
            $debugSetting = 'always_archive_data_day';
        } elseif ($period == 'range') {
            $debugSetting = 'always_archive_data_range';
        }

        return (bool) Config::getInstance()->Debug[$debugSetting];
    }

    /**
     * Returns the idArchive if the archive is available in the database for the requested plugin.
     * Returns false if the archive needs to be processed.
     *
     * (public for tests)
     *
     * @return array
     */
    public function loadExistingArchiveIdFromDb()
    {
        if ($this->isArchivingForcedToTrigger()) {
            $this->logger->debug("Archiving forced to trigger for {$this->params}.");

            // return no usable archive found, and no existing archive. this will skip invalidation, which should
            // be fine since we just force archiving.
            return [
                'idArchives' => false,
                'visits' => false,
                'visitsConverted' => false,
                'archiveExists' => false,
                'tsArchived' => false,
                'doneFlagValue' => false,
                'existingRecords' => null,
            ];
        }

        $minDatetimeArchiveProcessedUTC = $this->getMinTimeArchiveProcessed();
        $result = ArchiveSelector::getArchiveIdAndVisits($this->params, $minDatetimeArchiveProcessedUTC);
        return $result;
    }

    /**
     * Returns the minimum archive processed datetime to look at. Only public for tests.
     *
     * @return int|bool  Datetime timestamp, or false if must look at any archive available
     */
    protected function getMinTimeArchiveProcessed()
    {
        // for range periods we can archive in a browser request request, make sure to check for the ttl no matter what
        $isRangeArchiveAndArchivingEnabled = $this->params->getPeriod()->getLabel() == 'range'
            && Rules::isArchivingEnabledFor([$this->params->getSite()->getId()], $this->params->getSegment(), $this->params->getPeriod()->getLabel());

        if (!$isRangeArchiveAndArchivingEnabled) {
            $endDateTimestamp = self::determineIfArchivePermanent($this->params->getDateEnd());
            if ($endDateTimestamp) {
                // past archive
                return $endDateTimestamp;
            }
        }

        $dateStart = $this->params->getDateStart();
        $period    = $this->params->getPeriod();
        $segment   = $this->params->getSegment();
        $site      = $this->params->getSite();
        // in-progress archive
        return Rules::getMinTimeProcessedForInProgressArchive($dateStart, $period, $segment, $site);
    }

    protected static function determineIfArchivePermanent(Date $dateEnd)
    {
        $now = time();
        $endTimestampUTC = strtotime($dateEnd->getDateEndUTC());

        if ($endTimestampUTC <= $now) {
            // - if the period we are looking for is finished, we look for a ts_archived that
            //   is greater than the last day of the archive
            return $endTimestampUTC;
        }

        return false;
    }

    private function shouldArchiveForSiteEvenWhenNoVisits()
    {
        $idSitesToArchive = $this->getIdSitesToArchiveWhenNoVisits();
        return in_array($this->params->getSite()->getId(), $idSitesToArchive);
    }

    private function getIdSitesToArchiveWhenNoVisits()
    {
        $cacheKey = 'Archiving.getIdSitesToArchiveWhenNoVisits';

        if (!$this->cache->contains($cacheKey)) {
            $idSites = array();

            // leaving undocumented unless decided otherwise
            Piwik::postEvent('Archiving.getIdSitesToArchiveWhenNoVisits', array(&$idSites));

            $this->cache->save($cacheKey, $idSites);
        }

        return $this->cache->fetch($cacheKey);
    }

    // public for tests
    public function getReportsToInvalidate()
    {
        $sitesPerDays = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        foreach ($sitesPerDays as $dateStr => $siteIds) {
            if (
                empty($siteIds)
                || !in_array($this->params->getSite()->getId(), $siteIds)
            ) {
                unset($sitesPerDays[$dateStr]);
            }

            $date = Date::factory($dateStr);
            if (
                $date->isEarlier($this->params->getPeriod()->getDateStart())
                || $date->isLater($this->params->getPeriod()->getDateEnd())
            ) { // date in list is not the current date, so ignore it
                unset($sitesPerDays[$dateStr]);
            }
        }

        return $sitesPerDays;
    }

    private function invalidatedReportsIfNeeded()
    {
        $sitesPerDays = $this->getReportsToInvalidate();
        if (empty($sitesPerDays)) {
            return;
        }

        foreach ($sitesPerDays as $date => $siteIds) {
            try {
                $this->invalidator->markArchivesAsInvalidated([$this->params->getSite()->getId()], array(Date::factory($date)), false, $this->params->getSegment());
            } catch (\Exception $e) {
                Site::clearCache();
                throw $e;
            }
        }

        Site::clearCache();
    }

    public function canSkipThisArchive()
    {
        return $this->canSkipThisArchiveWithReason()[0];
    }

    /**
     * @internal
     *
     * @return array{0: bool, 1: string}
     */
    public function canSkipThisArchiveWithReason(): array
    {
        $params = $this->params;
        $idSite = $params->getSite()->getId();

        $isWebsiteUsingTracker = $this->isWebsiteUsingTheTracker($idSite);
        $isArchivingForcedWhenNoVisits = $this->shouldArchiveForSiteEvenWhenNoVisits();
        $hasSiteVisitsBetweenTimeframe = $this->hasSiteVisitsBetweenTimeframe($idSite, $params->getPeriod());
        $hasChildArchivesInPeriod = $this->hasChildArchivesInPeriod($idSite, $params->getPeriod());

        $canSkipArchiveForSegment = $this->canSkipArchiveForSegmentWithReason();

        if ($canSkipArchiveForSegment[0]) {
            return [
                true,
                'Skip archive for segment: ' . $canSkipArchiveForSegment[1],
            ];
        }

        if (!$isWebsiteUsingTracker) {
            return [
                false,
                'Site is not using the JavaScript tracker',
            ];
        }

        if ($isArchivingForcedWhenNoVisits) {
            return [
                false,
                'Archiving is forced when no visits',
            ];
        }

        if ($hasSiteVisitsBetweenTimeframe) {
            return [
                false,
                'Site has visits between start and end date',
            ];
        }

        if ($hasChildArchivesInPeriod) {
            return [
                false,
                'There are child archives in the period',
            ];
        }

        return [
            true,
            'Site is using tracker & archiving is not forced when no visits & site has has no visits between start and end date & there are no child archives in the period',
        ];
    }

    private function hasChildArchivesInPeriod($idSite, Period $period): bool
    {
        $cacheKey = CacheId::siteAware('Archiving.hasChildArchivesInPeriod.' . $period->getRangeString(), [$idSite]);

        if ($this->cache->contains($cacheKey)) {
            $hasChildArchivesInPeriod = $this->cache->fetch($cacheKey);
        } else {
            $hasChildArchivesInPeriod = $this->dataAccessModel->hasChildArchivesInPeriod($idSite, $period);
            $this->cache->save($cacheKey, $hasChildArchivesInPeriod);
        }
        return $hasChildArchivesInPeriod;
    }

    /**
     * @return array{0: bool, 1: string}
     */
    private function canSkipArchiveForSegmentWithReason(): array
    {
        $params = $this->params;

        if ($params->getSegment()->isEmpty()) {
            return [false, 'Segment is empty'];
        }

        if (!empty($params->getRequestedPlugin()) && Rules::isSegmentPluginArchivingDisabled($params->getRequestedPlugin(), $params->getSite()->getId())) {
            return [true, 'Plugin provided and segment plugin archiving disabled'];
        }

        // For better understanding of the next check please have a look at Rules::shouldProcessReportsAllPlugins implementation
        // and what conditions it returns false on. For our use here, we need to ensure that:
        //  - we are not running CLI archiving
        //  - we are not dealing with a range period
        //  - we don't have an empty segment
        //  - we don't have a segment that should be preprocessed
        //  - we are not forcing a single plugin archiving
        if (!Rules::shouldProcessReportsAllPlugins($params->getIdSites(), $params->getSegment(), $params->getPeriod()->getLabel())) {
            return [false, 'shouldProcessReportsAllPlugins reported false'];
        }

        /** @var SegmentArchiving */
        $segmentArchiving = StaticContainer::get(SegmentArchiving::class);
        $segmentInfo = $segmentArchiving->findSegmentForHash($params->getSegment()->getHash(), $params->getSite()->getId());

        if (!$segmentInfo) {
            return [false, 'segment not found for hash'];
        }

        $segmentArchiveStartDate = $segmentArchiving->getReArchiveSegmentStartDate($segmentInfo);

        if ($segmentArchiveStartDate !== null && $segmentArchiveStartDate->isLater($params->getPeriod()->getDateEnd()->getEndOfDay())) {
            $doneFlag = Rules::getDoneStringFlagFor(
                [$params->getSite()->getId()],
                $params->getSegment(),
                $params->getPeriod()->getLabel(),
                $params->getRequestedPlugin()
            );

            // if there is no invalidation where the report is null, we can skip
            // if we have invalidations for the period and name, but only for a specific reports, we can skip
            // if the report is not null we only want to rearchive if we have invalidation for that report
            // if we don't find invalidation for that report, we can skip
            $hasInvalidationsForPeriodAndName = $this->dataAccessModel->hasInvalidationForPeriodAndName($params->getSite()->getId(), $params->getPeriod(), $doneFlag, $params->getArchiveOnlyReport());

            if ($hasInvalidationsForPeriodAndName) {
                return [false, 'Has invalidations for period and name'];
            } else {
                return [true, 'No invalidations for period and name'];
            }
        }

        return [false, 'Segment archive date set or segment archive start date is earlier than period end of day'];
    }

    public function canSkipArchiveForSegment()
    {
        return $this->canSkipArchiveForSegmentWithReason()[0];
    }

    private function isWebsiteUsingTheTracker($idSite)
    {
        $idSitesNotUsingTracker = self::getSitesNotUsingTracker();

        $isUsingTracker = !in_array($idSite, $idSitesNotUsingTracker);

        return $isUsingTracker;
    }

    public static function getSitesNotUsingTracker()
    {
        $cache = Cache::getTransientCache();

        $cacheKey = 'Archiving.isWebsiteUsingTheTracker';
        $idSitesNotUsingTracker = $cache->fetch($cacheKey);
        if ($idSitesNotUsingTracker === false || !isset($idSitesNotUsingTracker)) {
            // we want to trigger event only once
            $idSitesNotUsingTracker = array();

            /**
             * This event is triggered when detecting whether there are sites that do not use the tracker.
             *
             * By default we only archive a site when there was actually any visit since the last archiving.
             * However, some plugins do import data from another source instead of using the tracker and therefore
             * will never have any visits for this site. To make sure we still archive data for such a site when
             * archiving for this site is requested, you can listen to this event and add the idSite to the list of
             * sites that do not use the tracker.
             *
             * @param bool $idSitesNotUsingTracker The list of idSites that rather import data instead of using the tracker
             */
            Piwik::postEvent('CronArchive.getIdSitesNotUsingTracker', array(&$idSitesNotUsingTracker));

            $cache->save($cacheKey, $idSitesNotUsingTracker);
        }
        return $idSitesNotUsingTracker;
    }

    private function hasSiteVisitsBetweenTimeframe($idSite, Period $period): bool
    {
        $cacheKeyStr = 'Archiving.hasSiteVisitsBetweenTimeframe.%s.%s';
        $cacheKey = CacheId::siteAware(sprintf($cacheKeyStr, $period->getLabel(), $period->getRangeString()), [$idSite]);

        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        $timezone = Site::getTimezoneFor($idSite);
        /** @var Date $date1 */
        /** @var Date $date2 */
        [$date1, $date2] = $period->getBoundsInTimezone($timezone);

        $hasSiteVisitsBetweenTimeframe = $this->rawLogDao->hasSiteVisitsBetweenTimeframe($date1->getDatetime(), $date2->getDatetime(), $idSite);
        $this->cache->save($cacheKey, $hasSiteVisitsBetweenTimeframe);

        if ($hasSiteVisitsBetweenTimeframe) {
            $currentPeriod = $period;
            do {
                $parentPeriodLabel = $currentPeriod->getParentPeriodLabel();
                if ($parentPeriodLabel) {
                    $parentPeriod = Period\Factory::build($parentPeriodLabel, $date1);
                    $cacheKey = CacheId::siteAware(sprintf($cacheKeyStr, $parentPeriod->getLabel(), $parentPeriod->getRangeString()), [$idSite]);
                    $this->cache->save($cacheKey, true);
                    $currentPeriod = $parentPeriod;
                }
            } while ($parentPeriodLabel);
        }

        return $hasSiteVisitsBetweenTimeframe;
    }

    public static function getArchivingDepth()
    {
        return self::$archivingDepth;
    }

    private function shouldForceInvalidatedArchive($value, $tsArchived)
    {
        $params = $this->params;

        // the archive is invalidated and we are in a browser request that is allowed archive it
        if (
            $value == ArchiveWriter::DONE_INVALIDATED
            && Rules::isArchivingEnabledFor([$params->getSite()->getId()], $params->getSegment(), $params->getPeriod()->getLabel())
        ) {
            // if coming from core:archive, force rearchiving, since if we don't the entry will be removed from archive_invalidations
            // w/o being rearchived
            if (SettingsServer::isArchivePhpTriggered()) {
                return true;
            }

            // if coming from a browser request, and period does not contain today, force rearchiving
            $timezone = $params->getSite()->getTimezone();
            if (!$params->getPeriod()->isDateInPeriod(Date::factoryInTimezone('today', $timezone))) {
                return true;
            }

            // if coming from a browser request, and period does contain today, check the ttl for the period (done just below this)
            $minDatetimeArchiveProcessedUTC = Rules::getMinTimeProcessedForInProgressArchive(
                $params->getDateStart(),
                $params->getPeriod(),
                $params->getSegment(),
                $params->getSite()
            );
            $minDatetimeArchiveProcessedUTC = Date::factory($minDatetimeArchiveProcessedUTC);
            if (
                $minDatetimeArchiveProcessedUTC
                && Date::factory($tsArchived)->isEarlier($minDatetimeArchiveProcessedUTC)
            ) {
                return true;
            }
        }

        return false;
    }
}
