<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Tracker;

use Piwik\Common;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * TODO
 *
 * TODO: document request metadata here
 */
class GoalsRequestProcessor extends RequestProcessor
{
    /**
     * TODO: GoalManager should be stateless and stored in DI.
     *
     * @var GoalManager
     */
    public static $goalManager = null;

    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        self::$goalManager = new GoalManager($request);

        if (self::$goalManager->isManualGoalConversion()) {
            // this request is from the JS call to piwikTracker.trackGoal()
            $someGoalsConverted = self::$goalManager->detectGoalId($request->getIdSite());

            $visitProperties->setRequestMetadata('Goals', 'someGoalsConverted', $someGoalsConverted);
            $visitProperties->setRequestMetadata('Goals', 'visitIsConverted', $someGoalsConverted);

            $visitProperties->setRequestMetadata('Actions', 'action', null); // don't track actions when doing manual goal conversions

            // if we find a idgoal in the URL, but then the goal is not valid, this is most likely a fake request
            if (!$someGoalsConverted) {
                Common::printDebug('Invalid goal tracking request for goal id = ' . self::$goalManager->idGoal);
                return true;
            }
        }

        return false;
    }

    public function manipulateVisitProperties(VisitProperties $visitProperties, Request $request)
    {
        $visitsConverted = $visitProperties->getRequestMetadata('Goals', 'visitIsConverted');

        /** @var Action $action */
        $action = $visitProperties->getRequestMetadata('Actions', 'action');

        // if the visit hasn't already been converted another way (ie, manual goal conversion or ecommerce conversion,
        // try to convert based on the action)
        if (!$visitsConverted
            && $action
        ) {
            $someGoalsConverted = self::$goalManager->detectGoalsMatchingUrl($request->getIdSite(), $action);

            $visitProperties->setRequestMetadata('Goals', 'someGoalsConverted', $someGoalsConverted);
            $visitProperties->setRequestMetadata('Goals', 'visitIsConverted', $someGoalsConverted);
        }
    }
}
