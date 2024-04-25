<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Config\GeneralConfig;
use Piwik\Piwik;
use Piwik\Session;

final class Service
{
    public function __construct()
    {
    }

    /**
     * Creates a trial request (and sends a mail to all super users)
     *
     * @return void
     */
    public function request(string $pluginName): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        $request->create();
    }


    /**
     * Returns if a plugin was already requested
     *
     * @return bool
     */
    public function wasRequested(string $pluginName): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        return $request->wasRequested();
    }

    /**
     * Creates notifications for all available plugin trial requests if any
     *
     * @return void
     * @throws Exception
     */
    public function createNotificationsIfNeeded(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return; // only super users can see and dismiss those notifications
        }

        foreach (Storage::getPluginsInStorage() as $pluginName) {
            $trialRequest = new Notification($pluginName, new Storage($pluginName));
            $trialRequest->createNotificationIfNeeded();
        }
    }

    /**
     * Dismisses a plugin trial notification for the current (super) user if the provided notification id matches a
     * plugin trial request.
     *
     * @param string $notificationId
     * @return void
     * @throws Exception
     */
    public function dismissNotification(string $notificationId): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return; // only super users can see and dismiss those notifications
        }

        if (strpos($notificationId, 'Marketplace_PluginTrialRequest_') !== 0) {
            return; // Ignore other notifications
        }

        $notificationParts = explode('_', $notificationId, 4);

        if (count($notificationParts) !== 4) {
            return; // unable to parse notification id
        }

        $userLogin = $notificationParts[2];
        $pluginName = $notificationParts[3];

        if ($userLogin !== md5(Piwik::getCurrentUserLogin())) {
            return; // Don't allow to unset notifications for other users
        }

        $pluginTrial = new Notification($pluginName, new Storage($pluginName));
        $pluginTrial->setNotificationDismissed();
    }

    /**
     * Cancels a plugin trial request
     *
     * @param string $pluginName
     * @return void
     * @throws Exception
     */
    public function cancelRequest(string $pluginName): void
    {
        $request = new Request($pluginName, new Storage($pluginName));
        $request->cancel();

        if (Piwik::hasUserSuperUserAccess() && Session::isStarted()) {
            $notification = new Notification($pluginName, new Storage($pluginName));
            $notification->removeFromSession();
        }
    }

    public function isEnabled(): bool
    {
        return -1 !== (int) GeneralConfig::getConfigValue('plugin_trial_request_expiration_in_days');
    }
}
