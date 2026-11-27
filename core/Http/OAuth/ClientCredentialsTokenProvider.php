<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Http\OAuth;

use Matomo\Cache\Lazy;
use Piwik\Date;
use Piwik\Http;
use Psr\Log\LoggerInterface;

/**
 * Gets an access token with the OAuth 2.0 client credentials flow and caches it until shortly before it expires.
 *
 * @internal
 */
abstract class ClientCredentialsTokenProvider implements AccessTokenProvider
{
    private const TIMEOUT_IN_SECONDS = 10;
    private const EXPIRY_MARGIN_IN_SECONDS = 60;
    private const FAILURE_LIFETIME_IN_SECONDS = 60;
    private const MAX_ERROR_DETAIL_LENGTH = 500;

    private Lazy $cache;

    private LoggerInterface $logger;

    public function __construct(Lazy $cache, LoggerInterface $logger)
    {
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Called on every request, so changed settings are used right away.
     */
    abstract protected function getClientCredentials(): ClientCredentials;

    /**
     * Names the connection in error messages, for example "SMTP".
     */
    abstract protected function getConnectionName(): string;

    public function getAccessToken(): string
    {
        $credentials = $this->getClientCredentials();
        $tokenUrl = $credentials->getTokenUrl();

        if (!$credentials->hasValidCredentialType() || !$credentials->isComplete()) {
            throw new TokenRequestFailedException($this->getMessage('The %s OAuth settings are incomplete.'));
        }

        if (stripos($tokenUrl, 'https://') !== 0) {
            throw new TokenRequestFailedException($this->getMessage('The %s OAuth token URL has to start with https://.'));
        }

        $cacheId = $this->getCacheId($credentials);

        $cachedToken = $this->fetchUnexpired($cacheId);
        if ($cachedToken !== null) {
            return (string) $cachedToken['token'];
        }

        // a failed request is remembered for a short time, so a batch of calls does not wait for each of them
        $failure = $this->fetchUnexpired($this->getFailureCacheId($credentials));
        if ($failure !== null) {
            throw new TokenRequestFailedException((string) $failure['message'], (string) $failure['detail']);
        }

        try {
            $token = $this->requestToken($tokenUrl, $this->getRequestBody($credentials));
        } catch (TokenRequestFailedException $e) {
            $this->saveWithExpiry(
                $this->getFailureCacheId($credentials),
                ['message' => $e->getMessage(), 'detail' => $e->getDetail()],
                self::FAILURE_LIFETIME_IN_SECONDS
            );

            // the message stays generic because it can be shown to visitors, so the reason is only logged
            $this->logger->warning('{message} Reason: {detail}', [
                'message' => $e->getMessage(),
                'detail' => $e->getDetail(),
                'ignoreInScreenWriter' => true,
            ]);

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
        try {
            $credentials = $this->getClientCredentials();
        } catch (TokenRequestFailedException $e) {
            // no token was cached for settings that cannot be used
            return;
        }

        $this->cache->delete($this->getCacheId($credentials));
        $this->cache->delete($this->getFailureCacheId($credentials));
    }

    /**
     * The expiry is stored with the entry, because not every cache backend removes expired entries.
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
            throw new TokenRequestFailedException($this->getMessage('The %s OAuth token endpoint could not be reached.'), $e->getMessage());
        }

        return $this->parseResponse($response);
    }

    /**
     * @return array<string, string>
     */
    private function getRequestBody(ClientCredentials $credentials): array
    {
        $requestBody = [
            'grant_type' => 'client_credentials',
            'client_id'  => $credentials->getClientId(),
        ];

        if ($credentials->isCertificate()) {
            try {
                $assertion = ClientAssertion::create(
                    $credentials->getClientId(),
                    $credentials->getTokenUrl(),
                    $credentials->getCertificatePath(),
                    $credentials->getPrivateKeyPath(),
                    $credentials->getPrivateKeyPassword(),
                    Date::getNowTimestamp()
                );
            } catch (TokenRequestFailedException $e) {
                throw new TokenRequestFailedException(
                    $this->getMessage('The %s OAuth certificate or private key could not be used.'),
                    $e->getDetail()
                );
            }

            $requestBody['client_assertion_type'] = ClientAssertion::ASSERTION_TYPE;
            $requestBody['client_assertion'] = $assertion;
        } else {
            $requestBody['client_secret'] = $credentials->getClientSecret();
        }

        if ($credentials->getScope() !== '') {
            $requestBody['scope'] = $credentials->getScope();
        }

        return $requestBody;
    }

    private function getCacheId(ClientCredentials $credentials): string
    {
        return 'OAuthAccessToken_' . $credentials->getHash();
    }

    private function getFailureCacheId(ClientCredentials $credentials): string
    {
        return $this->getCacheId($credentials) . '_failed';
    }

    private function getMessage(string $message): string
    {
        return sprintf($message, $this->getConnectionName());
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
                $this->getMessage('The %s OAuth token request failed with HTTP status ' . $status . '.'),
                $this->getErrorDetail($data)
            );
        }

        if (!is_array($data) || empty($data['access_token']) || !is_string($data['access_token'])) {
            throw new TokenRequestFailedException($this->getMessage('The %s OAuth token response did not contain an access token.'));
        }

        return $data;
    }

    private function getErrorDetail(mixed $data): string
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
