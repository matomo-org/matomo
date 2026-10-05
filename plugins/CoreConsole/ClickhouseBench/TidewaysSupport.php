<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use Symfony\Component\Process\Process;

/**
 * Makes the benchmark's child processes visible to Tideways.
 *
 * Two things stop a CLI benchmark from showing up in Tideways by default, and both are silent:
 *
 * 1. Tideways ignores CLI processes unless tideways.enable_cli is on. Without it the run
 *    completes normally and simply produces no trace, which looks the same as a run whose
 *    traces have not arrived yet.
 * 2. Sampling. At a production sample rate most of a short benchmark is not traced, and the
 *    traces that do land are an arbitrary subset - so the slowest case can easily be the one
 *    with no trace. A benchmark wants every run traced.
 *
 * Both are set as php -d overrides on the child rather than in php.ini, so a benchmark run
 * cannot change how the rest of the instance is monitored.
 *
 * The service name is the useful part in the UI: it is what separates the two legs, so the
 * MySQL and ClickHouse traces for the same case can be put side by side instead of being
 * averaged together under one service.
 */
final class TidewaysSupport
{
    public const DEFAULT_SERVICE = 'matomo-bench';

    /**
     * Where a trace lands in the UI. `cid` is the correlation id, which is TIDEWAYS_REF -
     * chosen here rather than by the CLI, which is the whole point: the link for a case is
     * known before the case runs, so it can go in the results table without parsing anything
     * back out of the child.
     */
    private const TRACE_URL_TEMPLATE = 'https://app.tideways.io/o/%s/traces?cid=%s';

    /**
     * @return array{loaded: bool, extension: ?string, version: ?string, hasDaemon: bool, notes: string[]}
     */
    public static function describe(): array
    {
        $extension = null;
        foreach (['tideways', 'tideways_xhprof'] as $candidate) {
            if (extension_loaded($candidate)) {
                $extension = $candidate;
                break;
            }
        }

        $notes = [];

        if ($extension === null) {
            $notes[] = 'No Tideways extension is loaded in this process. The benchmark still runs'
                . ' and still reports timings; it just will not produce traces.';
        }

        if ($extension === 'tideways_xhprof') {
            $notes[] = 'The loaded extension is tideways_xhprof (the standalone profiler), not the'
                . ' Tideways APM extension. It profiles, but it does not report to a Tideways'
                . ' service, so the traces will not appear in the Tideways UI.';
        }

        $apiKey = (string) ini_get('tideways.api_key');
        $connection = (string) ini_get('tideways.connection');
        $hasDaemon = $apiKey !== '' || $connection !== '';

        if ($extension === 'tideways' && !$hasDaemon) {
            $notes[] = 'The Tideways APM extension is loaded but neither tideways.api_key nor'
                . ' tideways.connection is set, so it has nowhere to send traces.';
        }

        if ($extension !== null && !filter_var((string) ini_get('tideways.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
            $notes[] = 'tideways.enable_cli is off in this process. The benchmark turns it on for'
                . ' every child it starts, so this only affects the parent.';
        }

        return [
            'loaded' => $extension !== null,
            'extension' => $extension,
            'version' => $extension === null ? null : (phpversion($extension) ?: null),
            'hasDaemon' => $hasDaemon,
            'notes' => $notes,
        ];
    }

    /**
     * php -d overrides for the children.
     *
     * Only the two directives that decide whether a CLI run is traced at all. Anything else a
     * particular Tideways install needs goes through --tideways-ini rather than being guessed
     * at here: ini names have differed between extension generations, an unknown directive is
     * silently ignored, and a wrong guess would look exactly like a working one.
     *
     * @return string[]
     */
    public static function phpIniOptions(): array
    {
        return [
            // Without this nothing on the CLI is traced at all - the run completes normally and
            // simply produces nothing, which looks like traces that have not arrived yet.
            'tideways.enable_cli=1',
            // Trace every run. At a production sample rate most of a short benchmark is not
            // traced, and the slowest case is as likely as any other to be the one that missed.
            'tideways.sample_rate=100',
        ];
    }

    /**
     * Environment for the children. TIDEWAYS_SERVICE is read by the extension at startup, so
     * it can label a process without touching any ini file.
     *
     * @return array<string, string>
     */
    public static function environment(
        string $service,
        Engine $engine,
        ?string $session = null,
        ?string $ref = null
    ): array {
        $env = [
            'TIDEWAYS_SERVICE' => $service . '-' . $engine->getKey(),
            'TIDEWAYS_SAMPLERATE' => '100',
        ];

        // Without a session token the extension reports MEASUREMENTS but never a callgraph
        // trace, which is the state this benchmark was in for its whole life: every run
        // produced timing data in the Tideways UI and not one trace, and no ini setting fixes
        // it. Neither tideways.sample_rate=100, nor tideways.trace_sample_rate=100, nor
        // tideways.monitor=full produces a trace on their own - measured, all three.
        //
        // These are the two variables `tideways run` injects, and setting them directly rather
        // than wrapping the child is what keeps this usable here: the wrapper prints its own
        // summary onto the child's stdout, which is the stream the benchmark parses the
        // archiving result out of.
        if ($session !== null && $session !== '' && $ref !== null && $ref !== '') {
            $env['TIDEWAYS_SESSION'] = $session;
            $env['TIDEWAYS_REF'] = $ref;
        }

        return $env;
    }

    /**
     * A profiling session token, obtained once per benchmark run from the `tideways` CLI.
     *
     * The token is signed and carries its own issue time, so it cannot be constructed here and
     * it does not last forever. One is taken at the start of a run and reused for every child;
     * a suite long enough to outlive it loses traces for its later cases and keeps its timings,
     * which is the right way round. Returns null when the CLI is absent or refuses, and the
     * benchmark then runs exactly as it did before.
     */
    public static function captureSession(string $project): ?string
    {
        if ($project === '') {
            return null;
        }

        $process = new Process([
            'tideways',
            'run',
            '-o',
            $project,
            PHP_BINARY,
            '-r',
            'echo getenv("TIDEWAYS_SESSION");',
        ]);
        $process->setTimeout(60.0);

        try {
            $process->run();
        } catch (\Throwable $e) {
            return null;
        }

        // The CLI prints its own status lines after the wrapped program's output, so take the
        // first line that looks like a token rather than the whole of stdout.
        foreach (preg_split('/\R/', $process->getOutput()) ?: [] as $line) {
            $line = trim($line);
            if ($line !== '' && strpos($line, 'method=') === 0) {
                return $line;
            }
        }

        return null;
    }

    /**
     * A correlation id for one case on one engine. Every process the case starts inherits it,
     * so the warmup, the timed iterations and any CliMulti grandchildren all collect under a
     * single link.
     */
    public static function makeRef(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return implode('-', [
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6)),
        ]);
    }

    public static function traceUrl(string $project, string $ref): string
    {
        if ($project === '' || $ref === '') {
            return '';
        }

        return sprintf(self::TRACE_URL_TEMPLATE, $project, rawurlencode($ref));
    }

    /**
     * @param string[] $extra additional "name=value" ini overrides from --tideways-ini
     * @return string[]
     */
    public static function phpIniOptionsWith(array $extra): array
    {
        return array_values(array_unique(array_merge(self::phpIniOptions(), array_filter(
            array_map('trim', $extra),
            static fn(string $option): bool => $option !== ''
        ))));
    }
}
