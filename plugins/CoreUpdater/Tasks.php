<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        $this->daily('sendNotificationIfUpdateAvailable', null, self::LOWEST_PRIORITY);
    }

    public function sendNotificationIfUpdateAvailable()
    {
        $coreUpdateCommunication = new UpdateCommunication();
        if ($coreUpdateCommunication->isEnabled()) {
            $coreUpdateCommunication->sendNotificationIfUpdateAvailable();
        }
    }
}