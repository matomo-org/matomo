<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: ScheduledTask.php
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
    /**
     * Class name where the specified method is located
     * @var string 
     */
	var $className;
#
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

	function __construct( $_className, $_methodName, $_scheduledTime)
	{
		$this->className = $_className;
		$this->methodName = $_methodName;
		$this->scheduledTime = $_scheduledTime;
	}
	
	/*
	 * Returns class name
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/*
	 * Returns method name
	 * @return string
	 */
	public function getMethodName()
	{
		return $this->methodName;
	}

	/*
	 * Returns scheduled time
	 * @return Piwik_ScheduledTime
	 */
	public function getScheduledTime()
	{
		return $this->scheduledTime;
	}
}
