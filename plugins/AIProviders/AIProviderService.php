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
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugins\AIProviders\Exception\AIProviderClientException;
use Piwik\Plugins\AIProviders\Exception\AIQuotaExceededException;
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
     *
     * Posts `AIProviders.beforeRequest` before the provider call, which throws
     * {@link AIQuotaExceededException} when a listener denies it, and
     * `AIProviders.usage` after it, whatever the outcome.
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

        // Fail rather than silently answer ungrounded: a caller that asked for
        // sources would otherwise store answers it cannot tell apart from grounded ones.
        if ($request->isWebSearchEnabled() && !$provider->supportsWebSearch()) {
            throw new AIProviderClientException(sprintf(
                '%s does not support web search.',
                $provider->getName()
            ));
        }

        $configuration = $this->configuration->getProviderConfiguration($provider);

        $context = $this->buildContext(AIRequestContext::TYPE_COMPLETE, $request, $provider);
        $this->requireAllowed($context);

        $response = $this->callProvider($context, function () use ($provider, $request, $configuration): AIProviderResponse {
            return $provider->complete($request, $configuration);
        });

        // Report before the empty check: the tokens were spent either way.
        $isEmpty = trim($response->getText()) === '';
        $this->postUsage(AIUsage::forCompletion(
            $context,
            $response,
            $isEmpty ? AIUsage::OUTCOME_EMPTY : AIUsage::OUTCOME_SUCCESS
        ));

        if ($isEmpty) {
            throw new \RuntimeException(sprintf('%s returned an empty response.', $provider->getName()));
        }

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
     *
     * Posts the same `AIProviders.beforeRequest` and `AIProviders.usage`
     * events as {@link complete()}.
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

        $context = $this->buildContext(AIRequestContext::TYPE_CONVERSE, $request, $provider);
        $this->requireAllowed($context);

        $response = $this->callProvider($context, function () use ($provider, $request, $configuration): AIConversationResponse {
            return $provider->converse($request, $configuration);
        });

        $this->postUsage(AIUsage::forConversation($context, $response));

        return $response;
    }

    /**
     * Asks the `AIProviders.beforeRequest` listeners whether the request would
     * be allowed, without calling the provider, so a feature can show a limit
     * message before the user starts. Throws {@link AIQuotaExceededException}
     * when it would be denied.
     *
     * Only the listeners' decision is checked, not whether the provider supports
     * the request. Not a reservation: the real call is checked again. The
     * context's {@link AIRequestContext::isProbe()} is true, and no
     * `AIProviders.usage` event follows.
     */
    public function assertRequestAllowed(AIRequest|AIConversationRequest $request): void
    {
        $providers = AIProviders::getAvailableProviders();
        $resolution = $this->resolveProviderId($request->getProviderId(), $request->getCallerPluginName(), $providers);

        if ($resolution['stripRequestedModel']) {
            $request = $request->withModel(null);
        }

        $provider = $this->requireProvider($providers, $resolution['providerId']);
        $type = $request instanceof AIConversationRequest ? AIRequestContext::TYPE_CONVERSE : AIRequestContext::TYPE_COMPLETE;

        $this->requireAllowed($this->buildContext($type, $request, $provider, true));
    }

    /**
     * Returns how many units of the given feature may still be used, for
     * callers that plan a batch of requests up front (for example a daily run),
     * or null when the budget is unlimited or nobody limits it.
     *
     * What a unit is (credits, checks, ...) is up to the listener that limits
     * the feature.
     *
     * @param string $featureKey The feature key the requests will use, for example `'AIBrandInsights.promptQuery'`.
     */
    public function getRemainingBudget(string $featureKey): ?int
    {
        $budget = null;

        /**
         * Triggered when a plugin asks how much of an AI feature's allowance is
         * left, before it plans a batch of AI requests.
         *
         * Leave `$budget` untouched for unlimited. Otherwise only ever lower it:
         * set it when it is null or above your remaining units, so the
         * strictest listener wins whatever the order.
         *
         * **Example**
         *
         *     public function provideAiBudget(string $featureKey, ?int &$budget): void
         *     {
         *         $remaining = $this->getRemainingChecks($featureKey);
         *         if ($budget === null || $remaining < $budget) {
         *             $budget = $remaining;
         *         }
         *     }
         *
         * @param string $featureKey The feature key, for example `'AIBrandInsights.promptQuery'`.
         * @param int|null &$budget  Remaining units, null for unlimited.
         */
        Piwik::postEvent('AIProviders.getRemainingBudget', [$featureKey, &$budget]);

        return $budget;
    }

    /**
     * Asks whether an AI feature action may run, for example adding one more
     * prompt, before the caller performs it. Read
     * {@link AIRequestDecision::isAllowed()} and show
     * {@link AIRequestDecision::getMessage()} when it is denied.
     *
     * @param string               $featureKey The action, for example `'AIBrandInsights.addPrompt'`.
     * @param array<string, mixed> $payload    Facts listeners need, for example `['currentCount' => 3]`.
     */
    public function checkFeatureAllowed(string $featureKey, array $payload = []): AIRequestDecision
    {
        $decision = new AIRequestDecision();

        /**
         * Triggered before a plugin performs an AI feature action, for example
         * adding a prompt, so a plugin enforcing limits can deny it.
         *
         * Listeners can only deny, so a denial stands whatever the order.
         *
         * **Example**
         *
         *     public function decideAiFeature(string $featureKey, array $payload, AIRequestDecision $decision): void
         *     {
         *         if ($featureKey === 'AIBrandInsights.addPrompt' && $payload['currentCount'] >= $this->getPromptLimit()) {
         *             $decision->deny('limit_reached', Piwik::translate('MyPlugin_PromptLimitReached'));
         *         }
         *     }
         *
         * @param string               $featureKey The action, for example `'AIBrandInsights.addPrompt'`.
         * @param array<string, mixed> $payload    Facts sent by the caller, for example `['currentCount' => 3]`.
         * @param AIRequestDecision    $decision   Starts as allowed; call `deny()` to refuse.
         */
        Piwik::postEvent('AIProviders.checkFeatureAllowed', [$featureKey, $payload, $decision]);

        return $decision;
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
     * Whether a grounded completion from this caller can run, so a feature that
     * needs sources can degrade before spending anything.
     *
     * Answered for the provider {@link complete()} would actually resolve to,
     * which is what makes it usable: on a managed instance the forced provider
     * wins unless the caller is allowlisted, and a forced provider without web
     * search makes every grounded request throw.
     *
     * Answers for the provider only, not for a specific request: it takes no
     * {@link AIRequest} and so cannot see request options that a provider
     * refuses to combine with search. Google rejects grounding together with
     * {@link AIRequest::withJsonResponse()}, and a `true` here does not warn
     * about it.
     *
     * @param string|null $requestedProviderId The same value the caller would pass to
     *                                         {@link AIRequest::withProviderId()}, if any.
     */
    public function canUseWebSearch(string $callerPluginName, ?string $requestedProviderId = null): bool
    {
        $providers = AIProviders::getAvailableProviders();

        try {
            $resolution = $this->resolveProviderId($requestedProviderId, $callerPluginName, $providers);
            $provider = $this->requireProvider($providers, $resolution['providerId']);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return $provider->supportsWebSearch()
            && $provider->isConfigured($this->configuration->getProviderConfiguration($provider));
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

    private function buildContext(
        string $requestType,
        AIRequest|AIConversationRequest $request,
        AIProvider $provider,
        bool $probe = false
    ): AIRequestContext {
        $featureKey = $request->getFeatureKey();

        return new AIRequestContext(
            Common::generateUniqId(),
            $requestType,
            $featureKey !== null && $featureKey !== '' ? $featureKey : $request->getCallerPluginName() . '.default',
            $request->getCallerPluginName(),
            $request->getIdSite(),
            $request->getUsageReference(),
            Piwik::getCurrentUserLogin(),
            $provider->getId(),
            $request->getModel() !== '' ? $request->getModel() : null,
            $request->getMaxTokens(),
            $request instanceof AIRequest && $request->isWebSearchEnabled(),
            $request->getMeta(),
            $probe
        );
    }

    /**
     * Throws when a `AIProviders.beforeRequest` listener denies the call.
     */
    private function requireAllowed(AIRequestContext $context): void
    {
        $decision = new AIRequestDecision();

        /**
         * Triggered before every {@link complete()} and {@link converse()} provider
         * call, after the provider is resolved, so a plugin can deny the call, for
         * example when a usage limit is reached. A denied call is not sent: the
         * caller gets an {@link AIQuotaExceededException} carrying the decision.
         * Also triggered by {@link assertRequestAllowed()}, with
         * `$context->isProbe()` true.
         *
         * Listeners can only deny, so a denial stands whatever the order. Only
         * `AIProviders.usage` reports a call that was made: a probe, a denial or
         * a failing listener means no usage event follows. Otherwise the
         * context's request ID is repeated in the matching usage event.
         * Connection tests and model listings in the admin UI call the provider
         * directly and post neither event.
         *
         * **Example**
         *
         *     public function decideAiRequest(AIRequestContext $context, AIRequestDecision $decision): void
         *     {
         *         if ($this->isOverLimit($context->getFeatureKey())) {
         *             $decision->deny('limit_reached', Piwik::translate('MyPlugin_AiLimitReached'), $used, $limit, 0);
         *         }
         *     }
         *
         * @param AIRequestContext  $context  The call about to be made. No prompt content.
         * @param AIRequestDecision $decision Starts as allowed; call `deny()` to refuse.
         */
        Piwik::postEvent('AIProviders.beforeRequest', [$context, $decision]);

        if (!$decision->isAllowed()) {
            throw new AIQuotaExceededException(
                $decision->getMessage() ?? Piwik::translate('AIProviders_QuotaReached'),
                $decision
            );
        }
    }

    /**
     * Runs the provider call and reports a failure through `AIProviders.usage`
     * before rethrowing it.
     *
     * @template T
     * @param callable(): T $call
     * @return T
     */
    private function callProvider(AIRequestContext $context, callable $call)
    {
        $start = microtime(true);

        try {
            return $call();
        } catch (\Throwable $e) {
            $this->postUsage(AIUsage::forError($context, $e, (int) round((microtime(true) - $start) * 1000)));

            throw $e;
        }
    }

    private function postUsage(AIUsage $usage): void
    {
        try {
            /**
             * Triggered after every {@link complete()} and {@link converse()}
             * provider call, whatever the outcome, so a plugin can meter or bill
             * AI usage. Check `$usage->getOutcome()`: a failed call is reported
             * too, usually without token counts or cost.
             *
             * Unlike most events, an exception thrown by a listener is logged and
             * not passed on, because the provider call has already been made
             * and paid for, and the caller should still get its answer. It does
             * stop the listeners after it, so catch your own errors.
             *
             * **Example**
             *
             *     public function recordAiUsage(AIUsage $usage): void
             *     {
             *         if ($usage->isSuccess()) {
             *             $this->store($usage->getContext()->getRequestId(), $usage->getInputTokens(), $usage->getOutputTokens());
             *         }
             *     }
             *
             * @param AIUsage $usage What the call used. No prompt or response content.
             */
            Piwik::postEvent('AIProviders.usage', [$usage]);
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->error(
                'An AIProviders.usage listener failed for AI request {requestId}: {exception}',
                ['requestId' => $usage->getContext()->getRequestId(), 'exception' => $e]
            );
        }
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
                'supportsWebSearch' => $provider->supportsWebSearch(),
            ];
        }, $usableProviders);
    }
}
