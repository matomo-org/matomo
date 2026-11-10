<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Mail\OAuth;

use Matomo\Cache\Lazy;
use Piwik\Config;
use Piwik\Http;

/**
 * Gets an access token with the OAuth 2.0 client credentials flow, using the `[mail] oauth_*` config settings.
 */
class ClientCredentialsTokenProvider implements AccessTokenProvider
{
    private const TIMEOUT_IN_SECONDS = 10;
    private const EXPIRY_MARGIN_IN_SECONDS = 60;
    private const MAX_ERROR_DETAIL_LENGTH = 500;

    /**
     * @var Lazy
     */
    private $cache;

    public function __construct(Lazy $cache)
    {
        $this->cache = $cache;
    }

    public function getAccessToken(): string
    {
        $mailConfig = $this->getMailConfig();

        $tokenUrl = trim((string) ($mailConfig['oauth_token_url'] ?? ''));
        $clientId = trim((string) ($mailConfig['oauth_client_id'] ?? ''));
        $clientSecret = (string) ($mailConfig['oauth_client_secret'] ?? '');
        $scope = trim((string) ($mailConfig['oauth_scope'] ?? ''));

        if ($tokenUrl === '' || $clientId === '' || $clientSecret === '') {
            throw new TokenRequestFailedException(
                'The SMTP OAuth settings are incomplete. Please set oauth_token_url, oauth_client_id and'
                . ' oauth_client_secret in the [mail] section of the config file.'
            );
        }

        if (stripos($tokenUrl, 'https://') !== 0) {
            throw new TokenRequestFailedException('The SMTP OAuth token URL has to start with https://.');
        }

        $cacheId = 'MailOAuthAccessToken_' . hash('sha256', implode("\n", [$tokenUrl, $clientId, $clientSecret, $scope]));

        $cachedToken = $this->cache->fetch($cacheId);
        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $requestBody = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];
        if ($scope !== '') {
            $requestBody['scope'] = $scope;
        }

        $token = $this->parseResponse($this->sendTokenRequest($tokenUrl, $requestBody));

        $lifetime = (int) ($token['expires_in'] ?? 0) - self::EXPIRY_MARGIN_IN_SECONDS;
        if ($lifetime > 0) {
            $this->cache->save($cacheId, $token['access_token'], $lifetime);
        }

        return $token['access_token'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getMailConfig(): array
    {
        return Config::getInstance()->mail;
    }

    /**
     * @param array<string, string> $requestBody
     * @return array{status: ?int, data?: ?string}
     */
    protected function sendTokenRequest(
        string $tokenUrl,
        #[\SensitiveParameter]
        array $requestBody
    ): array {
        try {
            return Http::sendHttpRequestBy(
                Http::getTransportMethod(),
                $tokenUrl,
                self::TIMEOUT_IN_SECONDS,
                $userAgent = null,
                $destinationPath = null,
                $file = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $acceptInvalidSslCertificate = false,
                $byteRange = false,
                $getExtendedInfo = true,
                $httpMethod = 'POST',
                $httpUsername = null,
                $httpPassword = null,
                $requestBody,
                $additionalHeaders = ['Accept: application/json']
            );
        } catch (\Exception $e) {
            throw new TokenRequestFailedException('The SMTP OAuth token request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param array{status: ?int, data?: ?string} $response
     * @return array{access_token: string, expires_in?: mixed}
     */
    private function parseResponse(array $response): array
    {
        $status = (int) ($response['status'] ?? 0);
        $data = json_decode((string) ($response['data'] ?? ''), true);

        if ($status !== 200) {
            throw new TokenRequestFailedException(
                'The SMTP OAuth token request failed with HTTP status ' . $status . $this->getErrorDetail($data) . '.'
            );
        }

        if (!is_array($data) || empty($data['access_token']) || !is_string($data['access_token'])) {
            throw new TokenRequestFailedException('The SMTP OAuth token response did not contain an access token.');
        }

        return $data;
    }

    /**
     * @param mixed $data the decoded response body, if there was any
     */
    private function getErrorDetail($data): string
    {
        if (!is_array($data)) {
            return '';
        }

        $details = [];
        foreach (['error', 'error_description'] as $key) {
            if (!empty($data[$key]) && is_string($data[$key])) {
                $details[] = trim((string) preg_replace('/\s+/', ' ', $data[$key]));
            }
        }

        if (empty($details)) {
            return '';
        }

        return ' (' . rtrim(mb_substr(implode(': ', $details), 0, self::MAX_ERROR_DETAIL_LENGTH), '.') . ')';
    }
}
