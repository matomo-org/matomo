<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use Piwik\Piwik;
use Piwik\Plugin\API as PluginAPI;
use Piwik\Plugins\AIProviders\Model\Configuration;
use Piwik\Plugins\AIProviders\Provider\AIProvider;

/**
 * Exposes AI provider configuration methods for the Matomo administration UI.
 *
 * @method static \Piwik\Plugins\AIProviders\API getInstance()
 */
class API extends PluginAPI
{
    /**
     * @var bool
     */
    protected $autoSanitizeInputParams = false;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var AIProviderService
     */
    private $aiProviderService;

    public function __construct(Configuration $configuration, AIProviderService $aiProviderService)
    {
        $this->configuration = $configuration;
        $this->aiProviderService = $aiProviderService;
    }

    /**
     * Returns AI provider settings for the administration UI.
     *
     * @return array<string, mixed> Provider metadata and masked configuration values.
     */
    public function getSettings(): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $providers = AIProviders::getAvailableProviders();

        return $this->configuration->getSettings($providers);
    }

    /**
     * Saves AI provider settings from the administration UI.
     *
     * API keys should be submitted as POST data and are never returned by this
     * API. In a managed environment the provider is
     * forced from configuration, so the submitted default provider, credentials,
     * and capability level are all ignored.
     *
     * All parameters are optional so the form can be saved before any provider
     * is connected: an empty default provider clears the stored default (or
     * falls back to the first usable provider), and an empty capability level
     * keeps the stored one. This lets a super user save credentials or the
     * default capability level on their own without first connecting a provider.
     *
     * @param string $defaultProviderId Provider ID to use by default. Empty to
     *                                  clear the default / let it fall back.
     * @param string $defaultCapabilityLevel Default model capability level.
     *                                       Empty to keep the stored value.
     * @param string $providerConfigurations JSON object keyed by provider ID
     *                                      with connection settings.
     * @return array<string, mixed> Updated provider metadata and masked configuration values.
     */
    public function saveSettings(
        string $defaultProviderId = '',
        string $defaultCapabilityLevel = '',
        #[\SensitiveParameter]
        string $providerConfigurations = '{}'
    ): array {
        Piwik::checkUserHasSuperUserAccess();

        $providers = AIProviders::getAvailableProviders();
        $configuration = $this->configuration;
        $configuration->saveSettings(
            $providers,
            $defaultProviderId,
            $defaultCapabilityLevel,
            $providerConfigurations
        );

        return $configuration->getSettings($providers);
    }

    /**
     * Tests one provider using the submitted connection settings.
     *
     * @param string $providerId Provider ID to test.
     * @param string $providerConfiguration JSON object with unsaved apiKey
     *                                      and endpointUrl values.
     * @return array{providerId: string, providerName: string, models: list<string>}
     *         Tested provider's metadata and the models it can serve (empty for
     *         providers that do not expose a model listing).
     */
    public function testConnection(
        string $providerId,
        #[\SensitiveParameter]
        string $providerConfiguration = '{}'
    ): array {
        Piwik::checkUserHasSuperUserAccess();

        $providers = AIProviders::getAvailableProviders();
        $provider = $this->getSelectableProvider($providers, $providerId);
        $configuration = $this->configuration->getProviderConfigurationForUse(
            $provider,
            $this->decodeProviderConfiguration($providerConfiguration)
        );

        $this->aiProviderService->testProviderConnection($provider, $configuration);

        return [
            'providerId' => $provider->getId(),
            'providerName' => $provider->getName(),
            'models' => $provider->listModels($configuration),
        ];
    }

    /**
     * Removes a stored provider connection.
     *
     * @param string $providerId Provider ID to disconnect.
     * @return array<string, mixed> Updated provider metadata and masked configuration values.
     */
    public function disconnectProvider(string $providerId): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $providers = AIProviders::getAvailableProviders();
        $this->getSelectableProvider($providers, $providerId);

        $this->configuration->removeProviderConfiguration($providerId);

        return $this->configuration->getSettings($providers);
    }

    /**
     * Resolves a provider for the administration endpoints. Restricted
     * providers (registered as non-selectable by a managed environment) are
     * reported as unknown on purpose, so admin surfaces neither reveal nor
     * operate on them.
     */
    private function getSelectableProvider(AIProvidersList $providers, string $providerId): AIProvider
    {
        $provider = $providers->getProvider($providerId);

        if ($provider === null || !$providers->isSelectable($providerId)) {
            throw new \InvalidArgumentException(
                Piwik::translate('AIProviders_ErrorUnknownProvider', $providerId)
            );
        }

        return $provider;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeProviderConfiguration(
        #[\SensitiveParameter]
        string $providerConfigurationJson
    ): array {
        $decoded = json_decode($providerConfigurationJson, true);

        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('Provider configuration must be a JSON object.');
        }

        return $decoded;
    }
}
