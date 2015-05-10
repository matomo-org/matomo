<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Timer;

use Piwik\Timer\Timer;

class TimerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function should_measure_time_elapsed()
    {
        $timer = new Timer();

        usleep(2000); // sleep 2 ms

        $duration = $timer->getTimeElapsed();

        $this->assertInternalType('float', $duration);
        $this->assertGreaterThan(0.002, $duration);
    }

    /**
     * @test
     */
    public function should_measure_memory_delta()
    {
        $timer = new Timer();

        $this->assertInternalType('int', $timer->getMemoryDelta());

        $a = $timer->getMemoryDelta();
        $this->assertGreaterThanOrEqual($a, $timer->getMemoryDelta());
    }

    /**
     * @test
     */
    public function should_format_measured_memory_delta()
    {
        $timer = new Timer();

        $this->assertInternalType('string', $timer->getMemoryDelta(true));
        $this->assertStringMatchesFormat('%i %s', $timer->getMemoryDelta(true));
    }

    /**
     * @test
     */
    public function should_cast_to_string()
    {
        $timer = new Timer();
        usleep(1000);
        $this->assertStringMatchesFormat('Time elapsed: %fs', (string) $timer);
    }
}
