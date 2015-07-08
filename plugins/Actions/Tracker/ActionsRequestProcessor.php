<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Tracker;

use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * TODO
 *
 * TODO: document request metadata here (ie, 'actions')
 */
class ActionsRequestProcessor extends RequestProcessor
{
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        // normal page view, potentially triggering a URL matching goal
        $action = Action::factory($request);
        $action->writeDebugInfo();

        $visitProperties->setRequestMetadata('Actions', 'action', $action);
    }

    public function manipulateVisitProperties(VisitProperties $visitProperties, Request $request)
    {
        /** @var Action $action */
        $action = $visitProperties->getRequestMetadata('Actions', 'action');

        if (!empty($action)) { // other plugins can unset the action if they want
            $action->loadIdsFromLogActionTable();
        }
    }
}
