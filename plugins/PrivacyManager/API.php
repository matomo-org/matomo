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
use Piwik\Request\AuthenticationToken;
use Piwik\Policy\CompliancePolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Site;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Validators\BaseValidator;

/**
 * The PrivacyManager API lets you manage GDPR workflows, anonymization settings, and privacy compliance controls.
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

    /**
     * @param array $visits
     * @return void
     */
    private function checkDataSubjectVisits($visits)
    {
        BaseValidator::check('visits', $visits, [new VisitsDataSubject()]);

        $idSites = [];
        foreach ($visits as $index => $visit) {
            $idSites[] = $visit['idsite'];
        }
        Piwik::checkUserHasAdminAccess($idSites);
    }

    /**
     * Deletes the requested data subjects from the stored visit data.
     *
     * @param array $visits Data subject visit descriptors to delete.
     * @return array Deletion results keyed by plugin or storage area.
     */
    public function deleteDataSubjects($visits)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->deleteDataSubjects($visits);
    }

    /**
     * Exports the requested data subjects from the stored visit data.
     *
     * @param array $visits Data subject visit descriptors to export.
     * @return array Export payload grouped by log table and plugin data source.
     */
    public function exportDataSubjects($visits)
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->exportDataSubjects($visits);
    }

    /**
     * Finds data subjects matching a segment across the requested websites.
     *
     * @param string|array $idSite Website ID(s) to query.
     *                             Accepts comma-separated IDs, "all", numeric IDs as strings, or ["all"].
     * @param string|null $segment Custom segment to filter the report.
     *                             Example: "referrerName==example.com"
     *                             Supports AND (;) and OR (,) operators.
     * @return array|\Piwik\DataTable Matching visitor details for sites with visitor logs or profiles enabled.
     */
    public function findDataSubjects($idSite, $segment)
    {
        Piwik::checkUserHasSomeAdminAccess();

        if (!Manager::getInstance()->isPluginActivated('Live')) {
            return [];
        }

        $siteIds = Site::getIdSitesFromIdSitesString($idSite, false, true);
        $siteIdsWithVisitorLogsOrProfilesEnabled = [];

        /*
         * Only retrieve data from sites that have visitor logs or profiles enabled.
         * Live::isVisitorProfileEnabled returns false if either logs or profiles
         * are disabled.
         */
        foreach ($siteIds as $siteId) {
            if (!Piwik::isUserHasViewAccess($siteId)) {
                continue;
            }

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

    /**
     * Schedules anonymization of selected raw visit data.
     *
     * @param string|array $idSites Website ID(s) to anonymize.
     *                              Accepts comma-separated IDs, "all", numeric IDs as strings, or ["all"].
     * @param string $date Date or date range to anonymize.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param bool $anonymizeIp `true` to anonymize visitor IP addresses.
     * @param bool $anonymizeLocation `true` to anonymize stored location data.
     * @param bool $anonymizeUserId `true` to anonymize stored user IDs.
     * @param array $unsetVisitColumns Visit columns to clear during anonymization.
     * @param array $unsetLinkVisitActionColumns Link-visit-action columns to clear during anonymization.
     * @param string $passwordConfirmation Current user password confirmation.
     * @return void
     */
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
            $idSites = Site::getIdSitesFromIdSitesString($idSites, false, true);
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

    /**
     * Returns visit-log columns that can be anonymized manually.
     *
     * @return array<int, array{column_name: string, default_value: mixed}> Available visit columns and their default
     *                                                                     replacement values.
     */
    public function getAvailableVisitColumnsToAnonymize()
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableVisitColumnsToAnonymize();

        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    /**
     * Returns link-visit-action columns that can be anonymized manually.
     *
     * @return array<int, array{column_name: string, default_value: mixed}> Available link-visit-action columns and
     *                                                                     their default replacement values.
     */
    public function getAvailableLinkVisitActionColumnsToAnonymize()
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableLinkVisitActionColumnsToAnonymize();

        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    /**
     * @param array $columns
     * @return array<int, array{column_name: string, default_value: mixed}>
     */
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
     * @internal
     *
     * @param int|null $idSiteSpecific Specific site ID to load settings for, or `null` for global settings.
     * @return array Anonymization settings and related UI metadata.
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
     *
     * Applies IP anonymization settings globally or for a specific website.
     *
     * @param bool $anonymizeIPEnable `true` to enable IP anonymization.
     * @param int $ipAddressMaskLength Number of bytes to mask in stored IP addresses.
     * @param bool $useAnonymizedIpForVisitEnrichment `true` to use anonymized IPs for visit enrichment.
     * @param bool $anonymizeUserId `true` to anonymize stored user IDs.
     * @param bool $anonymizeOrderId `true` to anonymize stored ecommerce order IDs.
     * @param string $anonymizeReferrer Referrer anonymization mode.
     * @param bool $forceCookielessTracking `true` to force cookieless tracking instance-wide. Ignored for
     *                                      site-specific settings.
     * @param bool $randomizeConfigId `true` to randomize visitor config IDs.
     * @param int|null $idSiteSpecific Specific site ID to update, or `null` for global settings.
     * @param bool $useSiteSpecificSettings `true` to keep site-specific settings enabled. If `false` for a
     *                                      site-specific request, the site override is removed and the method returns
     *                                      immediately.
     * @param string $passwordConfirmation Current user password confirmation. Only required when
     *                                     `$randomizeConfigId` is enabled.
     * @return bool `true` after the settings have been updated or the site override has been removed.
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
     *
     * Disables support for the Do Not Track browser header.
     *
     * @return bool `true` after Do Not Track support has been disabled.
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
     *
     * Enables support for the Do Not Track browser header.
     *
     * @return bool `true` after Do Not Track support has been enabled.
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
     *
     * @param int $deleteLowestInterval Minimum number of days between scheduled deletion runs.
     * @param string $passwordConfirmation Current user password confirmation.
     * @return bool `true` after the scheduled report deletion settings have been saved.
     */
    public function setScheduleReportDeletionSettings(
        $deleteLowestInterval = 7,
        #[\SensitiveParameter]
        $passwordConfirmation = ''
    ) {
        Piwik::checkUserHasSuperUserAccess();
        $this->confirmCurrentUserPassword($passwordConfirmation);

        return $this->savePurgeDataSettings(array(
            'delete_logs_schedule_lowest_interval' => (int) $deleteLowestInterval,
        ));
    }

    /**
     * @internal
     *
     * @param int|string $enableDeleteLogs Flag enabling raw log deletion.
     * @param int $deleteLogsOlderThan Delete logs older than this many days. Values below `1` are normalized to `1`.
     * @param string $passwordConfirmation Current user password confirmation.
     * @return bool `true` after the raw log deletion settings have been saved.
     */
    public function setDeleteLogsSettings(
        $enableDeleteLogs = '0',
        $deleteLogsOlderThan = 180,
        #[\SensitiveParameter]
        $passwordConfirmation = ''
    ) {
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
     *
     * @param int|string $enableDeleteReports Flag enabling report deletion.
     * @param int $deleteReportsOlderThan Delete reports older than this many periods. Values below `2` are
     *                                    normalized to `2`.
     * @param int $keepBasic Whether to keep basic metrics.
     * @param int $keepDay Whether to keep day reports.
     * @param int $keepWeek Whether to keep week reports.
     * @param int $keepMonth Whether to keep month reports.
     * @param int $keepYear Whether to keep year reports.
     * @param int $keepRange Whether to keep range reports.
     * @param int $keepSegments Whether to keep segmented reports.
     * @param string $passwordConfirmation Current user password confirmation.
     * @return bool `true` after the report deletion settings have been saved.
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
     * @param string $passwordConfirmation Current user password confirmation.
     * @return void
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
     *
     * Returns the available compliance policies exposed by PrivacyManager.
     *
     * @return array<array<string,string>>
     */
    public function getCompliancePolicies(): array
    {
        return PolicyManager::getAllPoliciesDetails();
    }

    /**
     * @internal
     *
     * @param int|string $idSite Site ID to inspect, or `all` for global compliance status.
     * @param string $complianceType Compliance policy name to inspect.
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
     *
     * @param string $idSite Site ID to update, or `all` for global compliance status.
     * @param string $complianceType Compliance policy name to update.
     * @param bool $enforce `true` to enforce the selected policy, `false` to disable enforcement.
     * @param string|null $passwordConfirmation Current user password confirmation when required.
     * @return bool `true` if the policy is enabled after the update, `false` otherwise.
     */
    public function setComplianceStatus(
        string $idSite,
        string $complianceType,
        bool $enforce,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): bool {
        if (!$this->featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
            throw new Exception('Feature not available');
        }

        Piwik::checkUserHasSuperUserAccess();

        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
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
