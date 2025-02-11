<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Piwik\API\Request;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Config as PiwikConfig;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\PrivacyManager\Dao\LogDataAnonymizer;
use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;
use Piwik\Plugins\PrivacyManager\Validators\VisitsDataSubject;
use Piwik\Site;
use Piwik\Validators\BaseValidator;

/**
 * API for plugin PrivacyManager
 *
 * @method static \Piwik\Plugins\PrivacyManager\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var DataSubjects
     */
    private $gdpr;

    /**
     * @var LogDataAnonymizations
     */
    private $logDataAnonymizations;

    /**
     * @var LogDataAnonymizer
     */
    private $logDataAnonymizer;

    /**
     * @var ReferrerAnonymizer
     */
    private $referrerAnonymizer;

    public function __construct(
        DataSubjects $gdpr,
        LogDataAnonymizations $logDataAnonymizations,
        LogDataAnonymizer $logDataAnonymizer,
        ReferrerAnonymizer $referrerAnonymizer
    ) {
        $this->gdpr = $gdpr;
        $this->logDataAnonymizations = $logDataAnonymizations;
        $this->logDataAnonymizer = $logDataAnonymizer;
        $this->referrerAnonymizer = $referrerAnonymizer;
    }

    private function checkDataSubjectVisits($visits)
    {
        BaseValidator::check('visits', $visits, [new VisitsDataSubject()]);

        $idSites = [];
        foreach ($visits as $index => $visit) {
            $idSites[] = $visit['idsite'];
        }
        Piwik::checkUserHasAdminAccess($idSites);
    }

    public function deleteDataSubjects($visits)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->deleteDataSubjects($visits);
    }

    public function exportDataSubjects($visits)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->exportDataSubjects($visits);
    }

    public function findDataSubjects($idSite, $segment)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $result = Request::processRequest('Live.getLastVisitsDetails', [
            'segment' => $segment,
            'idSite' => $idSite,
            'period' => 'range',
            'date' => '1998-01-01,today',
            'filter_limit' => 401,
            'doNotFetchActions' => 1
        ]);

        $columnsToKeep = [
            'lastActionDateTime',
            'idVisit',
            'idSite',
            'siteName',
            'visitorId',
            'visitIp',
            'userId',
            'deviceType',
            'deviceModel',
            'deviceTypeIcon',
            'operatingSystem',
            'operatingSystemIcon',
            'browser',
            'browserFamilyDescription',
            'browserIcon',
            'country',
            'region',
            'countryFlag',
        ];

        foreach ($result->getColumns() as $column) {
            if (!in_array($column, $columnsToKeep)) {
                $result->deleteColumn($column);
            }
        }

        // Note: Datatable PostProcessor is disabled for this method in PrivacyManager::shouldDisablePostProcessing
        return $result;
    }

    public function anonymizeSomeRawData(
        $idSites,
        $date,
        $anonymizeIp = false,
        $anonymizeLocation = false,
        $anonymizeUserId = false,
        $unsetVisitColumns = [],
        $unsetLinkVisitActionColumns = [],
        $passwordConfirmation = ''
    ) {
        Piwik::checkUserHasSuperUserAccess();

        $this->confirmCurrentUserPassword($passwordConfirmation);

        if ($idSites === 'all' || empty($idSites)) {
            $idSites = null; // all websites
        } else {
            $idSites = Site::getIdSitesFromIdSitesString($idSites);
        }
        $requester = Piwik::getCurrentUserLogin();
        $this->logDataAnonymizations->scheduleEntry(
            $requester,
            $idSites,
            $date,
            $anonymizeIp,
            $anonymizeLocation,
            $anonymizeUserId,
            $unsetVisitColumns,
            $unsetLinkVisitActionColumns
        );
    }

    public function getAvailableVisitColumnsToAnonymize()
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableVisitColumnsToAnonymize();

        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    public function getAvailableLinkVisitActionColumnsToAnonymize()
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableLinkVisitActionColumnsToAnonymize();

        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    private function formatAvailableColumnsToAnonymize($columns)
    {
        ksort($columns);
        $formatted = array();
        foreach ($columns as $column => $default) {
            $formatted[] = array(
                'column_name' => $column,
                'default_value' => $default
            );
        }

        return $formatted;
    }

    /**
     * @internal
     */
    public function setAnonymizeIpSettings($anonymizeIPEnable, $maskLength, $useAnonymizedIpForVisitEnrichment, $anonymizeUserId = false, $anonymizeOrderId = false, $anonymizeReferrer = '', $forceCookielessTracking = false, $randomizeConfigId = false)
    {
        Piwik::checkUserHasSuperUserAccess();

        if ($anonymizeIPEnable == '1') {
            IPAnonymizer::activate();
        } elseif ($anonymizeIPEnable == '0') {
            IPAnonymizer::deactivate();
        } else {
            // pass
        }

        if (
            !empty($anonymizeReferrer)
            && !array_key_exists($anonymizeReferrer, $this->referrerAnonymizer->getAvailableAnonymizationOptions())
        ) {
            $anonymizeReferrer = '';
        }

        $privacyConfig = new Config();
        $privacyConfig->ipAddressMaskLength = (int) $maskLength;
        $privacyConfig->useAnonymizedIpForVisitEnrichment = (bool) $useAnonymizedIpForVisitEnrichment;
        $privacyConfig->anonymizeReferrer = $anonymizeReferrer;

        if (false !== $anonymizeUserId) {
            $privacyConfig->anonymizeUserId = (bool) $anonymizeUserId;
        }

        if (false !== $anonymizeOrderId) {
            $privacyConfig->anonymizeOrderId = (bool) $anonymizeOrderId;
        }

        if (false !== $forceCookielessTracking) {
            $privacyConfig->forceCookielessTracking = (bool) $forceCookielessTracking;

            // update tracker files
            Piwik::postEvent('CustomJsTracker.updateTracker');
        }

        if (false !== $randomizeConfigId) {
            $privacyConfig->randomizeConfigId = (bool) $randomizeConfigId;
        }

        return true;
    }

    /**
     * @internal
     */
    public function deactivateDoNotTrack()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->deactivate();

        return true;
    }

    /**
     * @internal
     */
    public function activateDoNotTrack()
    {
        Piwik::checkUserHasSuperUserAccess();

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->activate();

        return true;
    }

    /**
     * @internal
     */
    public function setScheduleReportDeletionSettings($deleteLowestInterval = 7, $passwordConfirmation = '')
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->confirmCurrentUserPassword($passwordConfirmation);

        return $this->savePurgeDataSettings(array(
            'delete_logs_schedule_lowest_interval' => (int) $deleteLowestInterval
        ));
    }

    /**
     * @internal
     */
    public function setDeleteLogsSettings($enableDeleteLogs = '0', $deleteLogsOlderThan = 180, $passwordConfirmation = '')
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->confirmCurrentUserPassword($passwordConfirmation);

        $deleteLogsOlderThan = (int) $deleteLogsOlderThan;
        if ($deleteLogsOlderThan < 1) {
            $deleteLogsOlderThan = 1;
        }

        return $this->savePurgeDataSettings(array(
            'delete_logs_enable' => !empty($enableDeleteLogs),
            'delete_logs_older_than' => $deleteLogsOlderThan,
        ));
    }

    /**
     * @internal
     */
    public function setDeleteReportsSettings(
        $enableDeleteReports = 0,
        $deleteReportsOlderThan = 3,
        $keepBasic = 0,
        $keepDay = 0,
        $keepWeek = 0,
        $keepMonth = 0,
        $keepYear = 0,
        $keepRange = 0,
        $keepSegments = 0,
        $passwordConfirmation = ''
    ) {
        Piwik::checkUserHasSuperUserAccess();
        $this->confirmCurrentUserPassword($passwordConfirmation);

        $settings = [];

        // delete reports settings
        $settings['delete_reports_enable'] = !empty($enableDeleteReports);

        $deleteReportsOlderThan = (int) $deleteReportsOlderThan;
        if ($deleteReportsOlderThan < 2) {
            $deleteReportsOlderThan = 2;
        }

        $settings['delete_reports_older_than'] = $deleteReportsOlderThan;

        $settings['delete_reports_keep_basic_metrics']             = (int) $keepBasic;
        $settings['delete_reports_keep_day_reports']               = (int) $keepDay;
        $settings['delete_reports_keep_week_reports']              = (int) $keepWeek;
        $settings['delete_reports_keep_month_reports']             = (int) $keepMonth;
        $settings['delete_reports_keep_year_reports']              = (int) $keepYear;
        $settings['delete_reports_keep_range_reports']             = (int) $keepRange;
        $settings['delete_reports_keep_segment_reports']           = (int) $keepSegments;
        $settings['delete_logs_max_rows_per_query']                = PiwikConfig::getInstance()->Deletelogs['delete_logs_max_rows_per_query'];
        $settings['delete_logs_unused_actions_max_rows_per_query'] = PiwikConfig::getInstance()->Deletelogs['delete_logs_unused_actions_max_rows_per_query'];

        return $this->savePurgeDataSettings($settings);
    }

    /**
     * Executes a data purge, deleting raw data and report data using the current config options.
     *
     * @internal
     */
    public function executeDataPurge($passwordConfirmation)
    {
        $this->confirmCurrentUserPassword($passwordConfirmation);
        Piwik::checkUserHasSuperUserAccess();

        $this->checkDataPurgeAdminSettingsIsEnabled();

        $settings = PrivacyManager::getPurgeDataSettings();
        if ($settings['delete_logs_enable']) {
            /** @var LogDataPurger $logDataPurger */
            $logDataPurger = StaticContainer::get('Piwik\Plugins\PrivacyManager\LogDataPurger');
            $logDataPurger->purgeData($settings['delete_logs_older_than'], true);
        }
        if ($settings['delete_reports_enable']) {
            $reportsPurger = ReportsPurger::make($settings, PrivacyManager::getAllMetricsToKeep());
            $reportsPurger->purgeData(true);
        }
    }

    private function savePurgeDataSettings($settings)
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkDataPurgeAdminSettingsIsEnabled();

        PrivacyManager::savePurgeDataSettings($settings);

        return true;
    }

    private function checkDataPurgeAdminSettingsIsEnabled()
    {
        if (!Controller::isDataPurgeSettingsEnabled()) {
            throw new \Exception("Configuring deleting raw data and report data has been disabled by Matomo admins.");
        }
    }
}
