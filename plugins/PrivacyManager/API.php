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
use Piwik\Exception\NoPrivilegesException;
use Piwik\Http\BadRequestException;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Config as PiwikConfig;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\Live\Settings\VisitorLogDisabled;
use Piwik\Plugins\PrivacyManager\Compliance\ComplianceSettingsProvider;
use Piwik\Plugins\PrivacyManager\FeatureFlags\GranularPrivacyCompliance;
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
     * One more than the 400 visits the GDPR tools list, so that they can tell when there are more.
     */
    private const DATA_SUBJECTS_LIMIT = 401;

    /**
     * The value of log_visit.consent for a visit whose visitor gave consent.
     */
    private const CONSENT_GIVEN = 1;

    private DataSubjects $gdpr;

    private LogDataAnonymizations $logDataAnonymizations;

    private LogDataAnonymizer $logDataAnonymizer;

    private ComplianceSettingsProvider $complianceSettingsProvider;

    private FeatureFlagManager $featureFlagManager;

    public function __construct(
        DataSubjects $gdpr,
        LogDataAnonymizations $logDataAnonymizations,
        LogDataAnonymizer $logDataAnonymizer,
        ComplianceSettingsProvider $complianceSettingsProvider,
        FeatureFlagManager $featureFlagManager
    ) {
        $this->gdpr = $gdpr;
        $this->logDataAnonymizations = $logDataAnonymizations;
        $this->logDataAnonymizer = $logDataAnonymizer;
        $this->complianceSettingsProvider = $complianceSettingsProvider;
        $this->featureFlagManager = $featureFlagManager;
    }

    /**
     * Checks the visits given to export or delete, and returns them with their website and visit ids as integers.
     *
     * On a website whose compliance policy disables the visitor log, only visits whose visitor gave consent may be
     * exported or deleted, so the whole request is refused when any other visit of such a website is given.
     *
     * @param array<int, VisitDescriptor> $visits
     * @return array<int, VisitDescriptor>
     */
    private function checkDataSubjectVisits(array $visits): array
    {
        BaseValidator::check('visits', $visits, [new VisitsDataSubject()]);

        foreach ($visits as $index => $visit) {
            $visits[$index]['idsite'] = $this->getPositiveIntegerId($visit['idsite'], 'idsite', $index);
            $visits[$index]['idvisit'] = $this->getPositiveIntegerId($visit['idvisit'], 'idvisit', $index);
        }

        Piwik::checkUserHasAdminAccess(array_values(array_unique(array_column($visits, 'idsite'))));

        $this->checkVisitsOfConsentedVisitsOnlySitesHaveConsent($visits);

        return $visits;
    }

    /**
     * @param mixed $id
     * @param int|string $index The visit's key in the request, which a client may send as a string.
     */
    private function getPositiveIntegerId($id, string $key, $index): int
    {
        if (is_int($id) && $id > 0) {
            return $id;
        }

        if (is_string($id) && ctype_digit($id) && (int) $id > 0) {
            return (int) $id;
        }

        throw new BadRequestException('Invalid ' . $key . ' for visit at index ' . $index);
    }

    /**
     * @param array<int, VisitDescriptor> $visits
     */
    private function checkVisitsOfConsentedVisitsOnlySitesHaveConsent(array $visits): void
    {
        $visitsToCheck = [];
        foreach ($visits as $visit) {
            if ($this->isConsentedVisitsOnlySite($visit['idsite'])) {
                $visitsToCheck[] = $visit;
            }
        }

        if (empty($visitsToCheck)) {
            return;
        }

        $consentOfVisits = $this->gdpr->getConsentOfVisits(array_column($visitsToCheck, 'idvisit'));

        foreach ($visitsToCheck as $visit) {
            $stored = $consentOfVisits[$visit['idvisit']] ?? null;

            // a visit that does not exist, or belongs to another website, is refused the same way, so the
            // message does not tell whether it exists
            if (
                null === $stored
                || $stored['idsite'] !== $visit['idsite']
                || $stored['consent'] !== self::CONSENT_GIVEN
            ) {
                throw new NoPrivilegesException(
                    Piwik::translate('PrivacyManager_OnlyConsentedVisitsCanBeExportedOrDeleted'),
                    403
                );
            }
        }
    }

    /**
     * Whether the compliance policy disables the visitor log of the website, in which case the GDPR tools only
     * export or delete the visits of visitors who gave consent.
     *
     * Unlike {@link \Piwik\Plugins\Live\Live::isVisitorProfileLimitedToConsentedVisits()}, which decides whether such a website can be
     * searched, this does not look at the visitor profile settings: a visit of the website that was not found by a
     * search must still not be exported or deleted without consent.
     */
    private function isConsentedVisitsOnlySite(int $idSite): bool
    {
        return VisitorLogDisabled::isEnforced($idSite);
    }

    /**
     * Deletes the requested data subjects from the stored visit data.
     *
     * @param array<int, VisitDescriptor> $visits Data subject visit descriptors to delete.
     *                                            Each entry must contain `idsite` and `idvisit`.
     *                                            On a website whose compliance policy disables the visitor
     *                                            log, every given visit must have been tracked with the
     *                                            visitor's consent, or nothing is deleted.
     * @return array<string, int> Deletion counts keyed by storage area (e.g. log table or plugin name).
     */
    public function deleteDataSubjects(array $visits): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        $visits = $this->checkDataSubjectVisits($visits);

        return $this->gdpr->deleteDataSubjects($visits);
    }

    /**
     * Exports the requested data subjects from the stored visit data.
     *
     * @param array<int, VisitDescriptor> $visits Data subject visit descriptors to export.
     *                                            Each entry must contain `idsite` and `idvisit`.
     *                                            On a website whose compliance policy disables the visitor
     *                                            log, every given visit must have been tracked with the
     *                                            visitor's consent, or nothing is exported.
     * @return array<string, array<int, array<string, mixed>>> Export payload grouped by log table name, each
     *                                                         containing an array of row data.
     */
    public function exportDataSubjects(array $visits): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        $visits = $this->checkDataSubjectVisits($visits);

        return $this->gdpr->exportDataSubjects($visits);
    }

    /**
     * Finds data subjects matching a segment across the requested websites. Only returns results for sites
     * that have visitor logs or profiles enabled, and for sites whose compliance policy disables the visitor log,
     * where only the visits of visitors who gave consent are returned to a user with admin access to the site.
     * Returns at most 401 matching visits, the most recent first.
     *
     * @param int|string|int[] $idSite Website ID(s) to query.
     *                                 Accepts comma-separated IDs, "all", numeric IDs as strings, or ["all"].
     * @param string $segment Segment expression identifying the data subjects to find.
     *                        Example: "referrerName==example.com"
     *                        Supports AND (;) and OR (,) operators.
     * @return array{}|DataTable Matching visitor details with a reduced column set (identity, device,
     *                           location, and browser info), and the consent stored for the visit (1 when the
     *                           visitor consented, 0 when they refused, empty when none is stored). Returns an
     *                           empty array when no sites qualify.
     */
    public function findDataSubjects($idSite, string $segment)
    {
        Piwik::checkUserHasSomeAdminAccess();

        if (!Manager::getInstance()->isPluginActivated('Live')) {
            return [];
        }

        $sites = PrivacyManager::getIdSitesByDataSubjectSearchScope($idSite);

        if (empty($sites['all']) && empty($sites['consentedVisitsOnly'])) {
            return [];
        }

        $result = null;

        if (!empty($sites['all'])) {
            $result = $this->getVisitsOfDataSubjects($sites['all'], $segment);
        }

        if (!empty($sites['consentedVisitsOnly'])) {
            // The consent condition is intersected with the visits rather than appended to the segment, so that
            // no segment given here can widen it. It is part of the query, so the visits of visitors who did not
            // consent do not count towards the limit either.
            $consentedVisits = $this->getVisitsOfDataSubjects(
                $sites['consentedVisitsOnly'],
                $segment,
                'consentState==consented'
            );

            if (null === $result) {
                $result = $consentedVisits;
            } else {
                // Live disables the sort and limit filters on the tables it returns, so the visits of both are
                // merged into a new one
                $visits = new DataTable();
                $visits->addRowsFromArray($result->getRows());
                $visits->addRowsFromArray($consentedVisits->getRows());
                $visits->filter('Sort', ['lastActionTimestamp', 'desc']);
                $visits->filter('Limit', [0, self::DATA_SUBJECTS_LIMIT]);
                $result = $visits;
            }
        }

        $this->addConsentOfVisits($result, $sites['consentedVisitsOnly']);

        $columnsToKeep = [
            'lastActionDateTime',
            'idVisit',
            'idSite',
            'siteName',
            'visitorId',
            'visitIp',
            'userId',
            'consent',
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
     * Describes how the GDPR tools can search the requested websites for data subjects.
     *
     * @param int|string|int[] $idSite Website ID(s) to check.
     *                                 Accepts comma-separated IDs, "all", numeric IDs as strings, or ["all"].
     * @return array{available: bool, consentedVisitsOnly: bool, consentedVisitsOnlyIdSites: int[]} `available` is
     *         true when every requested website the user can view can be searched. `consentedVisitsOnly` is true
     *         when, for at least one of them, only the visits of visitors who gave consent can be found, and
     *         `consentedVisitsOnlyIdSites` lists those websites.
     */
    public function getDataSubjectSearchScope($idSite): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        if (!Manager::getInstance()->isPluginActivated('Live')) {
            return ['available' => false, 'consentedVisitsOnly' => false, 'consentedVisitsOnlyIdSites' => []];
        }

        $sites = PrivacyManager::getIdSitesByDataSubjectSearchScope($idSite);

        return [
            'available' => empty($sites['none']) && (!empty($sites['all']) || !empty($sites['consentedVisitsOnly'])),
            'consentedVisitsOnly' => !empty($sites['consentedVisitsOnly']),
            'consentedVisitsOnlyIdSites' => $sites['consentedVisitsOnly'],
        ];
    }

    /**
     * @param int[] $idSites
     * @param string|false $intersectSegment
     */
    private function getVisitsOfDataSubjects(array $idSites, string $segment, $intersectSegment = false): DataTable
    {
        $parameters = [
            'segment' => $segment,
            'idSite' => $idSites,
            'period' => 'range',
            'date' => '1998-01-01,today',
            'filter_limit' => self::DATA_SUBJECTS_LIMIT,
            'doNotFetchActions' => 1,
        ];

        if (false !== $intersectSegment) {
            $parameters['intersectSegment'] = $intersectSegment;
        }

        /** @var DataTable $visits */
        $visits = Request::processRequest('Live.getLastVisitsDetails', $parameters);

        return $visits;
    }

    /**
     * Adds the consent stored for each visit, and drops any visit of a website where only the visits of visitors
     * who gave consent may be found that is not stored with consent, whatever the query returned.
     *
     * @param int[] $consentedVisitsOnlyIdSites
     */
    private function addConsentOfVisits(DataTable $visits, array $consentedVisitsOnlyIdSites): void
    {
        $consentOfVisits = $this->gdpr->getConsentOfVisits($visits->getColumn('idVisit'));

        foreach ($visits->getRows() as $id => $row) {
            $stored = $consentOfVisits[(int) $row->getColumn('idVisit')] ?? null;
            $consent = null === $stored ? null : $stored['consent'];

            if (
                in_array((int) $row->getColumn('idSite'), $consentedVisitsOnlyIdSites, true)
                && $consent !== self::CONSENT_GIVEN
            ) {
                $visits->deleteRow($id);
                continue;
            }

            // empty when no consent is stored, so that every row has the column
            $row->setColumn('consent', $consent ?? '');
        }
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
     * Whether a privacy config property may be stored with the given value, given the compliance
     * policies enforced for the scope being saved.
     *
     * @param mixed $value
     * @throws \Piwik\Policy\Exceptions\CompliancePolicyViolationException when a policy forbids the value
     */
    private function mayStoreUnderCompliancePolicies(string $settingName, $value, ?int $idSite): bool
    {
        return PolicyManager::checkSettingValueAgainstPolicies(
            $settingName,
            $value,
            $idSite,
            PolicyManager::SETTING_TYPE_CUSTOM
        );
    }

    /**
     * Removes the choices a compliance policy no longer allows from a privacy setting's options,
     * so a field it only bounds keeps offering the values that stay compliant.
     *
     * Handles both option shapes used on this screen: a value => label map, and a list of
     * ['key' => value, ...] entries.
     *
     * @param array<mixed, mixed> $options
     * @return array<mixed, mixed>
     */
    private function filterOptionsAllowedByPolicies(array $options, string $settingName, ?int $idSite): array
    {
        $isKeyedList = isset($options[0]) && is_array($options[0]);
        $values = $isKeyedList ? array_column($options, 'key') : array_keys($options);

        // privacy config properties are addressed through the custom getter mechanism
        $allowedValues = PolicyManager::filterValuesAllowedByPolicies(
            $values,
            $settingName,
            $idSite,
            PolicyManager::SETTING_TYPE_CUSTOM
        );

        if (count($allowedValues) === count($values)) {
            return $options;
        }

        if ($isKeyedList) {
            return array_values(array_filter($options, static function ($option) use ($allowedValues) {
                return in_array($option['key'], $allowedValues, false);
            }));
        }

        return array_intersect_key($options, array_flip($allowedValues));
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
        // the stored values above already reflect any policy override, see
        // PrivacyManager\Config::getOptionValueWithPrivacyComplianceOverride()

        // provide extra settings
        [$trackerFilename, $trackerFileWritable] = $this->getTrackerFileDetails();
        $settings = array_merge($settings, [
            'maskLengthOptions' => $this->filterOptionsAllowedByPolicies(
                PrivacyManager::getMaskLengthOptions(),
                'ipAddressMaskLength',
                $idSite
            ),
            'useAnonymizedIpForVisitEnrichmentOptions' =>
                PrivacyManager::getUseAnonymizedIpForVisitEnrichmentOptions(),
            'referrerAnonymizationOptions' => $this->filterOptionsAllowedByPolicies(
                ReferrerAnonymizer::getAvailableAnonymizationOptions(),
                'anonymizeReferrer',
                $idSite
            ),
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

        if (
            !empty($anonymizeReferrer)
            && !array_key_exists($anonymizeReferrer, ReferrerAnonymizer::getAvailableAnonymizationOptions())
        ) {
            $anonymizeReferrer = '';
        }

        // resolved before anything is stored, so that a value breaking an enforced compliance
        // policy is rejected without leaving the other settings half applied
        $mayStore = [
            'ipAnonymizerEnabled' => $this->mayStoreUnderCompliancePolicies('ipAnonymizerEnabled', $anonymizeIPEnable, $idSite),
            'ipAddressMaskLength' => $this->mayStoreUnderCompliancePolicies('ipAddressMaskLength', $ipAddressMaskLength, $idSite),
            'anonymizeReferrer' => $this->mayStoreUnderCompliancePolicies('anonymizeReferrer', $anonymizeReferrer, $idSite),
            'anonymizeOrderId' => $this->mayStoreUnderCompliancePolicies('anonymizeOrderId', $anonymizeOrderId, $idSite),
        ];

        if ($mayStore['ipAnonymizerEnabled']) {
            if ($anonymizeIPEnable) {
                IPAnonymizer::activate($idSite);
            } else {
                IPAnonymizer::deactivate($idSite);
            }
        }

        $privacyConfig = new Config($idSite);
        $privacyConfig->useAnonymizedIpForVisitEnrichment = $useAnonymizedIpForVisitEnrichment;
        $privacyConfig->anonymizeUserId = $anonymizeUserId;
        $privacyConfig->randomizeConfigId = $randomizeConfigId;

        if ($mayStore['ipAddressMaskLength']) {
            $privacyConfig->ipAddressMaskLength = $ipAddressMaskLength;
        }

        if ($mayStore['anonymizeReferrer']) {
            $privacyConfig->anonymizeReferrer = $anonymizeReferrer;
        }

        if ($mayStore['anonymizeOrderId']) {
            $privacyConfig->anonymizeOrderId = $anonymizeOrderId;
        }

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

        $mayStoreRetention = PolicyManager::checkSettingValueAgainstPolicies(
            'delete_logs_older_than',
            $deleteLogsOlderThan,
            null,
            PolicyManager::SETTING_TYPE_OPTION
        );

        $settings = ['delete_logs_enable' => !empty($enableDeleteLogs)];

        // leaving the retention out entirely keeps whatever the user had configured before the
        // policy started applying, which savePurgeDataSettings() only rewrites when it is given
        if ($mayStoreRetention) {
            $settings['delete_logs_older_than'] = $deleteLogsOlderThan;
        }

        return $this->savePurgeDataSettings($settings);
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
     * @deprecated since Matomo 6.0, use {@link getCompliancePolicySettings()} instead.
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
     * Returns the granular per-setting enforcement configuration and compliance status of a compliance policy.
     *
     * Each returned setting contains its stable identifier, translated texts, current compliance
     * status (`compliant` when the underlying Matomo setting satisfies the requirement on its own,
     * `enforced` when the requirement is only met through policy enforcement, `non_compliant`
     * otherwise) and, for toggleable settings, its current enforcement state.
     *
     * @param int|string $idSite Site ID to inspect, or `all` for the instance-wide view.
     * @param string $compliancePolicy Compliance policy id to inspect, e.g. `cnil_v1`.
     * @return array<string, mixed> Policy details, config control flag, derived whole-policy
     *                              enforcement state and the list of settings.
     * @internal
     *
     */
    public function getCompliancePolicySettings($idSite, string $compliancePolicy): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkGranularComplianceFeatureIsEnabled();

        $policy = PolicyManager::getPolicyByName($compliancePolicy);

        if (is_null($policy)) {
            throw new Exception('Invalid compliance policy');
        }

        $idSite = $this->resolveCompliancePolicyIdSite($idSite);

        return $this->complianceSettingsProvider->getPolicySettings($policy, $idSite);
    }

    /**
     * @param int|string $idSite
     * @throws Exception when the site does not exist
     */
    private function resolveCompliancePolicyIdSite($idSite): ?int
    {
        if ($idSite === 'all') {
            return null;
        }

        $idSite = intval($idSite);
        new Site($idSite); // throws for unknown site ids

        return $idSite;
    }

    /**
     * Sets the enforcement state of individual compliance policy settings.
     *
     * Only the settings present in `settingValues` are changed; all other settings keep
     * their current enforcement state. Enforcing a setting overrides the underlying Matomo
     * setting at read time, the underlying configuration itself is never modified.
     *
     * @param int|string $idSite Site ID to update, or `all` for the instance-wide state.
     * @param string $compliancePolicy Compliance policy id to update, e.g. `cnil_v1`.
     * @param array<string, int|string|bool> $settingValues Map of policy setting id => whether to
     *                                                      enforce it, e.g. settingValues[PrivacyManager.IPAnonymisation]=1
     * @param string|null $passwordConfirmation Current user password confirmation when required.
     * @return array<string, mixed> The updated payload, as returned by {@link getCompliancePolicySettings()}.
     * @internal
     *
     */
    public function setCompliancePolicySettings(
        $idSite,
        string $compliancePolicy,
        array $settingValues,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): array {
        $policy = $this->checkCompliancePolicySettingsWriteAccess($compliancePolicy, $passwordConfirmation);

        $idSite = $this->resolveCompliancePolicyIdSite($idSite);

        $before = $this->complianceSettingsProvider->getPolicySettings($policy, $idSite);

        PolicyManager::setPolicySettingEnforcedStatuses($policy, $settingValues, $idSite);

        $after = $this->complianceSettingsProvider->getPolicySettings($policy, $idSite);

        $this->postCompliancePolicySettingsUpdated($policy, $idSite, $before, $after);

        return $after;
    }

    /**
     * Enables enforcement of every toggleable setting of a compliance policy at once.
     *
     * This enforces each of the policy's toggleable settings individually; every setting
     * stays individually toggleable afterwards. Settings managed outside the compliance
     * page are left untouched.
     *
     * @param int|string $idSite Site ID to update, or `all` for the instance-wide state.
     * @param string $compliancePolicy Compliance policy id to enforce, e.g. `cnil_v1`.
     * @param string|null $passwordConfirmation Current user password confirmation when required.
     * @return array<string, mixed> The updated payload, as returned by {@link getCompliancePolicySettings()}.
     * @internal
     *
     */
    public function enforceCompliancePolicySettings(
        $idSite,
        string $compliancePolicy,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): array {
        $policy = $this->checkCompliancePolicySettingsWriteAccess($compliancePolicy, $passwordConfirmation);

        $idSite = $this->resolveCompliancePolicyIdSite($idSite);

        $settingValues = [];
        foreach (PolicyManager::getAllControlledSettings($policy, $idSite) as $settingClass) {
            if ($settingClass::isExternallyManagedByPolicyPage()) {
                continue;
            }
            $settingValues[$settingClass::getPolicySettingId()] = true;
        }

        $before = $this->complianceSettingsProvider->getPolicySettings($policy, $idSite);

        PolicyManager::setPolicySettingEnforcedStatuses($policy, $settingValues, $idSite);

        $after = $this->complianceSettingsProvider->getPolicySettings($policy, $idSite);

        $this->postCompliancePolicySettingsUpdated($policy, $idSite, $before, $after);

        return $after;
    }

    /**
     * Announces the compliance policy settings a request has just changed, so an audit trail
     * can record who changed what.
     *
     * Nothing is posted when the request left every setting as it was, which keeps a save
     * that repeats the current state out of the audit trail. A request that fails never
     * reaches this point, and does not need to: the write is validated upfront and is
     * all-or-nothing, so a failed request leaves nothing behind to announce.
     *
     * @param class-string<CompliancePolicy> $policy
     * @param array<string, mixed> $before payload taken before the settings were written
     * @param array<string, mixed> $after payload taken after the settings were written
     */
    private function postCompliancePolicySettingsUpdated(
        string $policy,
        ?int $idSite,
        array $before,
        array $after
    ): void {
        $changes = $this->complianceSettingsProvider->diffPolicySettings($before, $after);

        if (empty($changes)) {
            return;
        }

        /**
         * Triggered after a request changed the enforcement state of one or more settings of a
         * compliance policy, for example through the privacy compliance page.
         *
         * The event is only triggered when something actually changed, and deliberately carries
         * nothing but the resulting compliance state: no request parameters, and therefore no
         * password confirmation or authentication token, are passed on.
         *
         * `changes` describes the difference between the compliance state before and after the
         * request, which is not the same thing as what this caller did: a setting's status also
         * follows the underlying Matomo setting, so a change another request made to one of those
         * in the meantime is part of the difference too. Treat it as the resulting state
         * difference rather than as an attribution of every entry to the acting user.
         *
         * **Example**
         *
         *     Piwik::addAction('PrivacyManager.compliancePolicySettingsUpdated', function ($update) {
         *         foreach ($update['changes'] as $change) {
         *             Log::info($change['id'] . ' is now ' . $change['status']);
         *         }
         *     });
         *
         * @param array $update Details of the change, containing the following data:
         *
         *                      - **policy**: The id of the compliance policy that was changed,
         *                                    e.g. `cnil_v1`.
         *                      - **idSite**: The website the policy was changed for, or `null`
         *                                    when the instance wide state was changed.
         *                      - **policyEnforced**: Whether every toggleable setting of the
         *                                            policy is enforced after the change.
         *                      - **changes**: The settings whose enforcement state or compliance
         *                                     status changed, each holding its `id`, `name`,
         *                                     `enforced`, `previousEnforced`, `status` and
         *                                     `previousStatus`. `id` is the stable identifier to
         *                                     persist; `name` is a translated title for display
         *                                     and changes with the interface language.
         */
        try {
            Piwik::postEvent('PrivacyManager.compliancePolicySettingsUpdated', [[
                'policy' => $policy::getName(),
                'idSite' => $idSite,
                'policyEnforced' => !empty($after['policyEnforced']),
                'changes' => $changes,
            ]]);
        } catch (\Throwable $e) {
            // the settings are already written by the time the change is announced, so a listener
            // that fails must not report the save as failed: the caller would retry, find nothing
            // left to change, and the change could then never be announced at all
            StaticContainer::get(LoggerInterface::class)->warning(
                'A listener of PrivacyManager.compliancePolicySettingsUpdated failed: {message}',
                ['message' => $e->getMessage(), 'exception' => $e]
            );
        }
    }

    /**
     * @return class-string<CompliancePolicy>
     */
    private function checkCompliancePolicySettingsWriteAccess(
        string $compliancePolicy,
        #[\SensitiveParameter]
        ?string $passwordConfirmation
    ): string {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkGranularComplianceFeatureIsEnabled();

        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        $policy = PolicyManager::getPolicyByName($compliancePolicy);

        if (is_null($policy)) {
            throw new Exception('Invalid compliance policy');
        }

        if (PolicyManager::isPolicyConfigControlled($policy)) {
            throw new Exception('The compliance policy is controlled by the config file and cannot be changed');
        }

        return $policy;
    }

    private function checkGranularComplianceFeatureIsEnabled(): void
    {
        if (!$this->featureFlagManager->isFeatureActive(GranularPrivacyCompliance::class)) {
            throw new Exception('Granular compliance configuration is not enabled');
        }
    }

    /**
     * Enables or disables enforcement of a compliance policy.
     *
     * @param string $idSite Site ID to update, or `all` for global compliance status.
     * @param string $complianceType Compliance policy name to update.
     * @param bool $enforce `true` to enforce the selected policy, `false` to disable enforcement.
     * @param string|null $passwordConfirmation Current user password confirmation when required.
     * @return bool `true` if the policy is enabled after the update, `false` otherwise.
     * @deprecated since Matomo 6.0, use {@link setCompliancePolicySettings()} or
     *             {@link enforceCompliancePolicySettings()} instead.
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
