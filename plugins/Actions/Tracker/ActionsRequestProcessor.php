<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Tracker;

use Piwik\Date;
use Piwik\Plugins\Contents\Actions\ActionContent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * Handles actions detection and recording during tracker requests.
 *
 * ## Request Metadata
 *
 * This RequestProcessor exposes the following metadata for the **Actions** plugin:
 *
 * **action**: Contains the `Action` instance that represents the action being tracked for
 *             the current tracking request.
 *
 *             Set in `processRequestParams()`.
 *
 *             Other RequestProcessors can unset this value to skip actions recording or
 *             change the value to change how they are recorded.
 *
 * **idReferrerActionUrl**: The idaction of the URL action that is the referrer for the action
 *                          being tracked.
 *
 *                          Set in `processRequestParams()`.
 *
 *                          Can be changed/unset to change the current action's referrer action.
 *
 * **idReferrerActionName**: The idaction of the name action that is the referrer for the action
 *                           being tracked.
 *
 *                           Set in `processRequestParams()`.
 *
 *                           Can be changed/unset to change the current action's referrer action.
 */
class ActionsRequestProcessor extends RequestProcessor
{
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        // normal page view, potentially triggering a URL matching goal
        $action = Action::factory($request);
        $action->writeDebugInfo();

        $request->setMetadata('Actions', 'action', $action);

        // save the exit actions of the last action in this visit as the referrer actions for the action being tracked.
        // when the visit is updated, these columns will be changed, so we have to do this before recordLogs
        $request->setMetadata('Actions', 'idReferrerActionUrl',
            $visitProperties->getProperty('visit_exit_idaction_url'));
        $request->setMetadata('Actions', 'idReferrerActionName',
            $visitProperties->getProperty('visit_exit_idaction_name'));
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        if (!empty($action)) { // other plugins can unset the action if they want
            $action->loadIdsFromLogActionTable();
            if ($action instanceof ActionContent && !$request->getParam('c_i') && !$this->shouldUpdateLastVisit($visitProperties)) {
                // impressions dont extend visit
                $request->setMetadata('Actions', 'doNotExtendVisit', true);
            }
        }
    }
    private function shouldUpdateLastVisit(VisitProperties $visitProperties)
    {
        $lastActionTime = $visitProperties->getProperty('visit_last_action_time');
        if (!empty($lastActionTime)) {
            // it is only numeric when directly being called afterRequestProcessed() and not eg handleExistingVisit
            // because the VisitLastActionTime dimension will overwrite the original value of the visitor.
            // we want to make sure to work on the value from the DB
            $lastActionTimeDate = Date::factory($lastActionTime)->addPeriod(1, 'minutes');
            if ($lastActionTimeDate->isEarlier(Date::now())) {
                // we update visit_last_action_time only if visit_last_action_time was updated more than 5 min ago
                // we do not update all the time or every minute as not needed and to save resources
                return true;
            }
        }
        return false;
    }

    /**
     * This method is called before updating an existing visit. You can set/change visit information
     * here to change what gets recorded in `log_visit`.
     *
     * Only implement this method if you cannot use a Dimension for the same thing.
     *
     * @param array &$valuesToUpdate
     * @param VisitProperties $visitProperties
     * @param Request $request
     */
    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        if ($request->getMetadata('Actions', 'doNotExtendVisit')) {
            unset($valuesToUpdate['visit_last_action_time']);
            unset($valuesToUpdate['visit_total_time']);
        }
    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        if ($action !== null
            && !$request->getMetadata('CoreHome', 'visitorNotFoundInDb')
        ) {
            $idReferrerActionUrl = 0;
            $idReferrerActionName = 0;

            if (!$request->getMetadata('CoreHome', 'isNewVisit')) {
                $idReferrerActionUrl = $request->getMetadata('Actions', 'idReferrerActionUrl');
                $idReferrerActionName = $request->getMetadata('Actions', 'idReferrerActionName');
            }

            $visitor = Visitor::makeFromVisitProperties($visitProperties, $request);
            $action->record($visitor, $idReferrerActionUrl, $idReferrerActionName);
        }
    }
}
