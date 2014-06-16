<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\CoreHome\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class VisitGoalBuyer extends VisitDimension
{
    static protected $visitEcommerceStatus = array(
        GoalManager::TYPE_BUYER_NONE                  => 'none',
        GoalManager::TYPE_BUYER_ORDERED               => 'ordered',
        GoalManager::TYPE_BUYER_OPEN_CART             => 'abandonedCart',
        GoalManager::TYPE_BUYER_ORDERED_AND_OPEN_CART => 'orderedThenAbandonedCart',
    );

    protected $fieldName = 'visit_goal_buyer';
    protected $fieldType = 'TINYINT(1) NOT NULL';

    protected function init()
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

    public function getName()
    {
        return '';
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return mixed
     */
    public function onNewVisit(Request $request, Visitor $visitor, $action)
    {
        $goalManager = new GoalManager($request);

        return $goalManager->getBuyerType();
    }

    /**
     * @param Request $request
     * @param Visitor $visitor
     * @param Action|null $action
     * @return int
     */
    public function onExistingVisit(Request $request, Visitor $visitor, $action)
    {
        $goalBuyer = $visitor->getVisitorColumn('visit_goal_buyer');

        // Ecommerce buyer status
        $goalManager          = new GoalManager($request);
        $visitEcommerceStatus = $goalManager->getBuyerType($goalBuyer);

        if($visitEcommerceStatus != GoalManager::TYPE_BUYER_NONE
            // only update if the value has changed (prevents overwriting the value in case a request has updated it in the meantime)
            && $visitEcommerceStatus != $goalBuyer) {
            return $visitEcommerceStatus;
        }

        return false;
    }

    static public function getVisitEcommerceStatus($status)
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
    static public function getVisitEcommerceStatusFromId($id)
    {
        if (!isset(self::$visitEcommerceStatus[$id])) {
            throw new \Exception("Unexpected ECommerce status value ");
        }
        return self::$visitEcommerceStatus[$id];
    }

}