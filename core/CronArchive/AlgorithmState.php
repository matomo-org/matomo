<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\CronArchive;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Concurrency\Semaphore;
use Piwik\CronArchive;
use Piwik\Date;
use Piwik\MetricsFormatter;
use Piwik\Option;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\CoreAdminHome\API as APICoreAdminHome;

/**
 * Encapsulates the logic of the CronArchive archiving algorithm in separate isolated
 * getters.
 *
 * The result of getters is cached. Getters that return data based on site is cached by
 * site ID.
 *
 * TODO: perhaps rename to AlgorithmRules or AlgorithmLogic... nah, neither one is good. need something more descriptive than AlgorithmState
 */
class AlgorithmState
{
    const ACTIVE_REQUESTS_SEMAPHORE_NAME = 'CronArchive.ActiveRequests';
    const FAILED_REQUESTS_SEMAPHORE_NAME = 'CronArchive.FailedRequests';
    const PROCESSED_WEBSITES_SEMAPHORE = 'CronArchive.ProcessedWebsites';

    // force-timeout-for-periods default (1 hour)
    const SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES = 3600;

    // Flag used to record timestamp in Option::
    const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";

    /**
     * Cache for each getter in this class.
     *
     * This array has two indexes: the site ID (or 'none') and the name of function whose
     * result is being cached.
     *
     * @var array
     */
    private $stateCache = array();

    /**
     * The CronArchive instance that is using this class.
     *
     * @var CronArchive
     */
    private $container;

    /**
     * Constructor.
     *
     * @param CronArchive $container
     */
    public function __construct(CronArchive $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the last time a website's day archives were calculated, or false if CronArchive
     * should not respect the archiving TTL (time-to-live) value.
     *
     * The timestamp is stored as an {@link \Piwik\Option}.
     *
     * See {@link getArchiveAndRespectTTL()}.
     *
     * @param int $idSite
     * @return int|false
     */
    public function getLastTimestampWebsiteProcessedDay($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            if ($self->getArchiveAndRespectTTL()) {
                $optionName = CronArchive::lastRunKey($idSite, "day");
                Option::clearCachedOption($optionName);
                return Option::get($optionName);
            } else {
                return false;
            }
        });
    }

    /**
     * Returns the last time a website's period archives were calculated, or false if CronArchive
     * should not respect the archiving TTL (time-to-live) value.
     *
     * The timestamp is stored as an {@link \Piwik\Option}.
     *
     * See {@link getArchiveAndRespectTTL()}.
     *
     * @param int $idSite
     * @return int|false
     */
    public function getLastTimestampWebsiteProcessedPeriods($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            if ($self->getArchiveAndRespectTTL()) {
                $optionName = $container->lastRunKey($idSite, "periods");
                Option::clearCachedOption($optionName);
                return Option::get($optionName);
            } else {
                return false;
            }
        });
    }

    /**
     * Returns the last time CronArchive was executed for a specific website.
     *
     * The last time a site's period archives were calculated is used as the last time CronArchive was
     * executed for the site.
     *
     * @param int $idSite
     * @return int
     */
    public function getSecondsSinceLastExecution($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            // For period other than days, we only re-process the reports at most
            // 1) every $processPeriodsMaximumEverySeconds
            $result = $container->startTime - $self->getLastTimestampWebsiteProcessedPeriods($idSite);

            // if timeout is more than 10 min, we account for a 5 min processing time, and allow trigger 1 min earlier
            if ($self->getProcessPeriodsMaximumEverySeconds() > 10 * 60) {
                $result += 5 * 60;
            }

            return $result;
        });
    }

    /**
     * Returns true if a site's data should be reprocessed because the current day has ended in the
     * site's timezone. false if otherwise.
     *
     * TODO: why must the archives be reprocessed, should say so here.
     *
     * @param int $idSite
     * @return bool
     */
    public function getDayHasEndedMustReprocesses($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return in_array($idSite, $self->getWebsitesInTimezoneWithNewDay());
        });
    }

    /**
     * Returns true if a website's old report data has been invalidated and must be reprocessed, false
     * if otherwise.
     *
     * @param int $idSite
     * @return bool
     */
    public function getIsOldReportInvalidedForWebsite($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return in_array($idSite, $self->getWebsitesWithInvalidatedArchiveData());
        });
    }

    /**
     * Returns true if archiving for a website is being forced during this CronArchive execution,
     * false if otherwise.
     *
     * @param int $idSite
     * @return bool
     */
    public function getIsWebsiteArchivingForced($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return in_array($idSite, $container->shouldArchiveSpecifiedSites);
        });
    }

    /**
     * TODO
     */
    public function getShouldArchivePeriods($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            $lastTimeProcessedPeriods = $self->getLastTimestampWebsiteProcessedPeriods($idSite);
            if (empty($lastTimeProcessedPeriods)) {
                // 2) OR always if script never executed for this website before
                return true;
            }

            // (*) If the website is archived because it is a new day in its timezone
            // We make sure all periods are archived, even if there is 0 visit today
            if ($self->getDayHasEndedMustReprocesses($idSite)) {
                return true;
            }

            // (*) If there was some old reports invalidated for this website
            // we make sure all these old reports are triggered at least once
            if ($self->getIsOldReportInvalidedForWebsite($idSite)) {
                return true;
            }

            if ($self->getIsWebsiteArchivingForced($idSite)) {
                return true;
            }

            return $self->getSecondsSinceLastExecution($idSite) > $self->getProcessPeriodsMaximumEverySeconds();
        });
    }

    /**
     * TODO
     */
    public function getElapsedTimeSinceLastArchiving($idSite, $pretty = false)
    {
        $result = $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return $container->startTime - $self->getLastTimestampWebsiteProcessedDay($idSite);
        });

        if ($pretty) {
            $result = MetricsFormatter::getPrettyTimeFromSeconds($result, true, $isHtml = false);
        }

        return $result;
    }

    /**
     * TODO
     *
     * valid if last archive age is less than TTL
     */
    public function getIsExistingArchveValid($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return $self->getElapsedTimeSinceLastArchiving($idSite) < $self->getTodayArchiveTimeToLive();
        });
    }

    /**
     * TODO
     */
    public function getHasBeenProcessedSinceMidnight($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            $lastTimestampWebsiteProcessedDay = $self->getLastTimestampWebsiteProcessedDay($idSite);

            if (false === $lastTimestampWebsiteProcessedDay) {
                return true;
            }

            $timezone = Site::getTimezoneFor($idSite);

            $dateInTimezone     = Date::factory('now', $timezone);
            $midnightInTimezone = $dateInTimezone->setTime('00:00:00');

            $lastProcessedDateInTimezone = Date::factory((int) $lastTimestampWebsiteProcessedDay, $timezone);

            return $lastProcessedDateInTimezone->getTimestamp() >= $midnightInTimezone->getTimestamp();
        });
    }

    /**
     * TODO
     */
    public function getShouldSkipDayArchive($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            $isExistingArchiveValid = $self->getIsExistingArchveValid($idSite);

            // Skip this day archive if last archive was older than TTL
            $skipDayArchive = $isExistingArchiveValid;

            // Invalidate old website forces the archiving for this site
            $skipDayArchive = $skipDayArchive && !$self->getIsOldReportInvalidedForWebsite($idSite);

            // Also reprocess when day has ended since last run
            if ($self->getDayHasEndedMustReprocesses($idSite)
                // it might have reprocessed for that day by another cron
                && !$self->getHasBeenProcessedSinceMidnight($idSite)
                && !$isExistingArchiveValid
            ) {
                $skipDayArchive = false;
            }

            if ($self->getIsWebsiteArchivingForced($idSite)) {
                $skipDayArchive = false;
            }

            return $skipDayArchive;
        });
    }

    /**
     * TODO
     */
    public function getActiveRequestsSemaphore($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return new Semaphore(AlgorithmState::ACTIVE_REQUESTS_SEMAPHORE_NAME . '.' . $idSite);
        });
    }

    /**
     * TODO
     */
    public function getFailedRequestsSemaphore($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return new Semaphore(AlgorithmState::FAILED_REQUESTS_SEMAPHORE_NAME . '.' . $idSite);
        });
    }

    /**
     * TODO
     */
    public function getProcessedWebsitesSemaphore()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return new Semaphore(AlgorithmState::PROCESSED_WEBSITES_SEMAPHORE);
        });
    }

    /**
     * TODO
     */
    public function getTodayArchiveTimeToLive()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return Rules::getTodayArchiveTimeToLive();
        });
    }

    /**
     * Returns the delay in seconds, that should be enforced, between calling archiving for Periods Archives.
     * It can be set by --force-timeout-for-periods=X
     *
     * @return int
     *
     * TODO: revise
     */
    public function getProcessPeriodsMaximumEverySeconds()
    {
        // TODO: 'none' should be const
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            if (empty($container->forceTimeoutPeriod)) {
                return self::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES;
            }

            // Ensure the cache for periods is at least as high as cache for today
            if ($container->forceTimeoutPeriod > $self->getTodayArchiveTimeToLive()) {
                return $container->forceTimeoutPeriod;
            }

            // TODO: should remove log statements from this class somehow
            $container->algorithmLogger->log("WARNING: Automatically increasing --force-timeout-for-periods from {$container->forceTimeoutPeriod} to "
                . $self->getTodayArchiveTimeToLive()
                . " to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");

            return $self->getTodayArchiveTimeToLive();
        });
    }

    /**
     * TODO
     */
    public function getSegmentsForSite($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            $segmentsAllSites = $self->getSegmentsForAllSites(); // TODO
            $segmentsThisSite = SettingsPiwik::getKnownSegmentsToArchiveForSite($idSite);
            if (!empty($segmentsThisSite)) {
                $container->algorithmLogger->log("Will pre-process the following " . count($segmentsThisSite) . " Segments for this website (id = $idSite): " . implode(", ", $segmentsThisSite));
            }
            return array_unique(array_merge($segmentsAllSites, $segmentsThisSite));
        });
    }

    /**
     * TODO
     */
    public function getSegmentsForAllSites()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $segments = SettingsPiwik::getKnownSegmentsToArchive();

            if (empty($segments)) {
                return array();
            }

            $container->algorithmLogger->log("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));

            return $segments;
        });
    }

    /**
     * TODO
     */
    public function getLastSuccessRunTimestamp()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return Option::get(self::OPTION_ARCHIVING_FINISHED_TS);
        });
    }

    /**
     * TODO
     */
    public function setLastSuccessRunTimestamp($time)
    {
        Option::set(self::OPTION_ARCHIVING_FINISHED_TS, $time);

        $this->clearInCache('none', __FUNCTION__);
    }

    /**
     * TODO
     */
    public function getShouldArchiveAllSitesWithTrafficSince()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            if (empty($container->shouldArchiveAllPeriodsSince)) {
                return false;
            }

            if (is_numeric($container->shouldArchiveAllPeriodsSince)
                && $container->shouldArchiveAllPeriodsSince > 1
            ) {
                return (int)$container->shouldArchiveAllPeriodsSince;
            }

            return true;
        });
    }

    /**
     * TODO

    // archiving  will be triggered on all websites with traffic in the last $shouldArchiveOnlySitesWithTrafficSince seconds
     */
    public function getShouldArchiveOnlySitesWithTrafficSince()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $lastSuccessRunTimestamp = $self->getLastSuccessRunTimestamp();
            $shouldArchiveOnlySitesWithTrafficSince = $self->getShouldArchiveAllSitesWithTrafficSince();

            if ($shouldArchiveOnlySitesWithTrafficSince === false) { // force-all-periods was not set
                if (empty($lastSuccessRunTimestamp)) {
                    // First time we run the script
                    $shouldArchiveOnlySitesWithTrafficSince = CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE;
                } else {
                    // there was a previous successful run
                    $shouldArchiveOnlySitesWithTrafficSince = time() - $lastSuccessRunTimestamp;
                }
            }  else { // force-all-periods was set
                if ($shouldArchiveOnlySitesWithTrafficSince === true) {
                    // force-all-periods without value
                    $shouldArchiveOnlySitesWithTrafficSince = CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE;
                }
            }

            return $shouldArchiveOnlySitesWithTrafficSince;
        });
    }

    /**
     * TODO
     */
    public function getPeriodsToProcess()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $periods = array_intersect($container->restrictToPeriods, $self->getDefaultPeriodsToProcess());
            $periods = array_intersect($periods, PeriodFactory::getPeriodsEnabledForAPI());
            return $periods;
        });
    }

    /**
     * TODO
     */
    public function getDefaultPeriodsToProcess()
    {
        return array('day', 'week', 'month', 'year');
    }

    /**
     * TODO
     */
    public function getArchiveAndRespectTTL()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return $self->getShouldArchiveAllSitesWithTrafficSince() === false; // return true if force-all-periods was not set
        });
    }

    /**
     * TODO
     */
    public function getAllWebsites()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return APISitesManager::getInstance()->getAllSitesId();
        });
    }

    /**
     * TODO
     */
    public function getWebsitesToArchive()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            if (count($container->shouldArchiveSpecifiedSites) > 0) {
                $container->algorithmLogger->log("- Will process " . count($container->shouldArchiveSpecifiedSites) . " websites (--force-idsites)");

                $websiteIds = $container->shouldArchiveSpecifiedSites;
            } else if ($container->shouldArchiveAllSites) {
                $container->algorithmLogger->log("- Will process all " . count($self->getAllWebsites()) . " websites");

                $websiteIds = $self->getAllWebsites();
            } else {
                $websiteIds = array_merge(
                    $self->getWebsitesWithVisitsSinceLastRun(),
                    $self->getWebsitesWithInvalidatedArchiveData(),
                    $self->getWebsitesInTimezoneWithNewDay()
                );
                $websiteIds = array_unique($websiteIds);

                /* TODO: broke this log. needs to diff against sites that are being archived because of non-timezone issues
                if (count($websiteDayHasFinishedSinceLastRun) > 0) {
                    $websiteDayHasFinishedSinceLastRun = array_diff($websiteDayHasFinishedSinceLastRun, $websiteIds);
                    $ids = !empty($websiteDayHasFinishedSinceLastRun) ? ", IDs: " . implode(", ", $websiteDayHasFinishedSinceLastRun) : "";
                    $container->algorithmLogger->log("- Will process " . count($websiteDayHasFinishedSinceLastRun)
                        . " other websites because the last time they were archived was on a different day (in the website's timezone) "
                        . $ids);
                }*/

            }

            $container->filterWebsiteIds($websiteIds); // TODO: bit of a hack making this public

            return $websiteIds;
        });
    }

    /**
     * TODO
     */
    public function getWebsitesWithVisitsSinceLastRun()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $shouldArchiveOnlySitesWithTrafficSince = $this->getShouldArchiveOnlySitesWithTrafficSince();

            $sitesIdWithVisits = APISitesManager::getInstance()->getSitesIdWithVisits(time() - $shouldArchiveOnlySitesWithTrafficSince);

            $websiteIds = !empty($sitesIdWithVisits) ? ", IDs: " . implode(", ", $sitesIdWithVisits) : "";
            $prettySeconds = \Piwik\MetricsFormatter::getPrettyTimeFromSeconds( $shouldArchiveOnlySitesWithTrafficSince, true, false);
            $container->algorithmLogger->log("- Will process " . count($sitesIdWithVisits) . " websites with new visits since "
                . $prettySeconds
                . " "
                . $websiteIds);

            return $sitesIdWithVisits;
        });
    }

    /**
     * TODO: update & modify
     *
     * Return All websites that had reports in the past which were invalidated recently
     * (see API CoreAdminHome.invalidateArchivedReports)
     * eg. when using Python log import script
     *
     * @return array
     */
    public function getWebsitesWithInvalidatedArchiveData()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $idSitesInvalidatedOldReports = APICoreAdminHome::getWebsiteIdsToInvalidate();

            if (count($idSitesInvalidatedOldReports) > 0) {
                $ids = ", IDs: " . implode(", ", $idSitesInvalidatedOldReports);
                $container->algorithmLogger->log("- Will process " . count($idSitesInvalidatedOldReports)
                    . " other websites because some old data reports have been invalidated (eg. using the Log Import script) "
                    . $ids);
            }

            return $idSitesInvalidatedOldReports;
        });
    }

    /**
     * TODO
     * Returns the list of websites in which timezones today is a new day
     * (compared to the last time archiving was executed)
     *
     * @param $websiteIds
     * @return array Website IDs
     */
    public function getWebsitesInTimezoneWithNewDay()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $timezones = $this->getTimezonesHavingNewDay();
            return APISitesManager::getInstance()->getSitesIdFromTimezones($timezones);
        });
    }

    /**
     * TODO
     * Returns the list of timezones where the specified timestamp in that timezone
     * is on a different day than today in that timezone.
     *
     * @return array
     */
    public function getTimezonesHavingNewDay()
    {
        return $this->getOrSetInCache('none', __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $timestamp = $self->getLastSuccessRunTimestamp();
            $uniqueTimezones = APISitesManager::getInstance()->getUniqueSiteTimezones();

            $timezoneToProcess = array();
            foreach ($uniqueTimezones as &$timezone) {
                $processedDateInTz = Date::factory((int)$timestamp, $timezone);
                $currentDateInTz = Date::factory('now', $timezone);

                if ($processedDateInTz->toString() != $currentDateInTz->toString()) {
                    $timezoneToProcess[] = $timezone;
                }
            }

            return $timezoneToProcess;
        });
    }

    /**
     * @param $idSite
     * @param $infoKey
     * @param $calculateCallback
     * @return mixed
     */
    private function getOrSetInCache($idSite, $infoKey, $calculateCallback)
    {
        if (!isset($this->stateCache[$idSite][$infoKey])) {
            $value = $calculateCallback($this, $this->container);

            $this->stateCache[$idSite][$infoKey] = $value;
        }

        return $this->stateCache[$idSite][$infoKey];
    }

    private function clearInCache($idSite, $infoKey)
    {
        unset($this->stateCache[$idSite][$infoKey]);
    }
}