<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\Jobs\Impl;

use Piwik\CliMulti;
use Piwik\Jobs\Impl\CliProcessor;
use Piwik\Jobs\Job;
use Piwik\Jobs\Queue;
use Piwik\Jobs\UrlJob;

class CliProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CliProcessor
     */
    public $cliProcessor;

    /**
     * @var Queue
     */
    public $mockQueue;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $mockCliMulti;

    public $queuedJobs;
    public $executingJobs;
    public $jobsExecuted;

    public $jobStartingCallbackUrls;
    public $jobFinishedCallbackUrls;

    public function setUp()
    {
        parent::setUp();

        $this->queuedJobs = array();
        $this->jobsExecuted = array();
        $this->executingJobs = array();
        $this->jobStartingCallbackUrls = array();
        $this->jobFinishedCallbackUrls = array();

        $this->mockQueue = $this->makeMockQueue();
        $this->mockCliMulti = $this->makeMockCliMulti();
        $maxNumberOfProcesses = 3; // TODO: make constant

        $this->cliProcessor = new CliProcessor(
            $this->mockQueue, $maxNumberOfProcesses, CliProcessor::DEFAULT_SLEEP_TIME, false, $this->mockCliMulti);
    }

    public function test_startProcessing_DoesNothing_WhenNoJobsToExecute()
    {
        $this->cliProcessor->startProcessing($finishWhenNoJobs = true);

        $this->assertEmpty($this->jobsExecuted);
    }

    public function test_startProcessing_AlwaysUsesMaxNumberOfProcesses_WhenExecutingJobs()
    {
        $self = $this;

        $this->addJobsToQueue($n = 9);

        $this->mockCliMulti->expects($this->any())->method('request')->will($this->returnCallback(function ($urls) use ($self) {
            $totalProcesses = count($urls) + count($self->executingJobs);
            $this->assertEquals(3, $totalProcesses);
        }));
        $this->mockCliMulti->expects($this->any())->method('start')->will($this->returnCallback(function ($urls) use ($self) {
            $totalProcesses = count($urls) + count($self->executingJobs);
            $this->assertEquals(3, $totalProcesses);
        }));

        $this->cliProcessor->startProcessing($finishWhenNoJobs = true);

        $this->assertEquals(array(
            "?jobN=0", "?jobN=1", "?jobN=2", "?jobN=3", "?jobN=4", "?jobN=5", "?jobN=6", "?jobN=7",
            "?jobN=8"
        ), array_unique($this->jobsExecuted));
        $this->assertEmpty($this->executingJobs);
    }

    public function test_startProcessing_ExecutesProperJobCallbacks_WhenExecutingJobs()
    {
        $onJobsStartingCallCount = 0;
        $onJobsFinishingCallCount = 0;

        $this->cliProcessor->setOnJobsStartingCallback(function ($jobs) use (&$onJobsStartingCallCount) {
            ++$onJobsStartingCallCount;
        });

        $this->cliProcessor->setOnJobsFinishedCallback(function ($jobs) use (&$onJobsFinishingCallCount) {
            ++$onJobsFinishingCallCount;
        });

        $this->addJobsToQueueWithCallbacks($n = 5);

        $this->cliProcessor->startProcessing($finishWhenNoJobs = true);

        $this->assertEquals(array(
            "?jobN=0", "?jobN=1", "?jobN=2", "?jobN=3", "?jobN=4"
        ), $this->jobStartingCallbackUrls);

        $this->assertEquals(array(
            "?jobN=0", "?jobN=1", "?jobN=2", "?jobN=3", "?jobN=4"
        ), $this->jobFinishedCallbackUrls);

        $this->assertEquals(2, $onJobsStartingCallCount);
        $this->assertEquals(2, $onJobsFinishingCallCount);
    }

    public function test_startProcessing_DoesNotPropagateJobCallbackExceptions()
    {
        $this->cliProcessor->setOnJobsStartingCallback(function ($jobs) {
            throw new \Exception("on jobs starting exception");
        });

        $this->cliProcessor->setOnJobsFinishedCallback(function ($jobs) {
            throw new \Exception("on jobs finished exception");
        });

        $this->addJobsToQueueWithCallbacks($n = 5, $throw = true);

        $this->cliProcessor->startProcessing($finishWhenNoJobs = true);
    }

    private function makeMockQueue()
    {
        $self = $this;

        $mock = $this->getMock("Piwik\\Jobs\\Queue", array('enqueue', 'pull', 'peek'));
        $mock->expects($this->any())->method('enqueue')->will($this->returnCallback(function ($jobs) use ($self) {
            $self->queuedJobs = array_merge($self->queuedJobs, $jobs);
        }));
        $mock->expects($this->any())->method('pull')->will($this->returnCallback(function ($count) use ($self) {
            return array_splice($self->queuedJobs, 0, $count);
        }));
        $mock->expects($this->any())->method('peek')->will($this->returnCallback(function () use ($self) {
            return count($self->queuedJobs);
        }));
        return $mock;
    }

    private function makeMockCliMulti()
    {
        // TODO: move mocks to separate files after moving files to piwik repo
        $self = $this;
        $onJobsFinished = null;

        $finishJobsChunk = function ($onJobsFinished) use ($self) {
            reset($self->executingJobs);

            $finishedJobUrls = array();
            $i = 0;
            foreach ($self->executingJobs as $key => $job) {
                if ($i >= 3) {
                    break;
                }

                $finishedJobUrls[$key] = $job;
                unset($self->executingJobs[$key]);

                ++$i;
            }

            $self->jobsExecuted = array_merge($self->jobsExecuted, $finishedJobUrls);

            $onJobsFinished($finishedJobUrls);
        };

        $mock = $this->getMock("Piwik\\CliMulti", array('request', 'start', 'getUnusedProcessCount'));
        $mock->expects($this->any())->method('request')->will($this->returnCallback(function ($urls, $callback)
            use ($self, &$onJobsFinished, $finishJobsChunk) {
            foreach ($urls as $id => $url) {
                $self->executingJobs[$id] = $url;
            }
            $onJobsFinished = $callback;

            $finishJobsChunk($callback);
        }));
        $mock->expects($this->any())->method('start')->will($this->returnCallback(function ($urls)
            use ($self, &$onJobsFinished, $finishJobsChunk) {
            foreach ($urls as $id => $url) {
                $self->executingJobs[$id] = $url;
            }

            $finishJobsChunk($onJobsFinished);
        }));
        $mock->expects($this->any())->method('getUnusedProcessCount')->will($this->returnCallback(function () use ($self) {
            return 3 - count($self->executingJobs);
        }));
        return $mock;
    }

    private function addJobsToQueue($n)
    {
        $jobs = array();
        for ($i = 0; $i != $n; ++$i) {
            $jobs[] = new UrlJob("?jobN=$i");
        }
        $this->mockQueue->enqueue($jobs);
    }

    private function addJobsToQueueWithCallbacks($n, $throw = false)
    {
        $self = $this;

        $jobs = array();
        for ($i = 0; $i != $n; ++$i) {
            $job = $this->getMock("Piwik\\Jobs\\UrlJob", array('jobStarting', 'jobFinished'));

            if ($throw) {
                $job->expects($this->any())->method('jobStarting')->will($this->returnCallback(function () use ($i) {
                    throw new \Exception("Job $i starting threw");
                }));

                $job->expects($this->any())->method('jobFinished')->will($this->returnCallback(function () use ($i) {
                    throw new \Exception("Job $i finished threw");
                }));
            } else {
                $job->expects($this->any())->method('jobStarting')->will($this->returnCallback(function () use ($self, $job) {
                    $self->jobStartingCallbackUrls[] = $job->getUrlString();
                }));

                $job->expects($this->any())->method('jobFinished')->will($this->returnCallback(function () use ($self, $job) {
                    $self->jobFinishedCallbackUrls[] = $job->getUrlString();
                }));
            }

            $job->url = array('jobN' => $i);

            $jobs[] = $job;
        }
        $this->mockQueue->enqueue($jobs);
    }
}