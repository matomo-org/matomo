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
use Piwik\Concurrency\Semaphore;
use Piwik\CronArchive\AlgorithmLogger;
use Piwik\CronArchive\AlgorithmOptions;
use Piwik\CronArchive\AlgorithmRules;
use Piwik\CronArchive\Hooks;
use Piwik\CronArchive\Hooks\Statistics;
use Piwik\CronArchive\Jobs\ArchiveDayVisits;
use Piwik\CronArchive\Jobs\ArchiveVisitsForNonDayOrSegment;
use Piwik\Jobs\Job;
use Piwik\Jobs\Processor;
use Piwik\Jobs\Impl\CliProcessor;
use Piwik\Jobs\Impl\DistributedJobsQueue;
use Piwik\Jobs\Queue;
use Piwik\Jobs\Helper as JobsHelper;

/**
 * ./console core:archive runs as a cron and is a useful tool for general maintenance,
 * and pre-process reports for a Fast dashboard rendering.
 *
 * TODO: test if multiple servers doing job processing will work
 */
class CronArchive
{
    const OPTION_CRON_ARCHIVE_IN_PROCESS = 'CronArchive.inProgress';

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
     * @var AlgorithmRules
     */
    private $algorithmRules;

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
    private $options;

    /**
     * List of hooks that extend basic CronArchive behavior. These objects are used to apply cross-cutting
     * concerns to the CronArchive algorithm, while still achieving separation of concerns.
     *
     * @var Hooks[]
     */
    private $hooks = array();

    /**
     * Constructor.
     *
     * @param AlgorithmOptions $options Options to manipulate how CronArchive behaves.
     * @param Queue|null $queue The queue to store distributed jobs. If null, a DistributedJobsQueue instance
     *                                 is created.
     * @param Processor|string|null $processor The job processor that will consume jobs in this CronArchive run.
     *                                  If null, a CliProcessor instances is created.
     * @throws Exception if a named Queue cannot be found or if $queue is not an implementation of Queue.
     */
    public function __construct(AlgorithmOptions $options, Queue $queue = null, Processor $processor = null)
    {
        $this->options = $options;
        $this->algorithmRules = new AlgorithmRules($this);
        $this->algorithmLogger = new AlgorithmLogger();

        if (empty($queue)) {
            $queue = new DistributedJobsQueue();
        }

        if (empty($processor)) {
            $processor = new CliProcessor($queue);
        }

        $this->queue = $queue;
        $this->processor = $processor;

        $this->hooks[] = $this->options;
        $this->hooks[] = new Hooks\Logging();
        $this->hooks[] = new Hooks\Statistics();
    }

    /**
     * Invokes a hook.
     *
     * @param string $name The {@link Hooks} method name.
     * @param string[] $args Extra arguments to pass to the method.
     */
    public function executeHook($name, $args = array())
    {
        $args = array_merge(array($this, $this->algorithmRules, $this->algorithmLogger), $args);

        foreach ($this->hooks as $hookCollection) {
            call_user_func_array(array($hookCollection, $name), $args);
        }
    }

    /**
     * Gets a Hooks instance by class name.
     *
     * @param string $class
     * @return Hooks|null
     */
    public function getHooks($class)
    {
        foreach ($this->hooks as $hooks) {
            if ($hooks instanceof $class) {
                return $hooks;
            }
        }
        return null;
    }

    public function runScheduledTasksInTrackerMode()
    {
        $this->executeHook('onInitTrackerTasks');

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
     * a site has been completed), read the docs for the algorithm's components (ie, AlgorithmRules,
     * AlgorithmOptions, the jobs, etc.).
     */
    public function run()
    {
        if (!$this->isCronArchivingAlreadyInProcess()) {
            $this->startCronArchiving();
        } else {
            $this->algorithmLogger->log("- Archiving already in progress, processing jobs for existing archiving run.");
        }

        $this->processQueuedJobs();

        $this->runScheduledTasks();

        /** @var Statistics $stats */
        $stats = $this->getHooks("Piwik\\CronArchive\\Hooks\\Statistics");
        if ($stats->errors->get() == 0) {
            // if no error mark this execution as the last successfully run execution
            $this->algorithmRules->setLastSuccessRunTimestamp(time());
        }

        $this->executeHook('onEnd');
    }

    private function isCronArchivingAlreadyInProcess()
    {
        return Option::get(self::OPTION_CRON_ARCHIVE_IN_PROCESS) !== false
            && $this->queue->peek() > 0;
    }

    private function startCronArchiving()
    {
        Option::set(self::OPTION_CRON_ARCHIVE_IN_PROCESS, 1);

        Semaphore::deleteLike("CronArchive%");
        $this->algorithmRules->clearTemporaryOptions();

        $this->executeHook('onInit');

        // record archiving start time
        Option::set(self::OPTION_ARCHIVING_STARTED_TS, time());

        /**
         * This event is triggered after a CronArchive instance is initialized.
         *
         * @param array $websiteIds The list of website IDs this CronArchive instance is processing.
         *                          This will be the entire list of IDs regardless of whether some have
         *                          already been processed.
         * @deprecated
         */
        Piwik::postEvent('CronArchive.init.finish', array($this->algorithmRules->getWebsitesToArchive()));

        $this->algorithmRules->getProcessedWebsitesSemaphore()->set(0);

        foreach ($this->algorithmRules->getWebsitesToArchive() as $idSite) {
            $this->queueDayArchivingJobsForSite($idSite);
        }
    }

    private function processQueuedJobs()
    {
        // we allow the consumer to be empty in case another server does the actual job processing
        if (empty($this->processor)) {
            $this->algorithmLogger->log("- No processor configured/specified, skipping job processing. Another server may be configured to process jobs.");
            return;
        }

        $this->executeHook('onStartProcessing');
        $this->processor->startProcessing($finishWhenNoJobs = true);
        $this->executeHook('onEndProcessing');
    }

    public function runScheduledTasks()
    {
        $this->executeHook('onStartRunScheduledTasks');

        if ($this->options->disableScheduledTasks) {
            return;
        }

        $tasksOutput = $this->request("?module=API&method=CoreAdminHome.runScheduledTasks&format=csv&convertToUnicode=0");

        $this->executeHook('onEndRunScheduledTasks', array($tasksOutput));
    }

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
            $this->executeHook('onApiRequestError', array($url, $e->getMessage()));
            return false;
        }

        if ($this->checkApiResponse($response, $url)) {
            return $response;
        }

        return false;
    }

    public function checkApiResponse($response, $url)
    {
        if (empty($response)
            || stripos($response, 'error')
        ) {
            $this->executeHook('onApiRequestError', array($url, $response));
            return false;
        }
        return true;
    }

    /**
     * Returns the {@link $algorithmRules} property.
     *
     * @return AlgorithmRules
     */
    public function getAlgorithmRules()
    {
        return $this->algorithmRules;
    }

    /**
     * @param $idSite
     * @return void
     */
    private function queueDayArchivingJobsForSite($idSite)
    {
        if ($this->options->shouldSkipWebsite($idSite)) {
            $this->executeHook('onSkipWebsiteDayArchiving', array($idSite, 'found in --skip-idsites'));
            return;
        }

        if ($idSite <= 0) {
            $this->executeHook('onSkipWebsiteDayArchiving', array($idSite, 'strange ID'));
            return;
        }

        // Test if we should process this website
        if ($this->algorithmRules->getShouldSkipDayArchive($idSite)) {
            $reason = "was archived " . $this->algorithmRules->getElapsedTimeSinceLastArchiving($idSite, $pretty = true) . " ago";
            $this->executeHook('onSkipWebsiteDayArchiving', array($idSite, $reason));

            return;
        }

        if (!$this->algorithmRules->getShouldProcessPeriod("day")) {
            // skip day archiving and proceed to period processing
            $this->queuePeriodAndSegmentArchivingFor($idSite);
            return;
        }

        // initialize idSite request semaphores
        $this->algorithmRules->getActiveRequestsSemaphore($idSite)->set(0);
        $this->algorithmRules->getFailedRequestsSemaphore($idSite)->set(0);

        $this->executeHook('onQueueDayArchiving', array($idSite));

        $date = $this->algorithmRules->getArchivingRequestDateParameterFor($idSite, "day");

        $job = new ArchiveDayVisits($idSite, $date, $this->options);
        $this->enqueueJob($job, $idSite);
    }

    public function queuePeriodAndSegmentArchivingFor($idSite)
    {
        $this->executeHook('onQueuePeriodAndSegmentArchiving', array($idSite));

        $dayDate = $this->algorithmRules->getArchivingRequestDateParameterFor($idSite, 'day');
        $this->queueSegmentsArchivingFor($idSite, 'day', $dayDate);

        foreach (array('week', 'month', 'year') as $period) {
            if (!$this->algorithmRules->getShouldProcessPeriod($period)) {
                continue;
            }

            $date = $this->algorithmRules->getArchivingRequestDateParameterFor($idSite, $period);

            $job = new ArchiveVisitsForNonDayOrSegment($idSite, $date, $period, $segment = false, $this->options);
            $this->enqueueJob($job, $idSite);

            $this->queueSegmentsArchivingFor($idSite, $period, $date);
        }
    }

    private function queueSegmentsArchivingFor($idSite, $period, $date)
    {
        foreach ($this->algorithmRules->getSegmentsToArchiveForSite($idSite) as $segment) {
            $job = new ArchiveVisitsForNonDayOrSegment($idSite, $date, $period, $segment, $this->options);
            $this->enqueueJob($job, $idSite);
        }
    }

    private function enqueueJob(Job $job, $idSite)
    {
        $this->executeHook('onEnqueueJob', array($job, $idSite));

        $this->queue->enqueue(array($job));

        $this->algorithmRules->getFailedRequestsSemaphore($idSite)->increment();
        $this->algorithmRules->getActiveRequestsSemaphore($idSite)->increment();
    }
}