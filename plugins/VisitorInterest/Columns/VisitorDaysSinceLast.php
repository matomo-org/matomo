<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitorInterest\Columns;

use Piwik\Columns\Dimension;
use Piwik\Plugin\Segment;

class VisitorDaysSinceLast extends Dimension
{
    protected $segmentName = 'secondsSinceLastVisit';
    protected $columnName = 'visitor_seconds_since_last';
    protected $type = self::TYPE_NUMBER;
    protected $nameSingular = 'General_DaysSinceLastVisit';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('daysSinceLastVisit');
        $segment->setName('General_DaysSinceLastVisit');
        $segment->setCategory('General_Visitors');
        $segment->setSqlFilter('log_visit.visitor_seconds_since_last');
        $segment->setSqlFilterValue(function ($value) {
            return (int)$value * 86400;
        });
        $this->addSegment($segment);
    }
}