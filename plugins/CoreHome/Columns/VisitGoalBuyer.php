<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitGoalBuyer extends VisitDimension
{
    // log_visit.visit_goal_buyer
    const TYPE_BUYER_NONE = 0;
    const TYPE_BUYER_ORDERED = 1;
    const TYPE_BUYER_OPEN_CART = GoalManager::TYPE_BUYER_OPEN_CART;
    const TYPE_BUYER_ORDERED_AND_OPEN_CART = GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART;

    protected static $visitEcommerceStatus = array(
        self::TYPE_BUYER_NONE                  => 'none',
        self::TYPE_BUYER_ORDERED               => 'ordered',
        self::TYPE_BUYER_OPEN_CART             => 'abandonedCart',
        self::TYPE_BUYER_ORDERED_AND_OPEN_CART => 'orderedThenAbandonedCart',
    );

    protected $columnName = 'visit_goal_buyer';
    protected $columnType = 'TINYINT(1) NOT NULL';

    protected function configureSegments()
    {
        $example = Piwik::translate('General_EcommerceVisitStatusEg', '"&segment=visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart"');
        $acceptedValues = implode(", ", self::$visitEcommerceStatus) . '. ' . $example;

        $segment = new Segment();
        $segment->setSegment('visitEcommerceStatus');
        $segment->setName('General_EcommerceVisitStatusDesc');
        $segment->setAcceptedValues($acceptedValues);
        $segment->setSqlFilterValue(__NAMESPACE__ . '\VisitGoalBuyer::getVisitEcommerceStatus');

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
        return $this->getBuyerType($request);
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $goalBuyer = $visitor->getVisitorColumn($this->columnName);

        // Ecommerce buyer status
        $visitEcommerceStatus = $this->getBuyerType($request, $goalBuyer);

        if ($visitEcommerceStatus != self::TYPE_BUYER_NONE
            // only update if the value has changed (prevents overwriting the value in case a request has
            // updated it in the meantime)
            && $visitEcommerceStatus != $goalBuyer) {

            return $visitEcommerceStatus;
        }

        return false;
    }

    public static function getVisitEcommerceStatus($status)
    {
        $id = array_search($status, self::$visitEcommerceStatus);

        if ($id === false) {
            throw new \Exception("Invalid 'visitEcommerceStatus' segment value $status");
        }

        return $id;
    }

    /**
     * @ignore
     */
    public static function getVisitEcommerceStatusFromId($id)
    {
        if (!isset(self::$visitEcommerceStatus[$id])) {
            throw new \Exception("Unexpected ECommerce status value ");
        }

        return self::$visitEcommerceStatus[$id];
    }

    private function getBuyerType(Request $request, $existingType = self::TYPE_BUYER_NONE)
    {
        $goalManager = new GoalManager($request);

        if (!$goalManager->requestIsEcommerce) {
            return $existingType;
        }

        if ($goalManager->isGoalAnOrder()) {
            return self::TYPE_BUYER_ORDERED;
        }

        // request is Add to Cart
        if ($existingType == self::TYPE_BUYER_ORDERED
            || $existingType == self::TYPE_BUYER_ORDERED_AND_OPEN_CART
        ) {
            return self::TYPE_BUYER_ORDERED_AND_OPEN_CART;
        }

        return self::TYPE_BUYER_OPEN_CART;
    }

}
