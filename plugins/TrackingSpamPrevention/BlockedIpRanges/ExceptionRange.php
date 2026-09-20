<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;

/**
 * For tests only
 */
class ExceptionRange implements IpRangeProviderInterface
{
    public function getRanges(): array
    {
        throw new \Exception('Failed to get any range');
    }
}
