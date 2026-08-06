<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

class Tasks extends \Piwik\Plugin\Tasks
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

    public function schedule()
    {
        $this->daily('updateBlockedIpRanges');
    }

    /**
     * To test execute the following command:
     * `./console core:run-scheduled-tasks --force "Piwik\Plugins\TrackingSpamPrevention\Tasks.updateBlockedIpRanges"`
     *
     */
    public function updateBlockedIpRanges()
    {
        if ($this->systemSettings->block_clouds->getValue()) {
            $this->blockedIpRanges->updateBlockedIpRanges();
        } else {
            // we also unset any banned IP every 24 hours
            $this->blockedIpRanges->unsetAllIpRanges();
        }
    }
}
