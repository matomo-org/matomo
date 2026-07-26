<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\Tracker;

use Piwik\Tracker\Request;
use Piwik\Tracker\RequestProcessor;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Stores the raw parameters of incoming tracking requests while at least one
 * user is watching Debug View. Runs after the core processors (plugin load
 * order), so the recorded action's idlink_va is available for correlating the
 * raw request with the hit shown in the stream. Bot requests never reach this
 * processor — they are captured by BotRequestProcessor on the tracker's bot
 * path instead.
 */
class RawRequestProcessor extends RequestProcessor
{
    /**
     * @var RequestCapture
     */
    private $capture;

    public function __construct(RequestCapture $capture)
    {
        $this->capture = $capture;
    }

    public function recordLogs(VisitProperties $visitProperties, Request $request)
    {
        try {
            if (!$this->capture->shouldCapture($request)) {
                return;
            }

            $idLinkVisitAction = null;
            $actionType = null;
            $action = $request->getMetadata('Actions', 'action');
            if ($action && method_exists($action, 'getIdLinkVisitAction') && $action->getIdLinkVisitAction()) {
                $idLinkVisitAction = (int) $action->getIdLinkVisitAction();
            }
            if (!$action) {
                // several plugins (MediaAnalytics, FormAnalytics, CrashAnalytics,
                // HeatmapSessionRecording, Heartbeat) null the recorded action in
                // their request processors so no log_link_visit_action row is
                // written; re-derive the Action solely to learn the request kind
                // (Action::factory only constructs objects, it records nothing)
                try {
                    $action = \Piwik\Tracker\Action::factory($request);
                } catch (\Exception $e) {
                    $action = null;
                }
            }
            if ($action && method_exists($action, 'getActionType')) {
                // the Action subclass the tracker chose knows the kind of request,
                // including types of other plugins (media, form, crash, ...)
                $actionType = (int) $action->getActionType();
            }

            $idVisit = $visitProperties->getProperty('idvisit');

            $this->capture->capture(
                $request,
                !empty($idVisit) ? (int) $idVisit : null,
                $idLinkVisitAction,
                $actionType
            );
        } catch (\Exception $e) {
            // avoid debug capturing breaking tracking
        }
    }
}
