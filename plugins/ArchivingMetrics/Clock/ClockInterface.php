<?php

namespace Piwik\Plugins\ArchivingMetrics\Clock;

interface ClockInterface
{
    public function now(): string;
    public function microtime(): float;
}
