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
 * What one provider call used, posted with the `AIProviders.usage` event after
 * every {@link AIProviderService::complete()} and
 * {@link AIProviderService::converse()} call, whatever its outcome.
 *
 * Token counts are null when the provider does not report them, which is
 * usual for {@link OUTCOME_ERROR}. Carries no prompt or response content.
 *
 * Known gaps: a call that fails after the provider billed it (for example a
 * per-call priced API that retries an empty answer, then gives up) is
 * reported as {@link OUTCOME_ERROR} without its cost. Google's output tokens
 * do not include its thinking tokens.
 */
final class AIUsage
{
    /** The provider answered. */
    public const OUTCOME_SUCCESS = 'success';

    /** The provider answered, but the completion was rejected as empty. Tokens were still spent. */
    public const OUTCOME_EMPTY = 'empty';

    /** The provider call failed. Tokens are usually unknown. */
    public const OUTCOME_ERROR = 'error';

    /**
     * @var AIRequestContext
     */
    private $context;

    /**
     * @var string
     */
    private $outcome;

    /**
     * @var string|null
     */
    private $model = null;

    /**
     * @var int|null
     */
    private $inputTokens = null;

    /**
     * @var int|null
     */
    private $outputTokens = null;

    /**
     * @var int|null
     */
    private $cacheReadTokens = null;

    /**
     * @var int|null
     */
    private $cacheWriteTokens = null;

    /**
     * @var int|null
     */
    private $webSearchCalls = 0;

    /**
     * @var int
     */
    private $flatFeeCalls = 0;

    /**
     * @var float|null
     */
    private $providerCost = null;

    /**
     * @var string|null
     */
    private $stopReason = null;

    /**
     * @var int|null
     */
    private $durationMs = null;

    /**
     * @var string|null
     */
    private $errorClass = null;

    /**
     * @var array<string, mixed>
     */
    private $providerMeta = [];

    private function __construct(AIRequestContext $context, string $outcome)
    {
        $this->context = $context;
        $this->outcome = $outcome;
    }

    /**
     * @param string $outcome {@link OUTCOME_SUCCESS} or {@link OUTCOME_EMPTY}
     */
    public static function forCompletion(AIRequestContext $context, AIProviderResponse $response, string $outcome): self
    {
        $usage = new self($context, $outcome);
        $usage->model = $response->getModel();
        $usage->inputTokens = $response->getInputTokens();
        $usage->outputTokens = $response->getOutputTokens();
        $usage->cacheReadTokens = $response->getCacheReadTokens();
        $usage->cacheWriteTokens = $response->getCacheWriteTokens();
        $usage->webSearchCalls = $response->wasWebSearchUsed() ? $response->getWebSearchRequestCount() : 0;
        $usage->flatFeeCalls = $response->getFlatFeeCalls();
        $usage->providerCost = $response->getCost();
        $usage->stopReason = $response->getStopReason();
        $usage->durationMs = $response->getExecutionTimeMs();
        $usage->providerMeta = $response->getProviderMeta();

        return $usage;
    }

    public static function forConversation(AIRequestContext $context, AIConversationResponse $response): self
    {
        $usage = new self($context, self::OUTCOME_SUCCESS);
        $usage->model = $response->getModel();
        $usage->inputTokens = $response->getInputTokens();
        $usage->outputTokens = $response->getOutputTokens();
        $usage->cacheReadTokens = $response->getCacheReadTokens();
        $usage->cacheWriteTokens = $response->getCacheWriteTokens();
        $usage->flatFeeCalls = $response->getFlatFeeCalls();
        $usage->providerCost = $response->getCost();
        $usage->stopReason = $response->getStopReason();
        $usage->durationMs = $response->getExecutionTimeMs();
        $usage->providerMeta = $response->getProviderMeta();

        return $usage;
    }

    public static function forError(AIRequestContext $context, \Throwable $error, int $durationMs): self
    {
        $usage = new self($context, self::OUTCOME_ERROR);
        $usage->durationMs = $durationMs;
        $usage->errorClass = get_class($error);

        return $usage;
    }

    public function getContext(): AIRequestContext
    {
        return $this->context;
    }

    /**
     * {@link OUTCOME_SUCCESS}, {@link OUTCOME_EMPTY} or {@link OUTCOME_ERROR}.
     */
    public function getOutcome(): string
    {
        return $this->outcome;
    }

    public function isSuccess(): bool
    {
        return $this->outcome === self::OUTCOME_SUCCESS;
    }

    /**
     * The model the provider reports, or null when the call failed.
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * Input tokens not served from or written to the prompt cache.
     */
    public function getInputTokens(): ?int
    {
        return $this->inputTokens;
    }

    public function getOutputTokens(): ?int
    {
        return $this->outputTokens;
    }

    /**
     * Input tokens served from the provider's prompt cache (billed at a reduced rate).
     */
    public function getCacheReadTokens(): ?int
    {
        return $this->cacheReadTokens;
    }

    /**
     * Input tokens written into the provider's prompt cache (billed at a premium rate).
     */
    public function getCacheWriteTokens(): ?int
    {
        return $this->cacheWriteTokens;
    }

    /**
     * Web searches the provider ran, 0 when none ran, or null when search ran
     * but the provider reports no count.
     */
    public function getWebSearchCalls(): ?int
    {
        return $this->webSearchCalls;
    }

    /**
     * Calls billed at a fixed price per call (for example 1 for each call to a
     * per-call priced API), 0 for token-billed providers.
     */
    public function getFlatFeeCalls(): int
    {
        return $this->flatFeeCalls;
    }

    /**
     * Cost in USD as billed by the provider, when it reports one, or null.
     */
    public function getProviderCost(): ?float
    {
        return $this->providerCost;
    }

    public function getStopReason(): ?string
    {
        return $this->stopReason;
    }

    /**
     * Provider request time in milliseconds, including retries, or null when
     * the provider did not report it.
     */
    public function getDurationMs(): ?int
    {
        return $this->durationMs;
    }

    /**
     * Class of the exception the call failed with, for {@link OUTCOME_ERROR}.
     */
    public function getErrorClass(): ?string
    {
        return $this->errorClass;
    }

    /**
     * Extra provider-specific data, set by the provider on its response.
     * Empty for {@link OUTCOME_ERROR}.
     *
     * @return array<string, mixed>
     */
    public function getProviderMeta(): array
    {
        return $this->providerMeta;
    }
}
