<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

use Piwik\Plugins\UsersManager\Emails\AuthTokenExpirationWarningNotificationEmail;

final class AuthTokenExpirationWarningEmailNotification extends TokenEmailNotification
{
    /** @var string */
    private $tokenExpirationDate;

    public function __construct(
        string $tokenId,
        string $tokenName,
        string $tokenCreationDate,
        array $recipients,
        array $emailData,
        string $tokenExpirationDate
    ) {
        parent::__construct(
            $tokenId,
            $tokenName,
            $tokenCreationDate,
            $recipients,
            $emailData
        );

        $this->tokenExpirationDate = $tokenExpirationDate;
    }

    public function getTokenExpirationDate(): string
    {
        return $this->tokenExpirationDate;
    }

    public function getEmailClass(): string
    {
        return AuthTokenExpirationWarningNotificationEmail::class;
    }
}
