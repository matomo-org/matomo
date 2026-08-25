<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\PerfCorpus\ChurnRunner;
use Piwik\Plugins\CoreConsole\PerfCorpus\Formatter;
use Piwik\Plugins\CoreConsole\PerfCorpus\Profile;
use Piwik\Plugins\CoreConsole\PerfCorpus\ProgressPrinter;
use Piwik\Plugins\CoreConsole\PerfCorpus\RunContext;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Tracker\Db as TrackerDb;

/**
 * Reproduces live tracking write load against a loaded corpus, by running the real tracker.
 *
 * The corpus is insert-only: it is the end state of a set of visits. Matomo's tracker is not
 * append-only, though - every action after the first UPDATEs the same log_visit row, and there
 * are several more UPDATEs around it - so a corpus alone says nothing about how a replication or
 * change-data-capture pipeline copes with that churn, nor about how the write path behaves under
 * sustained load.
 *
 * This measures it without anyone having to trust a model of the tracker: requests go through
 * Tracker::trackRequest(), so the SQL is produced by the tracker itself, and the statement mix is
 * read back out of the tracker's own SQL profiler.
 *
 * Run it after the corpus is loaded, so the tables are at realistic size. Then re-run the report
 * queries while it is running: a read benchmark taken on an idle system is not the number that
 * matters.
 */
class PerfCorpusChurn extends ConsoleCommand
{
    private const TOKEN_DESCRIPTION = 'perfcorpus-churn';

    protected function configure()
    {
        $this->setName('perfcorpus:churn');
        $this->setDescription(
            'Drive the real tracker against a loaded corpus to reproduce live INSERT/UPDATE churn, '
            . 'and report the statement mix it produced.'
        );

        $this->addRequiredValueOption('duration', null, 'How long to run, e.g. 30m, 2h, 600s.', '5m');
        $this->addRequiredValueOption(
            'visits-per-second',
            null,
            'New visits per second: a number, or peak / spike / average to derive it from the '
            . 'corpus profile\'s traffic curve. Default peak, because that is the rate that '
            . 'decides whether the write path keeps up.',
            'peak'
        );
        $this->addRequiredValueOption('seed', null, 'Integer seed for the traffic it generates.', 1);
        $this->addRequiredValueOption('workers', null, 'Tracker processes. Each is one PHP core.', 1);
        $this->addRequiredValueOption('run-id', null, 'Corpus run to match sites and URL pool to.', null);
        $this->addRequiredValueOption(
            'action-gap',
            null,
            'Wall-clock seconds between a visit\'s actions. Lower means fewer visits held open at '
            . 'once, and updates landing closer to their insert.',
            10
        );
        $this->addRequiredValueOption('max-open-visits', null, 'Refuse to hold more than this many visits open.', 20000);
        $this->addRequiredValueOption('page-performance-share', null, 'Share of pageviews sending a performance ping, as a percentage.', 85);
        $this->addRequiredValueOption('user-id-rewrite-share', null, 'Share of visits where a user id arrives mid-visit, as a percentage. Each one rewrites idvisitor across every log table.', 10);
        $this->addRequiredValueOption('report', null, 'Write the statement mix as JSON to this path.', null);
        $this->addNoValueOption(
            'no-visitor-id-cookie',
            null,
            'Do not send _id, so the tracker matches visits by fingerprint instead of by cookie. '
            . 'Shows how much of the idvisitor rewrite load comes from sending a visitor id.'
        );
        $this->addNoValueOption(
            'diurnal',
            null,
            'Follow the daily traffic curve instead of holding a flat rate, compressing a whole '
            . 'day into the run. Use it to see whether the sink catches up in the overnight '
            . 'trough; use the flat default to see whether it keeps up at peak.'
        );
        $this->addNoValueOption('worker', null, 'Internal: run as a child of another churn process.');
        $this->addNoValueOption('dry-run', null, 'Print what would be run, then exit.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        $durationSeconds = $this->parseDuration((string) $input->getOption('duration'));
        $actionGap = max(1, (int) $input->getOption('action-gap'));
        $workers = max(1, (int) $input->getOption('workers'));
        $seed = (int) $input->getOption('seed');

        $profile = $this->resolveProfile();
        $visitsPerSecond = $this->resolveRate($profile, (string) $input->getOption('visits-per-second'));

        // Visits stay open across their actions, so the number held at once is the rate times how
        // long one lasts. It is memory, and it is the thing that makes updates land on recently
        // written rows, so it is worth being explicit about rather than discovering.
        $meanVisitSeconds = (Profile::MEAN_ACTIONS_PER_VISIT - 1) * $actionGap;
        $openVisits = (int) ceil($visitsPerSecond * $meanVisitSeconds);
        $maxOpen = (int) $input->getOption('max-open-visits');

        if ($openVisits > $maxOpen) {
            throw new \RuntimeException(sprintf(
                'That rate needs %s visits held open at once (%d/s x %.0fs each), over the %s limit. '
                . 'Lower --visits-per-second, lower --action-gap, or raise --max-open-visits.',
                Formatter::rows($openVisits),
                $visitsPerSecond,
                $meanVisitSeconds,
                Formatter::rows($maxOpen)
            ));
        }

        $expectedRequests = $visitsPerSecond * Profile::MEAN_ACTIONS_PER_VISIT
            * (1 + ((float) $input->getOption('page-performance-share') / 100) * Profile::ACTION_TYPE_PAGEVIEW_SHARE);

        if (!$input->getOption('worker')) {
            $output->writeln('');
            $output->writeln(sprintf(
                '<info>perfcorpus:churn</info>  %s at %d visits/s  seed %d  %d worker(s)',
                Formatter::duration($durationSeconds),
                $visitsPerSecond,
                $seed,
                $workers
            ));
            $output->writeln(sprintf(
                '  %s visits held open at once, %.0fs each, ~%s tracking requests/s expected',
                Formatter::rows($openVisits),
                $meanVisitSeconds,
                Formatter::shortRows((int) round($expectedRequests))
            ));
            $output->writeln(sprintf(
                '  profile %s: average %.0f, peak %.0f, spike day %.0f tracking requests/s',
                $profile->getName(),
                $profile->getAverageActionsPerSecond(),
                $profile->getPeakActionsPerSecond(),
                $profile->getPeakActionsPerSecond(true)
            ));
            $output->writeln('  every request goes through Tracker::trackRequest() - the SQL is the tracker\'s, not ours');
            $output->writeln('');
        }

        if ($input->getOption('dry-run')) {
            $output->writeln('<comment>Nothing was tracked. Drop --dry-run to run it.</comment>');
            $output->writeln('');

            return self::SUCCESS;
        }

        if ($workers > 1 && !$input->getOption('worker')) {
            return $this->runPool($workers, $durationSeconds, $visitsPerSecond, $openVisits);
        }

        return $this->runInProcess(
            $profile,
            $seed,
            $durationSeconds,
            $openVisits,
            $actionGap,
            (float) $input->getOption('page-performance-share') / 100,
            (float) $input->getOption('user-id-rewrite-share') / 100,
            !$input->getOption('no-visitor-id-cookie')
        );
    }

    private function runInProcess(
        Profile $profile,
        int $seed,
        int $durationSeconds,
        int $openVisits,
        int $actionGap,
        float $performanceShare,
        float $rewriteShare
    ): int {
        $output = $this->getOutput();
        $isWorker = (bool) $this->getInput()->getOption('worker');

        $this->quietenTrackerLogging();

        $before = $this->readProfilingSnapshot();
        TrackerDb::enableProfiling();

        $runner = new ChurnRunner(
            $profile,
            $seed + ($isWorker ? (int) getmypid() : 0),
            $isWorker ? (int) getmypid() % 64 : 0,
            $this->resolveTokenAuth(),
            $actionGap,
            $performanceShare,
            $rewriteShare
        );

        $printer = new ProgressPrinter($output);
        $startedAt = microtime(true);

        $counters = $runner->run(
            $durationSeconds,
            $openVisits,
            function (array $c, float $elapsed) use ($printer, $durationSeconds, $isWorker) {
                if ($isWorker) {
                    return;
                }

                $printer->update(sprintf(
                    '  churn  %s %s   %s visits, %s requests   %.0f req/s   %s left',
                    Formatter::bar($elapsed / max(1, $durationSeconds)),
                    Formatter::percent($elapsed / max(1, $durationSeconds)),
                    Formatter::shortRows($c['visits']),
                    Formatter::shortRows($c['requests']),
                    $c['requests'] / max(0.001, $elapsed),
                    Formatter::duration((int) max(0, $durationSeconds - $elapsed))
                ));
            },
            $this->buildDiurnalMultiplier()
        );

        $elapsed = max(0.001, microtime(true) - $startedAt);

        // Flush the tracker's own per-query counters into log_profiling, then diff them against
        // what was there before this run.
        \Piwik\Tracker::getDatabase()->recordProfiling();
        TrackerDb::disableProfiling();

        if ($isWorker) {
            $output->writeln(json_encode(['counters' => $counters, 'elapsed' => $elapsed]));

            return self::SUCCESS;
        }

        $printer->finish(sprintf(
            '  churn  %s %s   %s visits, %s requests in %s (%.0f req/s)',
            Formatter::bar(1.0),
            Formatter::percent(1.0),
            Formatter::shortRows($counters['visits']),
            Formatter::shortRows($counters['requests']),
            Formatter::duration((int) round($elapsed)),
            $counters['requests'] / $elapsed
        ));

        $this->report($counters, $before, $elapsed);

        return ($counters['errors'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * One PHP process saturates at a few hundred tracking requests a second, because the tracker
     * does real work per request - device detection above all. Reaching a high-traffic site's
     * peak means several processes.
     */
    private function runPool(int $workers, int $durationSeconds, int $visitsPerSecond, int $openVisits): int
    {
        $output = $this->getOutput();
        $input = $this->getInput();
        $php = rtrim((new \Piwik\CliMulti\CliPhp())->findPhpBinary(), ' -q');

        $before = $this->readProfilingSnapshot();
        $processes = [];
        $perWorkerRate = max(1, (int) floor($visitsPerSecond / $workers));

        for ($i = 0; $i < $workers; $i++) {
            $command = [
                $php,
                PIWIK_DOCUMENT_ROOT . '/console',
                'perfcorpus:churn',
                '--worker',
                '--duration=' . $durationSeconds . 's',
                '--visits-per-second=' . $perWorkerRate,
                '--seed=' . ((int) $input->getOption('seed') + $i),
                '--action-gap=' . (int) $input->getOption('action-gap'),
                '--max-open-visits=' . (int) $input->getOption('max-open-visits'),
                '--page-performance-share=' . $input->getOption('page-performance-share'),
                '--user-id-rewrite-share=' . $input->getOption('user-id-rewrite-share'),
            ];

            if ($input->getOption('no-visitor-id-cookie')) {
                $command[] = '--no-visitor-id-cookie';
            }

            if ($input->getOption('diurnal')) {
                $command[] = '--diurnal';
            }

            if ($input->getOption('run-id')) {
                $command[] = '--run-id=' . (int) $input->getOption('run-id');
            }

            $process = new \Piwik\Process($command);
            $process->setTimeout(null);
            $process->start();
            $processes[] = $process;
        }

        $printer = new ProgressPrinter($output);
        $startedAt = microtime(true);

        while (true) {
            $running = 0;
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $running++;
                }
            }

            $elapsed = microtime(true) - $startedAt;
            $printer->update(sprintf(
                '  churn  %s %s   %d/%d workers running   %s left',
                Formatter::bar($elapsed / max(1, $durationSeconds)),
                Formatter::percent($elapsed / max(1, $durationSeconds)),
                $running,
                $workers,
                Formatter::duration((int) max(0, $durationSeconds - $elapsed))
            ));

            if (0 === $running) {
                break;
            }

            usleep(250000);
        }

        $elapsed = max(0.001, microtime(true) - $startedAt);
        $counters = ['visits' => 0, 'requests' => 0, 'errors' => 0];

        foreach ($processes as $process) {
            foreach (explode("\n", trim($process->getOutput())) as $line) {
                $line = trim($line);
                if ('' === $line || '{' !== $line[0]) {
                    continue;
                }
                $decoded = json_decode($line, true);
                if (!is_array($decoded) || !isset($decoded['counters'])) {
                    continue;
                }
                foreach ($decoded['counters'] as $key => $value) {
                    if (is_numeric($value)) {
                        $counters[$key] = ($counters[$key] ?? 0) + $value;
                    }
                }
            }
        }

        $printer->finish(sprintf(
            '  churn  %s %s   %s visits, %s requests in %s (%.0f req/s across %d workers)',
            Formatter::bar(1.0),
            Formatter::percent(1.0),
            Formatter::shortRows($counters['visits'] ?? 0),
            Formatter::shortRows($counters['requests'] ?? 0),
            Formatter::duration((int) round($elapsed)),
            ($counters['requests'] ?? 0) / $elapsed,
            $workers
        ));

        $this->report($counters, $before, $elapsed);

        return ($counters['errors'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * The tracker logs at INFO - PagePerformance announces every update it makes, for one - which
     * at hundreds of requests a second buries the progress line and costs real time. Nothing here
     * wants those messages; the report is built from the SQL profiler instead.
     */
    private function quietenTrackerLogging(): void
    {
        $log = Config::getInstance()->log;
        $log['log_level'] = 'ERROR';
        $log['log_writers'] = ['file'];
        Config::getInstance()->log = $log;

        try {
            $logger = StaticContainer::get(\Psr\Log\LoggerInterface::class);

            if (method_exists($logger, 'setLevel')) {
                $logger->setLevel('ERROR');
            }
        } catch (\Throwable $e) {
            // Best effort: a noisy run is annoying, not wrong.
        }
    }

    /**
     * The statement mix, taken from the tracker's own profiler rather than from anything this
     * command believes about the tracker.
     */
    private function report(array $counters, array $before, float $elapsed): void
    {
        $output = $this->getOutput();
        $after = $this->readProfilingSnapshot();
        $rows = [];

        foreach ($after as $query => $info) {
            $count = $info['count'] - ($before[$query]['count'] ?? 0);
            $time = $info['sum_time_ms'] - ($before[$query]['sum_time_ms'] ?? 0);

            if ($count > 0) {
                $rows[] = ['query' => $query, 'count' => $count, 'ms' => $time];
            }
        }

        usort($rows, static function (array $a, array $b): int {
            return $b['count'] <=> $a['count'];
        });

        $output->writeln('');
        $output->writeln('  <info>what the tracker did</info>');
        $output->writeln('');
        $output->writeln(sprintf('  %-9s %-9s %-9s  %s', 'count', 'per/s', 'avg ms', 'statement'));
        $output->writeln('  ' . str_repeat('-', 100));

        $inserts = 0;
        $updates = 0;
        $selects = 0;

        foreach (array_slice($rows, 0, 18) as $row) {
            $kind = strtoupper(substr(ltrim($row['query']), 0, 6));
            $output->writeln(sprintf(
                '  %-9s %-9s %-9s  %s',
                Formatter::rows($row['count']),
                number_format($row['count'] / $elapsed, 1),
                number_format($row['count'] > 0 ? $row['ms'] / $row['count'] : 0, 2),
                substr(preg_replace('/\s+/', ' ', $row['query']), 0, 84)
            ));
        }

        foreach ($rows as $row) {
            $kind = strtoupper(substr(ltrim($row['query']), 0, 6));
            if (0 === strpos($kind, 'INSERT')) {
                $inserts += $row['count'];
            } elseif (0 === strpos($kind, 'UPDATE')) {
                $updates += $row['count'];
            } elseif (0 === strpos($kind, 'SELECT')) {
                $selects += $row['count'];
            }
        }

        $output->writeln('');
        $output->writeln(sprintf(
            '  %s INSERT, %s UPDATE, %s SELECT  ->  %.2f UPDATEs per INSERT',
            Formatter::rows($inserts),
            Formatter::rows($updates),
            Formatter::rows($selects),
            $inserts > 0 ? $updates / $inserts : 0
        ));
        $output->writeln(sprintf(
            '  %s rows changed per second reaching the binlog, and therefore any replica',
            number_format(($inserts + $updates) / $elapsed, 1)
        ));

        $this->reportIdVisitorRewrites($rows, $elapsed, $counters);
        $output->writeln('');
        $output->writeln(sprintf(
            '  visits %s   pageviews %s   events %s   searches %s   perf pings %s',
            Formatter::rows($counters['visits'] ?? 0),
            Formatter::rows($counters['pageviews'] ?? 0),
            Formatter::rows($counters['events'] ?? 0),
            Formatter::rows($counters['searches'] ?? 0),
            Formatter::rows($counters['performancePings'] ?? 0)
        ));
        $output->writeln(sprintf(
            '  goal conversions %s   ecommerce orders %s   idvisitor rewrites %s   errors %s',
            Formatter::rows($counters['goalConversions'] ?? 0),
            Formatter::rows($counters['ecommerceOrders'] ?? 0),
            Formatter::rows($counters['userIdRewrites'] ?? 0),
            Formatter::rows($counters['errors'] ?? 0)
        ));

        if (!empty($counters['lastError'])) {
            $output->writeln('  <error>last error: ' . $counters['lastError'] . '</error>');
        }

        $output->writeln('');

        $reportPath = $this->getInput()->getOption('report');
        if (!empty($reportPath)) {
            file_put_contents($reportPath, json_encode([
                'ranAt' => gmdate('c'),
                'elapsedSeconds' => round($elapsed, 2),
                'counters' => $counters,
                'statements' => $rows,
                'totals' => ['insert' => $inserts, 'update' => $updates, 'select' => $selects],
            ], JSON_PRETTY_PRINT) . "\n");
            $output->writeln('  report written to ' . $reportPath);
            $output->writeln('');
        }
    }

    /**
     * The cross-table idvisitor rewrite gets its own line because it is the most expensive
     * statement the tracker issues and it touches three tables at once.
     *
     * With a stable visitor identity this does not fire at all: every visit here keeps the same
     * idvisitor for its whole life, as a real visitor does. What remains is caused entirely by
     * --user-id-rewrite-share - visits where a user id arrives mid-session and genuinely
     * re-identifies the visitor, which is real Matomo behaviour.
     *
     * So the number to validate is not this one, it is the input: what share of real visits set a
     * user id partway through. Set --user-id-rewrite-share to that and this follows.
     *
     * Two earlier versions overstated it badly, both worth remembering. Sending every visitor the
     * same IP collapsed config_id, the fingerprint the tracker falls back on, so unrelated
     * visitors matched each other's open visits. And restarting visitor ordinals from a fixed base
     * meant consecutive runs reused visitors inside Matomo's 30-minute visit window, turning
     * new-visit INSERTs into existing-visit UPDATEs.
     */
    private function reportIdVisitorRewrites(array $rows, float $elapsed, array $counters): void
    {
        $rewrites = 0;

        foreach ($rows as $row) {
            if (false !== stripos($row['query'], 'SET `idvisitor`') || false !== stripos($row['query'], 'SET idvisitor')) {
                $rewrites += $row['count'];
            }
        }

        if ($rewrites < 1) {
            return;
        }

        $this->getOutput()->writeln(sprintf(
            '  <comment>%s of those are idvisitor rewrites (%.1f/s across log_visit, '
            . 'log_link_visit_action, log_conversion and log_conversion_item) - validate this '
            . 'rate against a production capture before trusting it</comment>',
            Formatter::rows($rewrites),
            $rewrites / $elapsed
        ));
    }

    /**
     * @return array<string,array{count:int,sum_time_ms:float}>
     */
    private function readProfilingSnapshot(): array
    {
        $table = Common::prefixTable('log_profiling');
        $snapshot = [];

        try {
            foreach (Db::fetchAll("SELECT `query`, `count`, `sum_time_ms` FROM `$table`") as $row) {
                $snapshot[$row['query']] = [
                    'count' => (int) $row['count'],
                    'sum_time_ms' => (float) $row['sum_time_ms'],
                ];
            }
        } catch (\Exception $e) {
            // The table is created on demand by the profiler; an empty snapshot is correct here.
        }

        return $snapshot;
    }

    /**
     * Turns peak / spike / average into a number, from the profile's own traffic curve.
     *
     * Peak is the default on purpose. Real traffic is concentrated into working hours, so a test
     * run at the daily average sits at roughly 60% of the rate the system actually has to
     * survive, and would answer a question nobody asked.
     */
    private function resolveRate(Profile $profile, string $requested): int
    {
        $requested = strtolower(trim($requested));

        if (is_numeric($requested)) {
            return max(1, (int) $requested);
        }

        switch ($requested) {
            case 'average':
                return max(1, (int) ceil($profile->getPeakVisitsPerSecond() / 1.67));
            case 'peak':
                return max(1, (int) ceil($profile->getPeakVisitsPerSecond()));
            case 'spike':
                return max(1, (int) ceil($profile->getPeakVisitsPerSecond(true)));
        }

        throw new \InvalidArgumentException(
            '--visits-per-second must be a number, or one of: average, peak, spike.'
        );
    }

    /**
     * The daily traffic curve, compressed so one run covers a whole simulated day.
     */
    private function buildDiurnalMultiplier(): ?callable
    {
        if (!$this->getInput()->getOption('diurnal')) {
            return null;
        }

        $weights = Profile::DIURNAL_WEIGHTS;
        $peak = max($weights);

        return static function (float $fractionOfRun) use ($weights, $peak): float {
            $hour = (int) floor(max(0.0, min(0.999, $fractionOfRun)) * count($weights));

            return $weights[$hour] / $peak;
        };
    }

    private function resolveProfile(): Profile
    {
        $runId = $this->getInput()->getOption('run-id');

        try {
            $context = RunContext::load(null === $runId || '' === $runId ? null : (int) $runId);

            return $context->getProfile();
        } catch (\Exception $e) {
            // Churn does not need a corpus - it can run against any Matomo. Fall back to a small
            // profile purely for its site count and URL pool size.
            return Profile::make('small', (int) $this->getInput()->getOption('seed'));
        }
    }

    /**
     * An app-specific token, so the tracker accepts cip and geolocates the traffic. Without it
     * every churn visit comes from the machine running the command and the geo columns - which
     * archiving groups by - are empty.
     */
    private function resolveTokenAuth(): string
    {
        return Access::doAsSuperUser(function (): string {
            $model = new UsersModel();

            // Not getCurrentUserLogin(): inside doAsSuperUser that returns a pseudo-login with no
            // row behind it, and addTokenAuth needs a real user.
            $superUsers = $model->getUsersHavingSuperUserAccess();

            if (empty($superUsers)) {
                throw new \RuntimeException(
                    'No super user exists to authenticate the tracking requests with. The churn '
                    . 'test needs one so it can set cip and give each visit its own IP address.'
                );
            }

            $login = $superUsers[0]['login'];

            // Created through the model rather than through the API: createAppSpecificTokenAuth
            // requires the user's password to confirm, which a console command does not have. Only
            // the hash is stored, so a previous token cannot be recovered and is replaced instead.
            $token = $model->generateRandomTokenAuth();

            $model->addTokenAuth(
                $login,
                $token,
                self::TOKEN_DESCRIPTION . ' ' . gmdate('Y-m-d H:i:s'),
                Date::now()->getDatetime(),
                Date::now()->addDay(1)->getDatetime()
            );

            return $token;
        });
    }

    private function parseDuration(string $duration): int
    {
        if (preg_match('/^(\d+)\s*([smh]?)$/i', trim($duration), $matches)) {
            $value = (int) $matches[1];

            switch (strtolower($matches[2])) {
                case 'h':
                    return $value * 3600;
                case 'm':
                    return $value * 60;
                default:
                    return $value;
            }
        }

        throw new \InvalidArgumentException('--duration must look like 600s, 30m or 2h.');
    }
}
