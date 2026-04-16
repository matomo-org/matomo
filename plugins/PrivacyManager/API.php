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
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Config as PiwikConfig;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Plugins\Live\Live;
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
 * @phpstan-type VisitDescriptor array{idsite: int, idvisit: int}
 * @phpstan-type AnonymizableColumn array{column_name: string, default_value: mixed}
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

    public function __construct(
        DataSubjects $gdpr,
        LogDataAnonymizations $logDataAnonymizations,
        LogDataAnonymizer $logDataAnonymizer
    ) {
        $this->gdpr = $gdpr;
        $this->logDataAnonymizations = $logDataAnonymizations;
        $this->logDataAnonymizer = $logDataAnonymizer;
    }

    /**
     * @param array<int, VisitDescriptor> $visits
     */
    private function checkDataSubjectVisits(array $visits): void
    {
        BaseValidator::check('visits', $visits, [new VisitsDataSubject()]);

        $idSites = [];
        foreach ($visits as $visit) {
            $idSites[] = $visit['idsite'];
        }
        Piwik::checkUserHasAdminAccess($idSites);
    }

    /**
     * Deletes the requested data subjects from the stored visit data.
     *
     * @param array<int, VisitDescriptor> $visits Data subject visit descriptors to delete.
     *                                            Each entry must contain `idsite` and `idvisit`.
     * @return array<string, int> Deletion counts keyed by storage area (e.g. log table or plugin name).
     */
    public function deleteDataSubjects(array $visits): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->deleteDataSubjects($visits);
    }

    /**
     * Exports the requested data subjects from the stored visit data.
     *
     * @param array<int, VisitDescriptor> $visits Data subject visit descriptors to export.
     *                                            Each entry must contain `idsite` and `idvisit`.
     * @return array<string, array<int, array<string, mixed>>> Export payload grouped by log table name, each
     *                                                         containing an array of row data.
     */
    public function exportDataSubjects(array $visits): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkDataSubjectVisits($visits);

        return $this->gdpr->exportDataSubjects($visits);
    }

    /**
     * Finds data subjects matching a segment across the requested websites. Only returns results for sites
     * that have visitor logs or profiles enabled. Returns at most 401 matching visits.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 Accepts comma-separated IDs, "all", numeric IDs as strings, or ["all"].
     * @param string $segment Segment expression identifying the data subjects to find.
     *                        Example: "referrerName==example.com"
     *                        Supports AND (;) and OR (,) operators.
     * @return array{}|DataTable Matching visitor details with a reduced column set (identity, device,
     *                           location, and browser info). Returns an empty array when no sites qualify.
     */
    public function findDataSubjects($idSite, string $segment)
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

        /** @var DataTable $result */
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
     * Schedules anonymization of selected raw visit data. The anonymization is queued and processed
     * asynchronously by a scheduled task.
     *
     * @param int|string|int[] $idSites Website ID(s) to anonymize.
     *                                 - Single site ID (e.g. 1)
     *                                 - Multiple site IDs (e.g. [1, 4, 5])
     *                                 - Comma-separated list ("1,4,5")
     *                                 An empty value or "all" schedules anonymization for all websites.
     * @param string $date Date or date range to anonymize.
     *                     'YYYY-MM-DD', magic keywords (today, yesterday, lastWeek, lastMonth, lastYear),
     *                     or date range (ie, 'YYYY-MM-DD,YYYY-MM-DD', lastX, previousX).
     * @param bool $anonymizeIp `true` to anonymize visitor IP addresses.
     * @param bool $anonymizeLocation `true` to anonymize stored location data.
     * @param bool $anonymizeUserId `true` to anonymize stored user IDs.
     * @param string[] $unsetVisitColumns Visit column names to clear during anonymization.
     * @param string[] $unsetLinkVisitActionColumns Link-visit-action column names to clear during anonymization.
     * @param string $passwordConfirmation Current user password confirmation.
     */
    public function anonymizeSomeRawData(
        $idSites,
        string $date,
        $anonymizeIp = false,
        $anonymizeLocation = false,
        $anonymizeUserId = false,
        $unsetVisitColumns = [],
        $unsetLinkVisitActionColumns = [],
        #[\SensitiveParameter]
        string $passwordConfirmation = ''
    ): void {
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
     * @return AnonymizableColumn[] Available visit columns and their default replacement values.
     */
    public function getAvailableVisitColumnsToAnonymize(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableVisitColumnsToAnonymize();

        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    /**
     * Returns link-visit-action columns that can be anonymized manually.
     *
     * @return AnonymizableColumn[] Available link-visit-action columns and their default replacement values.
     */
    public function getAvailableLinkVisitActionColumnsToAnonymize(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $columns = $this->logDataAnonymizer->getAvailableLinkVisitActionColumnsToAnonymize();

        return $this->formatAvailableColumnsToAnonymize($columns);
    }

    /**
     * @param array<string, mixed> $columns
     * @return AnonymizableColumn[]
     */
    private function formatAvailableColumnsToAnonymize(array $columns): array
    {
        ksort($columns);
        $formatted = [];
        foreach ($columns as $column => $default) {
            $formatted[] = [
                'column_name' => $column,
                'default_value' => $default,
            ];
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
     * Returns the current anonymization and privacy settings.
     *
     * @param int|null $idSiteSpecific Specific site ID to load settings for, or `null` for global settings.
     * @return array<string, mixed> Anonymization settings including mask length options,
     *                              referrer anonymization options, and tracker file details.
     * @internal
     *
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
    ): bool {
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
    public function deactivateDoNotTrack(): bool
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
    public function activateDoNotTrack(): bool
    {
        Piwik::checkUserHasSuperUserAccess();

        $dntChecker = new DoNotTrackHeaderChecker();
        $dntChecker->activate();

        return true;
    }

    /**
     * Configures the minimum interval between scheduled data deletion runs.
     *
     * @param int $deleteLowestInterval Minimum number of days between scheduled deletion runs.
     * @param string $passwordConfirmation Current user password confirmation.
     * @return bool `true` after the settings have been saved.
     * @internal
     *
     */
    public function setScheduleReportDeletionSettings(
        $deleteLowestInterval = 7,
        #[\SensitiveParameter]
        string $passwordConfirmation = ''
    ): bool {
        Piwik::checkUserHasSuperUserAccess();
        $this->confirmCurrentUserPassword($passwordConfirmation);

        return $this->savePurgeDataSettings([
            'delete_logs_schedule_lowest_interval' => (int) $deleteLowestInterval,
        ]);
    }

    /**
     * Configures automatic raw log deletion settings.
     *
     * @param int|string $enableDeleteLogs Flag enabling raw log deletion.
     * @param int $deleteLogsOlderThan Delete logs older than this many days. Values below `1` are normalized to `1`.
     * @param string $passwordConfirmation Current user password confirmation.
     * @return bool `true` after the settings have been saved.
     * @internal
     *
     */
    public function setDeleteLogsSettings(
        $enableDeleteLogs = '0',
        $deleteLogsOlderThan = 180,
        #[\SensitiveParameter]
        string $passwordConfirmation = ''
    ): bool {
        Piwik::checkUserHasSuperUserAccess();
        $this->confirmCurrentUserPassword($passwordConfirmation);

        $deleteLogsOlderThan = (int) $deleteLogsOlderThan;
        if ($deleteLogsOlderThan < 1) {
            $deleteLogsOlderThan = 1;
        }

        return $this->savePurgeDataSettings([
            'delete_logs_enable' => !empty($enableDeleteLogs),
            'delete_logs_older_than' => $deleteLogsOlderThan,
        ]);
    }

    /**
     * Configures automatic report deletion settings.
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
     * @return bool `true` after the settings have been saved.
     * @internal
     *
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
        string $passwordConfirmation = ''
    ): bool {
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
     */
    public function executeDataPurge(
        #[\SensitiveParameter]
        string $passwordConfirmation
    ): void {
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
     * Returns the available compliance policies.
     *
     * @return array<int, array<string, string>> List of compliance policy details.
     * @internal
     *
     */
    public function getCompliancePolicies(): array
    {
        return PolicyManager::getAllPoliciesDetails();
    }

    /**
     * Returns the compliance status for a given policy and site.
     *
     * @param int|string $idSite Site ID to inspect, or `all` for global compliance status.
     * @param string $complianceType Compliance policy name to inspect.
     * @return array<string, bool|array<int, array<string, string>>> Compliance status including enforcement state,
     *                                                               config control flag, and requirement details.
     * @internal
     *
     */
    public function getComplianceStatus($idSite, string $complianceType): array
    {
        if ($idSite === 'all') {
            $idSite = null;
        } else {
            $idSite = intval($idSite);
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
     * Enables or disables enforcement of a compliance policy.
     *
     * @param string $idSite Site ID to update, or `all` for global compliance status.
     * @param string $complianceType Compliance policy name to update.
     * @param bool $enforce `true` to enforce the selected policy, `false` to disable enforcement.
     * @param string|null $passwordConfirmation Current user password confirmation when required.
     * @return bool `true` if the policy is enabled after the update, `false` otherwise.
     * @internal
     *
     */
    public function setComplianceStatus(
        string $idSite,
        string $complianceType,
        bool $enforce,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): bool {
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

    /**
     * @param array<string, mixed> $settings
     */
    private function savePurgeDataSettings(array $settings): bool
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkDataPurgeAdminSettingsIsEnabled();

        PrivacyManager::savePurgeDataSettings($settings);

        return true;
    }

    private function checkDataPurgeAdminSettingsIsEnabled(): void
    {
        if (!Controller::isDataPurgeSettingsEnabled()) {
            throw new \Exception("Configuring deleting raw data and report data has been disabled by Matomo admins.");
        }
    }
}
