<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock;

class Tracker extends \Piwik\Tracker
{
    private $isDebugEnabled = false;
    private $shouldRecord   = true;

    public function enableDebugMode()
    {
        $this->isDebugEnabled = true;
    }

    public function isDebugModeEnabled()
    {
        return $this->isDebugEnabled;
    }

    public function disableShouldRecordStatistics()
    {
        $this->shouldRecord = false;
    }

    public function shouldRecordStatistics()
    {
        return $this->shouldRecord;
    }
}
