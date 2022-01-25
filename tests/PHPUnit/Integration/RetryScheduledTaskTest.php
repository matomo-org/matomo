<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;
use Piwik\Scheduler\Timetable;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Scheduler
 * @group SchedulerRetryTaskTest
 */
class RetryScheduledTaskTest extends IntegrationTestCase
{

    public function testRetryCount()
    {

        $timetable = new Timetable();

        $task1 = 'task1';
        $task2 = 'task2';

        // Should be zero if no retry entry present
        $this->assertEquals(0, $timetable->getRetryCount($task1));

        // Should increment by one
        $timetable->incrementRetryCount($task1);
        $this->assertEquals(1, $timetable->getRetryCount($task1));

        // Should not break if more than one tasks is counting retries
        $timetable->incrementRetryCount($task2);
        $timetable->incrementRetryCount($task2);
        $this->assertEquals(2, $timetable->getRetryCount($task2));
        $timetable->incrementRetryCount($task1);
        $this->assertEquals(2, $timetable->getRetryCount($task1));

        // Should clear retry count without affecting other tasks
        $timetable->clearRetryCount($task1);
        $this->assertEquals(0, $timetable->getRetryCount($task1));
        $this->assertEquals(2, $timetable->getRetryCount($task2));
        $timetable->clearRetryCount($task2);
        $this->assertEquals(0, $timetable->getRetryCount($task1));

    }

}
