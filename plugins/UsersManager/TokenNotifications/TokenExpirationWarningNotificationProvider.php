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

class TokenExpirationWarningNotificationProvider extends TokenNotificationProvider
{
    protected function getPeriodThreshold(): ?string
    {
        $periodDays = (int) Config::getInstance()->General['auth_token_expiration_notification_days'];
        return ($periodDays && $periodDays !== -1) ? Date::factory('today')->subDay($periodDays)->getDateTime() : null;
    }

    protected function getTokensToNotify(string $periodThreshold): array
    {
        $db = Db::get();
        $sql = "SELECT * FROM " . Common::prefixTable('user_token_auth')
            . " WHERE (date_expired IS NOT NULL AND date_expired <= ?)"
            . " AND ts_expiration_notified IS NULL"
            . " AND system_token = 0"
            . " AND login != ?";

        $tokensToNotify = $db->fetchAll($sql, [
            $periodThreshold,
            'anonymous'
        ]);

        return $tokensToNotify;
    }

    protected function createNotification(array $token): TokenNotification
    {
        $user = $this->userModel->getUser($token['login']);
        $email = $user['email'];

        return new AuthTokenEmailExpirationWarningNotification(
            $token['idusertokenauth'],
            $token['description'],
            $token['date_created'],
            [$email],
            [$email => ['login' => $token['login']]],
            $token['date_expired']
        );
    }

    public function setTokenNotificationDispatched(string $tokenId): void
    {
        $this->userModel->setExpirationWarningNotificaitonWasSentForToken($tokenId, Date::factory('now')->getDatetime());
    }
}
