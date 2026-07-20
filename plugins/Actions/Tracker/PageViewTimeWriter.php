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
 */
class PageViewTimeWriter
{
    public const TABLE = 'log_page_view_time';
    public const CONFIG_KEY = 'record_accurate_page_view_time';
    public const DEFAULT_VISIT_STANDARD_LENGTH = 1800;

    /**
     * @return bool True when the tracker should write to log_page_view_time. The kill-switch is
     *              read via TrackerConfig so it picks up per-INI overrides without restart.
     */
    public static function isEnabled(): bool
    {
        $value = TrackerConfig::getConfigValue(self::CONFIG_KEY);
        return $value === null ? true : (bool) (int) $value;
    }

    /**
     * @return int visit_standard_length in seconds. Shared by writer and archiver so their caps
     *             cannot drift.
     */
    public static function getVisitStandardLength(): int
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
        $idVisitor = $visitProperties->getProperty('idvisitor');
        if (!is_string($idVisitor) || $idVisitor === '') {
            return;
        }

        $pvId = substr((string) $request->getParam('pv_id'), 0, 6);
        $pvId = $pvId !== '' ? $pvId : null;

        $serverTimeSql = date('Y-m-d H:i:s', (int) $request->getCurrentTimestamp());
        $cap = self::getVisitStandardLength();

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
                $idVisitor,
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
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST(?, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
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
        $db->query($sql, [$cap, $serverTimeSql, $idVisit, $newIdLinkVa, $serverTimeSql]);
    }

    private function insertPageView(
        int $idSite,
        int $idVisit,
        string $idVisitor,
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
                    (idsite, idvisit, idvisitor, idlink_va, idpageview, idaction_url, idaction_name, server_time, time_spent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE
                    idaction_url  = VALUES(idaction_url),
                    idaction_name = VALUES(idaction_name)";
        $db->query($sql, [
            $idSite,
            $idVisit,
            $idVisitor,
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

        // A page-view and its site-search hit share the same pv_id (the JS tracker does not
        // rotate pv_id between them), so (idvisit, pv_id) can match multiple rows. Update the
        // most-recent one — that is the currently-active row for this tab.
        //
        // The derived-table form avoids the statement-based-binlog warning that
        // `UPDATE ... ORDER BY ... LIMIT 1` triggers.
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST(?, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                 WHERE idpageviewtime = (
                        SELECT idpageviewtime FROM (
                            SELECT idpageviewtime FROM `$table`
                             WHERE idvisit = ? AND idpageview = ?
                          ORDER BY server_time DESC, idpageviewtime DESC
                             LIMIT 1
                        ) t
                       )";
        $db->query($sql, [$cap, $serverTimeSql, $idVisit, $pvId]);
    }
}
