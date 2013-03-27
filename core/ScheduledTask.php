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

/**
 * Piwik_ScheduledTask is used by the task scheduler and by plugins to configure runnable tasks.
 *
 * @package Piwik
 * @subpackage Piwik_ScheduledTask
 */
class Piwik_ScheduledTask
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
    var $objectInstance;

    /**
     * Class name where the specified method is located
     * @var string
     */
    var $className;

    /**
     * Class method to run when task is scheduled
     * @var string
     */
    var $methodName;

    /**
     * Parameter to pass to the executed method
     * @var string
     */
    var $methodParameter;

    /**
     * The scheduled time policy
     * @var Piwik_ScheduledTime
     */
    var $scheduledTime;

    /**
     * The priority of a task. Affects the order in which this task will be run.
     * @var int
     */
    var $priority;

    function __construct($_objectInstance, $_methodName, $_methodParameter, $_scheduledTime, $_priority = self::NORMAL_PRIORITY)
    {
        $this->className = get_class($_objectInstance);

        if ($_priority < self::HIGHEST_PRIORITY || $_priority > self::LOWEST_PRIORITY) {
            throw new Exception("Invalid priority for ScheduledTask '$this->className.$_methodName': $_priority");
        }

        $this->objectInstance = $_objectInstance;
        $this->methodName = $_methodName;
        $this->scheduledTime = $_scheduledTime;
        $this->methodParameter = $_methodParameter;
        $this->priority = $_priority;
    }

    /**
     * Return the object instance on which the method should be executed
     * @return string
     */
    public function getObjectInstance()
    {
        return $this->objectInstance;
    }

    /**
     * Return class name
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Return method name
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Return method parameter
     * @return string
     */
    public function getMethodParameter()
    {
        return $this->methodParameter;
    }


    /**
     * Return scheduled time
     * @return Piwik_ScheduledTime
     */
    public function getScheduledTime()
    {
        return $this->scheduledTime;
    }

    /**
     * Return the rescheduled time in milliseconds
     * @return int
     */
    public function getRescheduledTime()
    {
        return $this->getScheduledTime()->getRescheduledTime();
    }

    /**
     * Return the task priority. The priority will be an integer whose value is
     * between Piwik_ScheduledTask::HIGH_PRIORITY and Piwik_ScheduledTask::LOW_PRIORITY.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    public function getName()
    {
        return self::getTaskName($this->getClassName(), $this->getMethodName(), $this->getMethodParameter());
    }

    static public function getTaskName($className, $methodName, $methodParameter = null)
    {
        return $className . '.' . $methodName . ($methodParameter == null ? '' : '_' . $methodParameter);
    }
}
