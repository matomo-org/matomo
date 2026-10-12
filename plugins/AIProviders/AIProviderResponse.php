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
 * @phpstan-import-type WebSearchCitationArray from WebSearchUsage
 */
class AIProviderResponse
{
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
     * @var string
     */
    private $text;

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
     * Provider reasoning level that was actually applied.
     *
     * @var string
     */
    private $reasoningLevel;

    /**
     * Total provider request time in milliseconds, including retries.
     *
     * @var int|null
     */
    private $executionTimeMs;

    /**
     * Provider stop reason, when available.
     *
     * @var string|null
     */
    private $stopReason;

    /**
     * What the provider's web search actually did, or {@link WebSearchUsage::none()}
     * when it did not run.
     *
     * @var WebSearchUsage
     */
    private $webSearch;

    /**
     * Cost of the request in USD as billed by the provider, or null when the
     * provider does not report one (token-billed providers leave pricing to the
     * caller).
     *
     * @var float|null
     */
    private $cost;

    /**
     * Calls billed at a fixed price per call (for example 1 for a per-call
     * priced API), or 0 for token-billed providers.
     *
     * @var int
     */
    private $flatFeeCalls;

    /**
     * Extra provider-specific data for the `AIProviders.usage` event, without
     * prompt or response content.
     *
     * @var array<string, mixed>
     */
    private $providerMeta;

    /**
     * @param array<string, mixed> $providerMeta
     */
    public function __construct(
        string $providerId,
        string $providerName,
        string $model,
        string $text,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        string $reasoningLevel = AIRequest::REASONING_NONE,
        ?int $executionTimeMs = null,
        ?string $stopReason = null,
        ?WebSearchUsage $webSearch = null,
        ?float $cost = null,
        ?int $cacheReadTokens = null,
        ?int $cacheWriteTokens = null,
        int $flatFeeCalls = 0,
        array $providerMeta = []
    ) {
        $this->providerId = $providerId;
        $this->providerName = $providerName;
        $this->model = $model;
        $this->text = $text;
        $this->inputTokens = $inputTokens;
        $this->outputTokens = $outputTokens;
        $this->reasoningLevel = $reasoningLevel;
        $this->executionTimeMs = $executionTimeMs;
        $this->stopReason = $stopReason;
        $this->webSearch = $webSearch ?? WebSearchUsage::none();
        $this->cost = $cost;
        $this->cacheReadTokens = $cacheReadTokens;
        $this->cacheWriteTokens = $cacheWriteTokens;
        $this->flatFeeCalls = $flatFeeCalls;
        $this->providerMeta = $providerMeta;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getModel(): string
    {
        return $this->model;
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

    public function getReasoningLevel(): string
    {
        return $this->reasoningLevel;
    }

    /**
     * Whether the provider's web search actually ran, which is not the same as
     * whether it was requested: a model given the tool can decide the prompt
     * needs no search.
     */
    public function wasWebSearchUsed(): bool
    {
        return $this->webSearch->wasUsed();
    }

    /**
     * Web sources attached to this answer, deduplicated, cited ones first where
     * the provider distinguishes them. Titles are untrusted model output; see
     * {@link WebSearchUsage} for what is and is not guaranteed.
     *
     * @return list<WebSearchCitationArray>
     */
    public function getWebSearchCitations(): array
    {
        return $this->webSearch->getCitations();
    }

    /**
     * Number of searches the provider ran (what per-search fees are billed on),
     * or null when it did not run or reports no count.
     */
    public function getWebSearchRequestCount(): ?int
    {
        return $this->webSearch->getRequestCount();
    }

    /**
     * Search queries the model issued, when the provider echoes them. Can be
     * empty even when searches ran.
     *
     * @return list<string>
     */
    public function getWebSearchQueries(): array
    {
        return $this->webSearch->getQueries();
    }

    public function getExecutionTimeMs(): ?int
    {
        return $this->executionTimeMs;
    }

    public function getStopReason(): ?string
    {
        return $this->stopReason;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function getFlatFeeCalls(): int
    {
        return $this->flatFeeCalls;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProviderMeta(): array
    {
        return $this->providerMeta;
    }

    /**
     * Returns the response text decoded as a JSON array/object, or null when the
     * text is not valid JSON. Intended for requests made with
     * {@link AIRequest::withJsonResponse()}.
     *
     * @return array<mixed>|null
     */
    public function getJsonData(): ?array
    {
        $decoded = json_decode($this->stripJsonCodeFence($this->text), true);

        return is_array($decoded) ? $decoded : null;
    }

    private function stripJsonCodeFence(string $text): string
    {
        $text = trim($text);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        return $text;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'providerId' => $this->providerId,
            'providerName' => $this->providerName,
            'model' => $this->model,
            'text' => $this->text,
            'inputTokens' => $this->inputTokens,
            'outputTokens' => $this->outputTokens,
            'cacheReadTokens' => $this->cacheReadTokens,
            'cacheWriteTokens' => $this->cacheWriteTokens,
            'reasoningLevel' => $this->reasoningLevel,
            'webSearchUsed' => $this->wasWebSearchUsed(),
            'webSearchRequestCount' => $this->webSearch->getRequestCount(),
            'webSearchQueries' => $this->webSearch->getQueries(),
            'webSearchCitations' => $this->webSearch->getCitations(),
            'executionTimeMs' => $this->executionTimeMs,
            'stopReason' => $this->stopReason,
            'cost' => $this->cost,
        ];
    }
}
