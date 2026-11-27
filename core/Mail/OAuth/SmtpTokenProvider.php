<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Mail\OAuth;

use Piwik\Config;
use Piwik\Http\OAuth\ClientCredentials;
use Piwik\Http\OAuth\ClientCredentialsTokenProvider;
use Piwik\Http\OAuth\TokenRequestFailedException;

/**
 * Gets the SMTP access token with the `[mail] oauth_*` config settings.
 *
 * @internal
 */
class SmtpTokenProvider extends ClientCredentialsTokenProvider
{
    protected function getClientCredentials(): ClientCredentials
    {
        $mailConfig = $this->getMailConfig();

        $credentials = new ClientCredentials(
            trim((string) ($mailConfig['oauth_token_url'] ?? '')),
            trim((string) ($mailConfig['oauth_client_id'] ?? '')),
            trim((string) ($mailConfig['oauth_scope'] ?? '')),
            strtolower(trim((string) ($mailConfig['oauth_credential_type'] ?? ''))) ?: ClientCredentials::CREDENTIAL_TYPE_SECRET,
            (string) ($mailConfig['oauth_client_secret'] ?? ''),
            trim((string) ($mailConfig['oauth_certificate_path'] ?? '')),
            trim((string) ($mailConfig['oauth_private_key_path'] ?? '')),
            (string) ($mailConfig['oauth_private_key_password'] ?? '')
        );

        if (!$credentials->hasValidCredentialType()) {
            throw new TokenRequestFailedException('The SMTP OAuth setting oauth_credential_type has to be either "secret" or "certificate".');
        }

        if (!$credentials->isComplete()) {
            throw new TokenRequestFailedException(
                'The SMTP OAuth settings are incomplete. Please set oauth_token_url, oauth_client_id and oauth_'
                . ($credentials->isCertificate() ? 'certificate_path' : 'client_secret') . ' in the [mail] section of the config file.'
            );
        }

        return $credentials;
    }

    protected function getConnectionName(): string
    {
        return 'SMTP';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getMailConfig(): array
    {
        return Config::getInstance()->mail;
    }
}
