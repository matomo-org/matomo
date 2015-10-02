<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\Metrics\Formatter;
use Piwik\Plugins\CoreHome\Columns\VisitGoalBuyer;

class Visitor
{
    private $details = array();
    private $metricsFormatter = null;

    public function __construct($details)
    {
        $this->details = $details;
        $this->metricsFormatter = new Formatter();
    }

    public function getTimestampFirstAction()
    {
        return strtotime($this->details['visit_first_action_time']);
    }

    public function getVisitEcommerceStatusIcon()
    {
        $status = $this->getVisitEcommerceStatus();

        if (in_array($status, array('ordered', 'orderedThenAbandonedCart'))) {
            return "plugins/Morpheus/images/ecommerceOrder.gif";
        } elseif ($status == 'abandonedCart') {
            return "plugins/Morpheus/images/ecommerceAbandonedCart.gif";
        }
        // Note: it is important that there is no icon when there was no ecommerce conversion
        return null;
    }

    public function getVisitEcommerceStatus()
    {
        return VisitGoalBuyer::getVisitEcommerceStatusFromId($this->details['visit_goal_buyer']);
    }

    public function isVisitorGoalConverted()
    {
        return $this->details['visit_goal_converted'];
    }

    public function getVisitorGoalConvertedIcon()
    {
        return $this->isVisitorGoalConverted()
            ? "plugins/Morpheus/images/goal.png"
            : null;
    }

    public function getDaysSinceFirstVisit()
    {
        return $this->details['visitor_days_since_first'];
    }

    public function getDaysSinceLastEcommerceOrder()
    {
        return $this->details['visitor_days_since_order'];
    }

    public function getVisitorReturning()
    {
        $type = $this->details['visitor_returning'];
        return $type == 2
            ? 'returningCustomer'
            : ($type == 1
                ? 'returning'
                : 'new');
    }

    public function getVisitorReturningIcon()
    {
        $type = $this->getVisitorReturning();
        if ($type == 'returning'
            || $type == 'returningCustomer'
        ) {
            return "plugins/Live/images/returningVisitor.gif";
        }
        return null;
    }

    public function getVisitCount()
    {
        return $this->details['visitor_count_visits'];
    }

    public function getVisitLength()
    {
        return $this->details['visit_total_time'];
    }

    public function getVisitLengthPretty()
    {
        return $this->metricsFormatter->getPrettyTimeFromSeconds($this->details['visit_total_time'], true);
    }

    public function getUserId()
    {
        if (isset($this->details['user_id'])
            && strlen($this->details['user_id']) > 0) {
            return $this->details['user_id'];
        }
        return null;
    }
}
