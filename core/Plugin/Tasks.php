<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Development;
use Piwik\ScheduledTask;
use Piwik\ScheduledTime;

/**
 * Base class for all Tasks declarations.
 * Tasks are usually meant as scheduled tasks that are executed regularily by Piwik in the background. For instance
 * once every hour or every day. This could be for instance checking for updates, sending email reports, etc.
 * Please don't mix up tasks with console commands which can be executed on the CLI.
 */
class Tasks
{
    /**
     * @var ScheduledTask[]
     */
    private $tasks = array();

    const LOWEST_PRIORITY  = ScheduledTask::LOWEST_PRIORITY;
    const LOW_PRIORITY     = ScheduledTask::LOW_PRIORITY;
    const NORMAL_PRIORITY  = ScheduledTask::NORMAL_PRIORITY;
    const HIGH_PRIORITY    = ScheduledTask::HIGH_PRIORITY;
    const HIGHEST_PRIORITY = ScheduledTask::HIGHEST_PRIORITY;

    /**
     * This method is called to collect all schedule tasks. Register all your tasks here that should be executed
     * regularily such as daily or monthly.
     */
    public function schedule()
    {
        // eg $this->daily('myMethodName')
    }

    /**
     * @return ScheduledTask[] $tasks
     */
    public function getScheduledTasks()
    {
        return $this->tasks;
    }

    /**
     * Schedule the given tasks/method to run once every hour.
     *
     * @param string $methodName             The name of the method that will be called when the task is being
     *                                       exectuted. To make it work you need to create a public method having the
     *                                       given method name in your Tasks class.
     * @param null|string $methodParameter   Can be null if the task does not need any parameter or a string. It is not
     *                                       possible to specify multiple parameters as an array etc. If you need to
     *                                       pass multiple parameters separate them via any characters such as '###'.
     *                                       For instance '$param1###$param2###$param3'
     * @param int $priority                  Can be any constant such as self::LOW_PRIORITY
     *
     * @return ScheduledTime
     * @api
     */
    protected function hourly($methodName, $methodParameter = null, $priority = self::NORMAL_PRIORITY)
    {
        return $this->custom($this, $methodName, $methodParameter, 'hourly', $priority);
    }

    /**
     * Schedule the given tasks/method to run once every day.
     *
     * See {@link hourly()}
     * @api
     */
    protected function daily($methodName, $methodParameter = null, $priority = self::NORMAL_PRIORITY)
    {
        return $this->custom($this, $methodName, $methodParameter, 'daily', $priority);
    }

    /**
     * Schedule the given tasks/method to run once every week.
     *
     * See {@link hourly()}
     * @api
     */
    protected function weekly($methodName, $methodParameter = null, $priority = self::NORMAL_PRIORITY)
    {
        return $this->custom($this, $methodName, $methodParameter, 'weekly', $priority);
    }

    /**
     * Schedule the given tasks/method to run once every month.
     *
     * See {@link hourly()}
     * @api
     */
    protected function monthly($methodName, $methodParameter = null, $priority = self::NORMAL_PRIORITY)
    {
        return $this->custom($this, $methodName, $methodParameter, 'monthly', $priority);
    }

    /**
     * Schedules the given tasks/method to run depending at the given scheduled time. Unlike the convenient methods
     * such as {@link hourly()} you need to specify the object on which the given method should be called. This can be
     * either an instance of a class or a class name. For more information about these parameters see {@link hourly()}
     *
     * @param string|object $objectOrClassName
     * @param string $methodName
     * @param null|string $methodParameter
     * @param string|ScheduledTime $time
     * @param int $priority
     *
     * @return ScheduledTime
     *
     * @throws \Exception If a wrong time format is given. Needs to be either a string such as 'daily', 'weekly', ...
     *                    or an instance of {@link Piwik\ScheduledTime}
     *
     * @api
     */
    protected function custom($objectOrClassName, $methodName, $methodParameter, $time, $priority = self::NORMAL_PRIORITY)
    {
        $this->checkIsValidTask($objectOrClassName, $methodName);

        if (is_string($time)) {
            $time = ScheduledTime::factory($time);
        }

        if (!($time instanceof ScheduledTime)) {
            throw new \Exception('$time should be an instance of ScheduledTime');
        }

        $this->scheduleTask(new ScheduledTask($objectOrClassName, $methodName, $methodParameter, $time, $priority));

        return $time;
    }

    /**
     * In case you need very high flexibility and none of the other convenient methods such as {@link hourly()} or
     * {@link custom()} suit you, you can use this method to add a custom scheduled task.
     *
     * @param ScheduledTask $task
     */
    protected function scheduleTask(ScheduledTask $task)
    {
        $this->tasks[] = $task;
    }

    private function checkIsValidTask($objectOrClassName, $methodName)
    {
        Development::checkMethodIsCallable($objectOrClassName, $methodName, 'The registered task is not valid as the method');
    }
}