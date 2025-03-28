<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\TokenNotifications;

abstract class TokenNotification implements TokenNotificationInterface
{
    /** @var string */
    private $tokenId;

    /** @var string */
    private $tokenName;

    /** @var string */
    private $tokenCreationDate;

    /** @var string */
    private $email;

    /** @var string */
    private $login;

    public function __construct(
        string $tokenId,
        string $tokenName,
        string $tokenCreationDate,
        string $email,
        string $login
    ) {
        $this->tokenId = $tokenId;
        $this->tokenName = $tokenName;
        $this->tokenCreationDate = $tokenCreationDate;
        $this->email = $email;
        $this->login = $login;
    }

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    public function getTokenName(): string
    {
        return $this->tokenName;
    }

    public function getTokenCreationDate(): string
    {
        return $this->tokenCreationDate;
    }

    public function getEmailAddress(): string
    {
        return $this->email;
    }

    abstract public function getEmailClass(): string;

    public function getLogin(): string
    {
        return $this->login;
    }
}
