<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\Tracker;

use Exception;
use Piwik\Access;
use Piwik\Cache as PiwikCache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Exception\AuthenticationFailedException;
use Piwik\Http\BadRequestException;
use Piwik\Piwik;
use Piwik\Plugins\TrackingSpamPrevention\AllowListIpRange;
use Piwik\Plugins\TrackingSpamPrevention\BlockedIpRanges;
use Piwik\Plugins\TrackingSpamPrevention\SystemSettings;
use Piwik\Request\AuthenticationToken;
use Piwik\Tracker\Request;
use Piwik\Tracker;
use Piwik\Tracker\Visit\VisitProperties;

class RequestProcessor extends Tracker\RequestProcessor
{
    /**
     * @var SystemSettings
     */
    private $systemSettings;
    /**
     * @var BlockedIpRanges
     */
    private $blockedIpRanges;

    public function __construct(SystemSettings $systemSettings, BlockedIpRanges $blockedIpRanges)
    {
        $this->systemSettings = $systemSettings;
        $this->blockedIpRanges = $blockedIpRanges;
    }

    public function afterRequestProcessed(VisitProperties $visitProperties, Request $request)
    {
        $actions = $visitProperties->getProperty('visit_total_actions');
        $maxActions = $this->systemSettings->max_actions->getValue();

        if (empty($maxActions) || !is_numeric($maxActions) || $maxActions <= 0) {
            return false; // unlimited
        }
        if ((int)$actions >= $maxActions) {
            $ipString = $request->getIpString();
            if (StaticContainer::get(AllowListIpRange::class)->isAllowed($ipString)) {
                Common::printDebug("Ignoring max visits as the visit matches an IP range that is always allowed");
                return false;
            }

            // The ban applies to the whole installation, so an IP set via cip is only banned when the token
            // has Super User access. Tokens scoped to some sites only stop tracking of this request.
            if (!empty($request->getParam('cip')) && !$this->isSuperUserTokenAuth($request)) {
                Common::printDebug("Stop tracking as max number of actions reached, not banning IP set via cip without Super User token_auth");
                return true; // abort
            }

            $this->blockedIpRanges->banIp($ipString);

            Common::printDebug("Stop tracking as max number of actions reached");
            return true; // abort
        }

        return false;
    }

    /**
     * The token lookup mirrors Request::authenticateTrackingApi(). Access is checked even when
     * tracking_requests_require_authentication is disabled, as it is for every bulk tracking request.
     */
    private function isSuperUserTokenAuth(Request $request): bool
    {
        $tokenAuth = $request->getTokenAuth();

        try {
            if (empty($tokenAuth)) {
                $tokenAuth = StaticContainer::get(AuthenticationToken::class)->getAuthToken($request->getParams());
            }

            if (empty($tokenAuth)) {
                $tokenAuth = StaticContainer::get(AuthenticationToken::class)->getAuthToken();
            }
        } catch (AuthenticationFailedException | BadRequestException $e) {
            return false;
        }

        if (empty($tokenAuth)) {
            return false;
        }

        // cached per request so a bulk request only checks each token once
        $cache = PiwikCache::getTransientCache();
        $cacheKey = 'TrackingSpamPrevention_isSuperUserTokenAuth_' . $tokenAuth;

        if ($cache->contains($cacheKey)) {
            return (bool) $cache->fetch($cacheKey);
        }

        try {
            Piwik::postEvent('Request.initAuthenticationObject');

            /** @var \Piwik\Auth $auth */
            $auth = StaticContainer::get('Piwik\Auth');
            $auth->setTokenAuth($tokenAuth);
            // null, not '', makes Login\Auth authenticate by token only, as in Request::authenticateSuperUserOrAdminOrWrite()
            $auth->setLogin(null); // @phpstan-ignore argument.type
            $auth->setPassword(null);
            $auth->setPasswordHash(null);

            $access = new Access();
            $isSuperUser = $access->reloadAccess($auth) && $access->hasSuperUserAccess();
        } catch (Exception $e) {
            Common::printDebug("Could not check token_auth for Super User access: " . $e->getMessage());
            return false;
        }

        $cache->save($cacheKey, $isSuperUser);

        return $isSuperUser;
    }
}
