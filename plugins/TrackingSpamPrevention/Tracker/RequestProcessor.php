<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\Tracker;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\TrackingSpamPrevention\AllowListIpRange;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;

class RequestProcessor extends Tracker\RequestProcessor
{
    /**
     * @var SystemSettings
     */
    private $systemSettings;
    /**
     * @var BlockedIpRanges
     */
    private $blockedIpRanges;

    public function __construct(SystemSettings $systemSettings, BlockedIpRanges $blockedIpRanges)
    {
        $this->systemSettings = $systemSettings;
        $this->blockedIpRanges = $blockedIpRanges;
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $actions = $visitProperties->getProperty('visit_total_actions');
        $maxActions = $this->systemSettings->max_actions->getValue();

        if (empty($maxActions) || !is_numeric($maxActions) || $maxActions <= 0) {
            return; // unlimited
        }
        if ((int)$actions >= $maxActions) {
            $ipString = $request->getIpString();
            if (StaticContainer::get(AllowListIpRange::class)->isAllowed($ipString)) {
                Common::printDebug("Ignoring max visits as the visit matches an IP range that is always allowed");
                return;
            }

            $this->blockedIpRanges->banIp($ipString);

            Common::printDebug("Stop tracking as max number of actions reached");
            return true; // abort
        }
    }
}
