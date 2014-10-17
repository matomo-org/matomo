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
 * NOTE: This API is not stable. It will be considered stable after Dependency Injection is
 *       implemented in core.
 */
interface Queue
{
    /**
     * Adds a list of URLs to the queue. This operation is atomic.
     *
     * The order of the URLs must be preserved in the queue.
     *
     * @param string[] $urls The URLs to add. The hostname should be included.
     */
    public function enqueue($urls);

    /**
     * Removes N URLs from the beginning of the queue and returns them. This operation is atomic.
     *
     * The order of the returned URLs will be the same as the order in the queue.
     *
     * @param int $count The number of URLs to get.
     * @return string[] The URLs.
     */
    public function pull($count);

    /**
     * Returns the number of URLs stored in the queue. This operation is atomic.
     *
     * The number of URLs can change after this method returns.
     *
     * @return int
     */
    public function peek();
}