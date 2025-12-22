<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

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
