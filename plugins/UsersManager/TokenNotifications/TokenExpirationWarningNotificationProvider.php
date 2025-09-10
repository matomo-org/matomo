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
        return ($periodDays > 0) ? Date::factory('today')->addDay($periodDays)->getDateTime() : null;
    }

    protected function getTokensToNotify(string $periodThreshold): array
    {
        $db = Db::get();
        $sql = "SELECT * FROM " . Common::prefixTable('user_token_auth')
            . " WHERE date_expired IS NOT null"
            . " AND (date_expired <= ?)"
            . " AND (date_created <= ?)"
            . " AND ts_expiration_warning_notified IS NULL"
            . " AND system_token = 0"
            . " AND login != ?";

        $tokensToNotify = $db->fetchAll($sql, [
            $periodThreshold,
            $this->today,
            'anonymous'
        ]);

        return $tokensToNotify;
    }

    protected function createNotification(array $token): TokenNotification
    {
        $user = $this->userModel->getUser($token['login']);
        $email = $user['email'];

        return new AuthTokenExpirationWarningEmailNotification(
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
        $this->userModel->setExpirationWarningNotificationWasSentForToken($tokenId, Date::factory('now')->getDatetime());
    }
}
