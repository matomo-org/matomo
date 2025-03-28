<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use Piwik\Plugins\UsersManager\Emails\AuthTokenNotificationEmail;
use Piwik\Plugins\UsersManager\TokenNotifications\TokenNotification;

class AuthTokenNotification extends TokenNotification
{
    public function getEmailClass(): string
    {
        return AuthTokenNotificationEmail::class;
    }
}
