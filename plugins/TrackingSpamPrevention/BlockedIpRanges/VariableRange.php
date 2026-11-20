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
class VariableRange implements IpRangeProviderInterface
{
    /**
     * @var string[]
     */
    private $ranges;

    public function __construct($ranges)
    {
        $this->ranges = $ranges;
    }

    public function getRanges(): array
    {
        return $this->ranges;
    }
}
