<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

/**
 *
 */
class Timer
{
    private $timerStart;
    private $memoryStart;

    /**
     * @return \Piwik\Timer
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @return void
     */
    public function init()
    {
        $this->timerStart = $this->getMicrotime();
        $this->memoryStart = $this->getMemoryUsage();
    }

    /**
     * @param int $decimals
     * @return string
     */
    public function getTime($decimals = 3)
    {
        return number_format($this->getMicrotime() - $this->timerStart, $decimals, '.', '');
    }

    /**
     * @param int $decimals
     * @return string
     */
    public function getTimeMs($decimals = 3)
    {
        return number_format(1000 * ($this->getMicrotime() - $this->timerStart), $decimals, '.', '');
    }

    /**
     * @return string
     */
    public function getMemoryLeak()
    {
        return "Memory delta: " . MetricsFormatter::getPrettySizeFromBytes($this->getMemoryUsage() - $this->memoryStart);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Time elapsed: " . $this->getTime() . "s";
    }

    /**
     * @return float
     */
    private function getMicrotime()
    {
        list($micro_seconds, $seconds) = explode(" ", microtime());
        return ((float)$micro_seconds + (float)$seconds);
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
