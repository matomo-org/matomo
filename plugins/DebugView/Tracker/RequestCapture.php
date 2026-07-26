<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\Tracker;

use Piwik\Common;
use Piwik\Http;
use Piwik\Plugins\DebugView\Model\DebugRequests;
use Piwik\Tracker\Request;

/**
 * Shared capture logic for both tracker paths: normal visit requests
 * (RawRequestProcessor) and bot requests (BotRequestProcessor). Applies the
 * capture gates, redacts sensitive parameters and hands the request over to
 * the model for storage.
 */
class RequestCapture
{
    /**
     * Request parameters that must never be persisted.
     */
    public const REDACTED_PARAMS = ['token_auth'];

    /**
     * @var DebugRequests
     */
    private $debugRequests;

    public function __construct(DebugRequests $debugRequests)
    {
        $this->debugRequests = $debugRequests;
    }

    /**
     * Whether this request should be captured. The free in-memory debug=1 gate
     * runs first (mirroring GA4's debug_mode), so normal tracking traffic pays
     * no cost beyond a comparison; only explicitly flagged debug traffic pays
     * the single indexed armed-site SELECT. Callers should check this before
     * doing any capture-only work of their own.
     */
    public function shouldCapture(Request $request): bool
    {
        $parameters = $request->getRawParams();
        if (!is_array($parameters) || empty($parameters)) {
            return false;
        }

        if ((string) ($parameters['debug'] ?? '') !== '1') {
            return false;
        }

        $idSite = $request->getIdSiteIfExists();

        return !empty($idSite) && $this->debugRequests->isSiteActiveForTracker((int) $idSite);
    }

    /**
     * Redacts and stores one request. Expects the caller to have verified
     * shouldCapture() first; the ids/type describe what the tracker recorded
     * for the request (null for bot requests, where nothing is recorded), and
     * $botInfo marks requests captured on the bot path.
     */
    public function capture(
        Request $request,
        ?int $idVisit,
        ?int $idLinkVisitAction,
        ?int $actionType,
        ?array $botInfo = null
    ): void {
        $parameters = $request->getRawParams();

        foreach (self::REDACTED_PARAMS as $redacted) {
            if (array_key_exists($redacted, $parameters)) {
                $parameters[$redacted] = '__redacted__';
            }
        }

        $this->debugRequests->insertFromTracker(
            (int) $request->getIdSiteIfExists(),
            $idVisit,
            $idLinkVisitAction,
            // receipt time, deliberately not the request's event timestamp
            // (getCurrentTimestamp() honours cdt): the stream shows requests
            // as they ARRIVE, so a queued/backdated request received now must
            // appear now — its event time stays visible as the cdt parameter
            time(),
            $parameters,
            $this->getDefaultParameters(),
            $this->getOtherParameters($request),
            $actionType,
            $botInfo
        );
    }

    /**
     * Passively received request data that is not part of the tracking URL but
     * still influences tracking, shown as "Default Parameters" in the details
     * pane.
     */
    public function getDefaultParameters(): array
    {
        return [
            'userAgent'          => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'browserLanguage'    => (string) Common::getBrowserLanguage(),
            'clientHints'        => Http::getClientHintsFromServerVariables(),
            'serverTimeReceived' => time(),
        ];
    }

    /**
     * Other request state derived while tracking, shown as "Other" in the
     * details pane.
     */
    public function getOtherParameters(Request $request): array
    {
        return [
            'isAuthenticated' => (bool) $request->isAuthenticated(),
        ];
    }
}
