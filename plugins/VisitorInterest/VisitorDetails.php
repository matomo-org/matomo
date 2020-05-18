<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $visitor['daysSinceLastVisit'] = floor($this->details['visitor_seconds_since_last'] / 86400);
        $visitor['secondsSinceLastVisit'] = $this->details['visitor_seconds_since_last'];
    }
}