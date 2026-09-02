<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\CliMulti;
use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\CoreConsole\ClickhouseBench\BenchCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\CaseRunner;
use Piwik\Plugins\CoreConsole\ClickhouseBench\ConsoleProcess;
use Piwik\Plugins\CoreConsole\ClickhouseBench\Engine;
use Piwik\Plugins\CoreConsole\ClickhouseBench\MetricsReader;
use Piwik\Plugins\CoreConsole\ClickhouseBench\Reporter;
use Piwik\Plugins\CoreConsole\ClickhouseBench\ResultFingerprint;
use Piwik\Plugins\CoreConsole\ClickhouseBench\RunResult;
use Piwik\Plugins\CoreConsole\ClickhouseBench\SegmentRegistrar;
use Piwik\Plugins\CoreConsole\ClickhouseBench\SuiteBuilder;
use Piwik\Plugins\CoreConsole\ClickhouseBench\TidewaysSupport;

/**
 * Times the same reports and the same archiving runs on MySQL and on the analytics database.
 *
 * The work is done by Matomo's own console commands - core:invalidate-report-data, then either
 * the archiving request core:archive would have issued or core:archive itself, and
 * climulti:request for the report APIs. Nothing is reimplemented here, because the SQL Matomo
 * emits is the thing under test.
 */
class ClickhouseBenchmark extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('clickhouse:benchmark');
        $this->setDescription(
            'Times report APIs and archiving runs on MySQL and on the analytics database, and'
            . ' compares them.'
        );
        $this->setHelp(<<<'HELP'
Runs each case on each engine and prints a comparison.

The two legs differ by one environment variable, MATOMO_ANALYTICS_DB_DISABLED, which is the only
analytics override Matomo honours outside test mode and which can only ever turn the analytics
database off. So one deployed config serves both legs; nothing is edited between them.

The measurements come from Matomo's CLI:

  archive cases   core:invalidate-report-data, then the CoreAdminHome.archiveReports request
                  core:archive would have issued (--archive-driver=request, the default), or
                  core:archive itself (--archive-driver=cron)
  API cases       climulti:request

Archive timings are read from the ArchivingMetrics table, which records the archive build alone.
The child's wall clock is reported too, and the gap between them is bootstrap plus invalidation.

<comment>Examples</comment>

  # both engines, Visits Log and archiving, no segment and the compound segment
  ./console clickhouse:benchmark --date=2026-08-03

  # the full segment set, more warmups, JSON out
  ./console clickhouse:benchmark --date=2026-08-03 \
      --segments=none,compound,negated,conversion,ecommerce \
      --warmups=2 --iterations=5 --json=bench.json

  # the production archiving path end to end (needs the segments stored, see --setup-segments)
  ./console clickhouse:benchmark --date=2026-08-03 --suite=archive --archive-driver=cron

  # show the commands without running them
  ./console clickhouse:benchmark --date=2026-08-03 --dry-run
HELP);

        $this->addRequiredValueOption('date', null, 'Date or date range, as Matomo accepts it, eg 2026-08-03 or 2026-05-05,2026-08-31.');
        $this->addRequiredValueOption('idsite', null, 'Site to measure.', 1);
        $this->addRequiredValueOption('period', null, 'day, week, month, year or range.', 'day');
        $this->addRequiredValueOption('engine', null, 'Engines to measure, comma separated: mysql, clickhouse. Both by default.', '');
        $this->addRequiredValueOption('suite', null, 'Case groups to run, comma separated: api, archive.', 'api,archive');
        $this->addRequiredValueOption('segments', null, 'Segments to run, comma separated: none, compound, negated, conversion, ecommerce.', 'none,compound');
        $this->addRequiredValueOption('case', null, 'Only run these case ids. Globs allowed, eg "v1*". Repeatable.', [], true);
        $this->addRequiredValueOption('iterations', null, 'Timed iterations per case per engine.', 3);
        $this->addRequiredValueOption('warmups', null, 'Discarded iterations before the timed ones. ClickHouse Cloud needs more than one to converge.', 2);

        $this->addRequiredValueOption('archive-driver', null, 'request (one archiving request, ad-hoc segments, default) or cron (core:archive, needs stored segments).', CaseRunner::DRIVER_REQUEST);
        $this->addRequiredValueOption('archive-plugin', null, 'Archive only this plugin. Note ArchivingMetrics records no row for a plugin-scoped archive, so timings fall back to wall clock.', '');
        $this->addNegatableOption('purge-archives', null, 'Delete this case\'s existing archives and its stale archive_invalidations rows before each archive iteration. On by default - leaving either behind lets a later iteration skip work, which reads as a very fast leg.', true);
        $this->addNegatableOption('invalidate', null, 'Invalidate before each archive iteration. On by default - without it the second iteration reuses the first archive.', true);
        $this->addNoValueOption('cascade', null, 'Invalidate child periods too. For week/month/year, without this the run measures aggregation from existing day archives, not log queries.');

        $this->addRequiredValueOption('live-limit', null, 'filter_limit for the Visits Log cases.', 100);
        $this->addRequiredValueOption('transitions-url', null, 'A page URL that exists in the data. Without it the Transitions cases are skipped.', '');

        $this->addRequiredValueOption('needle-url', null, 'pageUrl needle for the built-in segments.', '/news/');
        $this->addRequiredValueOption('needle-excluded-url', null, 'pageUrl needle excluded by the negated segment.', '/sport/');
        $this->addRequiredValueOption('needle-title', null, 'pageTitle needle for the built-in segments.', 'Budget');
        $this->addRequiredValueOption('needle-transitions-title', null, 'pageTitle needle for the Transitions cases. Must MATCH the page in --transitions-url.', 'City');
        $this->addRequiredValueOption('needle-country', null, 'countryCode for the built-in segments.', 'de');
        $this->addRequiredValueOption('needle-product', null, 'productName needle for the ecommerce segment.', 'Daily');
        $this->addRequiredValueOption('needle-goal', null, 'idgoal for the conversion segment.', '1');

        $this->addNegatableOption('tideways', null, 'Enable Tideways in the children: tideways.enable_cli, full sample rate, and a per-leg service name.', true);
        $this->addRequiredValueOption('tideways-service', null, 'Tideways service name. The engine key is appended, so the two legs stay separable.', TidewaysSupport::DEFAULT_SERVICE);
        $this->addRequiredValueOption('tideways-ini', null, 'Extra php.ini override for the children, as name=value. Repeatable. For anything this install needs beyond tideways.enable_cli and tideways.sample_rate.', [], true);

        $this->addRequiredValueOption('timeout', null, 'Seconds before a child is killed. 0 for no limit.', 0);
        $this->addRequiredValueOption('json', null, 'Write the full results, including every iteration, to this file.', '');
        $this->addNoValueOption('dry-run', null, 'Print the commands each case would run, and stop.');
        $this->addNoValueOption('setup-segments', null, 'Store the built-in segments with auto-archiving so --archive-driver=cron can use them, then stop.');
        $this->addNoValueOption('allow-full-rearchive', null, 'With --setup-segments, permit segment creation while process_new_segments_from is beginning_of_time.');
        $this->addNoValueOption('allow-engine-mismatch', null, 'Run even if a leg does not route to the engine its label claims. The output is then not an A/B.');
        $this->addNoValueOption('report-engine', null, 'Internal. Print which engine this process routes to, as JSON, and exit.');
    }

    protected function doExecute(): int
    {
        $input = $this->getInput();
        $output = $this->getOutput();

        if ($input->getOption('report-engine')) {
            return $this->reportEngine();
        }

        $date = (string) $input->getOption('date');
        if ($date === '') {
            $this->writeErrorMessage('--date is required.');
            return self::FAILURE;
        }

        $engines = Engine::fromList((string) $input->getOption('engine'));
        $idSite = (int) $input->getOption('idsite');
        $archiveDriver = (string) $input->getOption('archive-driver');

        if (!in_array($archiveDriver, [CaseRunner::DRIVER_REQUEST, CaseRunner::DRIVER_CRON], true)) {
            $this->writeErrorMessage('--archive-driver must be "request" or "cron".');
            return self::FAILURE;
        }

        $needles = $this->readNeedles();
        $segments = SuiteBuilder::defaultSegments($needles);

        if ($input->getOption('setup-segments')) {
            return $this->setupSegments($idSite, $segments);
        }

        $cases = SuiteBuilder::filter(
            (new SuiteBuilder())->build([
                'idSite' => $idSite,
                'period' => (string) $input->getOption('period'),
                'date' => $date,
                'groups' => $this->readList((string) $input->getOption('suite')),
                'segments' => $segments,
                'segmentKeys' => $this->readList((string) $input->getOption('segments')),
                'liveLimit' => (int) $input->getOption('live-limit'),
                'archivePlugin' => (string) $input->getOption('archive-plugin'),
                'transitionsPageUrl' => (string) $input->getOption('transitions-url'),
                'needles' => $needles,
            ]),
            (array) $input->getOption('case')
        );

        if (empty($cases)) {
            $this->writeErrorMessage('No cases selected. Check --suite, --segments and --case.');
            return self::FAILURE;
        }

        $timeout = (float) $input->getOption('timeout');

        // Forwarded to EVERY child, including the preflight and calibration ones. On a
        // multi-instance host a child without it does not fail: it resolves to no install,
        // reports honestly that it is not using the analytics database, and the preflight
        // reads that as the operator having misconfigured the engine.
        $globalOptions = [];
        $matomoDomain = (string) ($input->getOption('matomo-domain') ?? '');
        if ($matomoDomain !== '') {
            $globalOptions[] = '--matomo-domain=' . $matomoDomain;
        }

        $process = new ConsoleProcess(
            PIWIK_INCLUDE_PATH . '/console',
            null,
            $timeout > 0 ? $timeout : null,
            $input->getOption('tideways')
                ? TidewaysSupport::phpIniOptionsWith((array) $input->getOption('tideways-ini'))
                : [],
            $globalOptions
        );

        $segmentIds = [];
        if ($archiveDriver === CaseRunner::DRIVER_CRON) {
            // Audited over the segments this run will actually archive, not over every segment
            // the suite knows about - otherwise selecting one case reports four problems, three
            // of which are about work that was never going to happen.
            $needed = array_values(array_unique(array_filter(array_map(
                static fn(BenchCase $case): string => $case->isArchive() ? $case->getSegment() : '',
                $cases
            ))));

            $audit = (new SegmentRegistrar())->audit($idSite, $needed);

            $missing = array_values(array_diff($needed, array_keys($audit['usable'])));
            if (!empty($missing)) {
                $this->writeErrorMessage(array_merge(
                    ['--archive-driver=cron needs every archived segment stored with auto-archiving,'
                        . ' because core:archive resolves a done-flag hash through the stored segment'
                        . ' list and skips what it cannot resolve. Not usable:'],
                    $audit['problems'],
                    ['', 'Run with --setup-segments to create them, or use the default'
                        . ' --archive-driver=request, which archives an ad-hoc segment directly.']
                ));
                return self::FAILURE;
            }
            $segmentIds = $audit['usable'];
        }

        $runner = new CaseRunner($process, new MetricsReader(), [
            'archiveDriver' => $archiveDriver,
            'invalidate' => (bool) $input->getOption('invalidate'),
            'purge' => (bool) $input->getOption('purge-archives'),
            'cascade' => (bool) $input->getOption('cascade'),
            'tideways' => (bool) $input->getOption('tideways'),
            'tidewaysService' => (string) $input->getOption('tideways-service'),
            'segmentIds' => $segmentIds,
        ]);

        if ($input->getOption('dry-run')) {
            return $this->dryRun($cases, $engines, $runner);
        }

        // Checked before anything is measured, not at the end when the results are written.
        // The command runs as the web user while the shell that typed it is usually root, so
        // $HOME expands to a directory the process cannot write - and finding that out after a
        // long run means the run is gone.
        $jsonPath = (string) $input->getOption('json');
        if ($jsonPath !== '' && !$this->isWritablePath($jsonPath)) {
            $this->writeErrorMessage(sprintf(
                'Cannot write --json=%s as user "%s". Nothing has been measured yet.'
                . ' Pick a path this user can write, eg /tmp/bench.json.',
                $jsonPath,
                $this->currentUser()
            ));
            return self::FAILURE;
        }

        if (
            !$this->printPreflight($process, $engines, $archiveDriver)
            && !$input->getOption('allow-engine-mismatch')
        ) {
            // Stopping here rather than warning and carrying on. A warning scrolls off the top
            // of a long run and what survives is a table with an engine name on a column that
            // measured something else - which is worse than no measurement at all.
            return self::FAILURE;
        }

        $warmups = max(0, (int) $input->getOption('warmups'));
        $iterations = max(1, (int) $input->getOption('iterations'));

        $calibration = $this->calibrate($process, $engines);

        /** @var RunResult[] $results */
        $results = [];

        foreach ($cases as $case) {
            $output->writeln('');
            $output->writeln('<info>' . $case->getId() . '</info> ' . $case->describe());

            // Engines alternate inside a case rather than one engine finishing the whole suite
            // first, so a change in load on the box during the run lands on both legs instead
            // of on whichever one happened to run second.
            foreach ($engines as $engine) {
                for ($i = 1; $i <= $warmups; $i++) {
                    $warm = $runner->run($case, $engine, $i, true);
                    $results[] = $warm;
                    $this->printRun($warm, 'warmup ' . $i . '/' . $warmups);
                }

                for ($i = 1; $i <= $iterations; $i++) {
                    $run = $runner->run($case, $engine, $i, false);
                    $results[] = $run;
                    $this->printRun($run, 'run ' . $i . '/' . $iterations);
                }
            }
        }

        return $this->report($results, $engines, $calibration, $cases);
    }

    /**
     * Verifies, from inside a child, that this process routes where the caller thinks.
     *
     * Deliberately reports no host, user or database name. The engine identity is what the
     * benchmark needs; connection details are infrastructure and this output is meant to be
     * safe to paste next to the numbers.
     */
    private function reportEngine(): int
    {
        $configured = false;
        $error = '';
        try {
            $configured = Db::hasAnalyticsConfigured();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $adapter = '';
        if ($configured) {
            try {
                $adapter = get_class(Db::getAnalytics());
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        $this->getOutput()->write((string) json_encode([
            'analytics' => $configured,
            'engine' => $configured ? Engine::CLICKHOUSE : Engine::MYSQL,
            'adapter' => $adapter,
            'error' => $error,
        ]));

        return $error === '' ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @param array<string, string> $segments
     */
    private function setupSegments(int $idSite, array $segments): int
    {
        $registrar = new SegmentRegistrar();
        $definitions = array_values(array_filter(
            array_values($segments),
            static fn(string $definition): bool => $definition !== ''
        ));

        try {
            $created = $registrar->create(
                $idSite,
                $definitions,
                (bool) $this->getInput()->getOption('allow-full-rearchive')
            );
        } catch (\RuntimeException $e) {
            $this->writeErrorMessage($e->getMessage());
            return self::FAILURE;
        }

        $audit = $registrar->audit($idSite, $definitions);

        $rows = [];
        foreach ($audit['usable'] as $definition => $idSegment) {
            $rows[] = [$idSegment, isset($created[$definition]) ? 'created' : 'existing', $definition];
        }
        $this->renderTable(['idsegment', 'state', 'definition'], $rows);

        if (!empty($audit['problems'])) {
            $this->writeErrorMessage(array_merge(['Still not usable:'], $audit['problems']));
        }

        if (!empty($created)) {
            $this->writeComment(
                'Segments were created with auto-archiving on, so Matomo has scheduled re-archiving'
                . ' for them. The next core:archive will work through that backlog - run it once and'
                . ' let it finish BEFORE timing anything, or the first archive case will measure the'
                . ' backlog rather than the case.'
            );
        }

        return self::SUCCESS;
    }

    /**
     * @param BenchCase[] $cases
     * @param Engine[] $engines
     */
    private function dryRun(array $cases, array $engines, CaseRunner $runner): int
    {
        $output = $this->getOutput();

        foreach ($cases as $case) {
            $output->writeln('');
            $output->writeln('<info>' . $case->getId() . '</info> ' . $case->describe());
            foreach ($runner->plan($case) as $command) {
                $output->writeln('  ' . $command);
            }
        }

        $output->writeln('');
        foreach ($engines as $engine) {
            $env = [];
            foreach ($engine->getChildEnvironment() as $name => $value) {
                $env[] = $name . '=' . $value;
            }
            $output->writeln('<comment>' . $engine->getLabel() . '</comment> runs the above with ' . implode(' ', $env));
        }

        return self::SUCCESS;
    }

    /**
     * @param Engine[] $engines
     * @return bool false when a leg does not route where its label says
     */
    private function printPreflight(ConsoleProcess $process, array $engines, string $archiveDriver): bool
    {
        $output = $this->getOutput();

        $tideways = TidewaysSupport::describe();
        $output->writeln('Tideways: ' . ($tideways['loaded']
            ? $tideways['extension'] . ' ' . ($tideways['version'] ?? '?')
                . ($tideways['hasDaemon'] ? ', reporting configured' : '')
            : '<comment>not loaded</comment>'));
        foreach ($tideways['notes'] as $note) {
            $this->writeComment($note);
        }

        // Each leg is asked, in its own process, which engine it actually routes to. The label
        // on every number in the table comes from an environment variable, and an environment
        // variable that failed to take effect produces a full run of correct-looking numbers
        // measured on the wrong engine, with nothing in the output to show it.
        $rows = [];
        foreach ($engines as $engine) {
            $outcome = $process->run(['clickhouse:benchmark', '--report-engine'], $engine->getChildEnvironment());
            $decoded = ResultFingerprint::decodeApiOutput($outcome['output']);
            $reported = is_array($decoded) ? (string) ($decoded['engine'] ?? '?') : '?';
            $matches = $reported === $engine->getKey();

            $rows[] = [
                $engine->getLabel(),
                $reported,
                $matches ? 'ok' : 'MISMATCH',
                is_array($decoded) ? (string) ($decoded['adapter'] ?? '') : trim($outcome['output']),
            ];
        }
        $this->renderTable(['leg', 'child reports', 'check', 'adapter'], $rows);

        $routingIsSound = true;
        foreach ($rows as $row) {
            if ($row[2] !== 'ok') {
                $routingIsSound = false;
                $this->writeErrorMessage(
                    'A leg does not route where its label says. Every number in the table would be'
                    . ' attributed to the wrong engine, and the table would look entirely normal.'
                    . ' Check [database_analytics] enabled/host, and that MATOMO_ANALYTICS_DB_DISABLED'
                    . ' is not exported in this shell. Pass --allow-engine-mismatch to measure anyway'
                    . ' - the results are then not an A/B.'
                );
            }
        }

        if ($archiveDriver === CaseRunner::DRIVER_CRON) {
            $cliMulti = new CliMulti();
            if (!$cliMulti->supportsAsync()) {
                $this->writeErrorMessage(
                    'core:archive cannot use CLI processes here, so it will archive over HTTP.'
                    . ' HTTP requests go to php-fpm, which does not inherit this process\'s'
                    . ' environment - so the engine selection would not reach the archiver and'
                    . ' both legs would run on whatever the config says. Use'
                    . ' --archive-driver=request instead.'
                );
                $routingIsSound = false;
            }
        }

        return $routingIsSound;
    }

    /**
     * Measures a child that does no work, so the per-process cost inside every other number is
     * known rather than guessed at.
     *
     * @param Engine[] $engines
     * @return array<string, ?float>
     */
    private function calibrate(ConsoleProcess $process, array $engines): array
    {
        $query = 'module=API&method=API.getMatomoVersion&format=json';
        $calibration = [];

        foreach ($engines as $engine) {
            $samples = [];

            // Four samples, first discarded. The first child of a run pays for cold opcache and
            // a cold filesystem cache, so a single sample overstates bootstrap - enough, on a
            // container filesystem, to come out larger than the whole case it is meant to
            // explain, which is how this was noticed.
            for ($i = 0; $i < 4; $i++) {
                $outcome = $process->run(
                    ['climulti:request', '--superuser', '--', $query],
                    $engine->getChildEnvironment()
                );
                if ($i > 0 && $outcome['exitCode'] === 0) {
                    $samples[] = $outcome['wallMs'];
                }
            }

            $calibration[$engine->getKey()] = Reporter::median($samples);
        }

        return $calibration;
    }

    private function printRun(RunResult $run, string $label): void
    {
        $engine = str_pad($run->getEngineKey(), 10);

        if (!$run->isOk()) {
            $this->getOutput()->writeln(sprintf('  %s %-14s <error>%s</error>', $engine, $label, $run->getError()));
            return;
        }

        $detail = Reporter::formatMs($run->getWallMs()) . ' wall';
        if ($run->hasArchiveMetrics()) {
            $detail = Reporter::formatMs($run->getArchiveMs()) . ' archive ('
                . $run->getArchiveCount() . ' archive' . ($run->getArchiveCount() === 1 ? '' : 's')
                . '), ' . $detail;
        }

        $fingerprint = $run->getFingerprint();
        if ($fingerprint !== null && $fingerprint['summary'] !== '') {
            $detail .= ', ' . $fingerprint['summary'];
        }

        $scrub = $run->getScrub();
        if ($scrub !== null && ($scrub['archives'] > 0 || $scrub['invalidations'] > 0)) {
            $detail .= sprintf(
                ' [scrubbed %d archive(s), %d invalidation(s)]',
                $scrub['archives'],
                $scrub['invalidations']
            );
        }

        $this->getOutput()->writeln(sprintf('  %s %-14s %s', $engine, $label, $detail));
    }

    /**
     * @param RunResult[] $results
     * @param Engine[] $engines
     * @param array<string, ?float> $calibration
     * @param BenchCase[] $cases
     */
    private function report(array $results, array $engines, array $calibration, array $cases): int
    {
        $output = $this->getOutput();
        $reporter = new Reporter();
        $summary = $reporter->summarise($results, $engines);

        $output->writeln('');
        $this->renderTable($reporter->tableHeader($engines), $reporter->tableRows($summary, $engines));

        $calibrationParts = [];
        foreach ($calibration as $engineKey => $ms) {
            $calibrationParts[] = $engineKey . ' ' . Reporter::formatMs($ms);
        }
        $output->writeln('');
        $output->writeln(
            'Per-process bootstrap (a child that does no work): ' . implode(', ', $calibrationParts)
            . '. Included in every wall clock, not in the archive times.'
        );

        $caveats = $reporter->caveats($summary);
        if (!empty($caveats)) {
            $output->writeln('');
            $output->writeln('<comment>Read before quoting these numbers:</comment>');
            foreach ($caveats as $caveat) {
                $output->writeln('  - ' . $caveat);
            }
        }

        $jsonPath = (string) $this->getInput()->getOption('json');
        if ($jsonPath !== '') {
            $json = $reporter->toJson($results, $summary, [
                'engines' => array_map(static fn(Engine $engine): string => $engine->getKey(), $engines),
                'archiveDriver' => (string) $this->getInput()->getOption('archive-driver'),
                'iterations' => (int) $this->getInput()->getOption('iterations'),
                'warmups' => (int) $this->getInput()->getOption('warmups'),
                'invalidate' => (bool) $this->getInput()->getOption('invalidate'),
                'purge' => (bool) $this->getInput()->getOption('purge-archives'),
                'cascade' => (bool) $this->getInput()->getOption('cascade'),
                'tideways' => TidewaysSupport::describe(),
                'bootstrapMs' => $calibration,
                'cases' => array_map(static fn(BenchCase $case): array => $case->toArray(), $cases),
            ]);

            if (false === file_put_contents($jsonPath, $json)) {
                $this->writeErrorMessage('Could not write ' . $jsonPath);
                return self::FAILURE;
            }
            $output->writeln('');
            $output->writeln('Wrote ' . $jsonPath);
        }

        $failed = array_filter(
            $results,
            static fn(RunResult $run): bool => !$run->isOk() && !$run->isWarmup()
        );

        if (!empty($failed)) {
            $this->writeErrorMessage(count($failed) . ' timed run(s) failed. See the table and the notes above.');
            return self::FAILURE;
        }

        $this->writeSuccessMessage('Benchmark complete.');

        return self::SUCCESS;
    }

    private function isWritablePath(string $path): bool
    {
        if (file_exists($path)) {
            return is_writable($path);
        }

        $directory = dirname($path);

        return is_dir($directory) && is_writable($directory);
    }

    private function currentUser(): string
    {
        if (function_exists('posix_geteuid') && function_exists('posix_getpwuid')) {
            $user = posix_getpwuid(posix_geteuid());
            if (!empty($user['name'])) {
                return (string) $user['name'];
            }
        }

        return (string) (getenv('USER') ?: 'unknown');
    }

    /**
     * @return array<string, string>
     */
    private function readNeedles(): array
    {
        $input = $this->getInput();

        return [
            'url' => (string) $input->getOption('needle-url'),
            'excludedUrl' => (string) $input->getOption('needle-excluded-url'),
            'title' => (string) $input->getOption('needle-title'),
            'transitionsTitle' => (string) $input->getOption('needle-transitions-title'),
            'country' => (string) $input->getOption('needle-country'),
            'product' => (string) $input->getOption('needle-product'),
            'idGoal' => (string) $input->getOption('needle-goal'),
        ];
    }

    /**
     * @return string[]
     */
    private function readList(string $value): array
    {
        return array_values(array_filter(
            array_map('trim', explode(',', $value)),
            static fn(string $item): bool => $item !== ''
        ));
    }
}
