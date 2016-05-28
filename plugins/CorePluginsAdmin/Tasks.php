<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var UpdateCommunication
     */
    private $updateCommunication;

    public function __construct(UpdateCommunication $updateCommunication)
    {
        $this->updateCommunication = $updateCommunication;
    }

    public function schedule()
    {
        $this->daily('clearAllCacheEntries', null, self::LOWEST_PRIORITY);

        if (CorePluginsAdmin::isMarketplaceEnabled()) {
            $this->daily('sendNotificationIfUpdatesAvailable', null, self::LOWEST_PRIORITY);
        }
    }

    public function clearAllCacheEntries()
    {
        $marketplace = new MarketplaceApiClient();
        $marketplace->clearAllCacheEntries();
    }

    public function sendNotificationIfUpdatesAvailable()
    {
        if ($this->updateCommunication->isEnabled()) {
            $this->updateCommunication->sendNotificationIfUpdatesAvailable();
        }
    }

}