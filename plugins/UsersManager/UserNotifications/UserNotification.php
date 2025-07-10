<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\UserNotifications;

abstract class UserNotification implements UserNotificationInterface
{
    /**
     * Data to hold for users, keyed by their username.
     *
     * @var array
     */
    private $users;

    public function __construct(
        array $users
    ) {
        $this->users = $users;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    abstract public function dispatch(): bool;
}
