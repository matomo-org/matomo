<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Date;
use Piwik\Plugins\UsersManager\Model as UserModel;

abstract class TokenNotificationProvider implements TokenNotificationProviderInterface
{
    /** @var Model */
    protected $userModel;

    /** @var string */
    protected $today;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->today = Date::factory('now')->getDatetime();
    }

    abstract protected function getPeriodThreshold(): ?string;

    abstract protected function getTokensToNotify(string $periodThreshold): array;

    abstract protected function createNotification(array $token): TokenNotification;

    public function getTokenNotificationsForDispatch(): array
    {
        $periodThreshold = $this->getPeriodThreshold();
        if (null === $periodThreshold) {
            return [];
        }

        $tokensToNotify = $this->getTokensToNotify($periodThreshold);

        $notifications = [];

        foreach ($tokensToNotify as $t) {
            $notifications[] = $this->createNotification($t);
        }

        return $notifications;
    }
}
