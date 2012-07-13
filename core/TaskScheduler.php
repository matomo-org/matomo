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

// When set to true, all scheduled tasks will be triggered in all requests (careful!)
define('DEBUG_FORCE_SCHEDULED_TASKS', false);

/**
 * Piwik_TaskScheduler is the class used to manage the execution of periodicaly planned task.
 *
 * It performs the following actions :
 * 	- Identifies tasks of Piwik
 *  - Runs tasks
 *
 * @package Piwik
 */

class Piwik_TaskScheduler
{
	const GET_TASKS_EVENT = "TaskScheduler.getScheduledTasks";
	const TIMETABLE_OPTION_STRING = "TaskScheduler.timetable";
	static private $running = false;
	
	/**
	 * runTasks collects tasks defined within piwik plugins, runs them if they are scheduled and reschedules
	 * the tasks that have been executed.
	 *
	 * @return array
	 */
	static public function runTasks()
	{
		// Gets the array where rescheduled timetables are stored
		$option = Piwik_GetOption(self::TIMETABLE_OPTION_STRING);

        $timetable = self::getTimetableFromOption($option);
        if($timetable === false) {
            return;
        }
        $forceScheduledTasks = false;
		if((isset($GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS']) && $GLOBALS['PIWIK_TRACKER_DEBUG_FORCE_SCHEDULED_TASKS'])
			|| DEBUG_FORCE_SCHEDULED_TASKS)
		{
			$forceScheduledTasks = true;
			$timetable = array();
		}
		// Collects tasks
		Piwik_PostEvent(self::GET_TASKS_EVENT, $tasks);

		$return = array();
		
		// for every priority level, starting with the highest and concluding with the lowest
		for ($priority = Piwik_ScheduledTask::HIGHEST_PRIORITY;
			 $priority <= Piwik_ScheduledTask::LOWEST_PRIORITY;
			 ++$priority)
		{
			// Loop through each task
			foreach ($tasks as $task)
			{
				// if the task does not have the current priority level, don't execute it yet
				if ($task->getPriority() != $priority)
				{
					continue;
				}
		
				$scheduledTime = $task->getScheduledTime();
				$className = $task->getClassName();
				$methodName = $task->getMethodName();

				$fullyQualifiedMethodName = get_class($className) . '.' . $methodName;
				
				/*
				 * Task has to be executed if :
				 * 	- it is the first time, ie. rescheduledTime is not set
				 *  - that task has already been executed and the current system time is greater than the
				 *    rescheduled time.
				 */
				if ( !isset($timetable[$fullyQualifiedMethodName])
					|| (isset($timetable[$fullyQualifiedMethodName])
					&& time() >= $timetable[$fullyQualifiedMethodName]) 
					|| $forceScheduledTasks)
				{
					// Updates the rescheduled time
					$timetable[$fullyQualifiedMethodName] = $scheduledTime->getRescheduledTime();
					Piwik_SetOption(self::TIMETABLE_OPTION_STRING, serialize($timetable));

					self::$running = true;
					// Run the task
					try {
						$timer = new Piwik_Timer;
						call_user_func ( array($className,$methodName) );
						$message = $timer->__toString();
					} catch(Exception $e) {
						$message = 'ERROR: '.$e->getMessage();
					}
					self::$running = false;
					$return[] = array('task' => $fullyQualifiedMethodName, 'output' => $message);
				}
			}
		}
		return $return;

	}

	static public function isTaskBeingExecuted()
	{
		return self::$running;
	}
	
    /**
     * return the timetable for a given task
     * @param string $className
     * @param string $methodName
     * @return mixed
     */
    static public function getScheduledTimeForTask($className, $methodName) {
		// Gets the array where rescheduled timetables are stored
		$option = Piwik_GetOption(self::TIMETABLE_OPTION_STRING);

		$timetable = self::getTimetableFromOption($option);
        if($timetable === false) {
            return;
        }

		$taskName = $className . '.' . $methodName;

		if(isset($timetable[$taskName])) {
			return $timetable[$taskName];
		} else {
			return false;
		}
	}

    static private function getTimetableFromOption($option = false) {
        if($option === false)
		{
			return array();
		}
		elseif(!is_string($option))
		{
			return false;
		}
		else
		{
			return unserialize($option);
		}
    }
}
