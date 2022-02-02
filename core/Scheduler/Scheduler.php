<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Scheduler;

use Piwik\Piwik;
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
     * Should the last run task be scheduled for a retry
     * @var bool
     */
    private $scheduleRetry = false;

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
        $readFromOption = true;
        for ($priority = Task::HIGHEST_PRIORITY; $priority <= Task::LOWEST_PRIORITY; ++$priority) {
            $this->logger->debug("Executing tasks with priority {priority}:", array('priority' => $priority));

            // loop through each task
            foreach ($tasks as $task) {
                // if the task does not have the current priority level, don't execute it yet
                if ($task->getPriority() != $priority) {
                    continue;
                }
                
                if ($readFromOption) {
                    // because other jobs might execute the scheduled tasks as well we have to read the up to date time table to not handle the same task twice
                    // ideally we would read from option every time but using $readFromOption as a minor performance tweak. There can be easily 100 tasks 
                    // of which we only execute very few and it's unlikely that the timetable changes too much in between while iterating over the loop and triggering the event.
                    // this way we only read from option when we actually execute or reschedule a task as this can take a few seconds.
                    $this->timetable->readFromOption();
                    $readFromOption = false;
                }

                $taskName = $task->getName();
                $shouldExecuteTask = $this->timetable->shouldExecuteTask($taskName);

                if ($this->timetable->taskShouldBeRescheduled($taskName)) {
                    $readFromOption = true;
                    $rescheduledDate = $this->timetable->rescheduleTask($task);

                    $this->logger->debug("Task {task} is scheduled to run again for {date}.", array('task' => $taskName, 'date' => $rescheduledDate));
                }

                /**
                 * Triggered before a task is executed.
                 *
                 * A plugin can listen to it and modify whether a specific task should be executed or not. This way
                 * you can force certain tasks to be executed more often or for example to be never executed.
                 *
                 * @param bool &$shouldExecuteTask Decides whether the task will be executed.
                 * @param Task $task The task that is about to be executed.
                 */
                Piwik::postEvent('ScheduledTasks.shouldExecuteTask', array(&$shouldExecuteTask, $task));

                if ($shouldExecuteTask) {
                    $readFromOption = true;
                    $this->scheduleRetry = false;
                    $message = $this->executeTask($task);

                    // Task has thrown an exception and should be scheduled for a retry
                    if ($this->scheduleRetry) {

                        if($this->timetable->getRetryCount($task->getName()) == 3) {

                            // Task has already been retried three times, give up
                            $this->timetable->clearRetryCount($task->getName());

                            $this->logger->warning("Scheduler: '{task}' has already been retried three times, giving up",
                                ['task' => $task->getName()]);

                        } else {

                            $readFromOption = true;
                            $rescheduledDate = $this->timetable->rescheduleTaskAndRunInOneHour($task);
                            $this->timetable->incrementRetryCount($task->getName());

                            $this->logger->info("Scheduler: '{task}' retry scheduled for {date}",
                                ['task' => $task->getName(), 'date' => $rescheduledDate]);
                        }
                        $this->scheduleRetry = false;
                    } else {
                        if ($this->timetable->getRetryCount($task->getName()) > 0) {
                            $this->timetable->clearRetryCount($task->getName());
                        }
                    }

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
     * Determines a task's scheduled time and persists it, overwriting the previous scheduled time.
     *
     * Call this method if your task's scheduled time has changed due to, for example, an option that
     * was changed.
     *
     * The task will be run the first time tomorrow.
     *
     * @param Task $task Describes the scheduled task being rescheduled.
     * @api
     */
    public function rescheduleTaskAndRunTomorrow(Task $task)
    {
        $this->logger->debug('Rescheduling task and setting first run for tomorrow {task}', array('task' => $task->getName()));

        $this->timetable->rescheduleTaskAndRunTomorrow($task);
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
     * @return mixed int|bool The time in milliseconds when the scheduled task will be executed
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

        /**
         * Triggered directly before a scheduled task is executed
         *
         * @param Task $task  The task that is about to be executed
         */
        Piwik::postEvent('ScheduledTasks.execute', array(&$task));

        try {
            $callable = array($task->getObjectInstance(), $task->getMethodName());
            call_user_func($callable, $task->getMethodParameter());
            $message = $timer->__toString();
        } catch (\Exception $e) {
            $this->logger->error("Scheduler: Error {errorMessage} for task '{task}'",
                ['errorMessage' => $e->getMessage(), 'task' => $task->getName()]);
            $message = 'ERROR: ' . $e->getMessage();

            // If the task has indicated that retrying on exception is safe then flag for rescheduling
            if ($e instanceof RetryableException) {
                $this->scheduleRetry = true;
            }
        }

        $this->isRunningTask = false;

        /**
         * Triggered after a scheduled task is successfully executed.
         *
         * You can use the event to execute for example another task whenever a specific task is executed or to clean up
         * certain resources.
         *
         * @param Task $task The task that was just executed
         */
        Piwik::postEvent('ScheduledTasks.execute.end', array(&$task));

        $this->logger->info("Scheduler: finished. {timeElapsed}", array(
            'timeElapsed' => $timer,
        ));

        return $message;
    }
}
