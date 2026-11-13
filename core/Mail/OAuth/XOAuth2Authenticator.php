<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Mail\OAuth;

use PHPMailer\PHPMailer\OAuthTokenProvider;

/**
 * Builds the SMTP XOAUTH2 credentials for a mailbox from an access token.
 *
 * It does not care which OAuth flow the access token came from.
 */
class XOAuth2Authenticator implements OAuthTokenProvider
{
    private string $mailbox;
    private string $accessToken;

    public function __construct(
        string $mailbox,
        #[\SensitiveParameter]
        string $accessToken
    ) {
        $this->mailbox = $mailbox;
        $this->accessToken = $accessToken;
    }

    public function getOauth64(): string
    {
        return base64_encode('user=' . $this->mailbox . "\001auth=Bearer " . $this->accessToken . "\001\001");
    }
}
