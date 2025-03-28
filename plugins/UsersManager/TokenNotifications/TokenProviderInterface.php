<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

interface TokenProviderInterface
{
    /**
     * Provides a list of tokens to be notified, each with their information
     * that can be used to populate the notification email
     *
     * @return TokenNotificationInterface[]
     */
    public function getTokensToNotify(): array;

    /**
     * Returns a callable that is called when the notification has been sent for a given token.
     * The callable is provided with unique token id as its param.
     *
     * @return callable
     */
    public function onTokenNotified(): callable;
}
