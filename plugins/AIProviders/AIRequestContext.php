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
 * Describes one provider call for the `AIProviders.beforeRequest` and
 * `AIProviders.usage` events, so listeners (for example quota or billing
 * plugins) can decide on and account for AI usage.
 *
 * Built by {@link AIProviderService} after provider resolution, so it names
 * the provider that is actually called, not the one the caller asked for.
 * Carries no prompt or response content.
 */
final class AIRequestContext
{
    public const TYPE_COMPLETE = 'complete';
    public const TYPE_CONVERSE = 'converse';

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var string
     */
    private $requestType;

    /**
     * @var string
     */
    private $featureKey;

    /**
     * @var string
     */
    private $callerPluginName;

    /**
     * @var int|null
     */
    private $idSite;

    /**
     * @var string|null
     */
    private $usageReference;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $providerId;

    /**
     * @var string|null
     */
    private $model;

    /**
     * @var int
     */
    private $maxOutputTokens;

    /**
     * @var bool
     */
    private $webSearchEnabled;

    /**
     * @var array<string, mixed>
     */
    private $meta;

    /**
     * @var bool
     */
    private $probe;

    /**
     * @param array<string, mixed> $meta
     */
    public function __construct(
        string $requestId,
        string $requestType,
        string $featureKey,
        string $callerPluginName,
        ?int $idSite,
        ?string $usageReference,
        string $login,
        string $providerId,
        ?string $model,
        int $maxOutputTokens,
        bool $webSearchEnabled,
        array $meta,
        bool $probe = false
    ) {
        $this->requestId = $requestId;
        $this->requestType = $requestType;
        $this->featureKey = $featureKey;
        $this->callerPluginName = $callerPluginName;
        $this->idSite = $idSite;
        $this->usageReference = $usageReference;
        $this->login = $login;
        $this->providerId = $providerId;
        $this->model = $model;
        $this->maxOutputTokens = $maxOutputTokens;
        $this->webSearchEnabled = $webSearchEnabled;
        $this->meta = $meta;
        $this->probe = $probe;
    }

    /**
     * Unique per provider call and the same in `beforeRequest` and `usage`, so
     * listeners can link the two and use it as a dedupe key.
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * {@link TYPE_COMPLETE} or {@link TYPE_CONVERSE}.
     */
    public function getRequestType(): string
    {
        return $this->requestType;
    }

    /**
     * The caller's feature key, by convention `'<Plugin>.<feature>'` (for
     * example `'AskMatomo.chat'`), or `'<Plugin>.default'` when the caller set none.
     */
    public function getFeatureKey(): string
    {
        return $this->featureKey;
    }

    public function getCallerPluginName(): string
    {
        return $this->callerPluginName;
    }

    public function getIdSite(): ?int
    {
        return $this->idSite;
    }

    /**
     * The caller's own identifier for this call (for example a conversation
     * turn or a stored query ID), or null.
     */
    public function getUsageReference(): ?string
    {
        return $this->usageReference;
    }

    /**
     * Login of the current user at call time. Scheduled tasks and the CLI run
     * as a system user, so this is not always a real person.
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * The resolved provider, not the requested one.
     */
    public function getProviderId(): string
    {
        return $this->providerId;
    }

    /**
     * The model the caller asked for, or null when the provider uses its
     * default or configured model. {@link AIUsage::getModel()} has the model
     * the provider reports back.
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * Output token limit of the request, so a listener can estimate the worst case.
     */
    public function getMaxOutputTokens(): int
    {
        return $this->maxOutputTokens;
    }

    /**
     * Whether the caller asked for the provider's web search. Whether it ran is
     * in {@link AIUsage::getWebSearchCalls()}.
     */
    public function isWebSearchEnabled(): bool
    {
        return $this->webSearchEnabled;
    }

    /**
     * Extra data the caller passed with {@link AIRequest::withMeta()} or
     * {@link AIConversationRequest::withMeta()}.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Whether this only asks if a call would be allowed
     * ({@link AIProviderService::assertRequestAllowed()}): no provider call
     * follows, so no `AIProviders.usage` event either.
     */
    public function isProbe(): bool
    {
        return $this->probe;
    }
}
