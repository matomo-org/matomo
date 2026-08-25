<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Exception;
use Piwik\Log\LoggerInterface;

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
     * @var Plugins
     */
    private $plugins;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UpdateCommunication $updateCommunication,
        Api\Client $api,
        Plugins $plugins,
        LoggerInterface $logger
    ) {
        $this->updateCommunication = $updateCommunication;
        $this->api = $api;
        $this->plugins = $plugins;
        $this->logger = $logger;
    }

    public function schedule()
    {
        $this->daily('clearAllCacheEntries', null, self::LOWEST_PRIORITY);
        // more often than the lists expire, so a visitor almost never pays for the requests the
        // overview page needs. The Marketplace serves them with an eight day max-age from a CDN, so
        // refetching hourly costs it cache hits rather than origin work.
        $this->hourly('warmCacheEntries', null, self::LOWEST_PRIORITY);
        $this->daily('sendNotificationIfUpdatesAvailable', null, self::LOWEST_PRIORITY);
    }

    public function clearAllCacheEntries()
    {
        $this->api->clearAllCacheEntries();

        // flushing on its own leaves whoever opens the Marketplace next to pay for every request
        // the page needs, which is the slowest it ever is, so refill it straight away rather than
        // waiting for the hourly task to come round
        $this->warmCacheEntries();
    }

    public function warmCacheEntries(): void
    {
        // the same queries, in the same shape, that Controller::searchPlugins() issues for the
        // default view. A different sort or purchase type is a different cache entry.
        $warmers = [
            function () {
                $this->plugins->getAllPlugins();
            },
            function () {
                $this->plugins->getAllThemes();
            },
            function () {
                // the premium filter is a separate cache entry, and the flush emptied it too
                $this->plugins->getAllPaidPlugins();
            },
        ];

        foreach ($warmers as $warmer) {
            try {
                $warmer();
            } catch (Exception $e) {
                // the Marketplace being unreachable must not fail the scheduled run
                $this->logger->info('Could not warm the Marketplace cache: {message}', [
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    public function sendNotificationIfUpdatesAvailable()
    {
        if ($this->updateCommunication->isEnabled()) {
            $this->updateCommunication->sendNotificationIfUpdatesAvailable();
        }
    }
}
