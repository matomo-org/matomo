<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
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
	const LOWEST_PRIORITY 	= 12;
	const LOW_PRIORITY 		= 9;
	const NORMAL_PRIORITY 	= 6;
	const HIGH_PRIORITY 	= 3;
	const HIGHEST_PRIORITY 	= 0;
	
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
	 * The scheduled time policy
	 * @var Piwik_ScheduledTime
	 */
	var $scheduledTime;
	
	/**
	 * The priority of a task. Affects the order in which this task will be run.
	 * @var int
	 */
	var $priority;

	function __construct( $_className, $_methodName, $_scheduledTime, $_priority = self::NORMAL_PRIORITY )
	{
		if ($_priority < self::HIGHEST_PRIORITY || $_priority > self::LOWEST_PRIORITY)
		{
			throw new Exception("Invalid priority for ScheduledTask '$_className.$_methodName': $_priority");
		}
		
		$this->className = $_className;
		$this->methodName = $_methodName;
		$this->scheduledTime = $_scheduledTime;
		$this->priority = $_priority;
	}
	
	/**
	 * Returns class name
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * Returns method name
	 * @return string
	 */
	public function getMethodName()
	{
		return $this->methodName;
	}

	/**
	 * Returns scheduled time
	 * @return Piwik_ScheduledTime
	 */
	public function getScheduledTime()
	{
		return $this->scheduledTime;
	}
	
	/**
	 * Returns the task priority. The priority will be an integer whose value is
	 * between Piwik_ScheduledTask::HIGH_PRIORITY and Piwik_ScheduledTask::LOW_PRIORITY.
	 * 
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}
}
