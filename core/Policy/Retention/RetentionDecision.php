<?php

declare(strict_types=1);

namespace Piwik\Policy\Retention;

final class RetentionDecision
{
    /** @var int */
    public $days;

    /** @var bool */
    public $locked;

    /** @var string|null */
    public $lockedByPolicy;

    /** @var string|null */
    public $reason;

    /** @var string */
    public $source; // "compliance" | "site" | "instance"

    public function __construct(
        int $days,
        bool $locked,
        ?string $lockedByPolicy,
        ?string $reason,
        string $source
    ) {
        $this->days = $days;
        $this->locked = $locked;
        $this->lockedByPolicy = $lockedByPolicy;
        $this->reason = $reason;
        $this->source = $source;
    }
}
