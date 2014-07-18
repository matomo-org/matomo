<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitGoalConverted extends VisitDimension
{
    protected $columnName = 'visit_goal_converted';
    protected $columnType = 'TINYINT(1) NOT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('visitConverted');
        $segment->setName('General_VisitConvertedGoal');
        $segment->setAcceptedValues('0, 1');
        $this->addSegment($segment);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        return 0;
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onConvertedVisit(Request $request, Visitor $visitor, $action)
    {
        return 1;
    }
}