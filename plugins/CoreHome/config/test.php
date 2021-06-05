<?php

use Piwik\Notification;

return [
    'observers.global' => DI\add([
        [
            'Request.dispatch',
            DI\value(
                function () {
                    if (!empty($_GET['setNotifications']) && $_GET['setNotifications'] == 1) {
                        // trigger some notification
                        $notification          = new Notification('This is a persistent test notification');
                        $notification->title   = 'Warning:';
                        $notification->context = Notification::CONTEXT_WARNING;
                        $notification->type    = Notification::TYPE_PERSISTENT;
                        $notification->flags   = Notification::FLAG_CLEAR;
                        Notification\Manager::notify('NotificationFixture_persistent_warning', $notification);

                        $notification          = new Notification('This is another persistent test notification');
                        $notification->title   = 'Error:';
                        $notification->context = Notification::CONTEXT_ERROR;
                        $notification->type    = Notification::TYPE_PERSISTENT;
                        $notification->flags   = Notification::FLAG_CLEAR;
                        Notification\Manager::notify('NotificationFixture_persistent_error', $notification);

                        $notification          = new Notification('This is transient test notification');
                        $notification->title   = 'Error:';
                        $notification->context = Notification::CONTEXT_ERROR;
                        $notification->type    = Notification::TYPE_TRANSIENT;
                        $notification->flags   = Notification::FLAG_CLEAR;
                        Notification\Manager::notify('NotificationFixture_transient_error', $notification);
                    }
                }
            ),
        ],
    ]),
];