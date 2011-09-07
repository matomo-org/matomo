<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */

/**
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome_API 
{
	static private $instance = null;
	/**
	 * @return Piwik_CoreAdminHome_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Will run all scheduled tasks due to run at this time.
	 * @return void
	 */
	public function runScheduledTasks()
	{
		Piwik::checkUserIsSuperUser();
		return Piwik_TaskScheduler::runTasks();
	}
	
	public function getKnownSegmentsToArchive()
	{
		Piwik::checkUserIsSuperUser();
		return Piwik::getKnownSegmentsToArchive();
	}
}
