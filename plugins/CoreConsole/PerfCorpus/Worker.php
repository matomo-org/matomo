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
use Piwik\Plugins\CoreConsole\PerfCorpus\Sink\MysqlLoadDataSink;

/**
 * The claim-work-report loop, shared by the standalone worker process and by the coordinator when
 * it runs a phase in-process.
 *
 * A worker owns one chunk at a time and heartbeats while it holds it. If it dies, the chunk stays
 * claimed until its heartbeat goes stale, at which point the coordinator deletes the chunk's id
 * ranges and puts it back. Nothing here needs to know whether the worker died cleanly, was
 * kill -9'd, or lost its database connection to a failover.
 *
 * Workers exit after --max-chunks and are replaced, which is core's own answer to long-running
 * memory growth (see CronArchive's --max-archives-to-process).
 */
class Worker
{
    /** Heartbeats are cheap but not free; one every few seconds is plenty against a 300s timeout. */
    private const HEARTBEAT_INTERVAL_SECONDS = 10;

    /** Recycle the connection periodically so a long run cannot sit on a stale one. */
    private const CONNECTION_RECYCLE_SECONDS = 3600;

    private RunContext $context;
    private ChunkQueue $queue;
    private int $workerNumber;
    private int $maxChunks;
    private int $maxAttempts;

    private ?ChunkGenerator $generator = null;
    private ?MysqlLoadDataSink $sink = null;

    private bool $shouldStop = false;
    private ?int $currentChunkId = null;
    private float $lastHeartbeat = 0.0;
    private float $lastReconnect = 0.0;

    public function __construct(RunContext $context, int $workerNumber, int $maxChunks, int $maxAttempts)
    {
        $this->context = $context;
        $this->queue = $context->getQueue();
        $this->workerNumber = $workerNumber;
        $this->maxChunks = $maxChunks;
        $this->maxAttempts = $maxAttempts;
        $this->lastReconnect = microtime(true);
    }

    /**
     * Asks the worker to stop after the chunk it is on. Wired to SIGINT/SIGTERM so Ctrl-C costs
     * at most one chunk rather than losing whatever was in flight.
     */
    public function requestStop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * @param callable|null $onChunkDone called with (chunk, stats) after each finished chunk
     * @return array{chunks: int, rows: int, failed: int}
     */
    public function run(int $phase, ?callable $onChunkDone = null): array
    {
        $totals = ['chunks' => 0, 'rows' => 0, 'failed' => 0];

        while (!$this->shouldStop && $totals['chunks'] < $this->maxChunks) {
            $chunk = $this->queue->claimNext($phase, $this->workerNumber);

            if (null === $chunk) {
                break;
            }

            $this->currentChunkId = (int) $chunk['idchunk'];
            $this->lastHeartbeat = microtime(true);
            $startedAt = microtime(true);

            try {
                $stats = $this->processChunk($phase, $chunk);

                $this->queue->complete($this->currentChunkId, $stats['rows'], $stats['checksum']);

                $totals['chunks']++;
                $totals['rows'] += $stats['rows'];

                if (null !== $onChunkDone) {
                    $onChunkDone($chunk, $stats + ['seconds' => microtime(true) - $startedAt]);
                }
            } catch (\Throwable $e) {
                $this->handleFailure($chunk, $e);
                $totals['failed']++;
            }

            $this->currentChunkId = null;
            $this->recycleConnectionIfDue();
        }

        return $totals;
    }

    private function processChunk(int $phase, array $chunk): array
    {
        if (ChunkQueue::PHASE_PLAN === $phase) {
            return $this->processPlanChunk($chunk);
        }

        return $this->processLoadChunk($chunk);
    }

    private function processLoadChunk(array $chunk): array
    {
        if (null === $chunk['idvisit_start']) {
            throw new \RuntimeException(
                'This chunk has no id range yet, which means the plan phase never finished for it.'
            );
        }

        // A retry has to start from nothing: the previous attempt may have written part of this
        // chunk's id range before it died.
        if ((int) $chunk['attempts'] > 1) {
            Cleaner::deleteChunkRows($chunk);
        }

        $sink = $this->getSink();
        $before = $sink->getRowsWritten();
        $sink->resetChecksum();

        $stats = $this->getGenerator()->generate($chunk, $sink, function () {
            $this->heartbeatIfDue();
        });

        $this->assertChunkLanded($chunk, $stats);

        return [
            'rows' => $sink->getRowsWritten() - $before,
            'checksum' => $sink->getChecksum(),
            'counts' => $stats,
            'fallback' => $sink->getFallbackReason(),
        ];
    }

    /**
     * Counts what actually reached the database in this chunk's id ranges and compares it with
     * what was generated. Cheap - a scan of one chunk's rows along the clustered index - and it
     * turns a silent partial write into a failed chunk that gets retried, rather than a hole
     * somebody discovers hours later when the corpus is being verified.
     */
    private function assertChunkLanded(array $chunk, array $stats): void
    {
        $checks = [
            ['log_visit', 'idvisit', (int) $chunk['idvisit_start'], $stats['visits']],
            ['log_link_visit_action', 'idlink_va', (int) $chunk['idlink_va_start'], $stats['actions']],
        ];

        foreach ($checks as [$table, $column, $start, $expected]) {
            if ($expected < 1) {
                continue;
            }

            $prefixed = Common::prefixTable($table);
            $actual = (int) Db::fetchOne(
                "SELECT COUNT(*) FROM `$prefixed` WHERE `$column` BETWEEN ? AND ?",
                [$start, $start + $expected - 1]
            );

            if ($actual !== $expected) {
                throw new \RuntimeException(sprintf(
                    'Chunk %d wrote %d rows to %s but %d are present in its id range %d..%d.',
                    (int) $chunk['idchunk'],
                    $expected,
                    $table,
                    $actual,
                    $start,
                    $start + $expected - 1
                ));
            }
        }
    }

    private function getGenerator(): ChunkGenerator
    {
        if (null === $this->generator) {
            $this->generator = new ChunkGenerator($this->context, $this->context->buildDictionary());
        }

        return $this->generator;
    }

    private function getSink(): MysqlLoadDataSink
    {
        if (null === $this->sink) {
            $this->sink = new MysqlLoadDataSink(
                MysqlLoadDataSink::defaultStagingDir($this->context->getRunId())
            );
        }

        return $this->sink;
    }

    private function processPlanChunk(array $chunk): array
    {
        $planner = new Planner($this->context);

        $counts = $planner->planShard((int) $chunk['shard'], function () {
            $this->heartbeatIfDue();
        });

        return [
            'rows' => $counts['visits'],
            'checksum' => sprintf('%08x', crc32(implode(',', [
                $counts['visits'],
                $counts['actions'],
                $counts['conversions'],
                $counts['items'],
                $counts['visitors'],
            ]))),
            'counts' => $counts,
        ];
    }

    /**
     * A failed chunk has to leave nothing behind before it can be retried, which is exactly what
     * the pre-allocated id ranges make possible.
     */
    private function handleFailure(array $chunk, \Throwable $e): void
    {
        try {
            if (ChunkQueue::PHASE_LOAD === (int) $chunk['phase']) {
                Cleaner::deleteChunkRows($chunk);
            }
        } catch (\Throwable $cleanupError) {
            // Reported through the chunk's error, below - the original failure matters more.
            $e = new \RuntimeException(
                $e->getMessage() . ' (cleanup also failed: ' . $cleanupError->getMessage() . ')',
                0,
                $e
            );
        }

        $this->queue->release((int) $chunk['idchunk'], $e->getMessage(), $this->maxAttempts);
    }

    public function heartbeatIfDue(): void
    {
        if (null === $this->currentChunkId) {
            return;
        }

        $now = microtime(true);

        if ($now - $this->lastHeartbeat < self::HEARTBEAT_INTERVAL_SECONDS) {
            return;
        }

        $this->queue->heartbeat($this->currentChunkId);
        $this->lastHeartbeat = $now;
    }

    /**
     * Mirrors CronArchive::disconnectDb(): drop the connection now and again so a run measured in
     * hours never trips over wait_timeout or a connection left behind by a failover.
     */
    private function recycleConnectionIfDue(): void
    {
        $now = microtime(true);

        if ($now - $this->lastReconnect < self::CONNECTION_RECYCLE_SECONDS) {
            return;
        }

        Db::destroyDatabaseObject();
        $this->lastReconnect = $now;
    }
}
