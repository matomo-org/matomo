<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\UserNotifications;

use Piwik\Plugins\UsersManager\Emails\InactiveUsersNotificationEmail;

final class InactiveUsersEmailNotification extends UserEmailNotification
{
    public function __construct(
        array $users,
        array $recipients,
        array $emailData = []
    ) {
        parent::__construct(
            $users,
            $recipients,
            $emailData
        );
    }

    public function getEmailClass(): string
    {
        return InactiveUsersNotificationEmail::class;
    }
}
