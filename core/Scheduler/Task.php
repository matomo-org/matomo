<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
    const LOWEST_PRIORITY = 12;
    const LOW_PRIORITY = 9;
    const NORMAL_PRIORITY = 6;
    const HIGH_PRIORITY = 3;
    const HIGHEST_PRIORITY = 0;

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
     * @param mixed $objectInstance The object or class that contains the method to execute regularly.
     *                              Usually this will be a {@link Plugin} instance.
     * @param string $methodName The name of the method that will be regularly executed.
     * @param mixed|null $methodParameter An optional parameter to pass to the method when executed.
     *                                    Must be convertible to string.
     * @param Schedule|null $scheduledTime A {@link Schedule} instance that describes when the method
     *                                          should be executed and how long before the next execution.
     * @param int $priority The priority of the task. Tasks with a higher priority will be executed first.
     *                      Tasks with low priority will be executed last.
     * @throws Exception
     */
    public function __construct($objectInstance, $methodName, $methodParameter, $scheduledTime,
                                $priority = self::NORMAL_PRIORITY)
    {
        $this->className = $this->getClassNameFromInstance($objectInstance);

        if ($priority < self::HIGHEST_PRIORITY || $priority > self::LOWEST_PRIORITY) {
            throw new Exception("Invalid priority for ScheduledTask '$this->className.$methodName': $priority");
        }

        $this->objectInstance = $objectInstance;
        $this->methodName = $methodName;
        $this->scheduledTime = $scheduledTime;
        $this->methodParameter = $methodParameter;
        $this->priority = $priority;
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
