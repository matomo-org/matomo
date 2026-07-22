<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Tracker;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\TrackerConfig;
use Piwik\Tracker\Visit\VisitProperties;

/**
 * Writes accurate per-pageview time-spent rows to log_page_view_time.
 *
 * Designed for the hot tracker path:
 *  - Single SQL statement per call (no SELECTs on the write path).
 *  - UPSERT keyed on (idvisit, idlink_va) so a retried tracker request for the same
 *    log_link_visit_action row is idempotent, and multi-tab attribution relies on the
 *    per-action idlink_va rather than the potentially-shared pv_id (a single page-view
 *    plus its site-search hit share the same pv_id but have distinct idlink_va values).
 *  - The visit_standard_length cap is applied in SQL so we never need a round-trip to read it
 *    or compute it client-side per row.
 *
 * Per-tab accuracy is best-effort: the class assumes the browser supplies a per-tab `pv_id`.
 * When it is present, every hit reaches the exact tab's row. Non-pageview hits with no
 * `pv_id` are a no-op: events use their own TYPE_EVENT idaction (different from the page's
 * TYPE_PAGE_URL idaction), so matching by idaction_url would never find the pageview row.
 *
 * Note: `closePreviousPageView()` picks the most recent row in the visit that is not the
 * currently-inserting one. With two tabs open, a new pageview in tab A closes whichever
 * *earlier* row was most recent — that may be tab B, meaning tab B is credited with any
 * time up to A's server_time. This is an approximation; treat it as "reasonable" rather
 * than "per-tab exact" for interleaved multi-tab sessions.
 *
 * Accepted residual inaccuracies (deliberate, do not "fix" without revisiting the design):
 *  - Cross-midnight visits (only possible with the non-default
 *    `create_new_visit_after_midnight = 0`): a day-2 hit can close a day-1 row after day 1
 *    was already archived, leaving that archive stale while the anti-join drops the day-2
 *    legacy credit. Core accepts the same staleness class for visit metrics under that
 *    setting (a continuing visit changes yesterday's aggregates without invalidation), and
 *    invalidating here would re-archive yesterday every day on such installs. With the
 *    default setting both UPDATEs are scoped to the current visit, which cannot span
 *    midnight, so day-1 rows are never touched from day 2.
 *  - Partial failure: if this writer's INSERT fails while the same hit's
 *    log_link_visit_action INSERT succeeded, the visit has a recorded action without a pvt
 *    row; a later close can then grow the previous row across the gap while the legacy path
 *    still credits the missing action, overcounting that interval once. No ordering of, or
 *    transaction around, the pvt statements alone can prevent this (the inconsistency is
 *    between llva and pvt), and coupling the two would violate the fault isolation above.
 */
class PageViewTimeWriter
{
    public const TABLE = 'log_page_view_time';
    public const CONFIG_KEY = 'record_accurate_page_view_time';
    public const PARAM_PV_TIME = 'pv_time';
    public const DEFAULT_VISIT_STANDARD_LENGTH = 1800;

    /**
     * @return bool True when the tracker should write to log_page_view_time. The kill-switch is
     *              read via TrackerConfig so it picks up per-INI overrides without restart,
     *              including per-site `[Tracker_N]` sections when an idSite is given.
     */
    public static function isEnabled(?int $idSite = null): bool
    {
        $value = TrackerConfig::getConfigValue(self::CONFIG_KEY, $idSite);
        return $value === null ? true : (bool) (int) $value;
    }

    /**
     * @return int visit_standard_length in seconds, used as the SQL-side cap on time_spent.
     *             The cap is applied at write time only: the archiver sums the already-capped
     *             values and does not re-cap.
     */
    private static function getVisitStandardLength(): int
    {
        $value = Config::getInstance()->Tracker['visit_standard_length'] ?? self::DEFAULT_VISIT_STANDARD_LENGTH;
        return (int) $value;
    }

    public function write(?Action $action, VisitProperties $visitProperties, Request $request): void
    {
        $idVisit = (int) $visitProperties->getProperty('idvisit');
        if ($idVisit <= 0) {
            return;
        }

        $idSite = (int) $request->getIdSite();

        // idpageview is CHAR(6) CHARACTER SET ascii, so a pv_id containing non-ASCII bytes
        // (crafted request or non-JS SDK; the byte-based substr can even split a multibyte
        // character) would make the INSERT fail under strict SQL mode. The JS tracker only
        // generates [0-9a-zA-Z]{6}; treat anything else as absent rather than lose the row.
        $pvId = substr((string) $request->getParam('pv_id'), 0, 6);
        $pvId = preg_match('/^[0-9a-zA-Z]{1,6}$/D', $pvId) ? $pvId : null;

        $serverTimeSql = date('Y-m-d H:i:s', (int) $request->getCurrentTimestamp());
        $cap = self::getVisitStandardLength();
        $clientTimeOnPage = $this->extractClientTimeOnPage($request, $cap);

        if ($action !== null && $this->isRecordableAction($action)) {
            $idLinkVa = (int) $action->getIdLinkVisitAction();
            if ($idLinkVa <= 0) {
                // No log_link_visit_action row was produced (recording was skipped upstream);
                // we would have nothing to key on for the anti-join. Bail out.
                return;
            }

            // Close the previous row in this visit before inserting the new one. This is the
            // cheaper alternative to a correlated-subquery backfill at archive time: at most
            // one indexed UPDATE per recorded hit, and the archive query can stay flat.
            $this->closePreviousPageView($idVisit, $idLinkVa, $serverTimeSql, $cap);

            $this->insertPageView(
                $idSite,
                $idVisit,
                $idLinkVa,
                $pvId,
                (int) $action->getIdActionUrl(),
                (int) $action->getIdActionName(),
                $serverTimeSql
            );
            return;
        }

        // Non-recorded hit: ping, event, content, outlink, download, page title only, etc.
        // Without pv_id we cannot safely attribute to a specific tab (events log their own
        // TYPE_EVENT idaction, so matching by idaction_url here would never find the pageview
        // row). Skip rather than touch the wrong row.
        if ($pvId === null) {
            return;
        }

        $this->updateTimeSpent($idVisit, $pvId, $serverTimeSql, $cap, $clientTimeOnPage);
    }

    /**
     * Read the optional client-provided time-on-page value from `pv_time` (seconds).
     *
     * When the tracker JS counts focused time itself (e.g. a custom in-page counter) it can send
     * the number directly with each follow-up tracker request (heartbeat / event / etc. — hits
     * that carry the same `pv_id` as the pageview). {@see updateTimeSpent()} then trusts it as
     * the authoritative value for that hit and skips the server-side `now − server_time`
     * calculation. Multiple hits with `pv_time` settle via the same `GREATEST()` rule everything
     * else uses, so a later hit can only grow the recorded time.
     *
     * On a brand-new pageview request the value is ignored — pageview rows are inserted at
     * `time_spent = 0` and only later same-tab hits update them.
     *
     * Returns null when the param is missing OR non-positive. Zero is treated as "no client
     * override" (not "the user spent 0s here"): a stored `time_spent = 0` is already reserved
     * by the archiver — see ActionReports::archiveDayActionsTime() — as the "not measured yet"
     * sentinel that triggers the last-page fallback to `visit_last_action_time − server_time`.
     * Trusting a client `0` would create a visitor-log vs Actions-report divergence on the last
     * page of a visit. Values above `Tracker.visit_standard_length` are clamped rather than
     * nulled — a client saying "3600s" gets recorded as `visit_standard_length`.
     */
    private function extractClientTimeOnPage(Request $request, int $cap): ?int
    {
        // `pv_time` is registered as an int with default -1 in Request::getParam(), so the
        // value we get back is guaranteed to be int. See `core/Tracker/Request.php`.
        $seconds = $request->getParam(self::PARAM_PV_TIME);
        if ($seconds <= 0) {
            return null;
        }
        if ($seconds > $cap) {
            $seconds = $cap;
        }
        return $seconds;
    }

    private function isRecordableAction(Action $action): bool
    {
        $type = (int) $action->getActionType();
        return $type === Action::TYPE_PAGE_URL || $type === Action::TYPE_SITE_SEARCH;
    }

    private function closePreviousPageView(
        int $idVisit,
        int $newIdLinkVa,
        string $serverTimeSql,
        int $cap
    ): void {
        $table = Common::prefixTable(self::TABLE);
        $db = Tracker::getDatabase();

        // Find the most-recent prior row in this visit (excluding the currently-inserting row,
        // which is not yet in the table but on retry can already be present via ON DUPLICATE).
        // Ordering by (server_time, idpageviewtime) is deterministic even when concurrent
        // requests arrive out of chronological order.
        //
        // The nested SELECT with a derived table is a workaround for the MySQL restriction that
        // an UPDATE cannot reference the target table directly in a subquery; it also lets us
        // avoid `UPDATE ... ORDER BY ... LIMIT 1` (unsafe under statement-based binlog replication).
        //
        // GREATEST() ensures out-of-order heartbeats can only grow time_spent; LEAST() caps it
        // at visit_standard_length so a stalled tab can't inflate one page.
        //
        // The cap is interpolated as a numeric literal (not a `?` bind param) because PDO_MYSQL
        // emulated prepares (the tracker default) sends bound integers as quoted strings, and
        // `LEAST('1800', 25)` triggers MySQL's string comparison rule ('1800' < '25' lexically)
        // so the cap wins even when the diff is well below it. `$cap` is a validated int from
        // Tracker config, safe to inline.
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST($cap, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                 WHERE idpageviewtime = (
                        SELECT idpageviewtime FROM (
                            SELECT idpageviewtime FROM `$table`
                             WHERE idvisit = ?
                               AND idlink_va <> ?
                               AND server_time < ?
                          ORDER BY server_time DESC, idpageviewtime DESC
                             LIMIT 1
                        ) t
                       )";
        $db->query($sql, [$serverTimeSql, $idVisit, $newIdLinkVa, $serverTimeSql]);
    }

    private function insertPageView(
        int $idSite,
        int $idVisit,
        int $idLinkVa,
        ?string $pvId,
        int $idActionUrl,
        int $idActionName,
        string $serverTimeSql
    ): void {
        $table = Common::prefixTable(self::TABLE);
        $db = Tracker::getDatabase();

        // Single-statement upsert keyed on (idvisit, idlink_va). Idempotent for a retried tracker
        // request: the second run refreshes idaction_url / idaction_name without disturbing
        // time_spent accumulated between the two runs (heartbeats between the retries can only
        // grow it via GREATEST() in updateTimeSpent()).
        $sql = "INSERT INTO `$table`
                    (idsite, idvisit, idlink_va, idpageview, idaction_url, idaction_name, server_time, time_spent)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE
                    idaction_url  = VALUES(idaction_url),
                    idaction_name = VALUES(idaction_name)";
        $db->query($sql, [
            $idSite,
            $idVisit,
            $idLinkVa,
            $pvId,
            $idActionUrl > 0 ? $idActionUrl : null,
            $idActionName > 0 ? $idActionName : null,
            $serverTimeSql,
        ]);
    }

    private function updateTimeSpent(
        int $idVisit,
        string $pvId,
        string $serverTimeSql,
        int $cap,
        ?int $clientTimeOnPage
    ): void {
        $table = Common::prefixTable(self::TABLE);
        $db = Tracker::getDatabase();

        // When the client supplied its own time-on-page measurement, trust it as the source of
        // truth for this hit — useful for trackers that measure focused-only time, which the
        // server cannot infer from request timestamps. We still GREATEST() so out-of-order or
        // smaller client values can't shrink an earlier larger observation.
        //
        // The bind param is wrapped in CAST(? AS UNSIGNED) because PDO_MYSQL emulated prepares
        // (the tracker default) send bound integers as quoted strings. Without the cast, any
        // string operand forces LEAST()/GREATEST() into lexicographic comparison — LEAST('1800',
        // '25') then returns '1800' and silently pins time_spent at the cap. The cast forces
        // integer comparison regardless of how PDO transmits the value.
        if ($clientTimeOnPage !== null) {
            $sql = "UPDATE `$table`
                       SET time_spent = LEAST($cap, GREATEST(time_spent, CAST(? AS UNSIGNED)))
                     WHERE idpageviewtime = (
                            SELECT idpageviewtime FROM (
                                SELECT idpageviewtime FROM `$table`
                                 WHERE idvisit = ? AND idpageview = ?
                              ORDER BY server_time DESC, idpageviewtime DESC
                                 LIMIT 1
                            ) t
                           )";
            $db->query($sql, [$clientTimeOnPage, $idVisit, $pvId]);
            return;
        }

        // A page-view and its site-search hit share the same pv_id (the JS tracker does not
        // rotate pv_id between them), so (idvisit, pv_id) can match multiple rows. Update the
        // most-recent one — that is the currently-active row for this tab.
        //
        // The derived-table form avoids the statement-based-binlog warning that
        // `UPDATE ... ORDER BY ... LIMIT 1` triggers. `$cap` is inlined for the same reason as
        // in closePreviousPageView(): PDO emulated prepares would send it as a quoted string.
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST($cap, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                 WHERE idpageviewtime = (
                        SELECT idpageviewtime FROM (
                            SELECT idpageviewtime FROM `$table`
                             WHERE idvisit = ? AND idpageview = ?
                          ORDER BY server_time DESC, idpageviewtime DESC
                             LIMIT 1
                        ) t
                       )";
        $db->query($sql, [$serverTimeSql, $idVisit, $pvId]);
    }
}
