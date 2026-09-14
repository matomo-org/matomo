<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Exception;
use Piwik\Common;
use Piwik\Db;

/**
 * Checks that the analytics database actually holds what MySQL holds.
 *
 * perfcorpus:churn reproduces live tracking write load, and the reason that matters is that a
 * corpus is insert-only while the tracker is not: every action after the first UPDATEs the same
 * log_visit row, and roughly one visit in ten has a user id arrive mid-visit, which rewrites
 * idvisitor across every log table. Those are the statements a change-data-capture pipeline is
 * most likely to get wrong, and the churn command measures only the write path - it reports the
 * statement mix the tracker produced and never looks at the other side.
 *
 * So this looks at the other side. Three questions, in increasing order of what they prove:
 *
 * - **Did the rows arrive at all?** MAX(primary key) on each side. Cheap, and the gap between
 *   them is replication lag in rows. Deliberately not COUNT(*): on a billion-row InnoDB table
 *   that is a full scan and does not return.
 * - **Did they arrive in time?** The same comparison over a recent window only, which separates
 *   "behind on everything" from "caught up on history but not keeping up now".
 * - **Did the UPDATES arrive?** A sample of recently-changed visits compared column by column.
 *   This is the one that matters. An insert-only pipeline passes the first two checks and fails
 *   this one, and it fails it silently - the row is present, it is just stale.
 *
 * Both connections come from Matomo, so this measures the copy Matomo would actually read.
 */
final class SyncVerifier
{
    public const LEVEL_FAST = 'fast';
    public const LEVEL_FULL = 'full';

    /**
     * Primary key per log table, for the watermark comparison. log_conversion and
     * log_conversion_item have composite keys and no single ascending column, so they are
     * compared on their time window only.
     */
    private const WATERMARKS = [
        'log_visit' => 'idvisit',
        'log_link_visit_action' => 'idlink_va',
        'log_action' => 'idaction',
    ];

    /** Time column to bound a recent-window comparison by. */
    private const TIME_COLUMNS = [
        'log_visit' => 'visit_last_action_time',
        'log_link_visit_action' => 'server_time',
        'log_conversion' => 'server_time',
        'log_conversion_item' => 'server_time',
    ];

    /**
     * Columns a tracker UPDATE moves. visit_last_action_time and visit_total_actions change on
     * every action of a visit; idvisitor is rewritten when a user id arrives mid-visit.
     */
    private const UPDATED_COLUMNS = [
        'visit_last_action_time',
        'visit_total_actions',
        'visit_total_interactions',
        'visit_total_time',
        'idvisitor',
    ];

    /** @var array<int, array<string, mixed>> */
    private array $results = [];

    /**
     * @param int $windowMinutes how far back the recent-window and sample checks look
     * @param int $sampleSize    how many changed visits to compare column by column
     * @return array{passed: bool, checks: array<int, array<string, mixed>>}
     */
    public function run(string $level, int $windowMinutes = 120, int $sampleSize = 200): array
    {
        $this->results = [];

        if (!Db::hasAnalyticsConfigured()) {
            $this->fail('analytics database', 'no analytics database is configured, nothing to verify against');

            return $this->summary();
        }

        $this->checkFinalSetting();
        $this->checkWatermarks();
        $this->checkRecentWindow($windowMinutes);
        $this->checkNothingMarkedDeleted($windowMinutes);

        if ($level === self::LEVEL_FULL) {
            $this->checkUpdatesLanded($windowMinutes, $sampleSize);
        }

        return $this->summary();
    }

    /**
     * Reading a ReplacingMergeTree without FINAL while a pipe is writing can return a superseded
     * row version, which would make the update check below compare against stale data and report
     * a failure that is really a configuration mistake. Not fatal - the run still tells you
     * something - but it has to be said out loud.
     */
    private function checkFinalSetting(): void
    {
        $config = Db::getAnalyticsDatabaseConfig();
        $final = (string) ($config['final'] ?? '');
        $isOn = $final === '' || filter_var($final, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false;

        if ($isOn) {
            $this->pass('FINAL', 'reads collapse row versions, so a row read here is its current version');

            return;
        }

        $this->warn(
            'FINAL',
            '[database_analytics] final is off. A read can return a superseded row version while a'
            . ' pipe is writing, so an update reported as missing below may simply be unmerged.'
        );
    }

    /**
     * How many rows behind the analytics copy is, per table.
     */
    private function checkWatermarks(): void
    {
        foreach (self::WATERMARKS as $table => $key) {
            try {
                $mysql = (int) Db::get()->fetchOne(
                    'SELECT COALESCE(MAX(' . $key . '), 0) FROM ' . Common::prefixTable($table)
                );
                $analytics = (int) Db::getAnalytics()->fetchOne(
                    'SELECT COALESCE(MAX(' . $key . '), 0) FROM ' . Common::prefixTable($table)
                );
            } catch (Exception $e) {
                $this->fail($table . ' watermark', $e->getMessage());
                continue;
            }

            $lag = $mysql - $analytics;
            $detail = sprintf(
                'mysql %s = %s, analytics = %s, lag %s row(s)',
                $key,
                number_format($mysql),
                number_format($analytics),
                number_format($lag)
            );

            if ($lag === 0) {
                $this->pass($table . ' watermark', $detail);
            } elseif ($lag > 0) {
                $this->warn($table . ' watermark', $detail . ' - behind');
            } else {
                // Ahead of the source is not lag, it is a copy holding rows the source no longer
                // has. Worth flagging rather than reading as a healthy negative number.
                $this->fail($table . ' watermark', $detail . ' - analytics is AHEAD of mysql');
            }
        }
    }

    /**
     * The same question, over a recent window only. A copy can be caught up on history and still
     * not be keeping up now, and only this check separates the two.
     */
    private function checkRecentWindow(int $windowMinutes): void
    {
        $since = Db::get()->fetchOne('SELECT DATE_SUB(NOW(), INTERVAL ' . (int) $windowMinutes . ' MINUTE)');

        foreach (self::TIME_COLUMNS as $table => $column) {
            try {
                $sql = 'SELECT COUNT(*) FROM ' . Common::prefixTable($table) . ' WHERE ' . $column . ' >= ?';
                $mysql = (int) Db::get()->fetchOne($sql, [$since]);
                $analytics = (int) Db::getAnalytics()->fetchOne($sql, [$since]);
            } catch (Exception $e) {
                $this->fail($table . ' last ' . $windowMinutes . 'm', $e->getMessage());
                continue;
            }

            $detail = sprintf('mysql %s row(s), analytics %s', number_format($mysql), number_format($analytics));

            if ($mysql === $analytics) {
                $this->pass($table . ' last ' . $windowMinutes . 'm', $detail);
            } else {
                $this->warn(
                    $table . ' last ' . $windowMinutes . 'm',
                    $detail . sprintf(', missing %s', number_format($mysql - $analytics))
                );
            }
        }
    }

    /**
     * A row whose latest version is a delete should not be readable. If any are marked deleted,
     * FINAL is what is hiding them, and a read without it would return rows MySQL does not have.
     *
     * Bounded to the same recent window as everything else, deliberately. Unbounded, this is a
     * SUM over every row of a multi-billion-row table, and with FINAL on - which is exactly when
     * this check runs - that is minutes of work for a question about rows the churn just wrote.
     */
    private function checkNothingMarkedDeleted(int $windowMinutes): void
    {
        $since = Db::get()->fetchOne('SELECT DATE_SUB(NOW(), INTERVAL ' . (int) $windowMinutes . ' MINUTE)');

        foreach (self::TIME_COLUMNS as $table => $column) {
            try {
                $deleted = (int) Db::getAnalytics()->fetchOne(
                    'SELECT COALESCE(SUM(_peerdb_is_deleted), 0) FROM ' . Common::prefixTable($table)
                    . ' WHERE ' . $column . ' >= ?',
                    [$since]
                );
            } catch (Exception $e) {
                // The column only exists on a CDC-populated copy. Absent is not a failure.
                continue;
            }

            $label = $table . ' deletes (last ' . $windowMinutes . 'm)';
            if ($deleted === 0) {
                $this->pass($label, 'no rows marked deleted');
            } else {
                $this->warn($label, number_format($deleted) . ' row(s) marked deleted');
            }
        }
    }

    /**
     * The check that an insert-only pipeline fails.
     *
     * Takes visits MySQL changed inside the window and compares the columns a tracker UPDATE
     * moves. A row that is present but stale passes every other check in this class.
     */
    private function checkUpdatesLanded(int $windowMinutes, int $sampleSize): void
    {
        $table = Common::prefixTable('log_visit');
        $columns = implode(', ', self::UPDATED_COLUMNS);

        try {
            $since = Db::get()->fetchOne('SELECT DATE_SUB(NOW(), INTERVAL ' . (int) $windowMinutes . ' MINUTE)');
            $mysqlRows = Db::get()->fetchAll(
                'SELECT idvisit, ' . $columns . ' FROM ' . $table
                . ' WHERE visit_last_action_time >= ? ORDER BY idvisit DESC LIMIT ' . (int) $sampleSize,
                [$since]
            );
        } catch (Exception $e) {
            $this->fail('updates landed', $e->getMessage());

            return;
        }

        if (empty($mysqlRows)) {
            $this->warn(
                'updates landed',
                'no visit changed in the last ' . $windowMinutes . ' minutes, so nothing to compare.'
                . ' Run this while or just after perfcorpus:churn.'
            );

            return;
        }

        $ids = array_map('intval', array_column($mysqlRows, 'idvisit'));

        try {
            $analyticsRows = Db::getAnalytics()->fetchAll(
                'SELECT idvisit, ' . $columns . ' FROM ' . $table
                . ' WHERE idvisit IN (' . implode(',', $ids) . ')'
            );
        } catch (Exception $e) {
            $this->fail('updates landed', $e->getMessage());

            return;
        }

        $byId = [];
        foreach ($analyticsRows as $row) {
            $byId[(int) $row['idvisit']] = $row;
        }

        $missing = 0;
        $stale = [];
        foreach ($mysqlRows as $row) {
            $id = (int) $row['idvisit'];
            if (!isset($byId[$id])) {
                $missing++;
                continue;
            }

            foreach (self::UPDATED_COLUMNS as $column) {
                $left = self::normalise($row[$column] ?? null);
                $right = self::normalise($byId[$id][$column] ?? null);
                if ($left !== $right) {
                    $stale[$column] = ($stale[$column] ?? 0) + 1;
                }
            }
        }

        $compared = count($mysqlRows);
        $detail = sprintf('%s visit(s) sampled, %s absent from analytics', number_format($compared), $missing);

        if ($missing === 0 && empty($stale)) {
            $this->pass('updates landed', $detail . ', every compared column matches');

            return;
        }

        foreach ($stale as $column => $count) {
            $detail .= sprintf(', %s differs on %s', $column, number_format($count));
        }

        // Absent rows are lag and may still arrive. A row that is present with the wrong value is
        // the failure this check exists for: the pipe delivered the insert and dropped the update.
        if (!empty($stale)) {
            $this->fail('updates landed', $detail);
        } else {
            $this->warn('updates landed', $detail . ' - lag, not loss, if they arrive later');
        }
    }

    /**
     * One value from either engine, in a form the two can be compared in.
     *
     * The two disagree about formatting on almost every type: DATETIME comes back with a
     * microsecond fraction from one and without from the other, integers arrive as strings, and
     * idvisitor is 8 raw bytes that are not safely printable.
     *
     * @param mixed $value
     */
    private static function normalise($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $string = (string) $value;

        // A datetime, with or without a fractional part.
        if (preg_match('~^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})(\.\d+)?$~', $string, $m)) {
            return $m[1];
        }

        if (is_numeric($string)) {
            $number = rtrim(rtrim(sprintf('%.6F', (float) $string), '0'), '.');

            return $number === '' || $number === '-0' ? '0' : $number;
        }

        // Binary ids and anything else: hex, so a non-printable byte cannot break the comparison.
        if (preg_match('~[^\x20-\x7e]~', $string)) {
            return 'hex:' . bin2hex($string);
        }

        return $string;
    }

    private function pass(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'PASS', 'detail' => $detail];
    }

    private function warn(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'WARN', 'detail' => $detail];
    }

    private function fail(string $check, string $detail): void
    {
        $this->results[] = ['check' => $check, 'status' => 'FAIL', 'detail' => $detail];
    }

    /**
     * @return array{passed: bool, checks: array<int, array<string, mixed>>}
     */
    private function summary(): array
    {
        $failed = false;
        foreach ($this->results as $result) {
            if ($result['status'] === 'FAIL') {
                $failed = true;
                break;
            }
        }

        return ['passed' => !$failed, 'checks' => $this->results];
    }
}
