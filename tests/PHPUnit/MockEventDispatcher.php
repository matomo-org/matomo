<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class MockEventDispatcher extends Event_Dispatcher
{
    private $forcedNotificationObject = false;

    function __construct($forcedNotificationObject)
    {
        $this->forcedNotificationObject = $forcedNotificationObject;
    }

    function &postNotification(&$notification, $pending = true, $bubble = true)
    {
        $notification->_notificationObject = $this->forcedNotificationObject;

        return $notification;
    }
}
