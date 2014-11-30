<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Jobs;

use Exception;
use Piwik\Container\StaticContainer;

/**
 * Helper that gets named Queues and Processors from DI config.
 */
class Helper
{
    const QUEUE_NAME_PREFIX = 'jobs.queues.';
    const PROCESSOR_NAME_PREFIX = 'jobs.processors.';

    /**
     * Returns a named queue instance.
     *
     * @param string $name The name of the queue instance. Prefixes the name w/ `"jobs.queues."` when looking in DI.
     * @return Queue
     * @throws Exception If the object in the container does not implement Queue.
     * @throws \DI\NotFoundException
     */
    public static function getNamedQueue($name)
    {
        $name = self::QUEUE_NAME_PREFIX . $name;

        $queue = StaticContainer::getContainer()->get($name);

        if (!($queue instanceof Queue)) {
            throw new Exception("Named queue '$name' must implement the Piwik\\Jobs\\Queue interface.");
        }

        return $queue;
    }

    /**
     * Returns a named processor instance.
     *
     * @param string $name The name of the processor instance. Prefixes the name w/ `"jobs.processors."` when looking in DI.
     * @return Processor
     * @throws Exception If the object in the container does not implement Processor.
     * @throws \DI\NotFoundException
     */
    public static function getNamedProcessor($name)
    {
        $name = self::PROCESSOR_NAME_PREFIX . $name;

        $processor = StaticContainer::getContainer()->get($name);

        if (!($processor instanceof Processor)) {
            throw new Exception("Named processor '$name' must implement the Piwik\\Jobs\\Processor interface.");
        }

        return $processor;
    }
}