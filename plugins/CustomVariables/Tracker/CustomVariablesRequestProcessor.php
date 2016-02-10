<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomVariables\Tracker;

use Piwik\Common;
use Piwik\Plugins\CustomVariables\Model;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Handles tracking of visit level custom variables.
 *
 * ### Request Metadata
 *
 * Defines the following request metadata for the **CustomVariables** plugin:
 *
 * * **visitCustomVariables**: An array of custom variable names & values. The data is stored
 *                             as log_visit column name/value pairs, eg,
 *
 *                             ```
 *                             array(
 *                                 'custom_var_k1' => 'the name',
 *                                 'custom_var_v1' => 'the value',
 *                                 ...
 *                             )
 *                             ```
 */
class CustomVariablesRequestProcessor extends RequestProcessor
{
    public function processRequestParams(VisitProperties $visitProperties, Request $request)
    {
        // TODO: re-add optimization where if custom variables exist in request, don't bother selecting them in Visitor
        $visitorCustomVariables = $request->getCustomVariables($scope = Model::SCOPE_VISIT);
        if (!empty($visitorCustomVariables)) {
            Common::printDebug("Visit level Custom Variables: ");
            Common::printDebug($visitorCustomVariables);
        }

        $request->setMetadata('CustomVariables', 'visitCustomVariables', $visitorCustomVariables);
    }

    public function onNewVisit(VisitProperties $visitProperties, Request $request)
    {
        $visitCustomVariables = $request->getMetadata('CustomVariables', 'visitCustomVariables');

        if (!empty($visitCustomVariables)) {
            $visitProperties->setProperties(array_merge($visitProperties->getProperties(), $visitCustomVariables));
        }
    }

    public function onExistingVisit(&$valuesToUpdate, VisitProperties $visitProperties, Request $request)
    {
        $visitCustomVariables = $request->getMetadata('CustomVariables', 'visitCustomVariables');

        if (!empty($visitCustomVariables)) {
            $valuesToUpdate = array_merge($valuesToUpdate, $visitCustomVariables);
        }
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $action = $request->getMetadata('Actions', 'action');

        if (empty($action) || !($action instanceof Action)) {
            return;
        }

        $customVariables = $action->getCustomVariables();

        if (!empty($customVariables)) {
            Common::printDebug("Page level Custom Variables: ");
            Common::printDebug($customVariables);

            foreach ($customVariables as $field => $value) {
                $action->setCustomField($field, $value);
            }
        }

    }
}
