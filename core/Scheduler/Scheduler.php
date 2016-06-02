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
use Psr\Log\LoggerInterface;

/**
 * Schedules task execution.
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
    private $isRunningTask = false;

    /**
     * @var Timetable
     */
    private $timetable;

    /**
     * @var TaskLoader
     */
    private $loader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(TaskLoader $loader, LoggerInterface $logger)
    {
        $this->timetable = new Timetable();
        $this->loader = $loader;
        $this->logger = $logger;
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

        $this->logger->debug('{count} scheduled tasks loaded', array('count' => count($tasks)));

        // remove from timetable tasks that are not active anymore
        $this->timetable->removeInactiveTasks($tasks);

        $this->logger->info("Starting Scheduled tasks... ");

        // for every priority level, starting with the highest and concluding with the lowest
        $executionResults = array();
        for ($priority = Task::HIGHEST_PRIORITY; $priority <= Task::LOWEST_PRIORITY; ++$priority) {
            $this->logger->debug("Executing tasks with priority {priority}:", array('priority' => $priority));

            // loop through each task
            foreach ($tasks as $task) {
                // if the task does not have the current priority level, don't execute it yet
                if ($task->getPriority() != $priority) {
                    continue;
                }

                $taskName = $task->getName();
                $shouldExecuteTask = $this->timetable->shouldExecuteTask($taskName);

                if ($this->timetable->taskShouldBeRescheduled($taskName)) {
                    $rescheduledDate = $this->timetable->rescheduleTask($task);

                    $this->logger->debug("Task {task} is scheduled to run again for {date}.", array('task' => $taskName, 'date' => $rescheduledDate));
                }

                if ($shouldExecuteTask) {
                    $message = $this->executeTask($task);

                    $executionResults[] = array('task' => $taskName, 'output' => $message);
                }
            }
        }

        $this->logger->info("done");

        return $executionResults;
    }

    /**
     * Run a specific task now. Will ignore the schedule completely.
     *
     * @param string $taskName
     * @return string Task output.
     */
    public function runTaskNow($taskName)
    {
        $tasks = $this->loader->loadTasks();

        foreach ($tasks as $task) {
            if ($task->getName() === $taskName) {
                return $this->executeTask($task);
            }
        }

        throw new \InvalidArgumentException('Task ' . $taskName . ' not found');
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
        $this->logger->debug('Rescheduling task {task}', array('task' => $task->getName()));

        $this->timetable->rescheduleTask($task);
    }

    /**
     * Returns true if the scheduler is currently running a task.
     *
     * @return bool
     */
    public function isRunningTask()
    {
        return $this->isRunningTask;
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
     * Returns the list of the task names.
     *
     * @return string[]
     */
    public function getTaskList()
    {
        $tasks = $this->loader->loadTasks();

        return array_map(function (Task $task) {
            return $task->getName();
        }, $tasks);
    }

    /**
     * Executes the given task
     *
     * @param Task $task
     * @return string
     */
    private function executeTask($task)
    {
        $this->logger->info("Scheduler: executing task {taskName}...", array(
            'taskName' => $task->getName(),
        ));

        $this->isRunningTask = true;

        $timer = new Timer();

        try {
            $callable = array($task->getObjectInstance(), $task->getMethodName());
            call_user_func($callable, $task->getMethodParameter());
            $message = $timer->__toString();
        } catch (Exception $e) {
            $message = 'ERROR: ' . $e->getMessage();
        }

        $this->isRunningTask = false;

        $this->logger->info("Scheduler: finished. {timeElapsed}", array(
            'timeElapsed' => $timer,
        ));

        return $message;
    }
}
