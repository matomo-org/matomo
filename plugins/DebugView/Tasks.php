<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView;

use Piwik\Plugins\DebugView\Model\DebugRequests;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var DebugRequests
     */
    private $debugRequests;

    public function __construct(DebugRequests $debugRequests)
    {
        $this->debugRequests = $debugRequests;
    }

    public function schedule()
    {
        $this->hourly('trimRawRequests');
    }

    /**
     * Trims the raw tracking request storage to MAX_ROWS_PER_SITE rows per
     * site. Runs hourly so individual tracking requests never pay for cleanup.
     */
    public function trimRawRequests()
    {
        $this->debugRequests->trimAllSites();
    }
}
