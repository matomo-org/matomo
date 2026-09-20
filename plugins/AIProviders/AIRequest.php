<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders;

/**
 * Immutable description of a single AI completion request.
 *
 * Trusted Matomo plugins build an {@link AIRequest} and pass it to
 * {@link AIProviderService::complete()} to run an AI feature through the
 * configured provider. Optional values are set with the fluent `with*()`
 * methods, each of which returns a new instance:
 *
 *     $response = $service->complete(
 *         (new AIRequest('Summarise this report.', 'Goals'))
 *             ->withSystemPrompt('You are a concise web analytics assistant.')
 *             ->withCapabilityLevel(Configuration::CAPABILITY_THINKING)
 *             ->withIdSite($idSite)
 *     );
 *
 * The selected `providerId` is treated as a hint: on a managed environment
 * it is overridden by the forced default provider,
 * unless the caller plugin is on the centrally managed
 * `[AIProviders] providerSelectionAllowlist`, in which case the requested
 * provider and model are honoured. See {@link AIProviderService::complete()}.
 *
 * **Hard rule:** the values passed to {@link withProviderId()} and
 * {@link withModel()} must originate from plugin code constants or
 * server-side configuration — never, directly or indirectly, from request
 * input (`Request::fromRequest()`, `Common::getRequestVar()`, superglobals).
 * Forwarding request input would let any API/HTTP client pick the provider
 * and model the server calls, which on managed hosting means circumventing
 * the centrally managed provider and its cost controls. If users may choose
 * between engines in a UI, the request parameter must be a key into a
 * server-side map defined by the plugin, never the provider/model string
 * itself.
 */
class AIRequest
{
    public const DEFAULT_MAX_TOKENS = 1024;
    public const DEFAULT_TEMPERATURE = 0.2;

    public const REASONING_NONE = 'none';

    public const FORMAT_TEXT = 'text';
    public const FORMAT_JSON = 'json';

    /**
     * @var string
     */
    private $userPrompt;

    /**
     * Name of the plugin issuing the request, used for accountability and
     * future usage accounting (for example `'Goals'`).
     *
     * @var string
     */
    private $callerPluginName;

    /**
     * @var string|null
     */
    private $systemPrompt = null;

    /**
     * @var string|null
     */
    private $providerId = null;

    /**
     * @var string|null
     */
    private $model = null;

    /**
     * Requested model capability level (see Configuration::CAPABILITY_*).
     * Advisory for now: providers do not yet map this to a specific model.
     *
     * @var string|null
     */
    private $capabilityLevel = null;

    /**
     * Optional identifier of the feature issuing the request (for example
     * `'goal-recommendation'`), used for future usage accounting.
     *
     * @var string|null
     */
    private $featureKey = null;

    /**
     * @var int|null
     */
    private $idSite = null;

    /**
     * @var int
     */
    private $maxTokens = self::DEFAULT_MAX_TOKENS;

    /**
     * @var float
     */
    private $temperature = self::DEFAULT_TEMPERATURE;

    /**
     * Desired response format: FORMAT_TEXT (default) or FORMAT_JSON. In JSON mode
     * the provider is asked to return a single JSON object — natively where the
     * provider supports it, otherwise via an explicit instruction.
     *
     * @var string
     */
    private $responseFormat = self::FORMAT_TEXT;

    /**
     * Requested reasoning level. Currently advisory only; providers do not send
     * provider-specific reasoning controls until those request formats are
     * confirmed.
     *
     * @var string
     */
    private $reasoningLevel = self::REASONING_NONE;

    /**
     * Whether the caller wants provider web search. Currently advisory only;
     * providers do not enable provider-specific web search tools yet.
     *
     * @var bool
     */
    private $webSearchEnabled = false;

    /**
     * Future provider-specific thinking budget. Not applied to requests yet.
     *
     * @var int|null
     */
    private $thinkingBudget = null;

    public function __construct(string $userPrompt, string $callerPluginName)
    {
        $this->userPrompt = $userPrompt;
        $this->callerPluginName = $callerPluginName;
    }

    public function withSystemPrompt(?string $systemPrompt): self
    {
        $request = clone $this;
        $request->systemPrompt = $systemPrompt;

        return $request;
    }

    /**
     * Requests a specific provider. Honoured on unmanaged instances and for
     * allowlisted caller plugins on managed instances; otherwise the forced
     * default provider wins (see the class docblock).
     *
     * The value must be a plugin constant or server-side config value, never
     * request input — see the hard rule in the class docblock.
     */
    public function withProviderId(?string $providerId): self
    {
        $request = clone $this;
        $request->providerId = $providerId;

        return $request;
    }

    /**
     * Requests a specific model. Stripped on managed instances unless the
     * caller plugin is allowlisted, because the model decides cost there.
     *
     * The value must be a plugin constant or server-side config value, never
     * request input — see the hard rule in the class docblock.
     */
    public function withModel(?string $model): self
    {
        $request = clone $this;
        $request->model = $model;

        return $request;
    }

    public function withCapabilityLevel(?string $capabilityLevel): self
    {
        $request = clone $this;
        $request->capabilityLevel = $capabilityLevel;

        return $request;
    }

    public function withFeatureKey(?string $featureKey): self
    {
        $request = clone $this;
        $request->featureKey = $featureKey;

        return $request;
    }

    public function withIdSite(?int $idSite): self
    {
        $request = clone $this;
        $request->idSite = $idSite;

        return $request;
    }

    public function withMaxTokens(int $maxTokens): self
    {
        $request = clone $this;
        $request->maxTokens = $maxTokens;

        return $request;
    }

    public function withTemperature(float $temperature): self
    {
        $request = clone $this;
        $request->temperature = $temperature;

        return $request;
    }

    public function withReasoningLevel(?string $reasoningLevel): self
    {
        $request = clone $this;
        $reasoningLevel = trim((string) $reasoningLevel);
        $request->reasoningLevel = $reasoningLevel !== '' ? $reasoningLevel : self::REASONING_NONE;

        return $request;
    }

    public function withWebSearchEnabled(bool $webSearchEnabled): self
    {
        $request = clone $this;
        $request->webSearchEnabled = $webSearchEnabled;

        return $request;
    }

    public function withThinkingBudget(?int $thinkingBudget): self
    {
        $request = clone $this;
        $request->thinkingBudget = $thinkingBudget;

        return $request;
    }

    /**
     * Requests a JSON response. The provider is asked to return a single valid
     * JSON object; read it with {@link AIProviderResponse::getJsonData()}.
     */
    public function withJsonResponse(): self
    {
        $request = clone $this;
        $request->responseFormat = self::FORMAT_JSON;

        return $request;
    }

    public function getUserPrompt(): string
    {
        return $this->userPrompt;
    }

    public function getCallerPluginName(): string
    {
        return $this->callerPluginName;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getCapabilityLevel(): ?string
    {
        return $this->capabilityLevel;
    }

    public function getFeatureKey(): ?string
    {
        return $this->featureKey;
    }

    public function getIdSite(): ?int
    {
        return $this->idSite;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getResponseFormat(): string
    {
        return $this->responseFormat;
    }

    public function getReasoningLevel(): string
    {
        return $this->reasoningLevel;
    }

    public function isWebSearchEnabled(): bool
    {
        return $this->webSearchEnabled;
    }

    public function getThinkingBudget(): ?int
    {
        return $this->thinkingBudget;
    }

    public function isJsonResponse(): bool
    {
        return $this->responseFormat === self::FORMAT_JSON;
    }
}
