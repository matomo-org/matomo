<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Columns;

use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;
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
    protected $columnType = 'TINYINT(1) NULL';
    protected $segmentName = 'visitEcommerceStatus';
    protected $nameSingular = 'General_EcommerceVisitStatusDesc';
    protected $type = self::TYPE_ENUM;

    public function __construct()
    {
        $example = Piwik::translate('General_EcommerceVisitStatusEg', '"&segment=visitEcommerceStatus==ordered,visitEcommerceStatus==orderedThenAbandonedCart"');
        $this->acceptValues = implode(", ", self::$visitEcommerceStatus) . '. ' . $example;
    }

    public function formatValue($value, $idSite, Formatter $formatter)
    {
        switch ($value) {
            case 'none';
            case '0':
            case self::TYPE_BUYER_NONE:
                return Piwik::translate('UserCountryMap_None');
            case 'ordered':
            case '1':
            case self::TYPE_BUYER_ORDERED:
                return Piwik::translate('CoreHome_VisitStatusOrdered');
            case 'abandonedCart':
            case self::TYPE_BUYER_OPEN_CART:
                return Piwik::translate('Goals_AbandonedCart');
            case 'orderedThenAbandonedCart':
            case self::TYPE_BUYER_ORDERED_AND_OPEN_CART:
                return Piwik::translate('CoreHome_VisitStatusOrderedThenAbandoned');
        }

        return $value;
    }

    public function getEnumColumnValues()
    {
        return self::$visitEcommerceStatus;
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
        $isRequestEcommerce = $request->getMetadata('Ecommerce', 'isRequestEcommerce');
        if (!$isRequestEcommerce) {
            return $existingType;
        }

        $isGoalAnOrder = $request->getMetadata('Ecommerce', 'isGoalAnOrder');
        if ($isGoalAnOrder) {
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
