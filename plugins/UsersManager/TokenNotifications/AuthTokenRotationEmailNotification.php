<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Plugins\UsersManager\Emails\AuthTokenRotationNotificationEmail;

final class AuthTokenRotationEmailNotification extends TokenEmailNotification
{
    public function getEmailClass(): string
    {
        return AuthTokenRotationNotificationEmail::class;
    }
}
