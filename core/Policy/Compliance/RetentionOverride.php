<?php

declare(strict_types=1);

namespace Piwik\Policy\Compliance;

/**
 * specific class for representing the value of the retention setting
 */
final class RetentionOverride
{
    /** @var int */
    public $days;

    /** @var string */
    public $reason;

    public function __construct(int $days, string $reason)
    {
        $this->days = $days;
        $this->reason = $reason;
    }
}
