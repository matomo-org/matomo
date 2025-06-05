<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;

class TokenRotationNotificationProvider extends TokenNotificationProvider
{
    protected function getPeriodThreshold(): ?string
    {
        $periodDays = (int) Config::getInstance()->General['auth_token_rotation_notification_days'];
        return ($periodDays > 0) ? Date::factory('today')->subDay($periodDays)->getDateTime() : null;
    }

    protected function getTokensToNotify(string $periodThreshold): array
    {
        $db = Db::get();
        $sql = "SELECT * FROM " . Common::prefixTable('user_token_auth')
            . " WHERE (date_expired is null or date_expired > ?)"
            . " AND (date_created <= ?)"
            . " AND ts_rotation_notified is null"
            . " AND system_token = 0"
            . " AND login != ?";

        $tokensToNotify = $db->fetchAll($sql, [
            $this->today,
            $periodThreshold,
            'anonymous'
        ]);

        return $tokensToNotify;
    }

    protected function createNotification(array $token): TokenNotification
    {
        $user = $this->userModel->getUser($token['login']);
        $email = $user['email'];

        return new AuthTokenRotationEmailNotification(
            $token['idusertokenauth'],
            $token['description'],
            $token['date_created'],
            [$email],
            [$email => ['login' => $token['login']]]
        );
    }

    public function setTokenNotificationDispatched(string $tokenId): void
    {
        $this->userModel->setRotationNotificationWasSentForToken($tokenId, Date::factory('now')->getDatetime());
    }
}
