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
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\Api\Service;
use Piwik\Plugins\Marketplace\Plugins\InvalidLicenses;
use Piwik\Plugins\Marketplace\PluginTrial\Service as PluginTrialService;
use Piwik\Plugins\UsersManager\SystemSettings;
use Piwik\Plugins\UsersManager\Validators\AllowedEmailDomain;
use Piwik\Plugins\UsersManager\Validators\Email;
use Piwik\Validators\Exception as ValidatorException;
use Piwik\Validators\NotEmpty;

/**
 * The Marketplace API lets you manage your license key so you can download & install in one-click <a target="_blank" rel="noreferrer" href="https://matomo.org/recommends/premium-plugins/">paid premium plugins</a> you have subscribed to.
 *
 * @method static \Piwik\Plugins\Marketplace\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var Client
     */
    private $marketplaceClient;

    /**
     * @var Service
     */
    private $marketplaceService;

    /**
     * @var InvalidLicenses
     */
    private $expired;

    /**
     * @var PluginManager
     */
    private $pluginManager;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var PluginTrialService
     */
    private $pluginTrialService;

    public function __construct(
        Service $service,
        Client $client,
        InvalidLicenses $expired,
        PluginManager $pluginManager,
        Environment $environment,
        PluginTrialService $pluginTrialService
    ) {
        $this->marketplaceService = $service;
        $this->marketplaceClient  = $client;
        $this->expired = $expired;
        $this->pluginManager = $pluginManager;
        $this->environment = $environment;
        $this->pluginTrialService = $pluginTrialService;
    }

    /**
     * @param string $pluginName
     *
     * @return bool
     * @throws Service\Exception If the marketplace request failed
     *
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
     * @return bool
     */
    public function deleteLicenseKey()
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->setLicenseKey(null);
        return true;
    }

    /**
     * @param string $pluginName
     *
     * @return bool
     *
     * @unsanitized
     * @internal
     */
    public function requestTrial(string $pluginName): bool
    {
        Piwik::checkUserIsNotAnonymous();

        if (Piwik::hasUserSuperUserAccess()) {
            throw new Exception('Cannot request trial as a super user');
        }

        if (!$this->pluginManager->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $pluginInfo = $this->marketplaceClient->getPluginInfo($pluginName);

        if (empty($pluginInfo['name'])) {
            throw new Exception('Unable to find plugin with given name: ' . $pluginName);
        }

        $this->pluginTrialService->request($pluginInfo['name'], $pluginInfo['displayName'] ?: $pluginInfo['name']);

        return true;
    }

    /**
     * @param string $pluginName
     *
     * @return bool
     * @throws Service\Exception If the marketplace request failed
     *
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
     * @param string $licenseKey
     * @return bool
     *
     * @throws Exception In case of an invalid license key
     * @throws Service\Exception In case of any network problems
     */
    public function saveLicenseKey($licenseKey)
    {
        Piwik::checkUserHasSuperUserAccess();

        $licenseKey = trim($licenseKey);

        // we are currently using the Marketplace service directly to 1) change LicenseKey and 2) not use any cache
        $this->marketplaceService->authenticate($licenseKey);

        try {
            $consumer = $this->marketplaceService->fetch('consumer/validate', array());
        } catch (Api\Service\Exception $e) {
            if ($e->getCode() === Api\Service\Exception::HTTP_ERROR) {
                throw $e;
            }

            $consumer = array();
        }

        if (empty($consumer['isValid'])) {
            throw new Exception(Piwik::translate('Marketplace_ExceptionLinceseKeyIsNotValid'));
        }

        $this->setLicenseKey($licenseKey);

        return true;
    }

    private function setLicenseKey($licenseKey)
    {
        $key = new LicenseKey();
        $key->set($licenseKey);

        $this->marketplaceClient->clearAllCacheEntries();
        $this->expired->clearCache();
    }
}
