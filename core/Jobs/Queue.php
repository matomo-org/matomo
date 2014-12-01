<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;

/**
 * Interface for a distributed queue.
 *
 * Distributed queues must support:
 *
 * - atomically queueing one or more string URLs to the end of the queue
 * - atomically pulling one or more string URLs from the beginning of the queue
 * - and atomically checking the size of the queue
 *
 * **NOTE: This API is not stable.**
 */
interface Queue
{
    /**
     * Adds a list of URLs to the queue. This operation is atomic.
     *
     * The order of the URLs must be preserved in the queue.
     *
     * @param Job[] $jobs An array of jobs to queue.
     */
    public function enqueue($jobs);

    /**
     * Removes N URLs from the beginning of the queue and returns them. This operation is atomic.
     *
     * The order of the returned URLs will be the same as the order in the queue.
     *
     * @param int $count The number of Jobs to get.
     * @return Job[] The Jobs.
     */
    public function pull($count);

    /**
     * Returns the number of Jobs stored in the queue. This operation is atomic.
     *
     * The number of Jobs can change after this method returns.
     *
     * @return int
     */
    public function peek();
}