<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Scheduler;

use Exception;
use Piwik\Scheduler\Schedule\Schedule;

/**
 * Describes a task that should be executed on a given time.
 *
 * See the {@link TaskScheduler} docs to learn more about scheduled tasks.
 *
 * @api
 */
class Task
{
    public const LOWEST_PRIORITY = 12;
    public const LOW_PRIORITY = 9;
    public const NORMAL_PRIORITY = 6;
    public const HIGH_PRIORITY = 3;
    public const HIGHEST_PRIORITY = 0;

    /**
     * Object instance on which the method will be executed by the task scheduler
     * @var string
     */
    private $objectInstance;

    /**
     * Class name where the specified method is located
     * @var string
     */
    private $className;

    /**
     * Class method to run when task is scheduled
     * @var string
     */
    private $methodName;

    /**
     * Parameter to pass to the executed method
     * @var string
     */
    private $methodParameter;

    /**
     * The scheduled time policy
     * @var Schedule
     */
    private $scheduledTime;

    /**
     * The priority of a task. Affects the order in which this task will be run.
     * @var int
     */
    private $priority;

    /**
     * This time is used as TTL when acquiring a lock for running the task.
     * The same task won't be executed again, while a lock for this task is acquired.
     * Setting the TTL to -1 will disable acquiring a lock and the same task can run in parallel.
     * If a process running a certain task is killed, without the lock being released, the task won't run again
     * before the ttl is expired.
     * It's recommended to set the ttl to the maximum expected run time of the task.
     * Note: If a task runs through correctly, the lock will be released immediately.
     */
    private $ttlInSeconds;

    /**
     * @param mixed $objectInstance The object or class that contains the method to execute regularly.
     *                              Usually this will be a {@link Plugin} instance.
     * @param string $methodName The name of the method that will be regularly executed.
     * @param mixed|null $methodParameter An optional parameter to pass to the method when executed.
     *                                    Must be convertible to string.
     * @param Schedule|null $scheduledTime A {@link Schedule} instance that describes when the method
     *                                          should be executed and how long before the next execution.
     * @param int $priority The priority of the task. Tasks with a higher priority will be executed first.
     *                      Tasks with low priority will be executed last.
     * @param int $ttlInSeconds TTL to use for this task. Defauts to 3600. See {@link self::$ttlInSeconds}
     * @throws Exception
     */
    public function __construct(
        $objectInstance,
        $methodName,
        $methodParameter,
        $scheduledTime,
        $priority = self::NORMAL_PRIORITY,
        ?int $ttlInSeconds = null
    ) {
        $this->className = $this->getClassNameFromInstance($objectInstance);

        if ($priority < self::HIGHEST_PRIORITY || $priority > self::LOWEST_PRIORITY) {
            throw new Exception("Invalid priority for ScheduledTask '$this->className.$methodName': $priority");
        }

        $ttlInSeconds = $ttlInSeconds ?? 3600;

        // only allow TTLs between 1 second and 1 week
        if ($ttlInSeconds !== -1 && ($ttlInSeconds < 1 || $ttlInSeconds > 604800)) {
            throw new Exception("Invalid TTL for ScheduledTask '$this->className.$methodName': $ttlInSeconds. The TTL must be -1 or between 1 and 604800 (1 second to 1 week).");
        }

        $this->objectInstance = $objectInstance;
        $this->methodName = $methodName;
        $this->scheduledTime = $scheduledTime;
        $this->methodParameter = $methodParameter;
        $this->priority = $priority;
        $this->ttlInSeconds = $ttlInSeconds;
    }

    protected function getClassNameFromInstance($_objectInstance)
    {
        if (is_string($_objectInstance)) {
            return $_objectInstance;
        }

        $namespaced = get_class($_objectInstance);

        return $namespaced;
    }

    /**
     * Returns the object instance that contains the method to execute. Returns a class
     * name if the method is static.
     *
     * @return mixed
     */
    public function getObjectInstance()
    {
        return $this->objectInstance;
    }

    /**
     * Returns the name of the class that contains the method to execute.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns the name of the method that will be executed.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the value that will be passed to the method when executed, or `null` if
     * no value will be supplied.
     *
     * @return string|null
     */
    public function getMethodParameter()
    {
        return $this->methodParameter;
    }

    /**
     * Returns a {@link Schedule} instance that describes when the method should be executed
     * and how long before the next execution.
     *
     * @return \Piwik\Scheduler\Schedule\Schedule
     */
    public function getScheduledTime()
    {
        return $this->scheduledTime;
    }

    /**
     * Returns the time in milliseconds when this task will be executed next.
     *
     * @return int
     */
    public function getRescheduledTime()
    {
        return $this->getScheduledTime()->getRescheduledTime();
    }

    /**
     * Returns the task priority. The priority will be an integer whose value is
     * between {@link HIGH_PRIORITY} and {@link LOW_PRIORITY}.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns the TTL for this task.
     * See {@link self::$ttlInSeconds}
     *
     * @return int
     */
    public function getTTL(): int
    {
        return $this->ttlInSeconds;
    }

    /**
     * Returns a unique name for this scheduled task. The name is stored in the DB and is used
     * to store a task's previous execution time. The name is created using:
     *
     * - the name of the class that contains the method to execute,
     * - the name of the method to regularly execute,
     * - and the value that is passed to the executed task.
     *
     * @return string
     */
    public function getName()
    {
        return self::getTaskName($this->getClassName(), $this->getMethodName(), $this->getMethodParameter());
    }

    /**
     * @ignore
     */
    public static function getTaskName($className, $methodName, $methodParameter = null)
    {
        return $className . '.' . $methodName . ($methodParameter == null ? '' : '_' . $methodParameter);
    }
}
