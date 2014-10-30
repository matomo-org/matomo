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
use Piwik\CronArchive\Jobs\ArchiveDayVisits;
use Piwik\CronArchive\Jobs\ArchiveVisitsForNonDayOrSegment;
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

    // Name of option used to store starting timestamp
    const OPTION_ARCHIVING_STARTED_TS = "LastFullArchivingStartTime";

    private $token_auth = false;

    /**
     * The distributed jobs queue to which new Jobs will be added.
     *
     * @var Queue
     */
    private $queue;

    /**
     * The job processor that will be run in this CronArchive execution (or null if no job processing
     * should be done within this PHP process). If null, jobs are queued and must be processed by another
     * PHP process.
     *
     * @var Processor|null
     */
    private $processor;

    /**
     * The CronArchive algorithm's state & non-queuing logic.
     *
     * @var AlgorithmState
     */
    private $algorithmState;

    /**
     * Statistics for this CronArchive run.
     *
     * @var AlgorithmStatistics
     */
    private $algorithmStats;

    /**
     * The class used to log information to the screen. By default the logger will just use {@link \Piwik\Log}.
     *
     * @var AlgorithmLogger
     */
    public $algorithmLogger;

    /**
     * The options that can alter the way this CronArchive instance behaves. Each option is available as
     * command line option in the core:archive command.
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
     * @param Queue|null $queue The queue to store distributed jobs. If null, a DistributedQueue instance
     *                          iscreated.
     * @param Processor|null $processor The job processor that will consume jobs in this CronArchive run.
     *                                  If null, a CliProcessor instances is created.
     */
    public function __construct($queue = null, $processor = null)
    {
        $this->options = new AlgorithmOptions();
        $this->algorithmState = new AlgorithmState($this);
        $this->algorithmStats = new AlgorithmStatistics();
        $this->algorithmLogger = new AlgorithmLogger();

        if (empty($queue)) {
            $queue = new DistributedQueue(self::ARCHIVING_JOB_NAMESPACE);

            if (empty($processor)) {
                $processor = new CliProcessor($queue);
                $processor->setAcceptInvalidSSLCertificate($this->options->acceptInvalidSSLCertificate);
            }
        }

        $this->queue = $queue;
        $this->processor = $processor;

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

        /**
         * This event is triggered after a CronArchive instance is initialized.
         *
         * @param array $websiteIds The list of website IDs this CronArchive instance is processing.
         *                          This will be the entire list of IDs regardless of whether some have
         *                          already been processed.
         */
        Piwik::postEvent('CronArchive.init.finish', array($this->algorithmState->getWebsitesToArchive()));
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
     *
     * The CronArchive algorithm is as follows:
     *
     * - queue jobs on the distributed queue to archive day statistics for each site
     *   - add hooks for each of these jobs; on finish, if there are visits, queue period & segment archiving
     *     jobs
     * - start processing jobs
     * - when finished display statistics
     *
     * To learn more about the specifics of the algorithm (eg, how it determines when archiving for
     * a site has been completed), read the docs for the algorithm's components (ie, AlgorithmState,
     * AlgorithmOptions, the jobs, etc.).
     */
    public function run()
    {
        $this->algorithmLogger->logSection("START");
        $this->algorithmLogger->log("Starting Piwik reports archiving...");

        if (!$this->isContinuationOfArchivingJob()) {
            Semaphore::deleteLike("CronArchive%");

            foreach ($this->algorithmState->getWebsitesToArchive() as $idSite) {
                $this->queueDayArchivingJobsForSite($idSite);
            }
        }

        // we allow the consumer to be empty in case another server does the actual job processing
        if (empty($this->processor)) {
            return;
        }

        $this->processor->startProcessing($finishWhenNoJobs = true);

        $this->algorithmStats->logSummary($this->algorithmLogger, $this->algorithmState);
    }

    public function handleError($errorMessage)
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

    // TODO: make sure to deal w/ $this->requests/$this->processed & other metrics

    // TODO: go through each method and see if it still needs to be called. eg, request() shouldn't be, but its code needs to be dealt w/
    /**
     * Issues a request to $url
     */
    private function request($url)
    {
        $url = $this->options->getProcessedUrl($url);

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
     * Returns the {@link $algorithmState} property.
     *
     * @return AlgorithmState
     */
    public function getAlgorithmState()
    {
        return $this->algorithmState;
    }

    /**
     * Returns the {@link $algorithmStats} property.
     *
     * @return AlgorithmStatistics
     */
    public function getAlgorithmStats()
    {
        return $this->algorithmStats;
    }

    /**
     * Returns the {@link $algorithmLogger} property.
     *
     * @return AlgorithmLogger
     */
    public function getAlgorithmLogger()
    {
        return $this->algorithmLogger;
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

        if (!$this->algorithmState->getShouldProcessPeriod("day")) {
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


        $date = $this->algorithmState->getArchivingRequestDateParameterFor($idSite, "day");

        $job = new ArchiveDayVisits($idSite, $date, $this->token_auth, $this->options);
        $this->queue->enqueue(array($job));
    }

    public function queuePeriodAndSegmentArchivingFor($idSite)
    {
        $dayDate = $this->algorithmState->getArchivingRequestDateParameterFor($idSite, 'day');
        $this->queueSegmentsArchivingFor($idSite, 'day', $dayDate);

        foreach (array('week', 'month', 'year') as $period) {
            if (!$this->algorithmState->getShouldProcessPeriod($period)) {
                continue;
            }

            $date = $this->algorithmState->getArchivingRequestDateParameterFor($idSite, $period);

            $job = new ArchiveVisitsForNonDayOrSegment($idSite, $date, $period, $segment = false, $this->token_auth, $this->options);
            $this->queue->enqueue(array($job));

            $this->queueSegmentsArchivingFor($idSite, $period, $date);
        }
    }

    // TODO: test if multiple servers doing job processing will work

    private function queueSegmentsArchivingFor($idSite, $period, $date)
    {
        foreach ($this->algorithmState->getSegmentsForSite($idSite) as $segment) {
            $job = new ArchiveVisitsForNonDayOrSegment($idSite, $date, $period, $segment, $this->token_auth, $this->options);
            $this->queue->enqueue(array($job));
        }
    }

    private function isContinuationOfArchivingJob()
    {
        return $this->queue->peek() > 0;
    }
}