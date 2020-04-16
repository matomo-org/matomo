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

class VisitorSecondsSinceOrder extends VisitDimension
{
    protected $columnName = 'visitor_seconds_since_order';
    protected $columnType = 'INT(11) UNSIGNED NULL';
    protected $segmentName = 'secondsSinceLastEcommerceOrder';
    protected $nameSingular = 'General_SecondsSinceLastEcommerceOrder';
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
        return $this->onExistingVisit($request, $visitor, $action);
    }

    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $idorder = $request->getParam('ec_id');
        $isOrder = !empty($idorder);
        if ($isOrder) {
            print "is order\n";
            return 0;
        }

        $secondsSinceLastOrder = $visitor->getVisitorColumn($this->columnName);
        if ($secondsSinceLastOrder === null) {
            print "no order yet\n";
            return null;
        }

        $visitsLastActionTime = Date::factory($visitor->getVisitorColumn('visit_last_action_time'))->getTimestamp();
        $secondsSinceLastAction = $request->getCurrentTimestamp() - $visitsLastActionTime;
print "$secondsSinceLastOrder - $visitsLastActionTime - $secondsSinceLastAction\n";
        return $secondsSinceLastOrder + $secondsSinceLastAction;
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
}