<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\AuthTokenNotifications;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\UsersManager\Emails\AuthTokenNotificationEmail;

final class AuthTokenNotification
{
    /** @var string */
    private $tokenId;

    /** @var string */
    private $tokenName;

    /** @var string */
    private $tokenCreationDate;

    /** @var string */
    private $login;

    /** @var string */
    private $email;

    /** @var callable */
    private $onNotificationSent;

    public function __construct(
        string $tokenId,
        string $tokenName,
        string $tokenCreationDate,
        string $login,
        string $email,
        callable $onNotificationSent
    ) {
        $this->tokenId = $tokenId;
        $this->tokenName = $tokenName;
        $this->tokenCreationDate = $tokenCreationDate;
        $this->login = $login;
        $this->email = $email;
        $this->onNotificationSent = $onNotificationSent;
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    public function getTokenCreationDate(): string
    {
        return $this->tokenCreationDate;
    }

    public function sendNotification(): void
    {
        // send email
        $email = StaticContainer::getContainer()->make(
            AuthTokenNotificationEmail::class,
            [
                'notification' => $this,
                'rotationPeriodDays' => Config::getInstance()->General['auth_token_rotation_notification_days'],
            ]
        );
        $email->safeSend();

        if (is_callable($this->onNotificationSent)) {
            call_user_func($this->onNotificationSent, $this->tokenId);
        }
    }
}
