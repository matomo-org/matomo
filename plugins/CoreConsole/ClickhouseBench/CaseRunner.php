<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Segment;

/**
 * Runs one case on one engine, by running Matomo's own console commands.
 *
 * Nothing here archives anything or dispatches an API request itself. The archive cases run
 * core:invalidate-report-data and then either climulti:request or core:archive; the API cases
 * run climulti:request. That is deliberate - a benchmark of a reimplementation of archiving
 * measures the reimplementation, and the emitted SQL is exactly the thing under test.
 *
 * Two archive drivers, because they answer different questions:
 *
 * - request: runs the single archiving request core:archive would have run for this one
 *   archive, byte for byte (see CronArchive::getVisitsRequestUrl() and makeRequestUrl()).
 *   Scoped to exactly one site/period/date/segment, works with an ad-hoc segment, and cannot
 *   wander off and archive something else. This is the default.
 * - cron: runs core:archive itself, the whole production path including the invalidation scan
 *   and the CliMulti fan-out. Closer to what an operator experiences, but it will only archive
 *   segments that are STORED with auto-archiving on, because CronArchive resolves a done-flag
 *   hash back to a segment definition through the stored segment list
 *   (QueueConsumer::findSegmentForArchive) and skips what it cannot resolve.
 */
final class CaseRunner
{
    public const DRIVER_REQUEST = 'request';
    public const DRIVER_CRON = 'cron';

    private ConsoleProcess $process;
    private MetricsReader $metrics;

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(ConsoleProcess $process, MetricsReader $metrics, array $options)
    {
        $this->process = $process;
        $this->metrics = $metrics;
        $this->options = $options + [
            'archiveDriver' => self::DRIVER_REQUEST,
            'invalidate' => true,
            'cascade' => false,
            'tideways' => true,
            'tidewaysService' => TidewaysSupport::DEFAULT_SERVICE,
            'segmentIds' => [],
            'matomoDomain' => '',
            'onCommand' => null,
        ];
    }

    /**
     * @return string[] the commands this case would run, for --dry-run
     */
    public function plan(BenchCase $case): array
    {
        $commands = [];
        if ($case->isArchive() && $this->options['invalidate']) {
            $commands[] = $this->renderCommand($this->invalidateArguments($case));
        }
        $commands[] = $this->renderCommand($this->measuredArguments($case));

        return $commands;
    }

    public function run(BenchCase $case, Engine $engine, int $iteration, bool $isWarmup): RunResult
    {
        $env = $engine->getChildEnvironment();
        if ($this->options['tideways']) {
            $env += TidewaysSupport::environment((string) $this->options['tidewaysService'], $engine);
        }

        $commands = [];

        // Invalidation is setup, not measurement, so it runs before the clock starts. It is
        // also what makes the run cold: without it the second iteration finds a usable archive,
        // reports wasCached, writes no ArchivingMetrics row, and lands in the results as a
        // suspiciously fast leg.
        if ($case->isArchive() && $this->options['invalidate']) {
            $invalidate = $this->execute($this->invalidateArguments($case), $env);
            $commands[] = $invalidate['command'];

            if ($invalidate['exitCode'] !== 0) {
                return $this->failure(
                    $engine,
                    $case,
                    $iteration,
                    $isWarmup,
                    $invalidate['wallMs'],
                    'core:invalidate-report-data exited ' . $invalidate['exitCode'] . ': '
                        . $this->lastLines($invalidate['output']),
                    $commands
                );
            }
        }

        $watermark = $case->isArchive() ? $this->metrics->watermark() : 0;

        $measured = $this->execute($this->measuredArguments($case), $env);
        $commands[] = $measured['command'];

        if ($measured['timedOut']) {
            return $this->failure(
                $engine,
                $case,
                $iteration,
                $isWarmup,
                $measured['wallMs'],
                'timed out',
                $commands
            );
        }

        if ($measured['exitCode'] !== 0) {
            return $this->failure(
                $engine,
                $case,
                $iteration,
                $isWarmup,
                $measured['wallMs'],
                'exited ' . $measured['exitCode'] . ': ' . $this->lastLines($measured['output']),
                $commands
            );
        }

        $isCronArchive = $case->isArchive() && $this->options['archiveDriver'] === self::DRIVER_CRON;
        $decoded = $isCronArchive ? null : ResultFingerprint::decodeApiOutput($measured['output']);

        // An API request that fails still exits 0 - the failure is in the payload. The version
        // gate ("Please run the update process first") arrives exactly this way, and reporting
        // it as a timing would publish the cost of refusing to answer.
        $apiError = $this->extractApiError($decoded);
        if ($apiError !== null) {
            return $this->failure($engine, $case, $iteration, $isWarmup, $measured['wallMs'], $apiError, $commands);
        }

        $archiveMs = null;
        $archiveExclusiveMs = null;
        $archiveCount = 0;
        $otherCount = 0;

        if ($case->isArchive()) {
            $rows = $this->metrics->since($watermark, $case->getIdSite(), $this->doneFlag($case));
            $archiveMs = MetricsReader::sumMs($rows['matched'], 'total_time');
            $archiveExclusiveMs = MetricsReader::sumMs($rows['matched'], 'total_time_exclusive');
            $archiveCount = count($rows['matched']);
            $otherCount = count($rows['other']);
        }

        return new RunResult(
            $engine->getKey(),
            $case,
            $iteration,
            $isWarmup,
            true,
            $measured['wallMs'],
            $isCronArchive
                ? ResultFingerprint::ofArchiveLog($measured['output'])
                : ResultFingerprint::of($decoded),
            $archiveMs,
            $archiveExclusiveMs,
            $archiveCount,
            $otherCount,
            '',
            $commands
        );
    }

    /**
     * @return string[]
     */
    private function invalidateArguments(BenchCase $case): array
    {
        $arguments = [
            'core:invalidate-report-data',
            '--sites=' . $case->getIdSite(),
            '--periods=' . $case->getPeriod(),
            '--dates=' . $case->getDate(),
            // An empty value is meaningful here and is not the same as omitting the option:
            // omitting it invalidates every segment, which on an instance with stored segments
            // would enqueue archiving work the benchmark never asked for.
            '--segment=' . $case->getSegment(),
            '-n',
        ];

        if ($this->options['cascade']) {
            $arguments[] = '--cascade';
        }

        return $this->withGlobalOptions($arguments);
    }

    /**
     * @return string[]
     */
    private function measuredArguments(BenchCase $case): array
    {
        if (!$case->isArchive()) {
            return $this->withGlobalOptions([
                'climulti:request',
                '--superuser',
                '--',
                $this->apiQuery($case),
            ]);
        }

        if ($this->options['archiveDriver'] === self::DRIVER_CRON) {
            return $this->withGlobalOptions($this->cronArchiveArguments($case));
        }

        return $this->withGlobalOptions([
            'climulti:request',
            '--superuser',
            '--',
            $this->archiveQuery($case),
        ]);
    }

    /**
     * @return string[]
     */
    private function cronArchiveArguments(BenchCase $case): array
    {
        $date = $case->getDate();
        $range = strpos($date, ',') === false ? $date . ',' . $date : $date;

        $arguments = [
            'core:archive',
            '--force-idsites=' . $case->getIdSite(),
            '--force-periods=' . $case->getPeriod(),
            '--force-date-range=' . $range,
            // Scheduled tasks are real work but they are not archiving, and they would land in
            // the wall clock of whichever leg happened to run at the top of the hour.
            '--disable-scheduled-tasks',
            // One at a time. Concurrency is a property of the deployment, not of the engine,
            // and parallel archives contend for the same connection pool - which would show up
            // as an engine difference.
            '--concurrent-requests-per-website=1',
            '--concurrent-archivers=1',
        ];

        if ($case->getSegment() === '') {
            $arguments[] = '--skip-all-segments';
        } else {
            $idSegment = $this->options['segmentIds'][$case->getSegment()] ?? null;
            if (empty($idSegment)) {
                // Reported as a failed run rather than quietly archiving all-visits instead:
                // core:archive would exit 0 having done something else entirely.
                throw new \RuntimeException(
                    'The cron driver needs this segment stored with auto-archiving enabled,'
                    . ' because core:archive resolves a done-flag hash through the stored segment'
                    . ' list and skips what it cannot resolve. Run the benchmark once with'
                    . ' --setup-segments, or use the default --archive-driver=request.'
                    . ' Segment: ' . $case->getSegment()
                );
            }
            $arguments[] = '--force-idsegments=' . $idSegment;
        }

        if ($this->options['tideways']) {
            // core:archive hands each archive to CliMulti, which starts a further PHP process.
            // Those inherit the environment but not this process's -d flags, so without this
            // the archiving work itself - the only part worth tracing - goes untraced.
            $phpCliOptions = $this->process->getPhpCliOptionsString();
            if ($phpCliOptions !== '') {
                $arguments[] = '--php-cli-options=' . $phpCliOptions;
            }
        }

        return $arguments;
    }

    /**
     * The archiving request core:archive would have issued for this one archive.
     *
     * Kept identical to CronArchive::getVisitsRequestUrl() plus makeRequestUrl(), including
     * trigger=archivephp - which is not decoration. It is what makes SettingsServer::
     * isArchivePhpTriggered() true, and that in turn is what authorises archiving on an
     * instance with browser archiving off AND what makes the ArchivingMetrics plugin record a
     * row at all. Without it the case looks like it worked and produces no timing.
     */
    private function archiveQuery(BenchCase $case): string
    {
        $query = 'module=API&method=CoreAdminHome.archiveReports'
            . '&idSite=' . $case->getIdSite()
            . '&period=' . $case->getPeriod()
            . '&date=' . $case->getDate()
            . '&format=json';

        if ($case->getSegment() !== '') {
            $query .= '&segment=' . urlencode($case->getSegment());
        }

        if ($case->getPlugin() !== '') {
            $query .= '&plugin=' . $case->getPlugin() . '&pluginOnly=1';
        }

        return $query . '&trigger=archivephp';
    }

    private function apiQuery(BenchCase $case): string
    {
        $params = [
            'module' => 'API',
            'method' => $case->getApiMethod(),
            'idSite' => (string) $case->getIdSite(),
            'period' => $case->getPeriod(),
            'date' => $case->getDate(),
            'format' => 'json',
        ];

        if ($case->getSegment() !== '') {
            $params['segment'] = $case->getSegment();
        }

        foreach ($case->getApiParams() as $name => $value) {
            $params[$name] = (string) $value;
        }

        $parts = [];
        foreach ($params as $name => $value) {
            $parts[] = $name . '=' . urlencode((string) $value);
        }

        return implode('&', $parts);
    }

    private function doneFlag(BenchCase $case): string
    {
        $segment = new Segment($case->getSegment(), [$case->getIdSite()]);

        // An empty $plugin means the all-plugins flag, which is the only kind ArchivingMetrics
        // records - it skips flags containing a '.', so a plugin-scoped archive has no row to
        // match anyway.
        return Rules::getDoneStringFlagFor([$case->getIdSite()], $segment, $case->getPeriod(), '');
    }

    /**
     * @param string[] $arguments
     * @return string[]
     */
    private function withGlobalOptions(array $arguments): array
    {
        $domain = (string) $this->options['matomoDomain'];
        if ($domain === '') {
            return $arguments;
        }

        // Forwarded because omitting it on a multi-instance host does not fail - it silently
        // resolves to a different instance, or to none.
        array_splice($arguments, 1, 0, ['--matomo-domain=' . $domain]);

        return $arguments;
    }

    /**
     * @param string[] $arguments
     * @param array<string, string> $env
     * @return array{command: string, exitCode: int, output: string, wallMs: float, timedOut: bool}
     */
    private function execute(array $arguments, array $env): array
    {
        $outcome = $this->process->run($arguments, $env);

        if (is_callable($this->options['onCommand'])) {
            ($this->options['onCommand'])($outcome);
        }

        return $outcome;
    }

    /**
     * @param mixed $decoded
     */
    private function extractApiError($decoded): ?string
    {
        if (!is_array($decoded)) {
            return null;
        }

        $isError = ($decoded['result'] ?? null) === 'error' || isset($decoded['error']);
        if (!$isError) {
            return null;
        }

        $message = $decoded['message'] ?? ($decoded['error'] ?? 'unknown API error');

        return 'API error: ' . (is_scalar($message) ? (string) $message : (string) json_encode($message));
    }

    private function lastLines(string $output, int $lines = 3): string
    {
        $trimmed = trim($output);
        if ($trimmed === '') {
            return '(no output)';
        }

        $all = preg_split('~\R~', $trimmed) ?: [];
        $tail = array_slice($all, -$lines);

        return trim(implode(' | ', $tail));
    }

    /**
     * @param string[] $commands
     */
    private function failure(
        Engine $engine,
        BenchCase $case,
        int $iteration,
        bool $isWarmup,
        float $wallMs,
        string $error,
        array $commands
    ): RunResult {
        return new RunResult(
            $engine->getKey(),
            $case,
            $iteration,
            $isWarmup,
            false,
            $wallMs,
            null,
            null,
            null,
            0,
            0,
            $error,
            $commands
        );
    }

    private function renderCommand(array $arguments): string
    {
        return $this->process->getConsolePath() . ' ' . implode(' ', array_map(
            static fn(string $argument): string => str_contains($argument, ' ') ? '"' . $argument . '"' : $argument,
            $arguments
        ));
    }
}
