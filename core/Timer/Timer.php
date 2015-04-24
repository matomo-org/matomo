<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Timer;

use Piwik\Metrics\Formatter;

/**
 * Utility class for timing.
 *
 * @api
 */
class Timer
{
    /**
     * @var Formatter
     */
    private $formatter;

    /**
     * @var float Time in seconds.
     */
    private $timeStart;

    /**
     * @var int
     */
    private $memoryStart;

    /**
     * Starts the timer
     */
    public function __construct()
    {
        $this->formatter = new Formatter();

        $this->timeStart = microtime(true);
        $this->memoryStart = $this->getMemoryUsage();
    }

    /**
     * Returns the time elapsed since the start in seconds.
     *
     * @return float
     */
    public function getTimeElapsed()
    {
        return microtime(true) - $this->timeStart;
    }

    /**
     * Returns the memory usage difference between now and when the timer was started.
     *
     * @param string $formatted If true will format the result, e.g.: `256 Kb`. If false, returns an int.
     * @return int|string
     */
    public function getMemoryDelta($formatted = false)
    {
        $delta = $this->getMemoryUsage() - $this->memoryStart;

        if ($formatted) {
            return $this->formatter->getPrettySizeFromBytes($this->getMemoryUsage() - $this->memoryStart);
        }

        return $delta;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $timeFormatted = number_format(microtime(true) - $this->timeStart, 3, '.', '');

        return 'Time elapsed: ' . $timeFormatted . 's';
    }

    /**
     * Returns current memory usage, if available
     *
     * @return int
     */
    private function getMemoryUsage()
    {
        if (function_exists('memory_get_usage')) {
            return memory_get_usage();
        }
        return 0;
    }
}
