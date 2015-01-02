<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Scheduler;

use Exception;
use Piwik\Timer;

/**
 * Schedules task execution.
 *
 * A scheduled task is a callback that should be executed every so often (such as daily,
 * weekly, monthly, etc.). They are registered in the **Scheduler** through the
 * {@hook TaskScheduler.getScheduledTasks} event.
 *
 * Tasks are executed when the cron core:archive command is executed.
 *
 * ### Examples
 *
 * **Scheduling a task**
 *
 *     // event handler for Scheduler.getScheduledTasks event
 *     public function getScheduledTasks(&$tasks)
 *     {
 *         $tasks[] = new Task(
 *             'Piwik\Plugins\CorePluginsAdmin\MarketplaceApiClient',
 *             'clearAllCacheEntries',
 *             null,
 *             Schedule::factory('daily'),
 *             Task::LOWEST_PRIORITY
 *         );
 *     }
 *
 * **Executing all pending tasks**
 *
 *     $results = $scheduler->run();
 *     $task1Result = $results[0];
 *     $task1Name = $task1Result['task'];
 *     $task1Output = $task1Result['output'];
 *
 *     echo "Executed task '$task1Name'. Task output:\n$task1Output";
 */
class Scheduler
{
    /**
     * Is the scheduler running any task.
     * @var bool
     */
    private $isRunning = false;

    /**
     * @var Timetable
     */
    private $timetable;

    /**
     * @var TaskLoader
     */
    private $loader;

    public function __construct(TaskLoader $loader)
    {
        $this->timetable = new Timetable();
        $this->loader = $loader;
    }

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
    public function run()
    {
        $tasks = $this->loader->loadTasks();

        // remove from timetable tasks that are not active anymore
        $this->timetable->removeInactiveTasks($tasks);

        // for every priority level, starting with the highest and concluding with the lowest
        $executionResults = array();
        for ($priority = Task::HIGHEST_PRIORITY;
             $priority <= Task::LOWEST_PRIORITY;
             ++$priority) {
            // loop through each task
            foreach ($tasks as $task) {
                // if the task does not have the current priority level, don't execute it yet
                if ($task->getPriority() != $priority) {
                    continue;
                }

                $taskName = $task->getName();
                $shouldExecuteTask = $this->timetable->shouldExecuteTask($taskName);

                if ($this->timetable->taskShouldBeRescheduled($taskName)) {
                    $this->timetable->rescheduleTask($task);
                }

                if ($shouldExecuteTask) {
                    $this->isRunning = true;
                    $message = $this->executeTask($task);
                    $this->isRunning = false;

                    $executionResults[] = array('task' => $taskName, 'output' => $message);
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
     * @param Task $task Describes the scheduled task being rescheduled.
     * @api
     */
    public function rescheduleTask(Task $task)
    {
        $this->timetable->rescheduleTask($task);
    }

    /**
     * Returns true if the scheduler is currently running a task.
     *
     * @return bool
     */
    public function isRunning()
    {
        return $this->isRunning;
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
    public function getScheduledTimeForMethod($className, $methodName, $methodParameter = null)
    {
        return $this->timetable->getScheduledTimeForMethod($className, $methodName, $methodParameter);
    }

    /**
     * Executes the given task
     *
     * @param Task $task
     * @return string
     */
    private function executeTask($task)
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
