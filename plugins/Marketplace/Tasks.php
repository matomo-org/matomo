<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace;

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

    public function __construct(UpdateCommunication $updateCommunication, Api\Client $api)
    {
        $this->updateCommunication = $updateCommunication;
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
        if ($this->updateCommunication->isEnabled()) {
            $this->updateCommunication->sendNotificationIfUpdatesAvailable();
        }
    }

}