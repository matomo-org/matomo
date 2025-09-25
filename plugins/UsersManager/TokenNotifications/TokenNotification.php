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
    /** @var array{login: string, tokenId: string, tokenName: string, tokenDate: string} */
    private $tokens;


    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getTokenIds(): array
    {
        return array_column($this->tokens, 'tokenId');
    }

    abstract public function dispatch(): bool;
}
