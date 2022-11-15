<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\SiteContentDetector;
use Piwik\Scheduler\Scheduler;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    const OPTION_LAST_DELETE_PIWIK_LOGS = "lastDelete_piwik_logs";
    const ACTIVATE_DNT_NONCE = 'PrivacyManager.activateDnt';
    const DEACTIVATE_DNT_NONCE = 'PrivacyManager.deactivateDnt';

    /**
     * @var ReferrerAnonymizer
     */
    private $referrerAnonymizer;

    /** @var SiteContentDetector */
    private $siteContentDetector;

    public function __construct(ReferrerAnonymizer $referrerAnonymizer, SiteContentDetector $siteContentDetector)
    {
        parent::__construct();
        $this->referrerAnonymizer = $referrerAnonymizer;
        $this->siteContentDetector = $siteContentDetector;
    }

    private function checkDataPurgeAdminSettingsIsEnabled()
    {
        if (!self::isDataPurgeSettingsEnabled()) {
            throw new \Exception("Configuring deleting raw data and report data has been disabled by Piwik admins.");
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
        $settings = [];

        // delete logs settings
        $settings['delete_logs_enable'] = Common::getRequestVar("enableDeleteLogs", 0);
        $settings['delete_logs_schedule_lowest_interval'] = Common::getRequestVar("deleteLowestInterval", 7);
        $settings['delete_logs_older_than'] = ((int)Common::getRequestVar("deleteLogsOlderThan", 180) < 1) ?
            1 : Common::getRequestVar("deleteOlderThan", 180);

        // delete reports settings
        $settings['delete_reports_enable'] = Common::getRequestVar("enableDeleteReports", 0);
        $deleteReportsOlderThan = Common::getRequestVar("deleteReportsOlderThan", 3);
        $settings['delete_reports_older_than'] = $deleteReportsOlderThan < 2 ? 2 : $deleteReportsOlderThan;
        $settings['delete_reports_keep_basic_metrics']             = Common::getRequestVar("keepBasic", 0);
        $settings['delete_reports_keep_day_reports']               = Common::getRequestVar("keepDay", 0);
        $settings['delete_reports_keep_week_reports']              = Common::getRequestVar("keepWeek", 0);
        $settings['delete_reports_keep_month_reports']             = Common::getRequestVar("keepMonth", 0);
        $settings['delete_reports_keep_year_reports']              = Common::getRequestVar("keepYear", 0);
        $settings['delete_reports_keep_range_reports']             = Common::getRequestVar("keepRange", 0);
        $settings['delete_reports_keep_segment_reports']           = Common::getRequestVar("keepSegments", 0);
        $settings['delete_logs_max_rows_per_query']                = PiwikConfig::getInstance()->Deletelogs['delete_logs_max_rows_per_query'];
        $settings['delete_logs_unused_actions_max_rows_per_query'] = PiwikConfig::getInstance()->Deletelogs['delete_logs_unused_actions_max_rows_per_query'];

        return $settings;
    }

    public function gdprOverview()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $purgeDataSettings = PrivacyManager::getPurgeDataSettings();

        $reportRetention = '';

        if ($purgeDataSettings['delete_reports_older_than'] > 12) {
            $years = floor($purgeDataSettings['delete_reports_older_than']/12);
            $reportRetention .=  $years . ' ' . Piwik::translate($years > 1 ? 'Intl_PeriodYears' : 'Intl_PeriodYear') . ' ';
        }
        if ($purgeDataSettings['delete_reports_older_than'] % 12 > 0) {
            $months = floor($purgeDataSettings['delete_reports_older_than']%12);
            $reportRetention .= $months . ' ' . Piwik::translate($months > 1 ? 'Intl_PeriodMonths' : 'Intl_PeriodMonth');
        }

        $rawDataRetention = '';

        if ($purgeDataSettings['delete_logs_older_than'] > 90) {
            // only show months when it is more than 90 days...
            $months = floor($purgeDataSettings['delete_logs_older_than']/30.4);
            $daysLeft = round($purgeDataSettings['delete_logs_older_than'] - ($months * 30.4));
            $rawDataRetention .= $months . ' ' . Piwik::translate($months > 1 ? 'Intl_PeriodMonths' : 'Intl_PeriodMonth') . ' ';

            if ($daysLeft > 0) {
                $rawDataRetention .= $daysLeft . ' ' . Piwik::translate($daysLeft > 1 ? 'Intl_PeriodDays' : 'Intl_PeriodDay');
            }

        } elseif ($purgeDataSettings['delete_logs_older_than'] > 0) {
            $days = $purgeDataSettings['delete_logs_older_than'];
            $rawDataRetention .= $days . ' ' . Piwik::translate($days > 1 ? 'Intl_PeriodDays' : 'Intl_PeriodDay');
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

        $doNotTrackOptions = [
            ['key' => '1',
                'value' => Piwik::translate('PrivacyManager_DoNotTrack_Enable'),
                'description' => Piwik::translate('General_Recommended')],
            ['key' => '0',
                'value' => Piwik::translate('PrivacyManager_DoNotTrack_Disable'),
                'description' => Piwik::translate('General_NotRecommended')]
        ];

        $dntChecker = new DoNotTrackHeaderChecker();

        $languages = APILanguagesManager::getInstance()->getAvailableLanguageNames();
        $languageOptions = [];
        foreach ($languages as $language) {
            $languageOptions[] = [
                'key' => $language['code'],
                'value' => $language['name']
            ];
        }

        return $this->renderTemplate('usersOptOut', [
            'language' => LanguagesManager::getLanguageCodeForCurrentUser(),
            'currentLanguageCode' => LanguagesManager::getLanguageCodeForCurrentUser(),
            'languageOptions' => $languageOptions,
            'doNotTrackOptions' => $doNotTrackOptions,
            'dntSupport' => $dntChecker->isActive()
        ]);
    }

    public function consent()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $view = new View('@PrivacyManager/askingForConsent');

        $this->siteContentDetector->detectContent([SiteContentDetector::CONSENT_MANAGER]);
        $view->consentManagerName = null;
        if ($this->siteContentDetector->consentManagerId) {
            $view->consentManagerName = $this->siteContentDetector->consentManagerName;
            $view->consentManagerUrl = $this->siteContentDetector->consentManagerUrl;
            $view->consentManagerIsConnected = $this->siteContentDetector->isConnected;
        }
        $this->setBasicVariablesView($view);
        return $view->render();
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
            $jsCodeGenerator = new TrackerCodeGenerator();
            $file = new File(PIWIK_DOCUMENT_ROOT . '/' . $jsCodeGenerator->getJsTrackerEndpoint());
            $filename = $jsCodeGenerator->getJsTrackerEndpoint();

            if (Manager::getInstance()->isPluginActivated('CustomJsTracker')) {
                $file = StaticContainer::get('Piwik\Plugins\CustomJsTracker\TrackerUpdater')->getToFile();
                $filename = $file->getName();
            }

            $view->trackerFileName = $filename;
            $view->trackerWritable = $file->hasWriteAccess();
            $view->deleteData = $this->getDeleteDataInfo();
            $view->anonymizeIP = $this->getAnonymizeIPInfo();
            $view->canDeleteLogActions = Db::isLockPrivilegeGranted();
            $view->dbUser = PiwikConfig::getInstance()->database['username'];
            $view->deactivateNonce = Nonce::getNonce(self::DEACTIVATE_DNT_NONCE);
            $view->activateNonce   = Nonce::getNonce(self::ACTIVATE_DNT_NONCE);

            $view->maskLengthOptions = [
                ['key' => '1',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskLength', ["1","192.168.100.xxx"]),
                      'description' => ''],
                ['key' => '2',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskLength', ["2","192.168.xxx.xxx"]),
                      'description' => Piwik::translate('General_Recommended')],
                ['key' => '3',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskLength', ["3","192.xxx.xxx.xxx"]),
                      'description' => ''],
                ['key' => '4',
                      'value' => Piwik::translate('PrivacyManager_AnonymizeIpMaskFully'),
                      'description' => '']
            ];
            $view->useAnonymizedIpForVisitEnrichmentOptions = [
                ['key' => '1',
                      'value' => Piwik::translate('General_Yes'),
                      'description' => Piwik::translate('PrivacyManager_RecommendedForPrivacy')],
                [
                      'key' => '0',
                      'value' => Piwik::translate('General_No'),
                      'description' => ''
                ]
            ];
            $view->scheduleDeletionOptions = [
                ['key' => '1',
                      'value' => Piwik::translate('Intl_PeriodDay')],
                ['key' => '7',
                      'value' => Piwik::translate('Intl_PeriodWeek')],
                ['key' => '30',
                      'value' => Piwik::translate('Intl_PeriodMonth')]
            ];
            $view->referrerAnonymizationOptions = $this->referrerAnonymizer->getAvailableAnonymizationOptions();
        }
        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();
        $this->setBasicVariablesView($view);

        $logDataAnonymizations = StaticContainer::get('Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations');
        $view->anonymizations = $logDataAnonymizations->getAllEntries();
        return $view->render();
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
        $result = [
            'currentSize' => $formatter->getPrettySizeFromBytes($totalBytes)
        ];

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
        $anonymizeIP = [];

        $privacyConfig = new Config();
        $anonymizeIP["enabled"] = IPAnonymizer::isActive();
        $anonymizeIP["maskLength"] = $privacyConfig->ipAddressMaskLength;
        $anonymizeIP["forceCookielessTracking"] = $privacyConfig->forceCookielessTracking;
        $anonymizeIP["anonymizeOrderId"] = $privacyConfig->anonymizeOrderId;
        $anonymizeIP["anonymizeUserId"] = $privacyConfig->anonymizeUserId;
        $anonymizeIP["useAnonymizedIpForVisitEnrichment"] = $privacyConfig->useAnonymizedIpForVisitEnrichment;
        $anonymizeIP["anonymizeReferrer"] = $privacyConfig->anonymizeReferrer;
        if (!$anonymizeIP["useAnonymizedIpForVisitEnrichment"]) {
            $anonymizeIP["useAnonymizedIpForVisitEnrichment"] = '0';
        }

        return $anonymizeIP;
    }

    private function getDeleteDataInfo()
    {
        Piwik::checkUserHasSuperUserAccess();
        $deleteDataInfos = [];
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
