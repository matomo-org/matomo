<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
		$option = Piwik_GetOption(self::TIMETABLE_OPTION_STRING);
		if($option === false)
		{
			$timetable = array();
		}
		elseif(!is_string($option))
		{
			return;
		}
		else
		{
			$timetable = unserialize($option);
		}

		// DEBUG Force trigger all Scheduled tasks, uncomment
//		$timetable = array();
		// Collects tasks
		Piwik_PostEvent(self::GET_TASKS_EVENT, $tasks);

		$return = array();
		// Loop through each task
		foreach ($tasks as $task)
		{
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
    			&& time() >= $timetable[$fullyQualifiedMethodName]) )
			{
				// Updates the rescheduled time
				$timetable[$fullyQualifiedMethodName] = $scheduledTime->getRescheduledTime();
				Piwik_SetOption(self::TIMETABLE_OPTION_STRING, serialize($timetable));

				// Run the task
				try {
					$timer = new Piwik_Timer;
					call_user_func ( array($className,$methodName) );
					$message = $timer->__toString();
				} catch(Exception $e) {
					$message = 'ERROR: '.$e->getMessage();
				}
				$return[] = array('task' => $fullyQualifiedMethodName, 'output' => $message);

			}
		}
		return $return;

	}
}
