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

    public function __construct(
        string $tokenId,
        string $tokenName,
        string $tokenCreationDate
    ) {
        $this->tokenId = $tokenId;
        $this->tokenName = $tokenName;
        $this->tokenCreationDate = $tokenCreationDate;
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

    abstract public function dispatch(): bool;
}
