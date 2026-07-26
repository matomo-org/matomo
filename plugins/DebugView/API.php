<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView;

use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\DebugView\Model\DebugRequests;
use Piwik\Plugins\Live\Live;
use Piwik\Site;

/**
 * API for the DebugView plugin. Streams recently received tracking requests
 * ("hits") of a single site, served directly from the captured raw requests.
 *
 * @method static \Piwik\Plugins\DebugView\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Longest look-back window the API can display; also the horizon after
     * which stored raw requests are useless and get purged by the trim task.
     */
    public const MAX_LAST_MINUTES = 60;

    /**
     * @var DebugRequests
     */
    private $debugRequests;

    /**
     * @var HitFlattener
     */
    private $hitFlattener;

    public function __construct(DebugRequests $debugRequests, HitFlattener $hitFlattener)
    {
        $this->debugRequests = $debugRequests;
        $this->hitFlattener = $hitFlattener;
    }

    /**
     * Returns the individual tracking requests ("hits") received for a site within the
     * last `$lastMinutes` minutes, sorted chronologically (oldest first, at most the
     * newest 500 per site). Only requests that were sent with the debug=1 URL parameter
     * while Debug View was being watched are returned — they are served straight from
     * the captured raw requests, so the cost of this method is independent of how much
     * traffic the site receives.
     *
     * Each hit contains:
     *
     * - `idRawRequest` (string): monotonic raw request id, a decimal string (BIGINT —
     *   do not parse into a float-based number type); use it as the `minId` cursor
     * - `idVisit` (string|null) and `idLinkVa` (int|null): references to lazily load
     *   the visit context through `Live.getLastVisitsDetails` with a `visitId==`
     *   segment; both are null when the tracker recorded no visit/action for the
     *   request (e.g. bot requests)
     * - `timestamp` (int): UTC unix timestamp of when the request was RECEIVED — a
     *   backdated event timestamp such as a queued request's cdt does not shift it
     *   out of the stream
     * - `timePretty` (string): the receipt time formatted in the site's timezone
     * - `type` (string): pageview|event|goal|download|outlink|search|ecommerceOrder|
     *   ecommerceAbandonedCart|content|ping|media|form|sessionRecording|crash|other
     * - `title` (string) and `subtitle` (string): stream labels derived from the
     *   request parameters
     * - `trackingParams` (array): the tracking request parameters as received, with
     *   two exceptions: `token_auth` is redacted to `__redacted__`, and any single
     *   value longer than 1000 characters is truncated with a trailing `...`
     * - `trackingParamsDefaults` (array|null): passively received request data
     *   (userAgent, browserLanguage, clientHints, serverTimeReceived), truncated the
     *   same way
     * - `trackingParamsOther` (array|null): request state derived while tracking,
     *   currently `isAuthenticated` (bool)
     * - `isBot` (bool) and `botName` (string|null): set for requests captured on the
     *   tracker's bot path; `botName` is null when the bot could not be named
     *
     * Note that capturing is not always on, so not every debug request is captured:
     * requests are only stored while at least one consumer of this method is watching
     * the site. The first call arms capturing for the next ~3 minutes and every further
     * call re-arms it. Requests sent before the first call are therefore not in the
     * stream, and the first response is typically empty — keep polling this method to
     * keep capturing active and pick up new hits via the `minId` cursor.
     *
     * @param int $idSite the site whose hits to stream; the user needs view access
     * @param int $lastMinutes how many minutes to look back (1-60)
     * @param int $minId incremental polling cursor: only hits with an id strictly
     *                   greater than this are returned (ids are monotonic)
     * @return array{hits: array<int, array<string, mixed>>, serverTime: int, timezone: string}
     *                   the captured hits as described above, the current UTC unix
     *                   timestamp on the server (`serverTime`, for client clock-offset
     *                   correction) and the site's timezone identifier (`timezone`)
     */
    public function getRecentHits(int $idSite, int $lastMinutes = 30, int $minId = 0): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        if (!Manager::getInstance()->isPluginActivated('Live')) {
            throw new \Exception(Piwik::translate('DebugView_LivePluginDisabledMessage'));
        }

        Live::checkIsVisitorLogEnabled($idSite);

        // someone is watching: capture raw tracking parameters for this site,
        // and keep the storage bound hard while doing so (the hourly task
        // alone would let a flooded stream grow for up to an hour)
        $this->debugRequests->markSiteActive($idSite);
        $this->debugRequests->trimSite($idSite);

        $lastMinutes = min(max($lastMinutes, 1), self::MAX_LAST_MINUTES);

        $now = Date::now()->getTimestamp();
        $windowStart = $now - ($lastMinutes * 60);
        $minId = max((int) $minId, 0);

        $timezone = Site::getTimezoneFor($idSite);

        $hits = [];
        foreach ($this->debugRequests->getForSite($idSite, $windowStart, $minId) as $row) {
            $stored = $this->debugRequests->decodeStoredParameters($row['parameters']);
            if ($stored === null) {
                continue;
            }

            $timestamp = (int) Date::factory($row['server_time'])->getTimestamp();

            try {
                $query = $stored['query'];
                $type = $this->hitFlattener->deriveType($query, $stored['actionType']);

                $hits[] = [
                    'idRawRequest' => (string) $row['idrawrequest'],
                    'idVisit'    => !empty($row['idvisit']) ? (string) $row['idvisit'] : null,
                    'idLinkVa'   => !empty($row['idlink_va']) ? (int) $row['idlink_va'] : null,
                    'timestamp'  => $timestamp,
                    'timePretty' => Date::factory($timestamp, $timezone)->getLocalized(Date::TIME_FORMAT),
                    'type'       => $type,
                    'title'      => $this->hitFlattener->buildTitle($query, $type),
                    'subtitle'   => $this->hitFlattener->buildSubtitle($query, $type),
                    'trackingParams'         => $query,
                    'trackingParamsDefaults' => $stored['defaults'],
                    'trackingParamsOther'    => $stored['other'],
                    'isBot'                  => $stored['bot'] !== null,
                    'botName'                => !empty($stored['bot']['name']) ? (string) $stored['bot']['name'] : null,
                ];
            } catch (\Throwable $e) {
                // stored parameters are attacker-controlled input: one
                // malformed row must never break the whole stream
                continue;
            }
        }

        usort($hits, function ($a, $b) {
            return [$a['timestamp'], (int) $a['idRawRequest']]
                <=> [$b['timestamp'], (int) $b['idRawRequest']];
        });

        return [
            'hits'       => $hits,
            'serverTime' => $now,
            'timezone'   => $timezone,
        ];
    }
}
