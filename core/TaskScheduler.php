<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: TaskScheduler.php
 *
 * @category Piwik
 * @package Piwik
 */

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
	
	/*
	 * runTasks collects tasks defined within piwik plugins, runs them if they are scheduled and reschedules
	 * the tasks that have been executed.
	 */
	static public function runTasks()
	{
		// Gets the array where rescheduled timetables are stored
		$timetable = unserialize(Piwik_GetOption(self::TIMETABLE_OPTION_STRING));
		
		// Collects tasks
		Piwik_PostEvent(self::GET_TASKS_EVENT, $tasks);

		// Loop through each task
		foreach ($tasks as $task)
		{
			$scheduledTime = $task->getScheduledTime();
			$className = $task->getClassName();
			$methodName = $task->getMethodName();

			$fullyQualifiedMethodName = $className . '.' . $methodName;
			
			$rescheduledTime = $timetable[$fullyQualifiedMethodName];
			
			/*
			 * Task has to be executed if :
			 * 	- it is the first time, ie. rescheduledTime is not set
			 *  - that task has already been executed and the current system time is greater than the
			 *    rescheduled time.
			 */
			if ( !isset($rescheduledTime) 	
				|| (isset($rescheduledTime) 
					&& time() >= $rescheduledTime) )
			{
				// Updates the rescheduled time
				$timetable[$fullyQualifiedMethodName] = $scheduledTime->getRescheduledTime();
				Piwik_SetOption(self::TIMETABLE_OPTION_STRING, serialize($timetable));

				// Run the task
				call_user_func ( array($className,$methodName) );
			}
		}
	}
}