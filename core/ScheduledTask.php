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

namespace Piwik;

use Exception;
use Piwik\ScheduledTime;

/**
 * Contains metadata describing a chunk of PHP code that should be executed at regular
 * intervals.
 * 
 * See the [TaskScheduler](#) docs to learn more about scheduled tasks.
 * 
 * @package Piwik
 * @subpackage ScheduledTask
 *
 * @api
 */
class ScheduledTask
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
     * @var ScheduledTime
     */
    private $scheduledTime;

    /**
     * The priority of a task. Affects the order in which this task will be run.
     * @var int
     */
    private $priority;

    /**
     * Constructor.
     * 
     * @param mixed $objectInstance The object or class name for the class that contains the method to
     *                              regularly execute. Usually this will be a [Plugin](#) instance.
     * @param string $methodName The name of the method of `$objectInstance` that will be regularly
     *                           executed.
     * @param mixed|null $methodParameter An optional parameter to pass to the method when executed.
     *                                    Must be convertible to string.
     * @param ScheduledTime|null $scheduledTime A [ScheduledTime](#) instance that describes when the method
     *                                          should be executed and how long before the next execution.
     * @param int $priority The priority of the task. Tasks with a higher priority will be executed first.
     *                      Tasks with low priority will be executed last.
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
        $class = explode('\\', $namespaced);
        return end($class);
    }

    /**
     * Returns the object instance on which the method should be executed. Returns a class
     * name if the method is static.
     * 
     * @return mixed
     */
    public function getObjectInstance()
    {
        return $this->objectInstance;
    }

    /**
     * Returns the class name that contains the method to execute regularly.
     * 
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns the method name that will be regularly executed.
     * 
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Returns the a value that will be passed to the method when executed, or `null` if
     * no value will be supplied.
     * 
     * @return string|null
     */
    public function getMethodParameter()
    {
        return $this->methodParameter;
    }

    /**
     * Returns a [ScheduledTime](#) instance that describes when the method should be executed
     * and how long before the next execution.
     *
     * @return ScheduledTime
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
     * between [ScheduledTask::HIGH_PRIORITY](#) and [ScheduledTask::LOW_PRIORITY](#).
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Returns a unique name for this scheduled task. The name is stored in the DB and is used
     * to store when tasks were last executed. The name is created using:
     * 
     * - the class name that contains the method to execute,
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