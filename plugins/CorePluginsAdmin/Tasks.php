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
        $updateCommunication = new UpdateCommunication();
        if ($updateCommunication->isEnabled()) {
            $updateCommunication->sendNotificationIfUpdatesAvailable();
        }
    }

}