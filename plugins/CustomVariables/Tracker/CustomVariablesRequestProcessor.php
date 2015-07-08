<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\Tracker;

use Piwik\Common;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * TODO
 *
 * TODO: document request metadata
 */
class CustomVariablesRequestProcessor extends RequestProcessor
{
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        // TODO: re-add optimization where if custom variables exist in request, don't bother selecting them in Visitor
        $visitorCustomVariables = $request->getCustomVariables($scope = 'visit');
        if (!empty($visitorCustomVariables)) {
            Common::printDebug("Visit level Custom Variables: ");
            Common::printDebug($visitorCustomVariables);
        }

        $visitProperties->setRequestMetadata('CustomVariables', 'visitCustomVariables', $visitorCustomVariables);
    }

    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        $visitCustomVariables = $visitProperties->getRequestMetadata('CustomVariables', 'visitCustomVariables');

        if (!empty($visitCustomVariables)) {
            $visitProperties->visitorInfo = array_merge($visitProperties->visitorInfo, $visitCustomVariables);
        }
    }

    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        $visitCustomVariables = $visitProperties->getRequestMetadata('CustomVariables', 'visitCustomVariables');

        if (!empty($visitCustomVariables)) {
            $valuesToUpdate = array_merge($valuesToUpdate, $visitCustomVariables);
        }
    }


}