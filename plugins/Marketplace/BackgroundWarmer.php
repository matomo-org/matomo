<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Matomo\Cache\Lazy;
use Piwik\CliMulti;
use Piwik\CliMulti\CliPhp;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Log\LoggerInterface;
use Piwik\Option;
use Piwik\Scheduler\Scheduler;

/**
 * Refreshes the Marketplace overview lists from a detached `marketplace:warm-cache` process, so
 * that neither a visitor nor a scheduler run waits on the Marketplace.
 *
 * Every refresh this class spawns leaves the request that asked for it, and a periodic one waits a
 * random delay first. Scheduled work runs whenever the installation's cron or traffic drives the scheduler,
 * which for most of them is within minutes of the hour, so requests made from inside it arrive at
 * the Marketplace from every installation at once.
 */
class BackgroundWarmer
{
    /**
     * How long an immediate refresh holds off the next one. A refresh that dies leaves nothing
     * behind to say so, so this is also how long the lists can stay stale before a visit retries.
     */
    private const SPAWN_HOLD_SECONDS = 300;

    private const SPAWN_CACHE_ID = 'marketplace.warm.spawnedAt';

    private const CANNOT_SPAWN_CACHE_ID = 'marketplace.warm.cannotSpawnAt';

    /**
     * Until the installer saves the completed config, Matomo keeps every cache in memory
     * (config/global.php), so a refresh that started sooner would warm nothing. The save follows
     * the spawn within milliseconds, and this is a wide margin over that.
     */
    private const INSTALLATION_DELAY_SECONDS = 10;

    /**
     * Short of the hour by a spawn hold, so that a periodic refresh that never started is claimed
     * by the next hourly check rather than the one after, which would let the lists expire first.
     */
    private const PERIODIC_MAX_DELAY_SECONDS = 3299;

    /**
     * A spawned run records itself as PHP boots, well within this of the moment it was due.
     */
    private const START_GRACE_SECONDS = 60;

    private const UPDATE_MAX_DELAY_SECONDS = 119;

    /**
     * Options rather than cache entries, so the daily cache flush does not take with it what the
     * next check needs to tell whether a delayed refresh ran.
     */
    private const DELAYED_PENDING_OPTION = 'Marketplace.warm.delayedPendingUntil';

    private const LAST_RUN_OPTION = 'Marketplace.warm.lastRunAt';

    private Lazy $cache;

    private CliPhp $cliPhp;

    private Scheduler $scheduler;

    private LoggerInterface $logger;

    private ?bool $canSpawn = null;

    private ?string $phpBinary = null;

    public function __construct(
        Lazy $cache,
        CliPhp $cliPhp,
        Scheduler $scheduler,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->cliPhp = $cliPhp;
        $this->scheduler = $scheduler;
        $this->logger = $logger;
    }

    /**
     * Whether the current request is someone using Matomo, as opposed to a console command or a
     * scheduler run. A tracker-triggered scheduler run is a web request too, and it is aligned to
     * the hour like any other.
     */
    public function isServingVisit(): bool
    {
        return !Common::isPhpCliMode() && !$this->scheduler->isRunningTask();
    }

    /**
     * Spawns a refresh that starts at once, for a visit that found the lists stale.
     *
     * Returns false only when this installation cannot spawn processes, leaving the caller to
     * refresh in the request instead. A refresh spawned recently counts as done.
     */
    public function refreshNow(int $ifOlderThanSeconds): bool
    {
        $now = Date::getNowTimestamp();
        $spawnedAt = $this->cache->fetch(self::SPAWN_CACHE_ID);

        // checked before canSpawn(), which runs `ps`, `uname` and `php`: the hold is only ever saved
        // by an installation that could spawn
        if (false !== $spawnedAt && $now - (int) $spawnedAt < self::SPAWN_HOLD_SECONDS) {
            return true;
        }

        // remembered across visits as well, since the checks are all a visit gets where nothing can
        // be spawned. Only visits get here, so it never holds off a console run that could spawn
        $cannotSpawnAt = $this->cache->fetch(self::CANNOT_SPAWN_CACHE_ID);

        if (false !== $cannotSpawnAt && $now - (int) $cannotSpawnAt < self::SPAWN_HOLD_SECONDS) {
            return false;
        }

        if (!$this->canSpawn()) {
            $this->cache->save(self::CANNOT_SPAWN_CACHE_ID, $now, self::SPAWN_HOLD_SECONDS);

            return false;
        }

        $this->cache->save(self::SPAWN_CACHE_ID, $now, self::SPAWN_HOLD_SECONDS);
        $this->spawn(0, $ifOlderThanSeconds);

        return true;
    }

    /**
     * Runs a flush of the cache the holds are kept in, then restores the holds that were still
     * running, so the flush does not let the next visit spawn another refresh at once.
     */
    public function keepSpawnHoldsThrough(callable $flush): void
    {
        $now = Date::getNowTimestamp();
        $held = [];

        foreach ([self::SPAWN_CACHE_ID, self::CANNOT_SPAWN_CACHE_ID] as $id) {
            $at = $this->cache->fetch($id);

            if (false !== $at && $now - (int) $at < self::SPAWN_HOLD_SECONDS) {
                $held[$id] = (int) $at;
            }
        }

        $flush();

        foreach ($held as $id => $at) {
            $this->cache->save($id, $at, self::SPAWN_HOLD_SECONDS - ($now - $at));
        }
    }

    /**
     * Spawns a refresh for an installation that is just being completed. Nothing is held off: the
     * installer's cache only lives as long as its request.
     */
    public function refreshAfterInstallation(): void
    {
        if (!$this->canSpawn()) {
            return;
        }

        $this->spawn(self::INSTALLATION_DELAY_SECONDS, 0);
    }

    /**
     * Spawns a refresh that starts at a random point within the next hour, unless one is already
     * waiting.
     *
     * Returns false only when this installation cannot spawn processes, leaving the caller to
     * refresh in the scheduler run instead: there, nothing else refreshes the lists without a visit.
     */
    public function refreshPeriodically(int $ifOlderThanSeconds): bool
    {
        return $this->spawnDelayedUnlessPending(self::PERIODIC_MAX_DELAY_SECONDS, $ifOlderThanSeconds);
    }

    /**
     * Called by every `marketplace:warm-cache` run, including one that finds the lists fresh or
     * cannot reach the Marketplace, since either still means the spawned process ran.
     */
    public function recordRun(): void
    {
        $this->saveMarker(self::LAST_RUN_OPTION, Date::getNowTimestamp());
    }

    /**
     * Whether the last delayed refresh should have started by now and never did, and if so forgets
     * it, so the next check spawns one again rather than refreshing in the scheduler every hour. A
     * scheduler run inside a unit that kills its process tree on exit, such as a systemd service or
     * a Kubernetes CronJob, takes a waiting child with it.
     */
    public function claimFailedDelayedRefresh(): bool
    {
        $pendingUntil = $this->fetchMarker(self::DELAYED_PENDING_OPTION);

        if (null === $pendingUntil || Date::getNowTimestamp() < $pendingUntil - self::SPAWN_HOLD_SECONDS + self::START_GRACE_SECONDS) {
            return false;
        }

        $lastRunAt = $this->fetchMarker(self::LAST_RUN_OPTION);

        if (null !== $lastRunAt && $lastRunAt >= $pendingUntil - self::SPAWN_HOLD_SECONDS) {
            return false;
        }

        $this->deleteMarker(self::DELAYED_PENDING_OPTION);

        return true;
    }

    /**
     * Spawns a refresh that starts within two minutes of an update, which emptied every cache.
     * Updates are often rolled out to many installations at once, hence the random delay.
     */
    public function refreshAfterUpdate(int $ifOlderThanSeconds): void
    {
        $this->spawnDelayedUnlessPending(self::UPDATE_MAX_DELAY_SECONDS, $ifOlderThanSeconds);
    }

    /**
     * Separated so a test can pin the delay.
     */
    protected function getRandomDelaySeconds(int $maxSeconds): int
    {
        return random_int(0, $maxSeconds);
    }

    /**
     * Runs the command that warms the cache. Separated so a test can read what would be run
     * without running it: the redirection in the command swallows everything a failure would say.
     */
    protected function execute(string $command): void
    {
        shell_exec($command);
    }

    /**
     * Separated, like the two below, so a unit test can keep the markers without a database.
     */
    protected function fetchMarker(string $name): ?int
    {
        $value = Option::get($name);

        return false === $value ? null : (int) $value;
    }

    protected function saveMarker(string $name, int $value): void
    {
        Option::set($name, (string) $value);
    }

    protected function deleteMarker(string $name): void
    {
        Option::delete($name);
    }

    private function spawnDelayedUnlessPending(int $maxDelaySeconds, int $ifOlderThanSeconds): bool
    {
        if (!$this->canSpawn()) {
            return false;
        }

        $now = Date::getNowTimestamp();
        $pendingUntil = $this->fetchMarker(self::DELAYED_PENDING_OPTION);

        // only one due within this caller's own window holds it off: an update must not wait out a
        // periodic refresh that is still most of an hour away, with every cache emptied
        if (null !== $pendingUntil && $now < $pendingUntil && $pendingUntil <= $now + $maxDelaySeconds + self::SPAWN_HOLD_SECONDS) {
            return true;
        }

        $delay = $this->getRandomDelaySeconds($maxDelaySeconds);

        // the margin covers the run itself, so the next hourly check does not spawn a second one
        // while the first is still fetching
        $holdFor = $delay + self::SPAWN_HOLD_SECONDS;
        $this->saveMarker(self::DELAYED_PENDING_OPTION, $now + $holdFor);

        $this->spawn($delay, $ifOlderThanSeconds);

        return true;
    }

    /**
     * Remembered because finding the binary can run `php` to read its version.
     */
    public function canSpawn(): bool
    {
        if (null === $this->canSpawn) {
            // the property, not supportsAsync(): the constructor already worked it out, and calling
            // the method would run every check again
            $this->canSpawn = true === $this->createCliMulti()->supportsAsync && '' !== $this->getPhpBinary();
        }

        return $this->canSpawn;
    }

    /**
     * Not injected: constructing CliMulti already runs the checks canSpawn() makes, which a visit
     * held off by a recent refresh has no need of.
     */
    protected function createCliMulti(): CliMulti
    {
        return StaticContainer::get(CliMulti::class);
    }

    private function getPhpBinary(): string
    {
        if (null === $this->phpBinary) {
            $this->phpBinary = (string) $this->cliPhp->findPhpBinary();
        }

        return $this->phpBinary;
    }

    private function spawn(int $delaySeconds, int $ifOlderThanSeconds): void
    {
        $command = $this->buildWarmCommand($delaySeconds, $ifOlderThanSeconds);

        // the only trace that this ran: the command discards both streams, so a child that dies
        // says nothing anywhere. CliMulti::executeAsyncCli() logs its command the same way.
        $this->logger->debug('Warming the Marketplace cache in the background: {command}', [
            'command' => $command,
        ]);

        $this->execute($command);
    }

    private function buildWarmCommand(int $delaySeconds, int $ifOlderThanSeconds): string
    {
        // without this the child resolves no hostname and falls back to config/config.ini.php, so
        // on a per-hostname-config install it would warm a different instance than this one. Same
        // guard as core/Updater/Migration/Plugin/Activate.php: the comparison means the hostname
        // already resolved to a config file that exists.
        $domain = Config::getLocalConfigPath() === Config::getDefaultLocalConfigPath()
            ? ''
            : Config::getHostname();
        $domainArg = !empty($domain) ? '--matomo-domain=' . escapeshellarg($domain) . ' ' : '';

        $warm = sprintf(
            '%s %s %smarketplace:warm-cache --if-older-than=%d',
            // not escaped: findPhpBinary() returns the binary with its own arguments attached
            // ("/usr/bin/php8.4 -q", or PHP_BINARY . " --php" for hhvm), so quoting it would name
            // a file that does not exist. CliMulti::buildCommand() interpolates it the same way.
            $this->getPhpBinary(),
            escapeshellarg(PIWIK_INCLUDE_PATH . '/console'),
            $domainArg,
            $ifOlderThanSeconds
        );

        if ($delaySeconds > 0) {
            // the shell waits rather than PHP, so nothing heavier than a sleep process is held
            $warm = sprintf('sleep %d && %s', $delaySeconds, $warm);
        }

        return sprintf('(%s) > /dev/null 2>&1 &', $warm);
    }
}
