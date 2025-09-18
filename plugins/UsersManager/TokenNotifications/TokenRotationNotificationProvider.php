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
        // Join on user table is done, to ensure we only fetch tokens, where the user still exists
        $sql = "SELECT * FROM " . Common::prefixTable('user_token_auth') . " t"
            . " JOIN  " . Common::prefixTable('user') . " u ON t.login = u.login"
            . " WHERE (t.date_expired is null or t.date_expired > ?)"
            . " AND (t.date_created <= ?)"
            . " AND t.ts_rotation_notified is null"
            . " AND t.system_token = 0"
            . " AND t.login != ?";

        $tokensToNotify = $db->fetchAll($sql, [
            $this->today,
            $periodThreshold,
            'anonymous',
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
        $this->userModel->setRotationNotificationWasSentForToken($tokenId, $this->today);
    }
}
