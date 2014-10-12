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
 * This data structure holds the scheduled times for each active scheduled task.
 */
class ScheduledTaskTimetable
{
    const TIMETABLE_OPTION_STRING = "TaskScheduler.timetable";

    private $timetable;

    public function __construct()
    {
        $optionData = Option::get(self::TIMETABLE_OPTION_STRING);
        $unserializedTimetable = @unserialize($optionData);

        $this->timetable = $unserializedTimetable === false ? array() : $unserializedTimetable;
    }

    public function getTimetable()
    {
        return $this->timetable;
    }

    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;
    }

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

        return $forceTaskExecution || ($this->taskHasBeenScheduledOnce($taskName) && time() >= $this->timetable[$taskName]);
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

    public function rescheduleTask($task)
    {
        // update the scheduled time
        $this->timetable[$task->getName()] = $task->getRescheduledTime();
        $this->save();
    }

    public function save()
    {
        Option::set(self::TIMETABLE_OPTION_STRING, serialize($this->timetable));
    }

    public function getScheduledTimeForMethod($className, $methodName, $methodParameter = null)
    {
        $taskName = ScheduledTask::getTaskName($className, $methodName, $methodParameter);

        return $this->taskHasBeenScheduledOnce($taskName) ? $this->timetable[$taskName] : false;
    }

    public function taskHasBeenScheduledOnce($taskName)
    {
        return isset($this->timetable[$taskName]);
    }
}
