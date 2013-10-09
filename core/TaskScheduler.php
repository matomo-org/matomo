<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

// When set to true, all scheduled tasks will be triggered in all requests (careful!)
namespace Piwik;

use Exception;

define('DEBUG_FORCE_SCHEDULED_TASKS', false);

/**
 * TaskScheduler is the class used to manage the execution of periodicaly planned task.
 *
 * It performs the following actions :
 *    - Identifies tasks of Piwik
 *  - Runs tasks
 *
 * @package Piwik
 */

class TaskScheduler
{
    const GET_TASKS_EVENT = "TaskScheduler.getScheduledTasks";
    const TIMETABLE_OPTION_STRING = "TaskScheduler.timetable";
    static private $running = false;

    /**
     * runTasks collects tasks defined within piwik plugins, runs them if they are scheduled and reschedules
     * the tasks that have been executed.
     *
     * @return array
     */
    static public function runTasks()
    {
        // get the array where rescheduled timetables are stored
        $timetable = self::getTimetableFromOptionTable();

        // collect tasks
        $tasks = array();

        /**
         * This event can be used to register any tasks that you may want to schedule on a regular basis. For instance
         * hourly, daily, weekly or monthly. It is comparable to a cronjob. The registered method will be executed
         * depending on the interval that you specify. See `Piwik\ScheduledTask` for more information.
         *
         * Example:
         * ```
         * public function getScheduledTasks(&$tasks)
         * {
         *     $tasks[] = new ScheduledTask(
         *         'Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient',
         *         'clearAllCacheEntries',
         *         null,
         *         new Daily(),
         *         ScheduledTask::LOWEST_PRIORITY
         *     );
         * }
         * ```
         */
        Piwik::postEvent(self::GET_TASKS_EVENT, array(&$tasks));
        /** @var ScheduledTask[] $tasks */

        // remove from timetable tasks that are not active anymore
        $activeTaskNames = array();
        foreach ($tasks as $task) {
            $activeTaskNames[] = $task->getName();
        }
        foreach (array_keys($timetable) as $taskName) {
            if (!in_array($taskName, $activeTaskNames)) {
                unset($timetable[$taskName]);
            }
        }

        // for every priority level, starting with the highest and concluding with the lowest
        $executionResults = array();
        for ($priority = ScheduledTask::HIGHEST_PRIORITY;
             $priority <= ScheduledTask::LOWEST_PRIORITY;
             ++$priority) {
            // loop through each task
            foreach ($tasks as $task) {
                // if the task does not have the current priority level, don't execute it yet
                if ($task->getPriority() != $priority) {
                    continue;
                }

                $taskName = $task->getName();
                if (self::taskShouldBeExecuted($taskName, $timetable)) {
                    self::$running = true;
                    $message = self::executeTask($task);
                    self::$running = false;

                    $executionResults[] = array('task' => $taskName, 'output' => $message);
                }

                if (self::taskShouldBeRescheduled($taskName, $timetable)) {
                    // update the scheduled time
                    $timetable[$taskName] = $task->getRescheduledTime();
                    Option::set(self::TIMETABLE_OPTION_STRING, serialize($timetable));
                }
            }
        }

        return $executionResults;
    }

    static public function isTaskBeingExecuted()
    {
        return self::$running;
    }

    /**
     * return the next task schedule for a given class and method name
     *
     * @param string $className
     * @param string $methodName
     * @param string $methodParameter
     * @return mixed int|bool the next schedule in miliseconds, false if task has never been run
     */
    static public function getScheduledTimeForMethod($className, $methodName, $methodParameter = null)
    {

        // get the array where rescheduled timetables are stored
        $timetable = self::getTimetableFromOptionTable();

        $taskName = ScheduledTask::getTaskName($className, $methodName, $methodParameter);

        return self::taskHasBeenScheduledOnce($taskName, $timetable) ? $timetable[$taskName] : false;
    }

    /**
     * Checks if the task should be executed
     *
     * Task has to be executed if :
     *  - the task has already been scheduled once and the current system time is greater than the scheduled time.
     *  - execution is forced, see $forceTaskExecution
     *
     * @param string $taskName
     * @param array $timetable
     *
     * @return boolean
     */
    static private function taskShouldBeExecuted($taskName, $timetable)
    {
        $forceTaskExecution =
            (isset($GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS']) && $GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS'])
            || DEBUG_FORCE_SCHEDULED_TASKS;

        return $forceTaskExecution || (self::taskHasBeenScheduledOnce($taskName, $timetable) && time() >= $timetable[$taskName]);
    }

    /**
     * Checks if a task should be rescheduled
     *
     * Task has to be rescheduled if :
     *  - the task has to be executed
     *  - the task has never been scheduled before
     *
     * @param string $taskName
     * @param array $timetable
     *
     * @return boolean
     */
    static private function taskShouldBeRescheduled($taskName, $timetable)
    {
        return !self::taskHasBeenScheduledOnce($taskName, $timetable) || self::taskShouldBeExecuted($taskName, $timetable);
    }

    static private function taskHasBeenScheduledOnce($taskName, $timetable)
    {
        return isset($timetable[$taskName]);
    }

    static private function getTimetableFromOptionValue($option)
    {
        $unserializedTimetable = @unserialize($option);
        return $unserializedTimetable === false ? array() : $unserializedTimetable;
    }

    static private function getTimetableFromOptionTable()
    {
        return self::getTimetableFromOptionValue(Option::get(self::TIMETABLE_OPTION_STRING));
    }

    /**
     * Executes the given taks
     *
     * @param ScheduledTask $task
     * @return string
     */
    static private function executeTask($task)
    {
        try {
            $timer = new Timer();
            call_user_func(array($task->getObjectInstance(), $task->getMethodName()), $task->getMethodParameter());
            $message = $timer->__toString();
        } catch (Exception $e) {
            $message = 'ERROR: ' . $e->getMessage();
        }

        return $message;
    }
}
