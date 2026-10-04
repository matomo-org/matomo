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
 * Attribution is per tab while the browser supplies `pv_id`. Without one, a hit closes the
 * most recent row in the visit instead, which is also the row the legacy path credits. With
 * two tabs open that most-recent row may belong to the other tab, so treat multi-tab
 * attribution as reasonable rather than exact.
 *
 * Accepted residual inaccuracies (deliberate, do not "fix" without revisiting the design):
 *  - Cross-midnight visits, only reachable with `create_new_visit_after_midnight = 0`: a
 *    day-2 hit can close a day-1 row after day 1 was archived, leaving that archive stale.
 *    Core accepts the same staleness for visit metrics under that setting, and invalidating
 *    here would re-archive yesterday every day.
 *  - Partial failure: if this writer's INSERT fails while the hit's log_link_visit_action
 *    INSERT succeeded, a later hit grows the previous row across the gap while the legacy
 *    path still credits the missing action, overcounting that interval once. Only the INSERT
 *    does this; a failed close leaves the row as it was, so the legacy value covers the rest
 *    unless an earlier hit already grew the row, which then suppresses it.
 */
class PageViewTimeWriter
{
    public const TABLE = 'log_page_view_time';
    public const CONFIG_KEY = 'record_accurate_page_view_time';
    public const PARAM_PV_TIME = 'pv_time';
    public const DEFAULT_VISIT_STANDARD_LENGTH = 1800;

    /**
     * Read via TrackerConfig so a per-site `[Tracker_N]` section can override it.
     */
    public static function isEnabled(?int $idSite = null): bool
    {
        $value = TrackerConfig::getConfigValue(self::CONFIG_KEY, $idSite);
        return $value === null ? true : (bool) (int) $value;
    }

    /**
     * Bounds one measurement, not a page's reported total: the archiver sums already-capped
     * rows, and the legacy metric it falls back to has never had a per-page bound either.
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

        // idpageview decides which row a later hit closes, so the whole value has to key it.
        // Truncating first would let pageview-1 and pageview-2 - which a site can set through
        // setPageViewId() - select the same row, and rejecting instead would drop attribution
        // for ids that are merely long, leaving a heartbeat with nothing to credit.
        //
        // So anything the column cannot hold verbatim is keyed by a stable hash of it. The
        // column is CHAR(6) ascii: a non-ASCII byte fails the INSERT under strict SQL mode,
        // and trailing spaces are stripped on comparison, so ids differing only there would
        // collide.
        $rawPvId = (string) $request->getParam('pv_id');
        if ($rawPvId === '') {
            $pvId = null;
        } elseif (preg_match('/^[\x21-\x7e]{1,6}$/D', $rawPvId)) {
            $pvId = $rawPvId;
        } else {
            $pvId = substr(md5($rawPvId), 0, 6);
        }

        $serverTimeSql = date('Y-m-d H:i:s', (int) $request->getCurrentTimestamp());
        $cap = self::getVisitStandardLength();

        if ($action !== null && $this->isRecordableAction($action)) {
            $idLinkVa = (int) $action->getIdLinkVisitAction();
            if ($idLinkVa <= 0) {
                // No log_link_visit_action row, so nothing for the anti-join to key on.
                return;
            }

            // Insert before closing: if the close fails, this row still exists at 0 and the
            // next hit closes it instead of reaching past it, which would count the gap twice.
            $this->insertPageView(
                $idSite,
                $idVisit,
                $idLinkVa,
                $pvId,
                (int) $action->getIdActionUrl(),
                (int) $action->getIdActionName(),
                $serverTimeSql
            );

            $this->closePreviousPageView($idVisit, $idLinkVa, $serverTimeSql, $cap);
            return;
        }

        // Non-recorded hit: ping, event, content, outlink, download, page title only, etc.
        // Without pv_id, fall back to closing the most recent row: that is the same page the
        // legacy path credits, since only pageviews and site searches move the visit exit ids.
        if ($pvId === null) {
            if ($action === null || (int) $action->getIdLinkVisitAction() <= 0) {
                // Nothing in log_link_visit_action to align with (ping, ecommerce, manual goal).
                return;
            }

            $this->closePreviousPageView($idVisit, (int) $action->getIdLinkVisitAction(), $serverTimeSql, $cap);
            return;
        }

        $clientTimeOnPage = $this->extractClientTimeOnPage($request, $cap);
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
     * Because everything settles through `GREATEST()`, a client value can only ever *raise*
     * `time_spent` — it is not an end-to-end override. When the visitor navigates on,
     * {@see closePreviousPageView()} applies the server-side wall-clock difference to the row,
     * which wins whenever it exceeds the client's (necessarily smaller) focused-only count. The
     * same happens on any later same-`pv_id` hit sent without `pv_time`. A smaller focused-only
     * value therefore only survives on rows the server never re-measures: the last page of a
     * visit, single-page visits, and abandoned tabs — exactly the rows where the server-side
     * calculation was historically least accurate.
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

        // The row this hit just inserted is excluded by idlink_va and by the strict
        // server_time bound. GREATEST() lets out-of-order heartbeats only grow time_spent.
        //
        // The derived table works around MySQL not allowing an UPDATE to reference its target
        // in a subquery, and avoids `UPDATE ... ORDER BY ... LIMIT 1`, which is binlog-unsafe.
        //
        // $cap is interpolated rather than bound: PDO_MYSQL emulated prepares (the tracker
        // default) send bound ints as strings, and LEAST('1800', 25) compares lexically, so
        // the cap would always win. It is a validated int from Tracker config.
        //
        // Five indexes start with idvisit, and on a short visit the optimiser prefers one of
        // the others and then sorts. Pinning the index that already provides the order keeps
        // this a LIMIT 1 walk, which no sort can beat.
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST($cap, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                 WHERE idpageviewtime = (
                        SELECT idpageviewtime FROM (
                            SELECT idpageviewtime FROM `$table` FORCE INDEX (index_idvisit_server_time)
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

        // A retried request must not reset time_spent accumulated since the first run, so the
        // upsert refreshes the idaction columns only.
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

        // When the client supplied its own time-on-page measurement, use it instead of the
        // server-side TIMESTAMPDIFF, e.g. for trackers that measure focused-only time. Both
        // settle through the same LEAST()/GREATEST() rule, so out-of-order or smaller values
        // can't shrink an earlier larger observation.
        //
        // The client value is wrapped in CAST(? AS UNSIGNED) because emulated prepares send
        // bound ints as strings, which would make LEAST()/GREATEST() compare lexically.
        if ($clientTimeOnPage !== null) {
            $newTimeSpentExpr = 'CAST(? AS UNSIGNED)';
            $newTimeSpentBind = $clientTimeOnPage;
        } else {
            $newTimeSpentExpr = 'TIMESTAMPDIFF(SECOND, server_time, ?)';
            $newTimeSpentBind = $serverTimeSql;
        }

        // A page-view and its site-search hit share one pv_id, so (idvisit, pv_id) can match
        // several rows; the most recent is the active one. Derived table and inlined $cap for
        // the same reasons as closePreviousPageView().
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST($cap, GREATEST(time_spent, $newTimeSpentExpr))
                 WHERE idpageviewtime = (
                        SELECT idpageviewtime FROM (
                            SELECT idpageviewtime FROM `$table`
                             WHERE idvisit = ? AND idpageview = ?
                          ORDER BY server_time DESC, idpageviewtime DESC
                             LIMIT 1
                        ) t
                       )";
        $db->query($sql, [$newTimeSpentBind, $idVisit, $pvId]);
    }
}
