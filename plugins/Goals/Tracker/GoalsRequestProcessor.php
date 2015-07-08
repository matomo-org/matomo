<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Tracker;

use Piwik\Common;
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
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        $goalManager = new GoalManager($request); // TODO: GoalManager should be stateless and stored in DI.

        if ($goalManager->isManualGoalConversion()) {
            // this request is from the JS call to piwikTracker.trackGoal()
            $someGoalsConverted = $goalManager->detectGoalId($request->getIdSite());

            $visitProperties->setRequestMetadata('Goals', 'someGoalsConverted', $someGoalsConverted);
            $visitProperties->setRequestMetadata('Goals', 'visitIsConverted', $someGoalsConverted);

            // if we find a idgoal in the URL, but then the goal is not valid, this is most likely a fake request
            if (!$someGoalsConverted) {
                Common::printDebug('Invalid goal tracking request for goal id = ' . $goalManager->idGoal);
                return true;
            }
        }

        return false;
    }
}