<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs\Impl;

use Exception;
use Piwik\CliMulti;
use Piwik\Jobs\Job;
use Piwik\Jobs\Processor;
use Piwik\Jobs\Queue;
use Piwik\Log;
use Piwik\Url;

/**
 * Job processor that uses processes executed via shell_exec to process jobs in a
 * distributed queue.
 */
class CliProcessor implements Processor
{
    const DEFAULT_MAX_SPAWNED_PROCESS_COUNT = 3;
    const DEFAULT_SLEEP_TIME = 60;

    /**
     * The queue that holds jobs.
     *
     * @var Queue
     */
    private $jobQueue;

    /**
     * A callback that is executed when a set of jobs has finished executing.
     *
     * @var callback|null
     */
    private $onJobsFinishedCallback;

    /**
     * A callback that is executed before a set of jobs is started.
     *
     * @var callback|null
     */
    private $onJobsStartingCallback;

    /**
     * The maximum number of processes to spawn.
     *
     * @var int
     */
    private $maxNumberOfSpawnedProcesses;

    /**
     * Whether the processor is currently processing jobs.
     *
     * @var bool
     */
    private $processing = false;

    /**
     * The amount of time to wait before checking the queue for more jobs.
     *
     * @var int
     */
    private $sleepTimeBetweenBatchJobExecutions;

    /**
     * CliMulti instance used to execute Piwik requests asynchronously.
     *
     * @var CliMulti
     */
    private $cliMulti;

    /**
     * List of jobs currently being executed. CliMulti only acts on URLs so the
     * Job instances must be stored somewhere in order to execute job specific callbacks.
     *
     * The array index is the ID of the Job. Job IDs must stay unique throughout the entire
     * processor run, and cannot be re-used.
     *
     * Jobs are removed after finishing.
     *
     * @var Job[]
     */
    private $jobs = array();

    /**
     * Current max job ID. Used to compute next unique job ID. Job IDs are unique within this
     * processor's execution and are used only w/ CliMulti.
     *
     * @var int
     */
    private $currentJobId = 0;

    /**
     * Constructor.
     *
     * @param Queue $jobQueue The distributed queue containing jobs.
     * @param int $maxNumberOfSpawnedProcesses The maximum number of jobs to process simultaneously.
     * @param int $sleepTimeBetweenBatchJobExecutions The amount of time to wait before checking the queue for
     *                                                more jobs.
     * @param bool $acceptInvalidSSLCertificate See {@link \Piwik\CliMulti}.
     * @param CliMulti|null $cliMulti Custom CliMulti instance to use. For testing.
     */
    public function __construct(Queue $jobQueue, $maxNumberOfSpawnedProcesses = self::DEFAULT_MAX_SPAWNED_PROCESS_COUNT,
                                $sleepTimeBetweenBatchJobExecutions = self::DEFAULT_SLEEP_TIME,
                                $acceptInvalidSSLCertificate = false, CliMulti $cliMulti = null)
    {
        $this->jobQueue = $jobQueue;
        $this->maxNumberOfSpawnedProcesses = $maxNumberOfSpawnedProcesses;

        if ($cliMulti === null) {
            $cliMulti = new CliMulti();
        }

        $this->cliMulti = $cliMulti;
        $this->cliMulti->setAcceptInvalidSSLCertificate($acceptInvalidSSLCertificate);
        $this->cliMulti->setConcurrentProcessesLimit($maxNumberOfSpawnedProcesses);
    }

    /**
     * Sets the callback to execute after one or more jobs finishes executing.
     *
     * @param callback $onJobsFinishedCallback The callback to execute. Signature must be:
     *
     *                                             function (array $responses)
     *
     *                                         Where `$responses` contains two elements: the Job instance
     *                                         and the string output.
     */
    public function setOnJobsFinishedCallback($onJobsFinishedCallback)
    {
        $this->onJobsFinishedCallback = $onJobsFinishedCallback;
    }

    /**
     * Sets the callback to execute before one or more jobs finishes executing.
     *
     * @param string $onJobsStartingCallback The callback to execute. Signature must be:
     *
     *                                           function (Job[] $urls)
     */
    public function setOnJobsStartingCallback($onJobsStartingCallback)
    {
        $this->onJobsStartingCallback = $onJobsStartingCallback;
    }

    /**
     * Starts processing jobs in the configured queue.
     *
     * @param bool $finishWhenNoJobs If `true`, this method will return when no jobs are in the queue.
     *                               If `false`, it will continue to check for jobs even if there are
     *                               none in the queue.
     * @throws Exception rethrows any exceptions caught by executing CliMulti or pulling jobs from the
     *                            queue.
     */
    public function startProcessing($finishWhenNoJobs = false)
    {
        $cliMulti = $this->cliMulti;
        $this->processing = true;

        try {
            for (;;) {
                $jobUrls = $this->pullJobs($this->maxNumberOfSpawnedProcesses);

                if (!empty($jobUrls)) {
                    $self = $this;
                    $cliMulti->request($jobUrls, function ($responses) use ($cliMulti, $self) {
                        if (!empty($responses)) {
                            Log::debug("CliProcessor::startProcessing: %s jobs finished", count($responses));

                            $self->executeJobFinishedCallbacks($responses);
                        }

                        // TODO: if there are no unused processes, then instead of executing this logic on next poll,
                        //       should wait until a process finishes. Will require re-architecting CliMulti.
                        $unusedProcessCount = $cliMulti->getUnusedProcessCount();
                        if ($unusedProcessCount > 0) {
                            $newRequests = $self->pullJobs($unusedProcessCount);
                            if (!empty($newRequests)) {
                                $cliMulti->start($newRequests);
                            }
                        }
                    });
                }

                if ($finishWhenNoJobs
                    || !$this->processing
                ) {
                    break;
                } else {
                    $this->waitBeforeCheckingForMoreJobs();
                }
            }
        } catch (Exception $ex) {
            $this->processing = false;

            throw $ex;
        }
    }

    /**
     * Stops processing jobs.
     *
     * Jobs currently being processed will continue to be processed.
     *
     * TODO: since php is not multi-threaded, maybe this method is useless? if on an application server,
     *       maybe it's not useless, need to check. if using w/ pthreads or some other actual threading lib it's not useless.
     */
    public function stopProcessing()
    {
        $this->processing = false;
    }

    /**
     * public for use in Closure.
     *
     * @return string[]
     */
    public function pullJobs($count)
    {
        if (!$this->processing) {
            return array();
        }

        /** @var Job[] $jobs */
        $jobs = $this->jobQueue->pull($count) ?: array();
        if (empty($jobs)) {
            return array();
        }

        foreach ($jobs as $job) {
            try {
                $job->jobStarting();
            } catch (Exception $ex) {
                Log::warning("CliProcessor::%s: Job starting hook threw exception: '%s'.", __FUNCTION__, $ex->getMessage());
                Log::debug($ex);
            }
        }

        $onJobStartingCallback = $this->onJobsStartingCallback;
        if (!empty($onJobStartingCallback)) {
            try {
                $onJobStartingCallback($jobs);
            } catch (Exception $ex) {
                Log::warning("CliProcessor::%s: onJobStarting callback threw exception: '%s'", __FUNCTION__, $ex->getMessage());
                Log::debug($ex);
            }
        }

        // add the jobs to the list of currently executing jobs and return the job URLs for the new
        // jobs to execute. the URL array is mapped by the jobs' unique IDs.

        $jobUrls = array();
        foreach ($jobs as $job) {
            $jobId = $this->getNextJobId();

            $this->jobs[$jobId] = $job;
            $jobUrls[$jobId] = $job->getUrlString();
        }

        Log::debug("CliProcessor::%s: pulled %s jobs: %s", __FUNCTION__, count($jobUrls), $jobUrls);

        return $jobUrls;
    }

    private function waitBeforeCheckingForMoreJobs()
    {
        Log::debug("CliProcessor::%s: Waiting %ss for new jobs.", __FUNCTION__, $this->sleepTimeBetweenBatchJobExecutions);

        sleep($this->sleepTimeBetweenBatchJobExecutions);
    }

    /**
     * public only for use in closure.
     */
    public function executeJobFinishedCallbacks($responses)
    {
        $jobsAndResponses = array();

        foreach ($responses as $jobId => $response) {
            $job = @$this->jobs[$jobId];

            if (empty($job)) {
                Log::debug("CliProcessor::%s: Unexpected error, job w/ ID = '%s' cannot be found in currently processing job list.",
                    __FUNCTION__, $jobId);

                continue;
            }

            $jobsAndResponses[] = array($job, $response);

            try {
                $job->jobFinished($response);
            } catch (Exception $ex) {
                Log::warning("CliProcessor::%s: jobFinished hook threw exception: '%s'", __FUNCTION__, $ex->getMessage());
                Log::debug($ex);
            }

            unset($this->jobs[$jobId]);
        }

        $onFinishJobs = $this->onJobsFinishedCallback;
        if (!empty($onFinishJobs)) {
            try {
                $onFinishJobs($jobsAndResponses);
            } catch (Exception $ex) {
                Log::warning("CliProcessor::%s: onFinishJobs callback threw exception: '%s'", __FUNCTION__, $ex->getMessage());
                Log::debug($ex);
            }
        }
    }

    private function getNextJobId()
    {
        ++$this->currentJobId;
        return $this->currentJobId;
    }
}