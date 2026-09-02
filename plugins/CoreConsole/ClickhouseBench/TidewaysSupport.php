<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

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
    public static function environment(string $service, Engine $engine): array
    {
        return [
            'TIDEWAYS_SERVICE' => $service . '-' . $engine->getKey(),
            'TIDEWAYS_SAMPLERATE' => '100',
        ];
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
