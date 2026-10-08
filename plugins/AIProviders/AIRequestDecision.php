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
 * Whether an AI request or feature may run, as decided by the listeners of
 * `AIProviders.beforeRequest` and `AIProviders.checkFeatureAllowed`.
 *
 * Starts as allowed, so nothing changes when no plugin listens. Listeners can
 * only {@link deny()}: there is no way to allow again, so one listener cannot
 * overrule another's denial whatever order they run in. The first denial
 * wins and later ones are ignored.
 */
final class AIRequestDecision
{
    /**
     * @var bool
     */
    private $allowed = true;

    /**
     * @var string|null
     */
    private $reason = null;

    /**
     * @var string|null
     */
    private $message = null;

    /**
     * @var int|null
     */
    private $used = null;

    /**
     * @var int|null
     */
    private $limit = null;

    /**
     * @var int|null
     */
    private $remaining = null;

    /**
     * Denies the request or feature.
     *
     * @param string      $reason    Machine-readable reason, for example `'limit_reached'` or `'disabled'`.
     * @param string|null $message   Translated text to show the user, or null for a generic message.
     * @param int|null    $used      Units used so far, for display only.
     * @param int|null    $limit     Allowance, for display only. Its meaning is up to the listener.
     * @param int|null    $remaining Units left, for display only.
     */
    public function deny(
        string $reason,
        ?string $message = null,
        ?int $used = null,
        ?int $limit = null,
        ?int $remaining = null
    ): void {
        if (!$this->allowed) {
            return;
        }

        $this->allowed = false;
        $this->reason = $reason;
        $this->message = $message;
        $this->used = $used;
        $this->limit = $limit;
        $this->remaining = $remaining;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getUsed(): ?int
    {
        return $this->used;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getRemaining(): ?int
    {
        return $this->remaining;
    }
}
