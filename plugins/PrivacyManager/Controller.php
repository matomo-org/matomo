<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_PrivacyManager
 */

/**
 *
 * @package Piwik_PrivacyManager
 */
class Piwik_PrivacyManager_Controller extends Piwik_Controller_Admin
{

	const ANONYMIZE_IP_PLUGIN_NAME = "AnonymizeIP";
	const OPTION_LAST_DELETE_PIWIK_LOGS = "lastDelete_piwik_logs";

	public function saveSettings()
	{
		Piwik::checkUserIsSuperUser();
		if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
			$this->checkTokenInUrl();
			switch (Piwik_Common::getRequestVar('form'))
			{
				case("formMaskLength"):
					$this->handlePluginState(Piwik_Common::getRequestVar("anonymizeIPEnable", 0));
					$trackerConfig = Piwik_Config::getInstance()->Tracker;
					$trackerConfig['ip_address_mask_length'] = Piwik_Common::getRequestVar("maskLength", 1);
					Piwik_Config::getInstance()->Tracker = $trackerConfig;
					Piwik_Config::getInstance()->forceSave();
					break;

				case("formDeleteSettings"):
					$settings = $this->getPurgeSettingsFromRequest();
					Piwik_PrivacyManager::savePurgeDataSettings($settings);
					break;

				default: //do nothing
					break;
			}
		}

		return $this->redirectToIndex('PrivacyManager', 'privacySettings', null, null, null, array('updated' => 1));
	}
	
	/**
	 * Utility function. Gets the delete logs/reports settings from the request and uses
	 * them to populate config arrays.
	 * 
	 * @return array An array containing the data deletion settings.
	 */
	private function getPurgeSettingsFromRequest()
	{
		$settings = array();
		
		// delete logs settings
		$settings['delete_logs_enable'] = Piwik_Common::getRequestVar("deleteEnable", 0);
		$settings['delete_logs_schedule_lowest_interval'] = Piwik_Common::getRequestVar("deleteLowestInterval", 7);
		$settings['delete_logs_older_than'] = ((int)Piwik_Common::getRequestVar("deleteOlderThan", 180) < 1) ?
				1 : Piwik_Common::getRequestVar("deleteOlderThan", 180);
		
		// delete reports settings
		$settings['delete_reports_enable'] = Piwik_Common::getRequestVar("deleteReportsEnable", 0);
		$deleteReportsOlderThan = Piwik_Common::getRequestVar("deleteReportsOlderThan", 3);
		$settings['delete_reports_older_than'] = $deleteReportsOlderThan < 3 ? 3 : $deleteReportsOlderThan;
		$settings['delete_reports_keep_basic_metrics'] = Piwik_Common::getRequestVar("deleteReportsKeepBasic", 0);
		$settings['delete_reports_keep_day_reports'] = Piwik_Common::getRequestVar("deleteReportsKeepDay", 0);
		$settings['delete_reports_keep_week_reports'] = Piwik_Common::getRequestVar("deleteReportsKeepWeek", 0);
		$settings['delete_reports_keep_month_reports'] = Piwik_Common::getRequestVar("deleteReportsKeepMonth", 0);
		$settings['delete_reports_keep_year_reports'] = Piwik_Common::getRequestVar("deleteReportsKeepYear", 0);
		$settings['delete_reports_keep_range_reports'] = Piwik_Common::getRequestVar("deleteReportsKeepRange", 0);
		$settings['delete_reports_keep_segment_reports'] = Piwik_Common::getRequestVar("deleteReportsKeepSegments", 0);
		
		$settings['delete_logs_max_rows_per_query'] = Piwik_PrivacyManager::DEFAULT_MAX_ROWS_PER_QUERY;
		
		return $settings;
	}
	
	/**
	 * Echo's an HTML chunk describing the current database size, and the estimated space
	 * savings after the scheduled data purge is run.
	 */
	public function getDatabaseSize()
	{
		Piwik::checkUserIsSuperUser();
		$view = Piwik_View::factory('databaseSize');
		
		$view->dbStats = $this->getDeleteDBSizeEstimate(true);
		$view->language = Piwik_LanguagesManager::getLanguageCodeForCurrentUser();
		
		echo $view->render();
	}

	public function privacySettings()
	{
		Piwik::checkUserHasSomeAdminAccess();
		$view = Piwik_View::factory('privacySettings');

		if (Piwik::isUserIsSuperUser())
		{
			$deleteLogs = array();

			$view->deleteData = $this->getDeleteDataInfo();
			$view->deleteDbStats = $this->getDeleteDBSizeEstimate();
			$view->anonymizeIP = $this->getAnonymizeIPInfo();
		}
		$view->language = Piwik_LanguagesManager::getLanguageCodeForCurrentUser();

		if (!Piwik_Config::getInstance()->isFileWritable())
		{
			$view->configFileNotWritable = true;
		}

		$this->setBasicVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();

		echo $view->render();
	}
	
	/**
	 * Executes a data purge, deleting log data and report data using the current config
	 * options. Echo's the result of getDatabaseSize after purging.
	 */
	public function executeDataPurge()
	{
		Piwik::checkUserIsSuperUser();
		$this->checkTokenInUrl();
		
		// if the request isn't a POST, redirect to index
		if ($_SERVER["REQUEST_METHOD"] != "POST")
		{
			return $this->redirectToIndex('PrivacyManager', 'privacySettings');
		}
		
		$settings = Piwik_PrivacyManager::getPurgeDataSettings();
		
		// execute the purge
		if ($settings['delete_logs_enable'])
		{
			$logDataPurger = Piwik_PrivacyManager_LogDataPurger::make($settings);
			$logDataPurger->purgeData();
		}
		
		if ($settings['delete_reports_enable'])
		{
			$reportsPurger = Piwik_PrivacyManager_ReportsPurger::make(
				$settings, Piwik_PrivacyManager::getAllMetricsToKeep());
			$reportsPurger->purgeData(true);
		}
		
		// re-calculate db size estimate
		$this->getDatabaseSize();
	}
	
	protected function getDeleteDBSizeEstimate( $getSettingsFromQuery = false )
	{
		// get the purging settings & create two purger instances
		if ($getSettingsFromQuery)
		{
			$settings = $this->getPurgeSettingsFromRequest();
		}
		else
		{
			$settings = Piwik_PrivacyManager::getPurgeDataSettings();
		}
		
		// maps tables whose data will be deleted with number of rows that will be deleted
		// if a value is -1, it means the table will be dropped.
		$deletedDataSummary = Piwik_PrivacyManager::getPurgeEstimate($settings);
		
		// determine the DB size & purged DB size
		$tableStatuses = Piwik_DBStats_API::getInstance()->getAllTablesStatus();
		
		$totalBytes = 0;
		foreach ($tableStatuses as $status)
		{
			$totalBytes += $status['Data_length'] + $status['Index_length'];
		}
		
		$totalAfterPurge = $totalBytes;
		foreach ($tableStatuses as $status)
		{
			$tableName = $status['Name'];
			if (isset($deletedDataSummary[$tableName]))
			{
				$tableTotalBytes = $status['Data_length'] + $status['Index_length'];
				
				// if dropping the table
				if ($deletedDataSummary[$tableName] === Piwik_PrivacyManager_ReportsPurger::DROP_TABLE)
				{
					$totalAfterPurge -= $tableTotalBytes;
				}
				else // if just deleting rows
				{
					$totalAfterPurge -= ($tableTotalBytes / $status['Rows']) * $deletedDataSummary[$tableName];
				}
			}
		}
		
		$result = array(
			'currentSize' => Piwik::getPrettySizeFromBytes($totalBytes),
			'sizeAfterPurge' => Piwik::getPrettySizeFromBytes($totalAfterPurge),
			'spaceSaved' => Piwik::getPrettySizeFromBytes($totalBytes - $totalAfterPurge)
		);
		
		return $result;
	}

	protected function getAnonymizeIPInfo()
	{
		Piwik::checkUserIsSuperUser();
		$anonymizeIP = array();

		Piwik_PluginsManager::getInstance()->loadPlugin(self::ANONYMIZE_IP_PLUGIN_NAME);

		$anonymizeIP["name"] = self::ANONYMIZE_IP_PLUGIN_NAME;
		$anonymizeIP["enabled"] = Piwik_PluginsManager::getInstance()->isPluginActivated(self::ANONYMIZE_IP_PLUGIN_NAME);
		$anonymizeIP["maskLength"] = Piwik_Config::getInstance()->Tracker['ip_address_mask_length'];
		$anonymizeIP["info"] = Piwik_PluginsManager::getInstance()->getLoadedPlugin(self::ANONYMIZE_IP_PLUGIN_NAME)->getInformation();

		return $anonymizeIP;
	}

	protected function getDeleteDataInfo()
	{
		Piwik::checkUserIsSuperUser();
		$deleteDataInfos = array();
		$taskScheduler = new Piwik_TaskScheduler();
		$deleteDataInfos["config"] = Piwik_PrivacyManager::getPurgeDataSettings();
		$deleteDataInfos["deleteTables"] =
			implode(", ", Piwik_PrivacyManager_LogDataPurger::getDeleteTableLogTables());

		$scheduleTimetable = $taskScheduler->getScheduledTimeForTask("Piwik_PrivacyManager", "deleteLogTables");

		$optionTable = Piwik_GetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS);

		//If task was already rescheduled, read time from taskTimetable. Else, calculate next possible runtime.
		if (!empty($scheduleTimetable) && ($scheduleTimetable - time() > 0)) {
			$nextPossibleSchedule = (int)$scheduleTimetable;
		} else {
			$date = Piwik_Date::factory("today");
			$nextPossibleSchedule = $date->addDay(1)->getTimestamp();
		}

		//deletion schedule did not run before
		if (empty($optionTable)) {
			$deleteDataInfos["lastRun"] = false;

			//next run ASAP (with next schedule run)
			$date = Piwik_Date::factory("today");
			$deleteDataInfos["nextScheduleTime"] = $nextPossibleSchedule;
		} else {
			$deleteDataInfos["lastRun"] = $optionTable;
			$deleteDataInfos["lastRunPretty"] = Piwik_Date::factory((int)$optionTable)->getLocalized('%day% %shortMonth% %longYear%');

			//Calculate next run based on last run + interval
			$nextScheduleRun = (int)($deleteDataInfos["lastRun"] + $deleteDataInfos["config"]["delete_logs_schedule_lowest_interval"] * 24 * 60 * 60);

			//is the calculated next run in the past? (e.g. plugin was disabled in the meantime or something) -> run ASAP
			if (($nextScheduleRun - time()) <= 0) {
				$deleteDataInfos["nextScheduleTime"] = $nextPossibleSchedule;
			} else {
				$deleteDataInfos["nextScheduleTime"] = $nextScheduleRun;
			}
		}

		$deleteDataInfos["nextRunPretty"] = Piwik::getPrettyTimeFromSeconds($deleteDataInfos["nextScheduleTime"] - time());

		return $deleteDataInfos;
	}

	protected function handlePluginState($state = 0)
	{
		$pluginController = new Piwik_CorePluginsAdmin_Controller();

		if ($state == 1 && !Piwik_PluginsManager::getInstance()->isPluginActivated(self::ANONYMIZE_IP_PLUGIN_NAME)) {
			$pluginController->activate($redirectAfter = false);
		} elseif ($state == 0 && Piwik_PluginsManager::getInstance()->isPluginActivated(self::ANONYMIZE_IP_PLUGIN_NAME)) {
			$pluginController->deactivate($redirectAfter = false);
		} else {
			//nothing to do
		}
	}
}
