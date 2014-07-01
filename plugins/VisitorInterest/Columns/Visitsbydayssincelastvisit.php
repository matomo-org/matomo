<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitsByDaysSinceLastVisit extends VisitDimension
{
    protected $columnName = 'visitor_days_since_last';
    protected $columnType = 'SMALLINT(5) UNSIGNED NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('daysSinceLastVisit');
        $segment->setName('General_DaysSinceLastVisit');
        $segment->setType(Segment::TYPE_METRIC);

        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('VisitorInterest_VisitsByDaysSinceLast');
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return $request->getDaysSinceLastVisit();
    }

}