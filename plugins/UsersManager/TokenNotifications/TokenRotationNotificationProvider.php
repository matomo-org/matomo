<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Config;
use Piwik\Date;

class TokenRotationNotificationProvider extends TokenNotificationProvider
{
    protected function getPeriodThreshold(): ?string
    {
        $periodDays = (int) Config::getInstance()->General['auth_token_rotation_notification_days'];
        return ($periodDays > 0) ? Date::factory('today')->subDay($periodDays)->getDateTime() : null;
    }

    protected function getTokensToNotify(string $periodThreshold): array
    {
        return $this->userModel->getTokensRequiringRotation($periodThreshold);
    }

    protected function createNotification(string $login, array $tokens): TokenNotification
    {
        $user = $this->userModel->getUser($login);
        $email = $user['email'];

        return new AuthTokenRotationEmailNotification(
            $tokens,
            [$email],
            [$email => ['login' => $login]]
        );
    }

    public function setTokenNotificationDispatched(string $tokenId): void
    {
        $this->userModel->setRotationNotificationWasSentForToken($tokenId, $this->today);
    }
}
