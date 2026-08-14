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
 * Immutable description of a single conversational round-trip: the full
 * message history in the canonical shape (see {@link CanonicalMessage}),
 * plus an optional tool catalogue the model may call.
 *
 * This is the multi-turn, tool-capable counterpart of {@link AIRequest}.
 * Use {@link AIRequest} with {@link AIProviderService::complete()} for
 * simple prompt-in/text-out features; use this class with
 * {@link AIProviderService::converse()} when the caller maintains a
 * conversation and dispatches tool calls itself:
 *
 *     $response = $service->converse(
 *         (new AIConversationRequest($messages, 'AskMatomo'))
 *             ->withSystemPrompt($systemPrompt)
 *             ->withTools($toolCatalog)
 *             ->withMaxTokens(2048)
 *     );
 *
 * The provider returns one assistant turn per call; running tools and
 * appending their results to the history for the next call is the caller's
 * responsibility.
 *
 * The same hard rule as for {@link AIRequest} applies: values passed to
 * {@link withProviderId()} and {@link withModel()} must originate from
 * plugin code constants or server-side configuration — never, directly or
 * indirectly, from request input.
 *
 * @phpstan-import-type CanonicalMessageArray from CanonicalMessage
 * @phpstan-type ToolCatalogEntryArray array{
 *     name: string,
 *     title?: string|null,
 *     description: string,
 *     inputSchema: array<string, mixed>,
 *     outputSchema?: array<string, mixed>|null,
 *     readOnly?: bool|null,
 *     destructive?: bool|null,
 *     idempotent?: bool|null,
 *     openWorld?: bool|null
 * }
 */
class AIConversationRequest
{
    public const DEFAULT_MAX_TOKENS = 2048;
    public const DEFAULT_TIMEOUT_SECONDS = 60;

    /**
     * Conversation history in canonical shape, oldest first.
     *
     * @var list<CanonicalMessageArray>
     */
    private $messages;

    /**
     * Name of the plugin issuing the request, used for accountability and
     * future usage accounting (for example `'AskMatomo'`).
     *
     * @var string
     */
    private $callerPluginName;

    /**
     * @var string|null
     */
    private $systemPrompt = null;

    /**
     * Tool catalogue offered to the model, in the MCP-aligned shape produced
     * by the McpServer plugin's tool catalog. The annotation hints
     * (readOnly/destructive/idempotent/openWorld) are advisory metadata for
     * the caller's approval flow; providers only forward name, description,
     * and inputSchema.
     *
     * @var list<ToolCatalogEntryArray>
     */
    private $tools = [];

    /**
     * @var string|null
     */
    private $providerId = null;

    /**
     * @var string|null
     */
    private $model = null;

    /**
     * Optional identifier of the feature issuing the request, used for
     * future usage accounting.
     *
     * @var string|null
     */
    private $featureKey = null;

    /**
     * @var int
     */
    private $maxTokens = self::DEFAULT_MAX_TOKENS;

    /**
     * @var float
     */
    private $temperature = AIRequest::DEFAULT_TEMPERATURE;

    /**
     * Requested model capability level (see Configuration::CAPABILITY_*), or
     * null to defer to the configured default.
     *
     * @var string|null
     */
    private $capabilityLevel = null;

    /**
     * Provider HTTP timeout. Conversational round-trips replay the whole
     * history and may produce tool calls, so the default is more generous
     * than for simple completions.
     *
     * @var int
     */
    private $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS;

    /**
     * @param list<CanonicalMessageArray> $messages
     */
    public function __construct(array $messages, string $callerPluginName)
    {
        $this->messages = $messages;
        $this->callerPluginName = $callerPluginName;
    }

    public function withSystemPrompt(?string $systemPrompt): self
    {
        $request = clone $this;
        $request->systemPrompt = $systemPrompt;

        return $request;
    }

    /**
     * Offers a tool catalogue to the model. See the `$tools` property for the
     * expected shape.
     *
     * @param list<ToolCatalogEntryArray> $tools
     */
    public function withTools(array $tools): self
    {
        $request = clone $this;
        $request->tools = $tools;

        return $request;
    }

    /**
     * Requests a specific provider. Honoured on unmanaged instances and for
     * allowlisted caller plugins on managed instances; otherwise the forced
     * default provider wins. The value must be a plugin constant or
     * server-side config value, never request input — see the class docblock.
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
     * The value must be a plugin constant or server-side config value, never
     * request input — see the class docblock.
     */
    public function withModel(?string $model): self
    {
        $request = clone $this;
        $request->model = $model;

        return $request;
    }

    public function withFeatureKey(?string $featureKey): self
    {
        $request = clone $this;
        $request->featureKey = $featureKey;

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

    public function withCapabilityLevel(?string $capabilityLevel): self
    {
        $request = clone $this;
        $request->capabilityLevel = $capabilityLevel;

        return $request;
    }

    public function withTimeoutSeconds(int $timeoutSeconds): self
    {
        $request = clone $this;
        $request->timeoutSeconds = max(1, $timeoutSeconds);

        return $request;
    }

    /**
     * @return list<CanonicalMessageArray>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getCallerPluginName(): string
    {
        return $this->callerPluginName;
    }

    public function getSystemPrompt(): ?string
    {
        return $this->systemPrompt;
    }

    /**
     * @return list<ToolCatalogEntryArray>
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    public function getProviderId(): ?string
    {
        return $this->providerId;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function getFeatureKey(): ?string
    {
        return $this->featureKey;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function getCapabilityLevel(): ?string
    {
        return $this->capabilityLevel;
    }

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }
}
