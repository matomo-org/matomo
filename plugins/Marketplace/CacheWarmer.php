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
use Piwik\Config;
use Piwik\Log\LoggerInterface;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;
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

    private CliPhp $cliPhp;

    private LoggerInterface $logger;

    public function __construct(
        Api\Client $api,
        Scheduler $scheduler,
        Tasks $tasks,
        Environment $environment,
        CliMulti $cliMulti,
        CliPhp $cliPhp,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->scheduler = $scheduler;
        $this->tasks = $tasks;
        $this->environment = $environment;
        $this->cliMulti = $cliMulti;
        $this->cliPhp = $cliPhp;
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
                $this->markTaskDue();
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

        $binary = $this->cliPhp->findPhpBinary();

        return empty($binary) ? null : $binary;
    }

    private function markTaskDue(): void
    {
        $this->scheduler->rescheduleTaskAndRunNow($this->getWarmCacheTask());
    }

    private function runInBackground(string $phpBinary): void
    {
        // belt and braces: every route to this point has already fetched through Api\Client, which
        // records the version before it looks the entry up. Recording it here too keeps the
        // guarantee local, because the version is part of every cache key and the process about to
        // be spawned runs under the CLI one, which no page reads.
        $this->environment->getWebPhpVersion();

        // null means the command could not be executed at all - shell_exec disabled, or a binary
        // that will not run. It cannot tell us whether a started child then booted successfully,
        // so this recovers only the failure it can actually see.
        if (null === $this->execute($this->buildWarmCommand($phpBinary))) {
            $this->markTaskDue();
        }
    }

    /**
     * Runs the command that warms the cache. Separated so a test can read what would be run
     * without running it: the redirection in the command swallows everything a failure would say.
     */
    protected function execute(string $command): ?string
    {
        return shell_exec($command);
    }

    private function buildWarmCommand(string $phpBinary): string
    {
        // without this the child resolves no hostname and falls back to config/config.ini.php, so
        // on a per-hostname-config install it would warm a different instance than the one that
        // just updated. Same guard as core/Updater/Migration/Plugin/Activate.php: the comparison
        // means the hostname already resolved to a config file that exists.
        $domain = Config::getLocalConfigPath() === Config::getDefaultLocalConfigPath()
            ? ''
            : Config::getHostname();
        $domainArg = !empty($domain) ? '--matomo-domain=' . escapeshellarg($domain) . ' ' : '';

        // no --force: a named task runs through Scheduler::runTaskNow(), which does not consult
        // the timetable, so the hourly schedule is left exactly as it was
        return sprintf(
            '%s %s %score:run-scheduled-tasks %s > /dev/null 2>&1 &',
            escapeshellarg($phpBinary),
            escapeshellarg(PIWIK_INCLUDE_PATH . '/console'),
            $domainArg,
            escapeshellarg($this->getWarmCacheTask()->getName())
        );
    }

    private function getWarmCacheTask(): Task
    {
        return $this->tasks->getWarmCacheEntriesTask();
    }
}
