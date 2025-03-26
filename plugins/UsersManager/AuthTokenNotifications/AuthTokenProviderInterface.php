<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\AuthTokenNotifications;

interface AuthTokenProviderInterface
{
    /**
     * Provide a list of auth tokens to be notified, each with their information
     * that can be used to populate the notification email
     *
     * @return AuthTokenNotification[]
     */
    public function getAuthTokensToNotify(): array;
}
