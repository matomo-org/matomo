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
 * The settings a client uses to request an access token from an OAuth 2.0 token endpoint.
 * The client authenticates with either a client secret or a certificate.
 *
 * @internal
 */
class ClientCredentials
{
    public const CREDENTIAL_TYPE_SECRET = 'secret';
    public const CREDENTIAL_TYPE_CERTIFICATE = 'certificate';

    private string $tokenUrl;
    private string $clientId;
    private string $scope;
    private string $credentialType;
    private string $clientSecret;
    private string $certificatePath;
    private string $privateKeyPath;
    private string $privateKeyPassword;

    /**
     * @param string $privateKeyPath when empty, the private key is read from the certificate file
     */
    public function __construct(
        string $tokenUrl,
        string $clientId,
        string $scope,
        string $credentialType,
        #[\SensitiveParameter]
        string $clientSecret = '',
        string $certificatePath = '',
        string $privateKeyPath = '',
        #[\SensitiveParameter]
        string $privateKeyPassword = ''
    ) {
        $this->tokenUrl = $tokenUrl;
        $this->clientId = $clientId;
        $this->scope = $scope;
        $this->credentialType = $credentialType;
        $this->clientSecret = $clientSecret;
        $this->certificatePath = $certificatePath;
        $this->privateKeyPath = $privateKeyPath;
        $this->privateKeyPassword = $privateKeyPassword;
    }

    public function getTokenUrl(): string
    {
        return $this->tokenUrl;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getScope(): string
    {
        return $this->scope;
    }

    public function getCredentialType(): string
    {
        return $this->credentialType;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getCertificatePath(): string
    {
        return $this->certificatePath;
    }

    public function getPrivateKeyPath(): string
    {
        return $this->privateKeyPath;
    }

    public function getPrivateKeyPassword(): string
    {
        return $this->privateKeyPassword;
    }

    public function isCertificate(): bool
    {
        return $this->credentialType === self::CREDENTIAL_TYPE_CERTIFICATE;
    }

    public function hasValidCredentialType(): bool
    {
        return in_array($this->credentialType, [self::CREDENTIAL_TYPE_SECRET, self::CREDENTIAL_TYPE_CERTIFICATE], true);
    }

    /**
     * Returns the name of the setting that holds the credential, the client secret or the certificate path.
     */
    public function getCredentialSettingName(): string
    {
        return $this->isCertificate() ? 'certificate_path' : 'client_secret';
    }

    public function isComplete(): bool
    {
        $credential = $this->isCertificate() ? $this->certificatePath : $this->clientSecret;

        return $this->tokenUrl !== '' && $this->clientId !== '' && $credential !== '';
    }

    /**
     * Identifies these settings, without revealing the secret, so a token can be cached for them.
     */
    public function getHash(): string
    {
        return hash('sha256', implode("\n", [
            $this->tokenUrl,
            $this->clientId,
            $this->scope,
            $this->credentialType,
            $this->clientSecret,
            $this->certificatePath,
            $this->privateKeyPath,
            $this->privateKeyPassword,
        ]));
    }
}
