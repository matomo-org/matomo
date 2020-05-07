<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Metrics\Formatter;

/**
 *
 */
class Timer
{
    private $timerStart;
    private $memoryStart;
    private $formatter;
    private $timerEnd;

    /**
     * @return \Piwik\Timer
     */
    public function __construct()
    {
        $this->formatter = new Formatter();

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

    public function finish()
    {
        $this->timerEnd = $this->getMicrotime();
    }

    /**
     * @param int $decimals
     * @return string
     */
    public function getTime($decimals = 3)
    {
        return number_format($this->getTimerEnd() - $this->timerStart, $decimals, '.', '');
    }

    /**
     * @param int $decimals
     * @return string
     */
    public function getTimeMs($decimals = 3)
    {
        return number_format(1000 * ($this->getTimerEnd() - $this->timerStart), $decimals, '.', '');
    }

    /**
     * @return string
     */
    public function getMemoryLeak()
    {
        return "Memory delta: " . $this->getMemoryLeakValue();
    }

    /**
     * @return string
     */
    public function getMemoryLeakValue()
    {
        return $this->formatter->getPrettySizeFromBytes($this->getMemoryUsage() - $this->memoryStart);
    }

    /**
     * @return string
     */
    public function getPeakMemoryValue()
    {
        return $this->formatter->getPrettySizeFromBytes($this->getPeakMemoryUsage());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Time elapsed: " . $this->getTime() . "s";
    }

    private function getTimerEnd()
    {
        return $this->timerEnd ?: $this->getMicrotime();
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

    public function getPeakMemoryUsage()
    {
        if (function_exists('memory_get_peak_usage')) {
            return memory_get_peak_usage();
        }
        return 0;
    }
}
