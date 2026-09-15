<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\CliMulti;
use Piwik\CliMulti\CliPhp;
use Piwik\Common;
use Piwik\Log\LoggerInterface;
use Piwik\Scheduler\Scheduler;
use Piwik\SettingsPiwik;
use Throwable;

/**
 * Brings the warming that {@link Tasks::warmCacheEntries()} does on the hour forward to a moment
 * the cache is known to be cold and the Marketplace is about to be opened.
 *
 * A newly registered task is only entered into the timetable on the scheduler's first run, never
 * executed by it (see Timetable::shouldExecuteTask()), so after an installation or an update the
 * overview lists stay cold for at least an hour - and longer on an instance whose scheduler runs
 * only from tracker requests. Whoever opens the Marketplace in that window pays for every request
 * the page needs, which is the slowest it ever is.
 */
class CacheWarmer
{
    private Api\Client $api;

    private Scheduler $scheduler;

    private Tasks $tasks;

    private Environment $environment;

    private CliMulti $cliMulti;

    private LoggerInterface $logger;

    public function __construct(
        Api\Client $api,
        Scheduler $scheduler,
        Tasks $tasks,
        Environment $environment,
        CliMulti $cliMulti,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->scheduler = $scheduler;
        $this->tasks = $tasks;
        $this->environment = $environment;
        $this->cliMulti = $cliMulti;
        $this->logger = $logger;
    }

    /**
     * Gets the overview lists warmed as soon as this installation can manage it, either in a
     * background process or by marking the hourly task due for the next scheduler run.
     */
    public function warmSoon(): void
    {
        try {
            if (!SettingsPiwik::isInternetEnabled() || $this->api->hasWarmOverviewLists()) {
                return;
            }

            $phpBinary = $this->findPhpBinaryForBackgroundRun();

            if (null === $phpBinary) {
                $this->scheduler->rescheduleTaskAndRunNow($this->tasks->getWarmCacheEntriesTask());
                return;
            }

            $this->runInBackground($phpBinary);
        } catch (Throwable $e) {
            // warming is an optimisation, so nothing here may fail the installation or update that
            // triggered it - including an error, which a bad php binary path would raise
            $this->logger->warning('Could not warm the Marketplace cache ahead of time: {message}', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Returns the binary to spawn the warming process with, or null when this installation should
     * mark the task due instead.
     */
    private function findPhpBinaryForBackgroundRun(): ?string
    {
        // an update run from the command line is usually an unattended deployment: nobody is
        // waiting on a page, and across a fleet every instance would reach this line at once.
        // Marking the task due spreads that over each instance's own next scheduler run, and costs
        // the Marketplace nothing it was not already going to be asked for.
        if (Common::isPhpCliMode() || !$this->cliMulti->supportsAsync()) {
            return null;
        }

        $binary = (new CliPhp())->findPhpBinary();

        return empty($binary) ? null : $binary;
    }

    private function runInBackground(string $phpBinary): void
    {
        // the web server's PHP version is part of every Marketplace cache key and only a web
        // request can record it, so it is stored before handing over to a process that would
        // otherwise warm entries under the CLI version, which no page reads
        $this->environment->getWebPhpVersion();

        shell_exec($this->buildWarmCommand($phpBinary));
    }

    /**
     * The command the background process runs. Kept apart from running it so a test can read it:
     * the redirection below swallows anything a malformed command would have said.
     */
    protected function buildWarmCommand(string $phpBinary): string
    {
        // no --force: a named task runs through Scheduler::runTaskNow(), which does not consult
        // the timetable, so the hourly schedule is left exactly as it was
        return sprintf(
            '%s %s/console core:run-scheduled-tasks %s > /dev/null 2>&1 &',
            $phpBinary,
            PIWIK_INCLUDE_PATH,
            escapeshellarg($this->tasks->getWarmCacheEntriesTask()->getName())
        );
    }
}
