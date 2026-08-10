<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

use InvalidArgumentException;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Model\Configuration;
use Piwik\Plugins\AIProviders\Provider\AIProvider;

/**
 * Entry point for Matomo plugins that want to run AI features.
 *
 * Obtain the service from the dependency injection container and pass an
 * {@link AIRequest}:
 *
 *     $service  = \Piwik\Container\StaticContainer::get(AIProviderService::class);
 *     $response = $service->complete(new AIRequest($prompt, 'Goals'));
 *     $text     = $response->getText();
 */
class AIProviderService
{
    /** Conversational features can run: provider configured and capable. */
    public const CONVERSATION_READY = 'ready';

    /** No conversation-capable provider is configured (missing credentials or no provider). */
    public const CONVERSATION_NOT_CONFIGURED = 'not_configured';

    /** A provider is selected but does not implement conversations. */
    public const CONVERSATION_PROVIDER_UNSUPPORTED = 'provider_unsupported';

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Completes the given request.
     *
     * The provider is resolved in this order: the provider forced by a managed
     * environment, then the provider requested by
     * the caller, then the configured default provider. A managed environment
     * wins even when the caller requests another provider, except for callers
     * on the `providerSelectionAllowlist` of the managed config, whose
     * requested provider and model are honoured (see below).
     *
     * Allowlisted callers (for example a plugin that must query several AI
     * engines because comparing the engines is the feature itself) get no
     * silent fallback: an unknown requested provider throws, and an
     * unconfigured one fails in the provider. Falling back to the forced
     * provider would silently produce answers from the wrong engine, which is
     * worse than a clear error.
     *
     * The requested model is forwarded for allowlisted callers and on
     * unmanaged instances, and stripped otherwise.
     */
    public function complete(AIRequest $request): AIProviderResponse
    {
        // use the capability level set in the admin ui, unless overwritten through the request
        if ($request->getCapabilityLevel() === null) {
            $request = $request->withCapabilityLevel($this->configuration->getDefaultCapabilityLevel());
        }

        $providers = AIProviders::getAvailableProviders();
        $resolution = $this->resolveProviderId($request->getProviderId(), $request->getCallerPluginName(), $providers);

        if ($resolution['stripRequestedModel']) {
            $request = $request->withProviderId($resolution['providerId'])->withModel(null);
        }

        $provider = $this->requireProvider($providers, $resolution['providerId']);
        $configuration = $this->configuration->getProviderConfiguration($provider);

        $response = $this->runWithProvider($provider, $configuration, $request);

        /**
         * TODO: publish an observability event here so billing or monitoring can
         * hook into AI usage without this plugin depending on them. Emit basic
         * data, for example:
         *
         *     Piwik::postEvent('AIProviders.usage', [[
         *         'caller'    => $request->getCallerPluginName(),
         *         'feature'   => $request->getFeatureKey(),
         *         'idSite'    => $request->getIdSite(),
         *         'login'     => Piwik::getCurrentUserLogin(),
         *         'provider'  => $provider->getId(),
         *         'model'     => $response->getModel(),
         *         'tokensIn'  => $response->getInputTokens(),
         *         'tokensOut' => $response->getOutputTokens(),
         *     ]]);
         *
         * Not implemented yet.
         */
        return $response;
    }

    /**
     * Runs one conversational round-trip (multi-turn messages plus optional
     * tool calls) and returns the assistant's turn.
     *
     * The provider is resolved exactly like in {@link complete()}: forced
     * provider, then the caller's requested provider when allowlisted, then
     * the configured default. A provider that does not support conversations
     * fails with a clear error instead of silently degrading; callers should
     * gate conversational features on {@link canConverse()}.
     *
     * Unlike {@link complete()}, an empty text response is valid here: a turn
     * may consist solely of tool_use blocks.
     */
    public function converse(AIConversationRequest $request): AIConversationResponse
    {
        // Use the capability level set in the admin UI unless the request overrides it.
        if ($request->getCapabilityLevel() === null) {
            $request = $request->withCapabilityLevel($this->configuration->getDefaultCapabilityLevel());
        }

        $providers = AIProviders::getAvailableProviders();
        $resolution = $this->resolveProviderId($request->getProviderId(), $request->getCallerPluginName(), $providers);

        if ($resolution['stripRequestedModel']) {
            $request = $request->withProviderId($resolution['providerId'])->withModel(null);
        }

        $provider = $this->requireProvider($providers, $resolution['providerId']);

        if (!$provider->supportsConversations()) {
            throw new AIProviderClientException(sprintf(
                '%s does not support multi-turn conversations.',
                $provider->getName()
            ));
        }

        $configuration = $this->configuration->getProviderConfiguration($provider);

        // TODO: publish the same `AIProviders.usage` observability event as
        // planned for complete() once it is implemented there.
        return $provider->converse($request, $configuration);
    }

    /**
     * Returns whether conversational features can run right now: the default
     * provider (honouring a managed environment's forced provider) exists, is
     * configured, and supports conversations. Plugins offering chat-style
     * features should hide or disable themselves when this returns false; use
     * {@link getConversationAvailability()} when the reason matters for the UI.
     */
    public function canConverse(): bool
    {
        return $this->getConversationAvailability()['status'] === self::CONVERSATION_READY;
    }

    /**
     * Reports whether conversational features can run, and why not when they
     * cannot, so callers can show an actionable message instead of a generic
     * "not configured" notice.
     *
     * `status` is one of:
     * - {@link CONVERSATION_READY}: the default provider exists, supports
     *   conversations, and is configured.
     * - {@link CONVERSATION_PROVIDER_UNSUPPORTED}: a provider is selected but
     *   does not implement conversations (for example a completion-only
     *   provider). The fix is to switch providers, so this wins over a missing
     *   configuration.
     * - {@link CONVERSATION_NOT_CONFIGURED}: the conversation-capable provider
     *   has no credentials yet, or no provider could be resolved.
     *
     * `providerId`/`providerName` identify the resolved provider when one
     * exists, so the message can name it.
     *
     * @return array{status: string, providerId: ?string, providerName: ?string}
     */
    public function getConversationAvailability(): array
    {
        try {
            $provider = $this->getDefaultProvider();
        } catch (InvalidArgumentException $e) {
            return ['status' => self::CONVERSATION_NOT_CONFIGURED, 'providerId' => null, 'providerName' => null];
        }

        $base = ['providerId' => $provider->getId(), 'providerName' => $provider->getName()];

        if (!$provider->supportsConversations()) {
            return ['status' => self::CONVERSATION_PROVIDER_UNSUPPORTED] + $base;
        }

        if (!$provider->isConfigured($this->configuration->getProviderConfiguration($provider))) {
            return ['status' => self::CONVERSATION_NOT_CONFIGURED] + $base;
        }

        return ['status' => self::CONVERSATION_READY] + $base;
    }

    /**
     * Resolves which provider a request runs through: the caller's requested
     * provider (unless {@link isLockedToForcedProvider()}), then the forced
     * provider, then the configured default.
     *
     * `stripRequestedModel` is true when the forced provider overrode the
     * request; the requested model must then be dropped too, because the
     * model decides cost on managed instances.
     *
     * @return array{providerId: string, stripRequestedModel: bool}
     */
    private function resolveProviderId(
        ?string $requestedProviderId,
        string $callerPluginName,
        AIProvidersList $providers
    ): array {
        $forcedProviderId = $this->configuration->getForcedProviderId();
        $hasRequestedProvider = $requestedProviderId !== null && $requestedProviderId !== '';

        if (
            $hasRequestedProvider
            && $forcedProviderId !== null
            && !$this->isLockedToForcedProvider($forcedProviderId, $callerPluginName)
        ) {
            // TODO: consider validating the requested model against a
            // per-provider `allowedModels` list from the managed config as a
            // cost backstop, once the planned `AIProviders.usage` event shows
            // whether actual token usage needs it.
            return ['providerId' => $requestedProviderId, 'stripRequestedModel' => false];
        }

        if ($forcedProviderId !== null) {
            return ['providerId' => $forcedProviderId, 'stripRequestedModel' => true];
        }

        if ($hasRequestedProvider) {
            return ['providerId' => $requestedProviderId, 'stripRequestedModel' => false];
        }

        return [
            'providerId' => $this->configuration->getDefaultProviderId($providers),
            'stripRequestedModel' => false,
        ];
    }

    /**
     * The managed-mode policy gate shared by {@link resolveProviderId()} and
     * {@link getProviderStatusesForCaller()}: a caller is locked to the forced
     * provider unless it is on the `providerSelectionAllowlist`.
     */
    private function isLockedToForcedProvider(?string $forcedProviderId, string $callerPluginName): bool
    {
        return $forcedProviderId !== null
            && !$this->configuration->isPluginAllowedToSelectProvider($callerPluginName);
    }

    private function requireProvider(AIProvidersList $providers, string $providerId): AIProvider
    {
        $provider = $providers->getProvider($providerId);

        if ($provider === null) {
            // Untranslated on purpose: only a plugin passing its own provider ID
            // reaches this, never the settings form.
            throw new InvalidArgumentException(sprintf('Unknown AI provider "%s".', $providerId));
        }

        return $provider;
    }

    /**
     * Validates a specific provider and configuration, with no provider
     * resolution. Restricted to the admin "test connection" flow, which needs
     * to test an unsaved provider/configuration before it is stored. Delegates
     * to the provider's lightweight connection probe and throws on failure.
     *
     * Callers must enforce their own access control (the admin API gates this
     * behind super-user access). Because it bypasses resolution — including the
     * provider forced by a managed environment — it must not be used as a general
     * completion entry point; use {@link complete()} for that.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    public function testProviderConnection(AIProvider $provider, array $configuration): void
    {
        $provider->verifyConnection($configuration);
    }

    /**
     * Executes the request against the given provider and guards against an empty
     * completion. Used by {@link complete()}.
     *
     * @param array{apiKey?: string, endpointUrl?: string, model?: string, useFipsEndpoint?: bool} $configuration
     */
    private function runWithProvider(AIProvider $provider, array $configuration, AIRequest $request): AIProviderResponse
    {
        $response = $provider->complete($request, $configuration);

        if (trim($response->getText()) === '') {
            throw new \RuntimeException(sprintf('%s returned an empty response.', $provider->getName()));
        }

        return $response;
    }

    /**
     * Returns the provider that completions run through by default, honouring a
     * provider forced by a managed environment.
     */
    public function getDefaultProvider(): AIProvider
    {
        $providers = AIProviders::getAvailableProviders();
        $forcedProviderId = $this->configuration->getForcedProviderId();
        $providerId = $forcedProviderId ?? $this->configuration->getDefaultProviderId($providers);

        return $this->requireProvider($providers, $providerId);
    }

    /**
     * Returns the configured default model capability level.
     */
    public function getDefaultCapabilityLevel(): string
    {
        return $this->configuration->getDefaultCapabilityLevel();
    }

    /**
     * Returns whether the plugin runs in a managed environment, that is, the
     * default provider is forced (and locked) from configuration so settings
     * cannot be changed from the administration UI.
     */
    public function isManaged(): bool
    {
        return $this->configuration->isManaged();
    }

    /**
     * Returns provider status metadata for the administration UI.
     *
     * Restricted providers (registered as non-selectable by a managed
     * environment) are excluded so they stay hidden from admin surfaces;
     * completion callers use {@link getProviderStatusesForCaller()} instead.
     *
     * @return array<int, array{
     *     id: string,
     *     name: string,
     *     description: string,
     *     defaultModel: string,
     *     isDefault: bool,
     *     isConfigured: bool,
     *     supportsCustomEndpoint: bool,
     *     endpointUrl: string
     * }>
     */
    public function getAvailableProviderStatuses(): array
    {
        $providers = AIProviders::getAvailableProviders();
        $defaultProviderId = $this->configuration->getForcedProviderId()
            ?? $this->configuration->getDefaultProviderId($providers);

        return array_map(function (AIProvider $provider) use ($defaultProviderId): array {
            $configuration = $this->configuration->getProviderConfiguration($provider);

            return [
                'id' => $provider->getId(),
                'name' => $provider->getName(),
                'description' => $provider->getDescription(),
                'defaultModel' => $provider->getDefaultModel(),
                'isDefault' => $provider->getId() === $defaultProviderId,
                'isConfigured' => $provider->isConfigured($configuration),
                'supportsCustomEndpoint' => $provider->supportsCustomEndpoint(),
                'endpointUrl' => $configuration['endpointUrl'],
            ];
        }, $providers->getSelectableProviders());
    }

    /**
     * Returns the providers the given caller can run completions through,
     * flagged with whether credentials are in place. Follows the provider
     * resolution of {@link complete()}.
     *
     * The caller name is self-declared (same trust model as complete()):
     * pass a hardcoded plugin name, and gate any HTTP exposure of the result
     * with the feature's usual access check.
     *
     * @return array<int, array{id: string, name: string, isConfigured: bool}>
     */
    public function getProviderStatusesForCaller(string $callerPluginName): array
    {
        $providers = AIProviders::getAvailableProviders();
        $forcedProviderId = $this->configuration->getForcedProviderId();

        if ($this->isLockedToForcedProvider($forcedProviderId, $callerPluginName)) {
            $forced = $providers->getProvider($forcedProviderId);
            $usableProviders = $forced !== null ? [$forced] : [];
        } else {
            $usableProviders = $providers->getProviders();
        }

        return array_map(function (AIProvider $provider): array {
            return [
                'id' => $provider->getId(),
                'name' => $provider->getName(),
                'isConfigured' => $provider->isConfigured(
                    $this->configuration->getProviderConfiguration($provider)
                ),
            ];
        }, $usableProviders);
    }
}
