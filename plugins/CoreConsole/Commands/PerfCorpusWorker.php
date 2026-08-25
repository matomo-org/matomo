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
use Piwik\Plugins\CoreConsole\PerfCorpus\RunContext;
use Piwik\Plugins\CoreConsole\PerfCorpus\Worker;

/**
 * One perfcorpus worker process. Claims chunks from perfcorpus_chunk and generates them.
 *
 * Normally spawned by perfcorpus:generate, which keeps a rolling pool of these. It can also be
 * started by hand - including on another machine - to add capacity to a run already in progress:
 * the queue lives in the database and chunks are claimed with a compare-and-swap, so workers need
 * no coordination beyond the database itself.
 *
 * Progress is reported by one JSON line per finished chunk on stdout, which the coordinator
 * reads. Keep that output small: nothing else should be written to stdout here.
 */
class PerfCorpusWorker extends ConsoleCommand
{
    private ?Worker $worker = null;

    protected function configure()
    {
        $this->setName('perfcorpus:worker');
        $this->setDescription('Internal: generate chunks for a perfcorpus run. Started by perfcorpus:generate.');

        $this->addRequiredValueOption('run-id', null, 'The run to join. Required.');
        $this->addRequiredValueOption('worker', null, 'Worker number, for the progress display.', 0);
        $this->addRequiredValueOption('max-chunks', null, 'Exit after this many chunks so memory cannot creep.', 500);
        $this->addRequiredValueOption('max-attempts', null, 'Give up on a chunk after this many failures.', 3);
        $this->addRequiredValueOption('phase', null, 'Which phase to work on: plan or load.', 'load');
    }

    /**
     * Handled so a Ctrl-C or a coordinator shutdown finishes the current chunk and releases it
     * cleanly, instead of leaving it claimed until its heartbeat goes stale.
     */
    public function getSystemSignalsToHandle(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSystemSignal(int $signal): void
    {
        if (null !== $this->worker) {
            $this->worker->requestStop();
        }
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $runId = $input->getOption('run-id');
        if (empty($runId)) {
            throw new \InvalidArgumentException('--run-id is required.');
        }

        $phase = 'plan' === $input->getOption('phase') ? ChunkQueue::PHASE_PLAN : ChunkQueue::PHASE_LOAD;
        $context = RunContext::load((int) $runId);
        $workerNumber = (int) $input->getOption('worker');

        $this->worker = new Worker(
            $context,
            $workerNumber,
            (int) $input->getOption('max-chunks'),
            (int) $input->getOption('max-attempts')
        );

        $totals = $this->worker->run($phase, function (array $chunk, array $stats) use ($output, $workerNumber) {
            $output->writeln(json_encode([
                'w' => $workerNumber,
                'chunk' => (int) $chunk['idchunk'],
                'day' => $chunk['day'],
                'shard' => (int) $chunk['shard'],
                'rows' => $stats['rows'],
                'seconds' => round($stats['seconds'], 3),
            ]));
        });

        $output->writeln(json_encode(['w' => $workerNumber, 'done' => $totals]));

        return $totals['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
