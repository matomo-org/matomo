<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
    /**
     * @var SessionNamespace
     */
    private static $session = null;

    /**
     * Posts a notification that will be shown in Piwik's status bar. If a notification with the same ID
     * has been posted and has not been closed/removed, it will be replaced with `$notification`.
     *
     * @param string       $id   A unique identifier for this notification. The ID must be a valid HTML
     *                           element ID. It can only contain alphanumeric characters (underscores can
     *                           be used).
     * @param Notification $notification The notification to post.
     * @api
     */
    public static function notify($id, Notification $notification)
    {
        self::checkId($id);

        self::removeOldestNotificationsIfThereAreTooMany();
        self::addNotification($id, $notification);
    }

    /**
     * Removes a posted notification by ID.
     *
     * @param string $id The notification ID, see {@link notify()}.
     */
    public static function cancel($id)
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
    public static function cancelAllNonPersistent()
    {
        foreach (static::getAllNotifications() as $id => $notification) {
            if (Notification::TYPE_PERSISTENT != $notification->type) {
                static::removeNotification($id);
            }
        }
    }

    /**
     * Determine all notifications that needs to be displayed. They are sorted by priority. Highest priorities first.
     * @return \ArrayObject
     */
    public static function getAllNotificationsToDisplay()
    {
        $notifications = static::getAllNotifications();

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
    private static function checkId($id)
    {
        if (empty($id)) {
            throw new \Exception('Notification ID is empty.');
        }

        if (!preg_match('/^(\w)*$/', $id)) {
            throw new \Exception('Invalid Notification ID given. Only word characters (AlNum + underscore) allowed.');
        }
    }

    private static function addNotification($id, Notification $notification)
    {
        if (!self::isEnabled()) {
            return;
        }

        $session = static::getSession();
        $session->notifications[$id] = $notification;
    }

    private static function removeOldestNotificationsIfThereAreTooMany()
    {
        $maxNotificationsInSession = 30;

        $session = static::getSession();

        while (count($session->notifications) >= $maxNotificationsInSession) {
            array_shift($session->notifications);
        }
    }

    private static function getAllNotifications()
    {
        if (!self::isEnabled()) {
            return array();
        }

        $session = static::getSession();

        return $session->notifications;
    }

    private static function removeNotification($id)
    {
        if (!self::isEnabled()) {
            return;
        }

        $session = static::getSession();
        if (array_key_exists($id, $session->notifications)) {
            unset($session->notifications[$id]);
        }
    }

    private static function isEnabled()
    {
        return Session::isWritable() && Session::isReadable();
    }

    /**
     * @return SessionNamespace
     */
    private static function getSession()
    {
        if (!isset(static::$session)) {
            static::$session = new SessionNamespace('notification');
        }

        if (empty(static::$session->notifications) && self::isEnabled()) {
            static::$session->notifications = array();
        }

        return static::$session;
    }
}
