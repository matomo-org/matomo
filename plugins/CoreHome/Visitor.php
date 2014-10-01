<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Plugins\CoreHome\Columns\VisitGoalBuyer;

class Visitor
{
    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    function getTimestampFirstAction()
    {
        return strtotime($this->details['visit_first_action_time']);
    }

    function getVisitEcommerceStatusIcon()
    {
        $status = $this->getVisitEcommerceStatus();

        if (in_array($status, array('ordered', 'orderedThenAbandonedCart'))) {
            return "plugins/Morpheus/images/ecommerceOrder.gif";
        } elseif ($status == 'abandonedCart') {
            return "plugins/Morpheus/images/ecommerceAbandonedCart.gif";
        }
        return null;
    }

    function getVisitEcommerceStatus()
    {
        return VisitGoalBuyer::getVisitEcommerceStatusFromId($this->details['visit_goal_buyer']);
    }

    function isVisitorGoalConverted()
    {
        return $this->details['visit_goal_converted'];
    }

    function getVisitorGoalConvertedIcon()
    {
        return $this->isVisitorGoalConverted()
            ? "plugins/Morpheus/images/goal.png"
            : null;
    }

    function getDaysSinceFirstVisit()
    {
        return $this->details['visitor_days_since_first'];
    }

    function getDaysSinceLastEcommerceOrder()
    {
        return $this->details['visitor_days_since_order'];
    }

    function getVisitorReturning()
    {
        $type = $this->details['visitor_returning'];
        return $type == 2
            ? 'returningCustomer'
            : ($type == 1
                ? 'returning'
                : 'new');
    }

    function getVisitorReturningIcon()
    {
        $type = $this->getVisitorReturning();
        if ($type == 'returning'
            || $type == 'returningCustomer'
        ) {
            return "plugins/Live/images/returningVisitor.gif";
        }
        return null;
    }

    function getVisitCount()
    {
        return $this->details['visitor_count_visits'];
    }

    function getVisitLength()
    {
        return $this->details['visit_total_time'];
    }

    function getVisitLengthPretty()
    {
        return \Piwik\MetricsFormatter::getPrettyTimeFromSeconds($this->details['visit_total_time']);
    }

    function getUserId()
    {
        if (isset($this->details['user_id'])
            && strlen($this->details['user_id']) > 0) {
            return $this->details['user_id'];
        }
        return null;
    }

}