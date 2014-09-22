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
use Piwik\Jobs\Consumer;
use Piwik\Jobs\Queue;

/**
 * TODO
 */
class CliConsumer implements Consumer
{
    const DEFAULT_MAX_SPAWNED_PROCESS_COUNT = 3;
    const DEFAULT_SLEEP_TIME = 60;

    /**
     * TODO
     *
     * @var Queue
     */
    private $jobQueue;

    /**
     * TODO
     *
     * @var callback|null
     */
    private $onJobsFinishedCallback;

    /**
     * TODO
     *
     * @var callback|null
     */
    private $onJobsStartingCallback;

    /**
     * TODO
     */
    private $maxNumberOfSpawnedProcesses;

    /**
     * TODO
     */
    private $consuming = false;

    /**
     * TODO
     *
     * @var int
     */
    private $sleepTimeBetweenBatchJobExecutions;

    /**
     * TODO
     */
    public function __construct(Queue $jobQueue, $maxNumberOfSpawnedProcesses = self::DEFAULT_MAX_SPAWNED_PROCESS_COUNT,
                                $sleepTimeBetweenBatchJobExecutions= self::DEFAULT_SLEEP_TIME)
    {
        $this->jobQueue = $jobQueue;
        $this->maxNumberOfSpawnedProcesses = $maxNumberOfSpawnedProcesses;
    }

    /**
     * TODO
     */
    public function setOnJobsFinishedCallback($onJobsFinishedCallback)
    {
        $this->onJobsFinishedCallback = $onJobsFinishedCallback;
    }

    /**
     * TODO
     */
    public function setOnJobsStartingCallback($onJobsStartingCallback)
    {
        $this->onJobsStartingCallback = $onJobsStartingCallback;
    }

    /**
     * TODO
     */
    public function startConsuming($finishWhenNoJobs = false)
    {
        $cliMulti = new CliMulti();
        $cliMulti->setConcurrentProcessesLimit($this->maxNumberOfSpawnedProcesses);

        $this->consuming = true;

        try {
            for (;;) {
                $jobs = $this->pullJobs($this->maxNumberOfSpawnedProcesses);

                $self = $this;
                $onFinishJobs = $self->onJobsFinishedCallback;
                $cliMulti->request($jobs, function ($responses) use ($cliMulti, $self, $onFinishJobs) {
                    $onFinishJobs($responses);

                    $newRequests = $self->pullJobs($cliMulti->getUnusedProcessCount());
                    $cliMulti->start($newRequests);
                });

                if ($finishWhenNoJobs
                    || !$this->consuming
                ) {
                    break;
                } else {
                    $this->waitBeforeCheckingForMoreJobs();
                }
            }
        } catch (Exception $ex) {
            $this->consuming = false;

            throw $ex;
        }
    }

    /**
     * TODO
     */
    public function stopConsuming()
    {
        $this->consuming = false;
    }

    /**
     * public for use in Closure.
     */
    public function pullJobs($count)
    {
        if (!$this->consuming) {
            return array();
        }

        $jobs = $this->jobQueue->pull($count);

        $onJobStartingCallback = $this->onJobsStartingCallback;
        $onJobStartingCallback($jobs);

        return $jobs;
    }

    private function waitBeforeCheckingForMoreJobs()
    {
        sleep($this->sleepTimeBetweenBatchJobExecutions);
    }
}