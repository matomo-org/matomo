<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Http\OAuth;

/**
 * Provides an OAuth 2.0 access token for a connection to another service.
 *
 * @internal
 */
interface AccessTokenProvider
{
    public function getAccessToken(): string;

    /**
     * Drops a cached access token, so the next call requests a new one.
     */
    public function forgetAccessToken(): void;
}
