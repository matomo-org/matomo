<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_PrivacyManager
 */

/**
 * @see plugins/PrivacyManager/LogDataPurger.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/PrivacyManager/LogDataPurger.php';

/**
 * @see plugins/PrivacyManager/ReportsPurger.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/PrivacyManager/ReportsPurger.php';

/**
 *
 * @package Piwik_PrivacyManager
 */
class Piwik_PrivacyManager extends Piwik_Plugin
{
    const OPTION_LAST_DELETE_PIWIK_LOGS = "lastDelete_piwik_logs";
    const OPTION_LAST_DELETE_PIWIK_REPORTS = 'lastDelete_piwik_reports';
    const OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL = "lastDelete_piwik_logs_initial";
    const DEFAULT_MAX_ROWS_PER_QUERY = 100000;

    // default config options for data purging feature
    public static $defaultPurgeDataOptions = array(
        'delete_logs_enable'                   => 0,
        'delete_logs_schedule_lowest_interval' => 7,
        'delete_logs_older_than'               => 180,
        'delete_logs_max_rows_per_query'       => self::DEFAULT_MAX_ROWS_PER_QUERY,
        'delete_reports_enable'                => 0,
        'delete_reports_older_than'            => 12,
        'delete_reports_keep_basic_metrics'    => 1,
        'delete_reports_keep_day_reports'      => 0,
        'delete_reports_keep_week_reports'     => 0,
        'delete_reports_keep_month_reports'    => 1,
        'delete_reports_keep_year_reports'     => 1,
        'delete_reports_keep_range_reports'    => 0,
        'delete_reports_keep_segment_reports'  => 0,
    );

    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('PrivacyManager_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles'         => 'getJsFiles',
            'AdminMenu.add'                   => 'addMenu',
            'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getScheduledTasks($notification)
    {
        $tasks = & $notification->getNotificationObject();

        // both tasks are low priority so they will execute after most others, but not lowest, so
        // they will execute before the optimize tables task

        $purgeReportDataTask = new Piwik_ScheduledTask(
            $this, 'deleteReportData', null, new Piwik_ScheduledTime_Daily(), Piwik_ScheduledTask::LOW_PRIORITY
        );
        $tasks[] = $purgeReportDataTask;

        $purgeLogDataTask = new Piwik_ScheduledTask(
            $this, 'deleteLogData', null, new Piwik_ScheduledTime_Daily(), Piwik_ScheduledTask::LOW_PRIORITY
        );
        $tasks[] = $purgeLogDataTask;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();

        $jsFiles[] = "plugins/PrivacyManager/templates/privacySettings.js";
    }

    function addMenu()
    {
        Piwik_AddAdminMenu('PrivacyManager_MenuPrivacySettings',
            array('module' => 'PrivacyManager', 'action' => 'privacySettings'),
            Piwik::isUserHasSomeAdminAccess(),
            $order = 7);
    }

    /**
     * Returns the settings for the data purging feature.
     *
     * @return array
     */
    public static function getPurgeDataSettings()
    {
        $settings = array();

        // load settings from ini config
        try {
            $oldSettings = array(
                'enable_auto_database_size_estimate',

                // backwards compatibility: load old values in ini config if present
                'delete_logs_enable',
                'delete_logs_schedule_lowest_interval',
                'delete_logs_older_than',
            );

            $deleteLogsSettings = Piwik_Config::getInstance()->Deletelogs;
            foreach ($oldSettings as $settingName) {
                $settings[$settingName] = $deleteLogsSettings[$settingName];
            }
        } catch (Exception $e) {
            // ignore
        }

        // load the settings for the data purging settings
        foreach (self::$defaultPurgeDataOptions as $optionName => $defaultValue) {
            $value = Piwik_GetOption($optionName);
            if ($value !== false) {
                $settings[$optionName] = $value;
            } else {
                // if the option hasn't been set/created, use the default value
                if (!isset($settings[$optionName])) {
                    $settings[$optionName] = $defaultValue;
                }

                // option is not saved in the DB, so save it now
                Piwik_SetOption($optionName, $settings[$optionName]);
            }
        }

        return $settings;
    }

    /**
     * Saves the supplied data purging settings.
     *
     * @param array $settings The settings to save.
     */
    public static function savePurgeDataSettings($settings)
    {
        $plugin = Piwik_PluginsManager::getInstance()->getLoadedPlugin('PrivacyManager');

        foreach (self::$defaultPurgeDataOptions as $optionName => $defaultValue) {
            if (isset($settings[$optionName])) {
                Piwik_SetOption($optionName, $settings[$optionName]);
            }
        }
    }

    /**
     * Deletes old archived data (reports & metrics).
     *
     * Archive tables are not optimized after, as that is handled by a separate scheduled task
     * in CoreAdminHome. This is a scheduled task and will only execute every N days. The number
     * of days is determined by the delete_logs_schedule_lowest_interval config option.
     *
     * If delete_reports_enable is set to 1, old archive data is deleted. The following
     * config options can tweak this behavior:
     * - delete_reports_older_than: The number of months after which archive data is considered
     *                              old. The current month is not considered when applying this
     *                              value.
     * - delete_reports_keep_basic_metrics: If set to 1, keeps certain metric data. Right now,
     *                                      all metric data is kept.
     * - delete_reports_keep_day_reports: If set to 1, keeps old daily reports.
     * - delete_reports_keep_week_reports: If set to 1, keeps old weekly reports.
     * - delete_reports_keep_month_reports: If set to 1, keeps old monthly reports.
     * - delete_reports_keep_year_reports: If set to 1, keeps old yearly reports.
     */
    public function deleteReportData()
    {
        $settings = self::getPurgeDataSettings();

        // Make sure, data deletion is enabled
        if ($settings['delete_reports_enable'] == 0) {
            return;
        }

        // make sure purging should run at this time (unless this is a forced purge)
        if (!$this->shouldPurgeData($settings, self::OPTION_LAST_DELETE_PIWIK_REPORTS)) {
            return;
        }

        // set last run time
        Piwik_SetOption(self::OPTION_LAST_DELETE_PIWIK_REPORTS, Piwik_Date::factory('today')->getTimestamp());

        Piwik_PrivacyManager_ReportsPurger::make($settings, self::getAllMetricsToKeep())->purgeData();
    }

    /**
     * Deletes old log data based on the options set in the Deletelogs config
     * section. This is a scheduled task and will only execute every N days. The number
     * of days is determined by the delete_logs_schedule_lowest_interval config option.
     *
     * If delete_logs_enable is set to 1, old data in the log_visit, log_conversion,
     * log_conversion_item and log_link_visit_action tables is deleted. The following
     * options can tweak this behavior:
     * - delete_logs_older_than: The number of days after which log data is considered old.
     *
     * @ToDo: return number of Rows deleted in last run; Display age of "oldest" row to help the user setting
     *        the day offset;
     */
    public function deleteLogData()
    {
        $settings = self::getPurgeDataSettings();

        // Make sure, data deletion is enabled
        if ($settings['delete_logs_enable'] == 0) {
            return;
        }

        // make sure purging should run at this time
        if (!$this->shouldPurgeData($settings, self::OPTION_LAST_DELETE_PIWIK_LOGS)) {
            return;
        }

        /*
         * Tell the DB that log deletion has run BEFORE deletion is executed;
         * If deletion / table optimization exceeds execution time, other tasks maybe prevented of being executed
         * every time, when the schedule is triggered.
         */
        $lastDeleteDate = Piwik_Date::factory("today")->getTimestamp();
        Piwik_SetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS, $lastDeleteDate);

        // execute the purge
        Piwik_PrivacyManager_LogDataPurger::make($settings)->purgeData();
    }

    /**
     * Returns an array describing what data would be purged if both log data & report
     * purging is invoked.
     *
     * The returned array maps table names with the number of rows that will be deleted.
     * If the table name is mapped with -1, the table will be dropped.
     *
     * @param array $settings The config options to use in the estimate. If null, the real
     *                        options are used.
     * @return array
     */
    public static function getPurgeEstimate($settings = null)
    {
        if (is_null($settings)) {
            $settings = self::getPurgeDataSettings();
        }

        $result = array();

        if ($settings['delete_logs_enable']) {
            $logDataPurger = Piwik_PrivacyManager_LogDataPurger::make($settings);
            $result = array_merge($result, $logDataPurger->getPurgeEstimate());
        }

        if ($settings['delete_reports_enable']) {
            $reportsPurger = Piwik_PrivacyManager_ReportsPurger::make($settings, self::getAllMetricsToKeep());
            $result = array_merge($result, $reportsPurger->getPurgeEstimate());
        }

        return $result;
    }

    /**
     * Returns true if a report with the given year & month should be purged or not.
     *
     * If reportsOlderThan is set to null or not supplied, this function will check if
     * a report should be purged, based on existing configuration. In this case, if
     * delete_reports_enable is set to 0, this function will return false.
     *
     * @param int $reportDateYear The year of the report in question.
     * @param int $reportDateMonth The month of the report in question.
     * @param int|Piwik_Date $reportsOlderThan If an int, the number of months a report must be older than
     *                                         in order to be purged. If a date, the date a report must be
     *                                         older than in order to be purged.
     * @return bool
     */
    public static function shouldReportBePurged($reportDateYear, $reportDateMonth, $reportsOlderThan = null)
    {
        // if no 'older than' value/date was supplied, use existing config
        if (is_null($reportsOlderThan)) {
            // if report deletion is not enabled, the report shouldn't be purged
            $settings = self::getPurgeDataSettings();
            if ($settings['delete_reports_enable'] == 0) {
                return false;
            }

            $reportsOlderThan = $settings['delete_reports_older_than'];
        }

        // if a integer was supplied, assume it is the number of months a report must be older than
        if (!($reportsOlderThan instanceof Piwik_Date)) {
            $reportsOlderThan = Piwik_Date::factory('today')->subMonth(1 + $reportsOlderThan);
        }

        return Piwik_PrivacyManager_ReportsPurger::shouldReportBePurged(
            $reportDateYear, $reportDateMonth, $reportsOlderThan);
    }

    /**
     * Returns the general metrics to keep when the 'delete_reports_keep_basic_metrics'
     * config is set to 1.
     */
    private static function getMetricsToKeep()
    {
        return array('nb_uniq_visitors', 'nb_visits', 'nb_actions', 'max_actions',
                     'sum_visit_length', 'bounce_count', 'nb_visits_converted', 'nb_conversions',
                     'revenue', 'quantity', 'price', 'orders');
    }

    /**
     * Returns the goal metrics to keep when the 'delete_reports_keep_basic_metrics'
     * config is set to 1.
     */
    private static function getGoalMetricsToKeep()
    {
        // keep all goal metrics
        return array_values(Piwik_Archive::$mappingFromIdToNameGoal);
    }

    /**
     * Returns the names of metrics that should be kept when purging as they appear in
     * archive tables.
     */
    public static function getAllMetricsToKeep()
    {
        $metricsToKeep = self::getMetricsToKeep();

        // convert goal metric names to correct archive names
        if (Piwik_Common::isGoalPluginEnabled()) {
            $goalMetricsToKeep = self::getGoalMetricsToKeep();

            $maxGoalId = self::getMaxGoalId();

            // for each goal metric, there's a different name for each goal, including the overview,
            // the order report & cart report
            foreach ($goalMetricsToKeep as $metric) {
                for ($i = 1; $i <= $maxGoalId; ++$i) // maxGoalId can be 0
                {
                    $metricsToKeep[] = Piwik_Goals::getRecordName($metric, $i);
                }

                $metricsToKeep[] = Piwik_Goals::getRecordName($metric);
                $metricsToKeep[] = Piwik_Goals::getRecordName($metric, Piwik_Tracker_GoalManager::IDGOAL_ORDER);
                $metricsToKeep[] = Piwik_Goals::getRecordName($metric, Piwik_Tracker_GoalManager::IDGOAL_CART);
            }
        }

        return $metricsToKeep;
    }

    /**
     * Returns true if one of the purge data tasks should run now, false if it shouldn't.
     */
    private function shouldPurgeData($settings, $lastRanOption)
    {
        // Log deletion may not run until it is once rescheduled (initial run). This is the
        // only way to guarantee the calculated next scheduled deletion time.
        $initialDelete = Piwik_GetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL);
        if (empty($initialDelete)) {
            Piwik_SetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL, 1);
            return false;
        }

        // Make sure, log purging is allowed to run now
        $lastDelete = Piwik_GetOption($lastRanOption);
        $deleteIntervalDays = $settings['delete_logs_schedule_lowest_interval'];
        $deleteIntervalSeconds = $this->getDeleteIntervalInSeconds($deleteIntervalDays);

        if ($lastDelete === false ||
            ($lastDelete !== false && ((int)$lastDelete + $deleteIntervalSeconds) <= time())
        ) {
            return true;
        } else // not time to run data purge
        {
            return false;
        }
    }

    function getDeleteIntervalInSeconds($deleteInterval)
    {
        return (int)$deleteInterval * 24 * 60 * 60;
    }

    private static function getMaxGoalId()
    {
        return Piwik_FetchOne("SELECT MAX(idgoal) FROM " . Piwik_Common::prefixTable('goal'));
    }
}

