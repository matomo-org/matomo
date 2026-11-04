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

    private Client $marketplaceClient;

    private Service $marketplaceService;

    private InvalidLicenses $expired;

    private PluginManager $pluginManager;

    private Environment $environment;

    private PluginTrialService $pluginTrialService;

    private FeatureFlagManager $featureFlagManager;

    public function __construct(
        Service $service,
        Client $client,
        InvalidLicenses $expired,
        PluginManager $pluginManager,
        Environment $environment,
        PluginTrialService $pluginTrialService,
        FeatureFlagManager $featureFlagManager
    ) {
        $this->marketplaceService = $service;
        $this->marketplaceClient  = $client;
        $this->expired = $expired;
        $this->pluginManager = $pluginManager;
        $this->environment = $environment;
        $this->pluginTrialService = $pluginTrialService;
        $this->featureFlagManager = $featureFlagManager;
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
        $this->checkValidPluginName($pluginName);

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
        $this->checkValidPluginName($pluginName);

        // checked first, so no trial is started for requests another super user has already decided on
        if (!$this->pluginTrialService->hasPendingRequests($pluginName)) {
            throw new Exception(Piwik::translate('Marketplace_PluginRequestAlreadyDecided'));
        }

        $this->startFreeTrial($pluginName);

        return $this->pluginTrialService->decide($pluginName, RequestHistory::DECISION_APPROVED);
    }

    private function checkPluginRequestsActive(): void
    {
        if (!$this->featureFlagManager->isFeatureActive(PluginRequests::class)) {
            throw new Exception('Plugin requests are not enabled');
        }
    }

    private function checkValidPluginName(string $pluginName): void
    {
        if (!$this->pluginManager->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
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
