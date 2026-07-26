<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\Tracker;

use DeviceDetector\Parser\Bot;
use Piwik\Tracker\Request;

/**
 * Captures debug-flagged requests that the tracker routes down its bot path
 * (Piwik\Tracker\BotRequest), where the normal request processors' recordLogs
 * stage never runs. Bot requests record no visit or action, so the captured
 * row carries no idvisit/idlink_va and is marked with the detected bot's name
 * instead — the UI shows the raw parameters but no processed details.
 */
class BotRequestProcessor extends \Piwik\Tracker\BotRequestProcessor
{
    /**
     * @var RequestCapture
     */
    private $capture;

    public function __construct(RequestCapture $capture)
    {
        $this->capture = $capture;
    }

    public function handleRequest(Request $request): bool
    {
        try {
            if ($this->capture->shouldCapture($request)) {
                $this->capture->capture(
                    $request,
                    $idVisit = null,
                    $idLinkVisitAction = null,
                    $actionType = null,
                    ['name' => $this->getBotName($request)]
                );
            }
        } catch (\Exception $e) {
            // avoid debug capturing break tracking
        }

        // this processor only captures debug data. The tracker ORs the return
        // values of all bot request processors, so other processors that
        // record the request as usual still count as having handled it —
        // returning false here only avoids adding an archive invalidation
        // trigger of our own, it never suppresses normal recording
        return false;
    }

    /**
     * Name of the detected bot. The device detector instance the tracker used
     * for its bot routing decision discards bot details, so the (much
     * lighter) bot parser runs once more — only captured debug requests pay
     * for it.
     */
    public function getBotName(Request $request): string
    {
        try {
            $botParser = new Bot();
            // getUserAgent() returns false when the request carries no user
            // agent at all
            $botParser->setUserAgent((string) $request->getUserAgent());
            $bot = $botParser->parse();

            return is_array($bot) && !empty($bot['name']) ? (string) $bot['name'] : '';
        } catch (\Exception $e) {
            return '';
        }
    }
}
