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
 * Manages scheduled task execution.
 * 
 * A scheduled task is a callback that should be executed every so often (such as daily,
 * weekly, monthly, etc.). They are registered with **TaskScheduler** through the
 * {@hook TaskScheduler.getScheduledTasks} event.
 * 
 * Tasks are executed when the cron archive.php script is executed.
 * 
 * ### Examples
 * 
 * **Scheduling a task**
 * 
 *     // event handler for TaskScheduler.getScheduledTasks event
 *     public function getScheduledTasks(&$tasks)
 *     {
 *         $tasks[] = new ScheduledTask(
 *             'Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient',
 *             'clearAllCacheEntries',
 *             null,
 *             ScheduledTime::factory('daily'),
 *             ScheduledTask::LOWEST_PRIORITY
 *         );
 *     }
 * 
 * **Executing all pending tasks**
 * 
 *     $results = TaskScheduler::runTasks();
 *     $task1Result = $results[0];
 *     $task1Name = $task1Result['task'];
 *     $task1Output = $task1Result['output'];
 * 
 *     echo "Executed task '$task1Name'. Task output:\n$task1Output";
 * 
 * @package Piwik
 */
class TaskScheduler
{
    const GET_TASKS_EVENT = "TaskScheduler.getScheduledTasks";
    const TIMETABLE_OPTION_STRING = "TaskScheduler.timetable";
    static private $running = false;

    /**
     * Executes tasks that are scheduled to run, then reschedules them.
     *
     * @return array An array describing the results of scheduled task execution. Each element
     *               in the array will have the following format:
     *               ```
     *               array(
     *                   'task' => 'task name',
     *                   'output' => '... task output ...'
     *               )
     *               ```
     */
    static public function runTasks()
    {
        // get the array where rescheduled timetables are stored
        $timetable = self::getTimetableFromOptionTable();

        // collect tasks
        $tasks = array();

        /**
         * Triggered during scheduled task execution. Collects all the tasks to run.
         * 
         * Subscribe to this event to schedule code execution on an hourly, daily, weekly or monthly
         * basis.
         *
         * **Example**
         * 
         *     public function getScheduledTasks(&$tasks)
         *     {
         *         $tasks[] = new ScheduledTask(
         *             'Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient',
         *             'clearAllCacheEntries',
         *             null,
         *             ScheduledTime::factory('daily'),
         *             ScheduledTask::LOWEST_PRIORITY
         *         );
         *     }
         * 
         * @param ScheduledTask[] &$tasks List of tasks to run periodically.
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
                    self::setTimetableFromOptionTable($timetable);
                }
            }
        }

        return $executionResults;
    }

    /**
     * Determines a task's scheduled time and persists it, overwriting the previous scheduled time.
     * 
     * Call this method if your task's scheduled time has changed due to, for example, an option that
     * was changed.
     * 
     * @param ScheduledTask $task Describes the scheduled task being rescheduled.
     * @api
     */
    static public function rescheduleTask(ScheduledTask $task)
    {
        $timetable = self::getTimetableFromOptionTable();

        $timetable[$task->getname()] = $task->getRescheduledTime();

        self::setTimetableFromOptionTable($timetable);
    }

    /**
     * Returns true if the TaskScheduler is currently running a scheduled task.
     * 
     * @return bool
     */
    static public function isTaskBeingExecuted()
    {
        return self::$running;
    }

    /**
     * Return the next scheduled time given the class and method names of a scheduled task.
     *
     * @param string $className The name of the class that contains the scheduled task method.
     * @param string $methodName The name of the scheduled task method.
     * @param string|null $methodParameter Optional method parameter.
     * @return mixed int|bool The time in miliseconds when the scheduled task will be executed
     *                        next or false if it is not scheduled to run.
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
    
    static private function setTimetableFromOptionTable($timetable)
    {
        Option::set(self::TIMETABLE_OPTION_STRING, serialize($timetable));
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