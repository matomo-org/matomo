<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Mail\OAuth;

/**
 * Provides the OAuth 2.0 access token used to authenticate against the SMTP server.
 *
 * Each OAuth flow gets its own implementation. Which one is used is decided in the DI config.
 */
interface AccessTokenProvider
{
    public function getAccessToken(): string;

    /**
     * Drops a cached access token, so the next email requests a new one.
     */
    public function forgetAccessToken(): void;
}
