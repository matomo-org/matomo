<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Scheduler;

use Piwik\Common;
use Piwik\Option;
use Piwik\Date;

/**
 * This data structure holds the scheduled times for each active scheduled task.
 */
class Timetable
{
    const TIMETABLE_OPTION_STRING = "TaskScheduler.timetable";
    const RETRY_OPTION_STRING = "TaskScheduler.retryList";

    private $timetable;
    private $retryList;

    public function __construct()
    {
        $this->readFromOption();
    }

    public function getTimetable()
    {
        return $this->timetable;
    }

    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;
    }

    public function setRetryList($retryList)
    {
        $this->retryList = $retryList;
    }

    /**
     * @param Task[] $activeTasks
     */
    public function removeInactiveTasks($activeTasks)
    {
        $activeTaskNames = array();
        foreach ($activeTasks as $task) {
            $activeTaskNames[] = $task->getName();
        }
        foreach (array_keys($this->timetable) as $taskName) {
            if (!in_array($taskName, $activeTaskNames)) {
                unset($this->timetable[$taskName]);
            }
        }
        $this->save();
    }

    public function getScheduledTaskNames()
    {
        return array_keys($this->timetable);
    }

    public function getScheduledTaskTime($taskName)
    {
        return isset($this->timetable[$taskName]) ? Date::factory($this->timetable[$taskName]) : false;
    }

    /**
     * Checks if the task should be executed
     *
     * Task has to be executed if :
     *  - the task has already been scheduled once and the current system time is greater than the scheduled time.
     *  - execution is forced, see $forceTaskExecution
     *
     * @param string $taskName
     *
     * @return boolean
     */
    public function shouldExecuteTask($taskName)
    {
        $forceTaskExecution = (defined('DEBUG_FORCE_SCHEDULED_TASKS') && DEBUG_FORCE_SCHEDULED_TASKS);

        if ($forceTaskExecution) {
            return true;
        }

        return $this->taskHasBeenScheduledOnce($taskName) && time() >= $this->timetable[$taskName];
    }

    /**
     * Checks if a task should be rescheduled
     *
     * Task has to be rescheduled if :
     *  - the task has to be executed
     *  - the task has never been scheduled before
     *
     * @param string $taskName
     *
     * @return boolean
     */
    public function taskShouldBeRescheduled($taskName)
    {
        return !$this->taskHasBeenScheduledOnce($taskName) || $this->shouldExecuteTask($taskName);
    }

    public function rescheduleTask(Task $task)
    {
        $rescheduledTime = $task->getRescheduledTime();

        // update the scheduled time
        $this->timetable[$task->getName()] = $rescheduledTime;
        $this->save();

        return Date::factory($rescheduledTime);
    }

    public function rescheduleTaskAndRunTomorrow(Task $task)
    {
        $tomorrow = Date::factory('tomorrow');

        // update the scheduled time
        $this->timetable[$task->getName()] = $tomorrow->getTimestamp();
        $this->save();

        return $tomorrow;
    }

    public function rescheduleTaskAndRunInOneHour(Task $task)
    {
        $oneHourFromNow = Date::factory('now')->addHour(1);

        // update the scheduled time
        $this->timetable[$task->getName()] = $oneHourFromNow->getTimestamp();
        $this->save();

        return $oneHourFromNow;
    }

    public function save()
    {
        Option::set(self::TIMETABLE_OPTION_STRING, serialize($this->timetable));
    }

    public function getScheduledTimeForMethod($className, $methodName, $methodParameter = null)
    {
        $taskName = Task::getTaskName($className, $methodName, $methodParameter);

        return $this->taskHasBeenScheduledOnce($taskName) ? $this->timetable[$taskName] : false;
    }

    public function taskHasBeenScheduledOnce($taskName)
    {
        return isset($this->timetable[$taskName]);
    }

    public function readFromOption()
    {
        Option::clearCachedOption(self::TIMETABLE_OPTION_STRING);
        $optionData = Option::get(self::TIMETABLE_OPTION_STRING);
        $unserializedTimetable = Common::safe_unserialize($optionData);

        $this->timetable = $unserializedTimetable === false ? array() : $unserializedTimetable;
    }

    /**
     * Read the retry list option from the database
     *
     * @throws \Throwable
     */
    private function readRetryList()
    {
        Option::clearCachedOption(self::RETRY_OPTION_STRING);
        $retryData = Option::get(self::RETRY_OPTION_STRING);
        $unserializedRetryList = Common::safe_unserialize($retryData);

        $this->retryList = $unserializedRetryList === false ? array() : $unserializedRetryList;
    }

    /**
     * Save the retry list option to the database
     */
    public function saveRetryList()
    {
        Option::set(self::RETRY_OPTION_STRING, serialize($this->retryList));
    }

    /**
     * Remove a task from the retry list
     *
     * @param string $taskName
     */
    public function clearRetryCount(string $taskName)
    {
        if (isset($this->retryList[$taskName])) {
            unset($this->retryList[$taskName]);
            $this->saveRetryList();
        }
    }

    /**
     * Increment the retry counter for a task
     *
     * @param string $taskName
     */
    public function incrementRetryCount(string $taskName)
    {
        $this->readRetryList();
        if (!isset($this->retryList[$taskName])) {
            $this->retryList[$taskName] = 0;
        }
        $this->retryList[$taskName]++;
        $this->saveRetryList();
    }

    /**
     * Return the current number of retries for a task
     *
     * @param string $taskName
     *
     * @return int
     */
    public function getRetryCount(string $taskName) : int
    {
        $this->readRetryList();

        // Ignore excessive retry counts, workaround for SchedulerTest mock
        if (!isset($this->retryList[$taskName]) || $this->retryList[$taskName] > 10000) {
            return 0;
        }

        return $this->retryList[$taskName];
    }

}
