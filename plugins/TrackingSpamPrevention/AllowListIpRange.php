<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Matomo\Network\IP;

class AllowListIpRange
{
    /**
     * @var SystemSettings
     */
    private $settings;

    public function __construct(SystemSettings $settings)
    {
        $this->settings = $settings;
    }

    public function isAllowed($ip)
    {
        $rangesAllowed = $this->settings->getAllowedIpRanges();

        if (!empty($rangesAllowed)) {
            $ip  = IP::fromStringIP($ip);
            return $ip->isInRanges($rangesAllowed);
        }

        return false;
    }
}
