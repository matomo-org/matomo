<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs\Impl;

use Piwik\Concurrency\AtomicList;
use Piwik\Db;
use Piwik\Jobs\Job;
use Piwik\Jobs\Queue;

/**
 * MySQL based distributed queue implementation.
 *
 * Uses an option value.
 */
class DistributedQueue implements Queue
{
    const JOBS_OPTION_NAME_PREFIX = 'DistributedQueue.jobs.';

    /**
     * Distributed list instance used to implement this queue.
     *
     * @var AtomicList
     */
    private $jobList;

    /**
     * Constructor.
     *
     * @param string $name The name of the queue.
     */
    public function __construct($name)
    {
        $this->jobList = new AtomicList(self::JOBS_OPTION_NAME_PREFIX . $name);
    }

    /**
     * Atomically adds a list of jobs to the Piwik Option that holds the queue.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @param Job[] $jobs The jobs to add.
     */
    public function enqueue($jobs)
    {
        $this->jobList->push($jobs);
    }

    /**
     * Atomically pops N jobs from the queue in the Piwik Option and returns them.
     *
     * This operation uses a named lock to ensure atomicity.
     *
     * @param int $count The maximum number of jobs to get.
     * @return Job[] The jobs at the front of the queue.
     */
    public function pull($count)
    {
        return $this->jobList->pull($count);
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
        return count($this->jobList->getAll());
    }
}