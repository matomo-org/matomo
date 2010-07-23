<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */

/**
 *
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome_API 
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	/**
	 * Will run all scheduled tasks due to run at this time.
	 * @deprecated Not deprecated, but this flag ensures the API is not listed in the page, 
	 * 				as it shouldn't be used appart from the crontab directly. 
	 * @return void
	 */
	public function runScheduledTime ( )
	{
		Piwik::checkUserIsSuperUser();
		Piwik_TaskScheduler::runTasks();
		return true;
	}
}
