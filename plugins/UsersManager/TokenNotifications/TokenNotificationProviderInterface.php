<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

interface TokenNotificationProviderInterface
{
    /**
     * Provides a list of token notifications to be dispatched,
     * each with their data that can be used e.g. to populate a notification email
     *
     * @return TokenNotificationInterface[]
     */
    public function getTokenNotificationsForDispatch(): array;

    /**
     * Sets information that a notification for a given token has been dispatched
     *
     * @param string $tokenId
     * @return void
     */
    public function setTokenNotificationDispatched(string $tokenId): void;
}
