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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UpdateCommunication $updateCommunication,
        Api\Client $api,
        LoggerInterface $logger
    ) {
        $this->updateCommunication = $updateCommunication;
        $this->api = $api;
        $this->logger = $logger;
    }

    public function schedule()
    {
        $this->daily('clearAllCacheEntries', null, self::LOWEST_PRIORITY);
        // more often than the lists expire, so a visitor almost never pays for the requests the
        // overview page needs. This costs the Marketplace three requests an hour per installation
        // that has it enabled: the responses carry a long max-age, but every Marketplace call is a
        // POST (see Api\Service::download()) and is answered as a CloudFront miss, so each refill
        // reaches the origin rather than a cache.
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
        try {
            $this->api->refreshOverviewListCaches();
        } catch (Exception $e) {
            // the Marketplace being unreachable must not fail the scheduled run
            $this->logger->info('Could not warm the Marketplace cache: {message}', [
                'message' => $e->getMessage(),
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
