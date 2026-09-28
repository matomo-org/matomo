<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Log\LoggerInterface;

class Tasks extends \Piwik\Plugin\Tasks
{
    private UpdateCommunication $updateCommunication;

    private Api\Client $api;

    private BackgroundWarmer $backgroundWarmer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UpdateCommunication $updateCommunication,
        Api\Client $api,
        BackgroundWarmer $backgroundWarmer,
        LoggerInterface $logger
    ) {
        $this->updateCommunication = $updateCommunication;
        $this->api = $api;
        $this->backgroundWarmer = $backgroundWarmer;
        $this->logger = $logger;
    }

    public function schedule()
    {
        $this->daily('clearAllCacheEntries', null, self::LOWEST_PRIORITY);
        // hourly only to check, and it requests nothing itself unless a background refill evidently
        // never ran or cannot be spawned: most installations' schedulers run within minutes of the
        // hour, so refilling from here sent every refresh to the Marketplace at the same time
        $this->hourly('warmCacheEntries', null, self::LOWEST_PRIORITY);
        $this->daily('sendNotificationIfUpdatesAvailable', null, self::LOWEST_PRIORITY);
    }

    public function clearAllCacheEntries()
    {
        $this->api->clearCacheEntriesExceptOverviewLists();
    }

    /**
     * Schedules a refill of the Marketplace overview lists at a random point in the next hour,
     * once they are missing or older than {@link Api\Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS}.
     * That bounds how stale a visit can find them, and visits refresh them sooner. Where the last
     * such refill evidently never ran, or this installation cannot spawn one, they are refilled in
     * this run instead.
     */
    public function warmCacheEntries(): void
    {
        try {
            $age = $this->api->getOverviewListsAge();

            if (null !== $age && $age < Api\Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS) {
                return;
            }

            if ($this->backgroundWarmer->claimFailedDelayedRefresh()) {
                $this->logger->info('The Marketplace lists were not refreshed in the background, refreshing them in the scheduled run');
                $this->api->tryRefreshOverviewListCaches();

                return;
            }

            if (!$this->backgroundWarmer->refreshPeriodically(Api\Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS)) {
                $this->api->tryRefreshOverviewListCaches();
            }
        } catch (\Throwable $e) {
            // must not fail the scheduled run, and Scheduler::executeTask() only catches an \Exception
            $this->logger->warning('Could not warm the Marketplace cache: {message}', [
                'message' => $e->getMessage(),
                'ignoreInScreenWriter' => true,
            ]);
        }
    }

    public function sendNotificationIfUpdatesAvailable()
    {
        if ($this->updateCommunication->isEnabled()) {
            $this->updateCommunication->sendNotificationIfUpdatesAvailable();
        }
    }
}
