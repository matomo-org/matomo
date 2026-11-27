<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Notification as MatomoNotification;
use Piwik\Piwik;
use Piwik\Plugin\Manager;

class Notification
{
    private string $pluginName;

    private Storage $storage;

    public function __construct(string $pluginName, Storage $storage)
    {
        if (!Manager::getInstance()->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $this->pluginName = $pluginName;
        $this->storage = $storage;
    }

    /**
     * Dismisses the notification for the current user
     */
    public function setNotificationDismissed(): void
    {
        $this->storage->setNotificationDismissed();
        $this->removeFromSession();
    }

    /**
     * Removes a notification from current users session
     */
    public function removeFromSession(): void
    {
        MatomoNotification\Manager::cancel($this->getNotificationId());
    }

    private function getNotificationId(): string
    {
        return sprintf('Marketplace_PluginTrialRequest_%s_%s', md5(Piwik::getCurrentUserLogin()), $this->pluginName);
    }
}
