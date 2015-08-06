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
 * Handles conversion detection and tracking for tracker requests.
 *
 * ## Request Metadata
 *
 * This processor defines the following request metadata under the **Goals**
 * plugin:
 *
 * * **goalsConverted**: The array of goals that were converted by this request. Each element
 *                       will be an array of goal column value pairs. The ecommerce goal will
 *                       only have the idgoal column set.
 *
 *                       Set in `processRequestParams()`.
 *
 *                       Plugins can set this to empty to skip conversion recording.
 *
 * * **visitIsConverted**: If `true`, the current visit should be marked as "converted". Note:
 *                         some goal conversions (ie, ecommerce) do not mark the visit as
 *                         "converted", so it is possible for goalsConverted to be non-empty
 *                         while visitIsConverted is `false`.
 *
 *                         Set in `processRequestParams()`.
 */
class GoalsRequestProcessor extends RequestProcessor
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
        $this->goalManager = new GoalManager();

        if ($this->isManualGoalConversion($request)) {
            // this request is from the JS call to piwikTracker.trackGoal()
            $goal = $this->goalManager->detectGoalId($request->getIdSite(), $request);

            $visitIsConverted = !empty($goal);
            $request->setMetadata('Goals', 'visitIsConverted', $visitIsConverted);

            $existingConvertedGoals = $request->getMetadata('Goals', 'goalsConverted') ?: array();
            $request->setMetadata('Goals', 'goalsConverted', array_merge($existingConvertedGoals, array($goal)));

            $request->setMetadata('Actions', 'action', null); // don't track actions when doing manual goal conversions

            // if we find a idgoal in the URL, but then the goal is not valid, this is most likely a fake request
            if (!$visitIsConverted) {
                $idGoal = $request->getParam('idgoal');
                Common::printDebug('Invalid goal tracking request for goal id = ' . $idGoal);
                return true;
            }
        }

        return false;
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $goalsConverted = $request->getMetadata('Goals', 'goalsConverted');

        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        // if the visit hasn't already been converted another way (ie, manual goal conversion or ecommerce conversion,
        // try to convert based on the action)
        if (empty($goalsConverted)
            && $action
        ) {
            $goalsConverted = $this->goalManager->detectGoalsMatchingUrl($request->getIdSite(), $action);

            $existingGoalsConverted = $request->getMetadata('Goals', 'goalsConverted') ?: array();
            $request->setMetadata('Goals', 'goalsConverted', array_merge($existingGoalsConverted, $goalsConverted));

            if (!empty($goalsConverted)) {
                $request->setMetadata('Goals', 'visitIsConverted', true);
            }
        }

        // There is an edge case when:
        // - two manual goal conversions happen in the same second
        // - which result in handleExistingVisit throwing the exception
        //   because the UPDATE didn't affect any rows (one row was found, but not updated since no field changed)
        // - the exception is caught here and will result in a new visit incorrectly
        // In this case, we cancel the current conversion to be recorded:
        $isManualGoalConversion = $this->isManualGoalConversion($request);
        $requestIsEcommerce = $request->getMetadata('Goals', 'isRequestEcommerce');
        $visitorNotFoundInDb = $request->getMetadata('CoreHome', 'visitorNotFoundInDb');

        if ($visitorNotFoundInDb
            && ($isManualGoalConversion
                || $requestIsEcommerce)
        ) {
            $request->setMetadata('Goals', 'goalsConverted', array());
            $request->setMetadata('Goals', 'visitIsConverted', false);
        }

    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        // record the goals if there were conversions in this request (even if the visit itself was not converted)
        $goalsConverted = $request->getMetadata('Goals', 'goalsConverted');
        if (!empty($goalsConverted)) {
            $this->goalManager->recordGoals($visitProperties, $request);
        }
    }

    private function isManualGoalConversion(Request $request)
    {
        $idGoal = $request->getParam('idgoal');
        return $idGoal > 0;
    }
}
