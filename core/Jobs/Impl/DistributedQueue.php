<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs\Impl;

use Piwik\Db;
use Piwik\Jobs\Job;
use Piwik\Jobs\Queue;
use Piwik\Option;
use Exception;

/**
 * MySQL based distributed queue implementation.
 *
 * Uses an option value.
 */
class DistributedQueue implements Queue
{
    const JOBS_OPTION_NAME_PREFIX = 'DistributedQueue.jobs.';

    /**
     * The name of the queue.
     *
     * @var string
     */
    private $name;

    /**
     * Constructor.
     *
     * @param string $name The name of the queue.
     */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

    /**
     * Atomically adds a list of URLs to the Piwik Option that holds the queue.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @param Job[] $jobs The URLs to add.
     */
    public function enqueue($jobs)
    {
        foreach ($jobs as $job) {
            $job->validate();
        }

        $self = $this;
        $this->runWithLock(function () use ($self, $jobs) {
            $existingJobs = $self->getJobUrls();
            $existingJobs = array_merge($existingJobs, $self->getSerializedJobs($jobs));
            $self->setJobUrls($existingJobs);
        });
    }

    /**
     * Atomically pops N URLs from the queue in the Piwik Option and returns them.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @param int $count The maximum number of URLs to get.
     * @return Job[] The URLs at the front of the queue.
     */
    public function pull($count)
    {
        $self = $this;
        return $this->runWithLock(function () use ($self, $count) {
            $existingJobs = $self->getJobUrls();

            $pulledJobs = array_splice($existingJobs, 0, $count);

            $self->setJobUrls($existingJobs);

            return $this->getDeserializedJobs($pulledJobs);
        });
    }

    /**
     * Returns the number of URLs in the queue in the Piwik Option.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @return int
     */
    public function peek()
    {
        $self = $this;
        return $this->runWithLock(function () use ($self) {
            return count($self->getJobUrls());
        });
    }

    private function runWithLock($callback)
    {
        $this->acquireLock();

        try {
            $result = $callback();

            $this->releaseLock();
        } catch (Exception $ex) {
            $this->releaseLock();

            throw $ex;
        }

        return $result;
    }

    private function getJobUrls()
    {
        $optionValue = Option::get($this->getJobUrlsOptionName());
        return @json_decode($optionValue, true) ?: array();
    }

    private function setJobUrls($existingJobs)
    {
        $optionValue = json_encode($existingJobs);
        Option::set($this->getJobUrlsOptionName(), $optionValue);
    }

    private function getJobUrlsOptionName()
    {
        return self::JOBS_OPTION_NAME_PREFIX . '_' . $this->name;
    }

    private function acquireLock()
    {
        $dbLockName = $this->getLockName();
        if (Db::getDbLock($dbLockName, $maxRetries = 30) === false) {
            throw new Exception("DistributedQueue::acquireLock: Cannot get named lock '$dbLockName'.");
        }
    }

    private function releaseLock()
    {
        $dbLockName = $this->getLockName();
        Db::releaseDbLock($dbLockName);
    }

    private function getLockName()
    {
        return self::JOBS_OPTION_NAME_PREFIX;
    }

    /**
     * Only public for use in closure.
     */
    public function getSerializedJobs($jobs)
    {
        return array_map('serialize', $jobs);
    }

    /**
     * Only public for use in closure.
     */
    public function getDeserializedJobs($pulledJobs)
    {
        return array_map('unserialize', $pulledJobs);
    }
}