<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Model;

use InvalidArgumentException;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Plugins\AIProviders\AIProvidersList;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Provider\AIProvider;

/**
 * Stores and resolves the AI provider configuration.
 *
 * Provider connection settings are merged per field in this order:
 *
 * 1. Namespaced DI values supplied by the instance owner:
 *
 *        AIProviders.openaiApiKey
 *
 * 2. The `[AIProviders]` section of `config.ini.php`, or environment variables:
 *
 *        [AIProviders]
 *        openaiApiKey = "..."                ; or env MATOMO_AIPROVIDERS_OPENAI_API_KEY
 *        custom-providerEndpointUrl = "..."  ; or env MATOMO_AIPROVIDERS_CUSTOM_PROVIDER_ENDPOINT_URL
 *        bedrockUseFipsEndpoint = "1"        ; or env MATOMO_AIPROVIDERS_BEDROCK_USE_FIPS_ENDPOINT
 *
 * An endpoint is only read for the providers that have an endpoint field (see
 * {@link AIProvider::supportsCustomEndpoint()}); the fixed hosted providers
 * ignore one supplied for them.
 *
 * 3. The database, written from the administration UI.
 *
 * The DI/config-file sources are how a managed environment supplies credentials.
 * A centrally supplied API key also decides the endpoint it may be sent to: for
 * a provider whose endpoint field is the request URL, neither a stored nor a
 * submitted endpoint is ever paired with such a key, so a superuser cannot
 * redirect it. Providers that expand the field into a host they control are
 * exempt, for example AWS Bedrock with its region (see
 * {@link AIProvider::endpointFieldRequiresUrl()}). Either way, an endpoint the
 * central sources decide cannot be changed from the settings form (see
 * {@link isEndpointUrlSuppliedCentrally()}).
 *
 * Errors that reject a value the admin submitted from the settings form are
 * translated (the `AIProviders_Error*` keys); everything the form cannot
 * produce stays in English.
 */
class Configuration
{
    public const SETTING_DEFAULT_PROVIDER = 'defaultProvider';
    public const SETTING_DEFAULT_CAPABILITY_LEVEL = 'defaultCapabilityLevel';
    public const SETTING_PROVIDER_CREDENTIALS = 'providerCredentials';

    /**
     * Key in the `[AIProviders]` config section listing the plugins that may
     * target a specific provider per request even when a managed environment
     * forces the default provider. See {@link isPluginAllowedToSelectProvider()}.
     */
    public const CONFIG_PROVIDER_SELECTION_ALLOWLIST = 'providerSelectionAllowlist';

    public const CAPABILITY_INSTANT = 'instant';
    public const CAPABILITY_THINKING = 'thinking';

    private const PLUGIN_NAME = 'AIProviders';
    private const DEFAULT_PROVIDER_ID = 'openai';

    /**
     * Stored default provider ID. Persisted in the plugin settings storage, but
     * not shown on the generic plugin settings page (the settings are not
     * registered in a settings container). The value can be overridden and
     * locked from `config.ini.php`, which is how managed environments force
     * a provider:
     *
     *     [AIProviders]
     *     defaultProvider = "openai"
     *
     * @var SystemSetting
     */
    private $defaultProvider;

    /**
     * @var SystemSetting
     */
    private $defaultCapabilityLevel;

    /**
     * Per-provider connection settings keyed by provider ID, for example
     * `['openai' => ['apiKey' => '...', 'endpointUrl' => '']]`.
     *
     * @var SystemSetting
     */
    private $providerCredentials;

    public function __construct()
    {
        $this->defaultProvider = new SystemSetting(
            self::SETTING_DEFAULT_PROVIDER,
            '',
            FieldConfig::TYPE_STRING,
            self::PLUGIN_NAME
        );
        $this->defaultCapabilityLevel = new SystemSetting(
            self::SETTING_DEFAULT_CAPABILITY_LEVEL,
            self::CAPABILITY_INSTANT,
            FieldConfig::TYPE_STRING,
            self::PLUGIN_NAME
        );
        $this->providerCredentials = new SystemSetting(
            self::SETTING_PROVIDER_CREDENTIALS,
            [],
            FieldConfig::TYPE_ARRAY,
            self::PLUGIN_NAME
        );
    }

    /**
     * Returns masked AI provider settings for the administration UI.
     *
     * Only selectable providers are included, so providers a managed
     * environment registered as restricted (usable by allowlisted plugins
     * only) are never revealed to admin surfaces.
     *
     * @return array{
     *     defaultProviderId: string,
     *     defaultCapabilityLevel: string,
     *     canEditProviderConfiguration: bool,
     *     canEditCapabilityLevel: bool,
     *     capabilityLevels: array<string, array{label: string, description: string}>,
     *     providers: array<int, array<string, mixed>>
     * } Provider metadata and masked configuration values.
     */
    public function getSettings(AIProvidersList $providers): array
    {
        return [
            'defaultProviderId' => $this->getDefaultProviderId($providers),
            'defaultCapabilityLevel' => $this->getDefaultCapabilityLevel(),
            'canEditProviderConfiguration' => $this->canEditProviderConfiguration(),
            'canEditCapabilityLevel' => $this->canEditCapabilityLevel(),
            'capabilityLevels' => $this->getCapabilityLevels(),
            'providers' => array_map(function (AIProvider $provider): array {
                $providerConfiguration = $this->getProviderConfiguration($provider);

                return array_merge($provider->toArray(), [
                    'configuration' => [
                        'hasApiKey' => !empty($providerConfiguration['apiKey']),
                        'endpointUrl' => $providerConfiguration['endpointUrl'],
                        'model' => $providerConfiguration['model'],
                        'useFipsEndpoint' => $providerConfiguration['useFipsEndpoint'],
                        'isUsable' => $provider->isConfigured($providerConfiguration),
                    ],
                ]);
            }, $providers->getSelectableProviders()),
        ];
    }

    /**
     * Saves the default provider, capability level, and provider connection settings.
     *
     * Both are optional so the form can be saved before any provider is
     * connected: an empty default provider clears it (or falls back to the
     * first usable one), and an empty capability level keeps the stored value.
     *
     * In a managed environment the provider and its credentials
     * are forced from configuration, so nothing is persisted here.
     */
    public function saveSettings(
        AIProvidersList $providers,
        string $defaultProviderId,
        string $defaultCapabilityLevel,
        #[\SensitiveParameter]
        string $providerConfigurationsJson
    ): void {
        if ($this->canEditProviderConfiguration()) {
            $submittedProviderConfigurations = $this->decodeProviderConfigurations($providerConfigurationsJson);
            $this->saveProviderConfigurations($providers, $submittedProviderConfigurations);
            $this->saveDefaultProviderId($providers, $defaultProviderId);
        }

        if ($this->canEditCapabilityLevel() && trim($defaultCapabilityLevel) !== '') {
            $this->saveDefaultCapabilityLevel($defaultCapabilityLevel);
        }
    }

    /**
     * Returns the effective server-side provider configuration including the API key.
     *
     * The returned array contains secrets. It is internal to the AIProviders
     * plugin and its providers and must never be returned from API methods,
     * logged, or exposed to other plugins. Other plugins run completions via
     * {@link \Piwik\Plugins\AIProviders\AIProviderService::complete()} and
     * never see credentials.
     *
     * @internal
     * @return array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool}
     */
    public function getProviderConfiguration(AIProvider $provider): array
    {
        $providerId = $provider->getId();
        $storedConfiguration = $this->getProviderConfigurations()[$providerId] ?? [
            'apiKey' => '',
            'endpointUrl' => '',
            'model' => '',
            'useFipsEndpoint' => false,
        ];
        $configFileConfiguration = $this->getConfigFileProviderConfiguration($providerId);

        return [
            'apiKey' => $configFileConfiguration['apiKey'] !== ''
                ? $configFileConfiguration['apiKey']
                : $storedConfiguration['apiKey'],
            'endpointUrl' => $this->resolveEndpointUrl(
                $provider,
                $configFileConfiguration,
                $storedConfiguration
            ),
            'model' => $configFileConfiguration['model'] !== ''
                ? $configFileConfiguration['model']
                : $storedConfiguration['model'],
            'useFipsEndpoint' => $configFileConfiguration['useFipsEndpoint'] !== null
                ? $configFileConfiguration['useFipsEndpoint']
                : $storedConfiguration['useFipsEndpoint'],
        ];
    }

    /**
     * @param array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool|null} $configFileConfiguration
     * @param array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool} $storedConfiguration
     */
    private function resolveEndpointUrl(
        AIProvider $provider,
        #[\SensitiveParameter]
        array $configFileConfiguration,
        #[\SensitiveParameter]
        array $storedConfiguration
    ): string {
        if (!$provider->supportsCustomEndpoint()) {
            return '';
        }

        if ($configFileConfiguration['endpointUrl'] !== '') {
            return $configFileConfiguration['endpointUrl'];
        }

        if ($this->isEndpointUrlPinnedBySuppliedApiKey($provider)) {
            return '';
        }

        return $storedConfiguration['endpointUrl'];
    }

    private function isEndpointUrlPinnedBySuppliedApiKey(AIProvider $provider): bool
    {
        return $this->getConfigFileProviderConfiguration($provider->getId())['apiKey'] !== ''
            && $provider->supportsCustomEndpoint()
            && $provider->endpointFieldRequiresUrl();
    }

    /**
     * Returns a validated server-side provider configuration, optionally using
     * unsaved values from the admin UI for connection testing.
     *
     * @param array<string, mixed> $submittedProviderConfiguration
     * @return array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool}
     */
    public function getProviderConfigurationForUse(
        AIProvider $provider,
        #[\SensitiveParameter]
        array $submittedProviderConfiguration = []
    ): array {
        $providerId = $provider->getId();

        // Checked against the submitted values before the merge below fills the
        // endpoint in from the effective configuration.
        $this->checkSubmittedEndpointUrlIsAllowed(
            $provider,
            $submittedProviderConfiguration,
            $this->getSubmittedEndpointUrl($submittedProviderConfiguration, $provider)
        );

        // Base the merge on the effective configuration so a connection test
        // exercises what complete() would actually use, including config-file
        // credentials. Only an omitted or matching endpoint survives the check
        // above.
        $existingConfiguration = $this->getProviderConfiguration($provider);
        $submittedProviderConfiguration = array_merge(
            $existingConfiguration,
            $submittedProviderConfiguration
        );

        return [
            'apiKey' => $this->getSubmittedApiKey(
                $submittedProviderConfiguration,
                [$providerId => $existingConfiguration],
                $providerId
            ),
            'endpointUrl' => $this->getSubmittedEndpointUrl($submittedProviderConfiguration, $provider),
            'model' => $this->getSubmittedModel($submittedProviderConfiguration, $provider),
            'useFipsEndpoint' => $this->getSubmittedUseFipsEndpoint($submittedProviderConfiguration, $provider),
        ];
    }

    /**
     * Removes the database-stored connection settings for a provider.
     *
     * Credentials supplied via the config file or environment (see the class
     * docblock) are not touched: they are not stored in the database and can
     * only be removed where they were defined.
     */
    public function removeProviderConfiguration(string $providerId): void
    {
        $providerConfigurations = $this->getProviderConfigurations();
        unset($providerConfigurations[$providerId]);

        $this->providerCredentials->setValue($providerConfigurations);
        $this->providerCredentials->save();
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    public function getCapabilityLevels(): array
    {
        return [
            self::CAPABILITY_INSTANT => [
                'label' => 'AIProviders_InstantCapability',
                'description' => 'AIProviders_InstantCapabilityDescription',
            ],
            self::CAPABILITY_THINKING => [
                'label' => 'AIProviders_ThinkingCapability',
                'description' => 'AIProviders_ThinkingCapabilityDescription',
            ],
        ];
    }

    /**
     * Returns whether provider connections can be edited in the UI.
     *
     * Provider configuration is locked whenever the default provider is forced
     * from configuration (a managed environment).
     */
    public function canEditProviderConfiguration(): bool
    {
        return !$this->isManaged() && $this->defaultProvider->isWritableByCurrentUser();
    }

    public function canEditCapabilityLevel(): bool
    {
        return !$this->isManaged() && $this->defaultCapabilityLevel->isWritableByCurrentUser();
    }

    /**
     * Returns the forced provider ID when running in a managed environment, or
     * null when the default provider may be chosen freely.
     *
     * Trusted callers use this to honour the centrally managed provider even
     * when they request a specific one.
     */
    public function getForcedProviderId(): ?string
    {
        if (!$this->isManaged()) {
            return null;
        }

        $providerId = $this->defaultProvider->getValue();

        return is_string($providerId) && $providerId !== '' ? $providerId : null;
    }

    /**
     * Returns the configured default provider ID, falling back to the first
     * configured provider. Only selectable providers are considered, so a
     * restricted provider can never become the default.
     */
    public function getDefaultProviderId(AIProvidersList $providers): string
    {
        $providerId = $this->defaultProvider->getValue();

        /**
         * When forced from configuration, use the
         * configured provider as-is so misconfiguration surfaces a clear error
         * from the provider rather than silently falling back.
         */
        if ($this->isManaged() && is_string($providerId) && $providerId !== '' && $providers->hasProvider($providerId)) {
            return $providerId;
        }

        if (
            is_string($providerId)
            && $providers->isSelectable($providerId)
            && $this->isProviderUsable($providers->getProvider($providerId))
        ) {
            return $providerId;
        }

        if (
            $providers->isSelectable(self::DEFAULT_PROVIDER_ID)
            && $this->isProviderUsable($providers->getProvider(self::DEFAULT_PROVIDER_ID))
        ) {
            return self::DEFAULT_PROVIDER_ID;
        }

        return $this->getFirstConfiguredProviderId($providers);
    }

    private function saveDefaultProviderId(AIProvidersList $providers, string $providerId): void
    {
        $providerId = trim($providerId);

        if ($providerId === '') {
            $providerId = $this->getFirstConfiguredProviderId($providers);

            if ($providerId === '') {
                $this->defaultProvider->setValue('');
                $this->defaultProvider->save();
                return;
            }
        }

        // Restricted providers are reported as unknown on purpose: admin
        // surfaces must not reveal that they exist.
        $provider = $providers->getProvider($providerId);

        if ($provider === null || !$providers->isSelectable($providerId)) {
            throw new InvalidArgumentException(
                Piwik::translate('AIProviders_ErrorUnknownProvider', $providerId)
            );
        }

        if (!$this->isProviderUsable($provider)) {
            throw new InvalidArgumentException(
                Piwik::translate('AIProviders_ErrorProviderNotConfigured', $provider->getName())
            );
        }

        $this->defaultProvider->setValue($providerId);
        $this->defaultProvider->save();
    }

    /**
     * Returns the configured default model capability level.
     */
    public function getDefaultCapabilityLevel(): string
    {
        $capabilityLevel = $this->defaultCapabilityLevel->getValue();

        if (!is_string($capabilityLevel) || !array_key_exists($capabilityLevel, $this->getCapabilityLevels())) {
            return self::CAPABILITY_INSTANT;
        }

        return $capabilityLevel;
    }

    private function saveDefaultCapabilityLevel(string $capabilityLevel): void
    {
        $capabilityLevel = trim($capabilityLevel);

        if (!array_key_exists($capabilityLevel, $this->getCapabilityLevels())) {
            throw new InvalidArgumentException(
                Piwik::translate('AIProviders_ErrorUnknownCapabilityLevel', $capabilityLevel)
            );
        }

        $this->defaultCapabilityLevel->setValue($capabilityLevel);
        $this->defaultCapabilityLevel->save();
    }

    /**
     * Returns whether the plugin runs in a managed environment, that is, the
     * default provider is forced (and locked) from configuration.
     */
    public function isManaged(): bool
    {
        $config = Config::getInstance()->AIProviders;
        $providerId = is_array($config) ? ($config[self::SETTING_DEFAULT_PROVIDER] ?? null) : null;

        return is_string($providerId) && trim($providerId) !== '';
    }

    /**
     * @return array<string, array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool}>
     */
    private function getProviderConfigurations(): array
    {
        $rawValue = $this->providerCredentials->getValue();

        if (!is_array($rawValue)) {
            return [];
        }

        $providerConfigurations = [];
        foreach ($rawValue as $providerId => $providerConfiguration) {
            if (!is_string($providerId) || !is_array($providerConfiguration)) {
                continue;
            }

            $providerConfigurations[$providerId] = [
                'apiKey' => isset($providerConfiguration['apiKey']) && is_string($providerConfiguration['apiKey'])
                    ? $providerConfiguration['apiKey']
                    : '',
                'endpointUrl' => isset($providerConfiguration['endpointUrl']) && is_string($providerConfiguration['endpointUrl'])
                    ? $providerConfiguration['endpointUrl']
                    : '',
                'model' => isset($providerConfiguration['model']) && is_string($providerConfiguration['model'])
                    ? $providerConfiguration['model']
                    : '',
                'useFipsEndpoint' => !empty($providerConfiguration['useFipsEndpoint']),
            ];
        }

        return $providerConfigurations;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function decodeProviderConfigurations(
        #[\SensitiveParameter]
        string $providerConfigurationsJson
    ): array {
        $decoded = json_decode($providerConfigurationsJson, true);

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('Provider configurations must be a JSON object.');
        }

        return $decoded;
    }

    /**
     * Persists the submitted provider connection settings to the database.
     *
     * Only selectable providers are accepted, so restricted providers cannot
     * be configured through the administration flow. The API key fallback
     * reads the stored (database) value on purpose: config-file credentials
     * must never be copied into the database. The endpoint is kept out of it
     * the same way while a supplied key pins it (see
     * {@link getEndpointUrlToStore()}).
     *
     * @param array<string, mixed> $submittedProviderConfigurations
     */
    private function saveProviderConfigurations(
        AIProvidersList $providers,
        #[\SensitiveParameter]
        array $submittedProviderConfigurations
    ): void {
        $existingProviderConfigurations = $this->getProviderConfigurations();
        $providerConfigurations = [];

        foreach ($providers->getSelectableProviders() as $provider) {
            $providerId = $provider->getId();
            $submittedProviderConfiguration = $submittedProviderConfigurations[$providerId] ?? [];

            if (!is_array($submittedProviderConfiguration)) {
                throw new InvalidArgumentException(sprintf('Invalid configuration for AI provider "%s".', $providerId));
            }

            $apiKey = $this->getSubmittedApiKey($submittedProviderConfiguration, $existingProviderConfigurations, $providerId);
            $endpointUrl = $this->getSubmittedEndpointUrl($submittedProviderConfiguration, $provider);
            $this->checkSubmittedEndpointUrlIsAllowed($provider, $submittedProviderConfiguration, $endpointUrl);
            $endpointUrl = $this->getEndpointUrlToStore($provider, $endpointUrl, $existingProviderConfigurations);
            $model = $this->getSubmittedModel($submittedProviderConfiguration, $provider);
            $useFipsEndpoint = $this->getSubmittedUseFipsEndpoint($submittedProviderConfiguration, $provider);

            // The model counts on its own: when the credentials and the endpoint
            // are supplied centrally, neither is stored (see
            // getEndpointUrlToStore()), and dropping the entry would discard the
            // model the admin picked and leave the provider unable to run.
            if ($apiKey === '' && $endpointUrl === '' && $model === '' && !$useFipsEndpoint) {
                continue;
            }

            $providerConfigurations[$providerId] = [
                'apiKey' => $apiKey,
                'endpointUrl' => $endpointUrl,
                'model' => $model,
                'useFipsEndpoint' => $useFipsEndpoint,
            ];
        }

        $this->providerCredentials->setValue($providerConfigurations);
        $this->providerCredentials->save();
    }

    /**
     * Rejects a submitted endpoint that the central sources decide instead.
     *
     * Only a value that actually differs from the effective one is rejected: the
     * admin form prefills the endpoint field and resubmits it unchanged on every
     * save, and a caller may omit the field entirely. Rejecting rather than
     * silently dropping the value keeps the save and connection-test paths
     * agreeing on what a connection can be, so a test cannot report a pairing a
     * save would refuse to store.
     *
     * @param array<string, mixed> $submittedProviderConfiguration
     */
    private function checkSubmittedEndpointUrlIsAllowed(
        AIProvider $provider,
        #[\SensitiveParameter]
        array $submittedProviderConfiguration,
        string $endpointUrl
    ): void {
        if (
            !array_key_exists('endpointUrl', $submittedProviderConfiguration)
            || !$this->isEndpointUrlSuppliedCentrally($provider)
            || $endpointUrl === $this->getEffectiveEndpointUrl($provider)
        ) {
            return;
        }

        // Getting here without a supplied endpoint means a supplied API key is
        // what decides it, the one case where the admin has to be told to add the
        // endpoint rather than to look up the value already in place.
        $messageKey = $this->getConfigFileProviderConfiguration($provider->getId())['endpointUrl'] === ''
            ? 'AIProviders_ErrorEndpointUrlManagedWithApiKey'
            : 'AIProviders_ErrorEndpointManaged';

        throw new InvalidArgumentException(Piwik::translate(
            $messageKey,
            [$provider->getName(), $provider->getId() . 'EndpointUrl']
        ));
    }

    /**
     * Returns the effective endpoint normalized the way a submitted one is (see
     * {@link getSubmittedEndpointUrl()}), so both are compared as the request
     * URL they produce. A value the provider rejects has no such URL, so it is
     * left as it is and matches nothing.
     */
    private function getEffectiveEndpointUrl(AIProvider $provider): string
    {
        $endpointUrl = $this->getProviderConfiguration($provider)['endpointUrl'];

        try {
            return $provider->normalizeEndpointUrl($endpointUrl);
        } catch (AIProviderClientException $e) {
            return $endpointUrl;
        }
    }

    /**
     * Returns the endpoint URL to persist, keeping the stored one untouched
     * while the effective endpoint comes from the central sources.
     *
     * The form posts the effective value back on every save, and neither half of
     * it belongs in the database: storing the empty one would erase the endpoint
     * the instance had configured before, and storing the supplied one would copy
     * central configuration into the database, where it would outlive its
     * removal.
     *
     * @param array<string, array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool}> $existingProviderConfigurations
     */
    private function getEndpointUrlToStore(
        AIProvider $provider,
        string $endpointUrl,
        #[\SensitiveParameter]
        array $existingProviderConfigurations
    ): string {
        if (!$this->isEndpointUrlSuppliedCentrally($provider)) {
            return $endpointUrl;
        }

        return $existingProviderConfigurations[$provider->getId()]['endpointUrl'] ?? '';
    }

    /**
     * Returns whether the endpoint the provider is called with is decided by the
     * DI/config-file sources rather than the database, either because one was
     * supplied there or because a supplied API key pins it. A submitted endpoint
     * can neither take effect nor be persisted in that case: it would sit
     * shadowed in the database and then silently take over the moment the central
     * value is removed.
     *
     * Providers without an endpoint field are never affected, so a value supplied
     * for one of them stays ignored rather than locking their settings.
     */
    private function isEndpointUrlSuppliedCentrally(AIProvider $provider): bool
    {
        if (!$provider->supportsCustomEndpoint()) {
            return false;
        }

        return $this->getConfigFileProviderConfiguration($provider->getId())['endpointUrl'] !== ''
            || $this->isEndpointUrlPinnedBySuppliedApiKey($provider);
    }

    /**
     * An empty API key means "keep the existing one": the UI cannot prefill a
     * write-only field, so it always submits an empty value unless the admin
     * typed a new key.
     *
     * @param array<string, mixed> $submittedProviderConfiguration
     * @param array<string, array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool}> $existingProviderConfigurations
     */
    private function getSubmittedApiKey(
        #[\SensitiveParameter]
        array $submittedProviderConfiguration,
        #[\SensitiveParameter]
        array $existingProviderConfigurations,
        string $providerId
    ): string {
        if ($this->hasSubmittedApiKey($submittedProviderConfiguration)) {
            return trim((string) $submittedProviderConfiguration['apiKey']);
        }

        return $existingProviderConfigurations[$providerId]['apiKey'] ?? '';
    }

    /**
     * @param array<string, mixed> $submittedProviderConfiguration
     */
    private function hasSubmittedApiKey(
        #[\SensitiveParameter]
        array $submittedProviderConfiguration
    ): bool {
        return isset($submittedProviderConfiguration['apiKey'])
            && is_string($submittedProviderConfiguration['apiKey'])
            && trim($submittedProviderConfiguration['apiKey']) !== '';
    }

    /**
     * @param array<string, mixed> $submittedProviderConfiguration
     */
    private function getSubmittedEndpointUrl(array $submittedProviderConfiguration, AIProvider $provider): string
    {
        if (!$provider->supportsCustomEndpoint()) {
            return '';
        }

        $endpointUrl = isset($submittedProviderConfiguration['endpointUrl'])
            && is_string($submittedProviderConfiguration['endpointUrl'])
            ? trim($submittedProviderConfiguration['endpointUrl'])
            : '';

        if ($endpointUrl === '') {
            return '';
        }

        try {
            $endpointUrl = $provider->normalizeEndpointUrl($endpointUrl);
        } catch (AIProviderClientException $e) {
            // The provider's own message stays English for the request-time
            // callers it shares; the admin reads the translated wording and the
            // original as the cause.
            throw $this->invalidEndpointFieldException($provider, $e);
        }

        if (!$provider->endpointFieldRequiresUrl()) {
            return $endpointUrl;
        }

        $parsedUrl = parse_url($endpointUrl);
        $scheme = is_array($parsedUrl) ? ($parsedUrl['scheme'] ?? '') : '';

        if (
            !filter_var($endpointUrl, FILTER_VALIDATE_URL)
            || !in_array($scheme, ['http', 'https'], true)
        ) {
            throw $this->invalidEndpointFieldException($provider);
        }

        return $endpointUrl;
    }

    /**
     * Both rejection paths in {@link getSubmittedEndpointUrl()} share this, so a
     * provider that only accepts shorthand states its wording once, on
     * {@link AIProvider::getEndpointFieldErrorMessage()}.
     */
    private function invalidEndpointFieldException(
        AIProvider $provider,
        ?AIProviderClientException $cause = null
    ): InvalidArgumentException {
        return new InvalidArgumentException(
            Piwik::translate($provider->getEndpointFieldErrorMessage(), $provider->getName()),
            0,
            $cause
        );
    }

    /**
     * @param array<string, mixed> $submittedProviderConfiguration
     */
    private function getSubmittedUseFipsEndpoint(array $submittedProviderConfiguration, AIProvider $provider): bool
    {
        return $provider->supportsFipsEndpoint()
            && !empty($submittedProviderConfiguration['useFipsEndpoint']);
    }

    /**
     * The model only applies to providers with a custom endpoint (the admin
     * picks it from the server's discovered models); it is empty for fixed
     * hosted providers, which use their own default model.
     *
     * @param array<string, mixed> $submittedProviderConfiguration
     */
    private function getSubmittedModel(array $submittedProviderConfiguration, AIProvider $provider): string
    {
        if (!$provider->supportsCustomEndpoint()) {
            return '';
        }

        return isset($submittedProviderConfiguration['model'])
            && is_string($submittedProviderConfiguration['model'])
            ? trim($submittedProviderConfiguration['model'])
            : '';
    }

    private function getFirstConfiguredProviderId(AIProvidersList $providers): string
    {
        foreach ($providers->getSelectableProviders() as $provider) {
            if ($this->isProviderUsable($provider)) {
                return $provider->getId();
            }
        }

        return '';
    }

    /**
     * Returns whether the provider can run completions with its effective
     * configuration (database merged with config file/environment).
     */
    private function isProviderUsable(?AIProvider $provider): bool
    {
        if ($provider === null) {
            return false;
        }

        return $provider->isConfigured($this->getProviderConfiguration($provider));
    }

    /**
     * Returns whether the given plugin may target a specific provider (and
     * model) per request even though a managed environment forces the default
     * provider. Controlled by the `[AIProviders] providerSelectionAllowlist[]`
     * config entries, which a managed environment keeps in its locked,
     * centrally managed config.
     *
     * This is a policy gate for centrally deployed plugins, not a sandbox:
     * the caller plugin name on an {@link \Piwik\Plugins\AIProviders\AIRequest}
     * is self-declared, and PHP code on the same instance can ultimately not
     * be restrained from anything. The guarantee that matters is that on a
     * managed instance neither this allowlist nor the values an allowlisted
     * plugin passes can be influenced by users (see the hard rule on
     * {@link \Piwik\Plugins\AIProviders\AIRequest::withProviderId()}).
     */
    public function isPluginAllowedToSelectProvider(string $pluginName): bool
    {
        if ($pluginName === '') {
            return false;
        }

        return in_array($pluginName, $this->getProviderSelectionAllowlist(), true);
    }

    /**
     * @return string[]
     */
    private function getProviderSelectionAllowlist(): array
    {
        $config = Config::getInstance()->AIProviders;
        $allowlist = is_array($config) ? ($config[self::CONFIG_PROVIDER_SELECTION_ALLOWLIST] ?? []) : [];

        // A single `providerSelectionAllowlist = "X"` entry (without `[]`)
        // parses as a string; accept it as a one-element list.
        if (is_string($allowlist)) {
            $allowlist = [$allowlist];
        }

        if (!is_array($allowlist)) {
            return [];
        }

        return array_values(array_filter($allowlist, 'is_string'));
    }

    /**
     * @return array{apiKey: string, endpointUrl: string, model: string, useFipsEndpoint: bool|null}
     */
    private function getConfigFileProviderConfiguration(string $providerId): array
    {
        return [
            'apiKey' => $this->getConfigFileValue($providerId, 'ApiKey', 'API_KEY'),
            'endpointUrl' => $this->getConfigFileValue($providerId, 'EndpointUrl', 'ENDPOINT_URL'),
            'model' => $this->getConfigFileValue($providerId, 'Model', 'MODEL'),
            'useFipsEndpoint' => $this->getConfigFileBooleanValue($providerId, 'UseFipsEndpoint', 'USE_FIPS_ENDPOINT'),
        ];
    }

    private function getConfigFileBooleanValue(string $providerId, string $configSuffix, string $envSuffix): ?bool
    {
        $diKey = self::PLUGIN_NAME . '.' . $providerId . $configSuffix;
        $container = StaticContainer::getContainer();

        if ($container->has($diKey)) {
            return $this->isTruthy($container->get($diKey));
        }

        $config = Config::getInstance()->AIProviders;
        $configKey = $providerId . $configSuffix;

        if (is_array($config) && array_key_exists($configKey, $config)) {
            return $this->isTruthy($config[$configKey]);
        }

        $envKey = 'MATOMO_AIPROVIDERS_' . strtoupper(str_replace('-', '_', $providerId)) . '_' . $envSuffix;
        $envValue = getenv($envKey);

        if (is_string($envValue)) {
            return $this->isTruthy($envValue);
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function isTruthy($value): bool
    {
        return is_scalar($value) && filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function getConfigFileValue(string $providerId, string $configSuffix, string $envSuffix): string
    {
        $diValue = $this->getDiConfigValue($providerId, $configSuffix);
        if ($diValue !== '') {
            return $diValue;
        }

        $config = Config::getInstance()->AIProviders;
        $configKey = $providerId . $configSuffix;

        if (is_array($config) && isset($config[$configKey]) && is_string($config[$configKey]) && trim($config[$configKey]) !== '') {
            return trim($config[$configKey]);
        }

        $envKey = 'MATOMO_AIPROVIDERS_' . strtoupper(str_replace('-', '_', $providerId)) . '_' . $envSuffix;
        $envValue = getenv($envKey);

        if (is_string($envValue) && trim($envValue) !== '') {
            return trim($envValue);
        }

        return '';
    }

    private function getDiConfigValue(string $providerId, string $configSuffix): string
    {
        $diKey = self::PLUGIN_NAME . '.' . $providerId . $configSuffix;
        $container = StaticContainer::getContainer();

        if (!$container->has($diKey)) {
            return '';
        }

        $value = $container->get($diKey);

        return is_string($value) && trim($value) !== '' ? trim($value) : '';
    }
}
