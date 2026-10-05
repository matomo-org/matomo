<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var UpdateCommunication
     */
    private $updateCommunication;

    /**
     * @var Api\Client
     */
    private $api;

    /**
     * @var BackgroundWarmer
     */
    private $backgroundWarmer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UpdateCommunication $updateCommunication,
        Api\Client $api,
        LoggerInterface $logger,
        ?BackgroundWarmer $backgroundWarmer = null
    ) {
        $this->updateCommunication = $updateCommunication;
        $this->api = $api;
        $this->logger = $logger;
        // optional and last so that a caller written for the old three-argument signature still works
        $this->backgroundWarmer = $backgroundWarmer ?? StaticContainer::get(BackgroundWarmer::class);
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
        $this->backgroundWarmer->keepSpawnHoldsThrough(function () {
            $this->api->clearCacheEntriesExceptOverviewLists();
        });
    }

    /**
     * Schedules a refill of the Marketplace overview lists at a random point in the next hour,
     * once they are missing or older than {@link Api\Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS}.
     * That bounds how stale a visit can find them, and visits refresh them sooner. Where the last
     * such refill evidently never ran, or this installation cannot spawn one, they are refilled in
     * this run instead, and without background processes already from
     * {@link Api\Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS}.
     */
    public function warmCacheEntries(): void
    {
        try {
            $age = $this->api->getOverviewListsAge();

            if (null !== $age && $age < Api\Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS) {
                // without background processes a visit would refresh these in the request, so this
                // run keeps them warm instead, as it did before the refreshes were spread out
                if ($age >= Api\Client::PLUGIN_LIST_REFRESH_AFTER_SECONDS && !$this->backgroundWarmer->canSpawn()) {
                    $this->api->tryRefreshOverviewListCaches(LogLevel::INFO);
                }

                return;
            }

            if ($this->backgroundWarmer->claimFailedDelayedRefresh()) {
                $this->logger->info('The Marketplace lists were not refreshed in the background, refreshing them in the scheduled run');
                $this->api->tryRefreshOverviewListCaches(LogLevel::INFO);

                return;
            }

            if (!$this->backgroundWarmer->refreshPeriodically(Api\Client::PLUGIN_LIST_PERIODIC_REFRESH_AFTER_SECONDS)) {
                $this->api->tryRefreshOverviewListCaches(LogLevel::INFO);
            }
        } catch (\Throwable $e) {
            // must not fail the scheduled run, and Scheduler::executeTask() only catches an \Exception
            $this->logger->warning('Could not warm the Marketplace cache: {message}', [
                'message' => $e->getMessage(),
                'ignoreInScreenWriter' => true,
            ]);
        }

        try {
            // the consumer is read on the dashboard, by the promotions that need to know
            // which premium products a license already covers. Those readers take it
            // cached-only so a dashboard never waits on plugins.matomo.org, which only
            // works while something keeps the entry filled.
            $this->api->refreshConsumerCache();
        } catch (\Throwable $e) {
            // `\Throwable` and not `Exception`, for the reason given above: this must not
            // fail the scheduled run, and Scheduler::executeTask() only catches an \Exception.
            $this->logger->warning('Could not warm the Marketplace consumer: {message}', [
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
