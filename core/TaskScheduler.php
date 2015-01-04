<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Container\StaticContainer;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;

// When set to true, all scheduled tasks will be triggered in all requests (careful!)
//define('DEBUG_FORCE_SCHEDULED_TASKS', true);

/**
 * Manages scheduled task execution.
 *
 * A scheduled task is a callback that should be executed every so often (such as daily,
 * weekly, monthly, etc.). They are registered by extending {@link \Piwik\Plugin\Tasks}.
 *
 * Tasks are executed when the `core:archive` command is executed.
 *
 * ### Examples
 *
 * **Scheduling a task**
 *
 *     class Tasks extends \Piwik\Plugin\Tasks
 *     {
 *         public function schedule()
 *         {
 *             $this->hourly('myTask');  // myTask() will be executed once every hour
 *         }
 *         public function myTask()
 *         {
 *             // do something
 *         }
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
 * @deprecated Use Piwik\Scheduler\Scheduler instead
 * @see \Piwik\Scheduler\Scheduler
 */
class TaskScheduler
{
    /**
     * Executes tasks that are scheduled to run, then reschedules them.
     *
     * @return array An array describing the results of scheduled task execution. Each element
     *               in the array will have the following format:
     *
     *               ```
     *               array(
     *                   'task' => 'task name',
     *                   'output' => '... task output ...'
     *               )
     *               ```
     */
    public static function runTasks()
    {
        return self::getInstance()->run();
    }

    /**
     * Determines a task's scheduled time and persists it, overwriting the previous scheduled time.
     *
     * Call this method if your task's scheduled time has changed due to, for example, an option that
     * was changed.
     *
     * @param Task $task Describes the scheduled task being rescheduled.
     * @api
     */
    public static function rescheduleTask(Task $task)
    {
        self::getInstance()->rescheduleTask($task);
    }

    /**
     * Returns true if the TaskScheduler is currently running a scheduled task.
     *
     * @return bool
     */
    public static function isTaskBeingExecuted()
    {
        return self::getInstance()->isRunningTask();
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
    public static function getScheduledTimeForMethod($className, $methodName, $methodParameter = null)
    {
        return self::getInstance()->getScheduledTimeForMethod($className, $methodName, $methodParameter);
    }

    /**
     * @return Scheduler
     */
    private static function getInstance()
    {
        return StaticContainer::getContainer()->get('Piwik\Scheduler\Scheduler');
    }
}
