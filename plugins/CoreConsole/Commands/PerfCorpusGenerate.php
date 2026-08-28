<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\PerfCorpus\ActionDictionary;
use Piwik\Plugins\CoreConsole\PerfCorpus\ChunkGenerator;
use Piwik\Plugins\CoreConsole\PerfCorpus\ChunkQueue;
use Piwik\Plugins\CoreConsole\PerfCorpus\Cleaner;
use Piwik\Plugins\CoreConsole\PerfCorpus\Coordinator;
use Piwik\Plugins\CoreConsole\PerfCorpus\Formatter;
use Piwik\Plugins\CoreConsole\PerfCorpus\ProgressPrinter;
use Piwik\Plugins\CoreConsole\PerfCorpus\Profile;
use Piwik\Plugins\CoreConsole\PerfCorpus\RunContext;
use Piwik\Plugins\CoreConsole\PerfCorpus\SetupHelper;
use Piwik\Plugins\CoreConsole\PerfCorpus\Sink\MysqlLoadDataSink;
use Piwik\Plugins\CoreConsole\PerfCorpus\Worker;

/**
 * Generates a large, realistic, reproducible performance corpus straight into the log tables.
 *
 * The corpus is a fixed read-side dataset for benchmarking archiving and reporting queries. It is
 * generated once, verified, and snapshotted; the snapshot is restored before every test run so
 * repeated measurements are comparable.
 *
 * Re-running the identical command resumes: work is tracked per chunk in the perfcorpus_chunk
 * table, and every chunk owns a pre-allocated range of primary keys, so a failed chunk can be
 * deleted by id range and redone without leaving duplicates or orphans behind.
 *
 * Development and benchmarking tooling. It is not intended for use on a production install.
 */
class PerfCorpusGenerate extends ConsoleCommand
{
    private ?Coordinator $coordinator = null;
    private ?Worker $worker = null;

    /**
     * Ctrl-C should cost one chunk per worker, not a dozen chunks left claimed until their
     * heartbeats expire.
     */
    public function getSystemSignalsToHandle(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSystemSignal(int $signal): void
    {
        $this->getOutput()->writeln('');
        $this->getOutput()->writeln('<comment>stopping after the current chunk(s)...</comment>');

        if (null !== $this->worker) {
            $this->worker->requestStop();
        }

        if (null !== $this->coordinator) {
            $this->coordinator->shutdown();
        }
    }

    protected function configure()
    {
        $this->setName('perfcorpus:generate');
        $this->setDescription(
            'Generate a large performance-test corpus into the log tables. Resumable: '
            . 're-run the same command to continue where it stopped.'
        );

        $this->addRequiredValueOption(
            'profile',
            null,
            'Corpus size: ' . implode(', ', Profile::getNames()) . '. Required, no default.'
        );
        $this->addRequiredValueOption(
            'seed',
            null,
            'Integer seed. The same seed and profile always produce the same corpus. Required.'
        );

        $this->addRequiredValueOption('end-date', null, 'Last day of the corpus (Y-m-d).', null);
        $this->addRequiredValueOption('sites', null, 'How many sites to spread traffic over.', Profile::DEFAULT_SITES);
        $this->addRequiredValueOption(
            'unique-url-share',
            null,
            'Share of pageviews landing on an effectively unique URL, as a percentage. Drives how '
            . 'big log_action gets - see --dry-run for the resulting table size.',
            Profile::DEFAULT_UNIQUE_URL_SHARE * 100
        );
        $this->addRequiredValueOption('shards', null, 'Chunks per day.', Profile::DEFAULT_SHARDS);
        $this->addRequiredValueOption('days', null, 'Override the profile\'s number of days.', null);
        $this->addRequiredValueOption(
            'final-month-hits',
            null,
            'Override the profile\'s tracked actions in the final 30 days.',
            null
        );

        $this->addRequiredValueOption('workers', null, 'Worker processes. Defaults to CPU count, capped at 12.', null);
        $this->addRequiredValueOption('spool-dir', null, 'Where the plan spool is written.', null);
        $this->addRequiredValueOption('stale-after', null, 'Seconds without a heartbeat before a chunk is reclaimed.', 300);
        $this->addRequiredValueOption('max-attempts', null, 'Give up on a chunk after this many failures.', 3);
        $this->addRequiredValueOption('max-chunks', null, 'Chunks a worker handles before it exits and is replaced.', 500);

        $this->addNoValueOption('dry-run', null, 'Print planned volumes, disk and runtime, then exit. Writes nothing.');
        $this->addNoValueOption('plan-only', null, 'Run the plan phase and stop.');
        $this->addNoValueOption('load-only', null, 'Skip planning; load from an existing plan.');
        $this->addNoValueOption('restart', null, 'Discard any existing run and start over. Destructive.');
        $this->addNoValueOption('allow-non-empty', null, 'Proceed even though the log tables already hold rows.');
    }

    protected function doExecute(): int
    {
        $profile = $this->buildProfile();
        $output = $this->getOutput();

        if ($this->getInput()->getOption('dry-run')) {
            $this->printEstimate($profile);

            return self::SUCCESS;
        }

        ChunkQueue::install();

        $context = $this->resolveRun($profile);

        $output->writeln('');
        $output->writeln(sprintf(
            '<info>perfcorpus %s</info>  seed %d  end %s  run %d',
            $profile->getName(),
            $profile->getSeed(),
            $profile->getEndDate(),
            $context->getRunId()
        ));
        $output->writeln(sprintf('  spool  %s', $context->getSpoolDir()));
        $output->writeln(sprintf(
            '  queue  %s plan chunks, %s load chunks',
            Formatter::rows($profile->getShardCount()),
            Formatter::rows($profile->getChunkCount())
        ));
        $output->writeln('');

        SetupHelper::run($profile, function (string $line) use ($output) {
            $output->writeln($line);
        });

        if (!$this->getInput()->getOption('load-only')) {
            $this->runPlanPhase($context);
            $dictionary = $this->installDictionary($context);
            $this->allocateIdRanges($context, $dictionary);
        }

        if ($this->getInput()->getOption('plan-only')) {
            $output->writeln('<info>Plan complete.</info> Drop --plan-only to load it.');
            $output->writeln('');

            return self::SUCCESS;
        }

        $this->runLoadPhase($context);

        $context->getQueue()->setRunStatus(ChunkQueue::RUN_DONE);
        $output->writeln('');
        $output->writeln(sprintf(
            '<info>Corpus generated.</info> Check it with: perfcorpus:verify --run-id=%d --level=full',
            $context->getRunId()
        ));
        $output->writeln('');

        return self::SUCCESS;
    }

    /**
     * Writes the fixed part of log_action, once, before any worker starts - every worker's
     * idaction arithmetic assumes these rows are already there. The base id is recorded on the
     * run so a worker joining later rebuilds exactly the same layout.
     */
    private function installDictionary(RunContext $context): ActionDictionary
    {
        $output = $this->getOutput();

        if (null !== $context->getIdActionBase()) {
            return $context->buildDictionary();
        }

        $base = 1 + (int) Db::fetchOne(
            'SELECT IFNULL(MAX(`idaction`), 0) FROM `' . Common::prefixTable('log_action') . '`'
        );

        $dictionary = new ActionDictionary($context->getProfile(), $base);
        $sink = new MysqlLoadDataSink(MysqlLoadDataSink::defaultStagingDir($context->getRunId()));
        $printer = new ProgressPrinter($output);
        $startedAt = microtime(true);

        $dictionary->insert($sink, function (int $written, int $total) use ($printer) {
            $printer->update(sprintf(
                '  dict   %s %s   %s / %s log_action rows',
                Formatter::bar($written / max(1, $total)),
                Formatter::percent($written / max(1, $total)),
                Formatter::shortRows($written),
                Formatter::shortRows($total)
            ));
        });

        $printer->finish(sprintf(
            '  dict   %s %s   %s log_action rows in %s%s',
            Formatter::bar(1.0),
            Formatter::percent(1.0),
            Formatter::rows($dictionary->getFixedRowCount()),
            Formatter::duration((int) round(microtime(true) - $startedAt)),
            $sink->isUsingLoadData() ? '' : ' (multi-row INSERT fallback)'
        ));

        $context->getQueue()->setIdActionBase($base);

        return $dictionary;
    }

    /**
     * Runs the load phase to completion, in this process. The rolling worker pool arrives in the
     * next step; the queue already supports it, so extra perfcorpus:worker processes started by
     * hand will join in and help right now.
     */
    private function runLoadPhase(RunContext $context): void
    {
        $output = $this->getOutput();
        $queue = $context->getQueue();

        $this->reclaimStaleChunks($context);

        $progress = $queue->getProgress(ChunkQueue::PHASE_LOAD);
        $plannedRows = max(1, $progress['plannedRows']);
        $alreadyDone = $progress['doneRows'];

        if (!$queue->hasPending(ChunkQueue::PHASE_LOAD)) {
            $output->writeln('  load   already complete');

            return;
        }

        $printer = new ProgressPrinter($output);
        $startedAt = microtime(true);
        $rows = 0;
        $chunks = 0;
        $workers = $this->resolveWorkerCount();

        $totals = $this->runPhase(
            $context,
            ChunkQueue::PHASE_LOAD,
            function (array $info) use (
                $printer,
                $plannedRows,
                $alreadyDone,
                $startedAt,
                $workers,
                &$rows,
                &$chunks
            ) {
                $chunks++;
                $rows += (int) $info['rows'];
                $elapsed = max(0.001, microtime(true) - $startedAt);
                $rate = $rows / $elapsed;
                // Planned rows count the five log tables; unique-URL log_action rows are minted
                // during generation and are not in the plan, so clamp rather than read over 100%.
                $done = min($plannedRows, $alreadyDone + $rows);
                $remaining = $rate > 0 ? (int) round(($plannedRows - $done) / $rate) : 0;

                $printer->update(sprintf(
                    '  load   %s %s   %s / %s rows   %s   %s   %dw   eta %s',
                    Formatter::bar($done / $plannedRows),
                    Formatter::percent($done / $plannedRows),
                    Formatter::shortRows($done),
                    Formatter::shortRows($plannedRows),
                    $info['day'] ?? '',
                    Formatter::rate($rate),
                    $workers,
                    Formatter::duration(max(0, $remaining))
                ));
            }
        );

        $elapsed = max(0.001, microtime(true) - $startedAt);
        $printer->finish(sprintf(
            '  load   %s %s   %s rows in %s (%s)',
            Formatter::bar(1.0),
            Formatter::percent(1.0),
            Formatter::rows($rows),
            Formatter::duration((int) round($elapsed)),
            Formatter::rate($rows / $elapsed)
        ));

        if ($totals['failed'] > 0 || $queue->hasPending(ChunkQueue::PHASE_LOAD)) {
            throw new \RuntimeException(sprintf(
                'The load phase did not finish: %d chunks failed. perfcorpus:status shows the '
                . 'errors; re-run this command to retry them.',
                $totals['failed']
            ));
        }
    }

    /**
     * Runs the plan phase to completion. Chunks are claimed from the queue, so this picks up
     * wherever a previous attempt stopped and adding a second process simply makes it faster.
     */
    private function runPlanPhase(RunContext $context): void
    {
        $output = $this->getOutput();
        $queue = $context->getQueue();

        if (!$queue->hasPending(ChunkQueue::PHASE_PLAN)) {
            $output->writeln('  plan   already complete');

            return;
        }

        $this->reclaimStaleChunks($context);

        $shards = $context->getProfile()->getShardCount();
        $startedAt = microtime(true);
        $done = 0;
        $visits = 0;

        $printer = new ProgressPrinter($output);

        $totals = $this->runPhase(
            $context,
            ChunkQueue::PHASE_PLAN,
            function (array $info) use ($printer, $shards, $startedAt, &$done, &$visits) {
                $done++;
                $visits += (int) $info['rows'];
                $elapsed = microtime(true) - $startedAt;
                $rate = $elapsed > 0 ? $visits / $elapsed : 0;
                $remaining = $rate > 0 && $done > 0
                    ? (int) round(($shards - $done) * ($visits / $done) / $rate)
                    : 0;

                $printer->update(sprintf(
                    '  plan   %s %s   %d/%d shards   %s visits   %s   eta %s',
                    Formatter::bar($done / max(1, $shards)),
                    Formatter::percent($done / max(1, $shards)),
                    $done,
                    $shards,
                    Formatter::shortRows($visits),
                    Formatter::rate($rate),
                    Formatter::duration($remaining)
                ));
            }
        );

        $printer->finish(sprintf(
            '  plan   %s %s   %d/%d shards   %s visits',
            Formatter::bar(1.0),
            Formatter::percent(1.0),
            $done,
            $shards,
            Formatter::shortRows($visits)
        ));

        if ($totals['failed'] > 0 || $queue->hasPending(ChunkQueue::PHASE_PLAN)) {
            throw new \RuntimeException(sprintf(
                'The plan phase did not finish: %d chunks failed. Run perfcorpus:status for the '
                . 'errors, then re-run this command to retry them.',
                $totals['failed']
            ));
        }

        $output->writeln(sprintf(
            '  plan   done in %s, %s visits spooled',
            Formatter::duration((int) round(microtime(true) - $startedAt)),
            Formatter::rows($visits)
        ));
    }

    /**
     * Hands every load chunk its block of primary keys, once, now that the plan phase knows the
     * exact counts. Ids start after whatever is already in the log tables, so --allow-non-empty
     * cannot collide with existing rows.
     */
    private function allocateIdRanges(RunContext $context, ActionDictionary $dictionary): void
    {
        $output = $this->getOutput();
        $queue = $context->getQueue();

        if ($queue->isAllocated()) {
            return;
        }

        $maxVisit = (int) Db::fetchOne(
            'SELECT IFNULL(MAX(`idvisit`), 0) FROM `' . Common::prefixTable('log_visit') . '`'
        );
        $maxLinkVa = (int) Db::fetchOne(
            'SELECT IFNULL(MAX(`idlink_va`), 0) FROM `' . Common::prefixTable('log_link_visit_action') . '`'
        );

        $next = $queue->allocateIdRanges(
            $maxVisit + 1,
            $maxLinkVa + 1,
            $dictionary->getFirstTailId(),
            $context->getProfile()->getUniqueUrlShare()
        );

        $output->writeln(sprintf(
            '  ids    idvisit %s..%s, idlink_va %s..%s, unique-url idaction %s..%s',
            Formatter::rows($maxVisit + 1),
            Formatter::rows($next['idvisit'] - 1),
            Formatter::rows($maxLinkVa + 1),
            Formatter::rows($next['idlink_va'] - 1),
            Formatter::rows($dictionary->getFirstTailId()),
            Formatter::rows($next['idaction'] - 1)
        ));

        $queue->setRunStatus(ChunkQueue::RUN_LOADING);
    }

    /**
     * Runs one phase to completion, either in this process or across a pool of worker processes.
     *
     * One worker means in-process: a stack trace from the real failure beats one relayed through
     * a subprocess, which matters while developing. More than one means a real pool. Either way
     * the queue decides who does what, so the two paths produce identical data - which is what
     * the determinism check verifies.
     *
     * @param callable $onChunk receives ['rows' => int, 'day' => ?string, 'shard' => ?int]
     */
    private function runPhase(RunContext $context, int $phase, callable $onChunk): array
    {
        $maxAttempts = (int) $this->getInput()->getOption('max-attempts');
        $workers = $this->resolveWorkerCount();

        if ($workers <= 1) {
            $this->worker = new Worker($context, 0, PHP_INT_MAX, $maxAttempts);

            $totals = $this->worker->run($phase, function (array $chunk, array $stats) use ($onChunk) {
                $onChunk([
                    'rows' => $stats['rows'],
                    'day' => $chunk['day'],
                    'shard' => (int) $chunk['shard'],
                ]);
            });

            $this->worker = null;

            return ['failed' => $totals['failed']];
        }

        $this->coordinator = new Coordinator(
            $context,
            $workers,
            (int) $this->getInput()->getOption('max-chunks'),
            $maxAttempts,
            (int) $this->getInput()->getOption('stale-after'),
            (string) $this->getInput()->getParameterOption('--matomo-domain', '')
        );

        $totals = $this->coordinator->run($phase, function (int $workerNumber, array $info) use ($onChunk) {
            $onChunk([
                'rows' => (int) ($info['rows'] ?? 0),
                'day' => $info['day'] ?? null,
                'shard' => isset($info['shard']) ? (int) $info['shard'] : null,
            ]);
        });

        $this->coordinator = null;

        // A worker exiting non-zero is not itself a failure: it reports one when a chunk gave up,
        // and the queue is the authority on that. The caller checks the queue.
        return ['failed' => 0];
    }

    /**
     * Puts back anything a dead worker still holds. Its id ranges are deleted first, so the retry
     * starts from a clean slate rather than on top of a half-written chunk.
     */
    private function reclaimStaleChunks(RunContext $context): void
    {
        $queue = $context->getQueue();
        $stale = $queue->findStale((int) $this->getInput()->getOption('stale-after'));

        if (empty($stale)) {
            return;
        }

        $output = $this->getOutput();
        $output->writeln(sprintf(
            '<comment>reclaiming %d chunk(s) whose worker stopped reporting</comment>',
            count($stale)
        ));

        foreach ($stale as $chunk) {
            if (ChunkQueue::PHASE_LOAD === (int) $chunk['phase']) {
                Cleaner::deleteChunkRows($chunk);
            }

            $queue->requeue((int) $chunk['idchunk']);
        }
    }

    /**
     * Resumes the matching run if there is one, otherwise creates it. This is what makes
     * "run the identical command again" the recovery procedure: the config stored on the run row
     * has to match exactly, so a changed profile starts a new corpus instead of silently mixing
     * two of them together.
     */
    private function resolveRun(Profile $profile): RunContext
    {
        $output = $this->getOutput();
        $existing = ChunkQueue::findResumableRun($profile);

        if (null !== $existing && $this->getInput()->getOption('restart')) {
            $context = RunContext::load($existing);

            $output->writeln(sprintf('<comment>--restart: discarding run %d</comment>', $existing));
            $deleted = Cleaner::deleteRunRows($context->getQueue(), $context->getSpoolDir());
            $context->getQueue()->deleteRun();
            $output->writeln(sprintf('  removed %s log rows and the spool', Formatter::rows($deleted)));

            $existing = null;
        }

        if (null !== $existing) {
            $output->writeln(sprintf('<comment>resuming run %d</comment>', $existing));

            return RunContext::load($existing);
        }

        $this->preflight($profile);

        $spoolRoot = $this->resolveSpoolDir();
        $idRun = ChunkQueue::createRun($profile, $spoolRoot . '/pending', $this->readGitCommit());

        $spoolDir = $spoolRoot . '/run-' . $idRun;
        Db::query(
            'UPDATE `' . ChunkQueue::runTable() . '` SET `spool_dir` = ? WHERE `idrun` = ?',
            [$spoolDir, $idRun]
        );

        $context = RunContext::load($idRun);
        $context->getQueue()->seedChunks($profile);

        return $context;
    }

    /**
     * Checks that would otherwise surface hours in, when the corpus is half written.
     */
    private function preflight(Profile $profile): void
    {
        $output = $this->getOutput();

        if (!$this->getInput()->getOption('allow-non-empty')) {
            $existingRows = $this->countExistingLogRows();

            if ($existingRows > 0) {
                throw new \RuntimeException(sprintf(
                    'The log tables already hold %s rows. The corpus has to be the only data in '
                    . 'them or the measurements mean nothing. Empty them, or pass --allow-non-empty '
                    . 'if you really do want to add to what is there.',
                    Formatter::rows($existingRows)
                ));
            }
        }

        $this->assertLogTableColumnsExist();

        $spoolRoot = $this->resolveSpoolDir();
        if (!is_dir($spoolRoot) && !@mkdir($spoolRoot, 0o770, true) && !is_dir($spoolRoot)) {
            throw new \RuntimeException('Cannot create the spool directory: ' . $spoolRoot);
        }
        if (!is_writable($spoolRoot)) {
            throw new \RuntimeException('The spool directory is not writable: ' . $spoolRoot);
        }

        $estimate = $profile->estimate();
        $free = @disk_free_space($spoolRoot);

        if (false !== $free && $free < $estimate['spoolBytes']) {
            throw new \RuntimeException(sprintf(
                'The plan spool needs about %s but %s only has %s free.',
                Formatter::bytes($estimate['spoolBytes']),
                $spoolRoot,
                Formatter::bytes((int) $free)
            ));
        }

        if (!$this->hasLocalInfile()) {
            $output->writeln(
                '<comment>LOAD DATA LOCAL INFILE is not available on this server. The load phase '
                . 'will fall back to multi-row INSERTs, which is several times slower. Enable '
                . 'local_infile on the server to avoid that.</comment>'
            );
        }
    }

    /**
     * Checks every column the generator writes actually exists before it writes anything.
     *
     * The log tables carry a skeleton from the schema plus whatever the dimension classes of the
     * active plugins add, so the column set moves with the Matomo version and with which plugins
     * are installed. Discovering a gap through a failed INSERT hours into a load, on one worker
     * out of twelve, is the worst way to find out.
     */
    private function assertLogTableColumnsExist(): void
    {
        $problems = [];

        foreach (ChunkGenerator::getRequiredColumns() as $table => $columns) {
            $present = DbHelper::getTableColumns(Common::prefixTable($table));
            $missing = array_diff($columns, array_keys($present));

            if (!empty($missing)) {
                $problems[] = sprintf('%s is missing %s', $table, implode(', ', $missing));
            }
        }

        if (!empty($problems)) {
            throw new \RuntimeException(
                "The log tables do not have every column this writes:\n  "
                . implode("\n  ", $problems)
                . "\n\nCustom dimension columns appear once CustomDimensions has dimensions "
                . 'configured, which this command does itself - so if those are the only ones '
                . 'listed, run it again. Anything else means a plugin is missing or the Matomo '
                . 'version differs from the one this was written against.'
            );
        }
    }

    private function countExistingLogRows(): int
    {
        $total = 0;

        foreach (['log_visit', 'log_link_visit_action', 'log_conversion', 'log_conversion_item'] as $table) {
            $prefixed = Common::prefixTable($table);
            // A bounded existence check: COUNT(*) on a populated log table is itself a slow query.
            $total += (int) Db::fetchOne("SELECT COUNT(*) FROM (SELECT 1 FROM `$prefixed` LIMIT 1) AS `probe`");
        }

        return $total;
    }

    private function hasLocalInfile(): bool
    {
        try {
            return '1' === (string) Db::fetchOne('SELECT @@local_infile');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Recorded on the run so a corpus can always be traced back to the code that made it.
     */
    private function readGitCommit(): ?string
    {
        if (!function_exists('shell_exec')) {
            return null;
        }

        $commit = @shell_exec('git -C ' . escapeshellarg(PIWIK_DOCUMENT_ROOT) . ' rev-parse HEAD 2>/dev/null');

        return is_string($commit) && 40 === strlen(trim($commit)) ? trim($commit) : null;
    }

    private function buildProfile(): Profile
    {
        $input = $this->getInput();

        $name = $input->getOption('profile');
        if (empty($name)) {
            throw new \InvalidArgumentException(
                '--profile is required. Available: ' . implode(', ', Profile::getNames())
            );
        }

        $seed = $input->getOption('seed');
        if (null === $seed || '' === $seed || !is_numeric($seed)) {
            throw new \InvalidArgumentException(
                '--seed is required and must be an integer. It is what makes the corpus reproducible.'
            );
        }

        return Profile::make($name, (int) $seed, [
            'endDate' => $input->getOption('end-date') ?: null,
            'sites' => $input->getOption('sites'),
            'uniqueUrlShare' => ((float) $input->getOption('unique-url-share')) / 100.0,
            'shards' => $input->getOption('shards'),
            'days' => $input->getOption('days'),
            'finalMonthActions' => $input->getOption('final-month-hits'),
        ]);
    }

    private function printEstimate(Profile $profile): void
    {
        $output = $this->getOutput();
        $estimate = $profile->estimate();
        $config = $profile->toArray();
        $workers = $this->resolveWorkerCount();
        $spoolDir = $this->resolveSpoolDir();

        $output->writeln('');
        $output->writeln(sprintf(
            '<info>perfcorpus %s</info>  seed %d  end %s',
            $config['profile'],
            $config['seed'],
            $config['endDate']
        ));
        $output->writeln('');

        $output->writeln(sprintf(
            '  span                  %d days (%s .. %s), ramp %d%% -> 100%%',
            $config['days'],
            $config['startDate'],
            $config['endDate'],
            (int) round($config['rampStart'] * 100)
        ));
        $output->writeln(sprintf(
            '  final 30 days         %s actions',
            Formatter::rows($config['finalMonthActions'])
        ));
        $output->writeln(sprintf(
            '  sites                 %d (site 1 takes %d%%)',
            $config['sites'],
            (int) round(Profile::SITE_1_SHARE * 100)
        ));
        $output->writeln(sprintf(
            '  unique-url share      %.1f%% of pageviews -> %s unique URLs',
            $config['uniqueUrlShare'] * 100,
            Formatter::rows($estimate['tailUrls'])
        ));
        $output->writeln(sprintf(
            '  hot url pool          %s (Zipf, exponent %.1f)',
            Formatter::rows($estimate['hotPool']),
            Profile::ZIPF_EXPONENT
        ));
        $output->writeln(sprintf('  mega-visits           %s', Formatter::rows($estimate['megaVisits'])));
        $output->writeln('');

        $output->writeln('  table                            rows        size');
        $output->writeln('  ---------------------------------------------------');
        foreach ($estimate['rows'] as $table => $count) {
            $output->writeln(sprintf(
                '  %-24s %12s  %10s',
                $table,
                Formatter::rows($count),
                Formatter::bytes($estimate['bytes'][$table])
            ));
        }
        $output->writeln('  ---------------------------------------------------');
        $output->writeln(sprintf(
            '  %-24s %12s  %10s',
            'total',
            Formatter::rows($estimate['totalRows']),
            Formatter::bytes($estimate['totalBytes'])
        ));
        $output->writeln('');

        $output->writeln(sprintf(
            '  plan spool            %s in %s',
            Formatter::bytes($estimate['spoolBytes']),
            $spoolDir
        ));
        $output->writeln(sprintf(
            '  chunks                %s (%d days x %d shards)',
            Formatter::rows($estimate['chunks']),
            $config['days'],
            $config['shards']
        ));
        $output->writeln(sprintf('  workers               %d', $workers));
        $output->writeln('');

        // The plan estimate is per worker and the plan phase is parallel across shards, so it has
        // to be divided to be comparable with the load estimate, which is already an aggregate.
        $planSeconds = (int) ceil($estimate['planSeconds'] / max(1, min($workers, $config['shards'])));

        $output->writeln(sprintf(
            '  estimated runtime     plan %s, load %s at an assumed %s',
            Formatter::duration($planSeconds),
            Formatter::duration($estimate['loadSeconds']),
            Formatter::rate(Profile::ASSUMED_LOAD_ROWS_PER_SECOND)
        ));
        $output->writeln(
            '                        the load rate is an assumption until measured - run a small '
            . 'profile first and correct it'
        );

        $this->warnAboutDisk($spoolDir, $estimate);

        $output->writeln('');
        $output->writeln('<comment>Nothing was written. Drop --dry-run to generate.</comment>');
        $output->writeln('');
    }

    /**
     * The corpus and the spool land on different volumes (database vs local disk), so only the
     * spool can be checked from here. The database side is reported so it can be checked by hand.
     */
    private function warnAboutDisk(string $spoolDir, array $estimate): void
    {
        $output = $this->getOutput();
        $checkDir = is_dir($spoolDir) ? $spoolDir : dirname($spoolDir);
        $free = @disk_free_space($checkDir);

        $output->writeln('');

        if (false === $free) {
            $output->writeln(sprintf('  <comment>could not read free space for %s</comment>', $checkDir));

            return;
        }

        $output->writeln(sprintf(
            '  local disk            %s free at %s',
            Formatter::bytes((int) $free),
            $checkDir
        ));

        // The staged CSV per chunk is transient, but every worker holds one at a time.
        $csvHeadroom = 200 * 1000 * 1000;
        $needed = $estimate['spoolBytes'] + $csvHeadroom;

        if ($free < $needed) {
            $output->writeln(sprintf(
                '  <error>not enough local disk: need about %s for the spool plus staged CSV</error>',
                Formatter::bytes($needed)
            ));
        }

        $output->writeln(sprintf(
            '  <comment>database needs about %s free - check the target server separately</comment>',
            Formatter::bytes($estimate['totalBytes'])
        ));
    }

    private function resolveWorkerCount(): int
    {
        $requested = $this->getInput()->getOption('workers');

        if (!empty($requested)) {
            return max(1, (int) $requested);
        }

        return max(1, min(12, $this->detectCpuCount()));
    }

    private function detectCpuCount(): int
    {
        if (function_exists('shell_exec')) {
            $count = @shell_exec('getconf _NPROCESSORS_ONLN 2>/dev/null');
            if (is_string($count) && is_numeric(trim($count))) {
                return (int) trim($count);
            }
        }

        return 4;
    }

    private function resolveSpoolDir(): string
    {
        $requested = $this->getInput()->getOption('spool-dir');

        if (!empty($requested)) {
            return rtrim($requested, '/');
        }

        return rtrim(StaticContainer::get('path.tmp'), '/') . '/perfcorpus';
    }
}
