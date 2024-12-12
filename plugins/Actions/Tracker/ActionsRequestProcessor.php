<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Tracker;

use Piwik\Common;
use Piwik\Tracker;
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
        $request->setMetadata(
            'Actions',
            'idReferrerActionUrl',
            $visitProperties->getProperty('visit_exit_idaction_url')
        );
        $request->setMetadata(
            'Actions',
            'idReferrerActionName',
            $visitProperties->getProperty('visit_exit_idaction_name')
        );
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        if (!empty($action)) { // other plugins can unset the action if they want
            $action->loadIdsFromLogActionTable();
        }
    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');
        if (
            $action !== null
            && !$request->getMetadata('CoreHome', 'visitorNotFoundInDb')
        ) {

            $this->upsertPageViewTime($action, $visitProperties, $request);

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

    private function upsertPageViewTime(Action $action, VisitProperties $visitProperties, Request $request) {

        $idVisit = $visitProperties->getProperty('idvisit');
        $idSite = $visitProperties->getProperty('idsite');
        $idActionName = $action->getIdActionName();
        $idActionUrl = $action->getIdActionUrl();

        $table = Common::prefixTable('log_timespent_pageview');
        $db = Tracker::getDatabase();

        if ($action->getActionType() === Action::TYPE_PAGE_URL) {
            // New pageview
            $currentTimestamp = $request->getCurrentTimestamp();

            // 1. Update the time_spent of the previous pageview (if it exists)
            $bindUpdatePrevious = [
                $currentTimestamp,
                (int) $idVisit,
                (int) $idSite,
                (int) $idVisit,
                (int) $idSite,
                $currentTimestamp
            ];
            $queryUpdatePrevious = <<<SQL
            UPDATE $table
            SET time_spent = ? - servertime
            WHERE idvisit = ? AND idsite = ?
            AND servertime = (
                SELECT MAX(servertime)
                FROM $table
                WHERE idvisit = ? AND idsite = ?
                  AND servertime <= ?
            );
SQL;
            $db->query($queryUpdatePrevious, $bindUpdatePrevious);

            // 2. Insert the new pageview
            $bindInsert = [
                (int) $idVisit,
                (int) $idSite,
                (int) $idActionName,
                (int) $idActionUrl,
                $currentTimestamp
            ];
            $queryInsert = <<<SQL
            INSERT INTO $table (idvisit, idsite, idaction_name, idaction_url, servertime, time_spent)
            VALUES (?, ?, ?, ?, ?, 0);
SQL;
            $db->query($queryInsert, $bindInsert);
        } else {
            // Update time spent for the latest record
            $bindUpdate = [
                $request->getCurrentTimestamp(),
                (int) $idVisit,
                (int) $idSite,
                (int) $idVisit,
                (int) $idSite
            ];
            $queryUpdate = <<<SQL
            UPDATE $table
            SET time_spent = ? - servertime
            WHERE idvisit = ? AND idsite = ?
            AND servertime = (
                SELECT MAX(servertime)
                FROM $table
                WHERE idvisit = ? AND idsite = ?
            );
SQL;
            $db->query($queryUpdate, $bindUpdate);
        }
    }
}
