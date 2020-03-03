<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\ArchiveProcessor;

use Piwik\Archive\ArchiveInvalidator;
use Piwik\Cache;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Context;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Site;

/**
 * This class uses PluginsArchiver class to trigger data aggregation and create archives.
 */
class Loader
{
    /**
     * Idarchive in the DB for the requested archive
     *
     * @var int
     */
    protected $idArchive;

    /**
     * @var Parameters
     */
    protected $params;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    /**
     * @var \Piwik\Cache\Cache
     */
    private $cache;

    public function __construct(Parameters $params, $invalidateBeforeArchiving = false)
    {
        $this->params = $params;
        $this->invalidateBeforeArchiving = $invalidateBeforeArchiving;
        $this->invalidator = StaticContainer::get(ArchiveInvalidator::class);
        $this->cache = Cache::getTransientCache();    }

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
            return $this->prepareArchiveImpl($pluginName);
        });
    }

    private function prepareArchiveImpl($pluginName)
    {
        $this->params->setRequestedPlugin($pluginName);

        list($idArchive, $visits, $visitsConverted, $isAnyArchiveExists) = $this->loadExistingArchiveIdFromDb();
        if (!empty($idArchive)) { // we have a usable idarchive (it's not invalidated and it's new enough)
            return $idArchive;
        }

        // if there is an archive, but we can't use it for some reason, invalidate existing archives before
        // we start archiving. if the archive is made invalid, we will correctly re-archive below.
        if ($this->invalidateBeforeArchiving
            && $isAnyArchiveExists
        ) {
            $this->invalidatedReportsIfNeeded();
        }

        /** @var ArchivingStatus $archivingStatus */
        $archivingStatus = StaticContainer::get(ArchivingStatus::class);
        $archivingStatus->archiveStarted($this->params);

        try {
            list($visits, $visitsConverted) = $this->prepareCoreMetricsArchive($visits, $visitsConverted);
            list($idArchive, $visits) = $this->prepareAllPluginsArchive($visits, $visitsConverted);
        } finally {
            $archivingStatus->archiveFinished();
        }

        if ($this->isThereSomeVisits($visits) || PluginsArchiver::doesAnyPluginArchiveWithoutVisits()) {
            return $idArchive;
        }

        return false;
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

            $this->params->setRequestedPlugin('VisitsSummary');

            $pluginsArchiver = new PluginsArchiver($this->params);
            $metrics = $pluginsArchiver->callAggregateCoreMetrics();
            $pluginsArchiver->finalizeArchive();

            $this->params->setRequestedPlugin($requestedPlugin);

            $visits = $metrics['nb_visits'];
            $visitsConverted = $metrics['nb_visits_converted'];
        }

        return array($visits, $visitsConverted);
    }

    protected function prepareAllPluginsArchive($visits, $visitsConverted)
    {
        $pluginsArchiver = new PluginsArchiver($this->params);

        if ($this->mustProcessVisitCount($visits)
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
                Rules::shouldProcessReportsAllPlugins($this->params->getIdSites(), $this->params->getSegment(), $this->params->getPeriod()->getLabel());
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
            // TODO: lot's log here
            // return no usable archive found, no existing archive. this will skip invalidation, which should
            // be fine since we just force archiving. TODO: check this is true?
            return [false, false, false, false];
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
        $endDateTimestamp = self::determineIfArchivePermanent($this->params->getDateEnd());
        if ($endDateTimestamp) {
            // past archive
            return $endDateTimestamp;
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
        $cache = Cache::getTransientCache();
        $cacheKey = 'Archiving.getIdSitesToArchiveWhenNoVisits';

        if (!$cache->contains($cacheKey)) {
            $idSites = array();

            // leaving undocumented unless decided otherwise
            Piwik::postEvent('Archiving.getIdSitesToArchiveWhenNoVisits', array(&$idSites));

            $cache->save($cacheKey, $idSites);
        }

        return $cache->fetch($cacheKey);
    }

    private function invalidatedReportsIfNeeded()
    {
        $sitesPerDays = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        // TODO: there should be a task that goes through the entire list if browser archiving is enab led
        foreach ($sitesPerDays as $date => $siteIds) {
            if (empty($siteIds)
                || !in_array($this->params->getSite()->getId(), $siteIds)
            ) {
                continue;
            }

            $date = Date::factory($date);
            if ($date->isEarlier($this->params->getPeriod()->getDateStart())
                || $date->isLater($this->params->getPeriod()->getDateEnd())
            ) {
                continue; // date in list is not the current date, so ignore it
            }

            try {
                $this->invalidator->markArchivesAsInvalidated([$this->params->getSite()->getId()], array($date), false, $this->params->getSegment());
            } catch (\Exception $e) {
                Site::clearCache();
                throw $e;
            }
        }

        Site::clearCache();
    }

    private function getSiteIdsThatAreRequestedInThisArchiveButWereNotInvalidatedYet()
    {
        $id = 'Archive.SiteIdsOfRememberedReportsInvalidated';

        if (!$this->cache->contains($id)) {
            $this->cache->save($id, array());
        }

        $siteIdsAlreadyHandled = $this->cache->fetch($id);
        $siteIdsRequested      = $this->params->getIdSites();

        foreach ($siteIdsRequested as $index => $siteIdRequested) {
            $siteIdRequested = (int) $siteIdRequested;

            if (in_array($siteIdRequested, $siteIdsAlreadyHandled)) {
                unset($siteIdsRequested[$index]); // was already handled previously, do not do it again
            } else {
                $siteIdsAlreadyHandled[] = $siteIdRequested; // we will handle this id this time
            }
        }

        $this->cache->save($id, $siteIdsAlreadyHandled);

        return $siteIdsRequested;
    }
}
