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
use Piwik\Concurrency\Semaphore;
use Piwik\CronArchive\AlgorithmLogger;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\AlgorithmStatistics;
use Piwik\CronArchive\AlgorithmState;
use Piwik\Jobs\Processor;
use Piwik\Jobs\Impl\CliProcessor;
use Piwik\Jobs\Impl\DistributedQueue;
use Piwik\Jobs\Queue;
use Piwik\Plugins\CoreAdminHome\API as APICoreAdminHome;

/**
 * ./console core:archive runs as a cron and is a useful tool for general maintenance,
 * and pre-process reports for a Fast dashboard rendering.
 *
 * TODO: make sure correct number of jobs pulled all the time (ie, if < max current, try pulling again)
 *       will require changes to CliMulti.
 */
class CronArchive
{
    const ARCHIVING_JOB_NAMESPACE = 'CronArchive';

    // the url can be set here before the init, and it will be used instead of --url=
    public static $url = false;

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

    // Name of option used to store starting timestamp
    const OPTION_ARCHIVING_STARTED_TS = "LastFullArchivingStartTime";

    private $token_auth = false;

    /**
     * TODO
     *
     * @var Queue
     */
    private $queue;

    /**
     * TODO
     *
     * @var Processor|null
     */
    private $consumer;

    /**
     * TODO
     *
     * @var AlgorithmState
     */
    private $algorithmState;

    /**
     * TODO
     *
     * @var AlgorithmStatistics
     */
    private $algorithmStats;

    /**
     * TODO
     *
     * @var AlgorithmLogger
     */
    public $algorithmLogger;

    /**
     * TODO
     *
     * @var AlgorithmOptions
     */
    public $options;

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
     * @param string|false $piwikUrl The URL to the Piwik installation to initiate archiving for. If `false`,
     *                               we determine it using the current request information.
     *
     *                               If invoked via the command line, $piwikUrl cannot be false.
     * TODO: update
     */
    public function __construct($queue = null, $consumer = null)
    {
        if (empty($queue)) {
            $queue = new DistributedQueue(self::ARCHIVING_JOB_NAMESPACE);

            if (empty($consumer)) {
                $consumer = new CliProcessor($queue);
            }
        }

        $this->options = new AlgorithmOptions();
        $this->algorithmState = new AlgorithmState($this);
        $this->algorithmStats = new AlgorithmStatistics();
        $this->algorithmLogger = new AlgorithmLogger();

        $this->queue = $queue;
        $this->consumer = $consumer;

        $self = $this;
        $this->consumer->setOnJobsFinishedCallback(function ($responses) use ($self) {
            foreach ($responses as $url => $response) {
                $self->responseFinished($url, $response);
            }
        });

        $this->initCore();
        $this->initTokenAuth();
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
        // Note: the order of methods call matters here.
        $this->logInitInfo();
        $this->logArchiveTimeoutInfo();

        // record archiving start time
        Option::set(self::OPTION_ARCHIVING_STARTED_TS, time());

        $periodsToProcess = $this->algorithmState->getPeriodsToProcess();
        if (!empty($periodsToProcess)) {
            $this->algorithmLogger->log("- Will process the following periods: " . implode(", ", $periodsToProcess) . " (--force-periods)");
        }


        if ($this->options->shouldStartProfiler) {
            \Piwik\Profiler::setupProfilerXHProf($mainRun = true);
            $this->algorithmLogger->log("XHProf profiling is enabled.");
        }

        /** TODO: deal w/ event
         * This event is triggered after a CronArchive instance is initialized.
         *
         * @param array $websiteIds The list of website IDs this CronArchive instance is processing.
         *                          This will be the entire list of IDs regardless of whether some have
         *                          already been processed.
         */
        //Piwik::postEvent('CronArchive.init.finish', array($this->websites->getInitialSiteIds()));
    }

    public function runScheduledTasksInTrackerMode()
    {
        $this->initCore();
        $this->initTokenAuth();
        $this->logInitInfo();
        $this->runScheduledTasks();
    }

    /**
     * Main function, runs archiving on all websites with new activity
     */
    public function run()
    {
        $this->algorithmLogger->logSection("START");
        $this->algorithmLogger->log("Starting Piwik reports archiving...");

        /**
         * Algorithm is:
         * - queue day archiving jobs for a site
         * - when a site finishes archiving for day, queue other requests including:
         *   * period archiving
         *   * segments archiving for day
         *   * segments archiving for periods
         *
         * TODO: be more descriptive
         */

        if (!$this->isContinuationOfArchivingJob()) {
            Semaphore::deleteLike("CronArchive%");

            foreach ($this->algorithmState->getWebsitesToArchive() as $idSite) {
                $this->queueDayArchivingJobsForSite($idSite);
            }
        }

        // we allow the consumer to be empty in case another server does the actual job processing
        if (empty($this->consumer)) {
            return;
        }

        $this->consumer->startProcessing($finishWhenNoJobs = true);

            /** TODO: deal w/ these events
             * This event is triggered before the cron archiving process starts archiving data for a single
             * site.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             */
            //Piwik::postEvent('CronArchive.archiveSingleSite.start', array($idSite));
            /**
             * This event is triggered immediately after the cron archiving process starts archiving data for a single
             * site.
             *
             * @param int $idSite The ID of the site we're archiving data for.
             */
            //Piwik::postEvent('CronArchive.archiveSingleSite.finish', array($idSite, $completed));

        $this->algorithmStats->logSummary($this->algorithmLogger, $this->algorithmState, $this->algorithmState->getWebsitesToArchive()); // TODO: remove 3rd param
    }

    private function handleError($errorMessage)
    {
        $this->algorithmStats->errors[] = $errorMessage;
        
        $this->algorithmLogger->logError($errorMessage);
    }

    /**
     * End of the script
     */
    public function end()
    {
        if (empty($this->algorithmStats->errors)) {
            // No error -> Logs the successful script execution until completion
            $this->algorithmState->setLastSuccessRunTimestamp(time());
            return;
        }

        $this->logErrorSummary();
    }

    private function logErrorSummary()
    {
        $this->algorithmLogger->logSection("SUMMARY OF ERRORS");
        foreach ($this->algorithmStats->errors as $error) {
            // do not logError since errors are already in stderr
            $this->algorithmLogger->log("Error: " . $error);
        }

        $this->algorithmLogger->logFatalError(count($this->algorithmStats->errors)
            . " total errors during this script execution, please investigate and try and fix these errors.");
    }

    public function runScheduledTasks()
    {
        $this->algorithmLogger->logSection("SCHEDULED TASKS");

        if ($this->options->disableScheduledTasks) {
            $this->algorithmLogger->log("Scheduled tasks are disabled with --disable-scheduled-tasks");
            return;
        }

        $this->algorithmLogger->log("Starting Scheduled tasks... ");

        $tasksOutput = $this->request("?module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0&token_auth=" . $this->token_auth);

        if ($tasksOutput == \Piwik\DataTable\Renderer\Csv::NO_DATA_AVAILABLE) {
            $tasksOutput = " No task to run";
        }

        $this->algorithmLogger->log($tasksOutput);
        $this->algorithmLogger->log("done");
        $this->algorithmLogger->logSection("");
    }

    /**
     * Returns base URL to process reports for the $idSite on a given $period
     */
    private function getVisitsRequestUrl($idSite, $period, $date)
    {
        $url = "?module=API&method=API.get&idSite=$idSite&period=$period&date=" . $date . "&format=php&token_auth=" . $this->token_auth;
        return $this->processUrl($url);
    }

    private function processUrl($url)
    {
        if ($this->options->shouldStartProfiler) {
            $url .= "&xhprof=2";
        }
        if ($this->options->testmode) {
            $url .= "&testmode=1";
        }
        $url .= self::APPEND_TO_API_REQUEST;
        return $url;
    }

    // TODO: make sure to deal w/ $this->requests/$this->processed & other metrics

    // TODO: go through each method and see if it still needs to be called. eg, request() shouldn't be, but its code needs to be dealt w/
    /**
     * Issues a request to $url
     */
    private function request($url)
    {
        $url = $this->processUrl($url);

        try {
            $cliMulti  = new CliMulti();
            $cliMulti->setAcceptInvalidSSLCertificate($this->options->acceptInvalidSSLCertificate);
            $responses = $cliMulti->request(array($url));

            $response  = !empty($responses) ? array_shift($responses) : null;
        } catch (Exception $e) {
            return $this->algorithmLogger->logNetworkError($url, $e->getMessage());
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
            return $this->algorithmLogger->logNetworkError($url, $response);
        }
        return true;
    }

    /**
     * Init Piwik, connect DB, create log & config objects, etc.
     */
    private function initCore()
    {
        try {
            FrontController::getInstance()->init();
        } catch (Exception $e) {
            throw new Exception("ERROR: During Piwik init, Message: " . $e->getMessage());
        }
    }

    private function initTokenAuth()
    {
        $token = '';

        /**
         * @ignore
         */
        Piwik::postEvent('CronArchive.getTokenAuth', array(&$token));
        
        $this->token_auth = $token;
    }

    public function getTokenAuth()
    {
        return $this->token_auth;
    }

    private function logInitInfo()
    {
        $this->algorithmLogger->logSection("INIT");
        $this->algorithmLogger->log("Running Piwik " . Version::VERSION . " as Super User");
    }

    private function logArchiveTimeoutInfo()
    {
        $this->algorithmLogger->logSection("NOTES");

        // Recommend to disable browser archiving when using this script
        if (Rules::isBrowserTriggerEnabled()) {
            $this->algorithmLogger->log("- If you execute this script at least once per hour (or more often) in a crontab, you may disable 'Browser trigger archiving' in Piwik UI > Settings > General Settings. ");
            $this->algorithmLogger->log("  See the doc at: http://piwik.org/docs/setup-auto-archiving/");
        }
        $this->algorithmLogger->log("- Reports for today will be processed at most every " . $this->algorithmState->getTodayArchiveTimeToLive()
            . " seconds. You can change this value in Piwik UI > Settings > General Settings.");
        $this->algorithmLogger->log("- Reports for the current week/month/year will be refreshed at most every "
            . $this->algorithmState->getProcessPeriodsMaximumEverySeconds() . " seconds.");

        // Try and not request older data we know is already archived
        $lastSuccessRunTimestamp = $this->algorithmState->getLastSuccessRunTimestamp();
        if ($lastSuccessRunTimestamp !== false) {
            $dateLast = time() - $lastSuccessRunTimestamp;
            $this->algorithmLogger->log("- Archiving was last executed without error " . MetricsFormatter::getPrettyTimeFromSeconds($dateLast, true, $isHtml = false) . " ago");
        }
    }

    /**
     * @param $idSite
     */
    protected function removeWebsiteFromInvalidatedWebsites($idSite)
    {
        $websiteIdsInvalidated = APICoreAdminHome::getWebsiteIdsToInvalidate();

        if (count($websiteIdsInvalidated)) {
            $found = array_search($idSite, $websiteIdsInvalidated);
            if ($found !== false) {
                unset($websiteIdsInvalidated[$found]);
                Option::set(APICoreAdminHome::OPTION_INVALIDATED_IDSITES, serialize($websiteIdsInvalidated));
            }
        }
    }

    private function getVisitsLastPeriodFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $today = end($stats);

        return $today['nb_visits'];
    }

    private function getVisitsFromApiResponse($stats)
    {
        if (empty($stats)) {
            return 0;
        }

        $visits = 0;
        foreach($stats as $metrics) {
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
     * @param $visitsInLastPeriods
     * @param $visitsToday
     * @param $timer
     */
    private function logArchivedWebsite($idSite, $period, $date, $segment, $visitsInLastPeriods, $visitsToday)
    {
        if (substr($date, 0, 4) === 'last') {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in last " . $date . " " . $period . "s, ";
            $thisPeriod = $period == "day" ? "today" : "this " . $period;
            $visitsInLastPeriod = (int)$visitsToday . " visits " . $thisPeriod . ", ";
        } else {
            $visitsInLastPeriods = (int)$visitsInLastPeriods . " visits in " . $period . "s included in: $date, ";
            $visitsInLastPeriod = '';
        }

        $this->algorithmLogger->log("Archived website id = $idSite, period = $period, "
            . $visitsInLastPeriods
            . $visitsInLastPeriod
            . " [segment = $segment]"
            ); // TODO: used to use $timer
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
     * @param $idSite
     * @return bool
     */
    private function isOldReportInvalidatedForWebsite($idSite)
    {
        return in_array($idSite, $this->algorithmState->getWebsitesWithInvalidatedArchiveData());
    }

    private function shouldProcessPeriod($period)
    {
        $periodsToProcess = $this->algorithmState->getPeriodsToProcess();
        if (empty($periodsToProcess)) {
            return true;
        }

        return in_array($period, $periodsToProcess);
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
            $lastTimestampWebsiteProcessed = strtotime(\Piwik\Site::getCreationDateFor($idSite));
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

    private function shouldSkipWebsite($idSite)
    {
        return in_array($idSite, $this->options->shouldSkipSpecifiedSites);
    }

    // TODO: need to log time of archiving for websites (in summary)
    /**
     * @param $idSite
     * @return void
     */
    private function queueDayArchivingJobsForSite($idSite)
    {
        if ($this->shouldSkipWebsite($idSite)) {
            $this->algorithmLogger->log("Skipped website id $idSite, found in --skip-idsites");

            ++$this->algorithmStats->skipped;
            return;
        }

        if ($idSite <= 0) {
            $this->algorithmLogger->log("Found strange site ID: '$idSite', skipping");

            ++$this->algorithmStats->skipped;
            return;
        }

        // Test if we should process this website
        if ($this->algorithmState->getShouldSkipDayArchive($idSite)) {
            $this->algorithmLogger->log("Skipped website id $idSite, already done "
                . $this->algorithmState->getElapsedTimeSinceLastArchiving($idSite, $pretty = true)
                . " ago");

            $this->algorithmStats->skippedDayArchivesWebsites++;
            $this->algorithmStats->skipped++;

            return;
        }

        if (!$this->shouldProcessPeriod("day")) {
            // skip day archiving and proceed to period processing
            $this->queuePeriodAndSegmentArchivingFor($idSite);
            return;
        }

        // Remove this website from the list of websites to be invalidated
        // since it's now just about to being re-processed, makes sure another running cron archiving process
        // does not archive the same idSite
        //if ($this->isOldReportInvalidatedForWebsite($idSite)) {
            // $this->removeWebsiteFromInvalidatedWebsites($idSite); TODO: no more multiple 'cron archiving process', so only invalidate after successful archive
        //}

        // when some data was purged from this website
        // we make sure we query all previous days/weeks/months
        $processDaysSince = $this->algorithmState->getLastTimestampWebsiteProcessedDay($idSite);
        if ($this->isOldReportInvalidatedForWebsite($idSite)
            // when --force-all-websites option,
            // also forces to archive last52 days to be safe
            || $this->options->shouldArchiveAllSites
        ) {
            $processDaysSince = false;
        }

        $date = $this->getApiDateParameter($idSite, "day", $processDaysSince);
        $this->queue->enqueue(array($this->getVisitsRequestUrl($idSite, "day", $date)));
    }

    private function queuePeriodAndSegmentArchivingFor($idSite)
    {
        $dayDate = $this->getApiDateParameter($idSite, 'day', $this->algorithmState->getLastTimestampWebsiteProcessedDay($idSite));
        $this->queueSegmentsArchivingFor($idSite, 'day', $dayDate);

        foreach (array('week', 'month', 'year') as $period) {
            if (!$this->shouldProcessPeriod($period)) {
                /* TODO:
                // if any period was skipped, we do not mark the Periods archiving as successful
                */
                continue;
            }

            $date = $this->getApiDateParameter($idSite, $period, $this->algorithmState->getLastTimestampWebsiteProcessedPeriods($idSite));

            $url = $this->getVisitsRequestUrl($idSite, $period, $date);

            $this->queue->enqueue(array($url));

            $this->queueSegmentsArchivingFor($idSite, $period, $date);
        }
    }

    /**
     * @return void
     */
    public function responseFinished($urlString, $textResponse)
    {
        $url = UrlHelper::getArrayFromQueryString($urlString);
        if (empty($url['idSite'])
            || empty($url['date'])
            || empty($url['period'])
        ) {
            return;
        }

        // TODO: rename Processor to Processor
        // TODO: if another job processor is run on another machine, it won't execute this logic...

        $idSite = $url['idSite'];
        $date   = $url['date'];
        $period = $url['period'];
        $segment = empty($url['segment']) ? null : $url['segment'];

        $response = @unserialize($textResponse);

        $visits = $visitsLast = 0;
        $isResponseValid = true;

        if (empty($textResponse)
            || !$this->checkResponse($textResponse, $urlString)
            || !is_array($response)
            || count($response) == 0
        ) {
            $isResponseValid = false;
        } else {
            $visits = $this->getVisitsLastPeriodFromApiResponse($response);
            $visitsLast = $this->getVisitsFromApiResponse($response);
        }

        if ($isResponseValid) {
            $this->algorithmState->getActiveRequestsSemaphore($idSite)->decrement();
        }

        // if archiving for a 'day' period finishes, check if there are visits and if so,
        // launch archiving for other periods and segments for the site
        if ($url['period'] === 'day'
            && empty($url['segment'])
        ) {
            if (!$isResponseValid) {
                $this->handleError("Empty or invalid response '$textResponse' for website id $idSite, skipping period and segment archiving.\n"
                              . "(URL used: $urlString)");
                $this->algorithmStats->skipped++;
                return;
            }

            $shouldArchivePeriods = $this->algorithmState->getShouldArchivePeriodsForWebsite($idSite);

            // If there is no visit today and we don't need to process this website, we can skip remaining archives
            if ($visits == 0
                && !$shouldArchivePeriods
            ) {
                $this->algorithmLogger->log("Skipped website id $idSite, no visit today");
                $this->algorithmStats->skipped++;
                return;
            }

            if ($visitsLast == 0
                && !$shouldArchivePeriods
                && $this->options->shouldArchiveAllSites
            ) {
                $this->algorithmLogger->log("Skipped website id $idSite, no visits in the last " . $date . " days");
                $this->algorithmStats->skipped++;
                return;
            }

            if (!$shouldArchivePeriods) {
                $this->algorithmLogger->log("Skipped website id $idSite periods processing, already done "
                    . $this->algorithmState->getElapsedTimeSinceLastArchiving($idSite, $pretty = true)
                    . " ago");
                $this->algorithmStats->skippedDayArchivesWebsites++;
                $this->algorithmStats->skipped++;
                return;
            }

            // mark 'day' period as successfully archived
            Option::set(self::lastRunKey($idSite, "day"), time());

            $this->algorithmState->getFailedRequestsSemaphore($idSite)->decrement();

            $this->algorithmStats->visitsToday += $visits;
            $this->algorithmStats->websitesWithVisitsSinceLastRun++;

            $this->queuePeriodAndSegmentArchivingFor($idSite); // TODO: all queuing must increase site's active request semaphore
        } else {
            if (!$isResponseValid) {
                $this->handleError("Error unserializing the following response from $urlString: " . $textResponse);

                return;
            }

            $failedRequestsCount = $this->algorithmState->getFailedRequestsSemaphore($idSite);
            $failedRequestsCount->decrement();

            if ($failedRequestsCount->get() === 0) {
                Option::set(self::lastRunKey($idSite, "periods"), time());

                $this->algorithmStats->archivedPeriodsArchivesWebsite++; // TODO: need to double check all metrics are counted correctly
                                                         // for example, this incremented only when success or always?
            }
        }

        $this->logArchivedWebsite($idSite, $period, $date, $segment, $visits, $visitsLast); // TODO no timer

        if ($this->algorithmState->getActiveRequestsSemaphore($idSite)->get() === 0) {
            $processedWebsitesCount = $this->algorithmState->getProcessedWebsitesSemaphore();
            $processedWebsitesCount->increment();

            Log::info("Archived website id = $idSite, "
                //. $requestsWebsite . " API requests, " TODO: necessary to report?
                // TODO: . $timerWebsite->__toString()
                . " [" . $processedWebsitesCount->get() . "/"
                . count($this->algorithmState->getWebsitesToArchive())
                . " done]");
        }
    }

    private function queueSegmentsArchivingFor($idSite, $period, $date)
    {
        $baseUrl = $this->getVisitsRequestUrl($idSite, $period, $date);

        foreach ($this->algorithmState->getSegmentsForSite($idSite) as $segment) {
            $urlWithSegment = $baseUrl . '&segment=' . urlencode($segment);

            $this->queue->enqueue(array($urlWithSegment));
        }
        // $cliMulti->setAcceptInvalidSSLCertificate($this->acceptInvalidSSLCertificate); // TODO: support in consumer
    }

    private function isContinuationOfArchivingJob()
    {
        return $this->queue->peek() > 0;
    }
}