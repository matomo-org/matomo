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
 * Brings the warming that {@link Tasks::warmCacheEntries()} does on the hour forward to the end of
 * an installation, where the cache is certainly cold and the Marketplace is the next thing a new
 * administrator tends to open.
 *
 * A newly registered task is only entered into the timetable on the scheduler's first run, never
 * executed by it (see Timetable::shouldExecuteTask()), so a fresh installation leaves the overview
 * lists cold for at least an hour - and longer on an instance that has no traffic yet to drive the
 * scheduler at all. Whoever opens the Marketplace in that window pays for every request the page
 * needs, which is the slowest it ever is.
 *
 * Updates do not come through here: {@link Marketplace::warmCacheAfterUpdate()} only marks the task
 * due, because building this class from inside the updater disturbs what other plugins report.
 */
class CacheWarmer
{
    private Api\Client $api;

    private Scheduler $scheduler;

    private Environment $environment;

    private CliMulti $cliMulti;

    private CliPhp $cliPhp;

    private LoggerInterface $logger;

    public function __construct(
        Api\Client $api,
        Scheduler $scheduler,
        Environment $environment,
        CliMulti $cliMulti,
        CliPhp $cliPhp,
        LoggerInterface $logger
    ) {
        $this->api = $api;
        $this->scheduler = $scheduler;
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
            // warming is an optimisation, so nothing here may fail the installation that triggered
            // it - including an error, which a bad php binary path would raise
            $this->logger->warning('Could not warm the Marketplace cache ahead of time: {message}', [
                'message' => $e->getMessage(),
            ]);

            try {
                // whatever failed above reached the database or the cache backend, while marking
                // the task due is one option write, so it is worth its own attempt - otherwise the
                // cheap half of this class is lost to a failure in the half that probes. Marking
                // due twice is harmless: the timetable holds one time per task.
                $this->markTaskDue();
            } catch (Throwable $ignored) {
                // nothing cheaper is left, so the hourly task warms on its own schedule
            }
        }
    }

    /**
     * Returns the binary to spawn the warming process with, or null when this installation should
     * mark the task due instead.
     */
    private function findPhpBinaryForBackgroundRun(): ?string
    {
        // defensive rather than reachable: core has no command-line installer, so today every
        // caller is a web request. A CLI caller must never spawn - nobody would be waiting on the
        // page it warms, and across a fleet every instance would reach this line at once - so it
        // falls back to marking the task due and warming on its own next scheduler run.
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

        // marked due as well as spawned. The command discards its output, so shell_exec returns
        // null whether the child booted or died, and a child that dies would otherwise leave the
        // cache cold with nothing anywhere to say so. The next scheduler run then warms it; the
        // price is one redundant warm on this path, which only a person can trigger.
        $this->markTaskDue();

        $command = $this->buildWarmCommand($phpBinary);

        // the only trace that this ran: the command discards both streams, so a child that dies
        // says nothing anywhere. CliMulti::executeAsyncCli() logs its command the same way.
        $this->logger->debug('Warming the Marketplace cache in the background: {command}', [
            'command' => $command,
        ]);

        $this->execute($command);
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
        // on a per-hostname-config install it would warm a different instance than the one being
        // installed. Same guard as core/Updater/Migration/Plugin/Activate.php: the comparison
        // means the hostname already resolved to a config file that exists.
        $domain = Config::getLocalConfigPath() === Config::getDefaultLocalConfigPath()
            ? ''
            : Config::getHostname();
        $domainArg = !empty($domain) ? '--matomo-domain=' . escapeshellarg($domain) . ' ' : '';

        // no --force: a named task runs through Scheduler::runTaskNow(), which does not consult
        // the timetable, so the hourly schedule is left exactly as it was
        return sprintf(
            '%s %s %score:run-scheduled-tasks %s > /dev/null 2>&1 &',
            // not escaped: findPhpBinary() returns the binary with its own arguments attached
            // ("/usr/bin/php8.4 -q", or PHP_BINARY . " --php" for hhvm), so quoting it would name
            // a file that does not exist. CliMulti::buildCommand() interpolates it the same way.
            $phpBinary,
            escapeshellarg(PIWIK_INCLUDE_PATH . '/console'),
            $domainArg,
            escapeshellarg($this->getWarmCacheTask()->getName())
        );
    }

    private function getWarmCacheTask(): Task
    {
        return Tasks::getWarmCacheEntriesTask();
    }
}
