<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use Piwik\CliMulti\CliPhp;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Runs a Matomo console command in a child process and times it.
 *
 * Everything this harness measures is measured by running Matomo's own CLI - core:archive,
 * core:invalidate-report-data, climulti:request - rather than by calling the archiver or the
 * API dispatcher from inside the harness. Two reasons. The obvious one is that a benchmark of
 * a reimplementation measures the reimplementation. The other is the engine switch: the leg is
 * selected by an environment variable, and an environment variable only takes effect at
 * process start, so each leg has to be a process anyway.
 *
 * A child costs roughly a second of bootstrap. Both legs pay it identically, and the
 * calibration case measures it so it can be subtracted - see Reporter.
 */
final class ConsoleProcess
{
    private string $consolePath;
    private string $phpBinary;
    private ?float $timeout;

    /** @var string[] arguments the resolved binary needs before anything else */
    private array $phpBinaryArgs;

    /** @var string[] php -d overrides, applied to the direct child */
    private array $phpIniOptions;

    /**
     * @param string[] $phpIniOptions e.g. ['tideways.enable_cli=1']
     */
    public function __construct(
        string $consolePath,
        ?string $phpBinary = null,
        ?float $timeout = null,
        array $phpIniOptions = []
    ) {
        $this->consolePath = $consolePath;
        $this->timeout = $timeout;
        $this->phpIniOptions = $phpIniOptions;

        if ($phpBinary !== null && $phpBinary !== '') {
            $this->phpBinary = $phpBinary;
            $this->phpBinaryArgs = [];
        } else {
            [$this->phpBinary, $this->phpBinaryArgs] = self::resolvePhpBinary();
        }
    }

    /**
     * @return array{0: string, 1: string[]}
     */
    private static function resolvePhpBinary(): array
    {
        // PHP_BINARY is this process's own interpreter, which is the one that should run the
        // children.
        //
        // CliPhp::findPhpBinary() is deliberately NOT used as the first choice: it returns a
        // shell command FRAGMENT, not a path - it always appends ' -q', a CGI-only flag, because
        // CliMulti builds a command string and runs it through a shell. Symfony Process takes an
        // array and execs argv[0] literally, so that fragment becomes a hunt for an executable
        // named "/usr/bin/php8.1 -q" and every child exits 127.
        if (@is_executable(PHP_BINARY)) {
            return [PHP_BINARY, []];
        }

        $fallback = trim((string) (new CliPhp())->findPhpBinary());
        $tokens = $fallback === '' ? [] : (preg_split('~\s+~', $fallback) ?: []);
        $binary = (string) array_shift($tokens);
        $tokens = array_values(array_filter($tokens, static fn(string $token): bool => $token !== '-q'));

        return [$binary === '' ? 'php' : $binary, $tokens];
    }

    /**
     * @param string[] $arguments console command name followed by its options
     * @param array<string, string> $env merged over the inherited environment
     * @param callable|null $onOutput receives output chunks as they arrive
     * @return array{command: string, exitCode: int, output: string, wallMs: float, timedOut: bool}
     */
    public function run(array $arguments, array $env = [], ?callable $onOutput = null): array
    {
        $command = array_merge([$this->phpBinary], $this->phpBinaryArgs);
        foreach ($this->phpIniOptions as $iniOption) {
            $command[] = '-d';
            $command[] = $iniOption;
        }
        $command[] = $this->consolePath;
        $command = array_merge($command, $arguments);

        $process = new Process($command, dirname($this->consolePath), $env);
        $process->setTimeout($this->timeout);

        $output = '';
        $collect = static function ($type, $chunk) use (&$output, $onOutput): void {
            $output .= $chunk;
            if ($onOutput !== null) {
                $onOutput((string) $chunk);
            }
        };

        $timedOut = false;
        $startedAt = hrtime(true);
        try {
            $process->run($collect);
        } catch (ProcessTimedOutException $e) {
            $timedOut = true;
        }
        $wallMs = (hrtime(true) - $startedAt) / 1e6;

        return [
            // Rendered into the run log and the JSON export. Safe to print: nothing this harness
            // puts on a command line is a credential - connection details reach the child through
            // the config file and the environment, never as an argument.
            'command' => $process->getCommandLine(),
            'exitCode' => $timedOut ? 124 : (int) $process->getExitCode(),
            'output' => $output,
            'wallMs' => $wallMs,
            'timedOut' => $timedOut,
        ];
    }

    /**
     * The same -d overrides, formatted for core:archive's --php-cli-options.
     *
     * core:archive does not do the archiving itself; it hands each archive to CliMulti, which
     * spawns a further PHP process. Those grandchildren inherit the environment (so they get
     * the engine selection) but NOT this process's -d flags, so the ini overrides have to be
     * forwarded explicitly or the archiving work itself goes untraced.
     */
    public function getPhpCliOptionsString(): string
    {
        $parts = [];
        foreach ($this->phpIniOptions as $iniOption) {
            $parts[] = '-d ' . $iniOption;
        }

        return implode(' ', $parts);
    }

    public function getPhpBinary(): string
    {
        return $this->phpBinary;
    }

    public function getConsolePath(): string
    {
        return $this->consolePath;
    }
}
