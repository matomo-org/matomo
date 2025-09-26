<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Notification;

use Piwik\Notification;
use Piwik\Session;
use Piwik\Session\SessionNamespace;

/**
 * Posts and removes UI notifications (see {@link Piwik\Notification} to learn more).
 *
 */
class Manager
{
    public const MAX_NOTIFICATIONS_IN_SESSION = 30;

    /**
     * @var ?SessionNamespace
     */
    private static $session = null;

    /**
     * @var array<string, Notification>
     */
    private static $notifications = [];

    /**
     * Posts a notification that will be shown in Piwik's status bar. If a notification with the same ID
     * has been posted and has not been closed/removed, it will be replaced with `$notification`.
     *
     * @param string       $id   A unique identifier for this notification. The ID must be a valid HTML
     *                           element ID. It can only contain alphanumeric characters (underscores can
     *                           be used).
     * @param Notification $notification The notification to post.
     * @return bool true if the notification was added, false if it was ignored because there were too many
     *                   pending ones.
     * @api
     */
    public static function notify($id, Notification $notification): bool
    {
        self::checkId($id);

        self::removeOldestNotificationsIfThereAreTooMany();
        return self::addNotification($id, $notification);
    }

    /**
     * Removes a posted notification by ID.
     *
     * @param string $id The notification ID, see {@link notify()}.
     */
    public static function cancel($id): void
    {
        self::checkId($id);

        self::removeNotification($id);
    }

    /**
     * Removes all temporary notifications.
     *
     * Call this method after the notifications have been
     * displayed to make sure temporary notifications won't be displayed twice.
     */
    public static function cancelAllNonPersistent(): void
    {
        foreach (self::getAllNotifications() as $id => $notification) {
            if (Notification::TYPE_PERSISTENT != $notification->type) {
                self::removeNotification($id);
            }
        }
    }

    /**
     * Determine all notifications that needs to be displayed. They are sorted by priority. Highest priorities first.
     * @return array<string, Notification>
     */
    public static function getAllNotificationsToDisplay(): array
    {
        $notifications = self::getAllNotifications();

        uasort($notifications, function ($n1, $n2) {
            /** @var Notification $n1 */ /** @var Notification $n2 */
            if ($n1->getPriority() == $n2->getPriority()) {
                return 0;
            }

            return $n1->getPriority() > $n2->getPriority() ? -1 : 1;
        });

        return $notifications;
    }

    /**
     * @param $id
     * @throws \Exception In case id is empty or if id contains non word characters
     */
    private static function checkId($id): void
    {
        if (empty($id)) {
            throw new \Exception('Notification ID is empty.');
        }

        if (!preg_match('/^\w*$/', $id)) {
            throw new \Exception('Invalid Notification ID given. Only word characters (AlNum + underscore) allowed.');
        }
    }

    private static function addNotification($id, Notification $notification): bool
    {
        self::saveNotificationAcrossUiRequestsIfNeeded($id, $notification);

        if (count(self::$notifications) >= self::MAX_NOTIFICATIONS_IN_SESSION) {
            return false;
        }

        // we store all kinda notifications here so in case the session is not enabled or disabled later there is still
        // a chance it gets delivered to the UI during the same request.
        self::$notifications[$id] = $notification;

        return true;
    }

    private static function saveNotificationAcrossUiRequestsIfNeeded($id, Notification $notification): void
    {
        if (self::isSessionEnabled()) {
            // we need to save even non persistent notifications if possible. Otherwise if there's a redirect
            // a notification is not shown on the next page view
            $session = self::getSession();
            $session->notifications[$id] = $notification;
        }
    }

    private static function removeOldestNotificationsIfThereAreTooMany(): void
    {
        if (!self::isSessionEnabled()) {
            return;
        }

        $maxNotificationsInSession = self::MAX_NOTIFICATIONS_IN_SESSION;

        $session = self::getSession();

        while (count($session->notifications) >= $maxNotificationsInSession) {
            array_shift($session->notifications);
        }
    }

    /**
     * @return array<string, Notification>
     */
    private static function getAllNotifications(): array
    {
        if (!self::isSessionEnabled()) {
            return [];
        }

        $notifications = self::$notifications;

        foreach ($notifications as $id => $notification) {
            // we copy them over to the session if possible and persist it in case the session was not yet
            // writable / enabled at the time the notification was added.
            self::saveNotificationAcrossUiRequestsIfNeeded($id, $notification);
        }

        $session = self::getSession();
        foreach ($session->notifications as $id => $notification) {
            $notifications[$id] = $notification;
        }

        return $notifications;
    }

    private static function removeNotification($id): void
    {
        if (array_key_exists($id, self::$notifications)) {
            unset(self::$notifications[$id]);
        }

        if (self::isSessionEnabled()) {
            $session = self::getSession();
            if (array_key_exists($id, $session->notifications)) {
                unset($session->notifications[$id]);
            }
        }
    }

    private static function isSessionEnabled(): bool
    {
        return Session::isWritable() && Session::isReadable();
    }

    private static function getSession(): SessionNamespace
    {
        if (!isset(self::$session)) {
            self::$session = new SessionNamespace('notification');
        }

        if (empty(self::$session->notifications) && self::isSessionEnabled()) {
            self::$session->notifications = [];
        }

        return self::$session;
    }

    public static function cancelAllNotifications(): void
    {
        self::$notifications = [];
    }

    /**
     * for tests
     * @return array<string, Notification>
     */
    public static function getPendingInMemoryNotifications(): array
    {
        return self::$notifications;
    }
}
