<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Common;
use Piwik\Db;

/**
 * The durable work queue behind a corpus run, and the reason the generator can be killed at any
 * moment without leaving a mess.
 *
 * Work is split into chunks. A plan chunk is one shard of the visitor universe; a load chunk is
 * one (day, shard) pair. Workers claim chunks with the same select-then-compare-and-swap that
 * core:archive uses for archive_invalidations (\Piwik\DataAccess\Model::startArchive), which
 * needs no row locks, works on any MySQL, and works across machines.
 *
 * The important part is that every load chunk owns a pre-allocated, contiguous range of
 * idvisit and idlink_va values, assigned once the plan phase knows the exact row counts. Rows
 * are inserted with explicit primary keys rather than AUTO_INCREMENT, which means:
 *
 *   - workers never contend on an auto-increment counter and need no ordering between them;
 *   - a failed chunk is undone by deleting its id ranges, so a retry cannot duplicate rows;
 *   - progress and verification are exact, because the planned row count is known up front.
 *
 * Chunks are seeded in (day, shard) order, so idvisit still increases with time at day
 * granularity - Live's visits log uses ORDER BY idvisit DESC as its recency proxy.
 */
class ChunkQueue
{
    public const PHASE_PLAN = 1;
    public const PHASE_LOAD = 2;

    public const STATUS_PENDING = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_DONE = 2;
    public const STATUS_FAILED = 3;

    public const RUN_PLANNING = 0;
    public const RUN_LOADING = 1;
    public const RUN_DONE = 2;
    public const RUN_FAILED = 3;

    /** Candidates fetched per claim attempt, so workers do not all fight over the same row. */
    private const CLAIM_CANDIDATES = 25;

    private int $idRun;

    public function __construct(int $idRun)
    {
        $this->idRun = $idRun;
    }

    public function getRunId(): int
    {
        return $this->idRun;
    }

    public static function runTable(): string
    {
        return Common::prefixTable('perfcorpus_run');
    }

    public static function chunkTable(): string
    {
        return Common::prefixTable('perfcorpus_chunk');
    }

    /**
     * Both tables are created on demand rather than through an Updates/ migration: they are
     * benchmarking tooling and must not become part of Matomo's schema.
     */
    public static function install(): void
    {
        $runTable = self::runTable();
        $chunkTable = self::chunkTable();

        Db::exec("CREATE TABLE IF NOT EXISTS `$runTable` (
            `idrun` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `config` MEDIUMTEXT NOT NULL,
            `spool_dir` VARCHAR(255) NOT NULL,
            `git_commit` VARCHAR(40) NULL,
            `idaction_base` INT UNSIGNED NULL,
            `status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `ts_created` DATETIME NOT NULL,
            `ts_finished` DATETIME NULL,
            PRIMARY KEY (`idrun`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        Db::exec("CREATE TABLE IF NOT EXISTS `$chunkTable` (
            `idchunk` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `idrun` INT UNSIGNED NOT NULL,
            `phase` TINYINT UNSIGNED NOT NULL,
            `day_index` INT NOT NULL,
            `shard` SMALLINT UNSIGNED NOT NULL,
            `day` DATE NULL,
            `visit_count` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `action_count` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `conversion_count` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `item_count` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `idvisit_start` BIGINT UNSIGNED NULL,
            `idlink_va_start` BIGINT UNSIGNED NULL,
            `idaction_tail_start` INT UNSIGNED NULL,
            `idaction_tail_count` INT UNSIGNED NOT NULL DEFAULT 0,
            `status` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `processing_host` VARCHAR(100) NULL,
            `process_id` VARCHAR(15) NULL,
            `ts_started` DATETIME NULL,
            `ts_heartbeat` DATETIME NULL,
            `ts_finished` DATETIME NULL,
            `rows_written` BIGINT UNSIGNED NOT NULL DEFAULT 0,
            `checksum` CHAR(8) NULL,
            `last_error` TEXT NULL,
            PRIMARY KEY (`idchunk`),
            UNIQUE KEY `unique_chunk` (`idrun`, `phase`, `day_index`, `shard`),
            KEY `index_claim` (`idrun`, `phase`, `status`, `idchunk`),
            KEY `index_heartbeat` (`idrun`, `status`, `ts_heartbeat`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // CREATE TABLE IF NOT EXISTS will not add a column to a table someone already has, and
        // pulling an updated branch onto an existing run should not produce a cryptic SQL error.
        self::addColumnIfMissing($runTable, 'idaction_base', 'INT UNSIGNED NULL');
        self::addColumnIfMissing($chunkTable, 'idaction_tail_start', 'INT UNSIGNED NULL');
        self::addColumnIfMissing($chunkTable, 'idaction_tail_count', 'INT UNSIGNED NOT NULL DEFAULT 0');
    }

    private static function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        $exists = Db::fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );

        if (!$exists) {
            Db::exec(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition));
        }
    }

    public function setIdActionBase(int $base): void
    {
        Db::query(
            'UPDATE `' . self::runTable() . '` SET `idaction_base` = ? WHERE `idrun` = ?',
            [$base, $this->idRun]
        );
    }

    public static function createRun(Profile $profile, string $spoolDir, ?string $gitCommit): int
    {
        $table = self::runTable();

        Db::query(
            "INSERT INTO `$table` (`config`, `spool_dir`, `git_commit`, `status`, `ts_created`)
             VALUES (?, ?, ?, ?, NOW())",
            [json_encode($profile->toArray()), $spoolDir, $gitCommit, self::RUN_PLANNING]
        );

        return (int) Db::fetchOne("SELECT MAX(`idrun`) FROM `$table`");
    }

    public static function getRun(int $idRun): ?array
    {
        $table = self::runTable();
        $row = Db::fetchRow("SELECT * FROM `$table` WHERE `idrun` = ?", [$idRun]);

        return $row ?: null;
    }

    public static function getLatestRunId(): ?int
    {
        $table = self::runTable();
        $id = Db::fetchOne("SELECT MAX(`idrun`) FROM `$table`");

        return $id ? (int) $id : null;
    }

    /**
     * Finds a run that matches this configuration, so re-running the same command resumes rather
     * than starting a second, overlapping corpus.
     */
    public static function findResumableRun(Profile $profile): ?int
    {
        $table = self::runTable();
        $rows = Db::fetchAll(
            "SELECT `idrun`, `config` FROM `$table` WHERE `status` IN (?, ?) ORDER BY `idrun` DESC",
            [self::RUN_PLANNING, self::RUN_LOADING]
        );

        $wanted = json_encode($profile->toArray());

        foreach ($rows as $row) {
            if ($row['config'] === $wanted) {
                return (int) $row['idrun'];
            }
        }

        return null;
    }

    public function setRunStatus(int $status): void
    {
        $table = self::runTable();
        $finished = in_array($status, [self::RUN_DONE, self::RUN_FAILED], true) ? 'NOW()' : 'NULL';

        Db::query(
            "UPDATE `$table` SET `status` = ?, `ts_finished` = $finished WHERE `idrun` = ?",
            [$status, $this->idRun]
        );
    }

    /**
     * Removes a run and everything it wrote to the queue. The log-table rows it produced are
     * deleted separately, by id range - see Cleaner.
     */
    public function deleteRun(): void
    {
        $runTable = self::runTable();
        $chunkTable = self::chunkTable();

        Db::query("DELETE FROM `$chunkTable` WHERE `idrun` = ?", [$this->idRun]);
        Db::query("DELETE FROM `$runTable` WHERE `idrun` = ?", [$this->idRun]);
    }

    /**
     * Creates one plan chunk per shard and one load chunk per (day, shard). Load chunks are
     * inserted in day order so their idchunk order matches chronological order, which is what
     * keeps idvisit increasing with time once ranges are allocated.
     */
    public function seedChunks(Profile $profile): void
    {
        $table = self::chunkTable();
        $shards = $profile->getShardCount();
        $days = $profile->getDayCount();

        $rows = [];
        for ($shard = 0; $shard < $shards; $shard++) {
            $rows[] = [$this->idRun, self::PHASE_PLAN, -1, $shard, null, 0];
        }

        for ($day = 0; $day < $days; $day++) {
            $date = $profile->getDateForDay($day);
            for ($shard = 0; $shard < $shards; $shard++) {
                $rows[] = [
                    $this->idRun,
                    self::PHASE_LOAD,
                    $day,
                    $shard,
                    $date,
                    $profile->getVisitsForChunk($day, $shard),
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $batch) {
            $placeholders = implode(',', array_fill(0, count($batch), '(?,?,?,?,?,?)'));
            $bind = [];
            foreach ($batch as $row) {
                array_push($bind, ...$row);
            }

            Db::query(
                "INSERT IGNORE INTO `$table`
                    (`idrun`, `phase`, `day_index`, `shard`, `day`, `visit_count`)
                 VALUES $placeholders",
                $bind
            );
        }
    }

    /**
     * Claims one pending chunk. Returns null when there is nothing left to claim.
     *
     * Several candidates are fetched and tried in an order rotated by the worker number, so N
     * workers starting at the same moment do not all collide on the same first row.
     */
    public function claimNext(int $phase, int $workerNumber = 0): ?array
    {
        $table = self::chunkTable();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $candidates = Db::fetchAll(
                "SELECT * FROM `$table`
                  WHERE `idrun` = ? AND `phase` = ? AND `status` = ?
                  ORDER BY `idchunk`
                  LIMIT " . self::CLAIM_CANDIDATES,
                [$this->idRun, $phase, self::STATUS_PENDING]
            );

            if (empty($candidates)) {
                return null;
            }

            $offset = $workerNumber % count($candidates);
            for ($i = 0; $i < count($candidates); $i++) {
                $chunk = $candidates[($i + $offset) % count($candidates)];

                if ($this->casClaim((int) $chunk['idchunk'])) {
                    $chunk['status'] = self::STATUS_RUNNING;
                    $chunk['attempts'] = (int) $chunk['attempts'] + 1;

                    return $chunk;
                }
            }
        }

        return null;
    }

    /**
     * The compare-and-swap itself. rowCount() > 0 means this process won the race; anything else
     * means another worker got there first and we move on.
     */
    private function casClaim(int $idChunk): bool
    {
        $table = self::chunkTable();

        $statement = Db::query(
            "UPDATE `$table`
                SET `status` = ?, `processing_host` = ?, `process_id` = ?,
                    `ts_started` = NOW(), `ts_heartbeat` = NOW(), `attempts` = `attempts` + 1
              WHERE `idchunk` = ? AND `status` = ?",
            [
                self::STATUS_RUNNING,
                substr((string) (gethostname() ?: 'unknown'), 0, 100),
                Common::getProcessId(),
                $idChunk,
                self::STATUS_PENDING,
            ]
        );

        return $statement->rowCount() > 0;
    }

    public function heartbeat(int $idChunk): void
    {
        $table = self::chunkTable();
        Db::query("UPDATE `$table` SET `ts_heartbeat` = NOW() WHERE `idchunk` = ?", [$idChunk]);
    }

    public function complete(int $idChunk, int $rowsWritten, string $checksum): void
    {
        $table = self::chunkTable();

        Db::query(
            "UPDATE `$table`
                SET `status` = ?, `ts_finished` = NOW(), `rows_written` = ?, `checksum` = ?,
                    `last_error` = NULL
              WHERE `idchunk` = ?",
            [self::STATUS_DONE, $rowsWritten, $checksum, $idChunk]
        );
    }

    /**
     * Puts a chunk back for another attempt, or parks it as failed once it has burned through
     * --max-attempts. A failed chunk is reported rather than retried forever.
     */
    public function release(int $idChunk, string $error, int $maxAttempts): void
    {
        $table = self::chunkTable();
        $attempts = (int) Db::fetchOne("SELECT `attempts` FROM `$table` WHERE `idchunk` = ?", [$idChunk]);
        $status = $attempts >= $maxAttempts ? self::STATUS_FAILED : self::STATUS_PENDING;

        Db::query(
            "UPDATE `$table`
                SET `status` = ?, `last_error` = ?, `processing_host` = NULL, `process_id` = NULL,
                    `ts_started` = NULL, `ts_heartbeat` = NULL
              WHERE `idchunk` = ?",
            [$status, substr($error, 0, 2000), $idChunk]
        );
    }

    /**
     * Chunks whose worker stopped heartbeating - a kill -9, an OOM, a lost SSH session, a
     * database failover. The caller must delete their id ranges from the log tables before they are
     * retried, which is safe precisely because those ranges are pre-allocated.
     *
     * @return array[] the chunk rows that were reclaimed
     */
    public function findStale(int $staleAfterSeconds): array
    {
        $table = self::chunkTable();

        return Db::fetchAll(
            "SELECT * FROM `$table`
              WHERE `idrun` = ? AND `status` = ?
                AND `ts_heartbeat` < DATE_SUB(NOW(), INTERVAL ? SECOND)
              ORDER BY `idchunk`",
            [$this->idRun, self::STATUS_RUNNING, $staleAfterSeconds]
        );
    }

    public function requeue(int $idChunk): void
    {
        $table = self::chunkTable();

        Db::query(
            "UPDATE `$table`
                SET `status` = ?, `processing_host` = NULL, `process_id` = NULL,
                    `ts_started` = NULL, `ts_heartbeat` = NULL, `rows_written` = 0
              WHERE `idchunk` = ? AND `status` = ?",
            [self::STATUS_PENDING, $idChunk, self::STATUS_RUNNING]
        );
    }

    /**
     * Records what the plan phase actually drew for one (day, shard), so id ranges can be sized
     * exactly rather than estimated.
     */
    public function setPlannedCounts(int $dayIndex, int $shard, array $counts): void
    {
        $table = self::chunkTable();

        Db::query(
            "UPDATE `$table`
                SET `visit_count` = ?, `action_count` = ?, `conversion_count` = ?, `item_count` = ?
              WHERE `idrun` = ? AND `phase` = ? AND `day_index` = ? AND `shard` = ?",
            [
                $counts['visits'],
                $counts['actions'],
                $counts['conversions'],
                $counts['items'],
                $this->idRun,
                self::PHASE_LOAD,
                $dayIndex,
                $shard,
            ]
        );
    }

    /**
     * All of one shard's per-day counts in a single statement. One UPDATE per day would be 27k
     * round trips at p200; this is one per shard.
     *
     * INSERT ... ON DUPLICATE KEY UPDATE rather than UPDATE, so it also repairs a chunk row that
     * somehow went missing, and so the whole thing is idempotent under a retry.
     *
     * @param array[] $rows dayIndex, shard, date, visits, actions, conversions, items
     */
    public function setPlannedCountsBatch(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $table = self::chunkTable();

        foreach (array_chunk($rows, 500) as $batch) {
            $placeholders = implode(',', array_fill(0, count($batch), '(?,?,?,?,?,?,?,?,?)'));
            $bind = [];

            foreach ($batch as $row) {
                array_push(
                    $bind,
                    $this->idRun,
                    self::PHASE_LOAD,
                    $row['dayIndex'],
                    $row['shard'],
                    $row['date'],
                    $row['visits'],
                    $row['actions'],
                    $row['conversions'],
                    $row['items']
                );
            }

            Db::query(
                "INSERT INTO `$table`
                    (`idrun`, `phase`, `day_index`, `shard`, `day`,
                     `visit_count`, `action_count`, `conversion_count`, `item_count`)
                 VALUES $placeholders
                 ON DUPLICATE KEY UPDATE
                     `visit_count` = VALUES(`visit_count`),
                     `action_count` = VALUES(`action_count`),
                     `conversion_count` = VALUES(`conversion_count`),
                     `item_count` = VALUES(`item_count`)",
                $bind
            );
        }
    }

    /**
     * Walks the load chunks in chronological order and hands each a contiguous block of primary
     * keys. Runs once, after planning, before any row is written.
     *
     * @return array{idvisit: int, idlink_va: int} the first id after the last allocated block
     */
    public function allocateIdRanges(int $idVisitBase, int $idLinkVaBase, int $idActionTailBase, float $uniqueUrlShare): array
    {
        $table = self::chunkTable();

        $chunks = Db::fetchAll(
            "SELECT `idchunk`, `visit_count`, `action_count`
               FROM `$table`
              WHERE `idrun` = ? AND `phase` = ?
              ORDER BY `day_index`, `shard`",
            [$this->idRun, self::PHASE_LOAD]
        );

        $nextVisit = $idVisitBase;
        $nextLinkVa = $idLinkVaBase;
        $nextTail = $idActionTailBase;

        foreach ($chunks as $chunk) {
            $actions = (int) $chunk['action_count'];

            // How many unique URLs this chunk can mint. The count is a binomial draw made during
            // generation rather than something the plan fixes, so the range carries headroom;
            // unused ids are simply gaps, and gaps in idaction cost nothing.
            $tailCount = (int) ceil(
                $actions * Profile::ACTION_TYPE_PAGEVIEW_SHARE * $uniqueUrlShare * 1.25
            ) + 16;

            Db::query(
                "UPDATE `$table`
                    SET `idvisit_start` = ?, `idlink_va_start` = ?,
                        `idaction_tail_start` = ?, `idaction_tail_count` = ?
                  WHERE `idchunk` = ?",
                [$nextVisit, $nextLinkVa, $nextTail, $tailCount, (int) $chunk['idchunk']]
            );

            $nextVisit += (int) $chunk['visit_count'];
            $nextLinkVa += $actions;
            $nextTail += $tailCount;
        }

        return ['idvisit' => $nextVisit, 'idlink_va' => $nextLinkVa, 'idaction' => $nextTail];
    }

    /**
     * True once every load chunk owns an id range. Allocation happens exactly once, after the
     * whole plan phase is finished and the real counts are known.
     */
    public function isAllocated(): bool
    {
        $table = self::chunkTable();

        $unallocated = Db::fetchOne(
            "SELECT COUNT(*) FROM `$table`
              WHERE `idrun` = ? AND `phase` = ? AND `idvisit_start` IS NULL",
            [$this->idRun, self::PHASE_LOAD]
        );

        return 0 === (int) $unallocated;
    }

    /**
     * Counts and row totals per status, for the progress display and for verification.
     */
    public function getProgress(int $phase): array
    {
        $table = self::chunkTable();

        $rows = Db::fetchAll(
            "SELECT `status`, COUNT(*) AS `chunks`,
                    SUM(`visit_count` + `action_count` + `conversion_count` + `item_count`) AS `planned_rows`,
                    SUM(`rows_written`) AS `written_rows`
               FROM `$table`
              WHERE `idrun` = ? AND `phase` = ?
              GROUP BY `status`",
            [$this->idRun, $phase]
        );

        $progress = [
            'chunks' => [
                self::STATUS_PENDING => 0,
                self::STATUS_RUNNING => 0,
                self::STATUS_DONE => 0,
                self::STATUS_FAILED => 0,
            ],
            'plannedRows' => 0,
            'writtenRows' => 0,
            'doneRows' => 0,
            'totalChunks' => 0,
        ];

        foreach ($rows as $row) {
            $status = (int) $row['status'];
            $progress['chunks'][$status] = (int) $row['chunks'];
            $progress['totalChunks'] += (int) $row['chunks'];
            $progress['plannedRows'] += (int) $row['planned_rows'];
            $progress['writtenRows'] += (int) $row['written_rows'];

            if (self::STATUS_DONE === $status) {
                $progress['doneRows'] = (int) $row['planned_rows'];
            }
        }

        return $progress;
    }

    /**
     * @return array[] chunks currently claimed, newest claim first - the per-worker progress lines
     */
    public function getRunningChunks(int $limit = 20): array
    {
        $table = self::chunkTable();

        return Db::fetchAll(
            "SELECT * FROM `$table`
              WHERE `idrun` = ? AND `status` = ?
              ORDER BY `ts_started` DESC
              LIMIT " . (int) $limit,
            [$this->idRun, self::STATUS_RUNNING]
        );
    }

    /**
     * @return array[] chunks that gave up
     */
    public function getFailedChunks(int $limit = 20): array
    {
        $table = self::chunkTable();

        return Db::fetchAll(
            "SELECT * FROM `$table` WHERE `idrun` = ? AND `status` = ? ORDER BY `idchunk` LIMIT " . (int) $limit,
            [$this->idRun, self::STATUS_FAILED]
        );
    }

    public function hasPending(int $phase): bool
    {
        $table = self::chunkTable();

        $count = Db::fetchOne(
            "SELECT COUNT(*) FROM `$table` WHERE `idrun` = ? AND `phase` = ? AND `status` IN (?, ?)",
            [$this->idRun, $phase, self::STATUS_PENDING, self::STATUS_RUNNING]
        );

        return $count > 0;
    }

    public function getChunk(int $idChunk): ?array
    {
        $table = self::chunkTable();
        $row = Db::fetchRow("SELECT * FROM `$table` WHERE `idchunk` = ?", [$idChunk]);

        return $row ?: null;
    }
}
