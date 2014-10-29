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
use Piwik\Piwik;
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
    const NO_SITE_ID = 'none';

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
     * site's timezone, false if otherwise.
     *
     * If the day has ended for a site, then the archive must be reprocessed in order to include data
     * tracked from the last time the site was archived and the end of the day.
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
     * Returns true if archive data should be processed for a specific website, false if otherwise.
     *
     * A website should have its archive data reprocessed if:
     *
     * - period data have never been calculated for the site
     * - the day in the site's timezone has ended and the new data recorded has to be factored into
     *   the site's period archives
     * - old report data was invalidated for the site
     * - archiving for this website has been forced via a CronArchive option
     * - the current period data is too old to be considered accurate
     *
     * @param int $idSite
     * @return bool
     */
    public function getShouldArchivePeriodsForWebsite($idSite)
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
     * Returns the amount of time since the last time the archiving process was initialized
     * for a site.
     *
     * @param int $idSite
     * @param bool $pretty If true, the number of seconds is formatted and returned as a string.
     * @return int|string
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
     * Returns true if a website's archiving data are valid. Archiving data is considered valid if the
     * amount of time since the data was last calculated is less than the configured time to live value.
     *
     * @param int $idSite
     * @return int
     */
    public function getIsExistingArchiveForWebsiteValid($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return $self->getElapsedTimeSinceLastArchiving($idSite) < $self->getTodayArchiveTimeToLive();
        });
    }

    /**
     * Returns true if a website's archiving data has been processed at least once after the last day's
     * midnight in the website's timezone, false if otherwise.
     *
     * @param int $idSite
     * @return bool
     */
    public function getHasWebsiteDataBeenProcessedAfterLastMidnight($idSite)
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
     * Returns true if we shouldn't calculate day statistics for the specified website during
     * this CronArchive run.
     *
     * If archiving data is still valid, running the archiving process for the website would
     * be wasteful. This method is used to make sure that waste doesn't happen.
     *
     * @param int $idSite
     * @return bool
     */
    public function getShouldSkipDayArchive($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            $isExistingArchiveValid = $self->getIsExistingArchiveForWebsiteValid($idSite);

            // Skip this day archive if last archive was newer than TTL
            $skipDayArchive = $isExistingArchiveValid;

            // Invalidate old website forces the archiving for this site
            $skipDayArchive = $skipDayArchive && !$self->getIsOldReportInvalidedForWebsite($idSite);

            // Also reprocess when day has ended since last run
            if ($self->getDayHasEndedMustReprocesses($idSite)
                // it might have reprocessed for that day by another cron
                && !$self->getHasWebsiteDataBeenProcessedAfterLastMidnight($idSite)
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
     * Returns the Semaphore used to store the number of active requests during a CronArchive
     * run.
     *
     * This semaphore is incremented by CronArchive when an archiving request is scheduled
     * in a jobs queue and decremented after the request is finished. When the semaphore
     * is 0 we consider the archiving process for the site to be complete. The check is done
     * after, both, a request finishes and follow up requests are scheduled. Follow up requests
     * are scheduled in the same thread as the check is done, so the count will never be 0
     * because of another job processing thread.
     *
     * @param int $idSite
     * @return Semaphore
     */
    public function getActiveRequestsSemaphore($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return new Semaphore(AlgorithmState::ACTIVE_REQUESTS_SEMAPHORE_NAME . '.' . $idSite);
        });
    }

    /**
     * Returns the Semaphore used to count the number of failed archiving requests for a site.
     * It is initialized to 0 and incremented if an archiving request for a site results in an
     * error.
     *
     * This semaphore is used for printing statistics at the end of the archiving process.
     * The main CronArchive process will print these statistics out when archiving finishes. All
     * other job processing servers will not.
     *
     * @param int $idSite
     * @return Semaphore
     */
    public function getFailedRequestsSemaphore($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            return new Semaphore(AlgorithmState::FAILED_REQUESTS_SEMAPHORE_NAME . '.' . $idSite);
        });
    }

    /**
     * Returns the Semaphore used to count the number of websites processed in this archiving
     * run.
     *
     * This semaphore is used for printing statistics at the end of the archiving process.
     * The main CronArchive process will print these statistics out when archiving finishes. All
     * other job processing servers will not.
     *
     * @param int $idSite
     * @return Semaphore
     */
    public function getProcessedWebsitesSemaphore()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return new Semaphore(AlgorithmState::PROCESSED_WEBSITES_SEMAPHORE);
        });
    }

    /**
     * Returns the configured time to live for archiving data.
     *
     * This value is determined by the _General Settings_ option or the `[General] time_before_today_archive_considered_outdated`
     * INI option.
     *
     * @return int
     */
    public function getTodayArchiveTimeToLive()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return Rules::getTodayArchiveTimeToLive();
        });
    }

    /**
     * Returns the minimum amount of time that must pass before periods archiving should be initiated for
     * a website. If the amount of time hasn't yet passed, and the archive data is still considered valid,
     * period archiving shouldn't be launched in the current CronArchive run.
     *
     * @return int
     */
    public function getProcessPeriodsMaximumEverySeconds()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
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
     * Returns the segments to archive for a specific Website. This will include segments that are specific
     * to this site and segments that are applied to all sites.
     *
     * @param int $idSite
     * @return string[]
     */
    public function getSegmentsForSite($idSite)
    {
        return $this->getOrSetInCache($idSite, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) use ($idSite) {
            $segmentsAllSites = $self->getSegmentsForAllSites();
            $segmentsThisSite = SettingsPiwik::getKnownSegmentsToArchiveForSite($idSite);
            if (!empty($segmentsThisSite)) {
                $container->algorithmLogger->log("Will pre-process the following " . count($segmentsThisSite) . " Segments for this website (id = $idSite): " . implode(", ", $segmentsThisSite));
            }
            return array_unique(array_merge($segmentsAllSites, $segmentsThisSite));
        });
    }

    /**
     * Returns the list of segments that are applied to all websites. These segments will be processed for
     * every website that is archived.
     *
     * @return string[]
     */
    public function getSegmentsForAllSites()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $segments = SettingsPiwik::getKnownSegmentsToArchive();

            if (empty($segments)) {
                return array();
            }

            $container->algorithmLogger->log("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));

            return $segments;
        });
    }

    /**
     * The returns the time of the last _successful_ CronArchive run, or false if CronArchive has never
     * completed successfully.
     *
     * This time is stored as an option value after the CronArchive process finishes successfully.
     *
     * @return int|false
     */
    public function getLastSuccessRunTimestamp()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return Option::get(self::OPTION_ARCHIVING_FINISHED_TS);
        });
    }

    /**
     * Sets the option that stores the last successful CronArchive run time to a specific time.
     *
     * @param int $time The timestamp to set the option to, ie, `time()`.
     */
    public function setLastSuccessRunTimestamp($time)
    {
        Option::set(self::OPTION_ARCHIVING_FINISHED_TS, $time);

        $this->clearInCache(self::NO_SITE_ID, __FUNCTION__);
    }

    /**
     * Returns true if we should only archive period data for websites that have seen traffic in the last
     * N seconds. The amount of seconds can be specified as an option in CronArchive.
     *
     * See {@link CronArchive::$shouldArchiveAllPeriodsSince}.
     *
     * @return int|false Returns the number of seconds or false if the option was not set.
     *
     * TODO: rewrite docs for this, can return true or int secs. what happens when true? what happens when false? etc.
     */
    public function getShouldArchivePeriodsOnlyForSitesWithTrafficSinceLastNSecs()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
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
     * Returns the amount of seconds in the past during which websites are required to have traffic
     * for archiving to be launched.
     *
     * If the --force-all-periods option was set to a specific value, this value is used as the
     * number of seconds. If the option was supplied but without a value, the `CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE`
     * constant is used.
     *
     * If the --force-all-periods option was not supplied and the CronArchive process was run successfully
     * before, then the time period is the number of seconds since the last CronArchive run time. If the
     * CronArchive process has not been run before, `CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE` is used.
     *
     * See {@link CronArchive::$shouldArchiveAllPeriodsSince}.
     *
     * @return int
     */
    public function getShouldArchiveOnlySitesWithTrafficSinceLastNSecs()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $lastSuccessRunTimestamp = $self->getLastSuccessRunTimestamp();
            $shouldArchiveOnlySitesWithTrafficSince = $self->getShouldArchivePeriodsOnlyForSitesWithTrafficSinceLastNSecs();

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
     * Returns the periods to process during this CronArchive execution.
     *
     * The result of this method can be influenced by the {@link CronArchive::$restrictToPeriods} property
     * and the result of {@link PeriodFactory::getPeriodsEnabledForAPI()}.
     *
     * @return string[] ie `array('day', 'month')`
     */
    public function getPeriodsToProcess()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $periods = array_intersect($container->restrictToPeriods, $self->getDefaultPeriodsToProcess());
            $periods = array_intersect($periods, PeriodFactory::getPeriodsEnabledForAPI());
            return $periods;
        });
    }

    /**
     * Returns the default periods to process during a CronArchive execution.
     *
     * @return string[]
     */
    public function getDefaultPeriodsToProcess()
    {
        return array('day', 'week', 'month', 'year');
    }

    /**
     * Returns `true` if we should respect the archiving TTL, `false` if otherwise.
     *
     * TODO: I don't know what it means for the CronArchive algorithm if this returns false (ie, we **shouldn't**
     *       respect the archiving TTL.
     *
     * @return bool
     */
    public function getArchiveAndRespectTTL()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return $self->getShouldArchivePeriodsOnlyForSitesWithTrafficSinceLastNSecs() === false; // return true if force-all-periods was not set
        });
    }

    /**
     * Returns the list of all websites the current user has access to.
     *
     * @return int
     */
    public function getAllWebsites()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            return APISitesManager::getInstance()->getAllSitesId();
        });
    }

    /**
     * Returns the list of sites that we will launch the archiving process for in this CronArchive
     * run.
     *
     * If the --force-idsites parameter is used w/ CronArchive, then these sites are archived. See
     * {@link CronArchive::$shouldArchiveSpecifiedSites}.
     *
     * If the --force-all-websites parameter is used w/ CronArchive, then all data for websites are
     * computed. See {@link CronArchive::$shouldArchiveAllSites}.
     *
     * If neither of the above options are supplied, then the websites that have had traffic since the
     * last CronArchive execution, the websites with invalidated archive data and the websites for whom
     * the current day has ended (in the website's timezone).
     *
     * The list of websites to archive can be further modified by the **CronArchive.filterWebsiteIds**
     * event.
     *
     * @return int[]
     */
    public function getWebsitesToArchive()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
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

                /* TODO: broke this log. needs to diff against sites that are being archived because of non-timezone related issues
                if (count($websiteDayHasFinishedSinceLastRun) > 0) {
                    $websiteDayHasFinishedSinceLastRun = array_diff($websiteDayHasFinishedSinceLastRun, $websiteIds);
                    $ids = !empty($websiteDayHasFinishedSinceLastRun) ? ", IDs: " . implode(", ", $websiteDayHasFinishedSinceLastRun) : "";
                    $container->algorithmLogger->log("- Will process " . count($websiteDayHasFinishedSinceLastRun)
                        . " other websites because the last time they were archived was on a different day (in the website's timezone) "
                        . $ids);
                }*/

            }

            // Keep only the websites that do exist
            $websiteIds = array_intersect($websiteIds, $self->getAllWebsites());

            /**
             * Triggered by the **core:archive** console command so plugins can modify the list of
             * websites that the archiving process will be launched for.
             *
             * Plugins can use this hook to add websites to archive, remove websites to archive, or change
             * the order in which websites will be archived.
             *
             * @param array $websiteIds The list of website IDs to launch the archiving process for.
             */
            Piwik::postEvent('CronArchive.filterWebsiteIds', array(&$websiteIds));

            return $websiteIds;
        });
    }

    /**
     * Returns the list of websites that have experienced visits since the last time CronArchive
     * successfully ran.
     *
     * Uses the **SitesManager.getSitesIdWithVisits** API method.
     *
     * See {@link getShouldArchiveOnlySitesWithTrafficSinceLastNSecs()}.
     *
     * @return int[]
     */
    public function getWebsitesWithVisitsSinceLastRun()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $shouldArchiveOnlySitesWithTrafficSince = $this->getShouldArchiveOnlySitesWithTrafficSinceLastNSecs();

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
     * Returns the IDs of the websites with invalidated archive data.
     *
     * See the **CoreAdminHome.invalidateArchivedReports** API method.
     *
     * @return int[]
     */
    public function getWebsitesWithInvalidatedArchiveData()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
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
     * Returns the list of websites for whom the current day has ended in the website's timezone.
     *
     * @return int[]
     */
    public function getWebsitesInTimezoneWithNewDay()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
            $timezones = $this->getTimezonesHavingNewDay();
            return APISitesManager::getInstance()->getSitesIdFromTimezones($timezones);
        });
    }

    /**
     * Returns the list of timezones for which the current day has ended.
     *
     * @return string[]
     */
    public function getTimezonesHavingNewDay()
    {
        return $this->getOrSetInCache(self::NO_SITE_ID, __FUNCTION__, function (AlgorithmState $self, CronArchive $container) {
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