<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Matomo\Network\IP;

class BlockListIpRange
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function __construct(SystemSettings $settings)
    {
        $this->settings = $settings;
    }

    public function isBlocked($ip)
    {
        $rangesBlocked = $this->settings->getBlockListIpRanges();

        if (!empty($rangesBlocked)) {
            $ip  = IP::fromStringIP($ip);
            return $ip->isInRanges($rangesBlocked);
        }

        return false;
    }
}
