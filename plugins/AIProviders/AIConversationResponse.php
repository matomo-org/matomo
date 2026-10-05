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
 * One assistant turn returned by {@link AIProviderService::converse()}: the
 * assistant's content blocks in the canonical shape (see
 * {@link CanonicalMessage}), the reason the model stopped, and usage
 * metadata.
 *
 * When `stopReason` is {@link STOP_TOOL_USE} the content contains one or
 * more `tool_use` blocks; the caller runs the tools and issues a follow-up
 * request whose history includes this turn and the tool results.
 *
 * @phpstan-import-type CanonicalContentBlockArray from CanonicalMessage
 */
class AIConversationResponse
{
    /**
     * Canonical stop reasons. Providers map their own vocabulary onto these
     * where a mapping exists and pass their raw value through otherwise, so
     * callers should treat unknown values like {@link STOP_END_TURN}.
     */
    public const STOP_END_TURN = 'end_turn';
    public const STOP_TOOL_USE = 'tool_use';
    public const STOP_MAX_TOKENS = 'max_tokens';
    public const STOP_SEQUENCE = 'stop_sequence';
    public const STOP_GUARDRAIL_INTERVENED = 'guardrail_intervened';

    /**
     * @var string
     */
    private $providerId;

    /**
     * @var string
     */
    private $providerName;

    /**
     * @var string
     */
    private $model;

    /**
     * Canonical assistant content blocks ('text' and/or 'tool_use').
     *
     * @var list<CanonicalContentBlockArray>
     */
    private $content;

    /**
     * @var string
     */
    private $stopReason;

    /**
     * Number of input (prompt) tokens reported by the provider, or null when
     * the provider does not report token usage.
     *
     * @var int|null
     */
    private $inputTokens;

    /**
     * Number of output (completion) tokens reported by the provider, or null
     * when the provider does not report token usage.
     *
     * @var int|null
     */
    private $outputTokens;

    /**
     * Number of input tokens served from the provider's prompt cache, or null
     * when the provider does not report cache usage. Not part of
     * {@link $inputTokens}: providers that cache report the two separately
     * because a cache read is billed at a reduced rate.
     *
     * @var int|null
     */
    private $cacheReadTokens;

    /**
     * Number of input tokens written into the provider's prompt cache, or null
     * when the provider does not report cache usage. Not part of
     * {@link $inputTokens}, and billed at a premium rate.
     *
     * @var int|null
     */
    private $cacheWriteTokens;

    /**
     * Total provider request time in milliseconds, including retries.
     *
     * @var int|null
     */
    private $executionTimeMs;

    /**
     * @param list<CanonicalContentBlockArray> $content
     */
    public function __construct(
        string $providerId,
        string $providerName,
        string $model,
        array $content,
        string $stopReason,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?int $executionTimeMs = null,
        ?int $cacheReadTokens = null,
        ?int $cacheWriteTokens = null
    ) {
        $this->providerId = $providerId;
        $this->providerName = $providerName;
        $this->model = $model;
        $this->content = $content;
        $this->stopReason = $stopReason;
        $this->inputTokens = $inputTokens;
        $this->outputTokens = $outputTokens;
        $this->executionTimeMs = $executionTimeMs;
        $this->cacheReadTokens = $cacheReadTokens;
        $this->cacheWriteTokens = $cacheWriteTokens;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return list<CanonicalContentBlockArray>
     */
    public function getContent(): array
    {
        return $this->content;
    }

    public function getStopReason(): string
    {
        return $this->stopReason;
    }

    /**
     * Concatenated text of all canonical text blocks. Convenience for
     * logging and for turns that carry no tool calls; tool-aware callers
     * should read {@link getContent()} instead.
     */
    public function getText(): string
    {
        $parts = [];
        foreach ($this->content as $block) {
            if (($block['type'] ?? null) === 'text' && is_string($block['text'] ?? null)) {
                $parts[] = $block['text'];
            }
        }

        return implode("\n", $parts);
    }

    public function getInputTokens(): ?int
    {
        return $this->inputTokens;
    }

    public function getOutputTokens(): ?int
    {
        return $this->outputTokens;
    }

    public function getCacheReadTokens(): ?int
    {
        return $this->cacheReadTokens;
    }

    public function getCacheWriteTokens(): ?int
    {
        return $this->cacheWriteTokens;
    }

    public function getExecutionTimeMs(): ?int
    {
        return $this->executionTimeMs;
    }
}
