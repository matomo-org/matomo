<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Config\GeneralConfig;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Session;
use Piwik\Session\SessionNamespace;

final class Service
{
    private const SESSION_NAMESPACE = 'Marketplace.PluginTrial';

    // bounds how long a notification the session dropped, or one built for another site or language, can stay so
    private const MAX_SECONDS_BETWEEN_CHECKS = 600;

    public function __construct()
    {
    }

    /**
     * Creates a trial request (and sends a mail to all super users)
     */
    public function request(string $pluginName, string $pluginDisplayName): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        $request->create($pluginDisplayName);
    }


    /**
     * Returns if the current user has already requested a plugin
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
     * Returns if any user's request for a plugin is pending, so super users were already notified of it
     */
    public function isPending(string $pluginName): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (new Storage($pluginName))->wasRequested();
    }

    /**
     * Creates notifications for all available plugin trial requests if any
     *
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

        // this runs on every request, so it is skipped while no request has changed since this session last checked
        $changeMarker = Storage::getChangeMarker();
        $session = self::isSessionEnabled() ? new SessionNamespace(self::SESSION_NAMESPACE) : null;
        if (
            $session !== null
            && $session->checkedMarker === $changeMarker
            && $session->checkedLogin === Piwik::getCurrentUserLogin()
            && $session->checkedAt > time() - self::MAX_SECONDS_BETWEEN_CHECKS
            // activating a plugin outside the plugin manager, eg in the config, changes no request
            && !array_filter((array) $session->checkedPlugins, [Manager::getInstance(), 'isPluginActivated'])
        ) {
            return;
        }

        // looked up at most once, and only if a check needs them
        $superUsers = null;
        $getSuperUsers = function () use (&$superUsers): array {
            return $superUsers ??= Storage::getSuperUserLogins();
        };
        $allChecked = true;
        $checkedPlugins = [];

        foreach (Storage::getAllInStorage() as $pluginName => $storage) {
            if (Manager::getInstance()->isPluginActivated($pluginName)) {
                try {
                    $this->cancelRequest($pluginName);
                } catch (\Throwable $e) {
                    // retried on the next page view, and must not hide the other plugins' notifications
                    $allChecked = false;
                }
                continue;
            }

            $checkedPlugins[] = $pluginName;

            $trialRequest = new Notification($pluginName, $storage);
            $trialRequest->createNotificationIfNeeded($getSuperUsers);
        }

        // a check that needed the current super users can change with them, and demoting one changes no request
        if ($session !== null && $allChecked && $superUsers === null) {
            $session->checkedMarker = $changeMarker;
            $session->checkedLogin = Piwik::getCurrentUserLogin();
            $session->checkedPlugins = $checkedPlugins;
            $session->checkedAt = time();
        }
    }

    /**
     * Dismisses a plugin trial notification for the current (super) user if the provided notification id matches a
     * plugin trial request.
     *
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

        if (!str_starts_with($notificationId, 'Marketplace_PluginTrialRequest_')) {
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

    /**
     * Without a session the notifications are not kept between requests, so every request has to check again
     */
    private static function isSessionEnabled(): bool
    {
        return Session::isWritable() && Session::isReadable();
    }

    public function isEnabled(): bool
    {
        return -1 !== GeneralConfig::getIntegerConfigValue('plugin_trial_request_expiration_in_days', 0);
    }
}
