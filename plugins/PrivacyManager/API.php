<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Exception;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Config as PiwikConfig;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\Live\Live;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\PrivacyManager\Dao\LogDataAnonymizer;
use Piwik\Plugins\PrivacyManager\Model\LogDataAnonymizations;
use Piwik\Plugins\PrivacyManager\Validators\VisitsDataSubject;
use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Site;
use Piwik\Tracker\TrackerCodeGenerator;
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
     * @var FeatureFlagManager
     */
    private $featureFlagManager;


    public function __construct(
        DataSubjects $gdpr,
        LogDataAnonymizations $logDataAnonymizations,
        LogDataAnonymizer $logDataAnonymizer,
        FeatureFlagManager $featureFlagManager
    ) {
        $this->gdpr = $gdpr;
        $this->logDataAnonymizations = $logDataAnonymizations;
        $this->logDataAnonymizer = $logDataAnonymizer;
        $this->featureFlagManager = $featureFlagManager;
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

        if (!Manager::getInstance()->isPluginActivated('Live')) {
            return [];
        }

        $siteIds = Site::getIdSitesFromIdSitesString($idSite);
        $siteIdsWithVisitorLogsOrProfilesEnabled = [];

        /*
         * Only retrieve data from sites that have visitor logs or profiles enabled.
         * Live::isVisitorProfileEnabled returns false if either logs or profiles
         * are disabled.
         */
        foreach ($siteIds as $siteId) {
            $isVisitorProfileEnabled = Live::isVisitorProfileEnabled($siteId);

            if ($isVisitorProfileEnabled) {
                $siteIdsWithVisitorLogsOrProfilesEnabled[] = $siteId;
            }
        }

        if (empty($siteIdsWithVisitorLogsOrProfilesEnabled)) {
            return [];
        }

        $result = Request::processRequest('Live.getLastVisitsDetails', [
            'segment' => $segment,
            'idSite' => $siteIdsWithVisitorLogsOrProfilesEnabled,
            'period' => 'range',
            'date' => '1998-01-01,today',
            'filter_limit' => 401,
            'doNotFetchActions' => 1,
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
        #[\SensitiveParameter]
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
                'default_value' => $default,
            );
        }

        return $formatted;
    }

    /**
     * Provide tracker file name and whether it's writable
     *
     * @return array{0: string, 1: bool}
     */
    private function getTrackerFileDetails(): array
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $jsCodeGenerator = new TrackerCodeGenerator();
            $file = new File(PIWIK_DOCUMENT_ROOT . '/' . $jsCodeGenerator->getJsTrackerEndpoint());
            $filename = $jsCodeGenerator->getJsTrackerEndpoint();

            if (Manager::getInstance()->isPluginActivated('CustomJsTracker')) {
                $file = StaticContainer::get('Piwik\Plugins\CustomJsTracker\TrackerUpdater')->getToFile();
                $filename = $file->getName();
            }

            return [$filename, $file->hasWriteAccess()];
        }

        return ['', false];
    }

    /**
     * Provide anonymisation settings to Matomo UI only
     *
     * @internal
     */
    public function getAnonymisationSettings(?int $idSiteSpecific = null): array
    {
        if (is_numeric($idSiteSpecific)) {
            $idSite = intval($idSiteSpecific);
            Piwik::checkUserHasAdminAccess($idSiteSpecific);
        } else {
            $idSite = null;
            Piwik::checkUserHasSuperUserAccess();
        }

        $privacyConfig = new Config($idSite);
        $settings = [];
        $extraMetadata = [];
        foreach ($privacyConfig->getConfigPropertyNames() as $propertyName) {
            $settings[$propertyName] = $privacyConfig->{$propertyName};

            // using custom setting type here as config properties use custom getter mechanism
            $settingType = PolicyManager::SETTING_TYPE_CUSTOM;
            $compliancePolicyControlled = PolicyManager::getCompliancePoliciesControllingASetting($propertyName, $idSite, $settingType);

            if (!empty($compliancePolicyControlled)) {
                $extraMetadata[$propertyName] = [
                    'compliancePolicyControlled' => $compliancePolicyControlled,
                    'idSite' => $idSite,
                ];
            }
        }
        $settings['useSiteSpecificSettings'] = $privacyConfig->useSiteSpecificSettings();

        // provide extra settings
        [$trackerFilename, $trackerFileWritable] = $this->getTrackerFileDetails();
        $settings = array_merge($settings, [
            'maskLengthOptions' => PrivacyManager::getMaskLengthOptions(),
            'useAnonymizedIpForVisitEnrichmentOptions' =>
                PrivacyManager::getUseAnonymizedIpForVisitEnrichmentOptions(),
            'referrerAnonymizationOptions' => ReferrerAnonymizer::getAvailableAnonymizationOptions(),
            'trackerFileName' => $trackerFilename,
            'trackerWritable' => $trackerFileWritable,
        ]);
        if (!empty($extraMetadata)) {
            $settings['extraMetadata'] = $extraMetadata;
        }

        return $settings;
    }

    /**
     * @internal
     */
    public function setAnonymizeIpSettings(
        bool $anonymizeIPEnable,
        int $ipAddressMaskLength,
        bool $useAnonymizedIpForVisitEnrichment,
        bool $anonymizeUserId = false,
        bool $anonymizeOrderId = false,
        string $anonymizeReferrer = '',
        bool $forceCookielessTracking = false,
        bool $randomizeConfigId = false,
        ?int $idSiteSpecific = null,
        bool $useSiteSpecificSettings = false,
        #[\SensitiveParameter]
        string $passwordConfirmation = ''
    ) {
        if (null !== $idSiteSpecific) {
            $idSite = $idSiteSpecific;
            Piwik::checkUserHasAdminAccess($idSiteSpecific);
        } else {
            $idSite = null;
            Piwik::checkUserHasSuperUserAccess();
        }

        // if we receive a specific site ID, and it's set not to use custom site settings, we need to remove them
        // so that the behaviour defaults to the system settings
        if ($idSite && !$useSiteSpecificSettings) {
            $privacyConfig = new Config($idSite);
            $privacyConfig->removeForSite();

            return true;
        }

        if ($randomizeConfigId) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if ($anonymizeIPEnable) {
            IPAnonymizer::activate($idSite);
        } else {
            IPAnonymizer::deactivate($idSite);
        }

        if (
            !empty($anonymizeReferrer)
            && !array_key_exists($anonymizeReferrer, ReferrerAnonymizer::getAvailableAnonymizationOptions())
        ) {
            $anonymizeReferrer = '';
        }

        $privacyConfig = new Config($idSite);
        $privacyConfig->ipAddressMaskLength = $ipAddressMaskLength;
        $privacyConfig->useAnonymizedIpForVisitEnrichment = $useAnonymizedIpForVisitEnrichment;
        $privacyConfig->anonymizeReferrer = $anonymizeReferrer;
        $privacyConfig->anonymizeUserId = $anonymizeUserId;
        $privacyConfig->anonymizeOrderId = $anonymizeOrderId;
        $privacyConfig->randomizeConfigId = $randomizeConfigId;

        if (!$idSite) {
            // only allow setting 'force cookieless tracking' instance-wide and skip it for site as it applies
            // changes to JS tracker files that we can't currently support on a per-site basis
            $privacyConfig->forceCookielessTracking = $forceCookielessTracking;

            // update tracker files
            Piwik::postEvent('CustomJsTracker.updateTracker');
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
            'delete_logs_schedule_lowest_interval' => (int) $deleteLowestInterval,
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
        #[\SensitiveParameter]
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
    public function executeDataPurge(
        #[\SensitiveParameter]
        $passwordConfirmation
    ) {
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

    /**
     * @internal
     * @return array<array<string,string>>
     */
    public function getCompliancePolicies(): array
    {
        return PolicyManager::getAllPoliciesDetails();
    }

    /**
     * @internal
     * @param int|string $idSite
     * @return array<string,bool|array<int, array<string,string>>>
     */
    public function getComplianceStatus($idSite, string $complianceType): array
    {
        if ($idSite === 'all') {
            $idSite = null;
        } else {
            $idSite = intval($idSite);
        }

        if (false === $this->featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            throw new Exception('Feature not available');
        }

        Piwik::checkUserHasSuperUserAccess();

        $policy = PolicyManager::getPolicyByName($complianceType);

        if (is_null($policy)) {
            throw new Exception('Invalid compliance type');
        }

        $payload['complianceModeEnforced'] = PolicyManager::isPolicyActive($policy, $idSite);
        $payload['complianceConfigControlled'] = PolicyManager::isPolicyConfigControlled($policy);
        $settingsUnderPolicy = PolicyManager::getAllControlledSettings($policy, $idSite);
        foreach ($settingsUnderPolicy as $setting) {
            $payload['complianceRequirements'][] = [
                'name' => $setting::getTitle(),
                'value' => $setting::isCompliant($policy, $idSite) ? 'compliant' : 'non_compliant',
                'notes' => $setting::getComplianceRequirementNote($idSite),
            ];
        }
        $unknownSettings = PolicyManager::getAllUnknownSettings($policy);
        foreach ($unknownSettings as $unknownSetting) {
            $payload['complianceRequirements'][] = [
                'name' => $unknownSetting['title'],
                'value' => 'unknown',
                'notes' => $unknownSetting['note'],
            ];
        }
        return $payload;
    }

    /**
     * @internal
     */
    public function setComplianceStatus(
        string $idSite,
        string $complianceType,
        bool $enforce,
        string $passwordConfirmation = null
    ): bool {
        if (!$this->featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            throw new Exception('Feature not available');
        }

        Piwik::checkUserHasSuperUserAccess();

        if (Common::getRequestVar('force_api_session', 0)) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        $policy = PolicyManager::getPolicyByName($complianceType);

        if (is_null($policy) || !is_a($policy, CompliancePolicy::class, true)) {
            throw new Exception('Invalid compliance type');
        }

        if ($idSite === 'all') {
            $idSite = null;
        } else {
            $idSite = intval($idSite);
        }

        PolicyManager::setPolicyActiveStatus($policy, $enforce, $idSite);

        return $enforce;
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
