<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitsSummary\tests\Fixtures;

use Piwik\Tests\Fixtures\VisitsOverSeveralDays;

/**
 * The same visits, from visitor IDs that are the same on every run, so the estimates built from them are too.
 */
class VisitsOverSeveralDaysWithFixedVisitorIds extends VisitsOverSeveralDays
{
    private $trackerCount = 0;

    protected function makeTracker($idSite, $dateTime, $debugStringAppend = '')
    {
        $tracker = parent::makeTracker($idSite, $dateTime, $debugStringAppend);
        $tracker->setVisitorId(substr(md5('visitor' . $this->trackerCount++), 0, 16));

        return $tracker;
    }
}
