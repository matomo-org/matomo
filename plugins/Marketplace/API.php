<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Exception;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Plugins as MarketplacePlugins;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\FeatureFlags\PluginRequests;
use Piwik\Plugins\Marketplace\Plugins\InvalidLicenses;
use Piwik\Plugins\Marketplace\PluginTrial\RequestHistory;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Plugins\UsersManager\Validators\AllowedEmailDomain;
use Piwik\Plugins\UsersManager\Validators\Email;
use Piwik\Validators\CharacterLength;
use Piwik\Validators\Exception as ValidatorException;
use Piwik\Validators\NotEmpty;

/**
 * The Marketplace API lets you manage your license key so you can download & install in one-click <a target="_blank" rel="noreferrer" href="https://matomo.org/recommends/premium-plugins/">paid premium plugins</a> you have subscribed to.
 *
 * @method static \Piwik\Plugins\Marketplace\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private const MAX_NOTE_LENGTH = 1000;

    private const MAX_HISTORY_LIMIT = 100;

    private Client $marketplaceClient;

    private Service $marketplaceService;

    private InvalidLicenses $expired;

    private PluginManager $pluginManager;

    private Environment $environment;

    private PluginTrialService $pluginTrialService;

    private FeatureFlagManager $featureFlagManager;

    private RequestHistory $requestHistory;

    public function __construct(
        Service $service,
        Client $client,
        InvalidLicenses $expired,
        PluginManager $pluginManager,
        Environment $environment,
        PluginTrialService $pluginTrialService,
        FeatureFlagManager $featureFlagManager,
        RequestHistory $requestHistory
    ) {
        $this->marketplaceService = $service;
        $this->marketplaceClient  = $client;
        $this->expired = $expired;
        $this->pluginManager = $pluginManager;
        $this->environment = $environment;
        $this->pluginTrialService = $pluginTrialService;
        $this->featureFlagManager = $featureFlagManager;
        $this->requestHistory = $requestHistory;
    }

    /**
     * @internal
     */
    public function createAccount(string $email): bool
    {
        Piwik::checkUserHasSuperUserAccess();

        $licenseKey = (new LicenseKey())->get();

        if (!empty($licenseKey)) {
            // not translated to allow special handling in frontend
            throw new Exception('Marketplace_CreateAccountErrorLicenseExists');
        }

        $notEmptyValidator = new NotEmpty();
        $notEmptyValidator->validate($email);

        try {
            $emailValidator = new Email();
            $emailValidator->validate($email);
        } catch (ValidatorException $e) {
            // rethrow with changed message
            throw new ValidatorException(
                Piwik::translate('Marketplace_CreateAccountErrorEmailInvalid', $email)
            );
        }

        // Ensure the provided email uses a domain that is allowed (if configured)
        $systemSettings = new SystemSettings();
        $allowedDomainsValidator = new AllowedEmailDomain($systemSettings);
        $allowedDomainsValidator->validate($email);

        try {
            /** @var array $result */
            $result = $this->marketplaceService->fetch(
                'createAccount',
                [],
                [
                    'email' => $email,
                ],
                true,
                false
            );
        } catch (Service\Exception $e) {
            // not translated to allow special handling in frontend
            throw new Exception('Marketplace_CreateAccountErrorAPI');
        }

        $this->marketplaceClient->clearAllCacheEntries();
        $this->expired->clearCache();

        $licenseKey = trim($result['data']['license_key'] ?? '');
        $status = $result['status'];

        if (200 !== $status || empty($licenseKey)) {
            switch ($status) {
                case 400:
                    $message = Piwik::translate('Marketplace_CreateAccountErrorAPIEmailInvalid');
                    break;

                case 409:
                    $message = Piwik::translate('Marketplace_CreateAccountErrorAPIEmailExists', $email);
                    break;

                default:
                    // not translated to allow special handling in frontend
                    $message = 'Marketplace_CreateAccountErrorAPI';
                    break;
            }

            throw new Exception($message);
        }

        $this->setLicenseKey($licenseKey);

        return true;
    }

    /**
     * Deletes an existing license key if one is set.
     *
     * @return bool `true` after the stored license key has been removed.
     */
    public function deleteLicenseKey(): bool
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->setLicenseKey(null);
        return true;
    }

    /**
     * @unsanitized
     * @internal
     */
    public function requestTrial(string $pluginName, string $message = ''): bool
    {
        Piwik::checkUserIsNotAnonymous();

        if (Piwik::hasUserSuperUserAccess()) {
            throw new Exception('Cannot request trial as a super user');
        }

        $message = $this->checkNote($message);
        $pluginInfo = $this->getRequestablePluginInfo($pluginName);
        $this->checkNotRequestedByCurrentUser($pluginInfo['name']);

        $this->pluginTrialService->request(
            $pluginInfo['name'],
            $pluginInfo['displayName'] ?: $pluginInfo['name'],
            RequestHistory::REQUEST_TYPE_TRIAL,
            $message
        );

        return true;
    }

    /**
     * Asks the super users to install a free plugin.
     *
     * @unsanitized
     * @internal
     */
    public function requestInstall(string $pluginName, string $message = ''): bool
    {
        Piwik::checkUserIsNotAnonymous();
        $this->checkPluginRequestsActive();

        if (Piwik::hasUserSuperUserAccess()) {
            throw new Exception('Cannot request install as a super user');
        }

        if (!CorePluginsAdmin::isPluginsAdminEnabled()) {
            throw new Exception('Plugins cannot be installed on this Matomo, so cannot be requested');
        }

        $message = $this->checkNote($message);
        $pluginInfo = $this->getRequestablePluginInfo($pluginName);

        if (empty($pluginInfo['isFree'])) {
            throw new Exception('Only free plugins can be requested for install: ' . $pluginInfo['name']);
        }

        if ($this->pluginManager->isPluginInstalled($pluginInfo['name'], true)) {
            throw new Exception('The plugin is already installed: ' . $pluginInfo['name']);
        }

        $this->checkNotRequestedByCurrentUser($pluginInfo['name']);

        $this->pluginTrialService->request(
            $pluginInfo['name'],
            $pluginInfo['displayName'] ?: $pluginInfo['name'],
            RequestHistory::REQUEST_TYPE_INSTALL,
            $message
        );

        return true;
    }

    /**
     * Declines all pending requests for a plugin.
     *
     * @unsanitized
     * @internal
     * @return int the number of requests declined
     */
    public function declinePluginRequest(string $pluginName, string $note = ''): int
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkPluginRequestsActive();

        $note = $this->checkNote($note);
        $this->checkHasPendingRequests($pluginName);

        return $this->pluginTrialService->decide($pluginName, RequestHistory::DECISION_DECLINED, $note);
    }

    /**
     * Starts a free trial of a requested plugin, and approves all of its pending requests once the trial started.
     *
     * @unsanitized
     * @internal
     * @return int the number of requests approved
     */
    public function approvePluginTrial(string $pluginName): int
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkPluginRequestsActive();
        // checked first, so no trial is started for requests another super user has already decided on
        $this->checkHasPendingRequests($pluginName);

        // free plugins have no trial, and are installed instead
        $pluginInfo = $this->marketplaceClient->getPluginInfo($pluginName);
        if (!empty($pluginInfo['isFree'])) {
            throw new Exception(Piwik::translate('Marketplace_FreePluginHasNoTrial'));
        }

        $this->startFreeTrial($pluginName);

        // the trial is running now, so the requests another super user declined while it was started are left declined
        // rather than reporting a failure for a trial that started
        return $this->pluginTrialService->decide($pluginName, RequestHistory::DECISION_APPROVED, null, false);
    }

    /**
     * Requests are also off when plugin_trial_request_expiration_in_days = -1, which stores none
     */
    /**
     * Returns the current user's plugin requests, newest first.
     *
     * @internal
     * @return list<array{pluginName: string, displayName: string, requestType: string, requestedAt: string,
     *                    message: string|null, status: string, decisionNote: string|null}>
     *         requestedAt in UTC as `Y-m-d H:i:s`; status `pending`, `approved` or `declined`; decisionNote is the
     *         Super User's note, only for a declined request
     */
    public function getMyPluginRequests(): array
    {
        Piwik::checkUserIsNotAnonymous();
        $this->checkPluginRequestsActive();

        $requests = $this->requestHistory->getRequestsOfLogin(Piwik::getCurrentUserLogin());
        $displayNames = $this->getDisplayNames(array_column($requests, 'plugin_name'));

        return array_map(fn (array $request) => [
            'pluginName' => $request['plugin_name'],
            'displayName' => $displayNames[$request['plugin_name']],
            'requestType' => $request['request_type'],
            'requestedAt' => $request['ts_requested'],
            'message' => $request['message'],
            'status' => $request['decision'] ?? 'pending',
            'decisionNote' => $request['decision'] === RequestHistory::DECISION_DECLINED ? $request['note'] : null,
        ], $requests);
    }

    /**
     * Returns the pending plugin requests, one entry per plugin with the most recently requested first.
     *
     * @internal
     * @return list<array{pluginName: string, displayName: string, isFree: bool|null, priceFrom: array<string, mixed>|null,
     *                    requestType: string, numRequests: int, lastRequestedAt: string,
     *                    requesters: list<array{login: string|null, email: string|null, requestedAt: string, message: string|null}>}>
     *         isFree and priceFrom are null when the Marketplace cannot be reached; priceFrom is the shop variation the
     *         plugin is sold from; requestType is that of the latest request; a deleted requester has no login or email;
     *         dates in UTC as `Y-m-d H:i:s`
     */
    public function getPendingPluginRequests(): array
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkPluginRequestsActive();

        $plugins = [];
        foreach ($this->requestHistory->getPendingRequests() as $request) {
            $pluginName = $request['plugin_name'];

            // newest first, so the first request seen of a plugin is its latest
            $plugins[$pluginName] ??= [
                'pluginName' => $pluginName,
                'requestType' => $request['request_type'],
                'lastRequestedAt' => $request['ts_requested'],
                'requesters' => [],
            ];
            $plugins[$pluginName]['requesters'][] = [
                'login' => $request['login'],
                'email' => $request['email'],
                'requestedAt' => $request['ts_requested'],
                'message' => $request['message'],
            ];
        }

        $pluginInfos = $this->marketplaceClient->getInfoOfPlugins(array_keys($plugins), true);

        $pending = [];
        foreach ($plugins as $pluginName => $plugin) {
            $pluginInfo = $pluginInfos[$pluginName] ?? null;

            $pending[] = [
                'pluginName' => $pluginName,
                'displayName' => ($pluginInfo['displayName'] ?? '') ?: $pluginName,
                'isFree' => $pluginInfo === null ? null : !empty($pluginInfo['isFree']),
                'priceFrom' => $pluginInfo === null ? null : MarketplacePlugins::getPriceFrom($pluginInfo),
                'requestType' => $plugin['requestType'],
                'numRequests' => count($plugin['requesters']),
                'lastRequestedAt' => $plugin['lastRequestedAt'],
                'requesters' => $plugin['requesters'],
            ];
        }

        usort($pending, fn (array $a, array $b) => strcmp($b['lastRequestedAt'], $a['lastRequestedAt']));

        return $pending;
    }

    /**
     * Returns one page of the decisions on plugin requests, newest first.
     *
     * @internal
     * @param int $offset the number of decisions to skip
     * @param int $limit the number of decisions to return, at most 100
     * @return array{total: int, decisions: list<array{pluginName: string, displayName: string, numRequests: int,
     *                                                 decision: string, decidedBy: string|null, note: string|null,
     *                                                 decidedAt: string}>}
     *         decidedBy is null for a decision Matomo made when the plugin was activated, or by a deleted user;
     *         decidedAt in UTC as `Y-m-d H:i:s`
     */
    public function getPluginRequestHistory(int $offset = 0, int $limit = 20): array
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkPluginRequestsActive();

        if ($offset < 0 || $limit < 1 || $limit > self::MAX_HISTORY_LIMIT) {
            throw new Exception('The offset must be 0 or more, and the limit between 1 and ' . self::MAX_HISTORY_LIMIT);
        }

        $decisions = $this->requestHistory->getDecisions($offset, $limit);
        $displayNames = $this->getDisplayNames(array_column($decisions, 'plugin_name'));

        return [
            'total' => $this->requestHistory->getNumDecisions(),
            'decisions' => array_map(fn (array $decision) => [
                'pluginName' => $decision['plugin_name'],
                'displayName' => $displayNames[$decision['plugin_name']],
                'numRequests' => (int) $decision['num_requests'],
                'decision' => $decision['decision'],
                'decidedBy' => $decision['decided_by'],
                'note' => $decision['note'],
                'decidedAt' => $decision['ts_decided'],
            ], $decisions),
        ];
    }

    /**
     * Only uses the cached Marketplace lists, so a list of many plugins sends no request per plugin
     *
     * @param string[] $pluginNames
     * @return array<string, string> keyed by plugin name, the plugin name itself when no display name is known
     */
    private function getDisplayNames(array $pluginNames): array
    {
        $pluginInfos = $this->marketplaceClient->getInfoOfPlugins($pluginNames, false);

        $displayNames = [];
        foreach ($pluginNames as $pluginName) {
            $displayNames[$pluginName] = ($pluginInfos[$pluginName]['displayName'] ?? '') ?: $pluginName;
        }

        return $displayNames;
    }

    private function checkPluginRequestsActive(): void
    {
        if (
            !$this->featureFlagManager->isFeatureActive(PluginRequests::class)
            || !$this->pluginTrialService->isEnabled()
        ) {
            throw new Exception('Plugin requests are not enabled');
        }
    }

    private function checkValidPluginName(string $pluginName): void
    {
        if (!$this->pluginManager->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }
    }

    private function checkHasPendingRequests(string $pluginName): void
    {
        $this->checkValidPluginName($pluginName);

        // requests Matomo did not approve on activation, eg as Matomo before 6.1 still served it, are approved now
        if ($this->pluginManager->isPluginActivated($pluginName)) {
            $this->pluginTrialService->cancelRequest($pluginName);
        }

        if (!$this->pluginTrialService->hasPendingRequests($pluginName)) {
            throw new Exception(Piwik::translate('Marketplace_NoPendingPluginRequests'));
        }
    }

    /**
     * A user requests each plugin once, also after their request was declined
     */
    private function checkNotRequestedByCurrentUser(string $pluginName): void
    {
        if ($this->pluginTrialService->wasRequested($pluginName)) {
            throw new Exception(Piwik::translate('Marketplace_PluginAlreadyRequested'));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getRequestablePluginInfo(string $pluginName): array
    {
        $this->checkValidPluginName($pluginName);

        $pluginInfo = $this->marketplaceClient->getPluginInfo($pluginName);

        if (empty($pluginInfo['name'])) {
            throw new Exception('Unable to find plugin with given name: ' . $pluginName);
        }

        return $pluginInfo;
    }

    /**
     * @return string|null null for an empty note
     */
    private function checkNote(string $note): ?string
    {
        $note = trim($note);
        (new CharacterLength(null, self::MAX_NOTE_LENGTH))->validate($note);

        return $note === '' ? null : $note;
    }

    /**
     * @internal
     */
    public function startFreeTrial(string $pluginName): bool
    {
        Piwik::checkUserHasSuperUserAccess();

        if (!$this->pluginManager->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given');
        }

        $licenseKey = (new LicenseKey())->get();

        $this->marketplaceService->authenticate($licenseKey);

        /** @var array $result */
        $result = $this->marketplaceService->fetch(
            'plugins/' . $pluginName . '/freeTrial',
            [
                'num_users' => $this->environment->getNumUsers(),
                'num_websites' => $this->environment->getNumWebsites(),
            ],
            [],
            true,
            false
        );

        $this->marketplaceClient->clearAllCacheEntries();
        $this->expired->clearCache();

        if (
            201 !== $result['status']
            || !is_string($result['data'])
            || '' !== trim($result['data'])
        ) {
            // We expect an exact empty 201 response from this API
            // Anything different should be an error
            throw new Exception(Piwik::translate('Marketplace_TrialStartErrorAPI'));
        }

        return true;
    }

    /**
     * Saves the given license key in case the key is actually valid (exists on the Matomo Marketplace and is not
     * yet expired).
     *
     * @param string $licenseKey Marketplace license key to validate and store.
     * @return bool `true` after the license key has been validated and saved.
     */
    public function saveLicenseKey(string $licenseKey): bool
    {
        Piwik::checkUserHasSuperUserAccess();

        $licenseKey = trim($licenseKey);

        // we are currently using the Marketplace service directly to 1) change LicenseKey and 2) not use any cache
        $this->marketplaceService->authenticate($licenseKey);

        try {
            /** @var array $consumer */
            $consumer = $this->marketplaceService->fetch('consumer/validate', array());
        } catch (Api\Service\Exception $e) {
            if ($e->getCode() === Api\Service\Exception::HTTP_ERROR) {
                throw $e;
            }

            $consumer = [];
        }

        if (empty($consumer['isValid'])) {
            throw new Exception(Piwik::translate('Marketplace_ExceptionLinceseKeyIsNotValid'));
        }

        $this->setLicenseKey($licenseKey);

        return true;
    }

    private function setLicenseKey(?string $licenseKey): void
    {
        $key = new LicenseKey();
        $key->set($licenseKey);

        $this->marketplaceClient->clearAllCacheEntries();
        $this->expired->clearCache();
    }
}
