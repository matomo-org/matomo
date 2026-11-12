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
use Piwik\Date;
use Piwik\Http;

/**
 * Gets an access token with the OAuth 2.0 client credentials flow, using the `[mail] oauth_*` config settings.
 */
class ClientCredentialsTokenProvider implements AccessTokenProvider
{
    private const TIMEOUT_IN_SECONDS = 10;
    private const EXPIRY_MARGIN_IN_SECONDS = 60;
    private const FAILURE_LIFETIME_IN_SECONDS = 60;
    private const MAX_ERROR_DETAIL_LENGTH = 500;

    private Lazy $cache;

    public function __construct(Lazy $cache)
    {
        $this->cache = $cache;
    }

    public function getAccessToken(): string
    {
        [$tokenUrl, $clientId, $clientSecret, $scope] = $this->getSettings();

        if ($tokenUrl === '' || $clientId === '' || $clientSecret === '') {
            throw new TokenRequestFailedException(
                'The SMTP OAuth settings are incomplete. Please set oauth_token_url, oauth_client_id and'
                . ' oauth_client_secret in the [mail] section of the config file.'
            );
        }

        if (stripos($tokenUrl, 'https://') !== 0) {
            throw new TokenRequestFailedException('The SMTP OAuth token URL has to start with https://.');
        }

        $cacheId = $this->getCacheId();

        $cachedToken = $this->fetchUnexpired($cacheId);
        if ($cachedToken !== null) {
            return (string) $cachedToken['token'];
        }

        // a failed request is remembered for a short time, so a batch of emails does not wait for each of them
        $failure = $this->fetchUnexpired($this->getFailureCacheId());
        if ($failure !== null) {
            throw new TokenRequestFailedException((string) $failure['message'], (string) $failure['detail']);
        }

        $requestBody = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
        ];
        if ($scope !== '') {
            $requestBody['scope'] = $scope;
        }

        try {
            $token = $this->requestToken($tokenUrl, $requestBody);
        } catch (TokenRequestFailedException $e) {
            $this->saveWithExpiry(
                $this->getFailureCacheId(),
                ['message' => $e->getMessage(), 'detail' => $e->getDetail()],
                self::FAILURE_LIFETIME_IN_SECONDS
            );

            throw $e;
        }

        $lifetime = (int) ($token['expires_in'] ?? 0) - self::EXPIRY_MARGIN_IN_SECONDS;
        if ($lifetime > 0) {
            $this->saveWithExpiry($cacheId, ['token' => $token['access_token']], $lifetime);
        }

        return $token['access_token'];
    }

    public function forgetAccessToken(): void
    {
        $this->cache->delete($this->getCacheId());
        $this->cache->delete($this->getFailureCacheId());
    }

    /**
     * The time an entry expires is stored with it, because not every cache backend removes an entry after its
     * lifetime. The array backend keeps it for as long as the process runs.
     *
     * @param array<string, string> $entry
     */
    private function saveWithExpiry(string $cacheId, array $entry, int $lifetime): void
    {
        $entry['expires'] = Date::getNowTimestamp() + $lifetime;

        $this->cache->save($cacheId, $entry, $lifetime);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fetchUnexpired(string $cacheId): ?array
    {
        $entry = $this->cache->fetch($cacheId);

        if (!is_array($entry) || (int) ($entry['expires'] ?? 0) <= Date::getNowTimestamp()) {
            return null;
        }

        return $entry;
    }

    /**
     * @param array<string, string> $requestBody
     * @return array{access_token: string, expires_in?: mixed}
     */
    private function requestToken(
        string $tokenUrl,
        #[\SensitiveParameter]
        array $requestBody
    ): array {
        try {
            $response = $this->sendTokenRequest($tokenUrl, $requestBody);
        } catch (\Exception $e) {
            // the reason can name the token URL, so it is kept out of the message
            throw new TokenRequestFailedException('The SMTP OAuth token endpoint could not be reached.', $e->getMessage());
        }

        return $this->parseResponse($response);
    }

    /**
     * @return array{string, string, string, string} token URL, client ID, client secret and scope
     */
    private function getSettings(): array
    {
        $mailConfig = $this->getMailConfig();

        return [
            trim((string) ($mailConfig['oauth_token_url'] ?? '')),
            trim((string) ($mailConfig['oauth_client_id'] ?? '')),
            (string) ($mailConfig['oauth_client_secret'] ?? ''),
            trim((string) ($mailConfig['oauth_scope'] ?? '')),
        ];
    }

    private function getCacheId(): string
    {
        return 'MailOAuthAccessToken_' . hash('sha256', implode("\n", $this->getSettings()));
    }

    private function getFailureCacheId(): string
    {
        return $this->getCacheId() . '_failed';
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
        return Http::sendHttpRequestBy(
            Http::getTransportMethod(),
            $tokenUrl,
            self::TIMEOUT_IN_SECONDS,
            getExtendedInfo: true,
            httpMethod: 'POST',
            requestBody: $requestBody,
            additionalHeaders: ['Accept: application/json'],
            // the request carries the client secret, which must never be sent on to another URL
            followRedirects: false
        );
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
                'The SMTP OAuth token request failed with HTTP status ' . $status . '.',
                $this->getErrorDetail($data)
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
                // the detail is printed on the command line, so control characters are removed
                $details[] = trim((string) preg_replace('/[\s\x00-\x1F\x7F]+/', ' ', $data[$key]));
            }
        }

        return mb_substr(implode(': ', $details), 0, self::MAX_ERROR_DETAIL_LENGTH);
    }
}
