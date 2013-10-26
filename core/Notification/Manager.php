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
 * @api
 */
class Manager
{
    private static $session = null;

    /**
     * Post a notification to be shown in the status bar. If a notification with the same id has already been posted by your application and has not yet been canceled, it will be replaced by the updated information.
     *
     * @param string       $id   A unique identifier for this notification
     * @param Notification $notification
     */
    public static function notify($id, Notification $notification)
    {
        $session = static::getSession();
        $session->$id = $notification;

        if (Notification::TYPE_PERSISTENT != $notification->type) {
            $session->setExpirationHops(1, $id);
        }
    }

    public static function getAllNotificationsToDisplay()
    {
        $session = static::getSession();
        $notifications = $session->getIterator();

        $notifications->uasort(function ($n1, $n2) {
            if ($n1->priority == $n2->priority) {
                return 0;
            }

            return $n1->priority > $n2->priority;
        });

        return $notifications;
    }

    /**
     * Cancel a previously registered notification.
     * @param $id
     */
    public static function cancel($id)
    {
        $session = static::getSession();
        unset($session->$id);
    }

    /**
     * @return SessionNamespace
     */
    private static function getSession()
    {
        if (!isset(static::$session)) {
            static::$session = new SessionNamespace('notification');
        }

        return static::$session;
    }
}