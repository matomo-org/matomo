<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Archiver\Request;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive\FixedSiteIds;
use Piwik\CronArchive\SharedSiteIds;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\DataAccess\RawLogDao;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Metrics\Formatter;
use Piwik\Period\Factory as PeriodFactory;
use Piwik\CronArchive\SitesToReprocessDistributedList;
use Piwik\CronArchive\SegmentArchivingRequestUrlProvider;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeAPI;
use Piwik\Plugins\SegmentEditor\Model as SegmentEditorModel;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Plugins\UsersManager\UserPreferences;
use Psr\Log\LoggerInterface;

/**
 * ./console core:archive runs as a cron and is a useful tool for general maintenance,
 * and pre-process reports for a Fast dashboard rendering.
 */
class CronArchive
{
    // the url can be set here before the init, and it will be used instead of --url=
    public static $url = false;

    // Max parallel requests for a same site's segments
    const MAX_CONCURRENT_API_REQUESTS = 3;

    // force-timeout-for-periods default (1 hour)
    const SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES = 3600;

    // force-all-periods default (7 days)
    const ARCHIVE_SITES_WITH_TRAFFIC_SINCE = 604800;

    // By default, will process last 52 days and months
    // It will be overwritten by the number of days since last archiving ran until completion.
    const DEFAULT_DATE_LAST = 52;

    // Since weeks are not used in yearly archives, we make sure that all possible weeks are processed
    const DEFAULT_DATE_LAST_WEEKS = 260;

    const DEFAULT_DATE_LAST_YEARS = 7;

    // Flag to know when the archive cron is calling the API
    const APPEND_TO_API_REQUEST = '&trigger=archivephp';

    // Flag used to record timestamp in Option::
    const OPTION_ARCHIVING_FINISHED_TS = "LastCompletedFullArchiving";

    // Name of option used to store starting timestamp
    const OPTION_ARCHIVING_STARTED_TS = "LastFullArchivingStartTime";

    // Show only first N characters from Piwik API output in case of errors
    const TRUNCATE_ERROR_MESSAGE_SUMMARY = 6000;

    // archiving  will be triggered on all websites with traffic in the last $shouldArchiveOnlySitesWithTrafficSince seconds
    private $shouldArchiveOnlySitesWithTrafficSince;

    // By default, we only process the current week/month/year at most once an hour
    private $processPeriodsMaximumEverySeconds;
    private $todayArchiveTimeToLive;
    private $websiteDayHasFinishedSinceLastRun = array();
    private $idSitesInvalidatedOldReports = array();
    private $shouldArchiveOnlySpecificPeriods = array();
    private $idSitesNotUsingTracker;

    /**
     * @var SharedSiteIds|FixedSiteIds
     */
    private $websites = array();
    private $allWebsites = array();
    private $segments = array();
    private $visitsToday = 0;
    private $requests = 0;
    private $archiveAndRespectTTL = true;

    private $lastSuccessRunTimestamp = false;
    private $errors = array();

    private $apiToInvalidateArchivedReport;

    const NO_ERROR = "no error";

    public $testmode = false;

    /**
     * The list of IDs for sites for whom archiving should be initiated. If supplied, only these
     * sites will be archived.
     *
     * @var int[]
     */
    public $shouldArchiveSpecifiedSites = array();

    /**
     * The list of IDs of sites to ignore when launching archiving. Archiving will not be launched
     * for any site whose ID is in this list (even if the ID is supplied in {@link $shouldArchiveSpecifiedSites}
     * or if {@link $shouldArchiveAllSites} is true).
     *
     * @var int[]
     */
    public $shouldSkipSpecifiedSites = array();

    /**
     * If true, archiving will be launched for every site.
     *
     * @var bool
     */
    public $shouldArchiveAllSites = false;

    /**
     * If true, xhprof will be initiated for the archiving run. Only for development/testing.
     *
     * @var bool
     */
    public $shouldStartProfiler = false;

    /**
     * Given options will be forwarded to the PHP command if the archiver is executed via CLI.
     * @var string
     */
    public $phpCliConfigurationOptions = '';

    /**
     * If HTTP requests are used to initiate archiving, this controls whether invalid SSL certificates should
     * be accepted or not by each request.
     *
     * @var bool
     */
    public $acceptInvalidSSLCertificate = false;

    /**
     * If set to true, scheduled tasks will not be run.
     *
     * @var bool
     */
    public $disableScheduledTasks = false;

    /**
     * The amount of seconds between non-day period archiving. That is, if archiving has been launched within
     * the past [$forceTimeoutPeriod] seconds, Piwik will not initiate archiving for week, month and year periods.
     *
     * @var int|false
     */
    public $forceTimeoutPeriod = false;

    /**
     * If supplied, archiving will be launched for sites that have had visits within the last [$shouldArchiveAllPeriodsSince]
     * seconds. If set to `true`, the value defaults to {@link ARCHIVE_SITES_WITH_TRAFFIC_SINCE}.
     *
     * @var int|bool
     */
    public $shouldArchiveAllPeriodsSince = false;

    /**
     * If supplied, archiving will be launched only for periods that fall within this date range. For example,
     * `"2012-01-01,2012-03-15"` would result in January 2012, February 2012 being archived but not April 2012.
     *
     * @var string|false eg, `"2012-01-01,2012-03-15"`
     */
    public $restrictToDateRange = false;

    /**
     * A list of periods to launch archiving for. By default, day, week, month and year periods
     * are considered. This variable can limit the periods to, for example, week & month only.
     *
     * @var string[] eg, `array("day","week","month","year")`
     */
    public $restrictToPeriods = array();

    /**
     * Forces CronArchive to retrieve data for the last [$dateLastForced] periods when initiating archiving.
     * When archiving weeks, for example, if 10 is supplied, the API will be called w/ last10. This will potentially
     * initiate archiving for the last 10 weeks.
     *
     * @var int|false
     */
    public $dateLastForced = false;

    /**
     * The number of concurrent requests to issue per website. Defaults to {@link MAX_CONCURRENT_API_REQUESTS}.
     *
     * Used when archiving a site's segments concurrently.
     *
     * @var int|false
     */
    public $concurrentRequestsPerWebsite = false;

    /**
     * List of segment strings to force archiving for. If a stored segment is not in this list, it will not
     * be archived.
     *
     * @var string[]
     */
    public $segmentsToForce = array();

    /**
     * @var bool
     */
    public $disableSegmentsArchiving = false;

    private $websitesWithVisitsSinceLastRun = 0;
    private $skippedPeriodsArchivesWebsite = 0;
    private $skippedPeriodsNoDataInPeriod = 0;
    private $skippedDayArchivesWebsites = 0;
    private $skippedDayNoRecentData = 0;
    private $skippedDayOnApiError = 0;
    private $skipped = 0;
    private $processed = 0;
    private $archivedPeriodsArchivesWebsite = 0;

    private $archivingStartingTime;

    private $formatter;

    /**
     * @var SegmentArchivingRequestUrlProvider
     */
    private $segmentArchivingRequestUrlProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Only used when archiving using HTTP requests.
     *
     * @var string
     */
    private $urlToPiwik = null;

    /**
     * @var ArchiveInvalidator
     */
    private $invalidator;

    /**
     * Returns the option name of the option that stores the time core:archive was last executed.
     *
     * @param int $idSite
     * @param string $period
     * @return string
     */
    public static function lastRunKey($idSite, $period)
    {
        return "lastRunArchive" . $period . "_" . $idSite;
    }

    /**
     * Constructor.
     *
     * @param string|null $processNewSegmentsFrom When to archive new segments from. See [General] process_new_segments_from
     *                                            for possible values.
     * @param LoggerInterface|null $logger
     */
    public function __construct($processNewSegmentsFrom = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: StaticContainer::get('Psr\Log\LoggerInterface');
        $this->formatter = new Formatter();

        $processNewSegmentsFrom = $processNewSegmentsFrom ?: StaticContainer::get('ini.General.process_new_segments_from');
        $this->segmentArchivingRequestUrlProvider = new SegmentArchivingRequestUrlProvider($processNewSegmentsFrom);

        $this->invalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');
    }

    /**
     * Initializes and runs the cron archiver.
     */
    public function main()
    {
        $self = $this;
        Access::doAsSuperUser(function () use ($self) {
            $self->init();
            $self->run();
            $self->runScheduledTasks();
            $self->end();
        });
    }

    public function init()
    {
        /**
         * This event is triggered during initializing archiving.
         *
         * @param CronArchive $this
         */
        Piwik::postEvent('CronArchive.init.start', array($this));

        SettingsServer::setMaxExecutionTime(0);

        $this->archivingStartingTime = time();

        // Note: the order of methods call matters here.
        $this->initStateFromParameters();

        $this->logInitInfo();
        $this->logArchiveTimeoutInfo();

        // record archiving start time
        Option::set(self::OPTION_ARCHIVING_STARTED_TS, time());

        $this->segments    = $this->initSegmentsToArchive();
        $this->allWebsites = APISitesManager::getInstance()->getAllSitesId();

        if (!empty($this->shouldArchiveOnlySpecificPeriods)) {
            $this->logger->info("- Will only process the following periods: " . implode(", ", $this->shouldArchiveOnlySpecificPeriods) . " (--force-periods)");
        }

        $this->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain();

        $websitesIds = $this->initWebsiteIds();
        $this->filterWebsiteIds($websitesIds);

        $this->websites = $this->createSitesToArchiveQueue($websitesIds);

        if ($this->websites->getInitialSiteIds() != $websitesIds) {
            $this->logger->info('Will ignore websites and help finish a previous started queue instead. IDs: ' . implode(', ', $this->websites->getInitialSiteIds()));
        }

        $this->logForcedSegmentInfo();

        /**
         * This event is triggered after a CronArchive instance is initialized.
         *
         * @param array $websiteIds The list of website IDs this CronArchive instance is processing.
         *                          This will be the entire list of IDs regardless of whether some have
         *                          already been processed.
         */
        Piwik::postEvent('CronArchive.init.finish', array($this->websites->getInitialSiteIds()));
    }

    /**
     * Main function, runs archiving on all websites with new activity
     */
    public function run()
    {
        $timer = new Timer;

        $this->logSection("START");
        $this->logger->info("Starting Piwik reports archiving...");

        do {
            $idSite = $this->websites->getNextSiteId();

            if (null === $idSite) {
                break;
            }

            flush();
            $requestsBefore = $this->requests;
            if ($idSite <= 0) {
                continue;
            }

            $skipWebsiteForced = in_array($idSite, $this->shouldSkipSpecifiedSites);
            if ($skipWebsiteForced) {
                $this->logger->info("Skipped website id $idSite, found in --skip-idsites ");
                $this->skipped++;
                continue;
            }

            $shouldCheckIfArchivingIsNeeded    = !$this->shouldArchiveSpecifiedSites && !$this->shouldArchiveAllSites && !$this->dateLastForced;
            $hasWebsiteDayFinishedSinceLastRun = in_array($idSite, $this->websiteDayHasFinishedSinceLastRun);
            $isOldReportInvalidatedForWebsite  = $this->isOldReportInvalidatedForWebsite($idSite);

            if ($shouldCheckIfArchivingIsNeeded) {
                // if not specific sites and not all websites should be archived, we check whether we actually have
                // to process the archives for this website (only if there were visits since midnight)
                if (!$hasWebsiteDayFinishedSinceLastRun && !$isOldReportInvalidatedForWebsite) {

                    if ($this->isWebsiteUsingTheTracker($idSite)) {

                        if(!$this->hadWebsiteTrafficSinceMidnightInTimezone($idSite)) {
                            $this->logger->info("Skipped website id $idSite as archiving is not needed");

                            $this->skippedDayNoRecentData++;
                            $this->skipped++;
                            continue;
                        }
                    } else {
                       $this->logger->info("- website id $idSite is not using the tracker");
                    }

                } elseif ($hasWebsiteDayFinishedSinceLastRun) {
                    $this->logger->info("Day has finished for website id $idSite since last run");
                } elseif ($isOldReportInvalidatedForWebsite) {
                    $this->logger->info("Old report was invalidated for website id $idSite");
                }
            }

            /**
             * This event is triggered before the cron archiving process starts archiving data for a single
             * site.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             */
            Piwik::postEvent('CronArchive.archiveSingleSite.start', array($idSite));

            $completed = $this->archiveSingleSite($idSite, $requestsBefore);

            /**
             * This event is triggered immediately after the cron archiving process starts archiving data for a single
             * site.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             */
            Piwik::postEvent('CronArchive.archiveSingleSite.finish', array($idSite, $completed));
        } while (!empty($idSite));

        $this->logger->info("Done archiving!");

        $this->logSection("SUMMARY");
        $this->logger->info("Total visits for today across archived websites: " . $this->visitsToday);

        $totalWebsites = count($this->allWebsites);
        $this->skipped = $totalWebsites - $this->websitesWithVisitsSinceLastRun;
        $this->logger->info("Archived today's reports for {$this->websitesWithVisitsSinceLastRun} websites");
        $this->logger->info("Archived week/month/year for {$this->archivedPeriodsArchivesWebsite} websites");
        $this->logger->info("Skipped {$this->skipped} websites");
        $this->logger->info("- {$this->skippedDayNoRecentData} skipped because no new visit since the last script execution");
        $this->logger->info("- {$this->skippedDayArchivesWebsites} skipped because existing daily reports are less than {$this->todayArchiveTimeToLive} seconds old");
        $this->logger->info("- {$this->skippedPeriodsArchivesWebsite} skipped because existing week/month/year periods reports are less than {$this->processPeriodsMaximumEverySeconds} seconds old");

        if($this->skippedPeriodsNoDataInPeriod) {
            $this->logger->info("- {$this->skippedPeriodsNoDataInPeriod} skipped periods archiving because no visit in recent days");
        }

        if($this->skippedDayOnApiError) {
            $this->logger->info("- {$this->skippedDayOnApiError} skipped because got an error while querying reporting API");
        }
        $this->logger->info("Total API requests: {$this->requests}");

        //DONE: done/total, visits, wtoday, wperiods, reqs, time, errors[count]: first eg.
        $percent = $this->websites->getNumSites() == 0
            ? ""
            : " " . round($this->processed * 100 / $this->websites->getNumSites(), 0) . "%";
        $this->logger->info("done: " .
            $this->processed . "/" . $this->websites->getNumSites() . "" . $percent . ", " .
            $this->visitsToday . " vtoday, $this->websitesWithVisitsSinceLastRun wtoday, {$this->archivedPeriodsArchivesWebsite} wperiods, " .
            $this->requests . " req, " . round($timer->getTimeMs()) . " ms, " .
            (empty($this->errors)
                ? self::NO_ERROR
                : (count($this->errors) . " errors."))
        );

        $this->logger->info($timer->__toString());
    }

    /**
     * End of the script
     */
    public function end()
    {
        /**
         * This event is triggered after archiving.
         *
         * @param CronArchive $this
         */
        Piwik::postEvent('CronArchive.end', array($this));

        if (empty($this->errors)) {
            // No error -> Logs the successful script execution until completion
            Option::set(self::OPTION_ARCHIVING_FINISHED_TS, time());
            return;
        }

        $this->logSection("SUMMARY OF ERRORS");
        foreach ($this->errors as $error) {
            // do not logError since errors are already in stderr
            $this->logger->info("Error: " . $error);
        }

        $summary = count($this->errors) . " total errors during this script execution, please investigate and try and fix these errors.";
        $this->logFatalError($summary);
    }

    public function logFatalError($m)
    {
        $this->logError($m);

        throw new Exception($m);
    }

    /**
     * @param int[] $idSegments
     */
    public function setSegmentsToForceFromSegmentIds($idSegments)
    {
        /** @var SegmentEditorModel $segmentEditorModel */
        $segmentEditorModel = StaticContainer::get('Piwik\Plugins\SegmentEditor\Model');
        $segments = $segmentEditorModel->getAllSegmentsAndIgnoreVisibility();

        $segments = array_filter($segments, function ($segment) use ($idSegments) {
            return in_array($segment['idsegment'], $idSegments);
        });

        $segments = array_map(function ($segment) {
            return $segment['definition'];
        }, $segments);

        $this->segmentsToForce = $segments;
    }

    public function runScheduledTasks()
    {
        $this->logSection("SCHEDULED TASKS");

        if ($this->disableScheduledTasks) {
            $this->logger->info("Scheduled tasks are disabled with --disable-scheduled-tasks");
            return;
        }

        // TODO: this is a HACK to get the purgeOutdatedArchives task to work when run below. without
        //       it, the task will not run because we no longer run the tasks through CliMulti.
        //       harder to implement alternatives include:
        //       - moving CronArchive logic to DI and setting a flag in the class when the whole process
        //         runs
        //       - setting a new DI environment for core:archive which CoreAdminHome can use to conditionally
        //         enable/disable the task
        $_GET['trigger'] = 'archivephp';
        CoreAdminHomeAPI::getInstance()->runScheduledTasks();

        $this->logSection("");
    }

    private function archiveSingleSite($idSite, $requestsBefore)
    {
        $timerWebsite = new Timer;

        $lastTimestampWebsiteProcessedPeriods = $lastTimestampWebsiteProcessedDay = false;

        if ($this->archiveAndRespectTTL) {
            Option::clearCachedOption($this->lastRunKey($idSite, "periods"));
            $lastTimestampWebsiteProcessedPeriods = $this->getPeriodLastProcessedTimestamp($idSite);

            Option::clearCachedOption($this->lastRunKey($idSite, "day"));
            $lastTimestampWebsiteProcessedDay = $this->getDayLastProcessedTimestamp($idSite);
        }

        $this->updateIdSitesInvalidatedOldReports();

        // For period other than days, we only re-process the reports at most
        // 1) every $processPeriodsMaximumEverySeconds
        $secondsSinceLastExecution = time() - $lastTimestampWebsiteProcessedPeriods;

        // if timeout is more than 10 min, we account for a 5 min processing time, and allow trigger 1 min earlier
        if ($this->processPeriodsMaximumEverySeconds > 10 * 60) {
            $secondsSinceLastExecution += 5 * 60;
        }

        $shouldArchivePeriods = $secondsSinceLastExecution > $this->processPeriodsMaximumEverySeconds;
        if (empty($lastTimestampWebsiteProcessedPeriods)) {
            // 2) OR always if script never executed for this website before
            $shouldArchivePeriods = true;
        }

        // (*) If the website is archived because it is a new day in its timezone
        // We make sure all periods are archived, even if there is 0 visit today
        $dayHasEndedMustReprocess = in_array($idSite, $this->websiteDayHasFinishedSinceLastRun);
        if ($dayHasEndedMustReprocess) {
            $shouldArchivePeriods = true;
        }

        // (*) If there was some old reports invalidated for this website
        // we make sure all these old reports are triggered at least once
        $websiteInvalidatedShouldReprocess = $this->isOldReportInvalidatedForWebsite($idSite);

        if ($websiteInvalidatedShouldReprocess) {
            $shouldArchivePeriods = true;
        }

        $websiteIdIsForced = in_array($idSite, $this->shouldArchiveSpecifiedSites);
        if ($websiteIdIsForced) {
            $shouldArchivePeriods = true;
        }

        // Test if we should process this website at all
        $elapsedSinceLastArchiving = time() - $lastTimestampWebsiteProcessedDay;

        // Skip this day archive if last archive was older than TTL
        $existingArchiveIsValid = ($elapsedSinceLastArchiving < $this->todayArchiveTimeToLive);

        $skipDayArchive = $existingArchiveIsValid;

        // Invalidate old website forces the archiving for this site
        $skipDayArchive = $skipDayArchive && !$websiteInvalidatedShouldReprocess;

        // Also reprocess when day has ended since last run
        if ($dayHasEndedMustReprocess
            // it might have reprocessed for that day by another cron
            && !$this->hasBeenProcessedSinceMidnight($idSite, $lastTimestampWebsiteProcessedDay)
            && !$existingArchiveIsValid) {
            $skipDayArchive = false;
        }

        if ($websiteIdIsForced) {
            $skipDayArchive = false;
        }

        if ($skipDayArchive) {
            $this->logger->info("Skipped website id $idSite, already done "
                . $this->formatter->getPrettyTimeFromSeconds($elapsedSinceLastArchiving, true)
                . " ago, " . $timerWebsite->__toString());
            $this->skippedDayArchivesWebsites++;
            $this->skipped++;
            return false;
        }

        /**
         * Trigger archiving for days
         */
        try {
            $shouldProceed = $this->processArchiveDays($idSite, $lastTimestampWebsiteProcessedDay, $shouldArchivePeriods, $timerWebsite);
        } catch (UnexpectedWebsiteFoundException $e) {
            // this website was deleted in the meantime
            $shouldProceed = false;
            $this->logger->info("Skipped website id $idSite, got: UnexpectedWebsiteFoundException, " . $timerWebsite->__toString());
        }

        if (!$shouldProceed) {
            return false;
        }

        if (!$shouldArchivePeriods) {
            $this->logger->info("Skipped website id $idSite periods processing, already done "
                . $this->formatter->getPrettyTimeFromSeconds($elapsedSinceLastArchiving, true)
                . " ago, " . $timerWebsite->__toString());
            $this->skippedPeriodsArchivesWebsite++;
            $this->skipped++;
            return false;
        }

        /**
         * Trigger archiving for non-day periods
         */
        $success = $this->processArchiveForPeriods($idSite, $lastTimestampWebsiteProcessedPeriods);

        // Record succesful run of this website's periods archiving
        if ($success) {
            Option::set($this->lastRunKey($idSite, "periods"), time());
        }

        if (!$success) {
            // cancel marking the site as reprocessed
            if ($websiteInvalidatedShouldReprocess) {
                $store = new SitesToReprocessDistributedList();
                $store->add($idSite);
            }
        }

        $this->archivedPeriodsArchivesWebsite++;

        $requestsWebsite = $this->requests - $requestsBefore;
        $this->logger->info("Archived website id = $idSite, "
            . $requestsWebsite . " API requests, "
            . $timerWebsite->__toString()
            . " [" . $this->websites->getNumProcessedWebsites() . "/"
            . $this->websites->getNumSites()
            . " done]");

        return true;
    }

    /**
     * @param $idSite
     * @param $lastTimestampWebsiteProcessedPeriods
     * @return bool
     */
    private function processArchiveForPeriods($idSite, $lastTimestampWebsiteProcessedPeriods)
    {
        $success = true;

        foreach (array('week', 'month', 'year') as $period) {
            if (!$this->shouldProcessPeriod($period)) {
                // if any period was skipped, we do not mark the Periods archiving as successful
                $success = false;
                continue;
            }

            $timer = new Timer();

            $date = $this->getApiDateParameter($idSite, $period, $lastTimestampWebsiteProcessedPeriods);
            $periodArchiveWasSuccessful = $this->archiveReportsFor($idSite, $period, $date, $archiveSegments = true, $timer);
            $success = $periodArchiveWasSuccessful && $success;
        }

        if ($this->shouldProcessPeriod('range')) {
            // period=range
            $customDateRangesToPreProcessForSite = $this->getCustomDateRangeToPreProcess($idSite);
            foreach ($customDateRangesToPreProcessForSite as $dateRange) {
                $timer = new Timer();
                $archiveSegments = false; // do not pre-process segments for period=range #7611
                $periodArchiveWasSuccessful = $this->archiveReportsFor($idSite, 'range', $dateRange, $archiveSegments, $timer);
                $success = $periodArchiveWasSuccessful && $success;
            }
        }

        return $success;
    }

    /**
     * Returns base URL to process reports for the $idSite on a given $period
     *
     * @param string $idSite
     * @param string $period
     * @param string $date
     * @param bool|false $segment
     * @return string
     */
    private function getVisitsRequestUrl($idSite, $period, $date, $segment = false)
    {
        $request = "?module=API&method=API.get&idSite=$idSite&period=$period&date=" . $date . "&format=php";
        if ($segment) {
            $request .= '&segment=' . urlencode($segment);
            ;
        }
        return $request;
    }

    private function initSegmentsToArchive()
    {
        $segments = \Piwik\SettingsPiwik::getKnownSegmentsToArchive();

        if (empty($segments)) {
            return array();
        }

        $this->logger->info("- Will pre-process " . count($segments) . " Segments for each website and each period: " . implode(", ", $segments));
        return $segments;
    }

    /**
     * @param $idSite
     * @param $lastTimestampWebsiteProcessedDay
     * @param $shouldArchivePeriods
     * @param $timerWebsite
     * @return bool
     */
    protected function processArchiveDays($idSite, $lastTimestampWebsiteProcessedDay, $shouldArchivePeriods, Timer $timerWebsite)
    {
        if (!$this->shouldProcessPeriod("day")) {
            // skip day archiving and proceed to period processing
            return true;
        }

        $timer = new Timer();

        // Fake that the request is already done, so that other core:archive commands
        // running do not grab the same website from the queue
        Option::set($this->lastRunKey($idSite, "day"), time());

        // Remove this website from the list of websites to be invalidated
        // since it's now just about to being re-processed, makes sure another running cron archiving process
        // does not archive the same idSite
        $websiteInvalidatedShouldReprocess = $this->isOldReportInvalidatedForWebsite($idSite);
        if ($websiteInvalidatedShouldReprocess) {
            $store = new SitesToReprocessDistributedList();
            $store->remove($idSite);
        }

        // when some data was purged from this website
        // we make sure we query all previous days/weeks/months
        $processDaysSince = $lastTimestampWebsiteProcessedDay;
        if ($websiteInvalidatedShouldReprocess
            // when --force-all-websites option,
            // also forces to archive last52 days to be safe
            || $this->shouldArchiveAllSites) {
            $processDaysSince = false;
        }

        $date = $this->getApiDateParameter($idSite, "day", $processDaysSince);
        $url = $this->getVisitsRequestUrl($idSite, "day", $date);

        $this->logArchiveWebsite($idSite, "day", $date);

        $content = $this->request($url);
        $daysResponse = @unserialize($content);

        if (empty($content)
            || !is_array($daysResponse)
            || count($daysResponse) == 0
        ) {
            // cancel the succesful run flag
            Option::set($this->lastRunKey($idSite, "day"), 0);

            // cancel marking the site as reprocessed
            if ($websiteInvalidatedShouldReprocess) {
                $store = new SitesToReprocessDistributedList();
                $store->add($idSite);
            }

            $this->logError("Empty or invalid response '$content' for website id $idSite, " . $timerWebsite->__toString() . ", skipping");
            $this->skippedDayOnApiError++;
            $this->skipped++;
            return false;
        }

        $visitsToday = $this->getVisitsLastPeriodFromApiResponse($daysResponse);
        $visitsLastDays = $this->getVisitsFromApiResponse($daysResponse);

        $this->requests++;
        $this->processed++;

        // If there is no visit today and we don't need to process this website, we can skip remaining archives
        if (
            0 == $visitsToday
            && !$shouldArchivePeriods
        ) {
            $this->logger->info("Skipped website id $idSite, no visit today, " . $timerWebsite->__toString());
            $this->skippedDayNoRecentData++;
            $this->skipped++;
            return false;
        }

        if (0 == $visitsLastDays
            && !$shouldArchivePeriods
            && $this->shouldArchiveAllSites
        ) {
            $humanReadableDate = $this->formatReadableDateRange($date);
            $this->logger->info("Skipped website id $idSite, no visits in the $humanReadableDate days, " . $timerWebsite->__toString());
            $this->skippedPeriodsNoDataInPeriod++;
            $this->skipped++;
            return false;
        }

        $this->visitsToday += $visitsToday;
        $this->websitesWithVisitsSinceLastRun++;

        $this->archiveReportsFor($idSite, "day", $this->getApiDateParameter($idSite, "day", $processDaysSince), $archiveSegments = true, $timer);

        return true;
    }

    /**
     * @param $idSite
     * @return array
     */
    private function getSegmentsForSite($idSite)
    {
        $segmentsAllSites = $this->segments;
        $segmentsThisSite = SettingsPiwik::getKnownSegmentsToArchiveForSite($idSite);
        $segments = array_unique(array_merge($segmentsAllSites, $segmentsThisSite));
        return $segments;
    }

    private function formatReadableDateRange($date)
    {
        if (0 === strpos($date, 'last')) {
            $readable = 'last ' . str_replace('last', '', $date);
        } elseif (0 === strpos($date, 'previous')) {
            $readable = 'previous ' . str_replace('previous', '', $date);
        } else {
            $readable = 'last ' . $date;
        }

        return $readable;
    }

    /**
     * Will trigger API requests for the specified Website $idSite,
     * for the specified $period, for all segments that are pre-processed for this website.
     * Requests are triggered using cURL multi handle
     *
     * @param $idSite int
     * @param $period string
     * @param $date string
     * @param $archiveSegments bool Whether to pre-process all custom segments
     * @param Timer $periodTimer
     * @return bool True on success, false if some request failed
     */
    private function archiveReportsFor($idSite, $period, $date, $archiveSegments, Timer $periodTimer)
    {
        $url = $this->getVisitsRequestUrl($idSite, $period, $date, $segment = false);
        $url = $this->makeRequestUrl($url);

        $visitsInLastPeriods = $visitsLastPeriod = 0;
        $success = true;

        $urls = array();

        $noSegmentUrl = $url;
        // already processed above for "day"
        if ($period != "day") {
            $urls[] = $url;
            $this->logArchiveWebsite($idSite, $period, $date);
        }

        $segmentRequestsCount = 0;
        if ($archiveSegments) {
            $urlsWithSegment = $this->getUrlsWithSegment($idSite, $period, $date);
            $urls = array_merge($urls, $urlsWithSegment);
            $segmentRequestsCount = count($urlsWithSegment);

            // in case several segment URLs for period=range had the date= rewritten to the same value, we only call API once
            $urls = array_unique($urls);
        }

        $this->requests += count($urls);

        $cliMulti = $this->makeCliMulti();
        $cliMulti->setConcurrentProcessesLimit($this->getConcurrentRequestsPerWebsite());
        $response = $cliMulti->request($urls);

        foreach ($urls as $index => $url) {
            $content = array_key_exists($index, $response) ? $response[$index] : null;
            $success = $success && $this->checkResponse($content, $url);

            if ($noSegmentUrl === $url && $success) {
                $stats = @unserialize($content);
                if (!is_array($stats)) {
                    $this->logError("Error unserializing the following response from $url: " . $content);
                }

                if ($period == 'range') {
                    // range returns one dataset (the sum of data between the two dates),
                    // whereas other periods return lastN which is N datasets in an array. Here we make our period=range dataset look like others:
                    $stats = array($stats);
                }

                $visitsInLastPeriods = $this->getVisitsFromApiResponse($stats);
                $visitsLastPeriod = $this->getVisitsLastPeriodFromApiResponse($stats);
            }
        }

        $this->logArchivedWebsite($idSite, $period, $date, $segmentRequestsCount, $visitsInLastPeriods, $visitsLastPeriod, $periodTimer);

        return $success;
    }

    /**
     * Logs a section in the output
     *
     * @param string $title
     */
    private function logSection($title = "")
    {
        $this->logger->info("---------------------------");
        if (!empty($title)) {
            $this->logger->info($title);
        }
    }

    public function logError($m)
    {
        if (!defined('PIWIK_ARCHIVE_NO_TRUNCATE')) {
            $m = substr($m, 0, self::TRUNCATE_ERROR_MESSAGE_SUMMARY);
        }
        $m = str_replace(array("\n", "\t"), " ", $m);
        $this->errors[] = $m;
        $this->logger->error($m);
    }

    private function logNetworkError($url, $response)
    {
        $message = "Got invalid response from API request: $url. ";
        if (empty($response)) {
            $message .= "The response was empty. This usually means a server error. This solution to this error is generally to increase the value of 'memory_limit' in your php.ini file. Please check your Web server Error Log file for more details.";
        } else {
            $message .= "Response was '$response'";
        }

        $this->logError($message);
        return false;
    }

    /**
     * Issues a request to $url eg. "?module=API&method=API.getDefaultMetricTranslations&format=original&serialize=1"
     *
     * @param string $url
     * @return string
     */
    private function request($url)
    {
        $url = $this->makeRequestUrl($url);

        try {
            $cliMulti  = $this->makeCliMulti();
            $responses = $cliMulti->request(array($url));

            $response  = !empty($responses) ? array_shift($responses) : null;
        } catch (Exception $e) {
            return $this->logNetworkError($url, $e->getMessage());
        }
        if ($this->checkResponse($response, $url)) {
            return $response;
        }
        return false;
    }

    private function checkResponse($response, $url)
    {
        if (empty($response)
            || stripos($response, 'error')
        ) {
            return $this->logNetworkError($url, $response);
        }
        return true;
    }

    /**
     * Initializes the various parameters to the script, based on input parameters.
     *
     */
    private function initStateFromParameters()
    {
        $this->todayArchiveTimeToLive = Rules::getTodayArchiveTimeToLive();
        $this->processPeriodsMaximumEverySeconds = $this->getDelayBetweenPeriodsArchives();
        $this->lastSuccessRunTimestamp = $this->getLastSuccessRunTimestamp();
        $this->shouldArchiveOnlySitesWithTrafficSince = $this->isShouldArchiveAllSitesWithTrafficSince();
        $this->shouldArchiveOnlySpecificPeriods = $this->getPeriodsToProcess();

        if ($this->shouldArchiveOnlySitesWithTrafficSince !== false) {
            // force-all-periods is set here
            $this->archiveAndRespectTTL = false;
        }
    }

    private function getSecondsSinceLastArchive()
    {
        $wasNotCustomTimeRequested = $this->shouldArchiveOnlySitesWithTrafficSince === false;

        if ($wasNotCustomTimeRequested && !empty($this->lastSuccessRunTimestamp)) {
            // there was a previous successful run

            return time() - $this->lastSuccessRunTimestamp;

        } elseif (is_numeric($this->shouldArchiveOnlySitesWithTrafficSince)) {
            // $shouldArchiveAllPeriodsSince was specified
            $secondsSinceStart = time() - $this->archivingStartingTime;
            return $this->shouldArchiveOnlySitesWithTrafficSince + $secondsSinceStart;
        }

        // force-all-periods without value
        return self::ARCHIVE_SITES_WITH_TRAFFIC_SINCE;
    }

    public function filterWebsiteIds(&$websiteIds)
    {
        // Keep only the websites that do exist
        $websiteIds = array_intersect($websiteIds, $this->allWebsites);

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
    }

    /**
     * @internal
     * @param $api
     */
    public function setApiToInvalidateArchivedReport($api)
    {
        $this->apiToInvalidateArchivedReport = $api;
    }

    private function getApiToInvalidateArchivedReport()
    {
        if ($this->apiToInvalidateArchivedReport) {
            return $this->apiToInvalidateArchivedReport;
        }

        return CoreAdminHomeAPI::getInstance();
    }

    public function invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain()
    {
        $sitesPerDays = $this->invalidator->getRememberedArchivedReportsThatShouldBeInvalidated();

        foreach ($sitesPerDays as $date => $siteIds) {
            $listSiteIds = implode(',', $siteIds);

            try {
                $this->logger->info('Will invalidate archived reports for ' . $date . ' for following websites ids: ' . $listSiteIds);
                $this->getApiToInvalidateArchivedReport()->invalidateArchivedReports($siteIds, $date);
            } catch (Exception $e) {
                $this->logger->info('Failed to invalidate archived reports: ' . $e->getMessage());
            }
        }
    }

    /**
     *  Returns the list of sites to loop over and archive.
     *  @return array
     */
    public function initWebsiteIds()
    {
        if (count($this->shouldArchiveSpecifiedSites) > 0) {
            $this->logger->info("- Will process " . count($this->shouldArchiveSpecifiedSites) . " websites (--force-idsites)");

            return $this->shouldArchiveSpecifiedSites;
        }

        $this->findWebsiteIdsInTimezoneWithNewDay($this->allWebsites);
        $this->findInvalidatedSitesToReprocess();

        if ($this->shouldArchiveAllSites) {
            $this->logger->info("- Will process all " . count($this->allWebsites) . " websites");
        }

        return $this->allWebsites;
    }

    private function updateIdSitesInvalidatedOldReports()
    {
        $store = new SitesToReprocessDistributedList();
        $this->idSitesInvalidatedOldReports = $store->getAll();
    }

    /**
     * Return All websites that had reports in the past which were invalidated recently
     * (see API CoreAdminHome.invalidateArchivedReports)
     * eg. when using Python log import script
     *
     * @return array
     */
    private function findInvalidatedSitesToReprocess()
    {
        $this->updateIdSitesInvalidatedOldReports();

        if (count($this->idSitesInvalidatedOldReports) > 0) {
            $ids = ", IDs: " . implode(", ", $this->idSitesInvalidatedOldReports);
            $this->logger->info("- Will process " . count($this->idSitesInvalidatedOldReports)
                . " other websites because some old data reports have been invalidated (eg. using the Log Import script) "
                . $ids);
        }

        return $this->idSitesInvalidatedOldReports;
    }

    /**
     * Detects whether a site had visits since midnight in the websites timezone
     *
     * @param $idSite
     * @return bool
     */
    private function hadWebsiteTrafficSinceMidnightInTimezone($idSite)
    {
        $timezone = Site::getTimezoneFor($idSite);

        $nowInTimezone      = Date::factory('now', $timezone);
        $midnightInTimezone = $nowInTimezone->setTime('00:00:00');

        $secondsSinceMidnight = $nowInTimezone->getTimestamp() - $midnightInTimezone->getTimestamp();

        $secondsSinceLastArchive = $this->getSecondsSinceLastArchive();
        if ($secondsSinceLastArchive < $secondsSinceMidnight) {
            $secondsBackToLookForVisits = $secondsSinceLastArchive;
            $sinceInfo = "(since the last successful archiving)";
        } else {
            $secondsBackToLookForVisits = $secondsSinceMidnight;
            $sinceInfo = "(since midnight)";
        }

        $from = Date::now()->subSeconds($secondsBackToLookForVisits)->getDatetime();
        $to   = Date::now()->addHour(1)->getDatetime();

        $dao = new RawLogDao();
        $hasVisits = $dao->hasSiteVisitsBetweenTimeframe($from, $to, $idSite);

        if ($hasVisits) {
            $this->logger->info("- tracking data found for website id $idSite since $from UTC $sinceInfo");
        } else {
            $this->logger->info("- no new tracking data for website id $idSite since $from UTC $sinceInfo");
        }

        return $hasVisits;
    }

    /**
     * Returns the list of timezones where the specified timestamp in that timezone
     * is on a different day than today in that timezone.
     *
     * @return array
     */
    private function getTimezonesHavingNewDaySinceLastRun()
    {
        $timestamp = $this->lastSuccessRunTimestamp;
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
    }

    private function hasBeenProcessedSinceMidnight($idSite, $lastTimestampWebsiteProcessedDay)
    {
        if (false === $lastTimestampWebsiteProcessedDay) {
            return true;
        }

        $timezone = Site::getTimezoneFor($idSite);

        $dateInTimezone     = Date::factory('now', $timezone);
        $midnightInTimezone = $dateInTimezone->setTime('00:00:00');

        $lastProcessedDateInTimezone = Date::factory((int) $lastTimestampWebsiteProcessedDay, $timezone);

        return $lastProcessedDateInTimezone->getTimestamp() >= $midnightInTimezone->getTimestamp();
    }

    /**
     * Returns the list of websites in which timezones today is a new day
     * (compared to the last time archiving was executed)
     *
     * @param $websiteIds
     * @return array Website IDs
     */
    private function findWebsiteIdsInTimezoneWithNewDay($websiteIds)
    {
        $timezones = $this->getTimezonesHavingNewDaySinceLastRun();
        $websiteDayHasFinishedSinceLastRun = APISitesManager::getInstance()->getSitesIdFromTimezones($timezones);
        $websiteDayHasFinishedSinceLastRun = array_intersect($websiteDayHasFinishedSinceLastRun, $websiteIds);
        $this->websiteDayHasFinishedSinceLastRun = $websiteDayHasFinishedSinceLastRun;

        if (count($websiteDayHasFinishedSinceLastRun) > 0) {
            $ids = !empty($websiteDayHasFinishedSinceLastRun) ? ", IDs: " . implode(", ", $websiteDayHasFinishedSinceLastRun) : "";
            $this->logger->info("- Will process " . count($websiteDayHasFinishedSinceLastRun)
                . " other websites because the last time they were archived was on a different day (in the website's timezone) "
                . $ids);
        }

        return $websiteDayHasFinishedSinceLastRun;
    }

    private function logInitInfo()
    {
        $this->logSection("INIT");
        $this->logger->info("Running Piwik " . Version::VERSION . " as Super User");
    }

    private function logArchiveTimeoutInfo()
    {
        $this->logSection("NOTES");

        // Recommend to disable browser archiving when using this script
        if (Rules::isBrowserTriggerEnabled()) {
            $this->logger->info("- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Piwik UI > Settings > General Settings.");
            $this->logger->info("  See the doc at: http://piwik.org/docs/setup-auto-archiving/");
        }
        $this->logger->info("- Reports for today will be processed at most every " . $this->todayArchiveTimeToLive
            . " seconds. You can change this value in Piwik UI > Settings > General Settings.");
        $this->logger->info("- Reports for the current week/month/year will be refreshed at most every "
            . $this->processPeriodsMaximumEverySeconds . " seconds.");

        // Try and not request older data we know is already archived
        if ($this->lastSuccessRunTimestamp !== false) {
            $dateLast = time() - $this->lastSuccessRunTimestamp;
            $this->logger->info("- Archiving was last executed without error "
                . $this->formatter->getPrettyTimeFromSeconds($dateLast, true) . " ago");
        }
    }

    /**
     * Returns the delay in seconds, that should be enforced, between calling archiving for Periods Archives.
     * It can be set by --force-timeout-for-periods=X
     *
     * @return int
     */
    private function getDelayBetweenPeriodsArchives()
    {
        if (empty($this->forceTimeoutPeriod)) {
            return self::SECONDS_DELAY_BETWEEN_PERIOD_ARCHIVES;
        }

        // Ensure the cache for periods is at least as high as cache for today
        if ($this->forceTimeoutPeriod > $this->todayArchiveTimeToLive) {
            return $this->forceTimeoutPeriod;
        }

        $this->logger->info("WARNING: Automatically increasing --force-timeout-for-periods from {$this->forceTimeoutPeriod} to "
            . $this->todayArchiveTimeToLive
            . " to match the cache timeout for Today's report specified in Piwik UI > Settings > General Settings");

        return $this->todayArchiveTimeToLive;
    }

    private function isShouldArchiveAllSitesWithTrafficSince()
    {
        if (empty($this->shouldArchiveAllPeriodsSince)) {
            return false;
        }

        if (is_numeric($this->shouldArchiveAllPeriodsSince)
            && $this->shouldArchiveAllPeriodsSince > 1
        ) {
            return (int)$this->shouldArchiveAllPeriodsSince;
        }

        return true;
    }

    private function getVisitsLastPeriodFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $today = end($stats);

        if (empty($today['nb_visits'])) {
            return 0;
        }

        return $today['nb_visits'];
    }

    private function getVisitsFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $visits = 0;
        foreach ($stats as $metrics) {
            if (empty($metrics['nb_visits'])) {
                continue;
            }
            $visits += $metrics['nb_visits'];
        }

        return $visits;
    }

    /**
     * @param $idSite
     * @param $period
     * @param $lastTimestampWebsiteProcessed
     * @return float|int|true
     */
    private function getApiDateParameter($idSite, $period, $lastTimestampWebsiteProcessed = false)
    {
        $dateRangeForced = $this->getDateRangeToProcess();

        if (!empty($dateRangeForced)) {
            return $dateRangeForced;
        }

        return $this->getDateLastN($idSite, $period, $lastTimestampWebsiteProcessed);
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @param $segmentsCount
     * @param $visitsInLastPeriods
     * @param $visitsToday
     * @param $timer
     */
    private function logArchivedWebsite($idSite, $period, $date, $segmentsCount, $visitsInLastPeriods, $visitsToday, Timer $timer)
    {
        if (strpos($date, 'last') === 0 || strpos($date, 'previous') === 0) {
            $humanReadable = $this->formatReadableDateRange($date);
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in $humanReadable " . $period . "s, ";
            $thisPeriod = $period == "day" ? "today" : "this " . $period;
            $visitsInLastPeriod = (int)$visitsToday . " visits " . $thisPeriod . ", ";
        } else {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in " . $period . "s included in: $date, ";
            $visitsInLastPeriod = '';
        }

        $this->logger->info("Archived website id = $idSite, period = $period, $segmentsCount segments, "
            . $visitsInLastPeriods
            . $visitsInLastPeriod
            . $timer->__toString());
    }

    private function getDateRangeToProcess()
    {
        if (empty($this->restrictToDateRange)) {
            return false;
        }

        if (strpos($this->restrictToDateRange, ',') === false) {
            throw new Exception("--force-date-range expects a date range ie. YYYY-MM-DD,YYYY-MM-DD");
        }

        return $this->restrictToDateRange;
    }

    /**
     * @return array
     */
    private function getPeriodsToProcess()
    {
        $this->restrictToPeriods = array_intersect($this->restrictToPeriods, $this->getDefaultPeriodsToProcess());
        $this->restrictToPeriods = array_intersect($this->restrictToPeriods, PeriodFactory::getPeriodsEnabledForAPI());

        return $this->restrictToPeriods;
    }

    /**
     * @return array
     */
    private function getDefaultPeriodsToProcess()
    {
        return array('day', 'week', 'month', 'year', 'range');
    }

    /**
     * @param $idSite
     * @return bool
     */
    private function isOldReportInvalidatedForWebsite($idSite)
    {
        return in_array($idSite, $this->idSitesInvalidatedOldReports);
    }

    private function isWebsiteUsingTheTracker($idSite)
    {
        if (!isset($this->idSitesNotUsingTracker)) {
            // we want to trigger event only once
            $this->idSitesNotUsingTracker = array();

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
            Piwik::postEvent('CronArchive.getIdSitesNotUsingTracker', array(&$this->idSitesNotUsingTracker));

            if (!empty($this->idSitesNotUsingTracker)) {
                $this->logger->info("- The following websites do not use the tracker: " . implode(',', $this->idSitesNotUsingTracker));
            }
        }

        $isUsingTracker = !in_array($idSite, $this->idSitesNotUsingTracker);

        return $isUsingTracker;
    }

    private function shouldProcessPeriod($period)
    {
        if (empty($this->shouldArchiveOnlySpecificPeriods)) {
            return true;
        }

        return in_array($period, $this->shouldArchiveOnlySpecificPeriods);
    }

    /**
     * @param $idSite
     * @param $period
     * @param $lastTimestampWebsiteProcessed
     * @return string
     */
    private function getDateLastN($idSite, $period, $lastTimestampWebsiteProcessed)
    {
        $dateLastMax = self::DEFAULT_DATE_LAST;
        if ($period == 'year') {
            $dateLastMax = self::DEFAULT_DATE_LAST_YEARS;
        } elseif ($period == 'week') {
            $dateLastMax = self::DEFAULT_DATE_LAST_WEEKS;
        }
        if (empty($lastTimestampWebsiteProcessed)) {
            $creationDateFor = \Piwik\Site::getCreationDateFor($idSite);
            $lastTimestampWebsiteProcessed = strtotime($creationDateFor);
        }

        // Enforcing last2 at minimum to work around timing issues and ensure we make most archives available
        $dateLast = floor((time() - $lastTimestampWebsiteProcessed) / 86400) + 2;
        if ($dateLast > $dateLastMax) {
            $dateLast = $dateLastMax;
        }

        if (!empty($this->dateLastForced)) {
            $dateLast = $this->dateLastForced;
        }

        return "last" . $dateLast;
    }

    /**
     * @return int
     */
    private function getConcurrentRequestsPerWebsite()
    {
        if (false !== $this->concurrentRequestsPerWebsite) {
            return $this->concurrentRequestsPerWebsite;
        }

        return self::MAX_CONCURRENT_API_REQUESTS;
    }

    /**
     * @param $idSite
     * @return false|string
     */
    private function getPeriodLastProcessedTimestamp($idSite)
    {
        $timestamp = Option::get($this->lastRunKey($idSite, "periods"));
        return $this->sanitiseTimestamp($timestamp);
    }

    /**
     * @param $idSite
     * @return false|string
     */
    private function getDayLastProcessedTimestamp($idSite)
    {
        $timestamp = Option::get($this->lastRunKey($idSite, "day"));
        return $this->sanitiseTimestamp($timestamp);
    }

    /**
     * @return false|string
     */
    private function getLastSuccessRunTimestamp()
    {
        $timestamp = Option::get(self::OPTION_ARCHIVING_FINISHED_TS);
        return $this->sanitiseTimestamp($timestamp);
    }

    private function sanitiseTimestamp($timestamp)
    {
        $now = time();
        return ($timestamp < $now) ? $timestamp : $now;
    }

    /**
     * @param $idSite
     * @return array of date strings
     */
    private function getCustomDateRangeToPreProcess($idSite)
    {
        static $cache = null;
        if (is_null($cache)) {
            $cache = $this->loadCustomDateRangeToPreProcess();
        }
        if (empty($cache[$idSite])) {
            return array();
        }
        $dates = array_unique($cache[$idSite]);
        return $dates;
    }

    /**
     * @return array
     */
    private function loadCustomDateRangeToPreProcess()
    {
        $customDateRangesToProcessForSites = array();

        // For all users who have selected this website to load by default,
        // we load the default period/date that will be loaded for this user
        // and make sure it's pre-archived
        $allUsersPreferences = APIUsersManager::getInstance()->getAllUsersPreferences(array(
            APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
            APIUsersManager::PREFERENCE_DEFAULT_REPORT
        ));

        foreach ($allUsersPreferences as $userLogin => $userPreferences) {
            if (!isset($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE])) {
                continue;
            }

            $defaultDate = $userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE];
            $preference = new UserPreferences();
            $period = $preference->getDefaultPeriod($defaultDate);
            if ($period != 'range') {
                continue;
            }

            if (isset($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT])
                && is_numeric($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT])) {
                // If user selected one particular website ID
                $idSites = array($userPreferences[APIUsersManager::PREFERENCE_DEFAULT_REPORT]);
            } else {
                // If user selected "All websites"  or some other random value, we pre-process all websites that he has access to
                $idSites = APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess($userLogin);
            }

            foreach ($idSites as $idSite) {
                $customDateRangesToProcessForSites[$idSite][] = $defaultDate;
            }
        }

        return $customDateRangesToProcessForSites;
    }

    /**
     * @param $url
     * @return string
     */
    private function makeRequestUrl($url)
    {
        $url = $url . self::APPEND_TO_API_REQUEST;

        if ($this->shouldStartProfiler) {
            $url .= "&xhprof=2";
        }

        if ($this->testmode) {
            $url .= "&testmode=1";
        }

        return $url;
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     * @return Request[]
     */
    private function getUrlsWithSegment($idSite, $period, $date)
    {
        $urlsWithSegment = array();
        $segmentsForSite = $this->getSegmentsForSite($idSite);

        $segments = array();
        foreach ($segmentsForSite as $segment) {
            if ($this->shouldSkipSegmentArchiving($segment)) {
                $this->logger->info("- skipping segment archiving for '{segment}'.", array('segment' => $segment));

                continue;
            }

            $segments[] = $segment;
        }

        $segmentCount = count($segments);
        $processedSegmentCount = 0;
        foreach ($segments as $segment) {
            $dateParamForSegment = $this->segmentArchivingRequestUrlProvider->getUrlParameterDateString($idSite, $period, $date, $segment);

            $urlWithSegment = $this->getVisitsRequestUrl($idSite, $period, $dateParamForSegment, $segment);
            $urlWithSegment = $this->makeRequestUrl($urlWithSegment);

            $request = new Request($urlWithSegment);
            $logger = $this->logger;
            $request->before(function () use ($logger, $segment, $segmentCount, &$processedSegmentCount) {
                $processedSegmentCount++;
                $logger->info(sprintf(
                    '- pre-processing segment %d/%d %s',
                    $processedSegmentCount,
                    $segmentCount,
                    $segment
                ));
            });

            $urlsWithSegment[] = $request;
        }

        return $urlsWithSegment;
    }

    private function createSitesToArchiveQueue($websitesIds)
    {
        // use synchronous, single process queue if --force-idsites is used or sharing site IDs isn't supported
        if (!SharedSiteIds::isSupported() || !empty($this->shouldArchiveSpecifiedSites)) {
            return new FixedSiteIds($websitesIds);
        }

        // use separate shared queue if --force-all-websites is used
        if (!empty($this->shouldArchiveAllSites)) {
            return new SharedSiteIds($websitesIds, SharedSiteIds::OPTION_ALL_WEBSITES);
        }

        return new SharedSiteIds($websitesIds);
    }

    /**
     * @param $idSite
     * @param $period
     * @param $date
     */
    private function logArchiveWebsite($idSite, $period, $date)
    {
        $this->logger->info(sprintf(
            "Will pre-process for website id = %s, period = %s, date = %s",
            $idSite,
            $period,
            $date
        ));
        $this->logger->info('- pre-processing all visits');
    }

    private function shouldSkipSegmentArchiving($segment)
    {
        if ($this->disableSegmentsArchiving) {
            return true;
        }

        return !empty($this->segmentsToForce) && !in_array($segment, $this->segmentsToForce);
    }

    private function logForcedSegmentInfo()
    {
        if (empty($this->segmentsToForce)) {
            return;
        }

        $this->logger->info("- Limiting segment archiving to following segments:");
        foreach ($this->segmentsToForce as $segmentDefinition) {
            $this->logger->info("  * " . $segmentDefinition);
        }
    }

    /**
     * @return CliMulti
     */
    private function makeCliMulti()
    {
        $cliMulti = StaticContainer::get('Piwik\CliMulti');
        $cliMulti->setUrlToPiwik($this->urlToPiwik);
        $cliMulti->setPhpCliConfigurationOptions($this->phpCliConfigurationOptions);
        $cliMulti->setAcceptInvalidSSLCertificate($this->acceptInvalidSSLCertificate);
        $cliMulti->runAsSuperUser();
        return $cliMulti;
    }

    public function setUrlToPiwik($url)
    {
        $this->urlToPiwik = $url;
    }
}
