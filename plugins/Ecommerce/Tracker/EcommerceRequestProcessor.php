<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Tracker;

use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Handles ecommerce tracking requests.
 *
 * ## Request Metadata
 *
 * This processor defines the following request metadata under the **Ecommerce**
 * plugin:
 *
 * * **isRequestEcommerce**: If `true`, the request is for an ecommerce goal conversion.
 *
 *                           Set in `processRequestParams()`.
 *
 * * **isGoalAnOrder**: If `true` the request is tracking an ecommerce order.
 *
 *                      Set in `processRequestParams()`.
 */
class EcommerceRequestProcessor extends RequestProcessor
{
    /**
     * @var GoalManager
     */
    public $goalManager = null;

    public function __construct(GoalManager $goalManager)
    {
        $this->goalManager = $goalManager;
    }

    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        $isGoalAnOrder = $this->isRequestForAnOrder($request);
        $request->setMetadata('Ecommerce', 'isGoalAnOrder', $isGoalAnOrder);

        $isRequestEcommerce = $this->isRequestEcommerce($request);
        $request->setMetadata('Ecommerce', 'isRequestEcommerce', $isRequestEcommerce);

        if ($isRequestEcommerce) {
            // Mark the visit as Converted only if it is an order (not for a Cart update)
            $idGoal = GoalManager::IDGOAL_CART;
            if ($isGoalAnOrder) {
                $idGoal = GoalManager::IDGOAL_ORDER;
                $request->setMetadata('Goals', 'visitIsConverted', true);
            }

            $request->setMetadata('Goals', 'goalsConverted', array(array('idgoal' => $idGoal)));

            $request->setMetadata('Actions', 'action', null); // don't track actions when tracking ecommerce orders
        }
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $goalsConverted = $request->getMetadata('Goals', 'goalsConverted');
        if (!empty($goalsConverted)) {
            $isThereExistingCartInVisit = $this->goalManager->detectIsThereExistingCartInVisit(
                $visitProperties->getProperties());
            $request->setMetadata('Goals', 'isThereExistingCartInVisit', $isThereExistingCartInVisit);
        }
    }

    private function isRequestForAnOrder(Request $request)
    {
        $orderId = $request->getParam('ec_id');
        return !empty($orderId);
    }

    private function isRequestEcommerce(Request $request)
    {
        $idGoal = $request->getParam('idgoal');
        return 0 == $idGoal;
    }
}
