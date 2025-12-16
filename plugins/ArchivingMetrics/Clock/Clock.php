<?php

namespace Piwik\Plugins\ArchivingMetrics\Clock;

final class Clock implements ClockInterface
{
    public function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public function microtime(): float
    {
        return microtime(true);
    }
}
