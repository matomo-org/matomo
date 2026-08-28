<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\CliMulti\CliPhp;
use Piwik\Process;

/**
 * Keeps a rolling pool of perfcorpus:worker processes busy until a phase is finished.
 *
 * Not CliMulti, deliberately. CliMulti can only run URL queries through climulti:request and
 * index.php, it waits for a whole batch to finish before starting the next one, and its liveness
 * check shells out to `ps wwx` for every process on every poll. What is needed here is a plain
 * rolling pool of console commands, which is a few dozen lines on top of Symfony's Process.
 *
 * The pool is not where the work is divided up - the queue is. Workers claim chunks with a
 * compare-and-swap, so this class never decides who does what; it only keeps processes alive and
 * collects their progress. That is why extra workers can be started by hand on other machines and
 * simply join in, and why killing the coordinator loses nothing but the display.
 */
class Coordinator
{
    private const POLL_INTERVAL_MICROSECONDS = 100000;

    /** How often to look for chunks whose worker stopped reporting. */
    private const RECLAIM_INTERVAL_SECONDS = 30;

    private RunContext $context;
    private int $workerCount;
    private int $maxChunksPerWorker;
    private int $maxAttempts;
    private int $staleAfterSeconds;

    /**
     * Forwarded to every worker.
     *
     * Matomo keys its config by host - Config builds misc/user/<hostname>/config.ini.php - so on a
     * multi-host install a worker started without --matomo-domain finds no installation, prints
     * "Matomo is not set up yet" and exits without ever claiming a chunk. The coordinator then
     * waits forever for progress that cannot arrive, which looks like a hang rather than a
     * failure. Empty string on a single-host install, where the option is not used.
     */
    private string $matomoDomain;

    /** @var Process[] */
    private array $processes = [];

    private int $nextWorkerNumber = 0;
    private bool $shouldStop = false;
    private float $lastReclaim = 0.0;

    public function __construct(
        RunContext $context,
        int $workerCount,
        int $maxChunksPerWorker,
        int $maxAttempts,
        int $staleAfterSeconds,
        string $matomoDomain = ''
    ) {
        $this->context = $context;
        $this->workerCount = max(1, $workerCount);
        $this->maxChunksPerWorker = $maxChunksPerWorker;
        $this->maxAttempts = $maxAttempts;
        $this->staleAfterSeconds = $staleAfterSeconds;
        $this->matomoDomain = $matomoDomain;
        $this->lastReclaim = microtime(true);
    }

    public function requestStop(): void
    {
        $this->shouldStop = true;
    }

    /**
     * @param callable $onChunk called with (workerNumber, chunkInfo) for every finished chunk
     * @return array{chunks: int, rows: int, workersFailed: int}
     */
    public function run(int $phase, callable $onChunk): array
    {
        $queue = $this->context->getQueue();
        $totals = ['chunks' => 0, 'rows' => 0, 'workersFailed' => 0];

        while (true) {
            $this->reclaimStaleIfDue();

            $hasPending = $queue->hasPending($phase);

            if (!$this->shouldStop && $hasPending) {
                $this->topUpPool($phase);
            }

            $this->collect($totals, $onChunk);

            if (empty($this->processes)) {
                if ($this->shouldStop || !$hasPending) {
                    break;
                }
            }

            usleep(self::POLL_INTERVAL_MICROSECONDS);
        }

        return $totals;
    }

    private function topUpPool(int $phase): void
    {
        while (count($this->processes) < $this->workerCount) {
            $process = new Process($this->buildCommand($phase, $this->nextWorkerNumber));
            $process->setTimeout(null);
            $process->start();

            $this->processes[$this->nextWorkerNumber] = $process;
            $this->nextWorkerNumber++;
        }
    }

    /**
     * Reads whatever the workers have written since the last poll. Workers emit one compact JSON
     * object per finished chunk, so this stays cheap even with a dozen of them.
     */
    private function collect(array &$totals, callable $onChunk): void
    {
        foreach ($this->processes as $number => $process) {
            $output = $process->getIncrementalOutput();

            if ('' !== trim((string) $output)) {
                foreach (explode("\n", trim($output)) as $line) {
                    $line = trim($line);

                    if ('' === $line || '{' !== $line[0]) {
                        continue;
                    }

                    $decoded = json_decode($line, true);

                    if (!is_array($decoded) || isset($decoded['done'])) {
                        continue;
                    }

                    $totals['chunks']++;
                    $totals['rows'] += (int) ($decoded['rows'] ?? 0);
                    $onChunk((int) ($decoded['w'] ?? $number), $decoded);
                }
            }

            if ($process->isRunning()) {
                continue;
            }

            if (0 !== $process->getExitCode()) {
                $totals['workersFailed']++;
            }

            unset($this->processes[$number]);
        }
    }

    /**
     * A worker that died without releasing its chunk leaves it claimed. Its id ranges are deleted
     * before the chunk goes back on the queue, so the retry starts from a clean slate.
     */
    private function reclaimStaleIfDue(): void
    {
        $now = microtime(true);

        if ($now - $this->lastReclaim < self::RECLAIM_INTERVAL_SECONDS) {
            return;
        }

        $this->lastReclaim = $now;
        $queue = $this->context->getQueue();

        foreach ($queue->findStale($this->staleAfterSeconds) as $chunk) {
            if (ChunkQueue::PHASE_LOAD === (int) $chunk['phase']) {
                Cleaner::deleteChunkRows($chunk);
            }

            $queue->requeue((int) $chunk['idchunk']);
        }
    }

    /**
     * Asks every worker to finish its current chunk and exit, then waits for them. Called on
     * Ctrl-C, so an interrupted run costs at most one chunk per worker rather than leaving a
     * dozen chunks claimed until their heartbeats expire.
     */
    public function shutdown(int $graceSeconds = 60): void
    {
        $this->shouldStop = true;

        foreach ($this->processes as $process) {
            $process->signal(SIGTERM);
        }

        $deadline = microtime(true) + $graceSeconds;

        while (microtime(true) < $deadline) {
            $running = false;

            foreach ($this->processes as $process) {
                if ($process->isRunning()) {
                    $running = true;
                }
            }

            if (!$running) {
                return;
            }

            usleep(self::POLL_INTERVAL_MICROSECONDS);
        }

        foreach ($this->processes as $process) {
            $process->stop(0);
        }
    }

    private function buildCommand(int $phase, int $workerNumber): array
    {
        // CliPhp appends ' -q' to the binary it finds, which is fine for a shell string and not
        // for an argv array.
        $php = rtrim((new CliPhp())->findPhpBinary(), ' -q');

        $command = [
            $php,
            PIWIK_DOCUMENT_ROOT . '/console',
            'perfcorpus:worker',
            '--run-id=' . $this->context->getRunId(),
            '--worker=' . $workerNumber,
            '--max-chunks=' . $this->maxChunksPerWorker,
            '--max-attempts=' . $this->maxAttempts,
            '--phase=' . (ChunkQueue::PHASE_PLAN === $phase ? 'plan' : 'load'),
        ];

        if ('' !== $this->matomoDomain) {
            $command[] = '--matomo-domain=' . $this->matomoDomain;
        }

        return $command;
    }
}
