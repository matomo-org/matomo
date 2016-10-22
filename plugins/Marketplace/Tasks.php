<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var Api\Client
     */
    private $api;

    public function __construct(Api\Client $api)
    {
        $this->api = $api;
    }

    public function schedule()
    {
        $this->daily('clearAllCacheEntries', null, self::LOWEST_PRIORITY);
        $this->daily('sendNotificationIfUpdatesAvailable', null, self::LOWEST_PRIORITY);
    }

    public function clearAllCacheEntries()
    {
        $this->api->clearAllCacheEntries();
    }

    public function sendNotificationIfUpdatesAvailable()
    {
        $updateCommunication = new UpdateCommunication();
        if ($updateCommunication->isEnabled()) {
            $updateCommunication->sendNotificationIfUpdatesAvailable();
        }
    }

}