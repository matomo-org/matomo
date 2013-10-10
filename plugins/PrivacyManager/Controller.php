<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package PrivacyManager
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\MetricsFormatter;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\DBStats\MySQLMetadataProvider;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\TaskScheduler;
use Piwik\View;

/**
 *
 * @package PrivacyManager
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{

    const ANONYMIZE_IP_PLUGIN_NAME = "AnonymizeIP";
    const OPTION_LAST_DELETE_PIWIK_LOGS = "lastDelete_piwik_logs";

    public function saveSettings()
    {
        Piwik::checkUserIsSuperUser();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();
            switch (Common::getRequestVar('form')) {
                case("formMaskLength"):
                    $this->handlePluginState(Common::getRequestVar("anonymizeIPEnable", 0));
                    $trackerConfig = Config::getInstance()->Tracker;
                    $trackerConfig['ip_address_mask_length'] = Common::getRequestVar("maskLength", 1);
                    Config::getInstance()->Tracker = $trackerConfig;
                    Config::getInstance()->forceSave();
                    break;

                case("formDeleteSettings"):
                    $settings = $this->getPurgeSettingsFromRequest();
                    PrivacyManager::savePurgeDataSettings($settings);
                    break;

                default: //do nothing
                    break;
            }
        }

        $this->redirectToIndex('PrivacyManager', 'privacySettings', null, null, null, array('updated' => 1));
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
        $settings['delete_logs_enable'] = Common::getRequestVar("deleteEnable", 0);
        $settings['delete_logs_schedule_lowest_interval'] = Common::getRequestVar("deleteLowestInterval", 7);
        $settings['delete_logs_older_than'] = ((int)Common::getRequestVar("deleteOlderThan", 180) < 1) ?
            1 : Common::getRequestVar("deleteOlderThan", 180);

        // delete reports settings
        $settings['delete_reports_enable'] = Common::getRequestVar("deleteReportsEnable", 0);
        $deleteReportsOlderThan = Common::getRequestVar("deleteReportsOlderThan", 3);
        $settings['delete_reports_older_than'] = $deleteReportsOlderThan < 3 ? 3 : $deleteReportsOlderThan;
        $settings['delete_reports_keep_basic_metrics'] = Common::getRequestVar("deleteReportsKeepBasic", 0);
        $settings['delete_reports_keep_day_reports'] = Common::getRequestVar("deleteReportsKeepDay", 0);
        $settings['delete_reports_keep_week_reports'] = Common::getRequestVar("deleteReportsKeepWeek", 0);
        $settings['delete_reports_keep_month_reports'] = Common::getRequestVar("deleteReportsKeepMonth", 0);
        $settings['delete_reports_keep_year_reports'] = Common::getRequestVar("deleteReportsKeepYear", 0);
        $settings['delete_reports_keep_range_reports'] = Common::getRequestVar("deleteReportsKeepRange", 0);
        $settings['delete_reports_keep_segment_reports'] = Common::getRequestVar("deleteReportsKeepSegments", 0);

        $settings['delete_logs_max_rows_per_query'] = PrivacyManager::DEFAULT_MAX_ROWS_PER_QUERY;

        return $settings;
    }

    /**
     * Echo's an HTML chunk describing the current database size, and the estimated space
     * savings after the scheduled data purge is run.
     */
    public function getDatabaseSize()
    {
        Piwik::checkUserIsSuperUser();
        $view = new View('@PrivacyManager/getDatabaseSize');

        $forceEstimate = Common::getRequestVar('forceEstimate', 0);
        $view->dbStats = $this->getDeleteDBSizeEstimate($getSettingsFromQuery = true, $forceEstimate);
        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();

        echo $view->render();
    }

    /**
     * Returns true if server side DoNotTrack support is enabled, false if otherwise.
     *
     * @return bool
     */
    public static function isDntSupported()
    {
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated('DoNotTrack');
    }

    public function privacySettings()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $view = new View('@PrivacyManager/privacySettings');

        if (Piwik::isUserIsSuperUser()) {
            $view->deleteData = $this->getDeleteDataInfo();
            $view->anonymizeIP = $this->getAnonymizeIPInfo();
            $view->dntSupport = self::isDntSupported();
            $view->canDeleteLogActions = Db::isLockPrivilegeGranted();
            $view->dbUser = Config::getInstance()->database['username'];
        }
        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();
        $this->displayWarningIfConfigFileNotWritable($view);
        $this->setBasicVariablesView($view);
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
        if ($_SERVER["REQUEST_METHOD"] != "POST"
            && !Common::isPhpCliMode()
        ) {
            $this->redirectToIndex('PrivacyManager', 'privacySettings');
            return;
        }

        $settings = PrivacyManager::getPurgeDataSettings();
        if ($settings['delete_logs_enable']) {
            $logDataPurger = LogDataPurger::make($settings);
            $logDataPurger->purgeData();
        }
        if ($settings['delete_reports_enable']) {
            $reportsPurger = ReportsPurger::make(
                $settings, PrivacyManager::getAllMetricsToKeep());
            $reportsPurger->purgeData(true);
        }
    }

    protected function getDeleteDBSizeEstimate($getSettingsFromQuery = false, $forceEstimate = false)
    {
        // get the purging settings & create two purger instances
        if ($getSettingsFromQuery) {
            $settings = $this->getPurgeSettingsFromRequest();
        } else {
            $settings = PrivacyManager::getPurgeDataSettings();
        }

        $doDatabaseSizeEstimate = Config::getInstance()->Deletelogs['enable_auto_database_size_estimate'];

        // determine the DB size & purged DB size
        $metadataProvider = new MySQLMetadataProvider();
        $tableStatuses = $metadataProvider->getAllTablesStatus();

        $totalBytes = 0;
        foreach ($tableStatuses as $status) {
            $totalBytes += $status['Data_length'] + $status['Index_length'];
        }

        $result = array(
            'currentSize' => MetricsFormatter::getPrettySizeFromBytes($totalBytes)
        );

        // if the db size estimate feature is enabled, get the estimate
        if ($doDatabaseSizeEstimate || $forceEstimate == 1) {
            // maps tables whose data will be deleted with number of rows that will be deleted
            // if a value is -1, it means the table will be dropped.
            $deletedDataSummary = PrivacyManager::getPurgeEstimate($settings);

            $totalAfterPurge = $totalBytes;
            foreach ($tableStatuses as $status) {
                $tableName = $status['Name'];
                if (isset($deletedDataSummary[$tableName])) {
                    $tableTotalBytes = $status['Data_length'] + $status['Index_length'];

                    // if dropping the table
                    if ($deletedDataSummary[$tableName] === ReportsPurger::DROP_TABLE) {
                        $totalAfterPurge -= $tableTotalBytes;
                    } else // if just deleting rows
                    {
                        if ($status['Rows'] > 0) {
                            $totalAfterPurge -= ($tableTotalBytes / $status['Rows']) * $deletedDataSummary[$tableName];
                        }
                    }
                }
            }

            $result['sizeAfterPurge'] = MetricsFormatter::getPrettySizeFromBytes($totalAfterPurge);
            $result['spaceSaved'] = MetricsFormatter::getPrettySizeFromBytes($totalBytes - $totalAfterPurge);
        }

        return $result;
    }

    protected function getAnonymizeIPInfo()
    {
        Piwik::checkUserIsSuperUser();
        $anonymizeIP = array();

        \Piwik\Plugin\Manager::getInstance()->loadPlugin(self::ANONYMIZE_IP_PLUGIN_NAME);

        $anonymizeIP["name"] = self::ANONYMIZE_IP_PLUGIN_NAME;
        $anonymizeIP["enabled"] = \Piwik\Plugin\Manager::getInstance()->isPluginActivated(self::ANONYMIZE_IP_PLUGIN_NAME);
        $anonymizeIP["maskLength"] = Config::getInstance()->Tracker['ip_address_mask_length'];
        $anonymizeIP["info"] = \Piwik\Plugin\Manager::getInstance()->getLoadedPlugin(self::ANONYMIZE_IP_PLUGIN_NAME)->getInformation();

        return $anonymizeIP;
    }

    protected function getDeleteDataInfo()
    {
        Piwik::checkUserIsSuperUser();
        $deleteDataInfos = array();
        $taskScheduler = new TaskScheduler();
        $deleteDataInfos["config"] = PrivacyManager::getPurgeDataSettings();
        $deleteDataInfos["deleteTables"] =
            "<br/>" . implode(", ", LogDataPurger::getDeleteTableLogTables());

        $scheduleTimetable = $taskScheduler->getScheduledTimeForMethod("PrivacyManager", "deleteLogTables");

        $optionTable = Option::get(self::OPTION_LAST_DELETE_PIWIK_LOGS);

        //If task was already rescheduled, read time from taskTimetable. Else, calculate next possible runtime.
        if (!empty($scheduleTimetable) && ($scheduleTimetable - time() > 0)) {
            $nextPossibleSchedule = (int)$scheduleTimetable;
        } else {
            $date = Date::factory("today");
            $nextPossibleSchedule = $date->addDay(1)->getTimestamp();
        }

        //deletion schedule did not run before
        if (empty($optionTable)) {
            $deleteDataInfos["lastRun"] = false;

            //next run ASAP (with next schedule run)
            $date = Date::factory("today");
            $deleteDataInfos["nextScheduleTime"] = $nextPossibleSchedule;
        } else {
            $deleteDataInfos["lastRun"] = $optionTable;
            $deleteDataInfos["lastRunPretty"] = Date::factory((int)$optionTable)->getLocalized('%day% %shortMonth% %longYear%');

            //Calculate next run based on last run + interval
            $nextScheduleRun = (int)($deleteDataInfos["lastRun"] + $deleteDataInfos["config"]["delete_logs_schedule_lowest_interval"] * 24 * 60 * 60);

            //is the calculated next run in the past? (e.g. plugin was disabled in the meantime or something) -> run ASAP
            if (($nextScheduleRun - time()) <= 0) {
                $deleteDataInfos["nextScheduleTime"] = $nextPossibleSchedule;
            } else {
                $deleteDataInfos["nextScheduleTime"] = $nextScheduleRun;
            }
        }

        $deleteDataInfos["nextRunPretty"] = MetricsFormatter::getPrettyTimeFromSeconds($deleteDataInfos["nextScheduleTime"] - time());

        return $deleteDataInfos;
    }

    protected function handlePluginState($state = 0)
    {
        $pluginController = new \Piwik\Plugins\CorePluginsAdmin\Controller();

        if ($state == 1 && !\Piwik\Plugin\Manager::getInstance()->isPluginActivated(self::ANONYMIZE_IP_PLUGIN_NAME)) {
            $pluginController->activate($redirectAfter = false);
        } elseif ($state == 0 && \Piwik\Plugin\Manager::getInstance()->isPluginActivated(self::ANONYMIZE_IP_PLUGIN_NAME)) {
            $pluginController->deactivate($redirectAfter = false);
        } else {
            //nothing to do
        }
    }
}
