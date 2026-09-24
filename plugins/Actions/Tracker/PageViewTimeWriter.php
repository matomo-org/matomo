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
 *    does this; a failed close leaves the row at 0, which the anti-join treats as absent.
 */
class PageViewTimeWriter
{
    public const TABLE = 'log_page_view_time';
    public const CONFIG_KEY = 'record_accurate_page_view_time';
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
     * Cap applied at write time only; the archiver sums already-capped values.
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

        $this->updateTimeSpent($idVisit, $pvId, $serverTimeSql, $cap);
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
        int $cap
    ): void {
        $table = Common::prefixTable(self::TABLE);
        $db = Tracker::getDatabase();

        // A page-view and its site-search hit share one pv_id, so (idvisit, pv_id) can match
        // several rows; the most recent is the active one. Derived table and inlined $cap for
        // the same reasons as closePreviousPageView().
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
