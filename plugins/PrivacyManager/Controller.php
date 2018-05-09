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
use Piwik\DataTable\Renderer\Json;
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
        $settings['delete_logs_enable'] = Common::getRequestVar("enableDeleteLogs", 0);
        $settings['delete_logs_schedule_lowest_interval'] = Common::getRequestVar("deleteLowestInterval", 7);
        $settings['delete_logs_older_than'] = ((int)Common::getRequestVar("deleteLogsOlderThan", 180) < 1) ?
            1 : Common::getRequestVar("deleteOlderThan", 180);

        // delete reports settings
        $settings['delete_reports_enable'] = Common::getRequestVar("enableDeleteReports", 0);
        $deleteReportsOlderThan = Common::getRequestVar("deleteReportsOlderThan", 3);
        $settings['delete_reports_older_than'] = $deleteReportsOlderThan < 3 ? 3 : $deleteReportsOlderThan;
        $settings['delete_reports_keep_basic_metrics']   = Common::getRequestVar("keepBasic", 0);
        $settings['delete_reports_keep_day_reports']     = Common::getRequestVar("keepDay", 0);
        $settings['delete_reports_keep_week_reports']    = Common::getRequestVar("keepWeek", 0);
        $settings['delete_reports_keep_month_reports']   = Common::getRequestVar("keepMonth", 0);
        $settings['delete_reports_keep_year_reports']    = Common::getRequestVar("keepYear", 0);
        $settings['delete_reports_keep_range_reports']   = Common::getRequestVar("keepRange", 0);
        $settings['delete_reports_keep_segment_reports'] = Common::getRequestVar("keepSegments", 0);
        $settings['delete_logs_max_rows_per_query']      = PiwikConfig::getInstance()->Deletelogs['delete_logs_max_rows_per_query'];

        return $settings;
    }

    public function gdprOverview()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();

        $reportRetention = '';

        if ($purgeDataSettings['delete_reports_older_than'] > 12) {
            $reportRetention .= floor($purgeDataSettings['delete_reports_older_than']/12) . ' ' . Piwik::translate('Intl_PeriodYears') . ' ';
        }
        if ($purgeDataSettings['delete_reports_older_than'] % 12 > 0) {
            $reportRetention .= floor($purgeDataSettings['delete_reports_older_than']%12) . ' ' . Piwik::translate('Intl_PeriodMonths');
        }

        $rawDataRetention = '';

        if ($purgeDataSettings['delete_reports_older_than'] > 30) {
            $rawDataRetention .= floor($purgeDataSettings['delete_reports_older_than']/30) . ' ' . Piwik::translate('Intl_PeriodMonths') . ' ';
        }
        if ($purgeDataSettings['delete_reports_older_than'] % 30 > 0) {
            $rawDataRetention .= floor($purgeDataSettings['delete_reports_older_than']%30) . ' ' . Piwik::translate('Intl_PeriodDays');
        }

        return $this->renderTemplate('gdprOverview', [
            'reportRetention'     => trim($reportRetention),
            'rawDataRetention'    => trim($rawDataRetention),
            'deleteLogsEnable'    => $purgeDataSettings['delete_logs_enable'],
            'deleteReportsEnable' => $purgeDataSettings['delete_reports_enable'],
        ]);
    }

    public function usersOptOut()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $language = LanguagesManager::getLanguageCodeForCurrentUser();

        $doNotTrackOptions = array(
            array('key' => '1',
                'value' => Piwik::translate('PrivacyManager_DoNotTrack_Enable'),
                'description' => Piwik::translate('General_Recommended')),
            array('key' => '0',
                'value' => Piwik::translate('PrivacyManager_DoNotTrack_Disable'),
                'description' => Piwik::translate('General_NotRecommended'))
        );

        $dntChecker = new DoNotTrackHeaderChecker();

        return $this->renderTemplate('usersOptOut', array(
            'language' => $language,
            'doNotTrackOptions' => $doNotTrackOptions,
            'dntSupport' => $dntChecker->isActive()
        ));
    }

    public function consent()
    {
        Piwik::checkUserHasSomeAdminAccess();
        return $this->renderTemplate('askingForConsent');
    }

    public function gdprTools()
    {
        Piwik::checkUserHasSomeAdminAccess();

        return $this->renderTemplate('gdprTools');
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
        Piwik::checkUserHasSuperUserAccess();
        $view = new View('@PrivacyManager/privacySettings');

        if (Piwik::hasUserSuperUserAccess()) {
            $view->deleteData = $this->getDeleteDataInfo();
            $view->anonymizeIP = $this->getAnonymizeIPInfo();
            $view->canDeleteLogActions = Db::isLockPrivilegeGranted();
            $view->dbUser = PiwikConfig::getInstance()->database['username'];
            $view->deactivateNonce = Nonce::getNonce(self::DEACTIVATE_DNT_NONCE);
            $view->activateNonce   = Nonce::getNonce(self::ACTIVATE_DNT_NONCE);

            $view->maskLengthOptions = array(
                array('key' => '1',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskLength', array("1","192.168.100.xxx")),
                      'description' => ''),
                array('key' => '2',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskLength', array("2","192.168.xxx.xxx")),
                      'description' => Piwik::translate('General_Recommended')),
                array('key' => '3',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskLength', array("3","192.xxx.xxx.xxx")),
                      'description' => '')
            );
            $view->useAnonymizedIpForVisitEnrichmentOptions = array(
                array('key' => '1',
                      'value' => Piwik::translate('General_Yes'),
                      'description' => Piwik::translate('PrivacyManager_RecommendedForPrivacy')),
                array(
                      'key' => '0',
                      'value' => Piwik::translate('General_No'),
                      'description' => ''
                )
            );
            $view->scheduleDeletionOptions = array(
                array('key' => '1',
                      'value' => Piwik::translate('Intl_PeriodDay')),
                array('key' => '7',
                      'value' => Piwik::translate('Intl_PeriodWeek')),
                array('key' => '30',
                      'value' => Piwik::translate('Intl_PeriodMonth'))
            );
        }
        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();
        $this->setBasicVariablesView($view);

        $logDataAnonymizations = StaticContainer::get('Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations');
        $view->anonymizations = $logDataAnonymizations->getAllEntries();
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

    private function getDeleteDBSizeEstimate($getSettingsFromQuery = false, $forceEstimate = false)
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

    private function getAnonymizeIPInfo()
    {
        Piwik::checkUserHasSuperUserAccess();
        $anonymizeIP = array();

        $privacyConfig = new Config();
        $anonymizeIP["enabled"] = IPAnonymizer::isActive();
        $anonymizeIP["maskLength"] = $privacyConfig->ipAddressMaskLength;
        $anonymizeIP["anonymizeOrderId"] = $privacyConfig->anonymizeOrderId;
        $anonymizeIP["anonymizeUserId"] = $privacyConfig->anonymizeUserId;
        $anonymizeIP["useAnonymizedIpForVisitEnrichment"] = $privacyConfig->useAnonymizedIpForVisitEnrichment;
        if (!$anonymizeIP["useAnonymizedIpForVisitEnrichment"]) {
            $anonymizeIP["useAnonymizedIpForVisitEnrichment"] = '0';
        }

        return $anonymizeIP;
    }

    private function getDeleteDataInfo()
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

}
