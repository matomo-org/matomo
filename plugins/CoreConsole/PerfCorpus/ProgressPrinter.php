<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * A progress line that behaves in both places this command actually runs.
 *
 * On a terminal it repaints one line in place. Redirected to a log file - which is how a
 * multi-hour run is really started, under nohup - carriage returns would produce a single
 * unreadable megabyte-long line, so it prints a timestamped line at a much lower rate instead.
 *
 * Either way the authoritative progress is the perfcorpus_chunk table, which perfcorpus:status
 * reads; this is only the convenience view for whoever is watching.
 */
class ProgressPrinter
{
    private const TERMINAL_INTERVAL_SECONDS = 0.25;
    private const LOG_INTERVAL_SECONDS = 15.0;

    private OutputInterface $output;
    private bool $interactive;
    private float $lastPaint = 0.0;
    private int $lastWidth = 0;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->interactive = $output->isDecorated();
    }

    public function update(string $line, bool $force = false): void
    {
        $now = microtime(true);
        $interval = $this->interactive ? self::TERMINAL_INTERVAL_SECONDS : self::LOG_INTERVAL_SECONDS;

        if (!$force && $now - $this->lastPaint < $interval) {
            return;
        }

        $this->lastPaint = $now;

        if (!$this->interactive) {
            $this->output->writeln(date('[H:i:s] ') . $line);

            return;
        }

        // Pad to the previous width so a shorter line cannot leave debris behind.
        $padding = max(0, $this->lastWidth - mb_strlen($line));
        $this->lastWidth = mb_strlen($line);

        $this->output->write("\r" . $line . str_repeat(' ', $padding));
    }

    /**
     * Ends the progress line and leaves the final state on screen.
     */
    public function finish(string $line): void
    {
        if ($this->interactive) {
            $padding = max(0, $this->lastWidth - mb_strlen($line));
            $this->output->write("\r" . $line . str_repeat(' ', $padding));
            $this->output->writeln('');
        } else {
            $this->output->writeln(date('[H:i:s] ') . $line);
        }

        $this->lastWidth = 0;
        $this->lastPaint = 0.0;
    }
}
