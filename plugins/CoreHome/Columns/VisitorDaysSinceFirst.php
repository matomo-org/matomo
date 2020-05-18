<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;

class VisitorDaysSinceFirst extends VisitDimension
{
    protected $type = self::TYPE_NUMBER;
    protected $sqlSegment = 'ROUND(log_visit.visitor_seconds_since_first / 86400)';
    protected $nameSingular = 'General_DaysSinceFirstVisit';
    protected $columnName = 'visitor_seconds_since_first';
    protected $segmentName = 'daysSinceFirstVisit';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('daysSinceFirstVisit');
        $segment->setName('General_DaysSinceFirstVisit');
        $segment->setCategory('General_Visitors');
        $segment->setSqlSegment('log_visit.visitor_seconds_since_first');
        $segment->setSqlFilterValue(function ($value) {
            return (int)$value * 86400;
        });
        $this->addSegment($segment);
    }
}