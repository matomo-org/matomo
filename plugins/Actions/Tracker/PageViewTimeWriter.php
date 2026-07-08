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
 *  - Single SQL statement per call (no SELECTs).
 *  - UPSERT keyed on (idvisit, idpageview) so concurrent hits for the same tab are race-safe.
 *  - The visit_standard_length cap is applied in SQL so we never need a round-trip to read it
 *    or compute it client-side per row.
 *
 * Multi-tab attribution relies on the browser supplying a per-tab `pv_id`. When `pv_id` is
 * present, every hit reaches the exact tab's row. Non-pageview hits with no `pv_id` are a
 * no-op: events use their own TYPE_EVENT idaction (different from the page's TYPE_PAGE_URL
 * idaction), so attempting to match by idaction_url would never find the pageview row anyway.
 * Skipping is the safe choice to avoid touching another tab's row.
 */
class PageViewTimeWriter
{
    public const TABLE = 'log_page_view_time';
    public const CONFIG_KEY = 'record_accurate_page_view_time';

    /**
     * @return bool True when the tracker should write to log_page_view_time. The kill-switch is
     *              read via TrackerConfig so it picks up per-INI overrides without restart.
     */
    public static function isEnabled(): bool
    {
        $value = TrackerConfig::getConfigValue(self::CONFIG_KEY);
        return $value === null ? true : (bool) (int) $value;
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
        $cap = $this->getVisitStandardLength();

        if ($action !== null && (int) $action->getActionType() === Action::TYPE_PAGE_URL) {
            // On a brand-new pageview, also close the previous pageview in this visit. This is
            // the cheaper alternative to a correlated-subquery backfill at archive time: at most
            // one indexed UPDATE per PV tracker hit, and the archive query can stay flat.
            $this->closePreviousPageView($idVisit, $pvId, $serverTimeSql, $cap);

            $this->insertPageView(
                $idSite,
                $idVisit,
                $idVisitor,
                $pvId,
                (int) $action->getIdActionUrl(),
                (int) $action->getIdActionName(),
                $serverTimeSql
            );
            return;
        }

        // Non-pageview hit: ping, event, content, search, outlink, download, page title only, etc.
        // Without pv_id we cannot safely attribute to a specific tab (events log their own
        // TYPE_EVENT idaction, so matching by idaction_url here would never find the pageview
        // row). Skip rather than touch the wrong row.
        if ($pvId === null) {
            return;
        }

        $this->updateTimeSpent($idVisit, $pvId, $serverTimeSql, $cap);
    }

    private function closePreviousPageView(
        int $idVisit,
        ?string $newPvId,
        string $serverTimeSql,
        int $cap
    ): void {
        $table = Common::prefixTable(self::TABLE);
        $db = Tracker::getDatabase();

        // Find the most-recently-inserted previous pageview row in this visit (excluding any
        // existing row that shares the new pv_id — that case happens on a retried PV request and
        // is already handled by the ON DUPLICATE KEY UPDATE branch of the insert).
        //
        // The UPDATE uses GREATEST so it never shrinks a value that heartbeats have already set
        // higher. ORDER BY idpageviewtime DESC LIMIT 1 picks exactly one row via the primary key.
        if ($newPvId !== null) {
            $sql = "UPDATE `$table`
                       SET time_spent = LEAST(?, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                     WHERE idvisit = ?
                       AND (idpageview IS NULL OR idpageview <> ?)
                       AND server_time < ?
                     ORDER BY idpageviewtime DESC
                     LIMIT 1";
            $db->query($sql, [$cap, $serverTimeSql, $idVisit, $newPvId, $serverTimeSql]);
            return;
        }

        $sql = "UPDATE `$table`
                   SET time_spent = LEAST(?, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                 WHERE idvisit = ?
                   AND server_time < ?
                 ORDER BY idpageviewtime DESC
                 LIMIT 1";
        $db->query($sql, [$cap, $serverTimeSql, $idVisit, $serverTimeSql]);
    }

    private function insertPageView(
        int $idSite,
        int $idVisit,
        string $idVisitor,
        ?string $pvId,
        int $idActionUrl,
        int $idActionName,
        string $serverTimeSql
    ): void {
        $table = Common::prefixTable(self::TABLE);
        $db = Tracker::getDatabase();

        if ($pvId !== null) {
            // Single-statement race-safe upsert. If a heartbeat reached the server before the
            // pageview row was inserted (unusual but possible), the existing row gets the
            // action ids backfilled without losing accumulated time_spent.
            $sql = "INSERT INTO `$table`
                        (idsite, idvisit, idvisitor, idpageview, idaction_url, idaction_name, server_time, time_spent)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                    ON DUPLICATE KEY UPDATE
                        idaction_url  = VALUES(idaction_url),
                        idaction_name = VALUES(idaction_name)";
            $db->query($sql, [
                $idSite,
                $idVisit,
                $idVisitor,
                $pvId,
                $idActionUrl > 0 ? $idActionUrl : null,
                $idActionName > 0 ? $idActionName : null,
                $serverTimeSql,
            ]);
            return;
        }

        // No pv_id (older trackers / server-side SDKs): allow multiple NULL-pv_id rows per visit;
        // the (idvisit, idpageview) UNIQUE constraint does not treat NULLs as equal.
        $sql = "INSERT INTO `$table`
                    (idsite, idvisit, idvisitor, idpageview, idaction_url, idaction_name, server_time, time_spent)
                VALUES (?, ?, ?, NULL, ?, ?, ?, 0)";
        $db->query($sql, [
            $idSite,
            $idVisit,
            $idVisitor,
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

        // Exact-tab match via the UNIQUE (idvisit, idpageview) index. One row, one update.
        // GREATEST() ensures heartbeats arriving out-of-order can only grow time_spent;
        // LEAST() caps it at visit_standard_length so a stalled tab can't inflate one page.
        $sql = "UPDATE `$table`
                   SET time_spent = LEAST(?, GREATEST(time_spent, TIMESTAMPDIFF(SECOND, server_time, ?)))
                 WHERE idvisit = ? AND idpageview = ?";
        $db->query($sql, [$cap, $serverTimeSql, $idVisit, $pvId]);
    }

    private function getVisitStandardLength(): int
    {
        $value = Config::getInstance()->Tracker['visit_standard_length'] ?? 1800;
        return (int) $value;
    }
}
