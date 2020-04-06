<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Date;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugin\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

// TODO: update that keeps old data? is that possible? maybe we need to keep both and use one if available, other if it isn't.
//       that's annoying. you know what, let's create an issue.
class VisitorDaysSinceOrder extends VisitDimension
{
    protected $columnName = 'visitor_last_order_time';
    protected $columnType = 'DATETIME UNSIGNED NULL';
    protected $segmentName = 'lastOrderTime'; // TODO: segment + keep existing (daysSinceLastEcommerceOrder)
    protected $nameSingular = 'General_DaysSinceLastEcommerceOrder'; // TODO: modify
    protected $category = 'General_Visitors'; // todo put into ecommerce category?
    protected $type = self::TYPE_NUMBER;

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $isOrder = $request->getParam('ec_id');
        if (!empty($isOrder)) {
            return Date::now()->getDatetime();
        }

        return $visitor->getPreviousVisitColumn('visitor_last_order_time') ?: null;
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $isOrder = $request->getParam('ec_id');
        if (!empty($isOrder)) {
            return Date::now()->getDatetime();
        }

        return $visitor->getVisitorColumn($this->columnName);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onAnyGoalConversion(Request $request, Visitor $visitor, $action)
    {
        return $visitor->getVisitorColumn($this->columnName);
    }

    protected function addSegment(Segment $segment)
    {
        parent::addSegment($segment);

        // TODO: add daysSinceLastEcommerceOrder segment (visitor_last_order_time -
        //       FUCK. this might screw me.
    }
}