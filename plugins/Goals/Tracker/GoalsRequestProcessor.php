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
use Piwik\Tracker\Visitor;

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
        $visitsConverted = $visitProperties->getRequestMetadata('Goals', 'visitIsConverted'); // TODO: double check, should this be visitIsConverted or someGoalsConverted?

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

        $someGoalsConverted = $visitProperties->getRequestMetadata('Goals', 'someGoalsConverted');
        if ($someGoalsConverted) {
            self::$goalManager->detectIsThereExistingCartInVisit($visitProperties->visitorInfo);
        }
    }

    public function processRequest(Visitor $visitor, VisitProperties $visitProperties)
    {
        $isManualGoalConversion = self::$goalManager->isManualGoalConversion();
        $requestIsEcommerce = self::$goalManager->requestIsEcommerce;

        $visitorNotFoundInDb = $visitProperties->getRequestMetadata('CoreHome', 'visitorNotFoundInDb');

        // There is an edge case when:
        // - two manual goal conversions happen in the same second
        // - which result in handleExistingVisit throwing the exception
        //   because the UPDATE didn't affect any rows (one row was found, but not updated since no field changed)
        // - the exception is caught here and will result in a new visit incorrectly
        // In this case, we cancel the current conversion to be recorded:
        if ($visitorNotFoundInDb
            && ($isManualGoalConversion
                || $requestIsEcommerce)
        ) {
            $visitProperties->setRequestMetadata('Goals', 'someGoalsConverted', false);
            $visitProperties->setRequestMetadata('Goals', 'visitIsConverted', false);
        }

        // record the goals if there were conversions in this request (even if the visit itself was not converted)
        if ($visitProperties->getRequestMetadata('Goals', 'someGoalsConverted')) {
            /** @var Action $action */
            $action = $visitProperties->getRequestMetadata('Actions', 'action');

            self::$goalManager->recordGoals(
                $visitor,
                $visitProperties->visitorInfo,
                $visitProperties->getRequestMetadata('CustomVariables', 'visitCustomVariables'),
                $action
            );
        }
    }
}
