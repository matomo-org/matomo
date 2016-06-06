<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Common;
use Piwik\Config as PiwikConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics\Formatter;
use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Scheduler\Scheduler;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    const OPTION_LAST_DELETE_PIWIK_LOGS = "lastDelete_piwik_logs";
    const ACTIVATE_DNT_NONCE = 'PrivacyManager.activateDnt';
    const DEACTIVATE_DNT_NONCE = 'PrivacyManager.deactivateDnt';

    public function saveSettings()
    {
        Piwik::checkUserHasSuperUserAccess();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->checkTokenInUrl();
            switch (Common::getRequestVar('form')) {
                case("formMaskLength"):
                    $enable = Common::getRequestVar("anonymizeIPEnable", 0);
                    if ($enable == 1) {
                        IPAnonymizer::activate();
                    } else if ($enable == 0) {
                        IPAnonymizer::deactivate();
                    } else {
                        // pass
                    }

                    $privacyConfig = new Config();
                    $privacyConfig->ipAddressMaskLength = Common::getRequestVar("maskLength", 1);
                    $privacyConfig->useAnonymizedIpForVisitEnrichment = Common::getRequestVar("useAnonymizedIpForVisitEnrichment", 1);
                    break;

                case("formDeleteSettings"):
                    $this->checkDataPurgeAdminSettingsIsEnabled();
                    $settings = $this->getPurgeSettingsFromRequest();
                    PrivacyManager::savePurgeDataSettings($settings);
                    break;

                default: //do nothing
                    break;
            }
        }

        $notification = new Notification(Piwik::translate('General_YourChangesHaveBeenSaved'));
        $notification->context = Notification::CONTEXT_SUCCESS;
        Notification\Manager::notify('PrivacyManager_ChangesHaveBeenSaved', $notification);

        $this->redirectToIndex('PrivacyManager', 'privacySettings', null, null, null, array('updated' => 1));
    }

    private function checkDataPurgeAdminSettingsIsEnabled()
    {
        if (!self::isDataPurgeSettingsEnabled()) {
            throw new \Exception("Configuring deleting log data and report data has been disabled by Piwik admins.");
        }
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
        $settings['delete_reports_keep_basic_metrics']   = Common::getRequestVar("deleteReportsKeepBasic", 0);
        $settings['delete_reports_keep_day_reports']     = Common::getRequestVar("deleteReportsKeepDay", 0);
        $settings['delete_reports_keep_week_reports']    = Common::getRequestVar("deleteReportsKeepWeek", 0);
        $settings['delete_reports_keep_month_reports']   = Common::getRequestVar("deleteReportsKeepMonth", 0);
        $settings['delete_reports_keep_year_reports']    = Common::getRequestVar("deleteReportsKeepYear", 0);
        $settings['delete_reports_keep_range_reports']   = Common::getRequestVar("deleteReportsKeepRange", 0);
        $settings['delete_reports_keep_segment_reports'] = Common::getRequestVar("deleteReportsKeepSegments", 0);
        $settings['delete_logs_max_rows_per_query']      = PiwikConfig::getInstance()->Deletelogs['delete_logs_max_rows_per_query'];

        return $settings;
    }

    /**
     * Echo's an HTML chunk describing the current database size, and the estimated space
     * savings after the scheduled data purge is run.
     */
    public function getDatabaseSize()
    {
        Piwik::checkUserHasSuperUserAccess();
        $view = new View('@PrivacyManager/getDatabaseSize');

        $forceEstimate = Common::getRequestVar('forceEstimate', 0);
        $view->dbStats = $this->getDeleteDBSizeEstimate($getSettingsFromQuery = true, $forceEstimate);
        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();

        return $view->render();
    }

    public function privacySettings()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $view = new View('@PrivacyManager/privacySettings');

        if (Piwik::hasUserSuperUserAccess()) {
            $view->deleteData = $this->getDeleteDataInfo();
            $view->anonymizeIP = $this->getAnonymizeIPInfo();
            $dntChecker = new DoNotTrackHeaderChecker();
            $view->dntSupport = $dntChecker->isActive();
            $view->canDeleteLogActions = Db::isLockPrivilegeGranted();
            $view->dbUser = PiwikConfig::getInstance()->database['username'];
            $view->deactivateNonce = Nonce::getNonce(self::DEACTIVATE_DNT_NONCE);
            $view->activateNonce   = Nonce::getNonce(self::ACTIVATE_DNT_NONCE);
        }
        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();
        $this->setBasicVariablesView($view);
        return $view->render();
    }

    /**
     * Executes a data purge, deleting log data and report data using the current config
     * options. Echo's the result of getDatabaseSize after purging.
     */
    public function executeDataPurge()
    {
        $this->checkDataPurgeAdminSettingsIsEnabled();

        Piwik::checkUserHasSuperUserAccess();
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
            /** @var LogDataPurger $logDataPurger */
            $logDataPurger = StaticContainer::get('Piwik\Plugins\PrivacyManager\LogDataPurger');
            $logDataPurger->purgeData($settings['delete_logs_older_than']);
        }
        if ($settings['delete_reports_enable']) {
            $reportsPurger = ReportsPurger::make($settings, PrivacyManager::getAllMetricsToKeep());
            $reportsPurger->purgeData(true);
        }
    }

    protected function getDeleteDBSizeEstimate($getSettingsFromQuery = false, $forceEstimate = false)
    {
        $this->checkDataPurgeAdminSettingsIsEnabled();

        // get the purging settings & create two purger instances
        if ($getSettingsFromQuery) {
            $settings = $this->getPurgeSettingsFromRequest();
        } else {
            $settings = PrivacyManager::getPurgeDataSettings();
        }

        $doDatabaseSizeEstimate = PiwikConfig::getInstance()->Deletelogs['enable_auto_database_size_estimate'];

        // determine the DB size & purged DB size
        $metadataProvider = StaticContainer::get('Piwik\Plugins\DBStats\MySQLMetadataProvider');
        $tableStatuses = $metadataProvider->getAllTablesStatus();

        $totalBytes = 0;
        foreach ($tableStatuses as $status) {
            $totalBytes += $status['Data_length'] + $status['Index_length'];
        }

        $formatter = new Formatter();
        $result = array(
            'currentSize' => $formatter->getPrettySizeFromBytes($totalBytes)
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

            $result['sizeAfterPurge'] = $formatter->getPrettySizeFromBytes($totalAfterPurge);
            $result['spaceSaved'] = $formatter->getPrettySizeFromBytes($totalBytes - $totalAfterPurge);
        }

        return $result;
    }

    protected function getAnonymizeIPInfo()
    {
        Piwik::checkUserHasSuperUserAccess();
        $anonymizeIP = array();

        $privacyConfig = new Config();
        $anonymizeIP["enabled"] = IpAnonymizer::isActive();
        $anonymizeIP["maskLength"] = $privacyConfig->ipAddressMaskLength;
        $anonymizeIP["useAnonymizedIpForVisitEnrichment"] = $privacyConfig->useAnonymizedIpForVisitEnrichment;

        return $anonymizeIP;
    }

    protected function getDeleteDataInfo()
    {
        Piwik::checkUserHasSuperUserAccess();
        $deleteDataInfos = array();
        $deleteDataInfos["config"] = PrivacyManager::getPurgeDataSettings();
        $deleteDataInfos["deleteTables"] =
            "<br/>" . implode(", ", LogDataPurger::getDeleteTableLogTables());

        /** @var Scheduler $scheduler */
        $scheduler = StaticContainer::getContainer()->get('Piwik\Scheduler\Scheduler');

        $scheduleTimetable = $scheduler->getScheduledTimeForMethod("PrivacyManager", "deleteLogTables");

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
                $deleteDataInfos["lastRunPretty"] = Date::factory((int)$optionTable)->getLocalized(Date::DATE_FORMAT_SHORT);

            //Calculate next run based on last run + interval
            $nextScheduleRun = (int)($deleteDataInfos["lastRun"] + $deleteDataInfos["config"]["delete_logs_schedule_lowest_interval"] * 24 * 60 * 60);

            //is the calculated next run in the past? (e.g. plugin was disabled in the meantime or something) -> run ASAP
            if (($nextScheduleRun - time()) <= 0) {
                $deleteDataInfos["nextScheduleTime"] = $nextPossibleSchedule;
            } else {
                $deleteDataInfos["nextScheduleTime"] = $nextScheduleRun;
            }
        }

        $formatter = new Formatter();

        $deleteDataInfos["nextRunPretty"] = $formatter->getPrettyTimeFromSeconds($deleteDataInfos["nextScheduleTime"] - time());

        return $deleteDataInfos;
    }

    public function deactivateDoNotTrack()
    {
        Piwik::checkUserHasSuperUserAccess();
        Nonce::checkNonce(self::DEACTIVATE_DNT_NONCE);

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->deactivate();

        $this->redirectToIndex('PrivacyManager', 'privacySettings');
    }

    public function activateDoNotTrack()
    {
        Piwik::checkUserHasSuperUserAccess();
        Nonce::checkNonce(self::ACTIVATE_DNT_NONCE);

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->activate();

        $this->redirectToIndex('PrivacyManager', 'privacySettings');
    }
}
