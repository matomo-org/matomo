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
 * TODO
 */
class EcommerceRequestProcessor extends RequestProcessor
{
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        $goalManager = new GoalManager($request); // TODO: GoalManager should be stateless and stored in DI.

        if ($goalManager->requestIsEcommerce) {
            $visitProperties->setRequestMetadata('Goals', 'someGoalsConverted', true);

            // Mark the visit as Converted only if it is an order (not for a Cart update)
            if ($goalManager->isGoalAnOrder()) {
                $visitProperties->setRequestMetadata('Goals', 'visitIsConverted', true);
            }

            $visitProperties->setRequestMetadata('Actions', 'action', null); // don't track actions when tracking ecommerce orders
        }
    }
}