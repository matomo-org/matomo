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
 * Starts as allowed, so nothing changes when no plugin listens. A listener can
 * {@link deny()} but there is no way to allow again, so one listener cannot
 * overrule another's denial whatever order they run in. The first denial
 * wins and later ones are ignored. A listener that allows can instead
 * {@link reportUsage()}; only {@link AIProviderService::checkFeatureAllowed()}
 * and {@link AIProviderService::probeRequest()} return an allowed decision to
 * their caller, which may show the figures and act on the limit. A denial
 * reaches callers either way.
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
     * @var AIUsageAction|null
     */
    private $action = null;

    /**
     * Denies the request or feature. Replaces what an earlier {@link reportUsage()} set, its action
     * included, so a denial carries only its own link.
     *
     * @param string             $reason    Machine-readable reason, for example `'limit_reached'` or `'disabled'`.
     * @param string|null        $message   Translated text to show the user, or null for a generic message.
     * @param int|null           $used      Units used so far.
     * @param int|null           $limit     Allowance, in the units the feature key defines. Callers may act on
     *                                      it, not only show it, so it must be the real allowance.
     * @param int|null           $remaining Units left.
     * @param AIUsageAction|null $action    A link that raises the allowance, for example "Buy more credits".
     *                                      Only pass one the current user can follow.
     */
    public function deny(
        string $reason,
        ?string $message = null,
        ?int $used = null,
        ?int $limit = null,
        ?int $remaining = null,
        ?AIUsageAction $action = null
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
        $this->action = $action;
    }

    /**
     * Reports usage figures for an allowed request or feature, so callers can show them without a
     * denial, for example "20 of 25 prompts used". Ignored once denied, which keeps its own figures.
     *
     * When several listeners report, the one with the least remaining wins, and on a tie the later one.
     * A listener reporting no remaining (unlimited) never replaces one that did, but a report without
     * remaining is not stricter than anything, even with a limit: a later report may replace it. A
     * report with no figures at all is ignored. The message and action travel with the figures they
     * came with.
     *
     * @param int|null           $used      Units used so far.
     * @param int|null           $limit     Allowance, in the units the feature key defines. Callers may act on it,
     *                                      not only show it, so it must be the real allowance.
     * @param int|null           $remaining Units left.
     * @param string|null        $message   Translated plain text about this usage, for example when the allowance renews.
     * @param AIUsageAction|null $action    A link that raises the allowance. Only pass one the current user can follow.
     */
    public function reportUsage(
        ?int $used,
        ?int $limit,
        ?int $remaining,
        ?string $message = null,
        ?AIUsageAction $action = null
    ): void {
        // A report saying nothing must not wipe one that said something.
        if (!$this->allowed || ($used === null && $limit === null && $remaining === null)) {
            return;
        }

        if ($this->remaining !== null && ($remaining === null || $remaining > $this->remaining)) {
            return;
        }

        $this->used = $used;
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->message = $message;
        $this->action = $action;
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * The denial's text, or the text that came with the reported usage.
     */
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

    /**
     * The link that came with the denial or with the reported usage.
     */
    public function getAction(): ?AIUsageAction
    {
        return $this->action;
    }
}
