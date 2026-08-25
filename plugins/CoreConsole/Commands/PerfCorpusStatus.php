<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\PerfCorpus\ChunkQueue;
use Piwik\Plugins\CoreConsole\PerfCorpus\Formatter;
use Piwik\Plugins\CoreConsole\PerfCorpus\RunContext;

/**
 * Progress of a perfcorpus run, read straight from the perfcorpus_chunk table.
 *
 * Deliberately separate from the run itself: a generation takes hours, and the SSH session that
 * started it will not survive that. This can be run from any shell, at any time, including after
 * the coordinator has been killed - the queue is the source of truth, not the process.
 */
class PerfCorpusStatus extends ConsoleCommand
{
    private const RUN_STATUS_LABELS = [
        ChunkQueue::RUN_PLANNING => 'planning',
        ChunkQueue::RUN_LOADING => 'loading',
        ChunkQueue::RUN_DONE => 'done',
        ChunkQueue::RUN_FAILED => 'failed',
    ];

    protected function configure()
    {
        $this->setName('perfcorpus:status');
        $this->setDescription('Show the progress of a perfcorpus run.');

        $this->addRequiredValueOption('run-id', null, 'Which run to report on. Defaults to the most recent.', null);
        $this->addNoValueOption('watch', null, 'Repaint every 2 seconds until interrupted.');
        $this->addNoValueOption('json', null, 'Emit one JSON object instead of the display.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $runId = $input->getOption('run-id');
        $context = RunContext::load(null === $runId || '' === $runId ? null : (int) $runId);

        if ($input->getOption('json')) {
            $this->getOutput()->writeln(json_encode($this->collect($context), JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if (!$input->getOption('watch')) {
            $this->render($context);

            return self::SUCCESS;
        }

        while (true) {
            $this->getOutput()->write("\033[H\033[J");
            $this->render($context);
            sleep(2);
        }
    }

    private function collect(RunContext $context): array
    {
        $queue = $context->getQueue();
        $profile = $context->getProfile();

        return [
            'runId' => $context->getRunId(),
            'status' => self::RUN_STATUS_LABELS[$context->getStatus()] ?? 'unknown',
            'profile' => $profile->toArray(),
            'gitCommit' => $context->getGitCommit(),
            'spoolDir' => $context->getSpoolDir(),
            'plan' => $queue->getProgress(ChunkQueue::PHASE_PLAN),
            'load' => $queue->getProgress(ChunkQueue::PHASE_LOAD),
        ];
    }

    private function render(RunContext $context): void
    {
        $output = $this->getOutput();
        $data = $this->collect($context);
        $profile = $context->getProfile();

        $output->writeln('');
        $output->writeln(sprintf(
            '<info>perfcorpus %s</info>  seed %d  run %d  <comment>%s</comment>',
            $profile->getName(),
            $profile->getSeed(),
            $data['runId'],
            $data['status']
        ));
        $output->writeln('');

        $this->renderPhase('plan', $data['plan'], 'shards');
        $this->renderPhase('load', $data['load'], 'chunks');

        $this->renderWorkers($context);
        $this->renderFailures($context);

        $output->writeln('');
    }

    private function renderPhase(string $label, array $progress, string $unit): void
    {
        $output = $this->getOutput();
        $total = max(1, $progress['totalChunks']);
        $done = $progress['chunks'][ChunkQueue::STATUS_DONE];

        // Progress is measured in planned rows, not chunks, so the bar stays honest when the late
        // months are several times larger than the early ones.
        $rowFraction = $progress['plannedRows'] > 0
            ? $progress['doneRows'] / $progress['plannedRows']
            : $done / $total;

        $output->writeln(sprintf(
            '  %-6s %s %s   %s/%s %s',
            $label,
            Formatter::bar($rowFraction),
            Formatter::percent($rowFraction),
            Formatter::rows($done),
            Formatter::rows($progress['totalChunks']),
            $unit
        ));

        if ($progress['plannedRows'] > 0) {
            $output->writeln(sprintf(
                '         rows   %s / %s',
                Formatter::shortRows($progress['doneRows']),
                Formatter::shortRows($progress['plannedRows'])
            ));
        }

        $running = $progress['chunks'][ChunkQueue::STATUS_RUNNING];
        $failed = $progress['chunks'][ChunkQueue::STATUS_FAILED];

        if ($running > 0 || $failed > 0) {
            $output->writeln(sprintf(
                '         %d running, %d failed',
                $running,
                $failed
            ));
        }
    }

    private function renderWorkers(RunContext $context): void
    {
        $running = $context->getQueue()->getRunningChunks(15);

        if (empty($running)) {
            return;
        }

        $output = $this->getOutput();
        $output->writeln('');

        foreach ($running as $chunk) {
            $started = strtotime($chunk['ts_started']);
            $heartbeat = $chunk['ts_heartbeat'] ? strtotime($chunk['ts_heartbeat']) : $started;

            $output->writeln(sprintf(
                '  %-14s %s shard %-4d  %s  heartbeat %ss ago',
                substr((string) $chunk['processing_host'], 0, 14),
                $chunk['day'] ?: 'plan',
                (int) $chunk['shard'],
                Formatter::duration(max(0, time() - $started)),
                max(0, time() - $heartbeat)
            ));
        }
    }

    private function renderFailures(RunContext $context): void
    {
        $failed = $context->getQueue()->getFailedChunks(5);

        if (empty($failed)) {
            return;
        }

        $output = $this->getOutput();
        $output->writeln('');
        $output->writeln('  <error>failed chunks</error>');

        foreach ($failed as $chunk) {
            $output->writeln(sprintf(
                '    chunk %d (%s shard %d, %d attempts): %s',
                (int) $chunk['idchunk'],
                $chunk['day'] ?: 'plan',
                (int) $chunk['shard'],
                (int) $chunk['attempts'],
                substr(str_replace("\n", ' ', (string) $chunk['last_error']), 0, 160)
            ));
        }
    }
}
