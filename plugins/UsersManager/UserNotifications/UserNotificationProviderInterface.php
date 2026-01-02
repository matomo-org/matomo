<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\UserNotifications;

interface UserNotificationProviderInterface
{
    /**
     * Provides a list of user notifications to be dispatched,
     * each with their data that can be used, e.g. to populate a notification email
     *
     * @return UserNotificationInterface[]
     */
    public function getUserNotificationsForDispatch(): array;

    /**
     * Sets information that a notification for a set of users has been dispatched
     *
     * @param array $users
     * @return void
     */
    public function setUserNotificationDispatched(array $users): void;
}
