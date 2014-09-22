<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs\Impl;

use Piwik\Db;
use Piwik\Jobs\Queue;
use Piwik\Option;
use SebastianBergmann\Exporter\Exception;

/**
 * TODO
 */
class DistributedQueue implements Queue
{
    const JOBS_OPTION_NAME_PREFIX = 'DistributedQueue.jobs.';

    /**
     * TODO
     *
     * @var string
     */
    private $jobNamespace;

    /**
     * TODO
     */
    public function __construct($jobNamespace = '')
    {
        $this->jobNamespace = $jobNamespace;
    }

    /**
     * TODO
     */
    public function enqueue($urls)
    {
        $self = $this;
        $this->runWithLock(function () use ($self, $urls) {
            $existingJobs = $self->getJobUrls();
            $existingJobs = array_merge($existingJobs, $urls);
            $self->setJobUrls($existingJobs);
        });
    }

    /**
     * TODO
     */
    public function pull($count)
    {
        $self = $this;
        return $this->runWithLock(function () use ($self, $count) {
            $existingJobs = $self->getJobUrls();

            $pulledJobs = array_splice($existingJobs, 0, $count);

            $self->setJobUrls($existingJobs);

            return $pulledJobs;
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
        return json_decode($optionValue, true);
    }

    private function setJobUrls($existingJobs)
    {
        $optionValue = json_encode($existingJobs);
        Option::set($this->getJobUrlsOptionName(), $optionValue);
    }

    private function getJobUrlsOptionName()
    {
        return self::JOBS_OPTION_NAME_PREFIX . '_' . $this->jobNamespace;
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
}