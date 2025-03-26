<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\UsersManager\AuthTokenNotifications\AuthTokenNotification;
use Piwik\Plugins\UsersManager\AuthTokenNotifications\AuthTokenProviderInterface;
use Piwik\Plugins\UsersManager\Model as UserModel;

class AuthTokenProvider implements AuthTokenProviderInterface
{
    /** @var Model */
    private $userModel;

    /** @var string */
    private $today;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->today = Date::factory('today')->getDatetime();
    }

    private function getRotationPeriodThreshold(): string
    {
        $periodDays = Config::getInstance()->General['auth_token_rotation_notification_days'];
        return Date::factory('today')->subDay($periodDays)->getDateTime();
    }

    public function setTokenNotified(string $tokenId): void
    {
        $this->userModel->setRotationNotificationWasSentForToken($tokenId, $this->today);
    }

    public function getAuthTokensToNotify(): array
    {
        $db = Db::get();
        $sql = "SELECT * FROM " . Common::prefixTable('user_token_auth')
            . " WHERE (date_expired is null or date_expired > ?)"
            . " AND (date_created <= ?)"
            . " AND ts_rotation_notified is null";

        $tokensToNotify = $db->fetchAll($sql, [
            $this->today,
            $this->getRotationPeriodThreshold()
        ]);

        $notifications = [];

        foreach ($tokensToNotify as $t) {
            $user = $this->userModel->getUser($t->login);
            $email = $user['email'];

            $notifications[] = new AuthTokenNotification(
                $t->idusertokenauth,
                $t->description,
                $t->date_created,
                $t->login,
                $email,
                [static::class, 'setTokenNotified']
            );
        }

        return $notifications;
    }
}
