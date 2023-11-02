<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;
use Piwik\Scheduler\RetryableException;
use Piwik\Scheduler\Timetable;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;
use Piwik\Tests\Framework\Mock\PiwikOption;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Log\NullLogger;
use ReflectionProperty;

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

    public function testTaskIsRetriedIfRetryableExcetionIsThrown()
    {

        // Mock timetable
        $now = time() - 60;
        $taskName = 'Piwik\Tests\Integration\RetryScheduledTaskTest.exceptionalTask';
        $timetableData = serialize([$taskName => $now]);
        self::getReflectedPiwikOptionInstance()->setValue(null, new PiwikOption($timetableData));

        // Create task
        $dailySchedule = $this->createPartialMock('Piwik\Scheduler\Schedule\Daily', array('getTime'));
        $dailySchedule->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($now));

        // Setup scheduler
        $tasks = [new Task($this, 'exceptionalTask', null, $dailySchedule)];
        $taskLoader = $this->createMock('Piwik\Scheduler\TaskLoader');
        $taskLoader->expects($this->atLeastOnce())
            ->method('loadTasks')
            ->willReturn($tasks);

        $scheduler = new Scheduler($taskLoader, new NullLogger());

        // First run
        $scheduler->run();
        $nextRun = $scheduler->getScheduledTimeForMethod('Piwik\Tests\Integration\RetryScheduledTaskTest', 'exceptionalTask', null);

        // Should be rescheduled one hour from now
        $this->assertEquals($now+3660, $nextRun);

        self::getReflectedPiwikOptionInstance()->setValue(null, null);

    }

    public function testTaskIsNotRetriedIfNormalExcetionIsThrown()
    {
        // Mock timetable
        $now = time() - 60;
        $taskName = 'Piwik\Tests\Integration\RetryScheduledTaskTest.normalExceptionTask';
        $timetableData = serialize([$taskName => $now]);
        self::getReflectedPiwikOptionInstance()->setValue(null, new PiwikOption($timetableData));

        // Create task
        $specificSchedule = $this->createPartialMock('Piwik\Scheduler\Schedule\SpecificTime', array('getTime'));
        $specificSchedule->setScheduledTime($now+50000);
        $specificSchedule->expects($this->any())
            ->method('getTime')
            ->will($this->returnValue($now));

        // Setup scheduler
        $tasks = [new Task($this, 'normalExceptionTask', null, $specificSchedule)];
        $taskLoader = $this->createMock('Piwik\Scheduler\TaskLoader');
        $taskLoader->expects($this->atLeastOnce())
            ->method('loadTasks')
            ->willReturn($tasks);

        $scheduler = new Scheduler($taskLoader, new NullLogger());

        // First run
        $scheduler->run();
        $nextRun = $scheduler->getScheduledTimeForMethod('Piwik\Tests\Integration\RetryScheduledTaskTest', 'normalExceptionTask', null);

        // Should not have scheduled for retry
        $this->assertEquals($now+50000, $nextRun);

        self::getReflectedPiwikOptionInstance()->setValue(null, null);
    }

    private static function getReflectedPiwikOptionInstance()
    {
        $piwikOptionInstance = new ReflectionProperty('Piwik\Option', 'instance');
        $piwikOptionInstance->setAccessible(true);
        return $piwikOptionInstance;
    }

    public function exceptionalTask()
    {
        throw new RetryableException('This task fails and should be retried');
    }

    public function normalExceptionTask()
    {
        throw new \Exception('This task fails and should not be retried');
    }

}
