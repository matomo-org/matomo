<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\Model;

use Piwik\Option;
use Piwik\Plugins\DebugView\API;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;

/**
 * Business logic for the raw tracking request capture shown in Debug View.
 *
 * Rows are only written while at least one user is actively watching Debug View
 * (see markSiteActive()) and only for requests flagged with debug=1. An hourly
 * scheduled task (see Tasks) trims each site's rows to MAX_ROWS_PER_SITE and
 * purges rows older than the longest window the API can display. All SQL lives
 * in Dao\RawRequestLog.
 */
class DebugRequests
{
    /**
     * Cap on the number of stored raw requests per site, enforced by the hourly
     * trim task.
     */
    public const MAX_ROWS_PER_SITE = 500;

    /**
     * Prefix of the per-site option holding the UTC timestamp until which
     * capturing is active for that site. One option row per site, so arming
     * one site can never overwrite another site's state (a shared map would
     * lose updates when viewers of different sites poll concurrently). Read
     * via direct queries on the option table: the tracker side must bypass
     * the tracker cache (so arming never invalidates it), and the reader side
     * must not trigger Option's load-all-options behaviour on every poll.
     */
    public const OPTION_ACTIVE_PREFIX = 'DebugView.rawRequestActiveSite.';

    /**
     * How long one poll of the UI keeps capturing active, in seconds. Polls
     * re-arm the flag well before it expires.
     */
    public const ACTIVE_SECONDS = 180;

    /**
     * Longest stored parameters JSON; anything bigger is dropped (a normal
     * tracking request is far below this).
     */
    public const MAX_PARAMS_LENGTH = 65535;

    /**
     * Longest stored value of a single parameter; anything longer is truncated.
     * Keeps oversized payloads (e.g. Heatmap & Session Recording requests) and
     * attacker-controlled values from bloating the storage.
     */
    public const MAX_PARAM_VALUE_LENGTH = 1000;

    // plain dots on purpose: a word like "truncated" would need translating
    public const TRUNCATION_MARKER = '...';

    /**
     * @var RawRequestLog
     */
    private $dao;

    public function __construct(RawRequestLog $dao)
    {
        $this->dao = $dao;
    }

    /**
     * Marks capturing as active for a site for the next ACTIVE_SECONDS seconds.
     * Called from the reading API on every poll; only writes the site's own
     * option when the stored state actually needs to change (roughly once a
     * minute per active site). Sites are armed fully independently — no other
     * site's state is ever read or written here. Never touches any tracker
     * cache. Expired options are removed by the hourly trim task.
     */
    public function markSiteActive(int $idSite): void
    {
        $now = time();

        if ($this->getActiveUntilTimestamp($idSite) < $now + 60) {
            Option::set(self::OPTION_ACTIVE_PREFIX . $idSite, (string) ($now + self::ACTIVE_SECONDS));
        }
    }

    /**
     * Reader-side: UTC timestamp until which capturing is armed for the site,
     * 0 when it is not armed at all.
     */
    public function getActiveUntilTimestamp(int $idSite): int
    {
        return (int) $this->dao->getOptionValue(self::OPTION_ACTIVE_PREFIX . $idSite);
    }

    /**
     * Tracker-side check whether capturing is armed for a site. Only runs for
     * requests already carrying debug=1, so the single indexed SELECT is paid
     * exclusively by explicit debug traffic while normal tracking traffic and
     * the shared tracker cache stay completely untouched.
     */
    public function isSiteActiveForTracker(int $idSite): bool
    {
        $until = (int) $this->dao->getOptionValue(self::OPTION_ACTIVE_PREFIX . $idSite);

        return time() <= $until;
    }

    /**
     * Tracker-side: stores one raw request. Deliberately does no trimming so a
     * tracking request never pays for cleanup; the hourly scheduled task
     * (Tasks::trimRawRequests) enforces the caps. Any failure is swallowed by
     * the caller so tracking is never impacted.
     */
    public function insertFromTracker(
        int $idSite,
        ?int $idVisit,
        ?int $idLinkVisitAction,
        int $serverTimestamp,
        array $parameters,
        array $defaultParameters = [],
        array $otherParameters = [],
        ?int $actionType = null,
        ?array $botInfo = null
    ): void {
        $data = [
            'query'      => $this->truncateOversizedValues($parameters),
            'defaults'   => $this->truncateOversizedValues($defaultParameters),
            'other'      => $this->truncateOversizedValues($otherParameters),
            'actionType' => $actionType,
        ];
        if ($botInfo !== null) {
            // requests captured on the tracker's bot path: nothing was
            // recorded for them, the group marks them and names the bot
            $data['bot'] = $this->truncateOversizedValues($botInfo);
        }

        $json = json_encode($data);
        if ($json === false || strlen($json) > self::MAX_PARAMS_LENGTH) {
            return;
        }

        $this->dao->insert($idSite, $idVisit, $idLinkVisitAction, $serverTimestamp, $json);
    }

    /**
     * Trims the storage, run by the hourly scheduled task: deletes every
     * request older than the longest window the API can display
     * (API::MAX_LAST_MINUTES), then caps each site at its newest
     * MAX_ROWS_PER_SITE rows.
     *
     * @return int number of deleted rows
     */
    public function trimAllSites(): int
    {
        $deleted = $this->dao->deleteOlderThan(time() - (API::MAX_LAST_MINUTES * 60));
        $deleted += $this->dao->trimToNewestPerSite(self::MAX_ROWS_PER_SITE);

        // housekeeping, not counted as deleted requests: drop the arming
        // options of sites nobody watches anymore
        $this->dao->deleteExpiredActiveSiteOptions(self::OPTION_ACTIVE_PREFIX, time());

        return $deleted;
    }

    /**
     * Reader-side: raw requests of one site newer than the given UTC timestamp,
     * optionally only rows with an id strictly greater than $minId. Never
     * returns more than the newest MAX_ROWS_PER_SITE matching rows, so a
     * flooded stream cannot blow up the response or the decoding work.
     *
     * @return array<int, array{idrawrequest: string, idvisit: string|null, idlink_va: string|null, server_time: string, parameters: string}>
     */
    public function getForSite(int $idSite, int $minServerTimestamp, int $minId = 0): array
    {
        return $this->dao->getForSite($idSite, $minServerTimestamp, $minId, self::MAX_ROWS_PER_SITE);
    }

    /**
     * Caps one site's storage at its newest MAX_ROWS_PER_SITE rows. Runs on
     * every stream poll: capturing only happens for watched sites, and the
     * polling viewer paying two small indexed queries keeps the storage bound
     * hard without adding any work to the tracker. The hourly task remains the
     * safety net for sites nobody watches anymore.
     *
     * @return int number of deleted rows
     */
    public function trimSite(int $idSite): int
    {
        return $this->dao->trimSiteToNewest($idSite, self::MAX_ROWS_PER_SITE);
    }

    /**
     * Decodes one stored `parameters` value into
     * ['query' => array, 'defaults' => array|null, 'other' => array|null,
     * 'actionType' => int|null, 'bot' => array|null]. A non-null 'bot' group
     * marks a request captured on the tracker's bot path. Returns null when
     * nothing usable is stored.
     *
     * @return array{query: array, defaults: array|null, other: array|null, actionType: int|null, bot: array|null}|null
     */
    public function decodeStoredParameters(string $storedJson): ?array
    {
        $decoded = json_decode($storedJson, true);
        if (!is_array($decoded) || !isset($decoded['query']) || !is_array($decoded['query'])) {
            return null;
        }

        $defaults = $decoded['defaults'] ?? null;
        $other = $decoded['other'] ?? null;
        $actionType = $decoded['actionType'] ?? null;
        $bot = $decoded['bot'] ?? null;

        return [
            'query'      => $decoded['query'],
            'defaults'   => is_array($defaults) && !empty($defaults) ? $defaults : null,
            'other'      => is_array($other) && !empty($other) ? $other : null,
            'actionType' => is_numeric($actionType) ? (int) $actionType : null,
            'bot'        => is_array($bot) && !empty($bot) ? $bot : null,
        ];
    }

    /**
     * Truncates every string value longer than MAX_PARAM_VALUE_LENGTH
     * characters (multibyte-safe — a byte-based cut could produce invalid
     * UTF-8 and make the whole row unencodable). Applied to query and default
     * parameters alike so oversized payloads such as Heatmap & Session
     * Recording requests, or deliberately bloated attacker values, never
     * store more than the limit per parameter.
     */
    public function truncateOversizedValues(array $values, int $depth = 0): array
    {
        foreach ($values as $key => $value) {
            if (is_string($value) && mb_strlen($value) > self::MAX_PARAM_VALUE_LENGTH) {
                $values[$key] = mb_substr($value, 0, self::MAX_PARAM_VALUE_LENGTH) . self::TRUNCATION_MARKER;
            } elseif (is_array($value)) {
                $values[$key] = $depth < 3 ? $this->truncateOversizedValues($value, $depth + 1) : [];
            }
        }

        return $values;
    }
}
