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
use Piwik\Plugins\UsersManager\TokenNotifications\TokenNotificationProviderInterface;
use Piwik\Plugins\UsersManager\Model as UserModel;

class TokenNotificationProvider implements TokenNotificationProviderInterface
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

    private function getRotationPeriodThreshold(): ?string
    {
        $periodDays = (int) Config::getInstance()->General['auth_token_rotation_notification_days'];
        return $periodDays ? Date::factory('today')->subDay($periodDays)->getDateTime() : null;
    }

    public function getTokenNotificationsForDispatch(): array
    {
        $rotationThreshold = $this->getRotationPeriodThreshold();
        if (null === $rotationThreshold) {
            return [];
        }

        $db = Db::get();
        $sql = "SELECT * FROM " . Common::prefixTable('user_token_auth')
            . " WHERE (date_expired is null or date_expired > ?)"
            . " AND (date_created <= ?)"
            . " AND ts_rotation_notified is null"
            . " AND system_token = 0"
            . " AND login != ?";

        $tokensToNotify = $db->fetchAll($sql, [
            $this->today,
            $rotationThreshold,
            'anonymous'
        ]);

        $notifications = [];

        foreach ($tokensToNotify as $t) {
            $user = $this->userModel->getUser($t['login']);
            $email = $user['email'];

            $notifications[] = new AuthTokenEmailNotification(
                $t['idusertokenauth'],
                $t['description'],
                $t['date_created'],
                [$email],
                [$email => ['login' => $t['login']]]
            );
        }

        return $notifications;
    }

    public function setTokenNotificationDispatched(string $tokenId): void
    {
        $this->userModel->setRotationNotificationWasSentForToken($tokenId, Date::factory('now')->getDatetime());
    }
}
