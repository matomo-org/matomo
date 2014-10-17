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
use Piwik\Jobs\Processor;
use Piwik\Jobs\Queue;

/**
 * Job processor that uses processes executed via shell_exec to process jobs in a
 * distributed queue.
 *
 * TODO: allow associating callback per job, not just every job [need DI]
 * TODO: add logging
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
     * Constructor.
     *
     * @param Queue $jobQueue The distributed queue containing jobs.
     * @param int $maxNumberOfSpawnedProcesses The maximum number of jobs to process simultaneously.
     * @param int $sleepTimeBetweenBatchJobExecutions The amount of time to wait before checking the queue for
     *                                                more jobs.
     */
    public function __construct(Queue $jobQueue, $maxNumberOfSpawnedProcesses = self::DEFAULT_MAX_SPAWNED_PROCESS_COUNT,
                                $sleepTimeBetweenBatchJobExecutions= self::DEFAULT_SLEEP_TIME)
    {
        $this->jobQueue = $jobQueue;
        $this->maxNumberOfSpawnedProcesses = $maxNumberOfSpawnedProcesses;
    }

    /**
     * Sets the callback to execute after one or more jobs finishes executing.
     *
     * @param callback $onJobsFinishedCallback The callback to execute. Signature must be:
     *
     *                                             function (string[] $responses)
     *
     *                                         Where `$responses` maps string URLs with string API responses.
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
     *                                           function (string[] $urls)
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
        $cliMulti = new CliMulti();
        $cliMulti->setConcurrentProcessesLimit($this->maxNumberOfSpawnedProcesses);

        $this->processing = true;

        try {
            for (;;) {
                $jobs = $this->pullJobs($this->maxNumberOfSpawnedProcesses);

                $self = $this;
                $onFinishJobs = $self->onJobsFinishedCallback;
                $cliMulti->request($jobs, function ($responses) use ($cliMulti, $self, $onFinishJobs) {
                    if (!empty($onFinishJobs)) {
                        $onFinishJobs($responses);
                    }

                    $newRequests = $self->pullJobs($cliMulti->getUnusedProcessCount());
                    $cliMulti->start($newRequests);
                });

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
     *       maybe it's not useless, need to check.
     */
    public function stopProcessing()
    {
        $this->processing = false;
    }

    /**
     * public for use in Closure.
     */
    public function pullJobs($count)
    {
        if (!$this->processing) {
            return array();
        }

        $jobs = $this->jobQueue->pull($count);

        $onJobStartingCallback = $this->onJobsStartingCallback;
        if (!empty($onJobStartingCallback)) {
            $onJobStartingCallback($jobs);
        }

        return $jobs;
    }

    private function waitBeforeCheckingForMoreJobs()
    {
        sleep($this->sleepTimeBetweenBatchJobExecutions);
    }
}