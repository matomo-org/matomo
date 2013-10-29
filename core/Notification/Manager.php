<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik\Notification;

use Piwik\Notification;
use Piwik\Session\SessionNamespace;

/**
 * @package Piwik
 * @subpackage Notification
 */
class Manager
{
    /**
     * @var SessionNamespace
     */
    private static $session = null;

    /**
     * Post a notification to be shown in the status bar. If a notification with the same id has already been posted
     * by your application and has not yet been canceled, it will be replaced by the updated information.
     *
     * @param string       $id   A unique identifier for this notification. Id must be a string and may contain only
     *                           word characters (AlNum + underscore)
     * @param Notification $notification
     *
     * @api
     */
    public static function notify($id, Notification $notification)
    {
        self::checkId($id);

        self::addNotification($id, $notification);
    }

    /**
     * Cancel a previously registered (or persistent) notification.
     * @param $id
     */
    public static function cancel($id)
    {
        self::checkId($id);

        self::removeNotification($id);
    }

    /**
     * Cancels all previously registered non-persist notification. Call this method after the notifications have been
     * displayed to make sure all non-persistent notifications won't be displayed multiple times.
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
        $session = static::getSession();
        $session->notifications[$id] = $notification;
    }

    private static function getAllNotifications()
    {
        $session = static::getSession();

        return $session->notifications;
    }

    private static function removeNotification($id)
    {
        $session = static::getSession();
        if (array_key_exists($id, $session->notifications)) {
            unset($session->notifications[$id]);
        }
    }

    /**
     * @return SessionNamespace
     */
    private static function getSession()
    {
        if (!isset(static::$session)) {
            static::$session = new SessionNamespace('notification');
        }

        if (empty(static::$session->notifications)) {
            static::$session->notifications = array();
        }

        return static::$session;
    }
}