<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Heartbeat\Tracker;

use Piwik\Common;
use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Handles ping tracker requests.
 *
 * Defines no request metadata.
 */
class PingRequestProcessor extends RequestProcessor
{
    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        if ($this->isPingRequest($request)) {
            // on a ping request that is received before the standard visit length, we just update the visit time w/o adding a new action
            Common::printDebug("-> ping=1 request: we do not track a new action nor a new visit nor any goal.");
            $request->setMetadata('Actions', 'action', null);
            $request->setMetadata('Goals', 'goalsConverted', array());
            $request->setMetadata('Goals', 'visitIsConverted', false);

            // When a ping request is received more than 30 min after the last request/ping,
            // we choose not to create a new visit.
            if ($request->getMetadata('CoreHome', 'isNewVisit')) {
                Common::printDebug("-> ping=1 request: we do _not_ create a new visit.");
                return true; // abort request
            }
        }

        return false;
    }

    private function isPingRequest(Request $request)
    {
        return $request->getParam('ping') == 1;
    }
}
